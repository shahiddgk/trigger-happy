
<div class="page-content">

    <nav class="page-breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="#">Trellis</a></li>
            <li class="breadcrumb-item active" aria-current="page"><?= $page_title ?></li>
        </ol>
    </nav>
    <div class="row">
    <div class="col-md-12 grid-margin stretch-card">
        <div class="card">
            <div class="card-body">
                <div class="row align-items-center d-flex justify-content-between" >
                <h6 class="card-title"><?= $page_title ?></h6>
                <!-- Add filter -->
                <div class="form-group">
                    <label for="filter_reason">Filter by Cancel Reason:</label>
                    <select name="filter_reason" id="filter_reason" class="form-control mb-3">
                        <option value="">All</option>
                        <option value="Poor User Experience">Poor User Experience</option>
                        <option value="Customer Support Issues">Customer Support Issues</option>
                        <option value="Limited Features">Limited Features</option>
                        <option value="Lack of Trust">Lack of Trust</option>
                        <option value="Technical Issues">Technical Issues</option>
                        <option value="Other">Other</option>
                    </select>
                    <button type="button" id="remove_filter" class="btn btn-secondary">Remove Filter</button>
                </div>
                </div>
                <!-- End filter -->
                <div class="table-responsive">
                    <table id="dataTableExample" class="table">
                        <thead>
                            <tr>
                                <th>SR.</th>
                                <th>Name</th>
                                <th>Email</th>
                                <th>Plan</th>
                                <th>Cancel Date</th>
                                <th>Cancel Reason</th>
                            </tr>
                        </thead>
                        <tbody id="reasons_table">
                            <?php if (empty($reasons)) { ?>
                                <tr>
                                    <td colspan="6" class="text-center">No data matched the filter</td>
                                </tr>
                            <?php } else { ?>
                                <?php foreach($reasons as $i => $reason) { ?>
                                    <tr>
                                        <td><?= $i+1 ?></td>
                                        <td><?=$reason['user_name']?></td>
                                        <td><?=$reason['user_email']?></td>
                                        <td><?=ucfirst($reason['plan_type'])?></td>
                                        <td><?=date('d-m-Y', strtotime($reason['cancel_date']))?></td>
                                        <td class="cancel-reason"><?=$reason['cancel_reason']?></td>
                                    </tr>
                                <?php } ?>
                            <?php } ?>
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
    $('#filter_reason').change(function() {
        var filterValue = $(this).val();
        $.ajax({
            url: '<?= base_url('admin/sub_cancel_reason') ?>',
            method: 'GET',
            data: { filter_reason: filterValue },
            success: function(data) {
                var reasons = JSON.parse(data);
                var tbody = $('#reasons_table');
                tbody.empty();
                if (reasons.length === 0) {
                    tbody.append('<tr><td colspan="6" class="text-center">No data matched the filter</td></tr>');
                } else {
                    $.each(reasons, function(i, reason) {
                        tbody.append('<tr>' +
                            '<td>' + (i+1) + '</td>' +
                            '<td>' + reason.user_name + '</td>' +
                            '<td>' + reason.user_email + '</td>' +
                            '<td>' + reason.plan_type.charAt(0).toUpperCase() + reason.plan_type.slice(1) + '</td>' +
                            '<td>' + new Date(reason.cancel_date).toLocaleDateString('en-GB') + '</td>' +
                            '<td class="cancel-reason">' + reason.cancel_reason + '</td>' +
                            '</tr>');
                    });
                }
            }
        });
    });

    $('#remove_filter').click(function() {
        $('#filter_reason').val('');
        $('#filter_reason').trigger('change');
    });
});
</script>
<style>
    .cancel-reason {
        white-space: normal !important;
        word-wrap: break-word !important;
    }
</style>
