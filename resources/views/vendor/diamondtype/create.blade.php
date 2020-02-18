@extends('layout.mainlayout')

@section('title', 'Diamond Type')

@section('body_class', 'header-light sidebar-dark sidebar-expandheader-light sidebar-dark sidebar-expand')

@section('content')

<main class="main-wrapper clearfix">
    <!-- Page Title Area -->
    <div class="row page-title clearfix">
        {{ Breadcrumbs::render('vendor-diamond-type.create') }}
        <!-- /.page-title-right -->
    </div>
    <!-- /.page-title -->
    <!-- =================================== -->
    <!-- Different data widgets ============ -->
    <!-- =================================== -->
    <div class="widget-list">
        <div class="row">
            <div class="col-md-12 widget-holder">
                <div class="widget-bg">
                    <div class="widget-body clearfix">
                        <h5 class="box-title mr-b-0">Insert Diamond 	</h5>
                        <p class="text-muted">You can add Diamond Type by filling this form</p>
                         @if (count($errors) > 0)
                          <div class="alert alert-danger">
                            <strong>Whoops!</strong> There were some problems with your input.<br><br>
                            <ul>
                               @foreach ($errors->all() as $error)
                                 <li>{{ $error }}</li>
                               @endforeach
                            </ul>
                          </div>
                        @endif
                        {!! Form::open(array('route' => 'vendor-diamond-type.store','method'=>'POST','id'=>'diamond_type_form')) !!}
                         <div class="row">
                                <div class="col-lg-12">
                                    <div class="form-group">
                                        <label for="l30">Diamond Name</label>
                                        <!-- <input class="form-control" id="l30" placeholder="Email Address" type="text"> -->
                                        {!! Form::text('name', null, array('placeholder' => 'Diamond Name','class' => 'form-control','id'=>'name')) !!}
                                    </div>
                                </div>
                                 <div class="form-actions btn-list">
                                <button class="btn btn-primary" type="submit">Submit</button>
                                <button class="btn btn-outline-default" type="reset">Cancel</button>
                            </div>
                        {!! Form::close() !!}
                         </div>
                    <!-- /.widget-body -->
                </div>
                <!-- /.widget-bg -->
            </div>
            <!-- /.widget-holder -->
        </div>
        <!-- /.row -->
    </div>
    <!-- /.widget-list -->
</main>
@endsection
@section('distinct_footer_script')

<script type="text/javascript">
   $.ajaxSetup({

        headers: {

            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')

        }

    });
    $(document).ready( function() {
       $("#diamond_type_form").validate({
       ignore: ":hidden",
        rules: {
            name: {
                required:true,
            },
        },
        messages: {
               name: "Please Enter Diamond Type!",
              
        }

      });
});

</script>
<style>
    #commentForm {
        width: 500px;
    }
    #commentForm label {
        width: 250px;
    }
    #commentForm label.error, #commentForm input.submit {
        margin-left: 253px;
    }
    #diamond_type_form {
        width: 670px;
    }
    #diamond_type_form label.error {
        margin-left: 10px;
        width: auto;
        display: table-row-group;
    }
    #diamond_type_form label.error {
        display: table-row-group;
        margin-left: 103px;
        color:  #e34747;

    }
    </style>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery-validate/1.19.1/jquery.validate.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery-validate/1.19.1/additional-methods.min.js"></script>
@endsection