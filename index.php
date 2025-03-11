<?php
require "config.php";
define("GOOGLE_AUTH",$_ENV["GOOGLE_AUTH"]);
$time = microtime();
$parts = explode(" ", $time);
//$current_time_with_microseconds = date("H:i:s", $parts[1]) . "." . $parts[0];
//log_writer2("index",$current_time_with_microseconds,"lv1");

$logoff = (!empty($_GET["logoff"]))?$_GET["logoff"]:"";
$msg="";
if($logoff==="out"){
	delete_old_token($_COOKIE['token']);
	$_SESSION = [];
	setCookie("token", '', -1, "/", "", true, true);
	$msg='ログオフしました';
}else if($logoff==="sinkitouroku"){
	delete_old_token($_COOKIE['token']);
	$_SESSION = [];
	setCookie("token", '', -1, "/", "", true, true);
	$msg='登録完了しました。IDとパスワードを入力しログインしてください。';
}else if (isset($_COOKIE['token'])) {
	header("HTTP/1.1 301 Moved Permanently");
	header("Location: logincheck.php");
	//exit();
}else{
	$msg=(!empty($_SESSION["msg"]))?$_SESSION["msg"]:"";
}
?>
<!DOCTYPE html>
<HTML>
	<HEAD>
		<?php
			require "header.php";
		?>
		<script src="https://accounts.google.com/gsi/client" ></script>
		<meta name="description" content="【完全無料】シンプルを極めたトレーニング記録WEBアプリ！自分用のオリジナルメニューのみで、記録時のメニュー選択もスムーズに！Volume/Maxも自動計算！グラフ化で成長具合も！">
	</HEAD>
	<!--ログイン画面-->
	<TITLE>肉体改造ネットワーク</TITLE>
	<BODY>
		<DIV class='text-center'>
			<IMG src="img/kanban.png" style="width:300px;">
		</DIV>
		<MAIN id='main'>
			<div v-if='msg!==""'  class='alert alert-warning text-center' role="alert">{{msg}}</div>
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
				<div id="g_id_onload"
				     data-client_id="<?php echo GOOGLE_AUTH;?>"
				     
						 data-callback="handleCredentialResponse"
				     data-auto_prompt="false">
				</div>
				<div class="g_id_signin"
				     data-type="standard"
				     data-size="large"
				     data-theme="outline"
				     data-text="sign_in_with"
				     data-shape="rectangular"
				     data-logo_alignment="left">
				</div>
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
			function handleCredentialResponse(response) {
  		   // decodeJwtResponse() is a custom function defined by you
  		   // to decode the credential response.
  		   const responsePayload = decodeJwtResponse(response.credential);

  		   console.log("ID: " + responsePayload.sub);
  		   console.log('Full Name: ' + responsePayload.name);
  		   console.log('Given Name: ' + responsePayload.given_name);
  		   console.log('Family Name: ' + responsePayload.family_name);
  		   console.log("Image URL: " + responsePayload.picture);
  		   console.log("Email: " + responsePayload.email);
  		}
			function decodeJwtResponse(token) {
        var base64Url = token.split(".")[1];
        var base64 = base64Url.replace(/-/g, "+").replace(/_/g, "/");
        var jsonPayload = decodeURIComponent(
          atob(base64)
            .split("")
            .map(function (c) {
              return "%" + ("00" + c.charCodeAt(0).toString(16)).slice(-2);
            })
            .join("")
        );

        return JSON.parse(jsonPayload);
      }


		</script>
		<script>//Vus.js
			const { createApp, ref, onMounted, computed, VueCookies,watch } = Vue;
			createApp({
				setup(){
					const msg = ref('<?php echo $msg;?>')
					return{
						msg
					}
				}
			}).mount('#main');
		</script>
	</BODY>
</HTML>
