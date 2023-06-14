
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
                                <th>Peer</th>
                                <th>Mentee</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                             foreach($tribe as $data) { ?>
                                <tr>
                                    <td><?=$data['mentor']?></td>
                                    <td><?=$data['peer']?></td>
                                    <td><?=$data['mentee']?></td>
                                </tr>
                            <?php } ?>
                        </tbody>
                    </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
