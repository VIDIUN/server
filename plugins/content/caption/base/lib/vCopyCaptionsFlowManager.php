<?php
class vCopyCaptionsFlowManager implements  vObjectAddedEventConsumer, vObjectChangedEventConsumer, vObjectReplacedEventConsumer
{
	/* (non-PHPdoc)
  * @see vObjectReplacedEventConsumer::shouldConsumeReplacedEvent()
  */
	public function shouldConsumeReplacedEvent(BaseObject $object)
	{
		if($object instanceof entry)
			return true;

		return false;
	}

	/* (non-PHPdoc)
	 * @see vObjectAddedEventConsumer::shouldConsumeAddedEvent()
	 */
	public function shouldConsumeAddedEvent(BaseObject $object)
	{
		if($object instanceof entry && $object->getReplacedEntryId() && $object->getIsTemporary() && !$object->getTempTrimEntry())
			return true;

		return false;
	}

	/* (non-PHPdoc)
	  * @see vObjectAddedEventConsumer::objectAdded()
	  */
	public function objectAdded(BaseObject $object, BatchJob $raisedJob = null)
	{
		if($object instanceof entry && $object->getReplacedEntryId() && $object->getIsTemporary() && !$object->getTempTrimEntry())
		{
			$this->copyUpdatedCaptionsToEntry($object);
		}

		return true;
	}


	/* (non-PHPdoc)
	 * @see vObjectReplacedEventConsumer::objectReplaced()
	*/
	public function objectReplaced(BaseObject $object, BaseObject $replacingObject, BatchJob $raisedJob = null) {
		$clipAttributes = self::getClipAttributesFromEntry($replacingObject);
		$clipConcatTrimFlow = self::isClipConcatTrimFlow($replacingObject);
		//replacement as a result of trimming
		if (!is_null($clipAttributes) || $clipConcatTrimFlow)
		{
			vEventsManager::setForceDeferredEvents(true);
			$c = new Criteria();
			$c->add(assetPeer::ENTRY_ID, $object->getId());
			$types = array(CaptionPlugin::getAssetTypeCoreValue(CaptionAssetType::CAPTION));
			if(count($types))
				$c->add(assetPeer::TYPE, $types, Criteria::IN);
			$this->deleteCaptions($c);
			//copy captions from replacement entry
			$replacementCaptions = assetPeer::retrieveByEntryId($replacingObject->getId(), array(CaptionPlugin::getAssetTypeCoreValue(CaptionAssetType::CAPTION)));
			foreach ($replacementCaptions as $captionAsset)
			{
				$newCaptionAsset = $captionAsset->copyToEntry($object->getId());
				$newCaptionAsset->save();
			}
			vEventsManager::flushEvents();
		}
		return true;
	}


	protected function deleteCaptions($c)
	{
		CuePointPeer::setUseCriteriaFilter(false);
		$captions = assetPeer::doSelect($c);
		$update = new Criteria();
		$update->add(assetPeer::STATUS, VidiunCaptionAssetStatus::DELETED);

		$con = Propel::getConnection(myDbHelper::DB_HELPER_CONN_MASTER);
		BasePeer::doUpdate($c, $update, $con);
		CuePointPeer::setUseCriteriaFilter(true);
		foreach($captions as $caption)
		{
			$caption->setStatus(VidiunCaptionAssetStatus::DELETED);
			VidiunLog::info("Deleted caption asset: [{$caption->getId()}]");
			vEventsManager::raiseEvent(new vObjectDeletedEvent($caption));
		}
	}

	/* (non-PHPdoc)
   * @see vObjectChangedEventConsumer::shouldConsumeChangedEvent()
   */
	public function shouldConsumeChangedEvent(BaseObject $object, array $modifiedColumns)
	{
		if($object instanceof entry)
		{
			if (myEntryUtils::wasEntryClipped($object, $modifiedColumns))
				return true;
		}
		return false;
	}

	/* (non-PHPdoc)
	 * @see vObjectChangedEventConsumer::objectChanged()
	 */
	public function objectChanged(BaseObject $object, array $modifiedColumns)
	{
		if($object instanceof entry)
		{
			if (myEntryUtils::wasEntryClipped($object, $modifiedColumns))
				$this->copyUpdatedCaptionsToEntry($object);
		}
		return true;
	}


	/**
	 *
	 * @param entry $destEntry new entry to copy and adjust captions from root entry to
	 */
	protected function copyUpdatedCaptionsToEntry(entry $destEntry)
	{
		$jobData = new vCopyCaptionsJobData();
		$jobData->setEntryId($destEntry->getId());

		//regular replacement
		if(!$destEntry->getTempTrimEntry() && $destEntry->getReplacedEntryId()){
			$sourceEntryId = $destEntry->getReplacedEntryId();
			$sourceEntry = entryPeer::retrieveByPK($sourceEntryId);
			if(!$sourceEntry)
			{
				VidiunLog::debug("Didn't copy captions for entry [{$destEntry->getId()}] because source entry [" . $sourceEntryId . "] wasn't found");
				return;
			}
			
			$captionAssets = assetPeer::retrieveByEntryId($sourceEntryId, array(CaptionPlugin::getAssetTypeCoreValue(CaptionAssetType::CAPTION)));
			if(!count($captionAssets))
			{
				VidiunLog::debug("No captions found on source entry [" . $sourceEntryId . "], no need to run copy captions job");
				return;
			}
			$vClipDescriptionArray = array();
			$vClipDescription = new vClipDescription();
			$vClipDescription->setSourceEntryId($sourceEntryId);
			$vClipDescription->setStartTime(0);
			$vClipDescription->setDuration($sourceEntry->getLengthInMsecs());
			$vClipDescriptionArray[] = $vClipDescription;
			$jobData->setFullCopy(true);
		}
		else { //trim or clip
			$clipAttributes = self::getClipAttributesFromEntry($destEntry);
			if (!is_null($clipAttributes))
			{
				$operationAttributes  = $destEntry->getOperationAttributes();
				$sourceEntry = entryPeer::retrieveByPK($destEntry->getSourceEntryId());
				if (is_null($sourceEntry))
				{
					VidiunLog::info("Didn't copy captions for entry [{$destEntry->getId()}] because source entry [" . $destEntry->getSourceEntryId() . "] wasn't found");
					return;
				}
				
				$sourceEntryId = $sourceEntry->getId();
				$captionAssets = assetPeer::retrieveByEntryId($sourceEntryId, array(CaptionPlugin::getAssetTypeCoreValue(CaptionAssetType::CAPTION)));
				if(!count($captionAssets))
				{
					VidiunLog::debug("No captions found on source entry [" . $sourceEntryId . "], no need to run copy captions job");
					return;
				}
				$globalOffset = 0;
				$vClipDescriptionArray = array();
				/** @var vClipAttributes $operationAttribute */
				foreach ($operationAttributes as $operationAttribute)
				{
					$vClipDescription = new vClipDescription();
					if (!$sourceEntryId)
					{
						//if no source entry we will not copy the entry ID. add clip offset to global offset and continue
						$globalOffset = $globalOffset + $operationAttribute->getDuration();
						continue;
					}
					$vClipDescription->setSourceEntryId($sourceEntryId);
					$vClipDescription->setStartTime($operationAttribute->getOffset() ? $operationAttribute->getOffset() : 0);
					$vClipDescription->setDuration($operationAttribute->getDuration() ? $operationAttribute->getDuration() : $sourceEntry->getLengthInMsecs());
					self::setCaptionGlobalOffset($operationAttribute, $globalOffset, $vClipDescription);
					$vClipDescriptionArray[] = $vClipDescription;
					//add clip offset to global offset
					$globalOffset = $globalOffset + $operationAttribute->getDuration();
				}
				$jobData->setClipsDescriptionArray($vClipDescriptionArray);
				$jobData->setFullCopy(false);
			}
		}

		$batchJob = new BatchJob();
		$batchJob->setEntryId($destEntry->getId());
		$batchJob->setPartnerId($destEntry->getPartnerId());

		vJobsManager::addJob($batchJob, $jobData, BatchJobType::COPY_CAPTIONS);
		return;
	}

	/**
	 * @param BaseObject entry to check
	 * @return vClipAttributes|null
	 */
	protected static function getClipAttributesFromEntry(BaseObject $object) {
		if ($object instanceof entry)
		{
			$operationAttributes = $object->getOperationAttributes();
			if (!is_null($operationAttributes) && count($operationAttributes) > 0)
			{
				$clipAttributes = reset($operationAttributes);
				if ($clipAttributes instanceof vClipAttributes)
					return $clipAttributes;
			}
		}
		return null;
	}

	/**
	 * @param BaseObject $object
	 * @return bool
	 */
	protected static function isClipConcatTrimFlow(BaseObject $object ) {
		if ( $object instanceof entry )
			return ($object->getFlowType() == EntryFlowType::TRIM_CONCAT);
		return false;
	}

	/**
	 * @param vClipAttributes $operationAttribute
	 * @param int $globalOffset
	 * @param vClipDescription $vClipDescription
	 */
	private static function setCaptionGlobalOffset($operationAttribute, $globalOffset, $vClipDescription)
	{
		if ($operationAttribute->getGlobalOffsetInDestination() || $operationAttribute->getGlobalOffsetInDestination() === 0) {
			$vClipDescription->setOffsetInDestination($operationAttribute->getGlobalOffsetInDestination());
		} else {
			$vClipDescription->setOffsetInDestination($globalOffset);
		}
	}

}


