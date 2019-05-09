<?php
/**
 * @package plugins.document
 * @subpackage lib
 */
class VDLTranscoderThumbAssetsGenerator extends VDLOperatorBase{
	
	public function __construct($id, $name=null, $sourceBlacklist=null, $targetBlacklist=null) {
    	
		parent::__construct($id,$name,$sourceBlacklist,$targetBlacklist);
    }
	
	/* (non-PHPdoc)
	 * @see VDLOperatorBase::GenerateCommandLine()
	 */
	public function GenerateCommandLine(VDLFlavor $design, VDLFlavor $target, $extra = null) {
		return null;
	}
}
