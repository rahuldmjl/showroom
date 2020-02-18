</div>
        <!-- /.login-center -->
    </div>
    <!-- /.body-container -->
    <!-- Scripts -->
    <script src="<?=URL::to('/');?>/cdnjs.cloudflare.com/ajax/libs/jquery/3.2.1/jquery.min.js"></script>
    <script src="<?=URL::to('/');?>/cdnjs.cloudflare.com/ajax/libs/twitter-bootstrap/3.3.7/js/bootstrap.min.js"></script>
    <script src="<?=URL::to('/');?>/assets/js/material-design.js"></script>
    <script>
        var checkLoad = function() {   
            document.readyState !== "complete" ? setTimeout(checkLoad, 11) : $('#email').parent('.form-group').addClass('input-has-value'); $('#password').parent('.form-group').addClass('input-has-value');   
        };  

        checkLoad(); 
    </script>
</body>


<!-- Mirrored from oscar.dharansh.in/default/page-register.html by HTTrack Website Copier/3.x [XR&CO'2014], Sun, 28 Apr 2019 12:28:20 GMT -->
</html>