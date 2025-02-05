<?php
// 設定ファイルインクルード
require "config.php";
log_writer2("\$_POST",$_POST,"lv3");
$now = date('Y-m-d');
//トレーニング種別
$shu = ($_POST["shu"]);
//グラフ種類（MAX:0 or トレーニング量:1 or MAX更新時:2)
$hyoji = ($_POST["hyoji"]);

if(isset($_SESSION['USER_ID'])){ //ユーザーチェックブロック
	$id = $_SESSION['USER_ID'];
	//echo "session:".$id;
}else if (check_auto_login($_COOKIE['token'])==0) {
	$id = $_SESSION['USER_ID'];
	//echo "クッキー:".$id;
}else{
	header("HTTP/1.1 301 Moved Permanently");
	header("Location: index.php");
	exit();
}	

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
<BODY class='graph'>
	<div id='app'>
	<div id="headerArea2">
		<div class='container'>
		<div class='row' style='height:100%;'>
			<div class='col-12' style='justify-content: center;height: auto;max-height:60px;position:relative;'>
				<p class="graph-title">{{graph_title}}</p>
				<p v-show='graph_subtitle!==""' style='color:darkgrey;font-size:12px;'>{{graph_subtitle}}</p>
			</div>
			<div class='col-12 text-end' style='margin-top:-10px;'>
				<a href="#" data-bs-toggle='modal' data-bs-target='#mokuhyou' style='font-size:12px;'>{{mokuhyou_disp}}</a>
			</div>
			<div class='col-12' id="graph" style='height:250px;margin-bottom:5px;position:relative;max-width:900px;'>
				<canvas id="myChart"></canvas>
			</div>
			
			<div class='d-flex align-items-center justify-content-center' style='width: 100%;height:40px;'>
				<div class='text-end' style='width:50%;max-width:200px;'>
					<select v-model='g_shu' class='form-select form-select-sm' style='width:100%;max-width:200px;'>
						<option value='max'>MAX記録</option>
						<option value='volume'>トレーニング量</option>
						<option value='growth'>MAX更新前</option>
					</select>
				</div>
				<div class='text-start' style='width:50%;max-width:200px;'>
					<select v-show='g_shu!=="growth"' v-model='gtype' class='form-select form-select-sm' style='width:100%;max-width:200px;'>
						<option value='12M'>直近12ヵ月</option>
						<option value='all'>全期間</option>
						<option value='year'>前年比較</option>
					</select>
				</div>

			</div>
		</div>
	</div>
	</div>
	<main class='container'>
		<template v-for='(list,index) in kintore_log' :key='list.ymd+list.jun'>
			<div class='accordion-item'>
				<div v-if='String(list.jun)==="0"' class='row shu accordion-header'>
					<button type='button' class='accordion-button collapsed' data-bs-toggle='collapse' :data-bs-target='`#collapseOne${list.ymd}${list.shu}`' 
					aria-expanded='false' aria-controls='collapseOne' >
						{{list.ymd}} {{(list.head_wt)}} kg
					</button>
				</div>
				<div v-if='String(list.jun)!=="0"'  :id='`collapseOne${list.ymd}${list.shu}`' class='accordion-collapse collapse' data-bs-parent='#accordionExample'>
					<div class='row lst accordion-body'>
						<div class='col-12' style='padding:0  0 6px;display:flex;'>
							<div style='width: 20px;'>{{list.No - 1}}</div>
							<div class='text-end' style='width: 70px;padding:0;'>{{list.weight}}kg</div>
							<div v-if="list.tani==='0'"      class='text-end' style='width: 60px;padding-right:0;'>{{list.rep}}({{list.rep2}})回</div>
							<div v-else-if="list.tani==='1'" class='text-end' style='width: 65px;padding-right:0;'>{{list.rep}}({{list.rep2}})秒</div>
							<div class='text-end' style='padding-right:0;width:50px;'>{{list.sets}}sets</div>
							<div class='' style='padding:0 0 0 10px;'>{{list.memo}}</div>
						</div>
					</div>
				</div>
			</div>
		</template>
	</main>
	<footer id=""  class='footerArea' >
		<div class='container hf_color'>
		<div class='row'>
			<div class='col-12 text-center'>
				<a href=<?php echo "'TOP.php?id=".$id."&pass=".$pass."'" ?> class='btn btn-secondary' style = 'margin-top:0.8em;text-decoration: none;'>戻 る</a>
			</div>
		</div>
		</div>
	</footer>
		<!--↓目標設定モーダル-->
		<div class='modal fade' id='mokuhyou' tabindex='-1' role='dialog' aria-labelledby='basicModal' aria-hidden='true'>
			<div class='modal-dialog  modal-dialog-centered'>
				<div class='modal-content edit' style=''>
					<form method = 'post' action='taisosiki_ins.php'>
						<div class='modal-header'>
	      			<h5 class="modal-title">目標設定</h5>
  	    			<button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
						</div>
						<div class='modal-body container'>
							<div class='row' style='margin:1px 20px;'>
								<div class="btn-group" role="group" aria-label="Basic radio toggle button group">
								  <input type="radio" class="btn-check" name="btnradio" id="btnradio1" v-model='mokuhyou_type' value='kg' autocomplete="off" >
								  <label class="btn btn-outline-primary" for="btnradio1">重量指定</label>

								  <input type="radio" class="btn-check" name="btnradio" id="btnradio3" v-model='mokuhyou_type' value='par' autocomplete="off">
								  <label class="btn btn-outline-primary" for="btnradio3">体重比</label>
								</div>
							</div>
							<div class='row' style='margin:1px 20px;'>
								<label for='shu2' class="form-label" style='padding-left:0;margin-bottom:1px;'>現体重（KG）</label>
								<input v-model='taijuu' type='Number' step="0.01" class="form-control form-control-sm" id='shu2' name='weight' required='required'>
							</div>
							<div class='row' style='margin:1px 20px;'>
								<label for='sibo' class="form-label" style='padding-left:0;margin-bottom:1px;'>体重比（％）</label>
								<input v-model='mokuhyou_par' type='Number' step="1" class="form-control form-control-sm" id='sibo' name='sibo'>
							</div>
							<div class='row' style='margin:1px 20px;'>
								<label for='shu2' class="form-label" style='padding-left:0;margin-bottom:1px;'>目標重量</label>
								<input v-model='mokuhyou_kg' type='Number' step="1" class="form-control form-control-sm" id='shu2' name='weight' required='required'>
							</div>
							<div class='row' style='margin:1px 20px;'>
								<label for='yobi2' class="form-label" style='padding-left:0;margin-bottom:1px;'>予備２</label>
								<input type='text' class="form-control form-control-sm" id='yobi2' name='yobi2'>
							</div>
						</div>
						<div class='modal-footer'>
							<button type='button' style='width:90px;' name='' class="btn btn-secondary mbtn" data-bs-dismiss="modal" >キャンセル</button>
							<button @click='set_mokuhyou' type='button' style='width:90px;' name='btn' class="btn btn-primary mbtn" data-bs-dismiss="modal" >登録</button>
						</div>
					</form>
				</div>
			</div>
		</div>

	</div>
	<script>
	</script>
	<script>//Vus.js
		const { createApp, ref, onMounted, computed, VueCookies,watch } = Vue;
		createApp({
			setup(){
				const kintore_log = ref()
				//label
				const gtype = ref('all')	//all.year
				const g_shu = ref('max')	//max,volume,growth

				const shu = ref('<?php echo $_POST["shu"];?>')	//トレーニング種目
				const graph_title = ref('')
				const graph_subtitle = ref('')
				let datasets = []
				let labels = []
				let fill_sts = []

				watch([gtype,g_shu],()=>{
					if(g_shu.value==="growth"){
						get_growth_data()
					}else if(g_shu.value==="max"){
						get_max_data()
					}else if(g_shu.value==="volume"){
						get_volume_data()
					}
				})
				
				const get_max_data = () =>{
					console_log("start get_max_data")
					const form_data = new FormData()
					form_data.append(`shu`, shu.value)
					form_data.append(`gtype`, gtype.value)
					axios
						.post("ajax_get_max_log.php",form_data, {headers: {'Content-Type': 'multipart/form-data'}})
						.then((response) => {
							console_log(response.data)
							kintore_log.value = response.data.kintore_log
							labels = response.data.labels
							datasets = []
							let color
							const skipped = (ctx, value) => ctx.p0.skip || ctx.p1.skip ? value : undefined;

							taijuu.value = response.data.taisosiki.weight
							mokuhyou_type.value = response.data.ms_training.mokuhyou_type
							if(mokuhyou_type.value==='kg'){
								mokuhyou_kg.value = response.data.ms_training.mokuhyou
							}else{
								mokuhyou_par.value = response.data.ms_training.mokuhyou
								mokuhyou_kg.value = response.data.taisosiki.weight * response.data.ms_training.mokuhyou / 100
							}
							//let fill_sts = (mokuhyou_kg.value!==0)?{above: 'blue', below: 'red', target: {value: mokuhyou_kg.value}}:false
							fill_sts = (mokuhyou_kg.value!==0)?{
								above: 'rgba('+(~~(256 * Math.random()))+','+(~~(256 * Math.random()))+','+ (~~(256 * Math.random()))+', 0.3)'
								, below: 'rgba('+(~~(256 * Math.random()))+','+(~~(256 * Math.random()))+','+ (~~(256 * Math.random()))+', 0.3)'
								, target: {value: mokuhyou_kg.value}
								}:false

							if(gtype.value==='year'){
								color = 'rgba('+(~~(256 * Math.random()))+','+(~~(256 * Math.random()))+','+ (~~(256 * Math.random()))+', 1)'
								datasets.push({
									'label':response.data.glabel1
									,'data':response.data.graph_data1
									,'backgroundColor': color
									,borderColor: color
									,fill:true
									,borderWidth: 4
									,pointRadius:2
									,segment: {
      						  borderColor: ctx => skipped(ctx, 'rgb(0,0,0,0.2)') ,
      						  borderDash: ctx => skipped(ctx, [6, 6]),
      						}
									,spanGaps: true
								})
								color = 'rgba('+(~~(256 * Math.random()))+','+(~~(256 * Math.random()))+','+ (~~(256 * Math.random()))+', 0.3)'
								datasets.push({
									'label':response.data.glabel2
									,'data':response.data.graph_data2
									,'backgroundColor': color
									,borderColor: color
									,fill:true
									,borderWidth: 2
									,pointRadius:1
									,segment: {
      						  borderColor: ctx => skipped(ctx, 'rgb(0,0,0,0.2)') ,
      						  borderDash: ctx => skipped(ctx, [6, 6]),
      						}
									,spanGaps: true
								})
							}else if(gtype.value==='all'){
								color = 'rgba('+(~~(256 * Math.random()))+','+(~~(256 * Math.random()))+','+ (~~(256 * Math.random()))+', 1)'
								datasets.push({
									'label':response.data.glabel1
									,'data':response.data.graph_data1
									, pointRadius:1
									,'backgroundColor': color
									,borderColor: color
									,borderWidth: 2
									,segment: {
      						  borderColor: ctx => skipped(ctx, 'rgb(0,0,0,0.2)') ,
      						  borderDash: ctx => skipped(ctx, [6, 6]),
      						}
									,spanGaps: true
									,fill: fill_sts
								})
							}else if(gtype.value==='12M'){
								color = 'rgba('+(~~(256 * Math.random()))+','+(~~(256 * Math.random()))+','+ (~~(256 * Math.random()))+', 1)'
								datasets.push({
									'label':response.data.glabel1
									,'data':response.data.graph_data1
									,'backgroundColor': color
									,borderColor: color
									,fill:fill_sts
									,borderWidth: 4
									,pointRadius:2
									,segment: {
      						  borderColor: ctx => skipped(ctx, 'rgb(0,0,0,0.2)') ,
      						  borderDash: ctx => skipped(ctx, [6, 6]),
      						}
									,spanGaps: true
								})
							}
							graph_title.value = response.data.graph_title
							graph_subtitle.value = response.data.subtitle
							create_graph(document.getElementById('myChart'))
						})
						.catch((error) => {
							console_log(`get_max_data ERROR:${error}`)
						})
						.finally(()=>{
						})
				}
				const get_volume_data = () =>{
					console_log("start get_volume_data")
					const form_data = new FormData()
					form_data.append(`shu`, shu.value)
					form_data.append(`gtype`, gtype.value)
					axios
						.post("ajax_get_volume_log.php",form_data, {headers: {'Content-Type': 'multipart/form-data'}})
						.then((response) => {
							console_log(response.data)
							kintore_log.value = response.data.kintore_log
							labels = response.data.labels
							datasets = []
							let color
							if(gtype.value==='year'){
								color1 = 'rgba('+(~~(256 * Math.random()))+','+(~~(256 * Math.random()))+','+ (~~(256 * Math.random()))	//max
								color3 = 'rgba('+(~~(256 * Math.random()))+','+(~~(256 * Math.random()))+','+ (~~(256 * Math.random()))	//max
								color2 = 'rgba('+(~~(256 * Math.random()))+','+(~~(256 * Math.random()))+','+ (~~(256 * Math.random()))	//volume
								color4 = 'rgba('+(~~(256 * Math.random()))+','+(~~(256 * Math.random()))+','+ (~~(256 * Math.random()))	//volume
								opacity1 = ', 1)'
								opacity2 = ', 0.6)'
								datasets.push({//去年のトレ量マックス
									'label':response.data.glabel2 + 'Mx'
									,'data':response.data.graph_data_max2
									,'backgroundColor':  color1 + opacity2
									,borderColor: color1 + opacity2
									,fill:true
									//,stepped: 'middle'
									,borderWidth: 2
									, pointRadius:1
									//,type:'bar'
									,hidden: true
								})
								datasets.push({//去年のトレ量
									'label':response.data.glabel2 + 'TTL'
									,'data':response.data.graph_data_total2
									,'backgroundColor':  color2 + opacity2
									,borderColor:  color2 + opacity2
									,fill:true
									,stepped: 'middle'
									,borderWidth: 2
									, pointRadius:1
									//,type:'bar'
								})
								color = 'rgba('+(~~(256 * Math.random()))+','+(~~(256 * Math.random()))+','+ (~~(256 * Math.random()))+', 1)'
								datasets.push({
									'label':response.data.glabel1 + 'Mx'
									,'data':response.data.graph_data_max1
									,'backgroundColor':  color3 + opacity1
									,borderColor:  color3 + opacity1
									,fill:true
									//,stepped: 'middle'
									,borderWidth: 4
									, pointRadius:2
									//,type:'bar'
									,hidden: true
								})
								datasets.push({
									'label':response.data.glabel1 + 'TTL'
									,'data':response.data.graph_data_total1
									,'backgroundColor':  color4 + opacity1
									,borderColor:  color4 + opacity1
									,fill:true
									,stepped: 'middle'
									,borderWidth: 4
									, pointRadius:2
									//,type:'bar'
								})
							}else if(gtype.value==='all'){
								color = 'rgba('+(~~(256 * Math.random()))+','+(~~(256 * Math.random()))+','+ (~~(256 * Math.random()))+', 1)'
								datasets.push({
									'label':response.data.glabel1 + 'Mx'
									,'data':response.data.graph_data_max1
									, pointRadius:1
									,'backgroundColor': color
									,borderColor: color
									,borderWidth: 2
								})
								datasets.push({
									'label':response.data.glabel1 + 'TTL'
									,'data':response.data.graph_data_total1
									,'backgroundColor': color
									,borderColor: color
									//,fill:true
									,borderWidth: 2
									//, pointRadius:1
									,type:'bar'
								})

							}else if(gtype.value==='12M'){
								color1 = 'rgba('+(~~(256 * Math.random()))+','+(~~(256 * Math.random()))+','+ (~~(256 * Math.random()))	//max
								color3 = 'rgba('+(~~(256 * Math.random()))+','+(~~(256 * Math.random()))+','+ (~~(256 * Math.random()))	//max
								color2 = 'rgba('+(~~(256 * Math.random()))+','+(~~(256 * Math.random()))+','+ (~~(256 * Math.random()))	//volume
								color4 = 'rgba('+(~~(256 * Math.random()))+','+(~~(256 * Math.random()))+','+ (~~(256 * Math.random()))	//volume
								opacity1 = ', 1)'
								opacity2 = ', 0.6)'
								/*datasets.push({//去年のトレ量マックス
									'label':response.data.glabel2 + 'Mx'
									,'data':response.data.graph_data_max2
									,'backgroundColor':  color1 + opacity2
									,borderColor: color1 + opacity2
									,fill:true
									//,stepped: 'middle'
									,borderWidth: 2
									, pointRadius:1
									//,type:'bar'
									,hidden: true
								})
								datasets.push({//去年のトレ量
									'label':response.data.glabel2 + 'TTL'
									,'data':response.data.graph_data_total2
									,'backgroundColor':  color2 + opacity2
									,borderColor:  color2 + opacity2
									,fill:true
									,stepped: 'middle'
									,borderWidth: 2
									, pointRadius:1
									//,type:'bar'
								})*/
								//color = 'rgba('+(~~(256 * Math.random()))+','+(~~(256 * Math.random()))+','+ (~~(256 * Math.random()))+', 1)'
								datasets.push({
									'label':response.data.glabel1 + 'Mx'
									,'data':response.data.graph_data_max1
									,'backgroundColor':  color3 + opacity1
									,borderColor:  color3 + opacity1
									,fill:true
									//,stepped: 'middle'
									,borderWidth: 4
									, pointRadius:2
									//,type:'bar'
									,hidden: true
								})
								datasets.push({
									'label':response.data.glabel1 + 'TTL'
									,'data':response.data.graph_data_total1
									,'backgroundColor':  color4 + opacity1
									,borderColor:  color4 + opacity1
									,fill:true
									,stepped: 'middle'
									,borderWidth: 4
									, pointRadius:2
									//,type:'bar'
								})
							}
							graph_title.value = response.data.graph_title
							graph_subtitle.value = response.data.subtitle
							create_graph(document.getElementById('myChart'))
						})
						.catch((error) => {
							console_log(`get_volume_data ERROR:${error}`)
						})
						.finally(()=>{
						})
				}
				const get_growth_data = () =>{
					console_log("start get_growth_data")
					const form_data = new FormData()
					form_data.append(`shu`, shu.value)
					axios
						.post("ajax_get_growth_log.php",form_data, {headers: {'Content-Type': 'multipart/form-data'}})
						.then((response) => {
							console_log(response.data)
							kintore_log.value = response.data.kintore_log
							labels = response.data.labels
							datasets = []
							color = 'rgba('+(~~(256 * Math.random()))+','+(~~(256 * Math.random()))+','+ (~~(256 * Math.random()))+', 0.5)'
							datasets.push({
								'label':response.data.glabel1
								,'data':response.data.graph_data1
								, pointRadius:1
								,'backgroundColor': color
								,borderColor: color
								,borderWidth: 2
								,type:'bar'
								,yAxisID:"y"
							})
							color = 'rgba('+(~~(256 * Math.random()))+','+(~~(256 * Math.random()))+','+ (~~(256 * Math.random()))+', 1)'
							datasets.push({
								'label':response.data.glabel2
								,'data':response.data.graph_data2
								, pointRadius:1
								,'backgroundColor': color
								,borderColor: color
								,borderWidth: 2
								//,type:'bar'
								,yAxisID:"y2"
							})
							graph_title.value = response.data.graph_title
							graph_subtitle.value = response.data.subtitle
							create_graph(document.getElementById('myChart'))
						})
						.catch((error) => {
							console_log(`get_growth_data ERROR:${error}`)
						})
						.finally(()=>{
						})
				}

				var graph_obj
				const create_graph = (ctx) =>{
					console_log("create_graph : graph_data")
					
					const graph_data = {
						labels    : labels
						,datasets : datasets
					}
				
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
								,y2: {
									stacked: false,
									position: "right",
									grid: {
										drawOnChartArea: false,
        					},
									display:(g_shu.value==='growth')?true:false,
									/*
									title:{
										display:true,
										text:'Kg'
									}*/
								}

							}
						}
					})      
				}

				const mokuhyou_type = ref('kg')	// or par
				const mokuhyou_par = ref()
				const mokuhyou_kg = ref()
				const taijuu = ref()
				const mokuhyou_disp = computed(()=>{
					if(mokuhyou_kg.value==0){
						return '目標設定へ'
					}
					if(mokuhyou_type.value==='kg'){
						return `目標：${Math.round(mokuhyou_kg.value)} kg`
					}else if(mokuhyou_type.value==='par'){
						mokuhyou_kg.value = taijuu.value * mokuhyou_par.value / 100
						return `目標：体重比 ${Math.round(mokuhyou_par.value)} %(${Math.round(mokuhyou_kg.value)}kg)`
					}else{
						return '目標設定へ'
					}
				})

				watch([mokuhyou_type,mokuhyou_par,mokuhyou_kg,taijuu],()=>{
					if(mokuhyou_type.value==='kg'){
						mokuhyou_par.value = (Number(mokuhyou_kg.value)/Number(taijuu.value)*Number(100))
					}else if(mokuhyou_type.value==='par'){
						mokuhyou_kg.value = Number(taijuu.value) * Number(mokuhyou_par.value) / Number(100)
						//mokuhyou_kg.value = Math.round(Number(mokuhyou_kg.value))
					}
					//create_graph(document.getElementById('myChart'))
					
				})

				const set_mokuhyou = () =>{
					console_log("start set_mokuhyou")
					const form_data = new FormData()
					form_data.append(`shu`, shu.value)
					form_data.append(`mokuhyou_type`, mokuhyou_type.value)
					if(mokuhyou_type.value==='kg'){
						form_data.append(`mokuhyou`, mokuhyou_kg.value)
					}else if(mokuhyou_type.value==='par'){
						form_data.append(`mokuhyou`, mokuhyou_par.value)
					}
					axios
						.post("ajax_ms_training_upd.php",form_data, {headers: {'Content-Type': 'multipart/form-data'}})
						.then((response) => {
							console_log(response.data)
							get_max_data()
							//create_graph(document.getElementById('myChart'))
						})
						.catch((error) => {
							console_log(`set_mokuhyou ERROR:${error}`)
						})
						.finally(()=>{
						})
				}

				onMounted(() => {
					console_log('onMounted')
					get_max_data()
				})
				return{
					kintore_log,
					graph_title,
					graph_subtitle,
					g_shu,
					gtype,
					mokuhyou_type,
					mokuhyou_par,
					mokuhyou_kg,
					taijuu,
					mokuhyou_disp,
					set_mokuhyou,
				}
			}
		}).mount('#app');
	</script>

</BODY>
</HTML>
