<?php
/**
 * @package plugins.sphinxSearch
 */
class SphinxSearchPlugin extends VidiunPlugin implements IVidiunEventConsumers, IVidiunCriteriaFactory, IVidiunPending
{
	const PLUGIN_NAME = 'sphinxSearch';
	const SPHINX_SEARCH_MANAGER = 'vSphinxSearchManager';
	
	public static function getPluginName()
	{
		return self::PLUGIN_NAME;
	}
	
	/**
	 * @return array
	 */
	public static function getEventConsumers()
	{
		return array(
			self::SPHINX_SEARCH_MANAGER,
		);
	}
	
	/**
	 * Creates a new VidiunCriteria for the given object name
	 * 
	 * @param string $objectType object type to create Criteria for.
	 * @return VidiunCriteria derived object
	 */
	public static function getVidiunCriteria($objectType)
	{
		if ($objectType == "entry")
			return new SphinxEntryCriteria();
			
		if ($objectType == "category")
			return new SphinxCategoryCriteria();
			
		if ($objectType == "vuser")
			return new SphinxVuserCriteria();
		
		if ($objectType == "categoryVuser")
			return new SphinxCategoryVuserCriteria();
			
		return null;
	}

	/**
	 * Returns a Vidiun dependency object that defines the relationship between two plugins.
	 *
	 * @return array<VidiunDependency> The Vidiun dependency object
	 */
	public static function dependsOn()
	{
		$searchDependency = new VidiunDependency(SearchPlugin::getPluginName());
		return array($searchDependency);
	}
}
