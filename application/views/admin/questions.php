<div class="page-content">

    <nav class="page-breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="#">Questions</a></li>
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
                                <th>Question Title</th>
                                <th>Options</th>
                                <th>Response Type</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if(isset($questions)){
                                foreach($questions as $key => $data){
                                    $splits = explode(",", json_decode($data['options'])); ?>
                            <tr>
                                <td><?= $key+1; ?></td>
                                <td width="45%" class="cstm-title"><?= $data['title'] .'<br>'. $data['sub_title']  ?></td>
                                <?php if($data['options'] != 'NULL'){ ?>
                                    <td class="cstm-options">
                                        <?php  foreach($splits as $split){ ?>
                                            <a href="#" class="badge badge-primary"><?= $split ?></a>
                                        <?php }  ?>
                                    </td>
                                <?php } else{ ?>
                                    <td></td>
                               <?php } ?>
                                <td><?= ucwords(str_replace("_", " ", $data['response_type'])); ?></td>
                                <td class="d-flex">
                                    <a href="<?= base_url('admin/edit_question/').$data['id']; ?>" class="nav-link">
                                        <i data-feather="edit"></i>
                                    </a>
                                    <a href="<?= base_url('admin/delete_question/').$data['id']; ?>" class="nav-link">
                                        <i data-feather="trash-2"></i>
                                    </a>
                                </td>
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