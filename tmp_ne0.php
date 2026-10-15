<?php
$h = file_get_contents('specs/notificaciones_empleador.html');
preg_match('/<style>(.*?)<\/style>/s', $h, $m);
file_put_contents('C:/Users/PC/AppData/Local/Temp/opencode/emp4_style.css', $m[1]);
$p = strpos($h, '<main'); $q = strpos($h, '</main>', $p);
file_put_contents('C:/Users/PC/AppData/Local/Temp/opencode/emp4_main.html', substr($h, $p, $q - $p + 7));
echo "style=".strlen($m[1])." main=".($q-$p+7)."\n";
