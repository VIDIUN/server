<?php
/**
 * Thumbnail Params Output service
 *
 * @service thumbParamsOutput
 * @package api
 * @subpackage services
 */
class ThumbParamsOutputService extends VidiunBaseService
{
	public function initService($serviceId, $serviceName, $actionName)
	{
		parent::initService($serviceId, $serviceName, $actionName);
		
		if($this->getPartnerId() != Partner::BATCH_PARTNER_ID && $this->getPartnerId() != Partner::ADMIN_CONSOLE_PARTNER_ID)
			throw new VidiunAPIException(VidiunErrors::SERVICE_FORBIDDEN, $this->serviceName.'->'.$this->actionName);
	}
	
	/**
	 * Get thumb params output object by ID
	 * 
	 * @action get
	 * @param int $id
	 * @return VidiunThumbParamsOutput
	 * @throws VidiunErrors::THUMB_PARAMS_OUTPUT_ID_NOT_FOUND
	 */
	public function getAction($id)
	{
		$thumbParamsOutputDb = assetParamsOutputPeer::retrieveByPK($id);
		
		if (!$thumbParamsOutputDb)
			throw new VidiunAPIException(VidiunErrors::THUMB_PARAMS_OUTPUT_ID_NOT_FOUND, $id);
			
		$thumbParamsOutput = new VidiunThumbParamsOutput();
		$thumbParamsOutput->fromObject($thumbParamsOutputDb, $this->getResponseProfile());
		
		return $thumbParamsOutput;
	}
	
	/**
	 * List thumb params output objects by filter and pager
	 * 
	 * @action list
	 * @param VidiunThumbParamsOutputFilter $filter
	 * @param VidiunFilterPager $pager
	 * @return VidiunThumbParamsOutputListResponse
	 */
	function listAction(VidiunThumbParamsOutputFilter $filter = null, VidiunFilterPager $pager = null)
	{
		if (!$filter)
			$filter = new VidiunThumbParamsOutputFilter();
			
		if(!$pager)
		{
			$pager = new VidiunFilterPager();
		}
			
		$types = VidiunPluginManager::getExtendedTypes(assetParamsOutputPeer::OM_CLASS, assetType::THUMBNAIL);
		return $filter->getTypeListResponse($pager, $this->getResponseProfile(), $types);
	}
}
