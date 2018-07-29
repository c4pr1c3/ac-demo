<?php

require_once 'utils.php';
require_once 'db.php';
require_once 'lang.php';
require_once 'config.php';
require_once 'register.bo.php';

function setupPageLayout1($req_method, &$pageLayout)
{
    $pageLayout['showRegFormOrNot'] = 'container';
    $pageLayout['showRegMsgOrNot'] = 'container';
    $pageLayout['userNameMsg'] = Prompt::$msg['invalid_username'];
    $pageLayout['userPasswordMsg'] =  Prompt::$msg['invalid_password'];
    $pageLayout['userEmailMsg'] = Prompt::$msg['invalid_email'];
    $pageLayout['retMsg'] = Prompt::$msg['reset_ok'];
    $pageLayout['userName-has-warning'] = '';
    $pageLayout['password-has-warning'] = '';
    $pageLayout['userEmail_has_warning'] = '';
    $pageLayout['has-warning'] = false;
    $pageLayout['passwordErrs'] = array();
    
    switch ($req_method) {
    case 'POST':
        $pageLayout['showRegFormOrNot'] = 'hidden';
        break;
    case 'GET':
        $pageLayout['showRegMsgOrNot'] = 'hidden';
        break;
    default:
        # code...
        break;
    }
}


function validateCheckLink($token, $email){
	if(checkTokenCountInDb($email, $token)){ 
   		return true;
   }
    return false;
}
