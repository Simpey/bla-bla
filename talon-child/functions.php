<?php
include(get_stylesheet_directory() . '/include/word-menu.php');
add_action('wp_enqueue_scripts', 'my_theme_enqueue_styles');
function my_theme_enqueue_styles()
{
    wp_enqueue_style('parent-style', get_template_directory_uri() . '/style.css');
    wp_enqueue_style('responsive-style', get_stylesheet_directory_uri() . '/assets/css/responsive-style.css');
    wp_enqueue_style('child-style', get_stylesheet_directory_uri() . '/assets/css/style.css');
}

add_action('wp_enqueue_scripts', 'my_theme_enqueue_scripts');
function my_theme_enqueue_scripts()
{
    wp_enqueue_script('ejs', get_stylesheet_directory_uri() . '/assets/js/ejs.js');
    wp_enqueue_script('sorttable', get_stylesheet_directory_uri() . '/assets/js/sorttable.js');
    wp_enqueue_script('tablesorter', get_stylesheet_directory_uri() . '/assets/js/jquery.tablesorter.js');
    wp_enqueue_script('main', get_stylesheet_directory_uri() . '/assets/js/script.js', array('jquery', 'sorttable', 'ejs'));
    wp_localize_script('main', 'main',
        array(
            'ajaxurl' => admin_url('admin-ajax.php'),
            'ejs_dir' => get_stylesheet_directory_uri() . '/template-ejs/',
        )
    );
}

function content_widgets_init()
{
    register_sidebar(array(
        'name' => 'Main',
        'id' => 'main-content-sidebar',
        'before_widget' => '<div>',
        'after_widget' => '</div>',
        'before_title' => '<h1>',
        'after_title' => '</h1>',
    ));
}

add_action('widgets_init', 'content_widgets_init');


add_action('wp_loaded', function () {
    remove_action('talon_footer', 'talon_footer_credits', 8);
});

function talon_footer_copyright()
{
    ?>
    <div class="site-info col-md-6">
        © 2018
    </div><!-- .site-info -->
    <?php
}

add_action('talon_footer', 'talon_footer_copyright', 8);


flush_rewrite_rules();

function add_query_vars_filter($vars)
{
    $vars[] = "query_str";
    $vars[] = "word";
    $vars[] = "word-length";
    $vars[] = "word-begins";
    $vars[] = "word-ends";
    $vars[] = "word-contains";
    $vars[] = "word-without";
    return $vars;
}

add_filter('query_vars', 'add_query_vars_filter');

function custom_rewrite_rule()
{
    add_rewrite_rule('^dictionary/([^\/]*)\/?', 'index.php?page_id=6&word=$matches[1]', 'top');
    add_rewrite_rule('^word-tips\/(.*)$', 'index.php?page_id=14&query_str=$matches[1]', 'top');
}


function metadesc($start, $length, $end, $contain, $without, $title)
{
    if ($title == true) {
        $str = "Words";
    } else {
        $str = "A list of Scrabble words";
    }
    if ($start != null) {
        $str .= " starting with " . strtoupper($start);
    }
    if ($length != null) {
        $str .= " with " . $length . "-letters length";
    }
    if ($end != null) {
        $str .= " ending with " . strtoupper($end);
    }
    if ($contain != null) {
        $str .= " containing " . strtoupper($contain);
    }
    if ($without != null) {
        $str .= " without " . strtoupper($without);
    }
    return $str;
}


// define the wpseo_metadesc callback
function filter_wpseo_metadesc($wpseo_replace_vars)
{
    $values = format_query(get_query_var('query_str'));
    if ($values) {
        $wpseo_replace_vars = metadesc($values['starting_with'], $values['with_letters_lenght'], $values['ending_with'], $values['containing'], $values['without'], false);
    }
    return $wpseo_replace_vars;
}

;
add_filter('wpseo_metadesc', 'filter_wpseo_metadesc', 10, 1);

function custom_title($title_parts)
{
    $values = format_query(get_query_var('query_str'));
    if ($values) {
        $title_parts = metadesc($values['starting_with'], $values['with_letters_lenght'], $values['ending_with'], $values['containing'], $values['without'], true);
    }
    return $title_parts;
}

add_filter('wpseo_title', 'custom_title');


function yoast_remove_canonical_items($canonical)
{
    if (is_page(14)) {
        return $canonical . get_query_var('query_str') . '/';
    }
    return $canonical;
}

add_filter('wpseo_canonical', 'yoast_remove_canonical_items');

add_action('init', 'custom_rewrite_rule', 10, 0);

function searchDefinition()
{
    $page_url = home_url() . '/dictionary/';
    $word = $_POST['word'];
    $response = array();
    $response['data'] = array();
    $mydb = new wpdb('harryxkn_007', 'Jame7s_BoNd', 'harryxkn_wordnet31', 'localhost');
    $rows = $mydb->get_results("SELECT * FROM words JOIN senses on senses.wordid = words.wordid JOIN synsets on senses.synsetid = synsets.synsetid WHERE words.lemma LIKE '" . $word . "' ORDER BY senses.tagcount DESC;");
    if (!empty($rows)) {
        $response['success'] = true;
        foreach ($rows as $obj) {
            $response['data'][] = $obj->definition;
        }
    } else if (empty($rows) && !empty($word)) {
        $response['success'] = false;
    }
    $response['push_url'] = $page_url . $word;
    wp_send_json($response);
    wp_die();
}

add_action('wp_ajax_searchDefinition', 'searchDefinition');
add_action('wp_ajax_nopriv_searchDefinition', 'searchDefinition');

function ltob($input_word, $atoz, &$num)
{
    $len_input_word = strlen($input_word);
    for ($i = 0; $i < $len_input_word; $i++) {
        $atoz_index = $atoz[$input_word[$i]];
        $num[$atoz_index] = min(7, $num[$atoz_index] + 1);
    }
    return $num;
}

add_shortcode('dictionary_form', 'dictionaryForm');
function dictionaryForm($atts)
{
//    global $wp_rewrite;
    ob_start();
    ?>
    <div class="container-fluid">
        <div class="row">
            <div class="col-md-12">
                <div class="page-title">Scrabble Dictionary</div>
                <div class="page-notice"> Check words in Scrabble Dictionary and make sure it's an official scrabble
                    word
                </div>
                <form method="post" action="" id="dictionaryForm">
                    <div class="row">
                        <div class="col-sm-6 col-md-6">
                            <input name="word" class="dictionary-input" required
                                   placeholder="Enter the word you want to check"
                                   type="text"/>
                        </div>
                        <div class="col-sm-6 col-md-6">
                            <input class="button form-btn" type="submit" value="Check dictionary">
                        </div>
                    </div>
                </form>
                <div id="words-meaning-list"></div>
            </div>
        </div>
    </div>
    <?php if (!empty(get_query_var('word'))) {
    echo "<script>jQuery(document).ready(function () {  
    jQuery('[name=word]').val('" . get_query_var('word', 1) . "'); jQuery('#dictionaryForm').submit(); });</script>";
}
    return ob_get_clean();
}


function searchWord()
{
    $response = array();
    parse_str($_POST['form'], $form_data);
    $letterlen = strlen(preg_replace("/[^a-zA-Z]+/", "", $form_data['q']));
    $searchword = strtolower(preg_replace("/[^a-zA-Z]+/", "", $form_data['q']));
    $wildno = min(2, strlen(preg_replace("/[^?]+/", "", $form_data['q'])));
    $wordlen = $letterlen + $wildno;
    $bw = strtoupper(preg_replace("/[^a-zA-Z]+/", "", $form_data['bw']));
    $ew = strtoupper(preg_replace("/[^a-zA-Z]+/", "", $form_data['ew']));
    $bwlen = strlen($bw);
    $ewlen = strlen($ew);
    $minlen = max(2, $bwlen + $ewlen);
    $dts = $form_data['dts'];
    $dlen = $form_data['dlen'];
    $fw1 = preg_replace('/[^a-zA-Z?]+/', '', $form_data['q']);
    $atoz = array("a" => 0, "b" => 1, "c" => 2, "d" => 3, "e" => 4, "f" => 5, "g" => 6, "h" => 7, "i" => 8,
        "j" => 9, "k" => 10, "l" => 11, "m" => 12, "n" => 13, "o" => 14, "p" => 15, "q" => 16,
        "r" => 17, "s" => 18, "t" => 19, "u" => 20, "v" => 21, "w" => 22, "x" => 23, "y" => 24, "z" => 25);
    $result = array_fill(0, 26, 0);
    $result = ltob($searchword, $atoz, $result);
    $input_bin = implode("", $result);
    if ($wordlen == 1) {
        echo '<p class="notificationtext"> Please enter at least 2 letters above!</p>';
    }
    $response['words'] = array();
    $response['length'] = array();
    $response['points'] = array();
    if ($wordlen > 1 and $wordlen < 16) {
        $mydb = new wpdb('harryxkn_007', 'Jame7s_BoNd', 'harryxkn_english', 'localhost');
        $listlen = $wordlen - $minlen;
        if ($dlen >= $minlen and $dlen <= $wordlen) $listlen = 0;
        for ($ltp = 0; $ltp <= $listlen; $ltp++) {
            $sublen = $wordlen - $ltp;
            if ($dlen >= $minlen and $dlen <= $wordlen) $sublen = $dlen;
            $rows = $mydb->get_results("select x,bin,len,scr from dict_scr where len=$sublen and dstype<=$dts;");
            $nsns = 0;
            $count_word = 0;
            if (!empty($rows)) {
                foreach ($rows as $obj) {
                    $count = 0;
                    for ($i = 0; $i < 26; $i++) {
                        if ($obj->bin[$i] > $input_bin[$i]) {
                            $count = $count + $obj->bin[$i] - $input_bin[$i];
                        }
                        if ($count > $wildno) {
                            break;
                        }
                    }
                    if ($count <= $wildno) {
                        if ((substr($obj->x, 0, $bwlen) == $bw) and (substr($obj->x, $obj->len - $ewlen, $ewlen) == $ew)) {
                            $response['points'][$count_word] = $obj->scr;
                            $response['words'][$count_word] = $obj->x;
                            $nsns = $nsns + 1;
                            if ($nsns == 1) {
                                    $response['length'][$count_word] =$sublen;
                            }
                        }
                    }
                    $count_word++;
                }
            }
        }
    }
    wp_send_json($response);
    wp_die();
}

add_action('wp_ajax_searchWord', 'searchWord');
add_action('wp_ajax_nopriv_searchWord', 'searchWord');

function suggestWord(){
    $word = $_POST['word'];
    $definition = $_POST['definition'];
//    var_dump($word);
//    var_dump($definition);
    $mydb = new wpdb('harryxkn_007', 'Jame7s_BoNd', 'harryxkn_requestedWords', 'localhost');
    if( ! empty($mydb->error) ) wp_die( $mydb->error );
//    $mydb ->query("INSERT INTO words (word, definition) VALUES ('".$word."', '".$definition."')");
    $mydb ->query($mydb->prepare("INSERT INTO words (word, definition) VALUES ('%s', '%s')",$word,$definition ));
    wp_send_json(array());
    wp_die();
}

add_action('wp_ajax_suggestWord', 'suggestWord');
add_action('wp_ajax_nopriv_suggestWord', 'suggestWord');


add_shortcode('main_form', 'mainForm');

function mainForm($atts)
{
    ob_start();
    ?>
    <div class="container-fluid">
        <div class="row">
            <div class="col-lg-12 col-md-12 col-sm-12">
                <h1 class="scrabble-header"> Words With Friends Helper</h1>
                <form name="findword" method="post" action="" id="scrabbleForm">
                    <div class="scrabble-notice"> Up to 15 letters, use ? as wildcards (2 max)</div>
                    <input type="text" name="q" class="typeletters"
                           autocomplete="off" required
                           value="<?php echo preg_replace("/[^a-zA-Z?]+/", "", $_POST['q']); ?>"
                           maxlength="15" placeholder="Type Letters Here"/> &nbsp;
                    <div>Optional:</div>
                    <div class="scrabble-options">
                        <input type="text" name="bw" class="letteroptions" autocomplete="off"
                               value="<?php echo preg_replace("/[^a-zA-Z]+/", "", $_POST['bw']); ?>" maxlength="15"
                               placeholder="Begins"/>&nbsp;
                        <input type="text" name="ew" class="letteroptions" autocomplete="off"
                               value="<?php echo preg_replace("/[^a-zA-Z]+/", "", $_POST['ew']); ?>" maxlength="15"
                               placeholder="Ends"/>
                        <input type="number" name="dlen" class="letteroptions" min="1" autocomplete="off"
                               value="<?php echo $_POST['dlen']; ?>" placeholder="Length"/>
                    </div>
                    <div class="row scrabble-radio">
                        <div class="col-md-12">
                            <div class="row">
                                <div class="col-md-12">
                                    <input type="radio" id="scrabble-radio_us" name="dts"
                                           value=1 <?php if (!isset($_POST['dts']) || (isset($_POST['dts']) && $_POST['dts'] == 1)) echo ' checked="checked"' ?> />
                                    <label for="scrabble-radio_us">United States (TWL)</label>
                                </div>
                                <div class="col-md-12">
                                    <input type="radio" id="scrabble-radio_uk" name="dts"
                                           value=2 <?php if (isset($_POST['dts']) && $_POST['dts'] == 2) echo ' checked="checked"'; ?> />
                                    <label for="scrabble-radio_uk">United Kingdom (SOWPODS)</label>
                                </div>
                            </div>
                        </div>
                    </div>
                    <input class="button form-btn" type="submit" value="Search">
                    <input type="reset" class="form-btn" value="Reset">
                </form>
                <div id="scrabbleTable"></div>
            </div>
        </div>
    </div>
    <?php
    return ob_get_clean();
}


function wordTips()
{
    parse_str($_POST['form'], $form);
    $bw = strtoupper(preg_replace("/[^a-zA-Z]+/", "", $form['wt-bw']));
    $ew = strtoupper(preg_replace("/[^a-zA-Z]+/", "", $form['wt-ew']));
    $cw = strtoupper(preg_replace("/[^a-zA-Z]+/", "", $form['wt-cw']));
    $nw = strtoupper(preg_replace("/[^a-zA-Z]+/", "", $form['wt-nw']));
    $dlen = $form['wt-dlen'];
    $bwlen = strlen($bw);
    $ewlen = strlen($ew);
    $nwlen = strlen($nw);
    $cwlen = strlen($cw);
    $dts = 3;
    $url_path_new = array();
    $page_url = home_url() . '/word-tips/';
    if ($bwlen > 0) {
        $url_path_new[] = 'starting_with';
        $url_path_new[] = strtolower($bw);
    }
    if ($ewlen > 0) {
        $url_path_new[] = 'ending_with';
        $url_path_new[] = strtolower($ew);
    }
    if ($cwlen > 0) {
        $url_path_new[] = 'containing';
        $url_path_new[] = strtolower($cw);
    }
    if ($nwlen > 0) {
        $url_path_new[] = 'without';
        $url_path_new[] = strtolower($nw);
    }
    if ($dlen > 1) {
        $url_path_new[] = 'with_letters_lenght';
        $url_path_new[] = strtolower($dlen);
    }
    $response = array();
    if ($bwlen > 0 or $ewlen > 0 or $cwlen > 0 or $dlen > 0) {
        $servername = "localhost";
        $username = "harryxkn_007";
        $password = "Jame7s_BoNd";
        $dbname = "harryxkn_english";
        $conn = mysqli_connect($servername, $username, $password, $dbname);
        if (!$conn) {
            die("Connection failed: " . mysqli_connect_error());
        }
        if ($dlen > 1 and $dlen < 16) {
            $sql = "select x,bin,len,scr,wwf from dict_scr where len=$dlen and dstype<=$dts;";
        }
        if ($dlen < 2 or $dlen > 15) {
            $sql = "select x,bin,len,scr,wwf from dict_scr where dstype<=$dts;";
        }
        $db = mysqli_query($conn, $sql);
        $nsns = 0;
        $response['length'] = array();
        $response['words'] = array();
        $response['scrabble-points'] = array();
        $response['wwf-points'] = array();
        $count_word = 0;
        if (mysqli_num_rows($db) > 0) {
            while ($row = mysqli_fetch_assoc($db)) {
                $exx = $row["x"];
                $ecw = $cw;
                $enw = $nw;
                if (strlen($cw) < 1) {
                    $exx = $row["x"] . "*";
                    $ecw = $cw . "*";
                }
                if (strlen($nw) < 1) {
                    $enw = $nw . "#";
                }
                if ((substr($row["x"], 0, $bwlen) == $bw) and (substr($row["x"], $row["len"] - $ewlen, $ewlen) == $ew)
                    and (strpos($exx, $ecw) !== false) and (strpos($exx, $enw) === false)
                ) {
                    $nsns = $nsns + 1;
                    $response['length'][$count_word] = $row["len"];
                    $response['words'][$count_word] = $row["x"];
                    $response['scrabblePoints'][$count_word] = $row["scr"];
                    $response['wwfPoints'][$count_word] = $row["wwf"];
                    $count_word++;
                }
            }
        }
    }
    $response['tableTitles'] = array_unique($response['length'], SORT_REGULAR);
    $response['metadesc'] = metadesc(strtolower($bw), strtolower($dlen), strtolower($ew), strtolower($cw), strtolower($nw), false);
    $response['push_url'] = $page_url . implode('/', $url_path_new);
    wp_send_json($response);
    wp_die();
}

add_action('wp_ajax_wordTips', 'wordTips');
add_action('wp_ajax_nopriv_wordTips', 'wordTips');

add_shortcode('word-tips_form', 'TipsForm');

function format_query($query_str)
{
    if ($query_str) {
        $query = explode('/', $query_str);
        if ($query) {
            $query = array_chunk($query, 2);
            $formatted = array();
            foreach ($query as $key => $item) {
                $formatted[$item[0]] = $item[1];
            }
            return $formatted;
        } else return false;
    } else return false;
}

function TipsForm($atts)
{
    ob_start();
    $values = format_query(get_query_var('query_str'));
    ?>
    <div class="container-fluid">
        <div class="row">
            <div class="col-lg-12 col-md-12 col-sm-12">
                <div class="page-title">Words With Friends Tips</div>
                <form method="post" action="" id="wordTipsForm">
                    <div class="row">
                        <div class="col-md-12">
                            <input name="wt-dlen" class="letteroptions" autocomplete="off"
                                   value="<?php echo $_POST['wt-dlen']; ?>" placeholder="Length" ;/>
                            <input name="wt-bw" class="letteroptions" autocomplete="off"
                                   value="<?php echo preg_replace("/[^a-zA-Z]+/", "", $_POST['wt-bw']); ?>"
                                   maxlength="15" placeholder="Begins"/>
                            <input name="wt-ew" class="letteroptions" autocomplete="off"
                                   value="<?php echo preg_replace("/[^a-zA-Z]+/", "", $_POST['wt-ew']); ?>"
                                   maxlength="15" placeholder="Ends"/>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-12">
                            <input name="wt-cw" class="letteroptions" autocomplete="off"
                                   value="<?php echo preg_replace("/[^a-zA-Z]+/", "", $_POST['wt-cw']); ?>"
                                   maxlength="15" placeholder="Contains"/>
                            <input name="wt-nw" class="letteroptions" autocomplete="off"
                                   value="<?php echo preg_replace("/[^a-zA-Z]+/", "", $_POST['wt-nw']); ?>"
                                   maxlength="15" placeholder="Without"/>
                        </div>
                    </div>
                    <input class="button form-btn" type="submit" value="Search">
                    <input type="reset" class="form-btn" value="Reset">
                </form>
                <div id="words-tips-table"></div>
            </div>
        </div>
    </div>
    <?php
    if ($values) {
        echo "<script>jQuery(document).ready(function () {
    jQuery('[name=wt-dlen]').val('" . $values['with_letters_lenght'] . "');
    jQuery('[name=wt-bw]').val('" . $values['starting_with'] . "');
    jQuery('[name=wt-ew]').val('" . $values['ending_with'] . "');
    jQuery('[name=wt-cw]').val('" . $values['containing'] . "');
    jQuery('[name=wt-nw]').val('" . $values['without'] . "');
    jQuery('#wordTipsForm').submit(); });</script>";
    }
    return ob_get_clean();
}
