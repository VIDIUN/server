<?php
/**
 * Enable indexing and searching schedule event objects in sphinx
 * @package plugins.reach
 */
class EntryVendorTaskSphinxPlugin extends VidiunPlugin implements IVidiunCriteriaFactory, IVidiunSphinxConfiguration, IVidiunPending
{
	const PLUGIN_NAME = 'entryVendorTaskSphinx';
	
	/* (non-PHPdoc)
	 * @see IVidiunPlugin::getPluginName()
	 */
	public static function getPluginName()
	{
		return self::PLUGIN_NAME;
	}
	
	/* (non-PHPdoc)
	 * @see IVidiunCriteriaFactory::getVidiunCriteria()
	 */
	public static function getVidiunCriteria($objectType)
	{
		if ($objectType == "EntryVendorTask")
			return new SphinxEntryVendorTaskCriteria();
			
		return null;
	}
	
	/* (non-PHPdoc)
	 * @see IVidiunSphinxConfiguration::getSphinxSchema()
	 */
	public static function getSphinxSchema()
	{
		return array(
			vSphinxSearchManager::getSphinxIndexName('entry_vendor_task') => array (	
				'path'		=> '/sphinx/vidiun_entry_vendor_task_rt',
				'fields'	=> array (
					'id' 				=> SphinxFieldType::RT_ATTR_BIGINT,
					'created_at'		=> SphinxFieldType::RT_ATTR_TIMESTAMP,
					'updated_at' 		=> SphinxFieldType::RT_ATTR_TIMESTAMP,
					'queue_time' 		=> SphinxFieldType::RT_ATTR_TIMESTAMP,
					'finish_time' 		=> SphinxFieldType::RT_ATTR_TIMESTAMP,
					'partner_id' 		=> SphinxFieldType::RT_ATTR_BIGINT,
					'vendor_partner_id' => SphinxFieldType::RT_FIELD,
					'entry_id' 			=> SphinxFieldType::RT_FIELD,
					'status' 			=> SphinxFieldType::RT_ATTR_UINT,
					'price' 			=> SphinxFieldType::RT_ATTR_UINT,
					'catalog_item_id' 	=> SphinxFieldType::RT_FIELD,
					'reach_profile_id'  => SphinxFieldType::RT_FIELD,
					'vuser_id'			=> SphinxFieldType::RT_FIELD,
					'user_id'			=> SphinxFieldType::RT_FIELD,
					'context' 			=> SphinxFieldType::RT_FIELD,
					'notes' 			=> SphinxFieldType::RT_FIELD,
					'catalog_item_data' => SphinxFieldType::RT_FIELD,
				)
			)
		);
	}
	
	/* (non-PHPdoc)
	 * @see IVidiunPending::dependsOn()
	 */
	public static function dependsOn()
	{
		$rechPluginDependency = new VidiunDependency(ReachPlugin::getPluginName());
		return array($rechPluginDependency);
	}
}
