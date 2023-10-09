<!DOCTYPE html>
<html>
<head>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

    <style>
        /* Add your CSS styling here */
        body {
            background-color: #33AEB1; /* Beautiful background color */
            font-family: Arial, sans-serif;
            margin: 0;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
        }
        .container {
            max-width: 600px;
            padding: 20px;
            background-color: #fff; /* Yellow background color */
            border-radius: 10px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
            text-align: center;
        }
        .message {
            margin-bottom: 20px;
        }
        .button {
            background-color: #007bff; /* Button background color */
            color: #fff;
            padding: 10px 20px;
            text-decoration: none;
            border-radius: 5px;
        }
    </style>
</head>
<body>
    <?php 
    $data_encoded = $this->input->get('data');
    $data_json = urldecode($data_encoded);
    $data_array = json_decode($data_json, true);
    ?>

    <?php if ($this->session->flashdata('success_message')) : ?>
        <div class="success-message"><?= $this->session->flashdata('success_message') ?></div>
    <?php endif; ?>

    <?php if ($this->session->flashdata('error_message')) : ?>
        <div class="error-message"><?= $this->session->flashdata('error_message') ?></div>
    <?php endif; ?>

    <div class="container">
        <h1>Burgeon Confirmation</h1>
        <div class="message">
            Hi <?php echo $data_array['receiver_name']; ?>,<br /><br />
            You have been invited to become a <?php echo $data_array['receiver_role']; ?> by <?php echo $data_array['sender_name']; ?>.<br />
        </div>

        <?php
        $accept_url = base_url() . "api/accept_invite?receiver_role=" . $data_array['receiver_role'] . "&sender_name=" . $data_array['sender_name'] . "&sender_id=" . $data_array['sender_id'] . "&receiver_id=" . $data_array['receiver_id'];
        $reject_url = base_url() . "api/reject_invite?sender_id=" . $data_array['sender_id'] . "&receiver_id=" . $data_array['receiver_id'];
        ?>
        <a class="button" href="<?= $accept_url ?>">Accept</a>
        <a class="button" href="<?= $reject_url ?>">Reject</a>

    </div>
</body>
</html>
