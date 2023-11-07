<style>
.response-box {
    background-color: #f2f2f2;
    border: 2px solid #333;
    padding: 20px;
    border-radius: 10px;
    margin-right: 20px;
}

.response-box h4 {
    font-family: 'Arial', sans-serif;
    font-size: 16px;
    color: #333;
    text-align: center;
}

.card-description {
    font-size: 16px;
    color: #333;
    text-align: center;
}

.response-item {
    margin-bottom: 20px;
}

.response-title {
    font-size: 16px;
    color: #007bff;
}

.response-text {
    font-size: 14px;
    color: #333;
}

.chat-body {
    background-color: #fff;
    border-top: 2px solid #333;
    border-right: 2px solid #333;
    border-left: 2px solid #333;
    border-top-left-radius: 10px;
    border-top-right-radius: 10px;
    padding: 20px;
    text-align: left;
    position: relative;
    /* height: 30%; */
    top: 0;
}

.chat-footer {
    display: flex;
    align-items: center;
    background-color: #fff;
    padding: 10px;
    border-bottom-left-radius: 10px;
    border-bottom-right-radius: 10px;
    border-bottom: 2px solid #333;
    border-right: 2px solid #333;
    border-left: 2px solid #333;
}

.search-form {
    flex-grow: 1;
    margin-right: 2px;
}

.input-group {
    display: flex;
    align-items: center;
}

.rounded-pill {
    border-radius: 50px !important;
}

.btn-icon {
    background-color: #33AEB1;
    color: #fff;
}

.btn-icon:hover {
    background-color: #0056b3;
}

.messages {
    list-style-type: none;
    padding: 0;
}

.message-item {
    display: flex;
    margin-bottom: 10px;
    max-width: 100%;
    border-radius: 10px;
    clear: both;
}

.message-item.me .content {
    justify-content: flex-end !important;
    margin-left: auto;
}

.message-item.friend .content {
    justify-content: flex-start !important;
}

.bubble {
    padding: 10px;
    border-radius: 10px;
}

.me .bubble {
    background-color: #33AEB1 !important;
    color: #fff;
}

.friend .bubble {
    background-color: #A8CACC !important;
    color: #000;
}
</style>

<div class="page-content d-flex justify-content-center align-items-center" style="height: 50%;">
    <div class="row">
        <div class="col-md-7">
            <div class="response-box">
                <h4 class="card-title">Response</h4>
                <p class="card-description">
                    <?php foreach ($response_data as $key => $value): ?>
                <div class="response-item">
                    <span class="response-title"><?= strip_tags($value['title']) ?></span>
                </div>
                <div class="response-item">
                    <span class="response-text">
                        Answer:
                        <?= ($value['options'] || $value['text'] ? strip_tags($value['options']) . ' ' . strip_tags($value['text']) : 'N/A') ?>
                    </span>
                </div>
                <?php endforeach; ?>
                </p>
            </div>
        </div>
        <div class="col-md-5">
            <div class="chat-body" id="chatBody">
                <ul class="messages">
                    <?php foreach ($chat_message as $key => $chat) : ?>
                    <?php
                    $messageClass = ($this->session->userdata('userid') === $chat->sender_id) ? 'me' : 'friend';
                    ?>
                    <?php if (!empty($chat->message)) : ?>
                    <li class="message-item <?= $messageClass ?>">
                        <div class="content">
                            <div class="message">
                                <div class="bubble">
                                    <p><?= $chat->message ?></p>
                                </div>
                            </div>
                        </div>
                    </li>
                    <?php endif; ?>
                    <?php endforeach; ?>
                </ul>
            </div>
            <div class="chat-footer d-flex">
                <div class="search-form flex-grow mr-2">
                    <div class="input-group">
                        <input type="text" class="form-control rounded-pill" id="feedbackText"
                            placeholder="Type a message">
                    </div>
                </div>
                <div>
                    <button type="button"
                        onclick="sendFeedbackChat('<?php echo $param_type; ?>', '<?php echo $entity_id; ?>')"
                        class="btn btn-primary btn-icon rounded-circle" id="sendFeedback">
                        <i data-feather="send"></i>
                    </button>
                </div>
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
</script>