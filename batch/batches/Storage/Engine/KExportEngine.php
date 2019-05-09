<?php
/**
 * 
 */
abstract class VExportEngine
{
	/**
	 * @var VidiunStorageJobData
	 */
	protected $data;
	
	/**
	 * @param VidiunStorageJobData $data
	 */
	public function __construct(VidiunStorageJobData $data)
	{
		$this->data = $data;
	}
	
	/**
	 * @return bool
	 */
	abstract function export ();
	
	
	/**
	 * @return bool
	 */
	abstract function verifyExportedResource ();
    
    /**
     * @return bool
     */
    abstract function delete();
	
	/**
	 * @param int $protocol
	 * @param VidiunStorageExportJobData $data
	 * @return VExportEngine
	 */
	public static function getInstance ($protocol, $partnerId, VidiunStorageJobData $data)
	{
		switch ($protocol)
		{
			case VidiunStorageProfileProtocol::FTP:
			case VidiunStorageProfileProtocol::VIDIUN_DC:
			case VidiunStorageProfileProtocol::S3:
			case VidiunStorageProfileProtocol::SCP:
			case VidiunStorageProfileProtocol::SFTP:
			case VidiunStorageProfileProtocol::LOCAL:
				return new VFileTransferExportEngine($data, $protocol);
			default:
				return VidiunPluginManager::loadObject('VExportEngine', $protocol, array($data, $partnerId));
		}
	}
}