<?php

require 'db.php';
require_once 'utils.php';

function delete_file($key, $uid) {

    if(preg_match(Config::$shaKeyRule, $key) !== 1) {
        $ret = [
            'error' => Prompt::$msg['delete_file_not_found']
        ];
        return $ret;
    }

    $create_time = findFileByShasumAndUid($key, $uid);

    if(!empty($create_time)) {
        try {
            if(deleteFileInDb($key, $uid) != 1) {
                throw new PDOException('delete failed', 1);
            }
        } catch(PDOException $e) {
            $ret = [
                'error' => Prompt::$msg['db_oops']
            ];
            return $ret;
        }
        $uploadfile = getUploadFilePath($uid, $key, $create_time);
        if(!unlink($uploadfile)) {
            $ret = [
                'error' => Prompt::$msg['delete_file_err']
            ];
            return $ret;
        }
    }

    return 'ok';

}

