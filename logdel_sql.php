<?php
require_once "config.php";
//require_once "database.php";
//$db = new Database();
//トランザクション処理

//結果書き込み
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


try{
	$db->begin_tran();
	$sql = "DELETE from tr_log where id = :id and ymd = :ymd and jun = :jun";
	$db->UP_DEL_EXEC($sql,[":id" => $id,":ymd" => $_POST["k_ymd"],":jun" => $_POST["k_jun"]]);
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