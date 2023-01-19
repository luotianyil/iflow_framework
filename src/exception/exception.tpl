<?php
    if (!function_exists('parse_suorce')) {
        function parse_suorce($source, $line) {
            $code = "";
            foreach ($source as $l => $content) {
                $code .= ($l === $line ? "<span style=\"color:red;\">$l</span>" : $l ) ." : <span class=\"errCodeContent\">".htmlspecialchars_decode(str_replace(" ", "&nbsp;", $content))."</span> <br />";
            }
            return $code;
        }
    }
?>


<!doctype html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport"
          content="width=device-width, user-scalable=no, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>系统异常 <?=$msg?></title>
    <style>
        body {
            font-family: Consolas,"Liberation Mono",Courier,Verdana,"微软雅黑",serif;
        }
        .main {
            padding: 10px;
            border: 1px solid #ccc;
        }
        .error {
            margin-bottom: 20px;
        }
        .errorCode {
            font-size: 14px;
            background: #292929;
            color: #d8d8d8;
            margin-top: 10px;
            padding: 10px;
        }
        .trace {
            font-size: 14px;
        }
    </style>
</head>
<body>
<div class="main">
    <div class="error">
        <div class="error-title">
            <div style="border-bottom: 1px solid #ccc; padding-bottom: 5px; font-size: 22px;">
                [<?=$code?>] Exception in <span style="cursor: pointer; border-bottom: 1px dashed #ccc; color: #14560c;" title="<?=$file?>"><?=basename($file)?></span> line <?=$line?>
            </div>
            <div style="font-size: 24px; margin-top: 10px;"><?=$msg?></div>
        </div>
        <div class="errorCode">
            <?=parse_suorce($source, $line)?>
        </div>
    </div>
    <div class="trace">
        <?=str_replace("\n", "<br />", $trace)?>
    </div>
</div>
</body>
</html>