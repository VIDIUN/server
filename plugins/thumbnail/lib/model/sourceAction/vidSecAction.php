<?php
/**
 * @package plugins.thumbnail
 * @subpackage model
 */

class vidSecAction extends sourceAction
{
	protected $second;
	protected $entrySource;

	protected $parameterAlias = array(
		"sec" => vThumbnailParameterName::SECOND,
		"s" => vThumbnailParameterName::SECOND,
	);

	protected function extractActionParameters()
	{
		$this->second = $this->getFloatActionParameter(vThumbnailParameterName::SECOND, 0);
		$this->entrySource = $this->getActionParameter(vThumbnailParameterName::SOURCE_ENTRY);
	}

	protected function validateInput()
	{
		if(!is_numeric($this->second) || $this->second < 0)
		{
			throw new VidiunAPIException(VidiunThumbnailErrors::BAD_QUERY, "Vid sec second cant be negative");
		}

		$this->validatePermissions();
	}


	protected function validatePermissions()
	{
		$partner = PartnerPeer::retrieveByPK( vCurrentContext::getCurrentPartnerId());
		if ($partner->getEnabledService(PermissionName::FEATURE_BLOCK_THUMBNAIL_CAPTURE))
		{
			throw new VidiunAPIException(VExternalErrors::NOT_ALLOWED_PARAMETER);
		}

		if ($enableCacheValidation)
		{
			$actionList = $secureEntryHelper->getActionList(RuleActionType::LIMIT_THUMBNAIL_CAPTURE);
			if ($actionList)
				VExternalErrors::dieError(VExternalErrors::NOT_ALLOWED_PARAMETER);
		}
	}
	/**
	 * @return Imagick
	 */
	protected function doAction()
	{
		// TODO: Implement doAction() method.
	}
}