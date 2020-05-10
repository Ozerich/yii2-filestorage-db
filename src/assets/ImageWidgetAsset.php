<?php

namespace ozerich\filestorage\assets;

use Yii;
use yii\web\AssetBundle;

/**
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @since 2.0
 */
class ImageWidgetAsset extends AssetBundle
{
    public $sourcePath = '@vendor/ozerich/yii2-filestorage-db/src/widgets/static';

    /**
     * @inheritdoc
     */
    public $depends = [
        'yii\web\JqueryAsset',
    ];

    public $js = [
        'https://code.jquery.com/ui/1.12.1/jquery-ui.min.js',
        'image-widget.js'
    ];

    public $css = [
        'image-widget.css'
    ];
}
