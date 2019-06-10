<?php
/**
 * @package Core
 * @subpackage model.data
 */
class vUploadedFileTokenResource extends vLocalFileResource
{
	/**
	 * Token that returned from upload.upload action or uploadToken.add action. 
	 * @var string
	 */
	private $token;
	
	public function getType()
	{
		return 'vLocalFileResource';
	}
	
	/* (non-PHPdoc)
	 * @see vLocalFileResource::attachCreatedObject()
	 */
	public function attachCreatedObject(BaseObject $object)
	{
		$dbUploadToken = UploadTokenPeer::retrieveByPK($this->token);
		if(is_null($dbUploadToken))
			return;
		
		$dbUploadToken->setObjectType(get_class($object));
		$dbUploadToken->setObjectId($object->getId());
		$dbUploadToken->save();
	}

	/**
	 * @param string $token
	 */
	public function setToken($token)
	{
		$this->token = $token;
	}
	
	public function getMediaType()
	{
		$dbUploadToken = UploadTokenPeer::retrieveByPK($this->token);
		if(is_null($dbUploadToken))
			return null;
		
		$fileName = $dbUploadToken->getFileName();
		if(!$fileName)
			return null;
		
		return myFileUploadService::getMediaTypeFromFileExt(pathinfo($fileName, PATHINFO_EXTENSION));
	}
}