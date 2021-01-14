<?php


namespace iflow\fileSystem\lib;

class upLoadFile extends fileSystem
{

    protected array $fileList = [];

    protected array $defValidate = [
        'mineType' => [],
        'size' => 2 * 1024 * 1024
    ];

    protected array $error = [];

    public function __construct($filename = '')
    {
        $this->config = config('fileSystem');
        $this->config = $this->config['disks'][$this->config['default']];
        parent::__construct($filename);
    }

    public function setFile($name, $file): static
    {
        $this->fileList[$name][] = new self($file['tmp_name']);
        return $this;
    }

    public function getFileList(): array
    {
        return $this->fileList;
    }

    public function getFile(string $index)
    {
        return $this->fileList[$index];
    }

    public function read(): string
    {
        return file_get_contents($this->getPathname());
    }

    public function move(string $savePath, array $config = [])
    {

        $savePath .= $this->config['rootPath'] . DIRECTORY_SEPARATOR . $savePath;
        $validate = $this->validate($config);
        if ($validate) {
            !is_dir($savePath) && mkdir($savePath, 0755, true);
            $fileNameType = $config['type'] ?? 'hash';
            $fileNameType = is_string($fileNameType) ? [
                $fileNameType
            ] : [
                array_keys($fileNameType),
                $fileNameType['algo'] ?? 'sha1'
            ];
            $fileName = call_user_func([$this, $fileNameType[0]], $fileNameType[1] ?? '');
            move_uploaded_file($this->getPathname(), $savePath.$fileName);
        }
        return $validate;
    }

    protected function validate(array $validate): static
    {
        $validate = array_merge($this->defValidate, $validate);
        if (count($validate['mineType']) > 0) {
            if (!in_array($this->getFileMine(), $validate['mineType'])) {
                $this->error[] = "file mineType error";
            }
        }

        if ($this->getSize() > $validate['size']) {
            $this->error[] = "file size error max";
        }

        return $this;
    }

    public function getError(): array
    {
        return $this->error;
    }
}