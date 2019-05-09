<?php
/**
 * @package plugins.exampleDistribution
 * @subpackage lib
 */
class ExampleDistributionEngine extends DistributionEngine implements 
	IDistributionEngineUpdate,
	IDistributionEngineSubmit,
	IDistributionEngineReport,
	IDistributionEngineDelete,
	IDistributionEngineCloseSubmit
{
	const FTP_SERVER_URL = 'example.ftp.com';
	
	/**
	 * Demonstrate using batch configuration
	 * Contains the path to the update XML template file
	 * @var string
	 */
	private $updateXmlTemplate;
	
	/* (non-PHPdoc)
	 * @see DistributionEngine::configure()
	 */
	public function configure()
	{
		parent::configure();
		
		// set default value
		$this->updateXmlTemplate = dirname(__FILE__) . '/../xml/update.template.xml';
		
		// load value from batch configuration
		if(VBatchBase::$taskConfig->params->updateXmlTemplate)
			$this->updateXmlTemplate = VBatchBase::$taskConfig->params->updateXmlTemplate;
	}

	/* (non-PHPdoc)
	 * @see IDistributionEngineSubmit::submit()
	 * 
	 * Demonstrate asynchronous external API usage
	 */
	public function submit(VidiunDistributionSubmitJobData $data)
	{
		// validates received object types
				
		if(!$data->distributionProfile || !($data->distributionProfile instanceof VidiunExampleDistributionProfile))
			VidiunLog::err("Distribution profile must be of type VidiunExampleDistributionProfile");
	
		if(!$data->providerData || !($data->providerData instanceof VidiunExampleDistributionJobProviderData))
			VidiunLog::err("Provider data must be of type VidiunExampleDistributionJobProviderData");
		
		// call the actual submit action
		$this->handleSubmit($data, $data->distributionProfile, $data->providerData);
		
		// always return false to be closed asynchronously by the closer
		return false;
	}

	/* (non-PHPdoc)
	 * @see IDistributionEngineCloseSubmit::closeSubmit()
	 */
	public function closeSubmit(VidiunDistributionSubmitJobData $data)
	{
		return ExampleExternalApiService::wasSubmitSucceed($data->remoteId);
	}

	/* (non-PHPdoc)
	 * @see IDistributionEngineDelete::delete()
	 */
	public function delete(VidiunDistributionDeleteJobData $data)
	{
		// demonstrate asynchronous XML delivery usage from XSL
		
		if(!$data->distributionProfile || !($data->distributionProfile instanceof VidiunExampleDistributionProfile))
			VidiunLog::err("Distribution profile must be of type VidiunExampleDistributionProfile");
	
		if(!$data->providerData || !($data->providerData instanceof VidiunExampleDistributionJobProviderData))
			VidiunLog::err("Provider data must be of type VidiunExampleDistributionJobProviderData");
		
		$this->handleDelete($data, $data->distributionProfile, $data->providerData);
		
		return false;
	}
	
	/* (non-PHPdoc)
	 * @see IDistributionEngineUpdate::update()
	 * 
	 * demonstrate asynchronous XML delivery usage from template and uploading the media
	 */
	public function update(VidiunDistributionUpdateJobData $data)
	{
		if(!$data->distributionProfile || !($data->distributionProfile instanceof VidiunExampleDistributionProfile))
			VidiunLog::err("Distribution profile must be of type VidiunExampleDistributionProfile");
	
		if(!$data->providerData || !($data->providerData instanceof VidiunExampleDistributionJobProviderData))
			VidiunLog::err("Provider data must be of type VidiunExampleDistributionJobProviderData");
		
		$this->handleUpdate($data, $data->distributionProfile, $data->providerData);
		
		return false;
	}

	/* (non-PHPdoc)
	 * @see IDistributionEngineReport::fetchReport()
	 * 
	 * Demonstrate asynchronous http url parsing
	 */
	public function fetchReport(VidiunDistributionFetchReportJobData $data)
	{
		// TODO
		return false;
	}
	
	/**
	 * @param VidiunDistributionJobData $data
	 * @param VidiunExampleDistributionProfile $distributionProfile
	 * @param VidiunExampleDistributionJobProviderData $providerData
	 */
	protected function handleSubmit(VidiunDistributionJobData $data, VidiunExampleDistributionProfile $distributionProfile, VidiunExampleDistributionJobProviderData $providerData)
	{
		$entryId = $data->entryDistribution->entryId;
		$partnerId = $distributionProfile->partnerId;
		$entry = $this->getEntry($partnerId, $entryId);

		// populate the external API object with the Vidiun entry data
		$exampleExternalApiMediaItem = new ExampleExternalApiMediaItem();
		$exampleExternalApiMediaItem->resourceId = $entry->id;
		$exampleExternalApiMediaItem->title = $entry->name;
		$exampleExternalApiMediaItem->description = $entry->description;
		$exampleExternalApiMediaItem->width = $entry->width;
		$exampleExternalApiMediaItem->height = $entry->height;
				
		// loads ftp manager
		$engineOptions = isset(VBatchBase::$taskConfig->engineOptions) ? VBatchBase::$taskConfig->engineOptions->toArray() : array();
		$ftpManager = vFileTransferMgr::getInstance(vFileTransferMgrType::FTP, $engineOptions);
		$ftpManager->login(self::FTP_SERVER_URL, $distributionProfile->username, $distributionProfile->password);
		
		// put the thumbnail on the FTP with the entry id as naming convention
		$remoteFile = $entry->id . '.jpg';
		$ftpManager->putFile($remoteFile, $providerData->thumbAssetFilePath);
		
		// put the video files on the FTP with the entry id as naming convention and index
		foreach($providerData->videoAssetFilePaths as $index => $videoAssetFilePath)
		{
			$localPath = $videoAssetFilePath->path;
			$pathInfo = pathinfo($localPath);
    		$fileExtension = $pathInfo['extension'];
    		
			$remoteFile = "{$entry->id}-{$index}.{$fileExtension}";
			$ftpManager->putFile($remoteFile, $localPath);
		}
		
		$remoteId = ExampleExternalApiService::submit($exampleExternalApiMediaItem);
		$data->remoteId = $remoteId;
	}
	
	/**
	 * @param VidiunDistributionJobData $data
	 * @param VidiunExampleDistributionProfile $distributionProfile
	 * @param VidiunExampleDistributionJobProviderData $providerData
	 */
	protected function handleDelete(VidiunDistributionJobData $data, VidiunExampleDistributionProfile $distributionProfile, VidiunExampleDistributionJobProviderData $providerData)
	{
		// TODO
	}
	
	/**
	 * @param VidiunDistributionJobData $data
	 * @param VidiunExampleDistributionProfile $distributionProfile
	 * @param VidiunExampleDistributionJobProviderData $providerData
	 */
	protected function handleUpdate(VidiunDistributionJobData $data, VidiunExampleDistributionProfile $distributionProfile, VidiunExampleDistributionJobProviderData $providerData)
	{
		$entryId = $data->entryDistribution->entryId;
		$partnerId = $distributionProfile->partnerId;
		$entry = $this->getEntry($partnerId, $entryId);
		
		$feed = new VDOMDocument();
		$feed->load($this->updateXmlTemplate);
		$feed->documentElement->setAttribute('mediaId', $data->remoteId);
		
		$nodes = array(
			'title' => 'name',
			'description' => 'description',
			'width' => 'width',
			'height' => 'height',
		);
		foreach($nodes as $nodeName => $entryAttribute)
		{
			$nodeElements = $feed->getElementsByTagName($nodeName);
			foreach($nodeElements as $nodeElement)
				$nodeElement->textContent = $entry->$entryAttribute;
		}
	
		// get the first asset id
		$thumbAssetIds = explode(',', $data->entryDistribution->thumbAssetIds);
		$thumbAssetId = reset($thumbAssetIds);
		$thumbElements = $feed->getElementsByTagName('thumb');
		$thumbElement = reset($thumbElements);
		$thumbElement->textContent = $this->getAssetUrl($thumbAssetId);
			
		$videosElements = $feed->getElementsByTagName('videos');
		$videosElement = reset($videosElements);
	
		$flavorAssets = $this->getFlavorAssets($partnerId, $data->entryDistribution->flavorAssetIds);
		VBatchBase::impersonate($partnerId);
		foreach($flavorAssets as $flavorAsset)
		{
			$url = $this->getAssetUrl($flavorAsset->id);
			
			$videoElement = $feed->createElement('video');
			$videoElement->textContent = $url;
			$videosElement->appendChild($videoElement);
		}
		VBatchBase::unimpersonate();
			
			
		$localFile = tempnam(sys_get_temp_dir(), 'example-update-');
		$feed->save($localFile);
		
		// loads ftp manager
		$engineOptions = isset(VBatchBase::$taskConfig->engineOptions) ? VBatchBase::$taskConfig->engineOptions->toArray() : array();
		$ftpManager = vFileTransferMgr::getInstance(vFileTransferMgrType::FTP, $engineOptions);
		$ftpManager->login(self::FTP_SERVER_URL, $distributionProfile->username, $distributionProfile->password);
		
		// put the XML file on the FTP
		$remoteFile = $entryId . '.xml';
		$ftpManager->putFile($remoteFile, $localFile);
		
		return true;
	}
}