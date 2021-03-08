<?php

namespace framework\view;

use framework\exception\HeroException;
use framework\file\FileUtils;

/**
 * Class frameworkTemplate
 * @package core\view
 */
class HerosphpTemplate
{
    private $viewPath;
    private $viewCachePath;

    /**
     * 通过assign函数传入的变量临时存放数组
     * @var array
     */
    private $templateVar = [];

    /**
     * 模板编译规则
     * @var array
     */
    private static $tempRules = [
        /**
         * 输出变量,数组
         * {$varname}, {$array['key']}
         */
        '/{\$([^\}|\.]{1,})}/i' => '<?php echo \$${1}?>',
        /**
         * 以 {$array.key} 形式输出一维数组元素
         */
        '/{\$([0-9a-z_]{1,})\.([0-9a-z_]{1,})}/i' => '<?php echo \$${1}[\'${2}\']?>',
        /**
         * 以 {$array.key1.key2} 形式输出二维数组
         */
        '/{\$([0-9a-z_]{1,})\.([0-9a-z_]{1,})\.([0-9a-z_]{1,})}/i' => '<?php echo \$${1}[\'${2}\'][\'${3}\']?>',

        //for 循环
        '/{for ([^\}]+)}/i' => '<?php for ${1} {?>',
        '/{\/for}/i' => '<?php } ?>',

        /**
         * foreach key => value 形式循环输出
         * foreach ( $array as $key => $value )
         */
        '/{loop\s+\$([^\}]{1,})\s+\$([^\}]{1,})\s+\$([^\}]{1,})\s*}/i' => '<?php foreach ( \$${1} as \$${2} => \$${3} ) { ?>',
        '/{\/loop}/i' => '<?php } ?>',

        /**
         * foreach 输出
         * foreach ( $array as $value )
         */
        '/{loop\s+\$(.*?)\s+\$([0-9a-z_]{1,})\s*}/i' => '<?php foreach ( \$${1} as \$${2} ) { ?>',
        '/{\/loop}/i' => '<?php } ?>',

        /**
         * {run}标签： 执行php表达式
         * {expr}标签：输出php表达式
         * {url}标签：输出格式化的url
         * {date}标签：根据时间戳输出格式化日期
         * {cut}标签：裁剪字指定长度的字符串,注意截取的格式是UTF-8,多余的字符会用...表示
         */
        '/{run\s+(.*?)}/i' => '<?php ${1} ?>',
        '/{expr\s+(.*?)}/i' => '<?php echo ${1} ?>',
        '/{url\s+(.*?)}/i' => '<?php echo url("${1}") ?>',
        '/{date\s+(.*?)(\s+(.*?))?}/i' => '<?php echo $this->getDate(${1}, "${2}") ?>',
        '/{cut\s+(.*?)(\s+(.*?))?}/i' => '<?php echo $this->cutString(${1}, "${2}") ?>',

        /**
         * if语句标签
         * if () {} elseif {}
         */
        '/{if\s+(.*?)}/i' => '<?php if ( ${1} ) { ?>',
        '/{else}/i' => '<?php } else { ?>',
        '/{elseif\s+(.*?)}/i' => '<?php } elseif ( ${1} ) { ?>',
        '/{\/if}/i' => '<?php } ?>',

        /**
         * 导入模板
         * require|include
         */
        '/{(require|include)\s{1,}([0-9a-z_\.\:]{1,})\s*}/i' => '<?php include $this->getIncludePath(\'${2}\')?>',

        /**
         * 引入静态资源 css file,javascript file
         */
        '/{(res):([a-z]{1,})\s+([^\}]+)\s*}/i' => '<?php echo $this->importResource(\'${2}\', "${3}")?>'
    ];

    /**
     * 静态资源模板
     * @var array
     */
    private static $resTemplate = [
        'css' => "<link rel=\"stylesheet\" type=\"text/css\" href=\"{url}\" />\n",
        'less' => "<link rel=\"stylesheet/less\" type=\"text/css\" href=\"{url}\" />\n",
        'js' => "<script charset=\"utf-8\" type=\"text/javascript\" src=\"{url}\"></script>\n"
    ];

    /**
     * 模板编译缓存配置
     * 0 : 不启用缓存，每次请求都重新编译(建议开发阶段启用)
     * 1 : 开启部分缓存， 如果模板文件有修改的话则放弃缓存，重新编译(建议测试阶段启用)
     * -1 : 不管模板有没有修改都不重新编译，节省模板修改时间判断，性能较高(建议正式部署阶段开启)
     * @var int
     */
    private $cache = 0;

    private $ext;

    public function __construct(array $config = [])
    {
        $this->viewPath = $config['view_path'];
        $this->viewCachePath = $config['cache_path'];
        $this->cache = $config['cache'];
        $this->ext = $config['ext'];
    }

    /**
     * 增加模板替换规则
     * @param array $rules
     */
    public function addRules(array $rules)
    {
        if (is_array($rules) && ! empty($rules)) {
            self::$tempRules = array_merge(self::$tempRules, $rules);
        }
    }

    /**
     * @param string $path
     * @param array $params
     * 渲染模板
     * @return string
     * @throws HeroException
     */
    public function render(string $path, array $params = []): string
    {
        $this->templateVar = array_merge($this->templateVar, $params);
        return $this->display($path);
    }

    /**
     * @param string $key
     * @param array $templateVar
     */
    public function setTemplateVar(string $key, $templateVar): void
    {
        $this->templateVar[$key] = $templateVar;
    }

    /**
     * @param string $tempFile
     * @return string
     * @throws HeroException
     */
    public function display(string $tempFile): string
    {
        $tempFile .= $this->ext;
        $compileFile = $tempFile . '.php';
        if (file_exists($this->viewPath . $tempFile)) {
            $this->compileTemplate($this->viewPath . $tempFile, $this->viewCachePath . $compileFile);
            return $this->getExecutedHtml($this->viewCachePath . $compileFile);
        }
        throw new HeroException('要编译的模板[' . $this->viewPath . $tempFile . '] 不存在！');
    }

    /**
     * 编译模板
     * @param string $tempFile 模板文件路径
     * @param string $compileFile 编译文件路径
     * @throws HeroException
     */
    private function compileTemplate(string $tempFile, string $compileFile)
    {

        //根据缓存情况编译模板
        if (! file_exists($compileFile) || ($this->cache == 1 && filemtime($compileFile) < filemtime($tempFile)) || $this->cache == 0) {
            //获取模板文件
            $content = @file_get_contents($tempFile);
            if ($content == false) {
                throw new HeroException('加载模板文件 {' . $tempFile . '} 失败！请在相应的目录建立模板文件。');
            }
            //替换模板
            $content = preg_replace(array_keys(self::$tempRules), self::$tempRules, $content);
            //生成编译目录
            if (! file_exists(dirname($compileFile))) {
                FileUtils::makeFileDirs(dirname($compileFile));
            }
            //生成php文件
            if (! file_put_contents($compileFile, $content, LOCK_EX)) {
                throw new HeroException("生成编译文件 {$compileFile} 失败。");
            }
        }
    }

    /**
     * 获取include路径
     * 如果没有申明应用则默认以当前的应用为相对路径
     * @param string $tempPath 被包含的模板路径
     * @return string
     * @throws HeroException
     */
    private function getIncludePath(string $tempPath): string
    {
        $filename = str_replace('.', '/', $tempPath) . $this->ext;   //模板文件名称
        $tempFile = $this->viewPath . $filename;
        $compileFile = $this->viewCachePath . $filename . '.php';
        //编译文件
        $this->compileTemplate($tempFile, $compileFile);
        return $compileFile;
    }

    /**
     * 引进静态资源如css，js
     * @param string $type 资源类别
     * @param string $path 资源路径
     * @return string
     */
    private function importResource(string $type, string $path): string
    {
        $src = '/' . $path;
        $template = self::$resTemplate[$type];
        return str_replace('{url}', $src, $template);
    }

    /**
     * 获取日期
     * @param $time
     * @param $format
     * @return string
     */
    private function getDate(string $time, string $format): string
    {
        if (! $format) {
            $format = 'Y-m-d H:i:s';
        }
        return date($format, $time);
    }

    /**
     * 裁剪字符串，使用utf-8编码裁剪
     * @param string $str 要裁剪的字符串
     * @param int $length 字符串长度
     * @return string
     */
    private function cutString(string $str, int $length): string
    {
        if (mb_strlen($str, 'UTF-8') <= $length) {
            return $str;
        }
        return mb_substr($str, 0, $length, 'UTF-8') . '...';
    }

    /**
     * 获取页面执行后的代码
     * @param string $compileTemplate
     * @return  string $html
     */
    private function getExecutedHtml(string $compileTemplate): string
    {
        ob_start();
        extract($this->templateVar);    //分配变量
        include $compileTemplate;
        $html = ob_get_contents();
        ob_end_clean();
        return $html;
    }
}
