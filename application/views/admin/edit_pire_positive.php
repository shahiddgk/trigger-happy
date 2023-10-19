<div class="page-content">

	<nav class="page-breadcrumb">
		<ol class="breadcrumb">
			<li class="breadcrumb-item"><a href="<?= base_url('admin/pire_pos_list')?>">Questions</a></li>
			<li class="breadcrumb-item active" aria-current="page"><?= $page_title; ?></li>
		</ol>
	</nav>

	<div class="row">
		<div class="col-lg-8 grid-margin stretch-card">
			<div class="card">
				<div class="card-body">
					<h4 class="card-title font-weight-bold">Add Question</h4>
					<form class="cmxform" method="post" action="<?= base_url('admin/update_pire_positive/').$question['id']; ?>">
						<fieldset>
							<div class="form-group">
								<label for="question_for" class="font-weight-bold">This question is for</label>
								<select class="form-control" name="question_for" id="question_for">
									<option value="pire" <?php echo ($question['type'] == 'pire') ? 'selected' : ''; ?>>Pire</option>
									<option value="naq" <?php echo ($question['type'] == 'naq') ? 'selected' : ''; ?>>NAQ</option>
									<option value="pire_pos" <?php echo ($question['type'] == 'pire_pos') ? 'selected' : ''; ?>>Pire Positive</option>
								</select>
							</div>
							<div class="form-group">
								<h4 class="card-title font-weight-bold">Title</h4>
								<textarea class="form-control" name="q_title" id="editor1" rows="2" required><?= $question['title'] ?></textarea>
							</div>
							<hr>
							<div class="form-group">
								<h4 class="card-title font-weight-bold">Video</h4>
								<textarea class="form-control" name="video_url" id="editor2" rows="10"><?= $question['video_url'] ?></textarea>
							</div>
							<label class="font-weight-bold">Response Type</label><br>
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
								&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
								<div class="form-check">
									<label class="form-check-label">
										<input type="radio" class="form-check-input" name="res_type" value="timer" <?php echo ($question['response_type']== 'timer' ? 'checked' : '');?>>
										Timer?
									</label>
								</div>
							</div>
							<div class="form-group" id="textLength" style="display:none;">
								<label for="name">Text Length</label>
								<input class="form-control" value="<?= $question['text_length']?>" name="text_length" type="number">
							</div>
							<hr>
							<div class="form-group tags_option">
								<label for="name" class="font-weight-bold">Options realted to this question</label>
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


<script type="text/javascript">
	$("input[name='res_type']").click(function() {
		if ($(this).val() == "open_text") {
			$("#textLength").show();
		} else {
			$("#textLength").hide();
		}
	});

	if ($("input[name='res_type']:checked").val() == "open_text") {
		$("#textLength").show();
	}
</script>