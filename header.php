<?php
// 共通ヘッダー
date_default_timezone_set('Asia/Tokyo');
require "./vendor/autoload.php";

//.envの取得
$dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
$dotenv->load();

define("MAIN_DOMAIN",$_ENV["MAIN_DOMAIN"]);
define("ROOT_URL","http://".MAIN_DOMAIN."/");

?>
<META http-equiv='Content-Type' content='text/html; charset=UTF-8'>
<META http-equiv='Content-Style-Type' content='text/css'>
<!-- ピンチzoom不可 -->
<meta name="viewport" content="width=device-width,initial-scale=1.0,minimum-scale=1.0,maximum-scale=1.0,user-scalable=no">
<link rel="apple-touch-icon" sizes="180x180" href="img/apple-touch-icon.png">
<link rel="icon" type="image/png" sizes="32x32" href="img/favicon-32x32.png">
<link rel="icon" type="image/png" sizes="16x16" href="img/favicon-16x16.png">
<link rel="manifest" href="/site.webmanifest">
<link rel="mask-icon" href="img/safari-pinned-tab.svg" color="#5bbad5">
<meta name="msapplication-TileColor" content="#2b5797">
<meta name="theme-color" content="#ffffff">




<link rel="stylesheet" type="text/css" href="css/st_sheet.css">
<link href="//maxcdn.bootstrapcdn.com/font-awesome/4.7.0/css/font-awesome.min.css" rel="stylesheet" integrity="sha384-wvfXpqpZZVQGK6TAh5PVlGOfQNHSoD2xbE+QkPxCAFlNEevoEH3Sl0sibVcOQVnN" crossorigin="anonymous">
<!--最新のjqueryらしい-->
<script src="//code.jquery.com/jquery-latest.min.js"></script>
<script src="js/original.js"></script>
<script type="text/javascript" src="js/flotr2.min.js"></script>

<!-- Bootstrap5 CSS -->
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-9ndCyUaIbzAi2FUVXJi0CjmCapSmO7SnpJef0486qhLnuZ2cdeRhO02iuK6FUUVM" crossorigin="anonymous">
<!-- Bootstrap Javascript -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js" integrity="sha384-geWF76RCwLtnZ8qwWowPQNguL3RmwHVBC9FhGdlKrxdiJJigb/j/68SIy3Te4Bkz" crossorigin="anonymous"></script>
<!-- fontawesome -->
<link href="css/FontAwesome/6.1.1-web/css/all.css" rel="stylesheet">
<!-- オリジナル CSS -->
<!-- Vue.js -->
<script src="https://unpkg.com/vue@next"></script>
<script src="https://unpkg.com/vue-cookies@1.8.2/vue-cookies.js"></script>
<!--ajaxライブラリ-->
<script src="https://cdnjs.cloudflare.com/ajax/libs/axios/0.19.0/axios.min.js"></script>
<script>axios.defaults.baseURL = <?php echo "'".ROOT_URL."'" ?>;</script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/decimal.js/9.0.0/decimal.min.js"></script><!--小数演算ライブラリ-->





<meta property='og:locale' content='ja_JP' />
<meta property='og:title' content='『トレーニングを記録しよう』' />
<meta property='og:type' content='website' />
<meta property='og:url' content='https://green-island.mixh.jp/training_test/index.php' />
<meta property='og:image' content='http://green-island.mixh.jp/training_test/img/koukoku.png' />
<meta property='og:site_name' content='肉体改造ネットワーク' />
<meta property='article:author' content='https://www.facebook.com/greengreenmidori'>
<meta property='og:description' content='筋トレ記録・解析ＷＥＢアプリ' />
<meta property='fb:admins' content='100000504600659' />

<link rel='manifest' href='site.webmanifest'>
    <script>
        /*
        if('serviceWorker' in navigator){
        	navigator.serviceWorker.register('serviceworker.js').then(function(){
        		console_log("Service Worker is registered!!");
        	});
        }
        */
        
        if ('serviceWorker' in navigator) {
            navigator.serviceWorker.register('serviceworker.js')
                .then(registration => {
                    // 登録成功
                    console_log("Service Worker is registered!!");
                    
                    //serviceworker.js　の更新確認(bit単位で比較し相違があったら更新する。らしい)
                    registration.onupdatefound = function() {
                        console_log('Service Worker is Updated');
                        registration.update();
                    }
                })
                .catch(err => {
                    // 登録失敗
                    console_log("Service Worker is Oops!!");
            });
        }

        if(window.matchMedia('(display-mode: standalone)').matches){
            // ここにPWA環境下でのみ実行するコードを記述
        }
        //スマフォで:active :hover を有効に
        document.getElementsByTagName('html')[0].setAttribute('ontouchstart', '');
    </script>