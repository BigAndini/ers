/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */


// /public/js/custom.js

jQuery(function($) {
    $("#create").on('click', function(event){
        event.preventDefault();
        var $stickynote = $(this);
        $.post("stickynotes/add", null,
            function(data){
                if(data.response == true){
                    $stickynote.before("<div class=\"sticky-note\"><textarea id=\"stickynote-"+data.new_note_id+"\"></textarea><a href=\"#\" id=\"remove-"+data.new_note_id+"\"class=\"delete-sticky\">X</a></div>");
                // print success message
                } else {
                    // print error message
                    console.log('could not add');
                }
            }, 'json');
    });

    $('#sticky-notes').on('click', 'a.delete-sticky',function(event){
        event.preventDefault();
        var $stickynote = $(this);
        var remove_id = $(this).attr('id');
        remove_id = remove_id.replace("remove-","");

        $.post("stickynotes/remove", {
            id: remove_id
        },
        function(data){
            if(data.response == true)
                $stickynote.parent().remove();
            else{
                // print error message
                console.log('could not remove ');
            }
        }, 'json');
    });

    $('#sticky-notes').on('keyup', 'textarea', function(event){
        var $stickynote = $(this);
        var update_id = $stickynote.attr('id'),
        update_content = $stickynote.val();
        update_id = update_id.replace("stickynote-","");

        $.post("stickynotes/update", {
            id: update_id,
            content: update_content
        },function(data){
            if(data.response == false){
                // print error message
                console.log('could not update');
            }
        }, 'json');

    });
    /*$('input[type=datetime]').datetimepicker({
        timeFormat: "HH:mm:ss",
        dateFormat: "yy-mm-dd",
        addSliderAccess: true,
	sliderAccessArgs: { touchonly: false }
    });*/
    $('.datepicker').datepicker({
        dateFormat: "yy-mm-dd",
        addSliderAccess: true,
	sliderAccessArgs: { touchonly: false }
    });
    /*$('.datetimepicker').datetimepicker({
        timeFormat: "HH:mm:ss",
        dateFormat: "yy-mm-dd",
        addSliderAccess: true,
	sliderAccessArgs: { touchonly: false }
    });*/
});