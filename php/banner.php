<?php
/*banner轮播图 */
require_once "conf.php";
if (!canUseIp()) {
    return;
}

if (empty($_REQUEST['action'])) {
    echo nullValuePrompt("action");
    return;
}

switch ($_REQUEST['action']) {
    case "addItem":
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


        if (empty($_POST['picture'])) {
            echo nullValuePrompt("picture");
            return;
        }

        if (empty($_POST['addTime'])) {
            echo nullValuePrompt("addTime");
            return;
        }

        if (empty($_POST['link'])) {
            echo nullValuePrompt("link");
            return;
        }
        createBanner($_POST['account'], $_POST['appId'], $_POST['title'], $_POST['picture'], $_POST['addTime'], $_POST['link']);
        break;
    case "getItems":
        getItems();
        break;
    case "clean":
        cleanBanner();
        break;
}

/*清理无用优惠券 */
function cleanBanner()
{
    $con = mysqli_connect(SERVERNAME, LOCALHOST, PASSWORD);
    mysqli_select_db($con, DATABASE_NAME);
    if (!$con) {
        echo createResponse(ERROR_CODE, "链接数据库出错。", null);
        return;
    } else {
        $now = date("Y-m-d H:i:s", time());
        $sql = "DELETE FROM " . DATABASE_NAME . ".`banner` WHERE expirationTime <= '" . $now . "'";
        $result = mysqli_query($con, $sql);
        if ($result) {
            $num = mysqli_affected_rows($con);
            if ($num > 0) {
                echo createResponse(SUCCESS_CODE, "已清理" . $num . "个过期轮播图", null);
            } else {
                echo createResponse(SUCCESS_CODE, "没有过期的轮播图", null);
            }
        } else {
            echo createResponse(ERROR_CODE, "清理失败", null);
        }
    }
    mysqli_close($con);
}

/**获取全部的轮播图 */
function getItems()
{
    $con = mysqli_connect(SERVERNAME, LOCALHOST, PASSWORD);
    mysqli_select_db($con, DATABASE_NAME);
    if (!$con) {
        echo createResponse(ERROR_CODE, "链接数据库出错。", null);
        return;
    } else {
        $total = array();
        $num = 0;
        $now = date("Y-m-d H:i:s", time());
        $sqlBanner = "SELECT * FROM " . DATABASE_NAME . ".`banner` WHERE expirationTime >= '" . $now . "'";
        $result = mysqli_query($con, $sqlBanner);
        if ($result != false && mysqli_num_rows($result) > 0) {
            while ($row = mysqli_fetch_assoc($result)) {
                $total[$num] = $row;
                $num++;
            }
            echo createResponse(SUCCESS_CODE, "获取成功，共" . $num . "条记录", $total);
        } else {
            echo createResponse(ERROR_CODE, "没有轮播图。", null);
        }
        if ($result != false) {
            mysqli_free_result($result);
        }
    }
    mysqli_close($con);
}

/**创建轮播图 */
function createBanner($account, $appId, $title, $picture, $addTime, $link)
{
    $con = mysqli_connect(SERVERNAME, LOCALHOST, PASSWORD);
    mysqli_select_db($con, DATABASE_NAME);
    if (!$con) {
        echo createResponse(ERROR_CODE, "链接数据库出错。", null);
        return;
    } else {
        $sqlUser = "SELECT * FROM " . DATABASE_NAME . ".`user` WHERE account='" . $account . "' AND appID='" . $appId . "'";
        $userResult = mysqli_query($con, $sqlUser);
        if (mysqli_num_rows($userResult) > 0) {
            $row = mysqli_fetch_assoc($userResult);
            $permission = $row['permission'];
            if ($permission == 1) {
                $nowTime = time();
                $createTime = date("Y-m-d H:i:s", $nowTime);
                $expirationTime = date("Y-m-d H:i:s", strtotime($addTime, $nowTime));
                $insertSql = "INSERT INTO " . DATABASE_NAME . ".`banner`(`title`, `owner`, `picture`, `link`, `createTime`, `expirationTime`) VALUES ('" . $title . "', '" . $account . "', '" . $picture . "', '" . $link . "', '" . $createTime . "','" . $expirationTime . "')";
                if (mysqli_query($con, $insertSql)) {
                    echo createResponse(SUCCESS_CODE, "添加成功。", null);
                } else {
                    echo createResponse(ERROR_CODE, "添加失败。", null);
                }
            } else {
                echo createResponse(ERROR_CODE, "您没有权限创建轮播图。", null);
            }
        } else {
            echo createResponse(ERROR_CODE, "找不到名为" . $account . "的用户或appId验证失败。", null);
        }
    }
    mysqli_close($con);
}
