<div class="page-content">
    <div class="row chat-wrapper">
        <div class="col-md-12">
            <div class="card">
                <div class="card-body">
                    <div class="container">
                        <div class="row">
                            <div class="col-12 mt-5 pt-3 pb-3 bg-white from-wrapper">
                            <div class="container">
                                <h3>Chat</h3>
                                <hr>
                                <div class="row">
                                <div class="col-12 col-sm-12 col-md-4 mb-3">
                                    <ul id="user-list" class="list-group"></ul>
                                </div>
                                <div class="col-12 col-sm-12 col-md-8">
                                    <div class="row">
                                    <div class="col-12">
                                        <div class="message-holder">
                                            <div id="messages" class="row"></div>
                                        </div>
                                        <div class="form-group">
                                        <textarea id="message-input" class="form-control" name="" rows="2"></textarea>
                                        </div>
                                    </div>
                                    <div class="col-12">
                                        <button id="send" class="btn float-right  btn-primary">Send</button>
                                    </div>
                                    </div>
                                </div>
                                </div>
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
    $(document).ready(function () {

        const socket = new WebSocket('wss://173.201.253.251:443');
        socket.onopen = () => {
            console.log('WebSocket connection established.');
        };
        socket.onmessage = (event) => {
            console.log('Received:', event.data);
        };


        // var conn = new WebSocket('ws://localhost:8080');

        // conn.onopen = function (e) {
        //     console.log("Connection established!");
        // };

        // conn.onmessage = function (e) {
        //     var message = JSON.parse(e.data); // Assuming the message is JSON
        //     console.log(message);
        //     // Handle the incoming message here
        // };

        $('#send').on('click', function () {
            var message = $('#message-input').val();
            // Send the message to the WebSocket server
            socket.send(message);
        });
    });
</script>
