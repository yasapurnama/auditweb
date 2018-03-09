@extends('layouts.app')

@section('content')
                    <section class="section">
                        <div class="row sameheight-container">
                            <div class="col-md-12">
                                <div class="card card-default">
                                    <div class="card-header">
                                        <div class="header-block">
                                            <p class="title"> Notification List </p><span class="pull-right"></span>
                                        </div>
                                    </div>
                                    <div class="card-block" style="padding: 10px 30px 30px 30px">
                                    
                                    <div class="table-responsive">
                                        <table class="table table-striped table-bordered table-condensed tool-result-table">
                                        <thead>
                                            <tr>
                                            <th>#</th> 
                                            <th>Notification Message</th> 
                                            <th>Time</th> 
                                            <th>Action</th>  
                                            </tr>
                                        </thead>
                                        <tbody>
                                        @foreach ($notifications as $key=>$result)
                                            <tr>
                                            <td>{{ ++$key }}</td>    
                                            <td>{{ $result->notif_message }}</td>          
                                            <td>{{ $result->created_at->diffForHumans() }}</td>    
                                            <td><button type="button" class="btn btn-danger btn-sm btn-confirm-delete" data-record-id="{{ $result->id }}"><em class="fa fa-trash-o"></em> Delete</button></td>    
                                            </tr>
                                        @endforeach
                                        </tbody>
                                        </table>
                                    </div>
                                    {!! $notifications->render() !!}
                                    </div>
                                    <div class="card-footer"> 
                                        <div class="pull-right">
                                            <a class="btn btn-primary" href="{{ route('setting') }}"><i class="fa fa-wrench"></i> Setting</a>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </section>
@endsection

@section('script')
<script type="text/javascript">
$(function() {
  
  $(".btn-confirm-delete").on("click", function(e){
    var id = $(this).data('recordId');
    $('#modal-result-id').val(id);
    $("#modal-delete-form").attr("action", "{{ route('notification.delete') }}");
    $('#modal-message').html('Are you sure want to delete this data?');
    $("#confirm-modal").modal('show');
  });

  $("#modal-btn-yes").on("click", function(e){
    e.preventDefault();
    document.getElementById('modal-delete-form').submit();
  });

});
</script>
@endsection