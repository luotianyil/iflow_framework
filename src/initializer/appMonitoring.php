<?php


namespace iflow\initializer;


use iflow\App;

class appMonitoring
{
    protected App $app;

    // 类全部注解
    protected array $annotations = [];
    protected array $config = [];

    // 实例化 入口类 全部注解
    public function initializer(App $app)
    {
        $this->app = $app;
        $this->config = config('app@appMonitoring');
        $this->appMonitoring();
    }

    protected function appMonitoring(): bool {
        if (!$this->config['enable']) return false;
        \Co\run(function () {
            \Co::sleep(floatval(bcdiv("{$this -> config['delayTime']}", "1000")));
            $this->Monitoring();
        });
        return true;
    }


    public function Monitoring() {
        $this->config = config('app@appMonitoring');
        $units = ['B', 'kB', 'MB'];
        $systemInfo = systemInfo();
        $MemFree = explode(' ', $systemInfo['mem']['MemFree'])[0] / 1024 / 1024;
        $diskFree = explode(' ', $systemInfo['disk']['free']);

        $content = "";
        if (in_array($diskFree[1], $units)) {
            $content .= "<p>硬盘储存已低于  {$this -> config['diskFree']} GB / 现有 {$systemInfo['disk']['free']} </p>";
        }

        if ($diskFree[1] === "GB" && $diskFree[0] < 1.01) {
            $content .= "<p>硬盘储存已低于 {$this -> config['diskFree']} GB / 现有 {$systemInfo['disk']['free']}</p>";
        }

        if ($MemFree < 1.01) {
            $content .= "<p>内存已低于 {$this -> config['MemFree']} GB / 现有 {$systemInfo['mem']['MemFree']}</p>";
        }

        if ($content !== '') {
            $content .= "<p>DateTime: ". date('Y-m-d H:i:s') ."</p>";
            \Co\run(function () use ($content) {
                emails($this->config['toEmails'], $content, subject: config('app@appName') . ' - 应用预警');
            });
        }
    }
}