<?php
###########################################
## Phanupong Panyadee
## http://www.appservnetwork.com
## Convert File from TIS-620 to UTF-8
## Powered by apples
##
## Version : 1.0
## Released : 2006-07-31
##
## HOW TO RUN THIS PROGRAM
##   1. This program design for WWW.
##   2. Access via www e.g. http://www.appservnetwork.com/free-scripts/utf-8/utf8_convert_file.php
##
###########################################

$char1 = "TIS-620";		# Source Character Set TIS-620 Please Leave BLANK !!
$char2 = "UTF-8";			# Destination Character Set
$output = "";
###########################################
if (!extension_loaded('iconv')) {
   if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') {
       dl('php_iconv.dll');
   } else {
       dl('iconv.so');
   }
}
if($_FILES) {
foreach($_FILES as $key => $value) {
	$file_name = $value["name"];
	$file_size = $value["size"];
	$file_type = $value["type"];
	$file_tmp_name = $value["tmp_name"];
}

$numfile=count($file_name);
if ($numfile != 0) {
	for ($i=0;$i<$numfile;$i++) {
		$arr=file($file_tmp_name[$i]);
		$count=count($arr);
		for ($x=0;$x<$count;$x++) {
			$var = ICONV("$char1","$char2",$arr[$x]);
			$lenchar1=strlen($arr[$x]);
			$lenchar2=strlen_utf8($var);
			if ($char1 == "TIS-620") {	 // Loop for Thai Convert. You can split to function.
				if ($lenchar1 != $lenchar2) {
					++$no_len;
					$var = ICONV("CP874","$char2",$arr[$x]);
					$lenchar2=strlen_utf8($var);
					if ($lenchar1 == $lenchar2) {
						$result_len = "Ok";
					} else {
						$result_len = "MacThai";
					}
					if ($result_len == "MacThai") {
						$var = ICONV("MacThai","$char2",$arr[$x]);
						$lenchar2=strlen_utf8($var);
						if ($lenchar1 == $lenchar2) {
							$result_len = "Ok";
						} else {
							$result_len = "Another Charset";
							$var = $arr[$x];
						}
					}
				} // end if $lenchar1 != $lenchar2
			}
			$output .= $var;
		}
		$handle = fopen($file_tmp_name[$i],"w");
		fwrite($handle,$output);
		fclose($handle);
		header("Pragma: public");
		header("Expires: 0");
		header("Cache-Control: must-revalidate, post-check=0, pre-check=0"); 
		header("Content-Type: application/force-download");
		header("Content-Type: application/octet-stream");
		header("Content-Type: application/download");
		header("Content-Disposition: attachment; filename=".basename("$file_name[$i]").";");
		header("Content-Transfer-Encoding: binary\r\n");
		header("Content-Length: ".filesize($file_tmp_name[$i]));
		readfile($file_tmp_name[$i]);
		unlink($file_tmp_name[$i]);
	}
	exit();
}
}

echo "<html>";
echo "<head>";
echo "<title>Convert file to UTF-8 format Powered by apples</title>";
echo "<META HTTP-EQUIV=\"Content-Type\" CONTENT=\"text/html; charset=UTF-8\">";
echo "</head>";
echo "<body>";
echo "<form action=\"utf8_convert_file.php\" method=\"post\" enctype=\"multipart/form-data\">";
echo "<table border=\"0\" width=\"332\" cellspacing=\"1\" bgcolor=\"#E6F2FF\" align=\"center\">";
echo "<tr><td width=\"332\" height=\"30\" align=\"center\" bgcolor=\"#DFEFFF\"><strong>Convert file to UTF-8 format.</strong></td></tr>";
echo "<tr><td width=\"332\" valign=\"top\" align=\"center\" bgcolor=\"#F4FAFF\">";
echo "<table border=\"0\" width=\"296\" cellspacing=\"1\">";
echo "<tr><td width=\"81\" height=\"26\">File 1 : </td>";
echo "<td width=\"203\" height=\"26\"><input type=\"file\" name=\"file[]\"><br></td>";
echo "</tr>";
echo "</table>";
echo "</td>";
echo "</tr>";
echo "<tr><td width=\"332\" height=\"35\" align=\"center\" bgcolor=\"#F9FCFF\"><input type=\"submit\" value=\"Convert Me !\"></td>";
echo "</tr>";
echo "</table>";
echo "</form>";
echo "<br><br><center>Powered by apples <br>";
echo "<a href=\"http://www.appservnetwork.com\">http://www.appservnetwork.com</a></html>";

function strlen_utf8 ($str) {
   $i = 0;
   $count = 0;
   $len = strlen ($str);
   while ($i < $len) {
		$chr = ord ($str[$i]);
		$count++;
		$i++;
		if ($i >= $len)   break;
			if ($chr & 0x80) {
			   $chr <<= 1;
			   while ($chr & 0x80) {
					$i++;
					$chr <<= 1;
			   }
			}
		}
   return $count;
}
?>