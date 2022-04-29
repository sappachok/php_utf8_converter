<?
###########################################
## Phanupong Panyadee
## http://www.appservnetwork.com
## Convert Database from TIS-620 to UTF-8
## Powered by apples
##
## Version : 1.0
## Released : 2006-07-31
##
## HOW TO RUN THIS PROGRAM
##   1. This program design for "Command Line Execution" Only !!   ******** Not for WWW  ********.
##   2. This program will be "Convert all tables on your Database", Please specify database name below.
##   3. BEFORE Convert database please BACKUP YOUR DATABASE FIRST.
##   4. On Windows OS (AppServ).
##       - Goto Start -> Run -> cmd
##       - C:\AppServ\php\php.exe -d max_execution_time=0 utf8_db.php
##   5. On Linux (Test on Debian Linux).
##      - php -d max_execution_time=0 utf8_db.php
##
###########################################

$dbhost = "localhost";
$dbuname = "root";
$dbpass = "";
$dbname = "app";

$char1 = "TIS-620";	# Source Character Set 
$char2 = "UTF-8";		# Destination Character Set
$varchar_len = "0";	# 1 = Press y to confirm
										# 0 = Automatic Add Length for Varchar()

###########################################
# Use utf8_general_ci because it is faster.
# Use utf8_unicode_ci because it is more accurate.

$field_type = "utf8_unicode_ci"; 

###########################################
echo "<pre>";
if (!extension_loaded('iconv')) {
   if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') {
       dl('php_iconv.dll');
   } else {
       dl('iconv.so');
   }
}

mysql_connect($dbhost,$dbuname,$dbpass) or die ("Can't Connect to MySQL");
mysql_db_query($dbname,"ALTER DATABASE `$dbname` default character set utf8 collate $field_type");
$result=mysql_db_query($dbname,"show tables");
while(list($table)=mysql_fetch_row($result)) {
        $result2=mysql_db_query($dbname,"desc $table");
        echo "Table : $table\n";
        mysql_db_query($dbname,"ALTER TABLE `$table` DEFAULT CHARACTER SET utf8 COLLATE $field_type");
        while(list($field,$type,$null,$key)=mysql_fetch_row($result2)) {
			list($typename,$olen)=explode("(",$type);
			switch ($typename) {
				case "varchar" :
					$result3=mysql_db_query($dbname,"SELECT LENGTH(MAX( $field )) FROM  `$table`");
					list($max_len)=mysql_fetch_row($result3);
					$olen=substr($olen,0,-1);
					$len = $olen + 80;
					if ($varchar_len == "1") {
						echo "\nField : $field\n";
						echo "Varchar($olen) Need to change to Varchar($len) ?\n";
						echo "Type 'y' to confirm : ";
						$input = trim(strtolower(fgets(STDIN)));
					} else {
						$input = "y";
					}
					if ($input == "y") {
						mysql_db_query($dbname,"ALTER TABLE `$table` CHANGE `$field` `$field` VARCHAR( $len ) ");
					}
					unset ($olen,$len);
				break;
			}
                mysql_db_query($dbname,"ALTER TABLE `$table` CHANGE `$field` `$field` $type CHARACTER SET utf8 COLLATE $field_type");
                $all_field[] = $field;
                if ($key == "PRI") {
                        $table_key[] = $field;
                }
        }
        foreach ($all_field as $value) {
                $select_field .= "`$value`,"; 
        }
        $num_field=count($all_field);
        $num_key=count($table_key);
        $select_field = substr($select_field ,0,-1);
        $update_field = substr($update_field ,0,-18);
        mysql_db_query($dbname,"SET NAMES latin1");
        mysql_db_query($dbname,"SET collation_connection = 'latin1_swedish_ci'");
        $result3=mysql_db_query($dbname,"select $select_field from $table");
        mysql_db_query($dbname,"SET NAMES utf8");
        mysql_db_query($dbname,"SET collation_connection = '$field_type'");
        $arow=mysql_num_rows($result3);
        $i = 1;
        if ($arow !=0) {
                while($arr=mysql_fetch_array($result3)){
                        if ($i == 1) {
							echo "   Found $arow fields.\n";
                        }
                        ++$i;
                        for ($x=0;$x<$num_field;$x++) {
							if (is_null($arr[$x])) {
								$var = "`$all_field[$x]`=NULL,";
							} else {
                                $var = ICONV("$char1","$char2",$arr[$x]);
								$lenchar1=strlen($arr[$x]);
								$lenchar2=strlen_utf8($var);
								if ($char1 == "TIS-620") {	 // Loop for Thai Convert. You can split to function.
									if ($lenchar1 != $lenchar2) {
										++$no_len;
										# echo "$no_len Char Len : $lenchar1 && $lenchar2 ";
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
										# echo "Again : $lenchar2 $result_len\n";
									} // end if $lenchar1 != $lenchar2
                                }
                                $var = ereg_replace("\\\\","\\\\",$var);
                                $var = ereg_replace("'","\'",$var);
                                $var = "`$all_field[$x]`='$var',";
							}
                                $update_field .= $var;
                                if (isset($table_key)) {
									if ($x==0) {
										for ($y=0;$y<$num_key;$y++) {
											$key = $table_key[$y];
											$update_where .= "`$table_key[$y]`='$arr[$key]' and ";
										}
									}
                                } else {
									$update_where .= "`$all_field[$x]`='$arr[$x]' and ";
                                }
                        }
                        $update_field = substr($update_field ,0,-1);
                        $update_where = substr($update_where ,0,-4);
                        #echo "UPDATE $table  SET $update_field where $update_where\n";
                        $uresult=mysql_db_query($dbname,"UPDATE $table  SET $update_field where $update_where");
						$converted = $converted + $uresult;
                        unset($update_where,$update_field);
                }
        } else {
                echo "   NO Data in this table. \n";
        }
         echo "   Converted $converted fields.\n";
        echo "------------------------\n";
        unset($all_field,$select_field,$update_field,$table_key,$converted);
}

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