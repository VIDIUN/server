<?php
/**
 * Flavor Params Output service
 *
 * @service flavorParamsOutput
 * @package api
 * @subpackage services
 */
class FlavorParamsOutputService extends VidiunBaseService
{
	public function initService($serviceId, $serviceName, $actionName)
	{
		parent::initService($serviceId, $serviceName, $actionName);
		
		if($this->getPartnerId() != Partner::BATCH_PARTNER_ID && $this->getPartnerId() != Partner::ADMIN_CONSOLE_PARTNER_ID)
			throw new VidiunAPIException(VidiunErrors::SERVICE_FORBIDDEN, $this->serviceName.'->'.$this->actionName);
	}
	
	/**
	 * Get flavor params output object by ID
	 * 
	 * @action get
	 * @param int $id
	 * @return VidiunFlavorParamsOutput
	 * @throws VidiunErrors::FLAVOR_PARAMS_OUTPUT_ID_NOT_FOUND
	 */
	public function getAction($id)
	{
		$flavorParamsOutputDb = assetParamsOutputPeer::retrieveByPK($id);
		
		if (!$flavorParamsOutputDb)
			throw new VidiunAPIException(VidiunErrors::FLAVOR_PARAMS_OUTPUT_ID_NOT_FOUND, $id);
			
		$flavorParamsOutput = VidiunFlavorParamsFactory::getFlavorParamsOutputInstance($flavorParamsOutputDb->getType());
		$flavorParamsOutput->fromObject($flavorParamsOutputDb, $this->getResponseProfile());
		
		return $flavorParamsOutput;
	}
	
	/**
	 * List flavor params output objects by filter and pager
	 * 
	 * @action list
	 * @param VidiunFlavorParamsOutputFilter $filter
	 * @param VidiunFilterPager $pager
	 * @return VidiunFlavorParamsOutputListResponse
	 */
	function listAction(VidiunFlavorParamsOutputFilter $filter = null, VidiunFilterPager $pager = null)
	{
		if (!$filter)
			$filter = new VidiunFlavorParamsOutputFilter();
			
		if(!$pager)
		{
			$pager = new VidiunFilterPager();
		}
			
		$types = VidiunPluginManager::getExtendedTypes(assetParamsOutputPeer::OM_CLASS, assetType::FLAVOR);
		return $filter->getTypeListResponse($pager, $this->getResponseProfile(), $types);
	}
}
