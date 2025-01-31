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
$sql = "select 
	ROW_NUMBER() OVER(partition by T.id,T.ymd,T.shu order by T.ymd,T.jun) as No,T.* 
	from (
		select 0 as SEQ,id,shu,0 as jun,sum(weight*rep*sets) as weight,0 as rep,0 as tani,0 as rep2,0 as sets,0 as cal,ymd,'' as memo,typ,0 as insdatetime from tr_log where id = ? and shu = ? group by ymd,shu 
		UNION ALL 
		select * from  tr_log where id = ? and shu = ?) as T 
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
  $weight = " - total：".number_format($row["weight"],0);
	$dataset[$i] = array_merge($row,array('head_wt'=> $weight));
	$i++;
}

$kintore_log = $dataset;
$dataset_work=[];

//ぐらふでーた取得
if($_POST["gtype"]==="year"){//直近1年
	$timestamp = strtotime('-23 months first day of this month');
	// タイムスタンプを日付形式に変換
	$date = date('Y-m-d', $timestamp);
}else if($_POST["gtype"]==="all"){//全期間
	$date = '2017-05-01';
}else{
	exit();
}

$sql = "select ymd,DATEDIFF(now(),ymd) as beforedate,ROW_NUMBER() OVER(order by ymd) as No,sum(weight*rep*sets) as weight ";
$sql .= "from tr_log where id = ? and shu = ? group by ymd,shu,id ";
$sql .= "order by ymd";

$sql = "WITH RECURSIVE cal AS (
	SELECT
		A.min_ymd AS date
	from
		(
			select min(ymd) as min_ymd from tr_log
			where
				id = :id1
				and shu = :shumoku1
				and ymd >= '$date'
			group by id, shu
		) as A
	UNION ALL
	SELECT DATE_ADD(cal.date, INTERVAL 1 Month) FROM cal WHERE cal.date <= CURDATE()
)
select
	left(cal.date, 7) as ym
	,DATEDIFF(now(),cal.date) as beforedate
	,:shumoku2 as shu
	,IFNULL(TEMP.max_volume,0) as max_volume
	,IFNULL(TEMP.total_volume,0) as total_volume
from
	cal
	left join (
		SELECT shu, left(ymd, 7) as ym, max(total_weight) as max_volume, sum(total_weight) as total_volume FROM 
		(SELECT shu, left(ymd, 7) as ym, ymd, sum(weight*rep*sets) as total_weight FROM `tr_log` where id = :id2 and shu = :shumoku3 group by shu, ymd) as tmp
 		group by shu, left(ymd, 7)
 	) as TEMP ON left(cal.date, 7) = TEMP.ym
	ORDER BY left(cal.date,7)";





$graph_title = "『".$shu."のﾄﾚｰﾆﾝｸﾞ量推移』";
//$btn_name = "MAX記録グラフへ";
//$typ=0;

$result = $pdo_h->prepare( $sql );
$result->bindValue('id1', $id, PDO::PARAM_STR);
$result->bindValue('id2', $id, PDO::PARAM_STR);
$result->bindValue('shumoku1', $shu, PDO::PARAM_STR);
$result->bindValue('shumoku2', $shu, PDO::PARAM_STR);
$result->bindValue('shumoku3', $shu, PDO::PARAM_STR);
$result->execute();
$dataset_work = $result->fetchAll(PDO::FETCH_ASSOC);
$dataset = [];
$i=1;
$graph_data_max=[];
$graph_data_max2=[];
$graph_data_total=[];
$graph_data_total2=[];

foreach($dataset_work as $row){
	$weight = ($row["total_volume"]);
	if($row["beforedate"]<0){
		continue;
	}

	if($_POST["gtype"]==="year"){//直近1年
		if($row["beforedate"]<=365){

			$labels[] = substr($row["ym"],-2);
			$graph_data_max[] = $row["max_volume"];
			$graph_data_total[] = $row["total_volume"];
		}else if($row["beforedate"]<=730){

			$graph_data_max2[] = $row["max_volume"];
			$graph_data_total2[] = $row["total_volume"];
		}
	}else if($_POST["gtype"]==="all"){//全期間
		
		$labels[] = $row["ym"];
		$graph_data_max[] = $row["max_volume"];
		$graph_data_total[] = $row["total_volume"];
}else{
		exit();
	}
	
	$i++;
}


//ラベル設定
if($_POST["gtype"]==="year"){//直近1年
	$glabel1="今";
	$glabel2="前";
	$subtitle = "各月の総Volume(棒グ)とDayﾄﾚのMaxVolume(線グ)";
	$graph_title .= "（前年比較）";
}else if($_POST["gtype"]==="all"){//全期間
	$glabel1="";
	$glabel2="";
	$subtitle = "全期間対象の月間総Volume推移";
	$graph_title .= "（全期間）";
}

$return_sts = array(
	"MSG" => $msg
	,"status" => $alert_status
	,"kintore_log" => $kintore_log
	,"graph_data_max1" => $graph_data_max
	,"graph_data_total1" => $graph_data_total
	,"graph_data_max2" => $graph_data_max2
	,"graph_data_total2" => $graph_data_total2
	,"labels" => $labels
	,"glabel1" => $glabel1
	,"glabel2" => $glabel2
	,"graph_title" => $graph_title
	,"subtitle" => $subtitle
);
header('Content-type: application/json');
echo json_encode($return_sts, JSON_UNESCAPED_UNICODE);

exit();

?>
