<?php
// 設定ファイルインクルード 
require "config.php";

$now = date('Y-m-d');
//$hyoji = $_POST['hyoji'];//最初は１：体重・体脂肪率
$hyoji = !empty($_POST["hyoji"])?$_POST["hyoji"]:"1";
$gtype = !empty($_POST["gtype"])?$_POST["gtype"]:"all";

if(isset($_SESSION['USER_ID'])){ //ユーザーチェックブロック
	$id = $_SESSION['USER_ID'];
	//echo "session:".$id;
}else if (check_auto_login($_COOKIE['token'])==0) {
	$id = $_SESSION['USER_ID'];
	//echo "クッキー:".$id;
}else{
	//header("HTTP/1.1 301 Moved Permanently");
	//header("Location: index.php");
	var_dump($_SESSION);
	exit();
}	

//履歴取得
$sql = "select ROW_NUMBER() OVER(partition by id order by id,ymd) as No,taisosiki.*,round(weight*taisibou/100,1) as sibouryou,round(weight-(weight*taisibou/100),1) as josibou ,DATEDIFF(now(),ymd) as beforedate
from taisosiki where id = ? order by ymd desc ";

$result = $pdo_h->prepare( $sql );
$result->bindValue(1, $id, PDO::PARAM_STR);
$result->execute();
$dataset_work = $result->fetchAll(PDO::FETCH_ASSOC);
$dataset = [];
$kintore_log = json_encode($dataset_work, JSON_UNESCAPED_UNICODE);
//var_dump($kintore_log);
//exit();

//履歴取得
$sql = "select * from users where id = ?";

$result = $pdo_h->prepare( $sql );
$result->bindValue(1, $id, PDO::PARAM_STR);
$result->execute();
$user = $result->fetchAll(PDO::FETCH_ASSOC);


//ぐらふでーた取得
if($hyoji == "2"){//MAX表示:最も重い重量で最も回数をこなしたセットを抽出
	$graph_title = "『骨格筋・脂肪量 の推移』";
	$btn_name = "体重・体脂肪率へ";
	$glabel1="骨格筋";
	$glabel2="脂肪";
	$typ=1;
	$hyoji_change=1;
}else if($hyoji == "1"){//total表示
	$graph_title = "『体重・体脂肪率 の推移』";
	$btn_name = "骨格筋・脂肪量へ";
	$glabel1="体重";
	$glabel2="体脂肪率";
	$typ=0;
	$hyoji_change=2;
}else{
	exit();
}

$i=1;
$maxline=0;
$minline=999999;
$maxline2=0;
$minline2=999999;
$graph_data="";
$graph_data2="";
foreach($dataset_work as $row){
	if($hyoji == "2"){//MAX表示
		$weight = ($row["josibou"]/2);
		$taisibou = ($row["sibouryou"]);
	}else if($hyoji == "1"){
		$weight = ($row["weight"]);
		$taisibou = ($row["taisibou"]);
	}else{
		exit();
	}

	if($gtype==="year"){//直近1年
		if($row["beforedate"]<=365){
			if($maxline<$weight){$maxline=$weight+10;}
			if($minline>$weight){$minline=$weight-10;}
			$graph_data .= "[".(356-$row["beforedate"]).",".$weight."],";	
			if($maxline2<$taisibou){$maxline2=$taisibou+2;}
			if($minline2>$taisibou){$minline2=$taisibou-1;}
			$graph_data2 .= "[".(356-$row["beforedate"]).",".$taisibou."],";	
			
		}else if($row["beforedate"]<=730){
			if($maxline<$weight){$maxline=$weight+10;}
			if($minline>$weight){$minline=$weight-10;}
			$graph_data2 .= "[".(730-$row["beforedate"]).",".$weight."],";	
			if($maxline2<$taisibou){$maxline2=$taisibou+2;}
			if($minline2>$taisibou){$minline2=$taisibou-1;}
			$graph_data2 .= "[".(730-$row["beforedate"]).",".$taisibou."],";	
		}
	}else if($gtype==="all"){//全期間
		if($maxline<$weight){$maxline=$weight+10;}
		if($minline>$weight){$minline=$weight-10;}
		$graph_data .= "[".$row["No"].",".$weight."],";

		if($maxline2<$taisibou){$maxline2=$taisibou+2;}
		if($minline2>$taisibou){$minline2=$taisibou-1;}
		$graph_data2 .= "[".$row["No"].",".$taisibou."],";
	}else{
		echo "are?";
		exit();
	}
	
	$i++;
}
if($gtype==="year"){//直近1年
	$btn_name2="全期間";
	$kikan="all";
	$gtype_change="";
}else if($gtype==="all"){//全期間
	$btn_name2="直近1年";
	$kikan="year";
	$gtype_change="all";
}

//var_dump($graph_data);
//exit();
?>
<HTML>
<HEAD>
	<?php
		require "header.php";
	?>
	<TITLE>肉体改造ネットワーク</TITLE>
</HEAD>
<BODY class = "graphe">
	<div id="headerArea2">
		<p class="graph-title"><?php echo $graph_title ?></p>
		<div id="graph" style='margin-bottom:5px;'></div>
		<div class='row' style='text-align: center;'>
			<FORM method="post" action="graph02.php" style='width:200px;margin-left:50px;;'>
				<button class='btn btn-primary' type="submit"> <?php echo $btn_name;?> </button>
				<INPUT type="hidden" name="hyoji" value=<?php echo $hyoji_change;?>>
				<INPUT type="hidden" name="id" value="<?php echo $id;?>">
				<INPUT type="hidden" name="gtype" value="<?php echo $gtype;?>"><!--期間は変わらない-->
			</FORM>
			<FORM method="post" action="graph02.php" style='width:130px;'>
				<button class='btn btn-primary' type="submit"> <?php echo $btn_name2;?> </button>
				<INPUT type="hidden" name="hyoji" value=<?php echo $hyoji_change;?>>
				<INPUT type="hidden" name="id" value="<?php echo $id;?>">
				<INPUT type="hidden" name="shu" value="<?php echo $shu;?>">
				<INPUT type="hidden" name="gtype" value="<?php echo $kikan;?>">
			</FORM>
		</div>
	</div>
	<main class='container-fluid' id='app'>
		<table class='table'>
			<thead>
				<!--<th>日付</th>-->
				<th>体重<br></th>
				<th>脂肪率<br></th>
				<th>脂肪量<br></th>
				<th>筋肉量<br></th>
				<th>骨格<br>筋率</th>
				<th>BMI<br></th>
			</thead>
		
			<template v-for='(list,index) in kintore_log' :key='list.ymd+list.No'>
				<tr class='lst'>
					<td class='text-left' colspan="5" style='padding:0;'>{{list.ymd}} memo:{{list.memo}}</td>
				</tr>
				<tr class='lst'>
					<td class='text-end' style='padding:0;'>{{list.weight}}kg</td>
					<td class='text-end' style='padding:0;'>{{list.taisibou}}%</td>
					<td class='text-end' style='padding:0;'>{{list.sibouryou}}kg</td>
					<td class='text-end' style='padding:0;'>{{list.josibou / 2}}kg</td>
					<td class='text-end' style='padding:0;'>{{Math.round((list.josibou / 2) / list.weight * 100)}}%</td>
					<td class='text-end' style='padding:0;'>{{Math.round((list.weight / (tall * tall)) * 10) /10}}</td>
				</tr>
			</template>
		</table>
	</main>
	<div id="footerArea2" style='text-align: center;'>
		<a href=<?php echo "'TOP.php?id=".$id."&pass=".$pass."'" ?> class='btn btn-secondary' style = 'margin-top:0.8em;text-decoration: none;'>戻 る</a>
	</div>
	<script>
		(function basic(container) {
		  var d1 = [<?php echo $graph_data;?>],
			d2 = [<?php echo $graph_data2;?>],
		  data = [
				{
		      data: d1,
		      label: "<?php echo $glabel1;?>"
				},{
		      data: d2,
					label: "<?php echo $glabel2;?>",
		      yaxis:2
				}
		  ];
		  function labelFn(label) {
		      return label;
		  }
		  graph = Flotr.draw(container, data, {
				yaxis:{
					min:<?php echo $minline; ?>,        //y軸の最小値を設定
					max:<?php echo $maxline; ?>,        //y軸の最大値を設定
					title:"<?php echo $glabel1;?>"
				}, //y軸にタイトルを表示
				y2axis:{
					min:<?php echo $minline2; ?>,        //y軸の最小値を設定
					max:<?php echo $maxline2; ?>,        //y軸の最大値を設定
					title:"<?php echo $glabel2;?>"
				}, //y軸にタイトルを表示
		  	legend: {
		      position: 'se',
		      labelFormatter: labelFn,
		      backgroundColor: "#D2E8FF"
		  	},
		    HtmlText: false
		  });
		})(document.getElementById("graph"));
	</script>
	<script>//Vus.js
		const { createApp, ref, onMounted, computed, VueCookies,watch } = Vue;
		createApp({
			setup(){
				const kintore_log = ref(<?php echo $kintore_log;?>)
				const tall = ref(<?php echo $user[0]["height"] / 100;?>)
				onMounted(() => {
					console_log('onMounted')
				})
				return{
					kintore_log,
					tall,
				}
			}
		}).mount('#app');
	</script>

</BODY>
</HTML>
