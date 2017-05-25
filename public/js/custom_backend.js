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
        yearRange: '1900:2016',
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
    $('#bankaccount-accordion .accordion-toggle').click(function () {
    /*$('#statement-accordion .accordion-toggle').click(function () {*/
        if($(this).hasClass("panelisopen")){
            $(this).removeClass("panelisopen");
        } else {
            var href = this.hash;
            var bankaccountId = href.replace("#bankaccount",""); 

            $(this).addClass("panelisopen");
            
            $( "#bankaccount" + bankaccountId ).html('<div class="panel-body"><p style="position: relative; margin: .5em auto; width: 20px;"><i class="fa-li fa fa-spinner fa-spin"></i></p></div>');
            $.get( "/admin/ajax/matching-bankstatement/" + bankaccountId, function( data ) {
                $( "#bankaccount" + bankaccountId ).html( data );
                $('#statement-accordion .accordion-toggle').click(function () {
                    if($(this).hasClass("panelisopen")){
                        $(this).removeClass("panelisopen");
                    } else {
                        var href = this.hash;
                        var bankaccountId = href.replace("#statement",""); 

                        $(this).addClass("panelisopen");

                        $.get( "/admin/ajax/matching-statementcols/" + bankaccountId, function( data ) {
                            $( "#statement" + bankaccountId ).html( data );
                        });
                    }
                });
            })
                        .fail(function() {
                            alert('failed to load bank statements');
                    $( "#bankaccount" + bankaccountId ).html('<div class="panel-body"><p style="position: relative; margin: 0em auto; width: 20px;"><i class="fa fa-warning"></i></p></div>');
                        });;
        }
    });
    
    $(".disabled").click(function(event) {
        event.preventDefault();
        return false;
    });
    
    $(function () {
        $('[data-toggle="tooltip"]').tooltip()
    })
    
    // change currency and payment type
    $('#change-currency').change(function(event) {
        
    });
    
    $("#change-currency").change(function () {
        var currencyId = $(this).val();
        if(currencyId !== "") {
            loadChangePaymenttype(currencyId);
        }
    });
});

function loadChangePaymenttype(currencyId) {
    var url = "/admin/ajax/choose-payment-types/" + currencyId;
    var count = 1;
    $.getJSON( url, function( data ) {
        var options = "";
        $.each(data, function(id, content) {
            if(typeof content.name === 'undefined') {
                return true;
            }
            var disabled = " disabled";
            if(content.active === true) {
                disabled = "";
            }
            options += "<option value='" + id + "'" + disabled + ">" + content.name + "</option>";
            count++;
        });
        $("#change-paymenttype").html(options).fadeIn();
    });
}

$(document).ready(function() {
    $(".dropdown-toggle").dropdown();
});