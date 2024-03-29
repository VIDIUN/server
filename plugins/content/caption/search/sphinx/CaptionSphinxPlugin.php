<?php
/**
 * Enable indexing and searching caption asset objects in sphinx
 * @package plugins.captionSphinx
 */
class CaptionSphinxPlugin extends VidiunPlugin implements IVidiunPending, IVidiunCriteriaFactory, IVidiunSphinxConfiguration
{
	const PLUGIN_NAME = 'captionSphinx';
	
	public static function getPluginName()
	{
		return self::PLUGIN_NAME;
	}
	
	/* (non-PHPdoc)
	 * @see IVidiunPending::dependsOn()
	 */
	public static function dependsOn()
	{
		$captionSearchDependency = new VidiunDependency(CaptionSearchPlugin::getPluginName());
		
		return array($captionSearchDependency);
	}


	/* (non-PHPdoc)
	 * @see IVidiunCriteriaFactory::getVidiunCriteria()
	 */
	public static function getVidiunCriteria($objectType)
	{
		if ($objectType == CaptionAssetItemPeer::OM_CLASS)
			return new SphinxCaptionAssetItemCriteria();
			
		return null;
	}
	
	public static function getSphinxSchemaFields()
	{
		return  array(
			'entry_id' => SphinxFieldType::RT_FIELD,
			'caption_asset_id' => SphinxFieldType::RT_FIELD,
			'tags' => SphinxFieldType::RT_FIELD,
			'content' => SphinxFieldType::RT_FIELD,
			'partner_description' => SphinxFieldType::RT_FIELD,
			'language' => SphinxFieldType::RT_FIELD,
			'label' => SphinxFieldType::RT_FIELD,
			'format' => SphinxFieldType::RT_FIELD,
			'caption_params_id' => SphinxFieldType::RT_ATTR_BIGINT,
			'partner_id' => SphinxFieldType::RT_ATTR_BIGINT,
			'version' => SphinxFieldType::RT_ATTR_BIGINT,
			'caption_asset_status' => SphinxFieldType::RT_ATTR_BIGINT,
			'size' => SphinxFieldType::RT_ATTR_BIGINT,
			'is_default' => SphinxFieldType::RT_ATTR_BIGINT,
			'start_time' => SphinxFieldType::RT_ATTR_BIGINT,
			'end_time' => SphinxFieldType::RT_ATTR_BIGINT,
			
			'created_at' => SphinxFieldType::RT_ATTR_TIMESTAMP,
			'updated_at' => SphinxFieldType::RT_ATTR_TIMESTAMP,
			
			'str_entry_id' => SphinxFieldType::RT_ATTR_STRING,
		);
	}
	
	/* (non-PHPdoc)
	 * @see IVidiunSphinxConfiguration::getSphinxSchema()
	 */
	public static function getSphinxSchema()
	{
		return array(
			vSphinxSearchManager::getSphinxIndexName(CaptionSearchPlugin::INDEX_NAME) => array (	
				'path'		=> '/sphinx/vidiun_caption_item_rt',
				'fields'	=> self::getSphinxSchemaFields(),
			)
		);
	}
}
