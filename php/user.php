<?php
/*用户系统（登录，登出，激活时间，用户权限） */
require_once "conf.php";
require_once "email.php";


if (!canUseIp()) {
    return;
}
/*email会拦截此操作，包括ip检查
if (empty($_REQUEST['action'])) {
    echo nullValuePrompt("action");
    return;
}*/
if (empty($_REQUEST['action'])) {
    return;
}


switch ($_REQUEST['action']) {
    case "register":
        if (empty($_POST['account'])) {
            echo nullValuePrompt("account");
            return;
        }
        if (empty($_POST['userName'])) {
            echo nullValuePrompt("userName");
            return;
        }
        if (empty($_POST['passWord'])) {
            echo nullValuePrompt("passWord");
            return;
        }
        if (empty($_POST['email'])) {
            echo nullValuePrompt("email");
            return;
        }

        if (empty($_POST['appID'])) {
            echo nullValuePrompt("appID");
            return;
        }
        register($_POST['account'], $_POST['userName'], $_POST['passWord'], $_POST['email'], $_POST['appID']);
        break;
    case "clear":
        //清理未激活的账号
        cleanInactiveUser();
        break;
    case "login":
        if (empty($_POST['account'])) {
            echo nullValuePrompt("account");
            return;
        }
        if (empty($_POST['passWord'])) {
            echo nullValuePrompt("passWord");
            return;
        }
        if (empty($_POST['appID'])) {
            echo nullValuePrompt("appID");
            return;
        }
        $isEmail = false;
        if (!empty($_POST['isEmail']) && $_POST['isEmail'] == "true") {
            $isEmail = true;
        }
        login($_POST['account'], $_POST['passWord'], $_POST['appID'], $isEmail);
        break;
    case "enableAccount":
        if (empty($_POST['account'])) {
            echo nullValuePrompt("account");
            return;
        }
        if (empty($_POST['key'])) {
            echo nullValuePrompt("key");
            return;
        }
        $isEmail = false;
        if (!empty($_POST['isEmail']) && $_POST['isEmail'] == "true") {
            $isEmail = true;
        }
        enableAccount($_POST['account'], $_POST['key'], $isEmail);
        break;
    case "changeAppId":
        if (empty($_POST['account'])) {
            echo nullValuePrompt("account");
            return;
        }
        if (empty($_POST['key'])) {
            echo nullValuePrompt("key");
            return;
        }
        if (empty($_POST['appID'])) {
            echo nullValuePrompt("appID");
            return;
        }
        $isEmail = false;
        if (!empty($_POST['isEmail']) && $_POST['isEmail'] == "true") {
            $isEmail = true;
        }
        changeAppID($_POST['account'], $_POST['key'], $_POST['appID'], $isEmail);
        break;
    case "changeUserName":
        if (empty($_POST['token'])) {
            echo nullValuePrompt("token");
            return;
        }
        if (empty($_POST['userName'])) {
            echo nullValuePrompt("userName");
            return;
        }
        modifyTheRecord($_POST['token'], "userName", $_POST['userName']);
        break;
    case "getSocialInfo":
        //获取社交信息
        if (empty($_POST['account'])) {
            echo nullValuePrompt("account");
            return;
        }
        getSocialInfo($_POST['account']);
        break;
    case "getInfo":
        //获取用户信息
        if (empty($_POST['adminToken'])) {
            echo nullValuePrompt("adminToken");
            return;
        }
        if (empty($_POST['account'])) {
            echo nullValuePrompt("account");
            return;
        }
        getInfo($_POST['adminToken'], $_POST['account']);
        break;
        //获取用户激活信息
    case "getUserActivationInfo":
        if (empty($_POST['token'])) {
            echo nullValuePrompt("token");
            return;
        }
        getUserActivationInfo($_POST['token']);
        break;
        //获取空间信息
    case "getSpaceInfo":
        if (empty($_POST['account'])) {
            echo nullValuePrompt("account");
            return;
        }
        getSpaceInfo($_POST['account']);
        break;
    case "verification":
        //验证设备
        if (empty($_POST['account'])) {
            echo nullValuePrompt("account");
            return;
        }
        if (empty($_POST['passWord'])) {
            echo nullValuePrompt("passWord");
            return;
        }
        if (empty($_POST['appID'])) {
            echo nullValuePrompt("appID");
            return;
        }
        $isEmail = false;
        if (!empty($_POST['isEmail']) && $_POST['isEmail'] == "true") {
            $isEmail = true;
        }
        verification($_POST['account'], $_POST['passWord'], $_POST['appID'], $isEmail);
        break;
        //更新空间信息
    case "updateSpaceInfo":
        if (empty($_POST['token'])) {
            echo nullValuePrompt("token");
            return;
        }
        if (empty($_POST['userName'])) {
            echo nullValuePrompt("userName");
            return;
        }
        if (empty($_POST['introduce'])) {
            echo nullValuePrompt("introduce");
            return;
        }
        if (empty($_POST['gender'])) {
            echo nullValuePrompt("gender");
            return;
        }
        //icon可以是文件，也可以是字符串
        $icon = null;
        if (!empty($_FILES['icon'])) {
            $icon = $_FILES['icon'];
        } else if (!empty($_POST['icon'])) {
            $icon = $_POST['icon'];
        }

        $cover = null;
        if (!empty($_FILES['cover'])) {
            $cover = $_FILES['cover'];
        } else if (!empty($_POST['cover'])) {
            $cover = $_POST['cover'];
        }
        updateSpaceInfo($_POST['token'], $_POST['userName'], $_POST['introduce'], $_POST['gender'], $icon, $cover);
        break;
    case "getSocialList":
        /*
        sortMode 可选参数
        -loginTime 按登录时间
        -createTime 按创建时间
        */
        $sortMode = null;
        if (!empty($_POST['sortMode'])) {
            $sortMode = $_POST['sortMode'];
        }
        /*
        limit 可选参数
        指定返回数量
        */
        $limit = null;
        if (!empty($_POST['limit'])) {
            $limit = $_POST['limit'];
        }
        getList(true, true, $sortMode, $limit);
        break;
    case "getUserIcon":
        //获取用户头像
        if (empty($_POST['account'])) {
            echo nullValuePrompt("account");
            return;
        }
        getIcon($_POST['account']);
        break;
    case "requestChangePassword":
        //请求修改密码

        if (empty($_POST['account'])) {
            echo nullValuePrompt("account");
            return;
        }
        $isEmail = false;
        if (!empty($_POST['isEmail']) && $_POST['isEmail'] == "true") {
            $isEmail = true;
        }
        requestChangePassword($_POST['account'], $isEmail);
        break;
    case "changePassword":
        //修改密码
        if (empty($_POST['account'])) {
            echo nullValuePrompt("account");
            return;
        }
        $isEmail = false;
        if (!empty($_POST['isEmail']) && $_POST['isEmail'] == "true") {
            $isEmail = true;
        }
        if (empty($_POST['code'])) {
            echo nullValuePrompt("code");
            return;
        }
        if (empty($_POST['newPassword'])) {
            echo nullValuePrompt("newPassword");
            return;
        }
        ChangePassword($_POST['account'], $isEmail, $_POST['code'], $_POST['newPassword']);
        break;
}




/*修改密码 */
function ChangePassword($account, $isEmail, $code, $newPassword)
{
    $con = mysqli_connect(SERVERNAME, LOCALHOST, PASSWORD);
    mysqli_select_db($con, DATABASE_NAME);
    if (!$con) {
        echo createResponse(ERROR_CODE, "链接数据库出错。", null);
        return;
    } else {
        $key = "account";
        $show = "用户";
        if ($isEmail) {
            $key = "email";
            $show = "邮箱";
        }
        $sql = "SELECT * FROM " . DATABASE_NAME . ".`user` WHERE " . $key . "='" . $account . "'";
        $result = mysqli_query($con, $sql);
        if (mysqli_num_rows($result) > 0) {
            $row = mysqli_fetch_assoc($result);
            $timeNumber = time();
            $nowTime = date("Y-m-d H:i:s", $timeNumber);
            $sqlCode = "SELECT * FROM " . DATABASE_NAME . ".`verification_code` WHERE account='" . $row['account'] . "' AND type='changePassword' AND expirationTime>='" . $nowTime . "' AND enable = 'true'";
            $resultCode = mysqli_query($con, $sqlCode);
            if (mysqli_num_rows($resultCode) > 0) {
                $rowCode = mysqli_fetch_assoc($resultCode);
                if ($rowCode['code'] == $code) {
                    $updata = "UPDATE " . DATABASE_NAME . ".`user` SET `password` ='" . $newPassword . "' WHERE " . $key . " = '" . $account . "'";
                    mysqli_query($con, $updata);
                    $sqlCode = "UPDATE " . DATABASE_NAME . ".`verification_code` SET `enable` = 'false' WHERE account='" . $row['account'] . "' AND type='changePassword' AND expirationTime>='" . $nowTime . "' AND enable = 'true'";
                    mysqli_query($con, $sqlCode);
                    echo createResponse(SUCCESS_CODE, "修改成功。", null);
                } else {
                    echo createResponse(ERROR_CODE, "验证码错误。", null);
                }
            } else {
                echo createResponse(ERROR_CODE, "请先获取验证码。", null);
            }
        } else {
            echo createResponse(ERROR_CODE, "找不到" . $show, null);
        }
    }
    mysqli_close($con);
}


/*请求修改密码 */
function requestChangePassword($account, $isEmail)
{
    $con = mysqli_connect(SERVERNAME, LOCALHOST, PASSWORD);
    mysqli_select_db($con, DATABASE_NAME);
    if (!$con) {
        echo createResponse(ERROR_CODE, "链接数据库出错。", null);
        return;
    } else {
        $key = "account";
        $show = "用户";
        if ($isEmail) {
            $key = "email";
            $show = "邮箱";
        }
        $sql = "SELECT * FROM " . DATABASE_NAME . ".`user` WHERE " . $key . "='" . $account . "'";
        $result = mysqli_query($con, $sql);
        $keyCode = createUniqueCode();
        if (mysqli_num_rows($result) > 0) {
            $row = mysqli_fetch_assoc($result);
            $timeNumber = time();
            $nowTime = date("Y-m-d H:i:s", $timeNumber);
            $sqlCode = "SELECT * FROM " . DATABASE_NAME . ".`verification_code` WHERE account='" . $row['account'] . "' AND type='changePassword' AND expirationTime>='" . $nowTime . "' AND enable = 'true'";
            $resultCode = mysqli_query($con, $sqlCode);
            if (mysqli_num_rows($resultCode) > 0) {
                $rowCode = mysqli_fetch_assoc($resultCode);
                echo createResponse(SUCCESS_CODE, "请在 " . $rowCode['expirationTime'] . " 后，获取新的验证码。", null);
            } else {
                //10分钟有效
                $endTime = date("Y-m-d H:i:s", strtotime("+10 minute", $timeNumber));
                send($row['email'], "您正在申请修改密码", "<p>" . $row['userName'] . "，您好！</p>
            <p>您的账户" . $row['account'] . "，修改密码，所需的令牌验证码为：</p>
            <h1><font color=\"#FF0000\">" . $keyCode . "</font></h1>
            <p>此验证码在" . $endTime . "前有效。</p>
            <p>令牌验证码是完成登录所必需的。没有人能够不访问这封电子邮件就访问您的帐户。</p>
            <hr>
            <p>此通知已发送至与您的 铁锈助手 帐户关联的电子邮件地址。</p>
            <p>这封电子邮件由系统自动生成，请勿回复。如果您需要额外帮助，请加入 <a href=\"https://jq.qq.com/?_wv=1027&k=fg3CUxiI\">铁锈助手官方群</a>。</p>
            <p>祝您生活愉快！</p>
            <p>-ColdMint</p>", false);
                //创建验证码
                $sqlCode = "INSERT INTO " . DATABASE_NAME . ".`verification_code`(`account`, `code`, `createTime`, `expirationTime`, `type`, `enable`) VALUES ('" . $row['account'] . "', '" . $keyCode . "', '" . $nowTime . "', '" . $endTime . "', 'changePassword', 'true')";
                mysqli_query($con, $sqlCode);
                echo createResponse(SUCCESS_CODE, "已发送邮件。", null);
            }
        } else {
            echo createResponse(ERROR_CODE, "找不到" . $show, null);
        }
    }
    mysqli_close($con);
}

/*清理未激活的用户 */
function cleanInactiveUser()
{
    $con = mysqli_connect(SERVERNAME, LOCALHOST, PASSWORD);
    mysqli_select_db($con, DATABASE_NAME);
    if (!$con) {
        echo createResponse(ERROR_CODE, "链接数据库出错。", null);
        return;
    } else {
        $sql = "DELETE FROM " . DATABASE_NAME . ".`user` WHERE enable !='true' AND enable !='verification'";
        $result = mysqli_query($con, $sql);
        if ($result) {
            $num = mysqli_affected_rows($con);
            if ($num > 0) {
                echo createResponse(SUCCESS_CODE, "已清理" . $num . "个未激活的账号", null);
            } else {
                echo createResponse(SUCCESS_CODE, "没有未激活的账号", null);
            }
        } else {
            echo createResponse(ERROR_CODE, "清理失败", null);
        }
    }
    mysqli_close($con);
}

/*获取用户列表（是否限于社交信息,是否过滤未激活用户-搜索条件,排序条件） */
function getList($social, $enable, $sortMode, $limit)
{
    $con = mysqli_connect(SERVERNAME, LOCALHOST, PASSWORD);
    mysqli_select_db($con, DATABASE_NAME);
    if (!$con) {
        echo createResponse(ERROR_CODE, "链接数据库出错。", null);
        return;
    } else {
        $sql = "SELECT * FROM " . DATABASE_NAME . ".`user`";
        if ($social) {
            $sql = "SELECT account,userName,email,permission,loginTime,gender,enable FROM " . DATABASE_NAME . ".`user`";
        }
        if ($enable) {
            $sql = $sql . " WHERE enable='true'";
        }
        if ($sortMode != null) {
            if ($sortMode == "loginTime") {
                $sql = $sql . " ORDER BY loginTime DESC";
            } else if ($sortMode == "createTime") {
                $sql = $sql . " ORDER BY creationTime DESC";
            }
        }
        if ($limit != null) {
            $sql = $sql . " LIMIT " . $limit;
        }
        $result = mysqli_query($con, $sql);
        if ($result != false && mysqli_num_rows($result) > 0) {
            $num = 0;
            while ($row = mysqli_fetch_assoc($result)) {
                $total[$num] = $row;
                $num++;
            }
            echo createResponse(SUCCESS_CODE, "获取成功，共" . $num . "条记录", $total);
        } else {
            echo createResponse(ERROR_CODE, "没有用户。", null);
        }
        if ($result != false) {
            mysqli_free_result($result);
        }
    }
    mysqli_close($con);
}


/*更新社交信息 */
function updateSpaceInfo($token, $userName, $introduce, $gender, $icon, $cover)
{
    $con = mysqli_connect(SERVERNAME, LOCALHOST, PASSWORD);
    mysqli_select_db($con, DATABASE_NAME);
    if (!$con) {
        echo createResponse(ERROR_CODE, "链接数据库出错。", null);
        return;
    } else {
        $sql = "SELECT * FROM " . DATABASE_NAME . ".`user` WHERE token='" . $token . "'";
        $result = mysqli_query($con, $sql);
        if (mysqli_num_rows($result) > 0) {
            $sqlUserName = "SELECT * FROM " . DATABASE_NAME . ".`user` WHERE userName='" . $userName . "'";
            $resultUserName = mysqli_query($con, $sqlUserName);
            $thisRow = mysqli_fetch_assoc($result);
            $account = $thisRow['account'];
            if (mysqli_num_rows($resultUserName) > 0) {
                $row = mysqli_fetch_assoc($resultUserName);

                $useToken = $row['token'];
                if ($useToken != $token) {
                    echo createResponse(ERROR_CODE, "用户名已被占用。", "@event:用户名占用");
                    return;
                }
            }

            $updata = "UPDATE " . DATABASE_NAME . ".`user` SET `userName` = '" . $userName . "' WHERE `token` = '" . $token . "'";
            mysqli_query($con, $updata);
            $updata = "UPDATE " . DATABASE_NAME . ".`community` SET `introduce` = '" . $introduce . "' WHERE `account` = '" . $account . "'";
            mysqli_query($con, $updata);
            $updata = "UPDATE " . DATABASE_NAME . ".`user` SET `gender` = '" . $gender . "' WHERE `token` = '" . $token . "'";
            mysqli_query($con, $updata);
            $folder = "../user/" . iconv("UTF-8", "GBK", $account);
            if (!file_exists($folder)) {
                mkdir($folder, 0777, true);
            }
            if ($icon != null) {
                if (is_string($icon)) {
                    $updata = "UPDATE " . DATABASE_NAME . ".`user` SET `headIcon` = '" . $icon . "' WHERE `token` = '" . $token . "'";
                    mysqli_query($con, $updata);
                } else {
                    if (!empty($icon)) {
                        $newIcon = $folder . "/icon.png";
                        $move = move_uploaded_file($icon["tmp_name"], $newIcon);
                        if ($move) {
                            $updata = "UPDATE " . DATABASE_NAME . ".`user` SET `headIcon` = '" . $newIcon . "' WHERE `token` = '" . $token . "'";
                            mysqli_query($con, $updata);
                        }
                    }
                }
            }

            if ($cover != null) {
                if (is_string($cover)) {
                    $updata = "UPDATE " . DATABASE_NAME . ".`community` SET `cover` = '" . $cover . "' WHERE `token` = '" . $token . "'";
                    mysqli_query($con, $updata);
                } else {
                    if (!empty($icon)) {
                        $newIcon = $folder . "/cover.png";
                        $move = move_uploaded_file($cover["tmp_name"], $newIcon);
                        if ($move) {
                            $updata = "UPDATE " . DATABASE_NAME . ".`community` SET `cover` = '" . $newIcon . "' WHERE `token` = '" . $token . "'";
                            mysqli_query($con, $updata);
                        }
                    }
                }
            }
            echo createResponse(SUCCESS_CODE, "已更新", null);
        } else {
            echo createResponse(ERROR_CODE, "令牌验证错误" . $token, null);
        }
    }
    mysqli_close($con);
}

/*获取当前用户图像 */
function getIcon($account)
{
    $con = mysqli_connect(SERVERNAME, LOCALHOST, PASSWORD);
    mysqli_select_db($con, DATABASE_NAME);
    if (!$con) {
        echo createResponse(ERROR_CODE, "链接数据库出错。", null);
    } else {
        $sql = "SELECT userName,headIcon FROM " . DATABASE_NAME . ".`user` WHERE account='" . $account . "'";
        $result = mysqli_query($con, $sql);
        if (mysqli_num_rows($result) > 0) {
            $row = mysqli_fetch_assoc($result);
            echo createResponse(SUCCESS_CODE, "获取成功。", $row);
        } else {
            echo createResponse(ERROR_CODE, "获取失败。", null);
        }
        mysqli_close($con);
    }
}

/*
 *添加硬币如果需要的话 
 */
function addCoinIfNeed($account)
{
    $con = mysqli_connect(SERVERNAME, LOCALHOST, PASSWORD);
    mysqli_select_db($con, DATABASE_NAME);
    $sqlUser = "SELECT coinNumber FROM " . DATABASE_NAME . ".`user` WHERE account='" . $account . "'";
    $userResult = mysqli_query($con, $sqlUser);
    if (mysqli_num_rows($userResult) > 0) {
        $userRow = mysqli_fetch_assoc($userResult);
        //如果有用户，获取大于等于今天的记录
        $currenttime = date("Y-m-d");
        $sql = "SELECT * FROM " . DATABASE_NAME . ".`coin_record` WHERE account='" . $account . "' AND time>='" . $currenttime . "'";
        $result = mysqli_query($con, $sql);
        if (mysqli_num_rows($result) == 0) {
            $newCoinNumber = $userRow['coinNumber'];
            $newCoinNumber++;
            $updata = "UPDATE " . DATABASE_NAME . ".`user` SET `coinNumber` = " . $newCoinNumber . " WHERE `account` = '" . $account . "'";
            mysqli_query($con, $updata);
            //如果没有记录，那么添加
            $number = 1;
            $nowTime = time();
            $createTime = date("Y-m-d H:i:s", $nowTime);
            $addSql = "INSERT INTO " . DATABASE_NAME . ".`coin_record`(`account`, `eventName`,`target`, `number`, `time`) VALUES ('" . $account . "', 'sign_in', 'self','" . $number . "', '" . $createTime . "')";
            mysqli_query($con, $addSql);
        }
    }
    mysqli_close($con);
}

/*
更新ip（私有方法）
 */
function updateIp($account)
{
    $con = mysqli_connect(SERVERNAME, LOCALHOST, PASSWORD);
    mysqli_select_db($con, DATABASE_NAME);
    $ip = getIp();
    $sqlIp = "UPDATE " . DATABASE_NAME . ".`ip_record` SET `account` = '" . $account . "' WHERE `ip` = '" . $ip . "'";
    $sqlIp2 = "UPDATE " . DATABASE_NAME . ".`user` SET `ip` = '" . $ip . "' WHERE `account` = '" . $account . "'";
    mysqli_query($con, $sqlIp);
    mysqli_query($con, $sqlIp2);
    mysqli_close($con);
}

/*获取当前用户的激活信息 */
function getUserActivationInfo($token)
{
    $con = mysqli_connect(SERVERNAME, LOCALHOST, PASSWORD);
    mysqli_select_db($con, DATABASE_NAME);
    if (!$con) {
        echo createResponse(ERROR_CODE, "链接数据库出错。", null);
        return;
    } else {
        $sql = "SELECT account,userName,headIcon,permission,email,enable,expirationTime,banTime,coinNumber FROM " . DATABASE_NAME . ".`user` WHERE token='" . $token . "'";
        $result = mysqli_query($con, $sql);
        if (mysqli_num_rows($result) > 0) {
          
            $row = mysqli_fetch_assoc($result);
            //是否可用
            $nowTime = time();
            $rowTime = null;
            if(ENABLE_PAYMENT){
                $rowTime = $row['expirationTime'];
            }else{
                $row['expirationTime'] = "forever";
                $rowTime = $row['expirationTime'];
            }
            $account = $row['account'];
            $activation = true;
            if ($rowTime != "forever") {
                $expirationTime = strtotime($rowTime);
                if ($nowTime > $expirationTime) {
                    $activation = false;
                }
            }
            $row['activation'] = $activation;
            $banTime = $row['banTime'];
            if (!empty($banTime)) {
                if ($banTime == "forever") {
                    echo createResponse(ERROR_CODE, "您的账号已被永久封禁。", null);
                    return;
                } else {
                    $banTimeNum = strtotime($banTime);
                    if ($nowTime < $banTimeNum) {
                        echo createResponse(ERROR_CODE, "您的账号已被封禁至" . $banTime, null);
                        return;
                    }
                }
            }
            $loginTime = date("Y-m-d H:i:s", $nowTime);
            $updata = "UPDATE " . DATABASE_NAME . ".`user` SET `loginTime` ='".$loginTime."' WHERE token = '" . $token . "'";
            mysqli_query($con, $updata);
            updateIp($account);
            addCoinIfNeed($account);
            echo createResponse(SUCCESS_CODE, "获取成功。", $row);
        } else {
            echo createResponse(ERROR_CODE, "登录状态已过期。", null);
        }
        mysqli_close($con);
    }
}

/*加载用户全部信息（管理员） */
function getInfo($adminToken, $account)
{
    $con = mysqli_connect(SERVERNAME, LOCALHOST, PASSWORD);
    mysqli_select_db($con, DATABASE_NAME);
    if (!$con) {
        echo createResponse(ERROR_CODE, "链接数据库出错。", null);
        return;
    } else {
        $adminSql = "SELECT * FROM " . DATABASE_NAME . ".`user` WHERE token='" . $adminToken . "'";
        $adminResult = mysqli_query($con, $adminSql);
        if (mysqli_num_rows($adminResult) > 0) {
            $adminRow = mysqli_fetch_assoc($adminResult);
            $permission = $adminRow['permission'];
            if ($permission == 1) {
                $sql = "SELECT * FROM " . DATABASE_NAME . ".`user` WHERE account='" . $account . "'";
                $result = mysqli_query($con, $sql);
                if (mysqli_num_rows($result) > 0) {
                    $row = mysqli_fetch_assoc($result);
                    echo createResponse(SUCCESS_CODE, "获取成功。", $row);
                } else {
                    echo createResponse(SUCCESS_CODE, "用户不存在。", null);
                }
            } else {
                echo createResponse(ERROR_CODE, "您不是管理员。", null);
            }
        } else {
            echo createResponse(ERROR_CODE, "管理员令牌验证失败。", null);
        }
    }
    mysqli_close($con);
}

/*加载社交信息 */
function getSocialInfo($account)
{
    $con = mysqli_connect(SERVERNAME, LOCALHOST, PASSWORD);
    mysqli_select_db($con, DATABASE_NAME);
    if (!$con) {
        echo createResponse(ERROR_CODE, "链接数据库出错。", null);
        return;
    } else {
        $sql = "SELECT account,userName,email,permission,loginTime,gender,enable,expirationTime FROM " . DATABASE_NAME . ".`user` WHERE account='" . $account . "'";
        $result = mysqli_query($con, $sql);
        if (mysqli_num_rows($result) > 0) {
            $row = mysqli_fetch_assoc($result);
            echo createResponse(SUCCESS_CODE, "获取成功", $row);
        } else {
            echo createResponse(ERROR_CODE, "用户不存在" . $account, null);
        }
    }
    mysqli_close($con);
}

/*加载空间信息 */
function getSpaceInfo($account)
{
    $con = mysqli_connect(SERVERNAME, LOCALHOST, PASSWORD);
    mysqli_select_db($con, DATABASE_NAME);
    if (!$con) {
        echo createResponse(ERROR_CODE, "链接数据库出错。", null);
        return;
    } else {
        $sql = "SELECT account,userName,headIcon,email,permission,loginTime,gender,enable,expirationTime,ip FROM " . DATABASE_NAME . ".`user` WHERE account='" . $account . "'";
        $result = mysqli_query($con, $sql);
        $row = mysqli_fetch_assoc($result);
        $sql2 = "SELECT * FROM " . DATABASE_NAME . ".`community` WHERE account='" . $account . "'";
        $result2 = mysqli_query($con, $sql2);
        $row2 = mysqli_fetch_assoc($result2);
        $ip = $row['ip'];
        $sql3 = "SELECT * FROM " . DATABASE_NAME . ".`ip_record` WHERE ip='" . $ip . "'";
        $result3 = mysqli_query($con, $sql3);
        $row3 = mysqli_fetch_assoc($result3);
        $end = null;
        if ($row != null && $row2 != null) {
            $end = array_merge($row, $row2);
            if ($row3['country'] == "中国") {
                $end['location'] = $row3['province'];
            } else {
                $end['location'] = $row3['country'];
            }
            unset($end['ip']);
        }
        if ($end != null && sizeof($end) > 0) {
            echo createResponse(SUCCESS_CODE, "获取成功", $end);
        } else {
            echo createResponse(ERROR_CODE, "找不到用户" . $account, null);
        }
    }
    mysqli_close($con);
}

/**
 * 修改记录
 * @param $token 账号
 * @param $key 键
 * @param $value 值
 */
function modifyTheRecord($token, $key, $value)
{
    $con = mysqli_connect(SERVERNAME, LOCALHOST, PASSWORD);
    mysqli_select_db($con, DATABASE_NAME);
    if (!$con) {
        echo createResponse(ERROR_CODE, "链接数据库出错。", null);
        return;
    } else {
        $sql = "SELECT * FROM " . DATABASE_NAME . ".`user` WHERE token='" . $token . "'";
        $result = mysqli_query($con, $sql);
        if (mysqli_num_rows($result) > 0) {
            $row = mysqli_fetch_assoc($result);
            $updata = "UPDATE " . DATABASE_NAME . ".`user` SET `" . $key . "` = '" . $value . "' WHERE `token` = '" . $token . "'";
            if (mysqli_query($con, $updata)) {
                echo createResponse(SUCCESS_CODE, "修改成功", null);
            } else {
                echo createResponse(ERROR_CODE, "修改失败", null);
            }
        } else {
            echo createResponse(ERROR_CODE, "令牌验证失败。", null);
        }
    }
    mysqli_close($con);
}


/**
 * 封禁用户
 * @param $account 用户名
 * @param $appID appid
 * @param $time 时间
 */
function banUser($account, $appID, $banAccount, $addTime)
{
    $con = mysqli_connect(SERVERNAME, LOCALHOST, PASSWORD);
    mysqli_select_db($con, DATABASE_NAME);
    if (!$con) {
        echo createResponse(ERROR_CODE, "链接数据库出错。", null);
        return;
    } else {
        $sql = "SELECT * FROM " . DATABASE_NAME . ".`user` WHERE account='" . $account . "' AND appID='" . $appID . "'";
        $result = mysqli_query($con, $sql);
        if (mysqli_num_rows($result) > 0) {
            $row = mysqli_fetch_assoc($result);
            $permission = $row['permission'];
            if ($permission == 1) {
                $nowTime = time();
                $expirationTime = date("Y-m-d H:i:s", strtotime($addTime, $nowTime));
                $updata = "UPDATE " . DATABASE_NAME . ".`user` SET `banTime` = '" . $expirationTime . "' WHERE `account` = '" . $account . "'";
                if (mysqli_query($con, $updata)) {
                    echo createResponse(SUCCESS_CODE, "已封禁至" . $expirationTime, null);
                } else {
                    echo createResponse(ERROR_CODE, "更新失败。", null);
                }
            } else {
            }
        } else {
            echo createResponse(ERROR_CODE, "找不到用户" . $account . "或appID验证失败。", null);
        }
    }
    mysqli_close($con);
}

/**
 * 注册方法
 * @param $account 账号
 * @param $passWord 密码
 * @param $email 邮箱
 * @return bool 是否注册成功
 */
function register($account, $userName, $passWord, $email, $appID)
{
    $con = mysqli_connect(SERVERNAME, LOCALHOST, PASSWORD);
    mysqli_select_db($con, DATABASE_NAME);
    $nowTime = time();
    $createTime = date("Y-m-d H:i:s", $nowTime);
    $expirationTime = date("Y-m-d H:i:s", strtotime(EXPERIENCE_TIME, $nowTime));
    $key = createUniqueCode();
    if (!$con) {
        echo createResponse(ERROR_CODE, "链接数据库出错。", null);
        return false;
    } else {
        $sqlAccount = "SELECT * FROM " . DATABASE_NAME . ".`user` WHERE account='" . $account . "'";
        $resultAccount = mysqli_query($con, $sqlAccount);
        if (mysqli_num_rows($resultAccount) > 0) {
            echo createResponse(ERROR_CODE, "账号已被占用。", "@event:账号占用");
            return false;
        }

        $sqlUserName = "SELECT * FROM " . DATABASE_NAME . ".`user` WHERE userName='" . $userName . "'";
        $resultUserName = mysqli_query($con, $sqlUserName);
        if (mysqli_num_rows($resultUserName) > 0) {
            echo createResponse(ERROR_CODE, "用户名已被占用。", "@event:用户名占用");
            return false;
        }

        $sqlEmail = "SELECT * FROM " . DATABASE_NAME . ".`user` WHERE email='" . $email . "'";
        $resultEmail = mysqli_query($con, $sqlEmail);
        if (mysqli_num_rows($resultEmail) > 0) {
            echo createResponse(ERROR_CODE, "邮箱已被占用。", "@event:邮箱占用");
            return false;
        }

        $sql = "INSERT INTO " . DATABASE_NAME . ".`user`(`account`, `password`, `token`, `userName`, `email`,`appID`, `enable`, `creationTime`, `loginTime`, `expirationTime`,`ip`) VALUES ('" . $account . "', '" . $passWord . "', '" . uuid() . "','" . $userName . "', '" . $email . "', '" . $appID . "', '" . $key . "', '" . $createTime . "', '" . $createTime . "', '" . $expirationTime . "','" . getIp() . "')";
        $sqlcommunity = "INSERT INTO " . DATABASE_NAME . ".`community`(`account`) VALUES ('" . $account . "')";
        $sqlLock2 = "INSERT INTO  " . DATABASE_NAME . ".`coupons`(`name`, `describe` , `type`, `value`, `target`, `num`, `createTime`,`expirationTime`) VALUES ('萌新折扣券', '萌新购买铁锈助手减免" . ((1 - DISCOUNT_VALUE) * 100) . "%', 'personal', '" . DISCOUNT_VALUE . "', '" . $account . "', 1, '" . $createTime . "','" . $expirationTime . "')";
        $sqlCode = "INSERT INTO " . DATABASE_NAME . ".`verification_code`(`account`, `code`, `createTime`, `expirationTime`, `type`, `enable`) VALUES ('" . $account . "', '" . $key . "', '" . $createTime . "', 'forever', 'register', 'true')";
        mysqli_query($con, $sqlCode);
        mysqli_query($con, $sqlLock2);
        mysqli_query($con, $sqlcommunity);
        if (mysqli_query($con, $sql)) {
            updateIp($account);
            echo createResponse(SUCCESS_CODE, "注册成功。", null);
            send($email, "请激活您的铁锈助手账号", "<p>" . $userName . "，您好！</p>
            <p>您登录帐户" . $account . "所需的令牌验证码为：</p>
            <h1><font color=\"#FF0000\">" . $key . "</font></h1>
            <p>令牌验证码是完成登录所必需的。没有人能够不访问这封电子邮件就访问您的帐户。</p>
            <hr>
            <p>此通知已发送至与您的 铁锈助手 帐户关联的电子邮件地址。</p>
            <p>这封电子邮件由系统自动生成，请勿回复。如果您需要额外帮助，请加入 <a href=\"https://jq.qq.com/?_wv=1027&k=fg3CUxiI\">铁锈助手官方群</a>。</p>
            <p>祝您生活愉快！</p>
            <p>-ColdMint</p>", false);
        } else {
            echo createResponse(ERROR_CODE, "注册失败。", mysqli_error($con));
            return false;
        }
    }
    mysqli_close($con);
    return true;
}

/*更改appid */
function changeAppID($account, $keyCode, $appID, $isEmail)
{
    $con = mysqli_connect(SERVERNAME, LOCALHOST, PASSWORD);
    mysqli_select_db($con, DATABASE_NAME);
    if (!$con) {
        echo createResponse(ERROR_CODE, "链接数据库出错。", null);
        return;
    } else {
        $key = "account";
        $show = "用户";
        if ($isEmail) {
            $key = "email";
            $show = "邮箱";
        }
        $sql = "SELECT * FROM " . DATABASE_NAME . ".`user` WHERE " . $key . "='" . $account . "'";
        $result = mysqli_query($con, $sql);
        if (mysqli_num_rows($result) > 0) {
            $row = mysqli_fetch_assoc($result);
            $enable = $row['enable'];
            $timeNumber = time();
            $nowTime = date("Y-m-d H:i:s", $timeNumber);
            if ($enable == "verification") {
                $sqlCode1 = "SELECT * FROM " . DATABASE_NAME . ".`verification_code` WHERE account='" . $row['account'] . "' AND type='verification' AND expirationTime>='" . $nowTime . "' AND enable = 'true'";
                $resultSql = mysqli_query($con, $sqlCode1);
                if (mysqli_num_rows($resultSql) > 0) {
                    $rowCode = mysqli_fetch_assoc($resultSql);
                    if ($rowCode['code'] == $keyCode) {
                        //加上appid
                        $updata = "UPDATE " . DATABASE_NAME . ".`user` SET `enable` ='true',`appID`='" . $appID . "' WHERE " . $key . " = '" . $account . "'";
                        mysqli_query($con, $updata);
                        $sqlCode = "UPDATE " . DATABASE_NAME . ".`verification_code` SET `enable` = 'false' WHERE account='" . $row['account'] . "' AND type='verification' AND expirationTime>='" . $nowTime . "' AND enable = 'true'";
                        mysqli_query($con, $sqlCode);

                        echo createResponse(SUCCESS_CODE, "验证成功" . $updata, null);
                    } else {
                        echo createResponse(ERROR_CODE, "验证码错误", null);
                    }
                } else {
                    echo createResponse(ERROR_CODE, "找不到验证码记录。", null);
                }
            } else {
                echo createResponse(ERROR_CODE, "您的账号不需要验证。", null);
            }
        } else {
            echo createResponse(ERROR_CODE, "找不到" . $show, null);
        }
    }
    mysqli_close($con);
}

/*验证设备(发送邮件) */
function verification($account, $passWord, $appID, $isEmail)
{
    $con = mysqli_connect(SERVERNAME, LOCALHOST, PASSWORD);
    mysqli_select_db($con, DATABASE_NAME);
    if (!$con) {
        echo createResponse(ERROR_CODE, "链接数据库出错。", null);
        return;
    } else {
        $key = "account";
        $show = "用户";
        if ($isEmail) {
            $key = "email";
            $show = "邮箱";
        }
        $sql = "SELECT * FROM " . DATABASE_NAME . ".`user` WHERE " . $key . "='" . $account . "'";
        $result = mysqli_query($con, $sql);
        if (mysqli_num_rows($result) > 0) {
            $row = mysqli_fetch_assoc($result);
            $truePassWord = $row['password'];
            if ($passWord == $truePassWord) {
                $enable = $row['enable'];
                $oldAppID = $row['appID'];
                if ($appID != $oldAppID || $enable == "verification") {
                    //是否有验证码没过期
                    $timeNumber = time();
                    $nowTime = date("Y-m-d H:i:s", $timeNumber);
                    $sqlCode = "SELECT * FROM " . DATABASE_NAME . ".`verification_code` WHERE account='" . $row['account'] . "' AND type='verification' AND expirationTime>='" . $nowTime . "' AND enable = 'true'";
                    $resultCode = mysqli_query($con, $sqlCode);
                    if (mysqli_num_rows($resultCode) > 0) {
                        $rowCode = mysqli_fetch_assoc($resultCode);
                        echo createResponse(SUCCESS_CODE, "请在 " . $rowCode['expirationTime'] . " 后，获取新的验证码。", null);
                    } else {
                        $keyCode = createUniqueCode();
                        //10分钟有效
                        $endTime = date("Y-m-d H:i:s", strtotime("+10 minute", $timeNumber));
                        send($row['email'], "请验证您的设备", "<p>" . $row['userName'] . "，您好！</p>
                    <p>我们检测到您登录帐户" . $account . "，在未经认证的设备上登录。需要进行安全认证。</p>
                    <p>登录所需的令牌验证码为：</p>
                    <h1><font color=\"#FF0000\">" . $keyCode . "</font></h1>
                    <p>此验证码在 " . $endTime . " 前有效。</p>
                    <p>令牌验证码是完成登录所必需的。没有人能够不访问这封电子邮件就访问您的帐户。</p>
                    <hr>
                    <p>此通知已发送至与您的 铁锈助手 帐户关联的电子邮件地址。</p>
                    <p>这封电子邮件由系统自动生成，请勿回复。如果您需要额外帮助，请加入 <a href=\"https://jq.qq.com/?_wv=1027&k=fg3CUxiI\">铁锈助手官方群</a>。</p>
                    <p>祝您生活愉快！</p>
                    <p>-ColdMint</p>", false);
                        $updata = "UPDATE " . DATABASE_NAME . ".`user` SET `enable` ='verification' WHERE " . $key . " = '" . $account . "'";
                        $in = "INSERT INTO " . DATABASE_NAME . ".`verification_code`(`account`, `code`, `createTime`, `expirationTime`, `type`, `enable`) VALUES ('" . $row['account'] . "', '" . $keyCode . "', '" . $nowTime . "', '" . $endTime . "', 'verification', 'true')";
                        mysqli_query($con, $in);
                        mysqli_query($con, $updata);
                        echo createResponse(SUCCESS_CODE, "已发送验证邮件", null);
                        return;
                    }
                } else {
                    echo createResponse(ERROR_CODE, "此账户，无需验证", null);
                }
            } else {
                //不能修改
                echo createResponse(ERROR_CODE, "密码错误", null);
            }
        } else {
            echo createResponse(ERROR_CODE, "找不到" . $show, null);
        }
        mysqli_close($con);
    }
}

/**
 * 登录
 * @param $account 账号
 * @param $passWord 密码
 */
function login($account, $passWord, $appID, $isEmail)
{
    $con = mysqli_connect(SERVERNAME, LOCALHOST, PASSWORD);
    mysqli_select_db($con, DATABASE_NAME);
    if (!$con) {
        echo createResponse(ERROR_CODE, "链接数据库出错。", null);
        return;
    } else {
        $key = "account";
        $show = "用户";
        if ($isEmail) {
            $key = "email";
            $show = "邮箱";
        }
        $sql = "SELECT * FROM " . DATABASE_NAME . ".`user` WHERE " . $key . "='" . $account . "'";
        $result = mysqli_query($con, $sql);
        if (mysqli_num_rows($result) > 0) {
            $row = mysqli_fetch_assoc($result);
            $truePassWord = $row['password'];
            $enable = $row['enable'];
            if ($enable != "true" && $enable != "verification") {
                //不能修改
                echo createResponse(ERROR_CODE, "请先激活您的账户", null);
                return;
            }


            if ($passWord != $truePassWord) {
                //不能修改
                echo createResponse(ERROR_CODE, "密码错误", null);
                return;
            }

            //是否可用
            $nowTime = time();
            $rowTime = null;
            if(ENABLE_PAYMENT){
                $rowTime = $row['expirationTime'];
            }else{
                $row['expirationTime'] = "forever";
                $rowTime = $row['expirationTime'];
            }
            $activation = true;
            if ($rowTime != "forever") {
                $expirationTime = strtotime($rowTime);
                if ($nowTime > $expirationTime) {
                    $activation = false;
                }
            }
            $banTime = $row['banTime'];
            if (!empty($banTime)) {
                if ($banTime == "forever") {
                    echo createResponse(ERROR_CODE, "您的账号已被永久封禁。", null);
                    return;
                } else {
                    $banTimeNum = strtotime($banTime);
                    if ($nowTime < $banTimeNum) {
                        echo createResponse(ERROR_CODE, "您的账号已被封禁至" . $banTime, null);
                        return;
                    }
                }
            }
            $row['activation'] = $activation;
            $oldAppID = $row['appID'];
            if ($appID != $oldAppID || $enable == "verification") {

                echo createResponse(ERROR_CODE, "请更改登录设备", null);
                return;
            } else {
                $token = uuid();
                $updata = "UPDATE " . DATABASE_NAME . ".`user` SET `loginTime` = '" . date("Y-m-d H:i:s", $nowTime) . "' WHERE " . $key . " = '" . $account . "'";
                mysqli_query($con, $updata);
                $updataToken = "UPDATE " . DATABASE_NAME . ".`user` SET `token` = '" . $token . "' WHERE " . $key . " = '" . $account . "'";
                mysqli_query($con, $updataToken);
                $updataIp = "UPDATE " . DATABASE_NAME . ".`user` SET `ip` = '" . getIp() . "' WHERE " . $key . " = '" . $account . "'";
                mysqli_query($con, $updataIp);
                $arr = array(
                    "token" => $token,
                    "expirationTime" => $row['expirationTime'],
                    "activation" => $activation,
                    "account" => $row['account']
                );
                updateIp($account);
                addCoinIfNeed($account);
                echo createResponse(SUCCESS_CODE, "登录成功", $arr);
            }
        } else {
            //不能修改
            echo createResponse(ERROR_CODE, "找不到" . $show, null);
        }
    }
    mysqli_close($con);
}

/**
 * 启用账号方法
 * @param $account 用户
 * @param $uuid 激活码
 * @return bool 是否启用成功
 */
function enableAccount($account, $uuid, $isEmail)
{
    if ($uuid == "true") {
        echo createResponse(ERROR_CODE, "激活失败。", null);
        return false;
    }
    $con = mysqli_connect(SERVERNAME, LOCALHOST, PASSWORD);
    mysqli_select_db($con, DATABASE_NAME);

    if (!$con) {
        echo createResponse(ERROR_CODE, "链接数据库出错。", null);
        return false;
    } else {
        $key = "account";
        $show = "用户";
        if ($isEmail) {
            $key = "email";
            $show = "邮箱";
        }
        $sql = "SELECT * FROM " . DATABASE_NAME . ".`user` WHERE `enable`='" . $uuid . "' AND  " . $key . "='" . $account . "'";
        $result = mysqli_query($con, $sql);
        if (mysqli_num_rows($result) > 0) {
            $sql2 = "UPDATE " . DATABASE_NAME . ".`user` SET `enable` = 'true' WHERE `enable` = '" . $uuid . "' AND  " . $key . "='" . $account . "'";
            $sql3 = "UPDATE " . DATABASE_NAME . ".`verification_code` SET `enable` = 'false' WHERE `type` = 'register' AND  " . $key . "='" . $account . "'";
            mysqli_query($con, $sql3);
            if (mysqli_query($con, $sql2)) {
                echo createResponse(SUCCESS_CODE, "激活成功。", null);
            } else {
                echo createResponse(ERROR_CODE, "激活失败。", mysqli_error($con));
                return false;
            }
        } else {
            echo createResponse(ERROR_CODE, "无效的激活码，使用" . $show, null);
        }
    }
    mysqli_close($con);
    return true;
}
