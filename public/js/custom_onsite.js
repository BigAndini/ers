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
    
    
    // package detail controls
    var $confirmAgegroupCheckbox = $('#confirm-agegroup-checkbox');
    var $selectAllButton = $('.select-all-button');
    var $confirmButton = $('.confirm-items-button');
    var $itemCheckboxes = $('.item-ship-checkbox').not(':disabled');
    
    function updateConfirmButton() {
        var anythingSelected = $itemCheckboxes.is(':checked');
        var agegroupConfirmed = ($confirmAgegroupCheckbox.length === 0 || $confirmAgegroupCheckbox.prop('checked'));
        if(!anythingSelected || !agegroupConfirmed) {
            $confirmButton.prop('disabled', true);
            $confirmButton.prop('class', 'btn btn-lg btn-danger confirm-items-button');
        } else {
            $confirmButton.prop('disabled', false);
            $confirmButton.prop('class', 'btn btn-lg btn-success confirm-items-button');
        }
        
    }
    
    $confirmAgegroupCheckbox.change(function() {
        var replaceClasses = [
            ['panel-warning', 'panel-success'],
            //['fa-warning', 'fa-check'],
            ['text-warning', 'text-success']
        ];
        
        var $container = $(this).closest('.panel');
        var oldClass = ($(this).prop('checked') ? 0 : 1);
        var newClass = ($(this).prop('checked') ? 1 : 0);
        for(var i=0; i<replaceClasses.length; i++) {
            $container
                    .find('.' + replaceClasses[i][oldClass])
                    .addBack('.' + replaceClasses[i][oldClass])
                    .removeClass(replaceClasses[i][oldClass])
                    .addClass(replaceClasses[i][newClass]);
        }
        
        updateConfirmButton();
    });
    
    $selectAllButton.click(function() {
        var newState = ($selectAllButton.text() === 'Select all');
        $itemCheckboxes.prop('checked', newState).change();
    });
    
    $itemCheckboxes.change(function() {
        var $container = $(this).closest('li');
        if($(this).prop('checked')) {
            $container.addClass('light-green-bg');
        } else {
            $container.removeClass('light-green-bg');
        }
        
        updateConfirmButton();
        
        if($itemCheckboxes.not(':checked').length === 0) {
            $selectAllButton.text('Select none');
        } else {
            $selectAllButton.text('Select all');
        }
    });
    
    if($itemCheckboxes.length === 0) {
        $selectAllButton.prop('disabled', true);
    }
    
    if(!$('.package-detail-view').length) {
        $('.onsite-search-box').focus();
    }
});