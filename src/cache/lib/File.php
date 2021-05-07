<?php


namespace iflow\cache\lib;


class File
{

    protected array $config = [];

    public function initializer(array $config): self
    {
        $this->config = $config;
        return $this;
    }

    protected function getStorePath(string $name)
    {
        return $this->config['path'] . $name;
    }

    protected function getStoreRoot() {
        return rtrim($this->config['path'], DIRECTORY_SEPARATOR);
    }

    protected function getExpired()
    {
        return $this->config['expired'];
    }

    /**
     * 设置/更新 缓存
     * @param string $name
     * @param array $data
     * @return array
     */
    public function set(string $name, array $data)
    {
        go(function () use ($name, $data) {
            !is_dir($this->config['path']) && mkdir($this->config['path'], 0755, true);
            $file = $this->getStorePath($name);
            $fileStream = fopen($file, "w+");
            $old_data = $this->get($name);
            flock($fileStream, LOCK_EX);
            $data['iflow_expired'] = strtotime('+'. $this->getExpired() . ' second');

            $data = $old_data ? array_replace_recursive($old_data, $data) : $data;
            $data = serialize($data);

            fwrite($fileStream, gzcompress($data));

            flock($fileStream, LOCK_UN);
            fclose($fileStream);
        });
        return $data;
    }

    /**
     * 获取缓存
     * @param string $name
     * @return array|mixed
     */
    public function get(string $name)
    {
        $file = $this->getStorePath($name);
        if (file_exists($file)) {
            $data = file_get_contents($file);
            if ($data !== '') {
                $data = unserialize(gzuncompress($data));
                return $data;
            }
        }
        return [];
    }

    /**
     * 删除缓存
     * @param string $name
     * @return bool
     */
    public function delete(string $name): bool
    {
        $file = $this->getStorePath($name);
        return file_exists($file) && unlink($this->getStorePath($name));
    }

    /**
     * 过期缓存回收
     * @param $lifetime
     */
    public function gc($lifetime) {
        $now = time();
        $files = find_files($this->getStoreRoot(), function (\SplFileInfo $item) use ($lifetime, $now) {
            return $now - $lifetime > $item -> getMTime();
        });
        foreach ($files as $file) {
            file_exists($file->getPathname()) && unlink($file->getPathname());
        }
    }
}