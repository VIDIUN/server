<?php
/**
 * @package api
 * @subpackage objects.factory
 */
class VidiunEntryFactory
{
	/**
	 * @param int $type
	 * @param bool $isAdmin
	 * @return VidiunBaseEntry
	 */
	static function getInstanceByType ($type, $isAdmin = false)
	{
		switch ($type) 
		{
			case VidiunEntryType::MEDIA_CLIP:
				$obj = new VidiunMediaEntry();
				break;
				
			case VidiunEntryType::MIX:
				$obj = new VidiunMixEntry();
				break;
				
			case VidiunEntryType::PLAYLIST:
				$obj = new VidiunPlaylist();
				break;
				
			case VidiunEntryType::DATA:
				$obj = new VidiunDataEntry();
				break;
				
			case VidiunEntryType::LIVE_STREAM:
				if($isAdmin)
				{
					$obj = new VidiunLiveStreamAdminEntry();
				}
				else
				{
					$obj = new VidiunLiveStreamEntry();
				}
				break;
				
			case VidiunEntryType::LIVE_CHANNEL:
				$obj = new VidiunLiveChannel();
				break;
				
			default:
				$obj = VidiunPluginManager::loadObject('VidiunBaseEntry', $type);
				
				if(!$obj)
					$obj = new VidiunBaseEntry();
					
				break;
		}
		
		return $obj;
	}
}
