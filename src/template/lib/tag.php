<?php


namespace iflow\template\lib;

use iflow\log\lib\channels\file;

class tag extends tags
{

    protected string $content = '';
    protected array $config = [];
    protected array $data = [];
    protected string $file;

    protected function funcParser(): string
    {
        $this->content = $this->includeParser();
        $this->content = $this->varParser();
        $this->content = $this->classParser();
        $this->content = $this->functionParser();

        foreach (["/(?:\{(.*)\})(.*)(?:\{)(.*)(?:\})/i", "/(?:\{(.*)\})(.*)|(\r\n)(?:\{)(.*)(?:\})/i"] as $regx) {
            preg_match_all(
                $regx,
                $this->content,
                $tags);

            $templateTags = $this->getTags($tags);

            foreach ($templateTags as $key => $value) {
                $tagInfo = $this->tagBegin($value[1]);
                $tagInfo .= $value[2];
                $tagInfo .= $this->tagEnd($value[3]);
                $this->content = str_replace($value[0], $tagInfo, $this->content);
            }
        }
        return $this->saveStore();
    }

    protected function varParser(): string
    {
        preg_match_all(
            "/(?:\{(\W\w+)\})/i",
            $this->content,
            $tags);
        if (empty($tags[1])) return $this->content;
        if (is_string($tags[1])) return str_replace($tags[0], "<?php echo ".$tags[1].";?>", $this->content);
        $templateTags = $this->getTags($tags);

        foreach ($templateTags as $tag) {
            $this->content = str_replace($tag[0], "<?php echo ".$tag[1].";?>", $this->content);
        }
        return $this->content;
    }

    protected function includeParser(): string
    {
        preg_match_all(
            "/(?:\{include(.*)\})/i",
            $this->content,
            $tags);
        $templateTags = $this->getTags($tags);
        foreach ($templateTags as $tag) {
            $file = trim(str_replace('"', '', $tag[1]));
            $this->content = str_replace($tag[0], file_get_contents($this->config['view_root_path']. $file. ".html"), $this->content);
        }
        return $this->content;
    }

    protected function classParser(): string
    {
        preg_match_all(
            "/(?:\{lib_(.*)\})/i",
            $this->content,
            $tags);
        $templateTags = $this->getTags($tags);
        foreach ($templateTags as $tag) {
            [$tps, $paramName] = $this->TagsType($tag);
            if (array_key_exists($tps[0], $this->config['tags'])) {
                $info = "<?php ";
                $info .= "$$paramName app('{$this->config['tags'][$tps[0]]['class']}') -> handle();";
                $info .= "?>";
                $this->content = str_replace($tag[0], $info, $this->content);
            }
        }
        return $this->content;
    }

    protected function functionParser(): string
    {
        preg_match_all(
            "/(?:\{fuc_(.*)\})/i",
            $this->content,
            $tags);
        $templateTags = $this->getTags($tags);
        foreach ($templateTags as $tag) {
            [$tps, $type] = $this->TagsType($tag);
            $info = "<?php $type {$tps[0]}; ?>";
            $this->content = str_replace($tag[0], $info, $this->content);
        }
        return $this->content;
    }

    protected function TagsType($tag, $type = '') {
        $tps = explode(':', $tag[1]);
        if (count($tps) > 1) {
            $type = $tps[1];
            $type = $type !== 'echo' ? "$$type =" : 'echo';
        }
        return [
            $tps,
            $type
        ];
    }

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

    protected function getStoreFile(): string {
        return $this->config['store_path'] . DIRECTORY_SEPARATOR . md5_file($this->file). '.php';
    }
}