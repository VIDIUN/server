<?php
/**
 * Kontiki job data
 * @package plugins.kontiki
 * @subpackage model
 * */
class vKontikiStorageDeleteJobData extends vStorageDeleteJobData
{
    /**
     * Unique Kontiki MOID for the content uploaded to Kontiki
     * @var string
     */
    protected $contentMoid;

    /**
     * @var string
     */
    protected $serviceToken;
    

    public function setContentMoid($contentMoid) 
    {
        $this->contentMoid = $contentMoid;
    }

    public function getContentMoid() 
    {
        return $this->contentMoid;
    }
    
    public function setServiceToken($v) 
    {
        $this->serviceToken = $v;
    }

    public function getServiceToken() 
    {
        return $this->serviceToken;
    }
    
    public function setJobData(StorageProfile $storage, FileSync $fileSync)
    {
        /* @var $storage KontikiStorageProfile */
        $this->setServerUrl($storage->getStorageUrl()); 
        $this->setServiceToken($storage->getServiceToken()); 
        if ($fileSync->getObjectType() != FileSyncObjectType::ASSET)
            throw new vCoreException("Incompatible filesync type", vCoreException::INTERNAL_SERVER_ERROR);
        
        $this->setContentMoid($fileSync->getFilePath());
    }
}
