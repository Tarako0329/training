<?php
  require "config.php";
  //require "functions.php";
  //トランザクション処理
  if(isset($_SESSION['USER_ID'])){
    $id = $_SESSION['USER_ID'];
    decho ("session:".$id);
  }else if (check_auto_login($_COOKIE['token'])==0) {
    $id = $_SESSION['USER_ID'];
    decho ("クッキー:".$id);
  }else{
    header("HTTP/1.1 301 Moved Permanently");
    header("Location: index.php");
    exit();
  }
  
  if($_POST["btn"] == "w_rireki"){
    //体組織記録画面の「履歴」ボタン
    //リダイレクト
    header("HTTP/1.1 307 Moved Permanently");
    header("Location: graph02.php");
    exit();
  }
  if($_POST["btn"] == "w_ins_bt"){
    //体組織記録画面の「記録」ボタン
    try{
      $pdo_h->beginTransaction();
      $sql = "insert into taisosiki values (?,?,?,?,?,?,'','',?);";
      $stmt = $pdo_h->prepare($sql);
      $stmt->bindValue(1, $id, PDO::PARAM_STR);
      $stmt->bindValue(2, $_POST["ymd"], PDO::PARAM_STR);
      $stmt->bindValue(3, $_POST["weight"], PDO::PARAM_STR);
      $stmt->bindValue(4, $_POST["sibo"], PDO::PARAM_STR);
      $stmt->bindValue(5, $_POST["yobi1"], PDO::PARAM_STR);
      $stmt->bindValue(6, $_POST["yobi2"], PDO::PARAM_STR);
      $stmt->bindValue(7, $_POST["memo"], PDO::PARAM_STR);
      $stmt->execute();
      $pdo_h->commit();
    }catch(Exception $e){
      $pdo_h->rollBack();
    }
    //リダイレクト
    header("HTTP/1.1 307 Moved Permanently");
    header("Location: graph02.php");
    exit();
  } 
    //ログイン失敗
    //リダイレクト
    header("HTTP/1.1 301 Moved Permanently");
    header("Location: index.php");

?>