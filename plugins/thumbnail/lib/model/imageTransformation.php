<?php
/**
 * @package plugins.thumbnail
 * @subpackage model
 */

class imageTransformation
{
	/** @var imageTransformationStep[] */
	protected $imageSteps = array();

	public function validate()
	{
		$stepsCount = count($this->imageSteps);
		if(!$stepsCount)
		{
			throw new VidiunAPIException(VidiunThumbnailErrors::EMPTY_IMAGE_TRANSFORMATION);
		}

		$firstStep = $this->imageSteps[0];
		if($firstStep->usesCompositeObject())
		{
			throw new VidiunAPIException(VidiunThumbnailErrors::FIRST_STEP_CANT_USE_COMP_ACTION);
		}

		for($i = 1 ; $i < $stepsCount; $i++)
		{
			if(!$this->imageSteps[$i]->usesCompositeObject())
			{
				throw new VidiunAPIException(VidiunThumbnailErrors::MISSING_COMPOSITE_ACTION);
			}
		}
	}

	public function execute()
	{
		try
		{
			$transformationParameters = array();
			foreach ($this->imageSteps as $step)
			{
				$transformationParameters[vThumbnailParameterName::COMPOSITE_OBJECT] = $step->execute($transformationParameters);
			}
		}
		catch(ImagickException $e)
		{
			VidiunLog::err("Imagick error:" . print_r($e));
			throw new VidiunAPIException(VidiunThumbnailErrors::TRANSFORMATION_RUNTIME_ERROR);
		}

		return $transformationParameters[vThumbnailParameterName::COMPOSITE_OBJECT];
	}

	/**
	 * @param imageTransformationStep $step
	 */
	public function addImageTransformationStep($step)
	{
		$this->imageSteps[] = $step;
	}
}