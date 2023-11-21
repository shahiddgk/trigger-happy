<div class="page-content">

	<nav class="page-breadcrumb">
		<ol class="breadcrumb">
			<li class="breadcrumb-item"><a href="<?= base_url('admin/column_list/' . $add_column->user_id)?>">column list</a></li>
			<li class="breadcrumb-item active" aria-current="page"><?= $page_title; ?></li>
		</ol>
	</nav>

	<div class="row">
		<div class="col-lg-8 grid-margin stretch-card">
			<div class="card">
                <div class="card-body">
                    <h4 class="card-title font-weight-bold">Add Column</h4>
                    <form class="flds" method="post" action="<?= base_url('admin/add_column_action/') . $add_column->user_id; ?>">
                        <fieldset>

                            <div class="form-group">
                                <label for="Type" class="font-weight-bold">Type</label>
                                <select class="form-control" name="entry_type" id="entry_type">
                                    <option value="entry">Entry</option>
                                    <option value="session">Session</option>
                                    <option value="meeting">Meeting</option>
                                </select>
                            </div>

                            <div class="form-group">
                                <h4 class="card-title font-weight-bold">Entry Title</h4>
                                <textarea class="form-control" name="entry_title" id="entry_title" rows="5" required></textarea>
                            </div>

                            <div class="form-group">
                                <h4 class="card-title font-weight-bold">Entry Description</h4>
                                <textarea class="form-control" name="entry_decs" id="entry_decs" rows="5" required></textarea>
                            </div>

                            <div class="form-group">
                                <h4 class="card-title font-weight-bold">Entry Takeaway</h4>
                                <textarea class="form-control" name="entry_takeaway" id="entry_takeaway" rows="5" required></textarea>
                            </div>

                            <div class="form-group">
                                <h4 class="card-title font-weight-bold">Entry Date</h4>
                                <input type="date" class="form-control" name="entry_date" id="entry_date" required>
                            </div>

                            <input class="btn btn-primary" type="submit" value="Submit">
                        </fieldset>
                    </form>
                </div>
			</div>
		</div>
	</div>
</div>
