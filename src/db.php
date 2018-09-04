<?php

function resetP($username,$hashpassword)
{
    $conn = connectDb();
    $sql = "update users set  password = :password where name=:username ;";
    $stmt = $conn->prepare($sql);
    $stmt->bindParam(':username', $username);     //将数值与参数捆绑
    $stmt->bindParam(':password', $hashpassword);     //将数值与参数捆绑
    $stmt->execute();
    // $result = $stmt->fetch(PDO::FETCH_ASSOC);    // 返回查询结果 即密

}
function  setInvalidReset($verify,$username)
{
    $conn = connectDb();
    $sql = "update reset set  valid = 0 where username=:username and access_key=:verify;";
    $stmt = $conn->prepare($sql);
    $stmt->bindParam(':username', $username);     //将数值与参数捆绑
    $stmt->bindParam(':verify', $verify);     //将数值与参数捆绑
    $stmt->execute();
   // $result = $stmt->fetch(PDO::FETCH_ASSOC);    // 返回查询结果 即密

    $sql2 = "select * from reset where username=:username and access_key =:verify";
    $stmt2 = $conn->prepare($sql2);
    $stmt2->bindParam(':username', $username);     //将数值与参数捆绑
    $stmt2->bindParam(':verify', $verify);     //将数值与参数捆绑
    $stmt2->execute();
    $result = $stmt2->fetch(PDO::FETCH_ASSOC);    // 返回查询结果 即密

    return $result['email'];
}
function getResetinfo($username,$verify)
{

    $conn = connectDb();
    $sql = "select * from reset where username=:username and access_key =:verify";
    $stmt = $conn->prepare($sql);
    $stmt->bindParam(':username', $username);     //将数值与参数捆绑
    $stmt->bindParam(':verify', $verify);     //将数值与参数捆绑
    $stmt->execute();
    $result = $stmt->fetch(PDO::FETCH_ASSOC);    // 返回查询结果 即密码



    if($result!==false){
        $now = time();
        $limit = (int)$result['access_time'];

        if($result['valid'] ==='1'&&$limit>=$now) {
            $fina =  true;
        }else{
            $fina =  false;
        }
    }else{
         $fina =  false;
    }

    return $fina;
}
function resetInDb($username, $email, $access_time, $access_key,$valid) {
    try {
        $conn = connectDb();
        $status =0;
        $sql = "INSERT INTO reset ( username, email, access_time , access_key,valid ) VALUES ( :username, :email, :access_time , :access_key,:valid)";
        $stmt = $conn->prepare($sql);
        $stmt->bindParam(':username', $username);
        $stmt->bindParam(':email', $email);
        $stmt->bindParam(':access_time', $access_time);
        $stmt->bindParam(':access_key', $access_key);
        $stmt->bindParam(':valid', $valid);
        return $stmt->execute();

    } catch(PDOException $e) {
        throw $e;
    }
}


function checkRegisterActive($name,$verify) {
    try {
        $conn = connectDb();
        $sql = "select * from users where name=:name";
        $stmt = $conn->prepare($sql);
        $stmt->bindParam(':name', $name);     //将数值与参数捆绑
        $stmt->execute();
        $result = $stmt->fetch(PDO::FETCH_ASSOC);    // 返回查询结果 即密码


        //$ppassword = base64_decode(result['password']);
        //$token = hash('sha256',$result['name'].$result['password']);                // 检测 varify 是否相同 相同进行激活 否则返回激活失败信息
        $token = sodium_bin2hex(sodium_crypto_generichash($result['name'].$result['password']));

        if($token ==$verify){

            $sql2 = "update users set valid = 1 where name = :name";
            $stmt2 = $conn->prepare($sql2);
            $stmt2->bindParam(':name', $name);     //将数值与参数捆绑
            $stmt2->execute();
           return 1;
        }
        else {
            return 0;
        }

        //return isset($result['password']) ? $result['password'] : '';   //检查该参数是否被设置为某个数值，如果有null 就返回false
    } catch(PDOException $e) {
        throw $e;
    }
}

function connectDb() {
    // 编辑/etc/apache2/envvars，添加WEB服务器的环境变量供PHP代码读取数据库连接配置信息
    //下列参数数值为db  acuser password123  acdemo   Array

    $servername = getenv('DB_AC_SERVERNAME');
    $username = getenv('DB_AC_USERNAME');
    $password = getenv('DB_AC_PASSWORD');
    $dbname = getenv('DB_AC_DBNAME');
    $charset = "utf8mb4";
    $options = array(
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_PERSISTENT => true
    );

    $conn = new PDO("mysql:host=$servername;dbname=$dbname;charset=$charset", $username, $password, $options);
    return $conn;
}

function checkRegisterInDb($name) {
    try {
        $conn = connectDb();
        $sql = "select salt,password from users where name=:name";
        $stmt = $conn->prepare($sql);
        $stmt->bindParam(':name', $name);     //将数值与参数捆绑
        $stmt->execute();
        $result = $stmt->fetch(PDO::FETCH_ASSOC);    // 返回查询结果 即密码
       // file_put_contents('debug.log', "checkRegisterInDb  &result ".json_encode($result)."\n",FILE_APPEND);


        return isset($result['password']) ? $result : '';   //检查该参数是否被设置为某个数值，如果有null 就返回false
    } catch(PDOException $e) {
        throw $e;
    }
}

function registerInDb($name, $password,$email,$salt) {
    try {
        $conn = connectDb();
        $status =0;
        $sql = "INSERT INTO users (name, password, email,valid,salt ) VALUES (:name, :password, :email ,:valid ,:salt)";
        $stmt = $conn->prepare($sql);
        $stmt->bindParam(':name', $name);
        $stmt->bindParam(':password', $password);
        $stmt->bindParam(':email', $email);
        $stmt->bindParam(':valid', $status);
        $stmt->bindParam(':salt', $salt);
        $re =  $stmt->execute();
        return $re;

    } catch(PDOException $e) {
        throw $e;
    }
}

function verifyRegisterInDb($name) {
    try {
        $conn = connectDb();
        $status =0;

        $sql = 'update  usres set valid = 1 where name = :name';
       // $sql = "INSERT INTO users (name, password, pubkey, privkey , email,valid ) VALUES (:name, :password, :pubkey, :privkey , :email ,:valid)";
        $stmt = $conn->prepare($sql);
        $stmt->bindParam(':name', $name);
//        $stmt->bindParam(':password', $password);
//        $stmt->bindParam(':pubkey', $pubkey);
//        $stmt->bindParam(':privkey', $privkey);
//        $stmt->bindParam(':email', $email);
//        $stmt->bindParam(':valid', $status);
        return $stmt->execute();

    } catch(PDOException $e) {
        throw $e;
    }
}

function getUserInfoInDb($name) {
    try {
        $conn = connectDb();
        $sql = "select * from users where name=:name";
        $stmt = $conn->prepare($sql);
        $stmt->bindParam(':name', $name);
        $stmt->execute();
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result;
    } catch(PDOException $e) {
        throw $e;
    }
}

function uploadFileInDb($name, $size, $enckey,$nonce_in_db, $sha256, $uid, $datetime,$sign) {
    try {
        $conn = connectDb();
        $sql = "INSERT INTO files (name, size, enckey, sha256, uid, create_time,sign,nonce) VALUES (:name, :size, :enckey, :sha256, :uid, :datetime  ,:sign,:nonce)";
        $stmt = $conn->prepare($sql);
        $stmt->bindParam(':name', $name);
        $stmt->bindParam(':size', $size);
        $stmt->bindParam(':enckey', $enckey);
        $stmt->bindParam(':sha256', $sha256);
        $stmt->bindParam(':uid', $uid);
        $stmt->bindParam(':datetime', $datetime);
        $stmt->bindParam(':sign', $sign);
        $stmt->bindParam(':nonce', $nonce_in_db);
        $result = $stmt->execute();
        return $result;

    } catch(PDOException $e) {
        throw $e;
    }
}

function getFileCountInDb($uid, $search = '') {
    try {
        $conn = connectDb();
        if(empty($search)) {
            $sql = "select count(id) as total from files where uid=:uid";
        } else {
            $sql = "select count(id) as total from files where uid=:uid and name like :search";
        }
        $stmt = $conn->prepare($sql);
        $stmt->bindParam(':uid', $uid, PDO::PARAM_INT);
        if(!empty($search)) {
            $stmt->bindValue(':search', '%' . $search . '%', PDO::PARAM_STR);
        }
        $stmt->execute();
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result['total'];
    } catch(PDOException $e) {
        throw $e;
    }
}

function listFilesInDb($uid, $start, $count, $search = '') {
    try {
        $conn = connectDb();
        if(empty($search)) {
            $sql = "select id, name, size, sha256, create_time from files where uid=:uid limit :start , :count";
        } else {
            $sql = "select id, name, size, sha256, create_time from files where uid=:uid and name like :search limit :start , :count";
        }

        $stmt = $conn->prepare($sql);
        $stmt->bindValue(':uid', (int)$uid, PDO::PARAM_INT);
        $stmt->bindValue(':start', (int)$start, PDO::PARAM_INT);
        $stmt->bindValue(':count', (int)$count, PDO::PARAM_INT);
        if(!empty($search)) {
            $stmt->bindValue(':search', '%' . $search . '%', PDO::PARAM_STR);
        }
        $stmt->execute();
        $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
        return $result;
    } catch(PDOException $e) {
        throw $e;
    }

}

function getSavedCipherTextFromDb($id, $uid) {
    try {
        $conn = connectDb();
        $sql = "select enckey,nonce, name, size, sha256, create_time from files where uid=:uid and id=:id";

        $stmt = $conn->prepare($sql);
        $stmt->bindValue(':uid', (int)$uid, PDO::PARAM_INT);
        $stmt->bindValue(':id', (int)$id, PDO::PARAM_INT);
        $stmt->execute();
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return array($result['enckey'], $result['nonce'],$result['name'], $result['size'], $result['sha256'], $result['create_time']);
    } catch(PDOException $e) {
        throw $e;
    }
}



function getSavedCipher_sign_TextFromDb($id, $uid) {  //用于从files 中取出文件的sign
    try {
        $conn = connectDb();
        $sql = "select sign  from files where uid=:uid and id=:id";

        $stmt = $conn->prepare($sql);
        $stmt->bindValue(':uid', (int)$uid, PDO::PARAM_INT);
        $stmt->bindValue(':id', (int)$id, PDO::PARAM_INT);
        $stmt->execute();
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result['sign'];
    } catch(PDOException $e) {
        throw $e;
    }
}



function findDuplicateFileInDb($sha256,$uid) {
    try {
        $conn = connectDb();
        $sql = "select count(id) as dup from files where sha256=:sha256 and uid =:uid";
        $stmt = $conn->prepare($sql);
        $stmt->bindParam(':sha256', $sha256, PDO::PARAM_STR);
         $stmt->bindParam(':uid', $uid, PDO::PARAM_STR);
        $stmt->execute();
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result['dup'];
    } catch(PDOException $e) {
        throw $e;
    }
}


function deleteFileInDb($sha256, $uid) {
    try {
        $conn = connectDb();
        $sql = "delete from files where sha256=:sha256 and uid=:uid";
        $stmt = $conn->prepare($sql);
        $stmt->bindParam(':uid', $uid, PDO::PARAM_INT);
        $stmt->bindParam(':sha256', $sha256, PDO::PARAM_STR);
        return $stmt->execute();
    } catch(PDOException $e) {
        throw $e;
    }
}

function findFileByShasumAndUid($sha256, $uid) {
    try {
        $conn = connectDb();
        $sql = "select create_time from files where sha256=:sha256 and uid=:uid";
        $stmt = $conn->prepare($sql);
        $stmt->bindParam(':uid', $uid, PDO::PARAM_INT);
        $stmt->bindParam(':sha256', $sha256, PDO::PARAM_STR);
        $stmt->execute();
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return isset($result['create_time']) ? $result['create_time'] : NULL;
    } catch(PDOException $e) {
        throw $e;
    }
}

function validateUserFileOwnershipInDb($uid, $fid, $sha256) {
    try {
        $conn = connectDb();
        $sql = "select count(id) as has_ownership from files where id=:fid and uid=:uid and sha256=:sha256";
        $stmt = $conn->prepare($sql);
        $stmt->bindParam(':uid', $uid, PDO::PARAM_INT);
        $stmt->bindParam(':fid', $fid, PDO::PARAM_INT);
        $stmt->bindParam(':sha256', $sha256, PDO::PARAM_STR);
        $stmt->execute();
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result['has_ownership'];
    } catch(PDOException $e) {
        throw $e;
    }
}

function saveShareFileInfoInDb($fid, $shareKeyHash, $enc_key_in_db, $shareFilePath, $nonce,$_sign) {
    try {
        $conn = connectDb();
        $sql = "INSERT INTO share (fid, sharekey, enckey, filepath, nonce1,_sign) VALUES (:fid, :sharekey, :enckey, :filepath, :nonce ,:_sign)";
        $stmt = $conn->prepare($sql);
        $stmt->bindParam(':fid', $fid);
        $stmt->bindParam(':sharekey', $shareKeyHash);
        $stmt->bindParam(':enckey', $enc_key_in_db);
        $stmt->bindParam(':filepath', $shareFilePath);
        $stmt->bindParam(':nonce', $nonce);
        $stmt->bindParam(':_sign', $_sign);
        return $stmt->execute();
    } catch(PDOException $e) {
        throw $e;
    }
}

function getFileShareInfoFromDb($fid, $nonce) {
    try {
        $conn = connectDb();
        $sql = "select users.name as uname, dcount, sharekey, share.enckey as enckey, filepath, files.name as fname, size , share._sign from share left join files on share.fid=files.id left join users on files.uid=users.id where fid=:fid and nonce1=:nonce1";

        $stmt = $conn->prepare($sql);
        $stmt->bindValue(':fid', (int)$fid, PDO::PARAM_INT);
        $stmt->bindValue(':nonce1', $nonce, PDO::PARAM_STR);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    } catch(PDOException $e) {
        throw $e;
    }
}


function getFileShareSignFromDb($fid, $nonce) {
    try {
        $conn = connectDb();
        $sql = "select  _sign from share  where fid=:fid and nonce=:nonce";

        $stmt = $conn->prepare($sql);
        $stmt->bindValue(':fid', (int)$fid, PDO::PARAM_INT);
        $stmt->bindValue(':nonce', $nonce, PDO::PARAM_STR);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    } catch(PDOException $e) {
        throw $e;
    }
}


function getFileShareUid($fid, $nonce) {
    try {
        $conn = connectDb();
        $sql = "select users.pubkey as pub_key from share left join files on share.fid=files.id left join users on files.uid=users.id where fid=:fid and nonce=:nonce";

        $stmt = $conn->prepare($sql);
        $stmt->bindValue(':fid', (int)$fid, PDO::PARAM_INT);
        $stmt->bindValue(':nonce', $nonce, PDO::PARAM_STR);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    } catch(PDOException $e) {
        throw $e;
    }
}

function updateDownloadCountInDb($fid, $nonce) {
    try {
        $conn = connectDb();
        $sql = "update share set dcount=dcount+1, access_time=now() where fid=:fid and nonce1=:nonce";
        $stmt = $conn->prepare($sql);
        $stmt->bindParam(':fid', $fid);
        $stmt->bindParam(':nonce', $nonce);
        return $stmt->execute();

    } catch(PDOException $e) {
        throw $e;
    }
}



