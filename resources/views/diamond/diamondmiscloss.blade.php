@extends('layout.mainlayout')

@section('title', 'Diamond Misc Loss')

@section('distinct_head')
@endsection
@section('body_class', 'header-light sidebar-dark sidebar-expandheader-light sidebar-dark sidebar-expand')
@section('content')

<main class="main-wrapper clearfix">
    <div class="row page-title clearfix">
        {{ Breadcrumbs::render('diamond.diamondissue') }}
    </div>
    <div class="widget-list">
        <div class="row">
            <div class="col-md-12 widget-holder">
                <div class="widget-bg">
                    <div class="widget-body clearfix">
                        <h5 class="box-title mr-b-0">Update weight</h5>
                        <p class="text-muted">You can add loss by filling this form</p>

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
                        @if ($message = Session::get('error'))
    	                    <div class="alert alert-danger">
    	                       <strong>Error</strong>: {{ $message }}
    	                    </div>
                        @endif
    		            @if (Session::has('success'))
    			            <div class="alert alert-icon alert-success border-success alert-dismissible fade show" role="alert">
    			                 <button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">×</span></button>
    			                 <i class="material-icons list-icon">check_circle</i>
    			                 <strong>Success</strong> :
    			                {!!Session::get('success')!!}.<br><br>
    			            </div>
    		        	@endif

                       
                        <label> <b>Total Weight: </b><p class="weightPrev">{{$weight}}</p></label><br/>

                        {!! Form::open(array('route' => 'diamond.diamondmisclossstore','method'=>'POST', 'files'=>'true','id' => 'myform')) !!}

                        <div class="dynamicadd" id="dynamicadd">                        
                            <div class="row">
                                <div class="col-lg-6 col-md-6 col-sm-12">
                                    <div class="form-group">
                                        <label for="l30">Remaining Weight</label>
                                         {!! Form::text('remaining_weight',null, array('required' => 'required','data-index' => '0', 'autocomplete' => 'off', 'class' => 'required form-control position-relative remaining_weight autocomplete_shape_txt','id'=>'remaining_weight','onblur'=> 'getMiscLossfun()','value' => "")) !!}         
                                    </div>
                                </div>

                                <input type="hidden" name="max_misc_limit" value="{{$max_misc_limit}}">
                                <input type="hidden" name="min_misc_limit" value="{{$min_misc_limit}}">
                                <div class="col-lg-6 col-md-6 col-sm-12">
                                    <div class="form-group">
                                        <label for="l30">Misc Loss</label>
                                         {!! Form::number('misc_loss', null, array('required' => 'required','data-index' => '0', 'autocomplete' => 'off', 'class' => 'required form-control position-relative misc_loss autocomplete_shape_txt','id'=>'smisc_loss','readonly' => "readonly")) !!}         
                                    </div>
                                </div>


                                <div class="col-lg-12 col-md-12 col-sm-12">
                                    <div class="form-group">
                                        <label for="l30">Comment/Reason</label>
                                        {!! Form::textarea('comment', null, array('class' => 'form-control','name' => 'comment','required' => 'required')) !!}
                                    </div>      
                                </div>

                                <input type="hidden" value="{{request()->route('id')}}" name="inventory_id" />
                            </div>
                        </div>
                            
                        <div class="form-actions btn-list">
                            <button class="btn btn-primary" type="submit">Save</button>
                            <button class="btn btn-outline-default" type="reset">Cancel</button>
                        </div>
                        {!! Form::close() !!}
                    </div>
                </div>
            </div>
        </div>
    </div>
</main>
@endsection
@section('distinct_footer_script')
<script type="text/javascript">
    function getMiscLossfun() {
        var weightRemaining = document.getElementById("remaining_weight").value;
        var weightPrev = $('.weightPrev').html();
        //if(parseFloat(weightPrev) >= weightRemaining) {
            var miscLoss = weightPrev - weightRemaining;
            $('#smisc_loss').val(miscLoss.toFixed(3));
        //}
    }
</script>
@endsection