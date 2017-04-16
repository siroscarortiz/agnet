<?php
require_once '../config.php';
if (!class_exists("Mailchimp", false)) {
          require_once('Mailchimp.php');    
}
 require_once('GetResponseAPI.class.php');   
     
$GLOBALS['lfb_connection'] = mysqli_connect($sql_server, $sql_user_name, $sql_password, $sql_database_name);
$GLOBALS['lfb_connection']->sqlPrefix = $sql_prefix;


function efp_initTranslations(){
    $translationFile = file_get_contents( '../languages/Estimation_Form.lang');
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
    
    $translationFile = file_get_contents( '../languages/tdgn/FormDesigner.lang');
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

function lfb_init() {
    $lfb_assetsUrl = 'assets/';
    $lfb_assetsDir = esc_url(trailingslashit(realpath(dirname(__FILE__) . '/assets/')));
    $lfb_cssUrl = 'export/';
    $lfb_uploadsDir = esc_url(trailingslashit(realpath(dirname(__FILE__) . '/uploads/')));
    $lfb_uploadsUrl = 'uploads/';
    chmod($lfb_uploadsDir, 0745);
}
if (isset($_POST['action'])) {
         $isAjax = isset($_SERVER['HTTP_X_REQUESTED_WITH']) AND strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest';
        if($isAjax){
    switch ($_POST['action']) { 
        
         case 'lfb_unlog':
            lfb_unlog();
            break;
        case 'lfb_passLost':
            lfb_passLost();
            break;
        case 'tld_exportCSS':
            tld_exportCSS();
            break;
        case 'tld_resetCSS':
            tld_resetCSS();
            break;
        case 'tld_saveCSS':
            tld_saveCSS();
            break;
        case 'tld_getCSS':
            tld_getCSS();
            break;
        case 'tld_saveEditedCSS':
            tld_saveEditedCSS();
            break;
        case 'lfb_saveStep':
            saveStep();
            break;
        case 'lfb_addStep':
            addStep();
            break;
        case 'lfb_loadStep':
            loadStep();
            break;
        case 'lfb_duplicateStep':
            duplicateStep();
            break;
        case 'lfb_removeStep':
            removeStep();
            break;
        case 'lfb_saveStepPosition':
            saveStepPosition();
            break;
        case 'lfb_newLink':
            newLink();
            break;
        case 'lfb_changePreviewHeight':
            changePreviewHeight();
            break;
        case 'lfb_saveLinks':
            saveLinks();
            break;
        case 'lfb_saveSettings':
            saveSettings();
            break;
        case 'lfb_loadSettings':
            loadSettings();
            break;
        case 'lfb_removeAllSteps':
            removeAllSteps();
            break;
        case 'lfb_addForm':
            addForm();
            break;
        case 'lfb_loadForm':
            loadForm();
            break;
        case 'lfb_saveForm':
            saveForm();
            break;
        case 'lfb_removeForm':
            removeForm();
            break;
        case 'lfb_addonTdgn':
            addonTdgn();
            break;
        case 'lfb_loadFields':
            loadFields();
            break;
        case 'lfb_removeRedirection':
            removeRedirection();
            break;
        case 'lfb_saveRedirection':
            saveRedirection();
            break;
        case 'lfb_saveField':
            saveField();
            break;
        case 'lfb_saveItem':
            saveItem();
            break;
        case 'lfb_removeItem':
            removeItem();
            break;
        case 'lfb_exportForms':
            exportForms();
            break;
        case 'lfb_importForms':
            importForms();
            break;
        case 'lfb_checkLicense':
            checkLicense();
            break;
        case 'lfb_duplicateForm':
            duplicateForm();
            break;
        case 'lfb_duplicateItem':
            duplicateItem();
            break;
        case 'lfb_removeField':
            removeField();
            break;
        case 'lfb_loadLogs':
            loadLogs();
            break;
        case 'lfb_removeLog':
            removeLog();
            break;
        case 'lfb_loadLog':
            loadLog();
            break;
        case 'lfb_removeCoupon':
            removeCoupon();
            break;
        case 'lfb_removeAllCoupons':
            removeAllCoupons();
            break;
        case 'lfb_saveCoupon':
            saveCoupon();
            break;
        case 'lfb_getMailchimpLists':
            getMailchimpLists();
            break;
        case 'lfb_getMailpoetLists':
            getMailpoetLists();
            break;
        case 'lfb_getGetResponseLists':
            getGetResponseLists();
            break;
        case 'lfb_exportLogs':
            exportLogs();
            break;
        case 'lfb_changeItemsOrders':
            changeItemsOrders();
            break;
        case 'lfb_changeLastFieldsOrders':
            changeLastFieldsOrders();
            break;
        case 'lfb_loadCharts':
            loadCharts();
            break;
        case 'lfb_importPic':
            importPic();
            break;
    }
    }
}

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

function sql_get_results($query) {
    $chkClose = false;

    $rep = array();
    $sql = mysqli_query($GLOBALS['lfb_connection'], $query);

    while ($data = mysqli_fetch_object($sql)) {
        $rep[] = $data;
    }
    return $rep;
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
        $whereString = substr($whereString, 1);
        $sql = mysqli_query($GLOBALS['lfb_connection'], 'DELETE FROM ' . $table . ' WHERE ' . $whereString);
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

function trailingslashit($string) {
    $string = rtrim($string, '/\\');
    return $string . '/';
}

function esc_url($url) {
    return filter_var($url, FILTER_SANITIZE_URL);
}

function getMailchimpLists() {
    $apiKey = sanitize_text_field($_POST['apiKey']);
    if ($apiKey != "") {
        $MailChimp = new Mailchimp($apiKey);
        $result = $MailChimp->lists->getList();
        foreach ($result['data'] as $list) {
            echo '<option value="' . $list['id'] . '">' . $list['name'] . '</option>';
        }
    }
    die();
}

function getMailpoetLists() {  

    die();
}

function getGetResponseLists() {
    if (isset($_POST['apiKey']) && $_POST['apiKey'] != "") {
        $apiKey = sanitize_text_field($_POST['apiKey']);
        if ($apiKey != "") {
            $GetResponse = new GetResponse($apiKey);
            $result = $GetResponse->getCampaigns();
            foreach ($result as $list => $value) {
                echo '<option value="' . $list . '">' . $value->name . '</option>';
            }
        }
    }
}

/* Load Logs */

function loadLogs() {

    $formID = sanitize_text_field($_POST['formID']);
    $rep = "";
    $table_name = $GLOBALS['lfb_connection']->sqlPrefix . "wpefc_logs";
    $logs = sql_get_results("SELECT * FROM $table_name WHERE formID=" . $formID . " AND checked=1 ORDER BY id DESC");
    foreach ($logs as $log) {
        $formTitle = "";
        $rep .= '<tr>
                 <td>' . date('Y-m-d', strtotime($log->dateLog)) . '</td>
                <td><a href="javascript:" onclick="lfb_loadLog(' . $log->id . ');">' . $log->ref . '</a></td>
                    <td>' . $log->email . '</td>
                    <td><a href="javascript:" onclick="lfb_loadLog(' . $log->id . ');" class="btn btn-primary btn-circle" data-toggle="tooltip" title="' . __('View this order', 'lfb') . '" data-placement="bottom"><span class="glyphicon glyphicon-search"></span></a>
                    <a href="javascript:" onclick="lfb_removeLog(' . $log->id . ',' . $formID . ');" class="btn btn-danger btn-circle" data-toggle="tooltip" title="' . __('Delete this order', 'lfb') . '" data-placement="bottom"><span class="glyphicon glyphicon-trash"></span></a></td>
          </tr>';
    }
    echo $rep;
    die();
}

/* Load Log */

function loadLog() {

    $logID = sanitize_text_field($_POST['logID']);
    $rep = "";
    $table_name = $GLOBALS['lfb_connection']->sqlPrefix . "wpefc_logs";
    $log = sql_get_results("SELECT * FROM $table_name WHERE id=" . $logID);
    if (count($log) > 0) {
        $log = $log[0];
        $rep = $log->content;
    }
    echo $rep;
    die();
}

/* Remove Log */

function removeLog() {

    $logID = sanitize_text_field($_POST['logID']);
    $table_name = $GLOBALS['lfb_connection']->sqlPrefix . "wpefc_logs";
    sql_delete($table_name, array('id' => $logID));
    die();
}

function jsonRemoveUnicodeSequences($struct) {
    return json_encode($struct);
}

function loadCharts() {
    if (!isset($isLogged) || $isLogged) {

        $formID = sanitize_text_field($_POST['formID']);
        $mode = sanitize_text_field($_POST['mode']);
        $rep = '';
        $conditionChecked = '';
        $table_name = $GLOBALS['lfb_connection']->sqlPrefix . "wpefc_forms";
        $form = sql_get_results("SELECT * FROM $table_name WHERE id=" . $formID . " LIMIT 1");
        if (count($form) > 0) {
            if ($mode == 'all') {
                $table_name = $GLOBALS['lfb_connection']->sqlPrefix . "wpefc_logs";
                $logs = sql_get_results("SELECT * FROM $table_name ORDER BY dateLog ASC LIMIT 1");
                $yearMin = date('Y');
                $currentYear = date('Y');
                if (count($logs) > 0) {
                    $log = $logs[0];
                    $yearMin = substr($log->dateLog, 0, 4);
                }
                $rep.= ($yearMin - 1) . ';0;0|';
                for ($a = $yearMin; $a <= $currentYear; $a++) {
                    $table_name = $GLOBALS['lfb_connection']->sqlPrefix . "wpefc_logs";
                    $logs = sql_get_results("SELECT * FROM $table_name WHERE formID=$formID AND dateLog LIKE '" . $a . "-%' ORDER BY dateLog ASC");
                    $valuePrice = 0;
                    $valueSubs = 0;
                    foreach ($logs as $log) {
                        $valuePrice += $log->totalPrice;
                        $valueSubs += $log->totalSubscription;
                    }
                    $rep.= $a . ';' . $valuePrice . ';' . $valueSubs . '|';
                }
            } else if ($mode == 'month') {
                $yearMonth = sanitize_text_field($_POST['yearMonth']);
                $year = substr($yearMonth, 0, 4);
                $month = substr($yearMonth, 6, 2);
                $nbDays = cal_days_in_month(CAL_GREGORIAN, $month, $year);

                for ($i = 1; $i <= $nbDays; $i++) {
                    $table_name = $GLOBALS['lfb_connection']->sqlPrefix . "wpefc_logs";
                    $logs = sql_get_results("SELECT * FROM $table_name WHERE formID=$formID AND dateLog LIKE '" . $yearMonth . '-' . $i . "' ORDER BY dateLog ASC");
                    $valuePrice = 0;
                    $valueSubs = 0;
                    foreach ($logs as $log) {
                        $valuePrice += $log->totalPrice;
                        $valueSubs += $log->totalSubscription;
                    }
                    $rep.= $i . ';' . $valuePrice . ';' . $valueSubs . '|';
                }
            } else {
                $year = sanitize_text_field($_POST['year']);
                for ($i = 1; $i <= 12; $i++) {
                    $month = $i;
                    if ($month < 10) {
                        $month = '0' . $month;
                    }
                    $yearMonth = $year . '-' . $month;

                    $table_name = $GLOBALS['lfb_connection']->sqlPrefix . "wpefc_logs";
                    $logs = sql_get_results("SELECT * FROM $table_name WHERE formID=$formID AND dateLog LIKE '" . $yearMonth . "%' ORDER BY dateLog ASC");
                    $valuePrice = 0;
                    $valueSubs = 0;
                    foreach ($logs as $log) {
                        $valuePrice += $log->totalPrice;
                        $valueSubs += $log->totalSubscription;
                    }
                    $rep.= $month . ';' . $valuePrice . ';' . $valueSubs . '|';
                }
                if (strlen($rep) > 0) {
                    $rep = substr($rep, 0, -1);
                } else {
                    $rep = '0;0;0|';
                }
            }
        }
        echo $rep;
        die();
    }
}

/*
 * Plugin init localization Tld
 */

function init_tld_localization() {
    /* $settings = getSettings();
      if($settings->tdgn_enabled && strlen($settings->purchaseCode) > 8){
      $moFiles = scandir(trailingslashit($this->dir) . 'languages/tdgn/');
      foreach ($moFiles as $moFile) {
      if (strlen($moFile) > 3 && substr($moFile, -3) == '.mo' && strpos($moFile, get_locale()) > -1) {
      load_textdomain('tld', trailingslashit($this->dir) . 'languages/tdgn/' . $moFile);
      }
      }
      } */
}

function addForm() {
    if (!isset($isLogged) || $isLogged) {

        $table_name = $GLOBALS['lfb_connection']->sqlPrefix . "wpefc_forms";
        sql_insert($table_name, array('title' => 'My new Form', 'btn_step' => "NEXT STEP", 'previous_step' => "return to previous step", 'intro_title' => "HOW MUCH TO MAKE MY WEBSITE ?", 'intro_text' => "Estimate the cost of a website easily using this awesome tool.", 'intro_btn' => "GET STARTED", 'last_title' => "Final cost", 'last_text' => "The final estimated price is : ", 'last_btn' => "ORDER MY WEBSITE", 'last_msg_label' => "Do you want to write a message ? ", 'succeed_text' => "Thanks, we will contact you soon", 'initial_price' => 0, 'email' => 'your@email.com', 'email_subject' => 'New order from your website', 'currency' => '$', 'currencyPosition' => 'left', 'errorMessage' => 'You need to select an item to continue', 'intro_enabled' => 1, 'email_userSubject' => 'Order confirmation',
            'email_adminContent' => '<p style="text-align:right;">Ref: <strong>[ref]</strong></p><h2 style="color: #008080;">Information</h2><hr/><span style="color: #444444;">[information_content]</span><span style="color: #444444;"> </span><hr/><h2 style="color: #008080;">Project</h2><hr/>[project_content]',
            'email_userContent' => '<p style="text-align:right;">Ref: <strong>[ref]</strong></p><h2 style="color: #008080;">Information</h2><hr/><span style="color: #444444;">[information_content]</span><span style="color: #444444;"> </span><hr/><h2 style="color: #008080;">Project</h2><hr/>[project_content]<hr/><p><span style="font-style:italic;">Thank you for your confidence.</span></p>',
            'colorA' => '#1abc9c', 'colorB' => '#34495e', 'colorC' => '#bdc3c7',
            'colorSecondary' => '#bdc3c7', 'colorSecondaryTxt' => '#ffffff', 'colorCbCircle' => '#7f8c9a', 'colorCbCircleOn' => '#bdc3c7',
            'item_pictures_size' => 64, 'colorBg' => '#ecf0f1', 'summary_title' => 'Summary', 'summary_description' => 'Description', 'summary_quantity' => 'Quantity', 'summary_price' => 'Price', 'summary_value' => 'Information', 'summary_total' => 'Total :', 'legalNoticeTitle' => 'I certify I completely read and I accept the legal notice by validating this form',
            'legalNoticeContent' => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit. Etiam faucibus lectus ac massa dictum, rhoncus bibendum mauris volutpat. Aenean venenatis mi porta gravida dignissim. Mauris eu ipsum convallis, semper massa sed, bibendum justo. Pellentesque porta suscipit aliquet. Integer quis odio tempus nibh cursus sollicitudin. Vivamus at rutrum dui. Proin sit amet porta neque, ac hendrerit purus.',
            'decimalsSeparator' => '.', 'thousandsSeparator' => ',', 'stripe_label_creditCard' => 'Credit card number', 'stripe_label_cvc' => 'CVC',
            'stripe_label_expiration' => 'Expiration date', 'stripe_currency' => 'USD', 'stripe_subsFrequencyType' => 'month', 'customCss' => '', 'customJS' => '', 'formStyles' => '',
            'redirectionDelay' => 5, 'useRedirectionConditions' => 0, 'txtDistanceError' => 'Calculating the distance could not be performed, please verify the input addresses'));

        $formID = sql_insert_id();

        $table_name = $GLOBALS['lfb_connection']->sqlPrefix . "wpefc_fields";
        sql_insert($table_name, array('formID' => $formID, 'label' => "Enter your email", 'isRequired' => 1, 'typefield' => 'input', 'visibility' => 'display', 'validation' => 'email'));
        sql_insert($table_name, array('formID' => $formID, 'label' => "Do you want to write a message ?", 'isRequired' => 0, 'typefield' => 'textarea', 'visibility' => 'toggle'));

        echo $formID;
        die();
    }
}

function lfb_checkLicenseCall() {
     try {
        $url = 'http://www.loopus-plugins.com/updates/update.php?checkCode=10550735&code=' . sanitize_text_field($_POST['code']);
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $rep = curl_exec($ch);
        if ($rep != '0410') {
            $table_name = $GLOBALS['lfb_connection']->sqlPrefix . "wpefc_settings";
            sql_update($table_name, array('purchaseCode' => sanitize_text_field($_POST['code'])), array('id' => 1));
        } else {
            echo '1';
        }
    } catch (Throwable $t) {
        $table_name = $GLOBALS['lfb_connection']->sqlPrefix . "wpefc_settings";
        sql_update($table_name, array('purchaseCode' => sanitize_text_field($_POST['code'])), array('id' => 1));
    } catch (Exception $e) {
        $table_name = $GLOBALS['lfb_connection']->sqlPrefix . "wpefc_settings";
        sql_update($table_name, array('purchaseCode' => sanitize_text_field($_POST['code'])), array('id' => 1));
    } 
}

function addonTdgn() {
    if (!isset($isLogged) || $isLogged) {


        lfb_checkLicenseCall();
        $settings = getSettings();
        if (strlen($settings->purchaseCode) > 8 && $_POST['code'] == $settings->purchaseCode) {
            $table_name = $GLOBALS['lfb_connection']->sqlPrefix . "wpefc_settings";
            sql_update($table_name, array('tdgn_enabled' => 707), array('id' => 1));
            echo '101';
        }
    }
    die();
}

function duplicateStep() {
    if (!isset($isLogged) || $isLogged) {

        $table_name = $GLOBALS['lfb_connection']->sqlPrefix . "wpefc_steps";
        $stepID = sanitize_text_field($_POST['stepID']);
        $steps = sql_get_results("SELECT * FROM $table_name WHERE id=" . $stepID);
        $step = $steps[0];
        $step->title = $step->title . ' (1)';
        $step->start = 0;
        unset($step->id);

        $content = json_decode($step->content);
        $content->previewPosX += 40;
        $content->previewPosY += 40;
        $content->start = 0;
        $step->content = stripslashes(jsonRemoveUnicodeSequences($content));

        //sql_insert($table_name, array('content' => jsonRemoveUnicodeSequences($content), 'start' => 0,'title'=>$step->title,'itemRequired'=>$step->itemRequired ));
        sql_insert($table_name, (array) $step);
        $newID = sql_insert_id();

        $table_name = $GLOBALS['lfb_connection']->sqlPrefix . "wpefc_items";
        $items = sql_get_results("SELECT * FROM $table_name WHERE stepID=$stepID");
        foreach ($items as $item) {
            $item->stepID = $newID;
            unset($item->id);
            sql_insert($table_name, (array) $item);
        }
        die();
    }
}

function duplicateItem() {
    if (!isset($isLogged) || $isLogged) {

        $table_name = $GLOBALS['lfb_connection']->sqlPrefix . "wpefc_items";
        $itemID = sanitize_text_field($_POST['itemID']);
        $items = sql_get_results("SELECT * FROM $table_name WHERE id=" . $itemID);
        $item = $items[0];
        $item->title = $item->title . ' (1)';
        unset($item->id);
        sql_insert($table_name, (array) $item);
    }
    die();
}

function changeItemsOrders() {
    if (!isset($isLogged) || $isLogged) {

        $items = sanitize_text_field($_POST['items']);
        $items = explode(',', $items);
        $table_name = $GLOBALS['lfb_connection']->sqlPrefix . "wpefc_items";
        foreach ($items as $key => $value) {
            sql_update($table_name, array('ordersort' => $key), array('id' => $value));
        }
    }
    die();
}

function changeLastFieldsOrders() {
    if (!isset($isLogged) || $isLogged) {

        $fields = sanitize_text_field($_POST['fields']);
        $fields = explode(',', $fields);
        $table_name = $GLOBALS['lfb_connection']->sqlPrefix . "wpefc_fields";
        foreach ($fields as $key => $value) {
            sql_update($table_name, array('ordersort' => $key), array('id' => $value));
        }
    }
    die();
}

function duplicateForm() {

    if (!isset($isLogged) || $isLogged) {
        $table_name = $GLOBALS['lfb_connection']->sqlPrefix . "wpefc_forms";
        $formID = sanitize_text_field($_POST['formID']);

        $table_forms = $GLOBALS['lfb_connection']->sqlPrefix . "wpefc_forms";
        $table_steps = $GLOBALS['lfb_connection']->sqlPrefix . "wpefc_steps";
        $table_items = $GLOBALS['lfb_connection']->sqlPrefix . "wpefc_items";
        $table_fields = $GLOBALS['lfb_connection']->sqlPrefix . "wpefc_fields";
        $table_links = $GLOBALS['lfb_connection']->sqlPrefix . "wpefc_links";
        $forms = sql_get_results("SELECT * FROM $table_forms WHERE id=$formID LIMIT 1");
        $form = $forms[0];
        unset($form->id);
        $form->title = $form->title . ' (1)';
        $form->current_ref = 1;
        sql_insert($table_forms, (array) $form);
        $newFormID = sql_insert_id();
        $fields = sql_get_results("SELECT * FROM $table_fields WHERE formID=$formID");
        foreach ($fields as $field) {
            unset($field->id);
            $field->formID = $newFormID;
            sql_insert($table_fields, (array) $field);
        }
        $stepsReplacement = array();
        $itemsReplacement = array();

        $steps = sql_get_results("SELECT * FROM $table_steps WHERE formID=$formID");
        foreach ($steps as $step) {
            $step->formID = $newFormID;
            $stepID = $step->id;
            unset($step->id);

            sql_insert($table_steps, (array) $step);
            $newStepID = sql_insert_id();
            $stepsReplacement[$stepID] = $newStepID;

            $items = sql_get_results("SELECT * FROM $table_items WHERE stepID=$stepID");
            foreach ($items as $item) {
                $itemID = $item->id;
                unset($item->id);
                $item->stepID = $newStepID;
                $item->formID = $newFormID;
                sql_insert($table_items, (array) $item);
                $newItemID = sql_insert_id();

                $itemsReplacement[$itemID] = $newItemID;
            }
        }
        $stepsNew = sql_get_results("SELECT * FROM $table_steps WHERE formID=$newFormID");
        foreach ($stepsNew as $step) {
            if ($step->showConditions != "") {
                $conditions = json_decode($step->showConditions);
                foreach ($conditions as $condition) {
                    $oldStep = substr($condition->interaction, 0, strpos($condition->interaction, '_'));
                    $oldItem = substr($condition->interaction, strpos($condition->interaction, '_') + 1);
                    $condition->interaction = $stepsReplacement[$oldStep] . '_' . $itemsReplacement[$oldItem];
                }
                sql_update($table_steps, array('showConditions' => jsonRemoveUnicodeSequences($conditions)), array('id' => $step->id));
            }
        }
        $itemsNew = sql_get_results("SELECT * FROM $table_items WHERE formID=$newFormID");
        foreach ($itemsNew as $item) {
            if ($item->showConditions != "") {
                $conditions = json_decode($item->showConditions);
                foreach ($conditions as $condition) {
                    $oldStep = substr($condition->interaction, 0, strpos($condition->interaction, '_'));
                    $oldItem = substr($condition->interaction, strpos($condition->interaction, '_') + 1);
                    $condition->interaction = $stepsReplacement[$oldStep] . '_' . $itemsReplacement[$oldItem];
                }
                sql_update($table_items, array('showConditions' => jsonRemoveUnicodeSequences($conditions)), array('id' => $item->id));
            }
            if ($item->calculation != "") {
                $lastPos = 0;
                $toReplace = array();
                $replaceBy = array();
                while (($lastPos = strpos($item->calculation, 'item-', $lastPos)) !== false) {
                    $oldItem = substr($item->calculation, $lastPos + 5, (strpos($item->calculation, '_', $lastPos) - ($lastPos + 5)));
                    $toReplace[] = $oldItem;
                    $replaceBy[] = $itemsReplacement[$oldItem];
                    $lastPos = $lastPos + 5;
                }

                $i = 0;
                $newCalculation = $item->calculation;
                $currentIndex = 0;
                foreach ($replaceBy as $value) {
                    $newCalculation = str_replace($toReplace[$i], $replaceBy[$i], $newCalculation);
                    $i++;
                }
                sql_update($table_items, array('calculation' => $newCalculation), array('id' => $item->id));
            }
        }

        $links = sql_get_results("SELECT * FROM $table_links WHERE formID=$formID");
        foreach ($links as $link) {
            unset($link->id);
            $link->originID = $stepsReplacement[$link->originID];
            $link->destinationID = $stepsReplacement[$link->destinationID];
            $link->formID = $newFormID;

            $conditions = json_decode($link->conditions);
            foreach ($conditions as $condition) {
                $oldStep = substr($condition->interaction, 0, strpos($condition->interaction, '_'));
                $oldItem = substr($condition->interaction, strpos($condition->interaction, '_') + 1);
                $condition->interaction = $stepsReplacement[$oldStep] . '_' . $itemsReplacement[$oldItem];
            }
            sql_insert($table_links, array('operator' => $link->operator, 'conditions' => jsonRemoveUnicodeSequences($conditions), 'originID' => $link->originID, 'destinationID' => $link->destinationID, 'formID' => $newFormID));
        }
    }

    die();
}

function tld_exportCSS() {

    $settings = getSettings();
    $styles = json_decode(stripslashes($_POST['styles']));
    $formID = (stripslashes($_POST['formID']));
    $gfonts = (stripslashes($_POST['gfonts']));
    $gfonts = explode(',', $gfonts);
    $filename = 'export_css_' . $formID . '.css';
    $existingContent = "";
    if (file_exists( '../export/' . $filename)) {
        $existingContent = file_get_contents( '../export/' . $filename);
    }
    $css = tdgn_generateCSS($styles, $formID, $gfonts, $existingContent);
    $file = file_put_contents( '../export/' . $filename, $css . PHP_EOL);
    chmod( '../export/' . $filename, 0745);

    echo $filename . '?tmp=' . rand(0, 1000) . date('Hmis');
    die();
}

function tld_resetCSS() {

    $settings = getSettings();
    $styles = json_decode(stripslashes($_POST['styles']));
    $formID = (stripslashes($_POST['formID']));
    $filename = 'formStyles_' . $formID . '.css';
    $file = file_put_contents( '../export/' . $filename, "");
    die();
}

function tld_saveCSS() {

    $settings = getSettings();
    $styles = (json_decode(stripslashes($_POST['styles'])));
    $formID = sanitize_text_field($_POST['formID']);
    $gfonts = (stripslashes($_POST['gfonts']));
    $gfonts = explode(',', $gfonts);
    $filename = 'formStyles_' . $formID . '.css';
    $existingContent = "";
    if (file_exists('../export/' . $filename)) {
        $existingContent = file_get_contents('../export/' . $filename);
    }
    $css = tdgn_generateCSS($styles, $formID, $gfonts, $existingContent);
    $file = file_put_contents('../export/' . $filename, $css . "\n");
    chmod('../export/' . $filename, 0745);
    $table_name = $GLOBALS['lfb_connection']->sqlPrefix . "wpefc_forms";
    sql_update($table_name, array('usedCssFile' => $filename, 'formStyles' => stripslashes(json_encode($styles, true))), array('id' => $formID));

    die();
}

function tld_getCSS() {

    $settings = getSettings();
    $formID = sanitize_text_field($_POST['formID']);
    $rep = "";
    $filename = 'formStyles_' . $formID . '.css';
    if (file_exists('../export/' . $filename)) {
        $rep = (file_get_contents('../export/' . $filename));
    }
    echo $rep;
    die();
}

function tld_saveEditedCSS() {
    $settings = getSettings();
    $formID = sanitize_text_field($_POST['formID']);
    $css = stripcslashes($_POST['css']);
    $filename = 'formStyles_' . $formID . '.css';
    file_put_contents('../export/' . $filename, $css . "\n");

    die();
}


function tdgn_generateCSS($styles, $formID, $gfonts, $existingContent) {
    $css = $existingContent;
    $endMediaQuery = '';

    foreach ($gfonts as $font) {
        if ($font != '') {
            $font = str_replace('"', '', $font);
            $css = '@import url("https://fonts.googleapis.com/css?family=' . $font . '");' . "\n" . $css;
        }
    }

    foreach ($styles as $deviceData) {
        $endMediaQuery = '';
        if ($deviceData->device == 'desktop') {
            if (count($deviceData->elements) > 0) {
                $css .= '@media (min-width:780px) {' . "\n";
                $endMediaQuery = '}';
            }
        } else if ($deviceData->device == 'desktopTablet') {
            if (count($deviceData->elements) > 0) {
                $css .= '@media (min-width:480px){' . "\n";
                $endMediaQuery = '}';
            }
        } else if ($deviceData->device == 'tablet') {
            if (count($deviceData->elements) > 0) {
                $css .= '@media (min-width:480px) and (max-width:780px) {' . "\n";
                $endMediaQuery = '}';
            }
        } else if ($deviceData->device == 'tabletPhone') {
            if (count($deviceData->elements) > 0) {
                $css .= '@media (max-width:780px) {' . "\n";
                $endMediaQuery = '}';
            }
        } else if ($deviceData->device == 'phone') {
            if (count($deviceData->elements) > 0) {
                $css .= '@media (max-width:480px) {' . "\n";
                $endMediaQuery = '}';
            }
        }
        foreach ($deviceData->elements as $elementData) {
            $css.= 'body #estimation_popup.wpe_bootstraped[data-form="' . $formID . '"] ' . $elementData->domSelector . ' {' . "\n";
            $style = str_replace(";", ";\n   ", $elementData->style);
            if (substr($style, -3) == "  ") {
                $style = substr($style, 0, -3);
            }
            $css.= "   " . $style;
            $css.= '}' . "\n";

            if ($elementData->hoverStyle != "") {
                $css.= 'body #estimation_popup.wpe_bootstraped[data-form="' . $formID . '"] ' . $elementData->domSelector . ':hover {' . "\n";
                $style = str_replace(";", ";\n   ", $elementData->hoverStyle);
                if (substr($style, -3) == "  ") {
                    $style = substr($style, 0, -3);
                }
                $css.= "   " . $style . "\n";
                $css.= '}' . "\n";
            }

            if ($elementData->focusStyle != "") {
                $css.= 'body #estimation_popup.wpe_bootstraped[data-form="' . $formID . '"] ' . $elementData->domSelector . ':focus {' . "\n";
                $style = str_replace(";", ";\n   ", $elementData->focusStyle);
                if (substr($style, -3) == "  ") {
                    $style = substr($style, 0, -3);
                }
                $css.= "   " . $style . "\n";
                $css.= '}' . "\n";
            }
        }
        $css = str_replace("   }", "}", $css);

        if ($endMediaQuery != '') {
            $css .= $endMediaQuery . "\n";
        }
    }

    return $css;
}

function saveForm() {
    if (!isset($isLogged) || $isLogged) {

        $table_name = $GLOBALS['lfb_connection']->sqlPrefix . "wpefc_forms";
        $formID = sanitize_text_field($_POST['formID']);
        $sqlDatas = array();
        foreach ($_POST as $key => $value) {
            if ($key != 'action' && $key != 'id' && $key != 'pll_ajax_backend' && $key != "undefined" && $key != "formID" && $key != "files" && $key != "client_action" && $key != "purchaseCode") {
                /*   if ($key == 'email_adminContent') {
                  $value = str_replace("../wp-content/", get_home_url() . '/wp-content/', $value);
                  $value = str_replace("../", get_home_url() . '/', $value);
                  }
                  if ($key == 'email_userContent') {
                  $value = str_replace("../wp-content/", get_home_url() . '/wp-content/', $value);
                  $value = str_replace("../", get_home_url() . '/', $value);
                  } */
                if ($key == 'percentToPay' && ($value == 0 || $value > 100)) {
                    $value = 100;
                }

                $sqlDatas[$key] = (stripslashes($value));
            }
        }
        if ($formID > 0) {
            sql_update($table_name, $sqlDatas, array('id' => $formID));
            $response = $formID;
        } else {
            if (isset($_POST['title'])) {
                sql_insert($table_name, $sqlDatas);
                $lastid = sql_insert_id();
                $response = $lastid;
            }
        }
        echo $response;
    }
    die();
}

function removeForm() {

    if (!isset($isLogged) || $isLogged) {
        $formID = sanitize_text_field($_POST['formID']);
        
        $table_name = $GLOBALS['lfb_connection']->sqlPrefix . "wpefc_items";
        $items = sql_get_results("SELECT * FROM $table_name WHERE formID=". $formID);
        foreach ($items as $item) {    
            if(file_exists('../'.$item->image)){
                unlink('../'.$item->image);
            }
        }
        
        $table_name = $GLOBALS['lfb_connection']->sqlPrefix . "wpefc_forms";
        sql_delete($table_name, array('id' => $formID));
        $table_name = $GLOBALS['lfb_connection']->sqlPrefix . "wpefc_steps";
        sql_delete($table_name, array('formID' => $formID));
        $table_name = $GLOBALS['lfb_connection']->sqlPrefix . "wpefc_fields";
        sql_delete($table_name, array('formID' => $formID));
        $table_name = $GLOBALS['lfb_connection']->sqlPrefix . "wpefc_items";
        sql_delete($table_name, array('formID' => $formID));
        $table_name = $GLOBALS['lfb_connection']->sqlPrefix . "wpefc_coupons";
        sql_delete($table_name, array('formID' => $formID));
        $table_name = $GLOBALS['lfb_connection']->sqlPrefix . "wpefc_logs";
        sql_delete($table_name, array('formID' => $formID));
   }
    //die();
}

function checkFields() {

    if (!isset($isLogged) || $isLogged) {
        $table_name = $GLOBALS['lfb_connection']->sqlPrefix . "wpefc_forms";
        $forms = sql_get_results("SELECT * FROM $table_name");
        foreach ($forms as $form) {
            $table_nameF = $GLOBALS['lfb_connection']->sqlPrefix . "wpefc_fields";
            $fields = sql_get_results("SELECT * FROM $table_nameF WHERE formID=" . $form->id);
            $table_nameI = $GLOBALS['lfb_connection']->sqlPrefix . "wpefc_items";
            $items = sql_get_results("SELECT * FROM $table_nameI WHERE formID=" . $form->id . ' AND type="textfield"');
            $chk = false;
            $chkF = false;
            foreach ($fields as $field) {
                if ($field->typefield == 'input' && $field->validation == "email") {
                    $chk = true;
                }
            }
            foreach ($items as $item) {
                if ($item->fieldType == "email") {
                    $chkF = true;
                }
            }
            if (!$chk && !$chkF && !$form->save_to_cart) {
                sql_insert($table_nameF, array('formID' => $form->id, 'validation' => "email", 'typefield' => "input", 'label' => "Email", 'isRequired' => 1));
            }
        }
    }
}

function checkLicense() {
    if (!isset($isLogged) || $isLogged) {
        checkLicenseCall();
    }
    die();
}

function checkLicenseCall() {
    if (!isset($isLogged) || $isLogged) {
        try {

            $url = 'http://www.loopus-plugins.com/updates/update.php?checkCode=10550735&code=' . sanitize_text_field($_POST['code']);
            $ch = curl_init($url);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            $rep = curl_exec($ch);
            if ($rep != '0410') {
                $table_name = $GLOBALS['lfb_connection']->sqlPrefix . "wpefc_settings";
                sql_update($table_name, array('purchaseCode' => sanitize_text_field($_POST['code'])), array('id' => 1));
            } else {
                echo '1';
            }
        } catch (Throwable $t) {
            $table_name = $GLOBALS['lfb_connection']->sqlPrefix . "wpefc_settings";
            sql_update($table_name, array('purchaseCode' => sanitize_text_field($_POST['code'])), array('id' => 1));
        } catch (Exception $e) {
            $table_name = $GLOBALS['lfb_connection']->sqlPrefix . "wpefc_settings";
            sql_update($table_name, array('purchaseCode' => sanitize_text_field($_POST['code'])), array('id' => 1));
        }
    }
}

function loadSettings() {

    //if(!isset($isLogged)||$isLogged){
    $table_name = $GLOBALS['lfb_connection']->sqlPrefix . "wpefc_settings";
    $settings = sql_get_results("SELECT * FROM $table_name WHERE id=1 LIMIT 1");
    $rep = array();
    if (count($settings) > 0) {
        $rep = $settings;
    }
    echo json_encode($rep);
    // }
    die();
}

function saveStepPosition() {

    if (!isset($isLogged) || $isLogged) {
        $stepID = sanitize_text_field($_POST['stepID']);
        $posX = sanitize_text_field($_POST['posX']);
        $posY = sanitize_text_field($_POST['posY']);
        $table_name = $GLOBALS['lfb_connection']->sqlPrefix . "wpefc_steps";
        $step = sql_get_results("SELECT * FROM $table_name WHERE id=" . $stepID . ' LIMIT 1');
        $step = $step[0];
        $content = json_decode($step->content);
        $content->previewPosX = $posX;
        $content->previewPosY = $posY;

        sql_update($table_name, array('content' => stripslashes(jsonRemoveUnicodeSequences($content))), array('id' => $stepID));
        echo '1';
    }
    die();
}

function newLink() {

    if (!isset($isLogged) || $isLogged) {
        $formID = sanitize_text_field($_POST['formID']);
        $originID = sanitize_text_field($_POST['originStepID']);
        $destinationID = sanitize_text_field($_POST['destinationStepID']);
        $table_name = $GLOBALS['lfb_connection']->sqlPrefix . "wpefc_links";
        sql_insert($table_name, array('originID' => $originID, 'destinationID' => $destinationID, 'conditions' => '[]', 'formID' => $formID));
        echo sql_insert_id();
    }
    die();
}

function loadForm() {

    if (!isset($isLogged) || $isLogged) {
        $formID = sanitize_text_field($_POST['formID']);
        $rep = new stdClass();
        $rep->steps = array();

        $table_name = $GLOBALS['lfb_connection']->sqlPrefix . "wpefc_forms";
        $forms = sql_get_results("SELECT * FROM $table_name WHERE id=" . $formID);
        $rep->form = $forms[0];
        if (!$rep->form->colorBg || $rep->form->colorBg == "") {
            $rep->form->colorBg = "#ecf0f1";
        }

        $table_name = $GLOBALS['lfb_connection']->sqlPrefix . "wpefc_settings";
        $params = sql_get_results("SELECT * FROM $table_name");
        $rep->params = $params[0];

        $table_name = $GLOBALS['lfb_connection']->sqlPrefix . "wpefc_steps";
        $steps = sql_get_results("SELECT * FROM $table_name WHERE formID=" . $formID);
        foreach ($steps as $step) {
            $table_name = $GLOBALS['lfb_connection']->sqlPrefix . "wpefc_items";
            $items = sql_get_results("SELECT * FROM $table_name WHERE stepID=" . $step->id . " ORDER BY ordersort ASC");
            $step->items = $items;
            $rep->steps[] = $step;
        }

        $table_name = $GLOBALS['lfb_connection']->sqlPrefix . "wpefc_links";
        $links = sql_get_results("SELECT * FROM $table_name WHERE formID=" . $formID);
        $rep->links = $links;

        $table_name = $GLOBALS['lfb_connection']->sqlPrefix . "wpefc_fields";
        $fields = sql_get_results("SELECT * FROM $table_name WHERE formID=" . $formID);
        $rep->fields = $fields;

        $table_name = $GLOBALS['lfb_connection']->sqlPrefix . "wpefc_coupons";
        $coupons = sql_get_results("SELECT * FROM $table_name WHERE formID=" . $formID);
        $rep->coupons = $coupons;

        $table_name = $GLOBALS['lfb_connection']->sqlPrefix . "wpefc_redirConditions";
        $redirections = sql_get_results("SELECT * FROM $table_name WHERE formID=" . $formID);
        $rep->redirections = $redirections;

        echo(jsonRemoveUnicodeSequences($rep));
    }
    die();
}

function loadFields() {

    if (!isset($isLogged) || $isLogged) {
        $formID = sanitize_text_field($_POST['formID']);
        $table_name = $GLOBALS['lfb_connection']->sqlPrefix . "wpefc_fields";
        $fields = sql_get_results("SELECT * FROM $table_name WHERE formID=" . $formID . " ORDER BY ordersort ASC");
        echo(jsonRemoveUnicodeSequences($fields));
    }
    die();
}

function removeField() {

    if (!isset($isLogged) || $isLogged) {
        $table_name = $GLOBALS['lfb_connection']->sqlPrefix . "wpefc_fields";
        $fieldID = sanitize_text_field($_POST['fieldID']);
        sql_delete($table_name, array('id' => $fieldID));
    }
    die();
}

function saveField() {

    if (!isset($isLogged) || $isLogged) {
        $table_name = $GLOBALS['lfb_connection']->sqlPrefix . "wpefc_fields";
        $fieldID = sanitize_text_field($_POST['id']);
        $formID = sanitize_text_field($_POST['formID']);
        $sqlDatas = array();
        foreach ($_POST as $key => $value) {
            if ($key != 'action' && $key != 'id' && $key != 'pll_ajax_backend') {
                $sqlDatas[$key] = sanitize_text_field(stripslashes($value));
            }
        }
        if ($fieldID > 0) {
            sql_update($table_name, $sqlDatas, array('id' => $fieldID));
            $response = $_POST['id'];
        } else {
            $sqlDatas['formID'] = $formID;
            sql_insert($table_name, $sqlDatas);
            $lastid = sql_insert_id();
            $response = $lastid;
        }
        echo $response;
    }
    die();
}

function saveRedirection() {

    if (!isset($isLogged) || $isLogged) {
        $table_redirs = $GLOBALS['lfb_connection']->sqlPrefix . "wpefc_redirConditions";
        $id = sanitize_text_field($_POST['id']);
        $formID = sanitize_text_field($_POST['formID']);
        $conditions = sanitize_text_field($_POST['conditions']);
        $url = sanitize_text_field($_POST['url']);
        $conditionsOperator = sanitize_text_field($_POST['operator']);
        $table_name = $GLOBALS['lfb_connection']->sqlPrefix . "wpefc_redirections";

        $data = array('formID' => $formID, 'conditions' => $conditions, 'conditionsOperator' => $conditionsOperator, 'url' => $url);
        if ($id > 0) {
            sql_update($table_redirs, $data, array('id' => $id));
        } else {
            sql_insert($table_redirs, $data);
            echo sql_insert_id();
        }
    }
    die();
}

function removeRedirection() {

    if (!isset($isLogged) || $isLogged) {
        $table_redirs = $GLOBALS['lfb_connection']->sqlPrefix . "wpefc_redirConditions";
        $id = sanitize_text_field($_POST['id']);
        sql_delete($table_redirs, array('id' => $id));
    }
    die();
}

function removeAllSteps() {


    if (!isset($isLogged) || $isLogged) {
        $formID = sanitize_text_field($_POST['formID']);

        $table_name = $GLOBALS['lfb_connection']->sqlPrefix . "wpefc_steps";
        $steps = sql_get_results("SELECT * FROM $table_name WHERE formID=" . $formID);
        foreach ($steps as $step) {
            $table_nameL = $GLOBALS['lfb_connection']->sqlPrefix . "wpefc_links";
            sql_delete($table_nameL, array('originID' => $step->id));
            sql_delete($table_nameL, array('destinationID' => $step->id));
        }

        sql_delete($table_name, array('formID' => $formID));
    }
    die();
}

function removeItem() {


    if (!isset($isLogged) || $isLogged) {
        $formID = sanitize_text_field($_POST['formID']);
        $stepID = sanitize_text_field($_POST['stepID']);
        $itemID = sanitize_text_field($_POST['itemID']);

        $table_name = $GLOBALS['lfb_connection']->sqlPrefix . "wpefc_items";
        sql_delete($table_name, array('id' => $itemID));


        $table_links = $GLOBALS['lfb_connection']->sqlPrefix . "wpefc_links";
        $links = sql_get_results("SELECT * FROM $table_links WHERE formID=$formID");
        foreach ($links as $link) {
            // unset($link->id);

            $conditions = json_decode($link->conditions);
            $newConditions = array();

            foreach ($conditions as $condition) {
                $oldStep = substr($condition->interaction, 0, strpos($condition->interaction, '_'));
                $oldItem = substr($condition->interaction, strpos($condition->interaction, '_') + 1);
                if ($oldStep == $stepID && $oldItem == $itemID) {
                    
                } else {
                    $newConditions[] = $condition;
                }
            }
            sql_update($table_links, array('conditions' => jsonRemoveUnicodeSequences($newConditions)), array('id' => $link->id));
        }
    }
    die();
}

function removeStep() {

    if (!isset($isLogged) || $isLogged) {
        $table_name = $GLOBALS['lfb_connection']->sqlPrefix . "wpefc_steps";

        sql_delete($table_name, array('id' => sanitize_text_field($_POST['stepID'])));
        $table_name = $GLOBALS['lfb_connection']->sqlPrefix . "wpefc_links";
        sql_delete($table_name, array('originID' => sanitize_text_field($_POST['stepID'])));
        sql_delete($table_name, array('destinationID' => sanitize_text_field($_POST['stepID'])));
    }
    die();
}

function addStep() {

    if (!isset($isLogged) || $isLogged) {
        $table_name = $GLOBALS['lfb_connection']->sqlPrefix . "wpefc_steps";
        $formID = sanitize_text_field($_POST['formID']);

        $data = new stdClass();
        $data->start = sanitize_text_field($_POST['start']);

        $stepsStart = sql_get_results("SELECT * FROM $table_name WHERE formID=" . $formID . " AND start=1");
        if (count($stepsStart) == 0) {
            $data->start = 1;
        }

        if ($data->start == 1) {
            $steps = sql_get_results("SELECT * FROM $table_name WHERE formID=" . $formID . " AND start=1");
            foreach ($steps as $step) {
                $dataContent = json_decode($step->content);
                $dataContent->start = 0;
                sql_update($table_name, array('content' => jsonRemoveUnicodeSequences($dataContent), 'start' => 0), array('id' => $data->id));
            }
        }
        $data->previewPosX = sanitize_text_field($_POST['previewPosX']);
        $data->previewPosY = sanitize_text_field($_POST['previewPosY']);
        $data->actions = array();


        sql_insert($table_name, array('content' => jsonRemoveUnicodeSequences($data), 'title' => __('My Step', 'lfb'), 'formID' => $formID, 'start' => $data->start,
            'interactions' => '', 'description' => '', 'showConditions' => ''));
        $data->id = sql_insert_id();
        sql_update($table_name, array('content' => jsonRemoveUnicodeSequences($data), 'formID' => $formID), array('id' => $data->id));
        echo json_encode((array) $data);
    }
    die();
}

function loadStep() {

    if (!isset($isLogged) || $isLogged) {
        $rep = new stdClass();
        $table_name = $GLOBALS['lfb_connection']->sqlPrefix . "wpefc_steps";
        $step = sql_get_results("SELECT * FROM $table_name WHERE id='" . sanitize_text_field($_POST['stepID']) . "' LIMIT 1");
        $rep->step = $step[0];
        $table_name = $GLOBALS['lfb_connection']->sqlPrefix . "wpefc_items";
        $items = sql_get_results("SELECT * FROM $table_name WHERE stepID='" . sanitize_text_field($_POST['stepID']) . "' ORDER BY ordersort ASC");
        $rep->items = $items;
        echo jsonRemoveUnicodeSequences((array) $rep);
    }
    die();
}

function saveItem() {

    if (!isset($isLogged) || $isLogged) {
        $formID = sanitize_text_field($_POST['formID']);
        $stepID = sanitize_text_field($_POST['stepID']);
        $itemID = sanitize_text_field($_POST['id']);

        $table_name = $GLOBALS['lfb_connection']->sqlPrefix . "wpefc_items";

        $sqlDatas = array();
        foreach ($_POST as $key => $value) {
            if ($key != 'action' && $key != 'id' && $key != 'pll_ajax_backend' && $key != "undefined" && $key != 'files') {
                $sqlDatas[$key] = stripslashes($value);
            }
        }
        if ($itemID > 0) {
            sql_update($table_name, $sqlDatas, array('id' => $itemID));
            $response = $_POST['id'];
        } else {
            $sqlDatas['formID'] = $formID;
            $sqlDatas['stepID'] = $stepID;
            sql_insert($table_name, $sqlDatas);
            $itemID = sql_insert_id();
        }
        echo $itemID;
    }
    die();
}

function saveStep() {

    if (!isset($isLogged) || $isLogged) {
        $formID = sanitize_text_field($_POST['formID']);
        $stepID = sanitize_text_field($_POST['id']);
        $table_name = $GLOBALS['lfb_connection']->sqlPrefix . "wpefc_steps";

        $sqlDatas = array();
        foreach ($_POST as $key => $value) {
            if ($key != 'action' && $key != 'id' && $key != 'pll_ajax_backend') {
                $sqlDatas[$key] = (stripslashes($value));
            }
        }

        if ($stepID > 0) {
            sql_update($table_name, $sqlDatas, array('id' => $stepID));
            $response = sanitize_text_field($_POST['id']);
        } else {
            $sqlDatas['formID'] = $formID;
            sql_insert($table_name, $sqlDatas);
            $stepID = sql_insert_id();
        }
        echo $stepID;
    }
    die();
}

function exportLogs() {

    if (!isset($isLogged) || $isLogged) {
        $formID = sanitize_text_field($_POST['formID']);
        $table_name = $GLOBALS['lfb_connection']->sqlPrefix . "wpefc_logs";
        $logs = sql_get_results("SELECT * FROM $table_name WHERE formID=" . $formID . " ORDER BY id ASC");
        if (!is_dir( '../tmp')) {
            mkdir('../tmp');
            chmod('../tmp', 0747);
        }
        $random = rand(1000, 100000);
        $filename = 'export_csv_' . $random . '.csv';
        $target_path =   '../tmp/' . $filename;
        $file = fopen($target_path, "w");

        $content = __('Date', 'lfb') . ';' .
                __('Form', 'lfb') . ';' .
                __('Total price', 'lfb') . ';' .
                __('Total Subscription', 'lfb') . ';' .
                __('Frequency of subscription', 'lfb') . ';' .
                __('Reference', 'lfb') . ';' .
                __('Order', 'lfb') . ';' .
                __('Email', 'lfb') . ';' .
                __('First name', 'lfb') . ';' .
                __('Last name', 'lfb') . ';' .
                __('Country', 'lfb') . ';' .
                __('State', 'lfb') . ';' .
                __('City', 'lfb') . ';' .
                __('Zip code', 'lfb') . ';' .
                __('Address', 'lfb') . ';';

        fwrite($file, $content . "\n");

        foreach ($logs as $log) {
            $verifiedPayment = __('No', 'lfb');
            if ($log->checked) {
                $verifiedPayment = __('Yes', 'lfb');
            }
            $contentTxt = str_replace('[n]', "\r\n", $log->contentTxt);
            $contentTxt = "\"$contentTxt\"";
            $content = $log->dateLog . ';' . $log->formTitle . ';' . number_format($log->totalPrice, 2) . ';' . number_format($log->totalSubscription, 2) . ';' . $log->subscriptionFrequency . ';' .
                    $log->ref . ';' .
                    $contentTxt . ';' .
                    $log->email . ';' .
                    $log->firstName . ';' .
                    $log->lastName . ';' .
                    $log->country . ';' .
                    $log->state . ';' .
                    $log->city . ';' .
                    $log->zip . ';' .
                    $log->address . ';';
            fwrite($file, $content . "\n");
        }
        fclose($file);
        echo 'tmp/' . $filename;
        die();
    }
}

function changePreviewHeight() {

    $height = sanitize_text_field($_POST['height']);
    $table_name = $GLOBALS['lfb_connection']->sqlPrefix . "wpefc_settings";
    sql_update($table_name, array('previewHeight' => $height), array('id' => 1));
    die();
}

function saveLinks() {
    if (!isset($isLogged) || $isLogged) {

        $formID = sanitize_text_field($_POST['formID']);
        $table_name = $GLOBALS['lfb_connection']->sqlPrefix . "wpefc_links";
        if (substr(sanitize_text_field($_POST['links']), 0, 1) == '[' && $formID != "") {
            $links = json_decode(stripslashes($_POST['links']));

            $existingLinks = sql_get_results("SELECT * FROM $table_name WHERE formID=" . $formID);
            if (count($existingLinks) > 1 && count($links) == 0) {
                
            } else {
                sql_query("DELETE FROM $table_name WHERE formID=" . $formID . " AND id>0");

                foreach ($links as $link) {
                    if (isset($link->destinationID) && $link->destinationID > 0) {
                        sql_insert($table_name, array('formID' => $formID, 'operator' => $link->operator, 'originID' => $link->originID, 'destinationID' => $link->destinationID, 'conditions' => jsonRemoveUnicodeSequences($link->conditions)));
                    }
                }
            }
        }
        echo '1';
        die();
    }
}

function importForms() {
    if (!isset($isLogged) || $isLogged) {

        $displayForm = true;
        $settings = getSettings();
        $code = $settings->purchaseCode;
        if (isset($_FILES['importFile'])) {
            $error = false;
            if (!is_dir( '../tmp')) {
                mkdir( '../tmp');
                chmod( '../tmp', 0747);
            }
            $target_path =  '../tmp/export_estimation_form.zip';
            if (@move_uploaded_file($_FILES['importFile']['tmp_name'], $target_path)) {


                $zip = new ZipArchive;
                $res = $zip->open($target_path);
                if ($res === TRUE) {
                    $zip->extractTo( '../tmp/');
                    $zip->close();

                    $formsData = array();

                    $jsonfilename = 'export_estimation_form.json';
                    if (!file_exists( '../tmp/export_estimation_form.json')) {
                        $jsonfilename = 'export_estimation_form';
                    }

                    $file = file_get_contents( '../tmp/' . $jsonfilename);
                    $dataJson = json_decode($file, true);

                    $table_name = $GLOBALS['lfb_connection']->sqlPrefix . "wpefc_forms";
                    sql_query("TRUNCATE TABLE $table_name");
                    if (array_key_exists('forms', $dataJson)) {
                        foreach ($dataJson['forms'] as $key => $value) {
                            if (!array_key_exists('email_adminContent', $value)) {
                                $value['email_adminContent'] = '<p>Ref: <strong>[ref]</strong></p><h2 style="color: #008080;">Information</h2><hr/><span style="font-weight: 600; color: #444444;">[information_content]</span><span style="color: #444444;"> </span><hr/><h2 style="color: #008080;">Project</h2><hr/>[project_content]<hr/><h4>Total: <strong><span style="color: #444444;">[total_price]</span></strong></h4>';
                                $value['email_userContent'] = '<p>Ref: <strong>[ref]</strong></p><h2 style="color: #008080;">Information</h2><hr/><span style="font-weight: 600; color: #444444;">[information_content]</span><span style="color: #444444;"> </span><hr/><h2 style="color: #008080;">Project</h2><hr/>[project_content]<hr/><h4>Total: <strong><span style="color: #444444;">[total_price]</span></strong></h4>';
                            }
                            if ($value['summary_hideQt'] == null) {
                                $value['summary_hideQt'] = 0;
                            }
                            if ($value['summary_hideZero'] == null) {
                                $value['summary_hideZero'] = 0;
                            }
                            if ($value['summary_hidePrices'] == null) {
                                $value['summary_hidePrices'] = 0;
                            }
                            if ($value['groupAutoClick'] == null) {
                                $value['groupAutoClick'] = 0;
                            }
                            if (array_key_exists('save_to_cart', $value)) {
                                unset($value['save_to_cart']);
                            }
                            if (array_key_exists('gravityFormID', $value)) {
                                unset($value['gravityFormID']);
                            }
                            if($value['usedCssFile'] != null && $value['usedCssFile'] != ""){
                                if (is_file('../tmp/' . $value['usedCssFile'])) {
                                    copy('../tmp/' . $value['usedCssFile'], '../export/'. $value['usedCssFile']);
                                }
                             }   

                            if (!array_key_exists('colorSecondary', $value)) {
                                $value['colorSecondary'] = '#bdc3c7';
                                $value['colorSecondaryTxt'] = '#ffffff';
                                $value['colorCbCircle'] = '#7f8c9a';
                                $value['colorCbCircleOn'] = '#bdc3c7';
                            }

                            if ($value['useRedirectionConditions'] == null) {
                                $value['useRedirectionConditions'] = 0;
                            }
                            if ($value['redirectionDelay'] == null) {
                                $value['redirectionDelay'] = 5;
                            }

                            if (array_key_exists('form_page_id', $value)) {
                                unset($value['form_page_id']);
                            }

                            sql_insert($table_name, $value);
                        }
                    }


                    $table_name = $GLOBALS['lfb_connection']->sqlPrefix . "wpefc_steps";
                    sql_query("TRUNCATE TABLE $table_name");
                    $prevPosX = 40;
                    $firstStep = false;
                    foreach ($dataJson['steps'] as $key => $value) {
                        if (!array_key_exists('formID', $value)) {
                            $value['formID'] = 1;
                        }
                        if (!array_key_exists('showInSummary', $value)) {
                            $value['showInSummary'] = 1;
                        }
                        if (!array_key_exists('content', $value)) {
                            $start = 0;
                            if (!$firstStep && $value['ordersort'] == 0) {
                                $start = 1;
                                $value['start'] = 1;
                                $firstStep = true;
                            }
                            $value['content'] = '{"start":"' . $start . '","previewPosX":"' . $prevPosX . '","previewPosY":"140","actions":[],"id":' . $value['id'] . '}';
                            $prevPosX += 200;
                        }
                        sql_insert($table_name, $value);
                    }

                    $table_name = $GLOBALS['lfb_connection']->sqlPrefix . "wpefc_fields";
                    sql_query("TRUNCATE TABLE $table_name");
                    if (array_key_exists('fields', $dataJson)) {
                        foreach ($dataJson['fields'] as $key => $value) {
                            if (!array_key_exists('validation', $value) && $value['id'] == '1') {
                                $value['validation'] = 'email';
                            }
                            if (array_key_exists('height', $value)) {
                                unset($value['height']);
                            }
                            sql_insert($table_name, $value);
                        }
                    }

                    $table_name = $GLOBALS['lfb_connection']->sqlPrefix . "wpefc_links";
                    sql_query("TRUNCATE TABLE $table_name");
                    if (array_key_exists('links', $dataJson)) {
                        foreach ($dataJson['links'] as $key => $value) {
                            sql_insert($table_name, $value);
                        }
                    }

                    $table_name = $GLOBALS['lfb_connection']->sqlPrefix . "wpefc_logs";
                    sql_query("TRUNCATE TABLE $table_name");
                    if (array_key_exists('logs', $dataJson)) {
                        foreach ($dataJson['logs'] as $key => $value) {
                            sql_insert($table_name, $value);
                        }
                    }


                    $table_name = $GLOBALS['lfb_connection']->sqlPrefix . "wpefc_coupons";
                    sql_query("TRUNCATE TABLE $table_name");
                    if (array_key_exists('coupons', $dataJson)) {
                        foreach ($dataJson['coupons'] as $key => $value) {
                            sql_insert($table_name, $value);
                        }
                    }

                    $table_name = $GLOBALS['lfb_connection']->sqlPrefix . "wpefc_redirConditions";
                    sql_query("TRUNCATE TABLE $table_name");
                    if (array_key_exists('redirections', $dataJson)) {
                        foreach ($dataJson['redirections'] as $key => $value) {
                            sql_insert($table_name, $value);
                        }
                    }



                    // Check links
                    $table_name = $GLOBALS['lfb_connection']->sqlPrefix . "wpefc_forms";
                    $forms = sql_get_results("SELECT * FROM $table_name");
                    foreach ($forms as $form) {
                        $table_name = $GLOBALS['lfb_connection']->sqlPrefix . "wpefc_links";
                        $links = sql_get_results("SELECT * FROM $table_name WHERE formID=" . $form->id);
                        if (count($links) == 0) {

                            $stepStartID = 0;
                            $stepStart = sql_get_results("SELECT * FROM " . $GLOBALS['lfb_connection']->sqlPrefix . "wpefc_steps WHERE start=1 AND formID=" . $form->id);
                            if (count($stepStart) > 0) {
                                $stepStart = $stepStart[0];
                                $stepStartID = $stepStart->id;
                            }
                            $steps = sql_get_results("SELECT * FROM " . $GLOBALS['lfb_connection']->sqlPrefix . "wpefc_steps WHERE formID=" . $form->id . " AND start=0 ORDER BY ordersort ASC, id ASC");
                            $i = 0;
                            $prevStepID = 0;
                            foreach ($steps as $step) {
                                if ($i == 0 && $stepStartID > 0) {
                                    sql_insert($GLOBALS['lfb_connection']->sqlPrefix . "wpefc_links", array('originID' => $stepStartID, 'destinationID' => $step->id, 'formID' => $form->id, 'conditions' => '[]'));
                                } else if ($i > 0 && $prevStepID > 0) {
                                    sql_insert($GLOBALS['lfb_connection']->sqlPrefix . "wpefc_links", array('originID' => $prevStepID, 'destinationID' => $step->id, 'formID' => $form->id, 'conditions' => '[]'));
                                }
                                $prevStepID = $step->id;
                                $i++;
                            }
                        }
                    }



                    $table_name = $GLOBALS['lfb_connection']->sqlPrefix . "wpefc_items";
                    sql_query("TRUNCATE TABLE $table_name");
                    foreach ($dataJson['items'] as $key => $value) {

                        if ($value['image'] && $value['image'] != "") {
                            $img_name = substr($value['image'], strrpos($value['image'], '/'));
                            copy( '../tmp/' . $img_name,'../uploads/' . $img_name);
                            $value['image'] = 'uploads/' . $img_name;
                        }
                        
                        if (array_key_exists('sliderStep', $value)) {
                            unset($value['sliderStep']);                                
                        }
                        if (array_key_exists('reduc_qt', $value)) {
                            unset($value['reduc_qt']);
                            unset($value['reduc_value']);
                        }

                        sql_insert($table_name, $value);
                    }


                    // check if form exists
                    $table_name = $GLOBALS['lfb_connection']->sqlPrefix . "wpefc_forms";
                    $forms = sql_get_results("SELECT * FROM $table_name LIMIT 1");
                    if (!$forms || count($forms) == 0) {
                        $formsData['title'] = 'My Estimation Form';
                        sql_insert($table_name, $formsData);
                    }


                    $files = glob( '../tmp/*');
                    foreach ($files as $file) {
                        if (is_file($file))
                            unlink($file);
                    }
                } else {
                    $error = true;
                }
            } else {
                $error = true;
            }
            if ($error) {
                echo __('An error occurred during the transfer', 'lfb');
                die();
            } else {
                $displayForm = false;
                echo 1;
                die();
            }
        }
    }
}

function exportForms() {
    if (!isset($isLogged) || $isLogged) {

        if (!is_dir( '../tmp')) {
            mkdir( '../tmp');
            chmod( '../tmp', 0747);
        }

        $destination =  '../tmp/export_estimation_form.zip';
        if (file_exists($destination)) {
            unlink($destination);
        }
        $zip = new ZipArchive();
        if ($zip->open($destination, ZipArchive::CREATE) !== true) {
            return false;
        }

        $jsonExport = array();
        $table_name = $GLOBALS['lfb_connection']->sqlPrefix . "wpefc_settings";
        $settings = getSettings();
        $settings->purchaseCode = "";
        $settings->tdgn_enabled = "";

        $jsonExport['settings'] = array();
        $jsonExport['settings'][] = $settings;


        $table_name = $GLOBALS['lfb_connection']->sqlPrefix . "wpefc_forms";
        $forms = array();
        foreach (sql_get_results("SELECT * FROM $table_name") as $key => $row) {
            $row->analyticsID = '';
            $forms[] = $row;
            if($row->usedCssFile != "" && file_exists(plugin_dir_path(__FILE__) . '../export/'.$row->usedCssFile)){                    
                $zip->addfile('../export/'.$row->usedCssFile, $row->usedCssFile);
            }
        }
        $jsonExport['forms'] = $forms;

        $table_name = $GLOBALS['lfb_connection']->sqlPrefix . "wpefc_logs";
        $logs = array();
        foreach (sql_get_results("SELECT * FROM $table_name") as $key => $row) {
            $logs[] = $row;
        }
        $jsonExport['logs'] = $logs;

        $table_name = $GLOBALS['lfb_connection']->sqlPrefix . "wpefc_coupons";
        $coupons = array();
        foreach (sql_get_results("SELECT * FROM $table_name") as $key => $row) {
            $coupons[] = $row;
        }
        $jsonExport['coupons'] = $coupons;

        $table_name = $GLOBALS['lfb_connection']->sqlPrefix . "wpefc_steps";
        $steps = array();
        foreach (sql_get_results("SELECT * FROM $table_name") as $key => $row) {
            $steps[] = $row;
        }
        $jsonExport['steps'] = $steps;

        $table_name = $GLOBALS['lfb_connection']->sqlPrefix . "wpefc_fields";
        $steps = array();
        foreach (sql_get_results("SELECT * FROM $table_name") as $key => $row) {
            $steps[] = $row;
        }
        $jsonExport['fields'] = $steps;

        $table_name = $GLOBALS['lfb_connection']->sqlPrefix . "wpefc_links";
        $steps = array();
        foreach (sql_get_results("SELECT * FROM $table_name") as $key => $row) {
            $steps[] = $row;
        }
        $jsonExport['links'] = $steps;

        $table_name = $GLOBALS['lfb_connection']->sqlPrefix . "wpefc_redirConditions";
        $redirs = array();
        foreach (sql_get_results("SELECT * FROM $table_name") as $key => $row) {
            $steps[] = $row;
        }
        $jsonExport['redirections'] = $redirs;




        $table_name = $GLOBALS['lfb_connection']->sqlPrefix . "wpefc_items";
        $items = array();
        foreach (sql_get_results("SELECT * FROM $table_name") as $key => $row) {
            $items[] = $row;
            if ($row->image != "") {
                $row->image = substr($row->image, strrpos($row->image, "/uploads/") + 8);
                $zip->addfile( "../uploads/" . $row->image, $row->image);
            }
        }

        $jsonExport['items'] = $items;
        $fp = fopen( '../tmp/export_estimation_form.json', 'w');
        fwrite($fp, json_encode($jsonExport));
        fclose($fp);

        $zip->addfile( '../tmp/export_estimation_form.json', 'export_estimation_form.json');
        $zip->close();
        echo '1';
        die();
    }
}

function removeAllCoupons() {
    if (!isset($isLogged) || $isLogged) {

        $formID = sanitize_text_field($_POST['formID']);
        $table_name = $GLOBALS['lfb_connection']->sqlPrefix . "wpefc_coupons";
        sql_delete($table_name, array('formID' => $formID));
    }
    die();
}

function removeCoupon() {
    if (!isset($isLogged) || $isLogged) {

        $couponID = sanitize_text_field($_POST['couponID']);
        $formID = sanitize_text_field($_POST['formID']);
        $table_name = $GLOBALS['lfb_connection']->sqlPrefix . "wpefc_coupons";
        sql_delete($table_name, array('id' => $couponID));
    }
    die();
}

function saveCoupon() {
    if (!isset($isLogged) || $isLogged) {

        $table_name = $GLOBALS['lfb_connection']->sqlPrefix . "wpefc_coupons";
        $couponID = sanitize_text_field($_POST['couponID']);
        $formID = sanitize_text_field($_POST['formID']);
        $couponCode = sanitize_text_field($_POST['couponCode']);
        $useMax = sanitize_text_field($_POST['useMax']);
        $reduction = sanitize_text_field($_POST['reduction']);
        $reductionType = sanitize_text_field($_POST['reductionType']);

        if ($couponID > 0) {
            sql_update($table_name, array('couponCode' => $couponCode, 'useMax' => $useMax, 'reduction' => $reduction, 'reductionType' => $reductionType), array('id' => $couponID));
            echo $couponID;
        } else {
            sql_insert($table_name, array('couponCode' => $couponCode, 'useMax' => $useMax, 'reduction' => $reduction, 'reductionType' => $reductionType, 'formID' => $formID));
            echo sql_insert_id();
        }
    }
    die();
}

function importPic() {
    if (!isset($isLogged) || $isLogged) {

        $displayForm = true;
        $settings = getSettings();
        $code = $settings->purchaseCode;
        if (isset($_FILES['importFile'])) {
            $error = false;
            $lfb_uploadsDir = '../uploads/';
            //echo $lfb_uploadsDir;
            if (!is_dir($lfb_uploadsDir)) {     // TODO
                mkdir($lfb_uploadsDir);
                chmod($lfb_uploadsDir, 0747);
            }
            $ext = substr($_FILES["importFile"]["name"], strrpos($_FILES["importFile"]["name"], '.'));
            $ext = strtolower($ext);
            // echo $ext;
            if ($ext == '.jpg' || $ext == '.png' || $ext == '.gif' || $ext == '.svg' || $ext == '.tif') {
                $target_path = $lfb_uploadsDir;
                $filename = rand(0, 1000).date('is') . '_' . basename($_FILES["importFile"]["name"]);
                if (@move_uploaded_file($_FILES['importFile']['tmp_name'], $target_path . $filename)) {
                    echo $filename;
                }
            }
        }
    }
}
function lfb_unlog(){
    session_start();
    $_SESSION['lfb_logged'] = 0;
    session_destroy();
}
function lfb_passLost(){
    $email = sanitize_text_field($_POST['email']);
        
    $table_name = $GLOBALS['lfb_connection']->sqlPrefix . "wpefc_settings";
    $settings = sql_get_results("SELECT * FROM $table_name WHERE admin_email='$email' AND id=1 LIMIT 1");
    if(count($settings)>0){
        $settings = $settings[0];
        require_once('PHPMailer/class.phpmailer.php');
            $emailUser = new PHPMailer();
            $emailUser->Subject   = "Your password for PHP Cost Estimation Payments Forms Builder";
            $emailUser->Body      = 'Here is your password : <strong>'.$settings->admin_pass.'</strong>';
            $emailUser->isHTML(true); 
            $emailUser->AddAddress($email); 
            if($emailUser->Send()){
        echo 1;
            }
    }else {
        echo 0;
    }
}
mysqli_close($GLOBALS['lfb_connection']);
?>