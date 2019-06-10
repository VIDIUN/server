<?php
/**
 * @package api
 * @subpackage objects
 * @deprecated use VidiunRule instead
 */
class VidiunPreviewRestriction extends VidiunSessionRestriction 
{
	/**
	 * The preview restriction length 
	 * 
	 * @var int
	 */
	public $previewLength;
	
	private static $mapBetweenObjects = array
	(
		"previewLength",
	);
	
	public function getMapBetweenObjects()
	{
		return array_merge(parent::getMapBetweenObjects(), self::$mapBetweenObjects);
	}
	
	/* (non-PHPdoc)
	 * @see VidiunBaseRestriction::toRule()
	 */
	public function toRule(VidiunRestrictionArray $restrictions)
	{
		// Preview restriction became a rule action, it's not a rule.
		return null;
	}
}