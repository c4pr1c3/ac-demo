<?php

require_once 'db.php';
require_once 'utils.php';
require_once 'config.php';

function listFiles($uid, $offset, $limit, $search = '') {

    $total   = getFileCountInDb($uid, $search);
    if($total > 0) {
      $content = listFilesInDb($uid, $offset, $limit, $search);
      // pack name with href
      foreach($content as $key => $row) {
        $content[$key]['name'] = '<a href="download.php?id=' . $row['id'] . '">' . $row['name'] . '</a>';
        $content[$key]['del'] = '<a href="#" onclick="ajaxDelete(this);">删除</a>';
      }
    } else {
      $content = array();
    }
    $ret = [
        'total' => $total,
        'rows' => $content
    ];

    return $ret;
}
