<?php
require "config.php";
$time = microtime();
$parts = explode(" ", $time);
$current_time_with_microseconds = date("H:i:s", $parts[1]) . "." . $parts[0];
log_writer2("index",$current_time_with_microseconds,"lv1");

$logoff = (!empty($_GET["logoff"]))?$_GET["logoff"]:"";

if($logoff==="out"){
	delete_old_token($_COOKIE['token']);
	$_SESSION = [];
	setCookie("token", '', -1, "/", "", true, true);
	$_SESSION["msg"]='ログオフしました';
}else if (isset($_COOKIE['token'])) {
	header("HTTP/1.1 301 Moved Permanently");
	header("Location: logincheck.php");
	//exit();
}
$_SESSION["msg"]=''
?>
<!DOCTYPE html>
<HTML>
	<HEAD>
		<?php
			require "header.php";
		?>
	</HEAD>
	<!--ログイン画面-->
	<TITLE>ログイン画面</TITLE>
	<HEADER></HEADER>
	<BODY>
		<DIV class='text-center'>
			<IMG src="img/kanban.png" style="width:300px;">
		</DIV>
		<MAIN id='main'>
			<div v-if='msg!==""'  class='alert alert-warning' role="alert">{{msg}}</div>
			<FORM method="post" action="logincheck.php" style='margin-top:30px;'>
				<DIV style='text-align: center;'>
					<div style='display:flex; justify-content:center;'>
						<label for="id" class="form-label" style='width:100px;text-align:right;'>ＩＤ：</label>
						<INPUT type="text" required='required' class='form-control' id="id" name="id" maxlength="100" style='ime-mode:disabled;max-width:200px;'>
					</div>
					<div style='margin-top:30px;display:flex; justify-content:center;'>
						<label for="pass" class="form-label" style='width:100px;text-align:right;'>パスワード：</label>
						<INPUT type="password" required='required' class='form-control' id="pass" name="pass" maxlength="100" style='max-width:200px;'>
					</div>
					<button type="submit" class='btn btn-primary'style='margin-top:30px;'>
						ＧＯ！！
					</button>
				</DIV>
				<INPUT type="hidden" name="auto" value="true">
			</FORM>

			<div class='accordion' id="recording">
				<div style='padding-top:5px;font-size:1.0rem;font-weight:700;top: 156px;height:75px;'>
					<div class='accordion-item'><!--ユーザー登録-->		
						<h2 class='accordion-header'>
							<button type='button' class='accordion-button collapsed' style='font-size:1.0rem;' data-bs-toggle='collapse' data-bs-target='#collapseOne' aria-expanded='false' aria-controls='collapseOne'>
								ユーザー登録
							</button>
						</h2>
						<div id='collapseOne' class='accordion-collapse collapse' data-bs-parent='#recording'>
							<div class='accordion-body'>
								<div class='row'>
									<div class='col-1 col-md-0' ></div>
									<div class='col-10 col-md-7' >
										<FORM method="post" action="recording.php">
											<DIV>ＩＤ：<INPUT required='required' type="text" class='form-control' name="id2" maxlength="100" style='ime-mode:disabled;'></DIV>
											<DIV>パスワード：<INPUT required='required' type="password" class='form-control' name="pass2" maxlength="100" style=''></DIV>
											<DIV>名前：<INPUT required='required' type="text" class='form-control' name="fname" maxlength="100" style=''></DIV>
											<DIV><span style='color:red;'>*</span>身長(cm)：<INPUT type="number" class='form-control' step="1" name="height" maxlength="10" style=''></DIV>
											<DIV><span style='color:red;'>*</span>生年月日：<INPUT required='required' type="date" class='form-control' name="birthday" maxlength="10" style=''></DIV>
											<DIV><span style='color:red;'>*</span>性別：
												<SELECT size="1" name="sex" class='form-select' style=''>
												<OPTION value="1">男</OPTION>
												<OPTION value="0">女</OPTION>
												</SELECT>
											</DIV>
											<DIV>(<span style='color:red;'>*</span>) パスワードを忘れた際の再設定に利用します。</DIV>
											<div class='text-center' style='margin-top:5px;margin-top:20px;'>
												<button class='btn btn-primary' type='submit' name="btn" value="ユーザー登録">ユーザー登録</button>
											</div>
										</FORM>
									</div>
									<div class='col-1' ></div>
								</div>
    					</div>
    				</div>
					</div><!--ユーザー登録-->		
					<div class='accordion-item'><!--パスリセット-->		
						<h2 class='accordion-header'>
							<button type='button' class='accordion-button collapsed' style='font-size:1.0rem;' data-bs-toggle='collapse' data-bs-target='#collapseTwo' aria-expanded='false' aria-controls='collapseTwo'>
								パスワード忘れちゃった
							</button>
						</h2>
						<div id='collapseTwo' class='accordion-collapse collapse' data-bs-parent='#passreset'>
							<div class='accordion-body'>
								<div class='row'>
									<div class='col-1 col-md-0' ></div>
									<div class='col-10 col-md-7' >
										<FORM method="post" action="passreset.php">
											<DIV>ＩＤ：<INPUT required='required' type="text" class='form-control' name="id2" maxlength="100" style='ime-mode:disabled;'></DIV>
											<DIV>新パスワード：<INPUT required='required' type="password" class='form-control' name="pass2" maxlength="100" style=''></DIV>
											<DIV>登録時の身長(cm)：<INPUT required='required' type="number" step="1" class='form-control' name="fname" maxlength="10" style=''></DIV>
											<DIV>
												生年月日：
												<INPUT type="date" class='form-control' name="birthday" maxlength="10" style='' aria-labelledby="HelpBlock">
												<div id="HelpBlock" class="form-text">
													2023/11/17に追加した項目です。それ以前に登録した方は空白のままにしてください。
												</div>
											</DIV>
											<DIV>登録時の性別：
												<SELECT size="1" name="sex" class='form-select' style=''>
												<OPTION value="1">男</OPTION>
												<OPTION value="0">女</OPTION>
												</SELECT>
											</DIV>
											<div class='text-center' style='margin-top:5px;margin-top:20px;'>
												<button class='btn btn-primary' type='submit' name="btn" value="パスワード更新">パスワード更新</button>
											</div>
										</FORM>
									</div>
									<div class='col-1' ></div>
								</div>
    					</div>
    				</div>
					</div><!--パスリセット-->
				</div>
			</div>
		</MAIN>
		<script>
			document.getElementById('main').onkeypress = (e) => {
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
		<script>//Vus.js
			const { createApp, ref, onMounted, computed, VueCookies,watch } = Vue;
			createApp({
				setup(){
					const msg = ref('<?php echo $_SESSION["msg"];?>')
					return{
						msg
					}
				}
			}).mount('#main');
		</script>
	</BODY>
</HTML>
