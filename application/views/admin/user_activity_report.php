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
                        <button id="exportCsvBtn" class="btn btn-primary excel_btn">Export to Csv</button>
                    </div>
                    <div class="table-responsive">
                        <table id="dataTable" class="table">
                            <thead>
                                <tr>
                                    <th>User</th>
                                    <th>Date</th>
                                    <th>Initial Naq/date</th>
                                    <th>Last Naq/date</th>
                                    <th>Level</th>
                                    <th>Delta</th>
                                    <th>Trailing 90 Total</th>
                                    <th>Pire T-90</th>
                                    <th>Trellis T-90</th>
                                    <th>Column T-90</th>
                                    <th>Ladder T-90</th>
                                    <th>Post T-90</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php $i = 1; ?>
                                <?php foreach ($users as $user): ?>
                                    <tr>
                                        <?php $i = 1; ?>
                                        <?php if ($show_alias_name === 'no'): ?>
                                        <td><?= $user->name ?></td>
                                        <?php else: ?>
                                        <td>User <?= $i++ ?></td>
                                        <?php endif; ?>
                                        <td><?= (new DateTime($user->created_at))->format('Y-m-d') ?></td>
                                        <td><?= ($user->naq_score->min_naq_response ? '(' . date('d-m-Y', strtotime($user->naq_score->min_naq_response)) . ' )' . $user->naq_score->min_naq_score : '') ?></td>
                                        <td><?= ($user->naq_score->max_naq_response ? '(' . date('d-m-Y', strtotime($user->naq_score->max_naq_response)) . ' )' . $user->naq_score->max_naq_score : '') ?></td>
                                        <td><?= $user->level ?></td>
                                        <td><?= $user->naq_score->delta ?></td>
                                        <td><?= $user->total_count ?></td>
                                        <td><?= $user->count_pire ?></td>
                                        <td><?= $user->count_trellis ?></td>
                                        <td><?= $user->count_column ?></td>
                                        <td><?= $user->count_ladder  ?></td>
                                        <td><?= "{$user->additional_data->sum_yes_reminders} ({$user->additional_data->sum_active_reminders}/{$user->additional_data->sum_reminders})" ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <script>
        $(document).ready(function() {
            var dataTable = $('#dataTable').DataTable();

            $('#exportCsvBtn').on('click', function() {
                var data = dataTable.data().toArray();
                var headers = dataTable.columns().header().toArray().map(header => $(header).text());
                var csvContent = "data:text/csv;charset=utf-8,";

                csvContent += '"' + headers.join('","') + '"\n';

                csvContent += data.map(row => row.map(cell => `"${cell}"`).join(",")).join("\n");

                var encodedUri = encodeURI(csvContent);
                var link = document.createElement("a");
                link.setAttribute("href", encodedUri);
                link.setAttribute("download", "User_activity.csv");
                document.body.appendChild(link);
                link.click();
            });
        });
    </script>
</div>
