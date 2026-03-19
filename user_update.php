<?php
  require_once "config.php";
	use classes\Security\Security;

  //トランザクション処理
  //log_writer2("\$POST",$_POST,"lv3");
  
	$msg="不正なアクセスです";
	$status="false";
	if($_POST["token"] === $_SESSION["token"]){
    //新規ユーザ登録画面の「登録」ボタン
		$id = $_POST["id"];
		$Security = new Security($id,key);

		$sql = "SELECT * from users where `id`=:id";
		$row = $db->SELECT($sql,[":id"=>$id]);
		if(count($row)<>1){
			//未登録ユーザ
		}else{
			try{
				$db->begin_tran();
				$sql = "UPDATE users set `name`=:name,sex=:sex,height=:height,birthday=:birthday where id=:id";
				$db->UP_DEL_EXEC($sql,[":name"=>$_POST['fname'],":sex"=>$_POST['sex'],":height"=>$_POST['height'],":birthday"=>$_POST['birthday'],":id"=>$_POST['id']]);
				if(!empty($_POST["pass"])){
					$pass = $Security->passEx($_POST['pass']);
					$sql = "UPDATE users set pass=:pass where id=:id";
					$db->UP_DEL_EXEC($sql,[":pass"=>$pass,":id"=>$id]);
				}
				$db->commit_tran();
			}catch(Exception $e){
				$msg = "catch Exception \$e：".$e;
				$db->rollback_tran($msg);
			}
		}

		$msg="処理完了";
		$status="success";
		header("HTTP/1.1 301 Moved Permanently");
    header("Location: TOP.php");
    exit();

  }
	header("HTTP/1.1 301 Moved Permanently");
	header("Location: index.php?logoff=out");
	exit();
?>