<?php
/**
 * @package Core
 * @subpackage model.data
 * @abstract
 * @deprecated
 * 
 * Old restriction for backward compatibility
 */
abstract class vAccessControlRestriction extends vRule
{
	const RESTRICTION_TYPE_RESTRICT_LIST = 0;
	const RESTRICTION_TYPE_ALLOW_LIST = 1;
	
	/**
	 * 
	 * @var accessControl
	 */
	protected $accessControl;

	/**
	 * @param accessControl $accessControl
	 */
	public function __construct(accessControl $accessControl = null)
	{
		$scope = null;
		if($accessControl)
		{
			$this->accessControl = $accessControl;
			$scope = $accessControl->getScope();
		}
		parent::__construct($scope);
		$contexts = array(
			ContextType::PLAY, 
			ContextType::DOWNLOAD, 
		);
		$partnerId = $accessControl ? $accessControl->getPartnerId() : vCurrentContext::$vs_partner_id;
		$partner = PartnerPeer::retrieveByPK($partnerId);
		if($partner) {
			if($partner->getRestrictThumbnailByVs())
				$contexts[] = ContextType::THUMBNAIL;
			if($partner->getShouldApplyAccessControlOnEntryMetadata())
				$contexts[] = ContextType::METADATA;
		}
			
		$this->setContexts($contexts);
	}
	
	/**
	 * @param accessControl $accessControl
	 */
	public function setAccessControl(accessControl $accessControl)
	{
		$this->accessControl = $accessControl;
	}

	/* (non-PHPdoc)
	 * @see vRule::applyContext()
	 */
	public function applyContext(vContextDataResult $context)
	{
		$fulfilled = parent::applyContext($context);

		if($fulfilled)
			foreach($this->actions as $action)
				if($action instanceof vAccessControlPreviewAction)
					$context->setPreviewLength($action->getLimit());
			
		return $fulfilled;
	}
	
	public function __sleep()
	{
		$vars = get_class_vars('vAccessControlRestriction');
		unset($vars['accessControl']);
		return array_keys($vars);
	}
}

