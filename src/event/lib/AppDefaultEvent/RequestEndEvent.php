<?php

namespace iflow\event\lib\AppDefaultEvent;

use iflow\event\lib\Abstracts\SubjectAbstract;

class RequestEndEvent extends SubjectAbstract {

    /**
     * 触发事件回调
     * @return $this
     */
    public function trigger(float $startTime = 0.00): RequestEndEvent {
        // TODO: Implement trigger() method.
        $this->saveRequestLogger($startTime) -> notify();
        return $this;
    }

    /**
     * 保存请求日志
     * @param float $startTime
     * @return $this
     */
    protected function saveRequestLogger(float $startTime): RequestEndEvent {
        if (config('app@saveRuntimeLog')) {
            $requestLogs = [
                'requestTime' => date('Y-m-d H:i:s', intval(request() -> server['request_time_float'])),
                'request_uri' => request() -> server['request_uri'],
                'method' => request() -> server['request_method'],
                'runMemoryUsage' => round(memory_get_usage() / 1024 / 1024, 2) . " M",
                'responseTime' => microtime(true) - $startTime . " s"
            ];

            $logInfo = "";
            foreach ($requestLogs as $key => $value) $logInfo .= $key . ": ". $value . " ";
            logs('info', $logInfo);
        }
        return $this;
    }
}
