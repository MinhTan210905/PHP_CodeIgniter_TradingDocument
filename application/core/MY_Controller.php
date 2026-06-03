<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Base Controller to enforce ban checks
 *
 * @property CI_Session $session
 * @property CI_Input $input
 * @property CI_DB_query_builder $db
 * @property CI_Router $router
 * @property CI_URI $uri
 * @property CI_Output $output
 */
class MY_Controller extends CI_Controller {

    public function __construct() {
        parent::__construct();

        // Check if user is logged in
        if ($this->session->userdata('logged_in')) {
            $user_id = $this->session->userdata('user_id');
            
            // Check if user is banned in database
            $user = $this->db->select('is_banned')
                             ->where('id', $user_id)
                             ->get('users')
                             ->row_array();
            
            if ($user && !empty($user['is_banned'])) {
                // Remove login session keys
                $this->session->unset_userdata(['user_id', 'username', 'full_name', 'avatar', 'role', 'logged_in']);
                
                // If this is an AJAX or API request, return JSON response
                if ($this->input->is_ajax_request() || $this->router->class === 'api' || strpos($this->uri->uri_string(), 'api/') === 0) {
                    $this->output->set_content_type('application/json');
                    $this->output->set_status_header(403);
                    echo json_encode([
                        'status' => 'error',
                        'message' => 'Tài khoản của bạn đã bị chặn! Vui lòng liên hệ Admin để biết thêm.'
                    ], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
                    exit;
                } else {
                    // Standard web request: set flash error message and redirect to login page
                    $this->session->set_flashdata('error', 'Tài khoản của bạn đã bị chặn! Vui lòng liên hệ Admin để biết thêm.');
                    redirect('auth');
                    exit;
                }
            }
        }
    }
}
