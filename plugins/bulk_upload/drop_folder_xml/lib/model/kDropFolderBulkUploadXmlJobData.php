<?php
/**
 * @package plugins.dropFolderXmlBulkUpload
 * @subpackage model.data
 */
class vDropFolderBulkUploadXmlJobData extends vBulkUploadXmlJobData
{
	/**
	 * The bulk upload drop folder id
	 * @var int
	 */
	protected $dropFolderId;

	/**
	 * @return int $dropFolderId
	 */
	public function getDropFolderId()
	{
		return $this->dropFolderId;
	}

	/**
	 * @param int $dropFolderId
	 */
	public function setDropFolderId($dropFolderId)
	{
		$this->dropFolderId = $dropFolderId;
	}
}