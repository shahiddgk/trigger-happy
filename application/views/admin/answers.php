<div class="page-content">

    <nav class="page-breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="<?= base_url('admin/users_list')?>">Users</a></li>
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
                                <th>Questions</th>
                                <th>Options</th>
                                <th>Text</th>
                                <th>Dated</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if(isset($answers)){
                                foreach($answers as $key => $data){
                                    $splits = explode(",", trim($data['options'], '[]'));?>
                                <tr>
                                    <td><?= $key+1; ?></td>
                                    <td width="40%" class="cstm-title"><?= $data['title'] ?></td>
                                    <?php if ($splits[0]==''){ ?>
                                        <td></td>
                                    <?php } else{ ?>
                                        <td>
                                            <?php  foreach($splits as $split){ ?>
                                                <a href="#" class="badge badge-primary"><?= $split ?></a>
                                            <?php }  ?>
                                        </td>
                                    <?php } ?>
                                    <td width="40%" class="cstm-title"><?= $data['text'] ?></td>
                                    <td><?= $data['created_at'] ?></td>
                                </tr>
                            <?php  } } ?>
                        </tbody>
                    </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

</div>