<?php

namespace uban\notice;

use Exception;
use think\Container;
use think\exception\Handle;
use think\facade\App;
use think\facade\Config;
use think\Response;
use uban\mail\UbanMail;
use uban\mail\UbanMailUserConfig;

class MailNotice extends Handle
{
    /**
     * @param Exception $e
     * @return \think\Response|\think\response\Json
     */
    public function render(Exception $e)
    {
        if (App::isDebug()) {
            return parent::render($e);
        }
        // 请求异常
        if ($e instanceof Exception) {

            // 调试模式，获取详细的错误信息
            $data = [
                'name'    => get_class($e),
                'file'    => $e->getFile(),
                'line'    => $e->getLine(),
                'message' => $this->getMessage($e),
                'trace'   => $e->getTrace(),
                'code'    => $this->getCode($e),
                'source'  => $this->getSourceCode($e),
                'datas'   => $this->getExtendData($e),
                'tables'  => [
                    'GET Data'              => $_GET,
                    'POST Data'             => $_POST,
                    'Files'                 => $_FILES,
                    'Cookies'               => $_COOKIE,
                    'Session'               => isset($_SESSION) ? $_SESSION : [],
                    'Server/Request Data'   => $_SERVER,
                    'Environment Variables' => $_ENV,
                    'ThinkPHP Constants'    => $this->ubanGetConst(),
                ],
            ];

            //保留一层
            while (ob_get_level() > 1) {
                ob_end_clean();
            }

            $data['echo'] = ob_get_clean();

            ob_start();
            extract($data);
            include "exception.html";

            // 获取并清空缓存
            $content = ob_get_clean();
            $userConfig = new UbanMailUserConfig();
            $config = $userConfig->getMailConfig();
            $sendTo = Config::get("app.bug_mail_to");
            $title = Config::get("app.bug_notice_title");
            $sendTo = explode(",", $sendTo);
            foreach ($sendTo as $item) {
                UbanMail::sendMail($config, $title, $content, $item);
            }
        }
        // 其他错误交给系统处理
        return parent::render($e);
    }

    /**
     * 获取常量列表
     * @return array 常量列表
     */
    protected function ubanGetConst()
    {
        return get_defined_constants(true)['user'];
    }
}