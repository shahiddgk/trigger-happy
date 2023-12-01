<style>
  .select2-selection {
    width: 189px !important;
  }
</style>

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
                    <form action="<?= base_url('admin/post_report') ?>" method="get" class="form-inline">
                        <div class="form-group mx-sm-3 mb-2">
                            <label for="name" class="sr-only">Filter by Name</label>
                            <select name="name" id="name" class="form-control"  style="width: 200% !important;">
                                <option value="">Select Name</option>
                                <?php foreach ($userList as $id => $name): ?>
                                    <option value="<?= $id ?>" <?= ($id == $selectedName) ? 'selected' : '' ?>>
                                        <?= $name; ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div class="form-group mx-sm-3">
                            <label for="date" class="sr-only">Filter by Date</label>
                            <input name="date" value="<?= $date ?>" id="date"
                                type="date" class="form-control">
                        </div>

                        <button type="submit" class="btn btn-primary">Filter</button>
                        <div class="form-group mx-sm-3">
                            <a href="<?= base_url('admin/post_report') ?>" class="btn btn-danger">Clear Filter</a>
                        </div>
                    </form>
                    <div class="table-responsive">
                        <table id="dataTable" class="table">
                            <thead>
                                <tr>
                                    <th>Sr.</th>
                                    <th>Text</th>
                                    <th>Date and Time</th>
                                    <th>Answers</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($reminders as $key => $row): ?>
                                    <tr>
                                        <td><?= $key+1 ?></td>
                                        <td><?= $row['text']; ?></td>
                                        <td><?= $row['date_time']; ?></td>
                                        <td><?= $row['reminder_stop'] === 'skip' ? '....' : $row['reminder_stop']; ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
        $(document).ready(function() {
            var dataTable = $('#dataTable').DataTable();

            $("#name").select2({
                width: "200px",
            });
        });
</script>        