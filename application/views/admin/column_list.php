<div class="page-content">

    <nav class="page-breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="<?= base_url('admin/column_users') ?>">Column Users</a></li>
            <li class="breadcrumb-item active" aria-current="page"><?= $page_title ?></li>
        </ol>
    </nav>

    <div class="row">
        <div class="col-md-12 grid-margin stretch-card">
            <div class="card">
                <div class="card-body">
                <form action="<?= base_url('admin/column_list/').$user_id; ?>" method="get" class="form-inline">
                        <div class="form-group mx-sm-3 mb-2">
                            <label for="status" class="sr-only">Filter by status</label>
                            <select name="status" id="status" class="form-control">
                                <option value="yes" <?php if(isset($status) && $status == 'yes') echo 'selected'; ?>>Completed</option>
                                <option value="no" <?php if(isset($status) && $status == 'no') echo 'selected'; ?>>Incomplete</option>
                            </select>
                        </div>
                        <button type="submit" class="btn btn-primary">Filter</button>
                        <div class="form-group mx-sm-3">
                            <a href="<?= base_url('admin/column_list/').$user_id; ?>" class="btn btn-danger">Clear Filter</a>
                        </div>
                    </form>
                    <div class="d-flex justify-content-between align-items-baseline">
                        <h6 class="card-title"><?= $page_title ?></h6>
                        <a href="<?= base_url('admin/add_column/').$user_id; ?>" class="btn btn-primary">Add Column <i data-feather="file-plus"></i></a>
                    </div>
                    <div class="table-responsive">
                        <table id="dataTableExample" class="table">
                        <thead>
                            <tr>
                                <th>Sr.</th>
                                <th>Task Title</th>
                                <th>Task Description</th>
                                <th>Task Takeaway</th>
                                <th>Task Date</th>
                                <th>Task Type</th>
                                <th>Completed</th>
                                <th>Edit</th>
                                <th>History</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            if (isset($column_list)) {
                                foreach ($column_list as $key => $data) { ?>
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
                                        <td><?= $data['entry_type']; ?></td>
                                        <td><?= $data['completed']; ?></td>
                                        <td class="d-flex">
                                            <a href="<?= base_url('admin/edit_column/') . $data['id']; ?>" class="nav-link">
                                                <i data-feather="edit"></i>
                                            </a>
                                        </td>
                                        <td>
                                            <a href="<?= base_url('admin/column_history/') . $data['id']; ?>" class="nav-link">
                                                <i data-feather="globe"></i>
                                            </a>
                                        </td>
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
