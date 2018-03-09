@extends('layouts.app')

@section('content')
                    <section class="section">
                        <div class="row sameheight-container">
                            <div class="col-md-12">
                                <div class="card card-default">
                                    <div class="card-header">
                                        <div class="header-block">
                                            <p class="title"> Audit History </p><span class="pull-right"></span>
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
                                            <th>Domain</th> 
                                            <th>Owner</th> 
                                            <th>Email</th> 
                                            <th>Date</th> 
                                            <th>Action</th>  
                                            </tr>
                                        </thead>
                                        <tbody>
                                        @foreach ($auditResults as $key=>$result)
                                            <tr>
                                            <td>{{ ++$key }}</td>    
                                            <td>{{ $result->web_domain }}</td>      
                                            <td>{{ $result->whois_domain_owner }}</td>    
                                            <td>{{ $result->whois_domain_email }}</td>    
                                            <td>{{ $result->created_at->format('d/m/Y H:i') }}</td>    
                                            <td><a href="{{ route('result', $result) }}" class="btn btn-info btn-sm"><em class="fa fa-eye"></em> View</a> <button type="button" class="btn btn-danger btn-sm btn-confirm-delete" data-record-id="{{ $result->id }}"><em class="fa fa-trash-o"></em> Delete</button></td>    
                                            </tr>
                                        @endforeach
                                        </tbody>
                                        </table>
                                    </div>
                                    {!! $auditResults->render() !!}
                                    </div>
                                    <div class="card-footer"> 
                                        <div class="pull-right">
                                            <a class="btn btn-primary" href="{{ route('scan') }}">New Scan <i class="fa fa-play-circle"></i></a>
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
    $("#modal-delete-form").attr("action", "{{ route('result.delete') }}");
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