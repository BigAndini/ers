/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */


// /public/js/custom.js

jQuery(function($) {
    $(window).focus(function(){
        if(window.sessionStorage) {
            if(window.sessionStorage.getItem('tabId') == null) {
                // TODO: check if generated code already exists and regenerade 
                // a new one if that's the case.
                window.sessionStorage.setItem('tabId', makeid());
            }
            $.ajax({
                url:"/ajax/session-storage/"+window.sessionStorage.getItem('tabId')
            }).done(function(data) {
                $('#tabId').html(data);
            });
        }
    });
    
    $('.datepicker').datepicker({
        /*dateFormat: "yy-mm-dd",*/
        dateFormat: "dd.mm.yy",
        addSliderAccess: true,
        firstDay: 1,
        changeMonth: true, 
        changeYear: true, 
        yearRange: '1900:2016',
	sliderAccessArgs: { touchonly: false },
        /*beforeShow: function(input, inst) {
            if($(input).val() == '') {
                $(input).val('15.04.1978');
            }
        }*/
        onChangeMonthYear:function(y, m, i){                                
            var d = i.selectedDay;
            $(this).datepicker('setDate', new Date(y, m - 1, d));
        }
    });
    $('.datetimepicker').datetimepicker({
        timeFormat: "HH:mm:ss",
        dateFormat: "yy-mm-dd",
        addSliderAccess: true,
	sliderAccessArgs: { touchonly: false }
    });
    
    $.cookieCuttr({
        cookieAnalytics: false,
        cookieMessage: 'We use cookies on this website, you can <a href="{{cookiePolicyLink}}" title="read about our cookies" target="_blank">read about them here</a>. To use the website as intended please...',
        cookiePolicyLink: '/info/cookie'
    });
    
    $( "#person-detail" ).tabs({
        create: function( event, ui ) {
            $( "#person-detail" ).find('input').prop('disabled', true);
            $( "#person-detail" ).find('select').prop('disabled', true);
            ui.panel.find('input').prop('disabled', false);
            ui.panel.find('select').prop('disabled', false);
        },
        /*activate: function( event, ui ) {},*/
        beforeActivate: function( event, ui ) {
            $( "#person-detail" ).find('input').prop('disabled', true);
            $( "#person-detail" ).find('select').prop('disabled', true);
            ui.newPanel.find('input').prop('disabled', false);
            ui.newPanel.find('select').prop('disabled', false);
        }
    });
    
    if($('#chooser').hasClass('in')) {
        $('#chooser').modal('show');
    }
});