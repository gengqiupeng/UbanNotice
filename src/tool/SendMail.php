<?php
/**
 * Created by IntelliJ IDEA.
 * User: beili
 * Date: 19-2-28
 * Time: 上午8:59
 */

namespace app\command;

use app\submit\service\SubmitLogService;
use app\submit\service\SubmitMailService;
use app\submit\service\SubmitService;
use PHPMailer\PHPMailer\PHPMailer;
use think\cache\driver\Redis;
use think\console\Command;
use think\console\Input;
use think\console\input\Argument;
use think\console\Output;
use think\facade\Cache;
use think\facade\Db;
use uban\errorCode\Constant;
use uban\mail\UbanMail;

class SendMail extends Command
{
    protected $days;
    protected $commandService;

    protected function configure()
    {
        $this
            ->setName("sendMail")
            ->setDescription("后台发送邮件")
            ->addArgument("redis_id", Argument::REQUIRED, "输入邮件ID");;
    }

    protected function execute(Input $input, Output $output)
    {
        $redis_id = $input->getArgument('redis_id');
        $redis = Cache::store('redis')->handler();
        $data = $redis->get($redis_id);
        $data = unserialize($data);
        UbanMail::sendMail($data['config'], $data['title'], $data['html'], $data['emailAddress']);
    }
}