<?php
/*App更新系统 */
require_once "conf.php";

if (empty($_REQUEST['action'])) {
    echo nullValuePrompt("action");
    return;
}

switch ($_REQUEST['action']) {
    case "addUpdate":
        if (empty($_POST['account'])) {
            echo nullValuePrompt("account");
            return;
        }
        if (empty($_POST['appId'])) {
            echo nullValuePrompt("appId");
            return;
        }
        if (empty($_POST['title'])) {
            echo nullValuePrompt("title");
            return;
        }
        if (empty($_POST['content'])) {
            echo nullValuePrompt("content");
            return;
        }
        if (empty($_POST['isBeta'])) {
            echo nullValuePrompt("isBeta");
            return;
        }
        if (empty($_POST['versionNumber'])) {
            echo nullValuePrompt("versionNumber");
            return;
        }
        if (empty($_POST['versionName'])) {
            echo nullValuePrompt("versionName");
            return;
        }
        if (empty($_POST['forced'])) {
            echo nullValuePrompt("forced");
            return;
        }
        if (empty($_POST['link'])) {
            echo nullValuePrompt("link");
            return;
        }
        addUpdate($_POST['account'], $_POST['appId'], $_POST['title'], $_POST['content'], $_POST['isBeta'], $_POST['versionNumber'], $_POST['versionName'], $_POST['forced'], $_POST['link']);
        break;
    case "getUpdate":
        getUpdate();
        break;
    case "getAllUpdate":
        getAllUpdate();
        break;
}


function getUpdate()
{
    $con = mysqli_connect(SERVERNAME, LOCALHOST, PASSWORD);
    mysqli_select_db($con, DATABASE_NAME);
    if (!$con) {
        echo createResponse(ERROR_CODE, "链接数据库出错。", null);
        return;
    } else {
        $sql = "SELECT * FROM " . DATABASE_NAME . ".`app_update` ORDER BY id DESC LIMIT 1";
        $result = mysqli_query($con, $sql);
        if (mysqli_num_rows($result) > 0) {
            $row = mysqli_fetch_assoc($result);
            echo createResponse(SUCCESS_CODE, "获取成功", $row);
        } else {
            echo createResponse(ERROR_CODE, "没有APP更新记录", null);
        }
    }
}

/**
 * 获取全部的程序更新记录
 * @return void
 */
function getAllUpdate()
{
    $con = mysqli_connect(SERVERNAME, LOCALHOST, PASSWORD);
    mysqli_select_db($con, DATABASE_NAME);
    if (!$con) {
        echo createResponse(ERROR_CODE, "链接数据库出错。", null);
        return;
    } else {
        $sql = "SELECT * FROM " . DATABASE_NAME . ".`app_update` ORDER BY id DESC";
        $result = mysqli_query($con, $sql);
        if (mysqli_num_rows($result) > 0) {
            $total = array();
            $num = 0;
            while ($row = mysqli_fetch_assoc($result)) {
                $total[$num] = $row;
                $num++;
            }
            echo createResponse(SUCCESS_CODE, "App更新记录(" . $num . ")", $total);
        } else {
            echo createResponse(ERROR_CODE, "没有APP更新记录", null);
        }
    }
}

/*添加更新记录 */
function addUpdate($account, $appId, $title, $content, $isBeta, $versionNumber, $versionName, $forced, $link)
{
    $con = mysqli_connect(SERVERNAME, LOCALHOST, PASSWORD);
    mysqli_select_db($con, DATABASE_NAME);
    if (!$con) {
        echo createResponse(ERROR_CODE, "链接数据库出错。", null);
        return;
    } else {
        $sqlUser = "SELECT * FROM " . DATABASE_NAME . ".`user` WHERE `account`='" . $account . "' AND appID='" . $appId . "'";
        $userResult = mysqli_query($con, $sqlUser);
        if (mysqli_num_rows($userResult) > 0) {
            $row = mysqli_fetch_assoc($userResult);
            $permission = $row['permission'];
            if ($permission == 1) {
                $now = time();
                $createTime = date("Y-m-d H:i:s", $now);
                $insertSql = "INSERT INTO " . DATABASE_NAME . ".`app_update`(`title`, `content`, `isBeta`, `versionNumber`, `versionName`, `forced`, `link`, `time`) VALUES ('" . $title . "', '" . $content . "', '" . $isBeta . "', '" . $versionNumber . "', '" . $versionName . "', '" . $forced . "', '" . $link . "', '" . $createTime . "')";
                $ok = mysqli_query($con, $insertSql);
                if ($ok) {
                    echo createResponse(SUCCESS_CODE, "发布成功", null);
                } else {
                    echo createResponse(SUCCESS_CODE, "发布失败", null);
                }
            } else {
                echo createResponse(ERROR_CODE, "您无权发布App更新。", null);
            }
        } else {
            echo createResponse(ERROR_CODE, "找不到名为" . $account . "的用户，或appid验证错误。", null);
        }
        mysqli_free_result($userResult);
    }
    mysqli_close($con);
}
