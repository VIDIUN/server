<?php
/**
 * @package plugins.smoothProtect
 * @subpackage lib
 * 
 * SmoothProtect plugin 
 */
class VDLOperatorSmoothProtect extends VDLOperatorBase {
    public function __construct($id="smoothProtect.SmoothProtect", $name=null, $sourceBlacklist=null, $targetBlacklist=null) {
    	parent::__construct($id,$name,$sourceBlacklist,$targetBlacklist);
    }

	/* ---------------------------
	 * GenerateCommandLine
	 */
    public function GenerateCommandLine(VDLFlavor $design, VDLFlavor $target, $extra=null)
	{
		$cmdStr = " ".SmoothProtectPlugin::PARAMS_STUB;
		return $cmdStr;
	}
	
	/* ---------------------------
	 * CheckConstraints
	 */
	public function CheckConstraints(VDLMediaDataSet $source, VDLFlavor $target, array &$errors=null, array &$warnings=null)
	{
	    return parent::CheckConstraints($source, $target, $errors, $warnings);
	}
}
	