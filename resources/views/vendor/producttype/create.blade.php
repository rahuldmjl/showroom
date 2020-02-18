@extends('layout.mainlayout')

@section('title', 'Product Type')

@section('body_class', 'header-light sidebar-dark sidebar-expandheader-light sidebar-dark sidebar-expand')

@section('content')

<main class="main-wrapper clearfix">
    <!-- Page Title Area -->
    <div class="row page-title clearfix">
        {{ Breadcrumbs::render('vendor-product-type.create') }}
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
                        <h5 class="box-title mr-b-0">Insert Product 	</h5>
                        <p class="text-muted">You can add Product Type by filling this form</p>
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
                        {!! Form::open(array('route' => 'vendor-product-type.store','method'=>'POST', 'id'=>'product_type_form')) !!}
                         <div class="row">
                                <div class="col-lg-12">
                                    <div class="form-group">
                                        <label for="l30">Product Name</label>
                                        <!-- <input class="form-control" id="l30" placeholder="Email Address" type="text"> -->
                                        {!! Form::text('name', null, array('placeholder' => 'Product Name','class' => 'form-control' ,'id'=>'name')) !!}
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
       $("#product_type_form").validate({
       ignore: ":hidden",
        rules: {
            name: {
                required:true,
                lettersonly:true
            }
        },
        messages: {
               required: "Please Enter Product Type",
               lettersonly:"Allowed only string!"
        }

      });
});

</script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery-validate/1.19.1/jquery.validate.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery-validate/1.19.1/additional-methods.min.js"></script>
@endsection