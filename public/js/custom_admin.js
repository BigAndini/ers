/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */


// /public/js/custom.js

jQuery(function($) {
    $('.datepicker').datepicker({
        /*dateFormat: "yy-mm-dd",*/
        dateFormat: "dd.mm.yy",
        addSliderAccess: true,
        firstDay: 1,
        changeMonth: true, 
        changeYear: true, 
        yearRange: '1900:2015',
	sliderAccessArgs: { touchonly: false },
        /*beforeShow: function(input, inst) {
            if($(input).val() == '') {
                $(input).val('15.04.1978');
            }
        }*/
    });
    $('.datetimepicker').datetimepicker({
        timeFormat: "HH:mm:ss",
        dateFormat: "yy-mm-dd",
        addSliderAccess: true,
	sliderAccessArgs: { touchonly: false }
    });
    
    /* ensure any open panels are closed before showing selected */
    /*$('#matching-accordion').on('show.bs.collapse', function () {
        $('#matching-accordion .in').collapse('hide');
    });*/
    $('#order-accordion .accordion-toggle').click(function () {
        if($(this).hasClass("panelisopen")){
            $(this).removeClass("panelisopen");
        } else {
            var href = this.hash;
            var orderId = href.replace("#order",""); 

            $(this).addClass("panelisopen");

            $.get( "/admin/ajax/matching-order/" + orderId, function( data ) {
                $( "#order" + orderId ).html( data );
            });
        }
    });
    $('#statement-accordion .accordion-toggle').click(function () {
        if($(this).hasClass("panelisopen")){
            $(this).removeClass("panelisopen");
        } else {
            var href = this.hash;
            var bankaccountId = href.replace("#bankaccount",""); 

            $(this).addClass("panelisopen");
            
            $.get( "/admin/ajax/matching-bankstatement/" + bankaccountId, function( data ) {
                $( "#bankaccount" + bankaccountId ).html( data );
            });
        }
    });
    
    $(".disabled").click(function(event) {
        event.preventDefault();
        return false;
    });
    
    $(function () {
        $('[data-toggle="tooltip"]').tooltip()
    })
});