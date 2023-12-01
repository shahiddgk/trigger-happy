<div class="page-content">

    <nav class="page-breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="<?= base_url('admin/column_list/').$column_history[0]['user_id'] ?>">Column List</a></li>
            <li class="breadcrumb-item active" aria-current="page"><?= $page_title ?></li>
        </ol>
    </nav>
    <div class="row">
        <div class="col-md-12 grid-margin stretch-card">
            <div class="card">
                <div class="card-body">
                    <div class="table-responsive">
                        <table id="dataTableExample" class="table">
                        <thead>
                            <tr>
                                <th>Sr.</th>
                                <th>Entry Title</th>
                                <th>Entry Description</th>
                                <th>Entry Takeaway</th>
                                <th>Entry Date</th>
                                <th>Updated By</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            if (isset($column_history)) {
                                foreach ($column_history as $key => $data) { ?>
                                    <tr>
                                        <td><?= $key + 1; ?></td>
                                        <td><?= $data['entry_title']; ?></td>
                                        <td>
                                            <?php
                                            $descriptionWords = explode(' ', $data['entry_decs']);
                                            $descriptionId = 'description_decs_' . $data['id'];
                                            ?>
                                            <div id="<?= $descriptionId ?>" style="white-space: pre-wrap;"><?= implode(' ', array_slice($descriptionWords, 0, 5)) ?></div>
                                            <?php if (count($descriptionWords) > 5) { ?>
                                                <a href="javascript:void(0);" onclick="toggleText('<?= $descriptionId ?>', '<?= implode(' ', $descriptionWords) ?>')">Read more</a>
                                            <?php } ?>
                                        </td>
                                        <td>
                                            <?php
                                            $takeawayWords = explode(' ', $data['entry_takeaway']);
                                            $takeawayId = 'description_takeaway_' . $data['id'];
                                            ?>
                                            <div id="<?= $takeawayId ?>" style="white-space: pre-wrap;"><?= implode(' ', array_slice($takeawayWords, 0, 5)) ?></div>
                                            <?php if (count($takeawayWords) > 5) { ?>
                                                <a href="javascript:void(0);" onclick="toggleText('<?= $takeawayId ?>', '<?= implode(' ', $takeawayWords) ?>')">Read more</a>
                                            <?php } ?>
                                        </td>
                                        <td><?= $data['entry_date']; ?></td>
                                        <td><?= $data['defined_by']; ?></td>
                                    </tr>
                            <?php }
                            } ?>
                        </tbody>
                    </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<script>
    function toggleText(id, fullText) {
        var elem = document.getElementById(id);
        var readMoreLink = elem.nextElementSibling;

        if (elem.innerText.trim() === fullText.trim()) {
            elem.innerHTML = fullText.substr(0, fullText.lastIndexOf(' ')) + '...';
            readMoreLink.style.display = 'inline';
        } else {
            elem.innerHTML = fullText;
            readMoreLink.style.display = 'none';
        }
    }
</script>
