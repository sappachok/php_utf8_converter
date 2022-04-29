<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Welcome extends CI_Controller {

	/**
	 * Index Page for this controller.
	 *
	 * Maps to the following URL
	 * 		http://example.com/index.php/welcome
	 *	- or -
	 * 		http://example.com/index.php/welcome/index
	 *	- or -
	 * Since this controller is set as the default controller in
	 * config/routes.php, it's displayed at http://example.com/
	 *
	 * So any other public methods not prefixed with an underscore will
	 * map to /index.php/welcome/<method_name>
	 * @see https://codeigniter.com/userguide3/general/urls.html
	 */
	public function __construct()
	{
		parent::__construct();
		// Your own constructor code
		$this->load->helper("file");
		$this->load->helper('directory');
	}

	public function index()
	{
		$convert = $this->input->post("convert");
		$target = $this->input->post("target");

		if($target) {
			if(!is_dir($target."/_output")) {
				mkdir($target."/_output");
			}

			//$dir = directory_map($target_path);
			$this->clone_dir($target."/_output/", $target);

			$this->convert($target."/_output/", $target);

			$this->load->view('convert');
		} else {
			$this->load->view('index');
		}
	}

	public function clone_dir($dist="_output", $target_path)
	{
		$dir = directory_map($target_path);

		if($dir)
		foreach($dir as $ind => $val) {
			if(is_array($val)) {
				if($ind != '_output\\') {
					@mkdir($dist."/".$ind); 
					echo "Create Folder :: ".$dist."/".$ind."<br>";
					$this->clone_dir($dist."/".$ind, $target_path."/".$ind);
				}
			} else {

			}
		}
	}

	public function convert($dist="_output", $target_path)
	{
		$dir = directory_map($target_path);

		if($dir)
		foreach($dir as $ind => $val) {
			if(is_array($val)) {
				if($ind != '_output\\') {
					//mkdir($dist."/".$ind); 
					//echo "Create :: ".$dist."/".$ind."<br>";
					$this->convert($dist."/".$ind, $target_path."/".$ind);
				}
			} else {
				$file_path = $target_path."/".$val;
				$this->utf8_convert($file_path, $dist);

				//$this->load->view("convert-file", $data);
			}
		}
	}

	public function utf8_convert($file_path, $dest="")
	{
		if (!extension_loaded('iconv')) {
		   if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') {
			   dl('php_iconv.dll');
		   } else {
			   dl('iconv.so');
		   }
		}

		echo "utf8_convert :: ".$file_path."<br>";

		$char1 = "TIS-620";		# Source Character Set TIS-620 Please Leave BLANK !!
		$char1 = "MacThai";		# Source Character Set TIS-620 Please Leave BLANK !!
		
		$char2 = "UTF-8";			# Destination Character Set
		$output = "";

		$file_info = get_file_info($file_path);
		echo $file_path."<br>";
		//var_dump($file_info);
		
		$file_name = $file_info["name"];
		$file_size = $file_info["size"];
		//$file_type = $file_info["type"];
		//$file_tmp_name = $file_info["tmp_name"];

		$arr=file($file_path);
		$count=count($arr);
		for ($x=0;$x<$count;$x++) {
			//echo $char1."=>".$char2."<br>";
			$var = ICONV($char1, $char2, $arr[$x]);
			$lenchar1=strlen($arr[$x]);
			$lenchar2=$this->strlen_utf8($var);
			if ($char1 == "TIS-620") {	 // Loop for Thai Convert. You can split to function.
				if ($lenchar1 != $lenchar2) {
					++$no_len;
					$var = ICONV("CP874", $char2, $arr[$x]);
					$lenchar2=$this->strlen_utf8($var);
					if ($lenchar1 == $lenchar2) {
						$result_len = "Ok";
					} else {
						$result_len = "MacThai";
					}
					if ($result_len == "MacThai") {
						$var = ICONV("MacThai", $char2 ,$arr[$x]);
						$lenchar2=$this->strlen_utf8($var);
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
		
		$handle = fopen($dest."/".$file_name,"w");
		fwrite($handle, $output);
		fclose($handle);

		echo "destination :: ".$dest."/".$file_name."<br>";
	}

	public function strlen_utf8($str) 
	{
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
}
