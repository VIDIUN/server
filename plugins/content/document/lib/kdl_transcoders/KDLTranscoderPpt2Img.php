<?php
/**
 * @package plugins.document
 * @subpackage lib
 */
class VDLTranscoderPpt2Img extends VDLOperatorBase{
	
	public function __construct($id, $name=null, $sourceBlacklist=null, $targetBlacklist=null) {
    	
		parent::__construct($id,$name,$sourceBlacklist,$targetBlacklist);
    }
	
	/* (non-PHPdoc)
	 * @see VDLOperatorBase::GenerateCommandLine()
	 */
	public function GenerateCommandLine(VDLFlavor $design, VDLFlavor $target, $extra = null) {
		$cmdStr = '';
		$cmdStr .= $extra ;
		
		$cmdStr .= ' -inputFile=' . VDLCmdlinePlaceholders::InFileName;
		$cmdStr .= ' -imagesfolder=' . VDLCmdlinePlaceholders::OutFileName;
		$cmdStr .= ' -xmlFile=' .  VDLCmdlinePlaceholders::OutFileName . DIRECTORY_SEPARATOR . "metadata.xml";
		$cmdStr .= ' -slideWidth=' . $target->_image->_sizeWidth;
		return $cmdStr;
	}
}
