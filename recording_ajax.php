<?php
	//GoogleAuthで登録した場合
  require_once "config.php";
  //GoogleAuth新規ユーザ登録用
  log_writer2("\$POST",$_POST,"lv3");
  
	$msg="不正なアクセスです";
	$status="false";
	if($_POST["token"] === $_SESSION["token"]){
		$id = $_POST["ID"] ?? -1;

		$row = $db->SELECT("SELECT * from users where id = :id",[":id" => $_POST["ID"]]);
		$row_cnt = count($row);
		if($row_cnt===1){
			$msg = "登録済ユーザ";
		}else{
			$msg = "新規ユーザ";
			//$pass = passEx($id,$id);
			$pass = $id;	//googleログインはパスワードにGoogle識別子IDをセットする
			log_writer2("\$pass",$pass,"lv3");
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