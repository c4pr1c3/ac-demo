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
        $row['name'] = htmlspecialchars($row['name']); // prevent DOM based XSS
        $content[$key]['name'] = '<a href="download.php?id=' . $row['id'] . '">' . $row['name'] . '</a>';
        $content[$key]['del'] = '<a href="#" onclick="ajaxDelete(this);"><span class="glyphicon glyphicon-remove"></span></a>';
        // <button type="button" class="btn btn-primary" data-toggle="modal" data-target="#exampleModal" data-whatever="@mdo">Open modal for @mdo</button>
        $content[$key]['share'] = '<a href="#" onclick="ajaxShare(this);" data-toggle="modal" data-target="#shareModal"><span class="glyphicon glyphicon-share"></span></a>';
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
