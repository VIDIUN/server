<?php
/**
 * @package plugins.quiz
 * @subpackage api.objects
 */
class VidiunQuizArray extends VidiunTypedArray
{
	public static function fromDbArray($arr, VidiunDetachedResponseProfile $responseProfile = null)
	{
		$newArr = new VidiunQuizArray();
		if ($arr == null)
			return $newArr;

		foreach ($arr as $obj)
		{
			$vQuiz = QuizPlugin::getQuizData($obj);
			if ( !is_null($vQuiz) ) {
				$quiz = new VidiunQuiz();
				$quiz->fromObject( $vQuiz, $responseProfile );
				$newArr[] = $quiz;
			}
		}
		
		return $newArr;
	}
		
	public function __construct()
	{
		parent::__construct("VidiunQuiz");
	}
}