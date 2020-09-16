<?php

namespace uban\notice;

use Exception;
use think\exception\Handle;
use think\facade\Config;
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
        // 请求异常
        if ($e instanceof Exception) {
            $response = $this->convertExceptionToResponse($e);
            $html = $response->getContent();
            $userConfig = new UbanMailUserConfig();
            $config = $userConfig->getMailConfig();
            $sendTo = Config::get("app.bug_mail_to");
            $title = Config::get("app.bug_notice_title");
            $sendTo = explode(",", $sendTo);
            foreach ($sendTo as $item) {
                UbanMail::sendMail($config, $title, $html, $item);
            }
        }
        // 其他错误交给系统处理
        return parent::render($e);
    }

}