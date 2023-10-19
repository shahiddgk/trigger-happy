<div class="page-content">

    <nav class="page-breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="#">Questions</a></li>
            <li class="breadcrumb-item active" aria-current="page"><?= $page_title ?></li>
        </ol>
    </nav>
    <div class="row">
    <div class="col-md-12">
        <div class="card">
            <div class="card-body d-flex justify-content-between align-items-center">
                <h6 class="card-title mb-0">Question</h6>
                <a href="<?= base_url('admin/add_pire_positive') ?>" class="btn btn-primary">Add Question</a>
            </div>
        </div>
    </div>
</div>
    <div class="row">
        <div class="col-md-12 grid-margin stretch-card">
            <div class="card">
                <div class="card-body">
                    <h6 class="card-title"><?= $page_title ?></h6>
                    <div class="table-responsive">
                        <table id="dataTableExample" class="table">
                            <thead>
                                <tr>
                                    <th>Sr.</th>
                                    <th>Question Title</th>
                                    <th>Options</th>
                                    <th>Response Type</th>
                                    <th>Record Type</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                if (isset($questions)) {
                                    $srNo = 1; // Initialize the Sr No counter
                                    foreach ($questions as $data) {
                                        $splits = explode(",", json_decode($data['options']));
                                        ?>
                                        <tr>
                                            <td><?= $srNo; ?></td> <!-- Display the Sr No -->
                                            <td width="45%" class="cstm-title">
                                                <?= $data['title'] . '<br>' . $data['sub_title'] ?></td>
                                            <?php if ($data['options'] != 'NULL') { ?>
                                                <td class="cstm-options">
                                                    <?php foreach ($splits as $split) { ?>
                                                        <a href="#" class="badge badge-primary"><?= $split ?></a>
                                                    <?php } ?>
                                                </td>
                                            <?php } else { ?>
                                                <td></td>
                                            <?php } ?>
                                            <td><?= ucwords(str_replace("_", " ", $data['response_type'])); ?></td>
                                            <td><?= $data['type'] ?></td>
                                            <td class="d-flex">
                                                <a href="<?= base_url('admin/edit_pire_positive/') . $data['id']; ?>" class="nav-link">
                                                    <i data-feather="edit"></i>
                                                </a>
                                                <a href="<?= base_url('admin/delete_pire_positive/') . $data['id']; ?>" class="nav-link">
                                                    <i data-feather="trash-2"></i>
                                                </a>
                                            </td>
                                        </tr>
                                        <?php
                                        $srNo++; // Increment the Sr No counter
                                    }
                                }
                                ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

</div>