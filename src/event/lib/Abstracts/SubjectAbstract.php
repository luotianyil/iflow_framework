<?php


namespace iflow\event\lib\Abstracts;

use SplObserver;
use SplSubject;

/**
 * 订阅类
 * Class SubjectAbstract
 * @package iflow\event\lib\Abstracts
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

    public function detach(SplObserver $observer): void {
        // TODO: Implement detach() method.
        foreach ($this->_observer as $index => $_observer) {
            if ($_observer === $observer) {
                unset($this->_observer[$index]);
                break;
            }
        }
    }

    // 变更通知
    public function notify(): void {
        // TODO: Implement notify() method.
        array_walk_recursive($this->_observer, fn ($observer) => $observer -> update($this));
    }

    /**
     * 事件触发
     * @return mixed
     */
    abstract public function trigger(): mixed;
}