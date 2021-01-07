<?php

namespace framework\file;

use framework\exception\HeroException;
use framework\exception\UploadException;

/**
 * Class FileUpload
 * @package framework\file
 */
class FileUpload
{
    /**
     * @var array 上传文件配置参数
     */
    protected $config = array(
        //允许上传的文件类型
        'allow_ext' => 'jpg|jpeg|png|gif|txt|pdf|rar|zip|swf|bmp|c|java|mp3',
        //图片的最大宽度, 0没有限制
        'max_width' => 0,
        //图片的最大高度, 0没有限制
        'max_height' => 0,
        //文件的最大尺寸
        'max_size' => 1024000,     /* 文件size的最大 1MB */
    );

    /**
     * @var string 原生文件名称
     */
    private $fileName;

    /**
     * @var string 临时文件名
     */
    private $tmpName;


    /**
     * FileUpload constructor.
     * @param $fileName
     * @param $tmpName
     */
    public function __construct($fileName, $tmpName)
    {
        $this->fileName = $fileName;
        $this->tmpName = $tmpName;
    }

    /**
     * upload file method.
     * @param string $path
     * @return       mixed false or file info array.
     * @throws \Exception
     */
    public function move(string $path)
    {
        if (!$this->checkUploadDir($path)) {
            throw new UploadException("上传的目录创建失败,请检查目录权限");
        }
        $newFilePath = $path . DIRECTORY_SEPARATOR . $this->getNewFileName($this->fileName);
        if (move_uploaded_file($this->tmpName, $newFilePath)) {
            return $newFilePath;
        }
        return false;
    }


    /**
     * @param array $config
     * @return $this
     * @throws UploadException
     */
    public function isValid($config = []): FileUpload
    {
        $this->config = array_merge($this->config, $config);
        //检测文件格式允许
        if ($this->config['allow_ext'] !== '*') {
            $_ext = strtolower($this->getFileExt($this->fileName));
            $_allow_ext = explode("|", $this->config['allow_ext']);
            if (!in_array($_ext, $_allow_ext, true)) {
                throw new UploadException("不允许的文件类型!");
            }
        }
        //检测大小
        if (filesize($this->fileName) > $this->config['max_size']) {
            throw new UploadException("文件超出大小限制!");
        }
        //如果是图片还要检查图片的宽度和高度是否超标
        $size = getimagesize($this->fileName);
        if ($size !== false) {
            if (($this->config['max_width'] > 0 && $size[0] > $this->config['max_width'])
                || ($this->config['max_height'] > 0 && $size[1] > $this->config['max_height'])) {
                throw new UploadException("文件尺寸超出了限制!");
            }
        }
        return $this;
    }


    /**
     * 检测上传目录
     * @param string $path
     * @return bool
     */
    protected function checkUploadDir(string $path): bool
    {
        if (!file_exists($path)) {
            return self::makeFileDirs($path);
        }
        return true;
    }

    /**
     * 创建多级目录
     * @param $path
     * @return bool
     */
    public static function makeFileDirs($path): bool
    {
        //必须考虑 "/" 和 "\" 两种目录分隔符
        $files = preg_split('/[\/|\\\]/s', $path);
        $_dir = '';
        foreach ($files as $value) {
            $_dir .= $value . DIRECTORY_SEPARATOR;
            if (!file_exists($_dir) && !mkdir($_dir) && !is_dir($_dir)) {
                throw new \RuntimeException(sprintf('Directory "%s" was not created', $_dir));
            }
        }
        return true;

    }


    /**
     * 获取文件名后缀
     * @param $filename
     * @return string
     */
    private function getFileExt($filename): string
    {
        $_pos = strrpos($filename, '.');
        return strtolower(substr($filename, $_pos + 1));
    }


    /**
     * 获取新的文件名
     * @param $filename
     * @return string
     * @throws \Exception
     */
    public function getNewFileName($filename): string
    {
        $_ext = $this->getFileExt($filename);
        return time() . '-' . random_int(100000, 999999) . '.' . $_ext;
    }
}
