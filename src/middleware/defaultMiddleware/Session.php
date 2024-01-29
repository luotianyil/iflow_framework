<?php


namespace iflow\middleware\defaultMiddleware;


use iflow\App;
use iflow\event\Adapter\Abstracts\ObserverAbstract;
use iflow\facade\Event;
use iflow\facade\Session as SessionObject;
use SplSubject;

class Session extends ObserverAbstract {

    /**
     * 初始化Session 注册结束事件服务
     * @param App $app
     * @param $next
     * @return mixed
     * @throws \Exception
     */
    public function handle(App $app, $next): mixed {
        SessionObject::initializer();
        Event::getEvent('RequestEndEvent') ?-> attach($this);
        return $next($app);
    }

    /**
     * 会话缓存
     * @param SplSubject $subject
     * @return void
     * @throws \Exception
     */
    public function update(SplSubject $subject): void {
        parent::update($subject); // TODO: Change the autogenerated stub
        SessionObject::save();
    }
}