<?php
defined('BASEPATH') or exit('No direct script access allowed');

/*
|--------------------------------------------------------------------------
| Base Site URL
|--------------------------------------------------------------------------
*/
if (isset($_SERVER['HTTP_HOST'])) {
    $base_url = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http") . "://" . $_SERVER['HTTP_HOST'];
    $base_url .= str_replace(basename($_SERVER['SCRIPT_NAME']), "", $_SERVER['SCRIPT_NAME']);
    $config['base_url'] = str_replace(' ', '%20', $base_url);
} else {
    $config['base_url'] = 'http://localhost/PHP_CodeIgniter_TradingDocument/';
}

/*
|--------------------------------------------------------------------------
| Index File
|--------------------------------------------------------------------------
*/
$config['index_page'] = '';  // Rỗng khi dùng .htaccess

/*
|--------------------------------------------------------------------------
| URI PROTOCOL
|--------------------------------------------------------------------------
*/
$config['uri_protocol'] = 'PATH_INFO';

/*
|--------------------------------------------------------------------------
| Default Language
|--------------------------------------------------------------------------
*/
$config['language'] = 'english';

/*
|--------------------------------------------------------------------------
| Default Character Set
|--------------------------------------------------------------------------
*/
$config['charset'] = 'UTF-8';

/*
|--------------------------------------------------------------------------
| Enable/Disable System Hooks
|--------------------------------------------------------------------------
*/
$config['enable_hooks'] = FALSE;

/*
|--------------------------------------------------------------------------
| Class Extension Prefix
|--------------------------------------------------------------------------
*/
$config['subclass_prefix'] = 'MY_';

/*
|--------------------------------------------------------------------------
| Composer auto-loading
|--------------------------------------------------------------------------
*/
$config['composer_autoload'] = FALSE;

/*
|--------------------------------------------------------------------------
| Allowed URL Characters
|--------------------------------------------------------------------------
*/
$config['permitted_uri_chars'] = 'a-z 0-9~%.:_\-';

/*
|--------------------------------------------------------------------------
| Enable Query Strings
|--------------------------------------------------------------------------
*/
$config['allow_get_array']         = TRUE;
$config['enable_query_strings']    = FALSE;
$config['controller_trigger']      = 'c';
$config['function_trigger']        = 'm';
$config['directory_trigger']       = 'd';

/*
|--------------------------------------------------------------------------
| Error Logging Threshold
|--------------------------------------------------------------------------
| Bật level 4 (ALL) để debug, sau đó đặt lại 1
*/
$config['log_threshold'] = 1;
$config['log_path'] = APPPATH . 'logs/';
$config['log_file_extension'] = '';
$config['log_file_permissions'] = 0644;
$config['log_date_format'] = 'Y-m-d H:i:s';

/*
|--------------------------------------------------------------------------
| Cache Directory Path
|--------------------------------------------------------------------------
*/
$config['cache_path'] = '';
$config['cache_query_string'] = FALSE;
$config['uncacheable_methods'] = array('POST');

/*
|--------------------------------------------------------------------------
| Encryption Key
|--------------------------------------------------------------------------
| FIX #B: Đọc từ biến môi trường thay vì hardcode để bảo mật hơn.
*/
$config['encryption_key'] = isset($_ENV['APP_SECRET_KEY']) ? $_ENV['APP_SECRET_KEY'] : (getenv('APP_SECRET_KEY') ?: 'MCUverse2025SecretKey!#$@');

/*
|--------------------------------------------------------------------------
| Session Variables
|--------------------------------------------------------------------------
*/
$config['sess_driver']            = 'files';
$config['sess_cookie_name']       = 'ci_session';
$config['sess_expiration']        = 7200;
$config['sess_save_path']         = NULL;
$config['sess_match_ip']          = FALSE;
$config['sess_time_to_update']    = 300;
$config['sess_regenerate_destroy'] = FALSE;

/*
|--------------------------------------------------------------------------
| Cookie Related Variables
|--------------------------------------------------------------------------
*/
$config['cookie_prefix']   = '';
$config['cookie_domain']   = '';
$config['cookie_path']     = '/';
$config['cookie_secure']   = FALSE;
$config['cookie_httponly'] = FALSE;

/*
|--------------------------------------------------------------------------
| Cross Site Request Forgery
|--------------------------------------------------------------------------
| FIX #A: Bật CSRF protection cho web form.
| API sử dụng session-based auth nên hưởng lợi từ bảo vệ này.
| Các AJAX endpoint dùng X-Requested-With header để bypass (was already handled).
*/
$config['csrf_protection']   = TRUE;
$config['csrf_token_name']   = 'csrf_token';
$config['csrf_cookie_name']  = 'csrf_cookie';
$config['csrf_expire']       = 7200;
$config['csrf_regenerate']   = TRUE;
$config['csrf_exclude_uris'] = [
    // Loại trừ toàn bộ API khỏi CSRF (dùng regex)
    '^api/.*',
    'api/auth/login',
    'api/auth/register',
    'api/posts',
    'api/posts/search',
    'api/posts/detail',
    'api/posts/create',
    'api/posts/delete',
    'api/orders',
    'api/orders/detail',
    'api/orders/request',
    'api/orders/confirm',
    'api/orders/reject',
    'api/orders/received',
    'api/orders/dispute',
    'api/orders/cancel',
    'api/orders/rate',
    'api/seller',
    'message/send_ajax',
    'message/poll',
    'message/total_unread',
    'message/send_meetup_ajax',
    'message/respond_meetup_ajax',
];

/*
|--------------------------------------------------------------------------
| Output Compression
|--------------------------------------------------------------------------
*/
$config['compress_output'] = FALSE;

/*
|--------------------------------------------------------------------------
| Master Time Reference
|--------------------------------------------------------------------------
*/
$config['time_reference'] = 'local';

/*
|--------------------------------------------------------------------------
| Rewrite PHP Short Tags
|--------------------------------------------------------------------------
*/
$config['rewrite_short_tags'] = FALSE;

/*
|--------------------------------------------------------------------------
| Reverse Proxy IPs
|--------------------------------------------------------------------------
*/
$config['proxy_ips'] = '';
