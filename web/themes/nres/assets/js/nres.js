//Modal windows logic
//TODO: Create single logic for all hrefs
$('#loginButton').click(function (){
    $('#modal').modal('show')
        .find('#modalContent')
        .load($(this).attr('href'));
    $("h4.modal-title").text($(this).text());
    return false;
});

$('#registerButton').click(function (){
    $('#modal').modal('show')
        .find('#modalContent')
        .load($(this).attr('href'));
    $("h4.modal-title").text($(this).text());
    return false;
});
$('#accountSettings').click(function (){
    $('#accountSettings').parent().parent().parent().removeClass('open')
    $('#modal').modal('show')
        .find('#modalContent')
        .load($(this).attr('href'));
    $("h4.modal-title").text($(this).text());
    return false;
});
$('#addProperty').click(function (){
    $('#modal').modal('show')
        .find('#modalContent')
        .load($(this).attr('href'));
    $("h4.modal-title").text($(this).text());
    return false;
});

$(document).on('click','.update_property a',function (){
    $('#modal').modal('show')
        .find('#modalContent')
        .load($(this).attr('href'));
    $("h4.modal-title").text($(this).attr('title'));
    return false;
});
$(document).on('click','.delete_property a',function (){
    $('#modal').modal('show')
        .find('#modalContent')
        .load($(this).attr('href'));
    $("h4.modal-title").text($(this).attr('title'));
    return false;
});

$(document).on('click','.view_property a', function (){
    $('#modal').modal('show')
        .find('#modalContent')
        .load($(this).attr('href'));
    $("h4.modal-title").text($(this).attr('Title'));
    return false;
});

$(document).on('click','.add_single_property',function (){
    $('#modal').find('#modalContent')
        .load($(this).attr('href'));
    $("h4.modal-title").text($(this).attr('title'));
    return false;
});
$(document).on('click','#property-save-add-another',function (){
    $('input[type="hidden"]#nresproperty-addnew').val(1);
    return true;
});
//$(document).on('click','#property-save-one',function (){
//
//    $('input[type="hidden"]#nresproperty-addnew').val(0);
//    return true;
//});


//Form submittion
nresAjax = [] || nresAjax;
nresAjax.showFlashMessage = function(type, msg) {
    $('#flash-message-container').html("<div class='alert alert-" + type
    +"'><button type=\"button\" class=\"close\" data-dismiss=\"alert\" aria-label=\"Close\"><span aria-hidden=\"true\">&times;</span></button> " + msg + "</div>")
    $('#flash-message-container').show(0).animate({"opacity": "1"}, 1500);
}
nresAjax.timeoutForFlashMessage = function() {
    setTimeout(function() {
        $('#flash-message-container').animate({"opacity": 0}, 2000).hide(0);
    },5000);
}

jQuery(document).ready(function() {


    $(document).on('beforeSubmit','#uploadcsv-form', function(event, jqXHR, settings) {

        var form = $('#uploadcsv-form');
        if(form.find('.has-error').length) {
            return false;
        }
        //var formData = new FormData();
        //formData.append( 'file', input.files[0] );
        //console.log(formData); return false;
        $('#uploadcsv-form .small-ajax-loader').show(0);
        $("#uploadLog").hide();
        $('#property-save-add-another').attr('disabled', true);
        $.ajax({
            url: $('#uploadcsv-form').attr('action'),
            type: 'post',
            data: new FormData( $(this)[0] ),
            processData: false,
            contentType: false,
            success: function(data) {
                $('#property-save-add-another').removeAttr('disabled');
                $('#uploadcsv-form .small-ajax-loader').hide(0);
                logData = JSON.parse(data);
                var logHtml = '';
                $.each(logData,function(i,e) {
                    logHtml +="Line " + i + ": " + e + '<br />';
                });
                $("#uploadLog").html(logHtml).show(200);
            }
        });

        return false;
    });

    $(document).on('beforeSubmit','#property-form', function(event, jqXHR, settings) {

        var form = $('#property-form');
        if(form.find('.has-error').length) {
            return false;
        }



        $('#property-form .small-ajax-loader').show(0);

        $('#property-save-add-another').attr('disabled', true);
        $('#property-save-one').attr('disabled', true);
        $.ajax({
            url: $('#property-form').attr('action'),
            type: 'post',
            data: form.serialize(),

            success: function(data) {

                flashMessagesData = JSON.parse(data);
                if (typeof(flashMessagesData.errors) !== 'undefined' ) {
                    $.each(flashMessagesData.errors,function(i,err) {
                        $('.field-nresproperty-' + i).removeClass('has-success').addClass('has-error');
                        $('.field-nresproperty-' + i + ' .help-block').html(err[0]);
                    });
                } else {
                    if ($('input[type="hidden"]#nresproperty-addnew').val()!="1") {
                        window.location.reload();
                        return true;
                    }
                    $.each(flashMessagesData,function(i,val) {
                        nresAjax.showFlashMessage(i,val);
                    });
                    nresAjax.timeoutForFlashMessage();

                    $('#modal').find('#modalContent')
                        .load('index.php?r=nresproperty/createsingle');
                }
                $('#property-save-add-another').removeAttr('disabled');
                $('#property-save-one').removeAttr('disabled');
                $('#property-form .small-ajax-loader').hide(0);

            }
        });

        return false;
    });


    (function($) {
        "use strict";

        $(function() {

            $(document).on('click', '.toggle-alerts',function(e) {
                e.preventDefault();
                if ($(this).data('toggle') == 'pre-storm') {
                    $('.visible-pre-storm').removeClass('hide');
                    $('.visible-post-storm').addClass('hide');
                } else {
                    $('.visible-pre-storm').addClass('hide');
                    $('.visible-post-storm').removeClass('hide');
                }
                var toggle = $(this).data('toggle');
                $('.toggle-alerts').each(function(index) {
                    if ($(this).data('toggle') == toggle) {
                        $(this).removeClass('collapsed');
                    } else {
                        $(this).addClass('collapsed');
                    }
                });
            });

        });

    }(jQuery));

    nresAjax.timeoutForFlashMessage();

    $(document).on('click','#loginBtn', function(event, jqXHR, settings) {
        $('#login-form-login').val(jQuery.trim($('#login-form-login').val()));
        return true;
    });
});

