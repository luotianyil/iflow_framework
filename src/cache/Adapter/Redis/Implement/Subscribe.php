<?php

namespace iflow\cache\Adapter\Redis\Implement;

use iflow\cache\Adapter\Redis\Redis;
use iflow\Container\Container;
use Swoole\Process\Manager;
use Swoole\Process\Pool;

class Subscribe {

    // 订阅的主题
    protected array $topic = [];

    protected Manager $manager;

    /**
     * redis 连接句柄
     * @var Redis
     */
    protected Redis $redis;

    /**
     * 订阅初始化
     * @param Redis $redis
     * @param array $topic
     * @return $this
     */
    public function initializer(Redis $redis, array $topic = []): static {
        $this->manager = new Manager();
        $this->redis = $redis;

        foreach ($topic as $t) $this->addSubscribeTopic($t);

        return $this;
    }

    /**
     * 新增订阅
     * @param array $topic 订阅的主题信息
     * @return $this
     */
    public function addSubscribeTopic(array $topic): static {
        $this->manager -> add(function (Pool $pool, int $workerId) use ($topic) {
            $topic['name'] = is_array($topic['name']) ? $topic['name'] : [ $topic['name'] ];
            $this -> subscribe($pool, $workerId, $topic);
        }, $topic['enableCoroutine'] ?? true);
        return $this;
    }

    /**
     * 订阅主题
     * @param Pool $pool
     * @param int $workerId
     * @param array $topic
     * @return void
     * @throws \iflow\Container\implement\generate\exceptions\InvokeClassException
     */
    protected function subscribe(Pool $pool, int $workerId, array $topic): void {
        $subscribeType = $topic['subscribeType'] ?? 'subscribe';
        $this->topic[$this->getTopicKey($topic['name'])] = [
            'topic' => $topic['name'],
            'callable' => $topic['callable'],
            'subscribeType' => $subscribeType,
            'workerId' => $workerId,
            'pool' => $pool,
            'source' => $topic
        ];

        $callable = $topic['callable'];
        if (is_array($callable)) {
            $callable[0] = is_object($callable[0]) ? $callable[0] : Container::getInstance() -> make($callable[0]);
        }

        call_user_func([ $this->redis, $subscribeType ], $topic['name'], $callable);
    }

    /**
     * 取消订阅
     * @param string|array $topicKey
     * @return bool
     */
    public function unSubscribe(string|array $topicKey): bool {
        $topicKey = $this->getTopicKey($topicKey);
        if (!array_key_exists($topicKey, $this->topic)) {
            return false;
        }

        $topic = $this->topic[$topicKey];
        $unType = str_starts_with($topic['subscribeType'], 'p') ? 'punsubscribe' : 'unsubscribe';

        call_user_func([ $this->redis, $unType], $topic['topic']);
        return $topic['pool'] -> shutdown();
    }

    /**
     * 获取订阅基础信息
     * @param string|array $topicKey
     * @return array
     */
    public function getTopic(string|array $topicKey): array {
        return $this->topic[$this->getTopicKey($topicKey)] ?? [];
    }

    protected function getTopicKey(string|array $topicKey): string {
        return is_array($topicKey) ? implode('|', $topicKey) : $topicKey;
    }

    /**
     * 启动服务
     * @return void
     */
    public function start(): void {
        $this->manager -> start();
    }
}
