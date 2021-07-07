<?php


namespace iflow\fileSystem\lib;

use iflow\exception\lib\HttpException;
use Psr\Http\Message\StreamInterface;
use Psr\Http\Message\UploadedFileInterface;

class upLoadFile extends fileSystem implements UploadedFileInterface
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
        $file['error'] = $file['error'] ?? 0;
        if ($file['error'] === 0) $this->fileList[$name][] = new self($file['tmp_name']);
        return $this;
    }

    /**
     * 获取上传文件列表
     * @return array
     */
    public function getFileList(): array
    {
        return $this->fileList;
    }

    /**
     * 获取单个文件
     * @param string $index
     * @return mixed|null
     */
    public function getFile(string $index)
    {
        return $this->fileList[$index] ?? null;
    }

    // 读取文件
    public function read(): string
    {
        return file_get_contents($this->getPathname());
    }

    /**
     * 保存文件至服务器
     * @param string $savePath
     * @param array $config
     * @return mixed
     */
    public function move(string $savePath, array $config = [])
    {
        $validate = $this->validate($config);
        if ($validate -> error) {
            throw new HttpException(403, $validate -> error[0]);
        }

        $fileName = $this->fileNameHash($config);
        $path = $this->getSavePath($savePath, $fileName);
        $path['path'] = str_replace("\\", "/", $path['path']);
        $basePath = dirname($path['path']);
        !is_dir($basePath) && mkdir($basePath, 0755, true);
        // 当 move_uploaded_file 无法使用时(非框架自带HTTP服务时) 直接使用 rename
        $upload = move_uploaded_file($this->getPathname(), $path['path']) ?: rename($this->getPathname(), $path['path']);
        return $upload ? $path : false;
    }

    /**
     * 文件重命名
     * @param array $config
     * @return mixed
     */
    protected function fileNameHash(array $config = []): mixed
    {
        $fileNameType = $config['type'] ?? 'hash';
        $fileNameType = is_string($fileNameType) ? [
            $fileNameType
        ] : [
            array_keys($fileNameType),
            $fileNameType['algo'] ?? 'sha1'
        ];
        return call_user_func([$this, $fileNameType[0]], $fileNameType[1] ?? 'sha1');
    }

    /**
     * 获取文件存储路径
     * @param string $savePath
     * @param string $fileName
     * @return string[]
     */
    protected function getSavePath(string $savePath, string $fileName) {
        $saveRoot = rtrim($this->config['rootPath'], DIRECTORY_SEPARATOR). DIRECTORY_SEPARATOR;
        $savePath = str_replace("\\", "/", $savePath . DIRECTORY_SEPARATOR . $fileName . '.' . $this->getExtension());
        $path = $saveRoot . $savePath;
        return [
            'savePath' => $savePath,
            'saveRoot' => $saveRoot,
            'path' => $path
        ];
    }

    /**
     * 验证文件规则
     * @param array $validate
     * @return $this
     */
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

    /**
     * 获取上传错误信息
     * @return array
     */
    public function getError(): array
    {
        return $this->error;
    }

    public function getStream(): bool|StreamInterface|string
    {
        // TODO: Implement getStream() method.
        return $this->read();
    }

    public function moveTo($targetPath)
    {
        // TODO: Implement moveTo() method.
        return $this->move($targetPath);
    }

    public function getClientFilename(): ?string
    {
        // TODO: Implement getClientFilename() method.
        return $this->getFilename();
    }

    public function getClientMediaType(): ?string
    {
        // TODO: Implement getClientMediaType() method.
        return $this->getFileMine();
    }
}