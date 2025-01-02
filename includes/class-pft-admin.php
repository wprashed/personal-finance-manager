<?php
/**
 * Admin functionality for Personal Finance Tracker
 *
 * @package PersonalFinanceTracker
 */

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

class PFT_Admin {
    public function __construct() {
        add_action('admin_menu', array($this, 'add_admin_menu'));
        add_action('admin_init', array($this, 'register_settings'));
        add_action('admin_enqueue_scripts', array($this, 'enqueue_admin_scripts'));
    }

    public function add_admin_menu() {
        add_menu_page(
            __('Personal Finance Tracker Settings', 'personal-finance-tracker'),
            __('Finance Tracker', 'personal-finance-tracker'),
            'manage_options',
            'pft-settings',
            array($this, 'render_settings_page'),
            'dashicons-chart-area',
            30
        );

        add_submenu_page(
            'pft-settings',
            __('Manage Categories', 'personal-finance-tracker'),
            __('Categories', 'personal-finance-tracker'),
            'manage_options',
            'pft-categories',
            array($this, 'render_categories_page')
        );
    }

    public function register_settings() {
        register_setting('pft_settings_group', 'pft_currency_symbol');
        register_setting('pft_settings_group', 'pft_date_format');
    }

    public function enqueue_admin_scripts($hook) {
        if ('toplevel_page_pft-settings' !== $hook && 'finance-tracker_page_pft-categories' !== $hook) {
            return;
        }

        wp_enqueue_style('pft-admin-styles', PFT_PLUGIN_URL . 'assets/css/pft-admin-styles.css', array(), PFT_VERSION);
        wp_enqueue_script('pft-admin-scripts', PFT_PLUGIN_URL . 'assets/js/pft-admin-scripts.js', array('jquery'), PFT_VERSION, true);
        wp_localize_script('pft-admin-scripts', 'pftAdmin', array(
            'ajaxurl' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('pft_admin_nonce')
        ));
    }

    public function render_settings_page() {
        ?>
        <div class="wrap">
            <h1><?php esc_html_e('Personal Finance Tracker Settings', 'personal-finance-tracker'); ?></h1>
            <form method="post" action="options.php">
                <?php
                settings_fields('pft_settings_group');
                do_settings_sections('pft_settings_group');
                ?>
                <table class="form-table">
                    <tr valign="top">
                        <th scope="row"><?php esc_html_e('Currency Symbol', 'personal-finance-tracker'); ?></th>
                        <td><input type="text" name="pft_currency_symbol" value="<?php echo esc_attr(get_option('pft_currency_symbol', '$')); ?>" /></td>
                    </tr>
                    <tr valign="top">
                        <th scope="row"><?php esc_html_e('Date Format', 'personal-finance-tracker'); ?></th>
                        <td>
                            <select name="pft_date_format">
                                <option value="Y-m-d" <?php selected(get_option('pft_date_format'), 'Y-m-d'); ?>><?php echo date('Y-m-d'); ?></option>
                                <option value="m/d/Y" <?php selected(get_option('pft_date_format'), 'm/d/Y'); ?>><?php echo date('m/d/Y'); ?></option>
                                <option value="d/m/Y" <?php selected(get_option('pft_date_format'), 'd/m/Y'); ?>><?php echo date('d/m/Y'); ?></option>
                            </select>
                        </td>
                    </tr>
                </table>
                <?php submit_button(); ?>
            </form>
        </div>
        <?php
    }

    public function render_categories_page() {
        ?>
        <div class="wrap">
            <h1><?php esc_html_e('Manage Categories', 'personal-finance-tracker'); ?></h1>
            <div id="pft-categories-app"></div>
        </div>
        <?php
    }
}
