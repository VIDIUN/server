<?php
/**
 * @package plugins.caption
 * @subpackage Scheduler
 */
class VAsyncCopyCaptions extends VJobHandlerWorker
{

	const START_TIME_ASC = "+startTime";

	/*
	 * @var VidiunCaptionSearchClientPlugin
	 */
	private $captionSearchClientPlugin = null;

	/*
	* @var VidiunCaptionClientPlugin
	*/
	private $captionClientPlugin = null;

	/**
	 * @param VSchedularTaskConfig $taskConfig
	 */
	public function __construct($taskConfig = null)
	{
		parent::__construct($taskConfig);

		$this->captionSearchClientPlugin = VidiunCaptionSearchClientPlugin::get(self::$vClient);
		$this->captionClientPlugin = VidiunCaptionClientPlugin::get(self::$vClient);
	}


	public static function getType()
	{
		return VidiunBatchJobType::COPY_CAPTIONS;
	}
	/**
	 * (non-PHPdoc)
	 * @see VBatchBase::getJobType()
	 */
	protected function getJobType()
	{
		return VidiunBatchJobType::COPY_CAPTIONS;
	}

	/* (non-PHPdoc)
	 * @see VJobHandlerWorker::exec()
	 */
	protected function exec(VidiunBatchJob $job)
	{
		return $this->copyCaptions($job, $job->data);
	}

	/**
	 * copy captions on specific time frame
	 * @throws vApplicativeException
	 */
	private function copyCaptions(VidiunBatchJob $job, VidiunCopyCaptionsJobData $data)
	{
		$firstClip = $data->clipsDescriptionArray[0];
		$this->updateJob($job, "Start copying captions from [$firstClip->sourceEntryId] to [$data->entryId]", VidiunBatchJobStatus::PROCESSING);
		self::impersonate($job->partnerId);
		$this->copyFromClipToDestination($data, $data->clipsDescriptionArray);
		self::unimpersonate();
		$this->closeJob($job, null, null, 'Finished copying captions', VidiunBatchJobStatus::FINISHED);
		return $job;
	}


	private function getAllCaptionAsset($entryId)
	{
		VidiunLog::info("Retrieve all caption assets for: [$entryId]");
		$filter = new VidiunAssetFilter();
		$filter->entryIdEqual = $entryId;
		try
		{
			$captionAssetsList = $this->captionClientPlugin->captionAsset->listAction($filter);
		}
		catch(Exception $e)
		{
			VidiunLog::info("Can't list caption assets for entry id [$entryId] " . $e->getMessage());
		}
		return $captionAssetsList->objects;
	}

	private function retrieveCaptionAssetsOnlyFromSupportedTypes($originalCaptionAssets)
	{
		$unsupportedFormats = $this->getUnsupportedFormats();
		$originalCaptionAssetsFiltered = array();
		foreach ($originalCaptionAssets as $originalCaptionAsset)
		{
			if (!in_array($originalCaptionAsset->format, $unsupportedFormats))
				array_push($originalCaptionAssetsFiltered, $originalCaptionAsset);
		}
		$objectsNum = count($originalCaptionAssetsFiltered);
		VidiunLog::info("[$objectsNum] caption assets left after filtering");
		return $originalCaptionAssetsFiltered;
	}


	private function getUnsupportedFormats()
	{
		$unsupportedFormats = array (CaptionType::CAP);
		return $unsupportedFormats;
	}

	private function cloneCaption($targetEntryId, $originalCaptionAsset)
	{
		VidiunLog::info("Start copying properties from caption asset: [{$originalCaptionAsset->id}] to new caption asset on entryId: [$targetEntryId]");
		$captionAsset = new VidiunCaptionAsset();
		$propertiesToCopy = array("tags", "fileExt", "language", "label", "format","isDefault");
		foreach ($propertiesToCopy as $property)
			$captionAsset->$property = $originalCaptionAsset->$property;
		try
		{
			$newCaption = $this->captionClientPlugin->captionAsset->add($targetEntryId , $captionAsset);
		}
		catch(Exception $e)
		{
			VidiunLog::info("Couldn't create new caption asset for entry id: [$targetEntryId]" . $e->getMessage());
		}
		return $newCaption;
	}

	private function loadNewCaptionAssetFile($captionAssetId, $contentResource)
	{
		try
		{
			$updatedCaption = $this->captionClientPlugin->captionAsset->setContent($captionAssetId, $contentResource);
		}
		catch(Exception $e)
		{
			VidiunLog::info("Can't set content to caption asset id: [$captionAssetId]" . $e->getMessage());
			return null;
		}
		return $updatedCaption;
	}


	private function createNewCaptionsFile($captionAssetId, $offset, $duration , $format, $fullCopy, $globalOffset){
		VidiunLog::info("Create new caption file based on captionAssetId:[$captionAssetId] in format: [$format] with offset: [$offset] and duration: [$duration]");
		$captionContent = "";

		$unsupported_formats = $this->getUnsupportedFormats();

		if($fullCopy)
		{
			VidiunLog::info("fullCopy mode - copy the content of captionAssetId: [$captionAssetId] without editing");
			$captionContent = $this->getCaptionContent($captionAssetId);
		}
		else
		{
			VidiunLog::info("Copy only the relevant content of captionAssetId: [$captionAssetId]");
			$endTime = $offset + $duration;

			if (!in_array($format, $unsupported_formats))
			{
				$captionContent = $this->getCaptionContent($captionAssetId);
				$captionsContentManager = vCaptionsContentManager::getCoreContentManager($format);
				$captionContent = $captionsContentManager->buildFile($captionContent, $offset, $endTime, $globalOffset);
			}
			else
				VidiunLog::info("copying captions for format: [$format] is not supported");
		}

		return $captionContent;
	}


	private function getCaptionContent($captionAssetId)
	{
		VidiunLog::info("Retrieve caption assets content for captionAssetId: [$captionAssetId]");

		try
		{
			$captionAssetContentUrl= $this->captionClientPlugin->captionAsset->serve($captionAssetId);
			$captionAssetContent = VCurlWrapper::getContent($captionAssetContentUrl);
		}
		catch(Exception $e)
		{
			VidiunLog::info("Can't serve caption asset id [$captionAssetId] " . $e->getMessage());
		}
		return $captionAssetContent;
	}

	/**
	 * @param VidiunCopyCaptionsJobData $data
	 * @param VidiunClipDescriptionArray $clipDescriptionArray
	 * @throws vApplicativeException
	 */
	private function copyFromClipToDestination(VidiunCopyCaptionsJobData $data, $clipDescriptionArray)
	{
		$errorMsg = '';
		//currently only one source
		$originalCaptionAssets = $this->getAllCaptionAsset($clipDescriptionArray[0]->sourceEntryId);
		if (!$data->fullCopy)
			$originalCaptionAssets = $this->retrieveCaptionAssetsOnlyFromSupportedTypes($originalCaptionAssets);
		foreach ($originalCaptionAssets as $originalCaptionAsset)
		{
			if ($originalCaptionAsset->status != VidiunCaptionAssetStatus::READY)
				continue;
			$newCaptionAsset = $this->cloneCaption($data->entryId, $originalCaptionAsset);
			$newCaptionAssetResource = new VidiunStringResource();
			$this->clipAndConcatSub($data, $clipDescriptionArray, $originalCaptionAsset, $newCaptionAsset, $newCaptionAssetResource,$errorMsg);
			$updatedCaption = $this->loadNewCaptionAssetFile($newCaptionAsset->id, $newCaptionAssetResource);
			if (!$updatedCaption)
				throw new vApplicativeException(VidiunBatchJobAppErrors::MISSING_ASSETS, "Created caption asset with id: [$newCaptionAsset->id], but couldn't load the new captions file to it");
		}
		if ($errorMsg)
			throw new vApplicativeException(VidiunBatchJobAppErrors::MISSING_ASSETS, $errorMsg);
	}

	/**
	 * @param VidiunCopyCaptionsJobData $data
	 * @param $clipDescriptionArray
	 * @param $originalCaptionAsset
	 * @param $newCaptionAsset
	 * @param $newCaptionAssetResource
	 * @param string $errorMsg
	 * @return string
	 */
	private function clipAndConcatSub(VidiunCopyCaptionsJobData $data, $clipDescriptionArray, $originalCaptionAsset, $newCaptionAsset, $newCaptionAssetResource, &$errorMsg)
	{
		foreach ($clipDescriptionArray as $clipDescription)
		{
			$toAppend = $this->createNewCaptionsFile($originalCaptionAsset->id, $clipDescription->startTime, $clipDescription->duration,
				$newCaptionAsset->format, $data->fullCopy, $clipDescription->offsetInDestination);
			if ($toAppend && $newCaptionAssetResource->content)
			{
				$captionsContentManager = vCaptionsContentManager::getCoreContentManager($newCaptionAsset->format);
				$newCaptionAssetResource->content = $captionsContentManager->merge($newCaptionAssetResource->content, $toAppend);
			}
			elseif(!$newCaptionAssetResource->content)
				$newCaptionAssetResource->content = $toAppend;
			if (is_null($toAppend))
			{
				$errorMsg = "Couldn't create new captions file for captionAssetId: [$originalCaptionAsset->id] and format: [$newCaptionAsset->format]";
			}
		}
	}

}