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
                <form action="<?= base_url('admin/column_users/' . $status)?>" method="get" class="form-inline">
                        <div class="form-group mx-sm-3 mb-2">
                            <label for="status" class="sr-only">Filter by status</label>
                            <select name="status" id="status" class="form-control">
                                <option value="all">All</option>
                                <option value="yes" <?php if(isset($status) && $status == 'yes') echo 'selected'; ?>>Completed</option>
                                <option value="no" <?php if(isset($status) && $status == 'no') echo 'selected'; ?>>Incomplete</option>
                            </select>
                        </div>
                        <button type="submit" class="btn btn-primary">Filter</button>
                        <div class="form-group mx-sm-3">
                            <a href="<?= base_url('admin/column_users/')?>" class="btn btn-danger">Clear Filter</a>
                        </div>
                    </form>
                    <br>
                    <h6 class="card-title"><?= $page_title ?></h6>
                    <div class="table-responsive">
                    <table id="dataTableExample" class="table">
                        <thead>
                            <tr>
                                <th>Sr.</th>
                                <th>Name</th>
                                <th>Email</th>
                                <th>View list</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            
                            if(isset($users)) {
                                foreach($users as $key => $data){ ?>
                                <tr>
                                    <td><?= $key+1; ?></td>
                                    <td><?= $data['name'] ?></td>
                                    <td><?= $data['email'] ?></td>
                                    <td class="d-flex">
                                        <a href="<?= base_url('admin/column_list/').$data['user_id']; ?>" class="nav-link">
                                            <i data-feather="eye"></i>
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