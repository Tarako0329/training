<?php
require_once "config.php";
define("GOOGLE_AUTH",$_ENV["GOOGLE_AUTH"]);
//$time = microtime();
//$parts = explode(" ", $time);
//$current_time_with_microseconds = date("H:i:s", $parts[1]) . "." . $parts[0];
//log_writer2("index",$current_time_with_microseconds,"lv1");

$logoff = (!empty($_GET["logoff"]))?$_GET["logoff"]:"";


$msg="";
if($_SESSION["msg"] === "ログオフしました"){
	delete_old_token($_COOKIE['token']);
	$_SESSION = [];
	setCookie("token", '', -1, "/", "", true, true);
	$msg='ログオフしました';
}else if($logoff==="sinkitouroku"){
	delete_old_token($_COOKIE['token']);
	$_SESSION["USER_ID"] = "";
	setCookie("token", '', -1, "/", "", true, true);
	$msg='登録完了しました。IDとパスワードを入力しログインしてください。';
}else if (isset($_COOKIE['token'])) {
	header("HTTP/1.1 301 Moved Permanently");
	header("Location: logincheck.php");
	//exit();
}else{
	$msg=(!empty($_SESSION["msg"]))?$_SESSION["msg"]:"";
	$_SESSION["msg"]="";
}

if($_COOKIE["user_type"]==="google"){
	$g_login="signin_with";
}else{
	$g_login="signup_with";
}

$token=get_token();
?>
<!DOCTYPE html>
<HTML>
	<HEAD>
		<?php
			require_once "header.php";
		?>
		<script src="https://accounts.google.com/gsi/client" ></script><!--google login api-->
		<meta name="description" content="【完全無料】シンプルを極めたトレーニング記録WEBアプリ！自分用のオリジナルメニューのみで、記録時のメニュー選択もスムーズに！Volume/Maxも自動計算！グラフ化で成長具合も！">
		<style>
			p{
				margin-bottom: 3px;
			}
		</style>
	</HEAD>
	<!--ログイン画面-->
	<TITLE>肉体改造ネットワーク</TITLE>
	<BODY>
		<div class='text-center'>
			<!--<IMG src="img/kanban.png" style="width:300px;">-->
			<IMG src="img/kanban2.png" style="width:300px;">
		</div>
		<MAIN class='container' id='main'>
			<div v-if='msg!==""'  class='alert alert-warning text-center' role="alert">{{msg}}</div>
			<FORM method="post" action="logincheck.php" style='margin-top:30px;'>
				<div style='text-align: center;'>
					<div style='display:flex; justify-content:center;'>
						<label for="id" class="form-label" style='width:100px;text-align:right;'>ＩＤ：</label>
						<INPUT type="text" required='required' class='form-control' id="id" name="id" maxlength="100" style='ime-mode:disabled;max-width:200px;'>
					</div>
					<div style='margin-top:30px;display:flex; justify-content:center;'>
						<label for="pass" class="form-label" style='width:100px;text-align:right;'>パスワード：</label>
						<INPUT type="password" required='required' class='form-control' id="pass" name="pass" maxlength="100" style='max-width:200px;'>
					</div>
					<div class='position-relative mt-3 mb-3'>
						<button type="submit" class='btn btn-primary mb-3' style='width:110px' id='GO'>ＧＯ！！</button>
						<div class="g_id_signin" style='width:200px;margin:auto;'
				     data-type="standard"
				     data-size="large"
				     data-theme="outline"
				     data-text="<?php echo $g_login;?>"
				     data-shape="rectangular"
				     data-logo_alignment="left">
						</div>
						<INPUT type="hidden" name="login_type" id='login_type'>
						<div id="g_id_onload"
						     data-client_id="<?php echo GOOGLE_AUTH;?>"
								 data-callback="handleCredentialResponse"
						     data-auto_prompt="false">
						</div>
					</div>
				</div>
			</FORM>
			<div class='row'>
				<div class='col-12 ps-3 pe-3 text-center'>
					<p>トレーニング内容を記録・分析するアプリケーションです。</p>
					<p>シンプル操作設計で簡単にトレーニングを記録。</p>
					<p>完全無料・広告一切なし</p>
					<div class='text-center mb-2 fs-5'>
						<a href="pbPolicy.php">＜プライバシーポリシー＞</a>
					</div>
					<div class='text-center mb-2 fs-5'>
						<a href="kiyaku.php">＜利用規約＞</a>
					</div>
				</div>
			</div>
			<div class='accordion' id="recording">
				<div style='padding-top:5px;font-size:1.0rem;font-weight:700;top: 156px;height:75px;'>
					<!--アプリ詳細-->		
					<!--<div class='accordion-item'>
						<h2 class='accordion-header'>
						<button type='button' class='accordion-button collapsed' style='font-size:1.0rem;' data-bs-toggle='collapse' data-bs-target='#collapseOne3' aria-expanded='false' aria-controls='collapseOne'>
							アプリ詳細はコチラ
						</button>
						</h2>
						<div id='collapseOne3' class='accordion-collapse collapse' data-bs-parent='#app_info'>
						<div class='accordion-body'>
							<div class='row'>
								<div class='col-12'>
									<div style='width:100%;height:500px;' id='Vmanager'>
										<iframe src="https://site.greeen-sys.com/%e8%82%89%e4%bd%93%e6%94%b9%e9%80%a0%e3%83%8d%e3%83%83%e3%83%88%e3%83%af%e3%83%bc%e3%82%af/" width="100%" height="100%" id='Vmanager-frame'></iframe>
									</div>
								</div>
							</div>
    				</div>
    				</div>
					</div>-->
					<!--アプリ詳細-->		
					<div class='accordion-item'><!--ユーザー登録-->		
						<h2 class='accordion-header'>
							<button type='button' class='accordion-button collapsed' style='font-size:1.0rem;' data-bs-toggle='collapse' data-bs-target='#collapseOne' aria-expanded='false' aria-controls='collapseOne'>
								ユーザー登録
							</button>
						</h2>
						<div id='collapseOne' class='accordion-collapse collapse' data-bs-parent='#recording'>
							<div class='accordion-body'>
								<div class='row'>
									<div class='col-12'>Googleアカウントでの登録は [Googleで続ける] から登録</div>
									<div class='col-1 col-md-0' ></div>
									<div class='col-10 col-md-7' >
										<FORM method="post" action="recording.php">
											<div>ＩＤ：<INPUT required='required' type="text" class='form-control' name="id2" maxlength="100" style='ime-mode:disabled;'></div>
											<div>パスワード：<INPUT required='required' type="password" class='form-control' name="pass2" maxlength="100" style=''></div>
											<div>名前：<INPUT required='required' type="text" class='form-control' name="fname" maxlength="100" style=''></div>
											<div><span style='color:red;'>*</span>身長(cm)：<INPUT type="number" class='form-control' step="1" name="height" maxlength="10" style=''></div>
											<div><span style='color:red;'>*</span>生年月日：<INPUT required='required' type="date" class='form-control' name="birthday" maxlength="10" style=''></div>
											<div><span style='color:red;'>*</span>性別：
												<SELECT size="1" name="sex" class='form-select' style=''>
												<OPTION value="1">男</OPTION>
												<OPTION value="0">女</OPTION>
												</SELECT>
											</div>
											<div>(<span style='color:red;'>*</span>) パスワードを忘れた際の再設定に利用します。</div>
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
											<div>ＩＤ：<INPUT required='required' type="text" class='form-control' name="id2" maxlength="100" style='ime-mode:disabled;'></div>
											<div>新パスワード：<INPUT required='required' type="password" class='form-control' name="pass2" maxlength="100" style=''></div>
											<div>登録時の身長(cm)：<INPUT required='required' type="number" step="1" class='form-control' name="fname" maxlength="10" style=''></div>
											<div>
												生年月日：
												<INPUT type="date" class='form-control' name="birthday" maxlength="10" style='' aria-labelledby="HelpBlock">
												<div id="HelpBlock" class="form-text">
													2023/11/17に追加した項目です。それ以前に登録した方は空白のままにしてください。
												</div>
											</div>
											<div>登録時の性別：
												<SELECT size="1" name="sex" class='form-select' style=''>
												<OPTION value="1">男</OPTION>
												<OPTION value="0">女</OPTION>
												</SELECT>
											</div>
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
				
  			console_log(responsePayload);
				
				const form = new FormData()
				form.append("ID",responsePayload.sub)
				form.append("name",responsePayload.given_name)
				form.append("token","<?php echo $token;?>")
				axios.post('recording_ajax.php',form, {headers: {'Content-Type': 'multipart/form-data'}})
				.then((response)=>{
					console_log(response)
					document.getElementById("login_type").value="google"
					document.getElementById("id").value=responsePayload.sub
					document.getElementById("pass").value=responsePayload.sub
					document.getElementById("GO").click()
				})
				.catch((error)=>{
					alert(error)
				})
				.finally(()=>{
				
				})

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
