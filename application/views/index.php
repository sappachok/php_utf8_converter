<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-1BmE4kWBq78iYhFldvKuhfTAU6auU8tT94WrHftjDbrCEXSU1oBoqyl2QvZ6jIW3" crossorigin="anonymous">
<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>

<style>
label {
	font-weight: bold;
	margin-bottom: 3px;
}
</style>
<div class="container">
<?php
if(!$target_path) $target_path = "C:\\xampp3\\htdocs\\encoder\\code\\example\\";

echo "<h2>Target path:</b> ".$target_path."</h2>";

$dir = directory_map($target_path);
?>
<br>
<form method="post">
<p>
	<label>Target path</label>
	<input type="text" name="target" value="<?php echo $target_path; ?>" class="form-control">
</p>
<p>
	<button type="button" name="get" class="btn btn-primary">Get File</button>
	<button type="submit" name="convert" class="btn btn-primary">Convert</button>
</p>
</form>

<?php
echo "<h2>Item Lists</h2>";

if($dir)
foreach($dir as $ind => $val) {
	if(is_array($val)) {
		echo "<b>".$ind."</b><br>";		
	} else {
		//echo $val."<br>";
	}
}

foreach($dir as $ind => $val) {
	if(is_array($val)) {
		//echo "<b>".$ind."</b><br>";		
	} else {
		echo $val."<br>";
	}
}
?>
</div>

<script>
$(document).ready(function() {
	$("button[name=get]").click(function() {
		window.location = "?path=" + $("input[name=target]").val();
	});
});
</script>