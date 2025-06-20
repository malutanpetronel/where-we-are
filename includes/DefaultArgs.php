<?php

namespace includes;

class DefaultArgs {
    private $args;

    public function __construct() {
        $this->args = array(
            'version' => 'Unknown',
            'author'  => 'Unknown',
        );

        $this->load_version_info();
    }

    private function load_version_info() {
        $version_file = dirname(__DIR__) . '/version.json';

        global $wp_filesystem;
        if (!function_exists('WP_Filesystem')) {
            require_once ABSPATH . 'wp-admin/includes/file.php';
        }

        if (WP_Filesystem()) {
            $version_data = json_decode($wp_filesystem->get_contents($version_file), true);
            if (isset($version_data['version'])) {
                $this->args['version'] = esc_html($version_data['version']);
            }
            if (isset($version_data['author'])) {
                $this->args['author'] = esc_html($version_data['author']);
            }
        }
    }

    public function get_args() {
        return $this->args;
    }
}
