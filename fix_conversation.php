<?php
$f = 'd:/Student_HCMUE/PHP/htdocs/PHP_CodeIgniter_TradingDocument/application/views/messages/conversation.php';
$c = file_get_contents($f);
$c = str_replace(
    "    console.log('Pusher Channels đã kết nối và sẵn sàng nhận tin nhắn thời gian thực!');\n} catch (e) {",
    "    console.log('Pusher Channels đã kết nối và sẵn sàng nhận tin nhắn thời gian thực!');\n    }\n} catch (e) {",
    $c
);
file_put_contents($f, $c);
echo "Done";
