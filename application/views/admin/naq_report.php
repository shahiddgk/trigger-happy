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
                    <div class="col-md-2 float-right m-2 d-inline-flex form-group">
                        <?php
                        // Get the current query parameters
                        $currentParams = $_GET;

                        // Extract selectedStartDate and selectedEndDate from the current parameters
                        $selectedStartDate = isset($currentParams['selectedStartDate']) ? $currentParams['selectedStartDate'] : '';
                        $selectedEndDate = isset($currentParams['selectedEndDate']) ? $currentParams['selectedEndDate'] : '';

                        // Generate the export CSV URL with the parameters
                        $exportCsvUrl = base_url('admin/export_csv') . '?selectedStartDate=' . urlencode($selectedStartDate) . '&selectedEndDate=' . urlencode($selectedEndDate);
                    ?>
                        <a href="<?= $exportCsvUrl ?>" class="btn btn-primary">Export CSV</a>
                    </div>
                    <form action="<?= base_url('admin/naq_report') ?>" method="get" class="form-inline">
                        <div class="form-group mx-sm-3">
                            <label for="selectedStartDate" class="sr-only">Filter start date</label>
                            <input name="selectedStartDate" value="<?= $selected_start_date ?>" id="selectedStartDate"
                                type="date" class="form-control">
                        </div>
                        <div class="form-group mx-sm-3">
                            <label for="selectedEndDate" class="sr-only">Filter end date</label>
                            <input name="selectedEndDate" value="<?= $selected_end_date ?>" id="selectedEndDate"
                                type="date" class="form-control">
                        </div>
                        <button type="submit" class="btn btn-primary">Filter</button>
                        <div class="form-group mx-sm-3">
                            <a href="<?= base_url('admin/naq_report') ?>" class="btn btn-danger">Clear Filter</a>
                        </div>
                    </form>
                    <div class="table-responsive">
                        <table id="dataTable" class="table">
                            <thead>
                                <tr>
                                    <th>Name</th>
                                    <th>NAQ Date</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($naq_report as $naq): ?>
                                <tr>
                                    <td><?= $naq['name'] ?></td>
                                    <td><?= $naq['naq_date'] ?></td>
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
        });
</script>        