<?php
/**
 * @package plugins.drm
 * @subpackage api.objects
 */
class VidiunAccessControlDrmPolicyAction extends VidiunRuleAction
{
	/**
	 * Drm policy id
	 * 
	 * @var int
	 */
	public $policyId;

	private static $mapBetweenObjects = array
	(
		'policyId',
	);

	/**
	 * Init object type
	 */
	public function __construct() 
	{
		$this->type = DrmAccessControlActionType::DRM_POLICY;
	}
	
	public function getMapBetweenObjects()
	{
		return array_merge(parent::getMapBetweenObjects(), self::$mapBetweenObjects);
	}
	
	/* (non-PHPdoc)
	 * @see VidiunObject::toObject()
	 */
	public function toObject($dbObject = null, $skip = array())
	{
		if(!$dbObject)
			$dbObject = new vAccessControlDrmPolicyAction();
			
		return parent::toObject($dbObject, $skip);
	}
}
