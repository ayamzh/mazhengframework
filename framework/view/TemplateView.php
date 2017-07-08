<?php
namespace framework\view;

use framework\core\ViewBase;

/**
 * 模板视图
 *
 * @package framework\view
 */
class TemplateView extends ViewBase
{
    /**
     * 左标识符
     *
     * @var string
     */
    const LEFT_IDENTIFIER = '<{';
    /**
     * 右标识符
     *
     * @var string
     */
    const RIGHT_IDENTIFIER = '}>';

    /**
     * 模板文件目录
     *
     * @var String
     */
    private static $templateDir;
    /**
     * 模板编译目录
     *
     * @var String
     */
    private static $compileDir;

    /**
     * 模板文件名
     *
     * @var string
     */
    private $fileName;
    /**
     * 模板文件路径
     *
     * @var string
     */
    private $templateFile;
    /**
     * 模板编译文件路径
     *
     * @var string
     */
    private $compileFile;

    /**
     * 获取模板编译文件名
     *
     * @return string
     */
    private function _getCompileName()
    {
        $fileItems = explode('.', $this->fileName);

        if (count($fileItems) > 1)
        {
            array_pop($fileItems);
        }

        return implode('.', $fileItems).'.'.filemtime($this->templateFile).'.php';
    }

    /**
     * 编译模板内容
     *
     * @param string $content
     * @return string
     */
    private function _compileContent($content)
    {
        $matchNum = preg_match_all('/'.preg_quote(self::LEFT_IDENTIFIER).'(.*?)'.preg_quote(self::RIGHT_IDENTIFIER).'/is', $content, $matchs);

        if (!empty($matchNum))
        {
            $pairs = array();

            for ($i = 0; $i < $matchNum; $i++)
            {
                $replace = $matchs[0][$i];
                $replace = $this->_compileVariable($replace);
                $replace = $this->_compileIdent($replace);

                $pairs[$matchs[0][$i]] = $replace;
            }

            if (count($pairs) > 0)
            {
                $content = str_replace(array_keys($pairs), array_values($pairs), $content);
            }
        }

        return $content;
    }

    /**
     * 编译模板变量
     *
     * @param string $content
     * @return string
     */
    private function _compileVariable($content)
    {
        $matchNum = preg_match_all('/(\$[\w\[\]\.\$]+)/', $content, $matchs);

        if (!empty($matchNum))
        {
            $pairs = array();
            $replace = array(
                '/\.(\w+)\./'   => "['\$1']",
                '/\.(\w+)\[/'   => "['\$1'][",
                '/\.(\w+)\]/'   => "['\$1']]",
                '/\.(\w+)$/m'  => "['\$1']",
                '/\](\w+)\[/'   => "]['\$1'][",
                '/\](\w+)\]/'   => "]['\$1']]",
                '/\](\w+)$/m'  => "]['\$1']",
                '/\$\[/'   => "\$this->model[",
            );

            for ($i = 0; $i < $matchNum; $i++)
            {
                $pairs[$matchs[0][$i]] = preg_replace(array_keys($replace), array_values($replace), $matchs[0][$i]);
            }

            if (count($pairs) > 0)
            {
                $content = str_replace(array_keys($pairs), array_values($pairs), $content);
            }
        }

        return $content;
    }

    /**
     * 编译模板标识符
     *
     * @param string $content
     * @return string
     */
    private function _compileIdent($content)
    {
        $content = preg_replace('/^'.preg_quote(self::LEFT_IDENTIFIER).'\s*=(.*?)\s*'.preg_quote(self::RIGHT_IDENTIFIER).'$/m', '<?php echo $1; ?>', $content);

        $content = preg_replace('/^'.preg_quote(self::LEFT_IDENTIFIER).'\s*while\s+(.*?)\s*'.preg_quote(self::RIGHT_IDENTIFIER).'$/m', '<?php while($1) { ?>', $content);
        $content = preg_replace('/^'.preg_quote(self::LEFT_IDENTIFIER).'\s*\/while\s*'.preg_quote(self::RIGHT_IDENTIFIER).'$/m', '<?php } ?>', $content);

        $content = preg_replace('/^'.preg_quote(self::LEFT_IDENTIFIER).'\s*foreach\s+(.*?)\s*'.preg_quote(self::RIGHT_IDENTIFIER).'$/m', '<?php foreach($1) { ?>', $content);
        $content = preg_replace('/^'.preg_quote(self::LEFT_IDENTIFIER).'\s*\/foreach\s*'.preg_quote(self::RIGHT_IDENTIFIER).'$/m', '<?php } ?>', $content);

        $content = preg_replace('/^'.preg_quote(self::LEFT_IDENTIFIER).'\s*for\s+(.*?)\s*'.preg_quote(self::RIGHT_IDENTIFIER).'$/m', '<?php for($1) { ?>', $content);
        $content = preg_replace('/^'.preg_quote(self::LEFT_IDENTIFIER).'\s*\/for\s*'.preg_quote(self::RIGHT_IDENTIFIER).'$/m', '<?php } ?>', $content);

        $content = preg_replace('/^'.preg_quote(self::LEFT_IDENTIFIER).'\s*if\s+(.*?)\s*'.preg_quote(self::RIGHT_IDENTIFIER).'$/m', '<?php if($1) { ?>', $content);
        $content = preg_replace('/^'.preg_quote(self::LEFT_IDENTIFIER).'\s*else\s*'.preg_quote(self::RIGHT_IDENTIFIER).'$/m', '<?php } else { ?>', $content);
        $content = preg_replace('/^'.preg_quote(self::LEFT_IDENTIFIER).'\s*else\s*if\s*'.preg_quote(self::RIGHT_IDENTIFIER).'$/m', '<?php } elseif { ?>', $content);
        $content = preg_replace('/^'.preg_quote(self::LEFT_IDENTIFIER).'\s*\/if\s*'.preg_quote(self::RIGHT_IDENTIFIER).'$/m', '<?php } ?>', $content);

        $content = preg_replace('/^'.preg_quote(self::LEFT_IDENTIFIER).'(.*?)'.preg_quote(self::RIGHT_IDENTIFIER).'$/m', '<?php $1; ?>', $content);

        return $content;
    }

    /**
     * 构造函数
     *
     * @param string $fileName
     * @param mixed $model
     */
    public function __construct($fileName, $model = null)
    {
        $this->fileName = $fileName;
        $this->model = $model;

        //require '/data/wutong/templates/template/'.$fileName;
        //exit;
        
        $this->templateFile = implode(DIRECTORY_SEPARATOR, array(self::$templateDir, $this->fileName));

        if (!is_readable($this->templateFile))
        {
            throw new \Exception('template file can\'t read: ' . $this->templateFile);
        }

        $this->compileFile = implode(DIRECTORY_SEPARATOR, array(self::$compileDir, $this->_getCompileName()));
    }

    /**
     * 设置配置信息
     *
     * @param string $templateDir
     * @param string $compileDir
     */
    public static function setConfig($templateDir, $compileDir)
    {
        self::$templateDir = $templateDir;
        self::$compileDir = $compileDir;
    }

    /**
     * 展示视图
     *
     */
    public function display()
    {
        header("Content-Type: text/html; charset=utf-8");

        if (!file_exists($this->compileFile))
        {
            if (!is_writable(self::$compileDir))
            {
                throw new \Exception('compile directory can\'t write: ' . self::$compileDir);
            }

            $content = file_get_contents($this->templateFile);
            $content = $this->_compileContent($content);

            file_put_contents($this->compileFile, $content);
        }

        require $this->compileFile;
    }
}
