<?php

/**
 * @package api
 * @subpackage objects.factory
 */
class VidiunDeliveryProfileFactory {
	
	public static function getCoreDeliveryProfileInstanceByType($type) {
		$coreType = vPluginableEnumsManager::apiToCore('DeliveryProfileType', $type); 
		$class = DeliveryProfilePeer::getClassByDeliveryProfileType($coreType);
		return new $class();
	}
	
	public static function getDeliveryProfileInstanceByType($type) {
		switch ($type) {
			case VidiunDeliveryProfileType::GENERIC_HLS:
			case VidiunDeliveryProfileType::GENERIC_HLS_MANIFEST:
				return new VidiunDeliveryProfileGenericAppleHttp();
			case VidiunDeliveryProfileType::GENERIC_HDS:
			case VidiunDeliveryProfileType::GENERIC_HDS_MANIFEST:
				return new VidiunDeliveryProfileGenericHds();
			case VidiunDeliveryProfileType::GENERIC_HTTP:
					return new VidiunDeliveryProfileGenericHttp();
			case VidiunDeliveryProfileType::RTMP:
			case VidiunDeliveryProfileType::LIVE_RTMP:
				return new VidiunDeliveryProfileRtmp();
			case VidiunDeliveryProfileType::AKAMAI_HTTP:
				return new VidiunDeliveryProfileAkamaiHttp();
			case VidiunDeliveryProfileType::AKAMAI_HLS_MANIFEST:
				return new VidiunDeliveryProfileAkamaiAppleHttpManifest();
			case VidiunDeliveryProfileType::AKAMAI_HDS:
				return new VidiunDeliveryProfileAkamaiHds();
			case VidiunDeliveryProfileType::LIVE_HLS:
				return new VidiunDeliveryProfileLiveAppleHttp();
			case VidiunDeliveryProfileType::GENERIC_SS:
				return new VidiunDeliveryProfileGenericSilverLight();
			case VidiunDeliveryProfileType::GENERIC_RTMP:
				return new VidiunDeliveryProfileGenericRtmp();
			case VidiunDeliveryProfileType::VOD_PACKAGER_HLS_MANIFEST:
			case VidiunDeliveryProfileType::VOD_PACKAGER_HLS:
				return new VidiunDeliveryProfileVodPackagerHls();
			case VidiunDeliveryProfileType::VOD_PACKAGER_DASH:
				return new VidiunDeliveryProfileVodPackagerPlayServer();
			case VidiunDeliveryProfileType::VOD_PACKAGER_MSS:
				return new VidiunDeliveryProfileVodPackagerPlayServer();
			case VidiunDeliveryProfileType::LIVE_PACKAGER_HLS:
				return new VidiunDeliveryProfileLivePackagerHls();
			case VidiunDeliveryProfileType::LIVE_PACKAGER_DASH:
			case VidiunDeliveryProfileType::LIVE_PACKAGER_MSS:
			case VidiunDeliveryProfileType::LIVE_PACKAGER_HDS:
				return new VidiunDeliveryProfileLivePackager();
			default:
				$obj = VidiunPluginManager::loadObject('VidiunDeliveryProfile', $type);
				if(!$obj)
					$obj = new VidiunDeliveryProfile();
				return $obj;
		}
	}
	
	public static function getTokenizerInstanceByType($type) {
		switch ($type) {
			case 'vLevel3UrlTokenizer':
				return new VidiunUrlTokenizerLevel3();
			case 'vLimeLightUrlTokenizer':
				return new VidiunUrlTokenizerLimeLight();
			case 'vAkamaiHttpUrlTokenizer':
				return new VidiunUrlTokenizerAkamaiHttp();
			case 'vAkamaiRtmpUrlTokenizer':
				return new VidiunUrlTokenizerAkamaiRtmp();
			case 'vAkamaiRtspUrlTokenizer':
				return new VidiunUrlTokenizerAkamaiRtsp();
			case 'vAkamaiSecureHDUrlTokenizer':
				return new VidiunUrlTokenizerAkamaiSecureHd();
			case 'vCloudFrontUrlTokenizer':
				return new VidiunUrlTokenizerCloudFront();
			case 'vBitGravityUrlTokenizer':
				return new VidiunUrlTokenizerBitGravity();
			case 'vVnptUrlTokenizer':
				return new VidiunUrlTokenizerVnpt();
			case 'vChtHttpUrlTokenizer':
				return new VidiunUrlTokenizerCht();
			case 'vChinaCacheUrlTokenizer':
				return new VidiunUrlTokenizerChinaCache();	
			case 'vVsUrlTokenizer':
				return new VidiunUrlTokenizerVs();
			case 'vWowzaSecureTokenUrlTokenizer':
				return new VidiunUrlTokenizerWowzaSecureToken();

			// Add other tokenizers here
			default:
				$apiObject = VidiunPluginManager::loadObject('VidiunTokenizer', $type);
				if($apiObject)
					return $apiObject;
				VidiunLog::err("Cannot load API object for core Tokenizer [" . $type . "]");
				return null;
		}
	}
	
	public static function getRecognizerByType($type) {
		switch ($type) {
			case 'vUrlRecognizerAkamaiG2O':
				return new VidiunUrlRecognizerAkamaiG2O();
			case 'vUrlRecognizer':
				return new VidiunUrlRecognizer();
			default:
				$apiObject = VidiunPluginManager::loadObject('VidiunRecognizer', $type);
				if($apiObject)
					return $apiObject;
				VidiunLog::err("Cannot load API object for core Recognizer [" . $type . "]");
				return null;
		}
	}

}
