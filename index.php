<?php
if (isset($_COOKIE['token'])) {
	header("HTTP/1.1 301 Moved Permanently");
	header("Location: logincheck.php");
	exit();
}

require "config.php";
//require "functions.php";
$height = '30';
?>

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
		<FORM method="post" action="logincheck.php" style='margin-top:30px;'>
			<DIV style='text-align: center;'>
				<div style='display:flex; justify-content:center;'>
					<label for="id" class="form-label" style='width:100px;text-align:right;'>ＩＤ：</label>
					<INPUT type="text" class='form-control' id="id" name="id" maxlength="10" style='ime-mode:disabled;max-width:200px;'>
				</div>
				<div style='margin-top:30px;display:flex; justify-content:center;'>
					<label for="pass" class="form-label" style='width:100px;text-align:right;'>パスワード：</label>
					<INPUT type="password" class='form-control' id="pass" name="pass" maxlength="10" style='max-width:200px;'>
				</div>
				<button type="submit" class='btn btn-primary'style='margin-top:30px;' name="btn" value="ＧＯ！！">
					ＧＯ！！
				</button>
			</DIV>
			<INPUT type="hidden" name="auto" value="true">
		</FORM>

		<div class='accordion' id="accordionExample"><!--ユーザー登録-->
			<div style='padding-top:5px;font-size:1.0rem;font-weight:700;top: 156px;height:52px;'>
				<hr>
				<div class='accordion-item'>
					<h2 class='accordion-header'>
						<button type='button' class='accordion-button collapsed' style='font-size:1.0rem;' data-bs-toggle='collapse' data-bs-target='#collapseOne' aria-expanded='false' aria-controls='collapseOne'>
							ユーザー登録
						</button>
					</h2>
					<div id='collapseOne' class='accordion-collapse collapse' data-bs-parent='#accordionExample'>
						<div class='accordion-body'>
							<div class='row'>
								<div class='col-1 col-md-0' ></div>
								<div class='col-10 col-md-7' >
									<FORM method="post" action="recording.php">
										<DIV>ＩＤ：<INPUT required='required' type="text" class='form-control' name="id2" maxlength="10" style='ime-mode:disabled;'></DIV>
										<DIV>パスワード：<INPUT required='required' type="password" class='form-control' name="pass2" maxlength="10" style=''></DIV>
										<DIV>名前：<INPUT required='required' type="text" class='form-control' name="fname" maxlength="10" style=''></DIV>
										<DIV>身長：<INPUT type="number" class='form-control' step="0.1" name="height" maxlength="10" style=''> cm</DIV>
										<DIV>性別：
											<SELECT size="1" name="sex" class='form-select' style=''>
											<OPTION value="1">男</OPTION>
											<OPTION value="0">女</OPTION>
											</SELECT>
										</DIV>
										<div class='text-center' style='margin-top:5px;margin-top:20px;'>
											<button class='btn btn-primary' type='submit' name="btn" value="ユーザー登録">ユーザー登録</button>
										</div>
									</FORM>
								</div>
								<div class='col-1' ></div>
							</div>
    				</div>
    			</div>
				</div>
			</div>
		</div><!--ユーザー登録-->		
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
	</BODY>
</HTML>
