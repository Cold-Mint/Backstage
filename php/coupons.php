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
    case "add":
        if (empty($_POST['name'])) {
            echo nullValuePrompt("name");
            return;
        }

        if (empty($_POST['describe'])) {
            echo nullValuePrompt("describe");
            return;
        }

        if (empty($_POST['type'])) {
            echo nullValuePrompt("type");
            return;
        }

        if ($_POST['type'] != "personal" && $_POST['type'] != "all" && $_POST['type'] != "unlock") {
            echo createResponse(ERROR_CODE, "type只能为personal(个人)，all(全部)，unlock(解锁购买计划)。", null);
            return;
        }

        //如果是个人使用必须规定目标
        $target = null;
        if ($_POST['type'] == "personal" || $_POST['type'] == "unlock") {
            if (empty($_POST['target'])) {
                echo nullValuePrompt("target(个人或解锁使用的优惠券必须规定目标)");
                return;
            } else {
                $target = $_POST['target'];
            }
        }

        //数字小于1按百分数计算，大于1按整数计算，unlock类型应该填写解锁的计划id
        if (empty($_POST['value'])) {
            echo nullValuePrompt("value");
            return;
        }

        //可选，默认使用1次，可设置数量(-1为无限使用)
        $num = 1;
        if (!empty($_POST['num'])) {
            $num = $_POST['num'];
        }

        //timeLimit优惠券的有效期
        if (empty($_POST['timeLimit'])) {
            echo nullValuePrompt("timeLimit");
            return;
        }
        addCoupons($_POST['name'], $_POST['describe'], $_POST['type'], $_POST['value'], $target, $num, $_POST['timeLimit']);
        break;
    case "list":
        if (empty($_POST['account'])) {
            echo nullValuePrompt("account");
            return;
        }
        getList($_POST['account']);
        break;
    case "clean":
        clean();
        break;
}

/*清理无用优惠券 */
function clean()
{
    $con = mysqli_connect(SERVERNAME, LOCALHOST, PASSWORD);
    mysqli_select_db($con, DATABASE_NAME);
    if (!$con) {
        echo createResponse(ERROR_CODE, "链接数据库出错。", null);
        return;
    } else {
        $now = date("Y-m-d H:i:s", time());
        $sql = "DELETE FROM " . DATABASE_NAME . ".`coupons` WHERE expirationTime <= '" . $now . "' OR num = 0";
        $result = mysqli_query($con, $sql);
        if ($result) {
            $num = mysqli_affected_rows($con);
            if ($num > 0) {
                echo createResponse(SUCCESS_CODE, "已清理" . $num . "张无用优惠券", null);
            } else {
                echo createResponse(SUCCESS_CODE, "没有无用的优惠券", null);
            }
        } else {
            echo createResponse(ERROR_CODE, "清理失败", null);
        }
    }
    mysqli_close($con);
}


/*获取某个用户的优惠券列表 */
function getList($account)
{
    $con = mysqli_connect(SERVERNAME, LOCALHOST, PASSWORD);
    mysqli_select_db($con, DATABASE_NAME);
    if (!$con) {
        echo createResponse(ERROR_CODE, "链接数据库出错。", null);
        return;
    } else {
        $now = date("Y-m-d H:i:s", time());
        $sql = "SELECT * FROM " . DATABASE_NAME . ".`coupons` WHERE ((type='personal' AND target='" . $account . "') OR type='all') AND expirationTime > '" . $now . "' AND (num > 0 OR num = -1) ORDER BY expirationTime ASC";
        $result = mysqli_query($con, $sql);
        if ($result != false && mysqli_num_rows($result) > 0) {
            $total = array();
            $num = 0;
            while ($row = mysqli_fetch_assoc($result)) {
                $total[$num] = $row;
                $num++;
            }
            echo createResponse(SUCCESS_CODE, "获取成功,共" . $num . "条", $total);
        } else {
            echo createResponse(SUCCESS_CODE, "无优惠券", null);
        }
    }
    mysqli_close($con);
}

/* 添加优惠券 */
function addCoupons($name, $describe, $type, $value, $target, $num, $timeLimit)
{
    $con = mysqli_connect(SERVERNAME, LOCALHOST, PASSWORD);
    mysqli_select_db($con, DATABASE_NAME);
    if (!$con) {
        echo createResponse(ERROR_CODE, "链接数据库出错。", null);
        return;
    } else {
        $nowTime = time();
        $createTime = date("Y-m-d H:i:s", $nowTime);
        $expirationTime = date("Y-m-d H:i:s", strtotime($timeLimit, $nowTime));
        $sql = "INSERT INTO  " . DATABASE_NAME . ".`coupons`(`name`, `describe` , `type`, `value`, `target`, `num`, `createTime`,`expirationTime`) VALUES ('" . $name . "', '" . $describe . "', '" . $type . "', '" . $value . "', '" . $target . "', " . $num . ", '" . $createTime . "','" . $expirationTime . "')";
        if (mysqli_query($con, $sql)) {
            echo createResponse(SUCCESS_CODE, "创建优惠券成功。", null);
        } else {
            echo createResponse(ERROR_CODE, "创建优惠券失败。", null);
        }
    }
    mysqli_close($con);
}
