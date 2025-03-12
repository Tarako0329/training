<!DOCTYPE html>
<html lang="ja">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
  <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:opsz,wght,FILL,GRAD@24,400,0,0&icon_names=browser_updated" />

  <title>PWAサイト インストール手順</title>
  <style>
    body {
      font-family: 'Arial Rounded MT Bold', sans-serif; /* ポップなフォント */
      margin: 0;
      padding: 0;
      background-color: #f0f0f0; /* パールホワイトに近い背景色 */
      color: #333;
      line-height: 1.6;
    }
    .container {
      width: 80%;
      max-width: 800px;
      margin: 20px auto;
      background-color: #fff;
      padding: 30px;
      box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1); /* 影を強めに */
      border-radius: 10px; /* 角を丸く */
    }
    h1, h2, h3 {
      color: #333;
      margin-bottom: 20px;
      border-bottom: 2px solid #eee; /* 少し太めの線 */
      padding-bottom: 10px;
    }
    ol {
      padding-left: 20px;
    }
    li {
      margin-bottom: 20px;
    }
    img {
      max-width: 100%;
      height: auto;
      display: block;
      margin: 10px auto;
      border: 2px solid #eee; /* 少し太めの枠線 */
      border-radius: 5px; /* 角を少し丸く */
    }
    p {
      margin-bottom: 10px;
    }
    ul {
      list-style-type: disc;
      margin-left: 20px;
    }
    ul li {
      margin-bottom: 5px;
    }
  </style>
</head>
<body>
  <div class="container">
    <h1>インストール手順</h1>

    <h2>Chromeの場合</h2>
    <ol>
      <li>
        <h3>インストールアイコンをタップ</h3>
        <p>アドレスバーに表示されるインストールアイコン<span class="material-symbols-outlined">browser_updated</span>をタップします。</p>
      </li>
      <li>
        <h3>インストールを確認</h3>
        <p>インストール確認のポップアップが表示されるので、「インストール」をタップします。</p>
        <img src="path/to/chrome-step3.png" alt="インストール確認">
      </li>
      <li>
        <h3>インストール完了</h3>
        <p>ホーム画面にPWAサイトのアイコンが追加されます。</p>
        <img src="path/to/chrome-step4.png" alt="ホーム画面に追加されたアイコン">
      </li>
    </ol>

    <h2>Safariの場合</h2>
    <ol>
      <li>
        <h3>共有ボタンをタップ</h3>
        <p>画面下部にある共有ボタン<i class="bi bi-box-arrow-up"></i>をタップします。</p>
      </li>
      <li>
        <h3>ホーム画面に追加をタップ</h3>
        <p>表示されたメニューから「ホーム画面に追加<i class="bi bi-plus-square"></i>」をタップします。</p>
      </li>
      <li>
        <h3>追加をタップ</h3>
        <p>サイト名とアイコンを確認し、「追加」をタップします。</p>
        <img src="path/to/safari-step4.png" alt="追加">
      </li>
      <li>
        <h3>インストール完了</h3>
        <p>ホーム画面にPWAサイトのアイコンが追加されます。</p>
        <img src="path/to/safari-step5.png" alt="ホーム画面に追加されたアイコン">
      </li>
    </ol>

  </div>
</body>
</html>