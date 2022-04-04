<?php
/*举报系统 */
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
        if (empty($_POST['type'])) {
            echo nullValuePrompt("type");
            return;
        }
        if ($_POST['type'] != "user" && $_POST['type'] != "mod") {
            echo createResponse(ERROR_CODE, "未知的举报类型，举报类型只能是user或mod", null);
            return;
        }
        if (empty($_POST['target'])) {
            echo nullValuePrompt("target");
            return;
        }
        if (empty($_POST['why'])) {
            echo nullValuePrompt("why");
            return;
        }
        if (empty($_POST['describe'])) {
            echo nullValuePrompt("describe");
            return;
        }
        send($_POST['account'], $_POST['type'], $_POST['target'], $_POST['why'], $_POST['describe']);
        break;
    case "list":
        loadList();
        break;
    case "dispose":
        if (empty($_POST['account'])) {
            echo nullValuePrompt("account");
            return;
        }
        if (empty($_POST['id'])) {
            echo nullValuePrompt("id");
            return;
        }
        if (empty($_POST['state'])) {
            echo nullValuePrompt("state");
            return;
        }
        disposeReport($_POST['account'], $_POST['id'], $_POST['state']);
        break;
}

/*处理举报 */
function disposeReport($account, $id, $state)
{
    $con = mysqli_connect(SERVERNAME, LOCALHOST, PASSWORD);
    mysqli_select_db($con, DATABASE_NAME);
    if (!$con) {
        echo createResponse(ERROR_CODE, "链接数据库出错。", null);
        return;
    } else {
        $sqlUser = "SELECT * FROM " . DATABASE_NAME . ".`user` WHERE `account`='" . $account . "'";
        $userResult = mysqli_query($con, $sqlUser);
        if (mysqli_num_rows($userResult) > 0) {
            $row = mysqli_fetch_assoc($userResult);
            $permission = $row['permission'];
            if ($permission < 3) {
                $sqlReport = "SELECT * FROM " . DATABASE_NAME . ".`report_record` WHERE `id`='" . $id . "' AND `state`='1' ORDER BY id DESC";
                $reportResult = mysqli_query($con, $sqlReport);
                if (mysqli_num_rows($reportResult) > 0) {
                    $stateCode = -1;
                    if ($state == "true") {
                        $row2 = mysqli_fetch_assoc($reportResult);
                        $type = $row2['type'];
                        $modId = $row2['target'];
                        if ($type == "mod") {
                            //模组设置下架
                            $modSql = "SELECT * FROM " . DATABASE_NAME . ".`mod` WHERE `id`='" . $modId . "'";
                            $modResult = mysqli_query($con, $modSql);
                            if (mysqli_num_rows($modResult) > 0) {
                                $updata = "UPDATE " . DATABASE_NAME . ".`mod` SET `hidden` = '-2' WHERE `id` = '" . $modId . "'";
                                if (mysqli_query($con, $updata)) {
                                    echo createResponse(SUCCESS_CODE, "修改成功", null);
                                } else {
                                    echo createResponse(ERROR_CODE, "修改失败", null);
                                }
                            } else {
                                echo createResponse(ERROR_CODE, "找不到id为" . $modId . "的模组。", null);
                            }
                            mysqli_free_result($modResult);
                        } else if ($type == "user") {
                            echo createResponse(ERROR_CODE, "暂不支持处理用户。", null);
                        }
                        $stateCode = 0;
                    } else {
                        echo createResponse(SUCCESS_CODE, "忽略成功", null);
                    }
                    $updatathis = "UPDATE " . DATABASE_NAME . ".`report_record` SET `admin` = '" . $account . "' WHERE `id` = '" . $id . "'";
                    mysqli_query($con, $updatathis);
                    $updatathis = "UPDATE " . DATABASE_NAME . ".`report_record` SET `state` = '" . $stateCode . "' WHERE `id` = '" . $id . "'";
                    mysqli_query($con, $updatathis);
                } else {
                    echo createResponse(ERROR_CODE, "找不到举报记录。", null);
                }
            } else {
                echo createResponse(ERROR_CODE, "您无权处理举报。", null);
            }
        } else {
            echo createResponse(ERROR_CODE, "找不到名为" . $account . "的用户。", null);
        }
    }
    mysqli_close($con);
}

/*加载举报列表 */
function loadList()
{
    $con = mysqli_connect(SERVERNAME, LOCALHOST, PASSWORD);
    mysqli_select_db($con, DATABASE_NAME);
    if (!$con) {
        echo createResponse(ERROR_CODE, "链接数据库出错。", null);
        return;
    } else {
        $total = array();
        $num = 0;
        $sqlMod = "SELECT * FROM " . DATABASE_NAME . ".`report_record` WHERE `state`='1' ORDER BY id DESC";
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
            echo createResponse(ERROR_CODE, "没有举报记录。", null);
        }
        if ($result != false) {
            mysqli_free_result($result);
        }
    }
    mysqli_close($con);
}

/*发送举报请求 */
function send($account, $type, $target, $why, $describe)
{
    $con = mysqli_connect(SERVERNAME, LOCALHOST, PASSWORD);
    mysqli_select_db($con, DATABASE_NAME);
    if (!$con) {
        echo createResponse(ERROR_CODE, "链接数据库出错。", null);
        return;
    } else {
        $sql = "SELECT * FROM " . DATABASE_NAME . ".`user` WHERE account='" . $account . "'";
        $result = mysqli_query($con, $sql);
        if (mysqli_num_rows($result) > 0) {
            # $row = mysqli_fetch_assoc($result);
            $now = time();
            $createTime = date("Y-m-d H:i:s", $now);

            $selectSql = "SELECT * FROM " . DATABASE_NAME . ".`";
            if ($type == "mod") {
                $selectSql = $selectSql . "mod` WHERE `id` = '" . $target . "'";
            } else {
                //其他 为用户
                $selectSql = $selectSql . "user` WHERE `account` = '" . $target . "'";
                if ($account == $target) {
                    echo createResponse(ERROR_CODE, "不能举报自己。", null);
                    mysqli_close($con);
                    return;
                }
            }
            $result2 = mysqli_query($con, $selectSql);
            if (mysqli_num_rows($result2) > 0) {
                $oldSql =  "SELECT * FROM " . DATABASE_NAME . ".`report_record` WHERE `account`='" . $account . "' AND `type`='" . $type . "' AND `target`='" . $target . "' AND `state`='1'";
                $oldResult = mysqli_query($con, $oldSql);
                if (mysqli_num_rows($oldResult) > 0) {
                    echo createResponse(ERROR_CODE, "您已经举报过" . $target . "了，请等待处理结果。", null);
                } else {
                    $insertSql = "INSERT INTO " . DATABASE_NAME . ".`report_record`( `account`, `type`, `target`, `why`, `describe`, `state`, `time`) VALUES ('" . $account . "', '" . $type . "', '" . $target . "', '" . $why . "', '" . $describe . "', '1', '" . $createTime . "')";
                    if (mysqli_query($con, $insertSql)) {
                        echo createResponse(SUCCESS_CODE, "举报成功。", null);
                    } else {
                        echo createResponse(ERROR_CODE, "举报失败。", null);
                    }
                }
            } else {
                echo createResponse(ERROR_CODE, "找不到举报的目标", null);
            }
        } else {
            echo createResponse(ERROR_CODE, "找不到用户", null);
            return false;
        }
    }
    mysqli_close($con);
}
