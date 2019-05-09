<?php

/**
 * @package plugins.scheduledTask
 * @subpackage api.objects.objectTasks
 */
class VidiunConvertEntryFlavorsObjectTask extends VidiunObjectTask
{
	/**
	 * Comma separated list of flavor param ids to convert
	 *
	 * @var string
	 */
	public $flavorParamsIds;

	/**
	 * Should reconvert when flavor already exists?
	 *
	 * @var bool
	 */
	public $reconvert;

	public function __construct()
	{
		$this->type = ObjectTaskType::CONVERT_ENTRY_FLAVORS;
	}

	public function toObject($dbObject = null, $skip = array())
	{
		/** @var vObjectTask $dbObject */
		$dbObject = parent::toObject($dbObject, $skip);

		$flavorParamsIds = array_unique(vString::fromCommaSeparatedToArray($this->flavorParamsIds));
		$dbObject->setDataValue('flavorParamsIds', $flavorParamsIds);
		$dbObject->setDataValue('reconvert', $this->reconvert);
		return $dbObject;
	}

	public function doFromObject($srcObj, VidiunDetachedResponseProfile $responseProfile = null)
	{
		parent::doFromObject($srcObj, $responseProfile);

		/** @var vObjectTask $srcObj */
		if($this->shouldGet('flavorParamsIds', $responseProfile))
			$this->flavorParamsIds = implode(',', $srcObj->getDataValue('flavorParamsIds'));
			
		if($this->shouldGet('reconvert', $responseProfile))
			$this->reconvert = $srcObj->getDataValue('reconvert');
	}
}