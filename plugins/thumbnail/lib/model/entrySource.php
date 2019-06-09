<?php
/**
 * @package plugins.thumbnail
 * @subpackage model
 */

class entrySource extends thumbnailSource
{
	protected $dbEntry;

	public function  __construct($entryId)
	{
		$dbEntry = entryPeer::retrieveByPK($entryId);
		if (!$dbEntry)
		{
			throw new VidiunAPIException(VidiunErrors::ENTRY_ID_NOT_FOUND, $entryId);
		}

		$secureEntryHelper = new VSecureEntryHelper($dbEntry, vCurrentContext::$vs, null, ContextType::THUMBNAIL);
		$secureEntryHelper->validateAccessControl();
		$this->dbEntry = $dbEntry;
	}


	public function getEntryMediaType()
	{
		return $this->dbEntry->getMediaType();
	}


	/**
	 * @return entry
	 */
	public function getEntry()
	{
		return $this->dbEntry;
	}

	public function getImage()
	{
		if($this->getEntryMediaType() == entry::ENTRY_MEDIA_TYPE_IMAGE)
		{
			$fileSyncKey = $this->dbEntry->getSyncKey(entry::FILE_SYNC_ENTRY_SUB_TYPE_DATA);
			$imageBlob = vFileSyncUtils::file_get_contents($fileSyncKey);
			$imagick = new Imagick();
			$imagick->readImageBlob($imageBlob);
			return $imagick;
		}

		throw new VidiunAPIException(VidiunThumbnailErrors::MISSING_SOURCE_ACTIONS_FOR_TYPE, $this->getEntryMediaType());
	}
}