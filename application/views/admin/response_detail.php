<div class="page-content d-flex justify-content-center align-items-center" style="height: 50%;">
    <div class="response-box" style="width: 45%; background-color: #f2f2f2; border: 2px solid #333; padding: 20px; border-radius: 10px; margin-right: 20px;">
        <h4 class="card-title">Response</h4>
        <p class="card-description" style="font-family: 'Arial', sans-serif; font-size: 16px; color: #333; text-align: center;">
            <?php foreach ($response_data as $key => $value): ?>
                <!-- Replace with your response content -->
                <div style="margin-bottom: 20px;">
                    <span style="font-size: 16px; color: #007bff;"><?= strip_tags($value['title']) ?></span>
                </div>
                <div style="margin-bottom: 20px;">
                    <span style="font-size: 14px; color: #333;">
                        Answer: <?= ($value['options'] || $value['text'] ? strip_tags($value['options']) . ' ' . strip_tags($value['text']) : 'N/A') ?>
                    </span>
                </div>
            <?php endforeach; ?>

            <button class="btn btn-primary" id="toggleChatButton" onclick="toggleChatBox()">Send Feedback</button>
        </p>
    </div>

    <div class="chat-box" style="width: 35%; background-color: #fff; border: 2px solid #333; border-radius: 10px; padding: 20px; text-align: left; display: none; position: relative; top: 0;">
        <div class="message">
            <?php if (!empty($chat_message)) : ?>
            <?php foreach ($chat_message as $key => $chat): ?>
                    <div style="background-color: #e0e0e0; border-radius: 10px; padding: 10px; margin: 10px 0;">
                        <span><?= $chat->message ?></span>
                    </div>
                <?php endforeach; ?>
            <?php else : ?>
                <p style="font-size: 16px; color: #333; text-align: center;">No chat messages available.</p>
            <?php endif; ?>
        </div>

        <div class="chat-footer d-flex">
            <div class="search-form flex-grow mr-2">
                <div class="input-group">
                    <input type="text" class="form-control rounded-pill" id="feedbackText" placeholder="Type a message">
                </div>
            </div>
            <div>
                <button type="button" onclick="sendFeedbackChat('<?php echo $param_type; ?>', '<?php echo $entity_id; ?>')" class="btn btn-primary btn-icon rounded-circle" id="sendFeedback">
                    <i data-feather="send"></i>
                </button>
            </div>
        </div>
    </div>
</div>


<script>

function sendFeedbackChat(param, shared_id) {
        var message = $('#feedbackText').val();
        if (message !== '') {
            $.ajax({
                url: '<?php echo base_url(); ?>admin/insert_feedback',
                type: 'POST',
                data: {
                    param: param,
                    shared_id: shared_id,
                    message: message,
                },
                success: function(response) {
                    $('#feedbackText').val('');
                    alert('Feedback sent successfully');
                    window.location.reload();
                },
                error: function(xhr, status, error) {
                    console.log('Error: ' + error);
                }
            });
        } else {
            alert('Please enter a message before sending feedback.');
        }
    }

    // JavaScript to toggle the chat-box display
    function toggleChatBox() {
        var chatBox = document.querySelector('.chat-box');
        chatBox.style.display = (chatBox.style.display === 'none' || chatBox.style.display === '') ? 'block' : 'none';
    }

    // Check if there are chat messages and show chat-box accordingly
    window.onload = function () {
        var chatMessages = <?php echo json_encode($chat_message); ?>;
        var chatBox = document.querySelector('.chat-box');
        if (chatMessages && chatMessages.length > 0) {
            chatBox.style.display = 'block';
        }
    }

</script>
