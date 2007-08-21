<?php
$database = 'dase_prod';
include 'cli_setup.php';

$IMAGE_REPOS = "/mnt/dar/favrc/for-dase";
if (!file_exists($IMAGE_REPOS)) {
	die ("cannot find $IMAGE_REPOS");
}
$found = array();

$dir = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($IMAGE_REPOS));

$contents = "<?php\n\n";

$i = 0;
foreach ($dir as $file) {
	if (!strpos($file,'/.')) {
		if (strpos($file,'.jpg') || strpos($file,'.tif')) {
			$k = basename($file);
			$contents .= "\$images['$k'] = \"$file\";\n";
			$i++;
			print "$i\n";
		}
	}
}


$host = "SQL01.austin.utexas.edu:1036";
$name = "vrc_live";
$user = "dasevrc";
$pass = "d453vrc";

$pdo = new PDO("dblib:host=$host;dbname=$name", $user, $pass);

$sql = "
	SELECT  
	acc_digital_num, 
	acc_num_PK 
	FROM tblAccession 
	WHERE acc_digital_num != ''
	";

$st = $pdo->prepare($sql);
$st->setFetchMode(PDO::FETCH_ASSOC);
$st->execute();

$contents .= "\n\n";

while ($row = $st->fetch()) {
	$it = new Dase_DB_Item;
	$it->serial_number = $row['acc_num_PK'];
	$it->collection_id = Dase_DB_Collection::get('vrc_collection')->id;
	$it->findOne();
	$sernum = $it->serial_number;
	$count = $it->getMediaCount();
	$contents .= "\$media_count['$sernum'] = \"$count\";\n";
	$i++;
	print "$i\n";
}

file_put_contents(DASE_PATH . '/modules/vrc/data/dataset.php',$contents);

echo "complete!";
