<?php
require_once "config.php";
require_once "database.php";

$db = new Database();
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

$now = date('Y-m-d');
//$hyoji  taiju：体重・体脂肪率 kinryou：筋肉量・脂肪量
$hyoji = !empty($_POST["hyoji"])?$_POST["hyoji"]:"1";
$gtype = !empty($_POST["gtype"])?$_POST["gtype"]:"all";

$msg="";
$alert_status="";

//履歴取得
$sql = "SELECT ROW_NUMBER() OVER(partition by id order by id,ymd) as No,taisosiki.*,round(weight*taisibou/100,1) as sibouryou,round(weight-(weight*taisibou/100),1) as josibou ,DATEDIFF(now(),ymd) as beforedate
from taisosiki where id = :id order by ymd desc ";
/*
$result = $pdo_h->prepare( $sql );
$result->bindValue(1, $id, PDO::PARAM_STR);
$result->execute();
$taisosiki_log = $result->fetchAll(PDO::FETCH_ASSOC);
*/
$taisosiki_log = $db->SELECT($sql,[":id"=>$id]);

//BMI算出用に身長取得
$sql = "SELECT (height/100) as height from users where id = :id";
/*
$result = $pdo_h->prepare( $sql );
$result->bindValue(1, $id, PDO::PARAM_STR);
$result->execute();
$user = $result->fetchAll(PDO::FETCH_ASSOC);
*/
$user = $db->SELECT($sql,[":id"=>$id]);


//ぐらふでーた取得
$sql = "SELECT 
	id
	,left(ymd,7) as ym
	,CASE
		WHEN right(ymd,2) <= 10 THEN '上'
	    WHEN right(ymd,2) <= 20 THEN '中'
	    WHEN right(ymd,2) <= 31 THEN '下'
	END as 旬
	,round(avg(weight),2) as weight
	,round(avg(taisibou),2) as taisibou
	,round(avg(weight)*avg(taisibou)/100,1) as sibouryou
	,round(avg(weight)-(avg(weight)*avg(taisibou)/100),1) as josibou
	,MIN(DATEDIFF(now(),ymd)) as beforedate
	,MIN(TIMESTAMPDIFF(MONTH, ymd, CURDATE())) as label_bk
	,CONCAT(
		IF(((YEAR(CURDATE()) - YEAR(MIN(ymd))) * 12 + (MONTH(CURDATE()) - MONTH(MIN(ymd)))) = 0
			,'今月'
			,CONCAT(((YEAR(CURDATE()) - YEAR(MIN(ymd))) * 12 + (MONTH(CURDATE()) - MONTH(MIN(ymd)))),'ヶ月前')),
		CASE
			WHEN right(ymd,2) <= 10 THEN '上旬'
	    WHEN right(ymd,2) <= 20 THEN '中旬'
	    WHEN right(ymd,2) <= 31 THEN '下旬'
	END) as label
	FROM `taisosiki`
	where id=:id
	group by id,left(ymd,7) 
	,CASE
		WHEN right(ymd,2) <= 10 THEN '上'
	    WHEN right(ymd,2) <= 20 THEN '中'
	    WHEN right(ymd,2) <= 31 THEN '下'
	END
	order by ymd ";
/*
$result = $pdo_h->prepare( $sql );
$result->bindValue(1, $id, PDO::PARAM_STR);
$result->execute();
$dataset_work = $result->fetchAll(PDO::FETCH_ASSOC);
*/
$dataset_work = $db->SELECT($sql,[":id"=>$id]);


if($hyoji == "kinryou"){//骨格筋・脂肪量 の推移
	$graph_title = "『骨格筋・脂肪量 の推移』";
	$glabel1="骨格筋kg";
	$glabel2="脂肪kg";
}else if($hyoji == "taiju"){//体重・体脂肪率 の推移
	$graph_title = "『体重・体脂肪率 の推移』";
	$glabel1="体重kg";
	$glabel2="体脂肪率%";
}else{
	exit();
}

$i=1;
$graph_data1=[];
$graph_data2=[];
$graph_data3=[];
$graph_data4=[];
$labels = [];
foreach($dataset_work as $row){
	if($row["beforedate"]<0){
		continue;
	}

	if($hyoji == "kinryou"){
		$weight = ($row["josibou"]/2);
		$taisibou = ($row["sibouryou"]);
	}else if($hyoji == "taiju"){
		$weight = ($row["weight"]);
		$taisibou = ($row["taisibou"]);
	}else{
		exit();
	}

	
	if($gtype==="year"){//直近1年
		if($row["beforedate"]<=365){
			$graph_data1[] = $weight;	
			$graph_data2[] = $taisibou;	
			$labels[] = $row["label"];

		}/*去年との比較はやめた
		else if($row["beforedate"]<=730){
			$graph_data3[] = $weight;	
			$graph_data4[] = $taisibou;	
		}*/
	}else if($gtype==="all"){//全期間
		$graph_data1[] = $weight;
		$graph_data2[] = $taisibou;
		$labels[] = $row["label"];
	}else{
		echo "are?";
		exit();
	}
	
	$i++;
}


$return_sts = array(
	"MSG" => $msg
	,"status" => $alert_status
	,"taisosiki_log" => $taisosiki_log
	,"graph_data1" => $graph_data1
	,"graph_data2" => $graph_data2
	,"graph_data3" => $graph_data3
	,"graph_data4" => $graph_data4
	,"labels" => $labels
	,"glabel1" => $glabel1
	,"glabel2" => $glabel2
	,"graph_title" => $graph_title
	,"subtitle" => "月を3分割(10日毎平均)のグラフ"
	,"height" => $user[0]["height"]
);
header('Content-type: application/json');
echo json_encode($return_sts, JSON_UNESCAPED_UNICODE);

exit();

?>
