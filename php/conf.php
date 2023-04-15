<?php
/*系统配置文件 */
header("Content-type: text/html; charset=utf-8");
const SERVERNAME = "localhost";
const LOCALHOST = "root";
const PASSWORD = "";
const ERROR_CODE = 1;
const SUCCESS_CODE = 0;
const DATABASE_NAME = "Rust";
//用户体验时间
const EXPERIENCE_TIME = "+7 day";
//新用户折扣时间
const DISCOUNT_TIME = EXPERIENCE_TIME;
//新人折扣比例
const DISCOUNT_VALUE = 0.8;
//启用付费
const ENABLE_PAYMENT = false;
//评论发布后多长时间内可以删除
const COMMENT_DELETE_TIME= "+5 minute";

/**此ip是否可以访问数据 */
function canUseIp()
{
    $con = mysqli_connect(SERVERNAME, LOCALHOST, PASSWORD);
    mysqli_select_db($con, DATABASE_NAME);
    $resultBool = false;
    if (!$con) {
        echo createResponse(ERROR_CODE, "链接数据库出错。", null);
        return $resultBool;
    } else {
        $ip = getIp();
        $sqlMod = "SELECT * FROM " . DATABASE_NAME . ".`ip_record` WHERE `ip`='" . $ip . "' ORDER BY time DESC";
        $result = mysqli_query($con, $sqlMod);
        $nowTime = time();
        $nowTimeRow = date("Y-m-d H:i:s", $nowTime);
        if ($result != false && mysqli_num_rows($result) > 0) {
            while ($row = mysqli_fetch_assoc($result)) {
                $available = $row['available'];
                if ($available == "false") {
                    echo createResponse(ERROR_CODE, "由于目标计算机积极拒绝,无法连接。", null);
                    break;
                }
                $lastAccessTime = strtotime($row['time']);
                $interval = $nowTime - $lastAccessTime;
                $count = $row['count'];
                if ($interval >= 60) {
                    //如果在60s之外，重置计数
                    $updateCountSql = "UPDATE " . DATABASE_NAME . ".`ip_record` SET `count` = '1' WHERE `ip` = '" . $ip . "'";
                    mysqli_query($con, $updateCountSql);
                    $updateTimeSql = "UPDATE " . DATABASE_NAME . ".`ip_record` SET `time` = '" . $nowTimeRow . "' WHERE `ip` = '" . $ip . "'";
                    mysqli_query($con, $updateTimeSql);
                    $resultBool = true;
                } else {
                    //60秒内访问超过120次被封禁
                    $count++;
                    if ($count > 120) {
                        echo createResponse(ERROR_CODE, "由于目标计算机积极拒绝,无法连接。", null);
                        $updateAvailableSql = "UPDATE " . DATABASE_NAME . ".`ip_record` SET `available` = 'false' WHERE `ip` = '" . $ip . "'";
                        mysqli_query($con, $updateAvailableSql);
                    } else {
                        $updateCountSql = "UPDATE " . DATABASE_NAME . ".`ip_record` SET `count` = '" . $count . "' WHERE `ip` = '" . $ip . "'";
                        mysqli_query($con, $updateCountSql);
                        $resultBool = true;
                    }
                }
            }
        } else {
            //没有数据，添加记录(解析目标ip)
            $curl = curl_init();
            curl_setopt_array($curl, array(
                CURLOPT_URL => 'https://api01.aliyun.venuscn.com/ip?ip='.$ip,
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_ENCODING => '',
                CURLOPT_MAXREDIRS => 10,
                CURLOPT_TIMEOUT => 0,
                CURLOPT_FOLLOWLOCATION => true,
                CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                CURLOPT_CUSTOMREQUEST => 'GET',
                CURLOPT_HTTPHEADER => array(
                    'Authorization: AppCode 3643a0325a294c6980f9de302429395e',
                    'User-Agent: server'
                ),
            ));

            $response = curl_exec($curl);
            curl_close($curl);
            $arr = json_decode($response, true);
            $country = $arr['data']['country'];
            $province = $arr['data']['region'];
            $city = $arr['data']['city'];
            $area = $arr['data']['district'];
            $insetSql = "INSERT INTO " . DATABASE_NAME . ".`ip_record`(`ip`, `time`,`country`,`province`,`city`,`area`, `available`, `count`) VALUES ('" . $ip . "', '" . $nowTimeRow . "','" . $country . "','" . $province . "', '" . $city . "','" . $area . "','true', '1')";
            mysqli_query($con, $insetSql);
            $resultBool = true;
        }
        if ($result != false) {
            mysqli_free_result($result);
        }
        return $resultBool;
    }
}



function getIp()
{
    if (isset($_SERVER)) {
        if (isset($_SERVER["HTTP_X_FORWARDED_FOR"])) {
            $realip = $_SERVER["HTTP_X_FORWARDED_FOR"];
        } elseif (isset($_SERVER["HTTP_CLIENT_IP"])) {
            $realip = $_SERVER["HTTP_CLIENT_IP"];
        } else {
            $realip = $_SERVER["REMOTE_ADDR"];
        }
    } else {
        //不允许就使用getenv获取
        if (getenv("HTTP_X_FORWARDED_FOR")) {
            $realip = getenv("HTTP_X_FORWARDED_FOR");
        } elseif (getenv("HTTP_CLIENT_IP")) {
            $realip = getenv("HTTP_CLIENT_IP");
        } else {
            $realip = getenv("REMOTE_ADDR");
        }
    }
    return $realip;
}

/**
 * 构建响应体
 * @param $code 代码
 * @param $message 消息
 * @param $data 数据
 * @return false|string 成功返回相应（json） 失败返回false
 */
function createResponse($code, $message, $data)
{
    $result = array(
        "code" => $code,
        "message" => $message,
        "data" => $data
    );
    return json_encode($result, JSON_UNESCAPED_UNICODE);
}


/**
 * 生成uuid
 * @return string
 */
function uuid()
{
    $chars = md5(uniqid(mt_rand(), true));
    $uuid = substr($chars, 0, 8) . '-'
        . substr($chars, 8, 4) . '-'
        . substr($chars, 12, 4) . '-'
        . substr($chars, 16, 4) . '-'
        . substr($chars, 20, 12);
    return $uuid;
}

/** 
 * 创建唯一验证码
 */
function createUniqueCode()
{
    $d = substr(base_convert(md5(uniqid(md5(microtime(true)), true)), 16, 10), 0, 6);
    return $d;
}

/**
 * 空值提示
 * @param $valueName 值名称
 */
function nullValuePrompt($valueName)
{
    return createResponse(ERROR_CODE, "请提交" . $valueName, $valueName);
}
