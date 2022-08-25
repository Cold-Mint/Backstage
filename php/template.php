<?php
/* 模板包 */
require_once "conf.php";

if (!canUseIp()) {
    return;
}

if (empty($_REQUEST['action'])) {
    echo nullValuePrompt("action");
    return;
}

switch ($_REQUEST['action']) {
        //创建模板包
    case "createTemplatePackage":
        if (empty($_POST['token'])) {
            echo nullValuePrompt("token");
            return;
        }
        if (empty($_POST['id'])) {
            echo nullValuePrompt("id");
            return;
        }
        if (empty($_POST['name'])) {
            echo nullValuePrompt("name");
            return;
        }
        if (empty($_POST['describe'])) {
            echo nullValuePrompt("describe");
            return;
        }
        if (empty($_POST['versionName'])) {
            echo nullValuePrompt("versionName");
            return;
        }
        if (empty($_POST['appVersionName'])) {
            echo nullValuePrompt("appVersionName");
            return;
        }
        if (empty($_POST['appVersionNumber'])) {
            echo nullValuePrompt("appVersionNumber");
            return;
        }
        //逻辑值true或false
        if (empty($_POST['publicState'])) {
            echo nullValuePrompt("public");
            return;
        } else {
            if ($_POST['publicState'] != "true" && $_POST['publicState'] != "false") {
                echo createResponse(ERROR_CODE, "公开状态只能为true或false", null);
                return;
            }
        }
        createTemplatePackage($_POST['id'], $_POST['token'], $_POST['name'], $_POST['describe'], $_POST['versionName'], $_POST['appVersionName'], $_POST['appVersionNumber'], $_POST['publicState']);
        break;
        //获取用户的模板包列表
    case "getTemplatePackageList":
        if (empty($_POST['token'])) {
            echo nullValuePrompt("token");
            return;
        }
        getTemplatePackageList($_POST['token']);
        break;
        //获取公开的模板包列表
    case "getPublicTemplatePackageList":
        if (empty($_POST['token'])) {
            echo nullValuePrompt("token");
            return;
        }
        getPublicTemplatePackageList($_POST['token']);
        break;
        //添加模板到模板包
    case "addTemplate":
        if (empty($_POST['token'])) {
            echo nullValuePrompt("token");
            return;
        }
        if (empty($_POST['title'])) {
            echo nullValuePrompt("title");
            return;
        }
        if (empty($_POST['id'])) {
            echo nullValuePrompt("id");
            return;
        }
        if (empty($_POST['content'])) {
            echo nullValuePrompt("content");
            return;
        }
        if (empty($_POST['packageId'])) {
            echo nullValuePrompt("packageId");
            return;
        }
        addTemplate($_POST['token'], $_POST['id'], $_POST['title'], $_POST['content'], $_POST['packageId']);
        break;
        //获取模板包内的模板列表
    case "getTemplateList":
        if (empty($_POST['packageId'])) {
            echo nullValuePrompt("packageId");
            return;
        }
        getTemplateList($_POST['packageId']);
        break;
    case "getTemplate":
        if (empty($_POST['id'])) {
            echo nullValuePrompt("id");
            return;
        }
        getTemplate($_POST['id']);
        break;
    case "subscription":
        if (empty($_POST['token'])) {
            echo nullValuePrompt("token");
            return;
        }
        if (empty($_POST['packageId'])) {
            echo nullValuePrompt("packageId");
            return;
        }
        subscription($_POST['token'], $_POST['packageId']);
        break;
    case "deleteSubscription":
        if (empty($_POST['token'])) {
            echo nullValuePrompt("token");
            return;
        }
        if (empty($_POST['packageId'])) {
            echo nullValuePrompt("packageId");
            return;
        }
        deleteSubscription($_POST['token'], $_POST['packageId']);
        break;
    case "getSubscriptionData":
        if (empty($_POST['token'])) {
            echo nullValuePrompt("token");
            return;
        }
        getSubscriptionData($_POST['token']);
        break;
}

//获取模板
function getTemplate($id)
{
    $con = mysqli_connect(SERVERNAME, LOCALHOST, PASSWORD);
    mysqli_select_db($con, DATABASE_NAME);
    if (!$con) {
        echo createResponse(ERROR_CODE, "链接数据库出错。", null);
        return;
    } else {
        $userSql = "SELECT * FROM " . DATABASE_NAME . ".`template_list` WHERE id='" . $id . "'";
        $userResult = mysqli_query($con, $userSql);
        if (mysqli_num_rows($userResult) > 0) {
            $userRow = mysqli_fetch_assoc($userResult);
            echo createResponse(SUCCESS_CODE, "获取成功。", $userRow);
        } else {
            echo createResponse(ERROR_CODE, "找不到模板。", null);
        }
    }
    mysqli_close($con);
}

//获取订阅信息
function getSubscriptionData($token)
{
    $con = mysqli_connect(SERVERNAME, LOCALHOST, PASSWORD);
    mysqli_select_db($con, DATABASE_NAME);
    if (!$con) {
        echo createResponse(ERROR_CODE, "链接数据库出错。", null);
        return;
    } else {

        $userSql = "SELECT * FROM " . DATABASE_NAME . ".`user` WHERE token='" . $token . "'";
        $userResult = mysqli_query($con, $userSql);
        if (mysqli_num_rows($userResult) > 0) {
            $userRow = mysqli_fetch_assoc($userResult);
            $account = $userRow['account'];
            $condition = "";
            $total = array();
            $subscribePackageSql = "SELECT * FROM " . DATABASE_NAME . ".`subscribe_record` WHERE account='" . $account . "'";
            $subscribePackageResult = mysqli_query($con, $subscribePackageSql);
            if (mysqli_num_rows($subscribePackageResult) > 0) {
                $num = 0;
                while ($row = mysqli_fetch_assoc($subscribePackageResult)) {
                    if ($num == 0) {
                        $condition =  "id='" . $row['packageId'] . "'";
                    } else {
                        $condition =  $condition . " OR id='" . $row['packageId'] . "'";
                    }
                    $num++;
                }

                $sql = "SELECT * FROM " . DATABASE_NAME . ".`template_package` WHERE (public='true' AND templateNumber>0) AND (" . $condition . ")";
                $result = mysqli_query($con, $sql);
                if (mysqli_num_rows($result) > 0) {
                    $num = 0;
                    while ($row = mysqli_fetch_assoc($result)) {
                        $sqlList = "SELECT * FROM " . DATABASE_NAME . ".`template_list` WHERE packageId='" . $row['id'] . "'";
                        $resultList = mysqli_query($con, $sqlList);
                        $list = array();
                        if (mysqli_num_rows($resultList) > 0) {
                            $num1 = 0;
                            while ($listRow = mysqli_fetch_assoc($resultList)) {
                                $list[$num1] = $listRow;
                                $num1++;
                            }
                        }
                        $row['templateList'] = $list;
                        $total[$num] = $row;
                        $num++;
                    }
                    echo createResponse(SUCCESS_CODE, "获取成功，共" . $num . "条记录", $total);
                } else {
                    echo createResponse(ERROR_CODE, "没有模板包。", null);
                }
            } else {
                echo createResponse(ERROR_CODE, "没有订阅信息。", null);
            }
        } else {
            echo createResponse(ERROR_CODE, "令牌验证失败。", null);
        }
    }
    mysqli_close($con);
}


//退订模板包
function deleteSubscription($token, $packageId)
{
    $con = mysqli_connect(SERVERNAME, LOCALHOST, PASSWORD);
    mysqli_select_db($con, DATABASE_NAME);
    if (!$con) {
        echo createResponse(ERROR_CODE, "链接数据库出错。", null);
        return;
    } else {
        $userSql = "SELECT * FROM " . DATABASE_NAME . ".`user` WHERE token='" . $token . "'";
        $userResult = mysqli_query($con, $userSql);
        if (mysqli_num_rows($userResult) > 0) {
            $userRow = mysqli_fetch_assoc($userResult);
            $account = $userRow['account'];
            $packageSql = "SELECT * FROM " . DATABASE_NAME . ".`template_package` WHERE  id='" . $packageId . "'  AND public='true' ";
            $packageResult = mysqli_query($con, $packageSql);
            if (mysqli_num_rows($packageResult) > 0) {
                $packageRow = mysqli_fetch_assoc($packageResult);
                $subscriptionNumber = $packageRow['subscriptionNumber'];
                $subscribePackageSql = "SELECT * FROM " . DATABASE_NAME . ".`subscribe_record` WHERE account='" . $account . "' AND packageId='" . $packageId . "'";
                $subscribePackageResult = mysqli_query($con, $subscribePackageSql);
                if (mysqli_num_rows($subscribePackageResult) > 0) {
                    $delSql = "DELETE FROM " . DATABASE_NAME . ".`subscribe_record` WHERE account='" . $account . "' AND packageId='" . $packageId . "'";
                    if (mysqli_query($con, $delSql)) {
                        $subscriptionNumber--;
                        $addSql2 = "UPDATE " . DATABASE_NAME . ".`template_package` SET `subscriptionNumber` = " . $subscriptionNumber . " WHERE `id` = '" . $packageId . "'";
                        mysqli_query($con, $addSql2);
                        echo createResponse(SUCCESS_CODE, "退订成功。", null);
                    } else {
                        echo createResponse(ERROR_CODE, "退订失败。", null);
                    }
                } else {
                    echo createResponse(ERROR_CODE, "没有订阅记录。", null);
                }
            } else {
                echo createResponse(ERROR_CODE, "找不到id为" . $packageId . "的模板包或目标模板包为私有状态。", null);
            }
        } else {
            echo createResponse(ERROR_CODE, "令牌验证失败。", null);
        }
    }
    mysqli_close($con);
}


//订阅模板包
function subscription($token, $packageId)
{
    $con = mysqli_connect(SERVERNAME, LOCALHOST, PASSWORD);
    mysqli_select_db($con, DATABASE_NAME);
    if (!$con) {
        echo createResponse(ERROR_CODE, "链接数据库出错。", null);
        return;
    } else {
        $userSql = "SELECT * FROM " . DATABASE_NAME . ".`user` WHERE token='" . $token . "'";
        $userResult = mysqli_query($con, $userSql);
        if (mysqli_num_rows($userResult) > 0) {
            $userRow = mysqli_fetch_assoc($userResult);
            $account = $userRow['account'];
            $packageSql = "SELECT * FROM " . DATABASE_NAME . ".`template_package` WHERE  id='" . $packageId . "'  AND public='true' ";
            $packageResult = mysqli_query($con, $packageSql);
            if (mysqli_num_rows($packageResult) > 0) {
                $packageRow = mysqli_fetch_assoc($packageResult);
                $subscriptionNumber = $packageRow['subscriptionNumber'];
                $subscribePackageSql = "SELECT * FROM " . DATABASE_NAME . ".`subscribe_record` WHERE account='" . $account . "' AND packageId='" . $packageId . "'";
                $subscribePackageResult = mysqli_query($con, $subscribePackageSql);
                if (mysqli_num_rows($subscribePackageResult) > 0) {
                    echo createResponse(ERROR_CODE, "您不能重复订阅。", null);
                } else {
                    $nowTime = time();
                    $createTime = date("Y-m-d H:i:s", $nowTime);
                    $addSql = "INSERT INTO " . DATABASE_NAME . ".`subscribe_record`(`account`, `packageId`, `time`) VALUES ('" . $account . "', '" . $packageId . "','" . $createTime . "')";
                    if (mysqli_query($con, $addSql)) {
                        $subscriptionNumber++;
                        $addSql2 = "UPDATE " . DATABASE_NAME . ".`template_package` SET `subscriptionNumber` = " . $subscriptionNumber . " WHERE `id` = '" . $packageId . "'";
                        mysqli_query($con, $addSql2);
                        echo createResponse(SUCCESS_CODE, "订阅成功", null);
                    } else {
                        echo createResponse(ERROR_CODE, "订阅失败。", null);
                    }
                }
            } else {
                echo createResponse(ERROR_CODE, "找不到id为" . $packageId . "的模板包或目标模板包为私有状态。", null);
            }
        } else {
            echo createResponse(ERROR_CODE, "令牌验证失败。", null);
        }
    }
    mysqli_close($con);
}


//添加模板到模板包
function addTemplate($token, $id, $title, $content, $packageId)
{
    $con = mysqli_connect(SERVERNAME, LOCALHOST, PASSWORD);
    mysqli_select_db($con, DATABASE_NAME);
    if (!$con) {
        echo createResponse(ERROR_CODE, "链接数据库出错。", null);
        return;
    } else {
        $userSql = "SELECT * FROM " . DATABASE_NAME . ".`user` WHERE token='" . $token . "'";
        $userResult = mysqli_query($con, $userSql);
        if (mysqli_num_rows($userResult) > 0) {
            $userRow = mysqli_fetch_assoc($userResult);
            $developer = $userRow['account'];
            $packageSql = "SELECT * FROM " . DATABASE_NAME . ".`template_package` WHERE developer='" . $developer . "' AND id='" . $packageId . "'";
            $packageResult = mysqli_query($con, $packageSql);
            if (mysqli_num_rows($packageResult) > 0) {
                $packageRow = mysqli_fetch_assoc($packageResult);
                $templateNumber = $packageRow['templateNumber'];
                $versionNumber = $packageRow['versionNumber'];
                $templateSql = "SELECT * FROM " . DATABASE_NAME . ".`template_list` WHERE developer='" . $developer . "' AND id='" . $id . "'";
                $templateResult = mysqli_query($con, $templateSql);
                if (mysqli_num_rows($templateResult) > 0) {
                    echo createResponse(ERROR_CODE, "@event:id重复", null);
                } else {
                    $nowTime = time();
                    $createTime = date("Y-m-d H:i:s", $nowTime);
                    $addSql = "INSERT INTO " . DATABASE_NAME . ".`template_list`(`id`, `title`, `content`, `packageId`, `developer`, `createTime`, `modificationTime`) VALUES ('" . $id . "', '" . $title . "', '" . $content . "', '" . $packageId . "', '" . $developer . "', '" . $createTime . "', '" . $createTime . "')";
                    if (mysqli_query($con, $addSql)) {
                        $templateNumber++;
                        $versionNumber++;
                        $addSql2 = "UPDATE " . DATABASE_NAME . ".`template_package` SET `templateNumber`=" . $templateNumber . ", `versionNumber`=" . $versionNumber . " WHERE id='" . $packageId . "'";
                        mysqli_query($con, $addSql2);
                        echo createResponse(SUCCESS_CODE, "创建成功", null);
                    } else {
                        echo createResponse(ERROR_CODE, "创建失败", null);
                    }
                }
            } else {
                echo createResponse(ERROR_CODE, "找不到id为" . $packageId . "的模板包", null);
            }
        } else {
            echo createResponse(ERROR_CODE, "令牌验证失败。", null);
        }
    }
    mysqli_close($con);
}


//获取模板列表
function getTemplateList($packageId)
{
    $con = mysqli_connect(SERVERNAME, LOCALHOST, PASSWORD);
    mysqli_select_db($con, DATABASE_NAME);
    if (!$con) {
        echo createResponse(ERROR_CODE, "链接数据库出错。", null);
        return;
    } else {
        $sqlPackage = "SELECT * FROM " . DATABASE_NAME . ".`template_package` WHERE id='" . $packageId . "'";
        $packageResult = mysqli_query($con, $sqlPackage);
        $sql = "SELECT * FROM " . DATABASE_NAME . ".`template_list` WHERE packageId='" . $packageId . "'";
        $result = mysqli_query($con, $sql);
        if (mysqli_num_rows($packageResult) > 0 && mysqli_num_rows($result) > 0) {
            $num = 0;
            $total = array();
            $list = array();
            $sqlrow = mysqli_fetch_assoc($packageResult);
            $total['packageData'] = $sqlrow;
            while ($row = mysqli_fetch_assoc($result)) {
                $list[$num] = $row;
                $num++;
            }
            $total['templateList'] = $list;
            echo createResponse(SUCCESS_CODE, "获取成功，共" . $num . "条记录", $total);
        } else {
            echo createResponse(ERROR_CODE, "没有模板。", null);
        }
    }
    mysqli_close($con);
}

//获取公开的模板包列表
function getPublicTemplatePackageList($token)
{
    $con = mysqli_connect(SERVERNAME, LOCALHOST, PASSWORD);
    mysqli_select_db($con, DATABASE_NAME);
    if (!$con) {
        echo createResponse(ERROR_CODE, "链接数据库出错。", null);
        return;
    } else {
        $userSql = "SELECT * FROM " . DATABASE_NAME . ".`user` WHERE token='" . $token . "'";
        $userResult = mysqli_query($con, $userSql);
        if (mysqli_num_rows($userResult) > 0) {
            $userRow = mysqli_fetch_assoc($userResult);
            $account = $userRow['account'];
            $subscribeList = array();
            $total = array();
            $subscribePackageSql = "SELECT * FROM " . DATABASE_NAME . ".`subscribe_record` WHERE account='" . $account . "'";
            $subscribePackageResult = mysqli_query($con, $subscribePackageSql);
            if (mysqli_num_rows($subscribePackageResult) > 0) {
                $num = 0;
                while ($row = mysqli_fetch_assoc($subscribePackageResult)) {
                    $subscribeList[$num] = $row['packageId'];
                    $num++;
                }
            }

            $sql = "SELECT * FROM " . DATABASE_NAME . ".`template_package` WHERE public='true' AND templateNumber>0";
            $result = mysqli_query($con, $sql);
            if (mysqli_num_rows($result) > 0) {
                $num = 0;
                while ($row = mysqli_fetch_assoc($result)) {
                    $row['subscribe'] = in_array($row['id'], $subscribeList);
                    $total[$num] = $row;
                    $num++;
                }
                echo createResponse(SUCCESS_CODE, "获取成功，共" . $num . "条记录", $total);
            } else {
                echo createResponse(ERROR_CODE, "没有模板包。", null);
            }
        } else {
            echo createResponse(ERROR_CODE, "令牌验证失败。", null);
        }
    }
    mysqli_close($con);
}

//获取模板包列表
function getTemplatePackageList($token)
{
    $con = mysqli_connect(SERVERNAME, LOCALHOST, PASSWORD);
    mysqli_select_db($con, DATABASE_NAME);
    if (!$con) {
        echo createResponse(ERROR_CODE, "链接数据库出错。", null);
        return;
    } else {
        $userSql = "SELECT * FROM " . DATABASE_NAME . ".`user` WHERE token='" . $token . "'";
        $userResult = mysqli_query($con, $userSql);
        if (mysqli_num_rows($userResult) > 0) {
            $userRow = mysqli_fetch_assoc($userResult);
            $developer = $userRow['account'];
            $total = array();
            $sql = "SELECT * FROM " . DATABASE_NAME . ".`template_package` WHERE developer='" . $developer . "'";
            $result = mysqli_query($con, $sql);
            if (mysqli_num_rows($result) > 0) {
                $num = 0;
                while ($row = mysqli_fetch_assoc($result)) {
                    $total[$num] = $row;
                    $num++;
                }
                echo createResponse(SUCCESS_CODE, "获取成功，共" . $num . "条记录", $total);
            } else {
                echo createResponse(ERROR_CODE, "没有模板包。", null);
            }
        } else {
            echo createResponse(ERROR_CODE, "令牌验证失败。", null);
        }
    }
    mysqli_close($con);
}


//创建模板包
function createTemplatePackage($id, $token, $name, $describe, $versionName, $appVersionName, $appVersionNumber, $publicState)
{
    $con = mysqli_connect(SERVERNAME, LOCALHOST, PASSWORD);
    mysqli_select_db($con, DATABASE_NAME);
    if (!$con) {
        echo createResponse(ERROR_CODE, "链接数据库出错。", null);
        return;
    } else {
        $userSql = "SELECT * FROM " . DATABASE_NAME . ".`user` WHERE token='" . $token . "'";
        $userResult = mysqli_query($con, $userSql);
        if (mysqli_num_rows($userResult) > 0) {
            $userRow = mysqli_fetch_assoc($userResult);
            $developer = $userRow['account'];
            $oldSql = "SELECT * FROM " . DATABASE_NAME . ".`template_package` WHERE id='" . $id . "'";
            $oldResult = mysqli_query($con, $oldSql);
            if (mysqli_num_rows($oldResult) > 0) {
                echo createResponse(ERROR_CODE, "@event:id重复", null);
            } else {
                $oldNameSql = "SELECT * FROM " . DATABASE_NAME . ".`template_package` WHERE name='" . $name . "'";
                $oldResult = mysqli_query($con, $oldNameSql);
                if (mysqli_num_rows($oldResult) > 0) {
                    echo createResponse(ERROR_CODE, "@event:名称重复", null);
                } else {
                    $nowTime = time();
                    $createTime = date("Y-m-d H:i:s", $nowTime);
                    $addSql = "INSERT INTO " . DATABASE_NAME . ".`template_package`(`id`,`name`, `describe`, `developer`, `versionName`, `versionNumber`, `appVersionName`, `appVersionNumber`, `public`, `createTime`, `modificationTime`) VALUES ('" . $id . "','" . $name . "', '" . $describe . "', '" . $developer . "', '" . $versionName . "', 1, '" . $appVersionName . "', '" . $appVersionNumber . "','" . $publicState . "','" . $createTime . "','" . $createTime . "')";
                    if (mysqli_query($con, $addSql)) {
                        echo createResponse(SUCCESS_CODE, "创建成功", null);
                    } else {
                        echo createResponse(ERROR_CODE, "创建失败", null);
                    }
                }
            }
        } else {
            echo createResponse(ERROR_CODE, "令牌验证失败。", null);
        }
    }
    mysqli_close($con);
}
