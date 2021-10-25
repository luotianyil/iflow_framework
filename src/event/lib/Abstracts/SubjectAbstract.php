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

    public function attach(SplObserver $observer): static
    {
        // TODO: Implement attach() method.
        if (in_array($observer, $this->_observer)) {
            return $this;
        }
        $this->_observer[] = $observer;
        return $this;
    }

    public function detach(SplObserver $observer)
    {
        // TODO: Implement detach() method.
        foreach ($this->_observer as $index => $_observer) {
            if ($_observer === $observer) {
                unset($this->_observer[$index]);
                break;
            }
        }
    }

    // 变更通知
    public function notify()
    {
        // TODO: Implement notify() method.
        foreach ($this->_observer as $observer) {
            $observer -> update($this);
        }
    }

    /**
     * 事件触发
     * @return mixed
     */
    abstract public function trigger(): mixed;
}