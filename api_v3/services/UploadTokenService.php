<?php

/**
 *
 * @service uploadToken
 * @package api
 * @subpackage services
 */
class UploadTokenService extends VidiunBaseService
{
	public function initService($serviceId, $serviceName, $actionName)
	{
		parent::initService($serviceId, $serviceName, $actionName);
		$this->applyPartnerFilterForClass('UploadToken');
	}
	
	/**
	 * Adds new upload token to upload a file
	 * 
	 * @action add
	 * @param VidiunUploadToken $uploadToken
	 * @return VidiunUploadToken
	 */
	function addAction(VidiunUploadToken $uploadToken = null)
	{
		if (is_null($uploadToken))
			$uploadToken = new VidiunUploadToken();
			
		// prepare the db object
		$uploadTokenDb = new UploadToken();
		
		// validate
		$uploadToken->toInsertableObject($uploadTokenDb);
		
		// set additional properties
		$uploadTokenDb->setPartnerId($this->getPartnerId());
		$uploadTokenDb->setVuserId($this->getVuser()->getId());
		
		// use the upload token manager to add the token
		$uploadTokenMgr = new vUploadTokenMgr($uploadTokenDb);
		$uploadTokenMgr->saveAsNewUploadToken();
		$uploadTokenDb = $uploadTokenMgr->getUploadToken();
		$uploadToken->fromObject($uploadTokenDb, $this->getResponseProfile());
		return $uploadToken;
	}
	
	/**
	 * Get upload token by id
	 * 
	 * @action get
	 * @param string $uploadTokenId
	 * @return VidiunUploadToken
	 */
	function getAction($uploadTokenId)
	{
		$this->restrictPeerToCurrentUser();
		$uploadTokenDb = UploadTokenPeer::retrieveByPK($uploadTokenId);
		if (is_null($uploadTokenDb))
			throw new VidiunAPIException(VidiunErrors::UPLOAD_TOKEN_NOT_FOUND);
			
		$uploadToken = new VidiunUploadToken();
		$uploadToken->fromObject($uploadTokenDb, $this->getResponseProfile());
		return $uploadToken;
	}
	
	/**
	 * Upload a file using the upload token id, returns an error on failure (an exception will be thrown when using one of the Vidiun clients)
	 * Chunks can be uploaded in parallel and they will be appended according to their resumeAt position.
	 * 
	 * A parallel upload session should have three stages:
	 * 1. A single upload with resume=false and finalChunk=false
	 * 2. Parallel upload requests each with resume=true,finalChunk=false and the expected resumetAt position.
	 *    If a chunk fails to upload it can be re-uploaded.
	 * 3. After all of the chunks have been uploaded a final chunk (can be of zero size) should be uploaded 
	 *    with resume=true, finalChunk=true and the expected resumeAt position. In case an UPLOAD_TOKEN_CANNOT_MATCH_EXPECTED_SIZE exception
	 *    has been returned (indicating not all of the chunks were appended yet) the final request can be retried.     
	 * 
	 * @action upload
	 * @param string $uploadTokenId
	 * @param file $fileData
	 * @param bool $resume
	 * @param bool $finalChunk
	 * @param float $resumeAt
	 * @return VidiunUploadToken
	 */
	function uploadAction($uploadTokenId, $fileData, $resume = false, $finalChunk = true, $resumeAt = -1)
	{
		$this->restrictPeerToCurrentUser();
		$uploadTokenDb = UploadTokenPeer::retrieveByPK($uploadTokenId);
		if (is_null($uploadTokenDb))
			throw new VidiunAPIException(VidiunErrors::UPLOAD_TOKEN_NOT_FOUND);

		// dont dump an upload to the other DC if it's the first uploaded chunk
		// optimizes cases where an upload token is created in one DC and the uploads go to the other
		if ($uploadTokenDb->getStatus() != UploadToken::UPLOAD_TOKEN_PENDING)
		{
			// if the token was already used for upload on another datacenter, proxy the upload action there
			$remoteDCHost = vUploadTokenMgr::getRemoteHostForUploadToken($uploadTokenId, vDataCenterMgr::getCurrentDcId());
			if($remoteDCHost)
			{
				vFileUtils::dumpApiRequest($remoteDCHost);
			}
		}

		$uploadTokenMgr = new vUploadTokenMgr($uploadTokenDb, $finalChunk);
		try
		{
			$uploadTokenMgr->uploadFileToToken($fileData, $resume, $resumeAt);
		}
		catch(vUploadTokenException $ex)
		{
			switch($ex->getCode())
			{
				case vUploadTokenException::UPLOAD_TOKEN_INVALID_STATUS:
					throw new VidiunAPIException(VidiunErrors::UPLOAD_TOKEN_INVALID_STATUS_FOR_UPLOAD);
				case vUploadTokenException::UPLOAD_TOKEN_FILE_NAME_IS_MISSING_FOR_UPLOADED_FILE:
				case vUploadTokenException::UPLOAD_TOKEN_UPLOAD_ERROR_OCCURRED:
				case vUploadTokenException::UPLOAD_TOKEN_FILE_IS_NOT_VALID:
				 	throw new VidiunAPIException(VidiunErrors::UPLOAD_ERROR);
				case vUploadTokenException::UPLOAD_TOKEN_FILE_NOT_FOUND_FOR_RESUME:
					throw new VidiunAPIException(VidiunErrors::UPLOAD_TOKEN_CANNOT_RESUME);
				case vUploadTokenException::UPLOAD_TOKEN_CANNOT_MATCH_EXPECTED_SIZE:
					throw new VidiunAPIException(VidiunErrors::UPLOAD_TOKEN_CANNOT_MATCH_EXPECTED_SIZE);
				case vUploadTokenException::UPLOAD_TOKEN_FILE_TYPE_RESTRICTED:
					throw new VidiunAPIException(VidiunErrors::UPLOAD_TOKEN_FILE_TYPE_RESTRICTED_FOR_UPLOAD);
				default:
					throw $ex;
			}
		}
		$uploadToken = new VidiunUploadToken();
		$uploadToken->fromObject($uploadTokenDb, $this->getResponseProfile());
		return $uploadToken;
	}

	/**
	 * Deletes the upload token by upload token id
	 * 
	 * @action delete
	 * @param string $uploadTokenId
	 */
	function deleteAction($uploadTokenId)
	{
		$this->restrictPeerToCurrentUser();
		$uploadTokenDb = UploadTokenPeer::retrieveByPK($uploadTokenId);
		if (is_null($uploadTokenDb))
			throw new VidiunAPIException(VidiunErrors::UPLOAD_TOKEN_NOT_FOUND);
			
		$uploadTokenMgr = new vUploadTokenMgr($uploadTokenDb);
		try
		{
			$uploadTokenMgr->deleteUploadToken();
		}
		catch(vCoreException $ex)
		{
			throw $ex;
		}
	}
	
	/**
	 * List upload token by filter with pager support. 
	 * When using a user session the service will be restricted to users objects only.
	 * 
	 * @action list
	 * @param VidiunUploadTokenFilter $filter
	 * @param VidiunFilterPager $pager
	 * @return VidiunUploadTokenListResponse
	 */
	function listAction(VidiunUploadTokenFilter $filter = null, VidiunFilterPager $pager = null)
	{
		if (!$filter)
			$filter = new VidiunUploadTokenFilter();
			
		if (!$pager)
			$pager = new VidiunFilterPager();
		
		$this->restrictPeerToCurrentUser();
			
		// translate the user id (puser id) to vuser id
		if ($filter->userIdEqual !== null)
		{
			$vuser = vuserPeer::getVuserByPartnerAndUid($this->getPartnerId(), $filter->userIdEqual);
			if ($vuser)
				$filter->userIdEqual = $vuser->getId();
			else 
				$filter->userIdEqual = -1; // no result will be returned when the user is missing
		}

                // in case a filename filter was passed enforce a statusIn filter in order to limit slow db queries
                if ($filter->fileNameEqual && $filter->statusEqual == null && $filter->statusIn == null)
                        $filter->statusIn = implode(",", array(VidiunUploadTokenStatus::PENDING, VidiunUploadTokenStatus::PARTIAL_UPLOAD, VidiunUploadTokenStatus::FULL_UPLOAD));
 
		// create the filter
		$uploadTokenFilter = new UploadTokenFilter();
		$filter->toObject($uploadTokenFilter);
		$c = new Criteria();
		$uploadTokenFilter->attachToCriteria($c);
		$totalCount = UploadTokenPeer::doCount($c);
		$pager->attachToCriteria($c);
		
		$list = UploadTokenPeer::doSelect($c);
		
		// create the response object
		$newList = VidiunUploadTokenArray::fromDbArray($list, $this->getResponseProfile());
		$response = new VidiunUploadTokenListResponse();
		$response->objects = $newList;
		$response->totalCount = $totalCount;
		return $response;
	}
	
	/**
	 * When using user session, restrict the peer to users tokens only
	 */
	protected function restrictPeerToCurrentUser()
	{
		if (!$this->getVs() || !$this->getVs()->isAdmin())
		{
			UploadTokenPeer::getCriteriaFilter()->getFilter()->addAnd(UploadTokenPeer::VUSER_ID, $this->getVuser()->getId());
		}
	}
}
