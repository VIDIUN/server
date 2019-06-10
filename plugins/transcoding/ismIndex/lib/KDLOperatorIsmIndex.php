<?php
/**
 * @package plugins.ismIndex
 * @subpackage lib
 * 
 * IsmIndex plugin accepts 'segmentDuration' settings in the 'extra' field json operator
 */
class VDLOperatorIsmIndex extends VDLOperatorBase {
    public function __construct($id, $name=null, $sourceBlacklist=null, $targetBlacklist=null) {
    	parent::__construct($id,$name,$sourceBlacklist,$targetBlacklist);
    }

	/* ---------------------------
	 * GenerateCommandLine
	 */
    public function GenerateCommandLine(VDLFlavor $design, VDLFlavor $target, $extra=null)
	{
//		ismindex -n /opt/vidiun/tmp/convert/convert_0_puqafx8u_c7219  /web//content/entry/data/51/949/0_puqafx8u_0_uh5zya8o_1.ismv /web//content/entry/data/51/949/0_puqafx8u_0_9lgbadny_1.ismv
		$cmdStr = "-n ".VDLCmdlinePlaceholders::OutFileName." ".VDLCmdlinePlaceholders::InFileName;
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
	