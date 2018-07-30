<?php

require 'config.php';

function isAjaxRequest() {
    if(isset($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest') { // handle AJAX redirect
        return true;
    }

    return false;
}

session_start();

$now = time();

if (isset($_SESSION['discard_after']) && $now > $_SESSION['discard_after']) {
    // this session has worn out its welcome; kill it and start a brand new one
    session_unset();
    session_destroy();
    session_start();

    setcookie('loggedInUser', NULL);
}

$_SESSION['discard_after'] = $now + Config::$sessionTimeout;

if(!isset($_SESSION['loggedInUser'])) {
    if(isAjaxRequest()) {
        $ret = json_encode(array('error' => 'login'));
    } else {
        header('Location: /index.html');

        $ret = <<<EOF
<script type="text/javascript">
window.top.location.href = "/index.html";
</script>
EOF;

    }
    echo $ret;
    exit();
} 

