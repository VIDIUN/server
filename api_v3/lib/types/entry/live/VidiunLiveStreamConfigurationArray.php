<?php
/**
 * @package api
 * @subpackage objects
 *
 */
class VidiunLiveStreamConfigurationArray extends VidiunTypedArray
{
	/**
	 * Returns API array object from regular array of database objects.
	 * @param array $dbArray
	 * @return VidiunLiveStreamConfiguration
	 */
	public static function fromDbArray(array $dbArray = null, VidiunDetachedResponseProfile $responseProfile = null)
	{
		$array = new VidiunLiveStreamConfigurationArray();
		if($dbArray && is_array($dbArray))
		{
			foreach($dbArray as $object)
			{
				/* @var $object vLiveStreamConfiguration */
				$configObject = new VidiunLiveStreamConfiguration();
				$configObject->fromObject($object, $responseProfile);;
				$array[] = $configObject;
			}
		}
		return $array;
	}
	
	public function __construct()
	{
		return parent::__construct("VidiunLiveStreamConfiguration");
	}
	
	/* (non-PHPdoc)
	 * @see VidiunTypedArray::toObjectsArray()
	 */
	public function toObjectsArray()
	{
		$objects = $this->toArray();
		for ($i = 0; $i < count($objects); $i++)
		{
			for ($j = $i+1; $j <count($objects); $j++ )
			{
				if ($objects[$i]->protocol == $objects[$j]->protocol)
				{
					unset($objects[$i]);
				}
			}
		}
		
		$ret = array();
		foreach ($objects as $object)
		{
			$ret[] = $object->toObject();
		}
		
		return $ret;
	}
	
}