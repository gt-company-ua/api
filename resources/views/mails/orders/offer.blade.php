<!DOCTYPE html>
<html lang="ua">
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Договір публічної оферти</title>


    <style>
        @page {margin: 5rem 0 !important; padding: 5rem !important;}
        html {font-size:13px; line-height:1.3em}
        html, body {padding:0; margin:0; min-width:320px}
        body, * {font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Oxygen-Sans, Ubuntu, Cantarell, "Helvetica Neue", sans-serif;}
        * {line-height:1.3em; }
        .doc {margin: 0 auto; position:relative; overflow:hidden;}
        h1, h2, h3, h4, h5, h6 {text-align: center; margin-top:1.5em; margin-bottom:.5em; line-height: 1.2em; font-weight:600}
        h1 {font-size:1.5rem}
        h2 {font-size:1.25rem}
        img {display:inline-block; max-width:100%; max-height:100%; height:auto; line-height:1;}
        p + p {margin-top: 1rem}
        a {color:#009681; font-weight:500}
        a:hover, a:focus {color:#B3D73D}
        .num {font-weight:600; color:#111}
        .doc__header {width:100%; position:relative; margin-top:-5rem}
        .doc__header, .doc__header_bg, .doc__logo_wrap {height:7rem}
        .doc__header_bg {position:absolute; top:0; left:0; right:0; background: url(https://api.greentravel.ua/storage/pdf/bg-waves.png) top left no-repeat; z-index:1; opacity:.33}
        .doc__logo_wrap {position:absolute; top:0; left:0; right:50%; background:#fff; padding:1rem; z-index:5}
        .doc__logo {min-width:10rem; width:30vw}
        .doc__logo_link, .doc__logo_wrap {display: flex; }
        /* === это для оформления превью. Скорее всего для PDF надо будет удалить === */
        body {background:#fff}
        .doc {margin: 3rem auto; background:#fff;}
        .doc__text {padding: 2rem 5rem}
        @page { margin: 100px; }
        /* ====== */
    </style>
</head>
<body>



<div class="doc">

    <div class="doc__header">
        <div class="doc__logo_wrap">
            <a href="https://greentravel.ua" class="doc__logo_link" target="_blank">
                <img src="https://api.greentravel.ua/storage/pdf/logo_text.png" alt="" class="doc__logo">
            </a>
        </div>
        <div class="doc__header_bg"></div>
    </div>


    <div class="doc__text">
        <h1>Персональна оферта</h1>
        <p>Код підтвердження: {{ $code }}</p>
    </div>
</div>

</body>
</html>
