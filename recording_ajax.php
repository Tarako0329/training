<?php
  require_once "config.php";
  require_once "database.php";
  //GoogleAuth新規ユーザ登録用
  log_writer2("\$POST",$_POST,"lv3");
  
	$msg="不正なアクセスです";
	$status="false";
	if($_POST["token"] === $_SESSION["token"]){
		$id = $_POST["ID"];
		/*
		$sql = "select * from users where ((id)=?)";
		$stmt = $pdo_h->prepare( $sql );
		$stmt->bindValue(1, $id, PDO::PARAM_STR);
		$stmt->execute();
		$row_cnt = $stmt->rowCount();
		*/
		$db = new Database();
		$row = $db->SELECT("select * from users where id = :id",[":id" => $_POST["ID"]]);
		$row_cnt = count($row);
		if($row_cnt===1){
			$msg = "登録済ユーザ";
			//$_SESSION['USER_ID'] = $id;
		}else{
			$msg = "新規ユーザ";
			$pass = passEx($id,$id);
			log_writer2("\$pass",$pass,"lv3");
			/*
			$sql = "insert into users(id,pass,name,user_type) values (?,?,?,'google')";
			$stmt = $pdo_h->prepare($sql);
			$stmt->bindValue(1, $id, PDO::PARAM_STR);
			$stmt->bindValue(2, $pass, PDO::PARAM_STR);
			$stmt->bindValue(3, $_POST['name'], PDO::PARAM_STR);
			$stmt->execute();
			*/
			$db->begin_tran();
			$db->INSERT("users",["id" => $id,"pass" => $pass,"name" => $_POST['name'],"user_type" => "google"]);
			$db->commit_tran();
		}
		$_SESSION['USER_ID'] = $id;
		$_SESSION['login_type'] = "google";
		$msg .= "【処理完了】";
		$status="success";
  }

	$return_sts = array(
		"MSG" => $msg
		,"status" => $status
	);
	header('Content-type: application/json');
	echo json_encode($return_sts, JSON_UNESCAPED_UNICODE);
  exit();
?>