<?php
$curr_dir = dirname(__FILE__);
require_once($curr_dir.'/../lib/Dase/Atom.php');
require_once($curr_dir.'/../lib/Dase/Atom/Feed.php');
require_once($curr_dir.'/../lib/Dase/Atom/Entry.php');
require_once($curr_dir.'/../lib/Dase/Atom/Entry/Item.php');
require_once($curr_dir.'/../lib/Dase/Atom/Feed/Item.php');

class FMXMLException extends Exception {
    function __construct($message, $code = 0) {
        parent::__construct('FMXMLException: '.$message, $code);

    }
}

class FMXMLField {

    private $name;
    private $empty_ok;
    private $max_repeat = 1;
    private $type = 'text';

    function __construct($field_name = '') {
        $this->name = $field_name;
    }

    function __toString() {
        return $this->getName();
    }

    public function setEmptyOK($ok = true) {
        $this->empty_ok = $ok !== 'NO';
        return $this;
    }

    public function getEmptyOK() {
        return $this->empty_ok;
    }

    public function setMaxRepeat($max) {
        $this->max_repeat = $max;
        return $this;
    }

    public function getMaxRepeat() {
        return $this->max_repeat;
    }

    public function setType($type) {
        $this->type = strtolower($type);
        return $this;
    }

    public function getType() {
        return $this->type;
    }

    public function setName($name) {
        $this->name = $name;
    }

    public function getName() {
        return $this->name;
    }
}

class FMXMLReader {
    
    private $filename;
    private $collection_url;
    private $xml;
    private $fields = array();
    private $id_col_name;
    private $title_col_name;

    const AS_CVS = 'CVS';
    const AS_ATOM = 'Atom';

   function __construct($filename, $collection_url = '') {
       $this->filename = $filename;
       $this->setCollectionUrl($collection_url);
       if($xml = file_get_contents($filename)) {
           $this->sx = new SimpleXMLElement($xml);
       } else {
           throw new FMXMLException('Could not open file: '.$filename);
       }
   }

   public function loadFieldDescriptors() {
       $fields = array();
       foreach($this->sx->METADATA->FIELD as $field_data) {
           $field = new FMXMLField((string)$field_data['NAME']);
           $field->setType((string)$field_data['TYPE'])->setEmptyOK((string)$field_data['EMPTYOK']);
           $field->setMaxRepeat((string)$field_data['MAXREPEAT']);
           $fields[] = $field;
       }
       $this->fields = $fields;
       if(!$this->title_col_name) $this->title_col_name = (string)$this->fields[0];
       if(!$this->id_col_name) $this->id_col_name = (string)$this->fields[0];
       return $this;
   }

   public function getCollectionUrl() {
       return $this->collection_url;
   }

   public function setCollectionUrl($collection_url) {
       $this->collection_url = substr($collection_url, -1) == '/' ? $collection_url : $collection_url.'/';
       return $this;
   }

   public function getFields() {
       return $this->fields;
   }

   public function setTitleField($col) {
       $fields = array();
       foreach($this->fields as $f => $field) $fields[] = (string)$field;
       if(in_array($col, $fields)) $this->title_col_name = $col;
       return $this;
   }

   public function getTitleField() {
       return $this->title_col_name;
   }
   
   public function setIdField($col) {
       $fields = array();
       foreach($this->fields as $f => $field) $fields[] = (string)$field;
       if(in_array($col, $fields)) $this->id_col_name = $col;
       return $this;
   }

   public function getIdField() {
       return $this->id_col_name;
   }



   public function parse($output = self::AS_ATOM) {
       if(!is_callable(array($this, 'formatAs'.$output))) throw new FMXMLException('Invalid output format');
       $recs = array();
       foreach($this->sx->RESULTSET->ROW as $row) {
           $col_num = 0;
           $rec = new stdClass();
           foreach($row->COL as $col) {
               $rec->{$this->fields[$col_num]} = (string)$col->DATA;
               $col_num++;
           }
           $recs[] = $rec;
       }
       return $this->{'formatAs'.$output}($recs);
   }

   public function formatAsAtom($recs) {
       $d = 'http://daseproject.org/ns/1.0';
       $output_dir = './fmxml_atom/'.date('Ymd_U').'/';
       if(!file_exists($output_dir)) if(!mkdir($output_dir, 0777, true)) die('Could not make ouput directory: '.$output_dir."\n");
       if(!is_writeable($output_dir)) die('Cannot write to '.$output_dir."\n");
       $orphaned_rows = 0;
       $migrated = 0;
       foreach($recs as $rec) {
           if(!$rec->{$this->id_col_name}) {
               print "--Skipping row without id: \n".print_r($rec, true)."\n";
               file_put_contents($output_dir.'00_ORPHANED.txt', print_r($rec, true), FILE_APPEND);
               $orphaned_rows++;
               continue;
           }
           $filename = preg_replace('/[^A-Za-z0-9_-]+/', '', $rec->{$this->id_col_name});
           $entry = new Dase_Atom_Entry_Item();
           $entry->addAuthor('DASe (Digital Archive Services', 'http://daseproject.org');
           $entry->setContent($rec->{$this->id_col_name});
           $entry->setId($this->getCollectionUrl().$rec->{$this->id_col_name});
           $entry->setTitle($rec->{$this->title_col_name});
           $entry->setUpdated(date(DATE_ATOM));
           foreach(get_object_vars($rec) as $prop => $val) {
               if($val !== 0 && !$val) continue;
               $entry->addElement(
                   'd:'.preg_replace('/[^a-z_]+/ ', '', str_replace(' ', '_', strtolower($prop))), 
                   $val, 
                   $d
                )->setAttribute('d:label',ucwords($prop));
           }
           //print $entry->asXML();
           print "Writing file {$output_dir}{$rec->{$this->id_col_name}}.atom.entry...\n";
           file_put_contents($output_dir.$rec->{$this->id_col_name}.'.atom.entry', $entry->asXML());
           $manifest .= 'Details follow:'."\n";
           $migrated++;
       }
       $manifest = '--- '.date('Y.m.d@H:i:s').': '.$this->filename."\n";
       $manifest .= 'Generated '.$migrated.' atom documents from FileMaker XML.'."\n";
       if($orphaned_rows > 0) {
           $manifest .= $orphaned_rows.' rows could not be moved.'."\n";
           $manifest .= 'Find details of orphaned rows in 00_ORPHANED.txt.'."\n";
       }
       file_put_contents($output_dir.'00_MANIFEST.txt', $manifest);
       return true;
   }

   public static function open($filename) {
       if(is_readable($filename)) {
           try {
               return new FMXMLReader($filename);
           } catch(Exception $e) {
               die($e->getMessage());
           }
       } else {
           die('Could not read file '.$filename."\n");
       }
   }
}

if(isset($argv[1]) && isset($argv[2])) {
    list($self, $file, $collection_url, $id_field, $title_field) = $argv;
    print "Loading $argv[1]...\n";
    $fmxml = FMXMLReader::open($file)->setCollectionUrl($collection_url);
    $fmxml->loadFieldDescriptors();
    if($id_field) $fmxml->setIdField($id_field);
    if($title_field) $fmxml->setTitleField($title_field);
    print "Title field set to {$fmxml->getTitleField()}.\n";
    print "ID field set to {$fmxml->getIdField()}.\n";
    $fmxml->parse();
} else {
    die('Usage: php -f class.fmxmlreader.php file.xml http://collection/url ["idfield"] ["titlefield"].'."\n");
}
?>
