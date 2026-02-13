<?php
	// 設定ファイルインクルード【開発中】
	require "config.php";

	if(isset($_SESSION['USER_ID'])){
		$id = $_SESSION['USER_ID'];
		decho ("session:".$id);
	}else if (check_auto_login($_COOKIE['token'])==0) {
		$id = $_SESSION['USER_ID'];
		decho ("クッキー:".$id);
	}else{
		//header("HTTP/1.1 301 Moved Permanently");
		//header("Location: index.php");
		log_writer2("POST",$_POST,"lv3");
		log_writer2("GET",$_GET,"lv3");
		echo "きたよ";
		exit();
	}

	$logoff=!empty($_GET["logoff"])?$_GET["logoff"]:"";

	if($logoff === "out"){
		delete_old_token($_COOKIE['token']);
		setCookie("token", '', -1, "/", "", true, true);
		$_SESSION=[];
		$_SESSION["msg"] = "ログオフしました";
		header("HTTP/1.1 301 Moved Permanently");
		header("Location: index.php");
		exit();
	}

	$now = date('Y-m-d');

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

	if($row[0]["user_type"]==="google"){
		setCookie("user_type", 'google', time()+60*60*24*365, "/", "", true, true);
	}

	$user_name = ($row[0]["name"]);
	$token=get_token();
	//echo "ログインＯＫ<BR>";
	//}
?>
<!DOCTYPE html>
<HTML>
	<HEAD>
		<?php
		require "header.php";
		?>
		<STYLE>
			button:active {/*iphoneでのボタン反応を確かめるためのクラス。*/
			  <?php if(EXEC_MODE!=="Product"){ echo "background-color: red !important;"; }?>
			}
			button, .btn {
			  /* 1. タップ時のデフォルトのグレーの網掛けを消す（反応をクリアにする） */
			  -webkit-tap-highlight-color: transparent;

			  /* 2. ズーム判定を無効にしてタップの反応を最速にする */
			  touch-image-action: manipulation;

			  /* 3. テキスト選択を防ぐ（連打した時に文字が選択されて重くなるのを防ぐ） */
			  user-select: none;
			  -webkit-user-select: none;
			}
		</STYLE>
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
						  <label class="toggle_label">
						    <input class="toggle_input" type="checkbox" v-model="disp_area">
						    <div class="toggle_rail"></div>
						  </label>
						</div>					
					</div>
					<div class="nav-item dropdown position-absolute end-0 top-0"  style='width:40px;'>
        	  <a class="nav-link " href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false">
        	    <i class="bi bi-list fs-1"></i>
        	  </a>
        	  <ul class="dropdown-menu">
							<li><a class="dropdown-item" href="#" data-bs-toggle='modal' data-bs-target='#user_info'>ユーザー情報</a></li>
							<li><a class="dropdown-item" href="#" data-bs-toggle='modal' data-bs-target='#pwa_info'>インストール手順</a></li>
        	    <li><a class="dropdown-item" href="TOP.php?logoff=out">ログオフ</a></li>
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
			<main v-show='background_show' class='container p-0' style='height:calc(100vh - 115px);'>
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
											<div class='col-12' style='padding:0;display:flex;'><!--ウェイト-->
												<template v-if="list.typ==='0'" >
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
												</template>
												<template v-if="list.typ==='2'">
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
													</template>
													<template v-if="list.typ==='1'">
													<div style='width: 20px;'>{{list.setjun}}</div>
													<div class='text-end' style='width: 50px;padding-right:0;'>{{list.rep}}分</div>	
													<div class='text-end' style='width: 70px;padding:0;'>{{list.rep2}}ｍ</div>
													<div class='text-end' style='width: 70px;padding-right:0;'>{{list.cal}}kcal</div>
													<div class='' style='padding:0 0 0 10px;max-width:250px;width:calc(100vw - 240px);font-size:12px;word-wrap: break-word;word-break: break-all;margin-top:-5px;'>{{list.memo}}</div>
													<button type='button' class='icn-btn' style='' 
														@click='setUpdate(list.jun,list.ymd3,list.shu,list.cal,list.rep,list.sets,list.rep2,list.memo,list.typ,"usanso")'>
														<i class='bi bi-pencil'></i>
													</button>
												</template>
											</div>
											<div class='col-12' style='padding:0;display:flex;font-size:12px;'><!--時刻-->
												<div class='text-start ps-1' style='margin-top:-5px;'>記録 [{{list.jikoku}}]</div>
												<div style='width: 20px;'></div>
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
							<div class='p-3 pt-0 ' style='overflow-y: scroll;height:calc(100vh - 170px);' id='ms_training'>
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
			<!--↓インストールモーダル-->
			
			<?php 
			$icon="img/icon-128x128.png";
			require "install_modal.php";
			?>

			<!--↓ユーザ情報モーダル-->
			<div class='modal fade' id='user_info' tabindex='-1' role='dialog' aria-labelledby='basicModal' aria-hidden='true'>
				<div class='modal-dialog  modal-dialog-centered'>
					<div class='modal-content edit' style=''>
						<FORM method="post" action="user_update.php">
							<div class='modal-header'>
	        			<h5 class="modal-title">ユーザー情報設定</h5>
  	      			<button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
							</div>
							<div class='modal-body container'>
								<div class='row'>
									<div class='col-1 col-md-0' ></div>
										<div class='col-10 ' >
											<div class='mb-2'>
												<p style='font-size:20px'>ＩＤ：<?php echo $row[0]["id"];?></p>
											</div>
											<div v-if="'google'!=='<?php echo $row[0]["user_type"];?>'" class='mb-2'>
												<label for='pass'>パスワード</label>
												<INPUT id='pass' type="password" class='form-control form-select-sm' name="pass" maxlength="100" style='' placeholder="変更時のみ入力">
											</div>
											<div class='mb-2'>
												<label for='fname'>名前</label>
												<INPUT id='fname' required='required' type="text" class='form-control form-select-sm' name="fname" maxlength="100" style='' value="<?php echo $row[0]["name"];?>">
											</div>
											<div class='mb-2'>
												<label for='height'><span style='color:yellow;'>※</span>身長(cm)</label>
												<INPUT id='height' type="number" class='form-control form-select-sm' step="1" name="height" maxlength="10" style='' value="<?php echo $row[0]["height"];?>">
											</div>
											<div class='mb-2'>
												<label for='birthday'><span style='color:yellow;'>※</span>生年月日</label>
												<INPUT id='birthday' required='required' type="date" class='form-control form-select-sm' name="birthday" value="<?php echo $row[0]["birthday"];?>">
											</div>
											<div class='mb-2'>
												<label for='sex'><span style='color:yellow;'>※</span>性別</label>
												<SELECT size="1" id='sex' name="sex" class='form-select form-select-sm' style=''  value="<?php echo $row[0]["sex"];?>">
													<OPTION value="1">男</OPTION>
													<OPTION value="0">女</OPTION>
												</SELECT>
											</div>
											<div><span style='color:yellow;'>※</span>：パスワード再設定に利用。(Googleログイン除く)</div>
											<input type="hidden" name='token' value="<?php echo $token;?>">
											<input type="hidden" name='id' value="<?php echo $row[0]["id"];?>">
										</div>
									</div>
									<div class='col-1' ></div>
								</div>
								<div class='modal-footer'>
									<button type='button' style='width:90px;font-size:13px;' name='' class="btn btn-secondary mbtn" data-bs-dismiss="modal" id=''>キャンセル</button>
									<button type='submit' style='width:90px;' name='btn' value='update' class="btn btn-primary mbtn" data-bs-dismiss="modal">更新</button>
								</div>
						</FORM>
					</div>
				</div>
			</div>

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
								<!--<div class='row' style='margin:1px 20px;'>
									<label for='yobi1' class="form-label" style='padding-left:0;margin-bottom:1px;'>予備１</label>
									<input type='text' class="form-control form-control-sm" id='yobi1' name='yobi1'>
								</div>
								<div class='row' style='margin:1px 20px;'>
									<label for='yobi2' class="form-label" style='padding-left:0;margin-bottom:1px;'>予備２</label>
									<input type='text' class="form-control form-control-sm" id='yobi2' name='yobi2'>
								</div>-->
								<div class='row' style='margin:1px 20px;'>
									<label for='memo' class="form-label" style='padding-left:0;margin-bottom:1px;'>今日のコンディション</label>
									<input type='text' class="form-control form-control-sm" id='memo' name='memo' value='' placeholder='空腹時・食後など'>
								</div>
							</div>
							<div class='modal-footer'>
								<button type='button' style='width:90px;font-size:13px;' name='' class="btn btn-secondary mbtn" data-bs-dismiss="modal" id='ts_modal_close'>キャンセル</button>
								<a href='graph_taisosiki.php' style='width:90px;' class="btn btn-primary mbtn" >履歴</a>
								<button type='submit' style='width:90px;' name='btn' value='w_ins_bt' class="btn btn-primary mbtn" data-bs-dismiss="modal">登録</button>
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
									<label for='ymd2' class="form-label" style='padding-left:0;margin-bottom:1px;'>日付</label>
									<input type='date' @focus='keydown' class="form-control form-control-sm" id='ymd2' name='ymd' v-model="ymd" required='required'>
								</div>
								<div class='row' style='margin:1px 20px;'>
									<label class="form-label" style='padding-left:0;margin-bottom:1px;'>種目</label>
								</div>
								<div class='row' style='margin:1px 20px;'>
								<nav class="navbar bg-body-tertiary p-0 " style='border-radius:4px;'>
								  <div class="container-fluid p-0 ">
								    <button class="navbar-toggler ps-2 pt-2 pb-2 d-flex" type="button" style='height:100%;width:100%;border-radius:0;font-size:14px;color:black;' 
										data-bs-toggle="collapse" data-bs-target="#navbarScroll" aria-controls="navbarScroll" aria-expanded="false" aria-label="Toggle navigation" id='us_select'>
								      <span class='text-start' style="width:90%;">{{shu_us}}</span><span class='text-end' style="width:10%;">▼</span>
								    </button>
								    <div class="collapse navbar-collapse" id="navbarScroll">
								      <ul class="navbar-nav me-auto my-2 my-lg-0 navbar-nav-scroll" style="--bs-scroll-height: 200px;">
												<template v-for='(list,index) in shumoku_us' :key='list.sort'>
													<li class="nav-item p-0 ps-3" style='font-size:14px;color:black;'>
								        	  <a class="nav-link" @click='set_shumoku(list.shu,"us")' role='button'>{{list.shu}}</a>
								        	</li>
												</template>
												</ul>
								      <div class="d-flex" >
								        <input class="form-control" style='width:80%;border-radius:0;' type="text" placeholder='トレーニング名' autocomplete="off" id='new_us'>
												<button type='button' class='btn btn-primary' style='width:20%;border-radius:0;' @click='add_shumoku("us","new_us")'>追加</button>
											</div>
								    </div>
								  </div>
								</nav>
								</div>

								<div class='row' style='margin:1px 20px;'>
									<label for='rep' class="form-label" style='padding-left:0;margin-bottom:1px;'>時間(分)</label>
									<input type='text' @focus='keydown' class="form-control form-control-sm" id='rep' name='rep' :value='kiroku[1]'>
								</div>
								<div class='row' style='margin:1px 20px;'>
									<label for='kyori' class="form-label" style='padding-left:0;margin-bottom:1px;'>距離(ｍ)</label>
									<input type='text' @focus='keydown' class="form-control form-control-sm" id='kyori' name='rep2' :value='kiroku[3]'>
								</div>
								<div class='row' style='margin:1px 20px;'>
									<label for='cal' class="form-label" style='padding-left:0;margin-bottom:1px;'>カロリー(Kcal)</label>
									<input type='text' @focus='keydown' class="form-control form-control-sm" id='cal' name='cal' :value='kiroku[0]'> <!--cal を weightで代用-->
								</div>
								<div class='row' style='margin:1px 20px;'>
									<label for='memo2' class="form-label" style='padding-left:0;margin-bottom:1px;'>SETメモ</label>
									<input type='text' @focus='keydown' class="form-control form-control-sm" id='memo2' name='memo' v-model='memo'>
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
								<input type='submit'  class="btn btn-primary mbtn" :value='mBtnName[0]'><!--登録・更新-->

								<button type='button' style="display:none;" class="btn btn-secondary mbtn" data-bs-dismiss="modal" id='us_modal_close' ></button><!--キャンセル-->
							</div>
							<INPUT type="hidden" name="typ" value="1">
							<INPUT type="hidden" name="shu1" :value="shu_us">							
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
			<div class='modal fade' id='edit_wt' data-bs-backdrop="static" tabindex='-1' role='dialog' aria-labelledby='basicModal' aria-hidden='true' >
				<div class='modal-dialog  modal-dialog-centered'>
					<div class='modal-content edit' style=''>
						<form method = 'post' @submit.prevent='OnSubmit' id='wt'>
							<div class='modal-header'>
	        			<h5 class="modal-title">トレーニング記録</h5>
  	      			<button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close" @click='setCancel'></button>
							</div>
							<div class='modal-body container'>
								<div v-show='keybord_show===false' class='row' style='margin:1px 20px;'>
									<label for='ymd3' class="form-label" style='padding-left:0;margin-bottom:1px;'>日付</label>
									<input type='date' @focus='keydown' class="form-control form-control-sm" id='ymd3' name='ymd' v-model="ymd" required='required'>
								</div>
								<div class='row' style='margin:1px 20px;'>
									<label class="form-label" style='padding-left:0;margin-bottom:1px;'>種目</label>
								</div>
								<div class='row' style='margin:1px 20px;'>
								<nav class="navbar bg-body-tertiary p-0 " style='border-radius:4px;'>
								  <div class="container-fluid p-0 ">
								    <button class="navbar-toggler ps-2 pt-2 pb-2 d-flex" type="button" style='height:100%;width:100%;border-radius:0;font-size:14px;color:black;' 
										data-bs-toggle="collapse" data-bs-target="#navbarScroll" aria-controls="navbarScroll" aria-expanded="false" aria-label="Toggle navigation" id='tr_select'>
								      <span class='text-start' style="width:90%;">{{shu}}</span><span class='text-end' style="width:10%;">▼</span>
								    </button>
								    <div class="collapse navbar-collapse" id="navbarScroll">
								      <ul class="navbar-nav me-auto my-2 my-lg-0 navbar-nav-scroll" style="--bs-scroll-height: 200px;">
													<!--<li class="nav-item dropdown ps-3">
								        	  <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false">
								        	    胸部
								        	  </a>
								        	  <ul class="dropdown-menu">
								        	    <li><a class="dropdown-item ps-1" href="#">ベンチプレス</a></li>
								        	    <li><a class="dropdown-item ps-1" href="#">ダンベルプレス</a></li>
								        	    <li><a class="dropdown-item ps-1" href="#">ダンベルフライ</a></li>
								        	  </ul>
								        	</li>-->
												<template v-for='(list,index) in shumoku_wt' :key='list.sort'>
													<li class="nav-item p-0 ps-3" style='font-size:14px;color:black;'>
								        	  <a class="nav-link" @click='set_shumoku(list.shu,"wt")' role='button'>{{list.shu}}</a>
								        	</li>
												</template>
												</ul>
								      <div class="d-flex" >
								        <input class="form-control" style='width:80%;border-radius:0;' type="text" placeholder='トレーニング名' autocomplete="off" id='new_tr'>
												<button type='button' class='btn btn-primary' style='width:20%;border-radius:0;' @click='add_shumoku("wt","new_tr")'>追加</button>
											</div>
								    </div>
								  </div>
								</nav>
								</div>

								<div class='row pt-1' style='margin:1px 20px;'><!--重量など記録部分label-->
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
								<div class='row' style='margin:1px 0px 1px 20px;display:flexbox;'><!--重量など記録部分-->
									<input type='number' :class="input_select[0]" readonly style='width:70px;padding:6 6;' @Click='setindex(0)' name='weight' :value="kiroku[0]" required='required'>
									<span style='padding:8px 0 0 5px;width:40px;'>kg x</span>
									<input type='number' :class="input_select[1]" readonly style='width:50px;padding:6 6;' @Click='setindex(1)' name='rep' :value="kiroku[1]" required='required'>
									<select class="form-select form-select-sm" style='width:50px;padding-left:5px;padding-right:15px;margin-left:5px;' name='tani' required='required'>
										<option value='0' selected>回</option>
										<option value='1'>秒</option>
									</select><span style='padding:8px 0 0 5px;width:15px;'>x</span>
									<input type='number' :class="input_select[2]" readonly style='width:40px;padding:6 6;' @Click='setindex(2)' name='sets' :value="kiroku[2]" required='required'><span style='padding:8px 0 0 5px;width:30px;'>SET</span>
								</div>
								<div class='row ' style='margin:1px 20px;'>
									<label for='rep2' class="form-label" style='padding-left:0;margin-bottom:1px;'>補助/チート</label>
									<input type='number' :class="input_select[3]" readonly style='width:50px;' id='rep2' @Click='setindex(3)' name='rep2' :value="kiroku[3]">
									
								</div>
								
								<div v-show='keybord_show'>
									<div class='row' style='margin:15px 20px 1px 20px;position:relative;'>
										<button type='button' class='btn btn-primary input-btn' @click='keydown'>1</button>
										<button type='button' class='btn btn-primary input-btn' @click='keydown'>2</button>
										<button type='button' class='btn btn-primary input-btn' @click='keydown'>3</button>
										<a href='#' onclick="alert('RM数を入力してください。直近3カ月のMAX重量から適切な重量を逆算してセットします。')" class='m-0 p-0 ps-2' style='position: absolute; left: 33%; top: -57px;height:30px;color:#fff;width:120px;font-size:12px;'><i class="bi bi-question-square me-1"></i>RM換算とは</a>
										<input type='checkbox'  autocomplete="off"  class="btn-check" id='rm_mode' v-model='rm_mode'>
										<label for='rm_mode' class='btn btn-outline-success input-btn' style='position: absolute; right: 33%; top: -32px;height:30px;color:#fff;' >RM換算</label>
										<button type='button' class='btn btn-secondary input-btn' style='position: absolute; right: 3px; top: -32px;height:30px;' @click='keybord_close()'>Ｘ</button>
									</div>
									<div class='row' style='margin:1px 20px 1px 20px;'>
										<button type='button' class='btn btn-primary input-btn' @click='keydown'>4</button>
										<button type='button' class='btn btn-primary input-btn' @click='keydown'>5</button>
										<button type='button' class='btn btn-primary input-btn' @click='keydown'>6</button>
									</div>
									<div class='row' style='margin:1px 20px 1px 20px;'>
										<button type='button' class='btn btn-primary input-btn' @click='keydown'>7</button>
										<button type='button' class='btn btn-primary input-btn' @click='keydown'>8</button>
										<button type='button' class='btn btn-primary input-btn' @click='keydown'>9</button>
									</div>
									<div class='row' style='margin:1px 20px 1px 20px;'>
										<button type='button' class='btn btn-primary input-btn' @click='keydown'>{{zero_ten}}</button>
										<button v-show='rm_mode===false' type='button' class='btn btn-primary input-btn' @click='keydown'>.</button>
										<button type='button' class='btn btn-primary input-btn' @click='keydown'>ｸﾘｱ</button>
									</div>
									<div class='row' style='margin:1px 20px 1px 20px;'>
										<button type='button' class='btn btn-primary' style='height:50px;width:50%' @click='keydown' value='-1'>≪</button>
										<button type='button' class='btn btn-primary' style='height:50px;width:50%' @click='keydown' value='1'>≫</button>
									</div>
								</div>
								
								<div class='row' style='margin:1px 20px;'>
								</div>

								<div class='row' style='margin:1px 20px;'>
									<label for='memo3' class="form-label" style='padding-left:0;margin-bottom:1px;'>SETメモ</label>
									<input type='text' @focus='keydown' class="form-control form-control-sm" id='memo3' name='memo' v-model="memo">
								</div>
								<div class='row' style='margin:1px 20px;'>
									<label for='condition2' class="form-label" style='padding-left:0;margin-bottom:1px;'>今日のコンディション</label>
									<input type='text' @focus='keydown' class="form-control form-control-sm" id='condition2' name='condition' value='' placeholder='好調・寝不足・調整日など'>
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
							<INPUT type="hidden" name="shu1" :value="shu">
							<INPUT type="hidden" name="typ" value="0">
							<INPUT type="hidden" name="cal" value="0">
							<INPUT type="hidden" name="NO" :value="Num">
							<INPUT type="hidden" name="motoYMD" :value="motoymd">
						</form>
					</div>
				</div>
			</div>
		</div>
		<script>
			// すべてのモーダルに対して有効な設定
			//閉じるボタンにフォーカスが残るのを防ぐ
			document.addEventListener('hide.bs.modal', function () {
			    if (document.activeElement instanceof HTMLElement) {
			        document.activeElement.blur();
			    }
			});
			// ページ読み込み時に実行される場所に追記
			document.addEventListener("touchstart", function() {}, true);
		</script>
		<script>//Vus.js
			const { createApp, ref, onMounted, onBeforeMount, computed, VueCookies,watch,nextTick } = Vue;
			createApp({
				setup(){
					const lock_trlog_area = () =>{
						console_log('lock_trlog_area')
						background_show.value=false
					}
					const unlock_trlog_area = () =>{
						console_log('unlock_trlog_area')
						background_show.value=true
					}

					const background_show = ref(true)
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
						console_log('week')
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
						console_log('watch(disp_area')
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

					const get_trlog = async(p,p_shumoku) =>{
						console_log('start get_trlog')
						console_log('start get_trlog :: ' + p + '::' + p_shumoku)
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
						shu_us.value = shumoku_us.value[0]["shu"]

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
										//document.getElementById(`btn_collapseOne${ymd.value}${shu.value}`).click()
										document.getElementById(`btn_collapseOne${ymd.value}${p_shumoku}`).click()
									}, 1000)
								}
							}
						})
						console_log('おわり2 get_trlog')
					}

					const kiroku = ref(['','','',0])
					const kiroku_index = ref('')
					const keybord_show = ref(false)
					const rm_mode = ref(false)
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
					const zero_ten = computed(()=>{if(rm_mode.value){return '10'}else{return '0'}})
					const keydown = (e) => {//電卓ボタンの処理
						console_log('keydown')
						console_log('target.value=' + e.target.value)
						console_log('target.innerHTML=' + e.target.innerHTML)
						if(e.target.innerHTML==="ｸﾘｱ"){//クリア
							kiroku.value[kiroku_index.value] = 0
							before_val='-'
							console_log('ｸﾘｱ')
						}else if(e.target.value==='-1'){//フォーカス戻る
							console_log('<')
							if(kiroku_index.value===0){return}
							kiroku_index.value = Number(kiroku_index.value) - 1
						}else if(e.target.value==='1'){//フォーカス進む
							console_log('>')
							if(kiroku_index.value===3){
								kiroku_index.value=''
								keybord_show.value=false
								return
							}
							kiroku_index.value = Number(kiroku_index.value) + 1
						}else if(e.target.value==='99'){//ない
							console_log('99')
							kiroku_index.value=''
							keybord_show.value=false
						}else if(e.target.innerHTML==="."){//小数点
							console_log('.')
							if(kiroku.value[kiroku_index.value].toString().indexOf('.')!==-1){
								//小数点連続は無視
								return
							}
							before_val = "."
						}else if((Number(e.target.innerHTML) >= 0 && Number(e.target.innerHTML)<=10 && e.target.innerHTML !== '') || before_val==='.'){
							console_log('key input')
							if(rm_mode.value && kiroku_index.value === 0){
								console_log('RM-MODE')
								get_rm_weight(e.target.innerHTML.toString())
							}else{
								if(kiroku.value[kiroku_index.value]==''){
									kiroku.value[kiroku_index.value] = e.target.innerHTML.toString()
								}else if(before_val==='.'){
									kiroku.value[kiroku_index.value] = Number(kiroku.value[kiroku_index.value].toString() + '.' + e.target.innerHTML.toString())
								}else{
									kiroku.value[kiroku_index.value] = Number(kiroku.value[kiroku_index.value].toString() + e.target.innerHTML.toString())
								}
								before_val='-'
							}
						}else{
							console_log('else')
							kiroku_index.value=''
							keybord_show.value=false
						}
					}

					const get_rm_weight = (p_rm) =>{
						console_log('get_rm_weight')
						if(rm_mode.value){
							axios.get(`ajax_get_RM_weight.php?shu=${shu.value}&rep=${p_rm}`)
							.then((response)=>{
								console_log(response.data)
								if(response.data.MSG){
									alert(response.data.MSG)
								}else{
									kiroku.value[0] = response.data.rm_weight
								}
							})
						}
					}

					const keybord_close = () =>{keybord_show.value=false}
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
					const shu_us = ref()
					const jiju = ref(false)	//自重種目ONOFF
					
					const memo = ref('')

					const set_shumoku =(p_shu,p_type) =>{
						console_log('set_shumoku')
						if(p_type==="wt"){
							shu.value = p_shu
							document.getElementById('tr_select').click()
						}else if(p_type==="us"){
							shu_us.value = p_shu
							document.getElementById('us_select').click()
						}
					}
					let MODAL_INST
					let MODAL
					const setUpdate = (NO,YMD,SHU,wt,rep,set,rep2,MEMO,typ,modal_id) =>{
						console_log('setUpdate start')
						Num.value = NO
						ymd.value = YMD
						motoymd.value = YMD
						//shu.value = SHU
						if(typ==="1"){
							shu_us.value = SHU
						}else{
							shu.value = SHU
						}
						kiroku.value[0]=wt
						kiroku.value[1]=rep
						kiroku.value[2]=set
						kiroku.value[3]=rep2
						memo.value=MEMO
						mBtnName.value[0] = '更新'
						mBtnName.value[1] = 'キャンセル'
						jiju.value = (typ==="2")?true:false

						MODAL = document.getElementById(modal_id)
						/*
						MODAL_INST = new bootstrap.Modal(MODAL, {
    					backdrop: 'static' // backdropをstaticに設定
  					});*/
						// すでにインスタンスがあるか確認し、なければ作る（二重生成防止）
						MODAL_INST = bootstrap.Modal.getInstance(MODAL) || new bootstrap.Modal(MODAL, {
						  backdrop: 'static' // コメント通り「背景クリックで閉じない」にするならこれ
						});						
						MODAL_INST.show();
					}
					const setCancel = () =>{
						console_log('setCancel start')
						
						Num.value = 0
						ymd.value = '<?php echo $now?>'
						shu.value = ''
						shu_us.value = ''
						kiroku.value[0]=0
						kiroku.value[1]=0
						kiroku.value[2]=0
						kiroku.value[3]=0
						memo.value=''
						mBtnName.value[0] = '登録'
						mBtnName.value[1] = '閉じる'
						keybord_show.value=false
						kiroku_index.value=''
						/*
						MODAL_INST = new bootstrap.Modal(MODAL, {
    					backdrop: true // backdropをtrueに設定
  					});
						*/
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

					//let new_shu
					const add_shumoku = (p_type,p_id) =>{
						console_log('add_shumoku')
						/*
						console_log(`add_shumoku_wt e:${e.target.value}`)
						shumoku_wt.value.unshift({shu:e.target.value,sort:''})
						shu.value = e.target.value
						new_shu = e.target
						*/
						const new_tr = document.getElementById(p_id).value
						if(new_tr==""){
							alert('トレーニング名を入力して下さい')
							return
						}
						if(p_type==="wt"){
							//const new_tr = document.getElementById('new_tr').value
							document.getElementById('tr_select').click()
							shu.value = new_tr
							document.getElementById('new_tr').value = ""
						}else if(p_type==="us"){
							//const new_tr = document.getElementById('new_us').value
							document.getElementById('us_select').click()
							shu_us.value = new_tr
							document.getElementById('new_us').value = ""
						}
						shumoku_wt.value.unshift({shu:new_tr,sort:''})
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
								//if(!e.currentTarget.elements['shu2'].value){
									alert('トレーニング種目が未入力です')
									return
								//}
							}
						}else if(e.target.id==='us'){
							if(!e.currentTarget.elements['shu1'].value){
								//if(!e.currentTarget.elements['shu2'].value){
									alert('トレーニング種目が未入力です')
									return
								//}
							}
						}
						//return
						const shumoku = e.currentTarget.elements['shu1'].value
						const formData = new FormData(e.target);
						axios
						.post("ajax_trlog_Ins.php",formData, {headers: {'Content-Type': 'multipart/form-data'}})
						.then(async(response) => {
							console_log(response.data)
							if(response.data.status==="success"){
								filter.value = response.data.filter
								//const accordion_elm = document.getElementById(`btn_collapseOne${ymd.value}${shu.value}`)
								const accordion_elm = document.getElementById(`btn_collapseOne${ymd.value}${shumoku}`)
								const door = accordion_elm ? accordion_elm.getAttribute( 'aria-expanded' ) : undefined
								
								if(door==='true'){
									console_log("アコーディオン閉じる")
									//document.getElementById(`btn_collapseOne${ymd.value}${shu.value}`).click()
									document.getElementById(`btn_collapseOne${ymd.value}${shumoku}`).click()
									get_trlog('open',shumoku)
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
								//if(new_shu){new_shu.value = ''}
								
								if(MODAL_INST){
									MODAL_INST = new bootstrap.Modal(MODAL, {
		    						backdrop: 'true' // backdropをstaticに設定
	  							})
								}
								if(jiju.value===true){
									kiroku.value[0] = "1"
								}else{
									kiroku.value[0] = ""
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
						console_log('move_recorde')
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
						console_log('setting1')
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

						if (window.matchMedia('(display-mode: standalone)').matches) {
							// PWAとして起動された場合の処理
						} else {
							//alert('ブラウザとして起動されました');
							const userAgent = navigator.userAgent;
				  		if (
				  		  userAgent.indexOf('Windows') !== -1 ||
				  		  userAgent.indexOf('Macintosh') !== -1 ||
				  		  userAgent.indexOf('Linux') !== -1
				  		) {
				  		  // パソコン.なにもしない
				  		} else {
				  		  // パソコン以外。インストールを勧める
								document.getElementById("pwa_info_btn").click()
				  		}
						
						}

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
						rm_mode,
						zero_ten,
						keydown,
						kiroku,
						keybord_show,
						keybord_close,
						setindex,
						kiroku_index,
						input_select,
						id,
						mBtnName,
						Num,
						ymd,
						motoymd,
						shu,
						shu_us,
						set_shumoku,
						memo,
						setUpdate,
						setCancel,
						delete_log,
						add_shumoku,
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
						background_show,
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
