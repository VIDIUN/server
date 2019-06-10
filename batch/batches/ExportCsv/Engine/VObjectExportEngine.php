<?php

/**
 * @package Scheduler
 * @subpackage ExportCsv
 */
abstract class VObjectExportEngine
{
	/**
	 * @param int $objectType of enum VidiunCopyObjectType
	 * @return VCopyingEngine
	 */
	public static function getInstance($objectType)
	{
		switch($objectType)
		{
			case VidiunExportObjectType::USER:
				return new KUserExportEngine();
			
			
			default:
				return VidiunPluginManager::loadObject('VObjectExportEngine', $objectType);
		}
	}
	
	abstract public function fillCsv (&$csvFile, &$data);
	
	/**
	 * Generate the first csv row containing the fields
	 */
	abstract protected function addHeaderRowToCsv($csvFile, $additionalFields);
}