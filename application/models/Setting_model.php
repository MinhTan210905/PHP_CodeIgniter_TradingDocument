<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Setting_model extends CI_Model {

    public function __construct() {
        parent::__construct();
        $this->load->database();
        // Tự động tạo bảng settings nếu chưa tồn tại để tiện dụng cho user
        $this->db->query("CREATE TABLE IF NOT EXISTS settings (
            skey VARCHAR(50) PRIMARY KEY,
            svalue TEXT
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;");
        
        // Chèn các cài đặt mặc định nếu chưa có
        $this->db->query("INSERT IGNORE INTO settings (skey, svalue) VALUES ('auto_approve_new', '0'), ('auto_approve_edit', '0'), ('auto_approve_min_stars', '0'), ('site_announcement', 'Chào mừng đến với diễn đàn pass tài liệu của Trường Đại học Sư phạm thành phố Hồ Chí Minh')");
    }

    public function get($key, $default = '0') {
        $row = $this->db->get_where('settings', ['skey' => $key])->row_array();
        return $row ? $row['svalue'] : $default;
    }

    public function set($key, $value) {
        $exists = $this->db->get_where('settings', ['skey' => $key])->num_rows();
        if ($exists) {
            $this->db->where('skey', $key);
            return $this->db->update('settings', ['svalue' => (string)$value]);
        } else {
            return $this->db->insert('settings', ['skey' => $key, 'svalue' => (string)$value]);
        }
    }

    public function get_all() {
        $res = $this->db->get('settings')->result_array();
        $settings = [];
        foreach($res as $r) {
            $settings[$r['skey']] = $r['svalue'];
        }
        return $settings;
    }
}
