<?php
if (!defined('ABSPATH')) {
    exit; // Blocare acces direct
}
?>

<h1><?php esc_html_e('Where we are', 'where-we-are'); ?></h1>
<p><?php esc_html_e('Set your map location on a OpenStreet MAP, and let your customers find you easy and see directions from where they are to your location.', 'where-we-are'); ?></p>
<!--    <h2>About "Where We Are" Plugin</h2>-->
<!--    <p>This plugin allows you to display your location on a map with customizable settings.</p>-->
<p>Version: <?php echo esc_html($version); ?></p>
<p>Author: <?php echo esc_html($author); ?></p>
<p><a href="https://www.webnou.ro" target="_blank"><?php echo esc_html__("Visit our website", "where-we-are") ?></a></p>

<!-- Display the logo here -->
<img src="<?php echo esc_url(PPWWA_URL . 'assets/images/logo.png'); ?>" alt="<?php echo esc_attr__('Where we are Plugin Logo', 'where-we-are'); ?>" style="max-width: 200px;">
