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
    case "changePassword":
        if (empty($_POST['account'])) {
            echo nullValuePrompt("account");
            return;
        }
        if (empty($_POST['passWord'])) {
            echo nullValuePrompt("passWord");
            return;
        }
        modifyTheRecord($_POST['account'], "passWord", $_POST['passWord']);
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
        if (empty($_POST['account'])) {
            echo nullValuePrompt("account");
            return;
        }
        if (empty($_POST['userName'])) {
            echo nullValuePrompt("userName");
            return;
        }
        modifyTheRecord($_POST['account'], "userName", $_POST['userName']);
        break;
    case "getSocialInfo":
        //获取社交信息
        if (empty($_POST['account'])) {
            echo nullValuePrompt("account");
            return;
        }
        getInfo($_POST['account'], true);
        break;
    case "getInfo":
        if (empty($_POST['account'])) {
            echo nullValuePrompt("account");
            return;
        }
        getInfo($_POST['account'], false);
        break;
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
    case "updateSpaceInfo":
        if (empty($_POST['account'])) {
            echo nullValuePrompt("account");
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
        updateSpaceInfo($_POST['account'], $_POST['userName'], $_POST['introduce'], $_POST['gender'], $icon, $cover);
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
            $sql =  "SELECT account,userName,email,permission,loginTime,gender,enable FROM " . DATABASE_NAME . ".`user`";
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
function updateSpaceInfo($account, $userName, $introduce, $gender, $icon, $cover)
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
            $sqlUserName = "SELECT * FROM " . DATABASE_NAME . ".`user` WHERE userName='" . $userName . "'";
            $resultUserName = mysqli_query($con, $sqlUserName);
            if (mysqli_num_rows($resultUserName) > 0) {
                $row = mysqli_fetch_assoc($resultUserName);
                $useAccount = $row['account'];
                if ($useAccount != $account) {
                    echo createResponse(ERROR_CODE, "用户名已被占用。", "@event:用户名占用");
                    return;
                }
            }

            $updata = "UPDATE " . DATABASE_NAME . ".`user` SET `userName` = '" . $userName . "' WHERE `account` = '" . $account . "'";
            mysqli_query($con, $updata);
            $updata = "UPDATE " . DATABASE_NAME . ".`community` SET `introduce` = '" . $introduce . "' WHERE `account` = '" . $account . "'";
            mysqli_query($con, $updata);
            $updata = "UPDATE " . DATABASE_NAME . ".`user` SET `gender` = '" . $gender . "' WHERE `account` = '" . $account . "'";
            mysqli_query($con, $updata);
            $folder = "../user/" . iconv("UTF-8", "GBK", $account);
            if (!file_exists($folder)) {
                mkdir($folder, 0777, true);
            }
            if ($icon != null) {
                if (is_string($icon)) {
                    $updata = "UPDATE " . DATABASE_NAME . ".`user` SET `headIcon` = '" . $icon . "' WHERE `account` = '" . $account . "'";
                    mysqli_query($con, $updata);
                } else {
                    if (!empty($icon)) {
                        $newIcon = $folder . "/icon.png";
                        $move =  move_uploaded_file($icon["tmp_name"], $newIcon);
                        if ($move) {
                            $updata = "UPDATE " . DATABASE_NAME . ".`user` SET `headIcon` = '" . $newIcon . "' WHERE `account` = '" . $account . "'";
                            mysqli_query($con, $updata);
                        }
                    }
                }
            }

            if ($cover != null) {
                if (is_string($cover)) {
                    $updata = "UPDATE " . DATABASE_NAME . ".`community` SET `cover` = '" . $cover . "' WHERE `account` = '" . $account . "'";
                    mysqli_query($con, $updata);
                } else {
                    if (!empty($icon)) {
                        $newIcon = $folder . "/cover.png";
                        $move =  move_uploaded_file($cover["tmp_name"], $newIcon);
                        if ($move) {
                            $updata = "UPDATE " . DATABASE_NAME . ".`community` SET `cover` = '" . $newIcon . "' WHERE `account` = '" . $account . "'";
                            mysqli_query($con, $updata);
                        }
                    }
                }
            }
            echo createResponse(SUCCESS_CODE, "已更新", null);
        } else {
            echo createResponse(ERROR_CODE, "找不到用户" . $account, null);
        }
    }
    mysqli_close($con);
}

/*加载信息(是否为社交模式？忽略隐私信息) */
function getInfo($account, $social)
{
    $con = mysqli_connect(SERVERNAME, LOCALHOST, PASSWORD);
    mysqli_select_db($con, DATABASE_NAME);
    if (!$con) {
        echo createResponse(ERROR_CODE, "链接数据库出错。", null);
        return;
    } else {
        $sql = "SELECT * FROM " . DATABASE_NAME . ".`user` WHERE account='" . $account . "'";
        if ($social) {
            $sql =  "SELECT account,userName,email,permission,loginTime,gender,enable,expirationTime FROM " . DATABASE_NAME . ".`user` WHERE account='" . $account . "'";
        }
        $result = mysqli_query($con, $sql);
        if (mysqli_num_rows($result) > 0) {
            $row = mysqli_fetch_assoc($result);
            echo createResponse(SUCCESS_CODE, "获取成功", $row);
        } else {
            echo createResponse(ERROR_CODE, "找不到用户" . $account, null);
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
        $sql =  "SELECT account,userName,headIcon,email,permission,loginTime,gender,enable,expirationTime FROM " . DATABASE_NAME . ".`user` WHERE account='" . $account . "'";
        $result = mysqli_query($con, $sql);
        $row = mysqli_fetch_assoc($result);
        $sql2 =  "SELECT * FROM " . DATABASE_NAME . ".`community` WHERE account='" . $account . "'";
        $result2 = mysqli_query($con, $sql2);
        $row2 = mysqli_fetch_assoc($result2);
        $end = null;
        if ($row != null && $row2 != null) {
            $end = array_merge($row, $row2);
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
 * @param $account 账号
 * @param $key 键
 * @param $value 值
 */
function modifyTheRecord($account, $key, $value)
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
            $row = mysqli_fetch_assoc($result);
            $expirationTime = strtotime($row['expirationTime']);
            $updata = "UPDATE " . DATABASE_NAME . ".`user` SET `" . $key . "` = '" . $value . "' WHERE `account` = '" . $account . "'";
            if (mysqli_query($con, $updata)) {
                echo createResponse(SUCCESS_CODE, "修改成功", null);
            } else {
                echo createResponse(ERROR_CODE, "修改失败", null);
            }
        } else {
            echo createResponse(ERROR_CODE, "找不到用户" . $account, null);
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
                $createTime = date("Y-m-d H:i:s", $nowTime);
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

        $sql = "INSERT INTO " . DATABASE_NAME . ".`user`(`account`, `password`, `userName`, `email`,`appID`, `enable`, `creationTime`, `loginTime`, `expirationTime`,`ip`) VALUES ('" . $account . "', '" . $passWord . "', '" . $userName . "', '" . $email . "', '" . $appID . "', '" . $key . "', '" . $createTime . "', '" . $createTime . "', '" . $expirationTime . "','" . getIp() . "')";
        $sqlcommunity = "INSERT INTO " . DATABASE_NAME . ".`community`(`account`) VALUES ('" . $account . "')";
        $sqlLock2 = "INSERT INTO  " . DATABASE_NAME . ".`coupons`(`name`, `describe` , `type`, `value`, `target`, `num`, `createTime`,`expirationTime`) VALUES ('萌新折扣券', '萌新购买铁锈助手减免" . ((1 - DISCOUNT_VALUE) * 100) . "%', 'personal', '" . DISCOUNT_VALUE . "', '" . $account . "', 1, '" . $createTime . "','" . $expirationTime . "')";
        mysqli_query($con, $sqlLock2);
        mysqli_query($con, $sqlcommunity);
        if (mysqli_query($con, $sql)) {
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
            $trueKey = $row['appID'];
            if ($enable == "verification") {
                if ($trueKey == $keyCode) {
                    $updata = "UPDATE " . DATABASE_NAME . ".`user` SET `appID` = '" . $appID . "',`enable` ='true' WHERE " . $key . " = '" . $account . "'";
                    mysqli_query($con, $updata);
                    echo createResponse(SUCCESS_CODE, "验证成功", null);
                } else {
                    echo createResponse(ERROR_CODE, "验证码错误", null);
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

/*验证设备 */
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
                    $keyCode = createUniqueCode();
                    send($row['email'], "请验证您的设备", "<p>" . $row['userName'] . "，您好！</p>
                <p>我们检测到您登录帐户" . $account . "，在未经认证的设备上登录。需要进行安全认证。</p>
                <p>登录所需的令牌验证码为：</p>
                <h1><font color=\"#FF0000\">" . $keyCode . "</font></h1>
                <p>令牌验证码是完成登录所必需的。没有人能够不访问这封电子邮件就访问您的帐户。</p>
                <hr>
                <p>此通知已发送至与您的 铁锈助手 帐户关联的电子邮件地址。</p>
                <p>这封电子邮件由系统自动生成，请勿回复。如果您需要额外帮助，请加入 <a href=\"https://jq.qq.com/?_wv=1027&k=fg3CUxiI\">铁锈助手官方群</a>。</p>
                <p>祝您生活愉快！</p>
                <p>-ColdMint</p>", false);
                    $updata = "UPDATE " . DATABASE_NAME . ".`user` SET `appID` = '" . $keyCode . "',`enable` ='verification' WHERE " . $key . " = '" . $account . "'";
                    mysqli_query($con, $updata);
                    echo createResponse(SUCCESS_CODE, "已发送验证邮件", null);
                    return;
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
            $rowTime = $row['expirationTime'];
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
                    if ($nowTime > $banTimeNum) {
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
                echo createResponse(SUCCESS_CODE, "登录成功", $row);
                $updata = "UPDATE " . DATABASE_NAME . ".`user` SET `loginTime` = '" . date("Y-m-d H:i:s", $nowTime) . "' WHERE " . $key . " = '" . $account . "'";
                mysqli_query($con, $updata);
                //getIp()
                $updataIp = "UPDATE " . DATABASE_NAME . ".`user` SET `ip` = '" . getIp() . "' WHERE " . $key . " = '" . $account . "'";
                mysqli_query($con, $updataIp);
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