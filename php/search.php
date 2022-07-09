<?php
/*搜索系统 */
require_once "conf.php";

if (empty($_REQUEST['action'])) {
    echo nullValuePrompt("action");
    return;
}


switch ($_REQUEST['action']) {
    case "searchAll":
        if (empty($_POST['key'])) {
            echo nullValuePrompt("key");
            return;
        }
        searchAll($_POST['key']);
        break;
    case "suggestions":
        if (empty($_POST['key'])) {
            echo nullValuePrompt("key");
            return;
        }
        suggestions($_POST['key']);
        break;
}

/*获取搜索建议 */
function suggestions($key)
{
    $con = mysqli_connect(SERVERNAME, LOCALHOST, PASSWORD);
    mysqli_select_db($con, DATABASE_NAME);
    if (!$con) {
        echo createResponse(ERROR_CODE, "链接数据库出错。", null);
        return;
    } else {
        $suggestionsArray = array();
        $num = 0;
        //根据搜索记录建议
        $sqlMod = "SELECT keyword FROM " . DATABASE_NAME . ".`search_record` WHERE `keyword` Like '%" . $key . "%' ORDER BY latestTime  DESC ";
        $result = mysqli_query($con, $sqlMod);
        if ($result != false && mysqli_num_rows($result) > 0) {
            while ($row = mysqli_fetch_assoc($result)) {
                $suggestionsArray[$num] = $row['keyword'];
                $num++;
            }
            mysqli_free_result($result);
        }
        //根据模组名建议
        $sqlMod = "SELECT name FROM " . DATABASE_NAME . ".`mod` WHERE `name` Like '%" . $key . "%'";
        $result = mysqli_query($con, $sqlMod);
        if ($result != false && mysqli_num_rows($result) > 0) {
            while ($row = mysqli_fetch_assoc($result)) {
                $suggestionsArray[$num] = $row['name'];
                $num++;
            }
            mysqli_free_result($result);
        }
        //根据用户名建议
        $sqlMod = "SELECT userName FROM " . DATABASE_NAME . ".`user` WHERE  `userName` Like '%" . $key . "%' AND `enable`='true'";
        $result = mysqli_query($con, $sqlMod);
        if ($result != false && mysqli_num_rows($result) > 0) {
            while ($row = mysqli_fetch_assoc($result)) {
                $suggestionsArray[$num] = $row['userName'];
                $num++;
            }
            mysqli_free_result($result);
        }
        $endArray = array_unique($suggestionsArray);
        echo createResponse(SUCCESS_CODE, "获取成功，共" . count($endArray) . "条记录", $endArray);
    }
}

/*搜索全部Api（可搜索用户，模组，评论，动态） */
function searchAll($key)
{
    $con = mysqli_connect(SERVERNAME, LOCALHOST, PASSWORD);
    mysqli_select_db($con, DATABASE_NAME);
    if (!$con) {
        echo createResponse(ERROR_CODE, "链接数据库出错。", null);
        return;
    } else {
        //记录搜索或添加次数
        $historySql = "SELECT * FROM " . DATABASE_NAME . ".`search_record` WHERE `keyword` = '" . $key . "'";
        $historyResult = mysqli_query($con, $historySql);
        $nowTime = time();
        $createTime = date("Y-m-d H:i:s", $nowTime);
        if (mysqli_num_rows($historyResult) > 0) {
            $row = mysqli_fetch_assoc($historyResult);
            $number = $row['number'];
            $number++;
            $sql = "UPDATE " . DATABASE_NAME . ".`search_record`  SET `latestTime` = '" . $createTime . "',`number` = '" . $number . "'  WHERE `keyword` = '" . $key . "'";
            mysqli_query($con, $sql);
        } else {
            $sql = "INSERT INTO " . DATABASE_NAME . ".`search_record` (keyword,number,creationTime,latestTime) VALUES ('" . $key . "','1','" . $createTime . "','" . $createTime . "')";
            mysqli_query($con, $sql);
        }

        $total = array();
        $num = 0;
        $typeArray = array();
        $typeNum = 0;

        //搜索模组（基于名称）
        $sqlMod = "SELECT * FROM " . DATABASE_NAME . ".`mod` WHERE `name` Like '%" . $key . "%' AND `hidden`=0";
        $result = mysqli_query($con, $sqlMod);
        if ($result != false && mysqli_num_rows($result) > 0) {
            $searchType = new searchType("mod");
            $typeArray[$typeNum] = $searchType;
            $typeNum++;
            while ($row = mysqli_fetch_assoc($result)) {
                $total[$num] = new searchResult($row['id'], $row['name'], "mod", $row['describe'], $row['icon']);
                $searchType->addNumber();
                $num++;
            }
            mysqli_free_result($result);
        }

        //搜索用户（基于用户名）
        $sqlMod = "SELECT * FROM " . DATABASE_NAME . ".`user` WHERE `userName` Like '%" . $key . "%' AND `enable`='true'";
        $result = mysqli_query($con, $sqlMod);
        if ($result != false && mysqli_num_rows($result) > 0) {
            $searchType = new searchType("user");
            $typeArray[$typeNum] = $searchType;
            $typeNum++;
            while ($row = mysqli_fetch_assoc($result)) {
                $total[$num] = new searchResult($row['account'], $row['userName'], "user", $row['account'], $row['headIcon']);
                $searchType->addNumber();
                $num++;
            }
            mysqli_free_result($result);
        }

        //搜索动态（基于动态内容）
        $sqlMod = "SELECT * FROM " . DATABASE_NAME . ".`dynamic` WHERE `content` Like '%" . $key . "%'";
        $result = mysqli_query($con, $sqlMod);
        if ($result != false && mysqli_num_rows($result) > 0) {
            $searchType = new searchType("dynamic");
            $typeArray[$typeNum] = $searchType;
            $typeNum++;
            while ($row = mysqli_fetch_assoc($result)) {
                $total[$num] = new searchResult($row['account'], $row['account'] . "的动态", "dynamic", $row['content'], null);
                $searchType->addNumber();
                $num++;
            }
            mysqli_free_result($result);
        }

        //搜索模组讨论（基于内容）
        $sqlMod = "SELECT * FROM " . DATABASE_NAME . ".`mod_comments` WHERE `content` Like '%" . $key . "%'";
        $result = mysqli_query($con, $sqlMod);
        if ($result != false && mysqli_num_rows($result) > 0) {
            $searchType = new searchType("mod_comments");
            $typeArray[$typeNum] = $searchType;
            $typeNum++;
            while ($row = mysqli_fetch_assoc($result)) {
                $total[$num] = new searchResult($row['modId'], $row['modId'] . "的讨论内容", "mod_comments", $row['content'], null);
                $searchType->addNumber();
                $num++;
            }
            mysqli_free_result($result);
        }
        //搜索更新记录（基于日志）
        $sqlMod = "SELECT * FROM " . DATABASE_NAME . ".`mod_versions` WHERE `updateLog` Like '%" . $key . "%'";
        $result = mysqli_query($con, $sqlMod);
        if ($result != false && mysqli_num_rows($result) > 0) {
            $searchType = new searchType("mod_versions");
            $typeArray[$typeNum] = $searchType;
            $typeNum++;
            while ($row = mysqli_fetch_assoc($result)) {
                $total[$num] = new searchResult($row['id'], $row['id'] . "的更新记录", "mod_versions", $row['updateLog'], null);
                $searchType->addNumber();
                $num++;
            }
            mysqli_free_result($result);
        }

        //搜索激活套餐
        $sqlMod = "SELECT * FROM " . DATABASE_NAME . ".`purchase_plan` WHERE `name` Like '%" . $key . "%' OR `describe` Like '%" . $key . "%' ORDER BY price ASC";
        $result = mysqli_query($con, $sqlMod);
        if ($result != false && mysqli_num_rows($result) > 0) {
            $searchType = new searchType("purchase_plan");
            $typeArray[$typeNum] = $searchType;
            $typeNum++;
            while ($row = mysqli_fetch_assoc($result)) {
                $total[$num] = new searchResult($row['id'], $row['name'] . " ￥" . $row['price'], "purchase_plan", $row['describe'], null);
                $searchType->addNumber();
                $num++;
            }
            mysqli_free_result($result);
        }
        $allArray = array();
        $allArray['total'] = $total;
        $allArray['type'] = $typeArray;
        echo createResponse(SUCCESS_CODE, "获取成功，共" . $num . "条记录", $allArray);
    }
}


/*搜索类型 */
class searchType
{
    //类型名称
    var $typeName;
    //数量
    var $num = 0;

    function __construct($typeName)
    {
        $this->typeName = $typeName;
    }

    /**
     * 添加计数
     */
    function addNumber()
    {
        $this->num++;
    }
}

/*搜索结果类 */
class searchResult
{
    //标题
    var $title;
    //类型
    var $type;
    //内容
    var $content;
    //图标
    var $icon;

    var $id;

    //构造函数
    function __construct($id, $title, $type, $content, $icon)
    {
        $this->id = $id;
        $this->title = $title;
        $this->type = $type;
        $this->content = $content;
        $this->icon = $icon;
    }
}
