<?php
if (!defined('ABSPATH')) {
    exit; // Blocare acces direct
}
?>
<h1><?php esc_html_e('Unlock Where we are', 'where-we-are'); ?></h1>
<p><?php esc_html_e('Remove the attribution at the bottom of the map.', 'where-we-are'); ?></p>

<?php if ((bool) $remove_attribution): ?>
    <div class="updated">
        <p><?php echo esc_html_e("Attribution was removed. Thank you for your support!", 'where-we-are') ?></p>
    </div>
    <p><?php echo esc_html__("Attribution was removed. Thank you for your support!", 'where-we-are') ?></p>
<?php else: ?>
    <div class="notice notice-warning">
        <p><?php echo esc_html__("You can remove the attribution from the map by making a one-time payment. Click the button below to proceed.", 'where-we-are') ?></p>
    </div>
    <p><?php echo esc_html__("You can remove the attribution from the map by making a one-time payment. Click the button below to proceed.", 'where-we-are') ?></p>
    <form action="<?php echo esc_url(home_url('/buy/initiate')); ?>" method="POST">
<!--    <form action="--><?php //echo esc_url('http://wp1.local/product/where-we-are/'); ?><!--" method="POST">-->
        <!-- Display the call to action here -->
        <img src="<?php echo esc_html(PPWWA_URL) . 'assets/images/remove.png'; ?>" alt="Plugin Logo" style="max-width: 200px;">
        <p><a href="https://www.webnou.ro" target="_blank"><?php echo esc_html__("Visit our website", 'where-we-are') ?></a></p>
        <br/>
        <input type="hidden" name="user_id" value="<?php echo esc_attr(get_current_user_id()); ?>">
        <button type="submit" class="button button-primary"><?php echo esc_html__("Remove Attribution", 'where-we-are') ?></button>
    </form>
<?php endif; ?>
