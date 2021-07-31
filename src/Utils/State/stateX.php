<?php


namespace iflow\Utils\State;


use iflow\Utils\State\lib\dataProxy;

class stateX extends dataProxy
{

    /**
     * @param string $getActionName Get方法名称
     * @param \Closure $getAction Get 闭包函数
     * @return stateX
     */
    public function setGetAction(string $getActionName, \Closure $getAction): static
    {
        $this->getAction[$getActionName] = $getAction;
        return $this;
    }

    /**
     * @param string $setActionName
     * @param \Closure $setAction
     * @return stateX
     */
    public function setSetAction(string $setActionName, \Closure $setAction): static
    {
        $this->setAction[$setActionName] = $setAction;
        return $this;
    }

    /**
     * 设置数据
     * @param string $key
     * @param mixed $data
     * @return $this
     */
    public function emit(string $key, mixed $data): static
    {
        $this -> offsetSet($key, $data);
        return $this;
    }

    /**
     * 执行获取闭包方法
     * @param string $getterName
     * @return mixed
     */
    public function callGetterAction(string $getterName): mixed
    {
        return $this->callAction($getterName);
    }

    /**
     * 执行设置闭包方法
     * @param string $setterName 方法名称
     * @return mixed
     */
    public function callSetterAction(string $setterName): mixed
    {
        return $this->callAction($setterName, 'setAction');
    }

    /**
     * 执行回调闭包方法
     * @param string $callBackName
     * @return mixed
     */
    public function callCallBackAction(string $callBackName): mixed
    {
        return $this->callAction($callBackName, 'callBackAction');
    }

}