<?php
class PFT_Admin {
    public function __construct() {
        add_action('admin_menu', array($this, 'add_admin_menu'));
        add_action('admin_init', array($this, 'register_settings'));
    }

    public function add_admin_menu() {
        add_menu_page(
            'Personal Finance Tracker Settings',
            'Finance Tracker',
            'manage_options',
            'pft-settings',
            array($this, 'render_settings_page'),
            'dashicons-chart-area',
            30
        );
    }

    public function register_settings() {
        register_setting('pft_settings_group', 'pft_currency_symbol');
    }

    public function render_settings_page() {
        ?>
        <div class="wrap">
            <h1>Personal Finance Tracker Settings</h1>
            <form method="post" action="options.php">
                <?php
                settings_fields('pft_settings_group');
                do_settings_sections('pft_settings_group');
                ?>
                <table class="form-table">
                    <tr valign="top">
                        <th scope="row">Currency Symbol</th>
                        <td><input type="text" name="pft_currency_symbol" value="<?php echo esc_attr(get_option('pft_currency_symbol', '$')); ?>" /></td>
                    </tr>
                </table>
                <?php submit_button(); ?>
            </form>
        </div>
        <?php
    }
}