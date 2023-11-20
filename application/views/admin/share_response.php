<style>
.group {
    display: flex;
    align-items: center;
    margin: auto;
    margin-bottom: 15px;
    border-radius: 10px;
    width: 80%;
    box-shadow: 0 0 10px rgba(0, 0, 0, 10);
    background-color: #d9d9d9;
}

.text-wrapper-3 {
    font-weight: bold;
    font-size: 20px;
    display: flex;
    align-items: center;
}

.type-class {
    color: #33aeb3;
    font-size: 20px;
}

.shared-by {
    margin-top: 5px;
    font-size: 16px;
    display: flex;
    align-items: center;
}

.shared-by-text {
    font-weight: bold;
    font-size: 14px;
    color: #555;
    margin-right: 5px;
}

.sender-name {
    font-size: 14px;
    color: #555;
}
.date {
    color : #555;
}

.group-2 {
    border-left: 20px solid #33aeb3;
    border-bottom-left-radius: 10px;
    border-top-left-radius: 10px;
    padding: 13px;
    flex: 1;
    display: flex;
    flex-direction: column;
}

.link-icon {
    margin-right: 5px;
    color : #33aeb3;
}

.arrow-icon {
    display: flex;
    align-items: center;
}

.feedback-waiting {
    text-align: center;
    font-size: 18px;
    font-weight: bold;
    color: #33aeb3;
    animation: blink 1s linear infinite;
}

@keyframes blink {
    0% {
        opacity: 1;
    }
    50% {
        opacity: 0;
    }
    100% {
        opacity: 1;
    }
}

.filter-dropdown {
    margin-bottom: 20px;
    display: table;
    align-items: center;
    justify-content: center;
}

/* Style the label */
.filter-dropdown label {
    margin-right: 10px;
    font-weight: bold;
    font-size: 16px;
}

/* Style the select element */
.filter-dropdown select {
    padding: 5px;
    border: 2px solid #33aeb3;
    border-radius: 5px;
    background-color: #f9f9f9;
    color: #333;
    font-size: 16px;
    cursor: pointer;
    transition: border-color 0.3s;
}

</style>

<div class="page-content">
    <div class="filter-dropdown">
        <label for="filter-select">Filter by</label>
        <select id="filter-select">
            <option value="all">All List</option>
            <option value="waiting">Waiting List</option>
        </select>
    </div>

    <?php foreach ($share_response as $room): ?>
    <?php
    // Calculate feedback given status for each response item
    $feedback_given = $this->common_model->select_where('id', 'sage_feedback', array('shared_id' => $room['id']))->num_rows();
    $feedback_class = ($feedback_given) ? '' : 'waiting';
    ?>
    <a href="<?= base_url('admin/response_detail?type=' . $room['type'] . '&entity_id=' . $room['entity_id']) ?>"
        class="group response-item <?= $feedback_class ?>">
        <div class="group-2">
            <div class="text-wrapper-3 type-class"><?= strtoupper($room['type']) ?></div>
            <p class="shared-by">
                <span class="shared-by-text">Shared by:</span>
                <span class="sender-name"><?= isset($room['sender_name']) ? $room['sender_name'] : 'N/A' ?></span>
            </p>
            <p class="date">
                <i class="link-icon" data-feather="calendar"></i>
                <?= date('M d Y', strtotime($room['created_at'])) ?>
            </p>
            <?php
            if (!$feedback_given) {
                echo '<p class="feedback-waiting">Waiting for feedback</p>';
            }
            ?>
        </div>
        <div class="arrow-icon">
            <i class="link-icon" data-feather="arrow-right-circle"></i>
        </div>
    </a>
    <?php endforeach; ?>
</div>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script>
$(document).ready(function() {
    $("#filter-select").change(function() {
        var selectedOption = $(this).val();

        // Hide all responses
        $(".response-item").hide();

        if (selectedOption === "all") {
            // Show all responses
            $(".response-item").show();
        } else if (selectedOption === "waiting") {
            // Show only waiting responses
            $(".response-item.waiting").show();
        }
    });
});
</script>

