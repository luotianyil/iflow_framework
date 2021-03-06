<?php


namespace iflow\template\lib;

use iflow\Utils\basicTools;

class tag extends tags
{

    protected string $content = '';
    protected array $config = [];
    protected array $data = [];
    protected array $literal = [];
    protected string $file;

    protected function funcParser(bool $save = true): string
    {
        $this->literal()
            -> includeParser()
            -> varParser()
            -> classParser()
            -> functionParser();

        preg_match_all(
            "/(?:\{(.*)\})([\s\S]*?)(?:\{)(.*)(?:\})/i",
            $this->content,
            $tags);

        $templateTags = $this->getTags($tags);

        foreach ($templateTags as $key => $value) {
            $tagInfo = $this->tagBegin($value[1]);
            $tagInfo .= $value[2];
            $tagInfo .= $this->tagEnd($value[3]);
            $this->content = str_replace($value[0], $tagInfo, $this->content);
        }

        $this->content = str_replace('{', "<?php ", $this->content);
        $this->content = str_replace('}', ";?>", $this->content);

        return $save ? $this->literalEnd() -> saveStore() : $this -> literalEnd() -> content;
    }

    /**
     * 验证变量
     * @return $this
     */
    protected function varParser(): static
    {
        preg_match_all(
            "/(?:\{v_(.*?)\})/i",
            $this->content,
            $tags);
        if (empty($tags[1])) return $this;
        if (is_string($tags[1])) {
            $this->content = str_replace($tags[0], "<?php echo $".ltrim($tags[1], '$').";?>", $this->content);
            return $this;
        }
        $templateTags = $this->getTags($tags);

        foreach ($templateTags as $tag) {
            $this->content = str_replace($tag[0], "<?php echo $".ltrim($tag[1], '$').";?>", $this->content);
        }
        return $this;
    }

    /**
     * 引入模板库
     * @return $this
     */
    protected function includeParser(): static
    {
        preg_match_all(
            "/(?:\{include(.*)\})/i",
            $this->content,
            $tags);
        $templateTags = $this->getTags($tags);
        foreach ($templateTags as $tag) {
            $file = trim(str_replace('"', '', $tag[1]));

            // 解析导入的文件
            $tagInfo = clone $this;
            $tagInfo -> content = file_get_contents($this->config['view_root_path']. $file. '.' . $this->config['view_suffix']);
            $tagInfo -> FileIsTemplateLibrary(true);
            $this->content = str_replace($tag[0], $tagInfo -> funcParser(false), $this->content);
        }
        return $this;
    }

    /**
     * 检测是否执行PHP类
     * @return $this
     */
    protected function classParser(): static
    {
        preg_match_all(
            "/(?:\{lib_(.*)\})/i",
            $this->content,
            $tags);
        $templateTags = $this->getTags($tags);
        foreach ($templateTags as $tag) {
            [$tps, $paramName, $params, $func] = $this->TagsType($tag);
            if (array_key_exists($tps[0], $this->config['tags'])) {
                $info = "<?php ";
                $info .= "$paramName app('{$this->config['tags'][$tps[0]]['class']}') -> $func($params);";
                $info .= "?>";
                $this->content = str_replace($tag[0], $info, $this->content);
            }
        }
        return $this;
    }

    /**
     * 检测是否执行方法
     * @return $this
     */
    protected function functionParser(): static
    {
        preg_match_all(
            "/(?:\{fuc_(.*)\})/i",
            $this->content,
            $tags);
        $templateTags = $this->getTags($tags);
        foreach ($templateTags as $tag) {
            $index = strrpos($tag[1], ":") + 1;

            // 获取执行方法
            $tps = substr($tag[1], 0, $index - 1);

            // 获取输出类型
            $type = substr($tag[1], $index);
            $type = $type !== 'echo' ? "$$type =" : 'echo';
            $info = "<?php $type {$tps}; ?>";
            $this->content = str_replace($tag[0], $info, $this->content);
        }
        return $this;
    }

    /**
     * 处理 执行方法/类 标签格式
     * @param $tag
     * @param string $type
     * @return array
     */
    protected function TagsType($tag, $type = ''): array
    {
        $tps = explode(':', $tag[1]);
        $param = "";
        if (count($tps) > 1) {
            $type = $tps[1];
            $type = $type !== 'echo' ? "$$type =" : 'echo';
            if (count($tps) > 2) {
                $params = explode('&', $tps[2]);
                foreach ($params as $paramName) {
                    $param .= "$paramName,";
                }
            }
        }
        return [
            $tps,
            $type,
            trim($param, ','),
            $tps[3] ?? 'handle'
        ];
    }

    /**
     * 原样输出开始处理
     * @return $this
     */
    protected function literal(): static
    {
        preg_match_all(
            "/(\{literal\})([\s\S]*?)(\{\/literal\})/i",
            $this->content,
            $tags);
        $templateTags = $this->getTags($tags);
        $utils = new basicTools();
        foreach ($templateTags as $tag) {
            $uuid = uniqid('iflowTemplate_'). $utils -> create_uuid();
            $this->content = str_replace($tag[0], $uuid, $this->content);
            $this->literal[$uuid] = $tag;
        }
        return $this;
    }

    /**
     * 原样输出结束
     * @return $this
     */
    protected function literalEnd(): static {

        foreach ($this->literal as $key => $value) {
            $this->content = str_replace($key, $this->literal[$key][2], $this->content);
        }
        return $this;
    }

    /**
     * 获取需要处理的TAG 标签
     * @param array $tags
     * @return array
     */
    protected function getTags($tags = []): array {
        $templateTags = [];
        foreach ($tags as $key => $value) {
            foreach ($value as $k => $tag) {
                $templateTags[$k][] = $tag;
            }
        }
        return $templateTags;
    }

    protected function tagBegin($tag): string
    {
        if ($tag === '') return '';
        return "<?php ".ltrim($tag, '/')."?>";
    }

    protected function tagEnd($tag): string
    {
        if ($tag === '') return '';
        return "<?php ".ltrim($tag, '/')."?>";
    }

    private function saveStore(): string {
        $store = $this->getStoreFile();
        !is_dir($this->config['store_path']) && mkdir($this->config['store_path'], 0755, true);
        file_put_contents($store, $this->content);
        return $store;
    }

    /**
     * 设置缓存地址
     * @return string
     */
    protected function getStoreFile(): string {
        return $this->config['store_path'] . DIRECTORY_SEPARATOR . md5($this->file). '.php';
    }

    protected function FileIsTemplateLibrary(bool $splitStart = false): bool
    {
        $content = explode(PHP_EOL, $this->content);
        $templateLibrary = $content[0] === '{templateLibrary}';

        if ($templateLibrary && $splitStart) {
            array_shift($content);
            $this->content = implode(PHP_EOL, $content);
        }
        return $templateLibrary;
    }
}