
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <title>Email Template</title>

    <style type="text/css">
        #email {
            font-family: Helvetica, Arial, sans-serif; font-size: 16px; color: #222721; line-height: 1.33; -webkit-text-size-adjust: 100%; -ms-text-size-adjust: 100%;
        }

        #email table, #email td {
            border-collapse: collapse !important;
        }

        #email a {
            text-decoration: none; color: #009383;
        }

        #email img {
            display: inline-block; border:0; line-height:1; outline: none; text-decoration: none; max-width: 100%; height: auto;
        }

        #email .container {
            max-width: 780px; width: 100%;
        }

        #email .content {
            padding: 40px 40px;
        }

        #email h1 {
            margin: 0 0 .75em; line-height:1.1;
        }

        #email p {
            margin: 0 0 .5em;
        }

        #email code {
            font-weight:600; padding:.25em .5em; border-radius:.5em; background:#eee; color:#009383;
        }

        #email .header {
            text-align: center;
        }

        #email .footer {
            text-align: center; font-size:.875em;
        }

        #email .footer__contacts {
            color: #888;
        }
    </style>
</head>


<body id="email" style="margin: 0; padding: 0;">
<table border="0" cellpadding="0" cellspacing="0" width="100%">
    <tr><td style="padding: 20px;">


            <table class="container" border="0" cellpadding="0" cellspacing="0">
                <tr>
                    <td class="header">
                        <a href="https://greentravel.ua/ua/">
                            <img width="780" src="https://greentravel.ua/email_files/email__header.jpg?v=3" alt="">
                        </a>
                    </td>
                </tr>


                <tr>
                    <td class="content" style="text-align:center">
                        <h1>Персональна оферта</h1>
                        <p>Код підтвердження: <code>{{ $code }}</code></p>
                    </td>
                </tr>


                <tr>
                    <td class="footer">
                        <a href="https://greentravel.ua/ua/" style="display:inline-block; margin-bottom:20px">
                            <img width="780" src="https://greentravel.ua/email_files/email__footer.jpg" alt="">
                        </a>

                        <div class="footer__contacts">
                            <p style="font-weight:600"><a href="https://greentravel.ua/ua/">🌐 greentravel.ua</a> <span style="opacity:.3; padding:0 1em">|</span> 📱 <a href="tel:0800217675">0800217675</a> (безкоштовний)</p>
                            <p>Твій страховий друг - green travel&trade;.<br>Працюємо для вас 24/7.</p>
                        </div>

                    </td>
                </tr>
            </table>


        </td></tr>
</table>
</body>

</html>
