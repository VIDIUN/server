<?php
/**
 * @package plugins.document
 * @subpackage lib
 */
class VOperationEngineThumbAssetsGenerator extends VOperationEngineDocument
{
	const IMAGES_LIST_XML_NAME = 'imagesList.xml';
	const MAX_MULTI_REQUEST_INDEX = 20;

	private $realInFilePath;

	public function operate(vOperator $operator = null, $inFilePath, $configFilePath = null)
	{
		$this->realInFilePath = realpath($inFilePath);
		$this->generateThumbAssets($this->parseImagesListXML());

		if ( $this->data ) { //no output files to copy
			$this->data->destFileSyncLocalPath = null;
			$this->data->logFileSyncLocalPath = null;
		}

		return true;
	}

	private function parseImagesListXML(){
		$imagesList = array();
		$xmlPath = $this->realInFilePath . DIRECTORY_SEPARATOR . self::IMAGES_LIST_XML_NAME;
		$str = vEncryptFileUtils::getEncryptedFileContent($xmlPath, $this->encryptionKey, VBatchBase::getIV());
		$imagesXml = new SimpleXMLElement($str);
		foreach ($imagesXml->item as $item) {
			$imagesList[] = (string)$item->name;
		}

		return $imagesList;
	}

	private function generateThumbAssets($imagesList)
	{
		if ( !$imagesList || count($imagesList)==0 )
		{
			VidiunLog::info('no slides, cannot generate thumb cue points');
			return;
		}

		VBatchBase::impersonate($this->job->partnerId);
		$entry = VBatchBase::$vClient->baseEntry->get($this->job->entryId);
		VBatchBase::unimpersonate();
		if ( !$entry || !$entry->parentEntryId ) {
			VidiunLog::info('no parentEntryId, cannot generate thumb cue points');
			return;
		}

		VBatchBase::impersonate($this->job->partnerId);
		$imagesArray = array_chunk($imagesList, self::MAX_MULTI_REQUEST_INDEX);
		for ($j=0; $j < count($imagesArray); $j++)
		{
			$this->addThumbCuePoints($imagesArray[$j], $entry->parentEntryId,$j);
		}
		VBatchBase::unimpersonate();
	}

	private function addThumbCuePoints( array $images, $cpEntryId,$pageIndex=0)
	{
		VBatchBase::$vClient->startMultiRequest();
		$index = 0;
		$sortIndex = 0;
		foreach ($images as $image) {
			$thumbCuePoint = new VidiunThumbCuePoint();
			$thumbCuePoint->entryId = $cpEntryId;
			$thumbCuePoint->partnerSortValue = $pageIndex*self::MAX_MULTI_REQUEST_INDEX+$sortIndex;
			$sortIndex++;
			VBatchBase::$vClient->cuePoint->add( $thumbCuePoint ) ;
			$index++;

			$thumbAsset = new VidiunTimedThumbAsset();
			$thumbAsset->tags = $this->job->entryId;
			$thumbAsset->cuePointId = "{" . $index . ":result:id}";
			VBatchBase::$vClient->thumbAsset->add( $cpEntryId, $thumbAsset) ;
			$index++;
			
			$resource = $this->getServerFileResource($this->realInFilePath . DIRECTORY_SEPARATOR . $image, $this->encryptionKey);
			VBatchBase::$vClient->thumbAsset->setContent("{" . $index . ":result:id}", $resource);
			$index++;
		}
		VBatchBase::$vClient->doMultiRequest();
	}

	private static function getServerFileResource($path, $key)
	{
		$resource = new VidiunServerFileResource();
		if (!$key)
			$resource->localFilePath = $path;
		else
		{
			$resource->localFilePath = self::createClearCopyOnCurrentFolder($path, $key);
			$resource->keepOriginalFile = false;
		}
		return $resource;
	}
	
	private static function createClearCopyOnCurrentFolder($path, $key)
	{
		$tempPath = VBatchBase::createTempClearFile($path, $key);
		$clearPath = self::getClearPath($path);
		vFile::moveFile($tempPath, $clearPath);
		//maintain original group and owner to clear file
		vFile::copyFileMetadata($path, $clearPath);
		return $clearPath;
	}

	private static function getClearPath($path)
	{
		$typeLen = strlen(pathinfo($path, PATHINFO_EXTENSION)) + 1;
		$pos = strlen($path) - $typeLen;
		return substr($path, 0, $pos) . '_TEMP_CLEAR' . substr($path, $pos);

	}
}
