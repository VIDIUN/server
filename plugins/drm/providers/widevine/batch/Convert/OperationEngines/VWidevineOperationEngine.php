<?php

class VWidevineOperationEngine extends VOperationEngine
{
	const PACKAGE_FILE_EXT = '.wvm';
	
	/**
	 * @var array
	 * batch job parameters
	 */
	private $params;
	
	/**
	 * @var string
	 * Name of the package, used as asset name in Widevine. Unique for the provider
	 */
	private $packageName;
	
	private $actualSrcAssetParams = array();
	
	private $originalEntryId;
	
	public function __construct($params, $outFilePath)
	{
		$this->params = $params;
	}
	
	/* (non-PHPdoc)
	 * @see VOperationEngine::getCmdLine()
	 */
	protected function getCmdLine() {}

	/*
	 * (non-PHPdoc)
	 * @see VOperationEngine::doOperation()
	 * 
	 * prepare PackageNotify request and send it to Widevine VOD Packager for encryption
	 */
	protected function doOperation()
	{
		VBatchBase::impersonate($this->job->partnerId);
		
		$entry = VBatchBase::$vClient->baseEntry->get($this->job->entryId);
		$this->buildPackageName($entry);
		
		VidiunLog::info('start Widevine packaging: '.$this->packageName);
		
		$drmPlugin = VidiunDrmClientPlugin::get(VBatchBase::$vClient);
		$profile = $drmPlugin->drmProfile->getByProvider(VidiunDrmProviderType::WIDEVINE);
		$wvAssetId = $this->registerAsset($profile);
		$this->encryptPackage($profile);		
		$this->updateFlavorAsset($wvAssetId);
		
		VBatchBase::unimpersonate();	
		
		return true;
	}
	
	private function registerAsset($profile)
	{
		$wvAssetId = '';
		$policy = null;
		$errorMessage = '';
		
		if($this->operator->params)
		{
			$params = explode(',', $this->operator->params);
			foreach ($params as $paramStr) 
			{
				$param = explode('=', $paramStr);
				if(isset($param[0]) && $param[0] == 'policy')
				{
					$policy = $param[1];
				}
			}
		}
		
		$wvAssetId = VWidevineBatchHelper::sendRegisterAssetRequest(
										$profile->regServerHost,
										$this->packageName,
										null,
										$profile->portal,
										$policy,
										$this->data->flavorParamsOutput->widevineDistributionStartDate,
										$this->data->flavorParamsOutput->widevineDistributionEndDate,
										$profile->iv, 
										$profile->key, 									
										$errorMessage);

		if(!$wvAssetId)
		{
			VBatchBase::unimpersonate();
			$logMessage = 'Asset registration failed, asset name: '.$this->packageName.' error: '.$errorMessage;
			VidiunLog::err($logMessage);
			throw new VOperationEngineException($logMessage);
		}
										
		VidiunLog::info('Widevine asset id: '.$wvAssetId);
		
		return $wvAssetId;
	}
	
	private function encryptPackage($profile)
	{
		$inputFiles = $this->getInputFilesList();
		$this->data->destFileSyncLocalPath = $this->data->destFileSyncLocalPath . self::PACKAGE_FILE_EXT;
				
		$returnValue = $this->executeEncryptPackageCmd($profile, $inputFiles);
		
		//try to fix source assets sync and re-try encryption
		if($returnValue == VWidevineBatchHelper::FIX_ASSET_ERROR_RETURN_CODE)
		{
			VidiunLog::info("Trying to fix input files due to mismatch error ...");
			$fixedInputFiles = $this->fixInputAssets($inputFiles);
			$returnValue = $this->executeEncryptPackageCmd($profile, $fixedInputFiles);
			if($returnValue != 0)
			{
				$this->handleEncryptPackageError($returnValue);
			}			
		}
		else if($returnValue != 0)
		{
			$this->handleEncryptPackageError($returnValue);
		}										
	}
	
	private function executeEncryptPackageCmd($profile, $inputFiles)
	{
		$returnValue = 0;
		$output = array();
		
		$cmd = VWidevineBatchHelper::getEncryptPackageCmdLine(
										$this->params->widevineExe, 
										$profile->regServerHost, 
										$profile->iv, 
										$profile->key, 
										$this->packageName, 
										$inputFiles, 
										$this->data->destFileSyncLocalPath,
										$profile->maxGop,
										$profile->portal);
										
		exec($cmd, $output, $returnValue);
		VidiunLog::info('Command execution output: '.print_r($output, true));	

		if($returnValue != 0)
		{
			if(strstr(implode(" ", $output), VWidevineBatchHelper::FIX_ASSET_ERROR))
			{
				return VWidevineBatchHelper::FIX_ASSET_ERROR_RETURN_CODE;
			}
		}
		return $returnValue;
	}
	
	private function handleEncryptPackageError($returnValue)
	{
		VBatchBase::unimpersonate();
		$errorMessage = '';
		$errorMessage = VWidevineBatchHelper::getEncryptPackageErrorMessage($returnValue);
		$logMessage = 'Package encryption failed, asset name: '.$this->packageName.' error: '.$errorMessage . ' error code: ' .$returnValue;
		VidiunLog::err($logMessage);
		throw new vTemporaryException ($logMessage);
	}
	
	private function getAssetIdsWithRedundantBitrates()
	{
		$srcAssetIds = array();
		foreach ($this->data->srcFileSyncs as $srcFileSyncDesc) 
		{
			$srcAssetIds[] = $srcFileSyncDesc->assetId;
		}		
		$srcAssetIds = implode(',', $srcAssetIds);
		
		$filter = new VidiunAssetFilter();
		$filter->entryIdEqual = $this->job->entryId;
		$filter->idIn = $srcAssetIds;
		$flavorAssetList = VBatchBase::$vClient->flavorAsset->listAction($filter);	

		$redundantAssets = array();
		if(count($flavorAssetList->objects) > 0)
		{
			$bitrates = array();			
			foreach ($flavorAssetList->objects as $flavorAsset) 
			{
				/* @var $flavorAsset VidiunFlavorAsset */
				if(in_array($flavorAsset->bitrate, $bitrates))
					$redundantAssets[] = $flavorAsset->id;
				else 
					$bitrates[] = $flavorAsset->bitrate;
			}
		}		
		return $redundantAssets;
	}
	
	private function getInputFilesList()
	{		
		$redundantAssets = $this->getAssetIdsWithRedundantBitrates();
		$inputFilesArr = array();
		
		foreach ($this->data->srcFileSyncs as $srcFileSyncDescriptor) 
		{
			if(in_array($srcFileSyncDescriptor->assetId, $redundantAssets))
			{
				VidiunLog::info('Skipping flavor asset due to redundant bitrate: '.$srcFileSyncDescriptor->assetId);
			}
			else 
			{
				$inputFilesArr[] = $srcFileSyncDescriptor->actualFileSyncLocalPath;
				$this->actualSrcAssetParams[] = $srcFileSyncDescriptor->assetParamsId;
			}
		}		
		return implode(',', $inputFilesArr);
	}
	
	private function buildPackageName($entry)
	{	
		$flavorAssetId = $this->data->flavorAssetId;
		$this->originalEntryId = $this->job->entryId;
			
		if($entry->replacedEntryId)
		{
			$this->originalEntryId = $entry->replacedEntryId;
			$filter = new VidiunAssetFilter();
			$filter->entryIdEqual = $entry->replacedEntryId;
			$filter->tagsLike = 'widevine'; 
			$flavorAssetList = VBatchBase::$vClient->flavorAsset->listAction($filter);
			
			if(count($flavorAssetList->objects) > 0)
			{
				$replacedFlavorParamsId = $this->data->flavorParamsOutput->flavorParamsId;
				foreach ($flavorAssetList->objects as $flavorAsset) 
				{
					/* @var $flavorAsset VidiunFlavorAsset */
					if($flavorAsset->flavorParamsId == $replacedFlavorParamsId)
					{
						$flavorAssetId = $flavorAsset->id;
						break;
					}
				}
			}
		}
		
		$this->packageName = $this->originalEntryId.'_'.$flavorAssetId;
	}
	
	private function updateFlavorAsset($wvAssetId = null)
	{
		$updatedFlavorAsset = new VidiunWidevineFlavorAsset();
		if($wvAssetId)
			$updatedFlavorAsset->widevineAssetId = $wvAssetId;
		$updatedFlavorAsset->actualSourceAssetParamsIds = implode(',', $this->actualSrcAssetParams);		
		$wvDistributionStartDate = $this->data->flavorParamsOutput->widevineDistributionStartDate;
		$wvDistributionEndDate = $this->data->flavorParamsOutput->widevineDistributionEndDate;
		$updatedFlavorAsset->widevineDistributionStartDate = $wvDistributionStartDate;
		$updatedFlavorAsset->widevineDistributionEndDate = $wvDistributionEndDate;
		VBatchBase::$vClient->flavorAsset->update($this->data->flavorAssetId, $updatedFlavorAsset);		
	}
	
	private function fixInputAssets($inputFiles)
	{
		$localTmpPath = dirname($this->data->destFileSyncLocalPath);
		$fixedInputFiles = array();
		$inputFiles = explode(',', $inputFiles);
		$returnValue = 0;
		$output = array();
		foreach ($inputFiles as $inputFile) 
		{
			$fixedInputFile = $localTmpPath.'/'.basename($inputFile);
			$cmd = VWidevineBatchHelper::getFixAssetCmdLine($this->params->ffmpegCmd, $inputFile, $fixedInputFile);
			$lastLine = exec($cmd, $output, $returnValue);
			
			VidiunLog::info('Command execution output: '.print_r($output, true));
		
			if($returnValue != 0)
			{
				VBatchBase::unimpersonate();
				$logMessage = 'Asset fix failed: '.$inputFile.' error: '.$lastLine;
				VidiunLog::err($logMessage);
				throw new VOperationEngineException($logMessage);
			}										
			$fixedInputFiles[] = $fixedInputFile;
		}
		
		return implode(',', $fixedInputFiles);
	}
	
}