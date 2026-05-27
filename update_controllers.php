<?php
$dir = 'd:/Student_HCMUE/PHP/htdocs/PHP_CodeIgniter_TradingDocument/application/controllers/';
foreach(glob($dir.'*.php') as $f) {
    file_put_contents($f, str_replace('count_pending_for_seller', 'count_action_required', file_get_contents($f)));
}
echo "Done";
