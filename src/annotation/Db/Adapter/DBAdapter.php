<?php

namespace iflow\annotation\Db\Adapter;

use iflow\Container\implement\annotation\exceptions\AttributeTypeException;
use iflow\Container\implement\generate\exceptions\InvokeClassException;
use iflow\Container\implement\generate\exceptions\InvokeFunctionException;

class DBAdapter {

    protected string $adapterNamespace = '\\iflow\\annotation\\Db\\Adapter';


    /**
     * @param string $sqlType
     * @param string $function
     * @param array $options
     * @return void
     * @throws AttributeTypeException
     * @throws InvokeClassException
     * @throws InvokeFunctionException
     */
    public function handle(string $sqlType, string $function, array $options): void {
        // TODO: Implement handle() method.
        $adapter = $this->adapterNamespace . '\\' . ucfirst($sqlType) . '\\' . ucfirst($function);

        app() -> invokeClass($adapter) -> handle($options);
    }
}