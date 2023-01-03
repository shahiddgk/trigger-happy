<div class="page-content">

	<nav class="page-breadcrumb">
		<ol class="breadcrumb">
			<li class="breadcrumb-item"><a href="#">Questions</a></li>
			<li class="breadcrumb-item active" aria-current="page"><?= $page_title; ?></li>
		</ol>
	</nav>

	<div class="row">
		<div class="col-lg-6 grid-margin stretch-card">
			<div class="card">
				<div class="card-body">
					<h4 class="card-title">Add Question</h4>
					<form class="cmxform" method="post" action="<?= base_url('admin/insert_question'); ?>">
						<fieldset>
							<div class="form-group">
								<label for="name">Title</label>
								<input id="name" class="form-control" name="q_title" type="text" required>
							</div>
							<div class="form-group">
								<label for="name">Sub Title</label>
								<input id="name" class="form-control" name="sub_title" type="text">
							</div>
							<hr>
							<label for="name">Response Type</label>
							<div class="form-group form-check-inline">
								<div class="form-check">
									<label class="form-check-label">
										<input type="radio" class="form-check-input" name="res_type" value="open_text">
										Open Text?
									</label>
								</div>
								&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
								<div class="form-check">
									<label class="form-check-label">
										<input type="radio" class="form-check-input" name="res_type" value="radio_btn">
										Radio Button?
									</label>
								</div>
								&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
								<div class="form-check">
									<label class="form-check-label">
										<input type="radio" class="form-check-input" name="res_type" value="check_box">
										Check Box?
									</label>
								</div>
								&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
								<div class="form-check">
									<label class="form-check-label">
										<input type="radio" class="form-check-input" name="res_type" value="timer">
										Timer?
									</label>
								</div>
							</div>
							
							<div class="form-group" id="textLength" style="display:none;">
								<label for="name">Text Length</label>
								<input class="form-control" name="text_length" type="number">
							</div>
							<hr>
							<div class="form-group tags_option">
								<label for="name">Options realted to this question</label>
								<input name="q_options" id="tags" value="" />
							</div>
							<input class="btn btn-primary" type="submit" value="Submit">
						</fieldset>
					</form>
				</div>
			</div>
		</div>
	</div>

</div>

<script type="text/javascript">
	$(document).ready(function() {
		$("input[name='res_type']").click(function() {
			if ($(this).val() == "open_text") {
				$("#textLength").show();
			} else {
				$("#textLength").hide();
			}
		});
	});
</script>