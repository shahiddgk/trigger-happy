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
                                <th>Ladeer</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                                foreach($sage_list as $key => $data){ ?>
                                <tr>
                                    <td><?= $key+1; ?></td>
                                    <td><?= $data['sender_name'] ?></td>
                                    <td><?= $data['sender_email'] ?></td>
                                    <td><?= $data['pire'] ? '<a href="' . base_url('admin/share_response/pire/' . $key) . '">Pire</a>' : '' ?></td>
                                    <td><?= $data['naq'] ? '<a href="' . base_url('admin/share_response/naq/' . $key) . '">Naq</a>' : '' ?></td>
                                    <td><a href="<?= base_url('admin/share_response/').$data['type'] . '/'. $data['sender_id'] ?>"><?= $data['type'] == 'ladder' ? $data['type'] : '' ?></a></td>
                                </tr>
                            <?php }  ?>
                        </tbody>
                    </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>