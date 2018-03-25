<?php

namespace blakit\filestorage;

use blakit\filestorage\helpers\TempFile;
use blakit\filestorage\models\File;
use blakit\filestorage\services\ImageService;
use blakit\filestorage\structures\Scenario;
use yii\base\InvalidArgumentException;
use yii\web\UploadedFile;

class Component extends \yii\base\Component
{
    /** @var Scenario[] */
    public $scenarios = [];

    /** @var int */
    public $max_timeout = 5;

    /** @var string */
    public $modelClass;

    /** @var string[] */
    private $errors = [];

    /** @var Scenario[] */
    static $_scenarios;

    /** @var integer */
    static $_max_timeout;

    /** @var integer */
    static $start_first;

    public function init()
    {
        foreach ($this->scenarios as $id => $scenario_config) {
            $scenario = new Scenario($id, $scenario_config);
            self::$_scenarios[$id] = $scenario;
        }

        self::$_max_timeout = $this->max_timeout;

        parent::init();
    }


    public function disableThumbnailTimeout()
    {
        self::$_max_timeout = 0;
    }

    /**
     * @param $scenario
     * @return Scenario
     */
    public static function getScenario($scenario)
    {
        if (isset(self::$_scenarios[$scenario])) {
            return self::$_scenarios[$scenario];
        }

        throw new InvalidArgumentException('Scenario "' . $scenario . '" not found');
    }

    /**
     * @param File $file
     */
    public function prepareThumbnails(File $file)
    {
        return self::staticPrepareThumbnails($file);
    }

    /**
     * @param File $file
     */
    public function deleteThumbnails(File $file)
    {
        $scenario = self::getScenario($file->scenario);

        foreach ($scenario->getThumbnails() as $thumbnail) {
            $scenario->getStorage()->delete($file->hash, $file->ext, $thumbnail);
        }
    }

    /**
     * @param UploadedFile $file
     * @param string $scenario
     * @return File|null
     */
    public function createFileFromUploadedFile(UploadedFile $file, $scenario)
    {
        $scenario = self::getScenario($scenario);

        if (!$file) {
            return null;
        }

        return $this->createFile($file->tempName, $file->name, $file->getExtension(), $scenario);
    }

    /**
     * @param string $url
     * @param string $scenario
     * @return File|null
     */
    public function createFileFromUrl($url, $scenario)
    {
        $scenario = self::getScenario($scenario);

        $p = strrpos($url, '?');
        if ($p !== false) {
            $url_without_params = substr($url, 0, $p);
        } else {
            $url_without_params = $url;
        }

        $file_name = $file_ext = null;

        $p = strrpos($url_without_params, '.');
        if ($p !== null) {
            $file_ext = substr($url_without_params, $p + 1);

            $p = strrpos($url_without_params, '/');
            if ($p !== false) {
                $file_name = substr($url_without_params, $p + 1);
            }
        }

        try {
            $content = file_get_contents($url);
        } catch (\Exception $ex) {
            return null;
        }

        $temp = new TempFile();
        $temp->write($content);

        return $this->createFile($temp->getPath(), $file_name, $file_ext, $scenario);
    }

    /**
     * @param $data
     * @param $file_name
     * @param $scenario
     * @return File
     */
    public function createFileFromBase64($base64_string, $file_name, $scenario)
    {
        list($meta, $content) = explode(';', $base64_string);

        $p = strpos($content, ',');
        if ($p !== false) {
            $content = substr($content, $p + 1);
        }

        $image_raw = base64_decode($content);

        if ($file_name) {
            $file_ext = end(explode('.', $file_name));
        } else {
            $mime_type = substr($meta, 5);
            $file_ext = ImageService::mime2ext($mime_type);
        }

        $temp = new TempFile();
        $temp->write($image_raw);

        return $this->createFile($temp->getPath(), $file_name, $file_ext, $scenario);
    }

    /**
     * @return array
     */
    public function getLastErrors()
    {
        return $this->errors;
    }

    /**
     * @param File $file
     */
    public static function staticPrepareThumbnails(File $file)
    {
        if (!self::$start_first) {
            self::$start_first = time();
        }

        if (self::$_max_timeout > 0 && (time() - self::$start_first >= self::$_max_timeout)) {
            return;
        }

        ImageService::prepareThumbnails($file, self::getScenario($file->scenario));
    }

    /**
     * @param $file_path
     * @param $file_hash
     * @param $file_ext
     * @param Scenario $scenario
     * @return File
     */
    private function createModel($file_path, $file_hash, $file_name, $file_ext, Scenario $scenario)
    {
        $file_info = ImageService::getImageInfo($file_path);

        if ($this->modelClass) {
            $model = \Yii::createObject($this->modelClass);
        } else {
            $model = new File();
        }

        $model->hash = $file_hash;
        $model->name = $file_name;
        $model->scenario = $scenario->getId();
        $model->width = $file_info['width'];
        $model->height = $file_info['height'];
        $model->size = $file_info['size'];
        $model->mime = mime_content_type($file_path);
        $model->ext = strtolower($file_ext);

        $model->save();

        return $model;
    }


    /**
     * @param $file_path
     * @param $file_ext
     * @param string $scenario
     * @return File
     */
    private function createFile($file_path, $file_name, $file_ext, $scenario)
    {
        $scenario = self::getScenario($scenario);

        $temp = new TempFile($file_ext);
        $temp->from($file_path);

        $validate = $scenario->getValidator()->validate($file_path);
        if (!$validate) {
            $this->errors = $scenario->getValidator()->getErrors();
            return null;
        }

        $this->errors = [];

        $file_ext = strtolower($file_ext);

        $file_hash = \Yii::$app->security->generateRandomString(32);
        $scenario->getStorage()->upload($file_path, $file_hash, $file_ext);

        $model = $this->createModel($temp->getPath(), $file_hash, $file_name, $file_ext, $scenario);

        $this->prepareThumbnails($model);

        return $model;
    }

}