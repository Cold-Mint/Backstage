    <?php
    require_once "../../php/conf.php";
    //模组信息变量

    //初始化模组信息函数
    function initInfo($modId)
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
                $modRow = mysqli_fetch_assoc($modResult);
                $hidden = $modRow['hidden'];
                if ($hidden == 1 || $hidden == -2) {
                    //被下架

                    //echo createResponse(ERROR_CODE, "此模组已被下架。", null);
                } else {
                    //echo createResponse(SUCCESS_CODE, "获取成功。", $modRow);
                    echo "<!DOCTYPE html>
                    <html>
                    <head>
                        <meta charset=\"utf-8\">
                        <meta name=\"viewport\" content=\"width=device-width\">
    
                        <title>" . $modRow['name'] . "</title>
                        <link rel=\"stylesheet\" href=\"../css/mdui.min.css\" />
                        </head>
    <body>
    
        <div class=\"mdui-container\">
        <div class=\"mdui-toolbar\">
        <span class=\"mdui-typo-title\">" . $modRow['name'] . "</span>
        <div class=\"mdui-toolbar-spacer\"></div>
      </div>
      <div class=\"mdui-card\">
      <div class=\"mdui-card-media\">
      <img src=\"" . $modRow['icon'] . "\"/>

</div>
      <div class=\"mdui-card-content\">" . $modRow['describe'] . "</div>
      <div class=\"mdui-card-actions\">
      <button class=\"mdui-btn mdui-ripple\" onclick=\"window.location.href = '../../".$modRow['link']."'\">下载</button>

      </div>
</div>
        </div>
    
        <script src=\"../js/mdui.min.js\"></script>
    </body>
    
    </html>";
                }
            } else {
                //echo createResponse(ERROR_CODE, "找不到id为" . $modId . "的模组。", null);

            }
        }
        mysqli_close($con);
    }

    initInfo($_GET['modId']) ?>
