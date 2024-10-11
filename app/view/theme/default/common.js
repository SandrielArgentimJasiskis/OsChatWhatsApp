function getAllAttendants(url, message) {
    if ($('select[name="is_self"]').val() == "0") {
        $('select[name="attendant_id"]').css('display', 'block');
        
        $.ajax({
            url: url,
            success: function(json) {
                $('select[name="attendant_id"]').html(json);
            }
        });
    } else {
        $('select[name="attendant_id"]').css('display', 'none');
        $('select[name="attendant_id"]').html('<option value="">' + message + '</option>');
    }
}

function changeMessageType() {
    $("#msgOptions").hide();
    $("#msgMediaType").hide();
    $('#msgTemplate').hide();
    $('textare[name="message_content[content]"]').hide();
    
    if ($('select[name="type"]').val() != 'media') {
        $('textare[name="message_content[content]"]').show();
    }
    
    if ($('select[name="type"]').val() == "interactive") {
        $('#msgOptions').show();
        $('#msgTemplate').hide();
    } else if ($('select[name="type"]').val() == 'media') {
        $('#msgMediaType').show();
        $('#msgTemplate').hide();
    } else if ($('select[name="type"]').val() == 'template') {
        $('#msgTemplate').show();
    }
}

function changeMessageTemplateVars() {
    var selectedOptionTemplate = $('select[name="message_content[template][id]"]').find('option:selected').attr('template-content');
    
    if (selectedOptionTemplate !== undefined) {
        console.log(selectedOptionTemplate);
        
        $('.template-content').hide();
        $('#message-template-content h3').show();
        
        $('#template-header-content').html('');
        $('#template-footer-content').html('');
        $('#template-buttons-content').html('');
        
        var template_header = $('textarea[name="template-content-' + ($('select[name="message_content[template][id]"]').find('option:selected').attr('value')).split("|")[0] + '-header"]');
        
        console.log(template_header);
        if ((template_header !== undefined) && template_header.length) {
            $('#template-header-content').html(template_header[0].innerText);
            
            if (template_header[0].textContent.includes('<img')) {
                $('#template-header-content').append('<input type="text" name="message_content[template][image_url]" onchange="changeTemplateImage();" />');
            }
        }
        
        $('textarea[name="template-content-' + ($('select[name="message_content[template][id]"]').find('option:selected').attr('value')).split("|")[0] + '-body"]').show();
        
        var template_footer = $('textarea[name="template-content-' + ($('select[name="message_content[template][id]"]').find('option:selected').attr('value')).split("|")[0] + '-footer"]');
        
        console.log(template_footer);
        
        if ((template_footer !== undefined) && template_footer.length) {
            $('#template-footer-content').html(template_footer[0].innerText);
        }
        
        var template_buttons = $('textarea[name="template-content-' + ($('select[name="message_content[template][id]"]').find('option:selected').attr('value')).split("|")[0] + '-buttons"]');
        
        console.log(template_buttons);
        
        if (template_buttons !== undefined) {
            $('#template-buttons-content').html(template_buttons[0].innerText);
        }
        
        var quantity_vars = selectedOptionTemplate.split("{{").length - 1;
        
        if (quantity_vars >= 1) {
            console.log('Foram encontradas ' + quantity_vars + ' variáveis no template');
            
            $('#message-template-input-vars').html('');
            $('#message-template-input-vars').show();
            
            for(i = 1; i <= quantity_vars; i++) {
                $('#message-template-input-vars').append('<input class="input-form" name="message_content[template][vars][' + i + ']" type="text" onchange="changeTemplateContent(' + i + ');" onkeyup="changeTemplateContent(' + i + ', ' + $('select[name="message_content[template][id]"]').find('option:selected').attr('value') + ');"></input>');
            }
        }
    }
}

function changeTemplateImage() {
    var input = $('input[name="message_content[template][image_url]"]');
    var image_url = input.val();
    
    $('#template-header-content img').attr('src', image_url);
}

function changeTemplateContent(input_var_id, template_id) {
    /*var var_content = $('input[name="message_content[template][var][' + input_var_id + ']"]').val();
    
    console.log(var_content);
    
    template_content = $('textarea[name="template-content-' + template_id + '"]').val();
    console.log(template_content);
    
    $('textarea[name="template-content-' + template_id + '"]').val(template_content.replace('{{' + input_var_id + '}}', var_content));*/
}

function changeMessageEvent() {
    if ($('select[name="event"]').val() == "response") {
        $('#msgResponses').show();
    } else {
        $('#msgResponses').hide();
    }
}

function getMessage(url, message_id, object) {
    object = '#td_select_message_option_event_response_' + object + ' select[name="message_response[' + object + '][option_id]"]';
    
    $.ajax({
        url: url + '&message_id=' + message_id,
        success: function(json) {
            $(object).html(json);
        }
    });
}

function getExtensionForm(url) {
	$.ajax({
        url: url,
        success: function(json) {
            $('#dialog').html(json);
			
			document.getElementById('dialog').showModal();
			
			$('.close-icon').click(function() {
                document.getElementById('dialog').close();
            });
            
            $('#dialog form .extension-bottom input[type="submit"]').click(function(event) {
                event.preventDefault();
                
                $.ajax({
                    url: $('#dialog form').attr('action'),
                    type: "post",
                    data: $('#dialog form').serialize(),
                    dataType: "json",
                    success: function(response){
                        console.log(response);
                        if (response.success) {
                            $('#dialog form .extension-bottom .msg').html(response.success);
                            $('#dialog form .extension-bottom .error').html('');
                            
                            $('#dialog form .extension-bottom .button-form').addClass('button-form-disabled').attr('disabled', 'true');
                            
                        } else {
                            $('#dialog form .extension-bottom .msg').html('');
                            $('#dialog form .extension-bottom .error').html(response.error);
                            
                            $('#dialog form .extension-bottom .button-form').removeClass('button-form-disabled');
                        }
                    }
                });
            });
        }
    });
}

function AddMsgOption() {
    row_option = Number($('input[name="row_options"]').val());
    
    $('#msgOptions table tbody').append('<tr id="msg-option-' + row_option + '"><td style="display: none"><input type="hidden" name="message_option[' + row_option + '][id]" value="" /></td><td><input type="text" name="message_option[' + row_option + '][option_title]" /></td><td><input type="text" name="message_option[' + row_option + '][option_description]" /></td><td><input type="text" name="message_option[' + row_option + '][option_sort_order]" /></td><td><i class="fa fa-minus" onclick="RemoveMsgOption(' + row_option + ')"></i></td></tr>');
    
    $('input[name="row_options"]').val(Number(row_option) + 1);
}

function RemoveMsgOption(option) {
    $('#msgOptions table tbody #msg-option-' + option).remove();
}

function AddMsgResponse(url, html, message_option) {
    row_response = Number($('input[name="row_responses"]').val());
    
    $('#msgResponses table tbody').append('<tr id="msg-response-' + row_response + '"><td style="display: none"><input type="hidden" name="message_response[' + row_response + '][id]" value="" /></td><td><select name="message_response[' + row_response + '][message_id]"  onchange="getMessage(\'' + url + '\', this.value, \''+ row_response + '\');">' + html + '</select></td><td id="td_select_message_option_event_response_' + row_response + '"><select name="message_response[' + row_response + '][option_id]"><option value="">' + message_option + '</option></select></td><td><i class="fa fa-minus" onclick="RemoveMsgResponse(' + row_response + ')"></i></td></tr>');
    
    $('input[name="row_responses"]').val(Number(row_response) + 1);
}

function RemoveMsgResponse(response) {
    $('#msgResponses table tbody #msg-response-' + response).remove();
}

function popupDelete(msg, delete_item) {
    if (confirm(msg)) {
        $('form').append('<input type="hidden" name="' + $('form').attr('content') + '" value="' + delete_item + '">');
        
        $('form').submit();
    }
}

$(document).ready(function() {
	$('input[type="text"]').keyup(function() {
		console.log($(this).val($(this).val().replace(/<|>|'|"/g, '')));
	});
	
	$('#msgTemplate').hide();
});
