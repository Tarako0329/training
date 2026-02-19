<?php
  require_once "config.php";
 	require_once "database.php";
	$db = new Database();

  //トランザクション処理
  //log_writer2("\$POST",$_POST,"lv3");
  
	$msg="不正なアクセスです";
	$status="false";
	if($_POST["token"] === $_SESSION["token"]){
    //新規ユーザ登録画面の「登録」ボタン
		$id = $_POST["id"];
		$sql = "SELECT * from users where id=?";
		$stmt = $pdo_h->prepare( $sql );
		$stmt->bindValue(1, $id, PDO::PARAM_STR);
		$stmt->execute();
		$row_cnt = $stmt->rowCount();
		if($row_cnt<>1){
			//未登録ユーザ
		}else{
			try{
				$db->begin_tran();
				$sql = "UPDATE users set `name`=:name,sex=:sex,height=:height,birthday=:birthday where id=:id";
				$db->UP_DEL_EXEC($sql,[":name"=>$_POST['fname'],":sex"=>$_POST['sex'],":height"=>$_POST['height'],":birthday"=>$_POST['birthday'],":id"=>$_POST['id']]);
				/*
				$stmt = $pdo_h->prepare($sql);
				$stmt->bindValue("name", $_POST['fname'], PDO::PARAM_STR);
				$stmt->bindValue("sex", $_POST['sex'], PDO::PARAM_STR);
				$stmt->bindValue("height", $_POST['height'], PDO::PARAM_STR);
				$stmt->bindValue("birthday", $_POST['birthday'], PDO::PARAM_STR);
				$stmt->bindValue("id", $_POST['id'], PDO::PARAM_STR);
				$stmt->execute();
				*/
				if(!empty($_POST["pass"])){
					$pass = passEx($_POST['pass'],$id);
					$sql = "UPDATE users set pass=:pass where id=:id";
					$db->UP_DEL_EXEC($sql,[":pass"=>$pass,":id"=>$id]);
					/*
					$stmt = $pdo_h->prepare($sql);
					$stmt->bindValue("pass",$pass , PDO::PARAM_STR);
					$stmt->bindValue("id", $id, PDO::PARAM_STR);
					$stmt->execute();
					*/
				}
				$db->commit_tran();
			}catch(Exception $e){
				$db->rollback_tran();
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
exit();
?>