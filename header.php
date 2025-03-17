<META http-equiv='Content-Type' content='text/html; charset=UTF-8'>
<META http-equiv='Content-Style-Type' content='text/css'>
<!-- ピンチzoom不可 -->
<meta name="viewport" content="width=device-width,initial-scale=1.0,minimum-scale=1.0,maximum-scale=1.0,user-scalable=no">
<link rel="apple-touch-icon" sizes="180x180" href="img/apple-touch-icon.png">
<link rel="icon" type="image/png" sizes="32x32" href="img/favicon-32x32.png">
<link rel="icon" type="image/png" sizes="16x16" href="img/favicon-16x16.png">
<link rel="mask-icon" href="img/safari-pinned-tab.svg" color="#5bbad5">
<meta name="msapplication-TileColor" content="#2b5797">
<meta name="theme-color" content="#ffffff">
<!-- Bootstrap5 CSS -->
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-9ndCyUaIbzAi2FUVXJi0CjmCapSmO7SnpJef0486qhLnuZ2cdeRhO02iuK6FUUVM" crossorigin="anonymous">
<!-- Bootstrap Javascript -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js" integrity="sha384-geWF76RCwLtnZ8qwWowPQNguL3RmwHVBC9FhGdlKrxdiJJigb/j/68SIy3Te4Bkz" crossorigin="anonymous"></script>
<!-- bootstrap-icons -->
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
<!-- オリジナル CSS -->
<link rel="stylesheet" type="text/css" href="css/st_sheet.css?<?php echo $time; ?>">
<!-- Vue.js -->
<script src="https://cdn.jsdelivr.net/npm/vue@3.4.4"></script>
<!--ajaxライブラリ-->
<script src="https://cdnjs.cloudflare.com/ajax/libs/axios/0.19.0/axios.min.js"></script>
<script>axios.defaults.baseURL = "<?php echo ROOT_URL;?>";</script>

<meta property='og:locale' content='ja_JP' />
<meta property='og:title' content='『トレーニングを記録しよう』' />
<meta property='og:type' content='website' />
<meta property='og:url' content='https://muscle.greeen-sys.com/' />
<meta property='og:image' content='https://muscle.greeen-sys.com/img/koukoku.png' />
<meta property='og:site_name' content='肉体改造ネットワーク' />
<meta property='article:author' content='https://www.facebook.com/greengreenmidori'>
<meta property='og:description' content='筋トレ記録・解析ＷＥＢアプリ' />
<meta property='fb:admins' content='100000504600659' />
<script>
    var KANKYO = <?php echo "'".EXEC_MODE."'" ;?>;
</script>

<script src="script/function.js?<?php echo $time; ?>"></script>
<link rel='manifest' href='site.webmanifest?<?php echo $time;?>'>
    <script>
        /*
        if('serviceWorker' in navigator){
        	navigator.serviceWorker.register('serviceworker.js').then(function(){
        		console_log("Service Worker is registered!!");
        	});
        }
        */
        
        /*if ('serviceWorker' in navigator) {
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
        document.getElementsByTagName('html')[0].setAttribute('ontouchstart', '');*/
    </script>