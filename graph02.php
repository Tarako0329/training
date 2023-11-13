<?php
// 設定ファイルインクルード
require "config.php";

$now = date('Y-m-d');
$hyoji = $_POST['hyoji'];//最初は１：体重・体脂肪率

if(isset($_SESSION['USER_ID'])){ //ユーザーチェックブロック
	$id = $_SESSION['USER_ID'];
	//echo "session:".$id;
}else if (check_auto_login($_COOKIE['token'])==0) {
	$id = $_SESSION['USER_ID'];
	//echo "クッキー:".$id;
}else{
	header("HTTP/1.1 301 Moved Permanently");
	header("Location: index.php");
}	

//ユーザー確認
unset($sql);
$sql = "select * from users where ((id)='".$id."')";
$result = $mysqli->query( $sql );
$row_cnt = $result->num_rows;
$row = $result->fetch_assoc(); 
if($row_cnt==0){
	echo "<P>ＩＤ 又はパスワードが間違っています。</P>".$id.$pass;
	?><a href="index.php"> 戻る</a><?php
	exit();
}


//履歴取得
$sql = "select ROW_NUMBER() OVER(partition by id order by id,ymd) as No,taisosiki.*,round(weight*taisibou/100,1) as sibouryou,round(weight-(weight*taisibou/100),1) as josibou 
from taisosiki where id = ? order by ymd desc ";

$result = $pdo_h->prepare( $sql );
$result->bindValue(1, $id, PDO::PARAM_STR);
$result->execute();
$dataset_work = $result->fetchAll(PDO::FETCH_ASSOC);
$dataset = [];
$kintore_log = json_encode($dataset_work, JSON_UNESCAPED_UNICODE);
//var_dump($kintore_log);
//exit();

//ぐらふでーた取得
if($hyoji == "0"){//MAX表示:最も重い重量で最も回数をこなしたセットを抽出
	$graph_title = "『筋骨・脂肪量 の推移』";
	$btn_name = "体重・体脂肪率へ";
	$glabel1="徐脂肪";
	$glabel2="脂肪";
	$typ=1;
}else{//total表示
	$graph_title = "『体重・体脂肪率 の推移』";
	$btn_name = "筋骨・脂肪量へ";
	$glabel1="体重";
	$glabel2="体脂肪率";
	$typ=0;
}

$i=1;
$maxline=0;
$minline=999999;
$maxline2=0;
$minline2=999999;
$graph_data="";
$graph_data2="";
foreach($dataset_work as $row){
	if($hyoji == "0"){//MAX表示
		$weight = ($row["josibou"]);
		$taisibou = ($row["sibouryou"]);
	}else{
		$weight = ($row["weight"]);
		$taisibou = ($row["taisibou"]);
	}

	if($_POST["gtype"]==="year"){//直近1年
		if($row["beforedate"]<=365){
			if($maxline<$weight){$maxline=$weight+10;}
			if($minline>$weight){$minline=$weight-10;}
			$graph_data .= "[".(356-$row["beforedate"]).",".$weight."],";	
		}else if($row["beforedate"]<=730){
			if($maxline<$weight){$maxline=$weight+10;}
			if($minline>$weight){$minline=$weight-10;}
			$graph_data2 .= "[".(730-$row["beforedate"]).",".$weight."],";	
		}
	}else if($_POST["gtype"]==="all"){//全期間
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
if($_POST["gtype"]==="year"){//直近1年
	$btn_name2="全期間";
	$kikan="all";
}else if($_POST["gtype"]==="all"){//全期間
	$btn_name2="直近1年";
	$kikan="year";
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
				<INPUT type="hidden" name="hyoji" value=<?php echo $typ;?>>
				<INPUT type="hidden" name="id" value="<?php echo $id;?>">
				<INPUT type="hidden" name="gtype" value="<?php echo $_POST["gtype"];?>">
			</FORM>
			<FORM method="post" action="graph02.php" style='width:130px;'>
				<button class='btn btn-primary' type="submit"> <?php echo $btn_name2;?> </button>
				<INPUT type="hidden" name="hyoji" value=<?php echo $typ;?>>
				<INPUT type="hidden" name="id" value="<?php echo $id;?>">
				<INPUT type="hidden" name="shu" value="<?php echo $shu;?>">
				<INPUT type="hidden" name="gtype" value="<?php echo $kikan;?>">
			</FORM>
		</div>
	</div>
	<main class='container-fluid' id='app'>
		<template v-for='(list,index) in kintore_log' :key='list.ymd+list.No'>
			<div class='row lst'>
				<div class='col-4 text-center' style='padding:0;'>{{list.ymd}}</div>
				<div class='col-2 text-end' style='padding:0;'>{{list.weight}}kg</div>
				<div class='col-2 text-end' style='padding:0;'>{{list.taisibou}}%</div>
				<div class='col-2 text-end' style='padding:0;'>{{list.sibouryou}}kg</div>
				<div class='col-2 text-end' style='padding:0;'>{{list.josibou}}kg</div>
				<div class='col-12 text-end' style='padding:0;'>{{list.memo}}</div>
			</div>
			
		</template>
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
				onMounted(() => {
					console_log('onMounted')
				})
				return{
					kintore_log,
				}
			}
		}).mount('#app');
	</script>

</BODY>
</HTML>
