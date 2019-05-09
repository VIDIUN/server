<?php

class VidiunReportResponseOptions extends VidiunObject
{
	/**
	 * @var string
	 */
	public $delimiter;

	/**
	 * @var bool
	 */
	public $skipEmptyDates;

	private static $map_between_objects = array
	(
		'delimiter',
		'skipEmptyDates',
	);

	protected function getMapBetweenObjects()
	{
		return array_merge(parent::getMapBetweenObjects(), self::$map_between_objects);
	}

	/* (non-PHPdoc)
 	* @see VidiunObject::toObject()
 	*/
	public function toObject($reportResponseOptions = null, $skip = array())
	{
		if(!$reportResponseOptions)
		{
			$reportResponseOptions = new vReportResponseOptions();
		}

		return parent::toObject($reportResponseOptions, $skip);
	}



}