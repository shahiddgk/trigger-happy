<style>
    .cstm-td{
        display: contents;
    }
    td.cstm-td a{
        margin: 4px;
    }
    .cstm-title{
        overflow-wrap: break-word;
        word-wrap: break-word;
        hyphens: auto;
        white-space: normal!important; 
    }
</style>

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
                                <th>Answer Type</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if(isset($questions)){
                                foreach($questions as $key => $data){
                                    $splits = explode(",", json_decode($data['options'])); ?>
                            <tr>
                                <td><?= $key+1; ?></td>
                                <td width="45%" class="cstm-title"><?= $data['title'] ?></td>
                                <?php if(!empty($data['options'])){ ?>
                                    <td class="cstm-td">
                                        <?php  foreach($splits as $split){ ?>
                                            <a href="#" class="badge badge-primary"><?= $split ?></a>
                                        <?php }  ?>
                                    </td>
                                <?php } else{ ?>
                                    <td></td>
                               <?php } ?>
                                <td><?= ucfirst($data['response_type']); ?></td>
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