<?php
require_once "base/Setting_page.php";
add_action('init', array('Tech_radar_menus', 'init'));


class Tech_radar_menus extends Setting_page
{
    const DEFAULT_TAB = 'new_words';
    public static $initiated = false;
    public static $setting_class = 'Tech_radar_menus';
    public static $page_name = 'New words requests';
    public static $setting_page_name = '';
    public static $page_position = 58;
    public static $textdomain = 'new_words_panel';
    public static $page_url = 'new_words_panel';
    public static $default_tab = 'new_words';

    public static $tabs = array(
        self::DEFAULT_TAB => 'Add word',
//        'languages_and_frameworks' => 'Languages and frameworks',
//        'tools' => 'Tools',
//        'platforms' => 'Platforms',
    );

    public static $allowed = array();
//    public static $categories = array('techniques', 'languages_and_frameworks', 'tools', 'platforms');
//    public static $sub_categories = array('adopt', 'trial', 'assess', 'hold');

    public static function init_hooks()
    {
        self::$initiated = true;
        add_action('admin_menu', array(self::$setting_class, 'add_child_theme_setting_page'));
    }

    public static function save_words($post)
    {
        global $wpdb;
        $wordnet = new wpdb('harryxkn_007', 'Jame7s_BoNd', 'harryxkn_wordnet31', 'localhost');
        if (!empty($wordnet->error)) wp_die($wordnet->error);
        $requestedWords = new wpdb('harryxkn_007', 'Jame7s_BoNd', 'harryxkn_requestedWords', 'localhost');
        if (!empty($requestedWords->error)) wp_die($requestedWords->error);
        $requestedWords->query("truncate words;");
        foreach ($post['words'] as $word) {
            $requestedWords->query( $requestedWords->prepare("INSERT INTO words(word, definition ) VALUES (%s, %s)", $word['word'], $word['definition']));
//            var_dump($word);
            if ($word['approve']) {
                if ($word['approve']) {
                    $wordnet->query( $wordnet->prepare("INSERT INTO words(wordid, lemma ) VALUES (%d, %s)", $wordnet->get_var("SELECT MAX(wordid)+1 from words"), $word['word']));
                    $wordnet->query( $wordnet->prepare("INSERT INTO senses (wordid, synsetid) VALUES (%d, %d)", $wordnet->get_var("SELECT MAX(wordid) from words"), $wordnet->get_var("SELECT MAX(synsetid)+1 from senses")));
                    $wordnet->query( $wordnet->prepare("INSERT INTO synsets ( synsetid, definition) VALUES (%d, %s)", $wordnet->get_var("SELECT MAX(synsetid) from senses"),$word['definition']));
                    $requestedWords->delete(words, ['id' => (int)$word['id']], ['%d']);
                }
            }
//            $requestedWords->query("truncate words;");
//            $requestedWords->query( $requestedWords->prepare("INSERT INTO words(word, definition ) VALUES (%s, %s)", $word['word'], $word['definition']));
//            var_dump($word);
        }
    }

    public static function new_words($tab)
    {
        //var_dump($tab);
        ?>
        <div class="new-administration-page">
            <div id="save-notice"></div>
            <form method="post">
                <?php
                static::render_other_fields($tab);
                $mydb = new wpdb('harryxkn_007', 'Jame7s_BoNd', 'harryxkn_requestedWords', 'localhost');
                $rows = $mydb->get_results("select id, word, definition from words");
                $output = array();
                $count = 0;
                if (!empty($rows)) {
                    foreach ($rows as $obj) {
                        $output[$count] = $obj;
                        $count++;
                    }
                }
                ?>
                <div class="section" style="margin-bottom: 25px">
                    <input type="hidden" name="custom_saving" value="save_words">
                    <div>
                        <div class="ub-invoice-items-container">
                            <table class="ub-invoice-table"
                                   data-invoice-table="<?php echo esc_attr(json_encode($output)); ?>">
                                <thead>
                                <tr>
                                    <th>Word</th>
                                    <th>Definition</th>
                                    <th></th>
                                    <th></th>
                                </tr>
                                </thead>
                                <tbody>
                                </tbody>
                            </table>
                            <script id="invoice_item" type="text/template">
                                <tr style="display: none">
                                    <td>
                                        <input type="hidden" data-attr="id" data-lookup>
                                        <input type="text" data-attr="word">
                                    </td>
                                    <td>
                                        <input type="text" data-attr="definition" data-lookup>
                                    </td>
                                    <td>
                                        <input type="checkbox" class="checkbox" data-attr="approve" data-lookup>
                                        <span>Approve word</span>
                                    </td>
                                    <td>
                                        <div class="buttons-row">
                                            <button data-invoice-action="remove">&times;</button>
                                        </div>
                                    </td>
                                </tr>
                            </script>
                            <button type="button" class="button ub-invoice-add-item"
                                    data-invoice-action="add">Add Item
                            </button>

                        </div>
                    </div>

                </div>
                <input type="submit" value="Save all changes" class="button button-primary button-large">
            </form>
            <div>
                <script>
                    function ub_invoice_table(mode, data) {
                        var $table = jQuery('.ub-invoice-table');

                        switch (mode) {
                            case 'init': {
                                var $data = JSON.parse($table.attr('data-invoice-table'));
                                $table.find('tbody').html('');
                                if ($data) {
                                    for (var item_count in $data) {
                                        if ($data.hasOwnProperty(item_count)) {
                                            ub_invoice_table('add', $data[item_count]);
                                        }
                                    }
                                    // $table.trigger('ub_total_amount_changed');
                                } else {
                                    ub_invoice_table('add');
                                }
                                break;
                            }
                            case 'add': {
                                var input_name = 'words';
                                var $tbody = jQuery('.ub-invoice-table tbody');
                                var count = $tbody.find('tr:last-child').attr('data-id');
                                count = count ? parseInt(count) + 1 : 0;
                                var $template = jQuery(jQuery('#invoice_item').html()).clone();

                                $template.find('[data-attr]').each(function (i, o) {
                                    var name_attr = jQuery(o).attr('data-attr');
                                    jQuery(o).attr('name', input_name + '[' + count + '][' + name_attr + ']');
                                    if (data && data[name_attr]) {
                                        switch (name_attr) {
                                            case 'hours':
                                            case 'tax': {
                                                if (data[name_attr] === 'on') {
                                                    jQuery(o).prop('checked', true);
                                                }
                                                break;
                                            }
                                            default: {
                                                jQuery(o).val(data[name_attr])
                                            }
                                        }
                                    }
                                });
                                $template.attr('data-id', count);

                                $tbody.append($template);
                                $table.trigger('ub_total_amount_changed');
                                $template.fadeIn(300);
                                break;
                            }
                            case 'save': {
                                break;
                            }
                            case 'undo': {
                                break;
                            }
                            default: {
                                break;
                            }
                        }
                    }

                    ub_invoice_table('init');
                    jQuery('.ub-invoice-items-container').on('click', '[data-invoice-action]', function (e) {
                        // e.preventDefault();
                        // e.stopPropagation();
                        var data_action = jQuery(this).attr('data-invoice-action');
                        var $table = jQuery('.ub-invoice-table');

                        switch (data_action) {
                            case 'add': {
                                ub_invoice_table('add');
                                break;
                            }
                            case 'remove': {
                                jQuery(this).closest('tr').remove();

                                break;
                            }
                        }
                        return false;
                    });
                </script>
            </div>

        </div>
        <?php
    }


    public static function get_form_fields()
    {
        return array();
    }

    public static function add_child_theme_setting_page()
    {
        add_menu_page(self::$page_name, self::$page_name, 'edit_posts', self::$page_url, array(self::$setting_class, 'top_level_setting_page'), 'dashicons-admin-site', self::$page_position);
    }
}
