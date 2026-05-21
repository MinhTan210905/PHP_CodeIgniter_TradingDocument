<?php
defined('BASEPATH') OR exit('No direct script access allowed');

// Trang chủ
$route['default_controller']   = 'trade';
$route['404_override']         = '';
$route['translate_uri_dashes'] = FALSE;

// Auth (Xác thực)
$route['auth']                           = 'auth/index';
$route['auth/login']                     = 'auth/index';
$route['auth/login_post']                = 'auth/login_post';
$route['auth/register']                  = 'auth/register';
$route['auth/register_post']             = 'auth/register_post';
$route['auth/logout']                    = 'auth/logout';
$route['auth/verify_otp']                = 'auth/verify_otp';
$route['auth/verify_otp_post']           = 'auth/verify_otp_post';
$route['auth/resend_otp']                = 'auth/resend_otp';
$route['auth/forgot_password']           = 'auth/forgot_password';
$route['auth/forgot_password_post']      = 'auth/forgot_password_post';
$route['auth/verify_forgot_password']    = 'auth/verify_forgot_password';
$route['auth/verify_forgot_password_post'] = 'auth/verify_forgot_password_post';
$route['auth/reset_password']            = 'auth/reset_password';
$route['auth/reset_password_post']       = 'auth/reset_password_post';

// Trade (Sách / Tài liệu)
$route['trade']                          = 'trade/index';
$route['trade/create']                   = 'trade/create';
$route['trade/detail/(:num)']            = 'trade/detail/$1';
$route['trade/update_status/(:num)']     = 'trade/update_status/$1';
$route['trade/delete/(:num)']            = 'trade/delete/$1';

// Comment (Bình luận)
$route['comment/add/(:num)']             = 'comment/add/$1';
$route['comment/delete/(:num)/(:num)']   = 'comment/delete/$1/$2';

// Rating (Đánh giá)
$route['rating/add/(:num)']              = 'rating/add/$1';

// Message (Chat & Realtime)
$route['message/inbox']                  = 'message/inbox';
$route['message/conversation/(:num)']    = 'message/conversation/$1';
$route['message/send']                   = 'message/send';
$route['message/send_ajax']              = 'message/send_ajax';
$route['message/poll/(:num)']            = 'message/poll_messages/$1';
$route['message/total_unread']          = 'message/total_unread';

// Profile (Cá nhân)
$route['profile']                        = 'profile/index';
$route['profile/toggle_phone']           = 'profile/toggle_phone';
$route['profile/update_phone']           = 'profile/update_phone';

// Admin (Quản lý)
$route['admin']                          = 'admin/index';
$route['admin/check_updates']            = 'admin/check_updates';
$route['admin/users']                    = 'admin/users';
$route['admin/delete_post/(:num)']       = 'admin/delete_post/$1';
$route['admin/approve_post/(:num)']      = 'admin/approve_post/$1';
$route['admin/reject_post/(:num)']       = 'admin/reject_post/$1';
$route['admin/toggle_role/(:num)']       = 'admin/toggle_role/$1';
$route['admin/ban_user/(:num)']          = 'admin/ban_user/$1';
$route['admin/unban_user/(:num)']        = 'admin/unban_user/$1';
$route['admin/delete_user/(:num)']       = 'admin/delete_user/$1';
$route['admin/edit_user_post/(:num)']    = 'admin/edit_user_post/$1';
$route['admin/categories']               = 'admin/categories';
$route['admin/add_category']             = 'admin/add_category';
$route['admin/edit_category/(:num)']     = 'admin/edit_category/$1';
$route['admin/delete_category/(:num)']   = 'admin/delete_category/$1';
$route['admin/payments']                 = 'admin/payments';
$route['admin/approve_withdrawal/(:num)']= 'admin/approve_withdrawal/$1';
$route['admin/reject_withdrawal/(:num)'] = 'admin/reject_withdrawal/$1';
$route['admin/confirm_payment/(:num)']   = 'admin/confirm_payment/$1';
$route['admin/update_settings']          = 'admin/update_settings';

// Orders (Đơn hàng)
$route['orders']                         = 'orders/index';
$route['orders/detail/(:num)']           = 'orders/detail/$1';
$route['orders/request/(:num)']          = 'orders/request_buy/$1';
$route['orders/confirm/(:num)']          = 'orders/confirm/$1';
$route['orders/reject/(:num)']           = 'orders/reject/$1';
$route['orders/received/(:num)']         = 'orders/received/$1';
$route['orders/dispute/(:num)']          = 'orders/dispute/$1';
$route['orders/cancel/(:num)']           = 'orders/cancel/$1';
$route['orders/rate/(:num)']             = 'orders/rate/$1';
$route['orders/submit_rating/(:num)']    = 'orders/submit_rating/$1';
$route['orders/checkout/(:num)']         = 'orders/checkout/$1';
$route['orders/process_checkout/(:num)'] = 'orders/process_checkout/$1';
$route['orders/delivered/(:num)']        = 'orders/delivered/$1';

// Seller (Sàn người bán)
$route['seller/(:num)']                  = 'seller/view/$1';

// Wallet (Ví HCMUEPay)
$route['wallet']                         = 'wallet/index';
$route['wallet/deposit']                 = 'wallet/deposit';
$route['wallet/withdraw']                = 'wallet/withdraw';

// API — RESTful endpoints
$route['api/auth/login']                 = 'api/login';
$route['api/auth/register']              = 'api/register';
$route['api/posts']                      = 'api/posts';
$route['api/posts/search']               = 'api/search';
$route['api/posts/detail/(:num)']        = 'api/detail/$1';
$route['api/posts/create']               = 'api/create_post_api';
$route['api/posts/delete/(:num)']        = 'api/delete_post_api/$1';
$route['api/orders']                     = 'api/orders_list';
$route['api/orders/detail/(:num)']       = 'api/order_detail/$1';
$route['api/orders/request/(:num)']      = 'api/order_request/$1';
$route['api/orders/confirm/(:num)']      = 'api/order_confirm/$1';
$route['api/orders/reject/(:num)']       = 'api/order_reject/$1';
$route['api/orders/received/(:num)']     = 'api/order_received/$1';
$route['api/orders/dispute/(:num)']      = 'api/order_dispute/$1';
$route['api/orders/cancel/(:num)']       = 'api/order_cancel/$1';
$route['api/orders/rate/(:num)']         = 'api/order_rate/$1';
$route['api/seller/(:num)']              = 'api/seller_info/$1';
$route['api/seller/(:num)/posts']        = 'api/seller_posts/$1';
$route['api/seller/(:num)/ratings']      = 'api/seller_ratings/$1';
