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
                                <th>Response Dated</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if(isset($answers)){
                                foreach($answers as $key => $data){
                                  $date =   date('Y-m-d', strtotime($data['created_at'])); ?>
                                <tr>
                                    <td><?= $key+1; ?></td>
                                    <td><a href="<?= base_url('admin/date_respose/'.$data['user_id'].'/'. $date); ?>"><?=  $date ?></a></td>
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