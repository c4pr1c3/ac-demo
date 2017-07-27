<?php

function connectDb() {
    $servername = "localhost";
    $username = "root";
    $password = "12345678";
    $dbname = "ac2017";
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

function getFileCountInDb($uid) {
    try {
        $conn = connectDb();
        $sql = "select count(id) as total from files where uid=:uid";
        $stmt = $conn->prepare($sql);
        $stmt->bindParam(':uid', $uid, PDO::PARAM_INT);
        $stmt->execute();
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result['total'];
    } catch(PDOException $e) {
        throw $e;
    }
}

function listFilesInDb($uid, $start, $count) {
    try {
        $conn = connectDb();
        $sql = "select id, name, size, sha256, create_time from files where uid=:uid limit :start , :count";
        $stmt = $conn->prepare($sql);
        $stmt->bindValue(':uid', (int)$uid, PDO::PARAM_INT);
        $stmt->bindValue(':start', (int)$start, PDO::PARAM_INT);
        $stmt->bindValue(':count', (int)$count, PDO::PARAM_INT);
        $stmt->execute();
        $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
        return $result;
    } catch(PDOException $e) {
        throw $e;
    }

}




