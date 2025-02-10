<?php

namespace iflow\swoole\implement\Server\Mqtt\Subscribe;

use iflow\cache\Adapter\Redis\Redis;
use iflow\facade\Cache;
use iflow\swoole\implement\Server\Mqtt\Subscribe\Traits\SubscribeTTLTrait;

class Subscribe {

    use SubscribeTTLTrait;

    public function __construct(protected array $subscribeConfig) {
        $this -> ttlUnSubscribe();
    }

    /**
     * 订阅主题
     * @param string|array $topic
     * @param int|array $fd
     * @return bool|null
     */
    public function subscribe(string|array $topic, int|array $fd): ?bool {
        $topic = is_array($topic) ? $topic : [ $topic ];
        $fd    = is_array($fd) ? $fd : [ $fd ];
        array_map(
            function ($_topic) use ($fd) {
                $this -> getCache() -> sAdd($this->getSubscribePrefix('sub_topic#' . $_topic), ...$fd);
                array_map(fn ($_fd) => $this -> getCache() -> sAdd('sub_topic_fd#' . $_fd, $_topic), $fd);
            },
            $topic
        );

        return true;
    }

    /**
     * 取消订阅主题
     * @param string|array $topic
     * @param int|array $fd
     * @return bool
     */
    public function unSubscribe(string|array $topic, int|array $fd): bool {
        $topic = is_array($topic) ? $topic : [ $topic ];
        $fd    = is_array($fd) ? $fd : [ $fd ];

        array_map(
            function ($_topic) use ($fd) {
                $this->getCache() -> sRem($this->getSubscribePrefix('sub_topic#' . $_topic), ...$fd);
                array_map(fn ($_fd) => $this -> getCache() -> sRem('sub_topic_fd#' . $_fd, $_topic), $fd);
            },
            $topic
        );

        return true;
    }

    /**
     * 获取指定主题下所有连接信息
     * @param string $topic
     * @return array
     * @throws \RedisException
     */
    public function getSubscribeTopicAllFd(string $topic): array {
        $res = $this->getCache() -> sMembers($this->getSubscribePrefix('sub_topic#' . $topic));
        return $res ?: [];
    }

    /**
     * 设置连接信息
     * @param int $fd
     * @param array $clientInfo
     * @return bool
     * @throws \RedisException
     */
    public function setClientInfoByFd(int $fd, array $clientInfo): ?bool {
        $this->getCache() -> hSet($this->getSubscribePrefix('client-info@uid'), $clientInfo['username'], $fd);
        return $this->getCache()
            -> hSet($this->getSubscribePrefix('client-info@fd'), $fd, serialize($clientInfo));
    }

    /**
     * 通过 FD 获取 CLIENT-INFO
     * @param int|array $fd
     * @return array
     * @throws \RedisException
     */
    public function getClientInfoByFd(int|array $fd): array {
        return array_filter(
            array_map(
                fn($fd) => @unserialize(
                    $this->getCache() -> hGet($this->getSubscribePrefix('client-info@fd'), $fd) ?: ''
                ),
                is_array($fd) ? $fd : [ $fd ]
            ),
            fn ($clientInfo) => !empty($clientInfo)
        );
    }

    /**
     * 通过用户名获取连接标识
     * @param string $username
     * @return int
     * @throws \RedisException
     */
    public function getFdByUsername(string $username): int {
        return $this->getCache() -> hGet($this->getSubscribePrefix('client-info@uid'), $username) ?: 0;
    }

    /**
     * 清空指定连接订阅信息
     * @param int|array $fd
     * @return void
     * @throws \RedisException
     */
    public function clearConnectByFd(int|array $fd): void {
        array_map(function ($fd) {
            $topic      = $this->getCache() -> sMembers('sub_topic_fd#' . $fd);
            $clientInfo = $this -> getClientInfoByFd($fd)[0] ?? [];
            if (!empty($topic)) {
                array_map(
                    fn ($_topic) => $this->getCache() -> sRem($this->getSubscribePrefix('sub_topic#' . $_topic), $fd),
                    $topic
                );
                // 移除已订阅主题
                $this->getCache() -> sRem('sub_topic_fd#' . $fd, ...$topic);
            }

            // 移除连接信息
            $this->getCache() -> hDel($this->getSubscribePrefix('client-info@fd'), $fd);
            $this->getCache() -> hDel($this->getSubscribePrefix('client-info@uid'), $clientInfo['username'] ?? '');
        }, is_array($fd) ? $fd : [ $fd ]);
    }

    protected function getSubscribeCacheKey(): string {
        return $this->subscribeConfig['cache'];
    }

    protected function getSubscribePrefix(string $key): string {
        return $this -> subscribeConfig['prefix'] . '#' . $key;
    }

    protected function getCache(): Redis {
        return Cache::store($this->getSubscribeCacheKey());
    }
}