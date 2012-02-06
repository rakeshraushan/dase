<?php

require_once 'Dase/DBO/Autogen/Item.php';

class Dase_DBO_Item extends Dase_DBO_Autogen_Item 
{

		public function removeFromSets()
		{
				$isi = new Dase_DBO_ItemsetItem($this->db);
				$isi->item_id = $this->id;
				foreach ($isi->findAll(1) as $doomed) {
						$doomed->delete();
				}
		}

		public function asArray($r)
		{
				$set = array();
				$set['id'] = $r->app_root.'/item/'.$this->name;
				$set['title'] = $this->title;
				$set['name'] = $this->name;
				$set['item_id'] = $this->id;
				if ($this->body) {
						$set['body'] = $this->body;
				}
				$set['created'] = $this->created;
				$set['updated'] = $this->updated;
				$set['links'] = array();
				$set['links']['self'] = $r->app_root.'/'.$this->url.'.json';
				if ($this->file_url) {
						$set['links']['file'] = $r->app_root.'/'.$this->file_url;
				}
				if ($this->thumbnail_url) {
						$set['links']['thumbnail'] = $r->app_root.'/'.$this->thumbnail_url;
				}
				if ($this->filesize) {
						$set['filesize'] = $this->filesize;
				}
				if ($this->mime) {
						$set['mime'] = $this->mime;
				}
				if ($this->width) {
						$set['width'] = $this->width;
				}
				if ($this->height) {
						$set['height'] = $this->height;
				}
				return $set;
		}

		public function asJson($r)
		{
				return Dase_Json::get($this->asArray($r));
		}
}
