<?php
namespace framework\annotation\yapi;


use Doctrine\Common\Annotations\AnnotationReader;
use Doctrine\Common\Annotations\AnnotationRegistry;
use GuzzleHttp\Client;
use framework\annotation\yapi\YController;
use framework\annotation\yapi\YMethod;
use framework\annotation\yapi\YParam;

class Yapi
{
    /**
     * yapi服务器地址
     * @var String
     */
    private static $ip;

    /**
     * yapi项目token
     * @var String
     */
    private static $token;

    /**
     * 扫描的目录
     * @var string
     */
    private static $scanPath;

    /**
     * 扫描命名空间
     * @var string
     */
    private static $scanNamespace;

    /** @var AnnotationReader $reader */
    private static $reader;

    /**
     * 生成的api文档的模块
     * @var String
     */
    private static $module;

    public static function run(): void
    {
        self::init();
        //扫描controller文件
        $files = self::scanAllFiles(self::$scanPath);
        foreach ($files ?? [] as $file) {
            require_once $file;
        }
        foreach (get_declared_classes() ?? [] as $class) {
            if (strstr($class, self::$scanNamespace)) {
                self::parseAnnotation($class);
            }
        }
    }

    /**
     * 初始化参数
     */
    private static function init()
    {
        $config = config('yapi');
        self::$ip = $config['ip'];
        self::$token = $config['token'];
        self::$module = $config['module'];
        self::$scanPath = APP_PATH . '/modules/' . self::$module . '/action';
        self::$scanNamespace = "app\modules\\" . self::$module . '\\action';
        // 添加标签白名单 系统默认去除
        //author 和 var
        $whitelist = ['author', 'after', 'afterClass', 'backupGlobals', 'backupStaticAttributes', 'before', 'beforeClass', 'codeCoverageIgnore*', 'covers', 'coversDefaultClass', 'coversNothing', 'dataProvider', 'depends', 'doesNotPerformAssertions', 'expectedException', 'expectedExceptionCode', 'expectedExceptionMessage', 'expectedExceptionMessageRegExp', 'group', 'large', 'medium', 'preserveGlobalState', 'requires', 'runTestsInSeparateProcesses', 'runInSeparateProcess', 'small', 'test', 'testdox', 'testWith', 'ticket', 'uses'];
        foreach ($whitelist as $v) {
            AnnotationReader::addGlobalIgnoredName($v);
        }
        self::$reader = new AnnotationReader();
        //AnnotationRegistry::registerAutoloadNamespace(__NAMESPACE__);
    }

    /**
     * object 转 array
     */
    public static function objectToArray($obj)
    {
        $arr = [];
        $_arr = is_object($obj) ? get_object_vars($obj) : $obj;
        foreach ($_arr as $key => $val) {
            $val = (is_array($val)) || is_object($val) ? self::objectToArray($val) : $val;
            $arr[$key] = $val;
        }
        return $arr;
    }

    /**
     * @param $index
     * @param $class
     * @return array
     * @throws \ReflectionException
     */
    private static function parseAnnotation($class)
    {
        $refClass = new \ReflectionClass($class);
        try {
            $classAnnotation = self::$reader->getClassAnnotation($refClass, YController::class);
            if ($classAnnotation === null) {
                return [];
            }
            $refMethods = $refClass->getMethods();
            foreach ($refMethods ?? [] as $reflectionMethod) {
                // is not public
                if (! $reflectionMethod->isPublic()) {
                    continue;
                }
                $methodAnnotation = self::$reader->getMethodAnnotation($reflectionMethod, YMethod::class);
                if ($methodAnnotation === null) {
                    continue;
                }
                //解析参数
                $methodAnnotations = self::$reader->getMethodAnnotations($reflectionMethod);
                $params = [];
                foreach ($methodAnnotations ?? [] as $mAnnotation) {
                    if (! ($mAnnotation instanceof YParam)) {
                        continue;
                    }
                    $params[] = self::objectToArray($mAnnotation);
                }

                $actionName = $refClass->getName();
                $len = strlen(self::$scanNamespace) + 1;
                $path = '/' . self::$module . ('/' . str_replace('\\', '/', rtrim(substr($actionName, $len), 'Action') . '/' . $reflectionMethod->getName()));
                self::generateMethodDocs($path, $methodAnnotation->method, $methodAnnotation->name, $params, $methodAnnotation->desc);
            }
        } catch (\Exception $e) {
            var_dump($e->getMessage());
        }
    }

    /**
     * 递归扫描文件夹
     * @param string $dir
     * @return array
     */
    private static function scanAllFiles(string $dir)
    {
        $ret = [];
        $files = glob($dir . '/*');
        foreach ($files ?? [] as $file) {
            if (is_dir($file)) {
                $ret = array_merge($ret, self::scanAllFiles($file));
            } elseif (pathinfo($file)['extension'] === 'php') {
                $ret[] = $file;
            }
        }
        return $ret;
    }

    /**
     * 获取注解reader
     * @return AnnotationReader
     */
    private static function getAnnotationReader(): AnnotationReader
    {
        // 添加标签白名单 系统默认去除
        //author 和 var
        $whitelist = ['ValidRequire', 'author', 'after', 'afterClass', 'backupGlobals', 'backupStaticAttributes', 'before', 'beforeClass', 'codeCoverageIgnore*', 'covers', 'coversDefaultClass', 'coversNothing', 'dataProvider', 'depends', 'doesNotPerformAssertions', 'expectedException', 'expectedExceptionCode', 'expectedExceptionMessage', 'expectedExceptionMessageRegExp', 'group', 'large', 'medium', 'preserveGlobalState', 'requires', 'runTestsInSeparateProcesses', 'runInSeparateProcess', 'small', 'test', 'testdox', 'testWith', 'ticket', 'uses'];
        foreach ($whitelist as $v) {
            AnnotationReader::addGlobalIgnoredName($v);
        }
        $reader = new AnnotationReader();
        AnnotationRegistry::registerAutoloadNamespace("app\utils\annotations");
        return $reader;
    }

    /**
     * 生成ActionDocs
     * @param $index
     * @param $name
     * @param $desc
     * @return array
     */
    private static function generateActionDocs($index, $name, $desc = ''): array
    {
        return [
            'index' => $index,
            'name' => $name,
            'desc' => $desc,
            'add_time' => time(),
            'up_time' => time(),
            'list' => [],
        ];
    }

    /**
     * @param $path
     * @param $method
     * @param $name
     * @param array $params
     * @param string $desc
     * @return array
     * 生成method文档
     * @todo: 返回
     */
    private static function generateMethodDocs($path, $method, $name, $params = [], $desc = '')
    {
        $method = strtoupper($method);

        $res = [
            'token' => self::$token,
            'req_query' => [],
            'req_headers' => [
                ['name' => 'Content-Type']
            ],
            'req_body_form' => [],
            'title' => $name,
            'catid' => '1376',
            'path' => $path,
            'status' => 'undone',
            'res_body_type' => 'json',
            'res_body' => '',
            'switch_notice' => false,
            'message' => '',
            'desc' => $desc,
            'method' => $method,
            'req_params' => []
        ];
        //POST,DELETE,PUT....
        if ($method !== 'GET') {
            $res['req_body_type'] = 'form';
            $res['req_headers'] = [[

                'required' => '1',
                'name' => 'Content-Type',
                'value' => 'application/x-www-form-urlencoded'
            ]];
            $res['req_body_form'] = $params;
        } else {
            $res['req_query'] = $params;
        }
        $client = new Client([
            'base_uri' => self::$ip,
            'timeout' => 30.0,
        ]);
        $params = [];
        $params['json'] = $res;
        $response = $client->request('POST', '/api/interface/add', $params);
        $result = json_decode($response->getBody()->getContents());
        echo "接口{$path}文档写入结果: {$result->errmsg}";
    }
}