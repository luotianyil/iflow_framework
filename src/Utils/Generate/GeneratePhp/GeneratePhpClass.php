<?php

namespace iflow\Utils\Generate\GeneratePhp;

use iflow\Container\implement\generate\exceptions\InvokeClassException;
use iflow\Utils\Generate\GeneratePhp\traits\{
    GeneratePhpClassParameterTrait, GeneratePhpMethodTrait, GeneratePhpReplaceTrait
};

class GeneratePhpClass {

    use GeneratePhpClassParameterTrait, GeneratePhpMethodTrait, GeneratePhpReplaceTrait;

    protected string $phpCode = '';

    protected string $phpTemplateCode;


    protected array $replaceTemplateHeap = [
        'replaceNamespace',
        'replaceClass',
        'replaceExtend',
        'replaceImplement',
        'replaceTrait',
        'replaceMethod',
    ];

    public function __construct(
        // 命名空间
        protected string $namespace = '',
        // 类名
        protected string $className = '',
        // 继承类
        protected string $extend = '',
        // 实现接口
        protected array $implements = [],
        // 多继承
        protected array $traits = [],
        // 构造函数参数
        protected array $args = [],
        // 需要生成的方法
        protected array $methods = [],
        // 生成方法回调
        protected ?\Closure $method = null,
        // 生成类存储至指定文件夹
        protected string $saveToFolder = '',
        // 是否排除实现接口父类方法
        protected bool $filterImplementParentMethod = true
    ) {}

    /**
     * 获取 生成的 CLASS 代码
     * @return string
     * @throws GeneratePhpClassException
     */
    public function getClassCode(): string {

        if ($this->phpCode) return $this->phpCode;

        $this->phpTemplateCode = $this->readPhpTemplate();
        $this->phpCode = $this->phpTemplateCode;

        foreach ($this->replaceTemplateHeap as $replaceMethod) {
            $this->phpCode = call_user_func([ $this, $replaceMethod ], $this->phpCode);
        }

        return $this->phpCode;
    }

    /**
     * 保存至文件
     * @return bool|string
     * @throws GeneratePhpClassException
     */
    public function saveToFile(): bool|string {

        if (!is_dir($this->saveToFolder)) return false;

        $phpCode = $this->getClassCode();
        $fileName = $this->getClassName();

        $saveToFile = "{$this -> saveToFolder}/{$fileName}.php";

        file_put_contents($saveToFile, $phpCode);

        return $saveToFile;
    }

    /**
     * 引入生成对象
     * @return object
     * @throws GeneratePhpClassException
     * @throws InvokeClassException
     */
    public function import(): object {

        if (($classPath = $this->saveToFile()) === false) {
            throw new GeneratePhpClassException($classPath);
        }

        if (class_exists($this->getNamespaceClass())) {
            app() -> delete($this->getNamespaceClass());
        }

        include $classPath;

        return app($this->getNamespaceClass(), $this->getArgs());
    }


    public function getNamespaceClass(): string {
        return $this->getNamespace(). '\\' . $this -> getClassName();
    }
}