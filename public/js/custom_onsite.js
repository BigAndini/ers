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
    var $itemShipButtons = $('.item-ship-button').not(':disabled');
    
    function updateShipButtons() {
        var agegroupConfirmed = ($confirmAgegroupCheckbox.length === 0 || $confirmAgegroupCheckbox.prop('checked'));
        if(agegroupConfirmed) {
            $itemShipButtons.prop('disabled', false);
            $itemShipButtons.removeClass('btn-danger');
            $itemShipButtons.addClass('btn-success');
        } else {
            $itemShipButtons.prop('disabled', true);
            $itemShipButtons.removeClass('btn-success');
            $itemShipButtons.addClass('btn-danger');
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
        
        updateShipButtons();
    });
    $confirmAgegroupCheckbox.change();
    
    $itemShipButtons.click(function() {
        var $button = $(this);
        var $container = $button.closest('li');
        
        $button.prop('disabled', true);
        $container.addClass('bg-info');
        
        var $form = $(this).closest('form');
        var url = $form.prop('action');
        $.post(url, {
            id: $form[0].id.value,
            csrf: $form[0].csrf.value,
            itemId: $button.data('itemid'),
        })
        .done(function(data) {
            var $newItem = $(data);
            var $newPackage = $newItem.find(".package");
            var highlightClass = $newPackage.find(".text-danger").length ? 'bg-danger' : 'bg-success';
            $newPackage.addClass(highlightClass);
            
            $container.replaceWith($newItem);
            setTimeout(function(){ 
                $newPackage.removeClass(highlightClass);
            }, 500);
        })
        .fail(function(e) {
            alert('Failed to ship item: Network error!');
            console.error(e);
            $button.prop('disabled', false);
        });
    });
    
    if(!$('.package-detail-view').length) {
        $('.onsite-search-box').focus();
    }
});