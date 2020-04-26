<?php

namespace ozerich\filestorage\structures;

use ozerich\filestorage\storage\BaseStorage;
use ozerich\filestorage\storage\FileStorage;
use ozerich\filestorage\validators\Validator;
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

    /** @var bool */
    private $fixOrientation = true;

    /** @var integer */
    private $quality = 100;

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

        if (isset($config['fixOrientation'])) {
            $this->fixOrientation = (bool)$config['fixOrientation'];
        }

        if (isset($config['quality'])) {
            if ($config['quality'] > 0 && $config['quality'] < 1) {
                $this->quality = $config['quality'] * 100;
            } else if ($config['quality'] > 100 || $config['quality'] < 1) {
                throw new InvalidConfigException('Quality is invalid');
            } else {
                $this->quality = $config['quality'];
            }
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
                isset($thumbnail['exact']) ? $thumbnail['exact'] : false,
                isset($thumbnail['2x']) ? $thumbnail['2x'] : false,
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
     * @param $config
     * @throws InvalidConfigException
     */
    private function createStorage($config)
    {
        if (isset($config['type'])) {
            if ($config['type'] == 'file') {
                $this->storage = new FileStorage($config);
            }
        } elseif (isset($config['class'])) {
            $this->storage = \Yii::createObject($config['class'], [$config]);
            if ($this->storage instanceof BaseStorage == false) {
                throw new InvalidConfigException('Invalid storage class, it must be inherited from BaseStorage');
            }
        } else {
            throw new InvalidConfigException('Invalid storage config for scenario "' . $this->getId() . '": type or class are not set');
        }
    }

    /**
     * @return int
     */
    public function getQuality()
    {
        return $this->quality;
    }

    /**
     * @return BaseStorage
     */
    public function getStorage()
    {
        return $this->storage;
    }

    /**
     * @return bool
     */
    public function shouldFixOrientation()
    {
        return $this->fixOrientation;
    }
}