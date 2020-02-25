<?php

function emailAuth($email){

    $pdo = pdoSqlConnect();
    $query = "SELECT EXISTS(SELECT * FROM users WHERE email= ?) AS exist;";


    $st = $pdo->prepare($query);
    $st->execute([$email]);
    $st->setFetchMode(PDO::FETCH_ASSOC);
    $res = $st->fetchAll();

    $st=null;
    $pdo = null;

    return intval($res[0]["exist"]);
}

function signUp($email,$pw){

    $pdo = pdoSqlConnect();
    $query = "INSERT INTO users (email,password) VALUES (?,?);";

    $st = $pdo->prepare($query);
    $st->execute([$email,$pw]);

    $st = null;
    $pdo = null;

    return 100;
}