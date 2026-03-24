<?php
require_once "config.php";
define("GOOGLE_AUTH",$_ENV["GOOGLE_AUTH"]);
$logoff = $_GET["logoff"] ?? "";


$msg="";
$cookie_token = $_COOKIE['token'] ?? "";
$_SESSION["msg"] = $_SESSION["msg"] ?? "";
if($_SESSION["msg"] === "ログオフしました"){
	delete_old_token($cookie_token);
	$_SESSION = [];
	setCookie("token", '', -1, "/", "", true, true);
	$msg='ログオフしました';
}else if($logoff==="sinkitouroku"){
	delete_old_token($cookie_token);
	$_SESSION["USER_ID"] = "";
	setCookie("token", '', -1, "/", "", true, true);
	$msg='登録完了しました。IDとパスワードを入力しログインしてください。';
}else if (isset($_COOKIE['token'])) {
	header("HTTP/1.1 301 Moved Permanently");
	header("Location: logincheck.php");
	//exit();
}else{
	$msg=$_SESSION["msg"] ?? "";
	$_SESSION["msg"]="";
}

if(($_COOKIE["user_type"] ?? "")==="google"){
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
						<!--<div class="g_id_signin" style='width:200px;margin:auto;'
				     data-type="standard"
				     data-size="large"
				     data-theme="outline"
				     data-text="<?php //echo $g_login;?>"
				     data-shape="rectangular"
				     data-logo_alignment="left">
						</div>
						<INPUT type="hidden" name="login_type" id='login_type'>
						<div id="g_id_onload"
						     data-client_id="<?php //echo GOOGLE_AUTH;?>"
								 data-callback="handleCredentialResponse"
						     data-auto_prompt="false">
						</div>-->

						<div class="text-center mt-3">
						  <button type="button" class="btn btn-outline-dark" @click="loginWithGoogle" style="text-transform:none">
						    <img width="20px" style="margin-bottom:3px; margin-right:5px" alt="Google sign-in" src="https://upload.wikimedia.org/wikipedia/commons/thumb/5/53/Google_%22G%22_Logo.svg/512px-Google_%22G%22_Logo.svg.png" />
						    Googleで続ける
						  </button>
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
			/*function handleCredentialResponse(response) {
  			// decodeJwtResponse() is a custom function defined by you
  			// to decode the credential response.
  			const responsePayload = decodeJwtResponse1(response.credential);
				
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
			function decodeJwtResponse1(token) {
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
      }*/

		</script>
		<script>//Vus.js
			const { createApp, ref, onMounted, computed, VueCookies,watch } = Vue;
			createApp({
				setup(){
					const msg = ref('<?php echo $msg;?>')
					let client
					// 独自ボタンや既存のフローから呼び出す関数
				  function loginWithGoogle() {
				    client.requestCode();
					}

				  function handleAuthCode(code) {
						console_log('handleAuthCode start');
				    const form = new FormData();
				    form.append("code", code); // IDトークンではなく認可コードを送る
						form.append("token", "<?php echo $token;?>");

				    axios.post('recording_ajax.php', form)
				    .then((response) => {
				      // サーバー側でリフレッシュトークン保存が成功したらログイン完了
				      document.getElementById("login_type").value = "google";
				      // ※サーバー側から返ってきたユーザーIDをセット
				      document.getElementById("id").value = response.data.user_sub;
				      document.getElementById("pass").value = response.data.user_sub;
				      document.getElementById("GO").click();
				    })
				    .catch((error) => alert("連携に失敗しました: " + error));
				  }

					/*function decodeJwtResponse(token) {
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
      		}*/

					onMounted(()=>{
						client = google.accounts.oauth2.initCodeClient({
    				  client_id: '<?php echo GOOGLE_AUTH;?>',
    				  // スプレッドシート操作権限を追加
    				  scope: 'https://www.googleapis.com/auth/spreadsheets.readonly https://www.googleapis.com/auth/spreadsheets email profile openid',
    				  ux_mode: 'popup',
    				  callback: (response) => {
    				    if (response.code) {
    				      // 2. 取得した「認可コード」をサーバーに送る
									console_log(response.code);
									//console_log(decodeJwtResponse(response.code));
									//console_log(decodeJwtResponse(response));
    				      handleAuthCode(response.code);
    				    }
    				  },
    				});						
					})
					return{
						msg
						,loginWithGoogle
					}
				}
			}).mount('#main');
		</script>
	</BODY>
</HTML>
