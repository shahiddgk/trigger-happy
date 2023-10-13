<div class="page-content">
    <div class="row chat-wrapper">
        <div class="col-md-12">
            <div class="card">
                <div class="card-body">
                    <div class="row position-relative">
                        <div class="col-lg-4 chat-aside border-lg-right">
                            <div class="aside-content">
                                <div class="aside-header">
                                    <div class="d-flex justify-content-between align-items-center pb-2 mb-2">
                                        <div class="d-flex align-items-center">
                                            <figure class="mr-2 mb-0">
                                                <img src="<?php echo base_url('uploads/profile/download.png'); ?>" class="img-sm rounded-circle" alt="profile">
                                                <div class="status online"></div>
                                            </figure>
                                            <div>
                                                <h6>Admin</h6>
                                            </div>
                                        </div>
                                    </div>
                                    <form class="search-form">
                                        <div class="input-group border rounded-sm">
                                            <div class="input-group-prepend">
                                                <div class="input-group-text border-0 rounded-sm">
                                                    <i data-feather="search" class="icon-md cursor-pointer"></i>
                                                </div>
                                            </div>
                                            <input type="text" class="form-control border-0 rounded-sm" id="searchForm" placeholder="Search here...">
                                        </div>
                                    </form>
                                </div>
                                <div class="aside-body">
                                    <div class="tab-content mt-3">
                                        <div class="tab-pane fade show active" id="chats" role="tabpanel" aria-labelledby="chats-tab">
                                            <div>
                                                <p class="text-muted mb-1">Recent chats</p>
                                                <ul class="list-unstyled chat-list px-1">
                                                    <?php foreach ($chat_room as $chat): ?>
                                                    <li class="chat-item pr-1" data-chat_id="<?php echo $chat['chat_id']; ?>">
                                                        <a href="javascript:;" class="d-flex align-items-center">
                                                            <figure class="mb-0 mr-2">
                                                                <?php
                                                                if ($chat['image'] != null) {
                                                                    $image_url = base_url('uploads/app_users/' . $chat['image']);
                                                                } else {
                                                                    $image_url = 'https://via.placeholder.com/30x30';
                                                                }
                                                                ?>
                                                                <img src="<?php echo $image_url; ?>" class="img-xs rounded-circle">
                                                                <div class="status online"></div>
                                                            </figure>
                                                            <div class="d-flex align-items-center justify-content-between flex-grow border-bottom">
                                                                <div>
                                                                    <p class="text-body"><?php echo $chat['name']; ?></p>
                                                                    <div class="d-flex align-items-center">
                                                                        <?php
                                                                        $words = explode(' ', $chat['entry_text']);
                                                                        if (count($words) > 2) {
                                                                            $short_text = implode(' ', array_slice($words, 0, 2)) . " .....";
                                                                        } else {
                                                                            $short_text = implode(' ', $words);
                                                                        }
                                                                        ?>
                                                                        <p class="text-muted tx-13">
                                                                            <?php echo $short_text; ?>
                                                                        </p>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                            <?php if ($chat['unread_count'] != 0): ?>
                                                            <div class="d-flex flex-column align-items-end" id="unreadChatCount<?php echo $chat['chat_id']; ?>">
                                                                <div class="badge badge-pill badge-primary ml-auto">
                                                                    <?php echo $chat['unread_count']; ?>
                                                                </div>
                                                            </div>
                                                            <?php endif; ?>
                                                        </a>
                                                    </li>
                                                    <?php endforeach; ?>
                                                </ul>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-lg-8 chat-content" style="display: none;" id="chatContent">
                            <div class="chat-header border-bottom pb-2">
                                <input type="hidden" id="is_chat_person_selected" value="">
                                <div class="d-flex justify-content-between">
                                    <div class="d-flex align-items-center">
                                        <i data-feather="corner-up-left" id="backToChatList" class="icon-lg mr-2 ml-n2 text-muted d-lg-none"></i>
                                        <figure class="mb-0 mr-2">
                                            <img id="selectedUserImage" src="" class="img-sm rounded-circle">
                                            <div class="status online"></div>
                                        </figure>
                                        <div>
                                            <p id="selectedUserName"></p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="chat-body" id="chatBody">
                                <ul class="messages"></ul>
                            </div>
                            <div class="chat-footer d-flex">
                                <div class="search-form flex-grow mr-2">
                                    <div class="input-group">
                                        <input type="text" class="form-control rounded-pill" id="feedbackText" placeholder="Type a message">
                                    </div>
                                    <input type="hidden" id="send_chat_id" value="">
                                </div>
                                <div>
                                    <button type="button" onclick="sendFeedbackChat()" class="btn btn-primary btn-icon rounded-circle" id="sendFeedback">
                                        <i data-feather="send"></i>
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<script>
$(document).ready(function() {
    $("#is_chat_person_selected").val(0);
    // get chat messages after every 10 seconds
    setInterval(() => {
        var chat_id = $('#send_chat_id').val();
        // get value from is_chat_person_selected
        var is_chat_person_selected = $("#is_chat_person_selected").val();
        if (is_chat_person_selected == 1) {
            displayUserChats(chat_id);
        }
    }, 5000);

    // Add a click event listener to each user in the chat list
    $('.chat-item').on('click', function() {
        $("#is_chat_person_selected").val(1);
        // Extract the selected user's data from the clicked item
        var chat_id = $(this).data('chat_id'); // Assuming you have a data attribute for chat_id
        $("#send_chat_id").val(chat_id);
        // Send an AJAX request to fetch chat messages for the selected user
        displayUserChats(chat_id);
        // read chats 
        readChatMessages(chat_id);

        // Update the chat header with the selected user's information
        var selectedUserImage = $(this).find('img').attr('src');
        var selectedUserName = $(this).find('.text-body').text();

        $('#selectedUserImage').attr('src', selectedUserImage);
        $('#selectedUserName').text(selectedUserName);
    });
});

function sendFeedbackChat() {
    var chat_id = $('#send_chat_id').val();
    var feedbackText = $('#feedbackText').val();
    if (feedbackText != '') {
        // make a POST request to store message data
        $.ajax({
            url: '<?= base_url('admin/insert_feedback') ?>',
            type: 'POST',
            data: {
                chat_id: chat_id,
                entry_text: feedbackText,
            },
            success: function(response) {
                $('#feedbackText').val('');
                displayUserChats(chat_id);
            },
            error: function(xhr, status, error) {
                console.log('Error: ' + error);
            }
        });
    }
}

function displayUserChats(chat_id) {
    $("#chatContent").show();
    $.ajax({
        type: 'GET',
        url: '<?= base_url('admin/chat_messages/') ?>' + '/' + chat_id,
        success: function(data) {
            var messagesHtml = '';
            var all_messages = JSON.parse(data); // Parse the JSON data
            var messages = all_messages.messages;
            var logged_user = all_messages.logged_id;

            // Loop through the chat messages
            for (var i = 0; i < messages.length; i++) {
                var message = messages[i];

                // Determine the message class based on sender_id
                var messageClass = logged_user == message.sender_id ? 'me' : 'friend';
                var dbImage = "<?php echo base_url('uploads/app_users/'); ?>" + message.image;

                if (message.image) {
                    var imageUrl = dbImage;
                } else {
                    var imageUrl = 'https://via.placeholder.com/30x30';
                }
                // Create the HTML structure for each message
                var messageHtml = `
                <li class="message-item ${messageClass}">
                    <img src="${imageUrl}" class="img-xs rounded-circle" alt="avatar">
                    <div class="content">
                        <div class="message">
                            <div class="bubble">
                                <p>${message.entry_text}</p>
                            </div>
                            <span>${message.created_at}</span>
                        </div>
                        <input type="hidden" name="chat_id" id="chat_id" value="${message.chat_id}">
                    </div>
                </li>
                `;

                messagesHtml += messageHtml; // Append the message to the HTML
            }

            // Update the chat body with the generated HTML
            $('#chatBody ul.messages').html(messagesHtml);
        },
        error: function(xhr, status, error) {
            console.log('Error: ' + error);
        }
    });
}

function readChatMessages(chat_id) {
    $.ajax({
        url: '<?= base_url('admin/read_chat_messages/') ?>' + chat_id,
        success: function() {
            $("#unreadChatCount" + chat_id).empty();
        }
    });
}
</script>
