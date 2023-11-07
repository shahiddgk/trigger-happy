<style>
    .custom-list {
        list-style: none;
        padding: 0;
    }

    .list-container {
        display: flex;
        flex-direction: column;
        align-items: center;
    }

    .list-item {
        background-color: #f2f2f2;
        border-radius: 10px;
        margin-bottom: 10px;
        padding: 10px;
        text-align: left;
        width: 100%;
    }

    .list-item a {
        text-decoration: none;
        color: #007bff;
        font-weight: bold;
    }

    .list-item p {
        margin: 0;
        font-size: 14px;
    }

    .list-item-info {
        font-size: 12px;
        color: #999;
    }
</style>


<div class="page-content">
    <nav class="page-breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="#">Home</a></li>
            <li class="breadcrumb-item active" aria-current="page"><?= $page_title ?></li>
        </ol>
    </nav>
    <div class="row">
        <div class="col-md-4">
            <div class="list-container">
                <h6><?= $page_title ?></h6>
                <ul class="custom-list">
                    <?php foreach ($chat_room as $room): ?>
                        <li class="card list-item">
                            <div class="overlap-group-3">
                                <a href="<?= base_url('admin/response_detail?type=' . $room['type'] . '&entity_id=' . $room['entity_id']) ?>" class="text-wrapper-6"><?= strtoupper($room['type']) ?></a>
                                <p class="p">
                                    <span class="span">Shared by:</span>
                                    <span class="text-wrapper-7">&nbsp;</span>
                                    <span class="text-wrapper-8"><?= $room['sender_name'] ?></span>
                                </p>
                            </div>
                            <p class="list-item-info">
                                <?= date('M d Y', strtotime($room['created_at'])) ?>
                            </p>
                        </li>
                    <?php endforeach; ?>
                </ul>
            </div>
        </div>
    </div>
</div>
