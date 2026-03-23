<?php
	require_once "config.php";
	//require_once "database.php";
	//$db = new Database();

	//トランザクション処理
	if(isset($_SESSION['USER_ID'])){
		$id = $_SESSION['USER_ID'];
	}else if (check_auto_login($_COOKIE['token'])==0) {
		$id = $_SESSION['USER_ID'];
	}else{
		header("HTTP/1.1 301 Moved Permanently");
		header("Location: index.php");
		exit();
	}
	
	if($_POST["btn"] == "w_ins_bt"){
		//体組織記録画面の「記録」ボタン
		try{
			//デリイン（１日１件）
			$db->begin_tran();
			$sql = "DELETE from taisosiki where id = :id and ymd = :ymd;";
			$db->UP_DEL_EXEC($sql,[":id"=>$id,":ymd"=>$_POST["ymd"]]);
			$db->INSERT("taisosiki",[
				"id" => $id,
				"ymd" => $_POST["ymd"],
				"weight" => $_POST["weight"],
				"taisibou" => $_POST["sibo"],
				"memo" => $_POST["memo"]
			]);
			
			$db->commit_tran();
		}catch(Exception $e){
			$msg = "catch Exception \$e：".$e;
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