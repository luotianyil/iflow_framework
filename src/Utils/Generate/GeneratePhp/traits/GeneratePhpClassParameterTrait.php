<?php

namespace iflow\Utils\Generate\GeneratePhp\traits;

trait GeneratePhpClassParameterTrait {

    protected string $phpTemplateDefaultPath = __DIR__;

    /**
     * @return string
     */
    public function getNamespace(): string {

        if ($this->namespace) return $this->namespace;

        return $this->namespace = 'GeneratePhp\\_Class\\'.$this->getClassName();
    }

    /**
     * @param string $namespace
     */
    public function setNamespace(string $namespace): void {
        $this->namespace = $namespace;
    }

    /**
     * @return string
     */
    public function getClassName(): string {

        if ($this -> className) return $this -> className;

        return $this->className = uniqid('Generate_');
    }

    /**
     * @param string $className
     */
    public function setClassName(string $className): void
    {
        $this->className = $className;
    }

    /**
     * @return string
     */
    public function getExtend(): string {
        return str_starts_with($this->extend, '\\') ? $this->extend : '\\'.$this->extend;
    }

    /**
     * @param string $extend
     */
    public function setExtend(string $extend): void
    {
        $this->extend = $extend;
    }

    /**
     * @return array
     */
    public function getImplements(): array {
        return array_map(function (string $implement) {
            return str_starts_with($implement, '\\') ? $implement : '\\'.$implement;
        }, $this->implements);
    }

    /**
     * @param array $implements
     */
    public function setImplements(array $implements): void
    {
        $this->implements = $implements;
    }

    /**
     * @return array
     */
    public function getArgs(): array
    {
        return $this->args;
    }

    /**
     * @param array $args
     */
    public function setArgs(array $args): void
    {
        $this->args = $args;
    }

    /**
     * @return string
     */
    public function getSaveToFolder(): string
    {
        return $this->saveToFolder;
    }

    /**
     * @param string $saveToFolder
     */
    public function setSaveToFolder(string $saveToFolder): void
    {
        $this->saveToFolder = $saveToFolder;
    }

}
