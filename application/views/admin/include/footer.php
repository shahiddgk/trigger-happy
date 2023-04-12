        <!-- partial:partials/_footer.html -->
                <footer class="footer d-flex flex-column flex-md-row align-items-center justify-content-between">
                    <p class="text-muted text-center text-md-left">Copyright © 2023 <a href="<?= base_url()?>" target="_blank">Burgeon</a>. All rights reserved</p>
                    <!-- <p class="text-muted text-center text-md-left mb-0 d-none d-md-block">Handcrafted With <i class="mb-1 text-primary ml-1 icon-small" data-feather="heart"></i></p> -->
                </footer>
                <!-- partial -->
                
            </div>
        </div>

        <!-- core:js -->
            <script src="<?= base_url()?>assets/admin/vendors/core/core.js"></script>
            <script src="<?= base_url()?>assets/admin/vendors/chartjs/Chart.min.js"></script>
            <!-- endinject -->
            <!-- plugin js for this page -->
            <script src="<?= base_url()?>assets/admin/vendors/jquery-validation/jquery.validate.min.js"></script>
            <script src="<?= base_url()?>assets/admin/vendors/bootstrap-maxlength/bootstrap-maxlength.min.js"></script>
            <script src="<?= base_url()?>assets/admin/vendors/inputmask/jquery.inputmask.min.js"></script>
            <script src="<?= base_url()?>assets/admin/vendors/select2/select2.min.js"></script>
            <script src="<?= base_url()?>assets/admin/vendors/typeahead.js/typeahead.bundle.min.js"></script>
            <script src="<?= base_url()?>assets/admin/vendors/jquery-tags-input/jquery.tagsinput.min.js"></script>
            <script src="<?= base_url()?>assets/admin/vendors/dropzone/dropzone.min.js"></script>
            <script src="<?= base_url()?>assets/admin/vendors/dropify/dist/dropify.min.js"></script>
            <script src="<?= base_url()?>assets/admin/vendors/bootstrap-colorpicker/bootstrap-colorpicker.min.js"></script>
            <script src="<?= base_url()?>assets/admin/vendors/bootstrap-datepicker/bootstrap-datepicker.min.js"></script>
            <script src="<?= base_url()?>assets/admin/vendors/moment/moment.min.js"></script>
            <script src="<?= base_url()?>assets/admin/vendors/tempusdominus-bootstrap-4/tempusdominus-bootstrap-4.js"></script>
            <!-- end plugin js for this page -->

            <!-- CKeditor simplemde-->
            <script src="https://cdn.ckeditor.com/ckeditor5/35.4.0/classic/ckeditor.js"></script>
            <script>
                ClassicEditor
                .create( document.querySelector( '#editor1' ) )
                .then( editor => {
                        // console.log( editor );
                } )
                .catch( error => {
                        // console.error( error );
                } );
            </script>
            <!-- CKeditor simplemde-->
            <!-- DataTAble -->
            <script src="<?= base_url()?>assets/admin/vendors/datatables.net/jquery.dataTables.js"></script>
            <script src="<?= base_url()?>assets/admin/vendors/datatables.net-bs4/dataTables.bootstrap4.js"></script>
            <script src="<?= base_url()?>assets/admin/js/data-table.js"></script>
            <!-- inject:js -->
            <script src="<?= base_url()?>assets/admin/vendors/feather-icons/feather.min.js"></script>
            <script src="<?= base_url()?>assets/admin/js/template.js"></script>
            <!-- endinject -->
            <!-- custom js for this page -->
            <script src="<?= base_url()?>assets/admin/js/form-validation.js"></script>
            <script src="<?= base_url()?>assets/admin/js/bootstrap-maxlength.js"></script>
            <script src="<?= base_url()?>assets/admin/js/inputmask.js"></script>
            <script src="<?= base_url()?>assets/admin/js/select2.js"></script>
            <script src="<?= base_url()?>assets/admin/js/typeahead.js"></script>
            <script src="<?= base_url()?>assets/admin/js/tags-input.js"></script>
            <script src="<?= base_url()?>assets/admin/js/dropzone.js"></script>
            <script src="<?= base_url()?>assets/admin/js/dropify.js"></script>
            <script src="<?= base_url()?>assets/admin/js/bootstrap-colorpicker.js"></script>
            <script src="<?= base_url()?>assets/admin/js/datepicker.js"></script>
            <script src="<?= base_url()?>assets/admin/js/timepicker.js"></script>
	    <!-- end custom js for this page -->
    </body>
</html>    