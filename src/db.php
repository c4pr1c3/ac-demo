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
        PDO::ATTR_PERSISTENT => true
    );
    $conn = new PDO("mysql:host=$servername;dbname=$dbname;charset=$charset", $username, $password, $options);
    return $conn;
}

function checkRegisterInDb($name) {
    try {
        $conn = connectDb();
        $sql = "select password from users where name=:name";
        $stmt = $conn->prepare($sql);
        $stmt->bindParam(':name', $name);
        $stmt->execute();
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return isset($result['password']) ? $result['password'] : '';
    } catch(PDOException $e) {
        throw $e;
    }
}

function registerInDb($name, $password, $pubkey, $privkey) {
    try {
        $conn = connectDb();
        $sql = "INSERT INTO users (name, password, pubkey, privkey) VALUES (:name, :password, :pubkey, :privkey)";
        $stmt = $conn->prepare($sql);
        $stmt->bindParam(':name', $name);
        $stmt->bindParam(':password', $password);
        $stmt->bindParam(':pubkey', $pubkey);
        $stmt->bindParam(':privkey', $privkey);
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

function uploadFileInDb($name, $size, $enckey, $sha256, $uid, $datetime) {
    try {
        $conn = connectDb();
        $sql = "INSERT INTO files (name, size, enckey, sha256, uid, create_time) VALUES (:name, :size, :enckey, :sha256, :uid, :datetime)";
        $stmt = $conn->prepare($sql);
        $stmt->bindParam(':name', $name);
        $stmt->bindParam(':size', $size);
        $stmt->bindParam(':enckey', $enckey);
        $stmt->bindParam(':sha256', $sha256);
        $stmt->bindParam(':uid', $uid);
        $stmt->bindParam(':datetime', $datetime);
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
        $sql = "select enckey, name, size, sha256, create_time from files where uid=:uid and id=:id";

        $stmt = $conn->prepare($sql);
        $stmt->bindValue(':uid', (int)$uid, PDO::PARAM_INT);
        $stmt->bindValue(':id', (int)$id, PDO::PARAM_INT);
        $stmt->execute();
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return array($result['enckey'], $result['name'], $result['size'], $result['sha256'], $result['create_time']);
    } catch(PDOException $e) {
        throw $e;
    }
}

function findDuplicateFileInDb($sha256) {
    try {
        $conn = connectDb();
        $sql = "select count(id) as dup from files where sha256=:sha256";
        $stmt = $conn->prepare($sql);
        $stmt->bindParam(':sha256', $sha256, PDO::PARAM_STR);
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
        $sql = "select users.name as uname,pubkey, dcount, sharekey, share.enckey as enckey, filepath, files.name as fname, size from share left join files on share.fid=files.id left join users on files.uid=users.id where fid=:fid and nonce=:nonce";

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



