<?php
/*错误收集 */
require_once "conf.php";
if (!canUseIp()) {
    return;
}

if (empty($_REQUEST['action'])) {
    echo nullValuePrompt("action");
    return;
}

switch ($_REQUEST['action']) {
    case "send":
        if (empty($_POST['message'])) {
            echo nullValuePrompt("message");
            return;
        }
        if (empty($_POST['versionName'])) {
            echo nullValuePrompt("versionName");
            return;
        }
        if (empty($_POST['versionNumber'])) {
            echo nullValuePrompt("versionNumber");
            return;
        }
        send($_POST['message'],$_POST['versionName'],$_POST['versionNumber']);
        break;
}

/*发送举报请求 */
function send($message, $versionName, $versionNumber)
{
    $con = mysqli_connect(SERVERNAME, LOCALHOST, PASSWORD);
    mysqli_select_db($con, DATABASE_NAME);
    if (!$con) {
        echo createResponse(ERROR_CODE, "链接数据库出错。", null);
        return;
    } else {
        $nowTime = time();
        $createTime = date("Y-m-d H:i:s", $nowTime);
        $sql = "INSERT INTO " . DATABASE_NAME . ".`error_record`(`message`, `time`,`versionName`,`versionNumber`) VALUES ('" . $message . "','" . $createTime . "', '" . $versionName . "', '" . $versionNumber . "')";
        if(mysqli_query($con, $sql))
        {
            echo createResponse(SUCCESS_CODE, "发送成功。", null);
        } else {
            echo createResponse(ERROR_CODE, "发送失败。", null);
        }
    }
    mysqli_close($con);
}
