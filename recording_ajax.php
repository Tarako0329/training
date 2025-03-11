<?php
  require "config.php";
  //require "functions.php";
  //トランザクション処理
  //log_writer2("\$POST",$_POST,"lv3");
  
	$msg="不正なアクセスです";
	$status="false";
	if($_POST["token"] === $_SESSION["token"]){
    //新規ユーザ登録画面の「登録」ボタン
		$id = ($_POST["ID"]);
		$sql = "select * from users where ((id)=?)";
		$stmt = $pdo_h->prepare( $sql );
		$stmt->bindValue(1, $id, PDO::PARAM_STR);
		$stmt->execute();
		$row_cnt = $stmt->rowCount();
		if($row_cnt==1){
			//登録済みユーザ
			//$_SESSION['USER_ID'] = $id;
		}else{
			$pass = passEx($id,$id);
			$sql = "insert into users(id,pass,name) values (?,?,?)";
			$stmt = $pdo_h->prepare($sql);
			$stmt->bindValue(1, $id, PDO::PARAM_STR);
			$stmt->bindValue(2, $pass, PDO::PARAM_STR);
			$stmt->bindValue(3, $_POST['name'], PDO::PARAM_STR);
			$stmt->execute();
	
		}
		$_SESSION['USER_ID'] = $id;
		$_SESSION['login_type'] = "google";
		$msg="処理完了";
		$status="success";
  }
   
  //不正アクセス
  //リダイレクト
	$return_sts = array(
		"MSG" => $msg
		,"status" => $status
	);
	header('Content-type: application/json');
	echo json_encode($return_sts, JSON_UNESCAPED_UNICODE);
  exit();
?>