<?php
/*模组系统 （上传，下载，模组信息管理） */
require_once "conf.php";

if (!canUseIp()) {
    return;
}

if (empty($_REQUEST['action'])) {
    echo nullValuePrompt("action");
    return;
}

switch ($_REQUEST['action']) {
    case "update":
        if (empty($_POST['account'])) {
            echo nullValuePrompt("account");
            return;
        }
        if (empty($_POST['appID'])) {
            echo nullValuePrompt("appID");
            return;
        }
        if (empty($_POST['modId'])) {
            echo nullValuePrompt("modId");
            return;
        }
        if (empty($_POST['modName'])) {
            echo nullValuePrompt("modName");
            return;
        }
        if (empty($_POST['describe'])) {
            echo nullValuePrompt("describe");
            return;
        }
        if (empty($_POST['tags'])) {
            echo nullValuePrompt("tags");
            return;
        }
        if (empty($_POST['unitNumber'])) {
            echo nullValuePrompt("unitNumber");
            return;
        }
        if (empty($_FILES['file'])) {
            echo nullValuePrompt("file");
            return;
        }
        if (empty($_POST['versionName'])) {
            echo nullValuePrompt("versionName");
            return;
        }
        if (empty($_POST['updateLog'])) {
            echo nullValuePrompt("updateLog");
            return;
        }
        $icon = null;
        if (!empty($_FILES['icon'])) {
            $icon = $_FILES['icon'];
        } else if (!empty($_POST['icon'])) {
            $icon = $_POST['icon'];
        }
        if ($_FILES['file']["size"] > 52428800) {
            echo createResponse(ERROR_CODE, "模组不能大于50MB。", null);
            return;
        }
        updateMod($_POST['appID'], $_POST['modId'], $_POST['account'], $_POST['modName'], $_POST['describe'], $_POST['tags'], $_POST['versionName'], $_POST['updateLog'], $icon, $_FILES['file']);
        break;
    case "release":
        if (empty($_POST['account'])) {
            echo nullValuePrompt("account");
            return;
        }
        if (empty($_POST['appID'])) {
            echo nullValuePrompt("appID");
            return;
        }
        if (empty($_POST['modId'])) {
            echo nullValuePrompt("modId");
            return;
        }
        if (empty($_POST['modName'])) {
            echo nullValuePrompt("modName");
            return;
        }
        if (empty($_POST['describe'])) {
            echo nullValuePrompt("describe");
            return;
        }
        if (empty($_POST['tags'])) {
            echo nullValuePrompt("tags");
            return;
        }
        if (empty($_POST['unitNumber'])) {
            echo nullValuePrompt("unitNumber");
            return;
        }
        if (empty($_FILES['file'])) {
            echo nullValuePrompt("file");
            return;
        }
        if (empty($_POST['versionName'])) {
            echo nullValuePrompt("versionName");
            return;
        }
        $icon = null;
        if (!empty($_FILES['icon'])) {
            $icon = $_FILES['icon'];
        } else if (!empty($_POST['icon'])) {
            $icon = $_POST['icon'];
        }
        if ($_FILES['file']["size"] > 52428800) {
            echo createResponse(ERROR_CODE, "模组不能大于50MB。", null);
            return;
        }

        //可选 $_FILES['icon']
        releaseMod($_POST['appID'], $_POST['modId'], $_POST['account'], $_POST['modName'], $_POST['describe'], $_POST['tags'], $_POST['versionName'], $_POST['unitNumber'], $icon, $_FILES['file']);
        break;
    case "audit":
        if (empty($_POST['account'])) {
            echo nullValuePrompt("account");
            return;
        }
        if (empty($_POST['modId'])) {
            echo nullValuePrompt("modId");
            return;
        }
        if (empty($_POST['state'])) {
            echo nullValuePrompt("state");
            return;
        }
        auditMod($_POST['account'], $_POST['modId'], $_POST['state']);
        break;
    case "getInfo":
        if (empty($_POST['account'])) {
            echo nullValuePrompt("account");
            return;
        }
        if (empty($_POST['modId'])) {
            echo nullValuePrompt("modId");
            return;
        }
        getInfo($_POST['account'], $_POST['modId']);
        break;
    case "random":
        //随机推荐
        if (empty($_POST['number'])) {
            echo nullValuePrompt("number");
            return;
        }
        randomRecommended($_POST['number']);
        break;
    case "list":
        /*
        sortMode 可选参数
        -latestTime 按更新时间倒序
        -downloadNumber 按下载数倒序
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
        //可选的tag筛选（仅显示可见的内容）
        if (empty($_POST['tag'])) {
            getList(false, $sortMode, $limit);
        } else {
            getTagModList($_POST['tag']);
        }
        break;
    case "auditList":
        /*
        sortMode 可选参数
        -latestTime 按更新时间倒序
        -downloadNumber 按下载数倒序
        */
        $sortMode = null;
        if (!empty($_POST['sortMode'])) {
            $sortMode = $_POST['sortMode'];
        }
        $limit = null;
        if (!empty($_POST['limit'])) {
            $limit = $_POST['limit'];
        }
        getList(true, $sortMode, $limit);
        break;
    case "comments":
        if (empty($_POST['account'])) {
            echo nullValuePrompt("account");
            return;
        }
        if (empty($_POST['appId'])) {
            echo nullValuePrompt("appId");
            return;
        }
        if (empty($_POST['modId'])) {
            echo nullValuePrompt("modId");
            return;
        }
        if (empty($_POST['content'])) {
            echo nullValuePrompt("content");
            return;
        }
        sendComments($_POST['account'], $_POST['appId'], $_POST['modId'], $_POST['content']);
        break;
    case "commentsList":
        if (empty($_POST['modId'])) {
            echo nullValuePrompt("modId");
            return;
        }
        commentsList($_POST['modId']);
        break;
    case "userModList":
        if (empty($_POST['account'])) {
            echo nullValuePrompt("account");
            return;
        }
        /*
        sortMode 可选参数
        -latestTime 按更新时间倒序
        -downloadNumber 按下载数倒序
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

        getUserModList($_POST['account'], $sortMode, $limit, false);
        break;
    case "userModListAllInfo":
        if (empty($_POST['account'])) {
            echo nullValuePrompt("account");
            return;
        }
        /*
        sortMode 可选参数
        -latestTime 按更新时间倒序
        -downloadNumber 按下载数倒序
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
        getUserModList($_POST['account'], $sortMode, $limit, true);
        break;
    case "addDownloadNumber":
        if (empty($_POST['modId'])) {
            echo nullValuePrompt("modId");
            return;
        }
        addDownloadNum($_POST['modId']);
        break;
    case "soldOutMod":
        if (empty($_POST['account'])) {
            echo nullValuePrompt("account");
            return;
        }
        if (empty($_POST['modId'])) {
            echo nullValuePrompt("modId");
            return;
        }
        soldOutMod($_POST['account'], $_POST['modId']);
        break;
    case "afreshAuditMod":
        if (empty($_POST['account'])) {
            echo nullValuePrompt("account");
            return;
        }
        if (empty($_POST['modId'])) {
            echo nullValuePrompt("modId");
            return;
        }
        afreshAuditMod($_POST['account'], $_POST['modId']);
        break;
    case "soleRecommended":
        if (empty($_POST['account'])) {
            echo nullValuePrompt("account");
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
        getSoleRecommended($_POST['account'], $limit);
        break;
    case "getUpdateRecord":
        if (empty($_POST['modId'])) {
            echo nullValuePrompt("modId");
            return;
        }
        getUpdateRecord($_POST['modId']);
        break;
}



/*随机推荐*/
function randomRecommended($number)
{
    $con = mysqli_connect(SERVERNAME, LOCALHOST, PASSWORD);
    mysqli_select_db($con, DATABASE_NAME);
    if (!$con) {
        echo createResponse(ERROR_CODE, "链接数据库出错。", null);
        return;
    } else {
        if ($number < 2) {
            echo createResponse(ERROR_CODE, "至少获取两条随机推荐。", null);
            return;
        }
        $num = 0;
        $sql = "SELECT * FROM " . DATABASE_NAME . ".`mod` WHERE hidden='0'";
        $result = mysqli_query($con, $sql);
        if ($result != false && mysqli_num_rows($result) > 0) {
            $total = array();
            while ($row = mysqli_fetch_assoc($result)) {
                $total[$num] = $row;
                $num++;
            }
            $arraySize = count($total);
            //如果传入的数据比数组的最大数据长度还要大，那么将其设置为数组的长度。
            if ($number > $arraySize) {
                $number = $arraySize;
            }
            //随机的数组索引集合
            $roundIndex = array_rand($total, $number);
            $roundData = array();
            for ($i = 0; $i < $number; $i++) {
                $roundData[$i] = $total[$roundIndex[$i]];
            }
            echo createResponse(SUCCESS_CODE, "获取成功，共" . $number . "条记录", $roundData);
        } else {
            echo createResponse(ERROR_CODE, "没有模组。", null);
        }
        if ($result != false) {
            mysqli_free_result($result);
        }
    }
    mysqli_close($con);
}



/*获取独家推荐 */
function getSoleRecommended($account, $limit)
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
            $newSql = "SELECT id,name,`describe`,icon,developer,downloadNumber,`updateTime` FROM " . DATABASE_NAME . ".`mod`";
            $num = 0;
            $total = array();
            while ($row = mysqli_fetch_assoc($result)) {
                if ($num == 0) {
                    $newSql = $newSql . " WHERE (`developer`='" . $row['targetAccount'] . "'";
                } else {
                    $newSql = $newSql . " OR `developer`='" . $row['targetAccount'] . "'";
                }
                $num++;
            }
            $newSql = $newSql . ") AND hidden = 0 ORDER BY `updateTime` DESC";
            if ($limit != null) {
                $newSql = $newSql . " LIMIT " . $limit;
            }
            //$result2为数据库咨询结果集
            $result2 = mysqli_query($con, $newSql);
            if ($result2 != false && mysqli_num_rows($result2) > 0) {
                $num2 = 0;
                while ($row2 = mysqli_fetch_assoc($result2)) {
                    $total[$num2] = $row2;
                    $num2++;
                }
                echo createResponse(SUCCESS_CODE, "获取成功，共" . $num2 . "条记录", $total);
            } else {
                echo createResponse(ERROR_CODE, "没有模组", null);
            }
        } else {
            echo createResponse(ERROR_CODE, "没有关注记录", null);
        }
    }
    mysqli_close($con);
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

/*评论模组 */
function sendComments($account, $appID, $modId, $content)
{
    $con = mysqli_connect(SERVERNAME, LOCALHOST, PASSWORD);
    mysqli_select_db($con, DATABASE_NAME);
    if (!$con) {
        echo createResponse(ERROR_CODE, "链接数据库出错。", null);
        return;
    } else {
        $sqlUser = "SELECT * FROM " . DATABASE_NAME . ".`user` WHERE account='" . $account . "' AND appID='" . $appID . "'";
        $userResult = mysqli_query($con, $sqlUser);
        if (mysqli_num_rows($userResult) > 0) {
            $sqlMod = "SELECT * FROM " . DATABASE_NAME . ".`mod` WHERE id='" . $modId . "'";
            $modResult = mysqli_query($con, $sqlMod);
            if (mysqli_num_rows($modResult) > 0) {
                $createTime = date("Y-m-d H:i:s", time());
                $addSql = "INSERT INTO " . DATABASE_NAME . ".`mod_comments`(`modId`, `account`, `content`, `time`) VALUES ('" . $modId . "', '" . $account . "', '" . $content . "', '" . $createTime . "')";
                if (mysqli_query($con, $addSql)) {
                    echo createResponse(SUCCESS_CODE, "发布成功。", null);
                } else {
                    echo createResponse(ERROR_CODE, "发布失败。", null);
                }
            } else {
                echo createResponse(ERROR_CODE, "找不到id为" . $modId . "的模组。", null);
            }
        } else {
            echo createResponse(ERROR_CODE, "找不到名为" . $account . "的用户或appId验证失败。", null);
        }
    }
}


/*获取模组的更新日志 */
function getUpdateRecord($modId)
{
    $con = mysqli_connect(SERVERNAME, LOCALHOST, PASSWORD);
    mysqli_select_db($con, DATABASE_NAME);
    if (!$con) {
        echo createResponse(ERROR_CODE, "链接数据库出错。", null);
        return;
    } else {
        $sql = "SELECT * FROM " . DATABASE_NAME . ".`mod_versions` WHERE id='" . $modId . "' ORDER BY time DESC";
        $result = mysqli_query($con, $sql);
        if (mysqli_num_rows($result) > 0) {
            $total = array();
            $num = 0;
            while ($row = mysqli_fetch_assoc($result)) {
                $total[$num] = $row;
                $num++;
            }
            echo createResponse(SUCCESS_CODE, "获取成功，共" . $num . "条记录。", $total);
        } else {
            echo createResponse(ERROR_CODE, "找不到id为" . $modId . "的模组。", null);
        }
    }
    mysqli_close($con);
}

/*获取模组信息 */
function getInfo($account, $modId)
{
    $con = mysqli_connect(SERVERNAME, LOCALHOST, PASSWORD);
    mysqli_select_db($con, DATABASE_NAME);
    if (!$con) {
        echo createResponse(ERROR_CODE, "链接数据库出错。", null);
        return;
    } else {
        $sqlUser = "SELECT * FROM " . DATABASE_NAME . ".`user` WHERE account='" . $account . "'";
        $userResult = mysqli_query($con, $sqlUser);
        if (mysqli_num_rows($userResult) > 0) {
            $sqlMod = "SELECT * FROM " . DATABASE_NAME . ".`mod` WHERE id='" . $modId . "'";
            $modResult = mysqli_query($con, $sqlMod);
            if (mysqli_num_rows($modResult) > 0) {
                $userRow = mysqli_fetch_assoc($userResult);
                $modRow = mysqli_fetch_assoc($modResult);
                $developer = $modRow['developer'];
                $hidden = $modRow['hidden'];
                $permission = $userRow['permission'];
                if ($hidden == 1) {
                    if ($developer === $account) {
                        echo createResponse(SUCCESS_CODE, "获取成功。", $modRow);
                    } else {
                        if ($permission < 3) {
                            //是管理员
                            echo createResponse(SUCCESS_CODE, "获取成功。", $modRow);
                        } else {
                            echo createResponse(ERROR_CODE, "此模组未经审核无法查看。", null);
                        }
                    }
                } else {
                    echo createResponse(SUCCESS_CODE, "获取成功。", $modRow);
                }
            } else {
                echo createResponse(ERROR_CODE, "找不到id为" . $modId . "的模组。", null);
            }
        } else {
            echo createResponse(ERROR_CODE, "找不到名为" . $account . "的用户。", null);
        }
    }
    mysqli_close($con);
}


/**更新模组方法 */
function updateMod($appID, $modId, $developer, $name, $describe, $tags, $versionName, $updataLog, $iconFile, $file)
{
    $con = mysqli_connect(SERVERNAME, LOCALHOST, PASSWORD);
    mysqli_select_db($con, DATABASE_NAME);
    if (!$con) {
        echo createResponse(ERROR_CODE, "链接数据库出错。", null);
        exit;
    } else {
        $sqlUser = "SELECT * FROM " . DATABASE_NAME . ".`user` WHERE account='" . $developer . "' AND appID='" . $appID . "'";
        $userResult = mysqli_query($con, $sqlUser);
        if (mysqli_num_rows($userResult) > 0) {
            $sql = "SELECT * FROM " . DATABASE_NAME . ".`mod` WHERE id='" . $modId . "'";
            $result = mysqli_query($con, $sql);
            if (mysqli_num_rows($result) > 0) {
                $modRow = mysqli_fetch_assoc($result);
                //id存在，并且找到一个模组的name与新的模组名称相等，那么返回名称占用
                $nameSql = "SELECT * FROM " . DATABASE_NAME . ".`mod` WHERE name='" . $name . "' AND id!='" . $modId . "'";
                $nameResult = mysqli_query($con, $nameSql);
                if (mysqli_num_rows($nameResult) > 0) {
                    echo createResponse(ERROR_CODE, "已存在名为" . $name . "的模组。", "@event:模组名占用");
                    exit;
                }

                $versionNameSql = "SELECT * FROM " . DATABASE_NAME . ".`mod` WHERE versionName='" . $versionName . "' AND id='" . $modId . "'";
                $versionNameResult = mysqli_query($con, $versionNameSql);
                if (mysqli_num_rows($versionNameResult) > 0) {
                    echo createResponse(ERROR_CODE, "已存在版本号名为" . $versionName . "的提交。", "@event:版本名占用");
                    exit;
                }

                $folder = "../user/" . iconv("UTF-8", "GBK", $developer) . "/mods/" . $modId;
                if (!file_exists($folder)) {
                    mkdir($folder, 0777, true);
                }

                //图标不为空且拷贝失败设置为null
                $realIcon = null;
                if ($iconFile != null) {
                    if (is_string($iconFile)) {
                        $realIcon = $iconFile;
                    } else {
                        $newIcon = $folder . "/icon.png";
                        $move =  move_uploaded_file($iconFile["tmp_name"], $newIcon);
                        if ($move) {
                            $realIcon = $newIcon;
                        }
                    }
                }

                //在超全局变量内搜索截图(最多6张)
                $screenshotFolder = $folder . "/screenshot";
                $screenshotData = null;
                for ($num = 0; $num < 6; $num++) {
                    $thisKey = "screenshot_" . $num;
                    if (array_key_exists($thisKey, $_FILES)) {
                        if (!file_exists($screenshotFolder)) {
                            mkdir($screenshotFolder, 0777, true);
                        }
                        $newScreenshot = $screenshotFolder . "/" . $num . ".png";
                        $move =  move_uploaded_file($_FILES[$thisKey]["tmp_name"], $newScreenshot);
                        if ($move) {
                            if ($screenshotData == null) {
                                $screenshotData = $newScreenshot;
                            } else {
                                $screenshotData = $screenshotData . "," . $newScreenshot;
                            }
                        } else {
                            break;
                        }
                    } else if (empty($_POST[$thisKey])) {
                        break;
                    } else {
                        if ($screenshotData == null) {
                            $screenshotData = $_POST[$thisKey];
                        } else {
                            $screenshotData = $screenshotData . "," . $_POST[$thisKey];
                        }
                    }
                }


                $newFile = $folder . "/" . $modId . ".rwmod";
                $nowTime = time();
                $createTime = date("Y-m-d H:i:s", $nowTime);
                $newVersionNumber = $modRow['versionNumber'] + 1;

                $up = "UPDATE " . DATABASE_NAME . ".`mod` SET `name`='" . $name . "',  `icon` = '" . $realIcon . "', `tags`='" . $tags . "',`screenshots` = '" . $screenshotData . "', `versionNumber` = " . $newVersionNumber . ", `versionName`='" . $versionName . "', `describe`='" . $describe . "',  `updateTime` = '" . $createTime . "'  WHERE `id` = '" . $modId . "'";
                $sqlUpdate = "INSERT INTO " . DATABASE_NAME . ".`mod_versions`(`id`, `versionName`, `versionNumber`, `updateLog`, `time`) VALUES ('" . $modId . "', '" . $versionName . "', " . $newVersionNumber . ", '" . $updataLog . "', '" . $createTime . "')";
                mysqli_query($con, $sqlUpdate);
                mysqli_query($con, $up);

                $move =  move_uploaded_file($file["tmp_name"], $newFile);
                if ($move) {
                    echo createResponse(SUCCESS_CODE, "已更新。", null);
                } else {
                    echo createResponse(ERROR_CODE, "文件上传失败。", null);
                }
            } else {
                echo createResponse(ERROR_CODE, "找不到id为" . $modId . "的模组。", null);
            }
        } else {
            echo createResponse(ERROR_CODE, "找不到名为" . $developer . "的用户。", null);
        }
        mysqli_free_result($userResult);
    }
    mysqli_close($con);
}


/* 发布模组方法*/
function releaseMod($appID, $modId, $developer, $name, $describe, $tags, $versionName, $unitNumber, $iconFile, $file)
{
    $con = mysqli_connect(SERVERNAME, LOCALHOST, PASSWORD);
    mysqli_select_db($con, DATABASE_NAME);
    if (!$con) {
        echo createResponse(ERROR_CODE, "链接数据库出错。", null);
        exit;
    } else {
        $sqlUser = "SELECT * FROM " . DATABASE_NAME . ".`user` WHERE account='" . $developer . "' AND appID='" . $appID . "'";
        $userResult = mysqli_query($con, $sqlUser);
        if (mysqli_num_rows($userResult) > 0) {
            $row = mysqli_fetch_assoc($userResult);
            $permission = $row['permission'];

            $nameSql = "SELECT * FROM " . DATABASE_NAME . ".`mod` WHERE name='" . $name . "'";
            $nameResult = mysqli_query($con, $nameSql);
            if (mysqli_num_rows($nameResult) > 0) {
                echo createResponse(ERROR_CODE, "已存在名为" . $name . "的模组。", "@event:模组名占用");
                exit;
            }

            $sql = "SELECT * FROM " . DATABASE_NAME . ".`mod` WHERE id='" . $modId . "'";
            $result = mysqli_query($con, $sql);
            if (mysqli_num_rows($result) > 0) {
                echo createResponse(ERROR_CODE, "已存在ID为" . $modId . "的模组。", "@event:Id占用");
            } else {
                $folder = "../user/" . iconv("UTF-8", "GBK", $developer) . "/mods/" . $modId;
                if (!file_exists($folder)) {
                    mkdir($folder, 0777, true);
                }

                //图标不为空且拷贝失败设置为null
                $realIcon = null;
                if ($iconFile != null) {
                    if (is_string($iconFile)) {
                        $realIcon = $iconFile;
                    } else {
                        $newIcon = $folder . "/icon.png";
                        $move =  move_uploaded_file($iconFile["tmp_name"], $newIcon);
                        if ($move) {
                            $realIcon = $newIcon;
                        }
                    }
                }

                //在超全局变量内搜索截图(最多6张)
                $screenshotFolder = $folder . "/screenshot";
                $screenshotData = null;
                for ($num = 0; $num < 6; $num++) {
                    $thisKey = "screenshot_" . $num;
                    if (array_key_exists($thisKey, $_FILES)) {
                        if (!file_exists($screenshotFolder)) {
                            mkdir($screenshotFolder, 0777, true);
                        }
                        $newScreenshot = $screenshotFolder . "/" . $num . ".png";
                        $move =  move_uploaded_file($_FILES[$thisKey]["tmp_name"], $newScreenshot);
                        if ($move) {
                            if ($screenshotData == null) {
                                $screenshotData = $newScreenshot;
                            } else {
                                $screenshotData = $screenshotData . "," . $newScreenshot;
                            }
                        } else {
                            break;
                        }
                    } else if (empty($_POST[$thisKey])) {
                        break;
                    } else {
                        if ($screenshotData == null) {
                            $screenshotData = $_POST[$thisKey];
                        } else {
                            $screenshotData = $screenshotData . "," . $_POST[$thisKey];
                        }
                    }
                }

                $newFile = $folder . "/" . $modId . ".rwmod";
                $nowTime = time();
                $createTime = date("Y-m-d H:i:s", $nowTime);
                $hidden = 1;
                if ($permission < 3) {
                    $hidden = 0;
                }
                $sql = "INSERT INTO " . DATABASE_NAME . ".`mod`(`id`,`name`, `describe`,`icon`,`screenshots`,`developer`, `tags`,`link`,`versionNumber`,`versionName`,`updateTime`, `creationTime`,`unitNumber`,`hidden`) VALUES ('" . $modId . "','" . $name . "', '" . $describe . "', '" . $realIcon . "', '" . $screenshotData . "', '" . $developer . "', '" . $tags . "', '" . $newFile . "','1','" . $versionName . "','" . $createTime . "', '" . $createTime . "','" . $unitNumber . "','" . $hidden . "')";
                $sqlUpdate = "INSERT INTO " . DATABASE_NAME . ".`mod_versions`(`id`, `versionName`, `versionNumber`, `updateLog`, `time`) VALUES ('" . $modId . "', '" . $versionName . "', 1, '初始提交', '" . $createTime . "')";
                mysqli_query($con, $sqlUpdate);
                if (mysqli_query($con, $sql)) {
                    $move =  move_uploaded_file($file["tmp_name"], $newFile);
                    if ($move) {
                        if ($hidden == 1) {
                            echo createResponse(SUCCESS_CODE, "模组发布成功，等待管理员审核。", null);
                        } else {
                            echo createResponse(SUCCESS_CODE, "模组发布成功，已自动上架", null);
                        }
                    } else {
                        echo createResponse(ERROR_CODE, "文件上传失败。", null);
                    }
                } else {
                    echo createResponse(ERROR_CODE, "发布失败。", mysqli_error($con));
                    return false;
                }
            }
            mysqli_free_result($result);
        } else {
            echo createResponse(ERROR_CODE, "找不到名为" . $developer . "的用户。", null);
        }

        mysqli_free_result($userResult);
    }
    mysqli_close($con);
}

/*审核模组 */
function auditMod($account, $modId, $state)
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
                $modSql = "SELECT * FROM " . DATABASE_NAME . ".`mod` WHERE `id`='" . $modId . "'";
                $modResult = mysqli_query($con, $modSql);
                if (mysqli_num_rows($modResult) > 0) {
                    $stateCode = -1;
                    if ($state == "true") {
                        $stateCode = 0;
                    }
                    $updata = "UPDATE " . DATABASE_NAME . ".`mod` SET `hidden` = '" . $stateCode . "' WHERE `id` = '" . $modId . "'";
                    if (mysqli_query($con, $updata)) {
                        echo createResponse(SUCCESS_CODE, "修改成功", null);
                    } else {
                        echo createResponse(ERROR_CODE, "修改失败", null);
                    }
                } else {
                    echo createResponse(ERROR_CODE, "找不到id为" . $modId . "的模组。", null);
                }
                mysqli_free_result($modResult);
            } else {
                echo createResponse(ERROR_CODE, "您无权审核模组。", null);
            }
        } else {
            echo createResponse(ERROR_CODE, "找不到名为" . $account . "的用户。", null);
        }
        mysqli_free_result($userResult);
    }
    mysqli_close($con);
}

/*获取模组列表(显示部分信息) */
function getList($loadHide, $sortMode, $limit)
{
    $con = mysqli_connect(SERVERNAME, LOCALHOST, PASSWORD);
    mysqli_select_db($con, DATABASE_NAME);
    if (!$con) {
        echo createResponse(ERROR_CODE, "链接数据库出错。", null);
        return;
    } else {
        $total = array();
        $num = 0;
        $hideID = 0;
        if ($loadHide) {
            $hideID = 1;
        }
        $sqlMod = "SELECT id,name,`describe`,icon,developer,downloadNumber,`updateTime` FROM " . DATABASE_NAME . ".`mod` WHERE `hidden`='" . $hideID . "'";
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
    mysqli_close($con);
}


/*下架模组 */
function soldOutMod($account, $modId)
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
            $modSql = "SELECT * FROM " . DATABASE_NAME . ".`mod` WHERE `id`='" . $modId . "'";
            $modResult = mysqli_query($con, $modSql);
            if (mysqli_num_rows($modResult) > 0) {
                $updata = "UPDATE " . DATABASE_NAME . ".`mod` SET `hidden` = '-1' WHERE `developer`='" . $account . "' AND `id` = '" . $modId . "'";
                if (mysqli_query($con, $updata)) {
                    echo createResponse(SUCCESS_CODE, "修改成功", null);
                } else {
                    echo createResponse(ERROR_CODE, "修改失败", null);
                }
            } else {
                echo createResponse(ERROR_CODE, "找不到id为" . $modId . "的模组。", null);
            }
            mysqli_free_result($modResult);
        } else {
            echo createResponse(ERROR_CODE, "找不到名为" . $account . "的用户。", null);
        }
        mysqli_free_result($userResult);
    }
    mysqli_close($con);
}



/*重新审核模组 */
function afreshAuditMod($account, $modId)
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
            $modSql = "SELECT * FROM " . DATABASE_NAME . ".`mod` WHERE `id`='" . $modId . "'";
            $modResult = mysqli_query($con, $modSql);
            if (mysqli_num_rows($modResult) > 0) {
                $updata = "UPDATE " . DATABASE_NAME . ".`mod` SET `hidden` = '1' WHERE `developer`='" . $account . "' AND `id` = '" . $modId . "'";
                if (mysqli_query($con, $updata)) {
                    echo createResponse(SUCCESS_CODE, "修改成功", null);
                } else {
                    echo createResponse(ERROR_CODE, "修改失败", null);
                }
            } else {
                echo createResponse(ERROR_CODE, "找不到id为" . $modId . "的模组。", null);
            }
            mysqli_free_result($modResult);
        } else {
            echo createResponse(ERROR_CODE, "找不到名为" . $account . "的用户。", null);
        }
        mysqli_free_result($userResult);
    }
    mysqli_close($con);
}



/*获取模组列表(按标签搜索) */
function getTagModList($tags)
{
    $con = mysqli_connect(SERVERNAME, LOCALHOST, PASSWORD);
    mysqli_select_db($con, DATABASE_NAME);
    if (!$con) {
        echo createResponse(ERROR_CODE, "链接数据库出错。", null);
        return;
    } else {
        $total = array();
        $num = 0;
        $sqlMod = "SELECT id,name,`describe`,icon,developer,downloadNumber,`updateTime` FROM " . DATABASE_NAME . ".`mod` WHERE `tags` Like '%" . $tags . "%' AND `hidden`=0";
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
    mysqli_close($con);
}

/*获取模组列表(按用户搜索) */
function getUserModList($account, $sortMode, $limit, $loadAll)
{
    $con = mysqli_connect(SERVERNAME, LOCALHOST, PASSWORD);
    mysqli_select_db($con, DATABASE_NAME);
    if (!$con) {
        echo createResponse(ERROR_CODE, "链接数据库出错。", null);
        return;
    } else {
        $total = array();
        $num = 0;
        $items = "id,name,`describe`,icon,developer,downloadNumber,`updateTime`";
        if ($loadAll == true) {
            $items = "*";
        }
        $sqlMod = "SELECT " . $items . " FROM " . DATABASE_NAME . ".`mod` WHERE `developer` = '" . $account . "'";
        if ($loadAll == false) {
            $sqlMod = $sqlMod . " AND `hidden`=0";
        }
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
    mysqli_close($con);
}
