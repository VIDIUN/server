<?php
/**
 * @package api
 * @subpackage objects
 */
class VidiunBulkUploadPluginDataArray extends VidiunTypedArray
{
	public function __construct()
	{
		return parent::__construct("VidiunBulkUploadPluginData");
	}
	
	public function toValuesArray()
	{
		$ret = array();
		foreach($this as $pluginData)
			$ret[$pluginData->field] = $pluginData->value;
			
		return $ret;
	}
}
?>