var wpe_initial_overflowBody = "auto";
var wpe_initial_overflowHtml = "auto";
var scripts = document.getElementsByTagName("script"),
    src = scripts[scripts.length-1].src;
var efp_root = src.substr(0,src.lastIndexOf('/')+1); 


if (efp_root.substr(efp_root.length-1,1) != "/"){
    efp_root = efp_root+'/';
}
if(document.location.href.indexOf('https://')>=0 && efp_root.indexOf('https://')<0){
    efp_root = 'https'+efp_root.substr(4,efp_root.length);
} else if(document.location.href.indexOf('http://')>=0 && efp_root.indexOf('https://')>=0){
    efp_root = 'http'+efp_root.substr(5,efp_root.length);
}

var styleA = document.createElement("link");
styleA.type = "text/css";
styleA.rel = "stylesheet";
styleA.href = efp_root+"assets/css/efp_frontend.css";
document.getElementsByTagName("head")[0].appendChild(styleA);
setTimeout(function(){
    
if (!window.jQuery) {

    timerJS = setInterval(function(){
        if (window.jQuery) {
            clearInterval(timerJS);
            efp_init();
        }
    },800);

    var script = document.createElement("script");
    script.type = "text/javascript";
    script.src = efp_root+"assets/js/jquery-2.2.4.min.js";
    document.getElementsByTagName("head")[0].appendChild(script);
} else {
    efp_init();
}
},200);

function efp_init(){
     efp_checkShortcodes();
    efp_checkBtns();
}


function efp_checkShortcodes() {
    var divsArray = new Array();
    
    jQuery('div').each(function () {
        if (jQuery(this).html().indexOf('[estimation_form') > -1) {
            divsArray.push(this);
        }
    });
    jQuery.each(divsArray,function () {        
            var content = jQuery(this).html();
            var firstChar = content.indexOf('[estimation_form ');
            var lastChar = content.indexOf(']', firstChar);
            var isPopup = false;
            var isFullScreen = false;
            if (jQuery(this).html().indexOf('popup="true"', firstChar) > -1) {
                isPopup = true;
            }
            if (jQuery(this).html().indexOf('fullscreen="true"', firstChar) > -1) {
                isFullScreen = true;
            }

            var formIDFirst = content.indexOf('form_id="', firstChar) + 9;
            var formIDLast = content.indexOf('"', formIDFirst);
            var formID = content.substr(formIDFirst, (formIDLast - formIDFirst));

            var cssClasses = '';
            var url = efp_root + 'viewForm.php?form=' + formID;
            if (isPopup) {
                cssClasses += 'wpe_popup';
                url += '&popup=1';
            }
            var d = new Date();
            url += '&tmp='+d.getMilliseconds();
            if (isFullScreen) {
                cssClasses += 'wpe_fullscreen';
                url += '&fullscreen=1';
                wpe_initial_overflowBody = jQuery('body').css('overflow-y');
                wpe_initial_overflowHtml = jQuery('html').css('overflow-y');
                jQuery('body,html').css('overflow-y', 'hidden');
            }
            var iframe = '<iframe class="efp_iframe form-' + formID + ' ' + cssClasses + '" src="' + url + '" data-form="' + formID + '"></iframe>';

            content = content.substr(0, firstChar) + iframe + content.substr(lastChar + 1, content.length);
            try {
                jQuery(this).html(content);
                jQuery(this).fadeIn();
            } catch (e) {
            }
    });
}
function efp_checkBtns() {
    jQuery('.open-estimation-form').click(wpe_popup_estimation);
}


function wpe_popup_estimation() {
    var form_id = 0;
    var cssClass = jQuery(this).attr('class');
    cssClass = cssClass.split(' ');
    jQuery.each(cssClass, function (c) {
        c = cssClass[c];
        if (c.indexOf('form-') > -1) {
            form_id = c.substr(c.indexOf('-') + 1, c.length);
        }
    });
    wpe_initial_overflowBody = jQuery('body').css('overflow-y');
    wpe_initial_overflowHtml = jQuery('html').css('overflow-y');
    jQuery('body,html').css('overflow-y', 'hidden');
    jQuery('.efp_iframe.form-' + form_id).css({
        left: 0,
        top: 0,
        opacity: 1
    });
    jQuery('.efp_iframe.form-' + form_id).fadeIn();
    jQuery('.efp_iframe.form-' + form_id).contents().find('#wpe_close_btn').show();
    jQuery('.efp_iframe.form-' + form_id).contents().find('#wpe_close_btn').click(function () {
            wpe_close_popup_estimation(form_id);
    });
}

function wpe_close_popup_estimation(form_id) {
    jQuery('.efp_iframe.form-' + form_id).fadeOut();
    setTimeout(function(){
        jQuery('body').css('overflow-y', wpe_initial_overflowBody);
        jQuery('html').css('overflow-y', wpe_initial_overflowHtml);       
    },1000);
}
