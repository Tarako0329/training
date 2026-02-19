<?php
  require_once "config.php";
  require_once "database.php";
  $db = new Database();

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
  
  if($_POST["btn"] == "w_ins_bt"){
    //体組織記録画面の「記録」ボタン
    try{
      //デリイン（１日１件）
      //$pdo_h->beginTransaction();
      $db->begin_tran();
      $sql = "DELETE from taisosiki where id = :id and ymd = :ymd;";
      $db->UP_DEL_EXEC($sql,[":id"=>$id,":ymd"=>$_POST["ymd"]]);
      /*
      $stmt = $pdo_h->prepare($sql);
      $stmt->bindValue(1, $id, PDO::PARAM_STR);
      $stmt->bindValue(2, $_POST["ymd"], PDO::PARAM_STR);
      $stmt->execute();
      */
      /*
      $sql = "insert into taisosiki values (?,?,?,?,?,?,'','',?);";
      $stmt = $pdo_h->prepare($sql);
      $stmt->bindValue(1, $id, PDO::PARAM_STR);
      $stmt->bindValue(2, $_POST["ymd"], PDO::PARAM_STR);
      $stmt->bindValue(3, $_POST["weight"], PDO::PARAM_STR);
      $stmt->bindValue(4, $_POST["sibo"], PDO::PARAM_STR);
      $stmt->bindValue(5, "", PDO::PARAM_STR);
      $stmt->bindValue(6, "", PDO::PARAM_STR);
      $stmt->bindValue(7, $_POST["memo"], PDO::PARAM_STR);
      $stmt->execute();
      */
      $db->INSERT("taisosiki",[
        "id" => $id,
        "ymd" => $_POST["ymd"],
        "weight" => $_POST["weight"],
        "taisibou" => $_POST["sibo"],
        "memo" => $_POST["memo"]
      ]);
      
      //$pdo_h->commit();
      $db->commit_tran();
    }catch(Exception $e){
      $msg = "catch Exception \$e：".$e;
      //$pdo_h->rollBack();
      $db->rollback_tran($msg);
    }
    //リダイレクト
    header("HTTP/1.1 307 Moved Permanently");
    header("Location: graph_taisosiki.php");
    exit();
  } 
  //ログイン失敗
  //リダイレクト
  header("HTTP/1.1 301 Moved Permanently");
  header("Location: index.php");
  exit();
?>