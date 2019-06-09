<?php
/**
 * @package api
 * @subpackage objects
 */
class VidiunStreamContainerArray extends VidiunTypedArray
{
	public static function fromDbArray($arr, VidiunDetachedResponseProfile $responseProfile = null)
	{
		$newArr = new VidiunStreamContainerArray();
		if ($arr == null)
			return $newArr;

		foreach ($arr as $obj)
		{
			$stream = new VidiunStreamContainer();
			$stream->fromObject( $obj, $responseProfile );
			$newArr[] = $stream;
		}

		return $newArr;
	}

	public function __construct()
	{
		parent::__construct("VidiunStreamContainer");
	}
}