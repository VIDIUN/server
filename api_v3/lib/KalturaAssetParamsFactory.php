<?php
/**
 * @package api
 * @subpackage objects.factory
 */
class VidiunAssetParamsFactory
{
	static function getAssetParamsOutputInstance($type)
	{
		switch ($type) 
		{
			case VidiunAssetType::FLAVOR:
				return new VidiunFlavorParamsOutput();
				
			case VidiunAssetType::THUMBNAIL:
				return new VidiunThumbParamsOutput();
				
			default:
				$obj = VidiunPluginManager::loadObject('VidiunAssetParamsOutput', $type);
				if($obj)
					return $obj;
					
				return new VidiunFlavorParamsOutput();
		}
	}
	
	static function getAssetParamsInstance($type)
	{
		switch ($type) 
		{
			case VidiunAssetType::FLAVOR:
				return new VidiunFlavorParams();
				
			case VidiunAssetType::THUMBNAIL:
				return new VidiunThumbParams();
				
			default:
				$obj = VidiunPluginManager::loadObject('VidiunAssetParams', $type);
				if($obj)
					return $obj;
					
				return new VidiunFlavorParams();
		}
	}
}
