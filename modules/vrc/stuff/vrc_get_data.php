<?php

$host = "SQL01.austin.utexas.edu:1036";
$name = "vrc_live";
$user = "dasevrc";
$pass = "d453vrc";

$pdo = new PDO("dblib:host=$host;dbname=$name", $user, $pass);

$sql = "
	SELECT  
	acc_mainentry, 
	acc_title1, 
	acc_alttitle, 
	acc_location, 
	acc_cat1, 
	acc_cat2, 
	acc_cat3, 
	acc_title2,
	acc_box, 
	acc_cycle,  
	mat_desc, 
	med_name,
	acc_unknown_medium, 
	mus_name, 
	mus_city,
	acc_unknown_museum, 
	acc_keyword, 
	acc_ref, 
	acc_source, 
	req_desc,
	acc_d, 
	acc_dimension, 
	acc_other_mus, 
	acc_cty_name_fk,
	acc_slide_status, 
	f_name, 
	acc_digital_num, 
	acc_dig_size, 
	acc_dig_type, 
	acc_num_PK, 
	acc_mainentry_d,
	c_slide,
	acc_role_name_fk,
	acc_item,
	acc_modified
	FROM tblAccession 
	LEFT JOIN tblMediumLU 
	ON tblAccession.acc_med_id_fk=tblMediumLU.med_id_PK 
	LEFT JOIN tblRequest 
	ON tblAccession.acc_req_num_Fk=tblRequest.req_num_PK 
	LEFT JOIN tblMaterialLU 
	ON tblAccession.acc_material=tblMaterialLU.mat_abbr_PK 
	LEFT JOIN tblMuseumLU
	ON tblAccession.acc_mus_id_fk=tblMuseumLU.mus_id_PK 
	LEFT JOIN tblFormatLU 
	ON tblAccession.acc_mus_id_fk=tblFormatLU.f_id_PK 
	LEFT JOIN tblCreator 
	ON tblAccession.acc_c_id_fk=tblCreator.c_id_PK 
	WHERE acc_digital_num != ''
	";

$sql = "
	SELECT  
	acc_digital_num, 
	acc_num_PK, 
	DATEDIFF(d,acc_modified,CURRENT_TIMESTAMP) as age 
	FROM tblAccession 
	WHERE acc_digital_num != ''
	ORDER BY age
	";

$writer = new XMLWriter();
$writer->openMemory();
$writer->setIndent(true);
$writer->startDocument('1.0','UTF-8');
$writer->startElement('items');

$st = $pdo->prepare($sql);
$st->setFetchMode(PDO::FETCH_ASSOC);
$st->execute();
while ($row = $st->fetch()) {
	$set = array();
	foreach($row as $key => $val) {
		$val = trim(mb_convert_encoding($val, "UTF-8", "cp1252"));
		$set[$key] = $val;
	}

	$writer->startElement('item');
	$writer->writeAttribute('serial_number',$set['acc_num_PK']);
	$writer->writeAttribute('digital_file',$set['acc_digital_num']);
	if ($set['age']) {
		$writer->writeAttribute('age',$set['age']);
	}

	/*
	foreach ($set as $att => $value) {
		if ($value) {
			$writer->startElement('metadata');
			$writer->writeAttribute('attribute_ascii_id',$att);
			$writer->text($value);
			$writer->endElement();
		}
	}
	 */
	$writer->endElement();
}
$writer->endElement();
$writer->endDocument();
file_put_contents('vrc.xml',$writer->flush(true));

