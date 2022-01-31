<?php


namespace iflow\template\lib;

use iflow\exception\lib\HttpException;
use iflow\exception\lib\HttpResponseException;
use iflow\Response;

class Parser extends tag implements TemplateParser
{

    public function __construct(array $config = [])
    {
        $this->config = $config;
    }

    public function config(array $config = [])
    {
        // TODO: Implement config() method.
        $this->config = array_merge($this->config, $config);
    }

    /**
     * 验证模板文件是否存在
     * @return bool
     */
    public function exists(): bool
    {
        // TODO: Implement exists() method.
        return file_exists($this->file);
    }

    /**
     * 渲染视图代码
     * @param string $template
     * @param array $data
     * @param array $config
     * @return Response
     */
    public function display(string $template, array $data = [], array $config = []): Response
    {
        // TODO: Implement display() method.
        $this->content = $template;
        $this->data = array_merge($this->data, $data);
        $this->config($config);
        return $this->render($this->templateParser());
    }

    /**
     * 渲染视图文件
     * @param string $template
     * @param array $data
     * @param array $config
     * @return Response
     */
    public function fetch(string $template, array $data = [], array $config = []): Response
    {
        // TODO: Implement fetch() method.
        $this->data = array_merge($this->data, $data);
        $this->config($config);

        $view_suffix = $this->config['view_suffix'] === '' ? '' : ".{$this->config['view_suffix']}";
        $this->file = $this->config['view_root_path'] . $template . $view_suffix;

        if ($this->exists()) {
            // 验证缓存文件是否有效
            $storeFile = $this->getStoreFile();
            if (file_exists($storeFile)) {
                if ($this->config['cache_enable']
                    && (
                        0 === $this->config['cache_time']
                        || fileatime($this->file) + $this->config['cache_time'] > time()
                    )
                ) {
                    return $this->render($storeFile);
                }
            }

            // 重新编译视图
            $this->content = file_get_contents($this->file);
            return $this->render($this->templateParser());
        } else {
            throw new HttpException(404, 'template file not exists');
        }
    }

    /**
     * 返回响应
     * @param string $filePath
     * @return Response
     */
    public function render(string $filePath = ''): Response
    {
        ob_start();
        extract($this->data, EXTR_OVERWRITE);
        include $filePath;
        $info = ob_get_contents();
        ob_end_clean();
        return response() -> data($info);
    }

    /**
     * 设置模板变量
     * @param string $name
     * @param array $data
     * @return static
     */
    public function assign(string $name, mixed $data): static
    {
        $this->data[$name] = $data;
        return $this;
    }

    /**
     * 获取当前模板代码
     * @return string
     */
    public function getContent(): string
    {
        $this->funcParser();
        return $this->content;
    }

    /**
     * 视图模板渲染
     * @return string
     */
    protected function templateParser(): string
    {
        if ($this->content === '') {
            throw new HttpResponseException(
                message() -> nodata('Template Content is Empty')
            );
        }

        if ($this->FileIsTemplateLibrary()) {
            throw new HttpResponseException(
                message() -> nodata('TemplateFile is templateLibrary')
            );
        }
        return $this->funcParser();
    }
}