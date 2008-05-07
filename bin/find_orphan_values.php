<?php
$database = 'dase_prod';
require_once 'cli_setup.php'; //sets up the environment (autoload, etc.)

$db = Dase_DB::get();
$sql = "
	SELECT count(v.id)
	FROM value v LEFT JOIN item i
	ON v.item_id = i.id
	WHERE i.id IS NULL
	";
$sth = $db->prepare($sql);
$sth->execute();
$count = $sth->fetchColumn();
print "$count orphaned values found\n\n";

$sql = "
	SELECT v.id, v.value_text
	FROM value v LEFT JOIN item i
	ON v.item_id = i.id
	WHERE i.id IS NULL
	";
$sth = $db->prepare($sql);
$sth->execute();
$total = 0;
$skipped = 0;
$deleted = 0;
$cli = true;
while ($row = $sth->fetch()) {
	$total++;
	print "\n{$row['value_text']} ({$row['id']}) is an orphan value ---- ";
	if ($cli) {
		$end = false;
		while (! $end) {
			$read = readline('delete item? [Y|n|x|all] ');
			if ('n' == $read) {
				$skipped++;
				print "\nskipping {$row['value_text']} (deleted: $deleted, skipped: $skipped, [total:$total])\n";
				$end = true;
			} elseif ('x' == $read) {
				$skipped++;
				print "\ngoodbye (deleted: $deleted, skipped: $skipped, [total:$total])\n\n";
				exit;
			} elseif ('all' == $read) {
				$deleted++;
				print "\ndeleting {$row['value_text']} (deleted: $deleted, skipped: $skipped, [total:$total])\n";
				$doomed = new Dase_DBO_Value;
				$doomed->load($row['id']);
				if ($doomed->value_text) {
					$doomed->delete();
				}
				$cli = false;
				$end = true;
			} else {
				$deleted++;
				print "\ndeleting {$row['value_text']} (deleted: $deleted, skipped: $skipped, [total:$total])\n";
				$doomed = new Dase_DBO_Value;
				$doomed->load($row['id']);
				if ($doomed->value_text) {
					$doomed->delete();
				}
				$end = true;
			}
		}
	} else {
		$deleted++;
		print "\ndeleting {$row['value_text']} (deleted: $deleted, skipped: $skipped, [total:$total])\n";
		$doomed = new Dase_DBO_Value;
		$doomed->load($row['id']);
		if ($doomed->value_text) {
			$doomed->delete();
		}
	}
}
