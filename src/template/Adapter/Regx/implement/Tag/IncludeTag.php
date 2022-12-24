<?php

namespace iflow\template\Adapter\Regx\implement\Tag;

use iflow\Container\Container;
use iflow\template\Adapter\Regx\implement\Abstracts\TagAbstract;
use iflow\template\Adapter\Regx\Interfaces\TagInterface;
use iflow\template\Adapter\Regx\RegxInterpreter\RegxInterpreter;
use iflow\template\exception\templateViewNotFound;

class IncludeTag extends TagAbstract {

    protected string $readFile = '';

    /**
     * 读取文件
     * @param string $tag
     * @param string $html
     * @param array $args
     * @return TagInterface
     * @throws templateViewNotFound
     */
    public function parser(string $tag, string $html, array $args): TagInterface {
        parent::parser($tag, $html, $args); // TODO: Change the autogenerated stub

        $this->readFile = $this->config -> getViewRootPath()
            . ($this->attrs['file'] ?? '')
            . '.'. $this->config -> getViewSuffix();

        $this->readImportTemplate();
        return $this;
    }

    /**
     * 加载引入模板文件
     * @return void
     * @throws templateViewNotFound
     * @throws \Exception
     */
    protected function readImportTemplate() {

        if (!$this->readFile || !file_exists($this->readFile))
            throw new templateViewNotFound("ReadTemplateFile Fails TemplatePath: {$this -> readFile}");

        $content = file_get_contents($this->readFile);

        if (!$this->isCloseLabel)
            $content = str_replace('{slot /}', $this->source, $content);

        $this->html = Container::getInstance() -> make(RegxInterpreter::class, [
            $content, $this->config ], true) -> getPhpTemplateCode();
    }


}