<?php

abstract class VOperationEngineDocument extends VSingleOutputOperationEngine {

	protected function getPdfInfo($file) {
		$pdfInfoExe = VBatchBase::$taskConfig->params->pdfInfo;
		$output = null;
		$command = $pdfInfoExe . " \"" . realpath($file) . "\" 2>&1";
		VidiunLog::info("Executing: $command");
		exec($command, $output);
		return $output;
	}
	
 	private function getFileInfo($filePath)
	{
		$returnValue = null;
		$output = null;
		$command = "file '{$filePath}'";
		VidiunLog::info("Executing: $command");
		exec($command, $output, $returnValue);
		return implode("\n",$output);
	}
	
	protected function checkFileType($filePath, $supportedTypes) {
	
		$matches = null;
		$fileType = null;
		
		$fileInfo = $this->getFileInfo($filePath);
		if(preg_match("/[^:]+: ([^,]+)/", $fileInfo, $matches)) 
			$fileType = $matches[1];

		foreach ($supportedTypes as $validType)
		{
			if (strpos($fileType, $validType) !== false)
				return null;
		}
	
		VidiunLog::info("file $filePath is of unexpected type : {$fileType}");
		return "invalid file type: {$fileType}";
	}
}

