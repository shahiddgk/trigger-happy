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
                    <div class="col-md-3 float-right m-2 d-inline-flex form-group">
                        <label>Filter By Date</label>
                        <input type="date" class="form-control" name="date_filter" id="date-input">
                    </div>
                    <div class="table-responsive">
                    <table id="dataTableExample" class="table">
                        <thead>
                            <tr>
                                <th>Name</th>
                                <th>Email</th>
                                <th>Date</th>
                            </tr>
                        </thead>
                        <tbody>
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
    $('#date-input').change(function() {
        var selectedDate = $(this).val();
        $.ajax({
        url: '<?php echo base_url("admin/get_users_by_date"); ?>',
        type: 'post',
        data: { date: selectedDate },
        success: function(response) {
            console.log(response);
            var userTable = $('#dataTableExample').DataTable({
            destroy: true,
            data: JSON.parse(response),
            columns: [
                { data: 'name' },
                { data: 'email' },
                { data: 'cr_date' }
            ]
            });
        },
        error: function(xhr, ajaxOptions, thrownError) {
            console.log(xhr.status);
            console.log(thrownError);
        }
        });
    });
});

</script>