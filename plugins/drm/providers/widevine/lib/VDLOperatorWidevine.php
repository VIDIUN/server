<?php
/**
 * @package plugins.widevine
 * @subpackage lib
 * 
 */
class VDLOperatorWidevine extends VDLOperatorBase 
{
 	/* ---------------------------
	 * GenerateCommandLine
	 */
    public function GenerateCommandLine(VDLFlavor $design, VDLFlavor $target, $extra=null)
	{
		return '';
	}
	
	public function CheckConstraints(VDLMediaDataSet $source, VDLFlavor $target, array &$errors=null, array &$warnings=null)
	{
	    return false;
	}
	
}
	