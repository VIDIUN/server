<?php
/**
 * @package api
 * @subpackage objects
 * @abstract
 */
abstract class VidiunDataCenterContentResource extends VidiunContentResource 
{
	public function getDc()
	{
		return vDataCenterMgr::getCurrentDcId();
	}

	/* (non-PHPdoc)
	 * @see VidiunObject::validateForUsage($sourceObject, $propertiesToSkip)
	 */
	public function validateForUsage($sourceObject, $propertiesToSkip = array())
	{
		parent::validateForUsage($sourceObject, $propertiesToSkip);
		
		$dc = $this->getDc();
		if($dc == vDataCenterMgr::getCurrentDcId())
			return;
			
		$remoteDCHost = vDataCenterMgr::getRemoteDcExternalUrlByDcId($dc);
		if($remoteDCHost)
			vFileUtils::dumpApiRequest($remoteDCHost);
			
		throw new VidiunAPIException(VidiunErrors::REMOTE_DC_NOT_FOUND, $dc);
	}
}