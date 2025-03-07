<?php
	require "config.php";
	//log_writer2("\$_POST",$_POST,"lv3");
	//$shu = ($_POST["shu"]);
	if(isset($_SESSION['USER_ID'])){ //ユーザーチェックブロック
		$id = $_SESSION['USER_ID'];
	}else if (check_auto_login($_COOKIE['token'])==0) {
		$id = $_SESSION['USER_ID'];
	}else{
		$return_sts = array(
			"MSG" => "失敗"
		);
		header('Content-type: application/json');
		echo json_encode($return_sts, JSON_UNESCAPED_UNICODE);
		exit();
	}	

	$msg = "";
	$alert_status = "";
	$rm_weight = "";

	$shu = $_GET["shu"];
	$rep = $_GET["rep"];
	//マックス
	//$sql = "SELECT 'a',MAX(IFNULL(max_weight,0)) as MAX_W FROM `tr_log_max_record` where id=:id and shu=:shu and ymd >= DATE_SUB(CURDATE(),INTERVAL 3 MONTH) group by id,shu";
	$sql = "SELECT IFNULL(MAX(max_weight),0) as MAX_W FROM `tr_log_max_record` where id=:id and shu=:shu and ymd >= DATE_SUB(CURDATE(),INTERVAL 3 MONTH)" ;
	$result = $pdo_h->prepare( $sql );
	$result->bindValue("id", $id, PDO::PARAM_STR);
	$result->bindValue("shu", $shu, PDO::PARAM_STR);
	$result->execute();
	$max_list = $result->fetchAll(PDO::FETCH_ASSOC);

	if($max_list[0]["MAX_W"]>0){
		if($rep == 0){

		}else if($rep <= 10){
			$sql = "SELECT * FROM `max_table` where rep=:rep";
			$result = $pdo_h->prepare( $sql );
			$result->bindValue("rep", $rep, PDO::PARAM_INT);
			$result->execute();
			$hiritu = $result->fetchAll(PDO::FETCH_ASSOC);
			$rm_weight = $max_list[0]["MAX_W"] * $hiritu[0]["hiritsu"];

			$rm_weight = $rm_weight - fmod($rm_weight , 2.5);
		}else{
			$msg = "10以下を指定してください";
		}
	
	}else{
		$msg = "直近３か月のマックス記録がありません";
	}



	$return_sts = array(
		"MSG" => $msg
		,"status" => $alert_status
		,"rm_weight" => $rm_weight
	);
	header('Content-type: application/json');
	echo json_encode($return_sts, JSON_UNESCAPED_UNICODE);

	exit();

?>
