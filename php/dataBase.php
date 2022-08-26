<?php
/*数据库链接 */
include "conf.php";

if (empty($_REQUEST['action'])) {
    echo nullValuePrompt("action");
    return;
}

switch ($_REQUEST['action']) {
    case "initDataBase":
        echo "<h3>数据表状态</h3><table border=\"1\"><tr><td>表名</td><td>作用</td><td>状态</td></tr>";
        createDataBase();
        createFeedbackTable();
        createUserTable();
        createModTable();
        createCommunityTable();
        createFollowRecordTable();
        createModComments();
        createActivation();
        createOrder();
        createDynamicTable();
        createReportRecordTable();
        createCouponsTable();
        createIpTable();
        createVersionTable();
        createBannerTable();
        createAppUpdateTable();
        createSearchRecordTable();
        createErrorRecordTable();
        createTemplatePackageTable();
        createTemplateTable();
        createSubscribeRecordTable();
        createVerificationCode();
        echo "</table>";
        break;
    default:
        echo "您访问的页面无效";
}

/*初始化计划 */
function initPlan()
{
    $con = mysqli_connect(SERVERNAME, LOCALHOST, PASSWORD);
    mysqli_select_db($con, DATABASE_NAME);
    if (!$con) {
        echo createResponse(ERROR_CODE, "链接数据库出错。", null);
        return;
    } else {
        //INSERT INTO `rust`.`purchase_plan`(`id`, `name`, `describe`, `price`, `limit`, `time`) VALUES ('313', '3123', '3213', 3131, 'lock', '3213')
        $sql = "INSERT INTO " . DATABASE_NAME . ".`purchase_plan`(`id`, `name`, `describe`, `price`, `limit`, `time`) VALUES ('month', '按月', '购买1个月', 3, 'AnyTime', '+1 month')";
        mysqli_query($con, $sql);
        $sql = "INSERT INTO " . DATABASE_NAME . ".`purchase_plan`(`id`, `name`, `describe`, `price`, `limit`, `time`) VALUES ('quarter', '按季度', '购买3个月', 9, 'AnyTime', '+3 month')";
        mysqli_query($con, $sql);
        $sql = "INSERT INTO " . DATABASE_NAME . ".`purchase_plan`(`id`, `name`, `describe`, `price`, `limit`, `time`) VALUES ('halfYear', '半年', '购买6个月', 18, 'AnyTime', '+6 month')";
        mysqli_query($con, $sql);
        $sql = "INSERT INTO " . DATABASE_NAME . ".`purchase_plan`(`id`, `name`, `describe`, `price`, `limit`, `time`) VALUES ('year', '一年', '购买12个月', 36, 'AnyTime', '+1 year')";
        mysqli_query($con, $sql);
        $sql = "INSERT INTO " . DATABASE_NAME . ".`purchase_plan`(`id`, `name`, `describe`, `price`, `limit`, `time`) VALUES ('forever', '永久', '永久激活', 40, 'lock', 'forever')";
        mysqli_query($con, $sql);
    }
}




/**
 * 删除数据库
 */
function deleteDataBase()
{
    $con = mysqli_connect(SERVERNAME, LOCALHOST, PASSWORD);
    if (!$con) {
        echo createResponse(ERROR_CODE, "链接数据库出错。", null);
        return false;
    }
    $sql = "DROP DATABASE " . DATABASE_NAME;
    if (mysqli_query($con, $sql)) {
        echo createResponse(SUCCESS_CODE, "删除数据库成功。", null);
    } else {
        echo createResponse(ERROR_CODE, "删除数据库失败。", mysqli_error($con));
        return false;
    }
    mysqli_close($con);
    return true;
}




/**
 * 创建数据库(不使用)
 */
function createDataBase()
{
    $con = mysqli_connect(SERVERNAME, LOCALHOST, PASSWORD);
    if (!$con) {
        echo createResponse(ERROR_CODE, "链接数据库出错。", null);
        return false;
    }
    $sql = "CREATE DATABASE " . DATABASE_NAME;
    if (mysqli_query($con, $sql)) {
        echo "<tr><td>数据库</td><td>数据库。</td><td>成功</td></tr>";
    } else {
        echo "<tr><td>数据库</td><td>" . mysqli_error($con) . "</td><td>失败</td></tr>";
        return false;
    }
    mysqli_close($con);
    return true;
}

//创建验证码表
function createVerificationCode(){
    $con = mysqli_connect(SERVERNAME, LOCALHOST, PASSWORD);
    mysqli_select_db($con, DATABASE_NAME);
    if (!$con) {
        echo createResponse(ERROR_CODE, "链接数据库出错。", null);
        return false;
    } else {
        $sql = "CREATE TABLE `verification_code` (
            `id` int(11) NOT NULL AUTO_INCREMENT,
            `account` varchar(20) DEFAULT NULL,
            `code` varchar(20) DEFAULT NULL,
            `createTime` varchar(20) DEFAULT NULL,
            `expirationTime` varchar(20) DEFAULT NULL,
            `type` varchar(20) DEFAULT NULL,
            `enable` varchar(10) DEFAULT NULL,
            PRIMARY KEY (`id`)
          ) ENGINE=InnoDB AUTO_INCREMENT=10 DEFAULT CHARSET=utf8mb4;";
        if (mysqli_query($con, $sql)) {
            echo "<tr><td>验证码记录表</td><td>存放服务器发送的验证码信息。</td><td>成功</td></tr>";
        } else {
            echo "<tr><td>验证码记录表</td><td>" . mysqli_error($con) . "</td><td>失败</td></tr>";
            return false;
        }
    }
    mysqli_close($con);
    return true;
}

//创建订阅表
function createSubscribeRecordTable(){
    $con = mysqli_connect(SERVERNAME, LOCALHOST, PASSWORD);
    mysqli_select_db($con, DATABASE_NAME);
    if (!$con) {
        echo createResponse(ERROR_CODE, "链接数据库出错。", null);
        return false;
    } else {
        $sql = "CREATE TABLE `subscribe_record` (
            `id` int(11) NOT NULL AUTO_INCREMENT,
            `account` varchar(20) DEFAULT NULL,
            `packageId` varchar(20) DEFAULT NULL,
            `time` varchar(20) DEFAULT NULL,
            PRIMARY KEY (`id`)
          ) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4;";
        if (mysqli_query($con, $sql)) {
            echo "<tr><td>模板包订阅记录表</td><td>存放模板包信息。</td><td>成功</td></tr>";
        } else {
            echo "<tr><td>模板包订阅记录表</td><td>" . mysqli_error($con) . "</td><td>失败</td></tr>";
            return false;
        }
    }
    mysqli_close($con);
    return true;
}

//创建模板包表
function createTemplatePackageTable(){
    $con = mysqli_connect(SERVERNAME, LOCALHOST, PASSWORD);
    mysqli_select_db($con, DATABASE_NAME);
    if (!$con) {
        echo createResponse(ERROR_CODE, "链接数据库出错。", null);
        return false;
    } else {
        $sql = "CREATE TABLE `template_package` (
            `id` varchar(20) NOT NULL,
            `name` varchar(20) DEFAULT NULL,
            `describe` varchar(255) DEFAULT NULL,
            `developer` varchar(20) DEFAULT NULL,
            `versionName` varchar(20) DEFAULT NULL,
            `versionNumber` int(11) DEFAULT NULL,
            `appVersionName` varchar(50) DEFAULT NULL,
            `appVersionNumber` int(11) DEFAULT NULL,
            `public` varchar(20) DEFAULT 'true',
            `createTime` varchar(20) DEFAULT NULL,
            `modificationTime` varchar(20) DEFAULT NULL,
            `downloadNumber` int(11) DEFAULT 0,
            `templateNumber` int(11) DEFAULT 0,
            PRIMARY KEY (`id`)
          ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;";
        if (mysqli_query($con, $sql)) {
            echo "<tr><td>模板包表</td><td>存放模板包信息。</td><td>成功</td></tr>";
        } else {
            echo "<tr><td>模板包表</td><td>" . mysqli_error($con) . "</td><td>失败</td></tr>";
            return false;
        }
    }
    mysqli_close($con);
    return true;
}

//创建模板包表
function createTemplateTable(){
    $con = mysqli_connect(SERVERNAME, LOCALHOST, PASSWORD);
    mysqli_select_db($con, DATABASE_NAME);
    if (!$con) {
        echo createResponse(ERROR_CODE, "链接数据库出错。", null);
        return false;
    } else {
        $sql = "CREATE TABLE `template_list` (
            `id` varchar(20) NOT NULL,
            `title` varchar(255) DEFAULT NULL,
            `content` text DEFAULT NULL,
            `packageId` varchar(20) DEFAULT NULL,
            `developer` varchar(20) DEFAULT NULL,
            `createTime` varchar(20) DEFAULT NULL,
            `modificationTime` varchar(20) DEFAULT NULL,
            `deleted` varchar(20) DEFAULT 'false',
            PRIMARY KEY (`id`)
          ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;";
        if (mysqli_query($con, $sql)) {
            echo "<tr><td>模板表</td><td>存放模板信息。</td><td>成功</td></tr>";
        } else {
            echo "<tr><td>模板表</td><td>" . mysqli_error($con) . "</td><td>失败</td></tr>";
            return false;
        }
    }
    mysqli_close($con);
    return true;
}

function createErrorRecordTable(){
    $con = mysqli_connect(SERVERNAME, LOCALHOST, PASSWORD);
    mysqli_select_db($con, DATABASE_NAME);
    if (!$con) {
        echo createResponse(ERROR_CODE, "链接数据库出错。", null);
        return false;
    } else {
        $sql = "CREATE TABLE `error_record` (
            `id` int(11) NOT NULL AUTO_INCREMENT,
            `message` text DEFAULT NULL,
            `time` varchar(20) DEFAULT NULL,
            `versionName` varchar(50) DEFAULT NULL,
            `versionNumber` int(11) DEFAULT NULL,
            PRIMARY KEY (`id`)
          ) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4;";
        if (mysqli_query($con, $sql)) {
            echo "<tr><td>错误记录表</td><td>存放错误异常记录。</td><td>成功</td></tr>";
        } else {
            echo "<tr><td>错误记录表</td><td>" . mysqli_error($con) . "</td><td>失败</td></tr>";
            return false;
        }
    }
    mysqli_close($con);
    return true;
}

function createSearchRecordTable(){
    $con = mysqli_connect(SERVERNAME, LOCALHOST, PASSWORD);
    mysqli_select_db($con, DATABASE_NAME);
    if (!$con) {
        echo createResponse(ERROR_CODE, "链接数据库出错。", null);
        return false;
    } else {
        $sql = "CREATE TABLE `search_record` (
            `keyword` varchar(255) NOT NULL,
            `number` int(11) DEFAULT NULL,
            `creationTime` varchar(20) DEFAULT NULL,
            `latestTime` varchar(20) DEFAULT NULL,
            PRIMARY KEY (`keyword`)
          ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;";
        if (mysqli_query($con, $sql)) {
            echo "<tr><td>搜索记录表</td><td>存放搜索记录。</td><td>成功</td></tr>";
        } else {
            echo "<tr><td>搜索记录表</td><td>" . mysqli_error($con) . "</td><td>失败</td></tr>";
            return false;
        }
    }
    mysqli_close($con);
    return true;
}

function createAppUpdateTable(){
    $con = mysqli_connect(SERVERNAME, LOCALHOST, PASSWORD);
    mysqli_select_db($con, DATABASE_NAME);
    if (!$con) {
        echo createResponse(ERROR_CODE, "链接数据库出错。", null);
        return false;
    } else {
        $sql = "CREATE TABLE `app_update` (
            `id` int(11) NOT NULL AUTO_INCREMENT,
            `title` varchar(30) DEFAULT NULL,
            `content` varchar(255) DEFAULT NULL,
            `isBeta` varchar(255) DEFAULT NULL,
            `versionNumber` int(11) DEFAULT NULL,
            `versionName` varchar(30) DEFAULT NULL,
            `forced` varchar(255) DEFAULT NULL,
            `link` varchar(255) DEFAULT NULL,
            `time` varchar(255) DEFAULT NULL,
            PRIMARY KEY (`id`)
          ) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4;";
        if (mysqli_query($con, $sql)) {
            echo "<tr><td>App更新表</td><td>存放App更新数据。</td><td>成功</td></tr>";
        } else {
            echo "<tr><td>App更新表</td><td>" . mysqli_error($con) . "</td><td>失败</td></tr>";
            return false;
        }
    }
    mysqli_close($con);
    return true;
}

function createBannerTable(){
    $con = mysqli_connect(SERVERNAME, LOCALHOST, PASSWORD);
    mysqli_select_db($con, DATABASE_NAME);
    if (!$con) {
        echo createResponse(ERROR_CODE, "链接数据库出错。", null);
        return false;
    } else {
        $sql = "CREATE TABLE `banner` (
            `id` int(11) NOT NULL AUTO_INCREMENT,
            `title` varchar(30) DEFAULT NULL,
            `owner` varchar(20) DEFAULT NULL,
            `picture` varchar(255) DEFAULT NULL,
            `link` varchar(255) DEFAULT NULL,
            `createTime` varchar(20) DEFAULT NULL,
            `expirationTime` varchar(20) DEFAULT NULL,
            PRIMARY KEY (`id`)
          ) ENGINE=InnoDB AUTO_INCREMENT=33 DEFAULT CHARSET=utf8mb4;";
        if (mysqli_query($con, $sql)) {
            echo "<tr><td>banner表</td><td>存放轮播图数据。</td><td>成功</td></tr>";
        } else {
            echo "<tr><td>banner表</td><td>" . mysqli_error($con) . "</td><td>失败</td></tr>";
            return false;
        }
    }
    mysqli_close($con);
    return true;
}


/**版本表 */
function createVersionTable()
{
    $con = mysqli_connect(SERVERNAME, LOCALHOST, PASSWORD);
    mysqli_select_db($con, DATABASE_NAME);
    if (!$con) {
        echo createResponse(ERROR_CODE, "链接数据库出错。", null);
        return false;
    } else {
        $sql = "CREATE TABLE `mod_versions` (
            `id` varchar(30) NOT NULL,
            `versionName` varchar(30) DEFAULT NULL,
            `versionNumber` int(11) DEFAULT NULL,
            `updateLog` varchar(255) DEFAULT NULL,
            `time` varchar(20) DEFAULT NULL
          ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;;";
        if (mysqli_query($con, $sql)) {
            echo "<tr><td>版本表</td><td>存放模组版本更新记录。</td><td>成功</td></tr>";
        } else {
            echo "<tr><td>版本表</td><td>" . mysqli_error($con) . "</td><td>失败</td></tr>";
            return false;
        }
    }
    mysqli_close($con);
    return true;
}

function createIpTable()
{
    $con = mysqli_connect(SERVERNAME, LOCALHOST, PASSWORD);
    mysqli_select_db($con, DATABASE_NAME);
    if (!$con) {
        echo createResponse(ERROR_CODE, "链接数据库出错。", null);
        return false;
    } else {
        $sql = "CREATE TABLE `ip_record` (
            `ip` varchar(30) NOT NULL,
            `time` varchar(30) DEFAULT NULL,
            `country` varchar(50) DEFAULT 'unknown',
            `province` varchar(255) DEFAULT 'unknown',
            `city` varchar(255) DEFAULT 'unknown',
            `area` varchar(255) DEFAULT 'unknown',
            `available` varchar(30) DEFAULT NULL,
            `count` varchar(30) DEFAULT NULL,
            PRIMARY KEY (`ip`)
          ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;";
        if (mysqli_query($con, $sql)) {
            echo "<tr><td>ip表</td><td>存放ip访问数据。</td><td>成功</td></tr>";
        } else {
            echo "<tr><td>ip表</td><td>" . mysqli_error($con) . "</td><td>失败</td></tr>";
            return false;
        }
    }
    mysqli_close($con);
    return true;
}

/**
 * 创建反馈表
 */
function createFeedbackTable()
{
    $con = mysqli_connect(SERVERNAME, LOCALHOST, PASSWORD);
    mysqli_select_db($con, DATABASE_NAME);
    if (!$con) {
        echo createResponse(ERROR_CODE, "链接数据库出错。", null);
        return false;
    } else {
        $sql = "CREATE TABLE `feedback` (
                `ID` int(10) unsigned NOT NULL AUTO_INCREMENT,
                `Content` varchar(255) DEFAULT NULL,
                `Time` datetime DEFAULT NULL,
                `VersionName` varchar(255) DEFAULT NULL,
                PRIMARY KEY (`ID`)
              ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;";
        if (mysqli_query($con, $sql)) {
            echo "<tr><td>反馈表</td><td>存放反馈数据。</td><td>成功</td></tr>";
        } else {
            echo "<tr><td>反馈表</td><td>" . mysqli_error($con) . "</td><td>失败</td></tr>";
            return false;
        }
    }
    mysqli_close($con);
    return true;
}

/**
 * 创建用户表
 */
function createUserTable()
{
    $con = mysqli_connect(SERVERNAME, LOCALHOST, PASSWORD);
    mysqli_select_db($con, DATABASE_NAME);
    if (!$con) {
        echo createResponse(ERROR_CODE, "链接数据库出错。", null);
        return;
    } else {
        $sql = "CREATE TABLE `user` (
            `account` varchar(20) NOT NULL,
            `password` varchar(20) NOT NULL,
            `token` varchar(255) NOT NULL,
            `userName` varchar(20) DEFAULT NULL,
            `email` varchar(30) DEFAULT NULL,
            `headIcon` varchar(255) DEFAULT NULL,
            `permission` int(11) DEFAULT 3,
            `appID` varchar(36) DEFAULT NULL,
            `enable` varchar(36) DEFAULT NULL,
            `gender` tinyint(1) DEFAULT 1,
            `creationTime` varchar(20) DEFAULT NULL,
            `loginTime` varchar(20) DEFAULT NULL,
            `expirationTime` varchar(20) DEFAULT NULL,
            `banTime` varchar(20) DEFAULT NULL,
            `ip` varchar(255) DEFAULT NULL,
            PRIMARY KEY (`account`) USING BTREE,
            UNIQUE KEY `email` (`email`),
            UNIQUE KEY `userName` (`userName`)
          ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;";
        if (mysqli_query($con, $sql)) {
            echo "<tr><td>用户表</td><td>存放用户数据。</td><td>成功</td></tr>";
        } else {
            echo "<tr><td>用户表</td><td>" . mysqli_error($con) . "</td><td>失败</td></tr>";
        }
    }
    mysqli_close($con);
}

/*创建订单表 */
function createOrder()
{
    $con = mysqli_connect(SERVERNAME, LOCALHOST, PASSWORD);
    mysqli_select_db($con, DATABASE_NAME);
    if (!$con) {
        echo createResponse(ERROR_CODE, "链接数据库出错。", null);
        return;
    } else {
        $sql = "CREATE TABLE `order` (
            `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
            `name` varchar(255) DEFAULT NULL,
            `account` varchar(255) DEFAULT NULL,
            `addTime` varchar(255) DEFAULT NULL,
            `price` decimal(10,2) DEFAULT NULL,
            `originalPrice` decimal(10,2) DEFAULT NULL,
            `createTime` varchar(255) DEFAULT NULL,
            `flag` varchar(40) DEFAULT NULL,
            `state` varchar(255) DEFAULT NULL,
            PRIMARY KEY (`id`)
          ) ENGINE=InnoDB AUTO_INCREMENT=17 DEFAULT CHARSET=utf8mb4;";
        if (mysqli_query($con, $sql)) {
            echo "<tr><td>订单表</td><td>存放购买订单。</td><td>成功</td></tr>";
        } else {
            echo "<tr><td>订单表</td><td>" . mysqli_error($con) . "</td><td>失败</td></tr>";
        }
    }
    mysqli_close($con);
}

/*创建动态表 */
function createDynamicTable()
{
    $con = mysqli_connect(SERVERNAME, LOCALHOST, PASSWORD);
    mysqli_select_db($con, DATABASE_NAME);
    if (!$con) {
        echo createResponse(ERROR_CODE, "链接数据库出错。", null);
        return;
    } else {
        $sql = "CREATE TABLE `dynamic` (
            `id` int(11) NOT NULL AUTO_INCREMENT,
            `account` varchar(20) NOT NULL,
            `content` varchar(255) DEFAULT NULL,
            `visible` varchar(30) DEFAULT 'true',
            `time` varchar(255) DEFAULT NULL,
            PRIMARY KEY (`id`)
          ) ENGINE=InnoDB AUTO_INCREMENT=23 DEFAULT CHARSET=utf8mb4;";
        if (mysqli_query($con, $sql)) {
            echo "<tr><td>动态表</td><td>存放用户动态。</td><td>成功</td></tr>";
        } else {
            echo "<tr><td>动态表</td><td>" . mysqli_error($con) . "</td><td>失败</td></tr>";
        }
    }
    mysqli_close($con);
}

/*
创建激活表
 */

function createActivation()
{
    $con = mysqli_connect(SERVERNAME, LOCALHOST, PASSWORD);
    mysqli_select_db($con, DATABASE_NAME);
    if (!$con) {
        echo createResponse(ERROR_CODE, "链接数据库出错。", null);
        return;
    } else {
        $sql = "CREATE TABLE `purchase_plan` (
            `id` varchar(255) NOT NULL,
            `name` varchar(255) DEFAULT NULL,
            `describe` varchar(255) DEFAULT NULL,
            `price` decimal(10,2) DEFAULT NULL,
            `limit` varchar(255) DEFAULT 'AnyTime',
            `time` varchar(255) DEFAULT NULL,
            PRIMARY KEY (`id`)
          ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;";
        if (mysqli_query($con, $sql)) {
            initPlan();
            echo "<tr><td>激活套餐表</td><td>存放激活助手的计划，已初始化数据。</td><td>成功</td></tr>";
        } else {
            echo "<tr><td>激活套餐表</td><td>" . mysqli_error($con) . "</td><td>失败</td></tr>";
        }
    }
    mysqli_close($con);
}

/**
 * 创建模组表
 */
function createModTable()
{
    $con = mysqli_connect(SERVERNAME, LOCALHOST, PASSWORD);
    mysqli_select_db($con, DATABASE_NAME);
    if (!$con) {
        echo createResponse(ERROR_CODE, "链接数据库出错。", null);
        return;
    } else {
        $sql = "CREATE TABLE `mod` (
            `id` varchar(30) NOT NULL,
            `name` varchar(20) DEFAULT NULL,
            `describe` varchar(500) DEFAULT NULL,
            `icon` varchar(255) DEFAULT NULL,
            `tags` varchar(255) DEFAULT NULL,
            `screenshots` varchar(255) DEFAULT NULL,
            `developer` varchar(20) DEFAULT NULL,
            `link` varchar(255) DEFAULT NULL,
            `downloadNumber` int(11) DEFAULT NULL,
            `versionNumber` int(11) DEFAULT NULL,
            `versionName` varchar(20) DEFAULT NULL,
            `updateTime` varchar(20) DEFAULT NULL,
            `creationTime` varchar(20) DEFAULT NULL,
            `unitNumber` int(11) DEFAULT 0,
            `hidden` tinyint(1) DEFAULT 1,
            PRIMARY KEY (`id`),
            UNIQUE KEY `name` (`name`) USING BTREE
          ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;";
        if (mysqli_query($con, $sql)) {
            echo "<tr><td>模组表</td><td>存放模组信息。</td><td>成功</td></tr>";
        } else {
            echo "<tr><td>模组表</td><td>" . mysqli_error($con) . "</td><td>失败</td></tr>";
        }
    }
    mysqli_close($con);
}



/**
 * 创建社交表
 */
function createCommunityTable()
{
    $con = mysqli_connect(SERVERNAME, LOCALHOST, PASSWORD);
    mysqli_select_db($con, DATABASE_NAME);
    if (!$con) {
        echo createResponse(ERROR_CODE, "链接数据库出错。", null);
        return;
    } else {
        $sql = "CREATE TABLE `community` (
            `account` varchar(20) NOT NULL,
            `cover` varchar(255) DEFAULT NULL,
            `introduce` varchar(255) DEFAULT NULL,
            `fans` int(11) DEFAULT 0,
            `follower` int(11) DEFAULT 0,
            `praise` int(11) DEFAULT 0,
            PRIMARY KEY (`account`)
          ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;";
        if (mysqli_query($con, $sql)) {
            echo "<tr><td>社交表</td><td>存放社交信息，粉丝数，个人主页介绍等。</td><td>成功</td></tr>";
        } else {
            echo "<tr><td>社交表</td><td>" . mysqli_error($con) . "</td><td>失败</td></tr>";
        }
    }
    mysqli_close($con);
}

/*创建模组评论 */
function createModComments()
{
    $con = mysqli_connect(SERVERNAME, LOCALHOST, PASSWORD);
    mysqli_select_db($con, DATABASE_NAME);
    if (!$con) {
        echo createResponse(ERROR_CODE, "链接数据库出错。", null);
        return;
    } else {
        $sql = "CREATE TABLE `mod_comments` (
            `id` INT ( 10 ) UNSIGNED NOT NULL AUTO_INCREMENT,
            `modId` VARCHAR ( 30 ) DEFAULT NULL,
            `account` VARCHAR ( 20 ) DEFAULT NULL,
            `content` VARCHAR ( 255 ) DEFAULT NULL,
            `time` VARCHAR ( 30 ) DEFAULT NULL,
        PRIMARY KEY ( `id` ) USING BTREE 
        ) ENGINE = INNODB AUTO_INCREMENT = 3 DEFAULT CHARSET = utf8mb4;";
        if (mysqli_query($con, $sql)) {
            echo "<tr><td>模组评论表</td><td>存放模组评论信息。</td><td>成功</td></tr>";
        } else {
            echo "<tr><td>模组评论表</td><td>" . mysqli_error($con) . "</td><td>失败</td></tr>";
        }
    }
    mysqli_close($con);
}


/**
 * 创建关注记录表
 */
function createFollowRecordTable()
{
    $con = mysqli_connect(SERVERNAME, LOCALHOST, PASSWORD);
    mysqli_select_db($con, DATABASE_NAME);
    if (!$con) {
        echo createResponse(ERROR_CODE, "链接数据库出错。", null);
        return;
    } else {
        $sql = "CREATE TABLE `follow_record` (
            `id` int(11) NOT NULL AUTO_INCREMENT,
            `account` varchar(20) NOT NULL,
            `type` varchar(20) NOT NULL,
            `targetAccount` varchar(20) NOT NULL,
            `time` varchar(20) NOT NULL,
            PRIMARY KEY (`id`)
          ) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4;";
        if (mysqli_query($con, $sql)) {
            echo "<tr><td>关注记录表</td><td>存放用户的关注状态。</td><td>成功</td></tr>";
        } else {
            echo "<tr><td>关注记录表</td><td>" . mysqli_error($con) . "</td><td>失败</td></tr>";
        }
    }
    mysqli_close($con);
}

/**
 * 创建举报记录表
 */
function createReportRecordTable()
{
    $con = mysqli_connect(SERVERNAME, LOCALHOST, PASSWORD);
    mysqli_select_db($con, DATABASE_NAME);
    if (!$con) {
        echo createResponse(ERROR_CODE, "链接数据库出错。", null);
        return;
    } else {
        $sql = "CREATE TABLE `report_record` (
            `id` int(11) NOT NULL AUTO_INCREMENT,
            `account` varchar(20) DEFAULT NULL,
            `type` varchar(20) DEFAULT NULL,
            `target` varchar(20) DEFAULT NULL,
            `why` varchar(50) DEFAULT NULL,
            `describe` varchar(255) DEFAULT NULL,
            `admin` varchar(20) DEFAULT NULL,
            `state` int(11) DEFAULT NULL,
            `time` varchar(20) DEFAULT NULL,
            PRIMARY KEY (`id`)
          ) ENGINE=InnoDB AUTO_INCREMENT=31 DEFAULT CHARSET=utf8mb4;";
        if (mysqli_query($con, $sql)) {
            echo "<tr><td>举报记录表</td><td>存放举报记录。</td><td>成功</td></tr>";
        } else {
            echo "<tr><td>举报记录表</td><td>" . mysqli_error($con) . "</td><td>失败</td></tr>";
        }
    }
    mysqli_close($con);
}

/**
 * 创建折扣券表
 */
function createCouponsTable()
{
    $con = mysqli_connect(SERVERNAME, LOCALHOST, PASSWORD);
    mysqli_select_db($con, DATABASE_NAME);
    if (!$con) {
        echo createResponse(ERROR_CODE, "链接数据库出错。", null);
        return;
    } else {
        $sql = "CREATE TABLE `coupons` (
            `id` int(11) NOT NULL AUTO_INCREMENT,
            `name` varchar(20) DEFAULT NULL,
            `describe` varchar(255) DEFAULT NULL,
            `type` varchar(20) DEFAULT NULL,
            `value` varchar(20) DEFAULT NULL,
            `target` varchar(20) DEFAULT NULL,
            `num` int(11) DEFAULT NULL,
            `createTime` varchar(20) DEFAULT NULL,
            `expirationTime` varchar(20) DEFAULT NULL,
            PRIMARY KEY (`id`)
          ) ENGINE=InnoDB AUTO_INCREMENT=33 DEFAULT CHARSET=utf8mb4;";
        if (mysqli_query($con, $sql)) {
            echo "<tr><td>折扣券表</td><td>存放折扣券信息。</td><td>成功</td></tr>";
        } else {
            echo "<tr><td>折扣券表</td><td>" . mysqli_error($con) . "</td><td>失败</td></tr>";
        }
    }
    mysqli_close($con);
}
