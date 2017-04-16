<?php
require_once '../config.php';
if (!class_exists("Mailchimp", false)) {
          require_once('Mailchimp.php');    
}
 require_once('GetResponseAPI.class.php');   
$GLOBALS['lfb_connection'] = mysqli_connect($sql_server, $sql_user_name, $sql_password, $sql_database_name);
$GLOBALS['lfb_connection']->sqlPrefix = $sql_prefix ;


$lfb_assetsUrl = 'assets/';
$lfb_assetsDir = esc_url(trailingslashit(realpath(dirname(__FILE__) . '/assets/')));
$lfb_cssUrl = 'export/';
$lfb_uploadsDir = esc_url(trailingslashit(realpath(dirname(__FILE__) . '/uploads/')));
$lfb_uploadsUrl = 'uploads/';


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

function sanitize_css($content){
    return $content;
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

function trailingslashit($string) {
    $string = rtrim($string, '/\\');
    return $string . '/';
}

function esc_url($url) {
    return filter_var($url, FILTER_SANITIZE_URL);
}

if (isset($_POST['action'])) {
     $isAjax = isset($_SERVER['HTTP_X_REQUESTED_WITH']) AND strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest';
        if($isAjax){
    switch ($_POST['action']) {
        case 'lfb_checkCaptcha':
            checkCaptcha();
            break;
        case 'send_email':
            send_email();
            break;
        case 'get_currentRef':
            get_currentRef();
            break;
        case 'lfb_upload_form':
            uploadFormFiles();
            break;
        case 'lfb_removeFile':
            removeFile();
            break;
        case 'lfb_sendCt':
            sendContact();
            break;
        case 'lfb_applyCouponCode':
            applyCouponCode();
            break;
    }
        }
}
if (isset($_GET['paypal'])) {
    cbb_proccess_paypal_ipn();
}

function checkCaptcha(){
    $captcha = sanitize_text_field($_POST['captcha']);
    session_start();
    if($captcha != "" && strtolower($captcha) == strtolower($_SESSION['lfb_random_number'])){
        echo 1;
    }
    die();            
}
function cbb_proccess_paypal_ipn() {

    require_once ('IpnListener.php');
    if(isset($_POST['item_number'])){
        $item_number = sanitize_text_field($_POST['item_number']);
        $table_name = $GLOBALS['lfb_connection']->sqlPrefix  . "wpefc_logs";
        $logReq = sql_get_results("SELECT * FROM $table_name WHERE ref='$item_number' LIMIT 1");
        if (count($logReq) > 0) {
            $log = $logReq[0];

            $table_name = $GLOBALS['lfb_connection']->sqlPrefix  . "wpefc_forms";
            $formReq = sql_get_results("SELECT * FROM $table_name WHERE id=".$log->formID." LIMIT 1");
            $form = $formReq[0];   
            $listener = new IpnListener();
            if ($form->paypal_useSandbox) {
                $listener->use_sandbox = true;           
            }
            if($verified = $listener->processIpn()){} else {

                $transactionData = $listener->getPostData(); 
                if($_POST['payment_status'] == 'Completed'){
                    if(!$log->checked){
                        sendOrderEmail($item_number,$log->formID);    
                    }
                }
            }                     

        }
    }
}

    /* Ajax : get Current ref */
    function get_currentRef() {
        $rep = false;
        $settings = getSettings();
        if (isset($_POST['formID']) && !is_array($_POST['formID'])) {
            $formID = sanitize_text_field($_POST['formID']);

            
            $table_name = $GLOBALS['lfb_connection']->sqlPrefix  . "wpefc_forms";
            $rows = sql_get_results("SELECT * FROM $table_name WHERE id=$formID LIMIT 1");
            $form = $rows[0];
            $current_ref = $form->current_ref + 1;
            sql_update($table_name, array('current_ref' => $current_ref), array('id' => $form->id));
            $rep = $form->ref_root . $current_ref;
        }
        echo $rep;
        die();
    }
    function lfb_generatePdfAdmin($order,$form){
         require_once('html2pdf/html2pdf.class.php');
                $html2pdf = new HTML2PDF('P', 'A4', 'en', true, 'UTF-8');
                $html2pdf->setDefaultFont('dejavusans'); 
                     
                $contentPdf = preg_replace('/(<[^>]+) style=".*?"/i', '$1', $order->content);
                $contentPdf = str_replace('<strong>', '', $contentPdf);
                $contentPdf = str_replace('</strong>', '', $contentPdf);
                $contentPdf = str_replace('<thead>', '', $contentPdf);
                $contentPdf = str_replace('</thead>', '', $contentPdf);
                $contentPdf = str_replace('<th', '<td', $contentPdf);
                $contentPdf = str_replace('</th', '</td', $contentPdf);
                $contentPdf = str_replace('<td', '<td style="padding: 4px;padding-right: 8px;"', $contentPdf);
                $contentPdf = str_replace('<tbody>', '', $contentPdf);
                $contentPdf = str_replace('</tbody>', '', $contentPdf);
                
                $html2pdf->writeHTML('<page>'.$contentPdf.'</page>');
                $fileName = $form->title.'-'.$order->ref.'-'.uniqid().'.pdf';
                $html2pdf->Output('../uploads/'.$fileName,'F');
                
            return ('../uploads/'.$fileName);
    }
    function lfb_generatePdfCustomer($order,$form){
          require_once('html2pdf/html2pdf.class.php');
                     
                    $html2pdf = new HTML2PDF('P', 'A4', 'en', true, 'UTF-8');                     
                    $html2pdf->setDefaultFont('dejavusans'); 
                    $contentPdf = preg_replace('/(<[^>]+) style=".*?"/i', '$1', $order->contentUser);
                    $contentPdf = str_replace('<strong>', '', $contentPdf);
                    $contentPdf = str_replace('</strong>', '', $contentPdf);
                    $contentPdf = str_replace('<thead>', '', $contentPdf);
                    $contentPdf = str_replace('</thead>', '', $contentPdf);
                    $contentPdf = str_replace('<th', '<td', $contentPdf);
                    $contentPdf = str_replace('</th', '</td', $contentPdf);
                    $contentPdf = str_replace('<td', '<td style="padding: 4px;padding-right: 8px;"', $contentPdf);
                    $contentPdf = str_replace('<tbody>', '', $contentPdf);
                    $contentPdf = str_replace('</tbody>', '', $contentPdf);

                    $html2pdf->writeHTML('<page>'.$contentPdf.'</page>');
                    $fileName = $form->title.'-'.$order->ref.'-'.uniqid().'.pdf';
                    $html2pdf->Output('../uploads/'.$fileName,'F');
                $html2pdf->Output('../uploads/'.$fileName,'F');
    }

    // Send email to admin & customer
    function sendOrderEmail($orderRef,$formID) {
        require_once('PHPMailer/class.phpmailer.php');
        
        $table_name = $GLOBALS['lfb_connection']->sqlPrefix  . "wpefc_logs";
        $rows = sql_get_results("SELECT * FROM $table_name WHERE ref='$orderRef' AND formID='$formID' LIMIT 1");
        if (count($rows) > 0) {
            $order = $rows[0];

            $table_name = $GLOBALS['lfb_connection']->sqlPrefix  . "wpefc_forms";
            $rows = sql_get_results("SELECT * FROM $table_name WHERE id=$order->formID LIMIT 1");
            $form = $rows[0];

            $attachmentAdmin = array();
            if($form->sendPdfAdmin){            
                 try {

                   $url = 'http://freehtmltopdf.com';
                    $data = array(  'convert' => '', 
                                    'html' => '<html><head><title>'.$form->title.'</title><meta http-equiv="Content-Type" content="text/html;charset=UTF-8"><style>body{font-family: Helvetica,Arial;}</style></head><body>'.$order->content.'</html>',
                                    'baseurl' => $_SERVER['SERVER_NAME']);

                    $options = array(
                            'http' => array(
                                    'header'  => "Content-type: application/x-www-form-urlencoded\r\n",
                                    'method'  => 'POST',
                                    'content' => http_build_query($data),
                            ),
                    );
                    $context  = stream_context_create($options);
                    $result = file_get_contents($url, false, $context);
                    $fileName = $form->title.'-'.$order->ref.'-'.uniqid().'.pdf';
                    chmod('../uploads', 0747);
                    $fp = fopen('../uploads/'.$fileName,'w');
                    fwrite($fp, $result);
                    fclose($fp);
                    $attachmentAdmin[] = '../uploads/'.$fileName;

                } catch (Throwable $t) {
                     $attachmentAdmin[] = lfb_generatePdfAdmin($order, $form);
                } catch (Exception $e) {
                     $attachmentAdmin[] = lfb_generatePdfAdmin($order, $form);
                }
               // $attachmentAdmin[] = lfb_generatePdfAdmin($order,$form);
            }
            
            $email = new PHPMailer();            
            $email->CharSet = "UTF-8";
            $email->From      = $order->email;
            $email->FromName  = $order->email;
            $email->Subject   = $form->email_subject . ' - ' . $order->ref;
            $email->Body      = $order->content;
            $email->isHTML(true); 
            
            if(strpos($form->email,',') !== false){
                $to_array = explode(',', $form->email);
                foreach($to_array as $address)
                {
                    $mail->addAddress($address);
                }
            } else {            
                $email->AddAddress($form->email );
            }
            if(count($attachmentAdmin)>0){
                $email->AddAttachment($attachmentAdmin[0] , basename($attachmentAdmin[0]));
            }
            if($email->Send()){
                if(count($attachmentAdmin)>0){
                   unlink($attachmentAdmin[0]);
               }
            }            
                                   
            if ($order->sendToUser && $order->email != '') { 
                 $attachmentCustomer = array();
                if($form->sendPdfCustomer){
                   try {

                       $url = 'http://freehtmltopdf.com';
                        $data = array(  'convert' => '', 
                                        'html' => '<html><head><title>'.$form->title.'</title><meta http-equiv="Content-Type" content="text/html;charset=UTF-8"><style>body{font-family: Helvetica,Arial;}</style></head><body>'.$order->contentUser.'</body></html>',
                                        'baseurl' => $_SERVER['SERVER_NAME']);

                        $options = array(
                                'http' => array(
                                        'header'  => "Content-type: application/x-www-form-urlencoded\r\n",
                                        'method'  => 'POST',
                                        'content' => http_build_query($data),
                                ),
                        );
                        $context  = stream_context_create($options);
                        $result = file_get_contents($url, false, $context);
                        $fileName = $form->title.'-'.$order->ref.'-'.uniqid().'.pdf';
                        chmod('../uploads', 0747);
                        $fp = fopen('../uploads/'.$fileName,'w');
                        fwrite($fp, $result);
                        fclose($fp);
                        $attachmentCustomer[] = '../uploads/'.$fileName;

                    } catch (Throwable $t) {
                     $attachmentCustomer[] = lfb_generatePdfCustomer($order, $form);
                    } catch (Exception $e) {
                     $attachmentCustomer[] = lfb_generatePdfCustomer($order, $form);
                    }
                    
                   // $attachmentCustomer[] = lfb_generatePdfCustomer($order,$form);
                }
                
                
            $emailUser = new PHPMailer();       
            $emailUser->CharSet = "UTF-8";
            $emailUser->From      = $form->email;
            $emailUser->FromName  = $form->email;
            $emailUser->Subject   = $form->email_userSubject;
            $emailUser->Body      = $order->contentUser;
            $emailUser->isHTML(true); 
            $emailUser->AddAddress($order->email );
            if(count($attachmentCustomer)>0){
                $emailUser->AddAttachment($attachmentCustomer[0] , basename($attachmentCustomer[0]));
            }
            if($emailUser->Send()){
                if(count($attachmentCustomer)>0){
                    unlink($attachmentCustomer[0]);
                }
            }
            }

            $table_name = $GLOBALS['lfb_connection']->sqlPrefix  . "wpefc_logs";
            sql_update($table_name, array('checked' => true), array('id' => $order->id));
        }
    }

    /*
     * Ajax : send email
     */
    function send_email() {
        $isAjax = isset($_SERVER['HTTP_X_REQUESTED_WITH']) AND strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest';
        if($isAjax){
        $settings = getSettings();
        $formID = sanitize_text_field($_POST['formID']);
        $formSession = sanitize_text_field(($_POST['formSession']));        
        $phone = sanitize_text_field($_POST['phone']);
        $firstName = sanitize_text_field($_POST['firstName']);
        $lastName = sanitize_text_field($_POST['lastName']);
        $address = sanitize_text_field($_POST['address']);
        $city = sanitize_text_field($_POST['city']);
        $country = sanitize_text_field($_POST['country']);
        $state = sanitize_text_field($_POST['state']);
        $zip = sanitize_text_field($_POST['zip']);
        $email = sanitize_text_field($_POST['email']);        
        $contentTxt = sanitize_text_field($_POST['contentTxt']);
        $contactSent = $_POST['contactSent'];
        
        $total = sanitize_text_field($_POST['total']);
        $totalSub = sanitize_text_field($_POST['totalSub']);
        $subFrequency = sanitize_text_field($_POST['subFrequency']);
        $formTitle = sanitize_text_field($_POST['formTitle']);
        $stripeToken = sanitize_text_field($_POST['stripeToken']);  
        $stripeTokenB = sanitize_text_field($_POST['stripeTokenB']);     
        $itemsArray = $_POST['items'];
                
        $usePaypalIpn = false;
        if (isset($_POST['usePaypalIpn']) && $_POST['usePaypalIpn'] == '1') {
            $usePaypalIpn = true;
        }
        $sendUser = 0;
        $discountCode =  sanitize_text_field($_POST['discountCode']);
        if($discountCode != ""){
            $table_name = $GLOBALS['lfb_connection']->sqlPrefix  . "wpefc_coupons";
            $rows = sql_get_results("SELECT * FROM $table_name WHERE formID=$formID AND couponCode='$discountCode' LIMIT 1");
            if(count($rows)>0){
                $coupon = $rows[0];
                $coupon->currentUses ++;
                if($coupon->useMax > 0 && $coupon->currentUses >= $coupon->useMax){
                    sql_delete($table_name, array('id' => $coupon->id));                    
                } else {
                    sql_update($table_name, array('currentUses' => $coupon->currentUses), array('id' => $coupon->id));                    
                }
            }   
        }

        $table_name = $GLOBALS['lfb_connection']->sqlPrefix  . "wpefc_forms";
        $rows = sql_get_results("SELECT * FROM $table_name WHERE id=$formID LIMIT 1");
        $form = $rows[0];
        session_start();
        if(!$form->useCaptcha ||($_SESSION['lfb_random_number'] != "" && strtolower($_SESSION['lfb_random_number']) == strtolower($_POST['captcha']))){

        $summary = ($_POST['summary']);
        $summaryA  = ($_POST['summaryA']);

        $contentProject = $summary;
        $contentProject = sanitize_css($contentProject);
        $informations = $_POST['informations'];
        $contentUser = '';

        $current_ref = $form->current_ref + 1;
        sql_update($table_name, array('current_ref' => $current_ref), array('id' => $form->id));
        if (!isset($_POST['gravity']) || $_POST['gravity'] == 0) {

            if ($_POST['email_toUser'] == '1') {
                $sendUser = 1;

                $projectCustomer = stripslashes($contentProject);
                $projectCustomer = str_replace('C:\\fakepath\\', "", $projectCustomer);

                $content = $form->email_userContent;
                $content = str_replace("[customer_email]", sanitize_text_field($_POST['email']), $content);
                $content = str_replace("[project_content]", $projectCustomer, $content);
                $content = str_replace("[information_content]", stripslashes($informations), $content);
                $content = str_replace("[total_price]", sanitize_text_field($_POST['totalTxt']), $content);
                $content = str_replace("[ref]", $form->ref_root . $current_ref, $content);
                
                // recover items values
            $lastPos = 0;            
            while (($lastPos = strpos($content, '[item-', $lastPos)) !== false) {
                $itemID = substr($content, $lastPos + 6, (strpos($content, '_', $lastPos) - ($lastPos + 6)));
                $attribute = substr($content,strpos($content, '_', $lastPos)+1,((strpos($content, ']', $lastPos))-strpos($content, '_', $lastPos))-1);
                $newContent = substr($content, 0, $lastPos);
                $newValue = '';
                $itemFound = false;
                if(substr($itemID,0,1) != 'f'){
                    foreach ($_POST['items'] as $key => $value) {
                        if ($value['itemid'] == $itemID){
                            if($value[$attribute]){
                                $newValue = stripslashes($value[$attribute]);
                                $itemFound = true;
                            }
                        }
                    }
                } else {
                    foreach ($_POST['fieldsLast'] as $key => $value) {
                        if ($value['fieldID'] == substr($itemID,1)){                            
                            $newValue = stripslashes($value['value']);
                            $itemFound = true;                            
                        }
                    }
                }                
                $newContent .= $newValue;
                $newContent .= substr($content, strpos($content, ']', $lastPos)+1);                
                $content = $newContent;
                
                if($itemFound){
                     $lastPos = $lastPos + strlen($newValue);                       
                }else {
                    $lastPos = $lastPos + strlen('[item-'.$itemID.']');
                }
            }
            
                $contentUser = $content;
            }

            $projectAdmin = stripslashes($summaryA);
            $lastPos = 0;
            $positions = array();

            $projectAdmin = str_replace('C:\\fakepath\\', "", $projectAdmin);
            while (($lastPos = strpos($projectAdmin, 'class="lfb_file">', $lastPos)) !== false) {
                $positions[] = $lastPos;
                $lastPos = $lastPos + 17;
                $fileStartPos = $lastPos;
                $lastSpan = strpos($projectAdmin, '</span>', $fileStartPos);
                $file = substr($projectAdmin, $fileStartPos, $lastSpan - $fileStartPos);
                $projectAdmin = str_replace($file, '<a href="' . $lfb_uploadsUrl. $formSession .'/'.$file . '">' . $file . '</a>', $projectAdmin);
            }
            
            $content = $form->email_adminContent;
            $content = str_replace("[customer_email]", $form->ref_root . $current_ref, $content);
            $content = str_replace("[project_content]", $projectAdmin, $content);
            $content = str_replace("[information_content]", stripslashes($informations), $content);
            $content = str_replace("[total_price]", sanitize_text_field($_POST['totalTxt']), $content);
            $content = str_replace("[ref]", $form->ref_root . $current_ref, $content);
            
            // recover items values
            $lastPos = 0;            
            while (($lastPos = strpos($content, '[item-', $lastPos)) !== false) {
                $itemID = substr($content, $lastPos + 6, (strpos($content, '_', $lastPos) - ($lastPos + 6)));
                $attribute = substr($content,strpos($content, '_', $lastPos)+1,((strpos($content, ']', $lastPos))-strpos($content, '_', $lastPos))-1);
                $newContent = substr($content, 0, $lastPos);
                $newValue = '';
                $itemFound = false;
                if(substr($itemID,0,1) != 'f'){
                    foreach ($_POST['items'] as $key => $value) {
                        if ($value['itemid'] == $itemID){
                            if($value[$attribute]){
                                $newValue = $value[$attribute];
                                $itemFound = true;
                            }
                        }
                    }
                } else {
                    foreach ($_POST['fieldsLast'] as $key => $value) {
                        if ($value['fieldID'] == substr($itemID,1)){                            
                            $newValue = $value['value'];
                            $itemFound = true;                            
                        }
                    }
                }                
                $newContent .= nl2br($newValue);
                $newContent .= substr($content, strpos($content, ']', $lastPos)+1);                
                $content = $newContent;
                
                if($itemFound){
                     $lastPos = $lastPos + strlen($newValue);                       
                }else {
                    $lastPos = $lastPos + strlen('[item-'.$itemID.']');
                }
            }
            

            if (isset($_POST['email']) && $contactSent == 0) {
                if($form->useMailchimp && $form->mailchimpList != ""){
                    try{
                    $MailChimp = new Mailchimp($form->mailchimpKey);
                    $merge_vars = array('FNAME'=>$firstName, 'LNAME'=>$lastName,'phone'=>$phone,
                        'address1'=>array('addr1'=>$address, 'city'=>$city, 'state'=>$state, 'zip'=>$zip,'country'=>$country));
                    
                    $MailChimp->lists->subscribe($form->mailchimpList,array('email'=>$email),$merge_vars,'html',$form->mailchimpOptin);
                    } catch (Throwable $t) {
                    } catch (Exception $e) {
                    }
                    
                }
                if($form->useGetResponse){ 
                     $GetResponse = new GetResponseEP($form->getResponseKey);
                     $merge_vars = array('firstName'=>$firstName,'lastName'=>$lastName,'phone'=>$phone,
                         'city'=>$city,'state'=>$state,'zipCode'=>$zip);
                     $GetResponse->addContact($form->getResponseList, $firstName.' '.$lastName, $email,'standard',0,$merge_vars);
                }
            }

            $table_name = $GLOBALS['lfb_connection']->sqlPrefix  . "wpefc_logs";
            $checked = false;
            
                   
            sql_insert($table_name, array('ref' => $form->ref_root . $current_ref, 'email' => $email,'phone'=>$phone,'firstName'=>$firstName,'lastName'=>$lastName,
                'address'=>$address,'city'=>$city,'country'=>$country,'state'=>$state,'zip'=>$zip,
                'formID' => $formID, 'dateLog' => date('Y-m-d'), 'content' => $content, 'contentUser' => $contentUser, 'sendToUser' => $sendUser,
                'totalPrice'=>$total, 'totalSubscription'=>$totalSub,'subscriptionFrequency'=>$subFrequency,'formTitle'=>$formTitle,'contentTxt'=>$contentTxt));
            $orderID = sql_insert_id();
            $chkStripe = false;
            $useStripe = false;
            if($stripeToken != "" && $form->use_stripe){
                $useStripe = true;
                $chkStripe = doStripePayment($orderID,$stripeToken,$stripeTokenB);                
            }
               
            if (!$usePaypalIpn && (!$useStripe || $chkStripe)) {
                sendOrderEmail($form->ref_root . $current_ref,$form->id);
            }
        }


        echo $form->ref_root . $current_ref;
        }
        }
        die();
    }
    
    /*
     * Stripe : new subscription
     */
    function doStripePayment($orderID,$stripeToken,$stripeTokenB) {
                  
        $rep = false;
        
        $table_name = $GLOBALS['lfb_connection']->sqlPrefix  . "wpefc_logs";
        $orders = sql_get_results("SELECT * FROM $table_name WHERE id=" . $orderID . " LIMIT 1");
        if(count($orders)>0){
            $order = $orders[0];
            $form = getFormDatas($order->formID);
             require_once( 'stripe/Stripe.php');
             require_once( 'stripe/JsonSerializable.php');
             require_once( 'stripe/ApiRequestor.php');
             require_once( 'stripe/ApiResponse.php');
             require_once( 'stripe/Error/Base.php');
             require_once( 'stripe/Error/InvalidRequest.php');
             require_once( 'stripe/Error/Authentication.php');
             require_once( 'stripe/Util/Util.php');
             require_once( 'stripe/Util/Set.php');
             require_once( 'stripe/HttpClient/ClientInterface.php');
             require_once( 'stripe/HttpClient/CurlClient.php');
             require_once( 'stripe/Util/RequestOptions.php');
             require_once( 'stripe/StripeObject.php');
             require_once( 'stripe/AttachedObject.php');
             require_once( 'stripe/ApiResource.php');
             require_once( 'stripe/Plan.php');
             require_once( 'stripe/ExternalAccount.php');
             require_once( 'stripe/Card.php');
             require_once( 'stripe/Charge.php');
             require_once( 'stripe/Collection.php');
             require_once( 'stripe/Error/Card.php');
             require_once( 'stripe/Customer.php');
             require_once( 'stripe/Subscription.php');
             
                     
            
            if($order->totalPrice>0){
                    $price = number_format((float)$order->totalPrice, 2, '', '');
                   try {
                \Stripe\Stripe::setApiKey($form->stripe_secretKey);    
                    $charge = \Stripe\Charge::create(array(
                    'amount' => $price,
                    "currency" => strtolower($form->stripe_currency),
                    'source' => $stripeToken,
                    'description'=> $form->title.' - '.$order->ref,
                    "metadata"=> array('email'=>$order->email)                     
                  ));  
                    
                $rep = true;
                    
                }catch (Throwable $t) { 
                    echo 'Throwable';
                    echo $t;
                } catch (\Stripe\Error\ApiConnection $e) {
                    echo 'ApiConnection';
                // Network problem, perhaps try again.
                } catch (\Stripe\Error\InvalidRequest $e) {
                    echo 'InvalidRequest';
                    // You screwed up in your programming. Shouldn't happen!
                } catch (\Stripe\Error\Api $e) {
                    echo 'Api';
                    // Stripe's servers are down!
                } catch (\Stripe\Error\Card $e) {
                    echo 'Card';
                    // Card was declined.
                }
            }
            if($order->totalSubscription>0){                  
                // $intervalsStripe = array('D'=>'day','W'=>'week', 'M'=>'month','Y'=>'year');
                 $interval = $form->stripe_subsFrequencyType;
                 $price = $order->totalSubscription;
                // echo $price."\n";
                 $price = number_format((float)$price, 2, '', '');
                 //echo $price;

                  try {
                      $trialDays = 0;
                      if($order->totalPrice>0){
                          $trialDays = 30;
                          if($interval == 'day'){
                              $trialDays = 1;
                          }
                          if($interval == 'week'){
                              $trialDays = 7;
                          }
                          if($interval == 'year'){
                              $trialDays = 365;
                          }
                      }
                 if($order->totalPrice >0){
                     $stripeToken = $stripeTokenB;
                 }
                \Stripe\Stripe::setApiKey($form->stripe_secretKey);   
                 \Stripe\Plan::create(array(
                     "amount" => $price,
                     "interval" => $interval,
                     "name" => $form->title.' - '.$order->ref,
                     "currency" => strtolower($form->stripe_currency),
                     "id" => $order->id,
                     "metadata"=> array('email'=>$order->email,'date'=>$order->dateLog),
                     "trial_period_days"=>$trialDays)
                   );
                 
                 $customer = \Stripe\Customer::create(array(
                    "source" => $stripeToken, 
                    "plan" => $order->id,
                    "email" => $order->email
                  ));
                 
                $rep = true;
                 
                  }catch (Throwable $t) { 
                    echo 'Throwable';
                    echo $t;
                } catch (\Stripe\Error\ApiConnection $e) {
                    echo 'ApiConnection';
                // Network problem, perhaps try again.
                } catch (\Stripe\Error\InvalidRequest $e) {
                    echo 'InvalidRequest';
                    // You screwed up in your programming. Shouldn't happen!
                } catch (\Stripe\Error\Api $e) {
                    echo 'Api';
                    // Stripe's servers are down!
                } catch (\Stripe\Error\Card $e) {
                    echo 'Card';
                    // Card was declined.
                }
            }
        }           
       
        return $rep;        
    }
    
    function sendContact(){   
        
        $phone = sanitize_text_field($_POST['phone']);
        $firstName = sanitize_text_field($_POST['firstName']);
        $lastName = sanitize_text_field($_POST['lastName']);
        $address = sanitize_text_field($_POST['address']);
        $city = sanitize_text_field($_POST['city']);
        $country = sanitize_text_field($_POST['country']);
        $state = sanitize_text_field($_POST['state']);
        $zip = sanitize_text_field($_POST['zip']);
        $email = sanitize_text_field($_POST['email']); 
        $formID = sanitize_text_field($_POST['formID']);
        
         $table_name = $GLOBALS['lfb_connection']->sqlPrefix  . "wpefc_forms";
        $rows = sql_get_results("SELECT * FROM $table_name WHERE id=$formID LIMIT 1");
        if(count($rows)>0){
            $form = $rows[0];
            
            if (isset($_POST['email'])) {
                if($form->useMailchimp && $form->mailchimpList != ""){
                    try{
                    $MailChimp = new Mailchimp($form->mailchimpKey);
                    $merge_vars = array('FNAME'=>$firstName, 'LNAME'=>$lastName,'phone'=>$phone,
                        'address1'=>array('addr1'=>$address, 'city'=>$city, 'state'=>$state, 'zip'=>$zip,'country'=>$country));
                    
                    $MailChimp->lists->subscribe($form->mailchimpList,array('email'=>$email),$merge_vars,'html',$form->mailchimpOptin);
                    } catch (Throwable $t) {
                    } catch (Exception $e) {
                    }
                    
                }
                if($form->useGetResponse){ 
                     $GetResponse = new GetResponseEP($form->getResponseKey);
                     $merge_vars = array('firstName'=>$firstName,'lastName'=>$lastName,'phone'=>$phone,
                         'city'=>$city,'state'=>$state,'zipCode'=>$zip);
                     $GetResponse->addContact($form->getResponseList, $firstName.' '.$lastName, $email,'standard',0,$merge_vars);
                }
            }
        }        
        die();
    }
    
    function applyCouponCode(){
        
        $rep = '';
        $table_name = $GLOBALS['lfb_connection']->sqlPrefix  . "wpefc_coupons";
        $formID = sanitize_text_field($_POST['formID']);        
        $code = sanitize_text_field($_POST['code']);
        $rows = sql_get_results("SELECT * FROM $table_name  WHERE couponCode='$code' AND formID=$formID LIMIT 1");
        $chk = false;
        if(count($rows)>0){
            $coupon = $rows[0];
            if($coupon->reductionType == 'percentage'){
               $rep =  $coupon->reduction.'%';
            } else {
               $rep =  $coupon->reduction;
            }
        }        
        echo $rep;
        die();
    }

    function custom_wp_mail_from($email) {
        return sanitize_text_field($_POST['email']);
    }

    /**
     * Get  fields datas
     * @since   1.6.0
     * @return object
     */
    function getFieldsData() {
        
        $table_name = $GLOBALS['lfb_connection']->sqlPrefix  . "wpefc_fields";
        $rows = sql_get_results("SELECT * FROM $table_name  ORDER BY ordersort ASC");
        return $rows;
    }

    /**
     * Get  fields from specific form
     * @since   1.6.0
     * @return object
     */
    function getFieldDatas($form_id) {
        
        $table_name = $GLOBALS['lfb_connection']->sqlPrefix  . "wpefc_fields";
        $rows = sql_get_results("SELECT * FROM $table_name WHERE formID=$form_id ORDER BY ordersort ASC");
        return $rows;
    }

    /**
     * Get  form by pageID
     * @since   1.6.0
     * @return object
     */
    function getFormByPageID($pageID) {
        
        $table_name = $GLOBALS['lfb_connection']->sqlPrefix  . "wpefc_forms";
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
        
        $table_name = $GLOBALS['lfb_connection']->sqlPrefix  . "wpefc_forms";
        $rows = sql_get_results("SELECT * FROM $table_name");
        return $rows;
    }

    /**
     * Get specific Form datas
     * @return object
     */
    function getFormDatas($form_id) {
        
        $table_name = $GLOBALS['lfb_connection']->sqlPrefix  . "wpefc_forms";
        $rows = sql_get_results("SELECT * FROM $table_name WHERE id=$form_id LIMIT 1");
        if (count($rows) > 0) {
            return $rows[0];
        } else {
            return null;
        }
    }

    /**
     * Recover uploaded files from the form
     * @access  public
     * @since   1.0.0
     * @return  object
     */
    function uploadFormFiles() {
        
        $formSession = sanitize_text_field($_POST['formSession']);
        $itemID = sanitize_text_field($_POST['itemID']);
         $table_name = $GLOBALS['lfb_connection']->sqlPrefix  . "wpefc_items";
        $rows = sql_get_results("SELECT * FROM $table_name WHERE id=$itemID LIMIT 1");
        $maxSize = 25;
        if(count($rows)>0){
            $maxSize= $rows[0]->fileSize;
        }
        $maxSize = $maxSize*pow(1024,2);
       
        foreach ($_FILES as $key => $value) {
            if ($value["error"] > 0) {
                echo "error";
            } else {
                if (strlen($value["name"]) > 4 &&
                        $value['size'] < $maxSize &&
                        strpos(strtolower($value["name"]), '.php') === false &&
                        strpos(strtolower($value["name"]), '.js') === false &&
                        strpos(strtolower($value["name"]), '.html') === false &&
                        strpos(strtolower($value["name"]), '.phtml') === false &&
                        strpos(strtolower($value["name"]), '.pl') === false &&
                        strpos(strtolower($value["name"]), '.py') === false &&
                        strpos(strtolower($value["name"]), '.jsp') === false &&
                        strpos(strtolower($value["name"]), '.asp') === false &&
                        strpos(strtolower($value["name"]), '.htm') === false &&
                        strpos(strtolower($value["name"]), '.shtml') === false &&
                        strpos(strtolower($value["name"]), '.sh') === false &&
                        strpos(strtolower($value["name"]), '.cgi') === false
                ) {
                    
                    if(!is_dir($lfb_uploadsDir.$formSession)){
                         mkdir($lfb_uploadsDir.$formSession);
                        chmod($lfb_uploadsDir.$formSession, 0747);
                    }
                    move_uploaded_file($value["tmp_name"],$lfb_uploadsDir.$formSession.'/'.$value["name"]);
                    chmod($lfb_uploadsDir.$formSession.'/'.$value["name"], 0644);
                }
            }
        }
        die();
    }
    
    function removeFile(){
        $formSession = sanitize_text_field($_POST['formSession']);
        $file = sanitize_text_field($_POST['file']);
        $fileName = $formSession . '_' . $file;
        if(file_exists($lfb_uploadsDir .$fileName)){
            unlink($lfb_uploadsDir .$fileName);
        }
        die();
    }

    /**
     * Return steps data.
     * @access  public
     * @since   1.0.0
     * @return  object
     */
    function getStepsData($form_id) {
        
        $table_name = $GLOBALS['lfb_connection']->sqlPrefix  . "wpefc_steps";
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
        
        $results = array();
        $table_name = $GLOBALS['lfb_connection']->sqlPrefix  . "wpefc_steps";
        $steps = sql_get_results("SELECT * FROM $table_name WHERE formID=$form_id ORDER BY ordersort");
        foreach ($steps as $step) {
            $table_name = $GLOBALS['lfb_connection']->sqlPrefix  . "wpefc_items";
            $rows = sql_get_results("SELECT * FROM $table_name WHERE stepID=$step->id ORDER BY ordersort");
            foreach ($rows as $row) {
                $results[] = $row;
            }
        }
        return $results;
    }
    lfb_init();
    mysqli_close($GLOBALS['lfb_connection']);
?>