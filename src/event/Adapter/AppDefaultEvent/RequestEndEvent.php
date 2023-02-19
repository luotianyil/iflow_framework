<?php

namespace iflow\event\Adapter\AppDefaultEvent;

use iflow\Container\implement\generate\exceptions\InvokeClassException;
use iflow\event\Adapter\Abstracts\SubjectAbstract;

class RequestEndEvent extends SubjectAbstract {

    /**
     * 触发事件回调
     * @return $this
     * @throws InvokeClassException
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
     * @throws InvokeClassException
     */
    protected function saveRequestLogger(float $startTime = 0): RequestEndEvent {
        $startTime = $startTime > 0 ? $startTime : app() -> getStartTimes();
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
