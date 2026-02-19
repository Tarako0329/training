<?php
  require_once "config.php";
  require_once "database.php";
  $db = new Database();	

  //パラメーター取得
  $id = !empty($_POST['id'])?$_POST['id']:0;

  if($_SESSION["login_type"]===$_POST["login_type"] && $_POST["login_type"]==="google"){
    $pass='%';
  }else{
    $pass = passEx(!empty($_POST['pass'])?$_POST['pass']:0,$id);
  }
  $cookie_token = !empty($_COOKIE['token'])?$_COOKIE['token']:"";
 
  //ログイン判定フラグ 0:ok 1:ng
  $normal_result = 1;
  $auto_result = 1;
  
  //log_writer("\$cookie_token",$cookie_token);
 
  //簡易ログイン
  if (empty($cookie_token)) {
   if (check_user($id, $pass) == 0) {
      $normal_result = 0;
    }else{
      $_SESSION["msg"]='ＩＤ 又はパスワードが間違っています。';
    }
  }else {//自動ログイン
    if (check_auto_login($cookie_token) == 0) {
    	$auto_result = 0;
    	$id = $_SESSION['USER_ID'];
    }else{
      $_SESSION["msg"]='自動ログインの期限切れです。再ログインしてください。';
    }
  }
 
  //ログイン判定
  if ($normal_result == 0 || $auto_result == 0) {
    //ログイン成功

    //トークン生成処理
    if ($normal_result  == 0 || $auto_result == 0) {
      //トークンの作成
      $token = get_token();
       //トークンの登録
       register_token($id, $token);
       //自動ログインのトークンを4週間の有効期限でCookieにセット
       setCookie("token", $token, time()+60*60*24*28, "/", "",true,true);
       //古いトークンの削除
       delete_old_token($cookie_token);
       $_SESSION['USER_ID'] = $id;
      
    }
  
    //リダイレクト
    header("HTTP/1.1 301 Moved Permanently");
    header("Location: TOP.php");
    exit();
  } else {
    //ログイン失敗
  	
    //リダイレクト
    header("HTTP/1.1 301 Moved Permanently");
    header("Location: index.php");
    exit();
  }

//---------------------------------------------------------------------------//
// ログイン処理
//---------------------------------------------------------------------------//
function check_user($id, $pass) {
	//$pdo_h = new PDO(DNS, USER_NAME, PASSWORD, get_pdo_options()); 

  $db = new Database();	
	$sql = "SELECT * from users where id = :id and pass like :pass";
  //$sql = "SELECT * from users where id = :id";
  /*
	$stmt = $pdo_h->prepare($sql);
	$stmt->bindValue(1, $id, PDO::PARAM_STR);
	$stmt->bindValue(2, $pass, PDO::PARAM_STR);
	$stmt->execute();
	$row_cnt = $stmt->rowCount();
	//$row = $stmt->fetchAll(PDO::FETCH_ASSOC);
  */
  $row = $db->SELECT($sql,[":id" => $id,"pass" => $pass]);
  $row_cnt = count($row);
  
  if($row_cnt===0){
    return 1;
  }
  return 0;
}

 
?>