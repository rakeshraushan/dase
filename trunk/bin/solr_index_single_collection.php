<?php

include 'config.php';

$solr_url = 'harpo.laits.utexas.edu:8080/solr/update';
$solr_url = 'quickdraw.laits.utexas.edu:8080/solr/update';


$colls = array(

	'keanepj',
	'american_west',
	'vrc',
	'classics',
	'aem246',
	'akohl',
	'amazonian_survey',
	'american_photography',
	'american_politics',
	'ancient_meso',
	'anthropology',
	'arabic_dialects',
	'archivision',
	'asian_studies',
	'asl',
	'atr253',
	'awf2',
	'bibliography',
	'bibliography_2nd',
	'biodoc',
	'blanton',
	'brucerg',
	'bsls',
	'canzoni',
	'ces_audio',
	'chinese_326',
	'chinese_412k_audio',
	'chinese_412l',
	'chinese_506',
	'chinese_507',
	'cms3457',
	'cola_pubs',
	'constitution',
	'costume',
	'crucinia_drawings',
	'cumbojd',
	'cup',
	'dase_help',
	'dedwards',
	'dellanto',
	'demo',
	'dnr266',
	'early_american_history',
	'ee_jewish_history',
	'efossils',
	'ekids',
	'employee',
	'eng',
	'english',
	'eskeletons',
	'faculty',
	'farmhouse_drawings',
	'farmhouse_object_photos',
	'farmhouse_plans',
	'farmhouse_site_photos',
	'ferguson_royce',
	'french_312n',
	'french_322e',
	'french_612',
	'friesen',
	'gallery',
	'geodia',
	'germanic',
	'germanic_multimedia',
	'germans_from_russia',
	'gov310',
	'halekj',
	'ica',
	'iemeb555',
	'images_of_india',
	'incoronata_drawings',
	'incoronata_object_photos',
	'incoronata_plans',
	'itsprop',
	'jek234',
	'jm3832',
	'jr34487',
	'json_lists',
	'kerkhoff',
	'landmarks',
	'leoshkoj',
	'llorrac',
	'lm25645',
	'maryneu',
	'mdj325',
	'medieval',
	'mes',
	'metaponto',
	'metaponto_people_and_places',
	'metaponto_survey',
	'mexican_american_experience',
	'mjbailey',
	'mjc228',
	'mooretj',
	'mouserak',
	'neubert',
	'new_forms',
	'nicolopulos',
	'pantanello_drawings',
	'pantanello_object_photos',
	'pantanello_plans',
	'pantanello_site_photos',
	'plan2',
	'plan2_multimedia',
	'psychology_video',
	'rbc323',
	'rm7233',
	'runderwood',
	'salcedo',
	'saldone_drawings',
	'saldone_object_photos',
	'sample',
	'sandbox',
	'sayers',
	'schulman',
	'sejal_shah',
	'services',
	'sk4543',
	'smallda',
	'soa_india',
	'south_asia',
	'spanish',
	'stuart',
	'suicide_terrorism',
	'sulo',
	'suloni_collection',
	'tarl',
	'tbh',
	'tenbarge',
	'test',
	'texpol_cms',
	'texpol_image',
	'texpol_utopia',
	'textiles',
	'tgdp_test',
	'timemap',
	'timf',
	'tmm394',
	'ttb265',
	'twinam',
	'upload',
	'ut',
	'utunes',
	'video_catalog',
	'video_production',
	'vvaliav',
	'waller_creek',
	'wileydc',
	'wittcm',
	'yoruba',
	'zamora',
);

$colls = array('test','japanese_grammar');

$i = 0;

foreach ($colls as $coll) {

	$c = Dase_DBO_Collection::get($db,$coll);

	if ($c) {
		foreach ($c->getItems() as $item) {
			$item = clone($item);
			$i++;
			if (0 == $i%500) {
				//commit every 100 items
				print "\nCOMITTING CHANGES\n";
				print $c->collection_name.':'.$item->serial_number.':'.$item->buildSearchIndex(0,true);
			} else {
				print $c->collection_name.':'.$item->serial_number.':'.$item->buildSearchIndex(0,false);
			}
			print " $i\n";
		}
	}
}

