<?php

function connectDb() {
    $servername = "localhost";
    $username = "root";
    $password = "12345678";
    $dbname = "ac2017";
    $charset = "utf8mb4";
    $conn = new PDO("mysql:host=$servername;dbname=$dbname;charset=$charset", $username, $password);
    // set the PDO error mode to exception
    $conn->setAttribute(array(
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_PERSISTENT => true
    ));
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
