<?php

namespace iflow\initializer;

use Generator;
use iflow\App;

class Helpers {

    /**
     * @var Generator[]
     */
    protected array $helpers = [];

    /**
     * 项目助手函数目录
     * @var string
     */
    protected string $frameworkHelperFolder = '';

    /**
     * 应用助手函数目录
     * @var string
     */
    protected string $applicationHelperFolder = '';

    /**
     * 应用 公共助手函数
     * @var string
     */
    protected string $applicationCommunityHelper = '';

    public function initializer(App $app): void {
        $this -> applicationHelperFolder = config('app@application_helper_folder', $app->getAppPath() . 'helpers');
        $this -> frameworkHelperFolder = $app->getFrameWorkPath() . 'helpers';
        $this->applicationCommunityHelper = $app->getAppPath() . 'common.php';
        $this->loadFrameworkHelper() -> loadApplicationHelper() -> includeHelperFile();
    }

    /**
     * 加载框架内置助手函数
     * @return $this
     */
    protected function loadFrameworkHelper(): Helpers {
        $this->helpers[] = find_files($this->frameworkHelperFolder,
            fn (\SplFileInfo $splFileInfo) => $splFileInfo -> getExtension() === 'php'
        );
        return $this;
    }

    /**
     * 加载应用内置助手函数
     * @return $this
     */
    protected function loadApplicationHelper(): Helpers {
        // 加载全局助手函数
        if (is_file($this->applicationCommunityHelper)) {
            include_once $this->applicationCommunityHelper;
        }

        // 加载应用目录类下 助手函数 文件
        if (is_dir($this->applicationHelperFolder)) {
            $this->helpers[] = find_files($this->applicationHelperFolder,
                fn (\SplFileInfo $splFileInfo) => $splFileInfo -> getExtension() === 'php'
            );
        }

        return $this;
    }

    protected function includeHelperFile(): void {
        array_map(function ($generators) {
            foreach ($generators as $file) {
                if ($file -> getExtension() === 'php') include_once $file -> getPathname();
            }
        }, $this->helpers);
    }
}