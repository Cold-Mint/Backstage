<?php
/*公开APi */
require_once "conf.php";

if (!canUseIp()) {
    return;
}

if (empty($_REQUEST['action'])) {
    echo nullValuePrompt("action");
    return;
}

switch ($_REQUEST['action']) {
    case "getModList":
        /*
        sortMode 可选参数
        -latestTime 按更新时间倒序
        -downloadNumber 按下载数倒序
        */
        $sortMode = null;
        if (!empty($_GET['sortMode'])) {
            $sortMode = $_POST['sortMode'];
        }
        /*
    limit 可选参数
    指定返回数量
    */
        $limit = null;
        if (!empty($_GET['limit'])) {
            $limit = $_POST['limit'];
        }
        getList($sortMode, $limit);
        break;
    case "getModInfo":
        if (empty($_GET['modId'])) {
            echo nullValuePrompt("modId");
            return;
        }
        getInfo($_GET['modId']);
        break;
    case "addDownloadNumber":
        if (empty($_GET['modId'])) {
            echo nullValuePrompt("modId");
            return;
        }
        addDownloadNum($_GET['modId']);
        break;
    case "getCommentsList":
        if (empty($_GET['modId'])) {
            echo nullValuePrompt("modId");
            return;
        }
        commentsList($_GET['modId']);
        break;
    default:
        echo createResponse(ERROR_CODE, "错误的请求。", null);
}


/*加载评论列表 */
function commentsList($modId)
{
    $con = mysqli_connect(SERVERNAME, LOCALHOST, PASSWORD);
    mysqli_select_db($con, DATABASE_NAME);
    if (!$con) {
        echo createResponse(ERROR_CODE, "链接数据库出错。", null);
        return;
    } else {
        $total = array();
        $num = 0;
        $sqlMod = "SELECT content,time,account,id FROM " . DATABASE_NAME . ".`mod_comments` WHERE `modId`='" . $modId . "' ORDER BY id DESC";
        $result = mysqli_query($con, $sqlMod);
        if ($result != false && mysqli_num_rows($result) > 0) {
            while ($row = mysqli_fetch_assoc($result)) {
                $sql =  "SELECT userName,headIcon FROM " . DATABASE_NAME . ".`user` WHERE account='" . $row['account'] . "'";
                $result2 = mysqli_query($con, $sql);
                $userInfo = mysqli_fetch_assoc($result2);
                $end = $row;
                if ($userInfo != null) {
                    $end = array_merge($row, $userInfo);
                }
                $total[$num] = $end;
                $num++;
            }
            echo createResponse(SUCCESS_CODE, "获取成功，共" . $num . "条记录", $total);
        } else {
            echo createResponse(ERROR_CODE, "没有评论。", null);
        }
        if ($result != false) {
            mysqli_free_result($result);
        }
    }
}


/*获取模组信息 */
function getInfo($modId)
{
    $con = mysqli_connect(SERVERNAME, LOCALHOST, PASSWORD);
    mysqli_select_db($con, DATABASE_NAME);
    if (!$con) {
        echo createResponse(ERROR_CODE, "链接数据库出错。", null);
        return;
    } else {
        $sqlMod = "SELECT * FROM " . DATABASE_NAME . ".`mod` WHERE id='" . $modId . "' AND hidden = 0";
        $modResult = mysqli_query($con, $sqlMod);
        if (mysqli_num_rows($modResult) > 0) {
            $modRow = mysqli_fetch_assoc($modResult);
            echo createResponse(SUCCESS_CODE, "获取成功。", $modRow);
        } else {
            echo createResponse(ERROR_CODE, "找不到id为" . $modId . "的模组。", null);
        }
    }
}

/*获取模组列表(显示部分信息) */
function getList($sortMode, $limit)
{
    $con = mysqli_connect(SERVERNAME, LOCALHOST, PASSWORD);
    mysqli_select_db($con, DATABASE_NAME);
    if (!$con) {
        echo createResponse(ERROR_CODE, "链接数据库出错。", null);
        return;
    } else {
        $total = array();
        $num = 0;
        $sqlMod = "SELECT id,name,`describe`,icon,developer,downloadNumber,`updateTime` FROM " . DATABASE_NAME . ".`mod` WHERE `hidden`='0'";
        if ($sortMode != null) {
            if ($sortMode == "latestTime") {
                $sqlMod = $sqlMod . " ORDER BY updateTime DESC";
            } else if ($sortMode == "downloadNumber") {
                $sqlMod = $sqlMod . " ORDER BY downloadNumber DESC";
            }
        }
        if ($limit != null) {
            $sqlMod = $sqlMod . " LIMIT " . $limit;
        }
        $result = mysqli_query($con, $sqlMod);
        if ($result != false && mysqli_num_rows($result) > 0) {
            while ($row = mysqli_fetch_assoc($result)) {
                $total[$num] = $row;
                $num++;
            }
            echo createResponse(SUCCESS_CODE, "获取成功，共" . $num . "条记录", $total);
        } else {
            echo createResponse(ERROR_CODE, "没有模组。", null);
        }
        if ($result != false) {
            mysqli_free_result($result);
        }
    }
}


/*添加下载量 */
function addDownloadNum($modId)
{
    $con = mysqli_connect(SERVERNAME, LOCALHOST, PASSWORD);
    mysqli_select_db($con, DATABASE_NAME);
    if (!$con) {
        echo createResponse(ERROR_CODE, "链接数据库出错。", null);
        return;
    } else {
        $sqlMod = "SELECT * FROM " . DATABASE_NAME . ".`mod` WHERE id='" . $modId . "'";
        $modResult = mysqli_query($con, $sqlMod);
        if (mysqli_num_rows($modResult) > 0) {
            $row = mysqli_fetch_assoc($modResult);
            $num = $row['downloadNumber'];
            $num++;
            $addSql = "UPDATE " . DATABASE_NAME . ".`mod` SET `downloadNumber`=" . $num . " WHERE id='" . $modId . "'";
            if (mysqli_query($con, $addSql)) {
                echo createResponse(SUCCESS_CODE, "添加成功。", null);
            } else {
                echo createResponse(ERROR_CODE, "添加失败。", null);
            }
        } else {
            echo createResponse(ERROR_CODE, "找不到id为" . $modId . "的模组。", null);
        }
    }
}
