<?php
// =========================================================
// 共通関数
// =========================================================

function decho($msg){
	if(__DIR__=="/home/ifduktdo/public_html/training_test"){
		echo $msg;
	}
	return 0;
}

// =========================================================
// 不可逆暗号化
// =========================================================
function passEx($str,$uid){
	if(strlen($str)<=8 and !empty($uid)){
		$rtn = crypt($str,key);
		for($i = 0; $i < 1000; $i++){
			$rtn = substr(crypt($rtn.$uid,key),2);
		}
	}else{
		$rtn = $str;
	}
	return $rtn;
}
// =========================================================
// 可逆暗号
// =========================================================
function rot13encrypt ($str) {
	//暗号化
  //return str_rot13(base64_encode($str));
	return $str;
}

function rot13decrypt ($str) {
	//暗号化解除
  //return base64_decode(str_rot13($str));
	return $str;
}

// =========================================================
// MAXウェイト算出
// =========================================================
function max_r($wt, $rep){
	if($rep == 1){
		return (double)$wt;
	}elseif($rep == 2){
		return (double)$wt / 0.95;
	}elseif($rep == 3){
		return (double)$wt / 0.93;
	}elseif($rep == 4){
		return (double)$wt / 0.90;
	}elseif($rep == 5){
		return (double)$wt / 0.87;
	}elseif($rep == 6){
		return (double)$wt / 0.85;
	}elseif($rep == 7){
		return (double)$wt / 0.82;
	}elseif($rep == 8){
		return (double)$wt / 0.80;
	}elseif($rep == 9){
		return (double)$wt / 0.77;
	}elseif($rep == 10){
		return (double)$wt / 0.75;
	}else{
		return (double)$wt;
	}
	
}


 
//---------------------------------------------------------------------------//
//自動ログイン処理
//--------------------------------------------------------------------------//
 function check_auto_login($token) {
	$pdo_h = new PDO(DNS, USER_NAME, PASSWORD, get_pdo_options());
  //2週間前の日付を取得
	$date = new DateTime("- 14 days");
	unset($sql);
 	$datetime = $date->format('Y-m-d H:i:s');

	$sql = "SELECT * FROM AUTO_LOGIN WHERE TOKEN = ? AND REGISTRATED_TIME >= ?;";

	$stmt = $pdo_h->prepare($sql);
	$stmt->bindValue(1, $token, PDO::PARAM_STR);
	$stmt->bindValue(2, $datetime, PDO::PARAM_STR);
	$stmt->execute();
	$row_cnt = $stmt->rowCount();

	if ($row_cnt == 1) {
    	while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
     		$_SESSION['USER_ID']  = $row['USER_ID'];
    	}
  	} else {
	   	//自動ログイン失敗
   		//Cookie のトークンを削除
   		setCookie("token", '', -1, "/", "", true, true);
 
	    //古くなったトークンを削除
   		delete_old_token($token);
    	return 1;
	}
    return 0;

}
 
//---------------------------------------------------------------------------//
//トークンの登録
//---------------------------------------------------------------------------//
 function register_token($id, $token) {
	$pdo_h = new PDO(DNS, USER_NAME, PASSWORD, get_pdo_options());

  //プレースホルダで SQL 作成
  $sql = "INSERT INTO AUTO_LOGIN ( USER_ID, TOKEN, REGISTRATED_TIME) VALUES (?,?,?);";

  //パラメーターの型を指定
  $stmt = $pdo_h->prepare($sql);
	$stmt->bindValue(1, $id, PDO::PARAM_STR);
	$stmt->bindValue(2, $token, PDO::PARAM_STR);
	$stmt->bindValue(3, date('Y-m-d H:i:s'), PDO::PARAM_STR);
		
    //パラメーターを渡して SQL 実行
	$stmt->execute();
 	
 	return 0;
 
 }
 
//---------------------------------------------------------------------------//
//トークンの削除
//---------------------------------------------------------------------------//
function delete_old_token($token) {
  //DB接続
  $pdo_h = new PDO(DNS, USER_NAME, PASSWORD, get_pdo_options());

  //プレースホルダで SQL 作成
  $sql = "DELETE  FROM AUTO_LOGIN WHERE TOKEN = ?";

  //パラメーターの型を指定
	$stmt = $pdo_h->prepare($sql);
	$stmt->bindValue(1, $token, PDO::PARAM_STR);
	
  //パラメーターを渡して SQL 実行
	$stmt->execute();
 	return 0;
}
 
//---------------------------------------------------------------------------//
// トークンを作成
//---------------------------------------------------------------------------//
function get_token() {
  $TOKEN_LENGTH = 16;//16*2=32桁
  $bytes = openssl_random_pseudo_bytes($TOKEN_LENGTH);
  return bin2hex($bytes);
}

// =========================================================
// PDO の接続オプション取得
// =========================================================
function get_pdo_options() {
  return array(PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
               PDO::MYSQL_ATTR_MULTI_STATEMENTS => false,   //sqlの複文禁止 "select * from hoge;delete from hoge"みたいなの
               PDO::ATTR_EMULATE_PREPARES => false);        //同上
}

?>
