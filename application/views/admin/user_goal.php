
<div class="page-content">

<nav class="page-breadcrumb">
    <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="#">Ladder</a></li>
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
                            <th>Physical</th>
                            <th>Goal</th>
                            <th>Date</th>
                            <th>Text</th>
                            <th>Description</th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php foreach ($goal as $goal) { ?>
                        <tr>
                            <td><?php echo $goal['option1']; ?></td>
                            <td><?php echo $goal['option2']; ?></td>
                            <td><?php echo $goal['date']; ?></td>
                            <td><?php echo $goal['text']; ?></td>
                            <td><?php echo $goal['description']; ?></td>
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
