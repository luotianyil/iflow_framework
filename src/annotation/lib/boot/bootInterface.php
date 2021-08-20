<?php


namespace iflow\annotation\lib\boot;


interface bootInterface
{
    /**
     * 启动引导方法入口
     * @return bootInterface
     */
    public function boot(): bootInterface;
}