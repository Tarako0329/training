<?php
  require "config.php";

  require "functions.php";
 
 
  //パラメーター取得
  $id = $_POST['id'];
  $pass = passEx($_POST["pass"],$id);
  $auto = $_POST['auto'];
  $cookie_token = $_COOKIE['token'];
 
  //ログイン判定フラグ 0:ok 1:ng
  $normal_result = 1;
  $auto_result = 1;
  
 
  //簡易ログイン
  if (!isset($cookie_token)) {
   if (check_user($id, $pass) == 0) {
      $normal_result = 0;
    }
  }
 
  //自動ログイン
  if (isset($cookie_token) ) {
    if (check_auto_login($cookie_token) == 0) {
    	$auto_result = 0;
    	$id = $_SESSION['USER_ID'];
    }
  }


 
  //ログイン判定
  if ($normal_result == 0 || $auto_result == 0) {
    //ログイン成功

    //セッション ID の振り直し
   session_regenerate_id(0);
    
    //トークン生成処理
    if (($normal_result  == 0 && $auto == true) || $auto_result == 0) {

      //トークンの作成
      $token = get_token();
    
     //トークンの登録
     register_token($id, $token);
 
     //自動ログインのトークンを２週間の有効期限でCookieにセット
     setCookie("token", $token, time()+60*60*24*14, "/", null, 0, 0);
 
     //古いトークンの削除
     delete_old_token($cookie_token);
  
  }
 
 
    //リダイレクト
    header("HTTP/1.1 301 Moved Permanently");
    header("Location: TOP.php");
  } else {
    //ログイン失敗
  	
    //リダイレクト
    header("HTTP/1.1 301 Moved Permanently");
    header("Location: index.php");
  }


?>