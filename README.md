## Kaleidoscope

你可以在 Laravel Framework 或是 Lumen Framework 快速的存取任何檔案資源，像是 Amazon S3 等等。

----
## Installtion

在專案 composer.json 加入 repositories 參數如下：
````
{
    "repositories": [
        {
            "type": "vcs",
            "url": "git@github.com:evabioz/kaleidoscope.git"
        }
    ],
    "require": {
        "evabioz/kaleidoscope": "dev-develop"
    }
}
````

執行 composer 更新指令：
````
php composer.phar update
````

## Laravel 5

找到 config/app.php 裡面的 providers 鍵值，並註冊 Service Provider：
````
'providers' => array(
    // ...
    Kaleidoscope\Providers\LaravelServiceProvider:class,
)
````

找到 config/app.php 裡面的 aliases 鍵值，並註冊 Facade alias：
````
'aliases' => array(
    // ...
    'Kaleidoscope' => Kaleidoscope\Facades\FeederFacade::class,
)
````

發佈 kaleidoscope 的相關檔案：
````
php artisan vendor:publish
````

## Lumen

在 bootstrap/app.php 找到 $app->register 加入 Service Provider：
````
$app->register(Kaleidoscope\Providers\LumenServiceProvider:class)
````

複製 kaleidoscope.php 檔到 config 目錄，並在 bootstrap/app.php 加入設定檔的別名：
````
$app->configure('kaleidoscope')
````

----
## Configuration

依照環境需求更新 kaleidoscope 設定檔：
````
[
    'max_size' => 10240,
    'default' => '',
    'storages' => []
]
````

----
## Usage

* Amaonz S3

    設定環境：
    ````
        // find storages key in kaleidoscope.php and write:

        'example' => [
        	/*
		     | Create a service instance based.
		     */
		    'driver' => '\\Kaleidoscope\\Services\\AmazonS3',

        	/*
		     | It's the flexibility of this package. You can define the type of upload
		     | file methods.
		     */
		    'types' => 'jpg,png,doc,pdf',

            'client_config' => [
                'key' => '',            // Your owned key.
                'secret' => '',         // Your owned secret.
                'region' => '',         // Your AWS region.
                'version' => 'latest'   // Default "latest".
            ],
            'object_config' => [
                'Bucket' => '',         // Your owned bucket.
                'ACL' => 'public-read'
            ]
        ]
    ````

    使用方法：
    ````
        // Get remote file
        Kaleidoscope::get('ITEM_KEY');

        // Storing file
        Kaleidoscope::store([
            'file' => ''   // Pass a string or url string
        ]);
    ````

----
## Features

* Create a storage service

    For example：
    ````
	   // Refer "example" settings on the configuration.
	   $storage = Kaleidoscope::stroage('example');
    ````

* RerenderImageTrait
    1. 加入 Route

        請在設計 Routing 時，遵循以下參數：

        > id（獲取資源的 key man）

        > type（支援 canvas, resize, widen, blur, opacity, gamma, rotate 等函式）

        > input（請參閱 http://image.intervention.io，預設使用,分開）
        >
        >	  // 如果 routing 需指定某一個 method ，下方將可結合 type 與 input
        >     {spec_name}（如同 input 屬性，支援函式請參閱上方）

        > ext（轉出的圖片格式，預設jpg）

        > quality（轉出的圖片品質，預設90）

        For example：
        ````
            // routes.php

            Route::get('/{id}', 'Controller@getOriginal');
                // ex: http://foo.bar/1
            Route::get('/{id}/{type}_{input}.{ext}', 'Controller@getRender');
                // ex: http://foo.bar/1/resize_100,100.png
            Route::get('/{id}/{resize}.{ext}', 'Controller@getRender');
                // ex: http://foo.bar/1/100,100.png
        ````
    2. 替換數據間隔

        For example：
        ````
            use RerenderImageTrait;

            // Override trait class property.
            protected $delimiter = 'x';

            // routes.php

            Route::get('/{id}/{type}_{input}.{ext}', 'Controller@getRender');
                // ex: http://foo.bar/1/resize_100x100.png
        ````
    3. 加入自訂類型

        參數說明：

        > task（欲執行的方法，每個類型使用|分開）

        > length（接收數據的長度，內容請參閱上面 input）

        For example：
        ````
            use RerenderImageTrait;

            public function __construct() {
                $this->addHandler('foobar', 'resize|blur'); // support mutiple tasks
            }

            // routes.php

            Route::get('/{id}/{foobar}.{ext}', 'Controller@getRender');
                // ex: http://foo.bar/1/100,100,1.png
        ````