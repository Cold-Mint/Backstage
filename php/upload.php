<?php
/*上传系统 */
require_once "conf.php";

if (!canUseIp()) {
    return;
}

if (empty($_REQUEST['action'])) {
    echo nullValuePrompt("action");
    return;
}


switch ($_REQUEST['action']) {
    case "head":
        if (empty($_POST['account'])) {
            echo nullValuePrompt("account");
            return;
        }
        if (empty($_FILES['headIcon'])) {
            echo nullValuePrompt("headIcon");
            return;
        }
        if ($_FILES['headIcon']["size"] > 3145728) {
            echo createResponse(ERROR_CODE, "头像不能大于3MB。", null);
            return;
        }

        if (strpos($_FILES['headIcon']["type"], "image") !== 0) {
            echo createResponse(ERROR_CODE, "未知的文件类型" . $_FILES['headIcon']["type"], null);
            return;
        }
        uploadHead($_POST['account'], $_FILES['headIcon']);
        break;
}

/*上传头像 */
function uploadHead($account, $headIcon)
{
    $con = mysqli_connect(SERVERNAME, LOCALHOST, PASSWORD);
    mysqli_select_db($con, DATABASE_NAME);
    if (!$con) {
        echo createResponse(ERROR_CODE, "链接数据库出错。", null);
        return;
    } else {
        $sqlUser = "SELECT * FROM " . DATABASE_NAME . ".`user` WHERE account='" . $account . "'";
        $userResult = mysqli_query($con, $sqlUser);
        if (mysqli_num_rows($userResult) > 0) {
            $folder = "../user/" . iconv("UTF-8", "GBK", $account);
            if (!file_exists($folder)) {
                mkdir($folder, 0777, true);
            }
            $newIcon = $folder . "/icon.png";
            $move =  move_uploaded_file($headIcon["tmp_name"], $newIcon);
            if ($move) {
                echo createResponse(ERROR_CODE, "上传成功。", null);
            } else {
                echo createResponse(ERROR_CODE, "上传失败。", null);
            }
        } else {
            echo createResponse(ERROR_CODE, "找不到名为" . $account . "的用户。", null);
        }
    }
}
