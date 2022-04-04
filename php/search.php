<?php
/*搜索系统 */
require_once "conf.php";

if (!canUseIp()) {
    return;
}

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
        $total = array();
        $num = 0;

        //搜索模组（基于名称）
        $sqlMod = "SELECT * FROM " . DATABASE_NAME . ".`mod` WHERE `name` Like '%" . $key . "%' AND `hidden`=0";
        $result = mysqli_query($con, $sqlMod);
        if ($result != false && mysqli_num_rows($result) > 0) {
            while ($row = mysqli_fetch_assoc($result)) {
                $total[$num] = new searchResult($row['id'], $row['name'], "mod", $row['describe'], $row['icon']);
                $num++;
            }
            mysqli_free_result($result);
        }

        //搜索用户（基于用户名）
        $sqlMod = "SELECT * FROM " . DATABASE_NAME . ".`user` WHERE `userName` Like '%" . $key . "%' AND `enable`='true'";
        $result = mysqli_query($con, $sqlMod);
        if ($result != false && mysqli_num_rows($result) > 0) {
            while ($row = mysqli_fetch_assoc($result)) {
                $total[$num] = new searchResult($row['account'], $row['userName'], "user", $row['account'], $row['headIcon']);
                $num++;
            }
            mysqli_free_result($result);
        }

        //搜索动态（基于动态内容）
        $sqlMod = "SELECT * FROM " . DATABASE_NAME . ".`dynamic` WHERE `content` Like '%" . $key . "%'";
        $result = mysqli_query($con, $sqlMod);
        if ($result != false && mysqli_num_rows($result) > 0) {
            while ($row = mysqli_fetch_assoc($result)) {
                $total[$num] = new searchResult($row['account'], $row['account']."的动态", "dynamic", $row['content'], null);
                $num++;
            }
            mysqli_free_result($result);
        }

        //搜索模组讨论（基于内容）
        $sqlMod = "SELECT * FROM " . DATABASE_NAME . ".`mod_comments` WHERE `content` Like '%" . $key . "%'";
        $result = mysqli_query($con, $sqlMod);
        if ($result != false && mysqli_num_rows($result) > 0) {
            while ($row = mysqli_fetch_assoc($result)) {
                $total[$num] = new searchResult($row['modId'], $row['modId']."的讨论内容", "mod_comments", $row['content'], null);
                $num++;
            }
            mysqli_free_result($result);
        }
        //搜索更新记录（基于日志）
        $sqlMod = "SELECT * FROM " . DATABASE_NAME . ".`mod_versions` WHERE `updateLog` Like '%" . $key . "%'";
        $result = mysqli_query($con, $sqlMod);
        if ($result != false && mysqli_num_rows($result) > 0) {
            while ($row = mysqli_fetch_assoc($result)) {
                $total[$num] = new searchResult($row['id'], $row['id']."的更新记录", "mod_versions", $row['updateLog'], null);
                $num++;
            }
            mysqli_free_result($result);
        }
        echo createResponse(SUCCESS_CODE, "获取成功，共" . $num . "条记录", $total);
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
