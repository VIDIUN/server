<?php
/**
 * Used to ingest media that is available on remote server and accessible using the supplied URL, the media file won't be downloaded but a file sync object of URL type will point to the media URL.
 *
 * @package api
 * @subpackage objects
 */
class VidiunRemoteStorageResourceArray extends VidiunTypedArray
{
	/**
	 * @param array<vRemoteStorageResource> $arr
	 * @return VidiunRemoteStorageResourceArray
	 */
	public static function fromDbArray(array $arr, VidiunDetachedResponseProfile $responseProfile = null)
	{
		$newArr = new VidiunRemoteStorageResourceArray();
		foreach($arr as $obj)
		{
			$nObj = new VidiunRemoteStorageResource();
			$nObj->fromObject($obj, $responseProfile);
			$newArr[] = $nObj;
		}

		return $newArr;
	}
	
	public function __construct()
	{
		parent::__construct("VidiunRemoteStorageResource");
	}
}