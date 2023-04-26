
<div class="page-content">

<nav class="page-breadcrumb">
    <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="#">Principle</a></li>
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
                            <th>Empowered truths</th>
                            <th>powerless believes</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($rhythms as $rhythm) { ?>
                            <tr>
                                <td><?php echo $rhythm['emp_truths']; ?></td>
                                <td><?php echo $rhythm['powerless_believes']; ?></td>
                            </tr>
                        <?php } ?>                       
                    </tbody>
                </table>
                </div>
            </div>
        </div>
    </div>
   </div>
</div>
