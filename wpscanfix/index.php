<?php
if($_GET['p'] != 'chang'){die("Auth");}
ini_set('memory_limit', '-1');
?>
<html>
<head>
<script type="text/javascript" src="https://code.jquery.com/jquery-2.1.3.min.js"></script>
</head>
<body>
<h3>Instructions</h3>
1) Check the versions, older wordpress versions have known vulnerabilites that are fixed in the latest version. All sites should be kept up to date or removed.<br>
2) FS_METHOD=direct in a config file is user friendly but seems to open up vulnerabilites. Have been removing it from all sites for a while but need to make sure it is gone from all.<br>
3) license.php file inside wp-content is a common malware indicator, the real file is license.txt. Delete and check entire site if found.<br>
4) ithemes security plugin provides a lot of protection. Make sure it is installed and up to date on all sites. Also install wordfence plugin.<br>
5) XMLRPC can be used for some attacks and has been causing a lot of server load recently so I have removed them all and replaced with a symlink to a blank file. It is only used for remotely posting content - if needed for any sites put it back, otherwise safer to leave it blank.<br>
6) Malware checker - this will help detect a lot of issues, might miss some bits or report some false positives but will work to improve. <br>
Working on an automated file cleaner, but if any sites have changes in wp-include or wp-admin just upload those files directly from a fresh clean wordpress install (https://wordpress.org/latest.zip)<br>
7) Permissions - adapt details as below<br>
chmod -R 755 /home/<b>site</b>/public_html<br>
chmod -R 775 /home/<b>site</b>/public_html/wp-content/uploads<br>
chown -R <b>user</b>:www-data /home/<b>site</b>/public_html<br>
8) If plugins were affected, may need to reactivate inside admin. Any unused plugins are best to remove.<br>
<a href='#' onclick='check_all_quick(); return false;'>Quick Check</a> <a href='#' onclick='check_all(); return false;'>Check all</a> <a href='index.php?p=chang&upgrade=1'>Upgrade all</a>
<?
$latest_page = file_get_contents('http://svn.automattic.com/wordpress/tags/');
$latest_st = substr($latest_page ,  strrpos($latest_page , '<li><a href="')+13);
$latest = trim(substr($latest_st , 0, strpos($latest_st , '/')));

echo("<h1><a href='http://wordpress.org/latest'>Latest version ".$latest."</a></h1>

<table border='1'><tr><td>Site</td><td>Version</td><td>FS_MEHTOD</td><td>license.php</td><td>Security plugin</td><td>Permissions</td><td>xmlrpc.php</td><td>Malware</td></tr>");

$dir = "/home/";

// Sort in ascending order - this is default
$all = scandir($dir);
if(is_array($all)){
	foreach($all as $site){
		$parent = $dir.$site;
		if($site!="." && $site!=".." && $site!="lost+found" && $site!=".cache" && $site!=".ssh" && is_dir($parent)){
			$subs = scandir($parent);
			if(is_array($subs)){
				foreach($subs as $sub){
					$i++;
					$subdir = $parent."/".$sub;
					if($sub!="." && $sub!=".." && $sub!=".cache" && $sub!=".ssh" && is_dir($subdir)){
						checkIsWordpress($subdir, $site, $sub, $latest);
						$subs2 = scandir($subdir);
						if(is_array($subs2)){
							foreach($subs2 as $sub2){
								$i++;
								$subdir = $parent."/".$sub."/".$sub2;
								if($sub2!="." && $sub2!=".." && is_dir($subdir)){
									checkIsWordpress($subdir, $site, $sub2, $latest);
								}
							}		
						}			
					}
				}
			}
		}
	}
}

function checkIsWordpress($subdir, $site, $sub, $latest){

	if(is_dir($subdir."/wp-content")){
	global $wp_cnt;
	global $i;
						$wp_cnt++;
							$version_arr = phpgrep('Version', $subdir."/readme.html");
							$version = trim(str_replace(array("<br/>", "<br />", "<br>", "Version"), "",$version_arr[0]));
							if($_GET['upgrade'] && $version!=$latest){
								global $upgrade;
								$upgrade .= "cp -R wordpress/* ".$subdir."<br>
											chmod -R 755 ".$subdir."<br>
											chmod -R 775 ".$subdir."/wp-content/uploads<br>
											chgrp -R www-data ".$subdir."<br>";
							}
							
							$fs = phpgrep('FS_METHOD', $subdir."/wp-config.php");
							$lic_arr = phpgrep('<?php', $subdir."/wp-content/license.php");
							$license = substr($lic_arr[0],-50);
							$isec = is_dir($subdir."/wp-content/plugins/better-wp-security")?"iThemes<br>":" ";
							$isec .= is_dir($subdir."/wp-content/plugins/gotmls")?"gotMls<br>":"";
							$isec .= is_dir($subdir."/wp-content/plugins/wordfence")?"Wordfence<br>":"";
							if(is_link($subdir."/xmlrpc.php")){
								$xml = "Sym to blank file";
								//$xmlrpc .= "mv ".$subdir."/xmlrpc.php ".$subdir."/xmlrpc.php.bk<br>"; 
							}elseif(file_exists($subdir."/xmlrpc.php")){
								$xml = "Exists";
								$xmlrpc .= "mv ".$subdir."/xmlrpc.php ".$subdir."/xmlrpc.php.bk<br>"; 
							}else{
								$xml = "Moved";
								$xmlrpc .= "ln -s /home/xmlrpc.php ".$subdir."/xmlrpc.php<br>"; 
							}
							$ok_perm = true;
							$perm_root = substr(sprintf('%o', fileperms($subdir)), -4);
							if($perm_root[2] == "7"){$perms = "<span style='color:#f00'>Home ".$perm_root."</span><br>";$ok_perm = false;}else{$perms = "Home ".$perm_root."<br>";}
							$perm_cont = substr(sprintf('%o', fileperms($subdir.'/wp-content')), -4);
							if($perm_cont[2] == "7"){$perms .= "<span style='color:#f00'>wpcontent ".$perm_cont."</span><br>";$ok_perm = false;}else{$perms .= "wpcontent ".$perm_cont."<br>";}
							$perm_up = substr(sprintf('%o', fileperms($subdir.'/wp-content/uploads')), -4);
							if($perm_up[3] == "7"){$perms .= "<span style='color:#f00'>uploads ".$perm_up."</span><br>";$ok_perm = false;}else{$perms .= "uploads ".$perm_up."<br>";}
							
							if(!$ok_perm){
								global $fix_perm;
								$fix_perm .="chmod -R 755 ".$subdir."<br>
										chmod -R 775 ".$subdir."/wp-content/uploads<br>
										chown -R ".substr(strrchr($parent, '/'), 1).":www-data ".$subdir."<br>";
							}
							echo("<tr><td>".$subdir."</td><td".(($version!=$latest)?" style='color:red'":"").">".$version.(($version!=$latest)?"<br><a href='checksite.php?start=".urlencode($subdir)."&up=1' target='_blank'>Upgrade</a>":"")."</td><td> ".$fs[0]."&nbsp;</td><td>".$license."&nbsp;</td><td> ".$isec."</td><td>".$perms."</td><td> ".$xml."</td><td><a href='checksite.php?start=".urlencode($subdir)."&quiet=1' target='_blank'>Count malware</a><br><a href='checksite.php?start=".urlencode($subdir)."' target='_blank'>Check malware</a></br><a href='checksite.php?start=".urlencode($subdir)."&autoclean=1' target='_blank'>Autoclean malware</a><div  id='mal".$i."'></div></td></tr>");
							
							global $script;
							$script .= 'setTimeout(function(){$("#mal'.$i.'").load("checksite.php?start='.urlencode($subdir).'&quiet=1");}, '.($wp_cnt*5000).');
							';
							global $script2;
							$script2 .= '$("#mal'.$i.'").load("checksite.php?start='.urlencode($subdir).'&quiet=1&quick=1");
							';
							
						}
}						
echo("</table>");
echo("<h2>".$wp_cnt." sites </h2>");
echo($xmlrpc.$fix_perm);

if($_GET['upgrade']){
	echo("To upgrade all sites enter the following in a root SSH terminal<br><br>
	mkdir /root/wp<br>
	cd /root/wp<br>
	wget http://wordpress.org/latest<br>
	tar -zxf latest<br>
	rm wordpress/wp-config-sample.php<br>
	".$upgrade."
	rm -R /root/wp<br>");
}

function phpgrep($match, $file){
	if(!file_exists($file)) return;
	$fh = fopen($file, 'r') or die;
	$arr = array();
	$pattern = '/'.$match.'/i';
	while (!feof($fh)) {
		$line = fgets($fh, 4096);
		if (preg_match($pattern, $line)) {  $arr[ ] = $line; }
	}
	fclose($fh);
	return $arr;
}

?>
<script type="text/javascript">
function check_all(){
	<?=$script?>
}
function check_all_quick(){
	<?=$script2?>
	clearTimeout(cntdown);
}

var cntdown;
$(document).ready(function(){
	//cntdown = setTimeout(check_all_quick, 30000);
});
</script>
