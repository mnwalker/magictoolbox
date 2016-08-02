<?php
$err = 0;
if(!$_GET['quiet']){echo("checking...".$_GET['start']."<br>");}

//echo implode("<br>",$mal);


if($_GET['up']){
	echo("Enter the following in a root SSH terminal<br><br>
	mkdir /root/wp<br>
	cd /root/wp<br>
	wget http://wordpress.org/latest<br>
	tar -zxf latest<br>
	rm wordpress/wp-config-sample.php<br>
	cp -R wordpress/* ".urldecode($_GET['start'])."<br>
	chmod -R 755 ".urldecode($_GET['start'])."<br>
	chmod -R 775 ".urldecode($_GET['start'])."/wp-content/uploads<br>
	chgrp -R www-data ".urldecode($_GET['start'])."<br>
	rm -R /root/wp<br>");
}else{
	$mal = scan_all_files(urldecode($_GET['start']));
	if(!$_GET['quiet']){echo("check complete");}
	elseif($_GET['quick']){echo("<span style='color:".(($err)?"red":"green").";'>".$err."+ files (quick)</span>");}
	else{echo("<span style='color:".(($err)?"red":"green").";'>".$err." files</span>");}
}

function scan_all_files($start, $arr=array(),$lvl=0){
	global $err;
	
	$lvl++;
	
	$pattern1 = '/gzdeflate\(/i';
	$ok_1 = array("load-scripts.php", "load-styles.php", "class-http.php", "class-pclzip.php", "pclzip.class.php", "pclzip.php", "ajax-actions.php", 'Encoder.php', 'Minify.php', 'nusoap.php', 'PgCache.php', 'class-wp-http-encoding.php');
	$pattern2 = '/isset\(\$GLOBALS\[\'\\\/i';
	$ok_2 = array();
	$pattern3 = '/strstr\(\$ua/i';
	$ok_3 = array();
	$pattern4 = '/eval\(base64_decode/i';
	$ok_4 = array();
	$ok_5 = array();
	$pattern6 = '/die\(PHP_OS.chr\(49\).chr\(48\).chr\(43\).md5\(0987654321/i';
	$ok_6 = array();
	$pattern7 = '/chr(114).chr(101)."a".chr(116)/i';
	$ok_7 = array();
	$ok_8 = array();
	$ok_9 = array('update_dialog.php', 'slides_list_posts.php', 'slides_posts.php', 'header-v1.php', 'header-v5.php');
	
	
	$all = scandir($start);
	if(is_array($all)){
		foreach($all as $a){
			if($a!="." && $a!=".." && $a!="lost+found"){
				$check = $start."/".$a;
				if(is_dir($check)){
					if($lvl < 3 || (!$_GET['quick'])){	$arr = scan_all_files($check, $arr, $lvl);}
				}elseif(substr($a,-4) == ".php"){
					
					$clean = true;
					if(file_exists($check)){
						$fh = fopen($check, 'r');
						if($fh){
							$i = 0;
						
							$ok = true;
							$ty = "";
							while (!feof($fh) && $ok) {
								$line = fgets($fh, 4096);
								if($ok){
									if($a == "license.php" && strpos($start,"screets-cx") < 1 && strpos($start,"adrotate") < 1 ){$ok = false; $ty = "a";}
									elseif (!(in_array($a, $ok_1)) && preg_match($pattern1, $line)) { $ok = false; $ty = "b"; }
									elseif (!(in_array($a, $ok_2)) && preg_match($pattern2, $line)) { $ok = false; $ty = "c"; }
									elseif (!(in_array($a, $ok_3)) && preg_match($pattern3, $line)) { $ok = false; $ty = "d"; }
									elseif (!(in_array($a, $ok_4)) && preg_match($pattern4, $line)) { $ok = false; $ty = "e"; }
									elseif (!(in_array($a, $ok_5)) && substr_count($line, '$GLOBALS[') > 5) { $ok = false; $ty = "f"; }
									elseif (!(in_array($a, $ok_6)) && preg_match($pattern6, $line)) { $ok = false; $ty = "g"; }
									elseif (!(in_array($a, $ok_7)) && preg_match($pattern7, $line)) { $ok = false; $ty = "h"; }
									
									if($i == 0){
										if (!(in_array($a, $ok_8)) && substr_count($line, 'strrev(') >= 2) { $ok = false; $ty = "i"; }
										elseif (!(in_array($a, $ok_9)) && substr_count($line, '<?php') >= 2 && strlen($line) > 2000) { $ok = false; $ty = "j"; }
									}
								}
								if(!$ok){
									$arr[ ] = $line;  $clean = false; $ic = $i; $il=$line;
									if($_GET['autoclean']){
										cleanFile($check, $i);
									}
								}
								$i++;
							}
						
							fclose($fh);
							if(!$clean){
								if(!$_GET['quiet'] && !$_GET['autoclean']){echo ("<font color='blue'>".$check." line ".$ic." - dirty[type ".$ty."](length ".strlen($il).")</font><br>".((strlen($il) > 400)?substr(htmlentities($il),100,200):htmlentities($il))."<br>");}
								else{$err++;}
							}
						}else{echo("Cant read ".$check);}
					}
				}
			}
		}
		if($_GET['autoclean'] && $lvl==1){
			echo("<br>now run following command as root<br>
					chmod -R 755 ".urldecode($_GET['start'])."<br>
					chmod -R 775 ".urldecode($_GET['start'])."/wp-content/uploads<br>
					chgrp -R www-data ".urldecode($_GET['start'])."<br>");
		}
	}
	return $arr;
}


function cleanFile($file, $ln){
		$folder = urldecode($_GET['start']);
		if(!is_writable($file)){
			echo("run following command as root<br>
			chmod -R 775 ".$folder."<br>
			chgrp -R www-data ".$folder."");
			die();
		}
      	$type="P";
        $lines = array();
        $f = fopen($file, 'r');
        $check_next = 0;
        $i == 0;
        $end_found = false;
        while($line = fgets($f)){
        	if($check_next > 0){
        		$check_next--;
        		$trim = trim($line);
        		if($trim[0] == '$'){$type="P"; $check_next = 0; $end_found = true;}
        		elseif($trim[0] == '?' && $trim[1] == '>'){$type = "P"; $check_next = 0;}
        		elseif($trim[0] == '<' && $trim[1] != '?'){$type = "H"; $check_next = 0;}
        	}
        	if($i == $ln){$check_next = 5;}
        	array_push($lines, $line);
        	$i++;
        }
        fclose($f);
        if($type=="P"){
        	$html_arr = explode("?>", $lines[$ln]);
        	if(end($html_arr) == $lines[$ln]){$end = "";}
        	else{$end = trim(end($html_arr));}
        	if($end[0] =="<" && $end[1] =="?"){$lines[$ln] = $end."\r\n"; echo(".."."<br><br><br>".$end);}
        	else{$lines[$ln] = "<?php\r\n";echo(",,");}
        	//$lines[$ln] = "<?php\r\n";   
        }else{
        	$html_arr = explode("?>", $lines[$ln]);
        	$end = trim(end($html_arr));
        	if($end[0] =="<"){$lines[$ln] = $end."\r\n";}
        	else{$lines[$ln] = "\r\n";}
        }
        
        echo("clean file ".$file." line ".$ln."[".$type."]<br>");//print_r($lines);
        
        $f = fopen($file, 'w');
        foreach($lines as $line){fwrite($f, $line);}
        fclose($f); 
}

?>
