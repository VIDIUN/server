<?php
/**
 * @package api
 * @subpackage objects
 * @deprecated use VidiunRule instead
 */
class VidiunSessionRestriction extends VidiunBaseRestriction 
{
	/* (non-PHPdoc)
	 * @see VidiunBaseRestriction::toRule()
	 */
	public function toRule(VidiunRestrictionArray $restrictions)
	{	
		$rule = null;
		
		foreach($restrictions as $restriction)
		{
			if($restriction instanceof VidiunPreviewRestriction)
			{
				$rule = $restriction->toObject(new vAccessControlPreviewRestriction());
			}
		}
	
		if(!$rule)
			$rule = $this->toObject(new vAccessControlSessionRestriction());
		
		return $rule;
	}
}