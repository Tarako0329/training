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
	$sql = "select log.*,con.condition,replace(log.ymd,'-','') as ymd2,log.ymd as ymd3
	,SUM(weight*rep*sets) OVER (PARTITION BY log.id,shu,log.ymd,log.typ) as total
	,RANK() OVER(PARTITION BY log.id,log.ymd,shu,log.typ order by jun ) as setjun 
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
	$sql = "SELECT 
						id,shu
					    ,MAX(IF(sort_3M = 1,near_3M_max,0)) as M3_max,MAX(IF(sort_3M = 1 and near_3M_max > 0,concat(weight,' x ',rep,'(',rep2,')'),'-')) as M3_set,MAX(IF(sort_3M = 1 and near_3M_max > 0,near_3M,'-')) as M3_date
					    ,MAX(IF(sort_1Y = 1,near_1Y_max,0)) as Y1_max,MAX(IF(sort_1Y = 1 and near_1Y_max > 0,concat(weight,' x ',rep,'(',rep2,')'),'-')) as Y1_set,MAX(IF(sort_1Y = 1 and near_1Y_max > 0,near_1Y,'-')) as Y1_date
					    ,MAX(IF(sort_MB = 1,max_weight,0)) as mybest, MAX(IF(sort_MB = 1,concat(weight,' x ',rep,'(',rep2,')'),'-')) as MB_set,                    MAX(IF(sort_MB = 1,ymd_MB,'-')) as MB_date
					FROM (
					SELECT SEQ,id,shu,max_weight,weight,rep,rep2
					,ymd
					,IF(DATE_SUB(CURDATE(),INTERVAL 3 MONTH) <= ymd , DATE_FORMAT(ymd,'%m/%d') , 0) as near_3M
					,IF(DATE_SUB(CURDATE(),INTERVAL 3 MONTH) <= ymd , max_weight , 0) as near_3M_max
					,row_number() over (
					  partition by `id`,`shu`
					  order by
					    IF(DATE_SUB(CURDATE(),INTERVAL 3 MONTH) <= ymd , max_weight , 0) desc,
					    IF(DATE_SUB(CURDATE(),INTERVAL 3 MONTH) <= ymd , ymd , 0) desc
					) AS `sort_3M`
					
					,IF(DATE_SUB(CURDATE(),INTERVAL 12 MONTH) <= ymd , DATE_FORMAT(ymd,'%m月') , 0) as near_1Y
					,IF(DATE_SUB(CURDATE(),INTERVAL 12 MONTH) <= ymd , max_weight , 0) as near_1Y_max
					,row_number() over (
					  partition by `id`,`shu`
					  order by
					    IF(DATE_SUB(CURDATE(),INTERVAL 12 MONTH) <= ymd , max_weight , 0) desc,
					    IF(DATE_SUB(CURDATE(),INTERVAL 12 MONTH) <= ymd , ymd , 0) desc
					) AS `sort_1Y`
					
					,DATE_FORMAT(ymd,'%y-%m') as ymd_MB
					,row_number() over (
					  partition by `id`,`shu`
					  order by
					    max_weight desc,
					    ymd desc
					) AS `sort_MB`
					FROM `tr_log_max_record` 
					where id = :id
					) tmp
					WHERE sort_MB = 1 or sort_1Y = 1 or sort_3M = 1
					GROUP BY id,shu;";
	$result = $pdo_h->prepare( $sql );
	$result->bindValue("id", $id, PDO::PARAM_STR);
	$result->execute();
	$max_list = $result->fetchAll(PDO::FETCH_ASSOC);
	$result = null;
	$dataset = null;


$return_sts = array(
	"MSG" => $msg
	,"status" => $alert_status
	,"kintore_log" => $kintore_log
	,"shumoku_list" => $shumoku_list
	,"max_log" => $max_list
);
header('Content-type: application/json');
echo json_encode($return_sts, JSON_UNESCAPED_UNICODE);

exit();

?>
