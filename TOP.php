<?php
	// 設定ファイルインクルード【開発中】
	require "config.php";

	//$time=date("YmdHis");

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
	$shu="";
	if(isset($_GET["msg"])==="error"){
		$msg=$_GET["msg"];
	}
	$shu=!empty($_GET["shu"])?$_GET["shu"]:"%";

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
?>
<!DOCTYPE html>
<HTML>
	<HEAD>
		<?php
		require "header.php";
		?>
		<TITLE>肉体改造ネットワーク</TITLE>
	</HEAD>
	
	<BODY >
		<div id='logger'>
			<header class='headerArea'>
				<div class='container d-flex hf_color position-relative'>
					<div class='pt-1 ' style='width:80%;'>
						<div>
							<p class='mb-1'>ようこそ 
							<?php echo $user_name;?> さん</p>
						</div>
						<div class="toggle_button">
						  <input id="toggle" class="toggle_input" type='checkbox' v-model='disp_area' @click=''/>
						  <label for="toggle" class="toggle_label">
						</div>
					</div>
					<div class="nav-item dropdown position-absolute end-0 top-0"  style='width:40px;'>
        	  <a class="nav-link " href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false">
        	    <i class="bi bi-list fs-1"></i>
        	  </a>
        	  <ul class="dropdown-menu">
							<li><a class="dropdown-item" href="#">ユーザー情報</a></li>
        	    <li><a class="dropdown-item" href="index.php?logoff=out">ログオフ</a></li>
        	  </ul>
        	</div>
					<div class="nav-item dropdown position-absolute start-50 top-50 translate-middle" style=''>
						
						<div class="input-group">
							<template v-if='filter==="%"'><span class="input-group-text" ><i class="bi bi-funnel"></i></span></template>
							<template v-else><button class="btn btn-primary" @click='()=>{filter="%"}'><i class="bi bi-funnel-fill"></i></button></template>
							<select  class="form-select form-select-sm" v-model='filter'>
							<option value='%'>フィルターオフ</option>
								<template v-for='(list,index) in shumoku' :key='list.sort'>
									<option :value='`${list.shu}`'>{{list.shu}}</option>
								</template>
							</select>
						</div>
					</div>
				</div>
			</header>
			<main class='container p-0' style='height:calc(100vh - 115px);'>
				<div class='row position-relative' style='height:100%;'>
					<div v-show='disp_area===false' class='col-12 col-md-7 col-lg-6 col-xl-5 ' style='height:100%;'>
						<div style='overflow-y:scroll;height:100%;width:100%;padding-bottom:170px;' id='tr_log_area'>
							<template v-for='(list,index) in log_edit' :key='list.ymd+list.jun+list.shu'>
								<div v-if='index===0 || (index!==0 && list.ymd !== log_edit[index-1].ymd)' class='row m-0' style='position:relative'><div class=' ymd'>{{list.ymd}} {{list.condition}}</div></div><!--日付-->
		
								<div class='accordion-item' style='position:relative;'>
									<div v-if='list.setjun === 1 || (list.shu+list.typ) !== (log_edit[index-1].shu+log_edit[index-1].typ)' class='row m-0 pb-0 shu accordion-header'>
										<button type='button' class='accordion-button collapsed' data-bs-toggle='collapse' :data-bs-target='`#collapseOne${list.ymd2}${list.shu}`' :id='`btn_collapseOne${list.ymd3}${list.shu}`'
											aria-expanded='false' aria-controls='collapseOne' style='width: 80%;'>
											{{list.shu}} 
											<template v-if="list.typ==='0'">-total:{{Number(list.total).toLocaleString()}}kg</template>
											<template v-if="list.typ==='2'">-Non Weight:{{Number(list.total).toLocaleString()}}回</template>
										</button>
										<a v-if="list.typ==='0'" :href='`graph_kintore.php?shu=${list.shu}&hyouji=0&gtype=all`' role='button' class='icn-btn text-center pt-1' >
											<i class='bi bi-graph-up-arrow' ></i>
										</a>
										<a v-if="list.typ==='2'" :href='`graph_kintore.php?shu=${list.shu}&hyouji=0&gtype=all`' role='button' class='icn-btn text-center pt-1' >
											<i class='bi bi-graph-up-arrow' ></i>
										</a>
										<a v-if="list.typ==='1'" :href='`graph_usanso.php?shu=${list.shu}&hyouji=0&gtype=all`' role='button' class='icn-btn text-center pt-1' >
											<i class='bi bi-graph-up-arrow' ></i>
										</a>
									</div>
									<div :id='`collapseOne${list.ymd2}${list.shu}`' class='accordion-collapse collapse' data-bs-parent='#accordionExample'>
										<div class='row m-0 lst accordion-body'>
											<div v-if="list.typ==='0'" class='col-12' style='padding:0  0 6px;display:flex;height:100%;'><!--ウェイト-->
												<div style='width: 20px;'>{{list.setjun}}</div>
												<div class='text-end' style='width: 70px;padding:0;'>{{list.weight}}kg</div>
												<div v-if="list.tani==='0'"      class='text-end' style='width: 60px;padding-right:0;'>{{list.rep}}({{list.rep2}})回</div>
												<div v-else-if="list.tani==='1'" class='text-end' style='width: 65px;padding-right:0;'>{{list.rep}}({{list.rep2}})秒</div>
												<div class='text-end' style='padding-right:0;width:50px;'>{{list.sets}}sets</div>
												<div class='' style='padding:0 0 0 10px;max-width:250px;width:calc(100vw - 240px);font-size:12px;word-wrap: break-word;word-break: break-all;margin-top:-5px;'>{{list.memo}}</div>
												<button type='button' class='icn-btn' style='' 
													@click='setUpdate(list.jun,list.ymd3,list.shu,list.weight,list.rep,list.sets,list.rep2,list.memo,list.typ,"edit_wt")'>
													<i class='bi bi-pencil'></i>
												</button>
											</div>
											<div v-if="list.typ==='2'" class='col-12' style='padding:0  0 6px;display:flex;height:100%;'><!--nonウェイト-->
												<div style='width: 20px;'>{{list.setjun}}</div>
												<div class='text-end' style='width: 70px;padding:0;'>自重</div>
												<div v-if="list.tani==='0'"       class='text-end' style='width: 60px;padding-right:0;'>{{list.rep}}({{list.rep2}})回</div>
												<div v-else-if="list.tani==='1'"  class='text-end' style='width: 65px;padding-right:0;'>{{list.rep}}({{list.rep2}})秒</div>
												<div class='text-end' style='padding-right:0;width:50px;'>{{list.sets}}sets</div>
												<div class='' style='padding:0 0 0 10px;max-width:250px;width:calc(100vw - 240px);font-size:12px;word-wrap: break-word;word-break: break-all;margin-top:-5px;'>{{list.memo}}</div>
												<button type='button' class='icn-btn' style='' 
													@click='setUpdate(list.jun,list.ymd3,list.shu,list.weight,list.rep,list.sets,list.rep2,list.memo,list.typ,"edit_wt")'>
													<i class='bi bi-pencil'></i>
												</button>
											</div>
											<div v-if="list.typ==='1'" class='col-12' style='padding:0  0 6px;display:flex;height:100%;'><!--有酸素-->
												<div style='width: 20px;'>{{list.setjun}}</div>
												<div class='text-end' style='width: 50px;padding-right:0;'>{{list.rep}}分</div>	
												<div class='text-end' style='width: 70px;padding:0;'>{{list.rep2}}ｍ</div>
		
												<div class='text-end' style='width: 70px;padding-right:0;'>{{list.cal}}kcal</div>
												<div class='' style='padding:0 0 0 10px;max-width:250px;width:calc(100vw - 240px);font-size:12px;word-wrap: break-word;word-break: break-all;margin-top:-5px;'>{{list.memo}}</div>
												<button type='button' class='icn-btn' style='' 
													@click='setUpdate(list.jun,list.ymd3,list.shu,list.cal,list.rep,list.sets,list.rep2,list.memo,list.typ,"usanso")'>
													<i class='bi bi-pencil'></i>
												</button>
											</div>
										</div>
									</div>
								</div>
							</template>
						</div>
					</div>
					<div class='d-none d-sm-block col-md-5 col-lg-6 col-xl-7 ' id='migi_area'>
						<div style='overflow-y: scroll;height:100vh;padding-bottom:170px;'>
							<div class='row m-0 mb-1 mt-1'>
								<div class='col-12 ps-3 position-relative'>
									Max記録と記録時のセット
									<button class='btn btn-secondary p-1 position-absolute pt-0 pb-0' style='right:16px;' @click='setting1()'><i class="bi bi-gear-wide"></i></button>
								</div>
							</div>
							<div class='p-3 pt-0 ' style='overflow-y: scroll;height:470px;' id='ms_training'>
								<table class='table table-sm caption-top' style='table-layout: fixed;'>
									<thead class='table-dark sticky-top'>
										<tr>
											<th v-show='setting_switch1' rowspan="3" style='width:30px;'></th>
											<th rowspan="3" style='max-width:100px;'>種目</th>
											<th colspan="1">3M</th>
											<th colspan="1">1Y</th>
											<th colspan="1">Best</th>
											<th v-show='setting_switch1' colspan="1" style='width:30px;'>隠</th>
										</tr>
										<tr>
											<th colspan="3">date weight x times</th>
											<th v-show='setting_switch1' colspan="1" style='width:30px;'></th>
										</tr>
									</thead>
									<tbody>
										<template v-for='(list,index) in max_log_sort' :key='list.shu'>
											<tr>
												<td v-show='setting_switch1' rowspan="3" class='table-info align-middle text-center' style='width:40px;' role='button' @click='move_recorde(index)' :id='`sort_${list.shu}`'>
													<i v-show='dragIndex===null' class="bi bi-arrow-down-up"></i>
													<i v-show='dragIndex!==null' class="bi bi-box-arrow-in-down-right"></i>
												</td>
												<td class='pb-0' rowspan="3" style='max-width:100px;'>
													{{list.shu}}
												</td>
												<td class='pb-0' colspan="1">{{list.M3_max}}</td>
												<td class='pb-0' colspan="1">{{list.Y1_max}}</td>
												<td class='pb-0' colspan="1">{{list.mybest}}</td>
												<td class='pb-0' v-show='setting_switch1' rowspan="3" class='text-center' style='width:30px;'>
													<input type='checkbox' class='form-check-input' v-model='list.display_hide1'>
												</td>
											</tr>
											<tr>
												<td class='pb-0'>{{list.M3_date}}</td>
												<td class='pb-0'>{{list.Y1_date}}</td>
												<td class='pb-0'>'{{list.MB_date}}</td>
											</tr>
											<tr>
												<td class='pb-0'>{{list.M3_set}}</td>
												<td class='pb-0'>{{list.Y1_set}}</td>
												<td class='pb-0'>{{list.MB_set}}</td>
											</tr>
										</template>
									</tbody>
								</table>
							</div>
						</div>
					</div>
				</div>
			</main>
			<footer class="footerArea">
				<div class='container d-flex hf_color p-0'>
					<div class='row m-0' style='width:100%;'><div class='p-0 col-12 col-md-7 col-lg-6 col-xl-5'>
						<ul id="menu">
						  <li><a href="#" data-bs-toggle='modal' data-bs-target='#taisosiki' @click='lock_trlog_area()'>体組織</a></li><!--@click='lock_trlog_area'-->
						  <li><a href="#" data-bs-toggle='modal' data-bs-target='#usanso'    @click='lock_trlog_area()'>有酸素系</a></li>
						  <li><a href="#" data-bs-toggle='modal' data-bs-target='#edit_wt'   @click='lock_trlog_area()'>ウェイト</a></li>
						</ul>
					</div></div>
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
									<label for='taiju' class="form-label" style='padding-left:0;margin-bottom:1px;'>体重（KG）</label>
									<input type='Number' step="0.01" class="form-control form-control-sm" id='taiju' name='weight' required='required'>
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
								<button type='button' style='width:90px;font-size:13px;' name='' class="btn btn-secondary mbtn" data-bs-dismiss="modal" id='ts_modal_close'>キャンセル</button>
								<a href='graph_taisosiki.php' style='width:90px;' class="btn btn-primary mbtn" >履歴</a>
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
						<form method = 'post' action='logInsUpd_sql.php' @submit.prevent='OnSubmit' id='us'>
							<div class='modal-header'>
	        			<h5 class="modal-title">有酸素トレーニング</h5>
  	      			<button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close" @click='setCancel'></button>
							</div>
							<div class='modal-body container'>
								<div v-if='keybord_show===false' class='row' style='margin:1px 20px;'>
									<label for='ymd' class="form-label" style='padding-left:0;margin-bottom:1px;'>日付</label>
									<input type='date' @focus='keydown' class="form-control form-control-sm" id='ymd' name='ymd' v-model="ymd" required='required'>
								</div>
								<div class='row' style='margin:1px 20px;'>
									<label for='shu1' class="form-label" style='padding-left:0;margin-bottom:1px;'>種目</label>
									<select id='shu1' @focus='keydown' class="form-select form-select-sm" name='shu1' v-model='shu' >
										<template v-for='(list,index) in shumoku_us' :key='list.sort'>
											<option :value='`${list.shu}`'>{{list.shu}}</option>
										</template>
									</select>
								</div>
								<div v-if='keybord_show===false' class='row' style='margin:1px 20px;'>
									<label for='shu2' class="form-label" style='padding-left:0;margin-bottom:1px;'>種目追加</label>
									<input type='text' @change='add_shumoku_wt' class="form-control form-control-sm" id='shu2' name='shu2' placeholder='リストにない場合は手入力' autocomplete="off">
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
									<label for='cal' class="form-label" style='padding-left:0;margin-bottom:1px;'>カロリー(Kcal)</label>
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

								<button type='button' style="display:none;" class="btn btn-secondary mbtn" data-bs-dismiss="modal" id='us_modal_close' ></button><!--キャンセル-->
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
			<div class='modal fade' id='edit_wt' tabindex='-1' role='dialog' aria-labelledby='basicModal' aria-hidden='true' >
				<div class='modal-dialog  modal-dialog-centered'>
					<div class='modal-content edit' style=''>
						<form method = 'post' @submit.prevent='OnSubmit' id='wt'>
							<div class='modal-header'>
	        			<h5 class="modal-title">トレーニング記録</h5>
  	      			<button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close" @click='setCancel'></button>
							</div>
							<div class='modal-body container'>
								<div v-show='keybord_show===false' class='row' style='margin:1px 20px;'>
									<label for='ymd' class="form-label" style='padding-left:0;margin-bottom:1px;'>日付</label>
									<input type='date' @focus='keydown' class="form-control form-control-sm" id='ymd' name='ymd' v-model="ymd" required='required'>
								</div>
								<div class='row' style='margin:1px 20px;'>
									<label for='shu1' class="form-label" style='padding-left:0;margin-bottom:1px;'>種目</label>
									<select id='shu1' @focus='keydown' class="form-select form-select-sm" name='shu1' v-model='shu'>
										<template v-for='(list,index) in shumoku_wt' :key='list.sort'>
											<option :value='`${list.shu}`'>{{list.shu}}</option>
										</template>
									</select>
								</div>
								<div v-show='keybord_show===false' class='row' style='margin:1px 20px;'>
									<label for='shu2' class="form-label" style='padding-left:0;margin-bottom:1px;'>種目追加</label>
									<input type='text' @change='add_shumoku_wt' class="form-control form-control-sm" id='shu2' name='shu2' placeholder='リストにない場合は手入力' autocomplete="off">
								</div>
								<div class='row pt-1' style='margin:1px 20px;'>
									<div class="col-3 p-0">
										<label class="form-label" style='padding-left:0;margin-bottom:1px;'>重量</label>
									</div>
									<div class="col-4 p-0">
										（
										<input type="checkbox" class="form-check-input" id="jiju" v-model='jiju' name='jiju'>
										<label class="form-check-label" for="jiju">自重</label>
										）
									</div>
								</div>
								<div class='row' style='margin:1px 0px 1px 20px;display:flexbox;'>
									<input type='number' :class="input_select[0]" readonly style='width:70px;padding:6 6;' @Click='setindex(0)' name='weight' :value="kiroku[0]" required='required'>
									<span style='padding:8px 0 0 5px;width:40px;'>kg x</span>
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
								
								<template v-if='keybord_show'>
									<div class='row' style='margin:15px 20px 1px 20px;'>
										<button type='button' class='btn btn-primary input-btn' @click='keydown'>1</button>
										<button type='button' class='btn btn-primary input-btn' @click='keydown'>2</button>
										<button type='button' class='btn btn-primary input-btn' @click='keydown'>3</button>
									</div>
								</template>
								<template v-if='keybord_show'>
									<div class='row' style='margin:1px 20px 1px 20px;'>
										<button type='button' class='btn btn-primary input-btn' @click='keydown'>4</button>
										<button type='button' class='btn btn-primary input-btn' @click='keydown'>5</button>
										<button type='button' class='btn btn-primary input-btn' @click='keydown'>6</button>
									</div>
								</template>
								<template v-if='keybord_show'>
									<div class='row' style='margin:1px 20px 1px 20px;'>
										<button type='button' class='btn btn-primary input-btn' @click='keydown'>7</button>
										<button type='button' class='btn btn-primary input-btn' @click='keydown'>8</button>
										<button type='button' class='btn btn-primary input-btn' @click='keydown'>9</button>
									</div>
								</template>
								<template v-if='keybord_show'>
									<div class='row' style='margin:1px 20px 1px 20px;'>
										<button type='button' class='btn btn-primary input-btn' @click='keydown'>0</button>
										<button type='button' class='btn btn-primary input-btn' @click='keydown'>.</button>
										<button type='button' class='btn btn-primary input-btn' @click='keydown'>C</button>
									</div>
								</template>
								<template v-if='keybord_show'>
									<div class='row' style='margin:1px 20px 1px 20px;'>
										<button type='button' class='btn btn-primary' style='height:60px;width:50%' @click='keydown' value='-1'>≪</button>
										<button type='button' class='btn btn-primary' style='height:60px;width:50%' @click='keydown' value='1'>≫</button>
									</div>
								</template>
								
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
								<button type='button' class="btn btn-secondary mbtn" data-bs-dismiss="modal" @click='setCancel' >{{mBtnName[1]}}</button><!--キャンセル-->
								<input type='submit' class="btn btn-primary mbtn" :value='mBtnName[0]'>{{}}<!--登録・更新-->
								
								<button type='button' style="display:none;" class="btn btn-secondary mbtn" data-bs-dismiss="modal" id='wt_modal_close' ></button><!--キャンセル-->
							</div>
							<INPUT type="hidden" name="typ" value="0">
							<INPUT type="hidden" name="cal" value="0">
							<INPUT type="hidden" name="NO" :value="Num">
							<INPUT type="hidden" name="motoYMD" :value="motoymd">
						</form>
					</div>
				</div>
			</div>
		</div>
		
		<script>//Vus.js
			const { createApp, ref, onMounted, onBeforeMount, computed, VueCookies,watch,nextTick } = Vue;
			createApp({
				setup(){
					const lock_trlog_area = () =>{
						document.getElementById('tr_log_area').style.overflowY = 'hidden'
						document.getElementById('tr_log_area').style.pointerEvents = 'none'
					}
					const unlock_trlog_area = () =>{
						document.getElementById('tr_log_area').style.overflowY = 'scroll'
						document.getElementById('tr_log_area').style.pointerEvents = 'auto'
					}

					const kintore_log = ref([])
					const shumoku = ref([])
					const max_log = ref([])
					const max_log_sort = computed(()=>{
						let temp = max_log.value.sort((a,b)=>{
							return a.sort-b.sort
							//return String(a.display_hide1) + a.sort > String(b.display_hide1) + b.sort
						})

						return temp.filter((list)=>{
							if(setting_switch1.value===true){
								return true
							}else{
								if(list.display_hide1===true || list.display_hide1==='true'){
									//return false
								}else{
									return true
								}
							}
						})
					})
					
					const id = ref('<?php echo $id;?>')
					const filter = ref('%')

					const week = (date) =>{
						const WeekChars = [ "(日)", "(月)", "(火)", "(水)", "(木)", "(金)", "(土)" ];
						let dObj = new Date( date );
						let wDay = dObj.getDay();
						//console_log("指定の日は、" + WeekChars[wDay] + "です。");
						return WeekChars[wDay]
					}
					const log_edit = computed(()=>{
						if(filter.value==="%"){
							return kintore_log.value
						}else{
							return kintore_log.value.filter((row)=>{
								if(row.shu===filter.value){return true}
							})
						}
					})
					const disp_area = ref(false)
					watch(disp_area,()=>{
						document.getElementById('migi_area').classList.toggle('col-12')
						document.getElementById('migi_area').classList.toggle('d-none')
						document.getElementById('migi_area').classList.toggle('d-sm-block')
					})

					const shumoku_wt = computed(()=>{
						return shumoku.value.filter((list)=>{
							if(list.typ!=1){return true}
						})
					})

					const shumoku_us = computed(()=>{
						return shumoku.value.filter((list)=>{
							if(list.typ==1){return true}
						})
					})

					const get_trlog = async(p) =>{
						console_log('start get_trlog')
						const response = await axios.post("ajax_get_trlog.php")
							.catch((error) => {
								console_log(`ajax_get_trlog ERROR:${error}`)
								console_log(error)
							})
							.finally(()=>{
								console_log('おわり get_trlog')
							})
						
						console_log(response.data)
						shumoku.value = response.data.shumoku_list
						kintore_log.value = response.data.kintore_log
						max_log.value = response.data.max_log
						shu.value = shumoku_wt.value[0]["shu"]

						max_log.value.forEach((list,index)=>{
							if(list.M3_max === list.Y1_max && list.M3_max === list.mybest){list.mybest = '〃' }
							if(list.M3_max === list.Y1_max){list.Y1_max = '〃' }
						})

						kintore_log.value.forEach((row,index)=>{
							row.ymd = row.ymd + ' ' + week(row.ymd)
							if((index + Number(1)) == kintore_log.value.length){
								if(p==='open'){
									setTimeout(()=>{
										console_log("アコーディオン開く")
										document.getElementById(`btn_collapseOne${ymd.value}${shu.value}`).click()
									}, 1000)
								}
							}
						})
						console_log('おわり2 get_trlog')
					}

					const kiroku = ref(['','','',0])
					const kiroku_index = ref('')
					const keybord_show = ref(false)
					const setindex = (i) =>{
						console_log(`setindex:${i}`)
						if(i===0 && jiju.value===true){
							console_log(`自重種目は重量入れない: index=${i} , jiju.value=${jiju.value}`)
							return 0 //自重種目は重量入れない
						}
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
					const shu = ref()
					const jiju = ref(false)	//自重種目ONOFF
					
					const memo = ref('')
					let MODAL_INST
					let MODAL
					const setUpdate = (NO,YMD,SHU,wt,rep,set,rep2,MEMO,typ,modal_id) =>{
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
						jiju.value = (typ==="2")?true:false

						MODAL = document.getElementById(modal_id)
						MODAL_INST = new bootstrap.Modal(MODAL, {
    					backdrop: 'static' // backdropをstaticに設定
  					});
						MODAL_INST.show();
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

						MODAL_INST = new bootstrap.Modal(MODAL, {
    					backdrop: 'true' // backdropをstaticに設定
  					});
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

					let new_shu
					const add_shumoku_wt = (e) =>{
						console_log(`add_shumoku_wt e:${e.target.value}`)
						shumoku_wt.value.unshift({shu:e.target.value,sort:''})
						shu.value = e.target.value
						new_shu = e.target
					}
					watch([jiju],()=>{
						if(jiju.value===true){
							kiroku.value[0] = "1"
						}else{
							kiroku.value[0] = ""
						}
					})
					const OnSubmit = (e) =>{
						console_log(`OnSubmit`)
						console_log(e.currentTarget.elements['shu1'].value)
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
							}else if(!e.currentTarget.elements['shu1'].value){
								if(!e.currentTarget.elements['shu2'].value){
									alert('トレーニング種目が未入力です')
									return
								}
							}
						}else if(e.target.id==='us'){
							if(!e.currentTarget.elements['shu1'].value){
								if(!e.currentTarget.elements['shu2'].value){
									alert('トレーニング種目が未入力です')
									return
								}
							}
						}
						//return
						
						const formData = new FormData(e.target);
						axios
						.post("ajax_trlog_Ins.php",formData, {headers: {'Content-Type': 'multipart/form-data'}})
						.then(async(response) => {
							console_log(response.data)
							if(response.data.status==="success"){
								filter.value = response.data.filter
								const accordion_elm = document.getElementById(`btn_collapseOne${ymd.value}${shu.value}`)
								const door = accordion_elm ? accordion_elm.getAttribute( 'aria-expanded' ) : undefined
								
								if(door==='true'){
									console_log("アコーディオン閉じる")
									document.getElementById(`btn_collapseOne${ymd.value}${shu.value}`).click()
									get_trlog('open')
								}else{
									get_trlog()
								}
								
								document.getElementById("wt_modal_close").click()
								document.getElementById("us_modal_close").click()
								document.getElementById("ts_modal_close").click()
								Num.value = 0
								ymd.value = '<?php echo $now?>'
								
								kiroku.value[0]=0
								kiroku.value[1]=0
								kiroku.value[2]=0
								kiroku.value[3]=0
								memo.value=''
								mBtnName.value[0] = '登録'
								mBtnName.value[1] = '閉じる'
								keybord_show.value=false
								kiroku_index.value=''
								if(new_shu){new_shu.value = ''}
								
								if(MODAL_INST){
									MODAL_INST = new bootstrap.Modal(MODAL, {
		    						backdrop: 'true' // backdropをstaticに設定
	  							})
								}

							}else{
								alert("処理が失敗しました")
								alert(response.data)
							}
						})
						.catch((error) => {
							console_log(`OnSubmit ERROR:${error}`)
							console_log(error)
							alert("ERROR:処理が失敗しました")
							filter.value = "%"
						})
						.finally(()=>{
						})
					}
					

					const draggingItem = ref(null);
					const dragIndex = ref(null);

					const move_recorde = (p_index) =>{//マウス
						// 出力テスト
						if(setting_switch1.value===false){return}
						//e.preventDefault()
						if(draggingItem.value){
							if(max_log_sort.value[dragIndex.value].sort == max_log_sort.value[p_index].sort){
								console_log(`なにもしない`)
								
							}else{
								console_log(`${max_log_sort.value[dragIndex.value].shu} を ${max_log_sort.value[p_index].shu} の位置に移動`)
								if(max_log_sort.value[dragIndex.value].sort > max_log_sort.value[p_index].sort){
									max_log_sort.value[dragIndex.value].sort = Number(max_log_sort.value[p_index].sort) - 1
								}else{
									max_log_sort.value[dragIndex.value].sort = Number(max_log_sort.value[p_index].sort) + 1
								}
								max_log_sort.value.forEach((list,index)=>{
									list.sort = (Number(index) + Number(1)) * Number(100)
								})
							}
							draggingItem.value.classList.remove('table-primary');
							draggingItem.value.classList.add('table-info');
							draggingItem.value = null
							dragIndex.value = null
						}else{
							//draggingItem.value = e.target
							draggingItem.value = document.getElementById(`sort_${max_log_sort.value[p_index].shu}`)
							draggingItem.value.classList.remove('table-info');// 
							draggingItem.value.classList.add('table-primary');
							
							console_log(document.getElementById(`sort_${max_log_sort.value[p_index].shu}`))
							dragIndex.value = p_index
							console_log(`${max_log_sort.value[p_index].shu} を選択`)
						}
					}

					const setting_switch1 = ref(false)
					const setting1 = () =>{
						if(setting_switch1.value===false){
							//alert('項目の表示非表示、ドラッグアンドドロップによる並べ替えを行います')
							setting_switch1.value=true
							document.getElementById('ms_training').style.height='100%'
						}else{
							if(confirm('編集を終了します。変更内容を保存しますか？')){
								const form = new FormData()
								form.append('data',JSON.stringify(max_log.value))
								axios.post('ajax_mstraining_delin.php',form, {headers: {'Content-Type': 'multipart/form-data'}})
								.then((response)=>{
									console_log(response)
								})
								.catch((error)=>{
									alert(error)
								})
								.finally(()=>{
									
								})
							}else{
								alert('')
							}
							setting_switch1.value=false
							document.getElementById('ms_training').style.height='500px'
						}
					}
					
					onBeforeMount(()=>{
						console_log('onBeforeMount')
					})

					onMounted(() => {
						console_log('onMounted')
						get_trlog()
						const Modal_taisosiki = document.getElementById('taisosiki'); // モーダルのIDを取得
						const Modal_usanso = document.getElementById('usanso'); // モーダルのIDを取得
						const Modal_edit_wt = document.getElementById('edit_wt'); // モーダルのIDを取得

						Modal_taisosiki.addEventListener('hidden.bs.modal', function (event) {
						  console.log('モーダルが閉じました');
							unlock_trlog_area()
						});
						Modal_usanso.addEventListener('hidden.bs.modal', function (event) {
						  console.log('モーダルが閉じました');
							unlock_trlog_area()
						});
						Modal_edit_wt.addEventListener('hidden.bs.modal', function (event) {
						  console.log('モーダルが閉じました');
							unlock_trlog_area()
						});

					})

					return{
						kintore_log,
						max_log,
						max_log_sort,
						week,
						log_edit,
						shumoku,
						shumoku_wt,
						shumoku_us,
						keydown,
						kiroku,
						keybord_show,
						setindex,
						kiroku_index,
						input_select,
						id,
						mBtnName,
						Num,
						ymd,
						motoymd,
						shu,
						memo,
						setUpdate,
						setCancel,
						delete_log,
						add_shumoku_wt,
						OnSubmit,
						filter,
						jiju,
						disp_area,
						dragIndex,
						draggingItem,
						move_recorde,
						setting_switch1,
						setting1,
						lock_trlog_area,
						unlock_trlog_area,
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
			window.onload = function() {
				axios.get("ajax_timestamper.php?point=TOP.js_loaded")
				.catch((error) => {
					console_log(`ajax_timestamper ERROR:${error}`)
					console_log(error)
				})
				.finally(()=>{
					console_log('おわり ajax_timestamper')
				})
			};

		</script>
	</BODY>
</HTML>

<?php
	$pdo_h = null;
	$time = microtime();
	$parts = explode(" ", $time);
	$current_time_with_microseconds = date("H:i:s", $parts[1]) . "." . $parts[0];
	log_writer2("TOP.php end",$current_time_with_microseconds,"lv1");
	?>
