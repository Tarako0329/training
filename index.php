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
	<!-- background: #f0fff0; -->
}</style>

<?php
	require "header.php";
?>

</HEAD>
<!--ログイン画面-->
<TITLE>ログイン画面</TITLE>
<BODY>
<DIV align="center"><IMG src="img/kanban.png" style="width:300px;"><BR>
<BR>
</DIV>

<FORM method="post" action="logincheck.php">
<P align="center"></P>
<P align="center"></P>
<DIV ALIGN="CENTER">　　　ＩＤ：<INPUT type="text" size="10" name="id" maxlength="10" style='font-size:20px;width:200px;height:<?php echo $height?>px;ime-mode:disabled;'></DIV>
<BR>
<DIV ALIGN="CENTER">パスワード：<INPUT type="password" size="8" name="pass" maxlength="10" style='font-size:20px;width:200px;height:<?php echo $height?>px;'></DIV>
<DIV align="center">
<INPUT type="hidden" name="auto" value="true"><BR>
<button type="submit" name="btn" value="ＧＯ！！" >
<span style="color: #4E9ABE; background-color: transparent">ＧＯ！！</span></button>
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
<BR>
<DIV ALIGN="CENTER">パスワード：<INPUT type="password" size="8" name="pass2" maxlength="10" style='font-size:20px;width:200px;height:<?php echo $height?>px;'></DIV>
<BR>
<DIV ALIGN="CENTER">　　　名前：<INPUT type="text" size="10" name="fname" maxlength="10" style='font-size:20px;width:200px;height:<?php echo $height?>px;'><BR></DIV>
<BR>
<DIV ALIGN="CENTER">　　　身長：<INPUT type="number" step="0.1" size="10" name="height" maxlength="10" style='font-size:20px;width:200px;height:<?php echo $height?>px;'> cm<BR></DIV>
<BR>
<DIV ALIGN="CENTER">　　　性別：
	<SELECT size="1" name="sex" style='font-size:20px;width:200px;height:<?php echo $height?>px;'>
	<OPTION value="1">男</OPTION>
	<OPTION value="0">女</OPTION>
	</SELECT></TD>
<BR>
<BR>

<DIV align="center">
<button type="submit" name="btn" value="ユーザー登録">
<span style="color: #4E9ABE; background-color: transparent">ユーザー登録</span></button>
</FORM>
</div>
</DIV>
<!--// 折り畳まれ部分 -->



</BODY>

</HTML>

