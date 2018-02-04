<?php

class Setting_page
{

    const DEFAULT_TAB = 'combined_tab';

    public static $initiated = false;

    public static $setting_class = 'Setting_page';

    public static $page_name = 'Site Settings';
    public static $setting_page_name = '';
    public static $page_position = 58.54;
    public static $textdomain = 'site-settings';
    public static $page_url = 'site-settings';
    public static $default_tab = 'combined_tab';
    public static $tabs = array(
        self::DEFAULT_TAB => 'Other',
        'reports' => 'Reports'
    );
    private static $allowed = array('contact_phones');

//    private static $allowed = array();


    private function __construct()
    {

    }

    private function __clone()
    {

    }

    public static function init()
    {
        if (!static::$initiated) {
            static::init_hooks();
            static::$setting_page_name = __(static::$page_name, static::$textdomain);
        }
    }

    public static function init_hooks()
    {
        static::$initiated = true;
        add_action('admin_menu', array(__CLASS__, 'add_child_theme_setting_page'));

//        add_action('wp_ajax_get_reports', array('Setting_page', 'get_reports'));
//        add_action('wp_ajax_nopriv_get_reports', array('Setting_page', 'get_reports'));
    }

    public static function top_level_setting_page()
    {
        $status = static::check_and_save_setting($_POST);
        static::admin_response_saving($status);

        echo "<div class='page-" . static::$page_url . "'>";
        echo "<h2>" . static::$page_name . "</h2>";

        $tabs = static::get_tabs();
//var_dump(static::$page_url);
//var_dump();

        echo '<h2 class="nav-tab-wrapper">';
        foreach ($tabs as $tab => $name) {
            $class = ($tab == $_GET['tab']) ? ' nav-tab-active' : '';
            if (!isset ($_GET['tab']) && $tab == static::$default_tab) {
                $class = ' nav-tab-active';
            }
            echo "<a class='nav-tab$class' href='?page=" . static::$page_url . "&tab=$tab'>$name</a>";
        }
        echo '</h2>';

        if (isset ($_GET['tab'])) {
            $tab = $_GET['tab'];
        } else $tab = static::$default_tab;

        switch ($tab) {
//          case 'CUSTOM_INIT_TAB' :
//              CUSTOM_INIT_TAB($tab,$data);
//              break;
            default :
                if (is_callable(array('static', $tab))) {
                    static::$tab($tab);
                } else {
                    echo "<form method='post'>";
                    $sections = static::get_form_fields()[$tab];
                    static::default_render_tab($tab, $sections);
                    echo '</form>';
                }
                break;

        }
        ?>
        <script>

        </script>
        <?php
        echo '</div>';
    }

    public function admin_response_saving($status)
    {
        if ($status['status'] && ($status['updated'] > 0 || $status['added'] > 0)) { // update success
            echo '<div class="notice notice-success is-dismissible">';
            echo $status['message'] . '<br>';
            if ($status['updated'] > 0) echo 'Updated options :' . $status['updated'] . '<br>';
            if ($status['added'] > 0) echo 'Added options :' . $status['added'] . '<br>';
            if ($status['denied'] > 0) echo 'Not allowed options :' . $status['denied'] . '<br>';
            echo '</div>';
        } elseif ($status['status'] == false && $status['error'] == 'NO_POST') { //empty request

        } elseif (!$status['status']) { // = false
            echo '<div class="notice notice-error"><pre>';
            var_dump($status);
            echo '</pre></div>';
        } elseif ($status['denied'] > 0) { // trying to update but all option denied
            echo '<div class="notice notice-error">';
            echo 'Not allowed options :' . $status['denied'] . '<br>';
            echo '</div>';
        } else {
            // not changed
        }
    }

    public static function get_form_fields()
    {
        return array(
            'other' => array(
                'section_name_test' => array(
                    'name' => 'Manage Swl site',

//                    'submit' => array(
//                        'label' => 'Save',
//                        'container_class' => 'button-container',
//                        'type' => 'submit',
//                        'class' => 'button button-primary',
//                    ),
                )
            ),
            'combined_tab' => array(
//                'section_name_test' => array(
//                    'name' => 'section name test',
//                    'field_name_test' => array(
//                        'id' => 'id_test',
//                        'label' => 'LABEL15',
//                        'name' => 'product_search_customer_idFDFS',
//                        'type' => 'text',
//                        'class' => 'wtf',
//                        'default' => 'get_option' // get_option("{$this[name]}")
//                    ),
//                    'field_hidden' => array(
//                        'name' => 'custom_saving',
//                        'type' => 'hidden',
//                        'default' => 'change_post_data' // get_option("{$this[name]}")
//                    ),
//                ),
                'contact_us_email' => array(
                    'name' => 'Contact us - emails',
                    'swl_contact_email_view' => array(
                        'id' => 'swl_contact_email_view_id',
                        'label' => 'Contact email frontend',
                        'name' => 'swl_contact_email_view',
                        'type' => 'text',
                        'class' => '',
                        'default' => 'get_option' // get_option("{$this[name]}")
                    ),
                    'swl_contact_form_email' => array(
                        'id' => 'swl_contact_form_email_id',
                        'label' => 'Swl contact form email',
                        'name' => 'swl_contact_form_email',
                        'type' => 'text',
                        'class' => '',
                        'default' => 'get_option' // get_option("{$this[name]}")
                    ),
                ),
            ),
//            'new_tab2' => array(
//                'name' => 'DEV EXEMPLE TAB',
//                'section_name_test' => array(
//                    'name' => 'section name test',
//                    'field_name_test3' => array(
//                        'id' => 'id_test',
//                        'label' => 'LABEL4',
//                        'name' => 'product_search_customer_idFDFS',
//                        'type' => 'text',
//                        'class' => 'wtf',
//                        'default' => 'get_option' // get_option("{$this[name]}")
//                    ),
//                    'field_select' => array(
//                        'id' => 'id_test',
//                        'label' => 'LABEL4',
//                        'name' => 'product_search_customer_idFDFS',
//                        'type' => 'select',
//                        'class' => 'wtf',
//                        'values' => array(
//                            'value1' => 'label1',
//                            'value2' => 'label2',
//                            'value3' => 'label3',
//                            'value4' => 'label4',
//                        ),
//                        'default' => 'get_option' // get_option("{$this[name]}")
//                    ),
//                    'field_hidden' => array(
//                        'name' => 'custom_saving',
//                        'type' => 'hidden',
//                        'default' => 'change_post_data' // get_option("{$this[name]}")
//                    ),
//                ),
//                'section_name_test2' => array(
//                    'name' => 'section name test',
//                    'field_name_test23' => array(
//                        'id' => 'id_test2',
//                        'label' => 'LABEL3',
//                        'name' => 'field_name2',
//                        'type' => 'text',
//                        'class' => 'wtf2',
//                        'default' => 'sdfds'
//                    ),
//                    'submit' => array(
//                        'label' => 'Save',
//                        'container_class' => 'button-container',
//                        'type' => 'submit',
//                        'class' => 'button button-primary',
//                    ),
//                ),
//            ),
        );
    }

    public function change_post_data($post)
    {
        if (false) {
            return $post;
        } else {
            return array(
                'post' => $post,
                'status' => false,
                'message' => 'exit by custom save',
                'error' => 'CUSTOM_SAVE_ERROR'
            );
        }

    }

    public function save_wp_paypal_currency($post)
    {
        if ($post['yy_currency']) {
            update_option('wp_paypal_currency_code', trim($post['yy_currency']));
            return $post;
        } else {
            return array(
                'post' => $post,
                'status' => 'failed',
                'message' => 'exit by custom save',
                'error' => 'CUSTOM_SAVE_ERROR'
            );
        }

    }

    public function render_current_tab_form($sections)
    {

        if($sections){
            foreach ($sections as $section_name => $section) {
                if ($section_name == 'name') continue;
                echo "<div class='section $section_name'>";
                echo "<h4>{$section['name']}</h4>";
                foreach ($section as $field_name => $field_data) {
                    if ($field_name == 'name') continue;
                    switch ($field_data['type']) {
                        case  'user_permissions':{
                            $args = array(
                                'order' => 'ASC',
                            );
                            $user_query = new WP_User_Query( $args );

                            ($field_data['default'] == 'get_option') ? $value = $field_data['default']($field_data['name']) : $value = $field_data['value'];

                            if ( ! empty( $user_query->results ) ) {
                                ?>
                                <div>
                                    <?php
                                    foreach ( $user_query->results as $user ) {
                                        ?>
                                        <div>
                                            <input id="<?php echo $field_data['id'] . '-' . $user->ID;?>"
                                                   name="<?php echo $field_data['name'] ?>[<?php echo $user->ID; ?>]"
                                                   type="checkbox"
                                                <?php if ($value[$user->ID]) {
                                                    echo 'checked="checked"';
                                                }; ?>
                                            >
                                            <label for="<?php echo $field_data['id'] . '-' . $user->ID;?>"><?php echo $user->display_name; ?></label>
                                        </div>
                                        <?php
                                    }
                                    ?>
                                </div>
                                <?php
                            } else {
                                echo 'No users found.';
                            }
                            break;
                        }
                        case 'text': {
                            echo "<div class='form-group {$field_data['class']}'>";
                            echo "<label for=\"{$field_data['id']}\">{$field_data['label']}</label>";
                            ($field_data['default'] == 'get_option') ? $value = $field_data['default']($field_data['name']) : $value = $field_data['value'];
                            echo "<input id=\"{$field_data['id']}\" name=\"{$field_data['name']}\" type=\"text\" value=\"{$value}\">";
                            echo "</div>";
                            break;
                        }
                        case 'submit': {
                            echo "<div class=\"{$field_data['button-container']}\">";
                            echo "<input class=\"{$field_data['class']}\" type=\"submit\" value=\"{$field_data['label']}\">";
                            echo "</div>";
                            break;
                        }
                        case 'checkbox----------------': {
                            echo "<div class='form-group {$field_data['class']}'>";
                            echo "<label for=\"{$field_data['id']}\">{$field_data['label']}</label>";
                            ($field_data['default'] == 'get_option') ? $value = $field_data['default']($field_data['name']) : $value = $field_data['value'];
                            var_dump($value);
                            ?>

                            <input id="<?php echo $field_data['id'];?>" name="<?php echo $field_data['name']; ?>" type="checkbox" <?php if ($value == 'on') { echo "checked='checked'"; } ?>>
                            <?php
                            echo "</div>";
                            break;
                        }
                        case 'hidden': {
                            echo "<input type='hidden' name='{$field_data['name']}' value='{$field_data['default']}'>";
                            break;
                        }
                        case 'select': {
                            echo "<div class='form-group {$field_data['class']}'>";
                            echo "<label for=\"{$field_data['id']}\">{$field_data['label']}</label>";
                            ($field_data['default'] == 'get_option') ? $selected = $field_data['default']($field_data['name']) : $selected = $field_data['value'];
                            ?>
                            <select id="<?= $field_data['id']; ?>" name="<?= $field_data['name']; ?>"
                                    class="<?= $field_data['class'] ?>">
                                <?php
                                foreach ($field_data['values'] as $value => $label) {
                                    ?>
                                    <option value="<?= $value ?>" <?php if ($value == $selected) {
                                        echo 'selected="selected"';
                                    }; ?>><?= $label ?></option>
                                    <?php
                                }
                                ?>
                            </select>
                            <?php
                            echo "</div>";
                            break;
                        }
                    }
                }
                echo "</div>";
            }


        }
    }

    public static function get_allowed_options()
    {
        foreach (static::get_form_fields() as $tab_name => $tab) {
            if ($tab_name == 'name') continue;
            foreach ($tab as $section_name => $section) {
                if ($section_name == 'name') continue;
                foreach ($section as $field_name => $field_data) {
                    if ($field_name == 'name') continue;
                    if ($field_data['type'] == 'submit') continue;
                    if (!$field_data['name']) {//NULL ALLOWED ? ERROR
                        echo '<pre>';
                        var_dump($section);
                        var_dump($field_name);
                        var_dump($field_data);
                        echo '</pre>';
                        wp_die();
                    }
                    static::$allowed[] = $field_data['name'];
                }
            }
        }
        return static::$allowed;
    }

    public function get_tabs()
    { //get all tabs predefined + dynamic tabs
        foreach (static::get_form_fields() as $key => $get_tab_form_field) {
            if (!empty($get_tab_form_field['name'])) {
                static::$tabs[$key] = $get_tab_form_field['name'];
            }
        }
        return static::$tabs;
    }

    public function check_and_save_setting($post)
    {
        $allowed_options = static::get_allowed_options();
        $response = array();
        if ($post['custom_saving'] && is_callable('static', $post['custom_saving'])) {
            $post = static::$post['custom_saving']($post);
            if ($post['status'] == 'failed') {
                return $post;
            }

        }

        if ($post) {
            $updated_options = 0;
            $added_options = 0;
            $not_allowed_options = 0;
            foreach ($post as $key => $value) {
                if (array_search($key, $allowed_options, true) === false) {
                    //error : denied add/update option
                    $not_allowed_options++;
                } else {
                    //save
                    if ($old_value = get_option($key)) {
                        if (empty($value)) {
                            delete_option($key);
                        } elseif ($old_value != $value) {
//                            var_dump('here');
                            update_option($key, $value);
                            $updated_options++;
                        } else {
                            // actual option...
                        }
                    } else {
                        if (add_option($key, $value, '', true) == false) {
                            delete_option($key);
                            if (add_option($key, $value, '', true) == false) {
                                delete_option($key);
                                add_option($key, $value, '', true);
                            } else {
                                update_option($key, $value);
                            }
                        } else {
                            update_option($key, $value);
                        }
                        $added_options++;
                    }
                }
            }
            return array(
                'status' => true,
                'message' => __("Settings saved", static::$textdomain),
                'updated' => $updated_options,
                'added' => $added_options,
                'denied' => $not_allowed_options,
            );
        } else {
            $response['status'] = false;
            $response['error'] = 'NO_POST';
            return $response;
        }
    }

    public function render_other_fields($tab)
    {
        $sections = static::get_form_fields()[$tab];
        static::default_render_tab($tab, $sections);
    }

    public function default_render_tab($tab, $sections)
    {
        ?>
        <div class="<?php echo $tab; ?>-settings">
            <?php
            static::render_current_tab_form($sections);
            ?>
        </div>
        <?php
    }

    public static function add_child_theme_setting_page()
    {
        add_menu_page(static::$page_name, static::$page_name, 'manage_options', static::$page_url, array(static::$setting_class, 'top_level_setting_page'), '', static::$page_position);
    }
}
