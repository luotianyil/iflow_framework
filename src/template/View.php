<?php


namespace iflow\template;

use iflow\Response;
use iflow\template\Adapter\Regx\RenderView;

class View {

    // 渲染方法
    protected string $rendering;
    protected array $config = [];
    protected string $renderClass = "iflow\\template\\";

    protected template|RenderView $renderObject;

    public function __construct() {
        $this->config = config('template');
        $this->rendering = $this->config['rendering'];

        $this->renderClass .= $this->rendering !== 'regx' ? 'Template' : 'Adapter\\Regx\\RenderView';
        $this->renderObject = new $this -> renderClass($this->config);
    }

    /**
     * 渲染视图文件
     * @param string $template
     * @param array $vars
     * @param array $config
     * @return Response
     * @throws exception\templateViewNotFound
     */
    public function fetch(string $template = '', array $vars = [], array $config = []): Response
    {
        return $this->send($this->renderObject -> fetch($template, $vars, $config));
    }

    /**
     * 渲染视图代码
     * @param string $template
     * @param array $vars
     * @param array $config
     * @return Response
     * @throws \Exception
     */
    public function display(string $template = '', array $vars = [], array $config = []): Response
    {
        return $this->send($this->renderObject -> display($template, $vars, $config));
    }

    /**
     * 返回响应
     * @param string|Response $info
     * @return Response
     */
    protected function send(string|Response $info): Response
    {
        if ($info instanceof Response) return $info;
        return response() -> data($info);
    }


    /**
     * 不编译视图
     * @param string $file
     * @param array $data
     * @return Response
     */
    public function render(string $file, array $data = []): Response
    {
        foreach ($data as $name => $value) {
            $this->renderObject -> assign($name, $value);
        }
        return $this->send(
            $this->renderObject->render($file)
        );
    }
}