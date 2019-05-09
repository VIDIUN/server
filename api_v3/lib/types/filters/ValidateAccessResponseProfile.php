<?php
/**
 * @package api
 * @subpackage filters
 */

class ValidateAccessResponseProfile
{

	const LIST_ACTION = 'listAction';
	const COMMENTS = '#@(.*?)\n#s';
	const RELATED_SERVICE = 'relatedService';
	const IGNORE = 'ignore';

	/**
	 * @param $relatedFilter
	 * @return bool
	 * @throws Exception
	 */
	public static function validateAccess($relatedFilter)
	{
		if (self::PartnerExcludedFromResponseProfile())
			return true;
		$clazz = self::getServiceClassInstance($relatedFilter);
		return self::validate($clazz);
	}

	/**
	 * @param $relatedFilter
	 * @return string $clazz
	 * @throws Exception
	 */
	private static function getServiceClassInstance($relatedFilter)
	{
		$clazz = self::getServiceClazz($relatedFilter);
		$clazz = trim($clazz);
		/** @relatedService ignore */
		if ($clazz === self::IGNORE)
			return $clazz;
		/** if Class is not found then do not allow the response profile filter to get the response  */
		if (!class_exists($clazz)) {
			$e = new VidiunAPIException (APIErrors::SERVICE_FORBIDDEN, 'Service class:  ' . $clazz . ' Not Found');
			header("X-Vidiun:error-" . $e->getCode());
			header("X-Vidiun-App: exiting on error " . $e->getCode() . " - " . $e->getMessage());
			throw $e;
		}
		return $clazz;
	}

	/**
	 * @param $relatedFilter
	 * @return string className
	 */
	private static function getServiceClazz($relatedFilter)
	{
		$r = VidiunTypeReflectorCacher::get(get_class($relatedFilter));
		$comments = $r->getComments();
		preg_match_all(self::COMMENTS, $comments, $annotationsArray);
		$annotations = $annotationsArray[1];
		foreach ($annotations as $annotation)
		{
			if (strpos($annotation, self::RELATED_SERVICE) === 0)
			{
				list(, $clazz) = explode(' ', $annotation);
				return $clazz;
			}
		}
		return '';
	}

	/**
	 * @param string $clazz
	 * @return bool
	 * @throws Exception
	 */
	private static function validate($clazz)
	{
		/** @relatedService ignore */
		if ($clazz === self::IGNORE)
			return true;
		try {
			/** @var VidiunBaseService $service */
			$service = new $clazz();
			list($serviceId, $serviceName) = VidiunServicesMap::getServiceIdAndServiceNameByClass($clazz);
			$service->initService($serviceId, $serviceName, 'list');
		} catch (Exception $e) {
			VidiunLog::INFO('Response Profile Validation Access Failed For Class: ' . $clazz);
			throw  $e;
		}
		return true;
	}

	/**
	 * @return bool
	 * @throws Exception
	 */
	private static function PartnerExcludedFromResponseProfile()
	{
		$partnerId = vCurrentContext::getCurrentPartnerId();
		$partnersToSkip = vConf::get('skip_response_profile_validation_partners','local', array());
		return in_array($partnerId,$partnersToSkip);
	}

}
