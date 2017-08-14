<?php

require 'config.php';

session_start();

$now = time();

if (isset($_SESSION['discard_after']) && $now > $_SESSION['discard_after']) {
    // this session has worn out its welcome; kill it and start a brand new one
    session_unset();
    session_destroy();
    session_start();
}

$_SESSION['discard_after'] = $now + Config::$sessionTimeout;

if(!isset($_SESSION['loggedInUser'])) {
    header('Location: /index.html');

    $logout_html = <<<EOF
<script type="text/javascript">
window.top.location.href = "/index.html";
</script>
EOF;

    echo $logout_html;
    exit();
}

