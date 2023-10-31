<?php
if (isset($_COOKIE['token'])) {
	header("HTTP/1.1 301 Moved Permanently");
	header("Location: logincheck.php");
}

require "config.php";
require "functions.php";
$height = '30';
?>

<HTML>
	<HEAD>
		<style>
		body {
			background: #f0fff0;
		}
		</style>

		<?php
			require "header.php";
		?>

	</HEAD>
	<!--ログイン画面-->
	<TITLE>ログイン画面</TITLE>
	<HEADER></HEADER>
	<BODY>
		<DIV align="center">
			<IMG src="img/kanban.png" style="width:300px;">
		</DIV>

		<FORM method="post" action="logincheck.php">
			<P align="center"></P>
			<P align="center"></P>
			<DIV ALIGN="CENTER">
				　　　ＩＤ：<INPUT type="text" size="10" name="id" maxlength="10" style='font-size:20px;width:200px;height:<?php echo $height?>px;ime-mode:disabled;'>
			</DIV>
			<BR>
			<DIV ALIGN="CENTER">パスワード：
				<INPUT type="password" size="8" name="pass" maxlength="10" style='font-size:20px;width:200px;height:<?php echo $height?>px;'>
			</DIV>
			<DIV align="center">
				<INPUT type="hidden" name="auto" value="true"><BR>
				<button type="submit" name="btn" value="ＧＯ！！" >
					<span style="color: #4E9ABE; background-color: transparent">ＧＯ！！</span>
				</button>
			</DIV>
		</FORM>
		<BR>
		<BR>



		<!-- 折り畳み展開ポインタ -->
		<div onclick="obj=document.getElementById('open').style; obj.display=(obj.display=='none')?'block':'none';">
		<a style="cursor:pointer;" class="open_button">　▼　 新規登録はこちら 　▼　</a>
		</div>

		<!-- 折り畳まれ部分 -->
		<div id="open" style="display:none;clear:both;">
			<FORM method="post" action="recording.php">
				<P align="center"></P>
				<P align="center"></P>
				<DIV ALIGN="CENTER">　　　ＩＤ：<INPUT type="text" size="10" name="id2" maxlength="10" style='font-size:20px;width:200px;height:<?php echo $height?>px;ime-mode:disabled;'></DIV>
				<DIV ALIGN="CENTER">パスワード：<INPUT type="password" size="8" name="pass2" maxlength="10" style='font-size:20px;width:200px;height:<?php echo $height?>px;'></DIV>
				<DIV ALIGN="CENTER">　　　名前：<INPUT type="text" size="10" name="fname" maxlength="10" style='font-size:20px;width:200px;height:<?php echo $height?>px;'><BR></DIV>
				<DIV ALIGN="CENTER">　　　身長：<INPUT type="number" step="0.1" size="10" name="height" maxlength="10" style='font-size:20px;width:200px;height:<?php echo $height?>px;'> cm<BR></DIV>
				<DIV ALIGN="CENTER">　　　性別：
					<SELECT size="1" name="sex" style='font-size:20px;width:200px;height:<?php echo $height?>px;'>
					<OPTION value="1">男</OPTION>
					<OPTION value="0">女</OPTION>
					</SELECT>
				<DIV align="center">
					<button type="submit" name="btn" value="ユーザー登録">
						<span style="color: #4E9ABE; background-color: transparent">ユーザー登録</span>
					</button>
				</DIV>
			</FORM>
		</div>
		<!--// 折り畳まれ部分 -->


		<div class='accordion' id="accordionExample"><!--割引処理-->
			<div style='padding-top:5px;font-size:1.4rem;font-weight:700;top: 156px;height:52px;'>
				<hr>
				<div class='accordion-item'>
					<h2 class='accordion-header'>
						<button type='button' class='accordion-button collapsed' style='font-size:2.1rem;' data-bs-toggle='collapse' data-bs-target='#collapseOne' aria-expanded='false' aria-controls='collapseOne'>
							割引・割増
						</button>
					</h2>
					<div id='collapseOne' class='accordion-collapse collapse' data-bs-parent='#accordionExample'>
						<div class='accordion-body'>
							<div class='row'>
								<div class='col-1 col-md-0' ></div>
								<div class='col-10 col-md-7' >

									


									<div class='text-center' style='margin-top:5px'>
										<button class='btn btn-primary' type='button' @click='Revised()'>ユーザー登録</button>
									</div>
									
								</div>
								<div class='col-1' ></div>
							</div>
    				</div>
    			</div>
				</div>
			</div>
		</div><!--割引処理-->		














	</BODY>

</HTML>

