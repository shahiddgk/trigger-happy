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
					<form class="cmxform" method="post" action="<?= base_url('admin/update_question/').$question['id']; ?>">
						<fieldset>
							<div class="form-group">
								<label for="name">Question Title</label>
								<input id="name" class="form-control" name="q_title" type="text" value="<?= $question['title'] ?>">
							</div>
							<hr>
							<div class="form-group form-check-inline">
								<div class="form-check">
									<label class="form-check-label">
										<input type="radio" class="form-check-input" name="res_type" value="open_text" <?php echo ($question['response_type']== 'open_text' ? 'checked' : '');?> >
										Open Text?
									</label>
								</div>
								&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
								<div class="form-check">
									<label class="form-check-label">
										<input type="radio" class="form-check-input" name="res_type" value="radio_btn" <?php echo ($question['response_type']== 'radio_btn' ? 'checked' : '');?>>
										Radio Button?
									</label>
								</div>
								&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
								<div class="form-check">
									<label class="form-check-label">
										<input type="radio" class="form-check-input" name="res_type" value="check_box" <?php echo ($question['response_type']== 'check_box' ? 'checked' : '');?>>
										Check Box?
									</label>
								</div>
							</div>
							<hr>
							<div class="form-group tags_option">
								<label for="name">Options realted to this question</label>
								<input name="q_options" id="tags" value="<?= json_decode($question['options'])?>" />
							</div>
							<input class="btn btn-primary" type="submit" value="Submit">
						</fieldset>
					</form>
				</div>
			</div>
		</div>
	</div>

</div>

<!-- <script>
$(function () {
	$(".chk_open_text").click(function() {
		if($(this).is(":checked")) {
			$(".tags_option").hide(200);
		} else {
			$(".tags_option").show(300);
		}
	});
});
</script> -->