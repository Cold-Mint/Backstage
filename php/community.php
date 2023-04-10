<?php
/*社交系统(粉丝数，关注数，动态等等) */
require_once "conf.php";

if (!canUseIp()) {
    return;
}

if (empty($_REQUEST['action'])) {
    echo nullValuePrompt("action");
    return;
}


switch ($_REQUEST['action']) {
    case "follow":
        if (empty($_POST['account'])) {
            echo nullValuePrompt("account");
            return;
        }
        if (empty($_POST['targetAccount'])) {
            echo nullValuePrompt("targetAccount");
            return;
        }
        followUser($_POST['account'], $_POST['targetAccount']);
        break;
    case "removeFans":
        if (empty($_POST['account'])) {
            echo nullValuePrompt("account");
            return;
        }
        if (empty($_POST['targetAccount'])) {
            echo nullValuePrompt("targetAccount");
            return;
        }
        $needBan = false;
        if (!empty($_POST['needBan']) && $_POST['needBan'] == "true") {
            $needBan = true;
        }
        deFans($_POST['account'], $_POST['targetAccount'], $needBan);
        break;
    case "deFollow":
        if (empty($_POST['account'])) {
            echo nullValuePrompt("account");
            return;
        }
        if (empty($_POST['targetAccount'])) {
            echo nullValuePrompt("targetAccount");
            return;
        }
        deFollowUser($_POST['account'], $_POST['targetAccount']);
        break;
    case "getFollowState":
        if (empty($_POST['account'])) {
            echo nullValuePrompt("account");
            return;
        }
        if (empty($_POST['targetAccount'])) {
            echo nullValuePrompt("targetAccount");
            return;
        }
        getFollowState($_POST['account'], $_POST['targetAccount']);
        break;
    case "getList":
        if (empty($_POST['account'])) {
            echo nullValuePrompt("account");
            return;
        }
        if (empty($_POST['isFollowMode'])) {
            //是否为关注者模式(为false加载粉丝)
            echo nullValuePrompt("isFollowMode");
            return;
        }
        /*  
        limit 可选参数
            指定返回数量
        */
        $limit = null;
        if (!empty($_POST['limit'])) {
            $limit = $_POST['limit'];
        }
        getList($_POST['account'], $_POST['isFollowMode'], $limit);
        break;
}

/*获取关注列表*/
function getList($account, $isFollowMode, $limit)
{
    $con = mysqli_connect(SERVERNAME, LOCALHOST, PASSWORD);
    mysqli_select_db($con, DATABASE_NAME);
    if (!$con) {
        echo createResponse(ERROR_CODE, "链接数据库出错。", null);
        return;
    } else {
        $sql = "SELECT * FROM " . DATABASE_NAME . ".`follow_record`";
        $key = null;
        if ($isFollowMode == "true") {
            //加载偶像（你关注谁）
            $key = "account";
        } else {
            //加载粉丝(谁关注你)
            $key = "targetAccount";
        }
        $sql = $sql . " WHERE " . $key . "='" . $account . "' AND type = 'follow'";
        //按关注时间倒序
        $sql = $sql . " ORDER BY 'time' DESC";
        if ($limit != null) {
            $sql = $sql . " LIMIT " . $limit;
        }
        $show = "account";
        if ($isFollowMode == "true") {
            $show = "targetAccount";
        }
        $result = mysqli_query($con, $sql);
        if ($result != false && mysqli_num_rows($result) > 0) {
            $num = 0;
            while ($row = mysqli_fetch_assoc($result)) {
                $end = null;
                $sql2 =  "SELECT * FROM " . DATABASE_NAME . ".`community` WHERE account='" . $row[$show] . "'";
                $result2 = mysqli_query($con, $sql2);
                $userInfo = mysqli_fetch_assoc($result2);
                $sql3 =  "SELECT account,userName,headIcon,email,permission,loginTime,gender,enable,dynamicColor  FROM " . DATABASE_NAME . ".`user` WHERE account='" . $row[$show] . "'";
                $result3 = mysqli_query($con, $sql3);
                $userInfo2 = mysqli_fetch_assoc($result3);
                if ($userInfo != null && $userInfo2 != null) {
                    $end = array_merge($userInfo, $userInfo2);
                }
                $total[$num] = $end;
                $num++;
            }
            echo createResponse(SUCCESS_CODE, "获取成功，共" . $num . "条记录", $total);
        } else {
            echo createResponse(ERROR_CODE, "没有关注记录", null);
        }
    }
    mysqli_close($con);
}

/*关注状态 (谁，关注谁)*/
function getFollowState($account, $targetAccount)
{
    $con = mysqli_connect(SERVERNAME, LOCALHOST, PASSWORD);
    mysqli_select_db($con, DATABASE_NAME);
    if (!$con) {
        echo createResponse(ERROR_CODE, "链接数据库出错。", null);
        return;
    } else {
        $sql = "SELECT * FROM " . DATABASE_NAME . ".`community` WHERE account='" . $account . "'";
        $result = mysqli_query($con, $sql);
        if (mysqli_num_rows($result) > 0) {
            $row = mysqli_fetch_assoc($result);
            $tagSql = "SELECT * FROM " . DATABASE_NAME . ".`community` WHERE account='" . $targetAccount . "'";
            $resultSql = mysqli_query($con, $tagSql);
            if (mysqli_num_rows($resultSql) > 0) {
                $targetRow = mysqli_fetch_assoc($resultSql);
                //ta不让我关注
                $followSql3 = "SELECT * FROM " . DATABASE_NAME . ".`follow_record` WHERE account='" . $targetAccount . "' AND type = 'banFans' AND targetAccount='" . $account . "'";
                $followResult3 = mysqli_query($con, $followSql3);
                if (mysqli_num_rows($followResult3) > 0) {
                    echo createResponse(SUCCESS_CODE, "拒绝关注", "@event:拒绝关注");
                    return;
                }
                //我关注了ta
                $followSql = "SELECT * FROM " . DATABASE_NAME . ".`follow_record` WHERE account='" . $account . "' AND type = 'follow' AND targetAccount='" . $targetAccount . "'";
                $followResult = mysqli_query($con, $followSql);
                //ta关注了我
                $followSql2 = "SELECT * FROM " . DATABASE_NAME . ".`follow_record` WHERE account='" . $targetAccount . "' AND type = 'follow' AND targetAccount='" . $account . "'";
                $followResult2 = mysqli_query($con, $followSql2);
                if (mysqli_num_rows($followResult) > 0) {
                    if (mysqli_num_rows($followResult2) > 0) {
                        echo createResponse(SUCCESS_CODE, "已互粉", "@event:已互粉");
                    } else {
                        echo createResponse(SUCCESS_CODE, "已关注", "@event:已关注");
                    }
                } else {
                    echo createResponse(SUCCESS_CODE, "关注", "@event:关注");
                }
            } else {
                echo createResponse(ERROR_CODE, "找不到目标用户" . $account, null);
            }
        } else {
            echo createResponse(ERROR_CODE, "找不到用户" . $account, null);
        }
    }
    mysqli_close($con);
}

/*关注用户 (谁，关注谁)*/
function followUser($account, $targetAccount)
{
    $con = mysqli_connect(SERVERNAME, LOCALHOST, PASSWORD);
    mysqli_select_db($con, DATABASE_NAME);
    if (!$con) {
        echo createResponse(ERROR_CODE, "链接数据库出错。", null);
        return;
    } else {
        $sql = "SELECT * FROM " . DATABASE_NAME . ".`community` WHERE account='" . $account . "'";
        $result = mysqli_query($con, $sql);
        if (mysqli_num_rows($result) > 0) {
            $row = mysqli_fetch_assoc($result);
            $tagSql = "SELECT * FROM " . DATABASE_NAME . ".`community` WHERE account='" . $targetAccount . "'";
            $resultSql = mysqli_query($con, $tagSql);
            if (mysqli_num_rows($resultSql) > 0) {
                $targetRow = mysqli_fetch_assoc($resultSql);
                $followSql = "SELECT * FROM " . DATABASE_NAME . ".`follow_record` WHERE account='" . $account . "' AND type = 'follow' AND targetAccount='" . $targetAccount . "'";
                $followResult = mysqli_query($con, $followSql);
                if (mysqli_num_rows($followResult) > 0) {
                    echo createResponse(ERROR_CODE, "不能重复关注用户" . $account, null);
                } else {
                    $blacklist = "SELECT * FROM " . DATABASE_NAME . ".`follow_record` WHERE account='" . $targetAccount . "' AND type = 'banFans' AND targetAccount='" . $account . "'";
                    $blacklistResult = mysqli_query($con, $blacklist);
                    if (mysqli_num_rows($blacklistResult) > 0) {
                        echo createResponse(ERROR_CODE, "ta拒绝了您的关注。", null);
                        return;
                    }

                    $nowTime = time();
                    $createTime = date("Y-m-d H:i:s", $nowTime);
                    $insertFollowRecord = "INSERT INTO " . DATABASE_NAME . ".`follow_record` (account,type,targetAccount,time) VALUES('" . $account . "','follow','" . $targetAccount . "','" . $createTime . "')";
                    $newFollwerNum = $row['follower'];
                    $newFollwerNum++;
                    $newFansNum = $targetRow['fans'];
                    $newFansNum++;
                    $addFans = "UPDATE " . DATABASE_NAME . ".`community` SET `fans` = '" . $newFansNum . "' WHERE `account` = '" . $targetAccount . "'";
                    $addFollwer = "UPDATE " . DATABASE_NAME . ".`community` SET `follower` = '" . $newFollwerNum . "' WHERE `account` = '" . $account . "'";
                    mysqli_query($con, $addFans);
                    mysqli_query($con, $addFollwer);
                    $end = mysqli_query($con, $insertFollowRecord);
                    if ($end) {
                        echo createResponse(SUCCESS_CODE, "关注成功", null);
                    } else {
                        echo createResponse(ERROR_CODE, $insertFollowRecord, null);
                    }
                }
            } else {
                echo createResponse(ERROR_CODE, "找不到目标用户" . $account, null);
            }
        } else {
            echo createResponse(ERROR_CODE, "找不到用户" . $account, null);
        }
    }
    mysqli_close($con);
}


/*移除粉丝(主人号，目标账号) */
function deFans($account, $targetAccount, $needBan)
{
    $con = mysqli_connect(SERVERNAME, LOCALHOST, PASSWORD);
    mysqli_select_db($con, DATABASE_NAME);
    if (!$con) {
        echo createResponse(ERROR_CODE, "链接数据库出错。", null);
        return;
    } else {
        $sql = "SELECT * FROM " . DATABASE_NAME . ".`community` WHERE account='" . $account . "'";
        $result = mysqli_query($con, $sql);
        if (mysqli_num_rows($result) > 0) {
            $row = mysqli_fetch_assoc($result);
            $tagSql = "SELECT * FROM " . DATABASE_NAME . ".`community` WHERE account='" . $targetAccount . "'";
            $resultSql = mysqli_query($con, $tagSql);
            if (mysqli_num_rows($resultSql) > 0) {
                $targetRow = mysqli_fetch_assoc($resultSql);
                //目标账号是否关注了自己
                $followSql = "SELECT * FROM " . DATABASE_NAME . ".`follow_record` WHERE account='" . $targetAccount . "' AND type = 'follow' AND targetAccount='" . $account . "'";
                $followResult = mysqli_query($con, $followSql);
                if (mysqli_num_rows($followResult) > 0) {
                    $nowTime = time();
                    $createTime = date("Y-m-d H:i:s", $nowTime);
                    $delFollowRecord = "DELETE FROM " . DATABASE_NAME . ".`follow_record` WHERE account='" . $targetAccount . "' AND type = 'follow' AND targetAccount='" . $account . "'";
                    $newFollwerNum = $targetRow['follower'];
                    $newFollwerNum--;
                    $newFansNum = $row['fans'];
                    $newFansNum--;
                    $addFans = "UPDATE " . DATABASE_NAME . ".`community` SET `fans` = '" . $newFansNum . "' WHERE `account` = '" . $account . "'";
                    $addFollwer = "UPDATE " . DATABASE_NAME . ".`community` SET `follower` = '" . $newFollwerNum . "' WHERE `account` = '" . $targetAccount . "'";
                    mysqli_query($con, $addFans);
                    mysqli_query($con, $addFollwer);
                    mysqli_query($con, $delFollowRecord);
                    if ($needBan == true) {
                        $insertFollowRecord = "INSERT INTO " . DATABASE_NAME . ".`follow_record` (account,type,targetAccount,time) VALUES('" . $account . "','banFans','" . $targetAccount . "','" . $createTime . "')";
                        mysqli_query($con, $insertFollowRecord);
                    }
                    echo createResponse(SUCCESS_CODE, "移除关注成功", null);
                } else {
                    echo createResponse(ERROR_CODE, "移除粉丝失败，ta从来没有关注过您", null);
                }
            } else {
                echo createResponse(ERROR_CODE, "找不到目标用户" . $account, null);
            }
        } else {
            echo createResponse(ERROR_CODE, "找不到用户" . $account, null);
        }
    }
    mysqli_close($con);
}

/*取消关注 (谁，关注谁)*/
function deFollowUser($account, $targetAccount)
{
    $con = mysqli_connect(SERVERNAME, LOCALHOST, PASSWORD);
    mysqli_select_db($con, DATABASE_NAME);
    if (!$con) {
        echo createResponse(ERROR_CODE, "链接数据库出错。", null);
        return;
    } else {
        $sql = "SELECT * FROM " . DATABASE_NAME . ".`community` WHERE account='" . $account . "'";
        $result = mysqli_query($con, $sql);
        if (mysqli_num_rows($result) > 0) {
            $row = mysqli_fetch_assoc($result);
            $tagSql = "SELECT * FROM " . DATABASE_NAME . ".`community` WHERE account='" . $targetAccount . "'";
            $resultSql = mysqli_query($con, $tagSql);
            if (mysqli_num_rows($resultSql) > 0) {
                $targetRow = mysqli_fetch_assoc($resultSql);
                $followSql = "SELECT * FROM " . DATABASE_NAME . ".`follow_record` WHERE account='" . $account . "' AND type = 'follow' AND targetAccount='" . $targetAccount . "'";
                $followResult = mysqli_query($con, $followSql);
                if (mysqli_num_rows($followResult) > 0) {
                    $nowTime = time();
                    $createTime = date("Y-m-d H:i:s", $nowTime);
                    $delFollowRecord = "DELETE FROM " . DATABASE_NAME . ".`follow_record` WHERE account='" . $account . "' AND type = 'follow' AND targetAccount='" . $targetAccount . "'";
                    $newFollwerNum = $row['follower'];
                    $newFollwerNum--;
                    $newFansNum = $targetRow['fans'];
                    $newFansNum--;
                    $addFans = "UPDATE " . DATABASE_NAME . ".`community` SET `fans` = '" . $newFansNum . "' WHERE `account` = '" . $targetAccount . "'";
                    $addFollwer = "UPDATE " . DATABASE_NAME . ".`community` SET `follower` = '" . $newFollwerNum . "' WHERE `account` = '" . $account . "'";
                    mysqli_query($con, $addFans);
                    mysqli_query($con, $addFollwer);
                    mysqli_query($con, $delFollowRecord);
                    echo createResponse(SUCCESS_CODE, "取消关注成功", null);
                } else {
                    echo createResponse(ERROR_CODE, "取消关注失败，您从来没有关注过ta", null);
                }
            } else {
                echo createResponse(ERROR_CODE, "找不到目标用户" . $account, null);
            }
        } else {
            echo createResponse(ERROR_CODE, "找不到用户" . $account, null);
        }
    }
    mysqli_close($con);
}
