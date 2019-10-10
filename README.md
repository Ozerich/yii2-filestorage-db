Installation
------------

The preferred way to install this extension is through [composer](http://getcomposer.org/download/).

Either run

```
php composer.phar require ozerich/yii2-filestorage-db "*"
```

or add

```
"ozerich/yii2-filestorage-db": "*"
```
to the require section of your `composer.json` file.

Add component configuration to your config.php 

```php
    'components' => [
        'media' => [
            'class' => 'ozerich\filestorage\FileStorage',
            'scenarios' => [
                'avatar' => [
                    'storage' => [
                        'type' => 'file',
                        'saveOriginalFilename' => false,
                        'uploadDirPath' => __DIR__ . '/../../web/uploads/avatars',
                        'uploadDirUrl' => '/uploads/avatars',
                    ],
                    'validator' => [
                        'maxSize' => 2 * 1024 * 1024,     // 2 MB
                        'checkExtensionByMimeType' => true,
                        'extensions' => ['jpg', 'jpeg', 'bmp', 'gif', 'png']
                    ],
                    'thumbnails' => [
                        [
                            'width' => 500
                        ],
                        [
                            'height' => 500
                        ],
                        [
                            'alias' => 'preview',
                            'width' => 250
                        ],
                        [
                            'width' => 200,
                            'height' => 200,
                            'exact' => true
                        ],
                    ],
                    'quality' => 75
                ],
                'document' => [
                    'storage' => [
                        'type' => 'file',
                        'uploadDirPath' => __DIR__ . '/../../web/uploads/documents',
                        'uploadDirUrl' => '/uploads/documents',
                    ],
                    'validator' => [
                        'maxSize' => 20 * 1024 * 1024,      // 20 MB
                        'checkExtensionByMimeType' => true,
                        'extensions' => ['pdf', 'doc'],
                    ],
                ]
            ]
        ]
    ]
```

Add media component to bootstrap

```php
    'config' => [
        'bootstrap' => [..., 'media'],
    ]
```

Add migrations path to your console config (console.php)

```php
    'config' => [
        'controllerMap' => [
            'migrate' => [
                'class' => 'yii\console\controllers\MigrateController',
                'migrationNamespaces' => [
                    'ozerich\filestorage\migrations',
                ],
            ],
        ],
    ]
```
	
		
Apply migrations
	
```
php yii migrate/up
```
	

Usage
-----

Example usage (get file from HTTP request):

```php
    /* app\controllers\UploadController.php */
    
    public function actionImage()
    {
        Yii::$app->response->format = 'json';
        $file = UploadedFile::getInstanceByName('file');
     
        $model = Yii::$app->media->createFileByUploadedFile($file, 'avatar');
    
        return [
            'image' => $model->toJSON()
        ];
    }
```

Output will be:

```json
    {
        "id": 1,
        "url": "http://localhost/uploads/images/W7/LK/W7LK3u5LJ7LGtc0nlGOqinl_AVZlinQH.jpg",
        "name": "test-file.jpg",
        "ext": "jpg",
        "mime": "image/jpeg",
        "size": 64749,
        "thumbnails": [
            {
                "id": "1_ORIGINAL",
                "thumb": "ORIGINAL",
                "width": 450,
                "height": 800,
                "url": "http://localhost/uploads/images/W7/LK/W7LK3u5LJ7LGtc0nlGOqinl_AVZlinQH.jpg"
            },
            {
                 "id": "1_500xAUTO",
                 "thumb": "500xAUTO",
                 "width": 500,
                 "height": 200,
                 "url": "http://localhost/uploads/images/W7/LK/W7LK3u5LJ7LGtc0nlGOqinl_AVZlinQH_500_AUTO.jpg"
            },
            {
                 "id": "1_AUTOx500",
                 "thumb": "AUTOx500",
                 "width": 260,
                 "height": 500,
                 "url": "http://localhost/uploads/images/W7/LK/W7LK3u5LJ7LGtc0nlGOqinl_AVZlinQH_AUTO_500.jpg"
            },
            {
                 "id": "1_200x200",
                 "thumb": "200x200",
                 "width": 200,
                 "height": 200,
                 "url": "http://localhost/uploads/images/W7/LK/W7LK3u5LJ7LGtc0nlGOqinl_AVZlinQH_200_200.jpg"
            }
        ]
    }
```

Example usage (load file from url):

```php 
    /* app\models\User.php */
    
    public function setUserAvatarFromUrl($image_url)
    {
       $image = $media->createFileFromUrl($image_url, 'avatar');
       $this->avatar_image_id = $image->id;
    }
```

Example usage (load file from base64string):

```php 
    /* app\controllers\UploadController.php */
    
    public function actionImage()
    {
        Yii::$app->response->format = 'json';
        $base64string = Yii::$app->request->post('data');
        $filename = Yii::$app->request->post('filename');
     
        $model = Yii::$app->media->createFileFromBase64($base64string, $filename, 'avatar');
    
        return [
            'image' => $model->toJSON()
        ];
    }
```

Console commands
-----

To use console commands you should create a file *FilestorageController.php* at your *commands* folders.

```
<?php

namespace app\commands;

class FilestorageController extends \ozerich\filestorage\console\FilestorageController
{
    // className of your File model
    public $modelClass = 'app\models\Image';
}
```

If you did not use console commands at your projects, don't forget to confugure them, you should insert code in your `config/console.php`

```
    'controllerNamespace' => 'app\commands',
```

List of commands:

1. **php yii filestorage/fix-thumbnails** - Regenerate thumbnails of all images





