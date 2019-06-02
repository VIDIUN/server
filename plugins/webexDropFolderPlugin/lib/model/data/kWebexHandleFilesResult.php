<?php
/**
 * @package plugins.dropFolder
 * @subpackage model
 */
class vWebexHandleFilesResult
{
	const FILE_ADDED_TO_DROP_FOLDER = 'fileAddedToDropFolder';
	const FILE_NOT_ADDED_TO_DROP_FOLDER = 'fileNotAddedToDropFolder';
	const FILE_NOT_HANDLED = 'fileNotHandled';
	const FILE_HANDLED = 'fileHandled';

	private $result;

	/**
	 * vWebexHandleFilesResult constructor.
	 */
	public function __construct()
	{
		$this->result = array();
	}

	public function addFileName($category, $fileName)
	{
		if(!isset($category))
			$this->result[$category] = array();

		$this->result[$category][] = $fileName;
	}

	public function toString()
	{
		$text = "";
		foreach ($this->result as $category => $fileNames)
		{
			$text.=$category.":".implode(",", $fileNames).PHP_EOL;
		}

		return $text;
	}
}