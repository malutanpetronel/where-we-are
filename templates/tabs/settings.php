<?php
if (!defined('ABSPATH')) {
    exit; // Blocare acces direct
}
?>
<form method="POST">
    <table class="form-table">
        <tr>
            <th><label for="company">Company</label></th>
            <td><input type="text" id="company" name="company" value="<?php echo esc_attr($company); ?>" class="regular-text" /></td>
        </tr>

        <tr>
            <th><label for="slogan">Slogan:</label></th>
            <td><input type="text" id="slogan" name="slogan" value="<?php echo esc_attr($slogan); ?>" class="regular-text" /></td>
        </tr>

        <tr>
            <th><label for="address">Address</label></th>
            <td><input type="text" id="address" name="address" value="<?php echo esc_attr($address); ?>" class="regular-text" /></td>
        </tr>

        <tr>
            <th><label for="latitude">Latitude</label></th>
            <td><input type="text" id="latitude" name="latitude" value="<?php echo esc_attr($latitude); ?>" class="regular-text" /></td>
        </tr>
        <tr>
            <th><label for="longitude">Longitude</label></th>
            <td><input type="text" id="longitude" name="longitude" value="<?php echo esc_attr($longitude); ?>" class="regular-text" /></td>
        </tr>

        <tr>
            <th><label for="zoom">Zoom Level</label></th>
            <td><input type="number" id="zoom" name="zoom" value="<?php echo esc_attr($zoom); ?>" min="1" max="22" /></td>
        </tr>
        <tr>
            <th></th>
            <td><?php wp_nonce_field( 'where_we_are_save_settings', 'where_we_are_nonce' ); ?></td>
        </tr>
    </table>
    <p><input type="submit" class="button button-primary" value="Save Settings"></p>
</form>
