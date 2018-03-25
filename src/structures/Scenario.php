<?php

namespace blakit\filestorage\structures;

use blakit\filestorage\storage\BaseStorage;
use blakit\filestorage\storage\FileStorage;
use blakit\filestorage\validators\Validator;
use yii\base\InvalidConfigException;

class Scenario
{
    /** @var string */
    private $id;

    /** @var array */
    private $thumbnails = [];

    /** @var array */
    private $thumbnails_by_alias = [];

    /** @var Validator */
    private $validator;

    /** @var BaseStorage */
    private $storage;

    /**
     * Scenario constructor.
     * @param $id
     * @param $config
     * @throws InvalidConfigException
     */
    public function __construct($id, $config)
    {
        $this->id = $id;

        if (!isset($config['storage'])) {
            throw new InvalidConfigException('storage is required');
        }

        $this->createStorage($config['storage']);

        if (isset($config['validator'])) {
            $this->validator = $this->createValidator($config['validator']);
        }

        if (isset($config['thumbnails'])) {
            $this->setThumbnails($config['thumbnails']);
        }
    }

    /**
     * @return string
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param array $thumbnails
     */
    private function setThumbnails($thumbnails)
    {
        foreach ($thumbnails as $thumbnail) {
            $thumbnail_model = new Thumbnail(
                isset($thumbnail['width']) ? $thumbnail['width'] : 0,
                isset($thumbnail['height']) ? $thumbnail['height'] : 0,
                isset($thumbnail['crop']) ? $thumbnail['crop'] : false,
                isset($thumbnail['exact']) ? $thumbnail['exact'] : false
            );

            $this->thumbnails[] = $thumbnail_model;

            if (isset($thumbnail['alias'])) {
                $this->thumbnails_by_alias[$thumbnail['alias']] = $thumbnail_model;
            }
        }
    }

    /**
     * @param $alias
     * @return Thumbnail|null
     */
    public function getThumbnailByAlias($alias)
    {
        return isset($this->thumbnails_by_alias[$alias]) ? $this->thumbnails_by_alias[$alias] : null;
    }

    /**
     * @return Thumbnail[]
     */
    public function getThumbnails()
    {
        return $this->thumbnails;
    }

    /**
     * @return bool
     */
    public function hasThumnbails()
    {
        return !empty($this->thumbnails);
    }

    /**
     * @param array $config
     * @return Validator
     */
    private function createValidator($config)
    {
        $validator = new Validator();

        if (isset($config['checkExtensionByMimeType'])) {
            $validator->setCheckExtensionByMimeType($config['checkExtensionByMimeType']);
        }

        if (isset($config['extensions'])) {
            $validator->setExtensions($config['extensions']);
        }

        if (isset($config['maxSize'])) {
            $validator->setMaxSize($config['maxSize']);
        }

        return $validator;
    }


    /**
     * @return Validator
     */
    public function getValidator()
    {
        return $this->validator;
    }

    /**
     * @param array $config
     */
    private function createStorage($config)
    {
        if (isset($config['type'])) {
            if ($config['type'] == 'file') {
                $this->storage = new FileStorage($config);
            }
        }
    }

    /**
     * @return BaseStorage
     */
    public function getStorage()
    {
        return $this->storage;
    }
}