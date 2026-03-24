<?php
  require_once "config.php";
  //IDの存在チェック
  log_writer2("\$POST",$_POST,"lv3");
  
	$msg="不正なアクセスです";
	$status="false";
	$id = $_POST["ID"] ?? -1;
	$token = NULL;
	if($_POST["token"] === $_SESSION["token"] && $id !== -1){

		$row = $db->SELECT("SELECT * from users where id = :id",[":id" => $_POST["ID"]]);
		$row_cnt = count($row);
		if($row_cnt===1){
			$msg = "登録済";
		}else{
			$msg = "新規";
		}
		$status="success";
		$token=get_token();
  }

	$return_sts = array(
		"MSG" => $msg
		,"status" => $status
		,"token" => $token
	);
	header('Content-type: application/json');
	echo json_encode($return_sts, JSON_UNESCAPED_UNICODE);
  exit();
?>