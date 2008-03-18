<?php

class ContentHandler
{
	public static function index() {
		$params = Dase::instance()->params;
		$t = new Dase_Xslt;
		$t->stylesheet = XSLT_PATH.'home/index.xsl';

		if ($params['eid']) {
			$user = Dase_DB_DaseUser::get($params['eid']);
			$t->addSourceNode($user->asSimpleXml());
		}

		//get content from db
		$content = new Dase_DB_Content;
		$content->page = 'home';
		$content->orderBy('updated DESC');
		$content->findOne();
		$sxtext = simplexml_load_string($content->text);
		$t->addSourceNode($sxtext);
		//xhtml output:
		Dase::display($t->transform());
	}

	public static function edit() {
		$t = new Dase_Xslt;
		$t->stylesheet = XSLT_PATH.'home/edit.xsl';
		$params = Dase::instance()->params;

		if ($params['eid']) {
			$user = Dase_DB_DaseUser::get($params['eid']);
			$t->addSourceNode($user->asSimpleXml());
		}

		//get content from db
		$content = new Dase_DB_Content;
		$content->page = 'home';
		$content->orderBy('updated DESC');
		$content->findOne();
		$text = str_replace('<div>','',$content->text);
		$text = str_replace('</div>','',$text);
		$text = html_entity_decode($text);
		$t->set('line_count',$content->line_count);
		$t->set('page-content',$text);
		Dase::display($t->transform());
	}

	public static function update() {
		$params = Dase::instance()->params;
		$cancel = Dase::filterPost('cancel');
		if ('cancel' == $cancel) {
			Dase::redirect("u/{$params['eid']}/home");
		}
		$text = html_entity_decode($_POST['text']);
		$text = str_replace("&","&amp;",$text);
		$text = str_replace("'", "&apos;", $text);  
		$text = str_replace("\"", "&quot;", $text);
		include(DASE_PATH . '/lib/SafeHtmlChecker.class.php');
		$checker = new SafeHtmlChecker;
		$checker->check('<all>'.$text.'</all>');
		if ($checker->isOK()) {
			$content = new Dase_DB_Content;
			$content->page = 'home';
			$content->text = '<div>'.$text.'</div>';
			$content->updated = date(DATE_ATOM);
			$content->updated_by_eid = $params['eid'];
			$content->line_count = substr_count($text,"\n");
			$content->insert();
		}
		Dase::redirect("u/{$params['eid']}/home");
	}

	public static function welcome() {
		$t = new Dase_Xslt;
		$t->stylesheet = XSLT_PATH.'home/welcome.xsl';
		$t->set('user-eid','');
		$t->set('admin-user',0);

		//xhtml output:
		Dase::display($t->transform());
	}
}

