<?php
/**
 * @package api
 * @subpackage objects
 * @deprecated use VidiunRule instead
 * @abstract
 */
abstract class VidiunBaseRestriction extends VidiunObject
{
	/**
	 * @param VidiunRestrictionArray $restrictions enable one restriction to be affected by other restrictions
	 * @return vAccessControlRestriction
	 * @abstract must be implemented
	 */
	abstract public function toRule(VidiunRestrictionArray $restrictions);
}