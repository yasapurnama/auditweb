                <!-- /.modal -->
                <div class="modal fade" id="confirm-modal">
                    <div class="modal-dialog" role="document">
                        <div class="modal-content">
                            <div class="modal-header">
                                <h4 class="modal-title">
                                    <i class="fa fa-warning"></i> Alert</h4>
                                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                    <span aria-hidden="true">&times;</span>
                                </button>
                            </div>
                            <div class="modal-body">
                                <p id="modal-message">Are you sure want to do this?</p>
                            </div>
                            <div class="modal-footer">
                                <button type="button" id="modal-btn-yes" class="btn btn-primary" data-dismiss="modal">Yes</button>
                                <button type="button" id="modal-btn-no" class="btn btn-secondary" data-dismiss="modal">No</button>
                                <form id="modal-delete-form" method="POST" style="display: none;">
                                    {{ csrf_field() }}
                                    <input type="hidden" name="data_id" id="modal-result-id">
                                </form>
                            </div>
                        </div>
                        <!-- /.modal-content -->
                    </div>
                    <!-- /.modal-dialog -->
                </div>
                <!-- /.modal -->