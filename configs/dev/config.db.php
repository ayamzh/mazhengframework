<?php
define('DB_ADVERT', 'weibo_ai_advert_online');

$GLOBALS['DB'] = [
    DB_ADVERT => [  //别名
        'master' => [
            'host'      => '127.0.0.1',
            'port'      => 3306,
            'user'      => 'ceshi',
            'password'  => 'sq_123456',
            'database'  => 'weibo_ai_advert_online',  //数据库名
            'charset'   => 'utf8',
            'pconnect'  => false,  // MySQL长连接
            'attributes' => [
                \PDO::ATTR_TIMEOUT => 20
            ],
        ],
        'slave' => [
            'host'      => '127.0.0.1;127.0.0.1',  //TODO 多从库支持
            'port'      => 3306,
            'user'      => 'ceshi',
            'password'  => 'sq_123456',
            'database'  => 'weibo_ai_advert_online',
            'charset'   => 'utf8',
            'pconnect'  => false,  // MySQL长连接
            'attributes' => [
                \PDO::ATTR_TIMEOUT => 20
            ],
        ],
    ],
];


