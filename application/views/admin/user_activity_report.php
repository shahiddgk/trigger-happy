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
                        <label class="p-2">Date</label>
                        <input type="date" class="form-control pl-2" name="date_filter" id="date-input">
                    </div>
                    <div class="table-responsive">
                        <table id="dataTableExample" class="table">
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
                                <?php foreach ($users as $user): ?>
                                    <tr>
                                        <td><?= $user->name ?></td>
                                        <td><?= (new DateTime($user->created_at))->format('Y-m-d') ?></td>
                                        <td><?= ($user->min_naq_response ? '(' . date('d-m-Y', strtotime($user->min_naq_response)) . ' )' . $user->min_naq_score : '') ?></td>
<td><?= ($user->max_naq_response ? '(' . date('d-m-Y', strtotime($user->max_naq_response)) . ' )' . $user->max_naq_score : '') ?></td>

                                        <td><?= $user->garden_level ?></td>
                                        <td><?= $user->max_naq_score - $user->min_naq_score ?></td>
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
</div>
