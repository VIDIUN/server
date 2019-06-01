<?php
/**
 * @package infra
 * @subpackage utils
 */
class vTime
{
	const REMOVE_DATE = -1;

	public static function getRelativeTime($value)
	{
		// empty fields should be treated as 0 and not as the current time
		if (strlen($value) == 0)
			return 0;
		$value = (int)$value;
		if ($value == self::REMOVE_DATE)
			return $value;
		$maxRelativeTime = vConf::get('max_relative_time');
		if (-$maxRelativeTime <= $value && $value <= $maxRelativeTime && self::isRelativeTimeEnabled())
		{
			$time = self::getTime();
			$value = $time + $value;
		}

		return $value;
	}

	/**
	 * Looks for the time that is stored under vs privilege as reference time.
	 * If not found, returns time().
	 *
	 * @param bool $notifyApiCache
	 * @return int
	 */
	public static function getTime($notifyApiCache = true)
	{
		if (vCurrentContext::$vs_object)
		{
			$referenceTime = vCurrentContext::$vs_object->getPrivilegeValue(vs::PRIVILEGE_REFERENCE_TIME);
			if ($referenceTime)
				return (int)$referenceTime;
		}
		if ($notifyApiCache)
			return vApiCache::getTime();
		else
			return time();
	}

	public static function isRelativeTimeEnabled()
	{
		if (!vConf::hasParam('disable_relative_time_partners'))
			return true;

		return !in_array(vCurrentContext::getCurrentPartnerId(), vConf::get('disable_relative_time_partners'));
	}
}