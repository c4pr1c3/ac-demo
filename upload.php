<?php

require 'config.php';

//file_put_contents('/tmp/files.log', json_encode($_FILES) . PHP_EOL, FILE_APPEND);

// TODO 处理客户端上传的文件散列值并查询数据库，如果该文件已经存在，则无需再处理文件上传数据

$p1 = $p2 = [];
if (empty($_FILES['cucFiles']['name'])) {
	echo '{}';
	return;
}
$files = $_FILES['cucFiles'];

// TODO 文件校验

// TODO 文件摘要和计算


// TODO 文件加密

for ($i = 0; $i < count($files); $i++) {
	//file_put_contents('/tmp/files.log', $i . "-" . $files['tmp_name'][$i] . PHP_EOL, FILE_APPEND);
	$uploadFileName = $files['name'][$i];
	$uploadFilePathParts =  pathinfo($uploadFileName);
	$basename = $uploadFilePathParts['filename'];
	$extname = $uploadFilePathParts['extension'];
	$key = hash_file('sha256', $files['tmp_name'][$i]); // FIXME 上传后保存的加密文件名
	$delApiUrl = ''; // FIXME 添加服务端的文件删除API
	$p1[$i] = sprintf("%s/%s", Config::$uploadRoot, $key); // FIXME 文件下载地址
	$p2[$i] = ['caption' => sprintf('%s.%s', $basename, $extname), 'size' => filesize($files['tmp_name'][$i]), 'width' => '120px', 'key' => $key];
}
echo json_encode([
	'initialPreview' => $p1, 
	'initialPreviewConfig' => $p2,   
	'append' => true // whether to append these configurations to initialPreview.
	// if set to false it will overwrite initial preview
	// if set to true it will append to initial preview
	// if this propery not set or passed, it will default to true.
]);
