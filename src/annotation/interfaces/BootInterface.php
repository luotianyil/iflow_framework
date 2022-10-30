<?php


namespace iflow\annotation\interfaces;


interface BootInterface
{
    /**
     * 启动引导方法入口
     * @return BootInterface
     */
    public function boot(): BootInterface;
}