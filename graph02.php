<?php
// 設定ファイルインクルード 
require "config.php";
log_writer2("\$_POST",$_POST,"lv3");

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
$sql = "SELECT 
id
,left(ymd,7) as ym
,CASE
	WHEN right(ymd,2) <= 10 THEN '上'
    WHEN right(ymd,2) <= 20 THEN '中'
    WHEN right(ymd,2) <= 31 THEN '下'
END as 旬
,round(avg(weight),2)
,round(avg(taisibou),2)
,round(avg(weight)*avg(taisibou)/100,1) as sibouryou
,round(avg(weight)-(avg(weight)*avg(taisibou)/100),1) as josibou
,MIN(DATEDIFF(now(),ymd)) as beforedate
FROM `taisosiki`
where id=?
group by id,left(ymd,7) 
,CASE
	WHEN right(ymd,2) <= 10 THEN '上'
    WHEN right(ymd,2) <= 20 THEN '中'
    WHEN right(ymd,2) <= 31 THEN '下'
END
order by ymd ";

$result = $pdo_h->prepare( $sql );
$result->bindValue(1, $id, PDO::PARAM_STR);
$result->execute();
$dataset_work = $result->fetchAll(PDO::FETCH_ASSOC);
$dataset = [];


if($hyoji == "2"){//骨格筋・脂肪量 の推移
	$graph_title = "『骨格筋・脂肪量 の推移』";
	$btn_name = "体重・体脂肪率へ";
	$glabel1="骨格筋";
	$glabel2="脂肪";
	$typ=1;
	$hyoji_change=1;
}else if($hyoji == "1"){//体重・体脂肪率 の推移
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
$graph_data1=[];
$graph_data2=[];
$graph_data3=[];
$graph_data4=[];
$labels = [];
foreach($dataset_work as $row){
	if($row["beforedate"]<0){
		continue;
	}

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
			$graph_data1[] = $weight;	
			$graph_data2[] = $taisibou;	
			$labels[] = $row["beforedate"];

		}else if($row["beforedate"]<=730){
			$graph_data3[] = $weight;	
			$graph_data4[] = $taisibou;	
			//$labels[] = $row["beforedate"];
		}
	}else if($gtype==="all"){//全期間
		$graph_data1[] = $weight;
		$graph_data2[] = $taisibou;
		$labels[] = $row["beforedate"];
	}else{
		echo "are?";
		exit();
	}
	
	$i++;
}
if($gtype==="year"){//直近1年
	$btn_name2="全期間";
	$kikan="all";
	//$gtype_change="";
}else if($gtype==="all"){//全期間
	$btn_name2="直近1年";
	$kikan="year";
	//$gtype_change="all";
}

//var_dump($graph_data);
//exit();
?>
<!DOCTYPE html>
<HTML>
<HEAD>
	<?php
		require "header.php";
	?>
	<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>
	<TITLE>肉体改造ネットワーク</TITLE>
</HEAD>
<BODY class = "graph" style='padding-top:392px;'>
<div  id='app'>
	<div id="headerArea2">
		<div class='row' style='height:100%;'>
			<div class='col-12' style='justify-content: center;height: auto;max-height:60px;'>
				<p class="graph-title">{{graph_title}}</p>
				<p v-show='graph_subtitle!==""' style='color:darkgrey;font-size:12px;'>{{graph_subtitle}}</p>
			</div>
		
			<div class='col-12' id="graph" style='height:250px;margin-bottom:5px;position:relative;max-width:900px;'>
				<canvas id="myChart"></canvas>
			</div>
		
			<div  class='d-flex align-items-center justify-content-center' style='width: 100%;height:40px;'>
				<FORM method="post" action="graph02.php" style='width:50%;'>
					<button class='btn btn-primary m-0' style='width:100%;max-width:200px;' type="submit"> <?php echo $btn_name;?> </button>
					<INPUT type="hidden" name="hyoji" value=<?php echo $hyoji_change;?>>
					<INPUT type="hidden" name="id" value="<?php echo $id;?>">
					<INPUT type="hidden" name="gtype" value="<?php echo $gtype;?>"><!--期間は変わらない-->
				</FORM>
				<FORM method="post" action="graph02.php" style='width:50%;'>
					<button class='btn btn-primary m-0' style='width:100%;max-width:200px;' type="submit"> <?php echo $btn_name2;?> </button>
					<INPUT type="hidden" name="hyoji" value=<?php echo $hyoji_change;?>>
					<INPUT type="hidden" name="id" value="<?php echo $id;?>">
					<INPUT type="hidden" name="gtype" value="<?php echo $kikan;?>">
				</FORM>
			</div>
		</div>
	</div>
	<main class='container-fluid'>
		<div class='' style='position:fixed;top:355px;left:0px; width: 100%;'>
			<table class='table m-0' style='max-width:400px;width:100%;'>
				<thead class=''>
					<!--<th>日付</th>-->
					<th class='text-end p-0' style='width:50px;'>体重<br></th>
					<th class='text-end p-0' style='width:50px;'>脂肪率<br></th>
					<th class='text-end p-0' style='width:50px;'>脂肪量<br></th>
					<th class='text-end p-0' style='width:50px;'>筋肉量<br></th>
					<th class='text-end p-0' style='width:50px;'>骨格<br>筋率</th>
					<th class='text-end p-0' style='width:50px;'>BMI<br></th>
				</thead>
			</table>
		</div>
		<div >
			<table class='table ' style='max-width:400px;width:100%;'>
				<template v-for='(list,index) in kintore_log' :key='list.ymd+list.No'>
					<tr class='lst'>
						<td class='text-left p-0' colspan="6" style=''>{{list.ymd}} memo:{{list.memo}}</td>
					</tr>
					<tr class='lst'>
						<td class='text-end p-0' style='width:50px;'>{{list.weight}}kg</td>
						<td class='text-end p-0' style='width:50px;'>{{list.taisibou}}%</td>
						<td class='text-end p-0' style='width:50px;'>{{list.sibouryou}}kg</td>
						<td class='text-end p-0' style='width:50px;'>{{list.josibou / 2}}kg</td>
						<td class='text-end p-0' style='width:50px;'>{{Math.round((list.josibou / 2) / list.weight * 100)}}%</td>
						<td class='text-end p-0' style='width:50px;'>{{Math.round((list.weight / (tall * tall)) * 10) /10}}</td>
					</tr>
				</template>
			</table>
		</div>
	</main>
	<footer id=""  class='footerArea text-center' >
		<a href=<?php echo "'TOP.php?id=".$id."&pass=".$pass."'" ?> class='btn btn-secondary' style = 'margin-top:0.8em;text-decoration: none;'>戻 る</a>
	</footer>
</div>
	<script>
	</script>
	<script>//Vus.js
		const { createApp, ref, onMounted, computed, VueCookies,watch } = Vue;
		createApp({
			setup(){
				const kintore_log = ref(<?php echo $kintore_log;?>)
				const tall = ref(<?php echo $user[0]["height"] / 100;?>)


				const G_type = ref('<?php echo $gtype ?>')
				const graph_title = ref('<?php echo $graph_title ?>')
				const graph_subtitle = ref('<?php echo $graph_subtitle ?>')
				let G_data1 = <?php echo json_encode($graph_data1); ?>

				let G_data2 = [<?php echo json_encode($graph_data2, JSON_UNESCAPED_UNICODE); ?>]

				let G_data3 = [<?php echo json_encode($graph_data3, JSON_UNESCAPED_UNICODE); ?>]

				let G_data4 = [<?php echo json_encode($graph_data4, JSON_UNESCAPED_UNICODE); ?>]

				let labels = [<?php echo json_encode($labels, JSON_UNESCAPED_UNICODE); ?>]
				
				let datasets = []

				var graph_obj
				const create_graph = (ctx) =>{
					console_log("create_graph : graph_data")
					
					const graph_data = {
						labels    : labels[0]
						,datasets : datasets
					}
					
					console_log(graph_data)

					if(graph_obj){
						graph_obj.destroy()
					}
				
					graph_obj = new Chart(ctx, {
						type : 'line'
						,data: graph_data
						,options: {
							plugins: {
								title: {
									display: false,
									//text: open_fil.value
									text: 'test'
								},
								filler:{
									drawTime : 'beforeDraw'
								}
							},
							responsive: true,
							maintainAspectRatio: false,
							scales: {
								x: {
									stacked: false,
									ticks:{
										maxTicksLimit: 6
										,stepSize: 2,
									}
									/*display:true,
									title:{
										display:true,
										text:'月'
									}*/
								},
								y: {
									stacked: false,
									/*display:true,
									title:{
										display:true,
										text:'Kg'
									}*/
								}
							}
						}
					})      
				}

				onMounted(() => {
					console_log('onMounted')
					if(G_type.value==='all'){
						console_log('onMounted:all')
						color1 = 'rgba('+(~~(256 * Math.random()))+','+(~~(256 * Math.random()))+','+ (~~(256 * Math.random()))	//max
						color3 = 'rgba('+(~~(256 * Math.random()))+','+(~~(256 * Math.random()))+','+ (~~(256 * Math.random()))	//max
						color2 = 'rgba('+(~~(256 * Math.random()))+','+(~~(256 * Math.random()))+','+ (~~(256 * Math.random()))	//volume
						color4 = 'rgba('+(~~(256 * Math.random()))+','+(~~(256 * Math.random()))+','+ (~~(256 * Math.random()))	//volume
						opacity1 = ', 1)'
						opacity2 = ', 0.6)'
						datasets.push({//体重・徐脂肪
							'label':'Mx'
							,'data':G_data1
							,'backgroundColor':  color1 + opacity2
							,borderColor: color1 + opacity2
							//,fill:true
							//,stepped: 'middle'
							,borderWidth: 2
							, pointRadius:1
							//,type:'bar'
						})
						datasets.push({//体脂肪率・体脂肪量
							'label':'TTL'
							,'data':G_data2
							,'backgroundColor':  color2 + opacity2
							,borderColor:  color2 + opacity2
							//,fill:true
							//,stepped: 'middle'
							,borderWidth: 2
							, pointRadius:1
							//,type:'bar'
						})
						console_log(datasets)
						create_graph(document.getElementById('myChart'))

					}else{
						console_log('onMounted:year')
						color1 = 'rgba('+(~~(256 * Math.random()))+','+(~~(256 * Math.random()))+','+ (~~(256 * Math.random()))	//max
						color3 = 'rgba('+(~~(256 * Math.random()))+','+(~~(256 * Math.random()))+','+ (~~(256 * Math.random()))	//max
						color2 = 'rgba('+(~~(256 * Math.random()))+','+(~~(256 * Math.random()))+','+ (~~(256 * Math.random()))	//volume
						color4 = 'rgba('+(~~(256 * Math.random()))+','+(~~(256 * Math.random()))+','+ (~~(256 * Math.random()))	//volume
						opacity1 = ', 1)'
						opacity2 = ', 0.6)'
						datasets.push({//体重・徐脂肪
							'label':'Mx'
							,'data':G_data1
							,'backgroundColor':  color1 + opacity2
							,borderColor: color1 + opacity2
							,fill:true
							//,stepped: 'middle'
							,borderWidth: 2
							, pointRadius:1
							//,type:'bar'
						})
						datasets.push({//体脂肪率・体脂肪量
							'label':'TTL'
							,'data':G_data2
							,'backgroundColor':  color2 + opacity2
							,borderColor:  color2 + opacity2
							,fill:true
							//,stepped: 'middle'
							,borderWidth: 2
							, pointRadius:1
							//,type:'bar'
						})
						datasets.push({//体重・徐脂肪
							'label':'Mx'
							,'data':G_data3
							,'backgroundColor':  color1 + opacity2
							,borderColor: color1 + opacity2
							,fill:true
							//,stepped: 'middle'
							,borderWidth: 2
							, pointRadius:1
							//,type:'bar'
						})
						datasets.push({//体脂肪率・体脂肪量
							'label':'TTL'
							,'data':G_data4
							,'backgroundColor':  color2 + opacity2
							,borderColor:  color2 + opacity2
							,fill:true
							//,stepped: 'middle'
							,borderWidth: 2
							, pointRadius:1
							//,type:'bar'
						})
						console_log(datasets)
						create_graph(document.getElementById('myChart'))
					}
				})
				return{
					kintore_log,
					tall,
					graph_title,
					graph_subtitle,
				}
			}
		}).mount('#app');
	</script>

</BODY>
</HTML>
