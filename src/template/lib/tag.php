<?php


namespace iflow\template\lib;


use iflow\template\Template;

class tag extends tags
{

    protected string $content = '';
    protected array $config = [];
    protected array $data = [];
    protected string $file;

    public function funcParser()
    {
        $this->content = $this->includeParser();
        $this->content = $this->varParser();
        $this->content = $this->classParser();

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

    public function varParser()
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

    public function includeParser()
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

    public function classParser()
    {
        preg_match_all(
            "/(?:\{lib_(.*)\})/i",
            $this->content,
            $tags);
        $templateTags = $this->getTags($tags);
        foreach ($templateTags as $tag) {
            $tps = explode(':', $tag[1]);
            $paramName = count($tps) > 1 ? $tps[1] : $tps[0];
            if (array_key_exists($tps[0], $this->config['tags'])) {
                $info = "<?php ";
                $info .= "$$paramName = app('{$this->config['tags'][$tps[0]]['class']}') -> handle();";
                $info .= "?>";
                $this->content = str_replace($tag[0], $info, $this->content);
            }
        }
        return $this->content;
    }

    protected function getTags($tags = []) {
        $templateTags = [];
        foreach ($tags as $key => $value) {
            foreach ($value as $k => $tag) {
                $templateTags[$k][] = $tag;
            }
        }
        return $templateTags;
    }

    protected function tagBegin($tag)
    {
        if ($tag === '') return '';
        return "<?php ".ltrim($tag, '/')."?>";
    }

    protected function tagEnd($tag)
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