<?php


namespace iflow\annotation\interfaces;


interface bootInterface
{
    /**
     * 启动引导方法入口
     * @return bootInterface
     */
    public function boot(): bootInterface;
}