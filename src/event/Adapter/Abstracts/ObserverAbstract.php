<?php


namespace iflow\event\Adapter\Abstracts;


use SplObserver;
use SplSubject;

/**
 * 观察类
 * Class ObserverAbstract
 * @package iflow\event\implement\Abstracts
 */
abstract class ObserverAbstract implements SplObserver {

    /**
     * 执行变更方法
     * @param SplSubject $subject
     */
    public function update(SplSubject $subject): void {}

}