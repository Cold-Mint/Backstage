<?php
/*意见反馈 */
require_once "conf.php";
if (!canUseIp()) {
    return;
}

if (empty($_REQUEST['action'])) {
    echo nullValuePrompt("action");
    return;
}

switch ($_REQUEST['action']) {
    case "send":
        if (empty($_POST['token'])) {
            echo nullValuePrompt("token");
            return;
        }
        send($_POST['token']);
        break;
}

function send(){
    
}
