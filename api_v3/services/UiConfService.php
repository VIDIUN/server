<?php
/**
 * UiConf service lets you create and manage your UIConfs for the various flash components
 * This service is used by the VMC-ApplicationStudio
 *
 * @service uiConf
 * @package api
 * @subpackage services
 */
class UiConfService extends VidiunBaseService 
{
	// use initService to add a peer to the partner filter
	/**
	 * @ignore
	 */
	public function initService($serviceId, $serviceName, $actionName)
	{
		parent::initService($serviceId, $serviceName, $actionName);
		if(strtolower($actionName) != 'listtemplates')
			$this->applyPartnerFilterForClass('uiConf'); 	
	}
	
	protected function partnerGroup($peer = null)
	{
		if ($this->actionName === 'get' || $this->actionName === 'clone')
			return '0';
		
		return parent::partnerGroup();
	}
	
	protected function vidiunNetworkAllowed($actionName)
	{
		if ($actionName === 'get') {
			return true;
		}
		if ($actionName === 'clone') {
			return true;
		}
		return parent::vidiunNetworkAllowed($actionName);
	}
	
	
	/**
	 * UIConf Add action allows you to add a UIConf to Vidiun DB
	 * 
	 * @action add
	 * @param VidiunUiConf $uiConf Mandatory input parameter of type VidiunUiConf
	 * @return VidiunUiConf
	 */
	function addAction( VidiunUiConf $uiConf )
	{
		$uiConf->validatePropertyNotNull('creationMode');
		if($uiConf->creationMode != VidiunUiConfCreationMode::ADVANCED && $uiConf->creationMode != VidiunUiConfCreationMode::WIZARD)
		{
			throw new VidiunAPIException ( "Should not create MANUAL ui_confs via the API!! MANUAL is deprecated" );
		}
		
		// if not specified set to true (default)
		if(is_null($uiConf->useCdn))
			$uiConf->useCdn = true;
		
		$dbUiConf = $uiConf->toUiConf();
		$dbUiConf->setPartnerId ( $this->getPartnerId() );
		$dbUiConf->save();
		
		$uiConf = new VidiunUiConf(); // start from blank
		$uiConf->fromObject($dbUiConf, $this->getResponseProfile());
		
		return $uiConf;
	}
	
	/**
	 * Update an existing UIConf
	 * 
	 * @action update
	 * @param int $id 
	 * @param VidiunUiConf $uiConf
	 * @return VidiunUiConf
	 *
	 * @throws APIErrors::INVALID_UI_CONF_ID
	 */	
	function updateAction( $id , VidiunUiConf $uiConf )
	{
		$dbUiConf = uiConfPeer::retrieveByPK( $id );
		
		if ( ! $dbUiConf )
			throw new VidiunAPIException ( APIErrors::INVALID_UI_CONF_ID , $id );
		
		$dbUiConf = $uiConf->toUpdatableObject($dbUiConf);
		
		$dbUiConf->save();
		$uiConf->fromObject($dbUiConf, $this->getResponseProfile());
		
		return $uiConf;
	}	

	/**
	 * Retrieve a UIConf by id
	 * 
	 * @action get
	 * @param int $id 
	 * @return VidiunUiConf
	 *
	 * @throws APIErrors::INVALID_UI_CONF_ID
	 */		
	function getAction(  $id )
	{
		$dbUiConf = uiConfPeer::retrieveByPK( $id );
		
		if ( ! $dbUiConf )
			throw new VidiunAPIException ( APIErrors::INVALID_UI_CONF_ID , $id );
		$uiConf = new VidiunUiConf();
		$uiConf->fromObject($dbUiConf, $this->getResponseProfile());
		
		return $uiConf;
	}

	/**
	 * Delete an existing UIConf
	 * 
	 * @action delete
	 * @param int $id
	 *
	 * @throws APIErrors::INVALID_UI_CONF_ID
	 */		
	function deleteAction(  $id )
	{
		$dbUiConf = uiConfPeer::retrieveByPK( $id );
		
		if ( ! $dbUiConf )
			throw new VidiunAPIException ( APIErrors::INVALID_UI_CONF_ID , $id );
		
		$dbUiConf->setStatus ( uiConf::UI_CONF_STATUS_DELETED );

		$dbUiConf->save();
	}

	/**
	 * Clone an existing UIConf
	 * 
	 * @action clone
	 * @param int $id 
	 * @return VidiunUiConf
	 *
	 * @throws APIErrors::INVALID_UI_CONF_ID
	 */	
	// TODO - get the new data of uiConf - will help override the parameters without needing to call update 
	function cloneAction( $id ) // , VidiunUiConf $_uiConf )
	{
		$dbUiConf = uiConfPeer::retrieveByPK( $id );
		
		if ( ! $dbUiConf )
			throw new VidiunAPIException ( APIErrors::INVALID_UI_CONF_ID , $id );
		$ui_conf_verride_params = new uiConf();
		$ui_conf_verride_params->setPartnerId( $this->getPartnerId() );
		$ui_conf_verride_params->setDisplayInSearch(1);  // the cloned ui_conf should NOT be a template
			
		$uiConfClone = $dbUiConf->cloneToNew ( $ui_conf_verride_params );

		$uiConf = new VidiunUiConf();
		$uiConf->fromObject($uiConfClone, $this->getResponseProfile());
		
		return $uiConf;
	}
	
	/**
	 * retrieve a list of available template UIConfs
	 *
	 * @action listTemplates
	 * @param VidiunUiConfFilter $filter
	 * @param VidiunFilterPager $pager
	 * @return VidiunUiConfListResponse
	 */
	function listTemplatesAction(VidiunUiConfFilter $filter = null , VidiunFilterPager $pager = null)
	{
		$templatePartnerId = 0;
		if ($this->getPartnerId() !== NULL)
		{
		        $partner = PartnerPeer::retrieveByPK($this->getPartnerId());
		        $templatePartnerId = $partner ? $partner->getTemplatePartnerId() : 0;
		}
		
		$templateCriteria = new Criteria();
		$templateCriteria->add(uiConfPeer::DISPLAY_IN_SEARCH , mySearchUtils::DISPLAY_IN_SEARCH_VIDIUN_NETWORK , Criteria::GREATER_EQUAL);
		$templateCriteria->addAnd(uiConfPeer::PARTNER_ID, $templatePartnerId);
		
		if (!$filter)
		        $filter = new VidiunUiConfFilter;
		$uiConfFilter = new uiConfFilter ();
		$filter->toObject( $uiConfFilter );
		$uiConfFilter->attachToCriteria( $templateCriteria);
		
		$count = uiConfPeer::doCount( $templateCriteria );
		if (!$pager)
		        $pager = new VidiunFilterPager ();
		$pager->attachToCriteria( $templateCriteria );
		$list = uiConfPeer::doSelect( $templateCriteria );
		$newList = VidiunUiConfArray::fromDbArray($list, $this->getResponseProfile());
		$response = new VidiunUiConfListResponse();
		$response->objects = $newList;
		$response->totalCount = $count;
		return $response;	
	}
	
	/**
	 * Retrieve a list of available UIConfs
	 * 
	 * @action list
	 * @param VidiunUiConfFilter $filter
	 * @param VidiunFilterPager $pager
	 * @return VidiunUiConfListResponse
	 */		
	function listAction( VidiunUiConfFilter $filter = null , VidiunFilterPager $pager = null)
	{
	    myDbHelper::$use_alternative_con = myDbHelper::DB_HELPER_CONN_PROPEL2;
	    
		if (!$filter)
			$filter = new VidiunUiConfFilter;
		$uiConfFilter = new uiConfFilter ();
		$filter->toObject( $uiConfFilter );
		
		$c = new Criteria();
		$uiConfFilter->attachToCriteria( $c );
		$count = uiConfPeer::doCount( $c );
		if (! $pager)
			$pager = new VidiunFilterPager ();
		$pager->attachToCriteria( $c );
		$list = uiConfPeer::doSelect( $c );
		
		$newList = VidiunUiConfArray::fromDbArray($list, $this->getResponseProfile());
		
		$response = new VidiunUiConfListResponse();
		$response->objects = $newList;
		$response->totalCount = $count;
		
		return $response;
	}
	
	/**
	 * Retrieve a list of all available versions by object type
	 * 
	 * @action getAvailableTypes
	 * @return VidiunUiConfTypeInfoArray
	 */
	function getAvailableTypesAction()
	{
		$flashPath = myContentStorage::getFSContentRootPath() . myContentStorage::getFSFlashRootPath();
		$flashPath = realpath($flashPath);
		$uiConf = new uiConf();
		$dirs = $uiConf->getDirectoryMap();
		$swfNames = $uiConf->getSwfNames();
		
		$typesInfoArray = new VidiunUiConfTypeInfoArray();
		foreach($dirs as $objType => $dir)
		{
			$typesInfo = new VidiunUiConfTypeInfo();
			$typesInfo->type = $objType;
			$typesInfo->directory = $dir;
			$typesInfo->filename = isset($swfNames[$objType]) ? $swfNames[$objType] : '';
			$versions = array();
			$path = $flashPath . '/' . $dir . '/';
			if(!file_exists($path) || !is_dir($path))
			{
				VidiunLog::err("Path [$path] does not exist");
				continue;
			}
				
			$path = realpath($path);
			$files = scandir($path);
			if(!$files)
			{
				VidiunLog::err("Could not scan directory [$path]");
				continue;
			}
				
			foreach($files as $file)
			{
				if (is_dir(realpath($path . '/' . $file)) && strpos($file, 'v') === 0)
					$versions[] = $file;
			}
			rsort($versions);
			
			$versionsObjectArray = new VidiunStringArray();
			foreach($versions as $version)
			{
				$versionString = new VidiunString();
				$versionString->value = $version;
				$versionsObjectArray[] = $versionString;
			}
		
			$typesInfo->versions = $versionsObjectArray;
			$typesInfoArray[] = $typesInfo;
		}
		return $typesInfoArray;
	}
}
