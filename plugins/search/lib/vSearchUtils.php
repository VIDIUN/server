<?php

class vSearchUtils
{
	public static function getSkipRepetitiveUpdatesValue($repetitiveUpdatesConfigKey, $className)
	{
		$skipRepetitiveUpdatesConfig = vConf::getMap($repetitiveUpdatesConfigKey);
		
		$updatesKey = strtolower(vCurrentContext::getCurrentPartnerId()."_".$className."_".vCurrentContext::$service."_".vCurrentContext::$action);
		if(isset($skipRepetitiveUpdatesConfig[$updatesKey]))
		{
			return array($skipRepetitiveUpdatesConfig[$updatesKey], $updatesKey);
		}
		
		$vsPuserId = vCurrentContext::$vs_uid;
		if(isset($vsPuserId))
		{
			//Replace dots with underscore since ini file do not support dont in the key name
			$vsPuserId = str_replace(".", "_", $vsPuserId);
			$updatesKey = strtolower($className."_".vCurrentContext::$service."_".vCurrentContext::$action."_".$vsPuserId);
			if(isset($skipRepetitiveUpdatesConfig[$updatesKey]))
			{
				return array($skipRepetitiveUpdatesConfig[$updatesKey], $updatesKey);
			}
		}
		
		$updatesKey = strtolower($className."_".vCurrentContext::$service."_".vCurrentContext::$action);
		if(isset($skipRepetitiveUpdatesConfig[$updatesKey]))
		{
			return array($skipRepetitiveUpdatesConfig[$updatesKey], $updatesKey);
		}
		
		$updatesKey = strtolower(vCurrentContext::getCurrentPartnerId()."_".$className);
		if(isset($skipRepetitiveUpdatesConfig[$updatesKey]))
		{
			return array($skipRepetitiveUpdatesConfig[$updatesKey], $updatesKey);
		}
		
		$updatesKey = strtolower($className);
		if(isset($skipRepetitiveUpdatesConfig[$updatesKey]))
		{
			return array($skipRepetitiveUpdatesConfig[$updatesKey], $updatesKey);
		}
		
		return array(null, null);
	}
}