<?php

/**
 * Subclass for representing a row from the 'file_sync' table.
 *
 * 
 *
 * @package Core
 * @subpackage model
 */ 
class FileSync extends BaseFileSync implements IBaseObject
{
	const FILE_SYNC_FILE_TYPE_FILE = 1;
	const FILE_SYNC_FILE_TYPE_LINK = 2;
	const FILE_SYNC_FILE_TYPE_URL = 3;
	const FILE_SYNC_FILE_TYPE_CACHE = 4;
	
	const FILE_SYNC_STATUS_ERROR = -1;
	const FILE_SYNC_STATUS_PENDING = 1;
	const FILE_SYNC_STATUS_READY = 2;
	const FILE_SYNC_STATUS_DELETED = 3;
	const FILE_SYNC_STATUS_PURGED = 4;
	
	private $statusMap = array (
		self::FILE_SYNC_STATUS_ERROR => "Error",
		self::FILE_SYNC_STATUS_PENDING => "Pending", 
		self::FILE_SYNC_STATUS_READY => "Ready",
		self::FILE_SYNC_STATUS_DELETED => "Deleted"
	);
	/**
	 * 
	 * @param FileSyncKey $key
	 * @return FileSync
	 */
	public static function createForFileSyncKey ( FileSyncKey $key )
	{
		$file_sync = new FileSync();
		$file_sync->setObjectId ( $key->object_id );
		$file_sync->setObjectType ( $key->object_type );
		$file_sync->setObjectSubType ( $key->object_sub_type );
		$file_sync->setVersion ( $key->version );
		if ( $key->partner_id ) $file_sync->setPartnerId ( $key->partner_id );
		return $file_sync;
	}

	private function generateKey()
	{
		return implode("_", array($this->getObjectId(), $this->getObjectType(), $this->getObjectSubType()));
	}

	public function encrypt()
	{
		if (!$this->shouldEncryptFile())
			return;

		$this->setEncryptionKey($this->generateKey());
		$this->save();

		$key = $this->getEncryptionKey();
		$realPath = realpath($this->getFullPath());
		VidiunLog::debug("Encrypting content of fileSync " . $this->id . ". key is: [$key] in path [$realPath]");
		vEncryptFileUtils::encrypt($realPath, $key, $this->getIv());
	}

	public function decrypt()
	{
		$realPath = realpath($this->getFullPath());
		$fileData = vFileBase::getFileContent( $realPath);
		if (!$this->isEncrypted()) 
		{
			VidiunLog::info("File of fileSyncId [$this->id] in path $realPath is not encrypted");
			return $fileData;
		}

		$key = $this->getEncryptionKey();
		VidiunLog::debug("Decrypting content of fileSync " . $this->id . ". key is: [$key]");
		$plainData = vEncryptFileUtils::decryptData($fileData, $key, $this->getIv());
		return $plainData;
	}

	public function shouldEncryptFile()
	{

		//check for partner configuration
		if(!$this->getPartnerId() || !PermissionPeer::isValidForPartner(PermissionName::FEATURE_CONTENT_ENCRYPTION, $this->getPartnerId()))
			return false;

		//check fileSync type
		$excludeObjectTypes = array(FileSyncObjectType::BATCHJOB);
		if (in_array($this->object_type, $excludeObjectTypes))
			return false;

		$fileTypeNotToEncrypt = array('log');
		if (in_array($this->getFileExt(), $fileTypeNotToEncrypt))
			return false;

		if ($this->object_type == FileSyncObjectType::ASSET)
		{
			/** @var  Asset $asset */
			$asset = assetPeer::retrieveById($this->object_id);
			if(!$asset)
			{
				VidiunLog::debug("Asset id [" . $this->object_id . "] not found");
				return false;
			}

			$shouldEncrypt = $asset->shouldEncrypt();
			VidiunLog::debug("Asset id [$this->object_id] of type [" . $asset->getType() . "] should be encrypt: [$shouldEncrypt]");
			if (!$shouldEncrypt)
				return false;
		}

		if ($this->getEncryptionKey())
		{
			VidiunLog::info("File of fileSyncId [$this->id] already has key and should not be encrypt again");
			return false;
		}
		return true;
	}
	

	public function setFileSizeFromPath ($filePath)
	{
		$fileSize = vEncryptFileUtils::fileSize($filePath, $this->getEncryptionKey(), $this->getIv());
		$this->setFileSize($fileSize);
	}

	private function getClearTempPath()
	{
		$type = pathinfo($this->getFilePath(), PATHINFO_EXTENSION);
		return sys_get_temp_dir(). "/". $this->getEncryptionKey() . $this->getVersion() . ".$type";
	}
	
	public function getFullPath ()
	{
		return $this->getFileRoot() . $this->getFilePath();
	}

	/**
	 * Its the caller responsibility to remove the file after usage
	 * @return string path to a temporary decrypted file
	 */
	public function createTempClear()
	{
		$realPath = realpath($this->getFullPath());
		$tempPath = $this->getClearTempPath();
		VidiunLog::info("Creating new file for syncId [$this->id] on [$tempPath]");
		if (!file_exists($tempPath))
			vEncryptFileUtils::decryptFile($realPath, $this->getEncryptionKey(), $this->getIv(), $tempPath);
		return $tempPath;
	}

	public function deleteTempClear()
	{
		$tempPath = $this->getClearTempPath();
		if (file_exists($tempPath))
			unlink($tempPath);
	}

	
	public function getFileExt()
	{
		return pathinfo($this->getFullPath(), PATHINFO_EXTENSION);
	}
	
	public function getStatusAsString()
	{
		return (isset($this->statusMap[$this->getStatus()])) ? $this->statusMap[$this->getStatus()] : "Unknown";
	}
	
	public function getExternalUrl($entryId, $format = PlaybackProtocol::HTTP)
	{
		$storage = StorageProfilePeer::retrieveByPK($this->getDc());
		if(!$storage || $storage->getProtocol() == StorageProfile::STORAGE_VIDIUN_DC)
			return vDataCenterMgr::getInternalRemoteUrl($this);

		$urlManager = DeliveryProfilePeer::getRemoteDeliveryByStorageId(DeliveryProfileDynamicAttributes::init($this->getDc(), $entryId, PlaybackProtocol::HTTP, infraRequestUtils::getProtocol()));
		if(is_null($urlManager) && infraRequestUtils::getProtocol() != 'http')
			$urlManager = DeliveryProfilePeer::getRemoteDeliveryByStorageId(DeliveryProfileDynamicAttributes::init($this->getDc(), $entryId));
		if(is_null($urlManager))
			return null;
		
		$url = $urlManager->getFileSyncUrl($this);
		$baseUrl = $urlManager->getUrl();
		
		$url = ltrim($url, "/");
		if (strpos($url, "://") === false){
			$url = rtrim($baseUrl, "/") . "/".$url ;
		}
		return $url;
	}
	
	/* (non-PHPdoc)
	 * @see BaseFileSync::preUpdate()
	 */
	public function preUpdate(PropelPDO $con = null)
	{
		if($this->isColumnModified(FileSyncPeer::STATUS) 
			&& in_array($this->getStatus(), array(self::FILE_SYNC_STATUS_DELETED, self::FILE_SYNC_STATUS_PURGED)))
		{
			$this->setDeletedId($this->getId());
		}
					
		return parent::preUpdate($con);
	 }

	/* (non-PHPdoc)
	 * @see lib/model/om/BaseFileSync#postUpdate()
	 */
	public function postUpdate(PropelPDO $con = null)
	{
		if ($this->alreadyInSave)
			return parent::postUpdate($con);
		
		$objectDeleted = false;
		if($this->isColumnModified(FileSyncPeer::STATUS) && $this->getStatus() == self::FILE_SYNC_STATUS_DELETED)
			$objectDeleted = true;
			
		$ret = parent::postUpdate($con);
		
		if($objectDeleted)
			vEventsManager::raiseEvent(new vObjectDeletedEvent($this));
			
		return $ret;
	}

	public function getCacheInvalidationKeys()
	{
		return array("fileSync:id=".strtolower($this->getId()), "fileSync:objectId=".strtolower($this->getObjectId()));
	}
	
	/* (non-PHPdoc)
	 * @see BaseFileSync::setStatus()
	 */
	public function setStatus($v)
	{
		if($v == FileSync::FILE_SYNC_STATUS_READY || $v == FileSync::FILE_SYNC_STATUS_ERROR)
			$this->setReadyAt(time());
			
		if ($v == FileSync::FILE_SYNC_STATUS_READY)
		{
			// no longer need these, unset them to reduce table size
			$this->unsetOriginalId();
			$this->unsetOriginalDc();
		}
		
		return parent::setStatus($v);
	}
	
	public function getIsDir() { return $this->getFromCustomData("isDir"); }
	public function setIsDir($v) { $this->putInCustomData("isDir", $v); }
	
	public function getOriginalId() { return $this->getFromCustomData("originalId"); }
	public function setOriginalId($v) { $this->putInCustomData("originalId", $v); }
	public function unsetOriginalId() { $this->removeFromCustomData("originalId"); }

	public function getOriginalDc() { return $this->getFromCustomData("originalDc"); }
	public function setOriginalDc($v) { $this->putInCustomData("originalDc", $v); }
	public function unsetOriginalDc() { $this->removeFromCustomData("originalDc"); }
	
	public function getContentMd5 () { return $this->getFromCustomData("contentMd5"); }
	public function setContentMd5 ($v) { $this->putInCustomData("contentMd5", $v);  }

	public function getEncryptionKey () { return $this->getFromCustomData("encryptionKey"); }
	public function setEncryptionKey ($v) { $this->putInCustomData("encryptionKey", $v);  }
	public function isEncrypted () { return ($this->getFromCustomData("encryptionKey"))? true : false ; }
	public function getIv() {return vConf::get("encryption_iv");}


}


class FileSyncKey 
{
	public $object_type;
	public $object_id;
	public $version;
	public $object_sub_type;
	public $partner_id;		// this field is not needed for the key, but it is actually derived from the 4 other fields 
	
	/**
	 * 
	 * @return string
	 */
	public function __toString()
	{
		$str = "";
		$str .= "object_type:[{$this->object_type}], ";
		$str .= "object_id:[{$this->object_id}], ";
		$str .= "version:[{$this->version}], ";
		$str .= "object_sub_type[{$this->object_sub_type}], ";
		$str .= "partner_id[{$this->partner_id}]";
		return $str;
	}
	
	public function getObjectType() { return $this->object_type; }
	public function getObjectId() { return $this->object_id; }
	public function getVersion() { return $this->version; }
	public function getObjectSubType() { return $this->object_sub_type; }
	public function getPartnerId() { return $this->partner_id; }
	
	public function setObjectType($v) { $this->object_type = $v; }
	public function setObjectId($v) { $this->object_id = $v; }
	public function setVersion($v) { $this->version = $v; }
	public function setObjectSubType($v) { $this->object_sub_type = $v; }
	public function setPartnerId($v) { $this->partner_id = $v; }
}

class FileSyncException extends Exception
{
	public function __construct ( $type , $sub_type , $allowed_sub_types )
	{
		parent::__construct( "For FileSync type [$type], unknown sub_type [$sub_type]" );
	}
}
