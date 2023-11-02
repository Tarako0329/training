<?php
	// 設定ファイルインクルード【開発中】
	require "config.php";
	//require "functions.php";
	require "edit_wt.php"; 		//ウェイト記録画面
	require "edit_usanso.php"; 	//有酸素系記録画面
	require "edit_taisosiki.php"; 	//体組織記録画面

	if(isset($_SESSION['USER_ID'])){
		$id = $_SESSION['USER_ID'];
		decho ("session:".$id);
	}else if (check_auto_login($_COOKIE['token'])==0) {
		$id = $_SESSION['USER_ID'];
		decho ("クッキー:".$id);
	}else{
		header("HTTP/1.1 301 Moved Permanently");
		header("Location: index.php");
	}
	$msg="";
	if(isset($_GET["msg"])==="error"){
		$msg=$_GET["msg"];
	}

	$now = date('Y-m-d');
	$week = [
	  '日', //0
	  '月', //1
	  '火', //2
	  '水', //3
	  '木', //4
	  '金', //5
	  '土', //6
	];
	if($_POST["btn"] == "ユーザー登録"){
		$id = ($_POST["id2"]);
		$pass = passEx($_POST["pass2"],$id);
		$sql = "insert into users values (?,?,?,?);";
		$stmt = $pdo_h->query("LOCK TABLES users WRITE");
		$stmt = $pdo_h->prepare($sql);
		$stmt->bindValue(1, $id, PDO::PARAM_STR);
		$stmt->bindValue(2, $pass, PDO::PARAM_STR);
		$stmt->bindValue(3, rot13encrypt($fname), PDO::PARAM_STR);
		$stmt->bindValue(4, $sex, PDO::PARAM_STR);
		$stmt->execute();
		$stmt = $pdo_h->query("UNLOCK TABLES");
		$user_name = $fname;
	}else{
		//ユーザー確認
		unset($sql);

		$sql = "select * from users where ((id)=?)";
		$stmt = $pdo_h->prepare( $sql );
		$stmt->bindValue(1, $id, PDO::PARAM_STR);
		$stmt->execute();
		$row_cnt = $stmt->rowCount();
		$row = $stmt->fetchAll(PDO::FETCH_ASSOC);

		if($row_cnt==0){
			echo "<P>ＩＤ 又はパスワードが間違っています。</P>";//.$id.$pass;
			?><a href="index.php"> 戻る</a><?php
			exit();
		}

		$user_name = rot13decrypt($row["name"]);
		//echo "ログインＯＫ<BR>";
	}

	//履歴取得
	$sql = "select *,replace(ymd,'-','') as ymd2,SUM(weight*rep*sets) OVER (PARTITION BY id,shu,ymd) as total,RANK() OVER(PARTITION BY id,ymd,shu order by jun ) as setjun from tr_log where id = ? and ymd >= ? order by ymd desc,jun ";
	$result = $pdo_h->prepare( $sql );
	$result->bindValue(1, $id, PDO::PARAM_STR);
	$result->bindValue(2, date("Y-m-d",strtotime("-13 month")), PDO::PARAM_STR);
	$result->execute();
	$dataset = $result->fetchAll(PDO::FETCH_ASSOC);
	$kintore_log = json_encode($dataset, JSON_UNESCAPED_UNICODE);
	$result = null;
	$dataset = null;

	//種目の取得
	//$sql = "select max(ymd) as ymd,jun,shu from tr_log where id in (?,'list') and typ = '0' group by shu order by max(ymd) desc,jun desc ";
	$sql = "select shu,max(insdatetime) as sort from tr_log where id in (?,'list') and typ = '0' group by shu order by sort desc";
	$result = $pdo_h->prepare( $sql );
	$result->bindValue(1, $id, PDO::PARAM_STR);
	$result->execute();
	$dataset = $result->fetchAll(PDO::FETCH_ASSOC);
	$shumoku_wt_list = json_encode($dataset, JSON_UNESCAPED_UNICODE);
	$result = null;
	$dataset = null;

?>

<HTML>
	<HEAD>
		<?php
		require "header.php";
		?>
		<TITLE>肉体改造ネットワーク</TITLE>
	</HEAD>
	
	<BODY>
		<div id='logger'>
			<header class='headerArea'>
			ようこそ <?php echo $user_name;?> さん
			</header>
			<main class='container-fluid'>
				<template v-for='(list,index) in log_edit' :key='list.ymd+list.jun'>
					<div v-if='index==0 || (index!==0 && list.ymd !== log_edit[index-1].ymd)' class='row ymd'>{{list.ymd}}</div>
					<div class='accordion-item'>
						<div v-if='index==0 || (index!==0 && list.shu !== log_edit[index-1].shu)' class='row shu accordion-header'>
							<button type='button' class='accordion-button collapsed' data-bs-toggle='collapse' :data-bs-target='`#collapseOne${list.ymd2}${list.shu}`' aria-expanded='false' aria-controls='collapseOne'>
								{{list.shu}} {{Number(list.total).toLocaleString()}}kg
							</button>
						</div>
						<div :id='`collapseOne${list.ymd2}${list.shu}`' class='accordion-collapse collapse' data-bs-parent='#accordionExample'>
							<div class='row lst accordion-body'>
								<div class='col-1'>{{list.setjun}}</div>
								<div class='col-2 text-end' style='padding:0;'>{{list.weight}}kg</div>
								<div class='col-2' style='padding-right:0;'>{{list.rep}}({{list.rep2}})回</div>
								<div class='col-2' style='padding-right:0;'>{{list.sets}}sets</div>
								<div class='col-5' style='padding:0;'>{{list.memo}}</div>
							</div>
						</div>
					</div>
				</template>
			</main>
			<footer class="footerArea">
			<ul id="menu">
		  	<li><a href="#" id="taisosiki">体組織</a></li>
		  	<li><a href="#" id="running">有酸素系</a></li>
			  <li><a href="#" data-bs-toggle='modal' data-bs-target='#edit_wt'>ウェイト</a></li>
			</ul>
			</footer>
			<!--↑footerArea -->
			<!--↓体組織系記録エリア-->
			<div class="edit" id="taisosiki-edit" style="height:350px;position:fixed;bottom:55px;display:none;">
			<?php
			//edit_taisosiki($id,"0",$enow);
			?>
			</div>

			<!--↓有酸素系記録エリア-->
			<div class="edit" id="usanso-edit" style="height:370px;position:fixed;bottom:55px;display:none;">
			<?php
			//edit_usanso($id,"0",$enow);
			?>
			</div>

			<!--↓ウェイト記録エリア-->
			<div class='modal fade' id='edit_wt' tabindex='-1' role='dialog' aria-labelledby='basicModal' aria-hidden='true'>
				<div class='modal-dialog  modal-dialog-centered'>
					<div class='modal-content edit' style=''>
						<form method = 'post' action='logins_sql.php'>
							<div class='modal-header'>
	        			<h5 class="modal-title">トレーニング記録</h5>
  	      			<button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
							</div>
							<div class='modal-body container'>
								<div class='row' style='margin:1px 20px;'>
									<label for='ymd' class="form-label" style='padding-left:0;margin-bottom:1px;'>日付</label>
									<input type='date' class="form-control" id='ymd' name='ymd' value="<?php echo $now?>">
								</div>
								<div class='row' style='margin:1px 20px;'>
									<label for='shu1' class="form-label" style='padding-left:0;margin-bottom:1px;'>種目</label>
									<select id='shu1' class="form-select" name='shu1'>
										<template v-for='(list,index) in shumoku_wt' :key='list.sort'>
											<option :value='`${list.shu}`'>{{list.shu}}</option>
										</template>
									</select>
								</div>
								<div class='row' style='margin:1px 20px;'>
									<label for='shu2' class="form-label" style='padding-left:0;margin-bottom:1px;'>種目追加</label>
									<input type='text' class="form-control" id='shu2' name='shu2' value="" placeholder='リストにない場合は手入力'>
								</div>
								<div class='row' style='margin:1px 10px;'>
									<label class="form-label" style='padding-left:0;margin-bottom:1px;'>重量</label>
								</div>

								<div class='row' style='margin:1px 0px 1px 20px;display:flexbox;'>
									<input type='number' :class="input_select[0]" readonly style='width:70px;padding:6 6;' @Click='setindex(0)' name='weight' :value="kiroku[0]"><span style='padding:8px 0 0 5px;width:40px;'>kg x</span>
									<input type='number' :class="input_select[1]" readonly style='width:50px;padding:6 6;' @Click='setindex(1)' name='rep' :value="kiroku[1]">
									<select class="form-select" style='width:50px;padding-left:5px;padding-right:15px;margin-left:5px;' name='tani'>
										<option value='0' selected>回</option>
										<option value='1'>秒</option>
									</select><span style='padding:8px 0 0 5px;width:15px;'>x</span>
									<input type='number' :class="input_select[2]" readonly style='width:40px;padding:6 6;' @Click='setindex(2)' name='sets' :value="kiroku[2]"><span style='padding:8px 0 0 5px;width:30px;'>SET</span>
								</div>
								<div class='row' style='margin:1px 20px;'>
									<label for='rep2' class="form-label" style='padding-left:0;margin-bottom:1px;'>内 有補助回数</label>
									<input type='number' :class="input_select[3]" readonly style='width:50px;' id='rep2' @Click='setindex(3)' name='rep2' :value="kiroku[3]">
								</div>

								<Transition>
								<template v-if='keybord_show'>
									<div class='row' style='margin:15px 20px 1px 20px;'>
										<button type='button' class='btn btn-primary' style='width:100' @click='keydown' value='-1'>≪</button>
										<button type='button' class='btn btn-primary' style='width:100' @click='keydown' value='1'>≫</button>
										<button type="button" class="btn btn-close btn-secondary input-btn" @click='keydown' value='99'></button>
									</div>
									</template>
									</Transition>
									<Transition>
									<template v-if='keybord_show'>
									<div class='row' style='margin:1px 20px;'>
										<button type='button' class='btn btn-primary input-btn' @click='keydown'>1</button>
										<button type='button' class='btn btn-primary input-btn' @click='keydown'>2</button>
										<button type='button' class='btn btn-primary input-btn' @click='keydown'>3</button>
										<button type='button' class='btn btn-primary input-btn' @click='keydown'>4</button>
										<button type='button' class='btn btn-primary input-btn' @click='keydown'>5</button>
										<button type='button' class='btn btn-primary input-btn' @click='keydown'>6</button>
									</div>
									</template>
									</Transition>
									<Transition>
									<template v-if='keybord_show'>
									<div class='row' style='margin:1px 20px 15px 20px;'>
										<button type='button' class='btn btn-primary input-btn' @click='keydown'>7</button>
										<button type='button' class='btn btn-primary input-btn' @click='keydown'>8</button>
										<button type='button' class='btn btn-primary input-btn' @click='keydown'>9</button>
										<button type='button' class='btn btn-primary input-btn' @click='keydown'>0</button>
										<button type='button' class='btn btn-primary input-btn' @click='keydown'>.</button>
										<button type='button' class='btn btn-secondary input-btn' @click='keydown'>C</button>
									</div>
								</template>
								</Transition>
								<div class='row' style='margin:1px 20px;'>
								</div>




								<div class='row' style='margin:1px 20px;'>
									<label for='memo' class="form-label" style='padding-left:0;margin-bottom:1px;'>SETメモ</label>
									<input type='text' class="form-control" id='memo' name='memo' value="">
								</div>
								<div class='row' style='margin:1px 20px;'>
									<label for='condition' class="form-label" style='padding-left:0;margin-bottom:1px;'>今日のコンディション</label>
									<input type='text' class="form-control" id='condition' name='condition' value='' placeholder='好調・寝不足・調整日など'>
								</div>
							</div>
							<div class='modal-footer'>
								<button type='button'  class="btn btn-secondary mbtn" data-bs-dismiss="modal" >閉じる</button>
								<button type='submit'  class="btn btn-primary mbtn" data-bs-dismiss="modal" >登録</button>
							</div>
							<INPUT type="hidden" name="typ" value="0">
						</form>
					</div>
				</div>
			</div>
		</div>
		
		<script>//Vus.js
			const { createApp, ref, onMounted, computed, VueCookies,watch } = Vue;
			createApp({
				setup(){
					const kintore_log = (<?php echo $kintore_log;?>)
					const week = (date) =>{
						const WeekChars = [ "(日)", "(月)", "(火)", "(水)", "(木)", "(金)", "(土)" ];
						let dObj = new Date( date );
						let wDay = dObj.getDay();
						//console_log("指定の日は、" + WeekChars[wDay] + "です。");
						return WeekChars[wDay]
					}
					const log_edit = computed(()=>{
						kintore_log.forEach((row)=>{
							row.ymd = row.ymd + ' ' + week(row.ymd)
						})
						return kintore_log
					})
					const shumoku_wt = ref(<?php echo $shumoku_wt_list;?>)
					const kiroku = ref([0,0,0,0])
					const kiroku_index = ref('')
					const setindex = (i) =>{
						kiroku_index.value = Number(i)
						keybord_show.value=true
					}
					let before_val = 0
					const keybord_show = ref(false)
					const keydown = (e) => {//電卓ボタンの処理
						//console_log(e.target.value,'lv3')
						if(e.target.innerHTML==="C"){
							kiroku.value[kiroku_index.value] = 0
							before_val=0
						}else if(e.target.value==='-1'){
							if(kiroku_index.value===0){return}
							kiroku_index.value = Number(kiroku_index.value) - 1
						}else if(e.target.value==='1'){
							if(kiroku_index.value===3){
								kiroku_index.value=''
								keybord_show.value=false
								return
							}
							kiroku_index.value = Number(kiroku_index.value) + 1
						}else if(e.target.value==='99'){
							kiroku_index.value=''
							keybord_show.value=false
						}else if(e.target.innerHTML==="."){
							if(kiroku.value[kiroku_index.value].toString().indexOf('.')!==-1){
								//小数点連続は無視
								return
							}
							before_val = "."
						}else{
							if(before_val==='.'){
								kiroku.value[kiroku_index.value] = Number(kiroku.value[kiroku_index.value].toString() + '.' + e.target.innerHTML.toString())
							}else{
								kiroku.value[kiroku_index.value] = Number(kiroku.value[kiroku_index.value].toString() + e.target.innerHTML.toString())
							}
							before_val=0
						}
					}
					const input_select = ref([['form-control',''],['form-control',''],['form-control',''],['form-control','']])
					watch([kiroku_index],()=>{
						console_log('watch')
						console_log(input_select.value)
						input_select.value.forEach((row,index)=>{
							if(index===kiroku_index.value){
								row[1] = 'selected'
							}else{
								row[1] = ''
							}
						})
					})
					onMounted(() => {
						console_log('onMounted')
					})
					return{
						//kintore_log,
						week,
						log_edit,
						shumoku_wt,
						keydown,
						kiroku,
						keybord_show,
						setindex,
						kiroku_index,
						input_select,
					}
				}
			}).mount('#logger');
		</script>
	</BODY>
</HTML>

<?php
	$pdo_h = null;
?>
