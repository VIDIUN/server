<?php
/**
 * @package api
 * @subpackage objects.factory
 */
class VidiunFlavorParamsFactory
{
	static function getFlavorParamsOutputInstance($type)
	{
		switch ($type) 
		{
			case VidiunAssetType::FLAVOR:
				return new VidiunFlavorParamsOutput();
				
			case VidiunAssetType::THUMBNAIL:
				return new VidiunThumbParamsOutput();
				
			default:
				$obj = VidiunPluginManager::loadObject('VidiunFlavorParamsOutput', $type);
				if($obj)
					return $obj;
					
				return new VidiunFlavorParamsOutput();
		}
	}
	
	static function getFlavorParamsInstance($type)
	{
		switch ($type) 
		{
			case VidiunAssetType::FLAVOR:
				return new VidiunFlavorParams();
				
			case VidiunAssetType::THUMBNAIL:
				return new VidiunThumbParams();
				
			case VidiunAssetType::LIVE:
				return new VidiunLiveParams();
				
			default:
				$obj = VidiunPluginManager::loadObject('VidiunFlavorParams', $type);
				if($obj)
					return $obj;
					
				return new VidiunFlavorParams();
		}
	}
}
