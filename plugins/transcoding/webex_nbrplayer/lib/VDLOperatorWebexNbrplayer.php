<?php
/**
 * @package plugins.webexNbrplayer
 * @subpackage lib
 */
class VDLOperatorWebexNbrplayer extends VDLOperatorBase {
	
    public function GenerateConfigData(VDLFlavor $design, VDLFlavor $target)
	{
		return null;
	}

    public function GenerateCommandLine(VDLFlavor $design, VDLFlavor $target, $extra=null)
    {
    	return '-Convert ' . VDLCmdlinePlaceholders::ConfigFileName;
    }
    
	/* ---------------------------
	 * CheckConstraints
	 */
	public function CheckConstraints(VDLMediaDataSet $source, VDLFlavor $target, array &$errors=null, array &$warnings=null)
	{
	    return parent::CheckConstraints($source, $target, $errors, $warnings);
	}

}

