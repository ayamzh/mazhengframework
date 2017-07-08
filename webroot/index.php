<?php
//    $classRef = new \ReflectionClass($this->className);
//             $this->tableName = $classRef->getConstant('TABLE_NAME');
// server {
//     listen 30122;
//     server_name 30122.dev.91hong.com.cn;
//     root /home/mazheng/cms/web;
//     #root /data/wwwroot/root/webstar/web;
//     access_log  /home/mazheng/nginx/logs/cms-access-log  main;
//     location / {
//         try_files $uri $uri/ /index.php?$args;
//     }
//     error_page 404 /404.html;
//     location ~ .*\.php$ {
//         fastcgi_pass unix:/dev/shm/php-cgi.sock;
//         fastcgi_index  index.php;
//         fastcgi_param  SCRIPT_FILENAME  $document_root$fastcgi_script_name;
//         include        fastcgi_params;
//     }
//     client_max_body_size 5M;
// }
require_once ("../framework/init.php");

use framework\dispatcher\HTTPDispatcher;
$dispatcher = new HTTPDispatcher();
$dispatcher->setRouteType(HTTPDispatcher::ROUTE_TYPE_PATH);
$dispatcher->dispatch();
