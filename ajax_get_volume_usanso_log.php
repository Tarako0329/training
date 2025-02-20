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

$gtype = $_POST["gtype"];
$msg = "";
$alert_status = "";


//履歴取得
$sql = "SELECT 
	ROW_NUMBER() OVER(partition by T.id,T.ymd,T.shu order by T.ymd,T.jun) as No,T.* 
	FROM (
		SELECT 0 as SEQ,id,shu,0 as jun,0 as weight,sum(rep) as rep,0 as tani,sum(rep2) as rep2,0 as sets,sum(cal) as cal,ymd,'' as memo,typ,0 as insdatetime FROM tr_log where id = ? and shu = ? group by ymd,shu 
		UNION ALL 
		SELECT * FROM  tr_log where id = ? and shu = ?) as T 
	order by T.ymd desc,T.jun ";

$result = $pdo_h->prepare( $sql );
$result->bindValue(1, $id, PDO::PARAM_STR);
$result->bindValue(2, $shu, PDO::PARAM_STR);
$result->bindValue(3, $id, PDO::PARAM_STR);
$result->bindValue(4, $shu, PDO::PARAM_STR);
$result->execute();
$dataset_work = $result->fetchAll(PDO::FETCH_ASSOC);
//log_writer2("\$dataset_work",$dataset_work,"lv3");
$dataset = [];
$i=0;
foreach($dataset_work as $row){
  $weight = " - total：".number_format($row["rep2"]/1000,2)."km ".number_format($row["cal"],0)."Kcal ".number_format($row["rep"]/60,1)."H";
	$dataset[$i] = array_merge($row,array('head_wt'=> $weight));
	$i++;
}

$kintore_log = $dataset;
$dataset_work=[];

//ぐらふでーた取得
if($gtype==="12M"){//直近1年
	$timestamp = strtotime('-23 months first day of this month');
	// タイムスタンプを日付形式に変換
	$date = date('Y-m-d', $timestamp);
}else if($gtype==="all"){//全期間
	$date = '2017-05-01';
}else{
	exit();
}
//
$sql = "WITH RECURSIVE cal AS (
		SELECT
			DATE_FORMAT(A.min_ymd, '%Y-%m-01') AS date
		FROM
			(
				SELECT min(ymd) as min_ymd FROM tr_log
				where
					id = :id1
					and shu = :shumoku1
					and ymd >= '$date'
				group by id, shu
			) as A
		UNION ALL
		SELECT DATE_ADD(cal.date, INTERVAL 1 Month) AS date FROM cal WHERE cal.date <= DATE_ADD(CURDATE(), INTERVAL 1 DAY)
	)
	SELECT
		left(cal.date, 7) as ym
		,DATEDIFF(now(),cal.date) as beforedate
		,:shumoku2 as shu
		,IFNULL(TEMP.total_kyori,0)/1000 as total_kyori
		,IFNULL(TEMP.total_hour,0)/60 as total_hour
		,IFNULL(TEMP.total_cal,0) as total_cal
	FROM
	cal
	left join (
		SELECT shu, left(ymd, 7) as ym, sum(rep) as total_hour, sum(rep2) as total_kyori , sum(cal) as total_cal 
		FROM tr_log WHERE id=:id2 AND shu=:shumoku3
 		group by shu, left(ymd, 7)
 	) as TEMP ON left(cal.date, 7) = TEMP.ym
	ORDER BY left(cal.date,7)";

/*$sql = "SELECT
	TEMP.ym
	,DATEDIFF(now(),TEMP.date) as beforedate
	,:shumoku2 as shu
	,IFNULL(TEMP.total_kyori,0)/1000 as total_kyori
	,IFNULL(TEMP.total_hour,0)/60 as total_hour
	,IFNULL(TEMP.total_cal,0) as total_cal
FROM
(
	SELECT shu, left(ymd, 7) as ym,min(ymd) as date, sum(rep) as total_hour, sum(rep2) as total_kyori , sum(cal) as total_cal 
	FROM tr_log WHERE id=:id2 AND shu=:shumoku3
	 group by shu, left(ymd, 7)
 ) as TEMP
ORDER BY TEMP.ym";*/

$graph_title = "『".$shu."のﾄﾚｰﾆﾝｸﾞ量推移』";

$dataset_work =[];
$result = $pdo_h->prepare( $sql );
$result->bindValue('id1', $id, PDO::PARAM_STR);
$result->bindValue('id2', $id, PDO::PARAM_STR);
$result->bindValue('shumoku1', $shu, PDO::PARAM_STR);
$result->bindValue('shumoku2', $shu, PDO::PARAM_STR);
$result->bindValue('shumoku3', $shu, PDO::PARAM_STR);
$result->execute();
$dataset_work = $result->fetchAll(PDO::FETCH_ASSOC);

//log_writer2("\$sql",$sql,"lv3");
//log_writer2("\$id",$id,"lv3");
//log_writer2("\$shu",$shu,"lv3");
//log_writer2("\$dataset_work",$dataset_work,"lv3");

$result->closeCursor();
$result = null;
$pdo_h = null;


$dataset = [];
$i=1;
$graph_data_total_km=[];
$graph_data_total_H=[];
$graph_data_total_cal=[];

foreach($dataset_work as $row){
	if($row["beforedate"]<0){
		continue;
	}

	if($gtype==="12M"){//直近1年
		if($row["beforedate"]<=365){
			$labels[] = substr($row["ym"],-2);
			$graph_data_total_km[] = $row["total_kyori"];
			$graph_data_total_H[] = $row["total_hour"];
			$graph_data_total_cal[] = $row["total_cal"];
		}else{
			//break;
		}
	}else if($gtype==="all"){//全期間
		$labels[] = $row["ym"];
		$graph_data_total_km[] = $row["total_kyori"];
		$graph_data_total_H[] = $row["total_hour"];
		$graph_data_total_cal[] = $row["total_cal"];
	}else{
		exit();
	}
	
	$i++;
}


//ラベル設定
$glabel_km="Km";
$glabel_cal="Kcal";
$glabel_H="Hour";
if($gtype==="12M"){//直近1年
	$subtitle = "";
	$graph_title .= "(直近1年)";
}else if($gtype==="all"){//全期間
	$subtitle = "";
	$graph_title .= "（全期間）";
}

$return_sts = array(
	"MSG" => $msg
	,"status" => $alert_status
	,"kintore_log" => $kintore_log
	,"graph_data_total_km" => $graph_data_total_km
	,"graph_data_total_cal" => $graph_data_total_cal
	,"graph_data_total_H" => $graph_data_total_H
	,"labels" => $labels
	,"glabel_km" => $glabel_km
	,"glabel_cal" => $glabel_cal
	,"glabel_H" => $glabel_H
	,"graph_title" => $graph_title
	,"subtitle" => $subtitle
);
header('Content-type: application/json');
echo json_encode($return_sts, JSON_UNESCAPED_UNICODE);

exit();

?>
