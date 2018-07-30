<?php

require 'upload.bo.php';

$ret = doFileUpload();

echo json_encode($ret);
