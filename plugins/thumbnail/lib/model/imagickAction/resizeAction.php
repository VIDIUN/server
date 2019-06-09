<?php
/**
 * @package plugins.thumbnail
 * @subpackage model.imagickAction
 */

class resizeAction extends imagickAction
{
	protected $newWidth;
	protected $newHeight;
	protected $currentWidth;
	protected $currentHeight;
	protected $bestFit;
	protected $filterType;
	protected $blur;
	protected $shouldUseResize;
	protected $compositeFit;
	protected $compositeObject;

	const MAX_IMAGE_SIZE = 10000;
	const BEST_FIT_MIN = 1;
	CONST MIN_DIMENSION = 0;

	protected $parameterAlias = array(
		"w" => vThumbnailParameterName::WIDTH,
		"h" => vThumbnailParameterName::HEIGHT,
		"ft" => vThumbnailParameterName::FILTER_TYPE,
		"filtertype" => vThumbnailParameterName::FILTER_TYPE,
		"b" => vThumbnailParameterName::BLUR,
		"bf" => vThumbnailParameterName::BEST_FIT,
		"bestfit" => vThumbnailParameterName::BEST_FIT,
		"cf" => vThumbnailParameterName::COMPOSITE_FIT,
		"compositefit" => vThumbnailParameterName::COMPOSITE_FIT,
	);

	protected function extractActionParameters()
	{
		$this->currentWidth = $this->image->getImageWidth();
		$this->currentHeight = $this->image->getImageHeight();
		$this->newWidth = $this->getIntActionParameter(vThumbnailParameterName::WIDTH);
		$this->newHeight = $this->getIntActionParameter(vThumbnailParameterName::HEIGHT);
		$this->filterType = $this->getActionParameter(vThumbnailParameterName::FILTER_TYPE, Imagick::FILTER_LANCZOS);
		$this->blur = $this->getFloatActionParameter(vThumbnailParameterName::BLUR, 1);
		$this->bestFit = $this->getBoolActionParameter(vThumbnailParameterName::BEST_FIT);
		$this->compositeFit = $this->getBoolActionParameter(vThumbnailParameterName::COMPOSITE_FIT);
		$this->compositeObject = $this->getActionParameter(vThumbnailParameterName::COMPOSITE_OBJECT);
		$this->shouldUseResize = true;
	}

	function validateInput()
	{
		if($this->compositeFit)
		{
			if(!$this->compositeObject)
			{
				throw new VidiunAPIException(VidiunThumbnailErrors::BAD_QUERY, 'Missing composite object');
			}
		}
		else
		{
			$this->validateDimensions();
		}
	}

	protected function validateDimensions()
	{
		if($this->bestFit && $this->newWidth < self::BEST_FIT_MIN)
		{
			throw new VidiunAPIException(VidiunThumbnailErrors::BAD_QUERY, 'If bestfit is supplied parameter width must be positive');
		}

		if($this->bestFit && $this->newHeight < self::BEST_FIT_MIN)
		{
			throw new VidiunAPIException(VidiunThumbnailErrors::BAD_QUERY, ' If bestfit is supplied parameter height must be positive');
		}

		if(!is_numeric($this->newWidth) || $this->newWidth < self::MIN_DIMENSION || $this->newWidth > self::MAX_IMAGE_SIZE)
		{
			throw new VidiunAPIException(VidiunThumbnailErrors::BAD_QUERY, 'width must be between 0 and 10000');
		}

		if(!is_numeric($this->newHeight) || $this->newHeight < self::MIN_DIMENSION || $this->newHeight > self::MAX_IMAGE_SIZE)
		{
			throw new VidiunAPIException(VidiunThumbnailErrors::BAD_QUERY, 'height must be between 0 and 10000');
		}
	}

	protected function doAction()
	{
		if($this->compositeFit)
		{
			$this->newHeight = $this->compositeObject->getImageHeight();
			$this->newWidth = $this->compositeObject->getImageWidth();
		}

		if($this->newHeight > $this->currentHeight && $this->newWidth > $this->currentWidth)
		{
			$this->shouldUseResize = false;
		}

		if($this->shouldUseResize)
		{
			$this->image->resizeImage($this->newWidth, $this->newHeight, $this->filterType, $this->blur, $this->bestFit);
		}
		else
		{
			$this->image->scaleImage($this->newWidth, $this->newHeight, $this->bestFit);
		}

		return $this->image;
	}
}