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
                                <th>Response Date</th>
                                <th>No. of response</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if(isset($answers)){
                                foreach($answers as $key => $data){
                                    $chk_date =   date('Y-m-d', strtotime($data['created_at'])); 
                                    $count = '0';
                                    $count =  $this->common_model->select_where_ASC_DESC_Group_by("*",'answers', array('user_id'=>$data['user_id'], 'type'=>'naq', 'DATE(created_at)'=> $chk_date), '' , '', 'response_id')->result_array();
                                    $answers[$key]['count'] = $count;
                                }
                                
                                    foreach($answers as $key => $data){
                                    $date =   date('Y-m-d', strtotime($data['created_at'])); 
                                ?>
                                <tr>
                                    <td><?= $key+1; ?></td>
                                    <td><?=  $date  ?></td>
                                    <td class="cstm-response">
                                    <?php if(isset($data['count'])){
                                        foreach($data['count'] as $no => $value){ ?>
                                        <a class="nav-link" href="<?= base_url('admin/get_response/'.$value['response_id']); ?>"><?=  $no + 1 ?></a>
                                    <?php } } ?>
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