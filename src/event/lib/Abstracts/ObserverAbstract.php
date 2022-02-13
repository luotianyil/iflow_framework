<?php


namespace iflow\event\lib\Abstracts;


use SplSubject;

/**
 * 观察类
 * Class ObserverAbstract
 * @package iflow\event\lib\Abstracts
 */
abstract class ObserverAbstract implements \SplObserver {

    /**
     * 执行变更方法
     * @param SplSubject $subject
     */
    public function update(SplSubject $subject): void {}

}