<?php


namespace iflow\template\lib;

class tag extends tags
{

    protected string $content = '';
    protected array $config = [];
    protected array $data = [];
    protected array $literal = [];
    protected string $file;

    protected function funcParser(): string
    {
        $this->literal()
            ->includeParser()
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

        return $this->literalEnd() -> saveStore();
    }

    protected function varParser(): static
    {
        preg_match_all(
            "/(?:\{(\W\w+)\})/i",
            $this->content,
            $tags);
        if (empty($tags[1])) return $this;
        if (is_string($tags[1])) {
            $this->content = str_replace($tags[0], "<?php echo ".$tags[1].";?>", $this->content);
            return $this;
        }
        $templateTags = $this->getTags($tags);

        foreach ($templateTags as $tag) {
            $this->content = str_replace($tag[0], "<?php echo ".$tag[1].";?>", $this->content);
        }
        return $this;
    }

    protected function includeParser(): static
    {
        preg_match_all(
            "/(?:\{include(.*)\})/i",
            $this->content,
            $tags);
        $templateTags = $this->getTags($tags);
        foreach ($templateTags as $tag) {
            $file = trim(str_replace('"', '', $tag[1]));
            $this->content = str_replace($tag[0], file_get_contents($this->config['view_root_path']. $file. '.' . $this->config['view_suffix']), $this->content);
        }
        return $this;
    }

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

    protected function functionParser(): static
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
        return $this;
    }

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

    protected function literal(): static
    {
        preg_match_all(
            "/(\{literal\})([\s\S]*?)(\{\/literal\})/i",
            $this->content,
            $tags);
        $templateTags = $this->getTags($tags);
        foreach ($templateTags as $tag) {
            $this->literal[] = $tag;
        }
        return $this;
    }

    protected function literalEnd(): static {
        preg_match_all(
            '/(<\?php literal\?>)([\s\S]*?)(<\?php echo \/literal;\?>)/i',
            $this->content,
            $tags
        );
        $templateTags = $this->getTags($tags);
        foreach ($templateTags as $key => $tag) {
            $this->content = str_replace($tag[0], $this->literal[$key][2], $this->content);
        }
        return $this;
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
        file_put_contents($store, str_replace("\r\n", '', $this->content));
        return $store;
    }

    protected function getStoreFile(): string {
        return $this->config['store_path'] . DIRECTORY_SEPARATOR . md5_file($this->file). '.php';
    }
}