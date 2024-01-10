<?php
  require "config.php";
  //パラメーター取得
  $id = !empty($_POST['id'])?$_POST['id']:0;
  $pass = passEx(!empty($_POST['pass'])?$_POST['pass']:0,$id);
  $auto = !empty($_POST['auto'])?$_POST['id']:0;
  $cookie_token = !empty($_COOKIE['token'])?$_COOKIE['token']:"";
 
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
    //session_regenerate_id(0);
    
    //トークン生成処理
    if (($normal_result  == 0 && $auto == true) || $auto_result == 0) {

      //トークンの作成
      $token = get_token();
    
     //トークンの登録
     register_token($id, $token);
 
     //自動ログインのトークンを２週間の有効期限でCookieにセット
     setCookie("token", $token, time()+60*60*24*14, "/", "",true,true);
 
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
	$pdo_h = new PDO(DNS, USER_NAME, PASSWORD, get_pdo_options()); 

	unset($sql);
	$sql = "select * from users where ((id)=?) and ((pass)=?)";
	$stmt = $pdo_h->prepare($sql);
	$stmt->bindValue(1, $id, PDO::PARAM_STR);
	$stmt->bindValue(2, $pass, PDO::PARAM_STR);
	$stmt->execute();
	$row_cnt = $stmt->rowCount();
	//$row = $stmt->fetchAll(PDO::FETCH_ASSOC);
	if($row_cnt==0){
		echo "<P>ＩＤ 又はパスワードが間違っています。</P>".$id.$pass;
		?><a href="index.php"> 戻る</a><?php
		exit();
	}
	return 0;
 }
 
?>