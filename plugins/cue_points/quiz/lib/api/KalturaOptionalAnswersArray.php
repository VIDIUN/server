<?php
/**
 *
 * Associative array of VidiunOptionalAnswer
 *
 * @package plugins.quiz
 * @subpackage api.objects
 */

class VidiunOptionalAnswersArray extends VidiunTypedArray {

	public function __construct()
	{
		return parent::__construct("VidiunOptionalAnswer");
	}

	public static function fromDbArray($arr, VidiunDetachedResponseProfile $responseProfile = null)
	{
		$newArr = new VidiunOptionalAnswersArray();
		if ($arr == null)
			return $newArr;

		foreach ($arr as $obj)
		{
			$answerObj = new VidiunOptionalAnswer();
			$answerObj->fromObject($obj, $responseProfile);
			$newArr[] = $answerObj;
		}

		return $newArr;
	}
}