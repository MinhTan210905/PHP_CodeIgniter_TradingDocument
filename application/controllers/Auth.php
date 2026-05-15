<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * @property CI_Session $session
 * @property CI_Input $input
 * @property Auth_model $Auth_model
 */
class Auth extends CI_Controller {

    public function __construct() {
        parent::__construct();
        $this->load->model('Auth_model');
        $this->load->library(['session', 'form_validation']);
        $this->load->helper(['form', 'url']);
    }

    // Trang đăng nhập
    public function index() {
        if ($this->session->userdata('user_id')) {
            redirect('trade');
        }
        $this->load->view('auth/login');
    }

    // Xử lý đăng nhập
    public function login_post() {
        $email    = $this->input->post('email');
        $password = $this->input->post('password');

        $user = $this->Auth_model->get_user_by_email($email);

        $is_ajax = $this->input->is_ajax_request();

        if ($user && password_verify($password, $user['password'])) {
            // Kiểm tra tài khoản bị chặn
            if (!empty($user['is_banned'])) {
                $message = 'Tài khoản của bạn đã bị chặn! Vui lòng liên hệ Admin để biết thêm.';
                if ($is_ajax) {
                    echo json_encode(['status' => 'error', 'message' => $message]);
                    return;
                }
                $this->session->set_flashdata('error', $message);
                redirect('auth');
                return;
            }
            $session_data = [
                'user_id'   => $user['id'],
                'username'  => $user['username'],
                'full_name' => $user['full_name'],
                'avatar'    => $user['avatar'],
                'role'      => $user['role'],
                'logged_in' => TRUE
            ];
            $this->session->set_userdata($session_data);

            if ($is_ajax) {
                echo json_encode(['status' => 'success', 'redirect' => site_url('trade')]);
                return;
            }
            redirect('trade');
        } else {
            $message = 'Email hoặc mật khẩu không đúng!';
            if ($is_ajax) {
                echo json_encode(['status' => 'error', 'message' => $message]);
                return;
            }
            $this->session->set_flashdata('error', $message);
            redirect('auth');
        }
    }

    // Trang đăng ký
    public function register() {
        if ($this->session->userdata('user_id')) {
            redirect('trade');
        }
        $this->load->view('auth/register');
    }

    // Xử lý đăng ký
    public function register_post() {
        $email_prefix = $this->input->post('email_prefix', TRUE);
        $username = $this->input->post('username', TRUE);
        $password = $this->input->post('password');
        $confirm  = $this->input->post('confirm_password');
        $full_name = $this->input->post('full_name', TRUE);

        // Kiểm tra trống
        if (empty($email_prefix) || empty($username) || empty($password) || empty($full_name)) {
            $this->session->set_flashdata('error', 'Vui lòng nhập đầy đủ các trường bắt buộc!');
            redirect('auth/register');
            return;
        }

        // Tạo email hoàn chỉnh
        $email = $email_prefix . '@student.hcmue.edu.vn';

        // Kiểm tra mật khẩu khớp
        if ($password !== $confirm) {
            $this->session->set_flashdata('error', 'Mật khẩu xác nhận không khớp!');
            redirect('auth/register');
            return;
        }

        // Kiểm tra email đã tồn tại
        if ($this->Auth_model->get_user_by_email($email)) {
            $this->session->set_flashdata('error', 'Email này đã được đăng ký!');
            redirect('auth/register');
            return;
        }

        // Kiểm tra username đã tồn tại
        if ($this->Auth_model->get_user_by_username($username)) {
            $this->session->set_flashdata('error', 'Tên đăng nhập đã tồn tại!');
            redirect('auth/register');
            return;
        }

        // Tạo mã OTP 6 số
        $otp = rand(100000, 999999);

        // Chuẩn bị data đăng ký
        $data = [
            'full_name' => $this->input->post('full_name', TRUE),
            'username'  => $username,
            'email'     => $email,
            'password'  => password_hash($password, PASSWORD_DEFAULT),
            'phone'     => $this->input->post('phone', TRUE),
            'phone_visible' => 0,
            'role'      => 'user',
            'otp'       => $otp,
            'otp_expires' => time() + 300 // Hết hạn sau 5 phút
        ];

        // Lưu tạm vào session
        $this->session->set_userdata('pending_reg', $data);

        // Gửi email chứa OTP dạng HTML đẹp
        $this->load->library('email');
        $this->email->initialize(['mailtype' => 'html']);

        $this->email->from($this->config->item('smtp_user') ?? 'no-reply@hcmue.edu.vn', 'HCMUE Pass Sách');
        $this->email->to($email);
        $this->email->subject('[HCMUE Pass Sách] Mã xác nhận đăng ký tài khoản của bạn');

        $full_name = htmlspecialchars($data['full_name']);
        $html_body = "
        <!DOCTYPE html>
        <html lang='vi'>
        <head><meta charset='UTF-8'><meta name='viewport' content='width=device-width,initial-scale=1'></head>
        <body style='margin:0;padding:0;background:#f0f4f8;font-family:Inter,Arial,sans-serif;'>
            <table width='100%' cellpadding='0' cellspacing='0' style='padding:40px 20px;'>
                <tr><td align='center'>
                    <table width='560' cellpadding='0' cellspacing='0' style='background:#ffffff;border-radius:16px;overflow:hidden;box-shadow:0 4px 24px rgba(0,0,0,0.08);max-width:560px;width:100%;'>
                        <tr>
                            <td style='background:linear-gradient(135deg,#003F8A,#0052B4);padding:32px 40px;text-align:center;'>
                                <h1 style='margin:0;color:#ffffff;font-size:22px;font-weight:800;letter-spacing:-0.5px;'>HCMUE Pass Sách</h1>
                                <p style='margin:6px 0 0;color:rgba(255,255,255,0.75);font-size:13px;'>Đại học Sư phạm TP.HCM</p>
                            </td>
                        </tr>
                        <tr>
                            <td style='padding:40px 40px 32px;'>
                                <p style='margin:0 0 16px;font-size:16px;color:#374151;'>Xin chào <strong>{$full_name}</strong>,</p>
                                <p style='margin:0 0 24px;font-size:14px;color:#6B7280;line-height:1.7;'>Bạn vừa yêu cầu đăng ký tài khoản trên <strong>HCMUE Pass Sách</strong>. Dưới đây là mã OTP để xác thực:</p>
                                <div style='background:#F0F5FF;border:2px dashed #003F8A;border-radius:12px;padding:28px;text-align:center;margin:0 0 28px;'>
                                    <p style='margin:0 0 8px;font-size:12px;color:#6B7280;letter-spacing:1px;text-transform:uppercase;'>Mã xác thực của bạn</p>
                                    <span style='font-size:42px;font-weight:800;color:#003F8A;letter-spacing:10px;'>{$otp}</span>
                                    <p style='margin:12px 0 0;font-size:12px;color:#9CA3AF;'>⏱ Có hiệu lực trong <strong>5 phút</strong></p>
                                </div>
                                <p style='margin:0 0 8px;font-size:13px;color:#9CA3AF;'>Nếu bạn không yêu cầu đăng ký, vui lòng bỏ qua email này.</p>
                            </td>
                        </tr>
                        <tr>
                            <td style='background:#F8FAFC;padding:20px 40px;border-top:1px solid #E5E7EB;text-align:center;'>
                                <p style='margin:0;font-size:12px;color:#9CA3AF;'>&copy; 2025 HCMUE Pass Sách &mdash; Đại học Sư phạm TP.HCM</p>
                            </td>
                        </tr>
                    </table>
                </td></tr>
            </table>
        </body>
        </html>";

        $this->email->message($html_body);

        if ($this->email->send()) {
            $this->session->set_flashdata('success', 'Đã gửi mã OTP đến email của bạn!');
            redirect('auth/verify_otp');
        } else {
            $this->session->set_flashdata('error', 'Gửi Email thất bại! Vui lòng cấu hình đúng Gmail và App Password trong file application/config/email.php');
            redirect('auth/register');
            return;
        }
    }

    // Trang nhập mã OTP
    public function verify_otp() {
        if (!$this->session->userdata('pending_reg')) {
            redirect('auth/register');
        }
        $this->load->view('auth/verify_otp');
    }

    // Xử lý xác nhận OTP
    public function verify_otp_post() {
        $pending_reg = $this->session->userdata('pending_reg');
        if (!$pending_reg) {
            redirect('auth/register');
        }

        $input_otp = $this->input->post('otp');

        // Kiểm tra OTP rỗng
        if (empty($input_otp)) {
            $this->session->set_flashdata('error', 'Vui lòng nhập mã OTP!');
            redirect('auth/verify_otp');
            return;
        }

        // Kiểm tra hết hạn
        if (time() > $pending_reg['otp_expires']) {
            $this->session->unset_userdata('pending_reg');
            $this->session->set_flashdata('error', 'Mã OTP đã hết hạn! Vui lòng đăng ký lại.');
            redirect('auth/register');
            return;
        }

        // Kiểm tra khớp OTP
        if ((string)$input_otp !== (string)$pending_reg['otp']) {
            $this->session->set_flashdata('error', 'Mã OTP không chính xác!');
            redirect('auth/verify_otp');
            return;
        }

        // Đăng ký thành công, xóa trường otp khỏi mảng data để insert db
        unset($pending_reg['otp']);
        unset($pending_reg['otp_expires']);

        // Insert vào DB
        $this->Auth_model->create_user($pending_reg);
        
        // Xóa session tạm
        $this->session->unset_userdata('pending_reg');

        $this->session->set_flashdata('success', 'Đăng ký thành công! Hãy đăng nhập.');
        redirect('auth');
    }

    // Đăng xuất
    public function logout() {
        $this->session->sess_destroy();
        redirect('auth');
    }

    // Gửi lại mã OTP
    public function resend_otp() {
        $pending_reg = $this->session->userdata('pending_reg');
        if (!$pending_reg) {
            redirect('auth/register');
            return;
        }

        // Kiểm tra phải đợi 5 phút kể từ lần gửi trước (gửi_lại chỉ khi OTP đã hết hạn hoặc sắp hết)
        // Lấy mã OTP mới và reset thời gian
        $otp = rand(100000, 999999);
        $pending_reg['otp']         = $otp;
        $pending_reg['otp_expires'] = time() + 300;
        $this->session->set_userdata('pending_reg', $pending_reg);

        $email     = $pending_reg['email'];
        $full_name = htmlspecialchars($pending_reg['full_name']);

        // Gửi email OTP HTML đẹp
        $this->load->library('email');
        $this->email->initialize(['mailtype' => 'html']);
        $this->email->from($this->config->item('smtp_user') ?? 'no-reply@hcmue.edu.vn', 'HCMUE Pass Sách');
        $this->email->to($email);
        $this->email->subject('[HCMUE Pass Sách] Mã OTP mới của bạn');

        $html_body = "
        <!DOCTYPE html>
        <html lang='vi'>
        <head><meta charset='UTF-8'></head>
        <body style='margin:0;padding:0;background:#f0f4f8;font-family:Inter,Arial,sans-serif;'>
            <table width='100%' cellpadding='0' cellspacing='0' style='padding:40px 20px;'>
                <tr><td align='center'>
                    <table width='560' cellpadding='0' cellspacing='0' style='background:#ffffff;border-radius:16px;overflow:hidden;box-shadow:0 4px 24px rgba(0,0,0,0.08);max-width:560px;width:100%;'>
                        <tr>
                            <td style='background:linear-gradient(135deg,#003F8A,#0052B4);padding:32px 40px;text-align:center;'>
                                <h1 style='margin:0;color:#ffffff;font-size:22px;font-weight:800;'>HCMUE Pass Sách</h1>
                                <p style='margin:6px 0 0;color:rgba(255,255,255,0.75);font-size:13px;'>Đại học Sư phạm TP.HCM</p>
                            </td>
                        </tr>
                        <tr>
                            <td style='padding:40px 40px 32px;'>
                                <p style='margin:0 0 16px;font-size:16px;color:#374151;'>Xin chào <strong>{$full_name}</strong>,</p>
                                <p style='margin:0 0 24px;font-size:14px;color:#6B7280;line-height:1.7;'>Dưới đây là <strong>mã OTP mới</strong> của bạn:</p>
                                <div style='background:#F0F5FF;border:2px dashed #003F8A;border-radius:12px;padding:28px;text-align:center;margin:0 0 28px;'>
                                    <p style='margin:0 0 8px;font-size:12px;color:#6B7280;letter-spacing:1px;text-transform:uppercase;'>Mã xác thực</p>
                                    <span style='font-size:42px;font-weight:800;color:#003F8A;letter-spacing:10px;'>{$otp}</span>
                                    <p style='margin:12px 0 0;font-size:12px;color:#9CA3AF;'>⏱ Có hiệu lực trong <strong>5 phút</strong></p>
                                </div>
                            </td>
                        </tr>
                        <tr>
                            <td style='background:#F8FAFC;padding:20px 40px;border-top:1px solid #E5E7EB;text-align:center;'>
                                <p style='margin:0;font-size:12px;color:#9CA3AF;'>&copy; 2025 HCMUE Pass Sách</p>
                            </td>
                        </tr>
                    </table>
                </td></tr>
            </table>
        </body></html>";

        $this->email->message($html_body);

        if ($this->email->send()) {
            $this->session->set_flashdata('success', 'Đã gửi lại mã OTP mới! Kiểm tra hộp thư của bạn.');
        } else {
            $this->session->set_flashdata('error', 'Gửi lại OTP thất bại!');
        }

        redirect('auth/verify_otp');
    }

    // ==========================================
    // KHÔI PHỤC MẬT KHẨU
    // ==========================================

    public function forgot_password() {
        if ($this->session->userdata('logged_in')) {
            redirect('home');
            return;
        }
        $this->load->view('auth/forgot_password');
    }

    public function forgot_password_post() {
        $email = $this->input->post('email', TRUE);

        $user = $this->Auth_model->get_user_by_email($email);
        if (!$user) {
            $this->session->set_flashdata('error', 'Không tìm thấy tài khoản với email này!');
            redirect('auth/forgot_password');
            return;
        }

        // Tạo OTP
        $otp = rand(100000, 999999);
        $forgot_data = [
            'email' => $email,
            'otp'   => $otp,
            'otp_expires' => time() + 300 // 5 minutes
        ];
        $this->session->set_userdata('forgot_pass', $forgot_data);

        // Gửi email
        $this->load->library('email');
        $this->email->initialize(['mailtype' => 'html']);
        $this->email->from($this->config->item('smtp_user') ?? 'no-reply@hcmue.edu.vn', 'HCMUE Pass Sách');
        $this->email->to($email);
        $this->email->subject('[HCMUE Pass Sách] Mã khôi phục mật khẩu');

        $html_body = "
        <!DOCTYPE html>
        <html lang='vi'>
        <head><meta charset='UTF-8'></head>
        <body style='margin:0;padding:0;background:#f0f4f8;font-family:Inter,Arial,sans-serif;'>
            <table width='100%' cellpadding='0' cellspacing='0' style='padding:40px 20px;'>
                <tr><td align='center'>
                    <table width='560' cellpadding='0' cellspacing='0' style='background:#ffffff;border-radius:16px;overflow:hidden;box-shadow:0 4px 24px rgba(0,0,0,0.08);max-width:560px;width:100%;'>
                        <tr>
                            <td style='background:linear-gradient(135deg,#003F8A,#0052B4);padding:32px 40px;text-align:center;'>
                                <h1 style='margin:0;color:#ffffff;font-size:22px;font-weight:800;'>HCMUE Pass Sách</h1>
                                <p style='margin:6px 0 0;color:rgba(255,255,255,0.75);font-size:13px;'>Khôi phục mật khẩu</p>
                            </td>
                        </tr>
                        <tr>
                            <td style='padding:40px 40px 32px;'>
                                <p style='margin:0 0 16px;font-size:16px;color:#374151;'>Xin chào,</p>
                                <p style='margin:0 0 24px;font-size:14px;color:#6B7280;line-height:1.7;'>Bạn đã yêu cầu khôi phục mật khẩu. Dưới đây là <strong>mã OTP</strong> của bạn:</p>
                                <div style='background:#F0F5FF;border:2px dashed #003F8A;border-radius:12px;padding:28px;text-align:center;margin:0 0 28px;'>
                                    <p style='margin:0 0 8px;font-size:12px;color:#6B7280;letter-spacing:1px;text-transform:uppercase;'>Mã xác thực</p>
                                    <span style='font-size:42px;font-weight:800;color:#003F8A;letter-spacing:10px;'>{$otp}</span>
                                    <p style='margin:12px 0 0;font-size:12px;color:#9CA3AF;'>⏱ Có hiệu lực trong <strong>5 phút</strong></p>
                                </div>
                            </td>
                        </tr>
                    </table>
                </td></tr>
            </table>
        </body></html>";

        $this->email->message($html_body);

        if ($this->email->send()) {
            $this->session->set_flashdata('success', 'Mã OTP đã được gửi đến email của bạn.');
            redirect('auth/verify_forgot_password');
        } else {
            $this->session->set_flashdata('error', 'Không thể gửi email lúc này. Vui lòng thử lại sau.');
            redirect('auth/forgot_password');
        }
    }

    public function verify_forgot_password() {
        if (!$this->session->userdata('forgot_pass')) {
            redirect('auth/forgot_password');
            return;
        }
        $this->load->view('auth/verify_forgot_password');
    }

    public function verify_forgot_password_post() {
        $forgot_data = $this->session->userdata('forgot_pass');
        if (!$forgot_data) {
            redirect('auth/forgot_password');
            return;
        }

        $input_otp = $this->input->post('otp', TRUE);

        if (time() > $forgot_data['otp_expires']) {
            $this->session->set_flashdata('error', 'Mã OTP đã hết hạn! Vui lòng yêu cầu lại.');
            redirect('auth/forgot_password');
            return;
        }

        if ($input_otp != $forgot_data['otp']) {
            $this->session->set_flashdata('error', 'Mã OTP không chính xác!');
            redirect('auth/verify_forgot_password');
            return;
        }

        // OTP đúng, cho phép reset password
        $this->session->set_userdata('reset_pass_allowed', $forgot_data['email']);
        redirect('auth/reset_password');
    }

    public function reset_password() {
        if (!$this->session->userdata('reset_pass_allowed')) {
            redirect('auth/login');
            return;
        }
        $this->load->view('auth/reset_password');
    }

    public function reset_password_post() {
        $email = $this->session->userdata('reset_pass_allowed');
        if (!$email) {
            redirect('auth/login');
            return;
        }

        $password = $this->input->post('password');
        $confirm  = $this->input->post('confirm_password');

        if ($password !== $confirm) {
            $this->session->set_flashdata('error', 'Mật khẩu xác nhận không khớp!');
            redirect('auth/reset_password');
            return;
        }

        $user = $this->Auth_model->get_user_by_email($email);
        if ($user) {
            $this->Auth_model->update_user($user['id'], [
                'password' => password_hash($password, PASSWORD_DEFAULT)
            ]);
            
            // Xóa session
            $this->session->unset_userdata('forgot_pass');
            $this->session->unset_userdata('reset_pass_allowed');

            $this->session->set_flashdata('success', 'Mật khẩu đã được thay đổi thành công! Vui lòng đăng nhập.');
            redirect('auth/login');
        } else {
            $this->session->set_flashdata('error', 'Đã xảy ra lỗi, vui lòng thử lại.');
            redirect('auth/forgot_password');
        }
    }
}
