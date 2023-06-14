<div class="page-content">

    <nav class="page-breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="#">Users</a></li>
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
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            
                            if(isset($users)){
                                foreach($users as $key => $user){
                                    $count = '0';
                                    $naq_count = 0;
                                    $pire_count = 0;
                                    $count =  $this->common_model->select_where_ASC_DESC_Group_by("*",'answers', array('user_id'=>$user['id']), '' , '', 'response_id')->num_rows();
                                    $naq_count = $this->common_model->select_where_ASC_DESC_Group_by("*",'answers', array('user_id'=>$user['id'], 'type'=>'naq'), '' , '', 'response_id')->num_rows();
                                    $pire_count = $this->common_model->select_where_ASC_DESC_Group_by("*",'answers', array('user_id'=>$user['id'], 'type'=>'pire'), '' , '', 'response_id')->num_rows();
                                    $users[$key]['count'] = $count;
                                    $users[$key]['naq_count'] = $naq_count;
                                    $users[$key]['pire_count'] = $pire_count;
                                }

                                usort($users, function($a, $b) {
                                    return $b['count'] - $a['count'];
                                });

                                foreach($users as $key => $data){ ?>
                                <tr>
                                    <td><?= $key+1; ?></td>
                                    <td><?= $data['name'] .' ('. $data['count'].')'?></td>
                                    <td><?= $data['email'] ?></td>
                                    <td><a data-toggle="tooltip" title="User Response" href="<?= base_url('admin/date_group/').$data['id'] ?>">Pire<?php echo' ('. $data['pire_count'].')' ?></a></td>
                                    <td><a data-toggle="tooltip" title="User Response" href="<?= base_url('admin/date_group_naq/').$data['id']  ?>">Naq<?php echo' ('. $data['naq_count'].')' ?></a></td>
                                    <td class="d-flex">
                                        <a href="<?= base_url('admin/delete_user/').$data['id']; ?>" class="nav-link">
                                            <i data-feather="trash-2"></i>
                                        </a>
                                    </td>
                                </tr>
                            
                            <?php } } ?>
                        </tbody>
                    </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

</div>