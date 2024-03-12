<?php


namespace iflow\event\Adapter\Abstracts;

use SplObserver;
use SplSubject;

/**
 * 订阅类
 * Class SubjectAbstract
 * @package iflow\event\implement\Abstracts
 */
abstract class SubjectAbstract implements SplSubject {

    /**
     * @var SplObserver[]
     */
    protected array $_observer = [];

    public function attach(SplObserver $observer): void {
        // TODO: Implement attach() method.
        if (!in_array($observer, $this->_observer)) {
            $this->_observer[] = $observer;
        }
    }

    /**
     * 删除订阅
     * @param SplObserver $observer
     * @return void
     */
    public function detach(SplObserver $observer): void {
        // TODO: Implement detach() method.
        $key = array_search($observer, $this->_observer);
        if ($key) unset($this->_observer[$key]);
    }

    /**
     * 变更通知
     * @return void
     */
    public function notify(): void {
        // TODO: Implement notify() method.
        array_walk_recursive($this->_observer, fn (SplObserver $observer) => $observer -> update($this));
    }

    /**
     * 事件触发
     * @return mixed
     */
    abstract public function trigger(): mixed;

}
