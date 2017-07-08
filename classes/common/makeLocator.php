<?php
$content = array();
$content[] = '<?php';
$content[] = 'namespace common;';
$content[] = '';
$content[] = 'use framework\core\Singleton;';
// $content[] = 'use service;';
$content[] = '';
$content[] = '/**';
$content[] = ' * 获取业务逻辑实例工具类';
$content[] = ' * ';
$content[] = ' */';
$content[] = 'class ServiceLocator';
$content[] = '{';

$serviceDir = dir("../service");

while (false !== ($entry = $serviceDir->read()))
{
    if (preg_match("/^([a-zA-Z0-9]+Service).php$/is", $entry, $items))
    {
        $content[] = "    /**";
        $content[] = "     * 取得一个{$items[1]}对象";
        $content[] = "     *";
        $content[] = "     * @return \\service\\{$items[1]}";
        $content[] = "     */";
        $content[] = "    public static function get{$items[1]}()";
        $content[] = "    {";
        $content[] = "        return Singleton::get(\"service\\\\{$items[1]}\");";
        $content[] = "    }";
        $content[] = "";
    }
}

$serviceDir->close();

$content[] = '}';
$content[] = '';

file_put_contents('ServiceLocator.php', implode("\n", $content));