<footer class="col-sm-12 text-center">
	@if (\Request::is('register'))
	  <hr>
	  	<p>Already have an account? <a href="<?=URL::to('/');?>/login" class="text-primary m-l-5"><b>Log In</b></a>
	  </p>
	@endif
</footer>