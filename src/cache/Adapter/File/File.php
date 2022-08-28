<?php


namespace iflow\cache\Adapter\File;

use iflow\cache\Adapter\AdapterInterface;

class File implements AdapterInterface {

    protected array $config = [];

    public function initializer(array $config): static {
        $this->config = $config;
        return $this;
    }

    protected function getStorePath(string $name): string
    {
        return $this->config['path'] . $name;
    }

    protected function getStoreRoot(): string {
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
    public function set(string $name, array $data): array
    {
        go(function () use ($name, $data) {
            !is_dir($this->config['path']) && mkdir($this->config['path'], 0755, true);
            $file = $this->getStorePath($name);

            $old_data = $this->get($name);
            $fileStream = fopen($file, "w+");
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
     * @return mixed
     */
    public function get(string $name): mixed {

        $path = $this->getStorePath($name);

        if (!file_exists($path) || $this->deleteExpiredFile($path)) return [];

        $file = fopen($path, 'rb');
        $content = '';
        if ($file) {
            try {
                if (flock($file, LOCK_SH)) {
                    clearstatcache(true, $path);
                    $content = fread($file, filesize($path) ?: 1);
                    flock($file, LOCK_UN);
                }
            } finally {
                fclose($file);
            }
        }

        return $content ? unserialize(gzuncompress($content)) : [];
    }

    /**
     * 删除过期文件
     * @param string $file
     * @return bool
     */
    protected function deleteExpiredFile(string $file): bool {
        // 当文件过期
        $expired = filectime($file) > time() + $this->getExpired();
        if ($expired) @unlink($file);

        return $expired;
    }

    /**
     * 验证指定缓存是否存在
     * @param string $name
     * @return bool
     */
    public function has(string $name): bool {
        return file_exists($this->getStorePath($name));
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
            return $now - $lifetime > $item -> getCTime();
        });
        foreach ($files as $file) {
            file_exists($file->getPathname()) && unlink($file->getPathname());
        }
    }
}

