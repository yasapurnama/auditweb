@extends('layouts.app')

@section('content')
                    <section class="section">
                        <div class="row sameheight-container">
                            <div class="col-md-12">
                                <div class="card card-default">
                                    <div class="card-header">
                                        <div class="header-block">
                                            <p class="title"> Users </p><span class="pull-right"></span>
                                        </div>
                                    </div>
                                    <div class="card-block" style="padding: 10px 30px 30px 30px">
                                    @if (session('status'))
                                        <div class="alert alert-success">
                                            {{ session('status') }}
                                        </div>
                                    @elseif(session('error'))
                                        <div class="alert alert-danger">
                                            {{ session('error') }}
                                        </div>
                                    @endif
                                    <div class="table-responsive">
                                        <table class="table table-striped table-bordered table-condensed tool-result-table">
                                        <thead>
                                            <tr>
                                            <th>#</th> 
                                            <th>Name</th> 
                                            <th>Email</th> 
                                            <th>Role</th> 
                                            <th>Status</th> 
                                            <th>Date Registered</th> 
                                            <th>Action</th>  
                                            </tr>
                                        </thead>
                                        <tbody>
                                        @foreach ($users as $key=>$result)
                                            <tr>
                                            <td>{{ ++$key }}</td>    
                                            <td>{{ $result->name }}</td>      
                                            <td>{{ $result->email }}</td>    
                                            <td>{{ $result->role == 2 ? "Admin" : "User" }}</td>    
                                            <td>{{ $result->status == 1 ? "Active" : "Disabled" }}</td>    
                                            <td>{{ $result->created_at->format('d/m/Y H:i') }}</td>    
                                            <td><a href="{{ route('manage.userview', $result) }}" class="btn btn-info btn-sm"><em class="fa fa-eye"></em> View</a> <button type="button" class="btn btn-danger btn-sm btn-confirm-delete" data-record-id="{{ $result->id }}"><em class="fa fa-ban"></em> Disable</button></td>    
                                            </tr>
                                        @endforeach
                                        </tbody>
                                        </table>
                                    </div>
                                    {!! $users->render() !!}
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
    $("#modal-delete-form").attr("action", "{{ route('manage.userban') }}");
    $('#modal-message').html('Are you sure want to disable this user?');
    $("#confirm-modal").modal('show');
  });

  $("#modal-btn-yes").on("click", function(e){
    e.preventDefault();
    document.getElementById('modal-delete-form').submit();
  });

});
</script>
@endsection