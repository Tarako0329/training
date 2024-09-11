<?php
require "config.php";
log_writer2("\$_POST",$_POST,"lv3");
$shu = ($_POST["shu"]);
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

	//履歴取得
	$sql = "select log.*,con.condition,replace(log.ymd,'-','') as ymd2,log.ymd as ymd3,SUM(weight*rep*sets) OVER (PARTITION BY log.id,shu,log.ymd) as total,RANK() OVER(PARTITION BY log.id,log.ymd,shu order by jun ) as setjun 
	from tr_log as log left join tr_condition as con on log.id=con.id and log.ymd=con.ymd where log.id = ? and log.ymd >= ? order by log.ymd desc,jun LIMIT ?";
	$result = $pdo_h->prepare( $sql );
	$result->bindValue(1, $id, PDO::PARAM_STR);
	$result->bindValue(2, date("Y-m-d",strtotime("-13 month")), PDO::PARAM_STR);
	$result->bindValue(3, 500, PDO::PARAM_INT);
	$result->execute();
	$kintore_log = $result->fetchAll(PDO::FETCH_ASSOC);
	$result = null;
	$dataset = null;

	//種目の取得
	//全種目
	$sql = "select typ,shu,max(insdatetime) as sort from tr_log where id in (?,'list') group by shu ,typ order by sort desc, typ";
	$result = $pdo_h->prepare( $sql );
	$result->bindValue(1, $id, PDO::PARAM_STR);
	$result->execute();
	$shumoku_list = $result->fetchAll(PDO::FETCH_ASSOC);
	$result = null;
	$dataset = null;

	//マックス
	$sql = "SELECT shu
		,max(IF(DATE_SUB(CURDATE(),INTERVAL 1 MONTH) <= ymd , max_weight , 0)) as near_1M_max
		,max(IF(DATE_SUB(CURDATE(),INTERVAL 3 MONTH) <= ymd , max_weight , 0)) as near_3M_max
		,max(max_weight) as full_max 
		FROM `tr_log_max_record` where id = ? group by shu order by max(ymd) desc";
	$result = $pdo_h->prepare( $sql );
	$result->bindValue(1, $id, PDO::PARAM_STR);
	$result->execute();
	$max_list = $result->fetchAll(PDO::FETCH_ASSOC);
	$result = null;
	$dataset = null;


$return_sts = array(
	"MSG" => $msg
	,"status" => $alert_status
	,"kintore_log" => $kintore_log
	,"shumoku_list" => $shumoku_list
	,"max_list" => $max_list
);
header('Content-type: application/json');
echo json_encode($return_sts, JSON_UNESCAPED_UNICODE);

exit();

?>
