<?php

/**
 * @package plugins.scheduledTaskMetadata
 * @subpackage api.objects.objectTasks
 */
class VidiunExecuteMetadataXsltObjectTask extends VidiunObjectTask
{
	/**
	 * Metadata profile id to lookup the metadata object
	 *
	 * @var int
	 */
	public $metadataProfileId;

	/**
	 * Metadata object type to lookup the metadata object
	 *
	 * @var VidiunMetadataObjectType
	 */
	public $metadataObjectType;

	/**
	 * The XSLT to execute
	 *
	 * @var string
	 */
	public $xslt;

	public function __construct()
	{
		$this->type = ScheduledTaskMetadataPlugin::getApiValue(ExecuteMetadataXsltObjectTaskType::EXECUTE_METADATA_XSLT);
	}

	/* (non-PHPdoc)
	 * @see VidiunObject::validateForUsage()
	 */
	public function validateForUsage($sourceObject, $propertiesToSkip = array())
	{
		parent::validateForUsage($sourceObject, $propertiesToSkip);

		$this->validatePropertyNotNull('metadataProfileId');
		$this->validatePropertyNotNull('metadataObjectType');
		$this->validatePropertyNotNull('xslt');

		myPartnerUtils::addPartnerToCriteria('MetadataProfile', vCurrentContext::getCurrentPartnerId(), true);
		$metadataProfile = MetadataProfilePeer::retrieveByPK($this->metadataProfileId);
		if (is_null($metadataProfile))
			throw new VidiunAPIException(MetadataErrors::METADATA_PROFILE_NOT_FOUND, $this->metadataProfileId);
	}

	public function toObject($dbObject = null, $skip = array())
	{
		/** @var vObjectTask $dbObject */
		$dbObject = parent::toObject($dbObject, $skip);
		$dbObject->setDataValue('metadataProfileId', $this->metadataProfileId);
		$dbObject->setDataValue('metadataObjectType', $this->metadataObjectType);
		$dbObject->setDataValue('xslt', $this->xslt);
		return $dbObject;
	}

	public function doFromObject($srcObj, VidiunDetachedResponseProfile $responseProfile = null)
	{
		parent::doFromObject($srcObj, $responseProfile);

		/** @var vObjectTask $srcObj */
		$this->metadataProfileId = $srcObj->getDataValue('metadataProfileId');
		$this->metadataObjectType = $srcObj->getDataValue('metadataObjectType');
		$this->xslt = $srcObj->getDataValue('xslt');
	}
}