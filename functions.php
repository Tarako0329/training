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
    return str_rot13(base64_encode($str));
}

function rot13decrypt ($str) {
	//暗号化解除
    return base64_decode(str_rot13($str));
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
// ログイン処理
//---------------------------------------------------------------------------//
function check_user($id, $pass) {
    $mysqli = new mysqli(sv, user, pass, dbname);

	unset($sql);
	$sql = "select * from users where ((id)='".$id."') and ((pass)='".$pass."')";
	$result = $mysqli->query( $sql );
	$row_cnt = $result->num_rows;
	$row = $result->fetch_assoc(); 
	if($row_cnt==0){
		echo "<P>ＩＤ 又はパスワードが間違っています。</P>".$id.$pass;
		?><a href="index.php"> 戻る</a><?php
		exit();
	}
	return 0;
 }
 
 
//---------------------------------------------------------------------------//
//自動ログイン処理
//--------------------------------------------------------------------------//
 function check_auto_login($token) {
    $mysqli = new mysqli(sv, user, pass, dbname);
 
  //2週間前の日付を取得
	$date = new DateTime("- 14 days");
	unset($sql);
 	$datetime = $date->format('Y-m-d H:i:s');

	$sql = "SELECT * FROM AUTO_LOGIN WHERE TOKEN = '".$token."' AND REGISTRATED_TIME >= '".$datetime."';";

	$result = $mysqli->query( $sql );

	$row_cnt = $result->num_rows;

	if ($row_cnt == 1) {
    	while ($row = $result->fetch_assoc()) {
     		$_SESSION['USER_ID']  = $row['USER_ID'];
    	}
  	} else {
	   	//自動ログイン失敗
   		//Cookie のトークンを削除
   		setCookie("token", '', -1, "/", null, 0, 0);
 
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
    $mysqli = new mysqli(sv, user, pass, dbname);

    //プレースホルダで SQL 作成
    $sql = "INSERT INTO AUTO_LOGIN ( USER_ID, TOKEN, REGISTRATED_TIME) VALUES ('".$id."','".$token."','".date('Y-m-d H:i:s')."');";
  
    //パラメーターの型を指定
    $stmt = $mysqli->prepare($sql);
  
    //パラメーターを渡して SQL 実行
	$stmt->execute();
 	
 	return 0;
 
 }
 
//---------------------------------------------------------------------------//
//トークンの削除
//---------------------------------------------------------------------------//
function delete_old_token($token) {
    //DB接続
    $mysqli = new mysqli(sv, user, pass, dbname);
 
    //プレースホルダで SQL 作成
    $sql = "DELETE  FROM AUTO_LOGIN WHERE TOKEN = '".$token."'";
  
    //パラメーターの型を指定
    $stmt = $mysqli->prepare($sql);
  
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

?>
