<?php

namespace iflow\response\Adapter;

use iflow\Container\implement\generate\exceptions\InvokeClassException;
use iflow\Response;

class StreamedResponse extends Response {

    public string $contentType = 'text/event-stream';

    public string $charSet = '';

    public array $headers = [
        'Cache-Control' => 'no-cache',
        'Connection'    => 'keep-alive'
    ];

    protected bool $finish_close = true;

    public string $finish_mark = '[DONE]';

    public int $retry;

    public string $event;

    /**
     * @param int $code
     * @param array $options [ 'retry' => 10000, 'event' => 'message' ]
     * @throws InvokeClassException
     */
    public function __construct(int $code = 200, array $options = []) {
        $this->init('', $code);

        $this -> setResponseHeader()
            -> setRetry($options['retry'] ?? 10000)
            -> setEventName($options['event'] ?? 'message');
    }

    /**
     * @param int $retry
     * @return $this
     */
    public function setRetry(int $retry): StreamedResponse {
        $this->retry = $retry;
        return $this;
    }

    /**
     * @param string $event
     * @return $this
     */
    public function setEventName(string $event): StreamedResponse {
        $this->event = $event;
        return $this;
    }

    /**
     * 结束后是否关闭链接
     * @param bool $finish_close
     * @return StreamedResponse
     */
    public function setFinishClose(bool $finish_close): StreamedResponse {
        $this->finish_close = $finish_close;
        return $this;
    }

    /**
     * 返回响应数据
     * @param mixed $content
     * @return bool
     */
    public function write(mixed $content): bool {
        $id = !is_array($content) || empty($content['$_id']) ? uniqid() : $content['$_id'];

        $content = (!is_array($content) ? $content : json_encode($content, JSON_UNESCAPED_UNICODE));

        if (method_exists($this->response, 'serverSentEvents')) {
            return $this->response -> serverSentEvents(
                [
                    'event' => $this->event,
                    'retry' => $this->retry,
                    'data' => strval($content),
                    'id' => $id
                ],
                $this->contentType
            );
        }
        return $this -> response -> write(
            "event: {$this->event}\n".
            "retry: {$this->retry}\n".
            "id: ". $id . "\n".
            "data: ". $content
            . "\n\n"
        );
    }

    /**
     * 结束请求
     * @param mixed|null $data
     * @return bool
     */
    public function send(mixed $data = ''): bool {
        $end = $this -> write($this->finish_mark);

        if ($this -> finish_close) $this -> end('');

        // 结束请求
        event('RequestEndEvent', $this -> startTime);
        return $end;
    }

}
