<?php
/*激活系统（支付） */
require_once "conf.php";
require_once "email.php";
if (!canUseIp()) {
    return;
}

if (empty($_REQUEST['action'])) {
    echo nullValuePrompt("action");
    return;
}

switch ($_REQUEST['action']) {
    case "getPlanList":
        if (empty($_POST['account'])) {
            echo nullValuePrompt("account");
            return;
        }
        getList($_POST['account']);
        break;
    case "getOrderList":
        $account = null;
        if (!empty($_POST['account'])) {
            $account = $_POST['account'];
        }
        getOrderList($account);
        break;
    case "createOrder":
        if (empty($_POST['account'])) {
            echo nullValuePrompt("account");
            return;
        }
        if (empty($_POST['planId'])) {
            echo nullValuePrompt("planId");
            return;
        }
        $couponsId = null;
        if (!empty($_POST['couponsId'])) {
            $couponsId = $_POST['couponsId'];
        }
        createOrder($_POST['account'], $couponsId, $_POST['planId']);
        break;
    case "getOrderInfo":
        if (empty($_POST['account'])) {
            echo nullValuePrompt("account");
            return;
        }
        if (empty($_POST['uuid'])) {
            echo nullValuePrompt("uuid");
            return;
        }
        getOrderInfo($_POST['account'], $_POST['uuid']);
        break;
    case "confirmOrder":
        if (empty($_POST['account'])) {
            echo nullValuePrompt("account");
            return;
        }
        if (empty($_POST['appId'])) {
            echo nullValuePrompt("appId");
            return;
        }
        if (empty($_POST['flag'])) {
            echo nullValuePrompt("flag");
            return;
        }
        if (empty($_POST['payState'])) {
            echo nullValuePrompt("payState");
            return;
        }
        confirmOrder($_POST['account'], $_POST['appId'], $_POST['flag'], $_POST['payState']);
        break;
}



/**确认订单 */
function confirmOrder($account, $appId, $flag, $payState)
{
    $con = mysqli_connect(SERVERNAME, LOCALHOST, PASSWORD);
    mysqli_select_db($con, DATABASE_NAME);
    if (!$con) {
        echo createResponse(ERROR_CODE, "链接数据库出错。", null);
        return;
    } else {
        $sqlUser = "SELECT * FROM " . DATABASE_NAME . ".`user` WHERE `account`='" . $account . "' AND `appID`='" . $appId . "'";
        $userResult = mysqli_query($con, $sqlUser);
        if (mysqli_num_rows($userResult) > 0) {
            $userRow = mysqli_fetch_assoc($userResult);
            $permission = $userRow['permission'];
            if ($permission == 1) {
                $sql = "SELECT * FROM " . DATABASE_NAME . ".`order` WHERE flag='" . $flag . "'";
                $result = mysqli_query($con, $sql);
                if ($result != false && mysqli_num_rows($result) > 0) {
                    $row = mysqli_fetch_assoc($result);
                    //$row订单信息
                    $state = $row['state'];
                    if ($state == "true") {
                        echo createResponse(ERROR_CODE, "此订单已结算，请检查订单状态", null);
                        return;
                    }

                    if ($payState != "true") {
                        $updataSql2 = "UPDATE " . DATABASE_NAME . ".`order` SET `state` = 'ignore' WHERE `flag` = '" . $flag . "'";
                        mysqli_query($con, $updataSql2);
                        echo createResponse(SUCCESS_CODE, "已忽略订单。", null);
                        return;
                    }

                    $targetAccount = $row['account'];
                    $targetAccountSql = "SELECT * FROM " . DATABASE_NAME . ".`user` WHERE `account`='" . $targetAccount . "'";
                    $targetUserResult = mysqli_query($con, $targetAccountSql);
                    if (mysqli_num_rows($targetUserResult) > 0) {
                        $targetUserRow = mysqli_fetch_assoc($targetUserResult);
                        $addTime = $row['addTime'];
                        if ($addTime == "forever") {
                            $updataSql = "UPDATE " . DATABASE_NAME . ".`user` SET `expirationTime` = 'forever' WHERE `account` = '" . $targetAccount . "'";
                            mysqli_query($con, $updataSql);
                            send($targetUserRow['email'], "订单完成通知", "<p>亲爱的" . $targetUserRow['userName'] . "，您好！</p>
                            <p>您的订单(" . $row['name'] . ")已完成,感谢您的支持。</p>
                            <p>助手账号(" . $targetAccount . ")已<font color=\"#FF0000\">永久激活</font>，可无限期使用。</p>
                            <hr>
                            <p>此通知已发送至与您的 铁锈助手 帐户关联的电子邮件地址。</p>
                            <p>这封电子邮件由系统自动生成，请勿回复。如果您需要额外帮助，请加入 <a href=\"https://jq.qq.com/?_wv=1027&k=fg3CUxiI\">铁锈助手官方群</a>。</p>
                            <p>祝您生活愉快！</p>
                            <p>-ColdMint</p>", false);
                        } else {
                            $expirationTime = strtotime($targetUserRow['expirationTime']);
                            $newExpirationTime = date("Y-m-d H:i:s", strtotime($addTime, $expirationTime));
                            $updataSql = "UPDATE " . DATABASE_NAME . ".`user` SET `expirationTime` = '" . $newExpirationTime . "' WHERE `account` = '" . $targetAccount . "'";
                            mysqli_query($con, $updataSql);
                            send($targetUserRow['email'], "订单完成通知", "<p>" . $targetUserRow['userName'] . "，您好！</p>
                            <p>您的订单(" . $row['name'] . ")已完成,感谢您的支持。</p>
                            <p>助手账号(" . $targetAccount . ")已激活至" . $newExpirationTime . "。</p>
                            <hr>
                            <p>此通知已发送至与您的 铁锈助手 帐户关联的电子邮件地址。</p>
                            <p>这封电子邮件由系统自动生成，请勿回复。如果您需要额外帮助，请加入 <a href=\"https://jq.qq.com/?_wv=1027&k=fg3CUxiI\">铁锈助手官方群</a>。</p>
                            <p>祝您生活愉快！</p>
                            <p>-ColdMint</p>", false);
                        }
                        $updataSql2 = "UPDATE " . DATABASE_NAME . ".`order` SET `state` = 'true' WHERE `flag` = '" . $flag . "'";
                        mysqli_query($con, $updataSql2);
                        echo createResponse(SUCCESS_CODE, "订单更新成功", null);
                    } else {
                        echo createResponse(ERROR_CODE, "订单指向的用户不存在。", null);
                    }
                } else {
                    echo createResponse(ERROR_CODE, "找不到订单", null);
                }
            } else {
                echo createResponse(ERROR_CODE, "您无权处理订单。", null);
            }
        } else {
            echo createResponse(ERROR_CODE, "找不到名为" . $account . "的用户，或者appId验证失败。", null);
        }
    }
    mysqli_close($con);
}


/*获取订单列表 */
function getOrderList($account)
{
    $con = mysqli_connect(SERVERNAME, LOCALHOST, PASSWORD);
    mysqli_select_db($con, DATABASE_NAME);
    if (!$con) {
        echo createResponse(ERROR_CODE, "链接数据库出错。", null);
        return;
    } else {
        $sql = "";
        if ($account == null) {
            $sql = "SELECT * FROM " . DATABASE_NAME . ".`order` ORDER BY createTime DESC LIMIT 100";
        } else {
            $sql = "SELECT * FROM " . DATABASE_NAME . ".`order` WHERE account='" . $account . "' ORDER BY createTime DESC LIMIT 100";
        }
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
            echo createResponse(ERROR_CODE, "找不到订单", null);
        }
    }
    mysqli_close($con);
}

/*获取订单信息 */
function getOrderInfo($account, $uuid)
{
    $con = mysqli_connect(SERVERNAME, LOCALHOST, PASSWORD);
    mysqli_select_db($con, DATABASE_NAME);
    if (!$con) {
        echo createResponse(ERROR_CODE, "链接数据库出错。", null);
        return;
    } else {
        $sql = "SELECT * FROM " . DATABASE_NAME . ".`order` WHERE account='" . $account . "' AND flag='" . $uuid . "'";
        $result = mysqli_query($con, $sql);
        if (mysqli_num_rows($result) > 0) {
            $row = mysqli_fetch_assoc($result);
            echo createResponse(SUCCESS_CODE, "获取成功。", $row);
        } else {
            echo createResponse(ERROR_CODE, "找不到订单" . $sql, null);
        }
    }
    mysqli_close($con);
}

/*创建订单 */
function createOrder($account, $couponsId, $planId)
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
            $selectSql = "SELECT * FROM " . DATABASE_NAME . ".`purchase_plan` WHERE `id` = '" . $planId . "'";
            $result2 = mysqli_query($con, $selectSql);
            if (mysqli_num_rows($result2) > 0) {
                $value = null;
                if ($couponsId != null) {
                    $couponsSql = "SELECT * FROM " . DATABASE_NAME . ".`coupons` WHERE id='" . $couponsId . "' ORDER BY expirationTime DESC";
                    $couponsResult = mysqli_query($con, $couponsSql);
                    if ($couponsResult == false || mysqli_num_rows($couponsResult) == 0) {
                        echo createResponse(ERROR_CODE, "找不到优惠券。", null);
                        return;
                    } else {
                        $couponsRow = mysqli_fetch_assoc($couponsResult);
                        $type = $couponsRow['type'];
                        if ($type == "unlock") {
                            echo createResponse(ERROR_CODE, "无法使用此类型的优惠券。", null);
                            return;
                        } elseif ($type == "personal") {
                            $target = $couponsRow['target'];
                            if ($target != $account) {
                                echo createResponse(ERROR_CODE, "您无权使用此优惠券。", null);
                                return;
                            }
                        }
                        $now = time();
                        $expirationTime = strtotime($couponsRow['expirationTime']);
                        if ($now > $expirationTime) {
                            echo createResponse(ERROR_CODE, "优惠券已过期。", null);
                            return;
                        }

                        $num = $couponsRow['num'];
                        if ($num <= 0 && $num != -1) {
                            echo createResponse(ERROR_CODE, "优惠券已经使用了。", null);
                            return;
                        }
                        //消费优惠券
                        if ($num > 0) {
                            $up = "UPDATE " . DATABASE_NAME . ".`coupons` SET `num` = " . ($num - 1) . " WHERE `id` = '" . $couponsId . "'";
                            mysqli_query($con, $up);
                        }
                        $value = $couponsRow['value'];
                    }
                }
                $row = mysqli_fetch_assoc($result);
                $now = time();
                $orderCreateTime = date("Y-m-d H:i:s", $now);
                $planRow = mysqli_fetch_assoc($result2);
                $name = $planRow['name'];
                $price = $planRow['price'];
                $originalPrice = $price;
                if ($value != null) {
                    if ($value >= 1) {
                        $price -= $value;
                    } else {
                        $price *= $value;
                    }
                    if ($price < 0) {
                        $price = 0;
                    }
                }
                $addTime = $planRow['time'];
                $key = uuid();
                $insertSql = "INSERT INTO " . DATABASE_NAME . ".`order`( `name`, `account`, `addTime`, `price`,`originalPrice`, `createTime`, `flag`, `state`) VALUES ('" . $name . "', '" . $account . "', '" . $addTime . "', " . $price . ", '" . $originalPrice . "','" . $orderCreateTime . "', '" . $key . "', 'false')";
                if (mysqli_query($con, $insertSql)) {
                    echo createResponse(SUCCESS_CODE, "创建订单成功。", $key);
                } else {
                    echo createResponse(ERROR_CODE, "创建订单失败。", null);
                }
            } else {
                echo createResponse(ERROR_CODE, "找不到计划" . $account, null);
            }
        } else {
            echo createResponse(ERROR_CODE, "找不到用户" . $account, null);
            return false;
        }
    }
    mysqli_close($con);
}

/*加载支付套餐列表 
*/
function getList($account)
{
    $con = mysqli_connect(SERVERNAME, LOCALHOST, PASSWORD);
    mysqli_select_db($con, DATABASE_NAME);
    if (!$con) {
        echo createResponse(ERROR_CODE, "链接数据库出错。", null);
        return;
    } else {
        $sql = "SELECT * FROM " . DATABASE_NAME . ".`purchase_plan` ORDER BY price ASC";
        $result = mysqli_query($con, $sql);
        if ($result != false && mysqli_num_rows($result) > 0) {
            $total = array();
            $num = 0;
            while ($row = mysqli_fetch_assoc($result)) {
                if ($row['limit'] == "lock") {
                    $now = date("Y-m-d H:i:s", time());
                    $sql2 = "SELECT * FROM " . DATABASE_NAME . ".`coupons` WHERE type='unlock' AND value='" . $row['id'] . "' AND target='" . $account . "' AND expirationTime > '" . $now . "' ORDER BY expirationTime DESC";
                    $result2 = mysqli_query($con, $sql2);
                    if ($result2 != false && mysqli_num_rows($result2) > 0) {
                        $couponsRow = mysqli_fetch_assoc($result2);
                        $row['limit'] = $couponsRow['expirationTime'];
                        $total[$num] = $row;
                        $num++;
                    }
                } else {
                    $total[$num] = $row;
                    $num++;
                }
            }
            echo createResponse(SUCCESS_CODE, "获取成功,共" . $num . "条", $total);
        } else {
            echo createResponse(ERROR_CODE, "找不到购买方案", null);
        }
    }
    mysqli_close($con);
}
