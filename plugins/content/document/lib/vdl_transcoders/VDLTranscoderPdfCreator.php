<?php
/**
 * @package plugins.document
 * @subpackage lib
 */
class VDLTranscoderPdfCreator extends VDLOperatorBase
{
	
	public function __construct($id, $name=null, $sourceBlacklist=null, $targetBlacklist=null) {
		parent::__construct($id,$name,$sourceBlacklist,$targetBlacklist);
    }
    


    /**
     * @param VDLFlavor $target
     * @return string configuration to be saved as file
     */
    public function getConfigFile(VDLFlavor $target)
    {
    	$configIni = parse_ini_file(PDFCREATOR_CONFIG_TEMPLATE);
    	if (!$configIni) {
    		throw new Exception('Cannot parse template configurations files ['.PDFCREATOR_CONFIG_TEMPLATE.']');
    	}
    	
    	// set up output format and filename
    	$configIni['AutosaveFormat'] = '0'; //PDF format
    	$configIni['UseAutosave'] = '1';
    	$configIni['UseAutosaveDirectory'] = '1';
    	$configIni['AutosaveDirectory'] = VDLCmdlinePlaceholders::OutDir;
    	$configIni['AutosaveFilename'] = VDLCmdlinePlaceholders::OutFileName;
    	
    	// pdf parameters from flavor params
    	if ($target->_pdf->_resolution) {
    		$configIni['PDFGeneralResolution'] = $target->_pdf->_resolution;
    	}
    	if ($target->_pdf->_paperHeight) {
    		$configIni['UseFixPapersize'] = '0';
    		$configIni['UseCustomPaperSize'] = '1';
    		$configIni['DeviceHeightPoints'] = $target->_pdf->_paperHeight;
    	}
    	if ($target->_pdf->_paperWidth) {
    		$configIni['UseFixPapersize'] = '0';
    		$configIni['UseCustomPaperSize'] = '1';
    		$configIni['DeviceWidthPoints'] = $target->_pdf->_paperWidth;
    	}
    	
    	$configStr = '[Options]'.PHP_EOL;
    	foreach ($configIni as $key => $value) {
    		$configStr .= $key.'='.$value.PHP_EOL;
    	}
 		    	
    	return $configStr;
    }
    
 
	
    public function GenerateCommandLine(VDLFlavor $design, VDLFlavor $target, $extra=null)
	{
		$cmdStr = VDLCmdlinePlaceholders::InFileName ." ". VDLCmdlinePlaceholders::OutFileName;
		if ($target->_pdf && $target->_pdf->_readonly){
			$cmdStr .=" --readonly";
		}
				
		return $cmdStr;
	}
	
}

