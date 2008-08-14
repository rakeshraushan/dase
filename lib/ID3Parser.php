<?php

/**
 * author: Reed Underwood
 *
 **/

class ID3Parser {

    private static $header_fields = array(
        'TIT1' => 'content group',
        'TIT2' => 'title',
        'TIT3' => 'subtitle',
        'TLEN' => 'length',
        'TALB' => 'album',
        'TPE1' => 'artist',
        'TPE2' => 'band',
        'TPE3' => 'conductor',
        'TPE4' => 'mix artist',
        'TCOM' => 'composer',
        'TRCK' => 'track',
        'TDAT' => 'date',
        'TIME' => 'time',
        'TYER' => 'year',
        'TEXT' => 'text author/lyricist',
        'TLAN' => 'language',
        'TCON' => 'content type',
        'TPOS' => 'part in set',
        'TSRC' => 'isrc',
        'TRDA' => 'recording dates',
        'TXXX' => 'user text'
    );

    private $filename;
    private $fh;

    function __construct($filename = false) {
        $this->filename = $filename;
    }

    public function open($filename = false) {
        $filename = $filename ? $filename : $this->filename;
        if(is_readable($filename)) {
            $this->fh = fopen($filename, 'rb');
            return $this;
        }
        else return false;
    }

    public function parse() {
        $header = array_merge(array('tag' => fread($this->fh, 3)), unpack('nver/Cflags/Nsize', fread($this->fh, 7)));
        $id3 = new stdClass();
        while(ftell($this->fh) < $header['size']) {
            $frame = array_merge(array('tag' => trim(fread($this->fh, 4))), unpack('Nsize/Hflags', fread($this->fh, 6)));
            if(!$frame['tag'] || !$frame['size']) break;
            $frame['payload'] = trim(fread($this->fh, $frame['size']));
            if(isset(self::$header_fields[$frame['tag']])) $id3->{self::$header_fields[$frame['tag']]} = trim($frame['payload']);
        }
        return $id3;
    }
}

if(isset($argc) && $argc > 1) {
    try {
        $p = new ID3Parser($argv[1]);
        $p->open();
        print_r($p->parse());
    } catch(Exception $e) {
        die((string)$e);
    }
}
