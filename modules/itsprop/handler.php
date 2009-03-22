<?php

class Dase_ModuleHandler_Itsprop extends Dase_Handler {

	public $is_chair = false;
	public $resource_map = array(
		'test' => 'test',
		'/' => 'welcome',
		'index' => 'home',
		'home' => 'home',
		'home_form' => 'home_form',
		'welcome' => 'welcome',	
		'login' => 'login',
		'logout' => 'logout',
		'person/{eid}' => 'person',
		'person/{eid}/proposal_form' => 'proposal_form',
		'proposals' => 'proposals',
		'proposal/{serial_number}' => 'proposal',
		'proposal/{serial_number}/unarchiver' => 'proposal_unarchiver',
		'proposal/{serial_number}/archiver' => 'proposal_archiver',
		'proposal/{serial_number}/email' => 'email',
		'proposal/{serial_number}/eval' => 'proposal_eval',
		'proposal/{serial_number}/preview' => 'proposal_preview',
		'proposal/{serial_number}/courses' => 'proposal_courses',
		'proposal/{serial_number}/budget_items' => 'proposal_budget_items',
		'proposal/{serial_number}/{up_or_down}' => 'move_proposal',
		'persons' => 'persons',
		'departments' => 'departments',
		'department/{dept_id}' => 'department',
		'department/{dept_id}/proposals' => 'department_proposals',
		'department/{dept_id}/vision' => 'vision',
		'department/{dept_id}/vision/preview' => 'vision_preview',
		'service_pass/{serviceuser}' => 'service_pass',
	);

	public function setup($r)
	{
		$this->is_superuser = false;
		$this->is_eval = false;
		$this->is_chair = false;
		$this->db = $r->retrieve('db');
		if ('welcome' != $r->resource && 'login' != $r->resource && 'department_proposals' != $r->resource) {
			$this->user = $r->getUser('cookie',false);
			if (!$this->user) {
				$r->renderRedirect($r->app_root.'/modules/'.$r->module.'/welcome');
			} else {
				$eid=$this->user->eid;
				$chair_feed = Dase_Atom_Feed::retrieve($r->app_root. "/search.atom?itsprop.dept_chair_eid=$eid");
				if (count($chair_feed->entries)) {
					$r->set('chair_feed',$chair_feed);
					$r->set('is_chair',1);
					$this->is_chair = true;
				}

				$person_data = Dase_Json::toPhp(Dase_Http::get($r->app_root.'/search.json?itsprop.person_role=evaluator&itsprop.person_eid='.$eid.'&auth=http','pkeane','opendata'));
				if ($person_data['count']) {
					$r->set('is_evaluator',1);
					$this->is_eval = true;
				}

				$is_super = $this->_isSuperuser($r,$this->user->eid);
				if ($is_super) {
					$this->is_superuser = true;
					$r->set('is_superuser',1);
				} 
				$this->service_pass = $r->retrieve('config')->getServicePassword('itsprop');
			}
		}
	}

	private function _isSuperuser($r,$eid) 
	{
		$mans = Dase_Json::toPhp(file_get_contents($r->app_root.'/collection/itsprop/managers.json'));
		if (count($mans) && isset($mans[$eid]) && 'superuser' == $mans[$eid]) {
			return true;
		} else {
			return false;
		}
	}

	public function getDepartment($r)
	{
		$tpl = new Dase_Template($r,true);
		$tpl->assign('user',$this->user);
		if ($r->get('eid') != $this->user->eid) {
			if (!$r->is_superuser) {
				$r->renderError(401);
			}
		}
		$dept = Dase_Atom_Entry::retrieve($r->app_root. "/item/itsprop/dept-".$r->get('dept_id').".atom");
		if (is_numeric($dept)) {
			$r->renderError($dept);
		}
		$tpl->assign('dept',$dept);
		$tpl->assign('cola_dept',Dept::getDept($r->get('dept_id')));
		$r->renderResponse($tpl->fetch('dept.tpl'));
	}

	public function getVision($r)
	{
		if (!$this->is_chair) {
			$r->renderError(401,'must be department chair');
		}
		$tpl = new Dase_Template($r,true);
		$tpl->assign('user',$this->user);
		$dept = Dase_Atom_Entry::retrieve($r->app_root. "/item/itsprop/dept-".$r->get('dept_id').".atom");
		if (is_numeric($dept)) {
			$r->renderError($dept);
		}
		$tpl->assign('props_link',$r->app_root.'/modules/itsprop/department/'.$r->get('dept_id').'/proposals');
		$tpl->assign('dept',$dept);
		$r->renderResponse($tpl->fetch('vision.tpl'));
	}

	public function getVisionPreview($r)
	{
		if (!$this->is_chair) {
			$r->renderError(401,'must be department chair');
		}
		$tpl = new Dase_Template($r,true);
		$tpl->assign('user',$this->user);
		$dept = Dase_Atom_Entry::retrieve($r->app_root. "/item/itsprop/dept-".$r->get('dept_id').".atom");
		if (is_numeric($dept)) {
			$r->renderError($dept);
		}
		$props = Dase_Atom_Feed::retrieve($dept->getChildfeedLinkUrlByTypeAtom('proposal'));
		$props = $props->filterOnExists('proposal_submitted');
		$props->sortBy('proposal_chair_rank');
		$tpl->assign('props',$props);
		$tpl->assign('dept',$dept);
		$r->renderResponse($tpl->fetch('vision_preview.tpl'));
	}

	/*
	public function postToVision($r)
	{
		//from DASe
		if (!$this->is_chair) {
			$r->renderError(401,'must be department chair');
		}
		$u = $r->getUser();
		if (!$u->can('write',$this->tag)) {
			$r->renderError(401,$u->eid .' is not authorized to write this resource');
		}
		$sort_array = $r->get('set_sort_item',true);
		$this->tag->sort($sort_array);
		$http_pw = $u->getHttpPassword($r->retrieve('config')->getAuth('token'));
		$t = new Dase_Template($r);
		$feed_url = $r->app_root.'/tag/'.$this->tag->id.'.atom';
		$t->assign('tag_feed',Dase_Atom_Feed::retrieve($feed_url,$u->eid,$http_pw));
		$r->renderResponse($t->fetch('item_set/tag_sorter.tpl'));
	}
	 */

	public function getDepartmentProposals($r)
	{

		if (!$this->is_chair && !$this->is_eval && !$this->is_superuser) {
		//	$r->renderError(401,'not authorized');
		}
		$this->user = $r->getUser('cookie',false);
		if (!$this->user) {
			$r->renderResponse('please logout and login again');
		}
		$tpl = new Dase_Template($r,true);
		$tpl->assign('user',$this->user);
		$dept = Dase_Atom_Entry::retrieve($r->app_root. "/item/itsprop/dept-".$r->get('dept_id').".atom");
		if (is_numeric($dept)) {
			$r->renderError($dept);
		}
		$props = Dase_Atom_Feed::retrieve($dept->getChildfeedLinkUrlByTypeAtom('proposal'));
		$props = $props->filterOnExists('proposal_submitted');
		$props->sortBy('proposal_chair_rank');
		$id_set = '';
		foreach ($props->entries as $p) {
			$id_set .= $p->proposal_chair_rank['id'];
		}
		$sort_token = md5($id_set);
		$tpl->assign('props',$props);
		$tpl->assign('sort_token',$sort_token);
		$r->renderResponse($tpl->fetch('dept_props.tpl'));
	}

	public function postToEmail($r)
	{
		$container = trim(file_get_contents("php://input"));
		$sernum = $r->get('serial_number');
		$proposal = Dase_Atom_Entry_Item::retrieve($r->app_root.'/item/itsprop/'.$sernum.'.atom');
		$person = Dase_Atom_Entry::retrieve($r->app_root. "/item/itsprop/".$proposal->getAuthorName().".atom");
		//fragile?? (dept must be first parent)
		//$dept_array = $person->getParentLinkNodesByItemType('department');
		$parent = $proposal->getParentLinks();
		$title = 'Liberal Arts ITS Grant Proposal: '.$proposal->proposal_name['text'];
		$department = Dase_Atom_Entry_Item::retrieve($parent[0]['href'].'.atom');
		$email = $department->dept_chair_email['text'];
		$chair_name = $department->dept_chair['text'];
		$container = str_replace('&lt;','<',$container);
		$container = str_replace('&gt;','>',$container);
		$h2t = new html2text($container);
		$text = $h2t->get_text();
		$text = 
			"Dear Chair-\n\nPlease find below the 09-10 IT Grant Proposal Submitted by ".$person->person_name['text'].". Please visit the LAITS Proposal Site (http://www.laits.utexas.edu/itsprop) to rank proposals and add your vision statement accordingly.\n\n".$text;
	
		$header = "From: LAITS Grant Proposal_Application \r\n";
		//use $email when its for real
		Dase_Log::debug('sending email to '.$email);
		mail($email,$title,$text,$header);
		mail('pkeane@mail.utexas.edu','[DEBUG] '.$title,$text,$header);
		$submitter_email = $person->person_email['text'];
		$text = 
			"[The following message was sent to your department chair]\n\n".$text;
		mail($submitter_email,$title,$text,$header);
		//mail('mikehegedus@mail.utexas.edu','Proposal: '.$title,$text,$header);
		$r->renderOk('sent');
	}

	public function postToDepartment($r)
	{
		if (!$r->is_superuser) {
			$r->renderError(401);
		}
		$dept = Dase_Atom_Entry::retrieve($r->app_root. "/item/itsprop/dept-".$r->get('id').".atom");
		$metadata_array = $dept->getRawMetadata();

		$request_array = array(
			'dept_name' => $r->get('name'),
			'title' => $r->get('name'),
			'dept_id' => $r->get('id'),
			'dept_chair' => $r->get('chair'),
			'dept_chair_email' => $r->get('chair_email'),
			'dept_chair_eid' => $r->get('chair_eid'),
			'dept_display' => $r->get('display'),
		);

		if ('yes' == $r->get('display')) {
			$dept->setStatus('public');
		}

		if ('no' == $r->get('display')) {
			$dept->setStatus('archive');
		}

		//lookup chair by eid
		if ($dept->getValue('dept_chair_eid') != $r->get('chair_eid')) {
			$ldap = Utlookup::getRecord($r->get('chair_eid'));
			if ($ldap) {
				$request_array['dept_chair'] = $ldap['name'];
				$request_array['dept_chair_email'] = $ldap['email'];
			}
		}

		foreach ($request_array as $ascii => $val) {
			$metadata_array[$ascii] = array($val);
		}

		$dept->replaceMetadata($metadata_array);
		$dept->putToUrl($dept->getEditLink(),'itsprop',$this->service_pass);
		$this->_expireDaseSearchCache($r);
		$r->renderRedirect($r->app_root.'/modules/itsprop/department/'.$r->get('id'));
	}

	private function _expireDaseSearchCache($r)
	{
		$url = $r->app_root.'/search/recent';
		$ch = curl_init();
		// set URL and other appropriate options
		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "DELETE");
		curl_setopt($ch, CURLOPT_FOLLOWLOCATION,true);
		curl_setopt($ch, CURLOPT_USERPWD,"itsprop:$this->service_pass");
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		$response = curl_exec($ch);
		$info = curl_getinfo($ch);
		curl_close($ch);
		return $info['http_code'];
	}

	public function getPerson($r) 
	{
		$tpl = new Dase_Template($r,true);
		$tpl->assign('user',$this->user);
		if ($r->get('eid') != $this->user->eid) {
			if (!$r->is_superuser) {
				$r->renderError(401);
			}
		}
		$depts_json = file_get_contents($r->app_root.'/item_type/itsprop/department/dept_name/values.json?public_only=1');
		$depts = Dase_Json::toPhp($depts_json);
		$tpl->assign('depts', Dase_Json::toPhp($depts_json));
		$person = Dase_Atom_Entry::retrieve($r->app_root. "/item/itsprop/".$r->get('eid').".atom");
		if (is_numeric($person)) {
			$r->renderError($person);
		}
		$tpl->assign('person',$person);
		$r->renderResponse($tpl->fetch('person.tpl'));
	}

	public function getPersons($r) 
	{
		$tpl = new Dase_Template($r,true);
		$tpl->assign('user',$this->user);
		$tpl->assign('person', Dase_Atom_Feed::retrieve($r->app_root. "/item/itsprop/".$this->user->eid.".atom"));
		$tpl->assign('persons', Dase_Atom_Feed::retrieve($r->app_root. "/item_type/itsprop/person/items.atom"));
		$r->renderResponse($tpl->fetch('persons.tpl'));
	}

	public function postToPersons($r) 
	{
		$ldap = Utlookup::getRecord($r->get('eid'));
		if ($ldap) {
			$person = new Dase_Atom_Entry_Item;
			$person->setTitle($ldap['name']);
			$person->setItemType('person');
			//we set title so auto-titling works in DASe
			$person->addMetadata('title',$ldap['name']); 
			$person->addMetadata('person_name',$ldap['name']); 
			$person->addMetadata('person_eid',$ldap['eid']); 
			$person->addMetadata('person_email',$ldap['email']); 
			$person->addMetadata('person_phone',$ldap['phone']); 
			$person->addMetadata('person_lastname',$ldap['lastname']); 
			$person->setUpdated(date(DATE_ATOM));
			$person->postToUrl($r->app_root.'/collection/itsprop','itsprop',$this->service_pass,$ldap['eid']);
			$params['msg'] = 'added user '.$ldap['eid'];
		} else {
			$params['msg'] = 'did not find '.$r->get('eid').' in UT Directory';
		}
		$r->renderRedirect($r->app_root.'/modules/'.$r->module.'/persons',$params);
	}

	public function getDepartments($r) 
	{
		$tpl = new Dase_Template($r,true);
		$tpl->assign('user',$this->user);
		$tpl->assign('person', Dase_Atom_Entry::retrieve($r->app_root. "/item/itsprop/".$this->user->eid.".atom"));
		$tpl->assign('depts', Dase_Atom_Feed::retrieve($r->app_root. "/item_type/itsprop/department/items.atom?sort=dept_name"));
		$r->renderResponse($tpl->fetch('departments.tpl'));
	}

	public function getProposals($r) 
	{
		if (!$this->is_chair && !$this->is_eval && !$this->is_superuser) {
			$r->renderError(401,'not authorized');
		}
		$tpl = new Dase_Template($r,true);
		$tpl->assign('user',$this->user);
		$tpl->assign('proposals', Dase_Atom_Feed::retrieve($r->app_root. "/item_type/itsprop/proposal/items.atom"));
		$r->renderResponse($tpl->fetch('proposals.tpl'));
	}

	public function postToProposalArchiver($r)
	{
		$proposal = Dase_Atom_Entry::retrieve($r->app_root. "/item/itsprop/".$r->get('serial_number').".atom");
		$metadata_array = $proposal->getRawMetadata();
		$metadata_array['proposal_submitted'] = array(date(DATE_ATOM));
		//$metadata_array['proposal_chair_rank'] = array('&nbsp;');
		$proposal->replaceMetadata($metadata_array);
		$proposal->putToUrl($proposal->getEditLink(),'itsprop',$this->service_pass);
		$params['msg'] = "your proposal has been submitted";
		$r->renderRedirect($r->app_root.'/modules/itsprop/home',$params);
	}

	public function postToProposalUnarchiver($r)
	{
		$proposal = Dase_Atom_Entry::retrieve($r->app_root. "/item/itsprop/".$r->get('serial_number').".atom");
		$metadata_array = $proposal->getRawMetadata();
		$metadata_array['proposal_submitted'] = array(' ');
		$proposal->replaceMetadata($metadata_array);
		$proposal->putToUrl($proposal->getEditLink(),'itsprop',$this->service_pass);
		$r->renderOk('success');
	}

	public function postToPerson($r)
	{
		if ($r->get('eid') != $this->user->eid) {
			if (!$r->is_superuser) {
				$r->renderError(401);
			}
		}
		$person = Dase_Atom_Entry::retrieve($r->app_root. "/item/itsprop/".$r->get('eid').".atom");
		$metadata_array = $person->getRawMetadata();
		$dept_array = $person->getParentLinkNodesByItemType('department');
		if (count($dept_array)) {
			$dept = $dept_array[0];
			$dept->removeAttribute('href');
			$dept->setAttribute('href',$r->get('department'));
		} else {
			$person->addLink($r->get('department'),'http://daseproject.org/relation/parent');
		}
		if ($r->get('refresh')) {
			$ldap = Utlookup::getRecord($r->get('eid'));
			$request_array['title'] = $ldap['name']; 
			$request_array['person_name'] = $ldap['name']; 
			$request_array['person_eid'] = $ldap['eid']; 
			$request_array['person_email'] = $ldap['email']; 
			$request_array['person_phone'] = $ldap['phone']; 
			$request_array['person_lastname'] = $ldap['lastname']; 
		} else {
			$request_array = array(
				'person_name' => $r->get('name'),
				'person_eid' => $r->get('eid'),
				'person_email' => $r->get('email'),
				'person_phone' => $r->get('phone'),
			);
		}
		foreach ($request_array as $ascii => $val) {
			$metadata_array[$ascii] = array($val);
		}

		$person->replaceMetadata($metadata_array);
		$person->putToUrl($person->getEditLink(),'itsprop',$this->service_pass);
		$r->renderRedirect($r->app_root.'/modules/itsprop/person/'.$r->get('eid'));
	}

	public function getHome($r) 
	{
		$tpl = new Dase_Template($r,true);
		$tpl->assign('user',$this->user);
		$home = Dase_Atom_Entry::retrieve($r->app_root. "/item/itsprop/page-home.atom");
		$tpl->assign('home',$home);
		$r->renderResponse($tpl->fetch('home.tpl'));
	}

	public function getHomeForm($r) 
	{
		if (!$this->is_superuser) {
			$r->renderError(404);
		}
		$tpl = new Dase_Template($r,true);
		$tpl->assign('user',$this->user);
		$home = Dase_Atom_Entry::retrieve($r->app_root. "/item/tisprop/page-home.atom");
		if (!$home) {
			$page = new Dase_Atom_Entry_Item;
			$page->setTitle('homepage');
			$page->setItemType('page');
			$page->addMetadata('title','homepage'); 
			$page->addMetadata('page_uri','/home'); 
			$page->setUpdated(date(DATE_ATOM));
			$page->postToUrl($r->app_root.'/collection/itsprop','itsprop',$this->service_pass,'page-home');
		}
		$home = Dase_Atom_Entry::retrieve($r->app_root. "/item/itsprop/page-home.atom");
		$tpl->assign('home',$home);
		$r->renderResponse($tpl->fetch('home_form.tpl'));
	}

	public function postToHomeForm($r) 
	{
		if (!$this->is_superuser) {
			$r->renderError(404);
		}
		if ('cancel' != $r->get('cancel')) {
			$home = Dase_Atom_Entry::retrieve($r->app_root. "/item/itsprop/page-home.atom");
			$home->replaceContent($r->get('home_text'));
			$home->putToUrl($home->getEditLink(),'itsprop',$this->service_pass);
		}
		$r->renderRedirect($r->app_root.'/modules/itsprop/home');
	}

	public function getWelcome($r) 
	{
		Uteid::logout($r);
		//$user = $r->getUser();
		$tpl = new Dase_Template($r,true);
		$r->renderResponse($tpl->fetch('welcome.tpl'));
	}

	public function getProposalForm($r)
	{
		$tpl = new Dase_Template($r,true);
		$tpl->assign('user',$this->user);
		$depts_json = file_get_contents($r->app_root.'/item_type/itsprop/department/dept_name/values.json?public_only=1');
		$tpl->assign('depts', Dase_Json::toPhp($depts_json));
		$person = Dase_Atom_Entry::retrieve($r->app_root. "/item/itsprop/".$this->user->eid.".atom");
		$tpl->assign('person',$person);
		$r->renderResponse($tpl->fetch('proposal_form.tpl'));
	}

	public function getProposal($r)
	{
		$tpl = new Dase_Template($r,true);
		$tpl->assign('user',$this->user);
		$depts_json = file_get_contents($r->app_root.'/item_type/itsprop/department/dept_name/values.json?public_only=1');
		$tpl->assign('depts', Dase_Json::toPhp($depts_json));
		$proposal = Dase_Atom_Entry::retrieve($r->app_root. "/item/itsprop/".$r->get('serial_number').".atom");
		if (is_numeric($proposal)) {
			$r->renderResponse($tpl->fetch('proposal404.tpl'));
		}
		if ($proposal->proposal_submitted['text']) {
			$r->renderRedirect($r->app_root.'/modules/itsprop/proposal/'.$r->get('serial_number').'/preview');
		}

		//$person = Dase_Atom_Entry::retrieve($r->app_root. "/item/itsprop/".$this->user->eid.".atom");
		$person = Dase_Atom_Entry::retrieve($r->app_root. "/item/itsprop/".$proposal->getAuthorName().".atom");
		$tpl->assign('person',$person);

		$tpl->assign('courses',$proposal->getChildfeedLinkUrlByTypeJson('course'));
		$tpl->assign('budget_items',$proposal->getChildfeedLinkUrlByTypeJson('budget_item'));
		$tpl->assign('proposal',$proposal);
		$tpl->assign('previewLink',$r->app_root.'/modules/itsprop/proposal/'.$r->get('serial_number').'/preview');
		$r->renderResponse($tpl->fetch('proposal.tpl'));
	}

	public function getProposalPreview($r)
	{
		$tpl = new Dase_Template($r,true);
		$tpl->assign('user',$this->user);
		//$depts_json = file_get_contents($r->app_root.'/item_type/itsprop/department/dept_name/values.json');
		//$tpl->assign('depts', Dase_Json::toPhp($depts_json));
		$proposal = Dase_Atom_Entry::retrieve($r->app_root. "/item/itsprop/".$r->get('serial_number').".atom");
		if (is_numeric($proposal)) {
			$r->renderResponse($tpl->fetch('proposal404.tpl'));
		}

		$dept_array = $proposal->getParentLinks();
		$department = Dase_Atom_Entry_Item::retrieve($dept_array[0]['href'].'.atom');
		$chair_email = $department->dept_chair_email['text'];
		$chair_name = $department->dept_chair['text'];
		$tpl->assign('chair_email',$chair_email);
		$tpl->assign('chair_name',$chair_name);

		$person = Dase_Atom_Entry::retrieve($r->app_root. "/item/itsprop/".$proposal->getAuthorName().".atom");
		$tpl->assign('person',$person);
		$tpl->assign('courses',Dase_Json::toPhp(file_get_contents($proposal->getChildfeedLinkUrlByTypeJson('course'))));
		$budget_items = Dase_Json::toPhp(file_get_contents($proposal->getChildfeedLinkUrlByTypeJson('budget_item')));
		$grand_total = 0;
		$display_bud = array();
		foreach ($budget_items as $bud) {
			$p = $bud['metadata']['budget_item_price'];
			$q = $bud['metadata']['budget_item_quantity'];
			$bud['total'] = $p*$q;
			$grand_total += $bud['total'];
			$display_bud[] = $bud;

		}
		$tpl->assign('grand_total',$grand_total);
		$tpl->assign('budget_items',$display_bud);
		$tpl->assign('proposal',$proposal);
		$tpl->assign('propLink',$r->app_root.'/modules/itsprop/proposal/'.$r->get('serial_number'));
		$r->renderResponse($tpl->fetch('preview.tpl'));
	}

	public function getProposalEval($r)
	{
		$tpl = new Dase_Template($r,true);
		$tpl->assign('user',$this->user);
		$proposal = Dase_Atom_Entry::retrieve($r->app_root. "/item/itsprop/".$r->get('serial_number').".atom");
		if (is_numeric($proposal)) {
			$r->renderResponse($tpl->fetch('proposal404.tpl'));
		}

		$dept_array = $proposal->getParentLinks();
		$department = Dase_Atom_Entry_Item::retrieve($dept_array[0]['href'].'.atom');
		$chair_email = $department->dept_chair_email['text'];
		$chair_name = $department->dept_chair['text'];
		$tpl->assign('chair_email',$chair_email);
		$tpl->assign('chair_name',$chair_name);

		$person = Dase_Atom_Entry::retrieve($r->app_root. "/item/itsprop/".$proposal->getAuthorName().".atom");
		$tpl->assign('person',$person);
		$tpl->assign('courses',Dase_Json::toPhp(file_get_contents($proposal->getChildfeedLinkUrlByTypeJson('course'))));
		$budget_items = Dase_Json::toPhp(file_get_contents($proposal->getChildfeedLinkUrlByTypeJson('budget_item')));
		$grand_total = 0;
		$display_bud = array();
		foreach ($budget_items as $bud) {
			$p = $bud['metadata']['budget_item_price'];
			$q = $bud['metadata']['budget_item_quantity'];
			$bud['total'] = $p*$q;
			$grand_total += $bud['total'];
			$display_bud[] = $bud;

		}
		/* vision preview */

		$props = Dase_Atom_Feed::retrieve($department->getChildfeedLinkUrlByTypeAtom('proposal'));
		$props = $props->filterOnExists('proposal_submitted');
		$props->sortBy('proposal_chair_rank');
		$tpl->assign('props',$props);
		$tpl->assign('dept',$department);


		$tpl->assign('grand_total',$grand_total);
		$tpl->assign('budget_items',$display_bud);
		$tpl->assign('proposal',$proposal);
		$tpl->assign('propLink',$r->app_root.'/modules/itsprop/proposal/'.$r->get('serial_number'));
		$r->renderResponse($tpl->fetch('eval.tpl'));
	}

	public function postToProposalForm($r)
	{
		$prop_name = $r->get('proposal_name');
		if (!$prop_name) {
			$prop_name = 'default title';
		}
		$proposal = new Dase_Atom_Entry_Item;
		$proposal->setTitle($prop_name);
		$proposal->setItemType('proposal');
		$proposal->addAuthor($this->user->eid);
		$proposal->addMetadata('title',$prop_name); 
		$proposal->addMetadata('proposal_budget_description','enter budget description here'); 
		$proposal->addMetadata('proposal_collaborators','enter collaborators here'); 
		$proposal->addMetadata('proposal_description','enter description here'); 
		$proposal->addMetadata('proposal_name',$prop_name); 
		$proposal->addMetadata('proposal_previous_funding','enter previous funding here'); 
		$proposal->addMetadata('proposal_professional_assistance','enter professional assistance here'); 
		$proposal->addMetadata('proposal_faculty_workshop','no'); 
		$proposal->addMetadata('proposal_sta','no'); 
		$proposal->addMetadata('proposal_chair_rank','99'); 
		$proposal->addMetadata('proposal_chair_comments','&nbsp;'); 
		$proposal->addMetadata('proposal_project_type',$r->get('proposal_project_type')); 
		$proposal->addMetadata('proposal_renovation_description','enter renovation description here'); 
		$proposal->addMetadata('proposal_summary','enter summary here'); 

		$proposal->setUpdated(date(DATE_ATOM));
		//department is a url
		$proposal->addLink($r->get('department'),'http://daseproject.org/relation/parent');
		//person too
		$user_url =  $this->app_root.'/item/itsprop/'.$this->user->eid;
		$proposal->addLink($user_url,'http://daseproject.org/relation/parent');
		$result = $proposal->postToUrl($r->app_root.'/collection/itsprop','itsprop',$this->service_pass);
		if (Dase_Util::isUrl($result)) {
			$parts = explode('/',trim($result));
			$sernum = str_replace('.atom','',array_pop($parts));
			$r->renderRedirect($r->app_root.'/modules/itsprop/proposal/'.$sernum);
		} else {
			$r->renderError(400,$result);
		}
	}

	/** this will be called ajaxily */
	public function postToProposalCourses($r)
	{
		$course = new Dase_Atom_Entry_Item;
		$course->setTitle($r->get('course_title'));
		$course->setItemType('course');
		$course->addAuthor($this->user->eid);
		$course->addMetadata('title',$r->get('course_title')); 
		$course->addMetadata('course_title',$r->get('course_title')); 
		$course->addMetadata('course_number',$r->get('course_number')); 
		$course->addMetadata('course_frequency',$r->get('course_frequency')); 
		$course->addMetadata('course_enrollment',$r->get('course_enrollment')); 
		$course->setUpdated(date(DATE_ATOM));
		$course->addLink($r->get('proposal'),'http://daseproject.org/relation/parent');
		$result = $course->postToUrl($r->app_root.'/collection/itsprop','itsprop',$this->service_pass);
		if (Dase_Util::isUrl($result)) {
			$r->renderResponse($result);
		} else {
			$r->renderError(400,$result);
		}
	}

	public function postToProposalBudgetItems($r)
	{
		/** json experiment
		$set['desc'] = $r->get('budget_item_description');
		$set['price'] = $r->get('budget_item_price');
		$set['quant'] = $r->get('budget_item_quantity');
		$set['type'] = $r->get('budget_item_type');
		print Dase_Json::get($set);exit;
		 */
		$budget_item = new Dase_Atom_Entry_Item;
		$budget_item->setTitle($r->get('budget_item_title'));
		$budget_item->setItemType('budget_item');
		$budget_item->addAuthor($this->user->eid);
		$budget_item->addMetadata('title',$r->get('budget_item_description')); 
		$budget_item->addMetadata('budget_item_description',$r->get('budget_item_description')); 
		$budget_item->addMetadata('budget_item_price',$r->get('budget_item_price')); 
		$budget_item->addMetadata('budget_item_quantity',$r->get('budget_item_quantity')); 
		$budget_item->addMetadata('budget_item_type',$r->get('budget_item_type')); 
		$budget_item->setUpdated(date(DATE_ATOM));
		$budget_item->addLink($r->get('proposal'),'http://daseproject.org/relation/parent');
		$result = $budget_item->postToUrl($r->app_root.'/collection/itsprop','itsprop',$this->service_pass);
		if (Dase_Util::isUrl($result)) {
			$r->renderResponse($result);
		} else {
			$r->renderError(400,$result);
		}
	}

	public function getServicePass($r)
	{
		$secret = $r->getCookie('module');
		$suser = $r->get('serviceuser');
		//checks the secret that was saved in cookie upon login
		if ($secret == $r->retrieve('config')->getSecret($r->get('serviceuser'))) {
			$r->renderResponse($r->retrieve('config')->getServicePassword($suser));
		} else {
			$r->renderError(401);
		}
	}

	public function getLogin($r)
	{
		$user = Uteid::login($this->db,$r);
		$secret = $r->retrieve('config')->getSecret('itsprop'); 
		//this secret will be saved as a cookie on the client
		//ONLY upon successful eid login.  Now the client can
		//request the service password (the server will check for 
		//this token before it returns the service password).
		$r->setCookie('module',$secret);
		$ldap = Utlookup::getRecord($user->eid);

		$service_pass = $r->retrieve('config')->getServicePassword('itsprop');

		$person = new Dase_Atom_Entry_Item;
		$person->setTitle($ldap['name']);
		$person->setItemType('person');
		//we set title so auto-titling works in DASe
		$person->addMetadata('title',$ldap['name']); 
		$person->addMetadata('person_name',$ldap['name']); 
		$person->addMetadata('person_eid',$ldap['eid']); 
		$person->addMetadata('person_email',$ldap['email']); 
		$person->addMetadata('person_phone',$ldap['phone']); 
		$person->addMetadata('person_lastname',$ldap['lastname']); 
		$person->setUpdated(date(DATE_ATOM));
		//will automatically fail if user exists (409, I think)
		$person->postToUrl($r->app_root.'/collection/itsprop','itsprop',$service_pass,$user->eid);
		$r->renderRedirect($r->app_root.'/modules/'.$r->module.'/home');
	}

	public function getLogout($r)
	{
		Uteid::logout($r);
		$r->retrieve('cookie')->clear();
		$r->retrieve('cookie')->clearByType('module');
		$r->renderRedirect($r->app_root.'/modules/'.$r->module.'/welcome');
	}

}
