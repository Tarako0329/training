<?php
  //ファビコンパス
	$icon="img/mstile2-150x150.png";
  /*
    JSで起動時に実行
  	if (window.matchMedia('(display-mode: standalone)').matches) {
			// PWAとして起動された場合の処理
		} else {
			//alert('ブラウザとして起動されました');
			document.getElementById("pwa_info_btn").click()
		}
  */
?>
<link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:opsz,wght,FILL,GRAD@24,400,0,0&icon_names=browser_updated" />
<button style="display:none;" data-bs-toggle='modal' data-bs-target='#pwa_info' id='pwa_info_btn'>pwa_info</button>
<div class='modal fade' id='pwa_info' tabindex='-1' role='dialog' aria-labelledby='basicModal' aria-hidden='true'>
	<div class='modal-dialog  modal-dialog-centered'>
		<div class='modal-content' style=''>
				<div class='modal-header'>
    			<h5 class="modal-title">インストール手順</h5>
    			<button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
				</div>
				<div class='modal-body container'>
					<ul class="nav nav-underline">
					  <li class="nav-item">
					    <a class="nav-link active" href="#" data-bs-toggle="tab" data-bs-target="#iphone">iPhone/Safari</a>
					  </li>
					  <li class="nav-item">
					    <a class="nav-link" href="#" data-bs-toggle="tab" data-bs-target="#android">Android/Chrome</a>
					  </li>
					</ul>
					<div class='tab-content'>
						<div class='row ps-3 pt-3 tab-pane active' style='font-size:12px;' id='iphone'>
							<ol>
							  <li>
							    <p class='mb-1'>共有ボタンをタップ</p>
							    <p>画面下部にある共有ボタン<i class="bi bi-box-arrow-up ms-1 me-1 fs-5"></i>をタップします。</p>
							  </li>
							  <li>
							    <p class='mb-1 mt-3'>ホーム画面に追加をタップ</p>
							    <p>表示されたメニューから「ホーム画面に追加<i class="bi bi-plus-square ms-2 me-1 fs-5"></i>」をタップします。</p>
							  </li>
							  <li>
							    <p class='mb-1 mt-3'>追加をタップ</p>
							    <p>サイト名とアイコンを確認し、「追加」をタップします。</p>
							  </li>
							  <li>
							    <p class='mb-1 mt-3'>インストール完了</p>
							    <p>ホーム画面にアプリケーションのアイコンが追加されます。</p>
							    <img src="<?php echo $icon;?>" alt="ホーム画面に追加されたアイコン">
							  </li>
							</ol>
						</div>
						<div class='row ps-3 pt-3 tab-pane' style='font-size:12px;' id='android'>
							<ol>
							  <li>
							    <p class='mb-1'>インストールアイコンをタップ</p>
							    <p>アドレスバーに表示されるインストールアイコン<span class="material-symbols-outlined ms-1 me-1 fs-5">browser_updated</span>をタップします。</p>
							  </li>
							  <li>
							    <p class='mb-1 mt-3'>インストールを確認</p>
							    <p>インストール確認のポップアップが表示されるので、「インストール」をタップします。</p>
							  </li>
							  <li>
							    <p class='mb-1 mt-3'>インストール完了</p>
							    <p>ホーム画面にアプリケーションのアイコンが追加されます。</p>
									<img src="<?php echo $icon;?>" alt="ホーム画面に追加されたアイコン">
							  </li>
							</ol>
						</div>
					</div>
				</div>
				<div class='modal-footer'>
					<button type='button' class="btn btn-secondary mbtn" data-bs-dismiss="modal" >閉じる</button>
				</div>
		</div>
	</div>
</div>