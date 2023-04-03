<?php
/*邮箱操作 */

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require_once '../PHPMailer-6.5.1/src/Exception.php';
require_once '../PHPMailer-6.5.1/src/PHPMailer.php';
require_once '../PHPMailer-6.5.1/src/SMTP.php';

require_once "conf.php";


if (empty($_REQUEST['action'])) {
    echo nullValuePrompt("action");
    return;
}

switch ($_REQUEST['action']) {
    case "send":
        if (empty($_POST['address'])) {
            echo nullValuePrompt("address");
            return;
        }
        if (empty($_POST['title'])) {
            echo nullValuePrompt("title");
            return;
        }
        if (empty($_POST['bodyHtml'])) {
            echo nullValuePrompt("bodyHtml");
            return;
        }
        send($_POST['address'], $_POST['title'], $_POST['bodyHtml'], true);
        break;
        //default:
        //echo createResponse(ERROR_CODE, "未知的活动" . $_REQUEST['action'], null);
}

/**
 * 发送邮件
 * @param $address 邮箱
 * @param $title 标题
 * @param $bodyHtml 内容（Html）
 * @param $needEcho 是否需要输出
 * @return bool 是否发送成功
 */
function send($address, $title, $bodyHtml, $needEcho)
{
    $mail = new PHPMailer(true);                              //传递' true '将启用异常
    try {
        //服务器配置
        $mail->CharSet = "UTF-8";                     //设定邮件编码
        $mail->SMTPDebug = 0;                        // 调试模式输出
        $mail->isSMTP();                             // 使用SMTP
        $mail->Host = 'smtp.qq.com';                // SMTP服务器
        $mail->SMTPAuth = true;                      // 允许 SMTP 认证
        $mail->Username = 'rust_helper@qq.com';                // SMTP 用户名  即邮箱的用户名
        $mail->Password = 'SMTP';             // SMTP 密码  部分邮箱是授权码(例如163邮箱)
        $mail->SMTPSecure = 'ssl';                    // 允许 TLS 或者ssl协议
        $mail->Port = 465;                            // 服务器端口 25 或者465 具体要看邮箱服务器支持

        $mail->setFrom('rust_helper@qq.com', '铁锈助手');  //发件人
        $mail->addAddress($address, 'Joe');  // 收件人
        //$mail->addAddress('ellen@example.com');  // 可添加多个收件人
        $mail->addReplyTo('rust_helper@qq.com', 'info'); //回复的时候回复给哪个邮箱 建议和发件人一致
        //$mail->addCC('cc@example.com');                    //抄送
        //$mail->addBCC('bcc@example.com');                    //密送

        //发送附件
        // $mail->addAttachment('../xy.zip');         // 添加附件
        // $mail->addAttachment('../thumb-1.jpg', 'new.jpg');    // 发送附件并且重命名

        //Content
        $mail->isHTML(true);                                  // 是否以HTML文档格式发送  发送后客户端可直接显示对应HTML内容
        $mail->Subject = $title;
        $mail->Body = $bodyHtml;
        $mail->AltBody = $bodyHtml;

        $mail->send();
        if ($needEcho == true) {
            echo createResponse(SUCCESS_CODE, "邮件发送成功", null);
        }
        return true;
    } catch (Exception $e) {
        if ($needEcho == true) {
            echo createResponse(ERROR_CODE, "邮件发送失败", $mail->ErrorInfo);
        }
        return false;
    }
}
