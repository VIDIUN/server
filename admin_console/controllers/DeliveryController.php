<?php

/**
 * @package Admin
 * @subpackage DeliveryProfile
 */
class DeliveryController extends Zend_Controller_Action
{
	
	public function assignDeliveryProfileAction()
	{
		$this->_helper->layout->disableLayout();
		$partnerId = $this->_getParam('partnerId');
		$storageId = $this->_getParam('storageId');
		$deliveryType = $this->_getParam('deliveryType');
		$streamerType = $this->_getParam('streamerType');
		$currentDps = $this->_getParam('currentDeliveryProfiles');
	
		$client = Infra_ClientHelper::getClient();
		$options = $this->getDeliveryProfiles($client, $partnerId, $streamerType, null, $deliveryType);
		$selected = $this->getDeliveryProfiles($client, $partnerId, $streamerType, $currentDps, $deliveryType);

		$this->view->possibleValues = array_diff_key($options, $selected);
		$this->view->selectedValues = $selected;
	
	}
	
	public function deliveryProfilesConfigurationAction()
	{
	
		$request = $this->getRequest();
		$page = $this->_getParam('page', 1);
		$pageSize = $this->_getParam('pageSize', 10);
	
		$client = Infra_ClientHelper::getClient();
		$form = new Form_PartnerIdFilter();
		$form->populate($request->getParams());
		
		$newForm = new Form_NewDeliveryProfile();
		$newForm->populate($request->getParams());
	
		$action = $this->view->url(array('controller' => 'delivery', 'action' => 'delivery-profiles-configuration'), null, true);
		$form->setAction($action);
	
		$partnerId = null;
		if ($request->getParam('filter_input') != '') {
			$partnerId = $request->getParam('filter_input');
			$newForm->getElement('newPartnerId')->setValue($partnerId);
		}
		
		$filter = new Vidiun_Client_Type_DeliveryProfileFilter();
		$filter->partnerIdEqual = $partnerId;
	
		// get results and paginate
		$paginatorAdapter = new Infra_FilterPaginator($client->deliveryProfile, "listAction", $partnerId, $filter);
		$paginator = new Infra_Paginator($paginatorAdapter, $request);
		$paginator->setCurrentPageNumber($page);
		$paginator->setItemCountPerPage($pageSize);
	
		// popule the form
		$form->populate($request->getParams());
	
		// set view
		$this->view->form = $form;
		$this->view->newForm = $newForm;
		$this->view->paginator = $paginator;
	
	}
	
	protected function getDeliveryProfiles($client, $partnerId, $streamerType, $dpIds = null, $deliveryType = 'VOD') {
	
		$options = array();
		$deliveryProfileService = new Vidiun_Client_DeliveryProfileService($client);
	
		Infra_ClientHelper::impersonate($partnerId);
		$filter = new Vidiun_Client_Type_DeliveryProfileFilter();
		if($dpIds) {
			if(empty($dpIds))
				return $options;
			$filter->idIn = $dpIds;
		}
		
		$filter->streamerTypeEqual = $streamerType;
		$filter->isLive = $deliveryType === "Live" ? true : false;
		$filter->statusIn = Vidiun_Client_Enum_DeliveryStatus::ACTIVE . "," . Vidiun_Client_Enum_DeliveryStatus::STAGING_OUT;
		
		$pager = new Vidiun_Client_Type_FilterPager();
		$pager->pageSize = 500;
		
		$dpsResponse = $deliveryProfileService->listAction($filter, $pager);
		Infra_ClientHelper::unimpersonate();
	
	
		if(!$dpsResponse->totalCount)
			return $options;
	
		foreach($dpsResponse->objects as $deliveryProfile) {
			$name = $deliveryProfile->id . " : " . $deliveryProfile->name;
			$options[$deliveryProfile->id] = array("name" => $name, "id" => $deliveryProfile->id);
		}
		
		// sort options by the order passed via dpIds
		if ($dpIds)
		{
		    $sortedOptions = array();
		
		    $dpIdsArray = explode(",", $dpIds);
		    foreach($dpIdsArray as $dpId)
		        $sortedOptions[$dpId] = $options[$dpId];
		    $options = $sortedOptions;
		}
				
		return $options;
	}
	
	protected function getDeliveryProfileForm($type) {
		switch ($type) {
			case Vidiun_Client_Enum_DeliveryProfileType::GENERIC_HLS:
			case Vidiun_Client_Enum_DeliveryProfileType::GENERIC_HLS_MANIFEST:
				return new Form_Delivery_DeliveryProfileGenericAppleHttp();
			case Vidiun_Client_Enum_DeliveryProfileType::GENERIC_HDS:
			case Vidiun_Client_Enum_DeliveryProfileType::GENERIC_HDS_MANIFEST:
				return new Form_Delivery_DeliveryProfileGenericHds();
			case Vidiun_Client_Enum_DeliveryProfileType::GENERIC_HTTP:
					return new Form_Delivery_DeliveryProfileGenericHttp();
			case Vidiun_Client_Enum_DeliveryProfileType::RTMP:
			case Vidiun_Client_Enum_DeliveryProfileType::LIVE_RTMP:
				return new Form_Delivery_DeliveryProfileRtmp();
			case Vidiun_Client_Enum_DeliveryProfileType::AKAMAI_HTTP:
				return new Form_Delivery_DeliveryProfileAkamaiHttp();
			case Vidiun_Client_Enum_DeliveryProfileType::AKAMAI_HDS:
				return new Form_Delivery_DeliveryProfileAkamaiHds();
			case Vidiun_Client_Enum_DeliveryProfileType::AKAMAI_HLS_MANIFEST:
				return new Form_Delivery_DeliveryProfileAkamaiAppleHttpManifest();
			case Vidiun_Client_Enum_DeliveryProfileType::LIVE_HLS:
				return new Form_Delivery_DeliveryProfileLiveAppleHttp();
			case Vidiun_Client_Enum_DeliveryProfileType::GENERIC_SS:
				return new Form_Delivery_DeliveryProfileGenericSilverLight();
			case Vidiun_Client_Enum_DeliveryProfileType::GENERIC_RTMP:
				return new Form_Delivery_DeliveryProfileGenericRtmp();
			case Vidiun_Client_Enum_DeliveryProfileType::VOD_PACKAGER_HLS_MANIFEST:
			case Vidiun_Client_Enum_DeliveryProfileType::VOD_PACKAGER_HLS:
				return new Form_Delivery_DeliveryProfileVodPackagerHls();
			case Vidiun_Client_Enum_DeliveryProfileType::VOD_PACKAGER_DASH:
				return new Form_Delivery_DeliveryProfileVodPackagerPlayServer();
			case Vidiun_Client_Enum_DeliveryProfileType::VOD_PACKAGER_MSS:
				return new Form_Delivery_DeliveryProfileVodPackagerPlayServer();
			case Vidiun_Client_Enum_DeliveryProfileType::LIVE_PACKAGER_HLS:
				return new Form_Delivery_DeliveryProfileLivePackagerHls();
			case Vidiun_Client_Enum_DeliveryProfileType::LIVE_PACKAGER_HDS:
			case Vidiun_Client_Enum_DeliveryProfileType::LIVE_PACKAGER_DASH:
			case Vidiun_Client_Enum_DeliveryProfileType::LIVE_PACKAGER_MSS:
				return new Form_Delivery_DeliveryProfileLivePackager();
			default:
				return new Form_Delivery_DeliveryProfileConfiguration();
		}
	}
	
	protected function getDeliveryProfileClass($type) {
		switch ($type) {
			case Vidiun_Client_Enum_DeliveryProfileType::GENERIC_HLS:
			case Vidiun_Client_Enum_DeliveryProfileType::GENERIC_HLS_MANIFEST:
				return 'Vidiun_Client_Type_DeliveryProfileGenericAppleHttp';
			case Vidiun_Client_Enum_DeliveryProfileType::GENERIC_HDS:
			case Vidiun_Client_Enum_DeliveryProfileType::GENERIC_HDS_MANIFEST:
				return 'Vidiun_Client_Type_DeliveryProfileGenericHds';
			case Vidiun_Client_Enum_DeliveryProfileType::GENERIC_HTTP:
				return 'Vidiun_Client_Type_DeliveryProfileGenericHttp';
			case Vidiun_Client_Enum_DeliveryProfileType::RTMP:
			case Vidiun_Client_Enum_DeliveryProfileType::LIVE_RTMP:
				return 'Vidiun_Client_Type_DeliveryProfileRtmp';
			case Vidiun_Client_Enum_DeliveryProfileType::AKAMAI_HTTP:
				return 'Vidiun_Client_Type_DeliveryProfileAkamaiHttp';
			case Vidiun_Client_Enum_DeliveryProfileType::AKAMAI_HDS:
				return 'Vidiun_Client_Type_DeliveryProfileAkamaiHds';
			case Vidiun_Client_Enum_DeliveryProfileType::AKAMAI_HLS_MANIFEST:
				return 'Vidiun_Client_Type_DeliveryProfileAkamaiAppleHttpManifest';
			case Vidiun_Client_Enum_DeliveryProfileType::LIVE_HLS:
				return 'Vidiun_Client_Type_DeliveryProfileLiveAppleHttp';
			case Vidiun_Client_Enum_DeliveryProfileType::GENERIC_SS:
				return 'Vidiun_Client_Type_DeliveryProfileGenericSilverLight';
			case Vidiun_Client_Enum_DeliveryProfileType::GENERIC_RTMP:
				return 'Vidiun_Client_Type_DeliveryProfileGenericRtmp';
			case Vidiun_Client_Enum_DeliveryProfileType::VOD_PACKAGER_HLS_MANIFEST:
			case Vidiun_Client_Enum_DeliveryProfileType::VOD_PACKAGER_HLS:
				return 'Vidiun_Client_Type_DeliveryProfileVodPackagerHls';
			case Vidiun_Client_Enum_DeliveryProfileType::VOD_PACKAGER_DASH:
				return 'Vidiun_Client_Type_DeliveryProfileVodPackagerPlayServer';
			case Vidiun_Client_Enum_DeliveryProfileType::VOD_PACKAGER_MSS:
				return 'Vidiun_Client_Type_DeliveryProfileVodPackagerPlayServer';
			case Vidiun_Client_Enum_DeliveryProfileType::LIVE_PACKAGER_HLS:
				return "Vidiun_Client_Type_DeliveryProfileLivePackagerHls";
			case Vidiun_Client_Enum_DeliveryProfileType::LIVE_PACKAGER_HDS:
			case Vidiun_Client_Enum_DeliveryProfileType::LIVE_PACKAGER_DASH:
			case Vidiun_Client_Enum_DeliveryProfileType::LIVE_PACKAGER_MSS:
				return 'Vidiun_Client_Type_DeliveryProfileLivePackager';
			default:
				return 'Vidiun_Client_Type_DeliveryProfile';
		}
	}
	
	protected function getTokenizerForm($type) {
		switch($type) {
			case 'Null':
				return new Form_Delivery_DeliveryProfileNullTokenizer();
			case 'Vidiun_Client_Type_UrlTokenizerAkamaiSecureHd':
				return new Form_Delivery_UrlTokenizerAkamaiSecureHd();
			case 'Vidiun_Client_Type_UrlTokenizerLevel3':
				return new Form_Delivery_UrlTokenizerLevel3();
			case 'Vidiun_Client_Type_UrlTokenizerLimeLight':
				return new Form_Delivery_UrlTokenizerLimeLight();
			case 'Vidiun_Client_Type_UrlTokenizerAkamaiHttp':
				return new Form_Delivery_UrlTokenizerAkamaiHttp();
			case 'Vidiun_Client_Type_UrlTokenizerAkamaiRtmp':
				return new Form_Delivery_UrlTokenizerAkamaiRtmp();
			case 'Vidiun_Client_Type_UrlTokenizerAkamaiRtsp':
				return new Form_Delivery_UrlTokenizerAkamaiRtsp();
			case 'Vidiun_Client_Type_UrlTokenizerBitGravity':
				return new Form_Delivery_UrlTokenizerBitGravity();
			case 'Vidiun_Client_Type_UrlTokenizerCloudFront':
					return new Form_Delivery_UrlTokenizerCloudFront();
			case 'Vidiun_Client_Type_UrlTokenizerVnpt':
					return new Form_Delivery_UrlTokenizerVnpt();
			case 'Vidiun_Client_Type_UrlTokenizerCht':
					return new Form_Delivery_UrlTokenizerLimeLight();
			case 'Vidiun_Client_Type_UrlTokenizerChinaCache':
					return new Form_Delivery_UrlTokenizerChinaCache();
			case 'Vidiun_Client_Type_UrlTokenizerWowzaSecureToken':
					return new Form_Delivery_UrlTokenizerWowzaSecureToken();
				
			default:
				return VidiunPluginManager::loadObject('Form_Delivery_DeliveryProfileTokenizer', $type, array());
		}
	}
	
	protected function getRecognizerForm($type) {
		if($type == 'Null')
			return new Form_Delivery_DeliveryProfileNullRecognizer();
		return new Form_Delivery_DeliveryProfileRecognizer();
	}
	
	/**
	 * We've decided to use an hard coded list. In the future, we might want to change it.
	 */
	protected function getTokenizerClasses() {
	
		$tokenizer = array();
		$tokenizer['Null'] = $this->view->translate('No Tokenizer');
		$tokenizer['Vidiun_Client_Type_UrlTokenizerLevel3'] = $this->view->translate('Vidiun_Client_Type_UrlTokenizerLevel3');
		$tokenizer['Vidiun_Client_Type_UrlTokenizerLimeLight'] = $this->view->translate('Vidiun_Client_Type_UrlTokenizerLimeLight');
		$tokenizer['Vidiun_Client_Type_UrlTokenizerAkamaiHttp'] = $this->view->translate('Vidiun_Client_Type_UrlTokenizerAkamaiHttp');
		$tokenizer['Vidiun_Client_Type_UrlTokenizerAkamaiRtmp'] = $this->view->translate('Vidiun_Client_Type_UrlTokenizerAkamaiRtmp');
		$tokenizer['Vidiun_Client_Type_UrlTokenizerAkamaiRtsp'] = $this->view->translate('Vidiun_Client_Type_UrlTokenizerAkamaiRtsp');
		$tokenizer['Vidiun_Client_Type_UrlTokenizerBitGravity'] = $this->view->translate('Vidiun_Client_Type_UrlTokenizerBitGravity');
		$tokenizer['Vidiun_Client_Type_UrlTokenizerAkamaiSecureHd'] = $this->view->translate('Vidiun_Client_Type_UrlTokenizerAkamaiSecureHd');
		$tokenizer['Vidiun_Client_Type_UrlTokenizerCloudFront'] = $this->view->translate('Vidiun_Client_Type_UrlTokenizerCloudFront');
		$tokenizer['Vidiun_Client_Type_UrlTokenizerVnpt'] = $this->view->translate('Vidiun_Client_Type_UrlTokenizerVnpt');
		$tokenizer['Vidiun_Client_Type_UrlTokenizerCht'] = $this->view->translate('Vidiun_Client_Type_UrlTokenizerCht');
		$tokenizer['Vidiun_Client_Type_UrlTokenizerChinaCache'] = $this->view->translate('Vidiun_Client_Type_UrlTokenizerChinaCache');
		$tokenizer['Vidiun_Client_Type_UrlTokenizerWowzaSecureToken'] = $this->view->translate('Vidiun_Client_Type_UrlTokenizerWowzaSecureToken');
		
		// Plugins
		$tokenizer['Vidiun_Client_Type_UrlTokenizerUplynk'] = $this->view->translate('Vidiun_Client_Type_UrlTokenizerUplynk');
		$tokenizer['Vidiun_Client_Type_UrlTokenizerVelocix'] = $this->view->translate('Vidiun_Client_Type_UrlTokenizerVelocix');
		
		return $tokenizer;
	}
	
	protected function getRecognizerClasses() {
		$recognizer = array();
		$recognizer['Null'] = $this->view->translate('No Recognizer');
		$recognizer['Vidiun_Client_Type_UrlRecognizer'] = $this->view->translate('Vidiun_Client_Type_UrlRecognizer');
		return $recognizer;
	}
	
	public function configureDeliveryProfileAction()
	{
		$this->_helper->layout->disableLayout();
		$partnerId = $this->_getParam('partnerId');
		$deliveryProfileId = $this->_getParam('deliveryProfileId');
		$type = $this->_getParam('type');
		$tokenizerClz = $this->_getParam('tokenizerClz');
		$recognizerClz = $this->_getParam('recognizerClz');
	
		$editMode = false;
	
		// Retrieve delivery profile if DP id is given
		$client = Infra_ClientHelper::getClient();
		$deliveryProfile = null;
		if ($deliveryProfileId)
		{
			Infra_ClientHelper::impersonate($partnerId);
			try
			{
				$deliveryProfile = $client->deliveryProfile->get($deliveryProfileId);
			}
			catch (Exception $e)
			{
				Infra_ClientHelper::unimpersonate();
				throw $e;
			}
			Infra_ClientHelper::unimpersonate();
			$type = $deliveryProfile->type;
		}
	
		$form = $this->getDeliveryProfileForm($type);
		$tokenizerForm = $this->getTokenizerForm($tokenizerClz);
		
		if(is_null($tokenizerForm))
			throw new Exception("Can't instanstiate tokenizer form of type $tokenizerClz");
		$tokenizerForm->updateTokenizerOptions($this->getTokenizerClasses());
		$form->addSubForm($tokenizerForm, "tokenizer");
		
		$recognizerForm = $this->getRecognizerForm($recognizerClz);
		if(is_null($recognizerForm))
			throw new Exception("Can't instanstiate recognizer from of type $recognizerClz");
		$recognizerForm->updateRecognizerOptions($this->getRecognizerClasses());
		$form->addSubForm($recognizerForm, "recognizer");
	
		$request = $this->getRequest();
		$form->populate($request->getParams());
	
		$request = $this->getRequest();
	
		$pager = new Vidiun_Client_Type_FilterPager();
		$pager->pageSize = 500;
		if (!$deliveryProfileId) //new
		{
			$partnerId = $request->getParam('new_partner_id');
			$form->getElement('partnerId')->setValue($partnerId);
		}
		else
		{
			if (!$request->isPost())
				$form->populateFromObject($deliveryProfile, false);
		}
	
		$form->getElement('partnerId')->setAttrib('readonly',true);
	
		if ($request->isPost())
		{
			$request = $this->getRequest();
			$formData = $request->getPost();
			
			if ($form->isValid($formData))
			{
				$this->view->formValid = true;
				$form->populate($formData);
	
				$deliveryProfileClass = $this->getDeliveryProfileClass($type);
				$deliveryFromForm = $form->getObject($deliveryProfileClass, $formData, false, false);
	
				Infra_ClientHelper::impersonate($deliveryFromForm->partnerId);
				$deliveryFromForm->partnerId = null;
				if (!$deliveryProfileId)
				{
					$client->deliveryProfile->add($deliveryFromForm);
				}
				else
				{
					$client->deliveryProfile->update($deliveryProfileId, $deliveryFromForm);
				}
			}
			else
			{
				$this->view->formValid = false;
				$form->populate($formData);
			}
		}
	
		$this->view->form = $form;
	}
	
	public function getTokenizerFormAction()
	{
		$this->_helper->layout->disableLayout();
		$type = $this->getRequest()->getParam('tokenizerClz');
		$form = $this->getTokenizerForm($type);
		if(is_null($form))
			throw new Exception("Can't instanstiate tokenizer form of type $form");
		
		$this->view->form = $form;
		$this->view->form->updateTokenizerOptions($this->getTokenizerClasses());
		$this->view->form->getElement("objectType")->setValue($type);
	}
	
	public function getRecognizerFormAction()
	{
		$this->_helper->layout->disableLayout();
		$type = $this->getRequest()->getParam('recognizerClz');
		$form = $this->getRecognizerForm($type);
		if(is_null($form))
			throw new Exception("Can't instanstiate recognizer form of type $form");
	
		$this->view->form = $form;
		$this->view->form->updateRecognizerOptions($this->getRecognizerClasses());
		$this->view->form->getElement("objectType")->setValue($type);
	}
	
	public function updateDeliveryProfileStatusAction() 
	{
		$request = $this->getRequest();
		$status = $request->getParam('status');
		$partnerId =  $request->getParam('partnerId');
		$deliveryProfileId =  $request->getParam('deliveryProfileId');
		
		$client = Infra_ClientHelper::getClient();
		$deliveryProfileService = new Vidiun_Client_DeliveryProfileService($client);
		
		Infra_ClientHelper::impersonate($partnerId);
		
		$deliveryProfile = new Vidiun_Client_Type_DeliveryProfile();
		$deliveryProfile->status = $status;
		$deliveryProfileService->update($deliveryProfileId, $deliveryProfile);
		
		Infra_ClientHelper::unimpersonate();
		echo $this->_helper->json('ok', false);
	}
}
