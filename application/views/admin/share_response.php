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
}

.group-2 {
    border-left: 20px solid  #33aeb3;
    border-bottom-left-radius: 10px;
    border-top-left-radius: 10px;
    padding: 13px;
    flex: 1;
    display: flex;
    flex-direction: column;
}

.link-icon {
    margin-right: 5px;
}

.arrow-icon {
    display: flex;
    align-items: center;
}



</style>

<div class="page-content">
    <?php foreach ($chat_room as $room): ?>
        <div class="group">
            <div class="group-2">
            <a href="<?= base_url('admin/response_detail?type=' . $room['type'] . '&entity_id=' . $room['entity_id']) ?>" class="text-wrapper-3 type-class"><?= strtoupper($room['type']) ?></a>
                <p class="shared-by">
                    <span class="shared-by-text">Shared by:</span>
                    <span class="sender-name"><?= $room['sender_name'] ?></span>
                </p>
                <p class="date">
                    <i class="link-icon" data-feather="calendar"></i>
                    <?= date('M d Y', strtotime($room['created_at'])) ?>
                </p>
            </div>
            <a href="<?= base_url('admin/response_detail?type=' . $room['type'] . '&entity_id=' . $room['entity_id']) ?>" class="arrow-icon">
                <i class="link-icon" data-feather="arrow-right-circle"></i>
            </a>
        </div>
    <?php endforeach; ?>
</div>
