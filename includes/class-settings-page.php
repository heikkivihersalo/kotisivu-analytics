<?php

/* Exit if accessed directly */
if (!defined('ABSPATH')) {
    exit;
}

class SettingsPage {

    /**
     * Setting name. Same as plugin name.
     */
    private $name;

    /**
     * Current setting values
     */
    private $values;

    /**
     * Default plugin values
     */
    private $default_values;

    /**
     * Plugin text domain. Is used in translations and menu slug.
     */
    private $text_domain;


    /**
     * Constructor
     * 
     */
    public function __construct($name, $values, $default_values, $text_domain) {
        $this->name = $name;
        $this->values = $values;
        $this->default_values = $default_values;
        $this->text_domain = $text_domain;

        add_action('admin_menu', [$this, 'register_options_page']);
        if (!empty($GLOBALS['pagenow']) && ('options-general.php' === $GLOBALS['pagenow'] || 'options.php' === $GLOBALS['pagenow'])) {
            add_action('admin_init', [$this, 'settings_page_group']);
        }
    }


    public function register_options_page() {
        add_options_page(
            __('Server-Side Analytics', $this->text_domain), // Page Title
            __('Server-Side Analytics', $this->text_domain), // Menu Title
            'manage_options', // Capability
            $this->text_domain,  // Menu Slug
            [$this, 'settings_page_callback'] // Callback function
        );
    }


    public function settings_page_group() {
        register_setting('server_side_settings', $this->name);
    }


    public function settings_page_callback() {
        if (!current_user_can('manage_options')) {
            wp_die('You do not have sufficient permissions to access this page');
        }

        $settings_attr = shortcode_atts($this->default_values, $this->values);

?>

        <div class=<?php echo $this->text_domain ?>>
            <h1><?php echo __("Server-Side Analytics Settings", $this->text_domain); ?></h1>

            <form method="post" action="options.php">
                <?php settings_fields('server_side_settings'); ?>
                <div class="<?php echo $this->text_domain ?>__container">
                    <div class="<?php echo $this->text_domain ?>__section">
                        <h2 class="<?php echo $this->text_domain ?>__heading"><?php echo __("Container Settings", $this->text_domain); ?></h2>
                        <!-- ENABLE SERVER-SIDE ANALYTICS --->
                        <div class="<?php echo $this->text_domain ?>__input-wrapper">
                            <label>
                                <input type="checkbox" id="<?php echo $this->name . '[active]' ?>" name="<?php echo $this->name . '[active]' ?>" value="1" <?php checked(1, $settings_attr['active']); ?>> <?php echo __('Enable server-side analytics', $this->text_domain); ?>
                            </label>
                        </div>
                        <!-- ENABLE JAVASCRIPT CONTAINER --->
                        <div class="<?php echo $this->text_domain ?>__input-wrapper">
                            <label>
                                <input type="checkbox" id="<?php echo $this->name . '[js_container]' ?>" name="<?php echo $this->name . '[js_container]' ?>" value="1" <?php checked(1, $settings_attr['js_container']); ?>> <?php echo __('Enable Javascript Container', $this->text_domain); ?>
                            </label>
                        </div>
                        <!-- TAG MANAGER URL --->
                        <div class="<?php echo $this->text_domain ?>__input-wrapper">
                            <label class="<?php echo $this->text_domain ?>__label is-bold" for="<?php echo $this->name . '[url]' ?>">
                                <?php echo __('URL', $this->text_domain); ?>
                            </label>
                            <p class="<?php echo $this->text_domain ?>__description"><?php echo __("Default URL is typically https://www.googletagmanager.com", $this->text_domain); ?></p>
                            <input type="url" id="<?php echo $this->name . '[url]' ?>" name="<?php echo $this->name . '[url]' ?>" value="<?php echo esc_attr($settings_attr['url']) ?>" />
                        </div>
                        <!-- TAG MANAGER ID --->
                        <div class="<?php echo $this->text_domain ?>__input-wrapper">
                            <label class="<?php echo $this->text_domain ?>__label is-bold" for="<?php echo $this->name . '[id]' ?>">
                                <?php echo __('ID', $this->text_domain); ?>
                            </label>
                            <p class="<?php echo $this->text_domain ?>__description"><?php echo __("Container ID for example GTM-XXXXXXX", $this->text_domain); ?></p>
                            <input type="text" id="<?php echo $this->name . '[id]' ?>" name="<?php echo $this->name . '[id]' ?>" value="<?php echo esc_attr($settings_attr['id']) ?>" />
                        </div>
                        <!-- TAG MANAGER TIMEOUT --->
                        <div class="<?php echo $this->text_domain ?>__input-wrapper">
                            <label class="<?php echo $this->text_domain ?>__label is-bold" for="<?php echo $this->name . '[timeout]' ?>">
                                <?php echo __('Timeout', $this->text_domain); ?>
                            </label>
                            <p class="<?php echo $this->text_domain ?>__description"><?php echo __("Sets the delay for loading tag manager container scripts", $this->text_domain); ?></p>
                            <input type="number" id="<?php echo $this->name . '[timeout]' ?>" name="<?php echo $this->name . '[timeout]' ?>" value="<?php echo esc_attr($settings_attr['timeout']) ?>" />
                        </div>
                        <!-- TAG MANAGER ENDPOINT --->
                        <div class="<?php echo $this->text_domain ?>__input-wrapper">
                            <label class="<?php echo $this->text_domain ?>__label is-bold" for="<?php echo $this->name . '[endpoint]' ?>">
                                <?php echo __('Endpoint', $this->text_domain); ?>
                            </label>
                            <p class="<?php echo $this->text_domain ?>__description"><?php echo __("Container (server) endpoint. Can be something like https://domain.tld/g/collect", $this->text_domain); ?></p>
                            <input type="url" id="<?php echo $this->name . '[endpoint]' ?>" name="<?php echo $this->name . '[endpoint]' ?>" value="<?php echo esc_attr($settings_attr['endpoint']) ?>" />
                        </div>
                    </div>
                    <div class="<?php echo $this->text_domain ?>__section">
                        <h2 class="<?php echo $this->text_domain ?>__heading"><?php echo __("Google Analytics 4", $this->text_domain); ?></h2>
                        <!-- GA4 MEASUREMENT_ID --->
                        <div class="<?php echo $this->text_domain ?>__input-wrapper">
                            <label class="<?php echo $this->text_domain ?>__label is-bold" for="<?php echo $this->name . '[measurement_id]' ?>">
                                <?php echo __('Measurement ID', $this->text_domain); ?>
                            </label>
                            <p class="<?php echo $this->text_domain ?>__description"><?php echo __("Measurement ID for Google Analytics 4. Should be added for the POST request to work correctly", $this->text_domain); ?></p>
                            <input type="text" id="<?php echo $this->name . '[measurement_id]' ?>" name="<?php echo $this->name . '[measurement_id]' ?>" value="<?php echo esc_attr($settings_attr['measurement_id']) ?>" />
                        </div>
                        <!-- COOKIE NAME --->
                        <div class="<?php echo $this->text_domain ?>__input-wrapper">
                            <label class="<?php echo $this->text_domain ?>__label is-bold" for="<?php echo $this->name . '[cookie_name]' ?>">
                                <?php echo __('Cookie Name', $this->text_domain); ?>
                            </label>
                            <p class="<?php echo $this->text_domain ?>__description"><?php echo __("Cookie name for server-side Google Analytics. By default Google generates a cookie called FPID.", $this->text_domain); ?></p>
                            <input type="text" id="<?php echo $this->name . '[cookie_name]' ?>" name="<?php echo $this->name . '[cookie_name]' ?>" value="<?php echo esc_attr($settings_attr['cookie_name']) ?>" />
                        </div>
                        <!-- COOKIE EXPIRES --->
                        <div class="<?php echo $this->text_domain ?>__input-wrapper">
                            <label class="<?php echo $this->text_domain ?>__label is-bold" for="<?php echo $this->name . '[cookie_expires]' ?>">
                                <?php echo __('Cookie Expires', $this->text_domain); ?>
                            </label>
                            <input type="number" id="<?php echo $this->name . '[cookie_expires]' ?>" name="<?php echo $this->name . '[cookie_expires]' ?>" value="<?php echo esc_attr($settings_attr['cookie_expires']) ?>" />
                        </div>
                        <!-- COOKIE SAMESITE --->
                        <div class="<?php echo $this->text_domain ?>__input-wrapper">
                            <label class="<?php echo $this->text_domain ?>__label is-bold" for="<?php echo $this->name . '[cookie_samesite]' ?>">
                                <?php echo __('Cookie SameSite', $this->text_domain); ?>
                            </label>
                            <select id="<?php echo $this->name . '[cookie_samesite]' ?>" name="<?php echo $this->name . '[cookie_samesite]' ?>">
                                <option value="Lax" <?php if ($settings_attr['cookie_samesite'] == 'Lax') echo 'selected="selected"'; ?>>Lax</option>
                                <option value="Strict" <?php if ($settings_attr['cookie_samesite'] == 'Strict') echo 'selected="selected"'; ?>>Strict</option>
                                <option value="None" <?php if ($settings_attr['cookie_samesite'] == '587') echo 'selected="selected"'; ?>>None</option>
                            </select>
                        </div>
                    </div>
                    <div class="<?php echo $this->text_domain ?>__section">
                        <h2 class="<?php echo $this->text_domain ?>__heading"><?php echo __('Cookie Consent Integration', $this->name); ?></h2>
                        <p class="<?php echo $this->text_domain ?>__description"><?php echo __("You can configure cookie consent settings here. Disabling CMP always loads server-side tracking with cookies. Hybrid tracking always sends http-requests, but does not set cookies without consent. Normal tracking only sents http-requests when user has given consent.", $this->text_domain); ?></p>
                        <!-- CMP PLATFORM TRACKING--->
                        <div class="<?php echo $this->text_domain ?>__input-wrapper">
                            <label class="<?php echo $this->text_domain ?>__label is-bold" for="<?php echo $this->name . '[cmp_tracking]' ?>">
                                <?php echo __('Tracking', $this->text_domain); ?>
                            </label>
                            <select id="<?php echo $this->name . '[cmp_tracking]' ?>" name="<?php echo $this->name . '[cmp_tracking]' ?>">
                                <option value="disable" <?php if ($settings_attr['cmp_tracking'] == 'disable') echo 'selected="selected"'; ?>>Disable CMP</option>
                                <option value="hybrid" <?php if ($settings_attr['cmp_tracking'] == 'hybrid') echo 'selected="selected"'; ?>>Hybrid Tracking (Alpha)</option>
                                <option value="normal" <?php if ($settings_attr['cmp_tracking'] == 'normal') echo 'selected="selected"'; ?>>Normal Tracking</option>
                            </select>
                        </div>
                        <!-- CMP PLATFORM PROVIDER--->
                        <div class="<?php echo $this->text_domain ?>__input-wrapper">
                            <label class="<?php echo $this->text_domain ?>__label is-bold" for="<?php echo $this->name . '[cmp_provider]' ?>">
                                <?php echo __('Provider', $this->text_domain); ?>
                            </label>
                            <select id="<?php echo $this->name . '[cmp_provider]' ?>" name="<?php echo $this->name . '[cmp_provider]' ?>">
                                <option value="cookiehub" <?php if ($settings_attr['cmp_provider'] == 'cookiehub') echo 'selected="selected"'; ?>>Cookiehub</option>
                                <option value="cookiebot" <?php if ($settings_attr['cmp_provider'] == 'cookiebot') echo 'selected="selected"'; ?>>Cookiebot</option>
                            </select>
                        </div>
                        <!-- CMP TRACKING ID--->
                        <div class="<?php echo $this->text_domain ?>__input-wrapper">
                            <label class="<?php echo $this->text_domain ?>__label is-bold" for="<?php echo $this->name . '[cmp_id]' ?>">
                                <?php echo __('Tracking ID', $this->text_domain); ?>
                            </label>
                            <input type="text" id="<?php echo $this->name . '[cmp_id]' ?>" name="<?php echo $this->name . '[cmp_id]' ?>" value="<?php echo esc_attr($settings_attr['cmp_id']) ?>" />
                        </div>
                    </div>
                    <div class="<?php echo $this->text_domain ?>__section">
                        <h2 class="<?php echo $this->text_domain ?>__heading"><?php echo __('Debugging', $this->name); ?></h2>
                        <p class="<?php echo $this->text_domain ?>__description"><?php echo __('You can get current preview string (X-Gtm-Server-Preview header) from Tag Manager -> Preview -> ... -> Send Requests Manually. Please note that preview string changes after a while so you might need to generate new one quite frequently.', $this->text_domain); ?></p>
                        <!-- ENABLE DEBUG MODE --->
                        <div class="<?php echo $this->text_domain ?>__input-wrapper">
                            <label>
                                <input type="checkbox" id="<?php echo $this->name . '[debug_mode]' ?>" name="<?php echo $this->name . '[debug_mode]' ?>" value="1" <?php checked(1, $settings_attr['debug_mode']); ?>> <?php echo __('Enable Debug Mode', $this->text_domain); ?>
                            </label>
                        </div>
                        <!-- PREVIEW STRING --->
                        <div class="<?php echo $this->text_domain ?>__input-wrapper">
                            <label class="<?php echo $this->text_domain ?>__label is-bold" for="<?php echo $this->name . '[preview_string]' ?>">
                                <?php echo __('Preview String', $this->text_domain); ?>
                            </label>
                            <input type="text" id="<?php echo $this->name . '[preview_string]' ?>" name="<?php echo $this->name . '[preview_string]' ?>" value="<?php echo esc_attr($settings_attr['preview_string']) ?>" />
                        </div>
                    </div>
                </div>
                <?php submit_button('Save'); ?>

            </form>
        </div>
<?php
    }
}
