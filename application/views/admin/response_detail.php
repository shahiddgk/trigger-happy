<style>
.response-box {
    background-color: #f2f2f2;
    border: 2px solid #333;
    padding: 20px;
    border-radius: 10px;
    margin-left: 50px;
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
    height: 70vh;
    max-height: 70vh;
    overflow-y: auto;
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

.message p {
    word-wrap: break-word;
    width: 100%;
    max-width: 200px;
}

.chat-header {
    margin-bottom: 10px;
}

/* .form-control {
    height: 35px !important;
    display: block;
} */

.chat-container {
    position: fixed;
    bottom: 20px;
    right: 20px;
    height: 85vh;
    max-height: 85vh;
    width: 35%;
    overflow-y: auto;
    background: white;
    border: 1px solid #ccc;
    padding: 15px;
}
</style>

<div class="page-content container-fluid">
    <div class="row">
        <div class="col-md-6">
                <?php if ($param_type == 'naq' || $param_type == 'pire'): ?>
                <div class="response-box">
                    <h4 class="card-title"><?= strtoupper($param_type) ?> Response</h4>
                    <p class="card-description">
                        <?php foreach ($response_data as $key => $value): ?>
                            <div class="response-item">
                                <span class="response-title"><?= strip_tags($value['title']) ?></span>
                            </div>
                            <div class="response-item">
                                <span class="response-text">
                                    Answer: <?= ($value['options'] || $value['text'] ? strip_tags($value['options']) . ' ' . strip_tags($value['text']) : 'N/A') ?>
                                </span>
                            </div>
                        <?php endforeach; ?>
                    </p>
                </div>
                <?php elseif ($param_type == 'column'): ?>
                <div class="response-box">
                    <h4 class="card-title">Column Response</h4>
                    <p class="card-description">
                        <?php foreach ($response_data as $key => $value): ?>
                            <div class="response-item">
                                <span class="response-title"><?= strip_tags($value['title']) ?></span>
                            </div>
                            <div class="response-item">
                                <span class="response-text">
                                    Tittle : <?= strip_tags($value['entry_title']) ?>
                                    <br>
                                    Description : <?= strip_tags($value['entry_decs']) ?>
                                    <br>
                                    Date : <?= strip_tags($value['entry_date']) ?>
                                    <br>
                                    TakeAway : <?= strip_tags($value['entry_takeaway']) ?>
                                    <br>
                                    Type : <?= strip_tags($value['entry_type']) ?>
                                    <br>
                                </span>
                            </div>
                        <?php endforeach; ?>
                    </p>
                </div>
            <?php endif; ?>
        </div>

        <div class="col-md-6">
            <div class="chat-container fixed-right" style="margin-top: 20px;">
            <div class="chat-body" id="chatBody">
                <div class="chat-header border-bottom pb-2">
                    <div class="d-flex justify-content-between">
                        <div class="d-flex align-items-center">
                            <i data-feather="corner-up-left" id="backToChatList" class="icon-lg mr-2 ml-n2 text-muted d-lg-none"></i>
                            <figure class="mb-0 mr-2">
                                <?php if (!empty($sender_detail['image']) && file_exists('uploads/app_users/' . $sender_detail['image'])) : ?>
                                    <img src="<?= base_url('uploads/app_users/' . $sender_detail['image']) ?>" class="img-sm rounded-circle" alt="image">
                                <?php else : ?>
                                    <img src="<?= base_url('uploads/app_users/default.png') ?>" class="img-sm rounded-circle" alt="default image">
                                <?php endif; ?>
                            </figure>
                            <div>
                                <p>
                                <?php
                                    if (!empty($sender_detail['name'])) {
                                        echo $sender_detail['name'];
                                    } else {
                                        echo 'Anonymous';
                                    }
                                ?>
                                </p>
                            </div>
                        </div>
                        <div class="d-flex align-items-center mr-n1">
                            <i data-feather="user-plus" class="icon-lg text-muted"></i>
                        </div>
                    </div>
                </div>
                <ul class="messages">
                    <?php
                    $lastMessage = !empty($chat_message) ? end($chat_message) : null;
                    $isMyLastMessage = $lastMessage ? ($this->session->userdata('userid') === $lastMessage->sender_id) : false;
                    if (!empty($chat_message)) {
                        foreach ($chat_message as $key => $chat) {
                            $messageClass = ($this->session->userdata('userid') === $chat->sender_id) ? 'me' : 'friend';
                            if (!empty($chat->message)) {
                    ?>
                            <li class="message-item <?= $messageClass ?>">
                                <div class="content">
                                    <div class="message">
                                        <div class="bubble">
                                            <p><?= $chat->message ?></p>
                                        </div>
                                    </div>
                                </div>
                            </li>
                    <?php
                            }
                        }
                    }
                    ?>
                    <input type="hidden" id="isMyLastMessage" value="<?= $isMyLastMessage ?>">
                </ul>
            </div>
                <div class="chat-footer d-flex">
                    <div class="search-form flex-grow mr-2">
                        <div class="input-group">
                        <textarea id="feedbackText" name="w3review" rows="7" cols="55"></textarea>                        
                    </div>
                    </div>
                    <div>
                        <button type="button" onclick="sendFeedbackChat('<?= $param_type ?>', '<?= $entity_id ?>')" class="btn btn-primary btn-icon rounded-circle" id="sendFeedback">
                            <i data-feather="send"></i>
                        </button>
                    </div>
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
                var result = JSON.parse(response);
                if (result.status === 'success') {
                    $('#feedbackText').val('');
                    Swal.fire({
                        icon: 'success',
                        title: 'Copied',
                        text: 'Feedback sent successfully',
                        timer: 2000, // time in milliseconds
                        toast: true, // set to true to make it a toast
                        position: 'top-right',
                        showConfirmButton: false, // hide the OK button
                        timerProgressBar: true, // show timer progress bar
                    });
                    setTimeout(() => {
                        window.location.reload();
                    }, 2000);
                } else if (result.status === 'error') {
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: 'Feedback not submitted. ' + result.message,
                    });
                }
            },
            error: function(xhr, status, error) {
                console.log(xhr.responseText);
            }
        });
    } else {
        Swal.fire({
            icon: 'error',
            title: 'Error',
            text: 'Please enter a message before sending feedback.',
        });
    }
}
window.onload = function() {
    let isMyLastMessage = document.getElementById('isMyLastMessage').value;
    if (isMyLastMessage) {
        $('#feedbackText').prop('disabled', true);
        $('#sendFeedback').prop('disabled', true);
    } else {
        $('#feedbackText').prop('disabled', false);
        $('#sendFeedback').prop('disabled', false);
    }
}
</script>