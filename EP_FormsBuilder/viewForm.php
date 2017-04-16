<?php
require_once './config.php';

$connection = mysqli_connect($sql_server, $sql_user_name, $sql_password, $sql_database_name);

$GLOBALS['lfb_connection'] = mysqli_connect($sql_server, $sql_user_name, $sql_password, $sql_database_name);
$GLOBALS['lfb_connection']->sqlPrefix = $sql_prefix;

function efp_initTranslations(){
    $translationFile = file_get_contents( 'languages/Estimation_Form.lang');
    $lastPos = 0;
    $toReplace = array();
    $replaceBy = array();
    while (($lastPos = strpos($translationFile, 'msgid "', $lastPos)) !== false) {
        $key = substr($translationFile,$lastPos+7,strpos($translationFile,'"',$lastPos+7)-($lastPos+7));
        $toReplace[] = $key;
        $lastPos = $lastPos + 8;
    }
    
    $lastPos = 0;
    while (($lastPos = strpos($translationFile, 'msgstr "', $lastPos)) !== false) {
        $value = substr($translationFile,$lastPos+8,strpos($translationFile,'"',$lastPos+8)-($lastPos+8));
        $replaceBy[] = $value;
        $lastPos = $lastPos + 8;
    }
    $GLOBALS['lfb_translationsLfbKeys'] = $toReplace;
    $GLOBALS['lfb_translationsLfbValues'] = $replaceBy;
    
    $translationFile = file_get_contents( 'languages/tdgn/FormDesigner.lang');
    $lastPos = 0;
    $toReplace = array();
    $replaceBy = array();
    while (($lastPos = strpos($translationFile, 'msgid "', $lastPos)) !== false) {
        $key = substr($translationFile,$lastPos+7,strpos($translationFile,'"',$lastPos+7)-($lastPos+7));
        $toReplace[] = $key;
        $lastPos = $lastPos + 8;
    }
    
    $lastPos = 0;
    while (($lastPos = strpos($translationFile, 'msgstr "', $lastPos)) !== false) {
        $value = substr($translationFile,$lastPos+8,strpos($translationFile,'"',$lastPos+8)-($lastPos+8));
        $replaceBy[] = $value;
        $lastPos = $lastPos + 8;
    }
    $GLOBALS['lfb_translationsTldKeys'] = $toReplace;
    $GLOBALS['lfb_translationsTldValues'] = $replaceBy;
}
efp_initTranslations();

if (isset($_GET['form'])) {


    function sql_get_results($query) {
        $chkClose = false;

        $rep = array();
        $sql = mysqli_query($GLOBALS['lfb_connection'], $query);

        while ($data = mysqli_fetch_object($sql)) {
            $rep[] = $data;
        }
        return $rep;
    }

    function esc_url($url) {
        return filter_var($url, FILTER_SANITIZE_URL);
    }

    function trailingslashit($string) {
        $string = rtrim($string, '/\\');
        return $string . '/';
    }

    $formID = mysqli_real_escape_string($connection, $_GET['form']);
    $lfb_form = sql_get_results('SELECT * FROM ' . $GLOBALS['lfb_connection']->sqlPrefix . 'wpefc_forms WHERE id=' . $formID . ' LIMIT 1');
    $form = $lfb_form[0];
    $lfb_assetsUrl = 'assets/';
    $lfb_assetsDir = esc_url(trailingslashit(realpath(dirname(__FILE__) . '/assets/')));
    $lfb_cssUrl = 'export/';
    $lfb_uploadsDir = esc_url(trailingslashit(realpath(dirname(__FILE__) . '/uploads/')));
    $lfb_uploadsUrl = 'uploads/';

function __($string,$key) {
        if($key == 'lfb'){
            if(in_array($string, $GLOBALS['lfb_translationsLfbKeys'])){
                $index = array_search($string, $GLOBALS['lfb_translationsLfbKeys']);
                if($index>-1 && $GLOBALS['lfb_translationsLfbValues'][$index] != ""){
                    $string = $GLOBALS['lfb_translationsLfbValues'][$index];
                }
            }
        }else if($key == 'tld'){
            if(in_array($string, $GLOBALS['lfb_translationsTldKeys'])){
                $index = array_search($string, $GLOBALS['lfb_translationsTldKeys']);
                if($index>-1 && $GLOBALS['lfb_translationsTldValues'][$index] != ""){
                    $string = $GLOBALS['lfb_translationsTldValues'][$index];
                }
            }
        }
        return $string;
    }

    function sql_insert_id() {
        $rep = false;
        $rep = mysqli_insert_id($GLOBALS['lfb_connection']);
        return $rep;
    }

    function sql_query($query) {
        $rep = false;
        $sql = mysqli_query($GLOBALS['lfb_connection'], $query);
    }

    function sql_insert($table, $data) {
        $rep = false;

        $keysString = '';
        $dataString = '';
        foreach ($data as $key => $value) {
            $keysString.=',' . $key;
            $dataString.=', "' . addslashes($value) . '"';
        }
        if ($dataString != "") {
            $dataString = substr($dataString, 1);
            $keysString = substr($keysString, 1);

            $sql = mysqli_query($GLOBALS['lfb_connection'], 'INSERT INTO ' . $table . ' (' . $keysString . ') VALUES (' . $dataString . ')') or die(mysqli_error($GLOBALS['lfb_connection']));
            $rep = true;
        }
        return $rep;
    }

    function sql_update($table, $data, $selector) {
        $dataString = '';
        $whereString = '';
        $rep = false;

        foreach ($data as $key => $value) {
            $dataString.=', ' . $key . '="' . addslashes($value) . '"';
        }
        foreach ($selector as $key => $value) {
            $whereString.=', ' . $key . '="' . addslashes($value) . '"';
        }
        if ($dataString != "") {
            if ($whereString != "") {
                $whereString = substr($whereString, 1);
                $whereString = 'WHERE ' . substr($whereString, 1);
            } else {
                $whereString = 'WHERE id>0';
            }
            $dataString = substr($dataString, 1);
            $sql = mysqli_query($GLOBALS['lfb_connection'], 'UPDATE ' . $table . ' SET ' . $dataString . ' ' . $whereString) or die(mysqli_error($GLOBALS['lfb_connection']));
            ;
            $rep = true;
        }
        return $rep;
        //
    }

    function sql_delete($table, $selector) {
        $rep = false;

        $whereString = '';
        foreach ($selector as $key => $value) {
            $whereString.=', ' . $key . '="' . $value . '"';
        }
        if ($whereString != "") {
            $sql = mysqli_query($GLOBALS['lfb_connection'], 'DELETE FROM ' . $table . ' ' . $whereString);
            $rep = true;
        }
        return $rep;
    }

    function sanitize_text_field($string) {
        $rep = $string;
        $rep = mysqli_real_escape_string($GLOBALS['lfb_connection'], $string);

        return $rep;
    }

    function getSettings() {

        $table_name = $GLOBALS['lfb_connection']->sqlPrefix . "wpefc_settings";
        $settings = sql_get_results("SELECT * FROM $table_name WHERE id=1 LIMIT 1");
        $rep = false;
        if (count($settings) > 0) {
            $rep = $settings[0];
        }
        return $rep;
    }

    /* function lfb_rewrite_rules($wp_rewrite) {
      $new_rules = array('EPFormsBuilder/paypal' => 'index.php?EPFormsBuilder=paypal');
      $wp_rewrite->rules = $new_rules + $wp_rewrite->rules;
      } */

    function jsonRemoveUnicodeSequences($struct) {
        return json_encode($struct, JSON_UNESCAPED_UNICODE);
    }

    function dateFormatToDatePickerFormat($dateFormat) {
        $chars = array(
            'd' => 'dd', 'j' => 'd', 'l' => 'DD', 'D' => 'D',
            'm' => 'mm', 'n' => 'm', 'F' => 'MM', 'M' => 'M',
            'Y' => 'yy', 'y' => 'y',
        );
        return strtr((string) $dateFormat, $chars);
    }

    function add_googleanalytics() {
        echo "<script>
            (function(i,s,o,g,r,a,m){i['GoogleAnalyticsObject']=r;i[r]=i[r]||function(){
            (i[r].q=i[r].q||[]).push(arguments)},i[r].l=1*new Date();a=s.createElement(o),
            m=s.getElementsByTagName(o)[0];a.async=1;a.src=g;m.parentNode.insertBefore(a,m)
            })(window,document,'script','//www.google-analytics.com/analytics.js','ga');
            ga('create', '" . $form->analyticsID . "', 'auto');
            ga('send', 'pageview');
          </script>";
    }

    function getFormatedPrice($price, $form) {
        $formatedPrice = $price;
        $priceNoDecimals = $formatedPrice;
        $decimals = "";
        if (strpos($formatedPrice, '.') > 0) {
            $formatedPrice = number_format($formatedPrice, 2, ".", "");
            $priceNoDecimals = substr($formatedPrice, 0, strpos($formatedPrice, '.'));
            $decimals = substr($formatedPrice, strpos($formatedPrice, '.') + 1, 2);
            $formatedPrice = str_replace(".", $formatedPrice, $form->decimalsSeparator);
            $decimals.='0';
            if (strlen($decimals) == 1) {
                
            }
            if (strlen($priceNoDecimals) > 3) {
                $formatedPrice = substr($priceNoDecimals, 0, -3) . $form->thousandsSeparator . substr($priceNoDecimals, -3) . $form->decimalsSeparator . $decimals;
            }
        } else {
            if (strlen($priceNoDecimals) > 3) {
                $formatedPrice = substr($priceNoDecimals, 0, -3) . $form->thousandsSeparator . substr($priceNoDecimals, -3);
            }
        }


        return $formatedPrice;
    }

    /**
     * Get  fields datas
     * @since   1.6.0
     * @return object
     */
    function getFieldsData() {

        $table_name = $GLOBALS['lfb_connection']->sqlPrefix . "wpefc_fields";
        $rows = sql_get_results("SELECT * FROM $table_name  ORDER BY ordersort ASC");
        return $rows;
    }

    /**
     * Get  fields from specific form
     * @since   1.6.0
     * @return object
     */
    function getFieldDatas($form_id) {

        $table_name = $GLOBALS['lfb_connection']->sqlPrefix . "wpefc_fields";
        $rows = sql_get_results("SELECT * FROM $table_name WHERE formID=$form_id ORDER BY ordersort ASC");
        return $rows;
    }

    /**
     * Get  form by pageID
     * @since   1.6.0
     * @return object
     */
    function getFormByPageID($pageID) {

        $table_name = $GLOBALS['lfb_connection']->sqlPrefix . "wpefc_forms";
        $rows = sql_get_results("SELECT * FROM $table_name WHERE form_page_id=$pageID LIMIT 1");
        if ($rows) {
            return $rows[0];
        } else {
            return null;
        }
    }

    /**
     * Get Forms datas
     * @return Array
     */
    function getFormsData() {

        $table_name = $GLOBALS['lfb_connection']->sqlPrefix . "wpefc_forms";
        $rows = sql_get_results("SELECT * FROM $table_name");
        return $rows;
    }

    /**
     * Get specific Form datas
     * @return object
     */
    function getFormDatas($form_id) {

        $table_name = $GLOBALS['lfb_connection']->sqlPrefix . "wpefc_forms";
        $rows = sql_get_results("SELECT * FROM $table_name WHERE id=$form_id LIMIT 1");
        if (count($rows) > 0) {
            return $rows[0];
        } else {
            return null;
        }
    }

    /**
     * Return steps data.
     * @access  public
     * @since   1.0.0
     * @return  object
     */
    function getStepsData($form_id) {
        global $wpdb;
        $table_name = $GLOBALS['lfb_connection']->sqlPrefix . "wpefc_steps";
        $rows = sql_get_results("SELECT * FROM $table_name WHERE formID=$form_id ORDER BY ordersort");
        return $rows;
    }

    /**
     * Return items data.
     * @access  public
     * @since   1.0.0
     * @return  object
     */
    function getItemsData($form_id) {
        global $wpdb;
        $results = array();
        $table_name = $GLOBALS['lfb_connection']->sqlPrefix . "wpefc_steps";
        $steps = sql_get_results("SELECT * FROM $table_name WHERE formID=$form_id ORDER BY ordersort");
        foreach ($steps as $step) {
            $table_name = $GLOBALS['lfb_connection']->sqlPrefix . "wpefc_items";
            $rows = sql_get_results("SELECT * FROM $table_name WHERE stepID=$step->id ORDER BY ordersort");
            foreach ($rows as $row) {
                $results[] = $row;
            }
        }
        return $results;
    }
    $settings = getSettings();
    ?>
    <!DOCTYPE html>
    <html>
        <head>
            <meta charset="UTF-8">
            <title></title>
            <link rel='stylesheet' id='lfb-reset-css'  href='assets/css/reset.css' type='text/css' media='all' />
            <link rel='stylesheet' id='lfb-jqueryui-css'  href='assets/css/jquery-ui-theme/jquery-ui.min.css' type='text/css' media='all' />
            <link rel='stylesheet' id='lfb-bootstrap-css'  href='assets/css/bootstrap.min.css' type='text/css' media='all' />
            <link rel='stylesheet' id='lfb-bootstrap-select-css'  href='assets/css/bootstrap-select.min.css' type='text/css' media='all' />
            <link rel='stylesheet' id='lfb-dropzone-css'  href='assets/css/dropzone.min.css' type='text/css' media='all' />
            <link rel='stylesheet' id='lfb-reset-css'  href='assets/css/colpick.min.css' type='text/css' media='all' />
            <link rel='stylesheet' id='lfb-flat-ui-css'  href='assets/css/flat-ui_frontend.min.css' type='text/css' media='all' />
            <link rel='stylesheet' id='lfb-fontawesome-css'  href='assets/css/font-awesome.min.css' type='text/css' media='all' />
            <link rel='stylesheet' id='lfb-core-css'  href='assets/css/lfb_forms.min.css' type='text/css' media='all' />

    <?php
     if (isset($_GET['lfb_action']) && $_GET['lfb_action'] == 'preview') {
    if ($settings->tdgn_enabled == 707  && strlen($settings->purchaseCode) > 8) {
        echo "<link rel='stylesheet' href='assets/css/lfb_formDesigner_frontend.min.css' type='text/css'></link>";
    }
    }
    if ($form->usedCssFile != '' && file_exists('export/' . $form->usedCssFile)) {
        echo "<link rel='stylesheet' id='lfb-core-css'  href='export/" . $form->usedCssFile . "' type='text/css' media='all' />";
    }

    if (!$form->colorA || $form->colorA == "") {
        $form->colorA = $settings->colorA;
    }
    if (!$form->colorB || $form->colorB == "") {
        $form->colorB = $settings->colorB;
    }
    if (!$form->colorC || $form->colorC == "") {
        $form->colorC = $settings->colorC;
    }
    if (!$form->item_pictures_size || $form->item_pictures_size == "") {
        $form->item_pictures_size = $settings->item_pictures_size;
    }
    $output = '<style>';
    if ($form->useGoogleFont && $form->googleFontName != "") {
        $fontname = str_replace(' ', '+', $form->googleFontName);
        $output .= '@import url(https://fonts.googleapis.com/css?family=' . $fontname . ':400,700);';

        $output .= 'body:not(.wp-admin) #estimation_popup.wpe_bootstraped[data-form="' . $form->id . '"] {';
        $output .= ' font-family:"' . $form->googleFontName . '"; ';
        $output .= '}';
    }

    $output .= '#estimation_popup.wpe_bootstraped[data-form="' . $form->id . '"] #mainPanel {';
    $output .= ' background-color:' . $form->colorBg . '; ';
    $output .= '}';
    $output .= '#estimation_popup.wpe_bootstraped[data-form="' . $form->id . '"] #lfb_loader {';
    $output .= ' background-color:' . $form->colorA . '; ';
    $output .= '}';
    $output .= '#estimation_popup.wpe_bootstraped[data-form="' . $form->id . '"]  {';
    $output .= ' color:' . $form->colorB . '; ';
    $output .= '}';
    $output .= "\n";
    $output .= '#estimation_popup.wpe_bootstraped[data-form="' . $form->id . '"] .genSlide .lfb_totalBottomContainer hr  {';
    $output .= ' border-color:' . $form->colorC . '; ';
    $output .= '}';
    $output .= "\n";


    $fieldsColor = $form->colorC;
    if (strtolower($fieldsColor) == '#ffffff') {
        $fieldsColor = '#bdc3c7';
    }
    $output .= 'body #estimation_popup.wpe_bootstraped[data-form="' . $form->id . '"] .form-control,#estimation_popup.wpe_bootstraped[data-form="' . $form->id . '"] #mainPanel ,'
            . '#estimation_popup.wpe_bootstraped[data-form="' . $form->id . '"] p,#estimation_popup.wpe_bootstraped[data-form="' . $form->id . '"] #lfb_summary tbody td,'
            . '#estimation_popup.wpe_bootstraped[data-form="' . $form->id . '"] #lfb_summary tbody #sfb_summaryTotalTr th:not(#lfb_summaryTotal) {';
    $output .= ' color:' . $fieldsColor . '; ';
    $output .= '}';
    $output .= "\n";

    $output .= '#estimation_popup.wpe_bootstraped[data-form="' . $form->id . '"]  .tooltip .tooltip-inner,#estimation_popup.wpe_bootstraped[data-form="' . $form->id . '"]   #mainPanel .genSlide .genContent div.selectable span.icon_quantity,#estimation_popup.wpe_bootstraped[data-form="' . $form->id . '"]   .dropdown-inverse {';
    $output .= ' background-color:' . $form->colorB . '; ';
    $output .= '}';
    $output .= "\n";
    $output .= '#estimation_popup.wpe_bootstraped[data-form="' . $form->id . '"]   .tooltip.top .tooltip-arrow {';
    $output .= ' border-top-color:' . $form->colorB . '; ';
    $output .= '}';
    $output .= "\n";
    $output .= '#estimation_popup.wpe_bootstraped[data-form="' . $form->id . '"]   .tooltip.bottom .tooltip-arrow {';
    $output .= ' border-bottom-color:' . $form->colorB . '; ';
    $output .= '}';
    $output .= "\n";
    $output .= '#estimation_popup.wpe_bootstraped[data-form="' . $form->id . '"]   .btn-primary,#estimation_popup.wpe_bootstraped[data-form="' . $form->id . '"] .gform_button,#estimation_popup.wpe_bootstraped[data-form="' . $form->id . '"]   .btn-primary:hover,#estimation_popup.wpe_bootstraped[data-form="' . $form->id . '"]   .btn-primary:active,#estimation_popup.wpe_bootstraped[data-form="' . $form->id . '"]    .genPrice .progress .progress-bar-price,#estimation_popup.wpe_bootstraped[data-form="' . $form->id . '"]    .progress-bar,#estimation_popup.wpe_bootstraped[data-form="' . $form->id . '"]   .quantityBtns a,#estimation_popup.wpe_bootstraped[data-form="' . $form->id . '"]   .btn-primary:active,#estimation_popup.wpe_bootstraped[data-form="' . $form->id . '"]    .btn-primary.active,#estimation_popup.wpe_bootstraped[data-form="' . $form->id . '"]    .open .dropdown-toggle.btn-primary,#estimation_popup.wpe_bootstraped[data-form="' . $form->id . '"]   .dropdown-inverse li.active > a,#estimation_popup.wpe_bootstraped[data-form="' . $form->id . '"]    .dropdown-inverse li.selected > a,#estimation_popup.wpe_bootstraped[data-form="' . $form->id . '"]   .btn-primary:active,#estimation_popup.wpe_bootstraped[data-form="' . $form->id . '"]
                    .btn-primary.active,#estimation_popup.wpe_bootstraped[data-form="' . $form->id . '"]   .open .dropdown-toggle.btn-primary,#estimation_popup.wpe_bootstraped[data-form="' . $form->id . '"]   .btn-primary:hover,#estimation_popup.wpe_bootstraped[data-form="' . $form->id . '"]    .btn-primary:focus,#estimation_popup.wpe_bootstraped[data-form="' . $form->id . '"]    .btn-primary:active,#estimation_popup.wpe_bootstraped[data-form="' . $form->id . '"]    .btn-primary.active,#estimation_popup.wpe_bootstraped[data-form="' . $form->id . '"]    .open .dropdown-toggle.btn-primary {';
    $output .= ' background-color:' . $form->colorA . '; ';
    $output .= '}';
    $output .= "\n";
    $output .= '#estimation_popup.wpe_bootstraped[data-form="' . $form->id . '"] .lfb_dropzone:focus,#estimation_popup.wpe_bootstraped[data-form="' . $form->id . '"] .has-switch > div.switch-on label,#estimation_popup.wpe_bootstraped[data-form="' . $form->id . '"]   .form-group.focus .form-control,#estimation_popup.wpe_bootstraped[data-form="' . $form->id . '"]   .form-control:focus {';
    $output .= ' border-color:' . $form->colorA . '; ';
    $output .= '}';
    $output .= "\n";
    $output .= '#estimation_popup.wpe_bootstraped[data-form="' . $form->id . '"] a:not(.btn),#estimation_popup.wpe_bootstraped[data-form="' . $form->id . '"]   a:not(.btn):hover,#estimation_popup.wpe_bootstraped[data-form="' . $form->id . '"]   a:not(.btn):active,#estimation_popup.wpe_bootstraped[data-form="' . $form->id . '"]   #mainPanel .genSlide .genContent div.selectable.checked span.icon_select,#estimation_popup.wpe_bootstraped[data-form="' . $form->id . '"]   #mainPanel #finalPrice,#estimation_popup.wpe_bootstraped[data-form="' . $form->id . '"]    .ginput_product_price,#estimation_popup.wpe_bootstraped[data-form="' . $form->id . '"]   .checkbox.checked,#estimation_popup.wpe_bootstraped[data-form="' . $form->id . '"]    .radio.checked,#estimation_popup.wpe_bootstraped[data-form="' . $form->id . '"]   .checkbox.checked .second-icon,#estimation_popup.wpe_bootstraped[data-form="' . $form->id . '"]    .radio.checked .second-icon {';
    $output .= ' color:' . $form->colorA . '; ';
    $output .= '}';
    $output .= "\n";
    $output .= '#estimation_popup.wpe_bootstraped[data-form="' . $form->id . '"]   #mainPanel .genSlide .genContent div.selectable .img {';
    $output .= ' max-width:' . $form->item_pictures_size . 'px; ';
    $output .= ' max-height:' . $form->item_pictures_size . 'px; ';
    $output .= '}';
    $output .= "\n";
    $output .= '#estimation_popup.wpe_bootstraped[data-form="' . $form->id . '"]   #mainPanel,#estimation_popup.wpe_bootstraped[data-form="' . $form->id . '"]   .form-control {';
    $output .= ' color:' . $form->colorC . '; ';
    $output .= '}';
    $output .= "\n";
    $output .= '#estimation_popup.wpe_bootstraped[data-form="' . $form->id . '"]   .form-control,#estimation_popup.wpe_bootstraped[data-form="' . $form->id . '"] .lfb_dropzone  {';
    $output .= ' color:' . $form->colorC . '; ';
    $output .= ' border-color:' . $form->colorSecondary . '; ';
    $output .= '}';
    $output .= "\n";
    $output .= '#estimation_popup.wpe_bootstraped[data-form="' . $form->id . '"]  .lfb_dropzone .dz-preview .dz-remove {';
    $output .= ' color:' . $form->colorC . '; ';
    $output .= '}';
    $output .= "\n";
    $output .= '#estimation_popup.wpe_bootstraped[data-form="' . $form->id . '"] .btn-default,'
            . '#estimation_popup.wpe_bootstraped[data-form="' . $form->id . '"] .has-switch span.switch-right,'
            . '#estimation_popup.wpe_bootstraped[data-form="' . $form->id . '"] .bootstrap-datetimepicker-widget .has-switch span.switch-right,'
            . '#estimation_popup.wpe_bootstraped[data-form="' . $form->id . '"] .dropdown-menu {';
    $output .= ' background-color:' . $form->colorSecondary . '; ';
    $output .= ' color:' . $form->colorSecondaryTxt . '; ';
    $output .= '}';
    $output .= "\n";
    $output .= '#estimation_popup.wpe_bootstraped[data-form="' . $form->id . '"] .lfb_bootstrap-select.btn-group .dropdown-menu li a{';
    $output .= ' color:' . $form->colorSecondaryTxt . '; ';
    $output .= '}';
    $output .= "\n";

    $output .= '#estimation_popup.wpe_bootstraped[data-form="' . $form->id . '"] .lfb_bootstrap-select.btn-group .dropdown-menu li.selected> a,'
            . '#estimation_popup.wpe_bootstraped[data-form="' . $form->id . '"] .lfb_bootstrap-select.btn-group .dropdown-menu li.selected> a:hover{';
    $output .= ' background-color:' . $form->colorA . '; ';
    $output .= '}';
    $output .= "\n";

    $output .= '#estimation_popup.wpe_bootstraped[data-form="' . $form->id . '"] .has-switch>div.switch-off label{';
    $output .= ' border-color:' . $form->colorSecondary . '; ';
    $output .= ' background-color:' . $form->colorCbCircle . '; ';
    $output .= '}';
    $output .= "\n";
    $output .= '#estimation_popup.wpe_bootstraped[data-form="' . $form->id . '"] .has-switch>div.switch-on label{';
    $output .= ' background-color:' . $form->colorCbCircleOn . '; ';
    $output .= '}';
    $output .= "\n";

    $output .= '#estimation_popup.wpe_bootstraped[data-form="' . $form->id . '"] .btn-default .bs-caret > .caret {';
    $output .= '  border-bottom-color:' . $form->colorSecondaryTxt . '; ';
    $output .= '  border-top-color:' . $form->colorSecondaryTxt . '; ';
    $output .= '}';
    $output .= "\n";
    $output .= '#estimation_popup.wpe_bootstraped[data-form="' . $form->id . '"] .genPrice .progress .progress-bar-price  {';
    $output .= ' font-size:' . $form->priceFontSize . 'px; ';
    $output .= '}';
    $output .= "\n";
    $maxWidth = 240;
    if ($form->item_pictures_size > $maxWidth) {
        $maxWidth = $form->item_pictures_size;
    }
    $output .= '#estimation_popup.wpe_bootstraped[data-form="' . $form->id . '"] .itemDes  {';
    $output .= ' max-width:' . ($maxWidth) . 'px; ';
    $output .= '}';
    $output .= "\n";
    $output .= '#estimation_popup.wpe_bootstraped[data-form="' . $form->id . '"] #mainPanel .genSlide .genContent div.selectable .wpe_itemQtField  {';
    $output .= ' width:' . ($form->item_pictures_size) . 'px; ';
    $output .= '}';
    $output .= "\n";
    $output .= '#estimation_popup.wpe_bootstraped[data-form="' . $form->id . '"] #mainPanel .genSlide .genContent div.selectable .wpe_itemQtField .wpe_qtfield  {';
    $output .= ' margin-left:' . (0 - (100 - ($form->item_pictures_size)) / 2) . 'px; ';
    $output .= '}';
    $output .= "\n";
    $output .= 'body .lfb_datepickerContainer .ui-datepicker-title { ';
    $output .= ' background-color:' . $form->colorA . '; ';
    $output .= '}';
    $output .= "\n";
    $output .= 'body .lfb_datepickerContainer td a {';
    $output .= ' color:' . $form->colorA . '; ';
    $output .= '}';
    $output .= "\n";
    $output .= 'body .lfb_datepickerContainer  td.ui-datepicker-today a {';
    $output .= ' color:' . $form->colorB . '; ';
    $output .= '}';
    $output .= "\n";
    $output .= '#estimation_popup.wpe_bootstraped[data-form="' . $form->id . '"] .has-switch span.switch-left {';
    $output .= ' background-color:' . $form->colorA . '; ';
    $output .= '}';
    $output .= "\n";
    $output .= '#estimation_popup.wpe_bootstraped[data-form="' . $form->id . '"] #mainPanel #lfb_summary table thead {';
    $output .= ' background-color:' . $form->colorA . '; ';
    $output .= '}';
    $output .= "\n";
    $output .= '#estimation_popup.wpe_bootstraped[data-form="' . $form->id . '"] #mainPanel #lfb_summary table th.sfb_summaryStep {';
    $output .= ' background-color:' . $fieldsColor . '; ';
    $output .= '}';
    $output .= "\n";
    $output .= '#estimation_popup.wpe_bootstraped[data-form="' . $form->id . '"] #mainPanel #lfb_summary table #lfb_summaryTotal {';
    $output .= ' color:' . $form->colorA . '; ';
    $output .= '}';
    $output .= "\n";
    $output .= '#estimation_popup.wpe_bootstraped[data-form="' . $form->id . '"] #mainPanel .wpe_sliderQt {';
    $output .= ' background-color:' . $form->colorC . '; ';
    $output .= '}';
    $output .= '#estimation_popup.wpe_bootstraped[data-form="' . $form->id . '"] #mainPanel [data-type="slider"] {';
    $output .= ' background-color:' . $form->colorC . '; ';
    $output .= '}';
    $output .= '#estimation_popup.wpe_bootstraped[data-form="' . $form->id . '"] #mainPanel .wpe_sliderQt .ui-slider-range,'
            . ' #estimation_popup.wpe_bootstraped[data-form="' . $form->id . '"] #mainPanel .wpe_sliderQt .ui-slider-handle, '
            . ' #estimation_popup.wpe_bootstraped[data-form="' . $form->id . '"] #mainPanel [data-type="slider"] .ui-slider-range,'
            . '#estimation_popup.wpe_bootstraped[data-form="' . $form->id . '"] #mainPanel [data-type="slider"] .ui-slider-handle {';
    $output .= ' background-color:' . $form->colorA . ' ; ';
    $output .= '}';
    $output .= "\n";
    $output .= '#estimation_popup.wpe_bootstraped[data-form="' . $form->id . '"] #mainPanel #finalPrice span:nth-child(2) {';
    $output .= ' color:' . $form->colorC . '; ';
    $output .= '}';
    $output .= "\n";
    $output .= '#estimation_popup.wpe_bootstraped[data-form="' . $form->id . '"] .lfb_colorPreview {';
    $output .= ' border-color:' . $form->colorC . '; ';
    $output .= '}';
    $output .= "\n";

    if ($form->columnsWidth > 0) {
        $output .= '#estimation_popup.wpe_bootstraped[data-form="' . $form->id . '"] .genContent .col-md-2{';
        $output .= ' width:' . $form->columnsWidth . 'px; ';
        $output .= '}';
        $output .= "\n";
    }

    if ($form->inverseGrayFx) {
        $output .= 'body #estimation_popup.wpe_bootstraped[data-form="' . $form->id . '"] #mainPanel .genSlide div.selectable:not(.checked) .img {
                            -webkit-filter: grayscale(100%);
                            -moz-filter: grayscale(100%);
                            -ms-filter: grayscale(100%);
                            -o-filter: grayscale(100%);
                            filter: grayscale(100%);
                            filter: gray;
                        }
                        body #estimation_popup.wpe_bootstraped[data-form="' . $form->id . '"] #mainPanel .genSlide div.selectable.checked .img {
                                -webkit-filter: grayscale(0%);
                            -moz-filter: grayscale(0%);
                            -ms-filter: grayscale(0%);
                            -o-filter: grayscale(0%);
                            filter: grayscale(0%);
                            filter: none;
                        }';
    }


#estimation_popup.wpe_bootstraped #mainPanel #finalPrice span:nth-child(2) {

    if ($form->customCss != "") {
        $output .= $form->customCss;
        $output .= "\n";
    }
    echo $output.'</style>';    
    if ($form->customJS != "") {
        $output = "\n<script>\n" . $form->customJS . "</script>\n";
        echo $output;
    }

    ?>
            <script type='text/javascript' src='assets/js/jquery-2.2.4.min.js'></script>
            <script type='text/javascript' src='assets/js/jquery-ui.min.js'></script>
            <script type='text/javascript' src='assets/js/jquery.ui.touch-punch.min.js'></script>
            <script type='text/javascript' src='assets/js/bootstrap.min.js'></script>
            <script type='text/javascript' src='assets/js/bootstrap-select.min.js'></script>
            <script type='text/javascript' src='assets/js/bootstrap-switch.js'></script>
            <script type='text/javascript' src='assets/js/colpick.min.js'></script>
            <script type='text/javascript' src='assets/js/dropzone.min.js'></script>
            <script type='text/javascript' src='assets/js/jquery-ui-i18n.min.js'></script>
            <script type='text/javascript' src='assets/js/lfb_form.min.js'></script>
            
    <?php
    if (isset($_GET['lfb_action']) && $_GET['lfb_action'] == 'preview') {
    if ($settings->tdgn_enabled == 707  && strlen($settings->purchaseCode) > 8) {
        echo "<script type='text/javascript' src='assets/js/lfb_formDesigner_frontend.min.js'></script>";
    }
    }
    if ($form->gmap_key != "") {
        echo "<script type='text/javascript' src='http://maps.googleapis.com/maps/api/js?key=" . $form->gmap_key . "'></script>";
    }
    if ($form->analyticsID != "") {
         echo "<script>
            (function(i,s,o,g,r,a,m){i['GoogleAnalyticsObject']=r;i[r]=i[r]||function(){
            (i[r].q=i[r].q||[]).push(arguments)},i[r].l=1*new Date();a=s.createElement(o),
            m=s.getElementsByTagName(o)[0];a.async=1;a.src=g;m.parentNode.insertBefore(a,m)
            })(window,document,'script','//www.google-analytics.com/analytics.js','ga');
            ga('create', '".$form->analyticsID."', 'auto');
            ga('send', 'pageview');
          </script>";
    }
    if ($form->use_stripe != "") {
        echo "<script type='text/javascript' src='https://js.stripe.com/v2/'></script>";
    }
    $table_name = $GLOBALS['lfb_connection']->sqlPrefix . "wpefc_links";
    $linksData = sql_get_results("SELECT * FROM $table_name WHERE formID=" . $formID);
    foreach ($linksData as $link) {
        $link->conditions = json_decode($link->conditions, true);
    }
    $links = stripslashes(json_encode($linksData, true));

    $table_name = $GLOBALS['lfb_connection']->sqlPrefix . "wpefc_redirConditions";
    $redirections = sql_get_results("SELECT * FROM $table_name WHERE formID=" . $formID);
    $redirections = stripslashes(json_encode($redirections));

    if ($form->decimalsSeparator == "") {
        $form->decimalsSeparator = '.';
    }
    $usePdf = 0;
    if ($form->sendPdfCustomer || $form->sendPdfAdmin) {
        $usePdf = 1;
    }
    echo "<script>"
    . "var wpe_forms = [{
       'currentRef' : 0,
       'ajaxurl' : 'includes/lfb-core.php',
       'initialPrice' : '" . $form->initial_price . "',
       'max_price' : '" . $form->max_price . "',
       'percentToPay' : '" . $form->percentToPay . "',
       'currency' : '" . $form->currency . "',
       'currencyPosition' : '" . $form->currencyPosition . "',
       'intro_enabled' : '" . $form->intro_enabled . "',
       'colorA' : '" . $form->colorA . "',
       'close_url' : '" . $form->close_url . "',
       'animationsSpeed' : '" . $form->animationsSpeed . "',
       'email_toUser' : '" . $form->email_toUser . "',
       'showSteps' : '" . $form->showSteps . "',
       'formID' : '" . $form->id . "',
       'showInitialPrice' : '" . $form->show_initialPrice . "',
       'disableTipMobile' : '" . $form->disableTipMobile . "',
       'legalNoticeEnable' : '" . $form->legalNoticeEnable . "',
       'links' : " . $links . ",
       'redirections': " . $redirections . ",
       'useRedirectionConditions': '" . $form->useRedirectionConditions . "',
       'usePdf': '" . $usePdf . "',
       'txt_yes' : '" . __('Yes', 'lfb') . "',
       'txt_no' : '" . __('No', 'lfb') . "',
       'txt_lastBtn' : '" . addslashes($form->last_btn) . "',
       'txt_btnStep' : '" . addslashes($form->btn_step) . "',
       'dateFormat' : '" . dateFormatToDatePickerFormat('Y-m-d') . "',
       'datePickerLanguage' : '" . $form->datepickerLang . "',
       'thousandsSeparator' : '" . $form->thousandsSeparator . "',
       'decimalsSeparator' : '" . $form->decimalsSeparator . "',
       'millionSeparator' : '" . $form->millionSeparator . "',
       'summary_hideQt' : '" . $form->summary_hideQt . "',
       'summary_hideZero' : '" . $form->summary_hideZero . "',
       'summary_hidePrices' : '" . $form->summary_hidePrices . "',
       'groupAutoClick' : '" . $form->groupAutoClick . "',
       'filesUpload_text' : '" . $form->filesUpload_text . "',
       'filesUploadSize_text' : '" . $form->filesUploadSize_text . "',
       'filesUploadType_text' : '" . $form->filesUploadType_text . "',
       'filesUploadLimit_text' : '" . $form->filesUploadLimit_text . "',
       'sendContactASAP' : '" . $form->sendContactASAP . "',
       'showTotalBottom' : '" . $form->showTotalBottom . "',
       'stripePubKey' : '" . $form->stripe_publishKey . "',
       'scrollTopMargin' : '" . $form->scrollTopMargin . "',
       'redirectionDelay' : '" . $form->redirectionDelay . "',
       'gmap_key': '" . $form->gmap_key . "',
       'txtDistanceError' : '" . $form->txtDistanceError . "',
       'formStyleSrc' : ''"
    . '}];'
    . '</script>';
    ?>
        </head>
        <body>
            <?php
            $settings = getSettings();
            $fields = getFieldDatas($form->id);
            $steps = getStepsData($form->id);
            $items = getItemsData($form->id);

            $formSession = uniqid();
            $priceSubs = '';
            $priceSubsClass = '';
            $dataSubs = '';
            $dataIsSubs = '';
            if ($form->isSubscription) {
                $dataIsSubs = 'data-isSubs="true"';
            }
            if ($form->isSubscription && $form->showSteps == 0) {
                $priceSubsClass = 'lfb_subsPrice';
                $priceSubs = '<span>' . $form->subscription_text . '</span>';
                $dataSubs = $form->subscription_text;
            }
            if ($form->isSubscription) {
                $priceSubBottom = '<span>' . $form->subscription_text . '</span>';
            }
            $dispIntro = '';
            if (!$form->intro_enabled) {
                $dispIntro = 'display:none !important;';
            }
            $progressBarHide = '';
            if ($form->showSteps == 2) {
                $progressBarHide = 'style="display: none !important;"';
            }

            $response = '<div id="lfb_bootstraped" class="lfb_bootstraped"><div id="estimation_popup" ' . $dataIsSubs . ' data-formtitle="' . $form->title . '" data-formsession="' . $formSession . '" data-autoclick="' . $form->groupAutoClick . '"  data-subs="' . $dataSubs . '" data-form="' . $formID . '" class="wpe_bootstraped wpe_fullscreen">
                <div id="lfb_loader"><div class="lfb_spinner"><div class="double-bounce1"></div><div class="double-bounce2"></div></div></div>
                <a id="wpe_close_btn" href="javascript:"><span class="fui-cross"></span></a>
                <div id="wpe_panel">
                <div class="container-fluid">
                    <div class="row">
                        <div class="" >
                            <div id="startInfos" style="' . $dispIntro . '">
                                <h1>' . $form->intro_title . '</h1>
                                <p>' . $form->intro_text . '</p>
                            </div>
                            <p style="' . $dispIntro . '">
                                <a href="javascript:" onclick="lfb_startFormIntro(' . $form->id . ');" class="btn btn-large btn-primary" id="btnStart">' . $form->intro_btn . '</a>
                            </p>

                            <div id="genPrice" class="genPrice" ' . $progressBarHide . '>
                                <div class="progress">
                                    <div class="progress-bar" style="width: 0%;">
                                        <div class="progress-bar-price ' . $priceSubsClass . '">
                                            <span>0$</span>
                                            ' . $priceSubs . '
                                        </div>
                                    </div>

                                </div>
                            </div>
                            <!-- /genPrice -->
                            <h2 id="finalText" class="stepTitle">' . $form->succeed_text . '</h2>
                        </div>
                        <!-- /col -->
                    </div>
                    <!-- /row -->
                <div id="mainPanel" class="palette-clouds" data-savecart="0" >
                <input type="hidden" name="action" value="lfb_upload_form"/>
                <input type="hidden" id="lfb_formSession" name="formSession" value="' . $formSession . '"/>';
            $i = 0;

            foreach ($steps as $dataSlide) {
                if ($dataSlide->formID == $form->id) {
                    $dataContent = json_decode($dataSlide->content);

                    $required = '';
                    if ($dataSlide->itemRequired > 0) {
                        $required = 'data-required="true"';
                    }
                    $useShowStepConditions = '';
                    $showStepConditionsOperator = '';
                    $showStepConditions = '';
                    if ($dataSlide->useShowConditions) {
                        $useShowStepConditions = 'data-useshowconditions="true"';
                        $dataSlide->showConditions = str_replace('"', "'", $dataSlide->showConditions);
                        $showStepConditions = 'data-showconditions="' . addslashes($dataSlide->showConditions) . '"';
                        $showStepConditionsOperator = 'data-showconditionsoperator="' . $dataSlide->showConditionsOperator . '"';
                    }

                    $response .= '<div class="genSlide" data-start="' . $dataContent->start . '" ' . $useShowStepConditions . ' ' . $showStepConditions . ' ' . $showStepConditionsOperator . ' data-showstepsum="' . $dataSlide->showInSummary . '" data-stepid="' . $dataSlide->id . '" data-title="' . $dataSlide->title . '" ' . $required . ' data-dependitem="' . $dataSlide->itemDepend . '">';
                    $response .= '	<h2 class="stepTitle">' . $dataSlide->title . '</h2>';
                    $contentNoDes = 'lfb_noDes';
                    if ($dataSlide->description != "") {
                        $response .= '	<p class="lfb_stepDescription">' . $dataSlide->description . '</p>';
                        $contentNoDes = '';
                    }
                    $response .= '	<div class="genContent container-fluid ' . $contentNoDes . '">';
                    $response .= '		<div class="row">';
                    $itemIndex = 0;
                    foreach ($items as $dataItem) {

                        if ($dataItem->stepID == $dataSlide->id) {
                            $chkDisplay = true;
                            $hiddenClass = '';
                            $checked = '';
                            $checkedCb = '';
                            $prodID = 0;
                            $itemRequired = '';
                            $showInSummary = '';
                            $useCalculation = '';
                            $calculation = '';
                            $useShowConditions = '';
                            $showConditionsOperator = '';
                            $showConditions = '';
                            $hideQtSummary = '';
                            $defaultValue = '';

                            if ($dataItem->defaultValue != "") {
                                $defaultValue = 'value="' . $dataItem->defaultValue . '"';
                            }

                            if ($dataItem->hideQtSummary) {
                                $hideQtSummary = 'data-hideqtsum="true"';
                            }

                            if ($dataItem->useShowConditions) {
                                $useShowConditions = 'data-useshowconditions="true"';
                                $dataItem->showConditions = str_replace('"', "'", $dataItem->showConditions);
                                $showConditions = 'data-showconditions="' . addslashes($dataItem->showConditions) . '"';
                                $showConditionsOperator = 'data-showconditionsoperator="' . $dataItem->showConditionsOperator . '"';
                            }

                            if ($dataItem->useCalculation) {
                                $useCalculation = 'data-usecalculation="true"';
                                $calculation = 'data-calculation="' . addslashes($dataItem->calculation) . '"';
                            }

                            if ($dataItem->isRequired) {
                                $itemRequired = 'data-required="true"';
                            }
                            if ($dataItem->ischecked == 1) {
                                $checked = 'prechecked';
                                $checkedCb = 'checked';
                            }
                            if ($dataItem->isHidden == 1) {
                                $hiddenClass = 'lfb-hidden';
                            }

                            if ($dataItem->showInSummary == 1) {
                                $showInSummary = 'data-showinsummary="true"';
                            }


                            $originalTitle = $dataItem->title;
                            $dataShowPrice = "";
                            if ($dataItem->showPrice) {
                                $dataShowPrice = 'data-showprice="1"';
                                if ($form->currencyPosition == 'right') {
                                    if ($dataItem->operation == "+") {
                                        $dataItem->title = $dataItem->title . " : " . getFormatedPrice($dataItem->price, $form) . $form->currency;
                                    }
                                    if ($dataItem->operation == "-") {
                                        $dataItem->title = $dataItem->title . " : -" . getFormatedPrice($dataItem->price, $form) . $form->currency;
                                    }
                                    if ($dataItem->operation == "x") {
                                        $dataItem->title = $dataItem->title . " : +" . getFormatedPrice($dataItem->price, $form) . '%';
                                    }
                                    if ($dataItem->operation == "/") {
                                        $dataItem->title = $dataItem->title . " : -" . getFormatedPrice($dataItem->price, $form) . '%';
                                    }
                                } else {
                                    if ($dataItem->operation == "+") {
                                        $dataItem->title = $dataItem->title . " : " . $form->currency . getFormatedPrice($dataItem->price, $form);
                                    }
                                    if ($dataItem->operation == "-") {
                                        $dataItem->title = $dataItem->title . " : -" . $form->currency . getFormatedPrice($dataItem->price, $form);
                                    }
                                    if ($dataItem->operation == "x") {
                                        $dataItem->title = $dataItem->title . " : +" . getFormatedPrice($dataItem->price, $form) . '%';
                                    }
                                    if ($dataItem->operation == "/") {
                                        $dataItem->title = $dataItem->title . " : -" . getFormatedPrice($dataItem->price, $form) . '%';
                                    }
                                }
                            }
                            $urlTag = "";
                            if ($dataItem->urlTarget != "") {
                                $urlTag .= 'data-urltarget="' . $dataItem->urlTarget . '"';
                            }
                            $isSinglePrice = '';
                            if ($form->isSubscription && $dataItem->isSinglePrice) {
                                $isSinglePrice = 'data-singleprice="true"';
                            }

                            if ($chkDisplay) {

                                $colClass = 'col-md-2' . ' ' . $hiddenClass . ' lfb_item';
                                if ($dataItem->useRow || $dataItem->type == 'richtext') {
                                    $itemIndex = 0;
                                    $colClass = 'col-md-12' . ' ' . $hiddenClass . ' lfb_item';
                                } else {
                                    if ($dataItem->isHidden == 0) {
                                        $itemIndex++;
                                    }
                                    if ($dataSlide->itemsPerRow > 0 && $itemIndex - 1 == $dataSlide->itemsPerRow) {
                                        $itemIndex = 1;
                                        $response .='<br/>';
                                    }
                                }
                                $distanceQt = '';
                                if ($dataItem->useDistanceAsQt && $dataItem->distanceQt != "") {
                                    $distanceQt = 'data-distanceqt="' . $dataItem->distanceQt . '"';
                                }

                                if ($dataItem->type == 'picture') {
                                    $response .= '<div class="itemBloc ' . $colClass . ' lfb_picRow">';
                                    $group = '';
                                    if ($dataItem->groupitems != "") {
                                        $group = 'data-group="' . $dataItem->groupitems . '"';
                                    }
                                    $tooltipPosition = 'bottom';
                                    if ($form->qtType == 1) {
                                        $tooltipPosition = 'top';
                                    }
                                    $response .= '<div class="selectable ' . $checked . '" ' . $itemRequired . ' ' . $useCalculation . ' ' . $hideQtSummary . ' ' . $calculation . ' ' . $distanceQt . ' ' . $useShowConditions . ' ' . $showConditions . ' ' . $showConditionsOperator . ' ' . $isSinglePrice . ' ' . $dataShowPrice . ' ' . $urlTag . ' ' . $showInSummary . '  data-reduc="' . $dataItem->reduc_enabled . '" data-reducqt="' . $dataItem->reducsQt . '"  data-operation="' . $dataItem->operation . '" data-itemid="' . $dataItem->id . '"  ' . $group . '  data-prodid="' . $prodID . '" data-title="' . $dataItem->title . '" data-toggle="tooltip" title="' . $dataItem->title . '" data-originaltitle="' . $originalTitle . '" data-placement="' . $tooltipPosition . '" data-price="' . $dataItem->price . '">';
                                    $tint = 'false';
                                    if ($dataItem->imageTint) {
                                        $tint = 'true';
                                    }
                                    $response .= '<img data-tint="' . $tint . '" src="' . $dataItem->image . '" alt="' . $dataItem->imageDes . '" class="img" />';

                                    $response .= '<span class="palette-clouds fui-cross icon_select"></span>';
                                    if ($dataItem->quantity_enabled) {
                                        if ($form->qtType == 1) {
                                            $qtMax = '';
                                            if (!$dataItem->useDistanceAsQt && $dataItem->quantity_max > 0) {
                                                $qtMax = 'max="' . $dataItem->quantity_max . '"';
                                            } else {
                                                $qtMax = 'max="999999999"';
                                            }
                                            if ($dataItem->quantity_min > 0) {
                                                $qtMin = $dataItem->quantity_min . '"';
                                            } else {
                                                $qtMin = '1';
                                            }
                                            $response .= '<div class="form-group wpe_itemQtField">';
                                            $response .= ' <input class="wpe_qtfield form-control" min="' . $qtMin . '" ' . $qtMax . ' type="number" value="' . $qtMin . '" /> ';

                                            $response .= '</div>';
                                        } else if (!$dataItem->useDistanceAsQt && $form->qtType == 2) {
                                            $response .= '<div class="quantityBtns wpe_sliderQtContainer" data-max="' . $dataItem->quantity_max . '" data-min="' . $dataItem->quantity_min . '">
                                                     <div class="wpe_sliderQt"></div>
                                                 </div>';
                                            $valMin = 1;
                                            if ($dataItem->quantity_min > 0) {
                                                $valMin = $dataItem->quantity_min;
                                            }
                                            $response .= '<span class="palette-turquoise icon_quantity wpe_hidden">' . $valMin . '</span>';
                                        } else {
                                            $response .= '<div class="quantityBtns" data-max="' . $dataItem->quantity_max . '" data-min="' . $dataItem->quantity_min . '">
                                                <a href="javascript:" data-btn="less">-</a>
                                                <a href="javascript:" data-btn="more">+</a>
                                                </div>';
                                            $valMin = 1;
                                            if ($dataItem->quantity_min > 0) {
                                                $valMin = $dataItem->quantity_min;
                                            }
                                            $response .= '<span class="palette-turquoise icon_quantity">' . $valMin . '</span>';
                                        }
                                    }
                                    $response .= '</div>';
                                    if ($dataItem->description != "") {
                                        $cssWidth = '';
                                        if ($dataItem->useRow) {
                                            $cssWidth = 'max-width: 100%;';
                                        }
                                        $response .= '<p class="itemDes" style="' . $cssWidth . '">' . $dataItem->description . '</p>';
                                    }
                                    $response .= '</div>';
                                } else if ($dataItem->type == 'datepicker') {
                                    $response .= '<div class="itemBloc ' . $colClass . '">';
                                    $response .= '<div class="form-group">';
                                    $response .= '<label>' . $dataItem->title . '</label>
                                                <input type="text" data-itemid="' . $dataItem->id . '"  ' . $showInSummary . '  ' . $hideQtSummary . '   ' . $useShowConditions . ' ' . $showConditions . ' ' . $showConditionsOperator . '  class="form-control lfb_datepicker" ' . $itemRequired . ' data-title="' . $dataItem->title . '" data-originaltitle="' . $originalTitle . '"  ' . $urlTag . '  />
                                              ';

                                    if ($dataItem->description != "") {
                                        $response .= '<p class="itemDes" style="margin: 0 auto; max-width: 90%;">' . $dataItem->description . '</p>';
                                    }
                                    $response .= '</div>';
                                    $response .= '</div>';
                                } else if ($dataItem->type == 'filefield_') {
                                    $response .= '<div class="itemBloc ' . $colClass . '">';
                                    if ($dataItem->fileSize == 0) {
                                        $dataItem->fileSize = 25;
                                    }
                                    $response .= '<div class="form-group">
                                              <label>' . $dataItem->title . '</label>
                                              <input type="file" ' . $itemRequired . ' data-filesize="' . $dataItem->fileSize . '"  ' . $showInSummary . '  ' . $hideQtSummary . '   ' . $useShowConditions . ' ' . $showConditions . ' ' . $showConditionsOperator . ' class="lfb_filefield"  name="file_' . $dataItem->id . '" data-itemid="' . $dataItem->id . '" data-title="' . $dataItem->title . '" data-originaltitle="' . $originalTitle . '" ' . $urlTag . '  />
                                              </div>
                                              ';
                                    if ($dataItem->description != "") {
                                        $response .= '<p class="itemDes" style="margin: 0 auto; max-width: 90%;">' . $dataItem->description . '</p>';
                                    }
                                    $response .= '</div>';
                                } else if ($dataItem->type == 'filefield') {
                                    $response .= '<div class="itemBloc ' . $colClass . '" style="margin-top: 18px;">';
                                    $response .= '<label>' . $dataItem->title . '</label>';
                                    $response .= '<div class="lfb_dropzone dropzone" data-filesize="' . $dataItem->fileSize . '" ' . $itemRequired . '  ' . $hideQtSummary . '   ' . $useShowConditions . ' ' . $showConditions . ' ' . $showConditionsOperator . ' ' . $showInSummary . ' data-allowedfiles="' . $dataItem->allowedFiles . '" data-maxfiles="' . $dataItem->maxFiles . '" id="lfb_dropzone_' . $dataItem->id . '" data-itemid="' . $dataItem->id . '" data-title="' . $dataItem->title . '" data-originaltitle="' . $originalTitle . '" ></div>';
                                    $response .= '</div>';
                                } else if ($dataItem->type == 'qtfield') {
                                    $response .= '<div class="itemBloc ' . $colClass . '">';
                                    $response .= '<div class="form-group">';
                                    $response .= '<label>' . $dataItem->title . '</label>';
                                    $qtMax = '';
                                    if ($qtMax > 0) {
                                        $qtMax = 'max="' . $dataItem->quantity_max . '"';
                                    }
                                    $response .= ' <input  ' . $urlTag . '  ' . $showInSummary . '  ' . $hideQtSummary . '   ' . $useShowConditions . ' ' . $showConditions . '  ' . $isSinglePrice . '  class="wpe_qtfield form-control" min="0" ' . $qtMax . ' ' . $dataShowPrice . ' type="number" value="0" data-reduc="' . $dataItem->reduc_enabled . '" data-price="' . $dataItem->price . '" data-reducqt="' . $dataItem->reducsQt . '" data-operation="' . $dataItem->operation . '" data-itemid="' . $dataItem->id . '" class="form-control" data-title="' . $dataItem->title . '" /> ';

                                    if ($dataItem->description != "") {
                                        $response .= '<p class="itemDes" style="margin: 0 auto; max-width: 90%;">' . $dataItem->description . '</p>';
                                    }
                                    $response .= '</div>';
                                    $response .= '</div>';
                                } else if ($dataItem->type == 'textarea') {
                                    $response .= '<div class="itemBloc ' . $colClass . '">';
                                    $response .= '<div class="form-group">';
                                    $response .= '<label>' . $dataItem->title . '</label>
                                              <textarea data-itemid="' . $dataItem->id . '"  ' . $useShowConditions . '  ' . $hideQtSummary . '  ' . $showConditions . ' ' . $showConditionsOperator . ' ' . $showInSummary . ' ' . $urlTag . ' class="form-control" ' . $itemRequired . ' data-title="' . $dataItem->title . '" data-originaltitle="' . $originalTitle . '">' . $dataItem->defaultValue . '</textarea>';
                                    if ($dataItem->description != "") {
                                        $response .= '<p class="itemDes" style="margin: 0 auto; max-width: 90%;">' . $dataItem->description . '</p>';
                                    }
                                    $response .= '</div>';
                                    $response .= '</div>';
                                } else if ($dataItem->type == 'select') {
                                    $response .= '<div class="itemBloc ' . $colClass . '">';
                                    $dropClass = "lfb_selectpicker";
                                    if ($form->disableDropdowns) {
                                        $dropClass = "";
                                    }
                                    $firstVDisabled = '';
                                    if ($dataItem->firstValueDisabled) {
                                        $firstVDisabled = 'data-firstvaluedisabled="true"';
                                    }
                                    $response .= '
                                              <label>' . $dataItem->title . '</label>
                                              <div class="form-group">
                                              <select class="form-control ' . $dropClass . ' " ' . $itemRequired . ' ' . $firstVDisabled . ' ' . $useShowConditions . '  ' . $hideQtSummary . '  ' . $showConditions . ' ' . $showConditionsOperator . ' ' . $showInSummary . ' ' . $isSinglePrice . '  data-operation="' . $dataItem->operation . '"  data-originaltitle="' . $originalTitle . '"  ' . $urlTag . '  data-itemid="' . $dataItem->id . '"  data-title="' . $dataItem->title . '" >';
                                    $optionsArray = explode('|', $dataItem->optionsValues);
                                    foreach ($optionsArray as $option) {
                                        if ($option != "") {
                                            $value = $option;
                                            $price = 0;
                                            if (strpos($option, ";;") > 0) {
                                                $optionArr = explode(";;", $option);
                                                $value = $optionArr[0];
                                                $price = $optionArr[1];
                                            }
                                            $response .= '<option value="' . $value . '" data-price="' . $price . '">' . $value . '</option>';
                                        }
                                    }
                                    $response .= '</select>
                                                </div>
                                              
                                              ';

                                    if ($dataItem->description != "") {
                                        $response .= '<p class="itemDes" style="margin: 0 auto; max-width: 90%;">' . $dataItem->description . '</p>';
                                    }
                                    $response .= '</div>';
                                } else if ($dataItem->type == 'richtext') {
                                    $response .= '<div class="lfb_richtext lfb_item" data-title="' . $dataItem->title . '"  ' . $useShowConditions . ' ' . $showConditions . ' ' . $showConditionsOperator . '>' . $dataItem->richtext . '</div>';
                                } else if ($dataItem->type == 'checkbox') {
                                    $activatePaypal = '';
                                    if ($dataItem->usePaypalIfChecked) {
                                        $activatePaypal = 'data-activatepaypal="true"';
                                    }
                                    $group = '';
                                    if ($dataItem->groupitems != "") {
                                        $group = 'data-group="' . $dataItem->groupitems . '"';
                                    }
                                    $response .= '<div class="itemBloc ' . $colClass . '">';
                                    $response .= '<p>
                                                <label>' . $dataItem->title . '</label>
                                                <br/>
                                                <input type="checkbox"  ' . $hideQtSummary . '  ' . $group . ' ' . $useCalculation . ' ' . $activatePaypal . ' ' . $calculation . '  ' . $useShowConditions . ' ' . $showConditions . ' ' . $showConditionsOperator . ' ' . $showInSummary . ' ' . $isSinglePrice . '  class="' . $checked . '" ' . $urlTag . ' ' . $dataShowPrice . ' data-operation="' . $dataItem->operation . '" data-originaltitle="' . $originalTitle . '" data-itemid="' . $dataItem->id . '" data-prodid="' . $prodID . '"  ' . $itemRequired . ' data-toggle="switch" ' . $checkedCb . ' data-price="' . $dataItem->price . '" data-title="' . $dataItem->title . '" />
                                                </p>';

                                    if ($dataItem->description != "") {
                                        $response .= '<p class="itemDes" style="margin: 0 auto; max-width: 90%;">' . $dataItem->description . '</p>';
                                    }
                                    $response .= '</div>';
                                } else if ($dataItem->type == 'colorpicker') {
                                    $response .= '<div class="itemBloc ' . $colClass . '">';
                                    $response .= '<div style="background-color: ' . $settings->colorA . ';"  ' . $useShowConditions . '  ' . $hideQtSummary . '  ' . $showConditions . ' ' . $showConditionsOperator . ' class="lfb_colorPreview checked" data-itemid="' . $dataItem->id . '"  ' . $urlTag . ' ' . $showInSummary . ' data-toggle="tooltip"  ' . $itemRequired . ' data-placement="bottom" data-title="' . $dataItem->title . '" data-originaltitle="' . $originalTitle . '" ></div>'
                                            . '<input type="text" value="' . $settings->colorA . '" class="lfb_colorpicker" />
                                                ';

                                    if ($dataItem->description != "") {
                                        $response .= '<p class="itemDes" style="margin: 0 auto; max-width: 90%;">' . $dataItem->description . '</p>';
                                    }
                                    $response .= '</div>';
                                } else if ($dataItem->type == 'numberfield') {
                                    $response .= '<div class="itemBloc ' . $colClass . '">';
                                    $response .= '<div class="form-group">';
                                    $minLength = '';
                                    $maxLength = '';
                                    if ($dataItem->minSize > 0) {
                                        $minLength = 'min="' . $dataItem->minSize . '"';
                                    }
                                    if ($dataItem->maxSize > 0) {
                                        $maxLength = 'max="' . $dataItem->maxSize . '"';
                                    }
                                    $response .= '<label>' . $dataItem->title . '</label>
                                                <input type="number" ' . $useShowConditions . ' ' . $showConditions . '  ' . $hideQtSummary . '  ' . $showConditionsOperator . ' data-itemid="' . $dataItem->id . '" ' . $minLength . ' ' . $maxLength . ' ' . $showInSummary . ' ' . $urlTag . ' ' . $defaultValue . ' class="form-control" ' . $itemRequired . ' data-title="' . $dataItem->title . '" data-originaltitle="' . $originalTitle . '" />
                                                ';

                                    if ($dataItem->description != "") {
                                        $response .= '<p class="itemDes" style="margin: 0 auto; max-width: 90%;">' . $dataItem->description . '</p>';
                                    }
                                    $response .= '</div>';
                                    $response .= '</div>';
                                } else if ($dataItem->type == 'slider') {
                                    $dataShowPrice = '';
                                    if ($dataItem->showPrice) {
                                        $dataShowPrice = 'data-showprice="1"';
                                    }
                                    $response .= '<div class="itemBloc ' . $colClass . '">';
                                    $minLength = 'data-min="0"';
                                    $maxLength = 'data-max="30"';
                                    if ($dataItem->maxSize < $dataItem->minSize) {
                                        $dataItem->minSize = $dataItem->maxSize;
                                    }
                                    if ($dataItem->minSize > 0) {
                                        $minLength = 'data-min="' . $dataItem->minSize . '"';
                                    }
                                    if ($dataItem->maxSize > 0) {
                                        $maxLength = 'data-max="' . $dataItem->maxSize . '"';
                                    }
                                    $response .= '<label>' . $dataItem->title . '</label>
                                                <div data-type="slider"  ' . $distanceQt . '  ' . $dataShowPrice . '  ' . $hideQtSummary . '  ' . $isSinglePrice . '  data-reducqt="' . $dataItem->reducsQt . '" data-operation="' . $dataItem->operation . '" data-reduc="' . $dataItem->reduc_enabled . '" data-price="' . $dataItem->price . '"  ' . $useCalculation . ' ' . $calculation . '  ' . $useShowConditions . ' ' . $showConditions . ' ' . $showConditionsOperator . ' data-itemid="' . $dataItem->id . '" ' . $minLength . ' ' . $maxLength . ' ' . $showInSummary . ' class="" data-title="' . $dataItem->title . '" data-originaltitle="' . $originalTitle . '" data-prodid="' . $prodID . '"  data-woovar="' . $wooVar . '"></div>
                                                ';

                                    if ($dataItem->description != "") {
                                        $response .= '<p class="itemDes" style="margin: 0 auto; max-width: 90%;">' . $dataItem->description . '</p>';
                                    }
                                    $response .= '</div>';
                                } else {
                                    $response .= '<div class="itemBloc ' . $colClass . '">';
                                    $response .= '<div class="form-group">';
                                    $minLength = '';
                                    $maxLength = '';
                                    $autocomp = '';
                                    if ($dataItem->minSize > 0) {
                                        $minLength = 'minlength="' . $dataItem->minSize . '"';
                                    }
                                    if ($dataItem->maxSize > 0) {
                                        $maxLength = 'maxlength="' . $dataItem->maxSize . '"';
                                    }
                                    if ($dataItem->fieldType == 'email') {
                                        $autocomp = 'autocomplete="on" name="email" ';
                                    }
                                    $response .= '<label>' . $dataItem->title . '</label>
                                                <input type="text" data-fieldtype="' . $dataItem->fieldType . '" ' . $defaultValue . '  ' . $hideQtSummary . '  ' . $autocomp . ' ' . $useShowConditions . ' ' . $showConditions . ' ' . $showConditionsOperator . ' data-itemid="' . $dataItem->id . '" ' . $minLength . ' ' . $maxLength . ' ' . $showInSummary . ' ' . $urlTag . ' class="form-control" ' . $itemRequired . ' data-title="' . $dataItem->title . '" data-originaltitle="' . $originalTitle . '" />
                                                ';

                                    if ($dataItem->description != "") {
                                        $response .= '<p class="itemDes" style="margin: 0 auto; max-width: 90%;">' . $dataItem->description . '</p>';
                                    }
                                    $response .= '</div>';
                                    $response .= '</div>';
                                }
                            }
                        }
                    }

                    $response .= ' </div>';
                    $response .= ' </div>';
                    if ($form->showTotalBottom) {
                        $response .= '<div class="lfb_totalBottomContainer ' . $priceSubsClass . '"><hr/><h3 class="lfb_totalBottom"> 
                            <span>0$</span>' . $priceSubBottom . '</h3></div>';
                    }
                    $response .= '<div class="errorMsg alert alert-danger">' . $form->errorMessage . '</div>';
                    $response .= '<p style="margin-top: 22px; position: absolute; width: 100%;" class="text-center lfb_btnNextContainer">';
                    $hideNtxStepBtn = '';
                    if ($dataSlide->hideNextStepBtn) {
                        $hideNtxStepBtn = 'lfb-hidden lfb-btnNext-hidden';
                    }
                    $response .= '<a href="javascript:" id="lfb_btnNext_' . $dataSlide->id . '" class="btn btn-wide btn-primary btn-next ' . $hideNtxStepBtn . '">' . $form->btn_step . '</a>';

                    if ($dataContent->start == 0) {
                        $response .= '<br/><a href="javascript:"  class="linkPrevious">' . $form->previous_step . '</a>';
                    }
                    $response .= '</p>';

                    $response .= '</div>';
                    $i++;
                }
            }

            $response .= '<div class="genSlide" id="finalSlide" data-stepid="final">
                <h2 class="stepTitle">' . $form->last_title . '</h2>
                <div class="genContent">
                    <div class="genContentSlide active">
                        <p>' . $form->last_text . '</p>';
            $dispFinalPrice = '';
            if ($form->hideFinalPrice == 1) {
                $dispFinalPrice = "display:none;";
            }
            $subTxt = '';
            if ($form->isSubscription == 1) {
                $subTxt = '<span>' . $form->subscription_text . '</span>';
            }
            $response .= '<h3 id="finalPrice" style="' . $dispFinalPrice . '"><span></span>' . $subTxt . '</h3>';

            $response .= '<div id="lfb_subTxtValue" style="display: none;">' . $priceSubs . '</div>';


            foreach ($fields as $field) {
                $response .= '<div class="form-group">';
                $placeholder = "";
                $disp = '';
                $dispLabel = 'block';
                if ($field->visibility == 'toggle') {
                    $disp = 'toggle';
                    $placeholder = "";
                } else {
                    $dispLabel = 'none';
                    $placeholder = $field->label;
                    if ($field->validation == 'fill') {
                        $req = "true";
                    }
                }
                $response .= '<label for="field_' . $field->id . '" style="display: ' . $dispLabel . '">' . $field->label . '</label>';
                if ($field->visibility == 'toggle') {
                    $response .= '<input id="field_' . $field->id . '_cb" type="checkbox" data-toggle="switch" data-fieldid="' . $field->id . '" /><br/>';
                }
                $req = "false";
                $autocomp = '';
                $emailField = '';
                if ($field->validation == 'email') {
                    $emailField = 'emailField';
                    $autocomp = 'autocomplete="on" name="email" ';
                }
                if ($field->validation == 'fill') {
                    $req = 'true';
                }

                if ($field->typefield == 'textarea') {
                    $response .= '<textarea id="field_' . $field->id . '" data-fieldtype="' . $field->fieldType . '"  data-required="' . $req . '"  class="form-control ' . $disp . ' ' . $emailField . '" placeholder="' . $placeholder . '"></textarea>';
                } else {
                    $response .= '<input type="text" id="field_' . $field->id . '" ' . $autocomp . ' data-fieldtype="' . $field->fieldType . '"  data-required="' . $req . '" placeholder="' . $placeholder . '" class="form-control ' . $emailField . ' ' . $disp . '"/>';
                }
                $response .= '</div>';
            }

            $response .= '<p style="margin-bottom: 28px;">';


            if ($form->useCoupons) {
                $response .= '<div id="lfb_couponContainer" class="form-group">'
                        . '<input type="text" placeholder="' . $form->couponText . '" id="lfb_couponField" class="form-control"/>'
                        . '<a href="javascript:" id="lfb_couponBtn" onclick="lfb_applyCouponCode(' . $form->id . ');" class="btn btn-primary"><span class="glyphicon glyphicon-check"></span></a>'
                        . '</div>';
            }

            $cssSum = '';
            $cssQtCol = '';
            if (!$form->useSummary) {
                $cssSum = 'lfb-hidden';
            }
            if ($form->summary_hideQt) {
                $cssQtCol = 'lfb-hidden';
            }
            $subTxt = '';
            if ($form->isSubscription == 1) {
                $subTxt = '<span class="lfb_subTxt">' . $form->subscription_text . '</span>';
            }
            $priceHiddenClass = '';
            if ($form->summary_hidePrices == 1) {
                $priceHiddenClass = 'lfb-hidden lfb_hidePrice';
            }
            $totalHiddenClass = '';
            if ($form->summary_hideTotal == 1) {
                $totalHiddenClass = 'lfb-hidden lfb_hidePrice';
            }
            $response .= '
                   <div id="lfb_summary" class="table-responsive ' . $cssSum . '">
                        <h4>' . $form->summary_title . '</h4>
                        <table class="table table-bordered">
                            <thead>
                                <th>' . $form->summary_description . '</th>
                                <th>' . $form->summary_value . '</th>
                                <th class="' . $cssQtCol . '">' . $form->summary_quantity . '</th>
                                <th class="' . $priceHiddenClass . '">' . $form->summary_price . '</th>
                            </thead>
                            <tbody>    
                                <tr id="lfb_summaryDiscountTr" class="lfb_static ' . $priceHiddenClass . '"><th colspan="3">' . $form->summary_discount . '</th><th id="lfb_summaryDiscount"><span></span></th></tr>                                  
                                <tr id="sfb_summaryTotalTr" class="lfb_static ' . $totalHiddenClass . '"><th colspan="3">' . $form->summary_total . '</th><th id="lfb_summaryTotal"><span></span>' . $subTxt . '</th></tr>                                  
                            </tbody>
                        </table>
                    </div>';


            if ($form->legalNoticeEnable) {
                $response .= '
                    <div id="lfb_legalNoticeContent">' . nl2br($form->legalNoticeContent) . '</div>
                    <div class="form-group" style=" margin-top: 14px;">
                      <label for="lfb_legalCheckbox">' . $form->legalNoticeTitle . '</label>
                      <input type="checkbox" data-toggle="switch" id="lfb_legalCheckbox" class="form-control"/>
                    </div>';
            }

            if ($form->use_stripe) {

                $response .= '<form id="lfb_stripeForm" action="" data-title="' . $form->title . '" method="post">';

                $response .= '
                    <div class="form-group">
                    <label>
                      <span>' . $form->stripe_label_creditCard . '</span>
                    </label><br/>
                      <input type="text" size="20" data-stripe="number" class="form-control">
                  </div>
                  <div class="form-group">
                    <label>
                      <span>' . $form->stripe_label_expiration . ' (MM/YY)</span>
                    </label><br/>
                    <input type="text" size="2" data-stripe="exp_month" class="form-control" style="display: inline-block;margin-right: 8px; width: 60px;">
                    <span style="font-size: 24px;"> / </span>
                    <input type="text" size="2" data-stripe="exp_year" class="form-control" style="display: inline-block;margin-left: 8px; width: 60px;">
                  </div>

                  <div class="form-group">
                    <label>
                      <span>' . $form->stripe_label_cvc . '</span>
                    </label><br/>
                      <input type="text" size="4" data-stripe="cvc"  class="form-control" style="width: 110px;">
                  </div>

                  <span class="payment-errors" style="color:red; font-size: 20px;padding-top: 28px;"></span>';
                   if($form->useCaptcha){
                 $response .= '<div id="lfb_captcha-wrap">
                    <div id="lfb_captchaPanel" class="form-group">
                        <p>'.$form->captchaLabel.'</p>
                        <img src="includes/captcha/get_captcha.php" alt="Captcha" id="lfb_captcha" />                            
                        <a href="javascript:" id="lfb_captcha_refresh" onclick="lfb_changeCaptcha('.$form->id.');"><span class="glyphicon glyphicon-refresh"></span></a><br/>
                        <input type="text" class="form-control" data-required="true" id="lfb_captchaField" />
                    </div>
                </div>';
                }
                  $response .='<p style="margin-top: 38px; margin-bottom: -28px;"><input type="submit" value="' . $form->last_btn . '"  id="wpe_btnOrderStripe"  class="btn btn-wide btn-primary"></p>';

                $response .= '</form>';
            } else if ($form->use_paypal) {
                $useIPN = '';
                if ($form->paypal_useIpn == 1) {
                    $useIPN = 'data-useipn="1"';
                }
                if ($form->paypal_useSandbox == 1) {
                    $response .= '<form id="wtmt_paypalForm" action="https://www.sandbox.paypal.com/cgi-bin/webscr" ' . $useIPN . ' method="post">';
                } else {
                    $response .= '<form id="wtmt_paypalForm" action="https://www.paypal.com/cgi-bin/webscr" ' . $useIPN . ' method="post">';
                }
            if($form->useCaptcha){
                 $response .= '<div id="lfb_captcha-wrap">
                    <div id="lfb_captchaPanel" class="form-group">
                        <p>'.$form->captchaLabel.'</p>
                        <img src="includes/captcha/get_captcha.php" alt="Captcha" id="lfb_captcha" />                            
                        <a href="javascript:" id="lfb_captcha_refresh" onclick="lfb_changeCaptcha('.$form->id.');"><span class="glyphicon glyphicon-refresh"></span></a><br/>
                        <input type="text" class="form-control" data-required="true" id="lfb_captchaField" />
                    </div>
                </div>';
                }
                $response .= '<a href="javascript:" id="btnOrderPaypal" class="btn btn-wide btn-primary">' . $form->last_btn . '</a>
                            <input type="submit" style="display: none;" name="submit"/>
                            <img alt="" border="0" src="https://www.paypal.com/en_US/i/scr/pixel.gif" width="1" height="1">
                            <input type="hidden" name="add" value="1">';
                if ($form->isSubscription == 1) {
                    $response .= '<input type="hidden" name="cmd" value="_xclick-subscriptions">
                            <input type="hidden" name="no_note" value="1">
                            <input type="hidden" name="src" value="1">
                            <input type="hidden" name="a3" value="15.00">
                            <input type="hidden" name="p3" value="' . $form->paypal_subsFrequency . '">
                            <input type="hidden" name="t3" value="' . $form->paypal_subsFrequencyType . '">
                            <input type="hidden" name="bn" value="PP-SubscriptionsBF:btn_subscribeCC_LG.gif:NonHostedGuest">';
                } else {
                    $response .= '<input type="hidden" name="cmd" value="_xclick">
                            <input type="hidden" name="amount" value="1">';
                }
                $lang = '';
                if ($form->paypal_languagePayment != "") {
                    $lang = '<input type="hidden" name="lc" value="' . $form->paypal_languagePayment . '"><input type="hidden" name="country" value="' . $form->paypal_languagePayment . '">';
                }
                $response .= '<input type="hidden" name="business" value="' . $form->paypal_email . '">
                            <input type="hidden" name="business_cs_email" value="' . $form->paypal_email . '">
                            <input type="hidden" name="item_name" value="' . $form->title . '">
                            <input type="hidden" name="item_number" value="A00001">
                            <input type="hidden" name="charset" value="utf-8">
                            <input type="hidden" name="no_shipping" value="1">
                            <input type="hidden" name="cn" value="Message">
                            <input type="hidden" name="custom" value="Form content">
                            <input type="hidden" name="currency_code" value="' . $form->paypal_currency . '">
                            <input type="hidden" name="return" value="' . $form->close_url . '">
                                ' . $lang . '
                        </form>';
            } else {
                 if($form->useCaptcha){
                 $response .= '<div id="lfb_captcha-wrap">
                    <div id="lfb_captchaPanel" class="form-group">
                        <p>'.$form->captchaLabel.'</p>
                        <img src="includes/captcha/get_captcha.php" alt="Captcha" id="lfb_captcha" />                            
                        <a href="javascript:" id="lfb_captcha_refresh" onclick="lfb_changeCaptcha('.$form->id.');"><span class="glyphicon glyphicon-refresh"></span></a><br/>
                        <input type="text" class="form-control" data-required="true" id="lfb_captchaField" />
                    </div>
                </div>';
                }
                $response .= ' <a href="javascript:" id="wpe_btnOrder" class="btn btn-wide btn-primary">' . $form->last_btn . '</a>';
            }
            if (count($steps) > 0) {
                $response .= '<div><a href="javascript:" class="linkPrevious">' . $form->previous_step . '</a></div>';
            }
            $response .= '</p>';
            //  }
            //  }
            $response .= '</div>';
            $response .= '</div>';
            $response .= '</div>';
            $response .= '</div>';
            $response .= '</div>';


            $response .= '</div>';
            $response .= '</div>';
            $response .= '</div>';
            /* end */


            echo $response;
            ?>
        </body>
    </html>
            <?php
        }
        mysqli_close($GLOBALS['lfb_connection']);
        ?>