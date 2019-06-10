<?php
/**
 * @package Var
 * @subpackage Partners
 */
class PartnerController extends Zend_Controller_Action
{

    public function init()
    {
        /* Initialize action controller here */
    }

    public function indexAction()
    {
        
        $this->_helper->redirector('list');
    }

    public function createAction()
	{
		$request = $this->getRequest();
		$client = Infra_ClientHelper::getClient();
		$form = new Form_PartnerCreate();
		
		$partner = Zend_Registry::get('config')->partner;
		
		 $this->view->errorDescription = "";
		
		if ($request->isPost())
		{
			if ($form->isValid($request->getPost()))
			{
				$partner = $form->getObject("Vidiun_Client_Type_Partner", $request->getPost());
				$templatePartnerId = $form->getValue("copyPartner");
				/* @var $partner Vidiun_Client_Type_Partner */
				if(is_array($partner->contentCategories))
					$partner->contentCategories = implode(',', $partner->contentCategories);
					
				$partner->description = "Multi-publishers console";
				$partner->type = Vidiun_Client_Enum_PartnerType::ADMIN_CONSOLE;
				
				try 
				{
				    $result = $client->partner->register($partner, null, $templatePartnerId);
    				// check for errors in partner.register
    				if ($client->isError($result)) 
    				{
    					if (strpos($result[0]['message'], 'already exists in system') !== false)
    						$form->getElement('admin_email')->addError('Email already exists');
    					else
    					    $this->view->errorDescription = 'An error occured: ' . $result[0]['message'];   
    				}
    				else
    				{
    					$this->_helper->redirector('list');
    				}
				}
				catch (Exception $e)
				{
				    $this->view->errorDescription = 'An error occured: ' . $e->getMessage();   
				}
				
			}
			else
			{
				$form->populate($request->getPost());
			}
		}
		
		$varConsoleFilter = new Vidiun_Client_VarConsole_Type_VarConsolePartnerFilter();
		$varConsoleFilter->groupTypeEq = Vidiun_Client_Enum_PartnerGroupType::TEMPLATE;
		$varConsoleFilter->statusEqual = Vidiun_Client_Enum_PartnerStatus::ACTIVE;
		$pager = new Vidiun_Client_Type_FilterPager();
		$templatePartnerList = $client->partner->listAction($varConsoleFilter, $pager);
		
		$providers = array();
		$providers[0] = $this->view->translate('partner-create default copy partner');
		foreach ($templatePartnerList->objects as $templatePartner)
		{
		    /* @var $templatePartner Vidiun_Client_Type_Partner */
		    $providers[$templatePartner->id] = $templatePartner->name;
		}
		
		$form->setProviders($providers);
		
		//If available sub-publisher quota was reached, submit button should be disabled.
		//Exclude publisher iteself, template sub-publisher and deleted sub-publisher
		$currentPartner = $client->partner->getInfo();
		$filter = new Vidiun_Client_VarConsole_Type_VarConsolePartnerFilter();
		$filter->idNotIn = $currentPartner->id;
		$filter->statusIn = implode(",", array (Vidiun_Client_Enum_PartnerStatus::ACTIVE, Vidiun_Client_Enum_PartnerStatus::BLOCKED));
		$filter->groupTypeEq = Vidiun_Client_Enum_PartnerGroupType::PUBLISHER;
		$subPublisherCount = $client->partner->count($filter);
		/* @var $currentPartner Vidiun_Client_Type_Partner */
		if ($currentPartner->publishersQuota - $subPublisherCount <= 0)
		{
    		$submitBtn = $form->getElement('submit');
            $submitBtn->setOptions(array(
                'disable' => array(1, 2)
            ));
		}
		
		$this->view->usedPublishers = $subPublisherCount;
		$this->view->remainingPublishers = $currentPartner->publishersQuota - $subPublisherCount > 0? $currentPartner->publishersQuota - $subPublisherCount : 0;
		
		$this->view->form = $form;
	}
    
    public function listAction ()
    {
        
        $request = $this->getRequest();
		$page = $this->_getParam('page', 1);
		$pageSize = $this->_getParam('pageSize', 10);
		
		// reset form url
		$action = $this->view->url(array('controller' => $request->getParam('controller'), 'action' => $request->getParam('action')), null, true);

		$client = Infra_ClientHelper::getClient();
		
		$currentPartner = $client->partner->getInfo();
		
		$form = new Form_PartnerFilter();
		$form->setAction($action);
		
		// init filter
		$partnerFilter = $this->getPartnerFilterFromRequest($request);
		
		$paginatorAdapter = new Infra_FilterPaginator($client->partner, "listAction", null, $partnerFilter);
		$paginator = new Infra_Paginator($paginatorAdapter, $request);
		$paginator->setCurrentPageNumber($page);
		$paginator->setItemCountPerPage($pageSize);
		
		// popule the form
		$form->populate($request->getParams());
		
		$this->view->currentPartnerId = Infra_AuthHelper::getAuthInstance()->getIdentity()->getPartnerId();
		
		// set view
		$this->view->form = $form;
		$this->view->paginator = $paginator;
		
    }
    
    private function getPartnerFilterFromRequest(Zend_Controller_Request_Abstract $request)
	{
		$filter = new Vidiun_Client_Type_PartnerFilter();
		$filterType = $request->getParam('filter_type');
		$filterInput = $request->getParam('filter_input');
		$filterIncludActive = $request->getParam('include_active');
		$filterIncludBlocked = $request->getParam('include_blocked');
		$filterIncludRemoved = $request->getParam('include_removed');
		
		if ($filterType == 'byid')
		{
			$filter->idIn = $filterInput;
		}
		else
		{
			if ($filterType == 'byname')
				$filter->nameLike = $filterInput;
			elseif ($filterType == 'free' && $filterInput)
				$filter->partnerNameDescriptionWebsiteAdminNameAdminEmailLike = $filterInput;
		}
		$statuses = array();
		if ($filterIncludActive)
			$statuses[] = Vidiun_Client_Enum_PartnerStatus::ACTIVE;
		if ($filterIncludBlocked)
			$statuses[] = Vidiun_Client_Enum_PartnerStatus::BLOCKED;
		if ($filterIncludRemoved)
			$statuses[] = Vidiun_Client_Enum_PartnerStatus::FULL_BLOCK;
		
		$statusIn = implode(',', $statuses);
		if ($statusIn != ''){
			$filter->statusIn = $statusIn;
		}else{
			$filter->statusIn = Vidiun_Client_Enum_PartnerStatus::ACTIVE . ',' . Vidiun_Client_Enum_PartnerStatus::BLOCKED;
		}
		 
		$filter->orderBy = Vidiun_Client_Enum_PartnerOrderBy::ID_DESC;
		return $filter;
	}
	
    public function updateStatusAction()
	{
		$this->_helper->viewRenderer->setNoRender();
		$partnerId = $this->_getParam('partner_id');
		$status = $this->_getParam('status');
		$client = Infra_ClientHelper::getClient();
		$varConsolePlugin = Vidiun_Client_VarConsole_Plugin::get($client);
		$varConsolePlugin->varConsole->updateStatus($partnerId, $status);
		echo $this->_helper->json('ok', false);
	}

    public function vmcRedirectAction()
	{
		$partnerId = $this->_getParam('partner_id');
		$vs = $this->generateAdminVs();
		if(!$vs)
			return;

		$url = $this->createVmcRedirectionUrl($vs, $partnerId);
		$this->getResponse()->setRedirect($url);
	}

	public function vmcNewRedirectAction()
	{
		$vs = $this->generateAdminVs();
		if(!$vs)
		{
			return;
		}

		$url = $this->createNewVmcRedirectionUrl($vs);
		$this->getResponse()->setRedirect($url);
	}

	private function generateAdminVs()
	{
		$impersonatedPartnerId = $this->_getParam('partner_id');
		$userId = $this->_getParam('user_id');
		$client = Infra_ClientHelper::getClient();
		$client->startMultiRequest();
		$client->partner->getInfo();
		if (!$userId)
		{
			$client->partner->get($impersonatedPartnerId);
		}

		$client->session->impersonate('{1:result:adminSecret}', $impersonatedPartnerId, $userId ? $userId : '{2:result:adminUserId}', Vidiun_Client_Enum_SessionType::ADMIN, '{1:result:id}', null, "disableentitlement");
		$result = $client->doMultiRequest();

		foreach($result as $resultItem)
		{
			if(is_a($resultItem, 'Exception'))
			{
				throw $resultItem;
			}
		}

		// The VS is always the last item received in the multi-request
		if(!$userId)
		{
			$userId = $result[1]->adminUserId;
		}

		$adminSecret = $result[0]->adminSecret;
		$partnerId =  $result[0]->id;
		
		if($userId != Zend_Auth::getInstance()->getIdentity()->getUser()->id)
			return $client->session->impersonate($adminSecret, $impersonatedPartnerId, $userId, Vidiun_Client_Enum_SessionType::ADMIN, $partnerId, null, "disableentitlement,enablechangeaccount:$impersonatedPartnerId");
		
		return end($result);
	}

	private static function getPartnersIdsFromPartnerListResponse($partnerListResponse)
	{
		$partnersId = array();
		foreach($partnerListResponse->objects as $partner)
			$partnersId[] = $partner->id;

		return  implode("/", $partnersId);
	}

	private function createVmcRedirectionUrl($vs, $partnerId)
	{
		$settings = Zend_Registry::get('config')->settings;
		if($settings->vmcUrl)
		{
			$url = $settings->vmcUrl;
		}
		else
		{
			$url = Infra_ClientHelper::getServiceUrl();
			$url .= '/index.php/vmc/extlogin';
		}

		$url .= '?vs='.$vs.'&partner_id='.$partnerId;
		return $url;
	}

	private function createNewVmcRedirectionUrl($vs)
	{
		$url = Infra_ClientHelper::getServiceUrl();
		if(substr($url, -1) == '/')
			$url = substr($url,0,-1);
		$url .= '/index.php/vmcng/actions/login-by-vs/'.$vs;
		return $url;
	}

	public function varConsoleRedirectAction()
	{
	    $request = $this->getRequest();
		$client = Infra_ClientHelper::getClient();
		$authorizedPartnerId = $this->_getParam('partner_id');
		
		$email = Infra_AuthHelper::getAuthInstance()->getIdentity()->getUser()->email;
		$password = Infra_AuthHelper::getAuthInstance()->getIdentity()->getPassword();
		$timezoneOffset = Infra_AuthHelper::getAuthInstance()->getIdentity()->getTimezoneOffset();
		
	    $adapter = new Vidiun_VarAuthAdapter();
	    $adapter->setCredentials($email, $password);
	    $adapter->setPartnerId($authorizedPartnerId);
	    $adapter->setTimezoneOffset($timezoneOffset);
		$auth = Infra_AuthHelper::getAuthInstance();
		$result = $auth->authenticate($adapter);
		
	    if ($result->isValid())
		{
			$this->_helper->redirector('list', 'partner');
		}
		else
		{
			throw new Exception("login failed");
		}
	}
	
	public function listByUserAction ()
	{
	    $request = $this->getRequest();
		$page = $this->_getParam('page', 1);
		$pageSize = $this->_getParam('pageSize', 500);
		$settings = Zend_Registry::get('config')->settings;
		// reset form url
		$action = $this->view->url(array('controller' => $request->getParam('controller'), 'action' => $request->getParam('action')), null, true);

		$client = Infra_ClientHelper::getClient();
		
		$form = new Form_PartnerFilter();
		$form->setAction($action);
		
		
		// get results and paginate
		//$systemPartnerPlugin = Vidiun_Client_SystemPartner_Plugin::get($client);
		$filter = new Vidiun_Client_VarConsole_Type_VarConsolePartnerFilter();
		if (isset($settings->requiredPermissions) && $settings->requiredPermissions)
		    $filter->partnerPermissionsExist = $settings->requiredPermissions;
		$filter->groupTypeIn = Vidiun_Client_Enum_PartnerGroupType::GROUP . "," . Vidiun_Client_Enum_PartnerGroupType::VAR_GROUP;
		$paginatorAdapter = new Infra_FilterPaginator($client->partner, "listPartnersForUser", null, $filter);
		$paginator = new Infra_Paginator($paginatorAdapter, $request);
		if ($paginator->getItemsCount() == 1)
		    $this->_helper->redirector('list', 'partner');
		$paginator->setCurrentPageNumber($page);
		$paginator->setItemCountPerPage($pageSize);
		
		// populate the form
		$form->populate($request->getParams());
		
		// set view
		$this->view->form = $form;
		$this->view->paginator = $paginator;
	}
	
    public function vmcUsersAction()
	{
		$this->_helper->layout->disableLayout();
		
		$partnerId = $this->_getParam('partner_id');
		if (!$partnerId) {
			//TODO: error
		}
		
		$page = $this->_getParam('page', 1);
		$pageSize = $this->_getParam('pageSize', 10);
		
		$filter = new Vidiun_Client_Type_UserFilter();
		$filter->isAdminEqual = true;
		$filter->partnerIdEqual = $partnerId;
		$filter->statusEqual = Vidiun_Client_Enum_UserStatus::ACTIVE;
		
		$client = Infra_ClientHelper::getClient();
		$paginatorAdapter = new Infra_FilterPaginator($client->user, "listAction", $partnerId, $filter);
		$paginator = new Infra_Paginator($paginatorAdapter);
		$paginator->setCurrentPageNumber($page);
		$paginator->setItemCountPerPage($pageSize);
		$paginator->setIndex(2);
		
		$this->view->partnerId = $partnerId;
		$this->view->paginator = $paginator;
	}
}

