<?php
require_once './config.php';
session_start();
$isInstalled = false;
$isLogged = false;

if (isset($_SESSION['lfb_logged']) && $_SESSION['lfb_logged'] == '101') {
    $isLogged = true;
}
$connection = mysqli_connect($sql_server, $sql_user_name, $sql_password, $sql_database_name);
if (!mysqli_connect_errno($connection)) {

    $GLOBALS['lfb_connection'] = $connection;
    $GLOBALS['lfb_connection']->sqlPrefix = $sql_prefix;
    $result = mysqli_query($connection, 'SHOW TABLES LIKE "' . $sql_prefix . 'wpefc_settings"') or die(mysqli_error($connection));
    $tableExists = mysqli_num_rows($result) > 0;
    if ($tableExists) {
        $req = mysqli_query($connection, 'SELECT * FROM ' . $sql_prefix . 'wpefc_settings WHERE id=1');
        $settings = mysqli_fetch_object($req);
        if (count($settings) > 0) {
            $isInstalled = true;
        } else {
            $isLogged = false;
        }
    } else {
        $isLogged = false;
    }
} else {
    $isLogged = false;
}
if ($isLogged) {
    if (!class_exists("Mailchimp", false)) {
              require_once('includes/Mailchimp.php');    
    }
     require_once('includes/GetResponseAPI.class.php');    
}

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

if (isset($_POST['action'])) {

    function ajax_lfb_login() {
        $email = mysqli_real_escape_string($GLOBALS['lfb_connection'],$_POST['email']);
        $pass = mysqli_real_escape_string($GLOBALS['lfb_connection'], $_POST['pass']);

        $admin = mysqli_query($GLOBALS['lfb_connection'],'SELECT * FROM ' . $GLOBALS['lfb_connection']->sqlPrefix . 'wpefc_settings WHERE id=1 AND admin_email="' . $email . '" AND admin_pass="' . $pass . '" LIMIT 1');
        $admin = mysqli_fetch_object($admin);
        if (count($admin) > 0 && $admin->admin_email) {
            $_SESSION['lfb_logged'] = 101;
            echo 1;
        } else {
            $_SESSION['lfb_logged'] = 0;
            echo 0;
        }
    }

    function ajax_lfb_install() {
        $step = ($_POST['step']);
        if ($step == 0) {
            $db_host = ($_POST['db_server']);
            $db_name = ($_POST['db_name']);
            $db_username = ($_POST['db_username']);
            $db_pass = ($_POST['db_pass']);
            $db_prefix = ($_POST['db_prefix']);

            $connectionInst = mysqli_connect($db_host, $db_username, $db_pass, $db_name);
            if (!mysqli_connect_errno($connectionInst)) {

                $configFile = fopen("./config.php", "w") or die("The plugin can't write the configuration file. Please check the file permissions or fill the database information directly in the config.php file");
                fwrite($configFile, '<?php' . "\n");
                fwrite($configFile, '$sql_server = "' . $db_host . '";' . "\n");
                fwrite($configFile, '$sql_database_name = "' . $db_name . '";' . "\n");
                fwrite($configFile, '$sql_user_name = "' . $db_username . '";' . "\n");
                fwrite($configFile, '$sql_password = "' . $db_pass . '";' . "\n");
                fwrite($configFile, '$sql_prefix = "' . $db_prefix . '";' . "\n");
                fwrite($configFile, '?>' . "\n");
                fclose($configFile);

                $sql_server = $db_host;
                $sql_database_name = $db_name;
                $sql_user_name = $db_username;
                $sql_password = $db_pass;
                $sql_prefix = $db_prefix;

                echo '1';

                mysqli_close($connectionInst);
            } else {
                echo 'The plugin can not connect to the database. Please verify the filled informations.';
            }
        } else if ($step == 1) {
            require './config.php';
            $connectionInst = mysqli_connect($sql_server, $sql_user_name, $sql_password, $sql_database_name);
            $admin_email = mysqli_real_escape_string($connectionInst, $_POST['admin_email']);
            $admin_pass = mysqli_real_escape_string($connectionInst, $_POST['admin_pass']);

            mysqli_query($connectionInst, 'DROP TABLE ' . $sql_prefix . 'wpefc_forms');
            mysqli_query($connectionInst, 'DROP TABLE ' . $sql_prefix . 'wpefc_settings');
            mysqli_query($connectionInst, 'DROP TABLE ' . $sql_prefix . 'wpefc_items');
            mysqli_query($connectionInst, 'DROP TABLE ' . $sql_prefix . 'wpefc_links');
            mysqli_query($connectionInst, 'DROP TABLE ' . $sql_prefix . 'wpefc_steps');
            mysqli_query($connectionInst, 'DROP TABLE ' . $sql_prefix . 'wpefc_fields');
            mysqli_query($connectionInst, 'DROP TABLE ' . $sql_prefix . 'wpefc_logs');
            mysqli_query($connectionInst, 'DROP TABLE ' . $sql_prefix . 'wpefc_coupons');
            mysqli_query($connectionInst, 'DROP TABLE ' . $sql_prefix . 'wpefc_redirConditions');

            $sql = "CREATE TABLE " . $sql_prefix . "wpefc_forms (
		id mediumint(9) NOT NULL AUTO_INCREMENT,
		title VARCHAR(120) NOT NULL DEFAULT '',
                errorMessage VARCHAR(240) NOT NULL DEFAULT '',
                intro_enabled BOOL DEFAULT 0,
                use_paypal BOOL NOT NULL  DEFAULT 0,
                paypal_email VARCHAR(250) NULL DEFAULT '',
                paypal_currency VARCHAR(3) NOT NULL DEFAULT 'USD',
                paypal_useIpn BOOL DEFAULT 0,
                paypal_useSandbox BOOL DEFAULT 0,
                paypal_subsFrequency SMALLINT(5) NOT NULL DEFAULT 1,
                paypal_subsFrequencyType VARCHAR(1) NOT NULL DEFAULT 'M',
                paypal_subsMaxPayments SMALLINT(5) NOT NULL DEFAULT 0,
                paypal_languagePayment VARCHAR(8) NOT NULL DEFAULT '',
                use_stripe BOOL DEFAULT 0,
                stripe_useSandbox BOOL DEFAULT 0,
                stripe_secretKey VARCHAR(250) NOT NULL DEFAULT '',
                stripe_publishKey VARCHAR(250) NOT NULL DEFAULT '',
                stripe_currency VARCHAR(6) NOT NULL DEFAULT '',
                stripe_subsFrequencyType VARCHAR(16) NOT NULL DEFAULT 'month',                
                isSubscription BOOL DEFAULT 0,
                subscription_text VARCHAR(250) NOT NULL DEFAULT '/month',
                close_url VARCHAR(250) NOT NULL DEFAULT '#',
                btn_step VARCHAR(120) NOT NULL DEFAULT '',
                previous_step VARCHAR(120) NOT NULL DEFAULT '',
                intro_title VARCHAR(120) NOT NULL DEFAULT '',
                intro_text TEXT NOT NULL ,
                intro_btn VARCHAR(120) NOT NULL DEFAULT '',
                last_title VARCHAR(120) NOT NULL DEFAULT '',
                last_text TEXT NOT NULL ,
                last_btn VARCHAR(120) NOT NULL DEFAULT '',
                last_msg_label VARCHAR(240) NOT NULL DEFAULT '',
                initial_price FLOAT NOT NULL DEFAULT 0,
                max_price FLOAT NOT NULL DEFAULT 0,
                succeed_text TEXT NOT NULL ,
                email VARCHAR(250) NOT NULL DEFAULT '',
                email_adminContent TEXT NOT NULL ,
                email_subject VARCHAR(250) NOT NULL DEFAULT '',
                email_toUser BOOL NOT NULL DEFAULT 0,
                email_userSubject VARCHAR(250) NOT NULL DEFAULT '',
                email_userContent TEXT NOT NULL ,
                currency VARCHAR (32) NOT NULL DEFAULT '',
                currencyPosition VARCHAR (32) NOT NULL DEFAULT '',
                animationsSpeed FLOAT NOT NULL DEFAULT 0.5,
                showSteps SMALLINT(5) NOT NULL DEFAULT 0,
                qtType SMALLINT(9) NOT NULL DEFAULT 0,
                show_initialPrice BOOL NOT NULL DEFAULT 0,
                ref_root VARCHAR(16) NOT NULL DEFAULT 'A000',
                current_ref INT(9) NOT NULL DEFAULT 1,
                colorA VARCHAR(16) NOT NULL DEFAULT '',
                colorB VARCHAR(16) NOT NULL DEFAULT '',
                colorC VARCHAR(16) NOT NULL DEFAULT '',
                colorBg VARCHAR(16) NOT NULL DEFAULT '',
                colorSecondary VARCHAR(16) NOT NULL DEFAULT '',
                colorSecondaryTxt VARCHAR(16) NOT NULL DEFAULT '',
                colorCbCircle VARCHAR(16) NOT NULL DEFAULT '',
                colorCbCircleOn VARCHAR(16) NOT NULL DEFAULT '',
                item_pictures_size SMALLINT(9) NOT NULL DEFAULT 0,
                hideFinalPrice BOOL NOT NULL DEFAULT 0,
                priceFontSize SMALLINT NOT NULL DEFAULT 18,
                customCss TEXT NOT NULL ,
                disableTipMobile BOOL NOT NULL DEFAULT 0,
                legalNoticeContent TEXT NOT NULL ,
                legalNoticeTitle TEXT NOT NULL ,
                legalNoticeEnable BOOL NOT NULL DEFAULT 0,
                datepickerLang VARCHAR(16)  NOT NULL DEFAULT '',
         	percentToPay FLOAT DEFAULT 100,
                thousandsSeparator VARCHAR(4) NOT NULL DEFAULT '',
                decimalsSeparator VARCHAR(4) NOT NULL DEFAULT '',
                millionSeparator VARCHAR(4) NOT NULL DEFAULT '',
                useSummary BOOL NOT NULL DEFAULT 0,
                summary_title VARCHAR(240) NOT NULL DEFAULT '',
                summary_description VARCHAR(240) NOT NULL DEFAULT '',
                summary_quantity VARCHAR(240) NOT NULL DEFAULT '',
                summary_price VARCHAR(240) NOT NULL DEFAULT '',
                summary_total VARCHAR(240) NOT NULL DEFAULT '',
                summary_value VARCHAR(240) NOT NULL DEFAULT '',
                summary_discount VARCHAR(240) NOT NULL DEFAULT 'Discount :',
                summary_hideQt BOOL DEFAULT 0,
                summary_hideZero BOOL DEFAULT 0,
                summary_hidePrices BOOL DEFAULT 0,
                summary_hideTotal BOOL DEFAULT 0,
                groupAutoClick BOOL DEFAULT 0,
                useCoupons BOOL NOT NULL DEFAULT 0,
                inverseGrayFx BOOL NOT NULL DEFAULT 0,                
                couponText VARCHAR(250) NOT NULL DEFAULT 'Discount coupon code',
                useMailchimp BOOL NOT NULL  DEFAULT 0,
                mailchimpKey VARCHAR(250) NOT NULL DEFAULT '',
                mailchimpList VARCHAR(250) NOT NULL DEFAULT '',
                mailchimpOptin BOOL NOT NULL DEFAULT 0,
                useMailpoet BOOL NOT NULL DEFAULT 0,
                mailPoetList VARCHAR(250) NOT NULL DEFAULT '',
                useGetResponse BOOL NOT NULL DEFAULT 0,
                getResponseKey VARCHAR(250) NOT NULL DEFAULT '',
                getResponseList VARCHAR(250) NOT NULL DEFAULT '',
                loadAllPages BOOL NOT NULL DEFAULT 0,
                filesUpload_text VARCHAR(250) NOT NULL DEFAULT 'Drop files here to upload', 
                filesUploadSize_text VARCHAR(250) NOT NULL DEFAULT 'File is too big (max size: {{maxFilesize}}MB)', 
                filesUploadType_text VARCHAR(250) NOT NULL DEFAULT 'Invalid file type',          
                filesUploadLimit_text VARCHAR(250) NOT NULL DEFAULT 'You can not upload any more files',
                useGoogleFont BOOL NOT NULL DEFAULT 1,
                googleFontName VARCHAR(250) NOT NULL DEFAULT 'Lato',
                analyticsID VARCHAR(250) NOT NULL DEFAULT '',
                sendPdfCustomer BOOL NOT NULL DEFAULT 0, 
                sendPdfAdmin BOOL NOT NULL DEFAULT 0, 
                sendContactASAP BOOL NOT NULL DEFAULT 0,
                showTotalBottom BOOL NOT NULL DEFAULT 0,
                stripe_label_creditCard VARCHAR(250) NOT NULL DEFAULT '',
                stripe_label_cvc VARCHAR(250) NOT NULL DEFAULT '',
                stripe_label_expiration VARCHAR(250) NOT NULL DEFAULT '',    
                scrollTopMargin INT(9) NOT NULL DEFAULT 0,
                redirectionDelay INT(9) NOT NULL DEFAULT 5,
                useRedirectionConditions BOOL NOT NULL DEFAULT 0,
                gmap_key VARCHAR(250) NOT NULL DEFAULT '',
                txtDistanceError TEXT NOT NULL ,
                customJS TEXT NOT NULL ,
                disableDropdowns BOOL NOT NULL DEFAULT 0,                
                usedCssFile VARCHAR(250) NOT NULL DEFAULT '',
                formStyles LONGTEXT NOT NULL,
                columnsWidth SMALLINT(5) NOT NULL DEFAULT 0,
                 useCaptcha BOOL NOT NULL DEFAULT 0,
                 captchaLabel VARCHAR(250) NOT NULL DEFAULT 'Please rewrite the following text in the field',
		UNIQUE KEY id (id)
		);";
            mysqli_query($connectionInst, $sql) or die(mysqli_error($connectionInst));
            
            

            $db_table_name = $sql_prefix . "wpefc_steps";
            $sql = "CREATE TABLE $db_table_name (
    		id mediumint(9) NOT NULL AUTO_INCREMENT,
    		formID mediumint (9) NOT NULL DEFAULT 0,
    		start BOOL  NOT NULL DEFAULT 0,
    		title VARCHAR(120) NOT NULL DEFAULT '',
    		content TEXT NOT NULL ,
    		ordersort mediumint(9) NOT NULL DEFAULT 0,
    		itemRequired BOOL  NOT NULL DEFAULT 0,
    		itemDepend SMALLINT(5) NOT NULL DEFAULT 0,
    		interactions TEXT NOT NULL ,
    		description TEXT NOT NULL ,
    		showInSummary BOOL  NOT NULL DEFAULT 1,
                itemsPerRow TINYINT(2) NOT NULL DEFAULT 0,
                useShowConditions BOOL NOT NULL DEFAULT 0,
                showConditions TEXT NOT NULL ,
                showConditionsOperator VARCHAR(8) NOT NULL DEFAULT '',
                hideNextStepBtn  BOOL NOT NULL DEFAULT 0,
    		UNIQUE KEY id (id)
    		);";
            mysqli_query($connectionInst, $sql) or die(mysqli_error($connectionInst));

            $db_table_name = $sql_prefix . "wpefc_logs";
            $sql = "CREATE TABLE $db_table_name (
    		id mediumint(9) NOT NULL AUTO_INCREMENT,
    		formID mediumint (9) NOT NULL DEFAULT 0,
    		ref VARCHAR(120) NOT NULL DEFAULT '',
    		email VARCHAR(250) NOT NULL DEFAULT '',
    		content MEDIUMTEXT NOT NULL ,
                contentUser MEDIUMTEXT NOT NULL ,
                contentTxt MEDIUMTEXT NOT NULL ,
                dateLog VARCHAR(64) NOT NULL DEFAULT '',
                sendToUser BOOL DEFAULT 0,
                checked BOOL DEFAULT 0,
                phone VARCHAR(120) NOT NULL DEFAULT '',
                firstName VARCHAR(250) NOT NULL DEFAULT '',
                lastName VARCHAR(250) NOT NULL DEFAULT '',
                address TEXT NOT NULL ,
                city VARCHAR(250) NOT NULL DEFAULT '',
                country VARCHAR(250) NOT NULL DEFAULT '',
                state VARCHAR(250) NOT NULL DEFAULT '',
                zip VARCHAR(128) NOT NULL DEFAULT '',
                totalPrice FLOAT NOT NULL DEFAULT 0,
                totalSubscription FLOAT NOT NULL DEFAULT 0,
                subscriptionFrequency VARCHAR(64) NOT NULL DEFAULT '',
                formTitle VARCHAR(250) NOT NULL DEFAULT '',
    		UNIQUE KEY id (id)
    		);";
            mysqli_query($connectionInst, $sql) or die(mysqli_error($connectionInst));

            $db_table_name = $sql_prefix . "wpefc_items";
            $sql = "CREATE TABLE $db_table_name (
                id mediumint(9) NOT NULL AUTO_INCREMENT,
                title VARCHAR(120) NOT NULL DEFAULT '',
                 description TEXT NOT NULL ,
                ordersort mediumint(9) NOT NULL DEFAULT 0,
                image VARCHAR(250) NOT NULL DEFAULT '',
                imageDes VARCHAR(250) NOT NULL DEFAULT '',
                groupitems VARCHAR(120) NOT NULL DEFAULT '',
                type VARCHAR(120) NOT NULL DEFAULT '',
                stepID mediumint(9) NOT NULL DEFAULT 0,
                formID mediumint(9) NOT NULL DEFAULT 0,
                 price FLOAT NOT NULL DEFAULT 0,
                 operation VARCHAR(1) NOT NULL DEFAULT '+',
                 ischecked BOOL DEFAULT 0,
                 isRequired BOOL DEFAULT 0,
                 quantity_enabled BOOL DEFAULT 0,
                 quantity_max INT(11)  NOT NULL DEFAULT 0,
                 quantity_min INT(11)  NOT NULL DEFAULT 0,
                 reduc_enabled BOOL NOT NULL DEFAULT 0,
                 reduc_qt SMALLINT(5) NOT NULL DEFAULT 0,
                 reduc_value FLOAT NOT NULL DEFAULT 0,
                 reducsQt LONGTEXT NOT NULL ,
                 isWooLinked BOOL DEFAULT 0,
                 wooProductID SMALLINT(5)  NOT NULL DEFAULT 0,
                 wooVariation SMALLINT(9)  NOT NULL DEFAULT 0,
                 imageTint BOOL DEFAULT 0,
                 showPrice BOOL DEFAULT 0,
                 useRow BOOL NOT NULL DEFAULT 0,
                 optionsValues TEXT NOT NULL ,
                 urlTarget VARCHAR(250) NOT NULL DEFAULT '',
                 showInSummary BOOL DEFAULT 1 DEFAULT 0,
                 richtext TEXT NOT NULL ,
                 isHidden BOOL NOT NULL DEFAULT 0,
                 minSize INT(11) NOT NULL DEFAULT 0,
                 maxSize INT(11) NOT NULL DEFAULT 0,
                 isNumeric BOOL NOT NULL DEFAULT 0,
                 isSinglePrice BOOL NOT NULL DEFAULT 0,
                 maxFiles SMALLINT(9) NOT NULL DEFAULT 0,
                 allowedFiles VARCHAR(250) NOT NULL DEFAULT '.png,.jpg,.jpeg,.gif,.zip,.rar',
                 useCalculation BOOL NOT NULL DEFAULT 0,
                 calculation TEXT NOT NULL ,
                 fieldType VARCHAR(64) NOT NULL DEFAULT '',
                 useShowConditions BOOL NOT NULL DEFAULT 0,
                 showConditions TEXT NOT NULL ,
                 showConditionsOperator VARCHAR(8) NOT NULL DEFAULT '',
                 usePaypalIfChecked BOOL NOT NULL DEFAULT 0,
                 useDistanceAsQt BOOL NOT NULL DEFAULT 0,
                 distanceQt VARCHAR(250) NOT NULL DEFAULT '',
                 hideQtSummary BOOL NOT NULL DEFAULT 0,
                 defaultValue TEXT NOT NULL ,
                 fileSize INT(9) NOT NULL DEFAULT 25,
                 firstValueDisabled BOOL NOT NULL DEFAULT 0,
  		UNIQUE KEY id (id)
		);";

            mysqli_query($connectionInst, $sql) or die(mysqli_error($connectionInst));

            $db_table_name = $sql_prefix . "wpefc_links";
            $sql = "CREATE TABLE $db_table_name (
    		id mediumint(9) NOT NULL AUTO_INCREMENT,
    		formID mediumint (9) NOT NULL DEFAULT 0,
    		originID INT(9) NOT NULL DEFAULT 0,
    		destinationID INT(9) NOT NULL DEFAULT 0,
    		conditions TEXT NOT NULL ,
                operator VARCHAR(8) NOT NULL DEFAULT '',
    		UNIQUE KEY id (id)
    		);";
            mysqli_query($connectionInst, $sql) or die(mysqli_error($connectionInst));

            $db_table_name = $sql_prefix . "wpefc_fields";
            $sql = "CREATE TABLE $db_table_name (
    		    id mediumint(9) NOT NULL AUTO_INCREMENT,
                    formID SMALLINT(5) NOT NULL DEFAULT 0,
    		    label VARCHAR(120) NOT NULL DEFAULT '',
    		    ordersort mediumint(9) NOT NULL DEFAULT 0,
    		    isRequired BOOL DEFAULT 0 DEFAULT 0,
    		    typefield VARCHAR(32) NOT NULL DEFAULT '',
    		    visibility VARCHAR(32) NOT NULL DEFAULT '',
                    validation VARCHAR(64) NOT NULL DEFAULT '',
                    fieldType VARCHAR(64) NOT NULL DEFAULT '',
    		UNIQUE KEY id (id)
    		);";

            mysqli_query($connectionInst, $sql) or die(mysqli_error($connectionInst));


            $db_table_name = $sql_prefix . "wpefc_coupons";
            $sql = "CREATE TABLE $db_table_name (
  		id mediumint(9) NOT NULL AUTO_INCREMENT,
                formID mediumint(9) NOT NULL DEFAULT 0,
  		couponCode VARCHAR(250) NOT NULL DEFAULT '',
  		reduction FLOAT NOT NULL DEFAULT 0,
                reductionType VARCHAR(64) NOT NULL DEFAULT '',
                useMax SMALLINT(5) NOT NULL DEFAULT 1,
                currentUses SMALLINT(5) NOT NULL DEFAULT 0,
  		UNIQUE KEY id (id)
  		);";
            mysqli_query($connectionInst, $sql) or die(mysqli_error($connectionInst));


            $db_table_name = $sql_prefix . "wpefc_redirConditions";
            $sql = "CREATE TABLE $db_table_name (
    		id mediumint(9) NOT NULL AUTO_INCREMENT,
    		formID mediumint (9) NOT NULL DEFAULT 0,    		
    		conditions TEXT NOT NULL ,
                conditionsOperator VARCHAR(4) NOT NULL DEFAULT '+',
                url VARCHAR(250) NOT NULL DEFAULT '',
    		UNIQUE KEY id (id)
    		);";
            mysqli_query($connectionInst, $sql) or die(mysqli_error($connectionInst));

            $db_table_name = $sql_prefix . "wpefc_settings";
            $sql = "CREATE TABLE $db_table_name (
  		id mediumint(9) NOT NULL AUTO_INCREMENT,
  		admin_email VARCHAR(250) NOT NULL DEFAULT '',
  		admin_pass VARCHAR(250) NOT NULL DEFAULT '',
  		purchaseCode VARCHAR(250) NOT NULL DEFAULT '',
  		previewHeight SMALLINT(5) NOT NULL DEFAULT 300,
                tdgn_enabled SMALLINT(5) NOT NULL DEFAULT 0,
                firstStart BOOL NOT NULL DEFAULT 1,
                versionPlugin VARCHAR(64) NOT NULL DEFAULT '0',
  		UNIQUE KEY id (id)
  		);";
            mysqli_query($connectionInst, $sql) or die(mysqli_error($connectionInst));
            mysqli_query($connectionInst, 'INSERT INTO ' . $db_table_name . ' (admin_email,admin_pass) VALUES ("' . $admin_email . '", "' . $admin_pass . '");') or die(mysqli_error($connectionInst));

            mysqli_close($connectionInst);
            echo '1';
        }
    }
 $isAjax = isset($_SERVER['HTTP_X_REQUESTED_WITH']) AND strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest';
        if($isAjax){
        switch ($_POST['action']) {
        
        case 'lfb_install':
            ajax_lfb_install();
            break;
        case 'lfb_login':
            ajax_lfb_login();
            break;
     }
    }
} else {

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
        if (!isset($connection)) {
            require './config.php';
            $chkClose = true;
            $connection = mysqli_connect($sql_server, $sql_user_name, $sql_password, $sql_database_name);
        }

        $rep = array();
        $sql = mysqli_query($GLOBALS['lfb_connection'], $query);
        while ($data = mysqli_fetch_object($sql)) {
            $rep[] = $data;
        }
        if ($chkClose) {
            mysqli_close($connection);
        }
        return $rep;
    }

    function sql_insert_id() {
        $rep = false;
        if (!isset($connection)) {
            require './config.php';
            $chkClose = true;
            $connection = mysqli_connect($sql_server, $sql_user_name, $sql_password, $sql_database_name);
        }
        $rep = mysqli_insert_id($connection);
        if ($chkClose) {
            mysqli_close($connection);
        }
        return $rep;
    }

    function sql_query($query) {
        $rep = false;
        if (!isset($connection)) {
            require './config.php';
            $chkClose = true;
            $connection = mysqli_connect($sql_server, $sql_user_name, $sql_password, $sql_database_name);
        }
        $sql = mysqli_query($connection, $query);
        if ($chkClose) {
            mysqli_close($connection);
        }
    }

    function sql_insert($table, $data) {
        $rep = false;
        if (!isset($connection)) {
            require './config.php';
            $chkClose = true;
            $connection = mysqli_connect($sql_server, $sql_user_name, $sql_password, $sql_database_name);
        }
        $keysString = '';
        $dataString = '';
        foreach ($data as $key => $value) {
            $keysString.=',' . $key;
            $dataString.=', "' . $value . '"';
        }
        if ($dataString != "") {
            $dataString = substr($dataString, 1);
            $keysString = substr($keysString, 1);
            $sql = mysqli_query($connection, 'INSERT INTO ' . $table . ' (' . $keysString . ') SET ' . $dataString);
            $rep = true;
        }
        if ($chkClose) {
            mysqli_close($connection);
        }
        return $rep;
    }

    function sql_update($table, $data, $selector) {
        $dataString = '';
        $whereString = '';
        $rep = false;
        if (!isset($connection)) {
            require './config.php';
            $chkClose = true;
            $connection = mysqli_connect($sql_server, $sql_user_name, $sql_password, $sql_database_name);
        }
        foreach ($data as $key => $value) {
            $dataString.=', ' . $key . '="' . $value . '"';
        }
        foreach ($selector as $key => $value) {
            $whereString.=', ' . $key . '="' . $value . '"';
        }
        if ($dataString != "") {
            if ($whereString != "") {
                $whereString = substr($whereString, 1);
                $whereString = 'WHERE ' . substr($whereString, 1);
            } else {
                $whereString = 'WHERE id>0';
            }
            $dataString = substr($dataString, 1);
            $sql = mysqli_query($connection, 'UPDATE ' . $table . ' SET ' . $dataString . ' ' . $whereString);
            $rep = true;
        }
        if ($chkClose) {
            mysqli_close($connection);
        }
        return $rep;
        //
    }

    function sql_delete($table, $selector) {
        $rep = false;
        if (!isset($connection)) {
            require './config.php';
            $chkClose = true;
            $connection = mysqli_connect($sql_server, $sql_user_name, $sql_password, $sql_database_name);
        }
        $whereString = '';
        foreach ($selector as $key => $value) {
            $whereString.=', ' . $key . '="' . $value . '"';
        }
        if ($whereString != "") {
            $sql = mysqli_query($connection, 'DELETE FROM ' . $table . ' ' . $whereString);
            $rep = true;
        }
        if ($chkClose) {
            mysqli_close($connection);
        }
        return $rep;
    }

    function sanitize_text_field($string) {
        $rep = $string;
        $rep = mysqli_real_escape_string($connection, $string);

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
    
    
    
function lfb_checkUpdate(){
    $version = 0;
    $result = mysqli_query($GLOBALS['lfb_connection'],"SHOW COLUMNS FROM `".$GLOBALS['lfb_connection']->sqlPrefix . 'wpefc_settings'."` LIKE 'versionPlugin'");
    $exists = (mysqli_num_rows($result))?TRUE:FALSE;
    if(!$exists){
        mysqli_query($GLOBALS['lfb_connection'], "ALTER TABLE " . $GLOBALS['lfb_connection']->sqlPrefix . 'wpefc_settings' . " ADD versionPlugin VARCHAR(64) NOT NULL DEFAULT '0';");
        mysqli_query($GLOBALS['lfb_connection'], "ALTER TABLE " . $GLOBALS['lfb_connection']->sqlPrefix . 'wpefc_forms' . " ADD useCaptcha BOOL NOT NULL DEFAULT 0;");
        mysqli_query($GLOBALS['lfb_connection'], "ALTER TABLE " . $GLOBALS['lfb_connection']->sqlPrefix . 'wpefc_forms' . " ADD captchaLabel VARCHAR(250) NOT NULL DEFAULT 'Please rewrite the following text in the field';");
        sql_update($GLOBALS['lfb_connection']->sqlPrefix . 'wpefc_settings', array('versionPlugin'=>'1.00'),array('id'=>1));
    }
}
function tdgn_showFormDesigner($form) {

    echo '<div id="lfb_bootstraped" class="lfb_bootstraped tld_panel tld_tdgnBootstrap">';
    ?>
    <div id="tld_tdgnContainer">

        <div id="tld_winSaveDialog" class="modal fade" tabindex="-1" role="dialog">
            <div class="modal-dialog" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <a href="javascript:" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">×</span></a>
                        <h4 class="modal-title"><?php echo __('Do you want to save before leaving ?', 'tld'); ?></h4>
                    </div>
                    <div class="modal-body">
                        <p><?php echo __('Do you want to save the modifications you did before leaving ?', 'tld'); ?></p>
                    </div>
                    <div class="modal-footer">
                        <button type="button" onclick="tld_toggleSavePanel();" class="btn btn-primary"><span class="glyphicon glyphicon-ok"></span><?php echo __('Yes', 'tld'); ?></button>
                        <button type="button" class="btn btn-default" data-dismiss="modal" onclick="tld_leaveConfirm();"><span class="glyphicon glyphicon-remove"></span><?php echo __('No', 'tld'); ?></button>
                    </div>
                </div><!-- /.modal-content -->
            </div><!-- /.modal-dialog -->
        </div><!-- /.modal -->

        <div id="tld_winSaveApplyDialog" class="modal fade" tabindex="-1" role="dialog">
            <div class="modal-dialog" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <a href="javascript:" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">×</span></a>
                        <h4 class="modal-title"><?php echo __('Apply styles to the current element ?', 'tld'); ?></h4>
                    </div>
                    <div class="modal-body">
                        <p><?php echo __('Do you want to apply the modified styles to the current element before saving ?', 'tld'); ?></p>
                    </div>
                    <div class="modal-footer">
                        <button type="button" data-dismiss="modal" onclick="tld_saveCurrentElement();
                                setTimeout(tld_confirmSaveStyles, 500);" class="btn btn-primary"><span class="glyphicon glyphicon-ok"></span><?php echo __('Yes', 'tld'); ?></button>
                        <button type="button" class="btn btn-default" data-dismiss="modal" onclick="tld_confirmSaveStyles();"><span class="glyphicon glyphicon-remove"></span><?php echo __('No', 'tld'); ?></button>
                    </div>
                </div><!-- /.modal-content -->
            </div><!-- /.modal-dialog -->
        </div><!-- /.modal -->

        <div id="tld_winSaveBeforeEditDialog" class="modal fade" tabindex="-1" role="dialog">
            <div class="modal-dialog" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <a href="javascript:" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">×</span></a>
                        <h4 class="modal-title"><?php echo __('Save styles before editing ?', 'tld'); ?></h4>
                    </div>
                    <div class="modal-body">
                        <p><?php echo __('Do you want to save the modified styles before editing the css code ?', 'tld'); ?></p>
                    </div>
                    <div class="modal-footer">
                        <button type="button" data-dismiss="modal" onclick="tld_confirmSaveStylesBeforeEdit();" class="btn btn-primary"><span class="glyphicon glyphicon-ok"></span><?php echo __('Yes', 'tld'); ?></button>
                        <button type="button" class="btn btn-default" data-dismiss="modal" onclick="tld_editCSS();"><span class="glyphicon glyphicon-remove"></span><?php echo __('No', 'tld'); ?></button>
                    </div>
                </div><!-- /.modal-content -->
            </div><!-- /.modal-dialog -->
        </div><!-- /.modal -->

        <div id="tld_winResetStylesDialog" class="modal fade" tabindex="-1" role="dialog">
            <div class="modal-dialog" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <a href="javascript:" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">×</span></a>
                        <h4 class="modal-title"><?php echo __('Reset the styles', 'tld'); ?></h4>
                    </div>
                    <div class="modal-body">
                        <p><?php echo __('Do you want to remove only the styles modified since the last save, or all styles that were created with this tool until now ?', 'tld'); ?></p>
                    </div>
                    <div class="modal-footer">
                        <button type="button" data-dismiss="modal" onclick="tld_resetSessionStyles();" class="btn btn-primary"><span class="glyphicon glyphicon-ok"></span><?php echo __('Only this session', 'tld'); ?></button>
                        <button type="button" class="btn btn-warning" data-dismiss="modal" onclick="tld_resetAllStyles();"><span class="glyphicon glyphicon-remove"></span><?php echo __('All styles from the beginning', 'tld'); ?></button>
                        <button type="button" style="display: none;" class="btn btn-default" data-dismiss="modal" onclick=""><span class="glyphicon glyphicon-remove"></span><?php echo __('Cancel', 'lfb'); ?></button>
                    </div>
                </div><!-- /.modal-content -->
            </div><!-- /.modal-dialog -->
        </div><!-- /.modal -->


        <div id="tld_winEditCSSDialog" class="modal fade">
            <div class="modal-dialog" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <a href="javascript:" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">×</span></a>
                        <h4 class="modal-title"><?php echo __('Edit the generated CSS code', 'tld'); ?></h4>
                    </div>
                    <div class="modal-body">
                        <textarea id="tld_editCssField"></textarea>
                    </div>
                    <div class="modal-footer">
                        <button type="button" data-dismiss="modal" onclick="tld_saveEditedCSS();" class="btn btn-primary"><span class="glyphicon glyphicon-floppy-disk"></span><?php echo __('Save', 'tld'); ?></button>
                        <button type="button" style="display: none;"  class="btn btn-default" data-dismiss="modal" onclick=""><span class="glyphicon glyphicon-remove"></span><?php echo __('Cancel', 'lfb'); ?></button>
                    </div>
                </div><!-- /.modal-content -->
            </div><!-- /.modal-dialog -->
        </div><!-- /.modal -->

        <div id="tld_savePanel" class="tld_collapsed">
            <div id="tld_savePanelHeader">
                <a href="javascript:" id="tld_savePanelToggleBtn" data-toggle="tooltip" data-placement="left" title="<?php echo __('Save the modifications', 'tld') ?>" onclick="tld_toggleSavePanel();" class="btn btn-circle btn-inverse">
                    <span class="glyphicon glyphicon-floppy-disk"></span>
                </a>
                <a href="javascript:" id="tld_savePanelExportBtn" data-toggle="tooltip" data-placement="left" title="<?php echo __('Edit the generated CSS code', 'tld') ?>" onclick="tld_openSaveBeforeEditDialog();" class="btn btn-circle btn-inverse">
                    <span class="glyphicon glyphicon-pencil"></span>
                </a>
                <a href="javascript:" id="tld_savePanelResetBtn" onclick="tld_resetStyles();" data-toggle="tooltip" data-placement="left" title="<?php echo __('Reset styles', 'tld') ?>"  class="btn btn-circle btn-inverse">
                    <span class="glyphicon glyphicon-trash" style="margin-left:-2px;"></span>
                </a>
                <a href="javascript:" data-dismiss="modal" id="tld_leaveBtn" onclick="tld_leave();" data-toggle="tooltip" data-placement="left" title="<?php echo __('Return to the form management', 'tld') ?>"  class="btn btn-circle btn-inverse">
                    <span class="glyphicon glyphicon-remove"  style="margin-left:1px;"></span>
                </a>

            </div>
            <div id="tld_savePanelBody">
            </div>
        </div>
        <div id="tld_tdgnPanel">
            <div id="tld_tdgnPanelHeader">
                <span class="fa fa-magic"></span><span id="tld_tdgnPanelHeaderTitle"><?php echo __('Form designer', 'tld'); ?></span>
                <a href="javascript:" id="tld_tdgnPanelToggleBtn" onclick="tld_tdgn_toggleTdgnPanel();" class="btn btn-circle btn-inverse"><span class="glyphicon glyphicon-chevron-left"></span></a>
            </div>
            <div id="tld_tdgnPanelBody" class="tld_scroll">
                <a href="javascript:"  onclick="tld_prepareSelectElement();" id="tld_tdgn_selectElementBtn" class="btn btn-lg btn-primary">
                    <span class="glyphicon glyphicon-hand-up"></span>
                    <?php echo __('Select an element', 'tld'); ?>
                </a>
                <div class="tld_tdgn_section" data-title="<?php echo __('Selection', 'tld'); ?>">
                    <div class="tld_tdgn_sectionBody">
                        <div class="form-group">
                            <label for="tld_tdgn_selectedElement">
                                <?php echo __('Selected element', 'tld'); ?> :
                            </label>
                            <div id="tld_tdgn_selectedElement"></div>
                        </div>
                        <div class="form-group">
                            <label for="tld_tdgn_applyModifsTo">
                                <?php echo __('Apply modifications to', 'tld'); ?> :
                            </label>
                            <select id="tld_tdgn_applyModifsTo" name="applyModifsTo" class="tld_selectpicker form-control">
                                <option value="onlyThis"><?php echo __('Only this element', 'tld'); ?></option>
                                <option value="cssClasses"><?php echo __('All elements having CSS classes', 'tld'); ?></option>
                            </select>
                        </div>
                        <div class="form-group" style="display: none;">
                            <label for="tld_tdgn_applyToClasses"><?php echo __('Enter the target CSS classes separated by spaces', 'tld'); ?></label>
                            <input type="text" id="tld_tdgn_applyToClasses"  class="form-control" />
                        </div>
                        <div class="form-group"  style="display: none;">
                            <label for="tld_tdgn_applyScope">
                                <?php echo __('Limit modifications to', 'tld'); ?> :
                            </label>
                            <select id="tld_tdgn_applyScope" class="form-control tld_selectpicker">
                                <option value="all"><?php echo __('All pages', 'tld'); ?></option>
                                <option value="page"><?php echo __('This page only', 'tld'); ?></option>
                                <option value="container"><?php echo __('The container having the css class', 'tld'); ?></option>
                            </select>
                        </div>
                        <div class="form-group" style="display: none;">
                            <label for="tld_tdgn_scopeContainerClass"><?php echo __('Enter the target CSS class', 'tld'); ?></label>
                            <input type="text" id="tld_tdgn_scopeContainerClass"  class="form-control" />
                        </div>
                    </div>
                </div>
                <div class="tld_tdgn_section" data-title="<?php echo __('Styles', 'tld'); ?>">
                    <div class="tld_tdgn_sectionBar">
                        <a href="javascript:" class="tld_active" onclick="tld_changeDeviceMode('all');" data-devicebtn="all"
                           data-toggle="tooltip" data-placement="top" title="<?php echo __('All devices', 'tld') ?>" >
                            <span class="fa fa-desktop"></span>
                            <span class="fa fa-tablet"></span>
                            <span class="fa fa-mobile"></span>
                        </a>
                        <a href="javascript:" onclick="tld_changeDeviceMode('desktop');"  data-devicebtn="desktop"
                           data-toggle="tooltip" data-placement="top" title="<?php echo __('Desktop only', 'tld') ?>">
                            <span class="fa fa-desktop"></span>
                        </a>
                        <a href="javascript:" onclick="tld_changeDeviceMode('desktopTablet');"  data-devicebtn="desktopTablet"
                           data-toggle="tooltip" data-placement="top" title="<?php echo __('Desktop & Tablets', 'tld') ?>">
                            <span class="fa fa-desktop"></span>
                            <span class="fa fa-tablet"></span>
                        </a>
                        <a href="javascript:" onclick="tld_changeDeviceMode('tabletPhone');"  data-devicebtn="tabletPhone"
                           data-toggle="tooltip" data-placement="top" title="<?php echo __('Tablets & Phones', 'tld') ?>">
                            <span class="fa fa-tablet"></span>
                            <span class="fa fa-mobile"></span>
                        </a>
                        <a href="javascript:" onclick="tld_changeDeviceMode('tablet');"  data-devicebtn="tablet" 
                           data-toggle="tooltip" data-placement="top" title="<?php echo __('Tablets only', 'tld') ?>">
                            <span class="fa fa-tablet"></span>
                        </a>
                        <a href="javascript:" onclick="tld_changeDeviceMode('phone');"  data-devicebtn="phone" 
                           data-toggle="tooltip" data-placement="top" title="<?php echo __('Phones only', 'tld') ?>">
                            <span class="fa fa-mobile"></span>
                        </a>
                        <p style="text-align: center;margin-bottom: 0px; margin-top: 5px;">
                            <select id="tld_stateSelect" class="form-group tld_selectpicker">
                                <option value="default"><?php echo __('Default state', 'tld'); ?></option>
                                <option value="hover"><?php echo __('Mouse over state', 'tld'); ?></option>
                                <option value="focus"><?php echo __('Focus state', 'tld'); ?></option>
                            </select>
                        </p>
                    </div>
                    <div class="tld_tdgn_sectionBody" style="padding-top: 4px;">
                        <div class="panel-group">
                            <div class="panel panel-default" data-style="background">
                                <div class="panel-heading">
                                    <h4 class="panel-title">
                                        <a data-toggle="collapse" href="#tdgn-style-background"><?php echo __('Background', 'tld'); ?></a>
                                    </h4>
                                </div>
                                <div id="tdgn-style-background" class="panel-collapse collapse">
                                    <div class="panel-body">
                                        <div class="form-group">
                                            <label><?php echo __('Background type', 'tld'); ?></label>
                                            <select id="tld_styleBackgroundType" class="form-control tld_selectpicker">
                                                <option value=""><?php echo __('Nothing', 'tld'); ?></option>
                                                <option value="color"><?php echo __('Color', 'tld'); ?></option>
                                                <option value="image"><?php echo __('Image', 'tld'); ?></option>
                                            </select>
                                        </div>
                                        <div id="tld_styleBackgroundType_colorToggle" data-dependson="backgroundType">   
                                            <div class="form-group">                                             
                                                <label><?php echo __('Background color', 'tld'); ?></label>
                                                <input type="text" id="tld_styleBackgroundType_color" class="form-control tld_colorpick" />
                                            </div>
                                            <div class="form-group">                                             
                                                <label><?php echo __('Background opacity', 'tld'); ?></label>
                                                <div id="tld_styleBackgroundType_colorAlpha" class="tld_slider" data-min="0" data-max="1" data-step="0.1"></div>
                                            </div>
                                        </div>
                                        <div id="tld_styleBackgroundType_imageToggle" data-dependson="backgroundType" style="display: none;">   
                                            <div class="form-group">                                             
                                                <label><?php echo __('Image url', 'tld'); ?></label>
                                                <input type="text" id="tld_styleBackgroundType_imageUrl" class="form-control" style="width: 137px; display: inline-block;"/>
                                                <a href="javascript:" onclick="lfb_openUploadPic(jQuery('#tld_styleBackgroundType_imageUrl'));" class="wos_imageBtn btn btn-default" ><span class="glyphicon glyphicon-cloud-download"></span></a>
                                            </div>  
                                            <div class="form-group">                                             
                                                <label><?php echo __('Image size', 'tld'); ?></label>
                                                <select id="tld_styleBackgroundType_imageSize" class="form-control tld_selectpicker" >
                                                    <option value="initial"><?php echo __('Initial', 'tld'); ?></option>
                                                    <option value="contain"><?php echo __('Contain', 'tld'); ?></option>
                                                    <option value="cover"><?php echo __('Cover', 'tld'); ?></option>
                                                </select>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="panel panel-default" data-style="background">
                                <div class="panel-heading">
                                    <h4 class="panel-title">
                                        <a data-toggle="collapse" href="#tdgn-style-borders"><?php echo __('Borders', 'tld'); ?></a>
                                    </h4>
                                </div>
                                <div id="tdgn-style-borders" class="panel-collapse collapse">
                                    <div class="panel-body">                                            
                                        <div class="form-group">                                             
                                            <label><?php echo __('Border size', 'tld'); ?></label>
                                            <div id="tld_style_borderSize" class="tld_slider tld_sliderHasField" data-min="0" data-max="32" ></div>
                                            <input type="number" class="tld_sliderField form-control" />
                                        </div>
                                        <div class="form-group">                                             
                                            <label><?php echo __('Border style', 'tld'); ?></label>
                                            <select id="tld_style_borderStyle" class="form-control tld_selectpicker" >
                                                <option value="none"><?php echo __('None', 'tld'); ?></option>
                                                <option value="solid"><?php echo __('Solid', 'tld'); ?></option>
                                                <option value="dashed"><?php echo __('Dashed', 'tld'); ?></option>
                                                <option value="dotted"><?php echo __('Dotted', 'tld'); ?></option>
                                                <option value="double"><?php echo __('Double', 'tld'); ?></option>
                                                <option value="inset"><?php echo __('Inset', 'tld'); ?></option>
                                            </select>
                                        </div>
                                        <div class="form-group">                                             
                                            <label><?php echo __('Border color', 'tld'); ?></label>
                                            <input type="text" id="tld_style_borderColor" class="form-control tld_colorpick" />
                                        </div>
                                        <div class="form-group">                                             
                                            <label><?php echo __('Top left radius', 'tld'); ?></label>
                                            <div id="tld_style_borderRadiusTopLeft" class="tld_slider tld_sliderHasField" data-min="0" data-max="64" ></div>
                                            <input type="number" class="tld_sliderField form-control" />
                                        </div>
                                        <div class="form-group">                                             
                                            <label><?php echo __('Top right radius', 'tld'); ?></label>
                                            <div id="tld_style_borderRadiusTopRight" class="tld_slider tld_sliderHasField" data-min="0" data-max="64" ></div>
                                            <input type="number" class="tld_sliderField form-control" />
                                        </div>
                                        <div class="form-group">                                             
                                            <label><?php echo __('Bottom left radius', 'tld'); ?></label>
                                            <div id="tld_style_borderRadiusBottomLeft" class="tld_slider tld_sliderHasField" data-min="0" data-max="64" ></div>
                                            <input type="number" class="tld_sliderField form-control" />
                                        </div>
                                        <div class="form-group">                                             
                                            <label><?php echo __('Bottom right radius', 'tld'); ?></label>
                                            <div id="tld_style_borderRadiusBottomRight" class="tld_slider tld_sliderHasField" data-min="0" data-max="64" ></div>
                                            <input type="number" class="tld_sliderField form-control" />
                                        </div>
                                    </div>
                                </div>
                            </div>                           



                            <div class="panel panel-default" data-style="size">
                                <div class="panel-heading">
                                    <h4 class="panel-title">
                                        <a data-toggle="collapse" href="#tdgn-style-margins"><?php echo __('Margins', 'tld'); ?></a>
                                    </h4>
                                </div>
                                <div id="tdgn-style-margins" class="panel-collapse collapse">
                                    <div class="panel-body"> 

                                        <div class="form-group">                                             
                                            <label><?php echo __('Margin top', 'tld'); ?></label>
                                            <select id="tld_style_marginTypeTop" class="form-control tld_selectpicker" >
                                                <option value="auto"><?php echo __('Auto', 'tld'); ?></option>
                                                <option value="fixed"><?php echo __('Fixed', 'tld'); ?></option>
                                                <option value="flexible"><?php echo __('Flexible', 'tld'); ?></option>
                                            </select>
                                        </div>
                                        <div class="form-group">                                             
                                            <label><?php echo __('Top', 'tld'); ?></label>
                                            <div id="tld_style_marginTop" class="tld_slider tld_sliderHasField" data-min="0" data-max="800"></div>
                                            <input type="number" class="tld_sliderField form-control" />
                                        </div>
                                        <div class="form-group">                                             
                                            <label><?php echo __('Top', 'tld'); ?></label>
                                            <div id="tld_style_marginTopFlex" class="tld_slider tld_sliderHasField" data-min="0" data-max="100"></div>
                                            <input type="number" class="tld_sliderField form-control" />
                                        </div>     

                                        <div class="form-group">                                             
                                            <label><?php echo __('Margin bottom', 'tld'); ?></label>
                                            <select id="tld_style_marginTypeBottom" class="form-control tld_selectpicker" >
                                                <option value="auto"><?php echo __('Auto', 'tld'); ?></option>
                                                <option value="fixed"><?php echo __('Fixed', 'tld'); ?></option>
                                                <option value="flexible"><?php echo __('Flexible', 'tld'); ?></option>
                                            </select>
                                        </div>   
                                        <div class="form-group">                                             
                                            <label><?php echo __('Bottom', 'tld'); ?></label>
                                            <div id="tld_style_marginBottom" class="tld_slider tld_sliderHasField" data-min="0" data-max="800"></div>
                                            <input type="number" class="tld_sliderField form-control" />
                                        </div>
                                        <div class="form-group">                                             
                                            <label><?php echo __('Bottom', 'tld'); ?></label>
                                            <div id="tld_style_marginBottomFlex" class="tld_slider tld_sliderHasField" data-min="0" data-max="100"></div>
                                            <input type="number" class="tld_sliderField form-control" />
                                        </div>

                                        <div class="form-group">                                             
                                            <label><?php echo __('Margin left', 'tld'); ?></label>
                                            <select id="tld_style_marginTypeLeft" class="form-control tld_selectpicker" >
                                                <option value="auto"><?php echo __('Auto', 'tld'); ?></option>
                                                <option value="fixed"><?php echo __('Fixed', 'tld'); ?></option>
                                                <option value="flexible"><?php echo __('Flexible', 'tld'); ?></option>
                                            </select>
                                        </div>

                                        <div class="form-group">                                             
                                            <label><?php echo __('Left', 'tld'); ?></label>
                                            <div id="tld_style_marginLeft" class="tld_slider tld_sliderHasField" data-min="0" data-max="800"></div>
                                            <input type="number" class="tld_sliderField form-control" />
                                        </div>
                                        <div class="form-group">                                             
                                            <label><?php echo __('Left', 'tld'); ?></label>
                                            <div id="tld_style_marginLeftFlex" class="tld_slider tld_sliderHasField" data-min="0" data-max="100"></div>
                                            <input type="number" class="tld_sliderField form-control" />
                                        </div>


                                        <div class="form-group">                                             
                                            <label><?php echo __('Margin right', 'tld'); ?></label>
                                            <select id="tld_style_marginTypeRight" class="form-control tld_selectpicker" >
                                                <option value="auto"><?php echo __('Auto', 'tld'); ?></option>
                                                <option value="fixed"><?php echo __('Fixed', 'tld'); ?></option>
                                                <option value="flexible"><?php echo __('Flexible', 'tld'); ?></option>
                                            </select>
                                        </div>

                                        <div class="form-group">                                             
                                            <label><?php echo __('Right', 'tld'); ?></label>
                                            <div id="tld_style_marginRight" class="tld_slider tld_sliderHasField" data-min="0" data-max="800"></div>
                                            <input type="number" class="tld_sliderField form-control" />
                                        </div>
                                        <div class="form-group">                                             
                                            <label><?php echo __('Right', 'tld'); ?></label>
                                            <div id="tld_style_marginRightFlex" class="tld_slider tld_sliderHasField" data-min="0" data-max="100"></div>
                                            <input type="number" class="tld_sliderField form-control" />
                                        </div>

                                    </div>
                                </div>
                            </div>

                            <div class="panel panel-default" data-style="size">
                                <div class="panel-heading">
                                    <h4 class="panel-title">
                                        <a data-toggle="collapse" href="#tdgn-style-paddings"><?php echo __('Paddings', 'tld'); ?></a>
                                    </h4>
                                </div>
                                <div id="tdgn-style-paddings" class="panel-collapse collapse">
                                    <div class="panel-body"> 

                                        <div class="form-group">                                             
                                            <label><?php echo __('Padding top', 'tld'); ?></label>
                                            <select id="tld_style_paddingTypeTop" class="form-control tld_selectpicker" >
                                                <option value="auto"><?php echo __('Auto', 'tld'); ?></option>
                                                <option value="fixed"><?php echo __('Fixed', 'tld'); ?></option>
                                                <option value="flexible"><?php echo __('Flexible', 'tld'); ?></option>
                                            </select>
                                        </div>
                                        <div class="form-group">                                             
                                            <label><?php echo __('Top', 'tld'); ?></label>
                                            <div id="tld_style_paddingTop" class="tld_slider tld_sliderHasField" data-min="0" data-max="400"></div>
                                            <input type="number" class="tld_sliderField form-control" />
                                        </div>
                                        <div class="form-group">                                             
                                            <label><?php echo __('Top', 'tld'); ?></label>
                                            <div id="tld_style_paddingTopFlex" class="tld_slider tld_sliderHasField" data-min="0" data-max="100"></div>
                                            <input type="number" class="tld_sliderField form-control" />
                                        </div>     

                                        <div class="form-group">                                             
                                            <label><?php echo __('Padding bottom', 'tld'); ?></label>
                                            <select id="tld_style_paddingTypeBottom" class="form-control tld_selectpicker" >
                                                <option value="auto"><?php echo __('Auto', 'tld'); ?></option>
                                                <option value="fixed"><?php echo __('Fixed', 'tld'); ?></option>
                                                <option value="flexible"><?php echo __('Flexible', 'tld'); ?></option>
                                            </select>
                                        </div>   
                                        <div class="form-group">                                             
                                            <label><?php echo __('Bottom', 'tld'); ?></label>
                                            <div id="tld_style_paddingBottom" class="tld_slider tld_sliderHasField" data-min="0" data-max="400"></div>
                                            <input type="number" class="tld_sliderField form-control" />
                                        </div>
                                        <div class="form-group">                                             
                                            <label><?php echo __('Bottom', 'tld'); ?></label>
                                            <div id="tld_style_paddingBottomFlex" class="tld_slider tld_sliderHasField" data-min="0" data-max="100"></div>
                                            <input type="number" class="tld_sliderField form-control" />
                                        </div>

                                        <div class="form-group">                                             
                                            <label><?php echo __('Padding left', 'tld'); ?></label>
                                            <select id="tld_style_paddingTypeLeft" class="form-control tld_selectpicker" >
                                                <option value="auto"><?php echo __('Auto', 'tld'); ?></option>
                                                <option value="fixed"><?php echo __('Fixed', 'tld'); ?></option>
                                                <option value="flexible"><?php echo __('Flexible', 'tld'); ?></option>
                                            </select>
                                        </div>

                                        <div class="form-group">                                             
                                            <label><?php echo __('Left', 'tld'); ?></label>
                                            <div id="tld_style_paddingLeft" class="tld_slider tld_sliderHasField" data-min="0" data-max="400"></div>
                                            <input type="number" class="tld_sliderField form-control" />
                                        </div>
                                        <div class="form-group">                                             
                                            <label><?php echo __('Left', 'tld'); ?></label>
                                            <div id="tld_style_paddingLeftFlex" class="tld_slider tld_sliderHasField" data-min="0" data-max="100"></div>
                                            <input type="number" class="tld_sliderField form-control" />
                                        </div>


                                        <div class="form-group">                                             
                                            <label><?php echo __('Padding right', 'tld'); ?></label>
                                            <select id="tld_style_paddingTypeRight" class="form-control tld_selectpicker" >
                                                <option value="auto"><?php echo __('Auto', 'tld'); ?></option>
                                                <option value="fixed"><?php echo __('Fixed', 'tld'); ?></option>
                                                <option value="flexible"><?php echo __('Flexible', 'tld'); ?></option>
                                            </select>
                                        </div>

                                        <div class="form-group">                                             
                                            <label><?php echo __('Right', 'tld'); ?></label>
                                            <div id="tld_style_paddingRight" class="tld_slider tld_sliderHasField" data-min="0" data-max="400"></div>
                                            <input type="number" class="tld_sliderField form-control" />
                                        </div>
                                        <div class="form-group">                                             
                                            <label><?php echo __('Right', 'tld'); ?></label>
                                            <div id="tld_style_paddingRightFlex" class="tld_slider tld_sliderHasField" data-min="0" data-max="100"></div>
                                            <input type="number" class="tld_sliderField form-control" />
                                        </div>

                                    </div>
                                </div>
                            </div>

                            <div class="panel panel-default" data-style="size">
                                <div class="panel-heading">
                                    <h4 class="panel-title">
                                        <a data-toggle="collapse" href="#tdgn-style-position"><?php echo __('Position', 'tld'); ?></a>
                                    </h4>
                                </div>
                                <div id="tdgn-style-position" class="panel-collapse collapse">
                                    <div class="panel-body">  
                                        <div class="form-group">                                             
                                            <label><?php echo __('Display mode', 'tld'); ?></label>
                                            <select id="tld_style_display" class="form-control tld_selectpicker" >
                                                <option value="inherit"><?php echo __('Default', 'tld'); ?></option>  
                                                <option value="block"><?php echo __('Block', 'tld'); ?></option> 
                                                <option value="inline"><?php echo __('Inline', 'tld'); ?></option>
                                                <option value="inline-block"><?php echo __('Inline block', 'tld'); ?></option>      
                                                <option value="none"><?php echo __('None', 'tld'); ?></option>                                                
                                            </select>
                                        </div>
                                        <div class="form-group">                                             
                                            <label><?php echo __('Float', 'tld'); ?></label>
                                            <select id="tld_style_float" class="form-control tld_selectpicker" >
                                                <option value="none"><?php echo __('None', 'tld'); ?></option>  
                                                <option value="left"><?php echo __('Left', 'tld'); ?></option>
                                                <option value="right"><?php echo __('Right', 'tld'); ?></option>                                        
                                            </select>
                                        </div>
                                        <div class="form-group">                                             
                                            <label><?php echo __('Clear', 'tld'); ?></label>
                                            <select id="tld_style_clear" class="form-control tld_selectpicker" >
                                                <option value="none"><?php echo __('None', 'tld'); ?></option>  
                                                <option value="both"><?php echo __('Both', 'tld'); ?></option>
                                                <option value="left"><?php echo __('Left', 'tld'); ?></option>
                                                <option value="right"><?php echo __('Right', 'tld'); ?></option>                                        
                                            </select>
                                        </div>
                                        <div class="form-group">                                             
                                            <label><?php echo __('Position type', 'tld'); ?></label>
                                            <select id="tld_style_position" class="form-control tld_selectpicker" >
                                                <option value="absolute"><?php echo __('Absolute', 'tld'); ?></option>
                                                <option value="fixed"><?php echo __('Fixed', 'tld'); ?></option>
                                                <option value="relative"><?php echo __('Relative', 'tld'); ?></option>
                                                <option value="static"><?php echo __('Static', 'tld'); ?></option>
                                            </select>
                                        </div>
                                        <div class="form-group">                                             
                                            <label><?php echo __('Position left', 'tld'); ?></label>
                                            <select id="tld_style_positionLeft" class="form-control tld_selectpicker" >
                                                <option value="auto"><?php echo __('Auto', 'tld'); ?></option>
                                                <option value="fixed"><?php echo __('Fixed', 'tld'); ?></option>
                                                <option value="flexible"><?php echo __('Flexible', 'tld'); ?></option>
                                            </select>
                                        </div>
                                        <div class="form-group">                                             
                                            <label><?php echo __('Left', 'tld'); ?></label>
                                            <div id="tld_style_left" class="tld_slider tld_sliderHasField" data-min="-1920" data-max="1920"></div>
                                            <input type="number" class="tld_sliderField form-control" />
                                        </div>
                                        <div class="form-group">                                             
                                            <label><?php echo __('Left', 'tld'); ?></label>
                                            <div id="tld_style_leftFlex" class="tld_slider tld_sliderHasField" data-min="0" data-max="100"></div>
                                            <input type="number" class="tld_sliderField form-control" />
                                        </div>
                                        <div class="form-group">                                             
                                            <label><?php echo __('Position top', 'tld'); ?></label>
                                            <select id="tld_style_positionTop" class="form-control tld_selectpicker" >
                                                <option value="auto"><?php echo __('Auto', 'tld'); ?></option>
                                                <option value="fixed"><?php echo __('Fixed', 'tld'); ?></option>
                                                <option value="flexible"><?php echo __('Flexible', 'tld'); ?></option>
                                            </select>
                                        </div>
                                        <div class="form-group">                                             
                                            <label><?php echo __('Top', 'tld'); ?></label>
                                            <div id="tld_style_top" class="tld_slider tld_sliderHasField" data-min="-1080" data-max="1080"></div>
                                            <input type="number" class="tld_sliderField form-control" />
                                        </div>
                                        <div class="form-group">                                             
                                            <label><?php echo __('Top', 'tld'); ?></label>
                                            <div id="tld_style_topFlex" class="tld_slider tld_sliderHasField" data-min="0" data-max="100"></div>
                                            <input type="number" class="tld_sliderField form-control" />
                                        </div>
                                        <div class="form-group">                                             
                                            <label><?php echo __('Position bottom', 'tld'); ?></label>
                                            <select id="tld_style_positionBottom" class="form-control tld_selectpicker" >
                                                <option value="auto"><?php echo __('Auto', 'tld'); ?></option>
                                                <option value="fixed"><?php echo __('Fixed', 'tld'); ?></option>
                                                <option value="flexible"><?php echo __('Flexible', 'tld'); ?></option>
                                            </select>
                                        </div>
                                        <div class="form-group">                                             
                                            <label><?php echo __('Bottom', 'tld'); ?></label>
                                            <div id="tld_style_bottom" class="tld_slider tld_sliderHasField" data-min="-1080" data-max="1080"></div>
                                            <input type="number" class="tld_sliderField form-control" />
                                        </div>
                                        <div class="form-group">                                             
                                            <label><?php echo __('Bottom', 'tld'); ?></label>
                                            <div id="tld_style_bottomFlex" class="tld_slider tld_sliderHasField" data-min="0" data-max="100"></div>
                                            <input type="number" class="tld_sliderField form-control" />
                                        </div>
                                        <div class="form-group">                                             
                                            <label><?php echo __('Position right', 'tld'); ?></label>
                                            <select id="tld_style_positionRight" class="form-control tld_selectpicker" >
                                                <option value="auto"><?php echo __('Auto', 'tld'); ?></option>
                                                <option value="fixed"><?php echo __('Fixed', 'tld'); ?></option>
                                                <option value="flexible"><?php echo __('Flexible', 'tld'); ?></option>
                                            </select>
                                        </div>
                                        <div class="form-group">                                             
                                            <label><?php echo __('Right', 'tld'); ?></label>
                                            <div id="tld_style_right" class="tld_slider tld_sliderHasField" data-min="-1920" data-max="1920"></div>
                                            <input type="number" class="tld_sliderField form-control" />
                                        </div>
                                        <div class="form-group">                                             
                                            <label><?php echo __('Right', 'tld'); ?></label>
                                            <div id="tld_style_rightFlex" class="tld_slider tld_sliderHasField" data-min="0" data-max="100"></div>
                                            <input type="number" class="tld_sliderField form-control" />
                                        </div>

                                    </div>
                                </div>
                            </div>

                            <div class="panel panel-default" data-style="size">
                                <div class="panel-heading">
                                    <h4 class="panel-title">
                                        <a data-toggle="collapse" href="#tdgn-style-size"><?php echo __('Size', 'tld'); ?></a>
                                    </h4>
                                </div>
                                <div id="tdgn-style-size" class="panel-collapse collapse">
                                    <div class="panel-body">     
                                        <div class="form-group">                                             
                                            <label><?php echo __('Width type', 'tld'); ?></label>
                                            <select id="tld_style_widthType" class="form-control tld_selectpicker" >
                                                <option value="auto"><?php echo __('Auto', 'tld'); ?></option>
                                                <option value="fixed"><?php echo __('Fixed', 'tld'); ?></option>
                                                <option value="flexible"><?php echo __('Flexible', 'tld'); ?></option>
                                            </select>
                                        </div>
                                        <div class="form-group">                                             
                                            <label><?php echo __('Width', 'tld'); ?></label>
                                            <div id="tld_style_width" class="tld_slider tld_sliderHasField" data-min="0" data-max="1920" ></div>
                                            <input type="number" class="tld_sliderField form-control" />
                                        </div>
                                        <div class="form-group">                                             
                                            <label><?php echo __('Width', 'tld'); ?></label>
                                            <div id="tld_style_widthFlex" class="tld_slider tld_sliderHasField" data-min="0" data-max="100" ></div>
                                            <input type="number" class="tld_sliderField form-control" />
                                        </div>
                                        <div class="form-group">                                             
                                            <label><?php echo __('Height type', 'tld'); ?></label>
                                            <select id="tld_style_heightType" class="form-control tld_selectpicker" >
                                                <option value="auto"><?php echo __('Auto', 'tld'); ?></option>
                                                <option value="fixed"><?php echo __('Fixed', 'tld'); ?></option>
                                                <option value="flexible"><?php echo __('Flexible', 'tld'); ?></option>
                                            </select>
                                        </div>
                                        <div class="form-group">                                             
                                            <label><?php echo __('Height', 'tld'); ?></label>
                                            <div id="tld_style_height" class="tld_slider tld_sliderHasField" data-min="0" data-max="1080" ></div>
                                            <input type="number" class="tld_sliderField form-control" />
                                        </div>
                                        <div class="form-group">                                             
                                            <label><?php echo __('Height', 'tld'); ?></label>
                                            <div id="tld_style_heightFlex" class="tld_slider tld_sliderHasField" data-min="0" data-max="100" ></div>
                                            <input type="number" class="tld_sliderField form-control" />
                                        </div>

                                    </div>
                                </div>
                            </div>

                            <div class="panel panel-default" data-style="size">
                                <div class="panel-heading">
                                    <h4 class="panel-title">
                                        <a data-toggle="collapse" href="#tdgn-style-visibility"><?php echo __('Scroll & Visibility', 'tld'); ?></a>
                                    </h4>
                                </div>
                                <div id="tdgn-style-visibility" class="panel-collapse collapse">
                                    <div class="panel-body"> 

                                        <div class="form-group">                                             
                                            <label><?php echo __('Scroll X', 'tld'); ?></label>
                                            <select id="tld_style_scrollX" class="form-control tld_selectpicker" >
                                                <option value="auto"><?php echo __('Auto', 'tld'); ?></option>
                                                <option value="hidden"><?php echo __('Hidden', 'tld'); ?></option>
                                                <option value="initial"><?php echo __('Initial', 'tld'); ?></option>
                                                <option value="overlay"><?php echo __('Overlay', 'tld'); ?></option>
                                                <option value="scroll"><?php echo __('Scroll', 'tld'); ?></option>
                                                <option value="visible"><?php echo __('Visible', 'tld'); ?></option>
                                            </select>
                                        </div>
                                        <div class="form-group">                                             
                                            <label><?php echo __('Scroll Y', 'tld'); ?></label>
                                            <select id="tld_style_scrollY" class="form-control tld_selectpicker" >
                                                <option value="auto"><?php echo __('Auto', 'tld'); ?></option>
                                                <option value="hidden"><?php echo __('Hidden', 'tld'); ?></option>
                                                <option value="initial"><?php echo __('Initial', 'tld'); ?></option>
                                                <option value="overlay"><?php echo __('Overlay', 'tld'); ?></option>
                                                <option value="scroll"><?php echo __('Scroll', 'tld'); ?></option>
                                                <option value="visible"><?php echo __('Visible', 'tld'); ?></option>
                                            </select>
                                        </div>
                                        <div class="form-group">                                             
                                            <label><?php echo __('Visibility', 'tld'); ?></label>
                                            <select id="tld_style_visibility" class="form-control tld_selectpicker" >
                                                <option value="hidden"><?php echo __('Hidden', 'tld'); ?></option>
                                                <option value="initial"><?php echo __('Initial', 'tld'); ?></option>
                                                <option value="visible"><?php echo __('Visible', 'tld'); ?></option>
                                            </select>
                                        </div>

                                        <div class="form-group">                                             
                                            <label><?php echo __('Opacity', 'tld'); ?></label>
                                            <div id="tld_style_opacity" class="tld_slider tld_sliderHasField" data-min="0" data-max="1" data-step="0.1" ></div>
                                            <input type="number" class="tld_sliderField form-control" />
                                        </div>

                                    </div>
                                </div>
                            </div>

                            <div class="panel panel-default" data-style="shadow">
                                <div class="panel-heading">
                                    <h4 class="panel-title">
                                        <a data-toggle="collapse" href="#tdgn-style-shadow"><?php echo __('Shadow', 'tld'); ?></a>
                                    </h4>
                                </div>
                                <div id="tdgn-style-shadow" class="panel-collapse collapse">
                                    <div class="panel-body">

                                        <div class="form-group">                                             
                                            <label><?php echo __('Shadow type', 'tld'); ?></label>
                                            <select id="tld_style_shadowType" class="form-control tld_selectpicker" >
                                                <option value="inside"><?php echo __('Inside', 'tld'); ?></option>
                                                <option value="none"><?php echo __('None', 'tld'); ?></option>
                                                <option value="outside"><?php echo __('Outside', 'tld'); ?></option>
                                            </select>
                                        </div>

                                        <div class="form-group">                                             
                                            <label><?php echo __('Size', 'tld'); ?></label>
                                            <div id="tld_style_shadowSize" class="tld_slider tld_sliderHasField" data-min="1" data-max="40" ></div>
                                            <input type="number" class="tld_sliderField form-control" />
                                        </div>
                                        <div class="form-group">                                             
                                            <label><?php echo __('Distance X', 'tld'); ?></label>
                                            <div id="tld_style_shadowX" class="tld_slider tld_sliderHasField" data-min="-40" data-max="40" ></div>
                                            <input type="number" class="tld_sliderField form-control" />
                                        </div>
                                        <div class="form-group">                                             
                                            <label><?php echo __('Distance Y', 'tld'); ?></label>
                                            <div id="tld_style_shadowY" class="tld_slider tld_sliderHasField" data-min="-40" data-max="40" ></div>
                                            <input type="number" class="tld_sliderField form-control" />
                                        </div>
                                        <div class="form-group">                                             
                                            <label><?php echo __('Color', 'tld'); ?></label>
                                            <input type="text" id="tld_style_shadowColor" class="form-control tld_colorpick" />
                                        </div>
                                        <div class="form-group">                                             
                                            <label><?php echo __('Opacity', 'tld'); ?></label>
                                            <div id="tld_style_shadowAlpha" class="tld_slider tld_sliderHasField" data-min="0" data-max="1" data-step="0.1" ></div>
                                            <input type="number" class="tld_sliderField form-control" />
                                        </div>

                                    </div>
                                </div>
                            </div>

                            <div class="panel panel-default" data-style="background">
                                <div class="panel-heading">
                                    <h4 class="panel-title">
                                        <a data-toggle="collapse" href="#tdgn-style-text"><?php echo __('Text', 'tld'); ?></a>
                                    </h4>
                                </div>
                                <div id="tdgn-style-text" class="panel-collapse collapse">
                                    <div class="panel-body">     
                                        <div class="form-group">                                                            
                                            <label></label>
                                            <select id="tld_style_fontFamily" class="form-control tld_selectpicker"><option data-default="true" value="Georgia, serif" data-fontname="georgia" >Georgia</option><option value="Helvetica Neue" data-default="true" data-fontname="helveticaneue">Helvetica Neue</option><option data-default="true" value="'Times New Roman', Times, serif" data-fontname="timesnewroman">Times New Roman</option><option value="Arial, Helvetica, sans-serif" data-default="true" data-fontname="arial">Arial</option><option value="'Arial Black', Gadget, sans-serif" data-default="true" data-fontname="arialblack">Arial Black</option><option data-default="true" value="Impact, Charcoal, sans-serif" data-fontname="impact">Impact</option><option data-default="true" value="Tahoma, Geneva, sans-serif" data-fontname="tahoma">Tahoma</option><option value="Verdana, Geneva, sans-serif" data-fontname="verdana">Verdana</option></select>
                                        </div>
                                        <div class="form-group">                                             
                                            <label><?php echo __('Font size', 'tld'); ?></label>
                                            <div id="tld_style_fontSize" class="tld_slider tld_sliderHasField" data-min="1" data-max="128" ></div>
                                            <input type="number" class="tld_sliderField form-control" />
                                        </div>
                                        <div class="form-group">                                             
                                            <label><?php echo __('Alignment', 'tld'); ?></label>
                                            <select id="tld_style_textAlign" class="form-control tld_selectpicker" >
                                                <option value="auto"><?php echo __('Auto', 'tld'); ?></option>
                                                <option value="left"><?php echo __('Left', 'tld'); ?></option>
                                                <option value="right"><?php echo __('Right', 'tld'); ?></option>
                                                <option value="justify"><?php echo __('Justify', 'tld'); ?></option>
                                            </select>
                                        </div>
                                        <div class="form-group">                                             
                                            <label><?php echo __('Line height type', 'tld'); ?></label>
                                            <select id="tld_style_lineHeightType" class="form-control tld_selectpicker" >
                                                <option value="fixed"><?php echo __('Fixed', 'tld'); ?></option>
                                                <option value="flexible"><?php echo __('Flexible', 'tld'); ?></option>
                                            </select>
                                        </div>
                                        <div class="form-group">                                             
                                            <label><?php echo __('Line height', 'tld'); ?></label>
                                            <div id="tld_style_lineHeight" class="tld_slider tld_sliderHasField" data-min="0" data-max="128" ></div>
                                            <input type="number" class="tld_sliderField form-control" />
                                        </div>
                                        <div class="form-group">                                             
                                            <label><?php echo __('Line height', 'tld'); ?></label>
                                            <div id="tld_style_lineHeightFlex" class="tld_slider tld_sliderHasField" data-min="0" data-max="100" ></div>
                                            <input type="number" class="tld_sliderField form-control" />
                                        </div>

                                        <div class="form-group">                                             
                                            <label><?php echo __('Text style', 'tld'); ?></label>
                                            <select id="tld_style_fontStyle" class="form-control tld_selectpicker" multiple>
                                                <option value="bold"><?php echo __('Bold', 'tld'); ?></option>
                                                <option value="italic"><?php echo __('Italic', 'tld'); ?></option>
                                                <option value="underline"><?php echo __('Underline', 'tld'); ?></option>
                                            </select>
                                        </div>
                                        <div class="form-group">                                             
                                            <label><?php echo __('Text color', 'tld'); ?></label>
                                            <input type="text" id="tld_style_fontColor" class="form-control tld_colorpick" />
                                        </div>
                                    </div>
                                </div>
                            </div>              


                            <div class="panel panel-default" data-style="shadow">
                                <div class="panel-heading">
                                    <h4 class="panel-title">
                                        <a data-toggle="collapse" href="#tdgn-style-textShadow"><?php echo __('Text shadow', 'tld'); ?></a>
                                    </h4>
                                </div>
                                <div id="tdgn-style-textShadow" class="panel-collapse collapse">
                                    <div class="panel-body">

                                        <div class="form-group">                                             
                                            <label><?php echo __('Distance X', 'tld'); ?></label>
                                            <div id="tld_style_textShadowX" class="tld_slider tld_sliderHasField" data-min="0" data-max="40" ></div>
                                            <input type="number" class="tld_sliderField form-control" />
                                        </div>
                                        <div class="form-group">                                             
                                            <label><?php echo __('Distance Y', 'tld'); ?></label>
                                            <div id="tld_style_textShadowY" class="tld_slider tld_sliderHasField" data-min="0" data-max="40" ></div>
                                            <input type="number" class="tld_sliderField form-control" />
                                        </div>
                                        <div class="form-group">                                             
                                            <label><?php echo __('Color', 'tld'); ?></label>
                                            <input type="text" id="tld_style_textShadowColor" class="form-control tld_colorpick" />
                                        </div>
                                        <div class="form-group">                                             
                                            <label><?php echo __('Opacity', 'tld'); ?></label>
                                            <div id="tld_style_textShadowAlpha" class="tld_slider tld_sliderHasField" data-min="0" data-max="1" data-step="0.1" ></div>
                                            <input type="number" class="tld_sliderField form-control" step="0.1" />
                                        </div>

                                    </div>
                                </div>
                            </div>


                        </div>
                    </div>
                </div>
                <a href="javascript:" onclick="tld_saveCurrentElement();" data-toggle="tooltip" data-placement="right" title="<?php echo __('Apply these styles to the current element', 'tld'); ?>" id="tld_confirmStylesBtn" class="btn btn-lg btn-primary">
                    <span class="glyphicon glyphicon-ok"></span>
                    <?php echo __('Apply', 'tld'); ?>
                </a>
            </div>
        </div>
        <iframe src="<?php echo 'viewForm.php?lfb_action=preview&form=' . $form->id; ?>" id="tld_tdgnFrame"></iframe>

        <div id="tld_tdgnInspector" class="tld_collapsed">
            <div id="tld_tdgnInspectorHeader">
                <span class="glyphicon glyphicon-eye-open"></span><span id="tld_tdgnInspectorHeaderTitle"><?php echo __('Inspector', 'tld'); ?></span>
                <a href="javascript:" id="tld_tdgnInspectorToggleBtn" onclick="tld_tdgn_toggleInspector();" class="btn btn-circle"><span class="glyphicon glyphicon-chevron-up"></span></a>
            </div>
            <div id="tld_tdgnInspectorBody" class="tld_scroll">

            </div>
        </div>
    </div>
    <?php
    echo '</div>';
}

    $lfb_assetsUrl = 'assets/';
    $lfb_assetsDir = esc_url(trailingslashit(realpath(dirname(__FILE__) . '/assets/')));
    $lfb_cssUrl = 'export/';
    $lfb_uploadsDir = esc_url(trailingslashit(realpath(dirname(__FILE__) . '/uploads/')));
    $lfb_uploadsUrl = 'uploads/';
    chmod('uploads/', 0745);
    chmod('tmp/', 0745);
    chmod('export/', 0745);
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
        <link rel='stylesheet' id='lfb-flat-ui-css'  href='assets/css/flat-ui_admin.min.css' type='text/css' media='all' />
        <link rel='stylesheet' id='lfb-fontawesome-css'  href='assets/css/font-awesome.min.css' type='text/css' media='all' />
        <link rel='stylesheet' id='lfb-lfb-admin-css'  href='assets/css/lfb_admin.min.css' type='text/css' media='all' />
        <?php
        if ($isLogged) {
            ?>
            <link rel='stylesheet' id='lfb-colpick-css'  href='assets/css/colpick.css' type='text/css' media='all' />
            <link rel='stylesheet' id='lfb-editor-css'  href='assets/css/summernote.min.css' type='text/css' media='all' />
            <link rel='stylesheet' id='lfb-editorB3-css'  href='assets/css/summernote-bs3.css' type='text/css' media='all' />
            <link rel='stylesheet' id='lfb-codemirror-css'  href='assets/css/codemirror.min.css' type='text/css' media='all' />
            <link rel='stylesheet' id='lfb-codemirrorTheme-css'  href='assets/css/codemirror-xq-light.min.css' type='text/css' media='all' />        
            <link rel='stylesheet' id='lfb-lfb-designer-css'  href='assets/css/lfb_formDesigner.min.css' type='text/css' media='all' />
            <link rel='stylesheet' id='lfb-lfb-adminGlobal-css'  href='assets/css/lfb_admin_global.css' type='text/css' media='all' />
    <?php
}
?>

        <script type='text/javascript' src='assets/js/jquery-2.2.4.min.js'></script>
        <script type='text/javascript' src='assets/js/jquery-ui.min.js'></script>
        <script type='text/javascript' src='assets/js/bootstrap.min.js'></script>
        <script type='text/javascript' src='assets/js/bootstrap-select.min.js'></script>
        <script type='text/javascript' src='assets/js/bootstrap-switch.js'></script>
<?php
if ($isLogged) {
    $settings = getSettings();
    $designForm = 0;
    if (isset($_GET['lfb_formDesign']) && $settings->tdgn_enabled == 707  && strlen($settings->purchaseCode) > 8) {
        $designForm = sanitize_text_field($_GET['lfb_formDesign']);
    }
    ?>
            <script type='text/javascript' src='assets/js/colpick.js'></script>
            <script type='text/javascript' src='assets/js/summernote.min.js'></script>
            <script type='text/javascript' src='assets/js/jquery.nicescroll.min.js'></script>
            <script type='text/javascript' src='https://www.gstatic.com/charts/loader.js'></script>
            <script type='text/javascript' src='assets/js/codemirror.min.js'></script>
            <script type='text/javascript' src='assets/js/codemirror-javascript.min.js'></script>
            <script type='text/javascript' src='assets/js/codemirror-css.min.js'></script>
            <script>
                var ajaxurl = 'includes/lfb-admin.php';
                var lfb_texts = new Array();
                lfb_texts['tip_flagStep'] = '<?php echo addslashes(__('Click the flag icon to set this step at first step', 'lfb')); ?>';
                lfb_texts['tip_linkStep'] = '<?php echo addslashes(__('Start a link to another step', 'lfb')); ?>';
                lfb_texts['tip_delStep'] = '<?php echo addslashes(__('Remove this step', 'lfb')); ?>';
                lfb_texts['tip_duplicateStep'] = '<?php echo addslashes(__('Duplicate this step', 'lfb')); ?>';
                lfb_texts['tip_editStep'] = '<?php echo addslashes(__('Edit this step', 'lfb')); ?>';
                lfb_texts['tip_editLink'] = '<?php echo addslashes(__('Edit a link', 'lfb')); ?>';
                lfb_texts['isSelected'] = '<?php echo addslashes(__('Is selected', 'lfb')); ?>';
                lfb_texts['isUnselected'] = '<?php echo addslashes(__('Is unselected', 'lfb')); ?>';
                lfb_texts['isPriceSuperior'] = '<?php echo addslashes(__('Is price superior to', 'lfb')); ?>';
                lfb_texts['isPriceInferior'] = '<?php echo addslashes(__('Is price inferior to', 'lfb')); ?>';
                lfb_texts['isPriceEqual'] = '<?php echo addslashes(__('Is price equal to', 'lfb')); ?>';
                lfb_texts['isntPriceEqual'] = '<?php echo addslashes(__("Is price different than", 'lfb')); ?>';
                lfb_texts['isSuperior'] = '<?php echo addslashes(__('Is superior to', 'lfb')); ?>';
                lfb_texts['isInferior'] = '<?php echo addslashes(__('Is inferior to', 'lfb')); ?>';
                lfb_texts['isEqual'] = '<?php echo addslashes(__('Is equal to', 'lfb')); ?>';
                lfb_texts['isntEqual'] = '<?php echo addslashes(__("Is different than", 'lfb')); ?>';
                lfb_texts['isQuantitySuperior'] = '<?php echo addslashes(__('Quantity selected is superior to', 'lfb')); ?>';
                lfb_texts['isQuantityInferior'] = '<?php echo addslashes(__('Quantity selected is inferior to', 'lfb')); ?>';
                lfb_texts['isQuantityEqual'] = '<?php echo addslashes(__('Quantity is equal to', 'lfb')); ?>';
                lfb_texts['isntQuantityEqual'] = '<?php echo addslashes(__("Quantity is different than", 'lfb')); ?>';
                lfb_texts['totalPrice'] = '<?php echo addslashes(__('Total price', 'lfb')); ?>';
                lfb_texts['totalQuantity'] = '<?php echo addslashes(__('Total quantity', 'lfb')); ?>';
                lfb_texts['isFilled'] = '<?php echo addslashes(__('Is Filled', 'lfb')); ?>';
                lfb_texts['errorExport'] = '<?php echo addslashes(__('An error occurred during the exportation. Please verify that your server supports the ZipArchive php library ', 'lfb')); ?>';
                lfb_texts['errorImport'] = '<?php echo addslashes(__('An error occurred during the importation. Please verify that your server supports the ZipArchive php library ', 'lfb')); ?>';
                lfb_texts['Yes'] = '<?php echo addslashes(__('Yes', 'lfb')); ?>';
                lfb_texts['No'] = '<?php echo addslashes(__('No', 'lfb')); ?>';
                lfb_texts['days'] = '<?php echo addslashes(__('Days', 'lfb')); ?>';
                lfb_texts['months'] = '<?php echo addslashes(__('Months', 'lfb')); ?>';
                lfb_texts['years'] = '<?php echo addslashes(__('Years', 'lfb')); ?>';
                lfb_texts['amountOrders'] = '<?php echo addslashes(__('Amount of orders', 'lfb')); ?>';
                lfb_texts['oneTimePayment'] = '<?php echo addslashes(__('One time payments or estimates', 'lfb')); ?>';
                lfb_texts['subscriptions'] = '<?php echo addslashes(__('Subscriptions', 'lfb')); ?>';
                lfb_texts['lastStep'] = '<?php echo addslashes(__('Last Step', 'lfb')); ?>';
                lfb_texts['Nothing'] = '<?php echo addslashes(__('Nothing', 'lfb')); ?>';
                lfb_texts['selectAnElement'] = '<?php echo addslashes(__('Select an element of your website', 'tld')); ?>';
                lfb_texts['stopSelection'] = '<?php echo addslashes(__('Stop the selection', 'tld')); ?>';
                lfb_texts['stylesApplied'] = '<?php echo addslashes(__('The styles are applied', 'tld')); ?>';
                lfb_texts['modifsSaved'] = '<?php echo addslashes(__('Styles are now applied to the website', 'tld')); ?>';

                var lfb_data = new Array({
                    assetsUrl: '<?php echo $lfb_assetsUrl; ?>',
                    websiteUrl: '',
                    exportUrl: '<?php echo $lfb_cssUrl; ?>',
                    designForm: '<?php echo $designForm; ?>',
                    lscV: 1,
                    texts: lfb_texts
                });
            </script>
            <script type='text/javascript' src='assets/js/lfb_admin.min.js'></script>
    <?php
    
    if ($settings->tdgn_enabled == 707  && strlen($settings->purchaseCode) > 8) {
        echo "<script type='text/javascript' src='assets/js/lfb_formDesigner.js'></script>";
    }
   // $settings = getSettings();
} else {
    echo "<script type='text/javascript' src='assets/js/lfb_install.min.js'></script>";
}
?>
    </head>
    <body>        
        <div id="lfb_loader"><div class="lfb_spinner"><div class="double-bounce1"></div><div class="double-bounce2"></div></div></div>
        <div id="lfb_bootstraped" class="lfb_bootstraped lfb_panel">
            <div id="estimation_popup" class="wpe_bootstraped">
<?php
if (!$isInstalled) {
    ?>
                    <div id="lfb_installPanel">
                        <div id="lfb_installPanelHeader">
                            <h4><span class="glyphicon glyphicon-hdd"></span> Installation</h4>
                        </div>
                        <div id="lfb_installPanelBody">                  
                            <div class="container-fluid">
                                <div data-stepinstall="0" data-title="Database">
                                    <div class="form-group">
                                        <label>Database server :</label>
                                        <input name="db_server" type="text" class="form-control" value="<?php echo $sql_server; ?>" />
                                    </div>
                                    <div class="form-group">
                                        <label>Database name :</label>
                                        <input name="db_name" type="text" class="form-control"  value="<?php echo $sql_database_name; ?>" />
                                    </div>
                                    <div class="form-group">
                                        <label>Username :</label>
                                        <input name="db_username" type="text" class="form-control"  value="<?php echo $sql_user_name; ?>"/>
                                    </div>
                                    <div class="form-group">
                                        <label>Password :</label>
                                        <input name="db_pass" type="password" class="form-control" value="<?php echo $sql_password; ?>"/>
                                    </div>
                                    <div class="form-group">
                                        <label>Tables prefix :</label>
                                        <input name="db_prefix" type="text" class="form-control" value="epfb_" />
                                    </div>
                                    <p style="text-align: center;">
                                        <a href="javascript:" onclick="lfb_installNext();" class="btn btn-default"><span class="glyphicon glyphicon-ok"></span>Install</a>
                                    </p>
                                    <div class="alert alert-danger" style="display: none;">
                                        The plugin can not connect to the database. Please check the filled informations.
                                    </div>
                                </div>
                                <div data-stepinstall="1" data-title="Admin">
                                    <div class="form-group">
                                        <label>Admin email :</label>
                                        <input name="admin_email" type="email" class="form-control" />
                                    </div>
                                    <div class="form-group">
                                        <label>Admin password :</label>
                                        <input name="admin_pass" type="password" class="form-control" data-min="4" />
                                    </div>
                                    <p style="text-align: center;">
                                        <a href="javascript:" onclick="lfb_installNext();" class="btn btn-default"><span class="glyphicon glyphicon-ok"></span>Install</a>
                                    </p>
                                    <div class="alert alert-danger" style="display: none;">
                                    </div>
                                </div>
                                <div data-stepinstall="2" data-title="Done !">
                                    <p>Done, the installation is finished :)</p>
                                    <p style="text-align: center;">
                                        <a href="javascript:" onclick="lfb_installFinished();" class="btn btn-default"><span class="glyphicon glyphicon-user"></span>Login</a>
                                    </p>
                                </div>
                                <div class="clearfix"></div>
                            </div>
                        </div>  
                    </div>
    <?php
} else if (!$isLogged) {
    ?>

                    <div id="lfb_installPanel">
                        <div id="lfb_installPanelHeader">
                            <h4><span class="glyphicon glyphicon-hdd"></span><?php echo __('Login','lfb');?></h4>
                        </div>
                        <div id="lfb_installPanelBody">                  
                            <div class="container-fluid">
                                <div data-stepinstall="0">
                                    <div class="form-group">
                                        <label><?php echo __('Admin email','lfb');?> :</label>
                                        <input name="login_email" type="text" class="form-control" autocomplete="on" />
                                    </div>
                                    <div class="form-group">
                                        <label><?php echo __('Password','lfb');?> :</label>
                                        <input name="login_pass" type="password" class="form-control" autocomplete="on" />
                                    </div>
                                    <p style="text-align: center;">
                                        <a href="javascript:" onclick="lfb_login();" class="btn btn-default"><span class="glyphicon glyphicon-check"></span><?php echo __('Login','lfb');?></a>
                                        <br/>
                                        <small><a href="javascript:" style="color: #FFF;" onclick="lfb_passLost();"><?php echo __('Did you lose your password ?','lfb');?></a></small>
                                    <span id="lfb_loginPassText" style="display: none;"><?php echo __('The password has been sent by email','lfb');?></span>
                                    </p>
                                    <div class="clearfix"></div>
                                </div>
                            </div>
                        </div>  
                    </div>
                    <?php
 } else {
     lfb_checkUpdate();
                    //  $connection = mysqli_connect($sql_server, $sql_user_name, $sql_password, $sql_database_name);
                    echo '<div id="lfb_formWrapper" >';
                    echo '<div class="lfb_winHeader col-md-12 palette palette-turquoise">
                           <span class="glyphicon  glyphicon-list-alt" style="opacity: 0;"></span><span class="lfb_iconLogo"></span>' . __('Estimation & Payment Forms', 'lfb') . '';
                    echo '<div class="btn-toolbar">';
                    echo '<div class="btn-group">';
                    echo '<a class="btn btn-primary" href="javascript:" onclick="lfb_closeSettings();" data-toggle="tooltip" title="' . __('Return to the forms list', 'lfb') . '" data-placement="left"><span class="glyphicon glyphicon-list"></span></a>';
                     echo '<a class="btn btn-primary" style="margin-left: 8px;" href="javascript:" onclick="lfb_unlog();" data-toggle="tooltip" title="' . __('Sign out', 'lfb') . '" data-placement="left"><span class="glyphicon glyphicon-off"></span></a>';

                    echo '</div>';
                    echo '</div>'; // eof toolbar
                    echo '</div>'; // eof lfb_winHeader
                    echo '<div class="clearfix"></div>';


                    echo '<div id="lfb_panelSettings">';
                    echo '<div class="container-fluid lfb_container" style="max-width: 90%;margin: 0 auto;margin-top: 18px;">';
                    echo '</div>'; // eof container
                    echo '</div>'; // eof lfb_panelSettings

                    echo '<div id="lfb_panelLogs">';
                    echo '<div class="container-fluid lfb_container" style="max-width: 90%;margin: 0 auto;margin-top: 18px;">';
                    echo '<div class="col-md-12">';

                    echo '<p style="float: right; margin-bottom:0px;">'
                    . '<a href="javascript:" onclick="lfb_exportLogs();" class="btn btn-default" style="margin-right: 12px;"><span class="glyphicon glyphicon-cloud-download"></span>' . __('Export as CSV', 'lfb') . '</a>'
                    . '<a href="javascript:" onclick="lfb_showLoader();lfb_openCharts(jQuery(\'#lfb_panelLogs\').attr(\'data-formid\'));"  style="margin-right: 12px;"  class="btn btn-default"><span class="glyphicon glyphicon-stats"></span>' . __('View statistics', 'lfb') . '</a>'
                    . '<a href="javascript:" onclick="lfb_loadForm(jQuery(\'#lfb_panelLogs\').attr(\'data-formid\'));" class="btn btn-default"><span class="glyphicon glyphicon-arrow-left"></span>' . __('Return to the form', 'lfb') . '</a>'
                    . '</p>';
                    echo '<div role="tabpanel">';
                    echo '<ul class="nav nav-tabs" role="tablist" >
                            <li role="presentation" class="active" ><a href="#wpefc_formsTabGeneral" aria-controls="general" role="tab" data-toggle="tab" ><span class="glyphicon glyphicon-th-list" ></span > ' . __('Orders List', 'lfb') . ' </a ></li >
                            </ul >';
                    echo '<div class="tab-content" >';
                    echo '<div role="tabpanel" class="tab-pane active" id="wpefc_formsTabGeneral" >';
                    echo '<table id="lfb_logsTable" class="table">';
                    echo '<thead>';
                    echo '<th>' . __('Date', 'lfb') . '</th>';
                    echo '<th>' . __('Reference', 'lfb') . '</th>';
                    echo '<th>' . __('Email', 'lfb') . '</th>';
                    echo '<th>' . __('Actions', 'lfb') . '</th>';
                    echo '</thead>';
                    echo '<tbody>';
                    echo '</tbody>';
                    echo '</table>';

                    echo '</div>'; // eof tab-content
                    echo '</div>'; // eof wpefc_formsTabGeneral
                    echo '</div>'; // eof tabpanel

                    echo '</div>'; // eof col-md-12"
                    echo '</div>'; // eof lfb_container

                    echo '</div>'; // eof lfb_panelLogs



                    echo '<div id="lfb_panelCharts">';
                    echo '<div class="container-fluid lfb_container" style="max-width: 90%;margin: 0 auto;margin-top: 18px;">';
                    echo '<div class="col-md-12">';
                    echo '<p style="float: right; margin-bottom:0px;">'
                    . '<a href="javascript:"  onclick="lfb_loadLogs(jQuery(\'#lfb_panelCharts\').attr(\'data-formid\'));"  style="margin-right: 12px;"  class="btn btn-default"><span class="glyphicon glyphicon-list-alt"></span>' . __('View orders', 'lfb') . '</a>'
                    . '<a href="javascript:" onclick="lfb_closeCharts();" class="btn btn-default"><span class="glyphicon glyphicon-arrow-left"></span>' . __('Return to the form', 'lfb') . '</a>'
                    . '</p>';
                    echo '<div role="tabpanel">';
                    echo '<ul class="nav nav-tabs" role="tablist" >
                            <li role="presentation" class="active" ><a href="#lfb_chartsTab" aria-controls="general" role="tab" data-toggle="tab" ><span class="glyphicon glyphicon-th-list" ></span > ' . __('Statistics', 'lfb') . ' </a ></li >
                            </ul >';
                    echo '<div class="tab-content" >';
                    echo '<div role="tabpanel" class="tab-pane active" id="lfb_chartsTab" >';
                    echo '<div id="lfb_chartsMenu">';
                    echo '<div class="form-group">';
                    echo '<label>' . __('Type of chart', 'lfb') . '</label>';
                    echo '<select id="lfb_chartsTypeSelect" class="form-control">';
                    echo '<option value="month">' . __('Month', 'lfb') . '</option>';
                    echo '<option value="year" selected>' . __('Year', 'lfb') . '</option>';
                    echo '<option value="all">' . __('All years', 'lfb') . '</option>';
                    echo '</select>';
                    echo '<select id="lfb_chartsMonth" class="form-control">';

                    $table_name = $sql_prefix . "wpefc_logs";
                    $logs = sql_get_results("SELECT * FROM $table_name ORDER BY dateLog ASC LIMIT 1");
                    $yearMin = date('Y');
                    $monthMin = 1;
                    $currentYear = date('Y');
                    if (count($logs) > 0) {
                        $log = $logs[0];
                        $yearMin = substr($log->dateLog, 0, 4);
                        $monthMin = substr($log->dateLog, 6, 2);
                    }
                    for ($a = $yearMin; $a <= $currentYear; $a++) {
                        for ($i = 1; $i <= 12; $i++) {
                            $month = $i;
                            if ($month < 10) {
                                $month = '0' . $month;
                            }
                            $sel = '';
                            if ($month == date('m')) {
                                $sel = 'selected';
                            }
                            echo '<option value="' . $a . '-' . $month . '" ' . $sel . '>' . $a . '-' . $month . '</option>';
                        }
                        $monthMin = 1;
                    }
                    echo '</select>';
                    echo '<select id="lfb_chartsYear" class="form-control">';


                    $table_name = $sql_prefix . "wpefc_logs";
                    $logs = sql_get_results("SELECT * FROM $table_name ORDER BY dateLog ASC LIMIT 1");
                    $yearMin = date('Y');
                    $currentYear = date('Y');
                    if (count($logs) > 0) {
                        $log = $logs[0];
                        $yearMin = substr($log->dateLog, 0, 4);
                    }
                    for ($i = $yearMin; $i <= $currentYear; $i++) {
                        $sel = '';
                        if ($i == $currentYear) {
                            $sel = 'selected';
                        }
                        echo '<option value="' . $i . '" ' . $sel . '>' . $i . '</option>';
                    }
                    echo '</select>';
                    echo '</div>';

                    echo '</div>'; // eof lfb_chartsMenu
                    echo '<div id="lfb_charts"></div>';

                    echo '</div>'; // eof tab-content
                    echo '</div>'; // eof wpefc_formsTabGeneral
                    echo '</div>'; // eof tabpanel

                    echo '</div>'; // eof col-md-12"
                    echo '</div>'; // eof lfb_container
                    echo '</div>'; // eof lfb_panelCharts


                    echo '<div class="clearfix"></div>';

                    echo '<div id="lfb_panelFormsList">';
                    echo '<div class="container-fluid lfb_container" style="max-width: 90%;margin: 0 auto;margin-top: 18px;">';
                    echo '<div class="col-md-12">';
                    echo '<div role="tabpanel">';
                    echo '<ul class="nav nav-tabs" role="tablist" >
                            <li role="presentation" class="active" ><a href="#wpefc_formsTabGeneral" aria-controls="general" role="tab" data-toggle="tab" ><span class="glyphicon glyphicon-th-list" ></span > ' . __('Forms List', 'lfb') . ' </a ></li >
                            </ul >';
                    echo '<div class="tab-content" >';
                    echo '<div role="tabpanel" class="tab-pane active" id="wpefc_formsTabGeneral" style="margin-top:0px;" >';

                    echo '<p style="text-align: right;">
                        <a href="javascript:" style="margin-right: 12px;float: left;" onclick="lfb_openWinLicense();" class="btn btn-default"><span class="glyphicon glyphicon-ok-sign"></span>' . __('Purchase code', 'lfb') . '</a>
                        <a href="javascript:" style="margin-right: 12px;" onclick="lfb_addForm();" class="btn btn-primary"><span class="glyphicon glyphicon-plus"></span>' . __('Add a new Form', 'lfb') . ' </a>
                        <a href="javascript:" style="margin-right: 12px;" onclick=" jQuery(\'#lfb_winImport\').modal(\'show\');" class="btn btn-warning"><span class="glyphicon glyphicon-import"></span>' . __('Import forms', 'lfb') . ' </a>
                        <a href="javascript:" onclick="lfb_exportForms();" class="btn btn-default"><span class="glyphicon glyphicon-export"></span>' . __('Export all forms', 'lfb') . ' </a>
                     </p>';
                    echo '<table class="table">';
                    echo '<thead>';
                    echo '<th>' . __('Form title', 'lfb') . '</th>';
                    echo '<th>' . __('Shortcode', 'lfb') . '</th>';
                    echo '<th>' . __('Actions', 'lfb') . '</th>';
                    echo '</thead>';
                    echo '<tbody>';
                    $table_name = $sql_prefix . "wpefc_forms";
                    $forms = sql_get_results("SELECT * FROM $table_name ORDER BY id ASC");
                    foreach ($forms as $form) {
                        echo '<tr>';
                        echo '<td><a href="javascript:" onclick="lfb_loadForm(' . $form->id . ');">' . $form->title . '</a></td>';
                        echo '<td><a href="javascript:" onclick="lfb_showShortcodeWin(' . $form->id . ');" class="btn btn-info btn-circle "><span class="glyphicon glyphicon-info-sign"></span></a><code>&lt;div&gt;[estimation_form form_id="' . $form->id . '"]&lt;/div&gt;</code></td>';
                        echo '<td>';
                        echo '<a href="javascript:" onclick="lfb_loadForm(' . $form->id . ');" class="btn btn-primary btn-circle " data-toggle="tooltip" title="' . __('Edit this form', 'lfb') . '" data-placement="bottom"><span class="glyphicon glyphicon-pencil"></span></a>';
                        echo '<a href="viewForm.php?lfb_action=preview&form=' . $form->id . '" target="_blank"  class="btn btn-default btn-circle " data-toggle="tooltip" title="' . __('Preview this form', 'lfb') . '" data-placement="bottom"><span class="glyphicon glyphicon-eye-open"></span></a>';
                        echo '<a href="javascript:" onclick="lfb_loadLogs(' . $form->id . ');" class="btn btn-default btn-circle " data-toggle="tooltip" title="' . __('View orders', 'lfb') . '" data-placement="bottom"><span class="glyphicon glyphicon-list-alt"></span></a>';
                        echo '<a href="javascript:"  onclick="lfb_openCharts(' . $form->id . ');"  class="btn btn-default btn-circle " data-toggle="tooltip" title="' . __('View statistics', 'lfb') . '" data-placement="bottom"><span class="glyphicon glyphicon-stats"></span></a>';
                        echo '<a href="javascript:" onclick="lfb_duplicateForm(' . $form->id . ');" class="btn btn-default btn-circle " data-toggle="tooltip" title="' . __('Duplicate this form', 'lfb') . '" data-placement="bottom"><span class="glyphicon glyphicon-duplicate"></span></a>';

                        echo '<a href="javascript:" onclick="lfb_removeForm(' . $form->id . ');" class="btn btn-danger btn-circle " data-toggle="tooltip" title="' . __('Delete this form', 'lfb') . '" data-placement="bottom"><span class="glyphicon glyphicon-trash"></span></a>';
                        echo '</td>';
                        echo '</tr>';
                    }
                    echo '</tbody>';
                    echo '</table>';

                    echo '</div>'; // eof tab-content
                    echo '</div>'; // eof wpefc_formsTabGeneral
                    echo '</div>'; // eof tabpanel


                    echo '</div>'; // eof col-md-12
                    echo '</div>'; // eof container
                    echo '</div>'; // eof lfb_panelFormsList


                    echo '<div id="lfb_panelPreview">';
                    echo '<div class="clearfix"></div>';
                    $tdgnAction = ' jQuery(\'#lfb_winTldAddon\').modal(\'show\');';
                    if ($settings->tdgn_enabled == 707 && strlen($settings->purchaseCode) > 8) {
                        $tdgnAction = 'lfb_openFormDesigner();';
                    }
                    echo '<div style="max-width: 90%;margin: 0 auto;margin-top: 18px;" id="lfb_formTopbtns">
                            <p class="text-right" style="float:right; margin-bottom:0px;">
                             <a href="javascript:"onclick="lfb_addStep( \'' . __('My Step', 'lfb') . '\');" class="btn btn-primary"><span class="glyphicon glyphicon-plus"></span>' . __("Add a step", 'lfb') . '</a>
                            <a href="javascript:" id="lfb_btnPreview" target="_blank" style="margin-left: 12px;"  class="btn btn-default"><span class="glyphicon glyphicon-eye-open"></span>' . __("View the form", 'lfb') . '</a>
                            <a href="javascript:" onclick="lfb_showShortcodeWin();" style="margin-left: 12px;"  class="btn btn-default"><span class="glyphicon glyphicon-info-sign"></span>' . __('Shortcode', 'lfb') . '</a>
                            <a href="javascript:" id="lfb_logsBtn" data-formid="0" onclick="lfb_loadLogs(jQuery(this).attr(\'data-formid\'));"  style="margin-left: 12px;"  class="btn btn-default"><span class="glyphicon glyphicon-list-alt"></span>' . __('View orders', 'lfb') . '</a>
                            <a href="javascript:" id="lfb_chartsBtn" data-formid="0" onclick="lfb_showLoader();lfb_loadCharts(jQuery(this).attr(\'data-formid\'));"  style="margin-left: 12px;"  class="btn btn-default"><span class="glyphicon glyphicon-stats"></span>' . __('View statistics', 'lfb') . '</a>
                            <a href="javascript:" id="lfb_formDesignerBtn" data-formid="0" onclick="' . $tdgnAction . '"  style="margin-left: 12px;"  class="btn btn-addon"><span class="fa fa-magic"></span>' . __('Form Designer', 'lfb') . '</a>
                            <a href="javascript:" style="margin-left: 12px;"  data-toggle="modal" data-target="#modal_removeAllSteps" class="btn btn-danger"><span class="glyphicon glyphicon-trash"></span>' . __("Remove all steps", 'lfb') . '</a>
                            </p>
                            <h3 id="lfb_stepsManagerTitle">' . __('Steps manager', 'lfb') . '</h3>

                            <div class="clearfix"></div>
                        </div>
                    ';

                    echo '
                    <!-- Modal -->
                    <div class="modal fade" id="modal_removeAllSteps" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
                      <div class="modal-dialog">
                        <div class="modal-content">
                          <div class="modal-body">
                            ' . __('Are you sure you want to delete all steps ?', 'lfb') . '
                          </div>
                          <div class="modal-footer">
                            <button type="button" class="btn btn-danger" data-dismiss="modal"  onclick="lfb_removeAllSteps();" >' . __('Yes', 'lfb') . '</button>
                            <button type="button" class="btn btn-default" data-dismiss="modal" >' . __('No', 'lfb') . '</button>
                          </div>
                        </div>
                      </div>
                    </div>';

                    echo '<div id="lfb_stepsOverflow">';
                    echo '<div id="lfb_stepsContainer">';
                    echo '<canvas id="lfb_stepsCanvas"></canvas>';
                    echo '</div>';
                    echo '</div>';


                    echo '<div id="lfb_formFields" style="max-width: 90%;margin: 0 auto;margin-top: 18px;" >
                            <h3>' . __('Form settings', 'lfb') . '</h3>
                        <div role="tabpanel" >

                          <!--Nav tabs-->
                          <ul class="nav nav-tabs" role="tablist" >
                            <li role="presentation" class="active" ><a href="#lfb_tabGeneral" aria-controls="general" role="tab" data-toggle="tab" ><span class="glyphicon glyphicon-cog" ></span > ' . __('General', 'lfb') . ' </a ></li >
                            <li role="presentation" ><a href="#lfb_tabTexts" aria-controls="texts" role="tab" data-toggle="tab" ><span class="glyphicon glyphicon-edit" ></span > ' . __('Texts', 'lfb') . ' </a ></li >
                            <li role="presentation" ><a href="#lfb_tabEmail" aria-controls="email" role="tab" data-toggle="tab" ><span class="glyphicon glyphicon-envelope" ></span > ' . __('Email', 'lfb') . ' </a ></li >
                            <li role="presentation" ><a href="#lfb_tabLastStep" aria-controls="last step" role="tab" data-toggle="tab" ><span class="glyphicon glyphicon-list" ></span > ' . __('Last Step', 'lfb') . ' </a ></li >
                            <li role="presentation" ><a href="#lfb_tabCoupons" aria-controls="coupons" role="tab" data-toggle="tab" ><span class="glyphicon glyphicon-gift" ></span > ' . __('Discount coupons', 'lfb') . ' </a ></li >
                            <li role="presentation" ><a href="#lfb_tabDesign" onclick="setTimeout(function(){lfb_editorCustomCSS.refresh();},100);" aria-controls="design" role="tab" data-toggle="tab" ><span class="glyphicon glyphicon-tint" ></span > ' . __('Design', 'lfb') . ' </a ></li >

            </ul >

                          <!--Tab panes-->
                          <div class="tab-content" >
                            <div role="tabpanel" class="tab-pane active" id="lfb_tabGeneral" >
                                <div class="row-fluid" >
                                    <div class="col-md-6" >
                                     <div class="form-group" >
                                            <label > ' . __('Title', 'lfb') . ' </label >
                                            <input type="text" name="title" class="form-control" />
                                            <small> ' . __('The form title', 'lfb') . ' </small>
                                        </div>
                                    <div class="form-group" >
                                            <label > ' . __('Order reference prefix', 'lfb') . ' </label >
                                            <input type="text" name="ref_root" class="form-control" />
                                            <small> ' . __('Enter a prefix for the order reference', 'lfb') . ' </small>
                                        </div>
                                        <div class="form-group" >
                                            <label > ' . __('Google Analytics ID', 'lfb') . ' </label >
                                            <input type="text" name="analyticsID" class="form-control" />
                                            <small> ' . __('By filling this field, you can track user actions in your form', 'lfb') . ' </small>
                                            <a href="https://support.google.com/analytics/answer/1032385?hl=en" target="_blank" style="margin-left: 8px;" class="btn btn-info btn-circle"><span class="glyphicon glyphicon-info-sign"></span></a>
                                        </div>
                                        <div class="form-group" >
                                            <label > ' . __('Google Maps browser key', 'lfb') . ' </label >
                                            <input type="text" name="gmap_key" class="form-control" />
                                            <small> ' . __('By filling this field, you can use distance calculations', 'lfb') . ' </small>
                                            <a href="https://developers.google.com/maps/documentation/javascript/get-api-key?hl=en" target="_blank" style="margin-left: 8px;" class="btn btn-info btn-circle"><span class="glyphicon glyphicon-info-sign"></span></a>
                                        </div>

                                        <div class="form-group" >
                                            <label > ' . __('Progress bar shows', 'lfb') . ' </label >
                                            <select  name="showSteps" class="form-control" />
                                                <option value="0" > ' . __('Price', 'lfb') . ' </option >
                                                <option value="1" > ' . __('Step', 'lfb') . ' </option >
                                                <option value="2" > ' . __('No progress bar', 'lfb') . ' </option >
                                            </select >
                                            <small> ' . __('The progress bar can show the price or step number', 'lfb') . ' </small>
                                        </div>                            

                                        <div class="form-group" >
                                            <label > ' . __('Show the total price at bottom ?', 'lfb') . ' </label >
                                            <input type="checkbox"  name="showTotalBottom" data-switch="switch" data-on-label="' . __('Yes', 'lfb') . '" data-off-label="' . __('No', 'lfb') . '"class=""   />
                                            <small> ' . __('Display or hide the total price at bottom of each step', 'lfb') . ' </small>
                                        </div>

                                        <div class="form-group" >
                                            <label > ' . __('Currency', 'lfb') . ' </label >
                                            <input type="text"  name="currency" class="form-control" />
                                            <small> ' . __('$, € , £ ...', 'lfb') . ' </small>
                                        </div>
                                        <div class="form-group" >
                                            <label > ' . __('Currency Position', 'lfb') . ' </label >
                                            <select  name="currencyPosition" class="form-control" />
                                                <option value="right" > ' . __('Right', 'lfb') . ' </option >
                                                <option value="left" > ' . __('Left', 'lfb') . ' </option >
                                            </select >
                                            <small> ' . __('Sets the currency position in the price', 'lfb') . ' </small>
                                        </div>
                                        <div class="form-group" >
                                            <label > ' . __('Quantity selection style', 'lfb') . ' </label >
                                            <select  name="qtType" class="form-control" />
                                                <option value="0" > ' . __('Buttons', 'lfb') . ' </option >
                                                <option value="1" > ' . __('Field', 'lfb') . ' </option >
                                                <option value="2" > ' . __('Slider', 'lfb') . ' </option >
                                            </select >
                                            <small> ' . __('If "field", tooltip will be positionned on top', 'lfb') . ' </small>
                                        </div>
                                        <div class="form-group" >
                                            <label > ' . __('Custom JS', 'lfb') . ' </label >                               
                                           <textarea name="customJS" class="form-control" ></textarea>
                                            <small> ' . __('You can paste your own js code here', 'lfb') . ' </small>
                                        </div>
                                    </div>
                                    <div class="col-md-6" >                            
                                         <div class="form-group" >
                                            <label > ' . __('Initial price', 'lfb') . ' </label >
                                            <input type="number" step="any" name="initial_price" class="form-control" />
                                            <small> ' . __('Starting price', 'lfb') . ' </small>
                                        </div>
                                        <div class="form-group" >
                                            <label > ' . __('Maximum price', 'lfb') . ' </label >
                                            <input type="number" step="any"  name="max_price" class="form-control" />
                                            <small> ' . __('Leave blank for automatic calculation', 'lfb') . ' </small>
                                        </div>
                                        <div class="form-group" >
                                            <label > ' . __('Hide initial price in the progress bar ? ', 'lfb') . ' </label >
                                            <input type="checkbox"  name="show_initialPrice" data-switch="switch" data-on-label="' . __('Yes', 'lfb') . '" data-off-label="' . __('No', 'lfb') . '"class=""   />
                                            <small> ' . __('Display or hide the initial price from progress bar', 'lfb') . ' </small>
                                        </div>
                                        <div class="form-group" >
                                            <label > ' . __('Hide tooltips on touch devices ?', 'lfb') . ' </label >
                                            <input type="checkbox"  name="disableTipMobile" data-switch="switch" data-on-label="' . __('Yes', 'lfb') . '" data-off-label="' . __('No', 'lfb') . '" class=""   />
                                            <small> ' . __('Hide tooltips on touch devices ?', 'lfb') . ' </small>
                                        </div>
                                        <div class="form-group" >
                                            <label > ' . __('Automatic next step', 'lfb') . ' </label >
                                            <input type="checkbox"  name="groupAutoClick" data-switch="switch" data-on-label="' . __('Yes', 'lfb') . '" data-off-label="' . __('No', 'lfb') . '" class=""   />
                                            <small> ' . __('Automatically go to the next step when selecting if only one product is selectable and step is required', 'lfb') . ' </small>
                                        </div>
                                        <div class="form-group" >
                                            <label > ' . __('Ajax navigation support', 'lfb') . ' </label >
                                            <input type="checkbox"  name="loadAllPages" data-switch="switch" data-on-label="' . __('Yes', 'lfb') . '" data-off-label="' . __('No', 'lfb') . '" class=""   />
                                            <small> ' . __('Activate this option if your theme uses ajax navigation to display pages', 'lfb') . ' </small>
                                        </div>

                                        <div class="form-group" >
                                            <label > ' . __('Use default dropdowns', 'lfb') . ' </label >
                                            <input type="checkbox"  name="disableDropdowns" data-switch="switch" data-on-label="' . __('Yes', 'lfb') . '" data-off-label="' . __('No', 'lfb') . '" class=""   />
                                            <small> ' . __("Activate this option if your select items don't work correctly", "lfb") . ' </small>
                                        </div>
                                        <div class="form-group" >
                                            <label > ' . __('Datepicker language', 'lfb') . ' </label >
                                            <select  name="datepickerLang" class="form-control" />
                                                <option value="">en</option >
                                                <option value="af">af</option >
                                                <option value="ar-DZ">ar-DZ</option >
                                                <option value="ar">ar</option >
                                                <option value="az">az</option >
                                                <option value="be">be</option >
                                                <option value="bg">bg</option >
                                                <option value="bs">bs</option >
                                                <option value="ca">ca</option >
                                                <option value="cs">cs</option >
                                                <option value="cy-GB">cy-GB</option >
                                                <option value="da">da</option >
                                                <option value="de">de</option >
                                                <option value="el">el</option >
                                                <option value="en-AU">en-AU</option >
                                                <option value="en-NZ">en-NZ</option >
                                                <option value="eo">eo</option >
                                                <option value="es">es</option >
                                                <option value="et">et</option >
                                                <option value="eu">eu</option >
                                                <option value="fa">fa</option >
                                                <option value="fi">fi</option >
                                                <option value="fo">fo</option >
                                                <option value="fr-CA">fr-CA</option >
                                                <option value="fr-CH">fr-CH</option >
                                                <option value="fr">fr</option >
                                                <option value="gl">gl</option >
                                                <option value="he">he</option >
                                                <option value="hi">hi</option >
                                                <option value="hr">hr</option >
                                                <option value="hu">hu</option >
                                                <option value="hy">hy</option >
                                                <option value="id">id</option >
                                                <option value="is">is</option >
                                                <option value="hr">hr</option >
                                                <option value="hu">hu</option >
                                                <option value="hy">hy</option >
                                                <option value="id">id</option >
                                                <option value="is">is</option >
                                                <option value="it-CH">it-CH</option >
                                                <option value="it">it</option >
                                                <option value="ja">ja</option >
                                                <option value="ka">ka</option >
                                                <option value="kk">kk</option >
                                                <option value="km">km</option >
                                                <option value="ko">ko</option >
                                                <option value="ky">ky</option >
                                                <option value="lb">lb</option >
                                                <option value="lt">lt</option >
                                                <option value="lv">lv</option >
                                                <option value="mk">mk</option >
                                                <option value="ml">ml</option >
                                                <option value="ms">ms</option >
                                                <option value="nb">nb</option >
                                                <option value="nl-BE">nl-BE</option >
                                                <option value="nl">nl</option >
                                                <option value="nn">nn</option >
                                                <option value="no">no</option >
                                                <option value="pl">pl</option >
                                                <option value="pt-BR">pt-BR</option >
                                                <option value="pt">pt</option >
                                                <option value="rm">rm</option >
                                                <option value="ro">ro</option >
                                                <option value="ru">ru</option >
                                                <option value="sk">sk</option >
                                                <option value="sl">sl</option >
                                                <option value="sg">sg</option >
                                                <option value="sr-SR">sr-SR</option >
                                                <option value="sr">sr</option >
                                                <option value="sv">sv</option >
                                                <option value="ta">ta</option >
                                                <option value="th">th</option >
                                                <option value="tj">tj</option >
                                                <option value="tr">tr</option >
                                                <option value="uk">bg</option >
                                                <option value="vi">vi</option >
                                                <option value="zh-CN">zh-CN</option >
                                                <option value="zh-HK">zh-HK</option >
                                                <option value="zh-TW">zh-TW</option >
                                            </select >
                                            <small> ' . __('Select your language code', 'lfb') . ' </small>
                                        </div>
                                        <div class="form-group" >
                                            <label > ' . __('Decimals separator', 'lfb') . ' </label >
                                            <input type="text"  name="decimalsSeparator" class="form-control" />
                                            <small> ' . __('Enter a separator or leave empty', 'lfb') . ' </small>
                                        </div>
                                        <div class="form-group" >
                                            <label > ' . __('Thousands separator', 'lfb') . ' </label >
                                            <input type="text"  name="thousandsSeparator" class="form-control" />
                                            <small> ' . __('Enter a separator or leave empty', 'lfb') . ' </small>
                                        </div>
                                        <div class="form-group" >
                                            <label > ' . __('Millions separator', 'lfb') . ' </label >
                                            <input type="text"  name="millionSeparator" class="form-control" />
                                            <small> ' . __('Enter a separator or leave empty', 'lfb') . ' </small>
                                        </div>
                                        <div class="form-group" >
                                            <label > ' . __('Animations speed', 'lfb') . ' </label >
                                            <input type="number" step="0.1"  name="animationsSpeed" class="form-control" />
                                            <small> ' . __('Sets the animations speed, in seconds(default : 0.5)', 'lfb') . ' </small>
                                        </div>


                                    </div>
                                </div>
                                <div class="clearfix" ></div>
                            </div>

                            <div role="tabpanel" class="tab-pane" id="lfb_tabTexts" >
                                <div class="row-fluid" >
                                    <div class="col-md-6" >
                                        <h4 > ' . __('General', 'lfb') . ' </h4 >                           
                                        <div class="form-group" >
                                            <label > ' . __('Selection required', 'lfb') . ' </label >
                                            <input type="text" name="errorMessage" class="form-control" />
                                            <small> ' . __('Something like "You need to select an item to continue"', 'lfb') . ' </small>
                                        </div>
                                        <div class="form-group" >
                                            <label > ' . __('Button "next step"', 'lfb') . ' </label >
                                            <input type="text" name="btn_step" class="form-control" />
                                            <small> ' . __('Something like "NEXT STEP"', 'lfb') . ' </small>
                                        </div>
                                        <div class="form-group" >
                                            <label > ' . __('Link "previous step"', 'lfb') . ' </label >
                                            <input type="text" name="previous_step" class="form-control" />
                                            <small> ' . __('Something like "return to previous step"', 'lfb') . ' </small>
                                        </div>
                                        <div class="form-group" >
                                            <label > ' . __('Label "Description"', 'lfb') . ' </label >
                                            <input type="text" name="summary_description" class="form-control" />
                                            <small> ' . __('Something like "Description"', 'lfb') . ' </small>
                                        </div>                             
                                        <div class="form-group" >
                                            <label > ' . __('Label "Quantity"', 'lfb') . ' </label >
                                            <input type="text" name="summary_quantity" class="form-control" />
                                            <small> ' . __('Something like "Quantity"', 'lfb') . ' </small>
                                        </div>                             
                                        <div class="form-group" >
                                            <label > ' . __('Label "Information"', 'lfb') . ' </label >
                                            <input type="text" name="summary_value" class="form-control" />
                                            <small> ' . __('Something like "Information"', 'lfb') . ' </small>
                                        </div>                                   
                                        <div class="form-group" >
                                            <label > ' . __('Label "Price"', 'lfb') . ' </label >
                                            <input type="text" name="summary_price" class="form-control" />
                                            <small> ' . __('Something like "Price"', 'lfb') . ' </small>
                                        </div>                  
                                        <div class="form-group" >
                                            <label > ' . __('Label "Total"', 'lfb') . ' </label >
                                            <input type="text" name="summary_total" class="form-control" />
                                            <small> ' . __('Something like "Total :"', 'lfb') . ' </small>
                                        </div>        
                                        <div class="form-group" >
                                            <label > ' . __('Label "Discount"', 'lfb') . ' </label >
                                            <input type="text" name="summary_discount" class="form-control" />
                                            <small> ' . __('Something like "Discount :"', 'lfb') . ' </small>
                                        </div>
                                        <div class="form-group" >
                                            <label > ' . __('Label of files fields', 'lfb') . ' </label >
                                            <input type="text" name="filesUpload_text" class="form-control" />
                                            <small> ' . __('Something like "Drop files here to upload"', 'lfb') . ' </small>
                                        </div>
                                        <div class="form-group" >
                                            <label > ' . __('Size error for files fields', 'lfb') . ' </label >
                                            <input type="text" name="filesUploadSize_text" class="form-control" />
                                            <small> ' . __('Something like "File is too big (max size: {{maxFilesize}}MB)"', 'lfb') . ' </small>
                                        </div>
                                        <div class="form-group" >
                                            <label > ' . __('File type error for files fields', 'lfb') . ' </label >
                                            <input type="text" name="filesUploadType_text" class="form-control" />
                                            <small> ' . __('Something like "Invalid file type"', 'lfb') . ' </small>
                                        </div>
                                        <div class="form-group" >
                                            <label > ' . __('Limit error for files fields', 'lfb') . ' </label >
                                            <input type="text" name="filesUploadLimit_text" class="form-control" />
                                            <small> ' . __('Something like "You can not upload any more files"', 'lfb') . ' </small>
                                        </div>   
                                        <div class="form-group" >
                                            <label > ' . __('Distance calculation error', 'lfb') . ' </label >
                                            <input type="text" name="txtDistanceError" class="form-control" />
                                            <small> ' . __('Something like "Calculating the distance could not be performed, please verify the input addresses"', 'lfb') . ' </small>
                                        </div>   
                                        <div class="form-group" >
                                            <label > ' . __('Captcha text', 'lfb') . ' </label >
                                            <input type="text" name="captchaLabel" class="form-control" />
                                        </div>  

                                    </div>
                                    <div class="col-md-6" >
                                     <h4 > ' . __('Introduction', 'lfb') . ' </h4 >
                                        <div class="form-group" >
                                            <label> ' . __('Enable Introduction ? ','lfb') . ' </label >
                                            <input type="checkbox"  name="intro_enabled" data-switch="switch" data-on-label="' . __('Yes', 'lfb') . '" data-off-label="' . __('No', 'lfb') . '" />
                                            <small> ' . __('Is Introduction enabled ? ', 'lfb') . ' </small>
                                        </div>
                                         <div class="form-group" >
                                            <label > ' . __('Introduction title', 'lfb') . ' </label >
                                            <input type="text" name="intro_title" class="form-control" />
                                            <small> ' . __('Something like "HOW MUCH TO MAKE MY WEBSITE ?"', 'lfb') . ' </small>
                                        </div>
                                        <div class="form-group" >
                                            <label > ' . __('Introduction text', 'lfb') . ' </label >
                                            <input type="text" name="intro_text" class="form-control" />
                                            <small> ' . __('Something like "Estimate the cost of a website easily using this awesome tool."', 'lfb') . ' </small>
                                        </div>
                                        <div class="form-group" >
                                            <label > ' . __('Introduction button', 'lfb') . ' </label >
                                            <input type="text" name="intro_btn" class="form-control" />
                                            <small> ' . __('Something like "GET STARTED"', 'lfb') . ' </small>
                                        </div>
                                        <h4> ' . __('Last Step', 'lfb') . ' </h4>
                                         <div class="form-group" >
                                            <label > ' . __('Last step title', 'lfb') . ' </label >
                                            <input type="text" name="last_title" class="form-control" />
                                            <small> ' . __('Something like "Final cost", "Result" ...', 'lfb') . ' </small>
                                        </div>
                                         <div class="form-group" >
                                            <label > ' . __('Last step text', 'lfb') . ' </label >
                                            <input type="text" name="last_text" class="form-control" />
                                            <small> ' . __('Something like "The final estimated price is :"', 'lfb') . ' </small>
                                        </div>
                                         <div class="form-group" >
                                            <label > ' . __('Last step button', 'lfb') . ' </label >
                                            <input type="text" name="last_btn" class="form-control" />
                                            <small> ' . __('Something like "ORDER MY WEBSITE"', 'lfb') . ' </small>
                                        </div>
                                         <div class="form-group" >
                                            <label > ' . __('Succeed text', 'lfb') . ' </label >
                                            <input type="text" name="succeed_text" class="form-control" />
                                            <small> ' . __('Something like "Thanks, we will contact you soon"', 'lfb') . ' </small>
                                        </div> 
                                        <h4> ' . __('Stripe payment', 'lfb') . ' </h4>                                   
                                         <div class="form-group" >
                                            <label > ' . __('Label "Credit Card number"', 'lfb') . ' </label >
                                            <input type="text" name="stripe_label_creditCard" class="form-control" />
                                            <small> ' . __('Something like "Credit Card number"', 'lfb') . ' </small>
                                        </div>
                                         <div class="form-group" >
                                            <label > ' . __('Label "CVC"', 'lfb') . ' </label >
                                            <input type="text" name="stripe_label_cvc" class="form-control" />
                                            <small> ' . __('Something like "CVC"', 'lfb') . ' </small>
                                        </div>
                                         <div class="form-group" >
                                            <label > ' . __('Label "Expiration date"', 'lfb') . ' </label >
                                            <input type="text" name="stripe_label_expiration" class="form-control" />
                                            <small> ' . __('Something like "Expiration date"', 'lfb') . ' </small>
                                        </div> 
                                    </div>

                                </div>
                                <div class="clearfix" ></div>
                            </div>

                            <div role="tabpanel" class="tab-pane" id="lfb_tabEmail" >
                                <div class="row-fluid" >
                                    <div class="col-md-6" >
                                        <h4 > ' . __('Admin email', 'lfb') . ' </h4 >
                                        <div class="form-group" >
                                            <label > ' . __('Admin email', 'lfb') . ' </label >
                                            <input type="text" name="email" class="form-control" />
                                            <small> ' . __('Email that will receive requests', 'lfb') . ' </small>
                                        </div>
                                         <div class="form-group" >
                                            <label > ' . __('Admin email subject', 'lfb') . ' </label >
                                            <input type="text" name="email_subject" class="form-control" />
                                            <small> ' . __('Something like "New order from your website"', 'lfb') . ' </small>
                                        </div>
                                        <div class="form-group" >
                                            <label > ' . __('Send the order as pdf', 'lfb') . ' </label >
                                            <input type="checkbox"  name="sendPdfAdmin" data-switch="switch" data-on-label="' . __('Yes', 'lfb') . '" data-off-label="' . __('No', 'lfb') . '" class=""   />
                                            <small> ' . __('A pdf file will be generated and sent as attachment', 'lfb') . ' </small>
                                        </div>

                                        <div class="form-group" >
                                           <!-- <label> ' . __('Admin email content', 'lfb') . ' </label> -->
                                                <p><strong> ' . __('Variables', 'lfb') . ' :</strong></p >
                                            <div class="palette palette-turquoise" >
                                                <p>
                                                  <strong>[project_content]</strong> : ' . __('Selected items list', 'lfb') . ' <br/>
                                                    <strong>[information_content]</strong> : ' . __('Last step form values', 'lfb') . ' <br/>
                                                    <strong>[total_price]</strong> : ' . __('Total price', 'lfb') . ' <br/>
                                                    <strong>[ref]</strong> : ' . __('Order reference', 'lfb') . ' <br/>
                                                </p>
                                                <a href="javascript:" id="lfb_btnAddEmailValue" onclick="lfb_addEmailValue(false);" class="btn btn-default" style="margin-bottom: 8px;"><span class="glyphicon glyphicon-plus"></span>' . __('Get the value of a field', 'lfb') . '</a>

                                            </div>
                                            <div id="email_adminContent_editor" >
                                            <div id="email_adminContent"></div>';

                    //        ' . wp_editor('<p>Ref: <strong>[ref]</strong></p><h2 style="color: #008080;">Information</h2><hr/><span style="font-weight: 600; color: #444444;">[information_content]</span><span style="color: #444444;"> </span><hr/><h2 style="color: #008080;">Project</h2><hr/>[project_content]<hr/><h4>Total: <strong><span style="color: #444444;">[total_price]</span></strong></h4>', 'email_adminContent', array('tinymce' => array('height' => 300))) . '
                    echo '</div>
                                        </div>
                                    </div>
                                         <div class="col-md-6" >
                                        <h4> ' . __('Customer email', 'lfb') . ' </h4>
                                         <div class="form-group" >
                                            <label > ' . __('Send email to the customer ? ', 'lfb') . ' </label >
                                            <input type="checkbox"  name="email_toUser" data-switch="switch" data-on-label="' . __('Yes', 'lfb') . '" data-off-label="' . __('No', 'lfb') . '" />
                                            <small> ' . __('If true, the user will receive a confirmation email', 'lfb') . ' </small>
                                        </div>
                                        <div id="lfb_formEmailUser" >
                                         <div class="form-group" >
                                            <label > ' . __('Customer email subject', 'lfb') . ' </label >
                                            <input type="text" name="email_userSubject" class="form-control" />
                                            <small> ' . __('Something like "Order confirmation"', 'lfb') . ' </small>
                                        </div>
                                        <div class="form-group" >
                                            <label > ' . __('Send the order as pdf', 'lfb') . ' </label >
                                            <input type="checkbox"  name="sendPdfCustomer" data-switch="switch" data-on-label="' . __('Yes', 'lfb') . '" data-off-label="' . __('No', 'lfb') . '" class=""   />
                                            <small> ' . __('A pdf file will be generated and sent as attachment', 'lfb') . ' </small>
                                        </div>
                                        <div class="form-group" >
                                                <p><strong > ' . __('Variables', 'lfb') . ' :</strong ></p >
                                            <div class="palette palette-turquoise" >
                                                <p>
                                                    <strong>[project_content]</strong> : ' . __('Selected items list', 'lfb') . ' <br/>
                                                    <strong>[information_content]</strong> : ' . __('Last step form values', 'lfb') . ' <br/>
                                                    <strong>[total_price]</strong> : ' . __('Total price', 'lfb') . ' <br/>
                                                    <strong>[ref]</strong> : ' . __('Order reference', 'lfb') . ' <br/>
                                                </p>
                                                <a href="javascript:" id="lfb_btnAddEmailValueCustomer" onclick="lfb_addEmailValue(true);" class="btn btn-default" style="margin-bottom: 8px;"><span class="glyphicon glyphicon-plus"></span>' . __('Get the value of a field', 'lfb') . '</a>
                                            </div>';



                    echo'  <div id="email_userContent_editor" >
                                            <div id="email_userContent"></div>';
                    echo '</div>
                                        </div>
                                    </div>

                                </div>
                                <div class="clearfix"></div>
                                <div class="row-fluid">
                                    <div class="col-md-6">
                                        <h4>' . __('Mailing list', 'lfb') . '</h4>
                                    </div>
                                    <div class="col-md-6"></div>
                                <div class="clearfix"></div>
                                    <div class="col-md-6">';
                    echo '<div class="form-group">'
                    . '<label>' . __('Send contact to Mailchimp ?', 'lfb') . '</label>'
                    . '<input type="checkbox" data-switch="switch"  name="useMailchimp"/>'
                    . '</div>';
                    echo '<div class="form-group">'
                    . '<label>' . __('Mailchimp API key', 'lfb') . ' :</label>'
                    . '<input type="text" class="form-control" name="mailchimpKey"/>'
                    . '<a href="http://kb.mailchimp.com/accounts/management/about-api-keys" target="_blank" style="margin-left: 8px;" class="btn btn-info btn-circle"><span class="glyphicon glyphicon-info-sign"></span></a>'
                    . '</div>';
                    echo '<div class="form-group">'
                    . '<label>' . __('Mailchimp list', 'lfb') . ' :</label>'
                    . '<select class="form-control" name="mailchimpList"></select>'
                    . '</div>';
                    echo '<div class="form-group">'
                    . '<label>' . __('Confirmation by email required ?', 'lfb') . '</label>'
                    . '<input type="checkbox" data-switch="switch"  name="mailchimpOptin"/>'
                    . '</div>';
                    echo '<div class="form-group" style="display: none !important;">'
                    . '<label>' . __('Send contact to MailPoet ?', 'lfb') . '</label>'
                    . '<input type="checkbox" data-switch="switch"  name="useMailpoet"/>'
                    . '</div>';
                    echo '<div class="form-group">'
                    . '<label>' . __('Mailpoet list', 'lfb') . ' :</label>'
                    . '<select class="form-control" name="mailPoetList"></select>'
                    . '</div>';
                    echo '</div>';
                    echo '<div class="col-md-6">';


                    echo '<div class="form-group">'
                    . '<label>' . __('Send contact to GetResponse ?', 'lfb') . '</label>'
                    . '<input type="checkbox" data-switch="switch"  name="useGetResponse"/>'
                    . '</div>';
                    echo '<div class="form-group">'
                    . '<label>' . __('GetResponse API key', 'lfb') . ' :</label>'
                    . '<input type="text" class="form-control" name="getResponseKey"/>'
                    . '<a href="https://support.getresponse.com/faq/where-i-find-api-key" target="_blank" style="margin-left: 8px;" class="btn btn-info btn-circle"><span class="glyphicon glyphicon-info-sign"></span></a>'
                    . '</div>';
                    echo '<div class="form-group">'
                    . '<label>' . __('GetResponse list', 'lfb') . ' :</label>'
                    . '<select class="form-control" name="getResponseList"></select>'
                    . '</div>';
                    echo '<div class="form-group">'
                    . '<label>' . __('Send contact as soon the email field is filled ?', 'lfb') . '</label>'
                    . '<input type="checkbox" data-switch="switch"  name="sendContactASAP"/>'
                    . '<small> ' . __('If checked, the contact will be send at end of the step containing the email field', 'lfb') . ' </small>'
                    . '</div>';
                    echo '</div>
                                </div>
                                <div class="clearfix" ></div>
                            </div>
                            </div>
                            <div role="tabpanel" class="tab-pane" id="lfb_tabLastStep" >
                                <div class="row-fluid" >
                                    <div class="col-md-6" >
                                        <div class="form-group" >
                                            <label > ' . __('Call an url on close', 'lfb') . ' </label >
                                            <input type="text" name="close_url" class="form-control" />
                                            <small> ' . __('Complete this field if you want to call a specific url on close . Otherwise leave it empty.', 'lfb') . ' </small>
                                        </div>
                                        <div class="form-group" >
                                            <label > ' . __('Conditions on redirection ?', 'lfb') . ' </label >
                                            <input  type="checkbox"  name="useRedirectionConditions" data-switch="switch" data-on-label="' . __('Yes', 'lfb') . '" data-off-label="' . __('No', 'lfb') . '"/>
                                            <small> ' . __('Activate it to create different possible redirections', 'lfb') . ' </small>
                                        </div>

                                        <div id="lfb_redirConditionsContainer">
                                        <p style="text-align: right;"><a href="javascript:" id="lfb_addRedirBtn" onclick="lfb_editRedirection(0);" class="btn btn-primary"><span class="glyphicon glyphicon-plus"></span> ' . __('Add a redirection', 'lfb') . '</a></p>
                                        <table id="lfb_redirsTable" class="table">
                                        <thead>
                                            <tr>
                                                <th>' . __('URL', 'lfb') . '</th>
                                                <th></th>
                                                <th></th>
                                            </tr>
                                        </thead>
                                        <tbody></tbody>
                                      </table>
                                      </div>

                                        <div class="form-group" >
                                            <label > ' . __('Delay before the redirection', 'lfb') . ' </label >
                                            <input type="numberfield" name="redirectionDelay" class="form-control" />
                                            <small> ' . __('Enter the wanted delay in seconds', 'lfb') . ' </small>
                                        </div>

                                            <div class="form-group" >
                                                <label > ' . __('Hide the final price ?', 'lfb') . ' </label >
                                                <input  type="checkbox"  name="hideFinalPrice" data-switch="switch" data-on-label="' . __('Yes', 'lfb') . '" data-off-label="' . __('No', 'lfb') . '"/>
                                                <small> ' . __('Set on true to hide the price on the last step.', 'lfb') . ' </small>
                                            </div>
                                        <div class="form-group" >
                                            <label > ' . __('Use Captcha ?  ', 'lfb') . ' </label >
                                            <input  type="checkbox"  name="useCaptcha" data-switch="switch" data-on-label="' . __('Yes', 'lfb') . '" data-off-label="' . __('No', 'lfb') . '"/>
                                        </div>

                                      <h4 > ' . __('Summary', 'lfb') . ' </h4 >
                                        <div class="form-group" >
                                            <label > ' . __('Show a summary ?', 'lfb') . ' </label >
                                            <input  type="checkbox"  name="useSummary" data-switch="switch" data-on-label="' . __('Yes', 'lfb') . '" data-off-label="' . __('No', 'lfb') . '"/>
                                            <small> ' . __('Do you want to show a summary on last step ?', 'lfb') . ' </small>
                                        </div>                                
                                        <div class="form-group" >
                                            <label > ' . __('Summary title', 'lfb') . ' </label >
                                            <input type="text" name="summary_title" class="form-control" />
                                            <small> ' . __('Something like "Summary"', 'lfb') . ' </small>
                                        </div>      
                                        <div class="form-group" >
                                            <label > ' . __('Hide quantity column', 'lfb') . ' </label >
                                            <input  type="checkbox"  name="summary_hideQt" data-switch="switch" data-on-label="' . __('Yes', 'lfb') . '" data-off-label="' . __('No', 'lfb') . '"/>
                                            <small> ' . __('Do you want to hide the column of quantities ?', 'lfb') . ' </small>
                                        </div>   
                                        <div class="form-group" >
                                            <label > ' . __('Hide zero prices', 'lfb') . ' </label >
                                            <input  type="checkbox"  name="summary_hideZero" data-switch="switch" data-on-label="' . __('Yes', 'lfb') . '" data-off-label="' . __('No', 'lfb') . '"/>
                                            <small> ' . __('Do you want to hide zero prices ?', 'lfb') . ' </small>
                                        </div>    
                                        <div class="form-group" >
                                            <label > ' . __('Hide all prices', 'lfb') . ' </label >
                                            <input  type="checkbox"  name="summary_hidePrices" data-switch="switch" data-on-label="' . __('Yes', 'lfb') . '" data-off-label="' . __('No', 'lfb') . '"/>
                                            <small> ' . __('Do you want to hide all prices ?', 'lfb') . ' </small>
                                        </div>                             
                                        <div class="form-group" >
                                            <label > ' . __('Hide total row', 'lfb') . ' </label >
                                            <input  type="checkbox"  name="summary_hideTotal" data-switch="switch" data-on-label="' . __('Yes', 'lfb') . '" data-off-label="' . __('No', 'lfb') . '"/>
                                            <small> ' . __('Do you want to hide the total row ?', 'lfb') . ' </small>
                                        </div> 

                                        <h4 > ' . __('Legal notice', 'lfb') . ' </h4 >
                                      <div>
                                           <div class="form-group" >
                                               <label > ' . __('Enable legal notice ?', 'lfb') . ' </label >
                                               <input type="checkbox"  name="legalNoticeEnable" data-switch="switch" data-on-label="' . __('Yes', 'lfb') . '" data-off-label="' . __('No', 'lfb') . '" />
                                               <small> ' . __('If true, the user must accept the notice before submitting the form', 'lfb') . ' </small>
                                           </div>
                                           <div class="form-group" >
                                              <label > ' . __('Sentence of acceptance', 'lfb') . ' </label >
                                              <input type="text" name="legalNoticeTitle" class="form-control" />
                                              <small> ' . __('Something like "I certify I completely read and I accept the legal notice by validating this form"', 'lfb') . ' </small>
                                          </div>
                                          <div class="form-group" >
                                             <label > ' . __('Content of the legal notice', 'lfb') . ' </label >
                                              <div id="lfb_legalNoticeContent"></div>
                                             <small> ' . __('Write your legal notice here', 'lfb') . ' </small>
                                         </div>
                                    </div><div class="clearfix" ></div>
                                    </div>


                                    </div>
                                    <div class="col-md-6" >
                                    <div class="lfb_paymentOption">
                                        <h4> ' . __('Payment', 'lfb') . ' </h4 >
                                        <div class="form-group " >
                                            <label > ' . __('Is subscription ?', 'lfb') . ' </label >
                                            <input type="checkbox"  name="isSubscription" data-switch="switch" data-on-label="' . __('Yes', 'lfb') . '" data-off-label="' . __('No', 'lfb') . '" />
                                            <small> ' . __('Does the price corresponds to a subscription ?', 'lfb') . ' </small>                            
                                        </div>                 
                                        <div class="form-group" >
                                            <label > ' . __('Text after price', 'lfb') . ' </label >
                                            <input type="text" name="subscription_text" class="form-control" maxlength="11" />
                                            <small> ' . __('Something like "/month"', 'lfb') . ' </small>
                                        </div>

                                        <div class="form-group" >
                                            <label > ' . __('Use paypal payment', 'lfb') . ' </label >
                                            <input type="checkbox"  name="use_paypal" data-switch="switch" data-on-label="' . __('Yes', 'lfb') . '" data-off-label="' . __('No', 'lfb') . '" />
                                            <small> ' . __('If true, the user will be redirected to the payment page', 'lfb') . ' </small>                            
                                        </div>

                                        <div id="lfb_formPaypal" >
                                         <div class="form-group" >
                                            <label > ' . __('Paypal email', 'lfb') . ' </label >
                                            <input type="text" name="paypal_email" class="form-control" />
                                            <small> ' . __('Enter your paypal email', 'lfb') . ' </small>
                                        </div>
                                        <div class="form-group" >
                                            <label > ' . __('Frequency of subscription','lfb') . ' </label >
                                            <select name="paypal_subsFrequency" class="form-control" style="margin-left: 8px; width: 80px;" />
                                                <option value="1">1</option><option value="2">2</option><option value="3">3</option><option value="4">4</option><option value="5">5</option><option value="6">6</option><option value="7">7</option><option value="8">8</option><option value="9">9</option><option value="10">10</option><option value="11">11</option><option value="12">12</option><option value="13">13</option><option value="14">14</option><option value="15">15</option><option value="16">16</option><option value="17">17</option><option value="18">18</option><option value="19">19</option><option value="20">20</option><option value="21">21</option><option value="22">22</option><option value="23">23</option><option value="24">24</option><option value="25">25</option><option value="26">26</option><option value="27">27</option><option value="28">28</option><option value="29">29</option><option value="30">30</option>
                                            </select>
                                            <select name="paypal_subsFrequencyType" class="form-control" style="display: inline-block; margin-left: 8px; width: 120px;" />
                                                <option value="D">' . __('day(s)', 'lfb') . '</option>
                                                <option value="W">' . __('week(s)', 'lfb') . '</option>
                                                <option value="M">' . __('month(s)', 'lfb') . '</option>
                                                <option value="Y">' . __('year(s)', 'lfb') . '</option>
                                            </select>
                                            <small> ' . __('Payment will be renewed every ... ?"', 'lfb') . ' </small>
                                        </div>
                                        <div class="form-group" >
                                            <label > ' . __('How many payments ?','lfb') . ' </label >
                                            <select name="paypal_subsMaxPayments" class="form-control" />
                                                <option value="0">' . __('Illimited', 'lfb') . '</option><option value="1">1</option><option value="2">2</option><option value="3">3</option><option value="4">4</option><option value="5">5</option><option value="6">6</option><option value="7">7</option><option value="8">8</option><option value="9">9</option><option value="10">10</option><option value="11">11</option><option value="12">12</option><option value="13">13</option><option value="14">14</option><option value="15">15</option><option value="16">16</option><option value="17">17</option><option value="18">18</option><option value="19">19</option><option value="20">20</option><option value="21">21</option><option value="22">22</option><option value="23">23</option><option value="24">24</option><option value="25">25</option><option value="26">26</option><option value="27">27</option><option value="28">28</option><option value="29">29</option><option value="30">30</option>
                                            </select>
                                            <small> ' . __('The subscription ends after how many payments ?', 'lfb') . ' </small>
                                        </div>                    
                                        <div class="form-group" >
                                            <label > ' . __('Percentage of the total price to pay', 'lfb') . ' </label >
                                            <input type="number" step="0.10" name="percentToPay" class="form-control" />
                                            <small> ' . __('Only this percentage will be paid by paypal', 'lfb') . ' </small>
                                        </div>
                                        <div class="form-group" >
                                            <label > ' . __('Paypal currency', 'lfb') . ' </label >
                                            <select name="paypal_currency" class="form-control" />
                                                <option value="AUD" > AUD</option >
                                                <option value="CAD" > CAD</option >
                                                <option value="CZK" > CZK</option >
                                                <option value="DKK" > DKK</option >
                                                <option value="EUR" > EUR</option >
                                                <option value="HKD" > HKD</option >
                                                <option value="HUF" > HUF</option >
                                                <option value="JPY" > JPY</option >
                                                <option value="NOK" > NOK</option >
                                                <option value="MXN" > MXN </option >
                                                <option value="NZD" > NZD</option >
                                                <option value="PLN" > PLN</option >
                                                <option value="GBP" > GBP</option >
                                                <option value="SGD" > SGD</option >
                                                <option value="SEK" > SEK</option >
                                                <option value="CHF" > CHF</option >
                                                <option value="USD" > USD</option >
                                                <option value="RUB" > RUB</option >
                                                <option value="PHP" > PHP</option >
                                                <option value="ILS" > ILS</option >
                                                <option value="BRL" > BRL</option >
                                                <option value="THB" > THB</option >                                    
                                                <option value="MYR" > MYR</option >                                    
                                            </select >
                                            <small> ' . __('Enter your paypal currency', 'lfb') . ' </small>
                                        </div>
                                        <div class="form-group" >
                                            <label > ' . __('Payment page language', 'lfb') . ' </label >
                                            <select name="paypal_languagePayment" class="form-control" />
                                                <option value="" > ' . __('Automatic', 'lfb') . '</option>
                                                <option value="ar_EG" >ar_EG/option>
                                                <option value="da_DK" >da_DK</option>
                                                <option value="de_DE" >de_DE</option>   
                                                <option value="en_US" >en_US</option>     
                                                <option value="es_ES" >es_ES</option>    
                                                <option value="fr_FR" >fr_FR</option>      
                                                <option value="id_ID" >id_ID</option>     
                                                <option value="it_IT" >it_IT</option>     
                                                <option value="ru_RU" >ru_RU</option>     
                                                <option value="zh_CN" >zh_CN</option>    
                                                <option value="zh_TW" >zh_TW</option>                                    
                                            </select >
                                            <small> ' . __('The payment page will be displayed in the selected language', 'lfb') . ' </small>
                                        </div>
                                        ';
                                $paypalReutnURL = 'http://'.$_SERVER['HTTP_HOST'] . '/' . basename(__DIR__) . '/includes/lfb-core.php?paypal=1';
                                if(isset($_SERVER['HTTPS'] ) ) {
                                $paypalReutnURL = 'https://'.$_SERVER['HTTP_HOST'] . '/' . basename(__DIR__) . '/includes/lfb-core.php?paypal=1';

                                }
                                        echo '
                                        <div class="form-group" >
                                            <label > ' . __('Use paypal IPN', 'lfb') . ' </label >
                                            <input type="checkbox"  name="paypal_useIpn" data-switch="switch" data-on-label="' . __('Yes', 'lfb') . '" data-off-label="' . __('No', 'lfb') . '" />
                                            <small> ' . __('Email will be send only if the payment has been done and verified', 'lfb') . ' </small> 
                                            <p id="lfb_infoIpn" class="alert alert-info" style="margin-top: 18px; display:none;">
                                                ' . sprintf(__('IPN requires a PayPal Business or Premier account and IPN must be configured on that account.<br/>See the <a %1$s>PayPal IPN Integration Guide</a> to learn how to set up IPN.<br/>The IPN listener URL you will need is : %2$s', 'lfb'), 'href="https://developer.paypal.com/webapps/developer/docs/classic/ipn/integration-guide/IPNSetup/" target="_blank"', '<br/><strong>' .$paypalReutnURL.'</strong>') . '
                                            </p>
                                        </div>
                                        <div class="form-group" >
                                            <label > ' . __('Use paypal Sandbox', 'lfb') . ' </label >
                                            <input type="checkbox"  name="paypal_useSandbox" data-switch="switch" data-on-label="' . __('Yes', 'lfb') . '" data-off-label="' . __('No', 'lfb') . '" />
                                            <small> ' . __('Enable Sandbox only to test with fake payments', 'lfb') . ' </small> 
                                        </div>
                                        </div> ';

                    echo '<div class="form-group" >
                                            <label > ' . __('Use stripe payment', 'lfb') . ' </label >
                                            <input type="checkbox"  name="use_stripe" data-switch="switch" data-on-label="' . __('Yes', 'lfb') . '" data-off-label="' . __('No', 'lfb') . '" />
                                            <small> ' . __('If true, the user will be redirected to the payment page', 'lfb') . ' </small>                            
                                        </div>
                                        <div class="form-group lfb_stripeField" >
                                            <label > ' . __('Stripe secret key', 'lfb') . ' </label >
                                            <input type="text" name="stripe_secretKey" class="form-control" />
                                            <small> ' . __('Enter your stripe secret key', 'lfb') . ' </small>
                                        </div>
                                        <div class="form-group lfb_stripeField" >
                                            <label > ' . __('Stripe publishable key', 'lfb') . ' </label >
                                            <input type="text" name="stripe_publishKey" class="form-control" />
                                            <small> ' . __('Enter your stripe publishable key', 'lfb') . ' </small>
                                        </div>
                                         <div class="form-group" >
                                            <label > ' . __('Frequency of subscription','lfb') . ' </label >
                                            <select name="stripe_subsFrequencyType" class="form-control" />
                                                <option value="day">' . __('day(s)', 'lfb') . '</option>
                                                <option value="week">' . __('week(s)', 'lfb') . '</option>
                                                <option value="month">' . __('month(s)', 'lfb') . '</option>
                                                <option value="year">' . __('year(s)', 'lfb') . '</option>
                                            </select>
                                            <small> ' . __('Payment will be renewed every ... ?"', 'lfb') . ' </small>
                                        </div>

                                         <div class="form-group lfb_stripeField" >
                                            <label > ' . __('Stripe currency', 'lfb') . ' </label >
                                            <select name="stripe_currency" class="form-control" />
                                                <option value="AED">United Arab Emirates Dirham
                                                </option>
                                                <option value="ALL">Albanian Lek
                                                </option>
                                                <option value="ANG">Netherlands Antillean Gulden
                                                </option>
                                                <option value="ARS">Argentine Peso
                                                </option>
                                                <option value="AUD">Australian Dollar
                                                </option>
                                                <option value="AWG">Aruban Florin
                                                </option>
                                                <option value="BBD">Barbadian Dollar
                                                </option>
                                                <option value="BDT">Bangladeshi Taka
                                                </option>
                                                <option value="BIF">Burundian Franc
                                                </option>
                                                <option value="BMD">Bermudian Dollar
                                                </option>
                                                <option value="BND">Brunei Dollar
                                                </option>
                                                <option value="BOB">Bolivian Boliviano
                                                </option>
                                                <option value="BRL">Brazilian Real
                                                </option>
                                                <option value="BSD">Bahamian Dollar
                                                </option>
                                                <option value="BWP">Botswana Pula
                                                </option>
                                                <option value="BZD">Belize Dollar
                                                </option>
                                                <option value="CAD">Canadian Dollar
                                                </option>
                                                <option value="CHF">Swiss Franc
                                                </option>
                                                <option value="CLP">Chilean Peso
                                                </option>
                                                <option value="CNY">Chinese Renminbi Yuan
                                                </option>
                                                <option value="COP">Colombian Peso
                                                </option>
                                                <option value="CRC">Costa Rican Colón
                                                </option>
                                                <option value="CVE">Cape Verdean Escudo
                                                </option>
                                                <option value="CZK">Czech Koruna
                                                </option>
                                                <option value="DJF">Djiboutian Franc
                                                </option>
                                                <option value="DKK">Danish Krone
                                                </option>
                                                <option value="DOP">Dominican Peso
                                                </option>
                                                <option value="DZD">Algerian Dinar
                                                </option>
                                                <option value="EGP">Egyptian Pound
                                                </option>
                                                <option value="ETB">Ethiopian Birr
                                                </option>
                                                <option value="EUR">Euro
                                                </option>
                                                <option value="FJD">Fijian Dollar
                                                </option>
                                                <option value="FKP">Falkland Islands Pound
                                                </option>
                                                <option value="GBP">British Pound
                                                </option>
                                                <option value="GIP">Gibraltar Pound
                                                </option>
                                                <option value="GMD">Gambian Dalasi
                                                </option>
                                                <option value="GNF">Guinean Franc
                                                </option>
                                                <option value="GTQ">Guatemalan Quetzal
                                                </option>
                                                <option value="GYD">Guyanese Dollar
                                                </option>
                                                <option value="HKD">Hong Kong Dollar
                                                </option>
                                                <option value="HNL">Honduran Lempira
                                                </option>
                                                <option value="HRK">Croatian Kuna
                                                </option>
                                                <option value="HTG">Haitian Gourde
                                                </option>
                                                <option value="HUF">Hungarian Forint
                                                </option>
                                                <option value="IDR">Indonesian Rupiah
                                                </option>
                                                <option value="ILS">Israeli New Sheqel
                                                </option>
                                                <option value="INR">Indian Rupee
                                                </option>
                                                <option value="ISK">Icelandic Króna
                                                </option>
                                                <option value="JMD">Jamaican Dollar
                                                </option>
                                                <option value="JPY">Japanese Yen
                                                </option>
                                                <option value="KES">Kenyan Shilling
                                                </option>
                                                <option value="KHR">Cambodian Riel
                                                </option>
                                                <option value="KMF">Comorian Franc
                                                </option>
                                                <option value="KRW">South Korean Won
                                                </option>
                                                <option value="KYD">Cayman Islands Dollar
                                                </option>
                                                <option value="KZT">Kazakhstani Tenge
                                                </option>
                                                <option value="LAK">Lao Kip
                                                </option>
                                                <option value="LBP">Lebanese Pound
                                                </option>
                                                <option value="LKR">Sri Lankan Rupee
                                                </option>
                                                <option value="LRD">Liberian Dollar
                                                </option>
                                                <option value="MAD">Moroccan Dirham
                                                </option>
                                                <option value="MDL">Moldovan Leu
                                                </option>
                                                <option value="MNT">Mongolian Tögrög
                                                </option>
                                                <option value="MOP">Macanese Pataca
                                                </option>
                                                <option value="MRO">Mauritanian Ouguiya
                                                </option>
                                                <option value="MUR">Mauritian Rupee
                                                </option>
                                                <option value="MVR">Maldivian Rufiyaa
                                                </option>
                                                <option value="MWK">Malawian Kwacha
                                                </option>
                                                <option value="MXN">Mexican Peso
                                                </option>
                                                <option value="MYR">Malaysian Ringgit
                                                </option>
                                                <option value="NAD">Namibian Dollar
                                                </option>
                                                <option value="NGN">Nigerian Naira
                                                </option>
                                                <option value="NIO">Nicaraguan Córdoba
                                                </option>
                                                <option value="NOK">Norwegian Krone
                                                </option>
                                                <option value="NPR">Nepalese Rupee
                                                </option>
                                                <option value="NZD">New Zealand Dollar
                                                </option>
                                                <option value="PAB">Panamanian Balboa
                                                </option>
                                                <option value="PEN">Peruvian Nuevo Sol
                                                </option>
                                                <option value="PGK">Papua New Guinean Kina
                                                </option>
                                                <option value="PHP">Philippine Peso
                                                </option>
                                                <option value="PKR">Pakistani Rupee
                                                </option>
                                                <option value="PLN">Polish Złoty
                                                </option>
                                                <option value="PYG">Paraguayan Guaraní
                                                </option>
                                                <option value="QAR">Qatari Riyal
                                                </option>
                                                <option value="RUB">Russian Ruble
                                                </option>
                                                <option value="SAR">Saudi Riyal
                                                </option>
                                                <option value="SBD">Solomon Islands Dollar
                                                </option>
                                                <option value="SCR">Seychellois Rupee
                                                </option>
                                                <option value="SEK">Swedish Krona
                                                </option>
                                                <option value="SGD">Singapore Dollar
                                                </option>
                                                <option value="SHP">Saint Helenian Pound
                                                </option>
                                                <option value="SLL">Sierra Leonean Leone
                                                </option>
                                                <option value="SOS">Somali Shilling
                                                </option>
                                                <option value="STD">São Tomé and Príncipe Dobra
                                                </option>
                                                <option value="SVC">Salvadoran Colón
                                                </option>
                                                <option value="SZL">Swazi Lilangeni
                                                </option>
                                                <option value="THB">Thai Baht
                                                </option>
                                                <option value="TOP">Tongan Paʻanga
                                                </option>
                                                <option value="TTD">Trinidad and Tobago Dollar
                                                </option>
                                                <option value="TWD">New Taiwan Dollar
                                                </option>
                                                <option value="TZS">Tanzanian Shilling
                                                </option>
                                                <option value="UAH">Ukrainian Hryvnia
                                                </option>
                                                <option value="UGX">Ugandan Shilling
                                                </option>
                                                <option value="USD">United States Dollar
                                                </option>
                                                <option value="UYU">Uruguayan Peso
                                                </option>
                                                <option value="UZS">Uzbekistani Som
                                                </option>
                                                <option value="VND">Vietnamese Đồng
                                                </option>
                                                <option value="VUV">Vanuatu Vatu
                                                </option>
                                                <option value="WST">Samoan Tala
                                                </option>
                                                <option value="XAF">Central African Cfa Franc
                                                </option>
                                                <option value="XOF">West African Cfa Franc
                                                </option>
                                                <option value="XPF">Cfp Franc
                                                </option>
                                                <option value="YER">Yemeni Rial
                                                </option>
                                                <option value="ZAR">South African Rand
                                            </select >
                                            <small> ' . __('Enter your stripe currency', 'lfb') . ' </small>
                                        </div>

                                    </div> ';


                    echo '<div class="col-md-12" id="lfb_finalStepFields" >
                                        <h4 > ' . __('Fields of the final step', 'lfb') . ' </h4 >
                                        <p style="text-align: left;" ><a href="javascript:" id="lfb_addFieldBtn" onclick="lfb_editField(0);" class="btn btn-primary" ><span class="glyphicon glyphicon-plus" ></span>' . __('Add a field', 'lfb') . ' </a></p>
                                        <table class="table table-striped table-bordered" >
                                            <thead >
                                                <tr >
                                                    <th > ' . __('Label', 'lfb') . ' </th >
                                                    <th > ' . __('Type', 'lfb') . ' </th >
                                                    <th > ' . __('Actions', 'lfb') . ' </th >
                                                </tr >
                                            </thead >
                                            <tbody >
                                            </tbody >
                                        </table >

                                    </div>
                                <div class="clearfix" ></div>
                ';


                    echo ' </div><div class="clearfix"></div></div>
                              <!--    <div class="clearfix" ></div>
                           </div> -->
                            <div role="tabpanel" class="tab-pane" id="lfb_tabDesign" >
                                <div class="row-fluid" >
                                        <div class="col-md-12" >
                                            <h4> ' . __('Design', 'lfb') . ' </h4>
                                        </div>                            
                                        <div class="col-md-4">

                                            <div class="form-group">
                                                <label>' . __('Use Google font ?', 'lfb') . '</label>
                                                <input type="checkbox"  name="useGoogleFont" data-switch="switch" data-on-label="' . __('Yes', 'lfb') . '" data-off-label="' . __('No', 'lfb') . '" />   
                                                <small>' . __('If disabled, the default theme font will be used', 'lfb') . '</small>
                                            </div>
                                            <div class="form-group" >
                                                   <label > ' . __('Google font name', 'lfb') . ' </label >
                                                   <input type="text" name="googleFontName" class="form-control" style="width: 100%;" />
                                                   <small> ' . __('ex : Lato', 'lfb') . '</small>
                                               <label></label>
                                               <a href="https://www.google.com/fonts" style="margin-top: 8px;" target="_blank" class="btn btn-default"><span class="glyphicon glyphicon-list"></span>' . __('See available Google fonts', 'lfb') . '</a>        
                                           </div>
                                            <div class="form-group" >
                                                <label > ' . __('Pictures size', 'lfb') . ' </label >
                                                <input type="number" name="item_pictures_size" class="form-control" />
                                                <small> ' . __('Enter a size in pixels(ex : 64)', 'lfb') . ' </small>
                                            </div>        
                                            <div class="form-group" >
                                                <label > ' . __('Scroll margin', 'lfb') . ' </label >
                                                <input type="number" name="scrollTopMargin" class="form-control" />
                                                <small> ' . __('Increase this value if your theme uses a fixed header', 'lfb') . '</small>
                                            </div>
                                            <div class="form-group" >
                                                <label > ' . __('Columns width', 'lfb') . ' </label >
                                                <input type="number" name="columnsWidth" class="form-control" />
                                                <small> ' . __('Set 0 to use automatic widths', 'lfb') . '</small>
                                            </div>
                                          </div>
                                        <div class="col-md-4" >
                                            <div class="form-group" >
                                                <label > ' . __('Main color', 'lfb') . ' </label >
                                                <input type="text" name="colorA" class="form-control colorpick" />
                                                <small> ' . __('ex : #1abc9c', 'lfb') . '</small>
                                            </div>
                                            <div class="form-group" >
                                                <label > ' . __('Secondary  color', 'lfb') . ' </label >
                                                <input type="text" name="colorSecondary" class="form-control colorpick" />
                                                <small> ' . __('ex : #bdc3c7', 'lfb') . '</small>
                                            </div>
                                            <div class="form-group" >
                                                <label > ' . __('Intro title & tooltips color', 'lfb') . ' </label >
                                                <input type="text" name="colorB" class="form-control colorpick" />
                                                <small> ' . __('ex : #34495e', 'lfb') . '</small>
                                            </div>        
                                            <div class="form-group" >
                                                <label > ' . __('Selected Switchbox circle color', 'lfb') . ' </label >
                                                <input type="text" name="colorCbCircleOn" class="form-control colorpick" />
                                                <small> ' . __('ex', 'lfb') . ' : #bdc3c7</small>
                                            </div>        
                                            <div class="form-group">
                                                <label>' . __('Inverse gray effect', 'lfb') . '</label>
                                                <input type="checkbox"  name="inverseGrayFx" data-switch="switch" data-on-label="' . __('Yes', 'lfb') . '" data-off-label="' . __('No', 'lfb') . '" />   
                                                <small>' . __('Apply the gray effect on unselected items ?', 'lfb') . '</small>
                                            </div>   
                                            </div>
                                        <div class="col-md-4">
                                            <div class="form-group" >
                                                  <label > ' . __('Texts color', 'lfb') . ' </label >
                                                  <input type="text" name="colorC" class="form-control colorpick" />
                                                  <small> ' . __('ex : #bdc3c7', 'lfb') . '</small>
                                              </div>

                                            <div class="form-group" >
                                                <label > ' . __('Secondary texts color', 'lfb') . ' </label >
                                                <input type="text" name="colorSecondaryTxt" class="form-control colorpick" />
                                                <small> ' . __('ex : #ffffff', 'lfb') . '</small>
                                            </div>
                                                <div class="form-group" >
                                                    <label > ' . __('Background color', 'lfb') . ' </label >
                                                    <input type="text" name="colorBg" class="form-control colorpick" />
                                                    <small> ' . __('ex : #ecf0f1', 'lfb') . '</small>
                                                </div> 
                                            <div class="form-group" >
                                                <label > ' . __('Deselected switchbox circle color', 'lfb') . ' </label >
                                                <input type="text" name="colorCbCircle" class="form-control colorpick" />
                                                <small> ' . __('ex', 'lfb') . ' : #7f8c9a</small>
                                            </div>     
                                        </div>
                                        <div class="col-md-12">

                                        <div class="form-group" >';
                    if ($settings->tdgn_enabled == 707 && strlen($settings->purchaseCode) > 8) {
                        echo '<a href="javascript:" onclick="jQuery(\'#lfb_formDesignerBtn\').trigger(\'click\');" style="float: right; margin-bottom: -18px;" class="btn btn-addon"><span class="fa fa-magic"></span>' . __('Form Designer', 'lfb') . '</a>';

                        echo '<div class="clearfix"></div>';
                    }
                    echo' <label style="margin-bottom: 18px;"> ' . __('Custom CSS rules', 'lfb') . ' </label >
                                            <textarea name="customCss" class="form-control" style=" width: 100%; max-width: inherit; height: 120px;}"></textarea>
                                            <small> ' . __('Enter your custom css code here', 'lfb') . '</small>
                                        </div>
                                        </div>
                                </div>
                                <div class="clearfix" ></div>

                            </div>

                            <div role="tabpanel" class="tab-pane" id="lfb_tabCoupons" >
                                <div class="row-fluid" >
                                    <div class="col-md-12" style="padding-top: 14px;">
                                        <h4 style="margin-top: 18px;"> ' . __('Discount coupons', 'lfb') . ' </h4>
                                     </div>
                                    <div class="col-md-6" >
                                        <div class="form-group">
                                            <label>' . __('Use discount coupons', 'lfb') . '</label>
                                            <input type="checkbox"  name="useCoupons" data-switch="switch" data-on-label="' . __('Yes', 'lfb') . '" data-off-label="' . __('No', 'lfb') . '" />   
                                            <small>' . __('If you enable this option, a discount coupon field will be displayed at end of the form', 'lfb') . '</small>
                                        </div>
                                    </div>
                                    <div class="col-md-6" >
                                        <div class="lfb_couponsContainer">
                                            <div class="form-group">
                                               <label>' . __('Label of the coupon field', 'lfb') . '</label>
                                               <input type="text"  name="couponText" class="form-control" />   
                                           </div>
                                        </div>
                                    </div>
                                    <div class="col-md-12" >
                                        <div class="lfb_couponsContainer">
                                            <p id="lfb_couponsTableBtns">
                                                <a href="javascript:" onclick="lfb_editCoupon(0);" class="btn btn-primary"><span class="glyphicon glyphicon-plus"></span>' . __('Add a new coupon', 'lfb') . '</a>
                                                <a href="javascript:" style="margin-left: 8px;" onclick="lfb_removeAllCoupons();" class="btn btn-danger"><span class="glyphicon glyphicon-trash"></span>' . __('Remove all coupons', 'lfb') . '</a>
                                            </p>
                                            <table id="lfb_couponsTable" class="table table-striped">
                                                <thead>
                                                    <tr>
                                                        <th>' . __('Coupon code', 'lfb') . '</th>
                                                        <th>' . __('Max uses', 'lfb') . '</th>
                                                        <th>' . __('Number of uses', 'lfb') . '</th>                                                
                                                        <th>' . __('Reduction', 'lfb') . '</th>
                                                        <th></th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>
                                </div>
                                <div class="clearfix" ></div>

                            </div>


                            <p style="text-align: center; padding-top: 18px;" ><a href="javascript:" onclick="lfb_saveForm();" class="btn btn-lg btn-primary" ><span class="glyphicon glyphicon-floppy-disk" ></span > ' . __('Save', 'lfb') . ' </a ></p >
                          </div>

                        </div> ';
                    echo '<div class="clearfix" ></div>';

                    echo '<div role="tabpanel" class="tab-pane" id="lfb_tabDesigner" >
                                <div class="row-fluid">
                                </div>
                            </div>
                            <div class="clearfix"></div>

                            ';

                    echo '</div> ';


                    echo '</div> ';
                    echo ' <div id="lfb_emailValueBubble" class="container-fluid" >
                            <div>
                            <div class="col-md-12" >
                                <div class="form-group" >
                                    <label > ' . __('Select an item', 'lfb') . ' </label >
                                    <select name="itemID" class="form-control" />
                                    </select >
                                </div>
                                <div class="form-group" style="display: none;" >
                                    <label > ' . __('Select an attribute', 'lfb') . ' </label >
                                    <select name="element" class="form-control" />
                                        <option value="">' . __('Price', 'lfb') . '</option>
                                        <option value="quantity">' . __('Quantity', 'lfb') . '</option>
                                        <option value="value">' . __('Value', 'lfb') . '</option>
                                    </select >
                                </div>
                                <p style="text-align: center;">
                                    <a href="javascript:" class="btn btn-primary"  onclick="lfb_saveEmailValue();"><span class="glyphicon glyphicon-disk"></span>' . __('Insert', 'lfb') . '</a>
                                </p>
                            </div>
                            </div> ';
                    echo '</div>'; // eof win lfb_emailValueBubble
                    echo ' <div id="lfb_fieldBubble" class="container-fluid" >
                            <div >
                                <input type="hidden" name="id" class="form-control" />
                            <div class="col-md-12" >
                            <div class="form-group" >
                                <label > ' . __('Label', 'lfb') . ' </label >
                                <input type="text" name="label" class="form-control" />
                                <small> ' . __('This is the field label', 'lfb') . ' </small>
                            </div>
                            <!--<div class="form-group" >
                                <label > ' . __('Order', 'lfb') . ' </label >
                                <input type="number" name="ordersort" class="form-control" />
                                <small> ' . __('Fields take place according to the order', 'lfb') . ' </small>
                            </div>-->
                            <div class="form-group" >
                                <label > ' . __('Type of field', 'lfb') . ' </label >
                                <select name="typefield" class="form-control" />
                                    <option value="input" selected="" selected > Input</option >
                                    <option value="textarea" > Textarea</option >
                                </select >
                                <small> ' . __('Choose a type', 'lfb') . ' </small>
                            </div>
                            <div class="form-group" >
                                <label > ' . __('Validation', 'lfb') . ' </label >
                                <select name="validation" class="form-control" />
                                    <option value="" selected > None</option >
                                    <option value="fill" > Must be filled </option >
                                    <option value="email" > Email</option >
                                </select >
                                <small> ' . __('Select a validation method', 'lfb') . ' </small>
                            </div>
                            <div class="form-group" >
                                <label> ' . __('Type of information', 'lfb') . ' </label >
                                <select name="fieldType" class="form-control">
                                    <option value="">' . __('Other', 'lfb') . '</option>    
                                    <option value="address">' . __('Address', 'lfb') . '</option>    
                                    <option value="city">' . __('City', 'lfb') . '</option>       
                                    <option value="country">' . __('Country', 'lfb') . '</option>      
                                    <option value="email">' . __('Email', 'lfb') . '</option>      
                                    <option value="firstName">' . __('First name', 'lfb') . '</option>  
                                    <option value="lastName">' . __('Last name', 'lfb') . '</option>  
                                    <option value="phone">' . __('Phone', 'lfb') . '</option>    
                                    <option value="state">' . __('State', 'lfb') . '</option>     
                                    <option value="zip">' . __('Zip code', 'lfb') . '</option>                           
                                </select>
                                <small> ' . __('It will allow the plugin to recover this information', 'lfb') . ' </small>
                            </div>
                            <div class="form-group" >
                                <label > ' . __('Toggle or displayed ? ', 'lfb') . ' </label >
                                <select name="visibility" class="form-control" />
                                    <option value="display" selected > Displayed</option >
                                    <option value="toggle" > Toggle</option >
                                </select >
                            </div>
                            <div class="form-group" >
                                <label ></label >
                                <a href="javascript:" onclick="lfb_saveField();" style="display: inline-block; width: 190px;" class="btn btn-primary btn-block" ><span class="glyphicon glyphicon-floppy-disk"></span>' . __('Insert', 'lfb') . '</a>
                            </div>

                            </div>
                            </div>
                        </div> ';

                    echo '<div id="lfb_winLink" class="lfb_window container-fluid"> ';
                    echo '<div class="lfb_winHeader col-md-12 palette palette-turquoise" ><span class="glyphicon glyphicon-pencil" ></span > ' . __('Edit a link', 'lfb');

                    echo ' <div class="btn-toolbar"> ';
                    echo '<div class="btn-group" > ';
                    echo '<a class="btn btn-primary" href="javascript:" ><span class="glyphicon glyphicon-remove lfb_btnWinClose" ></span ></a > ';
                    echo '</div> ';
                    echo '</div> '; // eof toolbar
                    echo '</div> '; // eof header

                    echo '<div class="clearfix"></div><div class="container-fluid lfb_container"   style="max-width: 90%;margin: 0 auto;margin-top: 18px;"> ';
                    echo '<div role="tabpanel">';
                    echo '<ul class="nav nav-tabs" role="tablist" >
                            <li role="presentation" class="active" ><a href="#lfb_linkTabGeneral" aria-controls="general" role="tab" data-toggle="tab" ><span class="glyphicon glyphicon-cog" ></span > ' . __('Link conditions', 'lfb') . ' </a ></li >
                            </ul >';
                    echo '<div class="tab-content" >';
                    echo '<div role="tabpanel" class="tab-pane active" id="lfb_linkTabGeneral" >';

                    echo '<div id="lfb_linkInteractions" > ';
                    echo '<div id="lfb_linkStepsPreview">
                            <div id="lfb_linkOriginStep" class="lfb_stepBloc "><div class="lfb_stepBlocWrapper"><h4 id="lfb_linkOriginTitle"></h4></div> </div>
                            <div id="lfb_linkStepArrow"></div>
                            <div id="lfb_linkDestinationStep" class="lfb_stepBloc  "><div class="lfb_stepBlocWrapper"><h4 id="lfb_linkDestinationTitle"></h4></div></div>
                          </div>';
                    echo '<p>'
                    . '<select id="lfb_linkOperator" class="form-control">'
                    . '<option value="">' . __('All conditions must be filled', 'lfb') . '</option>'
                    . '<option value="OR">' . __('One of the conditions must be filled', 'lfb') . '</option>'
                    . '</select>'
                    . '<a href="javascript:" class="btn btn-primary" onclick="lfb_addLinkInteraction();" ><span class="glyphicon glyphicon-plus" ></span > ' . __('Add a condition', 'lfb') . ' </a></p> ';
                    echo '<table id="lfb_conditionsTable" class="table">
                            <thead>
                                <tr>
                                    <th>' . __('Element', 'lfb') . '</th>
                                    <th>' . __('Condition', 'lfb') . '</th>
                                    <th>' . __('Value', 'lfb') . '</th>
                                    <th></th>
                                </tr>
                            </thead>
                            <tbody></tbody>
                          </table>';

                    echo '<div class="row" ><div class="col-md-12" ><p style="padding-left: 16px;padding-right: 16px; text-align: center;">'
                    . '   <a href="javascript:" onclick="lfb_linkSave();" class="btn btn-primary" style="margin-top: 24px; margin-right: 8px;" ><span class="glyphicon glyphicon-ok" ></span > ' . __('Save', 'lfb') . ' </a >
                          <a href="javascript:" onclick="lfb_linkDel();" class="btn btn-danger" style="margin-top: 24px;" ><span class="glyphicon glyphicon-trash" ></span > ' . __('Delete', 'lfb') . ' </a ></p ></div></div> ';

                    echo '<div class="clearfix"></div>';
                    echo '</div> '; // eof row
                    echo '</div> '; // eof lfb_linkInteractions
                    echo '</div> '; // eof tabpanel
                    echo '</div> '; // eof tab-content
                    echo '</div> '; // eof lfb_container

                    echo '</div> '; //eof lfb_winLink
                    // echo '</div> ';
                    //  echo '</div> ';
                    // echo '</div> ';// eof lfb_winLink



                    echo '<div id="lfb_winRedirection" class="lfb_window container-fluid"> ';
                    echo '<div class="lfb_winHeader col-md-12 palette palette-turquoise" ><span class="glyphicon glyphicon-pencil" ></span > ' . __('Edit a redirection', 'lfb');

                    echo ' <div class="btn-toolbar"> ';
                    echo '<div class="btn-group" > ';
                    echo '<a class="btn btn-primary" href="javascript:" ><span class="glyphicon glyphicon-remove lfb_btnWinClose" ></span ></a > ';
                    echo '</div> ';
                    echo '</div> '; // eof toolbar
                    echo '</div> '; // eof header

                    echo '<div class="clearfix"></div><div class="container-fluid lfb_container"   style="max-width: 90%;margin: 0 auto;margin-top: 18px;"> ';
                    echo '<div role="tabpanel">';
                    echo '<ul class="nav nav-tabs" role="tablist" >
                            <li role="presentation" class="active" ><a href="#lfb_redirTabGeneral" aria-controls="general" role="tab" data-toggle="tab" ><span class="glyphicon glyphicon-cog" ></span > ' . __('Link conditions', 'lfb') . ' </a ></li >
                            </ul >';
                    echo '<div class="tab-content" >';
                    echo '<div role="tabpanel" class="tab-pane active" id="lfb_redirTabGeneral" >';

                    echo '<div id="lfb_redirInteractions" > ';
                    echo '<div id="lfb_redirStepsPreview">
                            <div id="lfb_showIcon"></div>
                          </div>';
                    echo '<p>'
                    . '<div class="form-group">'
                    . '<label>' . __('URL', 'lfb') . ' : </label>'
                    . '<input type="text" id="lfb_redirUrl" class="form-control"/>'
                    . '</div>'
                    . '</p>';
                    echo '<p>'
                    . '<select id="lfb_redirOperator" class="form-control">'
                    . '<option value="">' . __('All conditions must be filled', 'lfb') . '</option>'
                    . '<option value="OR">' . __('One of the conditions must be filled', 'lfb') . '</option>'
                    . '</select>'
                    . '<a href="javascript:" class="btn btn-primary" onclick="lfb_addRedirInteraction();" ><span class="glyphicon glyphicon-plus" ></span > ' . __('Add a condition', 'lfb') . ' </a></p> ';
                    echo '<table id="lfb_redirConditionsTable" class="table">
                            <thead>
                                <tr>
                                    <th>' . __('Element', 'lfb') . '</th>
                                    <th>' . __('Condition', 'lfb') . '</th>
                                    <th>' . __('Value', 'lfb') . '</th>
                                    <th></th>
                                </tr>
                            </thead>
                            <tbody></tbody>
                          </table>';

                    echo '<div class="row" ><div class="col-md-12" ><p style="padding-left: 16px;padding-right: 16px; text-align: center;">'
                    . '   <a href="javascript:" onclick="lfb_redirSave();" class="btn btn-primary" style="margin-top: 24px; margin-right: 8px;" ><span class="glyphicon glyphicon-ok" ></span > ' . __('Save', 'lfb') . ' </a ></p ></div></div> ';

                    echo '<div class="clearfix"></div>';
                    echo '</div> '; // eof row
                    echo '</div> '; // eof lfb_linkInteractions
                    echo '</div> '; // eof tabpanel
                    echo '</div> '; // eof tab-content
                    echo '</div> '; // eof lfb_container

                    echo '</div> '; //eof lfb_winRedirection


                    echo '<div id="lfb_winCalculationConditions" class="lfb_window container-fluid"> ';
                    echo '<div class="lfb_winHeader col-md-12 palette palette-turquoise" ><span class="glyphicon glyphicon-pencil" ></span > ' . __('Add a condition', 'lfb');

                    echo ' <div class="btn-toolbar"> ';
                    echo '<div class="btn-group" > ';
                    echo '<a class="btn btn-primary" href="javascript:" ><span class="glyphicon glyphicon-remove lfb_btnWinClose" ></span ></a > ';
                    echo '</div> ';
                    echo '</div> '; // eof toolbar
                    echo '</div> '; // eof header

                    echo '<div class="clearfix"></div><div class="container-fluid lfb_container"   style="max-width: 90%;margin: 0 auto;margin-top: 18px;"> ';
                    //echo '<div role="tabpanel">';
                    echo '<ul class="nav nav-tabs" role="tablist" >
                            <li role="presentation" class="active" ><a href="#lfb_calcTabGeneral" aria-controls="general" role="tab" data-toggle="tab" ><span class="glyphicon glyphicon-cog" ></span > ' . __('Conditions', 'lfb') . ' </a ></li >
                            </ul >';
                    echo '<div class="tab-content" >';
                    echo '<div role="tabpanel" class="tab-pane active" id="lfb_calcTabGeneral" >';

                    echo '<div id="#lfb_calcInteractions" > ';
                    echo '<div id="lfb_calcStepsPreview">
                            <div id="lfb_calcIcon"></div>
                          </div>';
                    echo '<p>'
                    . '<select id="lfb_calcOperator" class="form-control">'
                    . '<option value="">' . __('All conditions must be filled', 'lfb') . '</option>'
                    . '<option value="OR">' . __('One of the conditions must be filled', 'lfb') . '</option>'
                    . '</select>'
                    . '<a href="javascript:" class="btn btn-primary" onclick="lfb_addCalcInteraction();" ><span class="glyphicon glyphicon-plus" ></span > ' . __('Add a condition', 'lfb') . ' </a></p> ';
                    echo '<table id="lfb_calcConditionsTable" class="table">
                            <thead>
                                <tr>
                                    <th>' . __('Element', 'lfb') . '</th>
                                    <th>' . __('Condition', 'lfb') . '</th>
                                    <th>' . __('Value', 'lfb') . '</th>
                                    <th></th>
                                </tr>
                            </thead>
                            <tbody></tbody>
                          </table>';

                    echo '<div class="row" ><div class="col-md-12" ><p style="padding-left: 16px;padding-right: 16px; text-align: center;">'
                    . '   <a href="javascript:" onclick="lfb_calcConditionSave();" class="btn btn-primary" style="margin-top: 24px; margin-right: 8px; margin-top: 18px;" ><span class="glyphicon glyphicon-ok" ></span > ' . __('Save', 'lfb') . ' </a>';
                    echo '<div class="clearfix"></div>';
                    echo '</div> '; // eof row
                    echo '<div class="clearfix"></div>';
                    echo '</div> '; // eof lfb_calcInteractions
                    echo '</div> '; // eof lfb_calcTabGeneral
                    echo '</div> '; // eof tabpanel
                    echo '</div> '; // eof tab-content
                    echo '</div> '; // eof lfb_container
                    echo '</div> '; // eof lfb_winCalculationConditions



                    echo '<div id="lfb_winShowConditions" class="lfb_window container-fluid"> ';
                    echo '<div class="lfb_winHeader col-md-12 palette palette-turquoise" ><span class="glyphicon glyphicon-pencil" ></span > ' . __('Add a condition', 'lfb');

                    echo ' <div class="btn-toolbar"> ';
                    echo '<div class="btn-group" > ';
                    echo '<a class="btn btn-primary" href="javascript:" ><span class="glyphicon glyphicon-remove lfb_btnWinClose" ></span ></a > ';
                    echo '</div> ';
                    echo '</div> '; // eof toolbar
                    echo '</div> '; // eof header

                    echo '<div class="clearfix"></div><div class="container-fluid lfb_container"   style="max-width: 90%;margin: 0 auto;margin-top: 18px;"> ';
                    //echo '<div role="tabpanel">';
                    echo '<ul class="nav nav-tabs" role="tablist" >
                            <li role="presentation" class="active" ><a href="#lfb_showTabGeneral" aria-controls="general" role="tab" data-toggle="tab" ><span class="glyphicon glyphicon-cog" ></span > ' . __('Conditions', 'lfb') . ' </a ></li >
                            </ul >';
                    echo '<div class="tab-content" >';
                    echo '<div role="tabpanel" class="tab-pane active" id="lfb_showTabGeneral" >';

                    echo '<div id="#lfb_showInteractions" > ';
                    echo '<div id="lfb_showStepsPreview">
                            <div id="lfb_showIcon"></div>
                          </div>';
                    echo '<p>'
                    . '<select id="lfb_showOperator" class="form-control">'
                    . '<option value="">' . __('All conditions must be filled', 'lfb') . '</option>'
                    . '<option value="OR">' . __('One of the conditions must be filled', 'lfb') . '</option>'
                    . '</select>'
                    . '<a href="javascript:" class="btn btn-primary" onclick="lfb_addShowInteraction();" ><span class="glyphicon glyphicon-plus" ></span > ' . __('Add a condition', 'lfb') . ' </a></p> ';
                    echo '<table id="lfb_showConditionsTable" class="table">
                            <thead>
                                <tr>
                                    <th>' . __('Element', 'lfb') . '</th>
                                    <th>' . __('Condition', 'lfb') . '</th>
                                    <th>' . __('Value', 'lfb') . '</th>
                                    <th></th>
                                </tr>
                            </thead>
                            <tbody></tbody>
                          </table>';

                    echo '<div class="row" ><div class="col-md-12" ><p style="padding-left: 16px;padding-right: 16px; text-align: center;">'
                    . '   <a href="javascript:" onclick="lfb_showConditionSave();" class="btn btn-primary" style="margin-top: 24px; margin-right: 8px;" ><span class="glyphicon glyphicon-ok" ></span > ' . __('Save', 'lfb') . ' </a >';
                    echo '<div class="clearfix"></div>';
                    echo '</div> '; // eof row
                    echo '</div> '; // eof lfb_calcInteractions
                    echo '</div> '; // eof lfb_calcTabGeneral
                    echo '</div> '; // eof tabpanel
                    echo '</div> '; // eof tab-content
                    echo '</div> '; // eof lfb_container
                    echo '</div> '; // eof lfb_winShowConditions


                    echo '<div id="lfb_winShowStepConditions" class="lfb_window container-fluid"> ';
                    echo '<div class="lfb_winHeader col-md-12 palette palette-turquoise" ><span class="glyphicon glyphicon-pencil" ></span > ' . __('Add a condition', 'lfb');

                    echo ' <div class="btn-toolbar"> ';
                    echo '<div class="btn-group" > ';
                    echo '<a class="btn btn-primary" href="javascript:" ><span class="glyphicon glyphicon-remove lfb_btnWinClose" ></span ></a > ';
                    echo '</div> ';
                    echo '</div> '; // eof toolbar
                    echo '</div> '; // eof header

                    echo '<div class="clearfix"></div><div class="container-fluid lfb_container"   style="max-width: 90%;margin: 0 auto;margin-top: 18px;"> ';
                    //echo '<div role="tabpanel">';
                    echo '<ul class="nav nav-tabs" role="tablist" >
                            <li role="presentation" class="active" ><a href="#lfb_showStepTabGeneral" aria-controls="general" role="tab" data-toggle="tab" ><span class="glyphicon glyphicon-cog" ></span > ' . __('Conditions', 'lfb') . ' </a ></li >
                            </ul >';
                    echo '<div class="tab-content" >';
                    echo '<div role="tabpanel" class="tab-pane active" id="lfb_showStepTabGeneral" >';

                    echo '<div id="lfb_showStepInteractions" > ';
                    echo '<div id="lfb_showStepStepsPreview">
                            <div id="lfb_showIcon"></div>
                          </div>';
                    echo '<p>'
                    . '<select id="lfb_showStepOperator" class="form-control">'
                    . '<option value="">' . __('All conditions must be filled', 'lfb') . '</option>'
                    . '<option value="OR">' . __('One of the conditions must be filled', 'lfb') . '</option>'
                    . '</select>'
                    . '<a href="javascript:" class="btn btn-primary" onclick="lfb_addShowStepInteraction();" ><span class="glyphicon glyphicon-plus" ></span > ' . __('Add a condition', 'lfb') . ' </a></p> ';
                    echo '<table id="lfb_showStepConditionsTable" class="table">
                            <thead>
                                <tr>
                                    <th>' . __('Element', 'lfb') . '</th>
                                    <th>' . __('Condition', 'lfb') . '</th>
                                    <th>' . __('Value', 'lfb') . '</th>
                                    <th></th>
                                </tr>
                            </thead>
                            <tbody></tbody>
                          </table>';

                    echo '<div class="row" ><div class="col-md-12" ><p style="padding-left: 16px;padding-right: 16px; text-align: center;">'
                    . '   <a href="javascript:" onclick="lfb_showStepConditionSave();" class="btn btn-primary" style="margin-top: 24px; margin-right: 8px;" ><span class="glyphicon glyphicon-ok" ></span > ' . __('Save', 'lfb') . ' </a >';
                    echo '<div class="clearfix"></div>';
                    echo '</div> '; // eof row
                    echo '</div> '; // eof lfb_calcInteractions
                    echo '</div> '; // eof lfb_calcTabGeneral
                    echo '</div> '; // eof tabpanel
                    echo '</div> '; // eof tab-content
                    echo '</div> '; // eof lfb_container
                    echo '</div> '; // eof lfb_winShowConditions




                    echo '<div id="lfb_winStep" class="lfb_window container-fluid">';
                    echo '<div class="lfb_winHeader col-md-12 palette palette-turquoise"><span class="glyphicon glyphicon-pencil"></span>' . __('Edit a step', 'lfb');

                    echo '<div class="btn-toolbar">';
                    echo '<div class="btn-group">';
                    echo '<a class="btn btn-primary" href="javascript:"><span class="glyphicon glyphicon-remove lfb_btnWinClose"></span></a>';
                    echo '</div>';
                    echo '</div>'; // eof toolbar
                    echo '</div>'; // eof header
                    echo '<div class="clearfix"></div>';
                    echo '<div class="container-fluid  lfb_container"  style="max-width: 90%;margin: 0 auto;margin-top: 18px;">';
                    echo '<div role="tabpanel">';
                    echo '<ul class="nav nav-tabs" role="tablist" >
                            <li role="presentation" class="active" ><a href="#lfb_stepTabGeneral" aria-controls="general" role="tab" data-toggle="tab" ><span class="glyphicon glyphicon-cog" ></span > ' . __('Step', 'lfb') . ' </a ></li >
                            </ul >';
                    echo '<div class="tab-content" >';
                    echo '<div role="tabpanel" class="tab-pane active" id="lfb_stepTabGeneral" >';
                    echo '<h4 style="padding-left: 14px; padding-right: 14px;">' . __('Step options', 'lfb') . ' </h4>';
                    echo '<div class="col-md-3">';
                    echo '<div class="form-group" >
                                <label> ' . __('Title', 'lfb') . ' </label >
                                <input type="text" name="title" class="form-control" />
                                <small> ' . __('This is the step name', 'lfb') . ' </small>
                            </div>';
                    echo '<div class="form-group" >
                                <label> ' . __('Description', 'lfb') . ' </label >
                                <input type="text" name="description" class="form-control" />
                                <small> ' . __('A facultative description', 'lfb') . ' </small>
                            </div>';

                    echo '</div>'; // eof col-md-4
                    echo '<div class="col-md-3">';

                    echo '
                            <div class="form-group" >
                                <label> ' . __('Max items per row', 'lfb') . ' </label >
                                 <input type="number" name="itemsPerRow" class="form-control" min="0" />
                                <small> ' . __('Leave 0 to fill the full width', 'lfb') . ' </small>
                            </div>
                            ';
                    echo '<div class="">
                                <label></label >
                                <textarea name="showConditions" style="display: none;"></textarea>
                                <input type="hidden" name="showConditionsOperator" style="display: none;"/>
                            </div>';
                    echo '<div class="form-group" style="height: 86px; margin-bottom: 0px; top: -18px;">
                                <label> ' . __('Selection required', 'lfb') . ' </label ><br/>
                                <input type="checkbox"  name="itemRequired" data-switch="switch" data-on-label="' . __('Yes', 'lfb') . '" data-off-label="' . __('No', 'lfb') . '" />

                                <!-- <select name="itemRequired" class="form-control" />
                                    <option value="0" > ' . __('No', 'lfb') . ' </option >
                                    <option value="1" > ' . __('Yes', 'lfb') . ' </option >
                                </select>-->
                                <small> ' . __('If true, the user must select at least one item to continue', 'lfb') . ' </small>
                            </div>';

                    echo '</div>'; // eof col-md-4
                    echo '<div class="col-md-3">';
                    echo '<div class="form-group" style="height: 86px; margin-bottom: 34px;" >
                                <label> ' . __('Show it depending on conditions ?', 'lfb') . ' </label ><br/>
                                <input type="checkbox"  name="useShowConditions" data-switch="switch" data-on-label="' . __('Yes', 'lfb') . '" data-off-label="' . __('No', 'lfb') . '" />

                                <a href="javascript:" id="showConditionsStepBtn" onclick="lfb_editShowStepConditions();" class="btn btn-primary btn-circle" style="margin-left: 8px;"><span class="glyphicon glyphicon-pencil"></span></a>
                                <small> ' . __('This step will be displayed only if the conditions are filled', 'lfb') . ' </small>
                            </div>
                            <div class="form-group" style="height: 86px; margin-bottom: 0px;  top: -18px;" >
                                <label> ' . __('Show in email/summary ?', 'lfb') . ' </label ><br/>
                                <input type="checkbox"  name="showInSummary" data-switch="switch" data-on-label="' . __('Yes', 'lfb') . '" data-off-label="' . __('No', 'lfb') . '" />

                                <!-- <select name="showInSummary" class="form-control" >
                                    <option value="0" > ' . __('No', 'lfb') . ' </option >
                                    <option value="1" > ' . __('Yes', 'lfb') . ' </option >
                                </select>-->
                                <small> ' . __('This step will be displayed in the summary', 'lfb') . ' </small>
                            </div>';


                    echo '</div>'; // eof col-md-3
                    echo '<div class="col-md-3">';
                    echo '<div class="form-group" style="height: 86px; margin-bottom: 34px;" >
                                <label> ' . __('Hide the next step button ?', 'lfb') . ' </label ><br/>
                                <input type="checkbox"  name="hideNextStepBtn" data-switch="switch" data-on-label="' . __('Yes', 'lfb') . '" data-off-label="' . __('No', 'lfb') . '" />

                            </div>';
                    echo '</div>'; // eof col-md-3
                    echo '<div class="col-md-12" style="padding-left: 14px; padding-right: 14px;">';
                    echo '<p style="text-align:center;"><a href="javascript:" class="btn btn-primary" onclick="lfb_saveStep();"><span class="glyphicon glyphicon-floppy-disk"></span>' . __('Save', 'lfb') . '</a></p>';
                    echo '</div>'; // eof col-md-12
                    echo '<div class="clearfix"></div>';


                    echo '<div role="tabpanel" id="lfb_itemsList" style="margin-top: 24px;padding-left: 14px; padding-right: 14px;">';
                    echo '<h4>' . __('Items List', 'lfb') . ' </h4>';
                    echo '<div id="lfb_itemTab" >';
                    echo '<div class="col-md-12">';
                    echo '<p style="padding-top: 24px;"><a href="javascript:" onclick="lfb_editItem(0);" class="btn btn-default"><span class="glyphicon glyphicon-plus"></span>' . __('Add a new Item', 'lfb') . '</a></p>';
                    echo '<table id="lfb_itemsTable" class="table">';
                    echo '<thead>
                            <th>' . __('Title', 'lfb') . '</th>
                            <th>' . __('Group', 'lfb') . '</th>
                            <th>' . __('Actions', 'lfb') . '</th>
                        </thead>';
                    echo '<tbody>';
                    echo '</tbody>';
                    echo '</table>';
                    echo '</div>'; // eof col-md-12
                    echo '<div class="clearfix"></div>';
                    echo '</div>'; // eof lfb_itemTab
                    echo '</div>'; // eof tabpanel

                    echo '</div>'; // eof lfb_stepTabGeneral
                    echo '</div>'; // eof tab-content
                    echo '</div>'; // eof tabpanel

                    echo '</div>'; // eof lfb_container
                    echo '</div>'; // eof win step


                    echo '<div id="lfb_winItem" class="lfb_window container-fluid">';
                    echo '<div class="lfb_winHeader col-md-12 palette palette-turquoise"><span class="glyphicon glyphicon-pencil"></span>' . __('Edit an item', 'lfb');

                    echo '<div class="btn-toolbar">';
                    echo '<div class="btn-group">';
                    echo '<a class="btn btn-primary" href="javascript:"><span class="glyphicon glyphicon-remove lfb_btnWinClose"></span></a>';
                    echo '</div>';
                    echo '</div>'; // eof toolbar
                    echo '</div>'; // eof header
                    echo '<div class="clearfix"></div>';
                    echo '<div class="container-fluid  lfb_container"  style="max-width: 90%;margin: 0 auto;margin-top: 18px;">';
                    echo '<div role="tabpanel">';
                    echo '<ul class="nav nav-tabs" role="tablist" >
                            <li role="presentation" class="active" ><a href="#lfb_itemTabGeneral" aria-controls="general" role="tab" data-toggle="tab" ><span class="glyphicon glyphicon-cog" ></span > ' . __('Item options', 'lfb') . ' </a ></li >
                            </ul >';
                    echo '<div class="tab-content" >';
                    echo '<div role="tabpanel" class="tab-pane active" id="lfb_itemTabGeneral" >';
                    echo '<div class="col-md-6">';
                    echo '<div class="form-group" >
                                <label> ' . __('Title', 'lfb') . ' </label >
                                <input type="text" name="title" class="form-control" />
                                <small> ' . __('This is the item name', 'lfb') . ' </small>
                            </div>';
                    echo '<div class="form-group" >
                                <label> ' . __('Small description', 'lfb') . ' </label >
                                <textarea name="description" class="form-control" style="height: 42px;" ></textarea>
                                <small> ' . __('Item small description. You can leave it empty.', 'lfb') . ' </small>
                            </div>';
                    echo '<div class="form-group" >
                                <label> ' . __('Group name', 'lfb') . ' </label >
                                <input type="text" name="groupitems" class="form-control" />
                                <small> ' . __('Only one of the items of a same group can be selected', 'lfb') . ' </small>
                            </div>';

                    echo '<div class="form-group" >
                                <label> ' . __('Type', 'lfb') . ' </label >
                                <select name="type" class="form-control">
                                    <option value="picture">' . __('Picture', 'lfb') . '</option>
                                    <option value="checkbox">' . __('Checkbox', 'lfb') . '</option>
                                    <option value="textfield">' . __('Text field', 'lfb') . '</option>
                                    <option value="numberfield">' . __('Number field', 'lfb') . '</option>
                                    <option value="textarea">' . __('Text area', 'lfb') . '</option>
                                    <option value="select">' . __('Select field', 'lfb') . '</option>
                                    <option value="datepicker">' . __('Date picker', 'lfb') . '</option>
                                    <option value="filefield">' . __('File field', 'lfb') . '</option>
                                    <option value="colorpicker" >' . __('Color picker', 'lfb') . '</option>
                                    <option value="richtext">' . __('Rich Text', 'lfb') . '</option>
                                    <option value="slider">' . __('Slider', 'lfb') . '</option>
                                </select>
                                <small> ' . __('Select a type of item', 'lfb') . ' </small>
                            </div>';

                    echo '<div class="form-group lfb_textOnly" >
                                <label> ' . __('Type of information', 'lfb') . ' </label >
                                <select name="fieldType" class="form-control">
                                    <option value="">' . __('Other', 'lfb') . '</option>    
                                    <option value="address">' . __('Address', 'lfb') . '</option>    
                                    <option value="city">' . __('City', 'lfb') . '</option>       
                                    <option value="country">' . __('Country', 'lfb') . '</option>      
                                    <option value="email">' . __('Email', 'lfb') . '</option>      
                                    <option value="firstName">' . __('First name', 'lfb') . '</option>  
                                    <option value="lastName">' . __('Last name', 'lfb') . '</option>  
                                    <option value="phone">' . __('Phone', 'lfb') . '</option>    
                                    <option value="state">' . __('State', 'lfb') . '</option>     
                                    <option value="zip">' . __('Zip code', 'lfb') . '</option>                           
                                </select>
                                <small> ' . __('It will allow the plugin to recover this information', 'lfb') . ' </small>
                            </div>';

                    echo '<div class="form-group " >
                                <label> ' . __('Min size', 'lfb') . ' </label >
                                <input type="number" name="minSize" class="form-control" />
                                <small> ' . __('Fill this field to limit the the minimum number of characters', 'lfb') . ' </small>
                            </div>';
                    echo '<div class="form-group " >
                                <label> ' . __('Max size', 'lfb') . ' </label >
                                <input type="number" name="maxSize" class="form-control" />
                                <small> ' . __('Fill this field to limit the the maximum number of characters', 'lfb') . ' </small>
                            </div>';

                    echo '<div id="lfb_itemOptionsValuesPanel"><table id="lfb_itemOptionsValues" class="table">';
                    echo '<thead>';
                    echo '<tr>';
                    echo '<th colspan="3">' . __('Options of select field', 'lfb') . '</th>';
                    echo '</tr>';
                    echo '</thead>';
                    echo '<tbody>';
                    echo '<tr class="static">';
                    echo '<td><div class="form-group" style="top: 10px;"><input type="text" id="option_new_value" class="form-control" value="" placeholder="' . __('Option value', 'lfb') . '"></div></td>'
                    . '<td><div class="form-group" style="top: 10px;"><input type="number" id="option_new_price" step="any" class="form-control" value="0" placeholder="' . __('Option price', 'lfb') . '"></div></td>';
                    echo '<td style="width: 200px;"><a href="javascript:" onclick="lfb_add_option();" class="btn btn-default"><span class="glyphicon glyphicon-plus" style="margin-right:8px;"></span>' . __('Add a new option', 'lfb') . '</a></td>';
                    echo '</tr>';
                    echo '</tbody>';
                    echo '</table></div>';



                    echo '<div class="form-group picOnly" >
                                <label > ' . __('Picture', 'lfb') . ' </label >
                                <input type="text" name="image" class="form-control " style="max-width: 140px; margin-right: 10px;display: inline-block;" />
                                <a class="btn btn-default imageBtn" style=" display: inline-block;">' . __('Upload Image', 'lfb') . '</a>
                                <small display: block;> ' . __('Select a picture', 'lfb') . ' </small>
                            </div>';
                    echo '<input type="hidden" name="imageDes"/>';
                    echo '<div class="form-group picOnly" >
                                <label> ' . __('Tint image ?', 'lfb') . ' </label >
                                <input type="checkbox"  name="imageTint" data-switch="switch" data-on-label="' . __('Yes', 'lfb') . '" data-off-label="' . __('No', 'lfb') . '" />
                                <small> ' . __('Automatically fill the picture with the main color', 'lfb') . ' </small>
                            </div>';

                    echo '<div class="form-group " >
                                <label> ' . __('Open url on click ?', 'lfb') . ' </label >
                                <input type="text"  name="urlTarget" class="form-control" placeholder="http://..."  />
                                <small> ' . __('If you fill an url, it will be opened in a new tab on selection', 'lfb') . ' </small>
                            </div>';

                    echo '<div class="form-group" >
                                <label> ' . __('Display price in title ?', 'lfb') . ' </label >
                                <input type="checkbox"  name="showPrice" data-switch="switch" data-on-label="' . __('Yes', 'lfb') . '" data-off-label="' . __('No', 'lfb') . '" />
                                <small> ' . __('Shows the price in the item title', 'lfb') . ' </small>
                            </div>';
                    echo '<div class="form-group" >
                                <label> ' . __('Use column or row ?', 'lfb') . ' </label >
                                <select name="useRow" class="form-control">
                                    <option value="0">' . __('Column', 'lfb') . '</option>
                                    <option value="1">' . __('Row', 'lfb') . '</option>
                                </select>
                                <small> ' . __('The item will be displayed as column or full row', 'lfb') . ' </small>
                            </div>';


                    echo '<div class="form-group" >
                                <label> ' . __('Show it depending on conditions ?', 'lfb') . ' </label >
                                <input type="checkbox"  name="useShowConditions" data-switch="switch" data-on-label="' . __('Yes', 'lfb') . '" data-off-label="' . __('No', 'lfb') . '" />
                                <small> ' . __('This item will be displayed only if the conditions are filled', 'lfb') . ' </small>
                            </div>';

                    echo '<div class="form-group" >
                                <label></label >
                                <textarea name="showConditions" style="display: none;"></textarea>
                                <input type="hidden" name="showConditionsOperator" style="display: none;"/>
                                <a href="javascript:" onclick="lfb_editShowConditions();" class="btn btn-primary"><span class="glyphicon glyphicon-question-sign"></span> ' . __('Edit conditions', 'lfb') . '</a>
                            </div>';

                    echo '</div>'; // eof col-md-6
                    echo '<div class="col-md-6">';

                    echo '<div class="form-group wooMasked" >
                                <label> ' . __('Price', 'lfb') . ' </label><label style="display: none;">' . __('Percentage', 'lfb') . '</label>
                                <input type="number" name="price" step="any" class="form-control" />
                                <small> ' . __('Sets the item price', 'lfb') . ' </small>
                            </div>';
                    echo '<div class="form-group" >
                               <label> ' . __('Use calculation ?', 'lfb') . ' </label >
                               <input type="checkbox"  name="useCalculation" data-switch="switch" data-on-label="' . __('Yes', 'lfb') . '" data-off-label="' . __('No', 'lfb') . '" />
                                <small> ' . __('If checked, the price will be replaced by a calculation', 'lfb') . ' </small>
                            </div>';

                    echo '<div class="form-group" >
                                <label> ' . __('Calculation', 'lfb') . ' </label >
                                <a href="javascript:" onclick="lfb_addCalculationValue();" class="btn btn-default" style="margin:4px;margin-bottom: 8px;"><span class="glyphicon glyphicon-plus"></span>' . __('Add a value', 'lfb') . '</a>
                                <a href="javascript:" onclick="lfb_addCalculationCondition();" class="btn btn-default" style="margin:4px;margin-bottom: 8px; margin-left:0px;"><span class="glyphicon glyphicon-plus"></span>' . __('Add a condition', 'lfb') . '</a>
                                <a href="javascript:" id="lfb_addDistanceBtn" onclick="lfb_editDistanceValue(false);" class="btn btn-default" style="margin:4px;margin-bottom: 8px;"><span class="glyphicon glyphicon-plus"></span>' . __('Add a distance', 'lfb') . '</a><br/>

                                <textarea name="calculation" class="form-control" style="max-width: 100%; width: 100%;" ></textarea>
                                <small> ' . __('Use the buttons to easily create your calculation', 'lfb') . ' </small>
                                <div class="alert alert-info" style="margin-top: 18px;">
                                    <p>' . __('Example of calculation', 'lfb') . ' :</p>
                                    <pre>10
            if(([item-3_quantity] >5) ) {
                    ([item-3_price]/2)*([item-1_quantity])
            } </pre>
                                <p style="font-size: 12px;">' . __('Here, the default price of the item will be $10. If the item #3 is selected, the price of the current item will be the half of the item #3 calculated price multiplied by the selected quantity of the item #1.', 'lfb') . '</p>
                                </div>
                            </div>';


                    echo '<div class="form-group" >
                                <label> ' . __('Operator', 'lfb') . ' </label >
                                <select name="operation" class="form-control">
                                    <option value="+">' . __('+', 'lfb') . '</option>
                                    <option value="-">' . __('-', 'lfb') . '</option>
                                    <option value="x">' . __('x', 'lfb') . '</option>
                                    <option value="/">' . __('/', 'lfb') . '</option>
                                </select>
                                <small> ' . __('+ and - allow you to add or remove the price of the total price, * and / allow you to add or remove a percentage from the total price', 'lfb') . ' </small>
                            </div>';



                    echo '<div class="form-group" >
                               <label> ' . __('Is selected ?', 'lfb') . ' </label >
                               <input type="checkbox"  name="ischecked" data-switch="switch" data-on-label="' . __('Yes', 'lfb') . '" data-off-label="' . __('No', 'lfb') . '" />
                                <small> ' . __('Is the item selected by default ?', 'lfb') . ' </small>
                            </div>';
                    echo '<div class="form-group" >
                               <label> ' . __('Is hidden ?', 'lfb') . ' </label >
                               <input type="checkbox"  name="isHidden" data-switch="switch" data-on-label="' . __('Yes', 'lfb') . '" data-off-label="' . __('No', 'lfb') . '" />
                                <small> ' . __('Item will be used in the calculation, but will not be displayed', 'lfb') . ' </small>
                            </div>';
                    echo '<div class="form-group" >
                                <label> ' . __('Is required ?', 'lfb') . ' </label >
                                <input type="checkbox"  name="isRequired" data-switch="switch" data-on-label="' . __('Yes', 'lfb') . '" data-off-label="' . __('No', 'lfb') . '" />
                                <small> ' . __('Is the item required to continue ?', 'lfb') . ' </small>
                            </div>';

                    echo '<div class="form-group" >
                                <label> ' . __('Disable first option selection', 'lfb') . ' </label >
                                <input type="checkbox"  name="firstValueDisabled" data-switch="switch" data-on-label="' . __('Yes', 'lfb') . '" data-off-label="' . __('No', 'lfb') . '" />
                                <small> ' . __("The first option can't be selected", 'lfb') . ' </small>
                            </div>';

                    echo '<div class="form-group" >
                                <label> ' . __('Use payment only if selected', 'lfb') . ' </label >
                                <input type="checkbox"  name="usePaypalIfChecked" data-switch="switch" data-on-label="' . __('Yes', 'lfb') . '" data-off-label="' . __('No', 'lfb') . '" />
                                <small> ' . __('Payment will be used only if this item is selected', 'lfb') . ' </small>
                            </div>';

                    echo '<div class="form-group" >
                                <label> ' . __('Show in email/summary ?', 'lfb') . ' </label >
                                <input type="checkbox"  name="showInSummary" data-switch="switch" data-on-label="' . __('Yes', 'lfb') . '" data-off-label="' . __('No', 'lfb') . '" />
                                <small> ' . __('This item will be displayed in the summary if the user selects it', 'lfb') . ' </small>
                            </div>';

                    echo '<div class="form-group" >
                                <label> ' . __('Hide quantity in summary ?', 'lfb') . ' </label >
                                <input type="checkbox"  name="hideQtSummary" data-switch="switch" data-on-label="' . __('Yes', 'lfb') . '" data-off-label="' . __('No', 'lfb') . '" />
                                <small> ' . __('The quantity of this item will be hidden in the summary', 'lfb') . ' </small>
                            </div>';


                    echo '<div class="form-group" >
                                <label> ' . __('Default value', 'lfb') . ' </label >
                                <input type="text" name="defaultValue" class="form-control" />
                                <small> ' . __('Defines the default value of this field', 'lfb') . ' </small>
                            </div>';

                    echo '<div class="form-group" >
                                <label> ' . __('Max files', 'lfb') . ' </label >
                                <input type="number" name="maxFiles" class="form-control" />
                                <small> ' . __('Maximum number of files the user can upload', 'lfb') . ' </small>
                            </div>';

                    echo '<div class="form-group" >
                                <label> ' . __('Maximum file size (MB)', 'lfb') . ' </label >
                                <input type="number" name="fileSize" class="form-control" />
                                <small> ' . __('Something like 25', 'lfb') . ' </small>
                            </div>';

                    echo '<div class="form-group" >
                                <label> ' . __('Allowed files', 'lfb') . ' </label >
                                <textarea name="allowedFiles" class="form-control" ></textarea>
                                <small> ' . __('Enter the allowed extensions separated by commas', 'lfb') . ' </small>
                            </div>';

                    echo '<div class="form-group" >
                                <label> ' . __("Isn't a part of the subscription ?", 'lfb') . ' </label >
                                <input type="checkbox"  name="isSinglePrice" data-switch="switch" data-on-label="' . __('Yes', 'lfb') . '" data-off-label="' . __('No', 'lfb') . '" />
                                <small> ' . __('If checked, the item price will not be a part of the subscription price', 'lfb') . ' </small>
                            </div>';

                    echo '<div class="form-group" >
                                <label> ' . __('Enable quantity choice ?', 'lfb') . ' </label >
                                <input type="checkbox"  name="quantity_enabled" data-switch="switch" data-on-label="' . __('Yes', 'lfb') . '" data-off-label="' . __('No', 'lfb') . '" />
                                <small> ' . __('Can the user select a quantity for this item ?', 'lfb') . ' </small>
                            </div>';
                    echo '<div id="efp_itemQuantity">';
                    echo '<div class="form-group" >
                                <label> ' . __('Min quantity', 'lfb') . ' </label >
                                <input type="number" name="quantity_min" class="form-control" />
                                <small> ' . __('Sets the minimum quantity that can be selected', 'lfb') . ' </small>
                            </div>';
                    echo '<div class="form-group" >
                                <label> ' . __('Max quantity', 'lfb') . ' </label >
                                <input type="number" name="quantity_max" class="form-control" />
                                <small> ' . __('Sets the maximum quantity that can be selected', 'lfb') . ' </small>
                            </div>';
                    echo '<div class="form-group" >
                                <label> ' . __('Apply reductions on quantities ?', 'lfb') . ' </label >
                                <input type="checkbox"  name="reduc_enabled" data-switch="switch" data-on-label="' . __('Yes', 'lfb') . '" data-off-label="' . __('No', 'lfb') . '" />
                                <small> ' . __('Apply reductions on quantities ?', 'lfb') . ' </small>
                            </div>';
                    echo '<div class="form-group" >
                                <label> ' . __('Use distance as quantity ?', 'lfb') . ' </label >
                                <input type="checkbox"  name="useDistanceAsQt" data-switch="switch" data-on-label="' . __('Yes', 'lfb') . '" data-off-label="' . __('No', 'lfb') . '" />
                                <small> ' . __('Use distance as quantity ?', 'lfb') . ' </small>
                            </div>
                            <input type="hidden" name="distanceQt"/>
                            <div id="lfb_distanceQtContainer" class="form-group" >
                                <label></label >
                                <a href="javascript:" onclick="lfb_editDistanceValue(true);" class="btn btn-default"> ' . __('Configure the distance', 'lfb') . ' </a>
                            </div>

                            ';
                    echo '<table id="lfb_itemPricesGrid" class="table">';
                    echo '<thead>';
                    echo '<tr>';
                    echo '<th>' . __('If quantity >= than', 'lfb') . '</th>';
                    echo '<th>' . __('Item price becomes', 'lfb') . '</th>';
                    echo '<th></th>';
                    echo '</tr>';
                    echo '</thead>';
                    echo '<tbody>';
                    echo '<tr class="static">';
                    echo '<td><input type="number" style="width: 100%;" class="form-control" id="reduc_new_qt" value="" placeholder="' . __('Quantity', 'lfb') . '"></td>';
                    echo '<td><input type="number"  style="width: 100%;" class="form-control"  id="reduc_new_price" value="" placeholder="' . __('Price', 'lfb') . '"></td>';
                    echo '<td><a href="javascript:" onclick="lfb_add_reduc();" class="btn btn-default"><span class="glyphicon glyphicon-plus" style="margin-right:8px;"></span>' . __('Add a new reduction', 'lfb') . '</a></td>';
                    echo '</tr>';
                    echo '</tbody>';
                    echo '</table>';
                    echo '</div>'; // eof efp_itemQuantity


                    echo '</div>'; // eof col-md-6
                    echo '<div class="col-md-12">';
                    echo '<div id="lfb_itemRichText"></div>';
                    echo '<p style="padding-left: 14px; padding-right: 14px;text-align:center;"><a href="javascript:" class="btn btn-primary" onclick="lfb_saveItem();"><span class="glyphicon glyphicon-floppy-disk"></span>' . __('Save', 'lfb') . '</a></p>';
                    echo '</div>'; // eof col-md-12
                    echo '<div class="clearfix"></div>';

                    echo '</div>'; // eof lfb_stepTabGeneral
                    echo '</div>'; // eof tab-content
                    echo '</div>'; // eof tabpanel

                    echo '</div>'; // eof lfb_container
                    echo '</div>'; // eof lfb_winItem



                    echo ' <div id="lfb_calculationValueBubble" class="container-fluid" >
                            <div>
                            <div class="col-md-12" >
                                <div class="form-group" >
                                    <label > ' . __('Select an item', 'lfb') . ' </label >
                                    <select name="itemID" class="form-control" />
                                    </select >
                                </div>
                                <div class="form-group" >
                                    <label > ' . __('Select an attribute', 'lfb') . ' </label >
                                    <select name="element" class="form-control" />
                                        <option value="">' . __('Price', 'lfb') . '</option>
                                        <option value="quantity">' . __('Quantity', 'lfb') . '</option>
                                        <option value="value">' . __('Value', 'lfb') . '</option>
                                    </select >
                                </div>
                                <p style="text-align: center;">
                                    <a href="javascript:" class="btn btn-primary"  onclick="lfb_saveCalculationValue();"><span class="glyphicon glyphicon-disk"></span>' . __('Insert', 'lfb') . '</a>
                                </p>
                            </div>
                            </div> ';
                    echo '</div>'; // eof win lfb_calculationValueBubble


                    echo '<div id="lfb_winDistance" class="lfb_window container-fluid"> ';
                    echo '<div class="lfb_winHeader col-md-12 palette palette-turquoise" ><span class="glyphicon glyphicon-pencil" ></span > ' . __('Distance calculation', 'lfb');

                    echo ' <div class="btn-toolbar"> ';
                    echo '<div class="btn-group" > ';
                    echo '<a class="btn btn-primary" href="javascript:" ><span class="glyphicon glyphicon-remove lfb_btnWinClose" ></span ></a > ';
                    echo '</div> ';
                    echo '</div> '; // eof toolbar
                    echo '</div> '; // eof header

                    echo '<div class="clearfix"></div><div class="container-fluid lfb_container"   style="max-width: 90%;margin: 0 auto;margin-top: 18px;"> ';
                    echo '<div role="tabpanel">';
                    echo '<ul class="nav nav-tabs" role="tablist" >
                            <li role="presentation" class="active" ><a href="#lfb_distanceTabGeneral" aria-controls="general" role="tab" data-toggle="tab" ><span class="glyphicon glyphicon-cog" ></span > ' . __('Distance calculation', 'lfb') . ' </a ></li>
                            </ul >';
                    echo '<div class="tab-content" >';
                    echo '<div role="tabpanel" class="tab-pane active" id="lfb_distanceTabGeneral" >';

                    echo '<div id="lfb_calcStepsPreview">
                                <div id="lfb_mapIcon"></div>
                              </div>';
                    echo '<div class="col-md-6" >
                                <h4>' . __('Departure address', 'lfb') . '</h4>
                                <table id="lfb_departTable" class="table table-striped">
                                <thead>
                                    <th>' . __('Type', 'lfb') . '</th>
                                    <th>' . __('Item', 'lfb') . '</th>
                                </thead>
                                <tbody>
                                    <tr>
                                    <td>' . __('Address', 'lfb') . '</td>
                                    <td>
                                        <select id="lfb_departAdressItem" class="form-control">
                                        </select>
                                    </td>
                                    </tr>
                                    <tr>
                                    <td>' . __('City', 'lfb') . '</td>
                                    <td>
                                        <select id="lfb_departCityItem" class="form-control">
                                        </select>
                                    </td>
                                    </tr>
                                    <tr>
                                    <td>' . __('Zip code', 'lfb') . '</td>
                                    <td>
                                        <select id="lfb_departZipItem" class="form-control">
                                        </select>
                                    </td>
                                    </tr>
                                    <tr>
                                    <td>' . __('Country', 'lfb') . '</td>
                                    <td>
                                        <select id="lfb_departCountryItem" class="form-control">
                                        </select>
                                    </td>
                                    </tr>
                                </tbody>
                                </table>
                                </div>
                                <div class="col-md-6" >
                                <h4>' . __('Arrival address', 'lfb') . '</h4>
                                    <table id="lfb_arrivalTable" class="table table-striped">
                                <thead>
                                    <th>' . __('Type', 'lfb') . '</th>
                                    <th>' . __('Item', 'lfb') . '</th>
                                </thead>
                                <tbody>
                                    <tr>
                                    <td>' . __('Address', 'lfb') . '</td>
                                    <td>
                                        <select id="lfb_arrivalAdressItem" class="form-control">
                                        </select>
                                    </td>
                                    </tr>
                                    <tr>
                                    <td>' . __('City', 'lfb') . '</td>
                                    <td>
                                        <select id="lfb_arrivalCityItem" class="form-control">
                                        </select>
                                    </td>
                                    </tr>
                                    <tr>
                                    <td>' . __('Zip code', 'lfb') . '</td>
                                    <td>
                                        <select id="lfb_arrivalZipItem" class="form-control">
                                        </select>
                                    </td>
                                    </tr>
                                    <tr>
                                    <td>' . __('Country', 'lfb') . '</td>
                                    <td>
                                        <select id="lfb_arrivalCountryItem" class="form-control">
                                        </select>
                                    </td>
                                    </tr>
                                </tbody>
                                </table>
                                </div>
                                <div class="clearfix"></div>
                                <p style="text-align: center;">
                                    ' . __('The result will be the distance between the two addresses in', 'lfb') . '
                                     <select class="form-control" id="lfb_distanceType" style="max-width: 280px;display: inline-block;margin-left: 8px;">
                                        <option value="km">' . __('km', 'lfb') . '</option>
                                        <option value="miles">' . __('miles', 'lfb') . '</option>
                                     </select>
                                </p>
                                <p style="text-align: center;">
                                    <a href="javascript:" class="btn btn-primary" style="margin-top:18px;"  onclick="lfb_saveDistanceValue();"><span class="glyphicon glyphicon-floppy-disk"></span>' . __('Insert', 'lfb') . '</a>
                                </p>
                            ';
                    echo '</div>';
                    echo '</div>';
                    echo '</div>';
                    echo '</div>';
                    echo '</div>'; // eof lfb_winRedirection



                    echo ' <div id="lfb_distanceValueBubble" class="container-fluid" >

                            </div> '; // eof win lfb_distanceValueBubble

                    echo '<div id="lfb_winEditCoupon" class="modal fade ">
                            <div class="modal-dialog">
                              <div class="modal-content">
                                <div class="modal-header">
                                    <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                                    <h4 class="modal-title">' . __('Edit a coupon', 'lfb') . '</h4>
                                </div>
                                <div class="modal-body" style="padding-bottom:0px;">
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label>' . __('Coupon code', 'lfb') . '</label>
                                            <input type="text" class="form-control" name="couponCode"/>
                                        </div>
                                    </div>                        
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label>' . __('Reduction type', 'lfb') . '</label>
                                            <select class="form-control" name="reductionType">
                                                <option value="">' . __('Price', 'lfb') . '</option>
                                                <option value="percentage">' . __('Percentage', 'lfb') . '</option>
                                            </select>
                                        </div>
                                    </div>                        
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label>' . __('Reduction', 'lfb') . '</label>
                                            <input type="number" step="any" class="form-control" name="reduction"/>
                                        </div>
                                    </div>                        
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label>' . __('Max uses', 'lfb') . '</label>
                                            <input type="number" class="form-control" name="useMax" min="0" /><br/>
                                            <small>' . __('Set 0 for an infinite use', 'lfb') . '</small>
                                        </div> 
                                    </div>
                                <div class="clearfix" ></div>
                                </div>
                                <div class="modal-footer" style="text-align: center;">
                                    <a href="javascript:" class="btn btn-primary"  onclick="lfb_saveCoupon();"><span class="glyphicon glyphicon-floppy-disk"></span>' . __('Save', 'lfb') . '</a>
                                </div><!-- /.modal-footer -->
                              </div><!-- /.modal-content -->
                            </div><!-- /.modal-dialog -->
                          </div><!-- /.modal -->';

                    echo '<div id="lfb_winLog" class="modal fade ">
                                     <div class="modal-dialog">
                                       <div class="modal-content">
                                         <div class="modal-body">
                                         </div>
                                         <div class="modal-footer" style="text-align: center;">
                                             <a href="javascript:" class="btn btn-default" data-dismiss="modal"><span class="glyphicon glyphicon-remove"></span>' . __('Close', 'lfb') . '</a>
                                         </div><!-- /.modal-footer -->
                                       </div><!-- /.modal-content -->
                                     </div><!-- /.modal-dialog -->
                                   </div><!-- /.modal -->';

                    echo '<div id="lfb_winShortcode" class="modal fade ">
                                     <div class="modal-dialog">
                                       <div class="modal-content">
                                         <div class="modal-header">
                                           <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                                           <h4 class="modal-title">' . __('Shortcode', 'lfb') . '</h4>
                                         </div>
                                         <div class="modal-body">
                                            <p style="margin-bottom: 0px;"><strong>' . __('Integrate form in a page', 'lfb') . ':</strong></p>
                                            <div id="lfb_shortcode_1"  class="lfb_shortcodeInput" onclick="lfb_selectPre(this);">'."&lt;div style=\"display:none;\"&gt;<br/>".'[estimation_form form_id=' . "&quot;" . '<span data-displayid="true">1</span>' . "&quot;" . ']'."<br/>&lt;/div&gt;".'</div>
                                            <p style="display:none;margin-bottom: 0px;"><strong>' . __('To use in fullscreen', 'lfb') . ':</strong></p>
                                            <div style="display:none;" id="lfb_shortcode_2"  class="lfb_shortcodeInput" onclick="lfb_selectPre(this);">'."&lt;div style=\"display:none;\"&gt;<br/>".'[estimation_form form_id=' . "&quot;" . '<span data-displayid="true">1</span>' . "&quot;" . ' fullscreen="true"]'."<br/>&lt;/div&gt;".'</div>

                                            <p style="margin-bottom: 0px;"><strong>' . __('To use as popup', 'lfb') . ':</strong></p>
                                            <div id="lfb_shortcode_3"  class="lfb_shortcodeInput" onclick="lfb_selectPre(this);">'."&lt;div style=\"display:none;\"&gt;<br/>".'[estimation_form form_id=' . "&quot;" . '<span data-displayid="true">1</span>' . "&quot;" . ' popup="true"]'."<br/>&lt;/div&gt;".'</div>

                                            <p style="margin-bottom: 0px;">To open the popup, simply use the css class "<b>open-estimation-form form-<span data-displayid="1" style="font-weight: bold;">1</span></b>".</p>
                                            <div id="lfb_shortcode_4" readonly class="lfb_shortcodeInput" onclick="lfb_selectPre(this);" >'."&lt;a href=" . "&quot;" . '#' . "&quot;" . ' class=' . "&quot;" . 'open-estimation-form form-<span data-displayid="true">1</span>' . "&quot;" . "&gt;<br/>Open Form<br/>&lt;/a&gt;".'</div>
                                         </div>
                                       </div><!-- /.modal-content -->
                                     </div><!-- /.modal-dialog -->
                                   </div><!-- /.modal -->';

                    $dispS = '';
                    if ($settings->purchaseCode == "") {
                        $dispS = 'true';
                    }
                    echo '<div id="lfb_winActivation" class="modal fade " data-show="' . $dispS . '" >
                                      <div class="modal-dialog">
                                        <div class="modal-content">
                                          <div class="modal-header">
                                            <h4 class="modal-title">' . __('Verification of the license', 'lfb') . '</h4>
                                          </div>
                                          <div class="modal-body">
                                            <div id="lfb_iconLock"></div>
                                            <p style="margin-bottom: 14px;">
                                                <span id="lfb_lscUnverified">' . __("The license of this plugin isn't verified", 'lfb') . '.<br/></span>' . __('Please fill the field below with your purchase code', 'lfb') . ' :
                                            </p>
                                            <div class="form-group" style="margin-bottom: 24px;">
                                                    <input type="text" value="' . $settings->purchaseCode . '" class="form-control" style="display:inline-block; width: 312px; margin-bottom: 4px" name="purchaseCode" placeholder="' . __('Enter your purchase code here', 'lfb') . '"/>
                                                    <a href="javascript:" onclick="lfb_checkLicense();" class="btn btn-primary"><span class="glyphicon glyphicon-check"></span>' . __('Verify', 'lfb') . '</a>
                                                    <br/>
                                                    <span style="font-size:12px;"><a href="' . $lfb_assetsUrl . 'img/purchaseCode.gif" target="_blank">' . __('Where can I find my purchase code ?', 'lfb') . '</a></span>
                                            </div>
                                            <div class="alert alert-danger" style="font-size:12px;  margin-bottom: 0px;" >
                                                    <span class="glyphicon glyphicon-warning-sign" style="margin-right: 12px;float: left;font-size: 22px;margin-top: 10px;margin-bottom: 10px;"></span>
                                                ' . __('Each website using this plugin needs a legal license (1 license = 1 website)', 'lfb') . '.<br/>
                                                ' . __('To read find more information on envato licenses', 'lfb') . ',
                                                    <a href="http://codecanyon.net/licenses/standard" target="_blank">' . __('click here', 'lfb') . '</a>.<br/>
                                                 ' . __('If you need to buy a new license of this plugin', 'lfb') . ', <a href="https://codecanyon.net/item/php-flat-estimation-payment-forms/10550735?ref=loopus" target="_blank">' . __('click here', 'lfb') . '</a>.
                                            </div>
                                          </div>
                                          <div class="modal-footer" style="text-align: center;">
                                                        <a href="javascript:"  id="lfb_closeWinActivationBtn" class="btn btn-default disabled"><span class="glyphicon glyphicon-remove"></span><span class="lfb_text">' . __('Close', 'lfb') . '</span></a>
                                                  </div><!-- /.modal-footer -->
                                        </div><!-- /.modal-content -->
                                      </div><!-- /.modal-dialog -->
                                    </div><!-- /.modal -->';

                    echo '<div id="lfb_winTldAddon" class="modal fade">
                                      <div class="modal-dialog">
                                        <div class="modal-content">
                                          <div class="modal-header">
                                            <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                                            <h4 class="modal-title">' . __('Activate an extension', 'lfb') . '</h4>
                                          </div>
                                          <div class="modal-body">
                                            <div id="lfb_iconGift"></div>
                                           <p style="line-height: 24px;">' . __('This extension is a free gift for users who bought a license for this plugin.', 'lfb') . '
                                           ' . __('To download and activate it, please simply fill your purchase code in the field below :', 'lfb') . '</p>
                                                <div class="form-group" style="margin-top: -4px;">
                                                    <input type="text" value="' . $settings->purchaseCode . '" class="form-control" style="display:inline-block; width: 296px; margin-bottom: 4px" name="purchaseCode" placeholder="Enter your puchase code here"/>
                                                    <a href="javascript:" onclick="lfb_addonTdgn();" class="btn btn-primary" style="margin-bottom: 3px;"><span class="glyphicon glyphicon-check"></span>' . __('Activate', 'lfb') . '</a>
                                                    <br/>
                                                    <span style="font-size:12px;"><a href="' . $lfb_assetsUrl . 'img/purchaseCode.gif" target="_blank">' . __('Where I can find my purchase code ?', 'lfb') . '</a></span>
                                            </div>
                                            <div class="alert alert-info" style="margin-bottom: 0px;">
                                            <p style="text-align: center">' . __('If you need to buy a new license of this plugin', 'lfb') . ', <a href="https://codecanyon.net/item/php-flat-estimation-payment-forms/10550735?ref=loopus" target="_blank">' . __('click here', 'lfb') . '</a>.</p>
                                            </div>
                                            </div>
                                          <div class="modal-footer" style="text-align: center;">
                                            <a href="javascript:" class="btn btn-default" data-dismiss="modal"><span class="glyphicon glyphicon-remove"></span>' . __('Cancel', 'lfb') . '</a>
                                        </div>
                                        </div><!-- /.modal-content -->
                                      </div><!-- /.modal-dialog -->
                                    </div><!-- /.modal -->';

                    echo '<div id="lfb_winImport" class="modal fade">
                                      <div class="modal-dialog">
                                        <div class="modal-content">
                                          <div class="modal-header">
                                            <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                                            <h4 class="modal-title">' . __('Import data', 'lfb') . '</h4>
                                          </div>
                                          <div class="modal-body">
                                           <div class="alert alert-danger"><p>' . __('Be carreful : all existing forms and steps will be erased importing new data.', 'lfb') . '</p></div>
                                               <form id="lfb_winImportForm" method="post" enctype="multipart/form-data">
                                                   <div class="form-group">
                                                    <input type="hidden" name="action" value="lfb_importForms"/>
                                                    <label>' . __('Select the .zip data file', 'lfb') . '</label><input name="importFile" type="file" class="" />
                                                   </div>
                                              </form>
                                          </div>
                                          <div class="modal-footer">
                                            <a href="javascript:" class="btn btn-default" data-dismiss="modal"><span class="glyphicon glyphicon-remove"></span>' . __('Cancel', 'lfb') . '</a>
                                            <a href="javascript:" class="btn btn-primary" onclick="lfb_importForms();"><span class="glyphicon glyphicon-floppy-disk"></span>' . __('Import', 'lfb') . '</a>
                                        </div>
                                        </div><!-- /.modal-content -->
                                      </div><!-- /.modal-dialog -->
                                    </div><!-- /.modal -->';


                    echo '<div id="lfb_winExport" class="modal fade">
                              <div class="modal-dialog">
                                <div class="modal-content">
                                  <div class="modal-header">
                                    <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                                    <h4 class="modal-title">' . __('Export data', 'lfb') . '</h4>
                                  </div>
                                  <div class="modal-body">
                                    <p style="text-align: center;"><a href="' . $lfb_assetsUrl . '../tmp/export_estimation_form.zip" target="_blank" onclick="jQuery(\'#lfb_winExport\').modal(\'hide\');" class="btn btn-primary btn-lg" id="lfb_exportLink"><span class="glyphicon glyphicon-floppy-disk"></span>' . __('Download the exported data', 'lfb') . '</a></p>
                                  </div>
                                </div><!-- /.modal-content -->
                              </div><!-- /.modal-dialog -->
                            </div><!-- /.modal -->';
                    
                    
                    echo '<div id="lfb_winUploadPic" class="modal fade">
                                      <div class="modal-dialog">
                                        <div class="modal-content">
                                          <div class="modal-header">
                                            <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                                            <h4 class="modal-title">' . __('Upload a picture', 'lfb') . '</h4>
                                          </div>
                                          <div class="modal-body">
                                               <form id="lfb_winUploadPicForm" method="post" enctype="multipart/form-data">
                                                   <div class="form-group">
                                                    <input type="hidden" name="action" value="lfb_importPic"/>
                                                    <label>' . __('Select the image file', 'lfb') . '</label><input name="importFile" type="file" class="" />
                                                   </div>
                                              </form>
                                          </div>
                                          <div class="modal-footer">
                                            <a href="javascript:" class="btn btn-default" data-dismiss="modal"><span class="glyphicon glyphicon-remove"></span>' . __('Cancel', 'lfb') . '</a>
                                            <a href="javascript:" class="btn btn-primary" onclick="lfb_importPicture();"><span class="glyphicon glyphicon-upload"></span>' . __('Import', 'lfb') . '</a>
                                        </div>
                                        </div><!-- /.modal-content -->
                                      </div><!-- /.modal-dialog -->
                                    </div><!-- /.modal -->';

                    echo '</div><!-- /wpe_bootstraped -->';

                    tdgn_showFormDesigner($form);
                }
                ?>
            </div>
        </div>
    </body>
</html>
<?php
}
mysqli_close($connection);
?>