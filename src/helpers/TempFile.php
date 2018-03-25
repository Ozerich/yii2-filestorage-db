<?php

namespace blakit\filestorage\helpers;

class TempFile
{
    /** @var string  */
    private $filename;

    /** @var string  */
    private $extension;

    /**
     * TempFile constructor.
     * @param null $file_ext
     */
    public function __construct($file_ext = null)
    {
        $this->extension = $file_ext;
        $this->filename = \Yii::$app->security->generateRandomString();
    }

    /**
     * @return string
     */
    public function getPath()
    {
        return \Yii::getAlias('@runtime') . DIRECTORY_SEPARATOR . $this->filename . ($this->extension ? '.' . $this->extension : '');
    }

    public function __destruct()
    {
        @unlink($this->getPath());
    }

    /**
     * @param $content
     */
    public function write($content)
    {
        $f = fopen($this->getPath(), 'w+');
        fwrite($f, $content);
        fclose($f);
    }

    /**
     * @param $file_path
     */
    public function from($file_path)
    {
        copy($file_path, $this->getPath());
    }
}