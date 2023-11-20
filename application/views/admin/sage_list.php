<div class="page-content">
    <nav class="page-breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="#">Sage List</a></li>
            <li class="breadcrumb-item active" aria-current="page"><?= $page_title ?></li>
        </ol>
    </nav>

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
                                <th>Name</th>
                                <th>Email</th>
                                <th>Pire</th>
                                <th>Naq</th>
                                <th>Column</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            $count = 1;
                            foreach ($sage_list as $key => $data) {
                                if ($data['pire_count'] !== 0 || $data['naq_count'] !== 0) { ?>
                                    <tr>
                                        <td><?= $count; ?></td>
                                        <td><?= $data['sender_name'] ?></td>
                                        <td><?= $data['sender_email'] ?></td>
                                        <td>
                                            <?php if ($data['pire_count'] !== 0): ?>
                                                <?php if ($data['pire_not_answered']): ?>
                                                    <a href="<?= base_url('admin/share_response/pire/' . $key) ?>">Pire (<?= $data['pire_count'] ?>)*</a>
                                                <?php else: ?>
                                                    <a href="<?= base_url('admin/share_response/pire/' . $key) ?>">Pire (<?= $data['pire_count'] ?>)</a>
                                                <?php endif; ?>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <?php if ($data['naq_count'] !== 0): ?>
                                                <?php if ($data['naq_not_answered']): ?>
                                                    <a href="<?= base_url('admin/share_response/naq/' . $key) ?>">Naq (<?= $data['naq_count'] ?>)*</a>
                                                <?php else: ?>
                                                    <a href="<?= base_url('admin/share_response/naq/' . $key) ?>">Naq (<?= $data['naq_count'] ?>)</a>
                                                <?php endif; ?>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <?php if ($data['column_count'] !== 0): ?>
                                                <?php if ($data['column_not_answered']): ?>
                                                    <a href="<?= base_url('admin/share_response/column/' . $key) ?>">Column (<?= $data['column_count'] ?>)*</a>
                                                <?php else: ?>
                                                    <a href="<?= base_url('admin/share_response/column/' . $key) ?>">Column (<?= $data['column_count'] ?>)</a>
                                                <?php endif; ?>
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                    <?php $count++;
                                }
                            } ?>
                        </tbody>
                    </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>