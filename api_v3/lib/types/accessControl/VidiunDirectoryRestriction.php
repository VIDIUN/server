<?php
/**
 * @package api
 * @subpackage objects
 * @deprecated
 */
class VidiunDirectoryRestriction extends VidiunBaseRestriction 
{
	/**
	 * Vidiun directory restriction type
	 * 
	 * @var VidiunDirectoryRestrictionType
	 */
	public $directoryRestrictionType;
	
	/* (non-PHPdoc)
	 * @see VidiunBaseRestriction::toRule()
	 */
	public function toRule(VidiunRestrictionArray $restrictions)
	{
		return null;
	}
}