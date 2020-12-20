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
        $dir = glob(trim($dir, DIRECTORY_SEPARATOR). DIRECTORY_SEPARATOR . '*' . (is_dir($dir) ? '' : $this->ext));
        foreach ($dir as $key) {
            $fileDir = explode(DIRECTORY_SEPARATOR, $key);
            if (is_file($key)) {
                $fileList[str_replace($this->ext, '', $fileDir[count($fileDir) - 1])] = $key;
            } else if (is_dir($key)) {
                $fileList[$fileDir[count($fileDir) - 1]] = $this->loadDirFile($key, $fileList);
            }
        }
        return $fileList;
    }

}