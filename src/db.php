<?php

function connectDb() {
    // 编辑/etc/apache2/envvars，添加WEB服务器的环境变量供PHP代码读取数据库连接配置信息
    $servername = getenv('DB_AC_SERVERNAME');
    $username = getenv('DB_AC_USERNAME');
    $password = getenv('DB_AC_PASSWORD');
    $dbname = getenv('DB_AC_DBNAME');
    $charset = "utf8mb4";
    $options = array(
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        // PDO::ATTR_PERSISTENT => true
    );
    $conn = new PDO("mysql:host=$servername;dbname=$dbname;charset=$charset", $username, $password, $options);
    return $conn;
}

function checkRegisterInDb($name, $email) {
    try {
        $conn = connectDb();
        $sql = "select password from users where name=:name or email=:email"; //prevent SQL injection
        $stmt = $conn->prepare($sql);
        $stmt->bindParam(':name', $name);
        $stmt->bindParam(':email',$email);
        $stmt->execute();
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return isset($result['password']) ? $result['password'] : '';
    } catch(PDOException $e) {
        throw $e;
    }
}

function registerInDb($name, $email, $password, $salt, $nonce, $pubkey) {
    try {
        $conn = connectDb();
        $sql = "INSERT INTO users (name, email, password, salt, nonce, pubkey) VALUES (:name, :email, :password, :salt, :nonce, :pubkey)";
        $stmt = $conn->prepare($sql);
        $stmt->bindParam(':name', $name);
        $stmt->bindParam('email', $email);
        $stmt->bindParam(':password', $password);
        $stmt->bindParam(':salt', $salt);
        $stmt->bindParam(':nonce', $nonce);
        $stmt->bindParam(':pubkey', $pubkey);
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

function uploadFileInDb($name, $size, $enckey, $sodium_hash, $nonce, $uid, $datetime, $signature) {
    try {
        $conn = connectDb();
        $sql = "INSERT INTO files (name, size, enckey, sodium_hash, nonce, uid, create_time, signature) VALUES (:name, :size, :enckey, :sodium_hash, :nonce, :uid, :datetime, :signature)";
        $stmt = $conn->prepare($sql);
        $stmt->bindParam(':name', $name);
        $stmt->bindParam(':size', $size);
        $stmt->bindParam(':enckey', $enckey);
        $stmt->bindParam(':sodium_hash', $sodium_hash);
        $stmt->bindParam(':nonce', $nonce);
        $stmt->bindParam(':uid', $uid);
        $stmt->bindParam(':datetime', $datetime);
        $stmt->bindParam(':signature', $signature);
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
            $sql = "select id, name, size, sodium_hash, create_time from files where uid=:uid limit :start , :count";
        } else {
            $sql = "select id, name, size, sodium_hash, create_time from files where uid=:uid and name like :search limit :start , :count";
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
        $sql = "select enckey, name, size, sodium_hash, nonce, create_time from files where uid=:uid and id=:id";

        $stmt = $conn->prepare($sql);
        $stmt->bindValue(':uid', (int)$uid, PDO::PARAM_INT);
        $stmt->bindValue(':id', (int)$id, PDO::PARAM_INT);
        $stmt->execute();
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return array(trim($result['enckey']), trim($result['name']), trim($result['size']), trim($result['sodium_hash']), trim($result['nonce']), trim($result['create_time']));
    } catch(PDOException $e) {
        throw $e;
    }
}

function findDuplicateFileInDb($sodium_hash, $uid) {
    try {
        $conn = connectDb();
        $sql = "select count(id) as dup from files where sodium_hash=:sodium_hash and uid=:uid";
        $stmt = $conn->prepare($sql);
        $stmt->bindParam(':sodium_hash', $sodium_hash, PDO::PARAM_STR);
        $stmt->bindParam(':uid', $uid, PDO::PARAM_INT);
        $stmt->execute();
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result['dup'];
    } catch(PDOException $e) {
        throw $e;
    }
}

function deleteFileInDb($sodium_hash, $uid) {
    try {
        $conn = connectDb();
        $sql = "delete from files where sodium_hash=:sodium_hash and uid=:uid";
        $stmt = $conn->prepare($sql);
        $stmt->bindParam(':uid', $uid, PDO::PARAM_INT);
        $stmt->bindParam(':sodium_hash', $sodium_hash, PDO::PARAM_STR);
        return $stmt->execute();
    } catch(PDOException $e) {
        throw $e;
    }
}

function findFileByHashAndUid($sodium_hash, $uid) {
    try {
        $conn = connectDb();
        $sql = "select create_time from files where sodium_hash=:sodium_hash and uid=:uid";
        $stmt = $conn->prepare($sql);
        $stmt->bindParam(':uid', $uid, PDO::PARAM_INT);
        $stmt->bindParam(':sodium_hash', $sodium_hash, PDO::PARAM_STR);
        $stmt->execute();
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return isset($result['create_time']) ? $result['create_time'] : NULL;
    } catch(PDOException $e) {
        throw $e;
    }
}

function validateUserFileOwnershipInDb($uid, $fid, $sodium_hash) {
    try {
        $conn = connectDb();
        $sql = "select count(id) as has_ownership from files where id=:fid and uid=:uid and sodium_hash=:sodium_hash";
        $stmt = $conn->prepare($sql);
        $stmt->bindParam(':uid', $uid, PDO::PARAM_INT);
        $stmt->bindParam(':fid', $fid, PDO::PARAM_INT);
        $stmt->bindParam(':sodium_hash', $sodium_hash, PDO::PARAM_STR);
        $stmt->execute();
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result['has_ownership'];
    } catch(PDOException $e) {
        throw $e;
    }
}

function saveShareFileInfoInDb($fid, $shareKeyHash, $enc_key_in_db, $shareFilePath, $nonce) {
    try {
        $conn = connectDb();
        $sql = "INSERT INTO share (fid, sharekey, enckey, filepath, nonce) VALUES (:fid, :sharekey, :enckey, :filepath, :nonce)";
        $stmt = $conn->prepare($sql);
        $stmt->bindParam(':fid', $fid);
        $stmt->bindParam(':sharekey', $shareKeyHash);
        $stmt->bindParam(':enckey', $enc_key_in_db);
        $stmt->bindParam(':filepath', $shareFilePath);
        $stmt->bindParam(':nonce', $nonce);
        return $stmt->execute();
    } catch(PDOException $e) {
        throw $e;
    }
}

function getFileShareInfoFromDb($fid, $nonce) {
    try {
        $conn = connectDb();
        $sql = "select users.name as uname, dcount, sharekey, share.enckey as enckey, filepath, files.name as fname, size, share.nonce as nonce from share left join files on share.fid=files.id left join users on files.uid=users.id where fid=:fid and share.nonce=:nonce";

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
        $sql = "update share set dcount=dcount+1, access_time=now() where fid=:fid and nonce=:nonce";
        $stmt = $conn->prepare($sql);
        $stmt->bindParam(':fid', $fid);
        $stmt->bindParam(':nonce', $nonce);
        return $stmt->execute();

    } catch(PDOException $e) {
        throw $e;
    }
}

// 找回密码
function getResetFromDb($email) {
    try {
        $conn = connectDb();
        $sql = "select email from users where email=:email";

        $stmt = $conn->prepare($sql);
        $stmt->bindValue(':email', $email);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    } catch(PDOException $e) {
        throw $e;
    }
}

function getFileLinkInfoFromDb($email){
    try{
        $conn = connectDb();
        $sql = "select id as id from users where email = :email";
        $stmt = $conn->prepare($sql);
        $stmt->bindValue(':email',$email);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }catch(PDOException $e){
        throw $e;
    }
}

function updateCheckTime($email, $flag){
    try {
        $conn = connectDb();
        if ($flag==0) {
            $sql = "update users set update_time=now() where email = :email";
            $stmt = $conn->prepare($sql);
            $stmt->bindParam(':email', $email);
            return $stmt->execute();
        }
        else if($flag==1){
            $sql = "update users set update_time=NULL where email = :email";
            $stmt = $conn->prepare($sql);
            $stmt->bindParam(':email', $email);
            return $stmt->execute();
        }
    } catch(PDOException $e) {
        throw $e;
    }
}

function findCheckTime($email){
    try {
        $conn = connectDb();
        $sql = "select UNIX_TIMESTAMP(update_time) as time from users where email = :email";
        $stmt = $conn->prepare($sql);
        $stmt->bindValue(':email',$email);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch(PDOException $e) {
        throw $e;
    }
}

function updateResetUsers($email, $hashedPassword, $salt, $nonce) {
    try {
        $conn = connectDb();
        $sql = "update users set password = :hashedPassword, nonce = :nonce, salt = :salt where email=:email";
        $stmt = $conn->prepare($sql);
        $stmt->bindParam(':email', $email);
        $stmt->bindParam(':hashedPassword',$hashedPassword);
        $stmt->bindParam(':nonce',$nonce);
        $stmt->bindParam(':salt',$salt);
        return $stmt->execute();

    } catch(PDOException $e) {
        throw $e;
    }
}


function saveToken($email, $token) {
    try {
        $conn = connectDb();
        $sql = "update users set token = :token where email=:email";
        $stmt = $conn->prepare($sql);
        $stmt->bindParam(':email',$email);
        $stmt->bindParam(':token', $token);
        return $stmt->execute();
    } catch(PDOException $e) {
        throw $e;
    }
}

function updateTokenCountInDb1($email) {
    try {
        $conn = connectDb();
        $sql = "update users set token = NULL where email=:email";
        $stmt = $conn->prepare($sql);
        $stmt->bindParam(':email', $email);  
        return $stmt->execute();
    } catch(PDOException $e) {
        throw $e;
    }
}

function checkTokenCountInDb($email,$token) {
    try {      
        $conn = connectDb();
        $sql = "select token as token from users where email = :email and token = :token";
        $stmt = $conn->prepare($sql);
        $stmt->bindValue(':email',$email);
        $stmt->bindValue(':token',$token);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch(PDOException $e) {
        throw $e;
    }
}