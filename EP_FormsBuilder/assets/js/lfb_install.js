var lfb_installStep = 0;
var ajaxurl = document.location.href;

function lfb_installNext(){
    //if(lfb_installStep == 0){
        var error = false;
        var data2Send = {};
        jQuery('#lfb_installPanelBody > .container-fluid>[data-stepinstall="'+lfb_installStep+'"] .alert').slideUp();
        jQuery('#lfb_installPanel .has-error').removeClass('has-error');
        jQuery('#lfb_installPanelBody > .container-fluid>[data-stepinstall="'+lfb_installStep+'"] input[name]').each(function(){
            var chkField = true;
        
            if(jQuery(this).is('[type="email"]') && !lfb_installCheckEmail(jQuery(this).val())){
                chkField = false;                
            } else if(jQuery(this).is('[data-min]') && jQuery(this).val().length < jQuery(this).attr('data-min')){
                chkField = false;
            } else if(jQuery(this).val() == "" && jQuery(this).attr('name') != "db_name"&& jQuery(this).attr('name') != "db_pass" && jQuery(this).attr('name') != "db_server"){
                chkField = false;
            }
            if(!chkField){
                error = true;
                jQuery(this).closest('.form-group').addClass('has-error');
            } else {
                eval('data2Send.'+jQuery(this).attr('name')+' = jQuery(this).val()');
            }
        });
        if(!error){
            data2Send.action = 'lfb_install';
            data2Send.step =lfb_installStep;
            jQuery.ajax({
               type: 'post',
               url: ajaxurl,
               data: data2Send,
               success: function(rep){
                   rep = rep.trim();
                   if(rep == 1){
                       lfb_installStep++;
                       jQuery('#lfb_installPanelHeader h4').html('Installation : '+jQuery('#lfb_installPanelBody > .container-fluid>[data-stepinstall="'+lfb_installStep+'"]').attr('data-title'))
                       if(jQuery('#lfb_installPanelBody > .container-fluid>[data-stepinstall="'+lfb_installStep+'"]').length ==0){
                           lfb_installFinished();
                       } else {
                           jQuery('#lfb_installPanelBody > .container-fluid>:not([data-stepinstall="'+lfb_installStep+'"])').fadeOut(300);
                           setTimeout(function(){
                            jQuery('#lfb_installPanelBody > .container-fluid>[data-stepinstall="'+lfb_installStep+'"]').fadeIn();                               
                           },400);
                       }
                   }else {
                       jQuery('#lfb_installPanelBody > .container-fluid>[data-stepinstall="'+lfb_installStep+'"] .alert').html('<p>'+rep+'</p>');
                       jQuery('#lfb_installPanelBody > .container-fluid>[data-stepinstall="'+lfb_installStep+'"] .alert').slideDown();
                   }
               }
            });
        }
   // }
}
function lfb_installFinished(){
    document.location.href = document.location.href;
}

function lfb_installCheckEmail(emailToTest) {
    if (emailToTest.indexOf("@") != "-1" && emailToTest.indexOf(".") != "-1" && emailToTest != "")
        return true;
    return false;
}
function lfb_login(){
    var error = false;
    var email = jQuery('#lfb_installPanel input[name="login_email"]').val();
    var pass = jQuery('#lfb_installPanel input[name="login_pass"]').val();
    jQuery('#lfb_installPanel input[name="login_email"]').closest('.form-group').removeClass('has-error');  
    jQuery('#lfb_installPanel input[name="login_pass"]').closest('.form-group').removeClass('has-error');  
    
    if(!lfb_installCheckEmail(email)){
        error = true;
         jQuery('#lfb_installPanel input[name="login_email"]').closest('.form-group').addClass('has-error');         
    }
    if(pass<3){        
        error = true;
         jQuery('#lfb_installPanel input[name="login_pass"]').closest('.form-group').addClass('has-error');
    }
    if(!error){
        jQuery.ajax({
           type: 'post',
           url: ajaxurl,
           data:{
               action: 'lfb_login',
               email: email,
               pass: pass
           },
           success: function(rep){
               if(rep == '1'){
                   document.location.href = document.location.href;
               } else {
                    jQuery('#lfb_installPanel input[name="login_email"]').closest('.form-group').addClass('has-error'); 
                    jQuery('#lfb_installPanel input[name="login_pass"]').closest('.form-group').addClass('has-error');                         
               }
           }
        });
    }
}
function lfb_passLost(){
    var error = false;
    var email = jQuery('#lfb_installPanel input[name="login_email"]').val();
    if(!lfb_installCheckEmail(email)){
        error = true;
         jQuery('#lfb_installPanel input[name="login_email"]').closest('.form-group').addClass('has-error');         
    } else {
        jQuery.ajax({
           type: 'post',
           url: 'includes/lfb-admin.php',
           data:{
               action: 'lfb_passLost',
               email: email
           },
           success: function(rep){
               if(rep == '1'){
                   alert(jQuery('#lfb_loginPassText').html());
               } else {
                    jQuery('#lfb_installPanel input[name="login_email"]').closest('.form-group').addClass('has-error');                     
               }
           }
       });
    }
}