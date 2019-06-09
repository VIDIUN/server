<?php
/**
 * @package plugins.thumbnail
 * @subpackage model.imagickAction
 */

class textAction extends imagickAction
{
	protected $x;
	protected $y;
	protected $font;
	protected $font_size;
	protected $text;
	protected $angle;
	protected $strokeColor;
	protected $fillColor;
	protected $maxWidth;
	protected $maxHeight;

	protected $parameterAlias = array(
		"f" => vThumbnailParameterName::FONT,
		"fs" => vThumbnailParameterName::FONT_SIZE,
		"fontsize" => vThumbnailParameterName::FONT_SIZE,
		"t" => vThumbnailParameterName::TEXT,
		"txt" => vThumbnailParameterName::TEXT,
		"a" => vThumbnailParameterName::ANGLE,
		"sc" => vThumbnailParameterName::STROKE_COLOR,
		"strokecolor" => vThumbnailParameterName::STROKE_COLOR,
		"fc" => vThumbnailParameterName::FILL_COLOR,
		"fillcolor" => vThumbnailParameterName::FILL_COLOR,
		"w" => vThumbnailParameterName::WIDTH,
		"maxwidth" => vThumbnailParameterName::WIDTH,
		"mw" => vThumbnailParameterName::WIDTH,
		"h" => vThumbnailParameterName::HEIGHT,
		"maxheight" => vThumbnailParameterName::HEIGHT,
		"mh" => vThumbnailParameterName::HEIGHT,
	);

	protected function extractActionParameters()
	{
		$this->x = $this->getIntActionParameter(vThumbnailParameterName::X, 0);
		$this->y = $this->getIntActionParameter(vThumbnailParameterName::Y, 10);
		$this->font_size = $this->getFloatActionParameter(vThumbnailParameterName::FONT_SIZE, 10);
		$this->text = $this->getActionParameter(vThumbnailParameterName::TEXT);
		$this->text = trim(urldecode($this->text));
		$this->font = $this->getActionParameter(vThumbnailParameterName::FONT, 'Courier');
		$this->angle = $this->getFloatActionParameter(vThumbnailParameterName::ANGLE, 0);
		$this->strokeColor = $this->getColorActionParameter(vThumbnailParameterName::STROKE_COLOR, "black");
		$this->fillColor = $this->getColorActionParameter(vThumbnailParameterName::FILL_COLOR, "black");
		$this->maxHeight = $this->getIntActionParameter(vThumbnailParameterName::HEIGHT);
		$this->maxWidth = $this->getIntActionParameter(vThumbnailParameterName::WIDTH);
	}

	protected function validateInput()
	{
		$this->validateColorParameter($this->strokeColor);
		$this->validateColorParameter($this->fillColor);
		if(!$this->text)
		{
			throw new VidiunAPIException(VidiunThumbnailErrors::BAD_QUERY, "You must supply a text for this action");
		}
	}

	/**
	 * @return Imagick
	 */
	protected function doAction()
	{
		$draw = new ImagickDraw();
		$draw->setFont($this->font);
		$draw->setFontSize($this->font_size);
		$draw->setStrokeColor($this->strokeColor);
		$draw->setFillColor($this->fillColor);
		if($this->maxWidth || $this->maxHeight)
		{
			$wordWrapHelper = new wordWrapHelper($this->image, $draw, $this->text, $this->maxWidth, $this->maxHeight);
			$this->text = $wordWrapHelper->calculateWordWrap();
		}

		$this->image->annotateImage($draw, $this->x, $this->y, $this->angle, $this->text);
		return $this->image;
	}
}
