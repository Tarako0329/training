<?php
	declare(strict_types=1);
	require_once "config.php";
	use classes\Security\Security;
	//require_once "database.php";
	//$db = new Database();	

	//パラメーター取得
	$id = $_POST['id'] ?? "0";
	$P_login_type = $_POST['login_type'] ?? "";
	$S_login_type = $_SESSION['login_type'] ?? "";

	if($P_login_type===$S_login_type && $P_login_type==="google"){
		$pass='%';
	}else{
		//$pass = passEx(!empty($_POST['pass'])?$_POST['pass']:0,$id);
		$pass = $_POST['pass'] ?? "xxxx";
	}
	$cookie_token = $_COOKIE['token'] ?? "";
 
	//ログイン判定フラグ 0:ok 1:ng
	$normal_result = 1;
	$auto_result = 1;
	
	//log_writer("\$cookie_token",$cookie_token);
 
	//簡易ログイン
	if (!U::exist($cookie_token)) {
	 //if (check_user($id, $pass) == 0) {
	 if (check_user($id, $pass) || $pass === "%") {
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
function check_user(string $id="-1", string $pass=""):bool {
	//$pdo_h = new PDO(DNS, USER_NAME, PASSWORD, get_pdo_options()); 
	
	$Security = new Security($id,key);
	global $db;
	//$db = new Database();	
	//$sql = "SELECT * from users where id = :id and pass like :pass";
	//$row = $db->SELECT($sql,[":id" => $id,"pass" => $pass]);
	$sql = "SELECT * from users where id = :id ";
	$row = $db->SELECT($sql,[":id" => $id]);
	$row_cnt = count($row);

	if($row_cnt===0){
		return false;
	}
	
	return $Security->verifyPassword($pass, $row[0]['pass']);
	/*
	if($row_cnt===0){
		return 1;
	}
	return 0;
	*/
}

 
?>