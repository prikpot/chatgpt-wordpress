jQuery(document).ready(function ($) {
    $('#chatgpt-client-form').on('submit', function (e) {
        e.preventDefault();
        var question = $('#question').val();

        $.ajax({
            type: 'POST',
            url: chatgpt_client_object.ajax_url,
            data: {
                action: 'chatgpt_send_question',
                question: question
            },
            beforeSend: function () {
                $('#chatgpt-response-container').html('Processing your question...');
            },
            success: function (response) {
                $('#chatgpt-response-container').html(response);
            },
            error: function () {
                $('#chatgpt-response-container').html('An error occurred. Please try again.');
            }
        });
    });
});
