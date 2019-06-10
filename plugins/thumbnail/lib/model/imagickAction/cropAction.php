<?php
/**
* @package plugins.thumbnail
* @subpackage model.imagickAction
*/

class cropAction extends imagickAction
{
	protected $newWidth;
	protected $newHeight;
	protected $currentWidth;
	protected $currentHeight;
	protected $gravityPoint;
	protected $x;
	protected $y;

	protected $parameterAlias = array(
		"gp" => vThumbnailParameterName::GRAVITY_POINT,
		"gravitypoint" => vThumbnailParameterName::GRAVITY_POINT,
		"w" => vThumbnailParameterName::WIDTH,
		"h" => vThumbnailParameterName::HEIGHT,
	);

	protected function extractActionParameters()
	{
		$this->newWidth = $this->getIntActionParameter(vThumbnailParameterName::WIDTH);
		$this->newHeight = $this->getIntActionParameter(vThumbnailParameterName::HEIGHT);
		$this->x = $this->getIntActionParameter(vThumbnailParameterName::X);
		$this->y = $this->getIntActionParameter(vThumbnailParameterName::Y);
		$this->gravityPoint = $this->getIntActionParameter(vThumbnailParameterName::GRAVITY_POINT, vCropGravityPoint::CENTER);
		$this->currentWidth = $this->image->getImageWidth();
		$this->currentHeight = $this->image->getImageHeight();
	}

	function validateInput()
	{
		$this->validateDimensions();
	}

	protected function validateDimensions()
	{
		if(($this->x && !$this->y) || (!$this->x && $this->y))
		{
			throw new VidiunAPIException(VidiunThumbnailErrors::BAD_QUERY, 'You cant define only crop x or crop y');
		}

		if($this->newWidth > $this->currentWidth)
		{
			throw new VidiunAPIException(VidiunThumbnailErrors::BAD_QUERY, 'crop width must be smaller or equal to the current width');
		}

		if($this->newHeight > $this->currentHeight)
		{
			throw new VidiunAPIException(VidiunThumbnailErrors::BAD_QUERY, 'crop height must be smaller or equal to the current height');
		}

		if($this->newWidth < 1)
		{
			throw new VidiunAPIException(VidiunThumbnailErrors::BAD_QUERY, 'width must be positive');
		}

		if($this->newHeight < 1)
		{
			throw new VidiunAPIException(VidiunThumbnailErrors::BAD_QUERY, 'height must be positive');
		}
	}

	/**
	 * @return Imagick
	 */
	protected function doAction()
	{
		if($this->x)
		{
			$this->image->cropImage($this->newWidth, $this->newHeight, $this->x, $this->y);
		}
		else
		{
			switch ($this->gravityPoint) {
				case vCropGravityPoint::TOP:
					$this->image->cropImage($this->newWidth, $this->newHeight, 0, 0);
					break;
				case vCropGravityPoint::CENTER:
					$this->image->cropImage($this->newWidth, $this->newHeight, $this->currentWidth / 2, $this->currentHeight / 2);
					break;
				default:
					throw new VidiunAPIException(VidiunThumbnailErrors::BAD_QUERY, 'illegal gravity point value');
			}
		}

		return $this->image;
	}
}
