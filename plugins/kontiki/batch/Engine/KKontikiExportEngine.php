<?php
/**
*/
class VKontikiExportEngine extends VExportEngine 
{
    protected $partnerId;
    
	/**
	 * @var KontikiAPWrapper
	 */
	protected $kontikiAPIWrapper;
	
    protected static $pending_statuses = array ("PENDING_RESTART","RESTARTING","PENDING","PROCESSING","TRANSCODE_QUEUED","TRANSCODE_DONE","TRANSCODING","UPLOADING","SCANNING","ENCRYPTING","ENCRYPT_DONE","SIGNING","SIGN_DONE","RESIZING_THUMBNAILS","RESIZING_THUMBNAILS_DONE","PUBLISHING","PENDING_APPROVAL");
    
    protected static $failed_statuses = array("RESTART_FAILED","UNPROCESSABLE","TRANSCODE_FAILED","TRANSCODE_ERROR","TRANSCODE_CANCELLED","TRANSCODE_INTERRUPTED","UPLOADING_FAILED","SCAN_FAILED","SCAN_ERROR","ENCRYPT_FAILED","SIGN_FAILED","RESIZING_THUMBNAILS_FAILED","SMIL_FILE_GENERATION_FAILED","PUBLISHING_FAILED","PENDING_APPROVAL_FAIL","READY_FAIL" );
    
    const FINISHED_STATUS = 'READY';
	
	
	function __construct($data, $partnerId)
	{
		parent::__construct($data);
        $this->partnerId = $partnerId;
		$this->kontikiAPIWrapper = new KontikiAPIWrapper($data->serverUrl);
    }
	
	/* (non-PHPdoc)
	 * @see VExportEngine::export()
	 */
	public function export() 
	{
		VBatchBase::impersonate($this->partnerId);
		$url = VBatchBase::$vClient->flavorAsset->getUrl($this->data->flavorAssetId, null, true);
		$kontikiResult = $this->kontikiAPIWrapper->addKontikiUploadResource(KontikiPlugin::SERVICE_TOKEN_PREFIX . base64_encode($this->data->serviceToken), $url);
		VidiunLog::info("Upload result: " . print_r($kontikiResult, true));
        
        if (!$kontikiResult->moid)
            throw new vApplicativeException(VidiunBatchJobAppErrors::MISSING_PARAMETERS, "missing mandatory parameter moid");
                    
        $uploadMoid = strval($kontikiResult->moid);
        
        VBatchBase::$vClient->startMultiRequest();
        $flavorAsset = VBatchBase::$vClient->flavorAsset->get($this->data->flavorAssetId);
        $entry = VBatchBase::$vClient->baseEntry->get($flavorAsset->entryId);
        $result = VBatchBase::$vClient->doMultiRequest();
        VBatchBase::unimpersonate();
		if (!$result || !count($result))
		{
			throw new Exception();
		}
		else if (!($result[0]) instanceof VidiunFlavorAsset)
		{
			throw new VidiunException($result[0]['message'], $result[0]['code']);
		}
		else if (!($result[1]) instanceof VidiunBaseEntry)
		{
			throw new VidiunException($result[1]['message'], $result[1]['code']);
		}
        $contentResourceResult = $this->kontikiAPIWrapper->addKontikiVideoContentResource(KontikiPlugin::SERVICE_TOKEN_PREFIX . base64_encode($this->data->serviceToken), $uploadMoid, $result[1], $result[0]);
        VidiunLog::info("Content resource result: " . $contentResourceResult);
        
        $this->data->contentMoid = strval($contentResourceResult->content->moid);
        
        return false;
	}

	/* (non-PHPdoc)
	 * @see VExportEngine::verifyExportedResource()
	 */
	public function verifyExportedResource()
    {
		$contentResource = $this->kontikiAPIWrapper->getKontikiContentResource(KontikiPlugin::SERVICE_TOKEN_PREFIX . base64_encode($this->data->serviceToken), $this->data->contentMoid);
        if (!$contentResource)
        {
            throw new vApplicativeException(VidiunBatchJobAppErrors::EXTERNAL_ENGINE_ERROR, "Failed to retrieve Kontiki content resource");
        }
        
        VidiunLog::info("content resource:". $contentResource->asXML());
        if (!strval($contentResource->content->contentStatusType))
        {
            throw new vApplicativeException(VidiunBatchJobAppErrors::EXTERNAL_ENGINE_ERROR, "Unexpected: Kontiki contentResource does not contain contentResourceStatusType");
        }
        
        $contentResourceStatus = strval($contentResource->content->contentStatusType);
        if ($contentResourceStatus == self::FINISHED_STATUS)
            return true;
        if (in_array($contentResourceStatus, self::$pending_statuses))
        {
            return false;
        }
        if (in_array($contentResourceStatus, self::$failed_statuses))
        {
            $nodeName = 'related-upload';
            throw new vApplicativeException(VidiunBatchJobAppErrors::EXTERNAL_ENGINE_ERROR, $contentResource->content->$nodeName->statusLog);
        }
	}
	
	/* (non-PHPdoc)
     * @see VExportEngine::verifyExportedResource()
     */
	public function delete ()
	{
	    $deleteResult = $this->kontikiAPIWrapper->deleteKontikiContentResource(KontikiPlugin::SERVICE_TOKEN_PREFIX . base64_encode($this->data->serviceToken), $this->data->contentMoid);
        if (!$deleteResult)
        {
            throw new vApplicativeException(VidiunBatchJobAppErrors::EXTERNAL_ENGINE_ERROR, "Failed to delete content resource");
        }
        
        return true;
	}

	
}