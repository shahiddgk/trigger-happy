
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
                                <th>Mentor</th>
                                <th>Mentor Desc</th>
                                <th>Peer</th>
                                <th>Peer Desc</th>
                                <th>Mentee</th>
                                <th>Mentee Desc</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach($tribe as $data) { ?>
                                <tr>
                                    <td><?=$data['mentor']?></td>
                                    <td><?=$data['mentor_desc']?></td>
                                    <td><?=$data['peer']?></td>
                                    <td><?=$data['peer_desc']?></td>
                                    <td><?=$data['mentee']?></td>
                                    <td><?=$data['mentee_desc']?></td>
                                
                                </tr>
                            <?php } ?>
                        </tbody>
                    </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
