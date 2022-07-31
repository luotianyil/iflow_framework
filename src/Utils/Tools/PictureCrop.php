<?php


namespace iflow\Utils\Tools;


class PictureCrop
{

    // 目标图片 长宽
    public int $width = 800;
    public int $height = 600;

    // 原图片
    public mixed $source;
    // 生成的目标图片
    public $crop = null;

    // 压缩后文件 图片
    public array $imageFile = [
        'file' => '',
        'path' => ''
    ];

    public array $sourceImageSize = [];

    public string $imageCreateFromType;

    /**
     * @param string $imagePath 图片地址
     * @param string $fontTtfPath 字体地址
     * @throws \Exception
     */
    public function __construct(protected string $imagePath, protected string $fontTtfPath)
    {
        if (!file_exists($this->imagePath)) {
            throw new \Exception('图片文件不存在');
        }
        $this->sourceImageSize = getimagesize($this->imagePath) ?: throw new \Exception('文件格式错误，需为图片文件');
        $this->imageCreateFromType = "imagecreatefrom".image_type_to_extension($this->sourceImageSize[2], false);
        $this->source = call_user_func($this->imageCreateFromType, $this->imagePath);
    }

    /**
     * @param int $height
     * @return static
     */
    public function setHeight(int $height): static
    {
        $this->height = $height;
        return $this;
    }

    /**
     * @param int $width
     * @return static
     */
    public function setWidth(int $width): static
    {
        $this->width = $width;
        return $this;
    }

    /**
     * 压缩图片
     * @return $this
     */
    public function compressionImage(): static
    {
        $this->crop = imagecreatetruecolor($this->width, $this->height);
        imagecopyresampled($this->crop, $this->source, 0, 0, 0, 0, $this->width, $this->height, $this->sourceImageSize[0], $this->sourceImageSize[1]);

        return $this;
    }

    /**
     * 裁剪图片
     * @param int $dst_x 原图片 在生成 图片x轴偏移量
     * @param int $dst_y 原图片 在生成 图片y轴偏移量
     * @param int $src_x 原图片 x 轴起始位置
     * @param int $src_y 原图片 y 轴起始位置
     * @return $this
     */
    public function PicCrop(int $dst_x = 0, int $dst_y = 0, int $src_x = 0, int $src_y = 0): static
    {
        $this->crop = imagecreatetruecolor($this->width, $this->height);
        imagecopy($this->crop, $this->source, $dst_x, $dst_y, $src_x, $src_y, $this->sourceImageSize[0], $this->sourceImageSize[1]);
        return $this;
    }

    /**
     * 输出图片
     * @param string|null $savePath
     * @return false|string
     */
    public function out(string $savePath = null) {
        ob_start();
        imagejpeg($this->crop ?: $this->source, $savePath);
        $info = ob_get_contents();
        ob_end_clean();

        // 释放图片
        imagedestroy($this->source);
        if ($this->crop) imagedestroy($this->crop);

        return $info;
    }

    /**
     * 设置水印
     * @param string $waterText 水印文字、图片
     * @param array $color
     * @param string $font 字体文件
     * @param int $fontSize 字体大小
     * @param int $dst_x 水印X轴开始位置
     * @param int $dst_y 水印Y轴开始位置
     * @return static
     */
    public function Watermark(string $waterText = "", array $color = [
        255, 255, 255, 50
    ], string $font = "",
      int $fontSize = 20,
      int $dst_x = 0, int $dst_y = 0, array $waterBackground = [ 0xFF,0xFF,0xFF ]
    ): static
    {
        $isFile = file_exists($waterText);
        if (!$isFile) {
            // 创建图片
            $font = str_replace('/', '\\', $font ?: $this->fontTtfPath);
            $width = strlen($waterText) * $fontSize;
            $height = $fontSize + 10;
            $water = imagecreate($width, $height);
            $background = imagecolorallocate($water, ...$waterBackground);
            imagecolortransparent($water, $background);
            imagettftext($water, $fontSize,0,0,25, imagecolorallocatealpha($water, ...$color), $font, $waterText);
        } else {
            $water = imagecreatefrompng($waterText);
        }
        imagecopymerge($this->crop ?: $this->source, $water, $dst_x, $dst_y, 0, 0, $this->width, $this->height, 30);
        imagedestroy($water);
        return $this;
    }
}