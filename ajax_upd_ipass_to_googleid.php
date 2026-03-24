<?php
  require_once "config.php";
  //IDの存在チェック
  log_writer2("\$POST",$_POST,"lv3");
  
	$msg="不正なアクセスです";
	$status="false";
	$google_id = $_POST["ID"] ?? -1;
	$old_id = $_SESSION["USER_ID"] ?? -1;
	$token = NULL;
	if($_POST["token2"] === $_SESSION["token"] && $google_id !== -1 && $old_id !== -1){
		try{
			$db->begin_tran();
			if($_POST["shori"] === "delin"){
				//既存の$google_idに紐づくデータを退避
				$db->UP_DEL_EXEC("UPDATE users set `id` = :newid where `id` = :id",["newid" => $google_id."_bk_".date('YmdHis'),":id" => $google_id]);
				$db->UP_DEL_EXEC("UPDATE tr_log set `id` = :newid where `id` = :id",["newid" => $google_id."_bk_".date('YmdHis'),":id" => $google_id]);
				$db->UP_DEL_EXEC("UPDATE tr_condition set `id` = :newid where `id` = :id",["newid" => $google_id."_bk_".date('YmdHis'),":id" => $google_id]);
				$db->UP_DEL_EXEC("UPDATE taisosiki set `id` = :newid where `id` = :id",["newid" => $google_id."_bk_".date('YmdHis'),":id" => $google_id]);
				$db->UP_DEL_EXEC("UPDATE ms_training set `id` = :newid where `id` = :id",["newid" => $google_id."_bk_".date('YmdHis'),":id" => $google_id]);
				$db->UP_DEL_EXEC("UPDATE AUTO_LOGIN set `id` = :newid where `id` = :id",["newid" => $google_id."_bk_".date('YmdHis'),":id" => $google_id]);
			}

			if($_POST["shori"] === "delin" || $_POST["shori"] === "new"){
				$db->UP_DEL_EXEC("UPDATE users set `id` = :newid,`pass` = :pass, `user_type` = 'google' where `id` = :id",["newid" => $google_id,"pass" => $google_id,":id" => $old_id]);
				$db->UP_DEL_EXEC("UPDATE tr_log set `id` = :newid where `id` = :id",["newid" => $google_id,":id" => $old_id]);
				$db->UP_DEL_EXEC("UPDATE tr_condition set `id` = :newid where `id` = :id",["newid" => $google_id,":id" => $old_id]);
				$db->UP_DEL_EXEC("UPDATE taisosiki set `id` = :newid where `id` = :id",["newid" => $google_id,":id" => $old_id]);
				$db->UP_DEL_EXEC("UPDATE ms_training set `id` = :newid where `id` = :id",["newid" => $google_id,":id" => $old_id]);
				$db->UP_DEL_EXEC("UPDATE AUTO_LOGIN set `id` = :newid where `id` = :id",["newid" => $google_id,":id" => $old_id]);
			}

			$db->commit_tran();
			$status="success";
			$msg = "成功";
		}catch(Exception $e){
			$db->rollback_tran($e->getMessage());
			$msg=$e->getMessage();
			$status="error";
		}
	
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