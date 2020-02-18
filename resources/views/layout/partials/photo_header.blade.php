<?php
$user = Auth::user();
if (!empty(Auth::user()->avatar) && file_exists('assets/images/' . Auth::user()->avatar)) {
	$user_avatar = URL::to('/') . '/assets/images/' . Auth::user()->avatar;
} else {
	$user_avatar = URL::to('/') . '/assets/images/users.jpeg';
}
DB::setTablePrefix('dml_');
?>
    <div class="content-wrapper">
        <!-- SIDEBAR -->
        <aside class="site-sidebar scrollbar-enabled clearfix">
            <!-- User Details -->
            <div class="side-user">
                <a class="col-sm-12 media clearfix" href="javascript:void(0);">
                    <figure class="media-left media-middle user--online thumb-sm mr-r-10 mr-b-0">
                        <img src="<?=$user_avatar?>" class="media-object rounded-circle" />
                    </figure>
                    <div class="media-body hide-menu">
                        <h4 class="media-heading mr-b-5 text-uppercase">{{$user->name}}</h4><span class="user-type fs-12">My Account <i class="fa fa-caret-down"></i></span>
                    </div>
                </a>
                <div class="clearfix"></div>
                <ul class="nav in side-menu">

                    <li><a href="{{url('profile')}}"><i class="list-icon material-icons">person</i> Edit Profile</a>
                    </li>
                    <li><a href="{{url('changepassword')}}"><i  class="list-icon material-icons">lock</i> Change Password</a>
                    </li>
                    <li><a href="<?=URL::to('/');?>/logout" onclick="event.preventDefault();
                                                     document.getElementById('logout-form-sidebar').submit();"><i class="list-icon material-icons">settings_power</i> Logout</a>
                        <form id="logout-form-sidebar" action="{{ route('logout') }}" method="POST" style="display: none;">
                                        @csrf
                                    </form>
                    </li>

                </ul>
            </div>
            <!-- /.side-user -->
            <!-- Sidebar Menu -->
            <nav class="sidebar-nav">
                <ul class="nav in side-menu">
                    <li class="@if (\Request::is('/') || \Request::is('/')) current-page @endif"><a href="<?=URL::to('/Photoshop/Photography');?>" class="ripple"><span class="color-color-scheme"><i class="list-icon material-icons">network_check</i> <span class="hide-menu">Dashboard </span></span></a>
               </li>
              
               <li class="@if (\Request::is('users*') || \Request::is('roles*') || \Request::is('permissions*')) current-page @endif menu-item-has-children "><a href="javascript:void(0);" class="ripple"><span class="@if (\Request::is('users*') || \Request::is('roles*') || \Request::is('permissions*')) color-color-scheme @endif"><i class="list-icon material-icons">add_a_photo</i> <span class="hide-menu">Photography</span></span></a>
                <ul class="list-unstyled sub-menu @if ( \Request::is('users*') || \Request::is('roles*') || \Request::is('permissions*') ) in @endif">
                    <li class="@if(\Request::is('users*')) active @endif"><a href="<?=URL::to('/Photoshop/Photography/pending');?>">Pending &nbsp;<span class="badge badge-border badge-border-inverted bg-primary">0</span></a>
                    </li>
                    <li class="@if(\Request::is('roles*')) active @endif"><a href="<?=URL::to('/Photoshop/Photography/done');?>">Done &nbsp;<span class="badge badge-border badge-border-inverted bg-primary">0</span></a>
                    </li>
                    <li class="@if(\Request::is('permissions*')) active @endif"><a href="<?=URL::to('/Photoshop/Photography/rework');?>">Rework &nbsp;<span class="badge badge-border badge-border-inverted bg-primary">0</span></a>
                    </li>
                </ul>
            </li>

            <li class="@if (\Request::is('users*') || \Request::is('roles*') || \Request::is('permissions*')) current-page @endif menu-item-has-children "><a href="javascript:void(0);" class="ripple"><span class="@if (\Request::is('users*') || \Request::is('roles*') || \Request::is('permissions*')) color-color-scheme @endif"><i class="list-icon material-icons">camera</i> <span class="hide-menu">PSD</span></span></a>
                <ul class="list-unstyled sub-menu @if ( \Request::is('users*') || \Request::is('roles*') || \Request::is('permissions*') ) in @endif">
                    <li class="@if(\Request::is('users*')) active @endif"><a href="<?=URL::to('/Photoshop/psd/pending');?>">Pending &nbsp;<span class="badge badge-border badge-border-inverted bg-primary">0</span></a>
                    </li>
                    <li class="@if(\Request::is('roles*')) active @endif"><a href="<?=URL::to('/Photoshop/psd/done');?>">Done &nbsp;<span class="badge badge-border badge-border-inverted bg-primary">0</span></a>
                    </li>
                    <li class="@if(\Request::is('permissions*')) active @endif"><a href="<?=URL::to('/Photoshop/psd/rework');?>">Rework &nbsp;<span class="badge badge-border badge-border-inverted bg-primary">0</span></a>
                    </li>
                </ul>
            </li>
            <li class="@if (\Request::is('users*') || \Request::is('roles*') || \Request::is('permissions*')) current-page @endif menu-item-has-children "><a href="javascript:void(0);" class="ripple"><span class="@if (\Request::is('users*') || \Request::is('roles*') || \Request::is('permissions*')) color-color-scheme @endif"><i class="list-icon material-icons">monochrome_photos</i> <span class="hide-menu">Placement</span></span></a>
                <ul class="list-unstyled sub-menu @if ( \Request::is('users*') || \Request::is('roles*') || \Request::is('permissions*') ) in @endif">
                    <li class="@if(\Request::is('users*')) active @endif"><a href="<?=URL::to('/Photoshop/Placement/pending');?>">Pending &nbsp;<span class="badge badge-border badge-border-inverted bg-primary">0</span></a>
                    </li>
                    <li class="@if(\Request::is('roles*')) active @endif"><a href="<?=URL::to('/Photoshop/Placement/done');?>">Done &nbsp;<span class="badge badge-border badge-border-inverted bg-primary">0</span></a>
                    </li>
                    <li class="@if(\Request::is('permissions*')) active @endif"><a href="<?=URL::to('/Photoshop/Placement/rework');?>">Rework &nbsp;<span class="badge badge-border badge-border-inverted bg-primary">0</span></a>
                    </li>
                </ul>
            </li>
            <li class="@if (\Request::is('users*') || \Request::is('roles*') || \Request::is('permissions*')) current-page @endif menu-item-has-children "><a href="javascript:void(0);" class="ripple"><span class="@if (\Request::is('users*') || \Request::is('roles*') || \Request::is('permissions*')) color-color-scheme @endif"><i class="list-icon material-icons">broken_image</i> <span class="hide-menu">Editing</span></span></a>
                <ul class="list-unstyled sub-menu @if ( \Request::is('users*') || \Request::is('roles*') || \Request::is('permissions*') ) in @endif">
                    <li class="@if(\Request::is('users*')) active @endif"><a href="<?=URL::to('/Photoshop/Editing/pending');?>">Pending &nbsp;<span class="badge badge-border badge-border-inverted bg-primary">0</span></a>
                    </li>
                    <li class="@if(\Request::is('roles*')) active @endif"><a href="<?=URL::to('/Photoshop/Editing/done');?>">Done &nbsp;<span class="badge badge-border badge-border-inverted bg-primary">0</span></a>
                    </li>
                    <li class="@if(\Request::is('permissions*')) active @endif"><a href="<?=URL::to('/Photoshop/Editing/rework');?>">Rework &nbsp;<span class="badge badge-border badge-border-inverted bg-primary">0</span></a>
                    </li>
                </ul>
            </li>
            <li class="@if (\Request::is('users*') || \Request::is('roles*') || \Request::is('permissions*')) current-page @endif menu-item-has-children "><a href="javascript:void(0);" class="ripple"><span class="@if (\Request::is('users*') || \Request::is('roles*') || \Request::is('permissions*')) color-color-scheme @endif"><i class="list-icon material-icons">monochrome_photos</i> <span class="hide-menu">JPEG</span></span></a>
                <ul class="list-unstyled sub-menu @if ( \Request::is('users*') || \Request::is('roles*') || \Request::is('permissions*') ) in @endif">
                    <li class="@if(\Request::is('users*')) active @endif"><a href="<?=URL::to('/Photoshop/JPEG/pending');?>">Pending &nbsp;<span class="badge badge-border badge-border-inverted bg-primary">0</span></a>
                    </li>
                    <li class="@if(\Request::is('roles*')) active @endif"><a href="<?=URL::to('/Photoshop/JPEG/done');?>">Done &nbsp;<span class="badge badge-border badge-border-inverted bg-primary">0</span></a>
                    </li>
                    <li class="@if(\Request::is('permissions*')) active @endif"><a href="<?=URL::to('/Photoshop/JPEG/rework');?>">Rework &nbsp;<span class="badge badge-border badge-border-inverted bg-primary">0</span></a>
                    </li>
                </ul>
            </li>
            <li class="@if (\Request::is('users*') || \Request::is('roles*') || \Request::is('permissions*')) current-page @endif menu-item-has-children "><a href="javascript:void(0);" class="ripple"><span class="@if (\Request::is('users*') || \Request::is('roles*') || \Request::is('permissions*')) color-color-scheme @endif"><i class="list-icon material-icons">cloud_upload</i> <span class="hide-menu">Product</span></span></a>
                <ul class="list-unstyled sub-menu @if ( \Request::is('users*') || \Request::is('roles*') || \Request::is('permissions*') ) in @endif">
                    <li class="@if(\Request::is('users*')) active @endif"><a href="<?=URL::to('Photoshop/Product/add');?>">Add </a>
                    </li>
                    <li class="@if(\Request::is('users*')) active @endif"><a href="<?=URL::to('Photoshop/Product/list');?>">List </a>
                    </li>
                  
                </ul>
            </li>
        </ul>
        
    </nav>
            <!-- /.sidebar-nav -->
</aside>
        <!-- /.site-sidebar -->

