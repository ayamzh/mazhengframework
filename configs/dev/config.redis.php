<?php
define('REDIS_INDEX_CACHE', 'cache');  //公用缓存
define('REDIS_INDEX_LOCAL_CACHE', 'localCache');  //本地缓存 127.0.0.1

//普通REDIS
$GLOBALS['REDIS'] = [
    REDIS_INDEX_CACHE => [  //别名
        'host'=>'127.0.0.1','port'=>'6379', 'db'=>0, 'serialize' => true,
    ],
    REDIS_INDEX_LOCAL_CACHE => [
        'host'=>'127.0.0.1','port'=>'6379', 'db'=>1, 'serialize' => true,
    ],
];

//集群REDIS
$GLOBALS['REDIS_CLUSTER'] = [
    REDIS_INDEX_CACHE => [  //别名
        ['host'=>'127.0.0.1','port'=>'6379', 'db'=>0, 'serialize' => true]
    ],
];


