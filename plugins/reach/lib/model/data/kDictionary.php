
<?php

/**
 * Define language Dictionary profile
 *
 * @package plugins.reach
 * @subpackage model
 *
 */
class vDictionary
{
	/**
	 * @var VidiunCatalogItemLanguage
	 */
	protected $language;

	/**
	 * @var string
	 */
	protected $data;

	/**
	 * @return the VidiunCatalogItemLanguage
	 */
	public function getLanguage()
	{
		return $this->language;
	}

	/**
	 * @return the data
	 */
	public function getData()
	{
		return $this->data;
	}

	/**
	 * @param VidiunCatalogItemLanguage $language
	 */
	public function setLanguage($language)
	{
		$this->language = $language;
	}

	/**
	 * @param string $data
	 */
	public function setData($data)
	{
		$this->data = $data;
	}
}