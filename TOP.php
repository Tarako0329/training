<?php
	// 設定ファイルインクルード【開発中】
	require "config.php";
	//require "functions.php";
	//require "edit_wt.php"; 		//ウェイト記録画面
	//require "edit_usanso.php"; 	//有酸素系記録画面
	//require "edit_taisosiki.php"; 	//体組織記録画面

	$time=date("YmdHis");

	if(isset($_SESSION['USER_ID'])){
		$id = $_SESSION['USER_ID'];
		decho ("session:".$id);
	}else if (check_auto_login($_COOKIE['token'])==0) {
		$id = $_SESSION['USER_ID'];
		decho ("クッキー:".$id);
	}else{
		header("HTTP/1.1 301 Moved Permanently");
		header("Location: index.php");
		exit();
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

		$user_name = ($row[0]["name"]);
		//echo "ログインＯＫ<BR>";
	//}

	//履歴取得
	$sql = "select log.*,con.condition,replace(log.ymd,'-','') as ymd2,log.ymd as ymd3,SUM(weight*rep*sets) OVER (PARTITION BY log.id,shu,log.ymd) as total,RANK() OVER(PARTITION BY log.id,log.ymd,shu order by jun ) as setjun 
	from tr_log as log left join tr_condition as con on log.id=con.id and log.ymd=con.ymd where log.id = ? and log.ymd >= ? order by log.ymd desc,jun ";
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

	$sql = "select shu,max(insdatetime) as sort from tr_log where id in (?,'list') and typ = '1' group by shu order by sort desc";
	$result = $pdo_h->prepare( $sql );
	$result->bindValue(1, $id, PDO::PARAM_STR);
	$result->execute();
	$dataset = $result->fetchAll(PDO::FETCH_ASSOC);
	$shumoku_us_list = json_encode($dataset, JSON_UNESCAPED_UNICODE);
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
				<div class='container d-flex hf_color'>
					<div class='pt-1' style='width:80%;'>ようこそ <?php echo $user_name;?> さん</div>
					<div class="nav-item dropdown text-end pt-1"  style='width:20%;'>
        	  <a class="nav-link " href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false">
        	    <i class="bi bi-list fs-1"></i>
        	  </a>
        	  <ul class="dropdown-menu">
							<li><a class="dropdown-item" href="#">種目別 ＭＡＸ一覧</a></li>
        	    <li><a class="dropdown-item" href="#">MAX更新時期のメニュー</a></li>
        	    <!--<li><hr class="dropdown-divider"></li>
        	    <li><a class="dropdown-item" href="#">Something else here</a></li>-->
        	  </ul>
        	</div>
				</div>
			</header>
			<main class='container'>
				<template v-for='(list,index) in log_edit' :key='list.ymd+list.jun'>
					<div v-if='index===0 || (index!==0 && list.ymd !== log_edit[index-1].ymd)' class='row ymd'>{{list.ymd}} {{list.condition}}</div><!--日付-->

					<div class='accordion-item' style='position:relative;'>
						<div v-if='list.setjun === 1' class='row shu accordion-header'>
							<button type='button' class='accordion-button collapsed' data-bs-toggle='collapse' :data-bs-target='`#collapseOne${list.ymd2}${list.shu}`' 
								aria-expanded='false' aria-controls='collapseOne' style='width: 80%;'>
								{{list.shu}} 
								<template v-if="list.typ==='0'">-total:{{Number(list.total).toLocaleString()}}kg</template>
							</button>
							<button type='button' class='icn-btn' @click='GoGrapho01(list.shu,0)' style=''>
								<i class='fa fa-line-chart' ></i>
							</button>
						</div>
						<div :id='`collapseOne${list.ymd2}${list.shu}`' class='accordion-collapse collapse' data-bs-parent='#accordionExample'>
							<div v-if="list.typ==='0'" class='row lst accordion-body'><!--ウェイト-->
								<div class='col-4' style='padding:0  0 6px;display:flex;'>
									<div style='width: 10%;'>{{list.setjun}}</div>
									<div class='text-end' style='width: 40%;padding:0;'>{{list.weight}}kg</div>
									<div class='text-end' style='width: 50%;padding-right:0;'>{{list.rep}}({{list.rep2}})回</div>
								</div>
								<div class='col-2' style='padding-right:0;'>{{list.sets}}sets</div>
								<div class='col-5' style='padding:0 0 0 10px;'>{{list.memo}}</div>
								<button type='button' class='icn-btn' style='' 
								@click='setUpdate(list.jun,list.ymd3,list.shu,list.weight,list.rep,list.sets,list.rep2,list.memo,list.typ)'
								data-bs-toggle='modal' data-bs-target='#edit_wt'>
									<i class='fa fa-edit'></i>
								</button>
							</div>
							<div v-if="list.typ==='1'" class='row lst accordion-body'><!--有酸素-->
								<div class='col-1'>{{list.setjun}}</div>
								<div class='col-2 text-end' style='padding-right:0;'>{{list.rep}}分</div>	
								<div class='col-2 text-end' style='padding:0;'>{{list.rep2}}ｍ</div>
								
								<div class='col-2' style='padding-right:0;'>{{list.cal}}kcal</div>
								<div class='col-5' style='padding:0 0 0 10px;'>{{list.memo}}</div>
								<button type='button' class='icn-btn' style='' 
								@click='setUpdate(list.jun,list.ymd3,list.shu,list.cal,list.rep,list.sets,list.rep2,list.memo,list.typ)'
								data-bs-toggle='modal' data-bs-target='#usanso'>
									<i class='fa fa-edit'></i>
								</button>
							</div>
						</div>
					</div>
				</template>
			</main>
			<footer class="footerArea">
				<div class='container d-flex hf_color p-0'>
					<ul id="menu">
					  <li><a href="#" data-bs-toggle='modal' data-bs-target='#taisosiki'>体組織</a></li>
					  <li><a href="#" data-bs-toggle='modal' data-bs-target='#usanso'>有酸素系</a></li>
					  <li><a href="#" data-bs-toggle='modal' data-bs-target='#edit_wt'>ウェイト</a></li>
					</ul>
				</div>
			</footer>
			<!--↑footerArea -->
			<!--↓体組織系記録モーダル-->
			<div class='modal fade' id='taisosiki' tabindex='-1' role='dialog' aria-labelledby='basicModal' aria-hidden='true'>
				<div class='modal-dialog  modal-dialog-centered'>
					<div class='modal-content edit' style=''>
						<form method = 'post' action='taisosiki_ins.php'>
							<div class='modal-header'>
	        			<h5 class="modal-title">体組織の記録</h5>
  	      			<button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
							</div>
							<div class='modal-body container'>
								<div class='row' style='margin:1px 20px;'>
									<label for='ymd' class="form-label" style='padding-left:0;margin-bottom:1px;'>日付</label>
									<input type='date' class="form-control form-control-sm" id='ymd' name='ymd' v-model="ymd" required='required'>
								</div>
								<div class='row' style='margin:1px 20px;'>
									<label for='shu2' class="form-label" style='padding-left:0;margin-bottom:1px;'>体重（KG）</label>
									<input type='Number' step="0.01" class="form-control form-control-sm" id='shu2' name='weight' required='required'>
								</div>
								<div class='row' style='margin:1px 20px;'>
									<label for='sibo' class="form-label" style='padding-left:0;margin-bottom:1px;'>体脂肪率（％）</label>
									<input type='Number' step="0.01" class="form-control form-control-sm" id='sibo' name='sibo'>
								</div>
								<div class='row' style='margin:1px 20px;'>
									<label for='yobi1' class="form-label" style='padding-left:0;margin-bottom:1px;'>予備１</label>
									<input type='text' class="form-control form-control-sm" id='yobi1' name='yobi1'>
								</div>
								<div class='row' style='margin:1px 20px;'>
									<label for='yobi2' class="form-label" style='padding-left:0;margin-bottom:1px;'>予備２</label>
									<input type='text' class="form-control form-control-sm" id='yobi2' name='yobi2'>
								</div>
								<div class='row' style='margin:1px 20px;'>
									<label for='memo' class="form-label" style='padding-left:0;margin-bottom:1px;'>今日のコンディション</label>
									<input type='text' class="form-control form-control-sm" id='memo' name='memo' value='' placeholder='空腹時・食後など'>
								</div>
							</div>
							<div class='modal-footer'>
								<button type='button' style='width:90px;' name='' class="btn btn-secondary mbtn" data-bs-dismiss="modal" >キャンセル</button>
								<!--<button type='submit' style='width:90px;' name='btn' value='w_rireki' class="btn btn-primary mbtn" data-bs-dismiss="modal" >履歴</button>-->
								<a href='graph02.php' style='width:90px;' class="btn btn-primary mbtn" >履歴</a>
								<button type='submit' style='width:90px;' name='btn' value='w_ins_bt' class="btn btn-primary mbtn" data-bs-dismiss="modal" >登録</button>
							</div>
							<input type='hidden' name='hyoji' value='1'>
							<input type='hidden' name='gtype' value='all'>
						</form>
					</div>
				</div>
			</div>

			<!--↓有酸素系記録モーダル-->
			<div class='modal fade' id='usanso' tabindex='-1' role='dialog' aria-labelledby='basicModal' aria-hidden='true'>
				<div class='modal-dialog  modal-dialog-centered'>
					<div class='modal-content edit' style=''>
						<form method = 'post' action='logInsUpd_sql.php'>
							<div class='modal-header'>
	        			<h5 class="modal-title">有酸素トレーニング</h5>
  	      			<button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
							</div>
							<div class='modal-body container'>
								<div v-if='keybord_show===false' class='row' style='margin:1px 20px;'>
									<label for='ymd' class="form-label" style='padding-left:0;margin-bottom:1px;'>日付</label>
									<input type='date' @focus='keydown' class="form-control form-control-sm" id='ymd' name='ymd' v-model="ymd" required='required'>
								</div>
								<div class='row' style='margin:1px 20px;'>
									<label for='shu1' class="form-label" style='padding-left:0;margin-bottom:1px;'>種目</label>
									<select id='shu1' @focus='keydown' class="form-select form-select-sm" name='shu1' v-model='shu'>
										<template v-for='(list,index) in shumoku_us' :key='list.sort'>
											<option :value='`${list.shu}`'>{{list.shu}}</option>
										</template>
									</select>
								</div>
								<div v-if='keybord_show===false' class='row' style='margin:1px 20px;'>
									<label for='shu2' class="form-label" style='padding-left:0;margin-bottom:1px;'>種目追加</label>
									<input type='text' @change='add_shumoku_wt' class="form-control form-control-sm" id='shu2' name='shu2' placeholder='リストにない場合は手入力'>
								</div>
								<div class='row' style='margin:1px 20px;'>
									<label for='rep' class="form-label" style='padding-left:0;margin-bottom:1px;'>時間(分)</label>
									<input type='text' @focus='keydown' class="form-control form-control-sm" id='rep' name='rep' :value='kiroku[1]'>
								</div>
								<div class='row' style='margin:1px 20px;'>
									<label for='rep2' class="form-label" style='padding-left:0;margin-bottom:1px;'>距離(ｍ)</label>
									<input type='text' @focus='keydown' class="form-control form-control-sm" id='rep2' name='rep2' :value='kiroku[3]'>
								</div>
								<div class='row' style='margin:1px 20px;'>
									<label for='cal' class="form-label" style='padding-left:0;margin-bottom:1px;'>カロリー：</label>
									<input type='text' @focus='keydown' class="form-control form-control-sm" id='cal' name='cal' :value='kiroku[0]'> <!--cal を weightで代用-->
								</div>
								<div class='row' style='margin:1px 20px;'>
									<label for='memo' class="form-label" style='padding-left:0;margin-bottom:1px;'>SETメモ</label>
									<input type='text' @focus='keydown' class="form-control form-control-sm" id='memo' name='memo' :value='memo'>
								</div>
								<div class='row' style='margin:1px 20px;'>
									<label for='condition' class="form-label" style='padding-left:0;margin-bottom:1px;'>今日のコンディション</label>
									<input type='text' @focus='keydown' class="form-control form-control-sm" id='condition' name='condition' value='' placeholder='好調・寝不足・調整日など'>
								</div>
							</div>
							<div class='modal-footer'>
								<template v-if='mBtnName[0]==="更新"'>
									<button type='button' class="btn btn-danger mbtn" style='width:60px;' data-bs-dismiss="modal" @click='delete_log(Num,motoymd)'>削除</button>
								</template>
								<button type='button'  class="btn btn-secondary mbtn" data-bs-dismiss="modal" @click='setCancel'>{{mBtnName[1]}}</button><!--キャンセル-->
								<input type='submit'  class="btn btn-primary mbtn" :value='mBtnName[0]'>{{}}<!--登録・更新-->
							</div>
							<INPUT type="hidden" name="typ" value="1">
							<INPUT type="hidden" name="NO" :value="Num">
							<INPUT type="hidden" name="motoYMD" :value="motoymd">
							<INPUT type="hidden" name="tani" value="2">
							<INPUT type="hidden" name="sets" value="1">
							<INPUT type="hidden" name="weight" value="0">
						</form>
					</div>
				</div>
			</div>

			<!--↓ウェイト記録モーダル-->
			<div class='modal fade' id='edit_wt' tabindex='-1' role='dialog' aria-labelledby='basicModal' aria-hidden='true'>
				<div class='modal-dialog  modal-dialog-centered'>
					<div class='modal-content edit' style=''>
						<form method = 'post' action='logInsUpd_sql.php' @submit.prevent='OnSubmit' id='wt'>
							<div class='modal-header'>
	        			<h5 class="modal-title">トレーニング記録</h5>
  	      			<button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
							</div>
							<div class='modal-body container'>
								<!--<Transition>-->
									<div v-show='keybord_show===false' class='row' style='margin:1px 20px;'>
										<label for='ymd' class="form-label" style='padding-left:0;margin-bottom:1px;'>日付</label>
										<input type='date' @focus='keydown' class="form-control form-control-sm" id='ymd' name='ymd' v-model="ymd" required='required'>
									</div>
								<!--</Transition>-->
								<div class='row' style='margin:1px 20px;'>
									<label for='shu1' class="form-label" style='padding-left:0;margin-bottom:1px;'>種目</label>
									<select id='shu1' @focus='keydown' class="form-select form-select-sm" name='shu1' v-model='shu'>
										<template v-for='(list,index) in shumoku_wt' :key='list.sort'>
											<option :value='`${list.shu}`'>{{list.shu}}</option>
										</template>
									</select>
								</div>
								<!--<Transition>-->
									<div v-show='keybord_show===false' class='row' style='margin:1px 20px;'>
										<label for='shu2' class="form-label" style='padding-left:0;margin-bottom:1px;'>種目追加</label>
										<input type='text' @change='add_shumoku_wt' class="form-control form-control-sm" id='shu2' name='shu2' placeholder='リストにない場合は手入力'>
									</div>
								<!--</Transition>-->
								<div class='row' style='margin:1px 10px;'>
									<label class="form-label" style='padding-left:0;margin-bottom:1px;'>重量</label>
								</div>
								<div class='row' style='margin:1px 0px 1px 20px;display:flexbox;'>
									<input type='number' :class="input_select[0]" readonly style='width:70px;padding:6 6;' @Click='setindex(0)' name='weight' :value="kiroku[0]" required='required'><span style='padding:8px 0 0 5px;width:40px;'>kg x</span>
									<input type='number' :class="input_select[1]" readonly style='width:50px;padding:6 6;' @Click='setindex(1)' name='rep' :value="kiroku[1]" required='required'>
									<select class="form-select form-select-sm" style='width:50px;padding-left:5px;padding-right:15px;margin-left:5px;' name='tani' required='required'>
										<option value='0' selected>回</option>
										<option value='1'>秒</option>
									</select><span style='padding:8px 0 0 5px;width:15px;'>x</span>
									<input type='number' :class="input_select[2]" readonly style='width:40px;padding:6 6;' @Click='setindex(2)' name='sets' :value="kiroku[2]" required='required'><span style='padding:8px 0 0 5px;width:30px;'>SET</span>
								</div>
								<div class='row' style='margin:1px 20px;'>
									<label for='rep2' class="form-label" style='padding-left:0;margin-bottom:1px;'>内 有補助回数</label>
									<input type='number' :class="input_select[3]" readonly style='width:50px;' id='rep2' @Click='setindex(3)' name='rep2' :value="kiroku[3]">
								</div>
								<!--<Transition>-->
									<template v-if='keybord_show'>
										<div class='row' style='margin:15px 20px 1px 20px;'>
											<button type='button' class='btn btn-primary input-btn' @click='keydown'>1</button>
											<button type='button' class='btn btn-primary input-btn' @click='keydown'>2</button>
											<button type='button' class='btn btn-primary input-btn' @click='keydown'>3</button>
										</div>
									</template>
								<!--</Transition>-->
								<!--<Transition>-->
									<template v-if='keybord_show'>
										<div class='row' style='margin:1px 20px 1px 20px;'>
											<button type='button' class='btn btn-primary input-btn' @click='keydown'>4</button>
											<button type='button' class='btn btn-primary input-btn' @click='keydown'>5</button>
											<button type='button' class='btn btn-primary input-btn' @click='keydown'>6</button>
										</div>
									</template>
								<!--</Transition>-->
								<!--<Transition>-->
									<template v-if='keybord_show'>
										<div class='row' style='margin:1px 20px 1px 20px;'>
											<button type='button' class='btn btn-primary input-btn' @click='keydown'>7</button>
											<button type='button' class='btn btn-primary input-btn' @click='keydown'>8</button>
											<button type='button' class='btn btn-primary input-btn' @click='keydown'>9</button>
										</div>
									</template>
								<!--</Transition>-->
								<!--<Transition>-->
									<template v-if='keybord_show'>
										<div class='row' style='margin:1px 20px 1px 20px;'>
											<button type='button' class='btn btn-primary input-btn' @click='keydown'>0</button>
											<button type='button' class='btn btn-primary input-btn' @click='keydown'>.</button>
											<button type='button' class='btn btn-primary input-btn' @click='keydown'>C</button>
										</div>
									</template>
								<!--</Transition>-->
								<!--<Transition>-->
									<template v-if='keybord_show'>
										<div class='row' style='margin:1px 20px 1px 20px;'>
											<button type='button' class='btn btn-primary' style='height:60px;width:50%' @click='keydown' value='-1'>≪</button>
											<button type='button' class='btn btn-primary' style='height:60px;width:50%' @click='keydown' value='1'>≫</button>
										</div>
									</template>
								<!--</Transition>-->

								<div class='row' style='margin:1px 20px;'>
								</div>

								<div class='row' style='margin:1px 20px;'>
									<label for='memo' class="form-label" style='padding-left:0;margin-bottom:1px;'>SETメモ</label>
									<input type='text' @focus='keydown' class="form-control form-control-sm" id='memo' name='memo' :value="memo">
								</div>
								<div class='row' style='margin:1px 20px;'>
									<label for='condition' class="form-label" style='padding-left:0;margin-bottom:1px;'>今日のコンディション</label>
									<input type='text' @focus='keydown' class="form-control form-control-sm" id='condition' name='condition' value='' placeholder='好調・寝不足・調整日など'>
								</div>
							</div>
							<div class='modal-footer'>
								<template v-if='mBtnName[0]==="更新"'>
									<button type='button' class="btn btn-danger mbtn" style='width:60px;' data-bs-dismiss="modal" @click='delete_log(Num,motoymd)'>削除</button>
								</template>
								<button type='button'  class="btn btn-secondary mbtn" data-bs-dismiss="modal" @click='setCancel'>{{mBtnName[1]}}</button><!--キャンセル-->
								<input type='submit'  class="btn btn-primary mbtn" :value='mBtnName[0]'>{{}}<!--登録・更新-->
							</div>
							<INPUT type="hidden" name="typ" value="0">
							<INPUT type="hidden" name="NO" :value="Num">
							<INPUT type="hidden" name="motoYMD" :value="motoymd">
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
					const id = ref('<?php echo $id;?>')
					//const pass = ref('<?php //echo $pass;?>')
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
					const shumoku_us = ref(<?php echo $shumoku_us_list;?>)
					const kiroku = ref(['','','',0])
					const kiroku_index = ref('')
					const keybord_show = ref(false)
					const setindex = (i) =>{
						console_log(`setindex:${i}`)
						kiroku_index.value = Number(i)
						keybord_show.value=true
					}
					let before_val = '-'
					const keydown = (e) => {//電卓ボタンの処理
						console_log('target.value=' + e.target.value)
						console_log('target.innerHTML=' + e.target.innerHTML)
						if(e.target.innerHTML==="C"){
							kiroku.value[kiroku_index.value] = 0
							before_val='-'
							console_log('c')
						}else if(e.target.value==='-1'){
							console_log('<')
							if(kiroku_index.value===0){return}
							kiroku_index.value = Number(kiroku_index.value) - 1
						}else if(e.target.value==='1'){
							console_log('>')
							if(kiroku_index.value===3){
								kiroku_index.value=''
								keybord_show.value=false
								return
							}
							kiroku_index.value = Number(kiroku_index.value) + 1
						}else if(e.target.value==='99'){
							console_log('99')
							kiroku_index.value=''
							keybord_show.value=false
						}else if(e.target.innerHTML==="."){
							console_log('.')
							if(kiroku.value[kiroku_index.value].toString().indexOf('.')!==-1){
								//小数点連続は無視
								return
							}
							before_val = "."
						}else if((Number(e.target.innerHTML) >= 0 && Number(e.target.innerHTML)<=9 && e.target.innerHTML !== '') || before_val==='.'){
							console_log('key input')
							if(kiroku.value[kiroku_index.value]==''){
								kiroku.value[kiroku_index.value] = e.target.innerHTML.toString()
							}else if(before_val==='.'){
								kiroku.value[kiroku_index.value] = Number(kiroku.value[kiroku_index.value].toString() + '.' + e.target.innerHTML.toString())
							}else{
								kiroku.value[kiroku_index.value] = Number(kiroku.value[kiroku_index.value].toString() + e.target.innerHTML.toString())
							}
							before_val='-'
						}else{
							console_log('else')
							kiroku_index.value=''
							keybord_show.value=false
						}
					}
					const input_select = ref([['form-control','form-control-sm',''],['form-control','form-control-sm',''],['form-control','form-control-sm',''],['form-control','form-control-sm','']])
					watch([kiroku_index],()=>{
						console_log('watch kiroku_index')
						console_log(input_select.value)
						input_select.value.forEach((row,index)=>{
							if(index===kiroku_index.value){
								row[2] = 'selected'
							}else{
								row[2] = ''
							}
						})
					})
					const mBtnName = ref(['登録','閉じる'])
					const Num = ref('')
					const ymd = ref('<?php echo $now?>')
					const motoymd = ref('')
					const shu = ref(shumoku_wt.value[0]["shu"])
					//const shu2 = ref('')
					const memo = ref('')
					const setUpdate = (NO,YMD,SHU,wt,rep,set,rep2,MEMO,typ) =>{
						console_log('setUpdate start')
						Num.value = NO
						ymd.value = YMD
						motoymd.value = YMD
						shu.value = SHU
						kiroku.value[0]=wt
						kiroku.value[1]=rep
						kiroku.value[2]=set
						kiroku.value[3]=rep2
						memo.value=MEMO
						mBtnName.value[0] = '更新'
						mBtnName.value[1] = 'キャンセル'
					}
					const setCancel = () =>{
						console_log('setCancel start')
						Num.value = 0
						ymd.value = '<?php echo $now?>'
						shu.value = ''
						kiroku.value[0]=0
						kiroku.value[1]=0
						kiroku.value[2]=0
						kiroku.value[3]=0
						memo.value=''
						mBtnName.value[0] = '登録'
						mBtnName.value[1] = '閉じる'
						keybord_show.value=false
						kiroku_index.value=''
					}
					const delete_log = (NO,YMD) =>{
						console_log('delete_log start')
						if(confirm('削除してよいですか？')===false){
							return
						}
						let form = document.createElement('form');
    				let numbers = document.createElement('input');
						let date = document.createElement('input');

    				form.method = 'POST';
    				form.action = 'logdel_sql.php';
						
    				numbers.type = 'hidden'; //入力フォームが表示されないように
    				numbers.name = 'k_jun';
    				numbers.value = NO;
						
    				date.type = 'hidden'; //入力フォームが表示されないように
    				date.name = 'k_ymd';
    				date.value = YMD;

						form.appendChild(numbers);
						form.appendChild(date);
    				document.body.appendChild(form);
						
    				form.submit();						
					}
					const GoGrapho01 = (SHURUI,HYOUJI) =>{
						console_log('GoGrapho01 start')
						let form = document.createElement('form');
    				let shu = document.createElement('input');
						let hyoji = document.createElement('input');
						let gtype = document.createElement('input');

    				form.method = 'POST';
    				form.action = 'graph01.php';
						
    				shu.type = 'hidden'; //入力フォームが表示されないように
    				shu.name = 'shu';
    				shu.value = SHURUI;
						
    				hyoji.type = 'hidden'; //入力フォームが表示されないように
    				hyoji.name = 'hyoji';
    				hyoji.value = HYOUJI;

    				gtype.type = 'hidden'; //入力フォームが表示されないように
    				gtype.name = 'gtype';
    				gtype.value = 'year';

						form.appendChild(shu);
						form.appendChild(hyoji);
						form.appendChild(gtype);
    				document.body.appendChild(form);
						
    				form.submit();						
					}
					const add_shumoku_wt = (e) =>{
						console_log(`add_shumoku_wt e:${e.target.value}`)
						shumoku_wt.value.unshift({shu:e.target.value,sort:''})
						shu.value = e.target.value
					}
					const OnSubmit = (e) =>{
						console_log(`OnSubmit e:${e.target.id}`)
						if(e.target.id==='wt'){
							keybord_show.value=false
							if(!ymd.value){
								alert('日付が未入力です')
								return
							}else if(!kiroku.value[0]){
								alert('重量が未入力です')
								return
							}else if(!kiroku.value[1]){
								alert('回数が未入力です')
								return
							}else if(!kiroku.value[2]){
								alert('セット数が未入力です')
								return
							}else if(!kiroku.value[0]){
								kiroku.value[0]=0
							}
						}
						e.target.submit()
					}
						
					onMounted(() => {
						console_log('onMounted')
					})
					return{
						//kintore_log,
						week,
						log_edit,
						shumoku_wt,
						shumoku_us,
						keydown,
						kiroku,
						keybord_show,
						setindex,
						kiroku_index,
						input_select,
						id,
						//pass,
						mBtnName,
						Num,
						ymd,
						motoymd,
						shu,
						memo,
						setUpdate,
						setCancel,
						delete_log,
						GoGrapho01,
						//shu2,
						add_shumoku_wt,
						OnSubmit,
					}
				}
			}).mount('#logger');
			// Enterキーが押された時にSubmitされるのを抑制する
			document.getElementById('logger').onkeypress = (e) => {
				// form1に入力されたキーを取得
				const key = e.keyCode || e.charCode || 0;
				// 13はEnterキーのキーコード
				if (key == 13) {
					// アクションを行わない
					console_log('enter 無効');
					e.preventDefault();
				}
			}

		</script>
	</BODY>
</HTML>

<?php
	$pdo_h = null;
?>
