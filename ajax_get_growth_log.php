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
$sql = "select ROW_NUMBER() OVER(partition by T.id,T.ymd,T.shu order by T.ymd,T.jun) as No,T.* 
from (select 0 as SEQ,id,shu,0 as jun,sum(weight*rep*sets) as weight,0 as rep,0 as tani,0 as rep2,0 as sets,0 as cal,ymd,'' as memo,typ,0 as insdatetime ";
$sql .= "from tr_log where id = ? and shu = ? group by ymd,shu UNION ALL select * from  tr_log where id = ? and shu = ?) as T ";
$sql .= "having T.ymd between DATE_SUB(?,INTERVAL 4 MONTH) and ? ";
$sql .= "order by T.ymd desc,T.jun ";

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
  $weight = " - total：".number_format($row["weight"],0);
	$dataset[$i] = array_merge($row,array('head_wt'=> $weight));
	$i++;
}
//$kintore_log = json_encode($dataset, JSON_UNESCAPED_UNICODE);
$kintore_log = $dataset;
$dataset_work=[];

//ぐらふでーた取得
$sql = "select ymd,DATEDIFF(?,ymd) as beforedate,ROW_NUMBER() OVER(order by ymd) as No,sum(weight*rep*sets) as weight ";
$sql .= "from tr_log where id = ? and shu = ? and ymd between DATE_SUB(?,INTERVAL 4 MONTH) and ? group by ymd,shu,id ";
$sql .= "order by ymd";
$graph_title = "『".$shu."MAX更新前のVolume推移』";
$subtitle="最初のMAX更新日4ヵ月前から";
//$btn_name = "MAX記録グラフへ";
//$typ=0;

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
//$maxline=0;
//$minline=999999;
$graph_data=[];
//$graph_data2=[];
foreach($dataset_work as $row){
  //$weight = number_format(max_r($row["weight"], $row["rep"] - $row["rep2"]),2);
	$weight = ($row["weight"]);


	//if($maxline<$weight){$maxline=$weight+10;}
	//if($minline>$weight){$minline=$weight-500;}
	
	//$graph_data[] = [$i,$weight];
	if($i===0){
		$labels[] = $row["beforedate"]."日前";
	}else{
		$labels[] = $row["beforedate"];
	}
	$graph_data[] = $weight;
	$i++;
}
$labels[$i-1] = "更新日";

//if($minline<0){$minline=0;}

//ラベル設定
/*
if($_POST["gtype"]==="year"){//直近1年
	//$btn_name2="全期間";
	//$kikan="all";
	$glabel1="直近1年";
	$glabel2="１年前";
}else if($_POST["gtype"]==="all"){//全期間
	$btn_name2="直近1年";
	$kikan="year";
	$glabel1="全期間";
	$glabel2="";
}
*/
$return_sts = array(
	"MSG" => $msg
	,"status" => $alert_status
	,"kintore_log" => $kintore_log
	,"graph_data1" => $graph_data
	,"labels" => $labels
	//,"graph_data2" => $graph_data2
	//,"btn_name2" => $btn_name2
	,"kikan" => $kikan
	,"glabel1" => "総Volume"
	//,"glabel2" => $glabel2
	//,"maxline" => $maxline
	//,"minline" => $minline
	,"graph_title" => $graph_title
	,"subtitle" => $subtitle
	//,"btn_name" => $btn_name
);
header('Content-type: application/json');
echo json_encode($return_sts, JSON_UNESCAPED_UNICODE);

exit();

?>
