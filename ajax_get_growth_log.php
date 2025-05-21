<?php
require "config.php";
log_writer2("\$_POST",$_POST,"lv3");
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

$shu = ($_POST["shu"]);
$msg = "";
$alert_status = "";


//max値を検索
$sql = "SELECT id,shu,max(max_weight) as max_w FROM `tr_log_max_record` where id=? and shu=? group by id,shu";
$result = $pdo_h->prepare( $sql );
$result->bindValue(1, $id, PDO::PARAM_STR);
$result->bindValue(2, $shu, PDO::PARAM_STR);
$result->execute();
$tmp = $result->fetchAll(PDO::FETCH_ASSOC);

//max値を記録した最初の日を検索
$sql = "SELECT id,shu,min(ymd) as first_day FROM `tr_log_max_record` where id=? and shu=? and max_weight=? group by id,shu";
$result = $pdo_h->prepare( $sql );
$result->bindValue(1, $id, PDO::PARAM_STR);
$result->bindValue(2, $shu, PDO::PARAM_STR);
$result->bindValue(3, $tmp[0]["max_w"], PDO::PARAM_INT);
$result->execute();
$tmp = $result->fetchAll(PDO::FETCH_ASSOC);

//履歴取得
$sql = "select 
		ROW_NUMBER() OVER(partition by T.id,T.ymd,T.shu order by T.ymd,T.jun) as No,T.* 
	from (
		select 0 as SEQ,L.id,L.shu,0 as jun,sum(L.weight*L.rep*L.sets) as weight,0 as rep,0 as tani,0 as rep2,0 as sets,0 as cal,L.ymd,'' as memo,L.typ,0 as insdatetime, MAX(M.max_weight) as max_weight
		from tr_log as L
		inner join tr_log_max_record as M
		ON L.id = M.id
		AND L.ymd = M.ymd
		AND L.shu = M.shu
		where L.id = ? and L.shu = ? 
		group by ymd,shu 
		UNION ALL
		select * ,0 as max_weight from  tr_log where id = ? and shu = ?
	) as T 
	having T.ymd between DATE_SUB(?,INTERVAL 4 MONTH) and ? 
	order by T.ymd desc,T.jun ";

$result = $pdo_h->prepare( $sql );
$result->bindValue(1, $id, PDO::PARAM_STR);
$result->bindValue(2, $shu, PDO::PARAM_STR);
$result->bindValue(3, $id, PDO::PARAM_STR);
$result->bindValue(4, $shu, PDO::PARAM_STR);
$result->bindValue(5, $tmp[0]["first_day"], PDO::PARAM_STR);
$result->bindValue(6, $tmp[0]["first_day"], PDO::PARAM_STR);
$result->execute();
$dataset_work = $result->fetchAll(PDO::FETCH_ASSOC);
//log_writer2("\$dataset_work",$dataset_work,"lv3");
$dataset = [];
$i=0;
foreach($dataset_work as $row){
  $weight = " - total：".number_format($row["weight"],0)."  MAX：".number_format($row["max_weight"],0);
	$dataset[$i] = array_merge($row,array('head_wt'=> $weight));
	$i++;
}
//$kintore_log = json_encode($dataset, JSON_UNESCAPED_UNICODE);
$kintore_log = $dataset;
$dataset_work=[];

//ぐらふでーた取得(Volue)
$sql = "select ymd,DATEDIFF(?,ymd) as beforedate,ROW_NUMBER() OVER(order by ymd) as No,sum(weight*rep*sets) as weight ";
$sql .= "from tr_log where id = ? and shu = ? and ymd between DATE_SUB(?,INTERVAL 4 MONTH) and ? group by ymd,shu,id ";
$sql .= "order by ymd";
$graph_title = "MAX更新前のVolume推移";
$subtitle="最初のMAX更新日4ヵ月前から";

$result = $pdo_h->prepare( $sql );
$result->bindValue(1, $tmp[0]["first_day"], PDO::PARAM_STR);
$result->bindValue(2, $id, PDO::PARAM_STR);
$result->bindValue(3, $shu, PDO::PARAM_STR);
$result->bindValue(4, $tmp[0]["first_day"], PDO::PARAM_STR);
$result->bindValue(5, $tmp[0]["first_day"], PDO::PARAM_STR);
$result->execute();
$dataset_work = $result->fetchAll(PDO::FETCH_ASSOC);
$dataset = [];
$i=0;

$graph_data=[];
//$graph_data2=[];
foreach($dataset_work as $row){
	$weight = ($row["weight"]);

	if($i===0){
		$labels[] = $row["beforedate"]."日前";
	}else{
		$labels[] = $row["beforedate"];
	}
	$graph_data[] = $weight;
	$i++;
}
$labels[$i-1] = "更新日";

//ぐらふでーた取得(Max)
$sql = "select max_weight as weight ";
$sql .= "from tr_log_max_record where id = ? and shu = ? and ymd between DATE_SUB(?,INTERVAL 4 MONTH) and ? ";
$sql .= "order by ymd";

$result = $pdo_h->prepare( $sql );
$result->bindValue(1, $id, PDO::PARAM_STR);
$result->bindValue(2, $shu, PDO::PARAM_STR);
$result->bindValue(3, $tmp[0]["first_day"], PDO::PARAM_STR);
$result->bindValue(4, $tmp[0]["first_day"], PDO::PARAM_STR);
$result->execute();
$dataset_work = $result->fetchAll(PDO::FETCH_ASSOC);

foreach($dataset_work as $row){
	$graph_data2[] = $row["weight"];
}

$return_sts = array(
	"MSG" => $msg
	,"status" => $alert_status
	,"kintore_log" => $kintore_log
	,"graph_data1" => $graph_data
	,"graph_data2" => $graph_data2
	,"labels" => $labels
	//,"kikan" => $kikan
	,"glabel1" => "総Volume"
	,"glabel2" => "Max"
	,"graph_title" => $graph_title
	,"subtitle" => $subtitle
);
header('Content-type: application/json');
echo json_encode($return_sts, JSON_UNESCAPED_UNICODE);

exit();

?>
