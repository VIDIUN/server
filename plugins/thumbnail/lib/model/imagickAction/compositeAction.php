<?php
/**
 * @package plugins.thunmbnail
 * @subpackage model.imagickAction
 */

class compositeAction extends imagickAction
{
	protected $compositeType;
	protected $channel;
	protected $x;
	protected $y;
	protected $compositeObject;
	protected $opacity;

	const MAX_OPACITY = "100";
	const MIN_OPACITY = "1";

	protected $parameterAlias = array(
		"ct" => vThumbnailParameterName::COMPOSITE_TYPE,
		"compositetype" => vThumbnailParameterName::COMPOSITE_TYPE,
		"ch" => vThumbnailParameterName::CHANNEL,
		"op" => vThumbnailParameterName::OPACITY,
	);

	protected function extractActionParameters()
	{
		$this->x = $this->getIntActionParameter(vThumbnailParameterName::X, 0);
		$this->y = $this->getIntActionParameter(vThumbnailParameterName::Y, 0);
		$this->compositeType = $this->getIntActionParameter(vThumbnailParameterName::COMPOSITE_TYPE, imagick::COMPOSITE_DEFAULT);
		$this->channel = $this->getIntActionParameter(vThumbnailParameterName::CHANNEL, Imagick::CHANNEL_ALL);
		$this->compositeObject = $this->getActionParameter(vThumbnailParameterName::COMPOSITE_OBJECT);
		$this->opacity = $this->getIntActionParameter(vThumbnailParameterName::OPACITY);
	}

	protected function validateInput()
	{
		if($this->opacity && ($this->opacity < self::MIN_OPACITY || $this->opacity > SELF::MAX_OPACITY))
		{
			throw new VidiunAPIException(VidiunThumbnailErrors::BAD_QUERY, 'opacity must be between 1-100');
		}

		if(!$this->compositeObject)
		{
			throw new VidiunAPIException(VidiunThumbnailErrors::BAD_QUERY, 'Missing composite object');
		}
	}

	/**
	 * @return Imagick
	 * @throws VidiunAPIException
	 */
	protected function doAction()
	{
		if($this->opacity)
		{
			$opacity = new \Imagick();
			$pseudoString = "gradient:gray({$this->opacity}%)-gray({$this->opacity}%)";
			$opacity->newPseudoImage($this->image->getImageWidth(), $this->image->getImageHeight(), $pseudoString);
			$this->image->compositeImage($opacity, \Imagick::COMPOSITE_COPYOPACITY, 0, 0);
		}

		if(!$this->compositeObject->compositeImage($this->image, $this->compositeType, $this->x, $this->y, $this->channel))
		{
			throw new VidiunAPIException(VidiunThumbnailErrors::BAD_QUERY, 'Failed to compose image');
		}

		return $this->compositeObject;
	}

	public function canHandleCompositeObject()
	{
		return true;
	}
}