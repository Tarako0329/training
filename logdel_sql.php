<?php
require_once "config.php";
//トランザクション処理
use classes\SpreadSheet\SpreadSheet;
use classes\Security\Security;
define("GOOGLE_AUTH",$_ENV["GOOGLE_AUTH"]);
define("GOOGLE_AUTH_SKEY",$_ENV["GOOGLE_AUTH_SKEY"]);

//結果書き込み
if(isset($_SESSION['USER_ID'])){
	$id = $_SESSION['USER_ID'];
}else if (check_auto_login($_COOKIE['token'])==0) {
	$id = $_SESSION['USER_ID'];
}else{
	header("HTTP/1.1 301 Moved Permanently");
	header("Location: index.php");
	exit();
}

$sheetname = $_POST["typ"] == "1" ? "有酸素運動" : "ウェイトトレーニング";

try{
	$db->begin_tran();
	//リフレッシュトークンの取得
	$row = $db->SELECT("SELECT * FROM users WHERE id = :id",["id"=>$_SESSION['USER_ID']]);
	$SQ = new Security($_SESSION['USER_ID'],key);
	$refreshToken = $SQ->decrypt($row[0]['google_refresh_token']);
	$db_spsfilename = $row[0]['spsfilename'] ?? "";

	$spread_flg = U::exist($refreshToken) && U::exist($db_spsfilename);

	$sql = "DELETE from tr_log where id = :id and SEQ = :SEQ";
	$db->UP_DEL_EXEC($sql,[":id" => $id,":SEQ" => $_POST["SEQ"]]);

	if($spread_flg){
		$SpreadSheet = new SpreadSheet($refreshToken, $db_spsfilename);
		$SpreadSheet->G_DELETE($_POST["SEQ"],$sheetname);
	}

	$db->commit_tran();

}catch(Exception $e){
	$msg = "catch Exception \$e：".$e;
	$db->rollback_tran($msg);

}
//ログイン失敗
//リダイレクト
header("HTTP/1.1 301 Moved Permanently");
header("Location: index.php");
exit();
?>