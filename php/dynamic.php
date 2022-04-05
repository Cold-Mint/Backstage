<?php
/* 发布动态 */
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
        if (empty($_POST['account'])) {
            echo nullValuePrompt("account");
            return;
        }

        if (empty($_POST['appId'])) {
            echo nullValuePrompt("appId");
            return;
        }

        if (empty($_POST['context'])) {
            echo nullValuePrompt("context");
            return;
        }
        send($_POST['account'], $_POST['appId'], $_POST['context']);
        break;
    case "list":
        $account = null;
        if (!empty($_POST['account'])) {
            $account = $_POST['account'];
        }
        $limit = null;
        if (!empty($_POST['limit'])) {
            $limit = $_POST['limit'];
        }
        getlist($account, $limit);
        break;
    case "delete":
        if (empty($_POST['account'])) {
            echo nullValuePrompt("account");
            return;
        }

        if (empty($_POST['appId'])) {
            echo nullValuePrompt("appId");
            return;
        }

        if (empty($_POST['id'])) {
            echo nullValuePrompt("id");
            return;
        }
        delete($_POST['account'], $_POST['appId'], $_POST['id']);
        break;
    case "getAllDynamic":
        if (empty($_POST['account'])) {
            echo nullValuePrompt("account");
            return;
        }
        $limit = null;
        if (!empty($_POST['limit'])) {
            $limit = $_POST['limit'];
        }
        getAllDynamic($_POST['account'], $limit);
}

/*获取全部动态 */
function getAllDynamic($account, $limit)
{
    $con = mysqli_connect(SERVERNAME, LOCALHOST, PASSWORD);
    mysqli_select_db($con, DATABASE_NAME);
    if (!$con) {
        echo createResponse(ERROR_CODE, "链接数据库出错。", null);
        return;
    } else {
        $sql = "SELECT * FROM " . DATABASE_NAME . ".`follow_record` WHERE account='" . $account . "'";
        $result = mysqli_query($con, $sql);
        if ($result != false && mysqli_num_rows($result) > 0) {
            $newSql = "SELECT * FROM " . DATABASE_NAME . ".`dynamic`";
            $num = 0;
            while ($row = mysqli_fetch_assoc($result)) {
                if ($num == 0) {
                    $newSql = $newSql . " WHERE account='" . $row['targetAccount'] . "'";
                } else {
                    $newSql = $newSql . " OR account='" . $row['targetAccount'] . "'";
                }
                $num++;
            }
            $newSql = $newSql . " ORDER BY time DESC";
            if ($limit != null) {
                $newSql = $newSql . " LIMIT " . $limit;
            }
            $result2 = mysqli_query($con, $newSql);
            if ($result2 != false && mysqli_num_rows($result2) > 0) {
                $total = array();
                $num2 = 0;
                while ($row2 = mysqli_fetch_assoc($result2)) {
                    $sql2 =  "SELECT account,userName,headIcon,email,permission,loginTime,gender,`enable` FROM " . DATABASE_NAME . ".`user` WHERE account='" . $row2['account'] . "' AND AND visible='true'";
                    $result3 = mysqli_query($con, $sql2);
                    if($result3!=false)
                    {
                        $row3 = mysqli_fetch_assoc($result3);
                        $total[$num2] = array_merge($row2, $row3);
                    }
                    $num2++;
                }
                echo createResponse(SUCCESS_CODE, "获取成功，共" . $num2 . "条记录", $total);
            } else {
                echo createResponse(ERROR_CODE, "没有动态", null);
            }
        } else {
            echo createResponse(ERROR_CODE, "没有关注记录", null);
        }
    }
    mysqli_close($con);
}

/*删除动态 */
function delete($account, $appId, $id)
{
    $con = mysqli_connect(SERVERNAME, LOCALHOST, PASSWORD);
    mysqli_select_db($con, DATABASE_NAME);
    if (!$con) {
        echo createResponse(ERROR_CODE, "链接数据库出错。", null);
        return;
    } else {
        $sql = "SELECT * FROM " . DATABASE_NAME . ".`dynamic` WHERE id='" . $id . "'";
        $result = mysqli_query($con, $sql);
        if (mysqli_num_rows($result) > 0) {
            $row = mysqli_fetch_assoc($result);
            $visible = $row['visible'];
            if ($visible == "true") {
                $publisher = $row['account'];
                $sqlUser = "SELECT * FROM " . DATABASE_NAME . ".`user` WHERE account='" . $account . "' AND appID= '" . $appId . "'";
                $userResult = mysqli_query($con, $sqlUser);
                if ($userResult != false && mysqli_num_rows($userResult) > 0) {
                    $userRow = mysqli_fetch_assoc($userResult);
                    $permission = $userRow['permission'];
                    if ($permission < 3) {
                        //是管理员,删除动态
                        $deleteSql = "UPDATE " . DATABASE_NAME . ".`dynamic` SET `visible` = 'false' WHERE `id` = '" . $id . "'";
                        if (mysqli_query($con, $deleteSql)) {
                            echo createResponse(SUCCESS_CODE, "删除成功。", null);
                        } else {
                            echo createResponse(ERROR_CODE, "删除失败。", null);
                        }
                    } else {
                        //是普通用户
                        if ($account == $publisher) {
                            //是同一个用户，可以删除
                            $deleteSql = "UPDATE " . DATABASE_NAME . ".`dynamic` SET `visible` = 'false' WHERE `id` = '" . $id . "'";
                            if (mysqli_query($con, $deleteSql)) {
                                echo createResponse(SUCCESS_CODE, "删除成功。", null);
                            } else {
                                echo createResponse(ERROR_CODE, "删除失败。", null);
                            }
                        } else {
                            echo createResponse(ERROR_CODE, "您无权删除动态。", null);
                        }
                    }
                } else {
                    //找不到
                    echo createResponse(ERROR_CODE, "找不到用户或appId验证失败。", null);
                }
            } else {
                echo createResponse(ERROR_CODE, "此动态已被删除", null);
            }
        } else {
            echo createResponse(ERROR_CODE, "找不到动态", null);
        }
    }
    mysqli_close($con);
}

/*发送动态 */
function send($account, $appId, $context)
{
    $con = mysqli_connect(SERVERNAME, LOCALHOST, PASSWORD);
    mysqli_select_db($con, DATABASE_NAME);
    if (!$con) {
        echo createResponse(ERROR_CODE, "链接数据库出错。", null);
        return;
    } else {
        $sql = "SELECT * FROM " . DATABASE_NAME . ".`user` WHERE account='" . $account . "' AND appID='" . $appId . "'";
        $result = mysqli_query($con, $sql);
        if (mysqli_num_rows($result) > 0) {
            $nowTime = time();
            $createTime = date("Y-m-d H:i:s", $nowTime);
            $sql2 = "INSERT INTO " . DATABASE_NAME . ".`dynamic` (account,content,time) VALUES ('" . $account . "','" . $context . "','" . $createTime . "')";
            if (mysqli_query($con, $sql2)) {
                echo createResponse(SUCCESS_CODE, "发布成功", null);
            } else {
                echo createResponse(ERROR_CODE, "发布失败", null);
            }
        } else {
            echo createResponse(ERROR_CODE, "找不到用户或appID验证失败。", null);
        }
    }
    mysqli_close($con);
}

/*获取列表(账号,限制返回数量) */
function getList($account, $limit)
{
    $con = mysqli_connect(SERVERNAME, LOCALHOST, PASSWORD);
    mysqli_select_db($con, DATABASE_NAME);
    $sql = "SELECT * FROM " . DATABASE_NAME . ".`dynamic`";
    if ($account != null) {
        $sql = $sql . "WHERE account='" . $account . "'";
    }
    $sql = $sql . "AND visible='true' ORDER BY time DESC";
    if ($limit != null) {
        $sql = $sql . " LIMIT " . $limit;
    }
    $result = mysqli_query($con, $sql);
    if ($result != false && mysqli_num_rows($result) > 0) {
        $num = 0;
        while ($row = mysqli_fetch_assoc($result)) {
            $sql2 =  "SELECT account,userName,headIcon,email,permission,loginTime,gender,enable FROM " . DATABASE_NAME . ".`user` WHERE account='" . $row['account'] . "'";
            $result2 = mysqli_query($con, $sql2);
            $row2 = mysqli_fetch_assoc($result2);
            $total[$num] = array_merge($row, $row2);
            $num++;
        }
        echo createResponse(SUCCESS_CODE, "获取成功，共" . $num . "条记录", $total);
    } else {
        echo createResponse(ERROR_CODE, "没有动态", null);
    }
    mysqli_close($con);
}
