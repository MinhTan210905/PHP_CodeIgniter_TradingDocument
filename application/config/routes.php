<?php
defined('BASEPATH') OR exit('No direct script access allowed');

// Trang chủ
$route['default_controller']  = 'trade';
$route['404_override']        = '';
$route['translate_uri_dashes'] = FALSE;

// Auth
$route['auth']                 = 'auth/index';
$route['auth/login']           = 'auth/index';
$route['auth/login_post']      = 'auth/login_post';
$route['auth/register']        = 'auth/register';
$route['auth/register_post']   = 'auth/register_post';
$route['auth/logout']          = 'auth/logout';

// Trade
$route['trade']                      = 'trade/index';
$route['trade/create']               = 'trade/create';
$route['trade/detail/(:num)']        = 'trade/detail/$1';
$route['trade/update_status/(:num)'] = 'trade/update_status/$1';
$route['trade/delete/(:num)']        = 'trade/delete/$1';

// Comment
$route['comment/add/(:num)']         = 'comment/add/$1';
$route['comment/delete/(:num)/(:num)'] = 'comment/delete/$1/$2';

// Rating
$route['rating/add/(:num)']          = 'rating/add/$1';

// Message (Chat)
$route['message/inbox']              = 'message/inbox';
$route['message/conversation/(:num)'] = 'message/conversation/$1';
$route['message/send']               = 'message/send';

// Profile
$route['profile']                    = 'profile/index';
$route['profile/toggle_phone']       = 'profile/toggle_phone';
$route['profile/update_phone']       = 'profile/update_phone';

// Admin
$route['admin']                        = 'admin/index';
$route['admin/users']                  = 'admin/users';
$route['admin/delete_post/(:num)']     = 'admin/delete_post/$1';
$route['admin/approve_post/(:num)']    = 'admin/approve_post/$1';
$route['admin/reject_post/(:num)']     = 'admin/reject_post/$1';
$route['admin/toggle_role/(:num)']     = 'admin/toggle_role/$1';

// API — RESTful endpoints
// Auth
$route['api/auth/login']               = 'api/login';
$route['api/auth/register']            = 'api/register';
// Posts
$route['api/posts']                    = 'api/posts';
$route['api/posts/search']             = 'api/search';
$route['api/posts/detail/(:num)']      = 'api/detail/$1';
$route['api/posts/create']             = 'api/create_post_api';
$route['api/posts/delete/(:num)']      = 'api/delete_post_api/$1';
// Orders
$route['api/orders']                   = 'api/orders_list';
$route['api/orders/detail/(:num)']     = 'api/order_detail/$1';
$route['api/orders/request/(:num)']    = 'api/order_request/$1';
$route['api/orders/confirm/(:num)']    = 'api/order_confirm/$1';
$route['api/orders/reject/(:num)']     = 'api/order_reject/$1';
$route['api/orders/received/(:num)']   = 'api/order_received/$1';
$route['api/orders/dispute/(:num)']    = 'api/order_dispute/$1';
$route['api/orders/cancel/(:num)']     = 'api/order_cancel/$1';
$route['api/orders/rate/(:num)']       = 'api/order_rate/$1';
// Seller
$route['api/seller/(:num)']            = 'api/seller_info/$1';
$route['api/seller/(:num)/posts']      = 'api/seller_posts/$1';
$route['api/seller/(:num)/ratings']    = 'api/seller_ratings/$1';

// Orders (Đơn hàng — Shopee-style)
$route['orders']                           = 'orders/index';
$route['orders/detail/(:num)']             = 'orders/detail/$1';
$route['orders/request/(:num)']            = 'orders/request_buy/$1';
$route['orders/confirm/(:num)']            = 'orders/confirm/$1';
$route['orders/reject/(:num)']             = 'orders/reject/$1';
$route['orders/received/(:num)']           = 'orders/received/$1';
$route['orders/dispute/(:num)']            = 'orders/dispute/$1';
$route['orders/cancel/(:num)']             = 'orders/cancel/$1';
$route['orders/rate/(:num)']               = 'orders/rate/$1';
$route['orders/submit_rating/(:num)']      = 'orders/submit_rating/$1';

// Seller storefront (Sàn người bán)
$route['seller/(:num)']                    = 'seller/view/$1';

