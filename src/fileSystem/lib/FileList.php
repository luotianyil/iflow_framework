<?php


namespace iflow\fileSystem\lib;


class FileList
{

    public string $ext = '';

    /**
     * 获取文件夹文件
     * @param string $dir
     * @param string $ext
     * @param bool $traverse
     * @return array
     */
    public function loadFileList(string $dir, string $ext = '', bool $traverse = false) : array
    {
        $this->ext = $ext?:$this->ext;
        if (!$traverse) {
            return glob($dir . '*' . $this->ext);
        }
        return match (is_dir($dir)) {
            true => $this->loadDirFile($dir),
            false => glob($dir . '*' . $this->ext),
            default => []
        };
    }

    /**
     * 遍历文件夹
     * @param string $dir 文件地址
     * @param array $fileList | 文件列表
     * @return array
     */
    public function loadDirFile(string $dir, array $fileList = []) : array
    {
        $iterator = new \FilesystemIterator($dir. DIRECTORY_SEPARATOR);
        foreach ($iterator as $file) {
            if (is_dir($file -> getPathname())) {
                $fileList[$file -> getBasename()] = $this->loadDirFile($file -> getPathname());
            } else {
                $fileList[
                str_replace($this->ext, '', $file -> getBasename())
                ] = $file -> getPathname();
            }
        }
        return $fileList;
    }

    /**
     * 获取当前目录下所有文件夹
     * @param string $dir
     * @return array
     */
    public function loadDir(string $dir): array
    {
        // 获取目录
        $iterator = new \FilesystemIterator(trim($dir, DIRECTORY_SEPARATOR). DIRECTORY_SEPARATOR);
        $fileList = [];
        foreach ($iterator as $file) {
            if (is_dir($file -> getPathname())) {
                $fileList[$file -> getBasename()] = [
                    'root' => $file -> getPathname(),
                    'children' => $this->loadDir($file -> getPathname())
                ];
            }
        }
        return $fileList;
    }

    /**
     * 删除目录
     * @param string $dir 需要删除的目录
     * @param array $ignore 忽略的目录
     * @return array
     */
    public function removeDir(string $dir, array $ignore = []): array
    {
        $iterator = new \FilesystemIterator(trim($dir, DIRECTORY_SEPARATOR). DIRECTORY_SEPARATOR);
        $count = 0;
        $ignoreCount = 0;
        foreach ($iterator as $file) {
            $path = $file -> getPathname();
            if (is_dir($path)) {
                if (!in_array($path, $ignore)) {
                    $remove = $this -> removeDir($path, $ignore);
                    $ignoreCount += $remove['ignoreCount'];
                    $count += $remove['dirCount'];
                    // 检测文件夹是否为空
                    if (!(new \FilesystemIterator($path)) -> valid()) {
                        rmdir($path);
                    }
                } else {
                    $ignoreCount += 1;
                }
            } else {
                unlink($file -> getPathname());
            }
            $count += 1;
        }
        return [
            'dirCount' => $count,
            'ignoreCount' => $ignoreCount
        ];
    }

}