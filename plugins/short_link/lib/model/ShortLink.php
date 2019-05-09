<?php


/**
 * Skeleton subclass for representing a row from the 'short_link' table.
 *
 * 
 *
 * You should add additional methods to this class to meet the
 * application requirements.  This class will only be generated as
 * long as it does not already exist in the output directory.
 *
 * @package plugins.shortLink
 * @subpackage model
 */
class ShortLink extends BaseShortLink implements IBaseObject {

	protected $puserId;
	
	/**
	 * @return string $puserId
	 */
	public function getPuserId()
	{
		if(!$this->puserId)
		{
			if(!$this->getVuserId())
				return null;
				
			$vuser = vuserPeer::retrieveByPK($this->getVuserId());
			if(!$vuser)
				return null;
				
			$this->puserId = $vuser->getPuserId();
		}
		
		return $this->puserId;
	}

	/**
	 * Set the puser id and the vuser id
	 * If the vuser doesn't exist it will be created
	 * @param string $puserId
	 */
	public function setPuserId($puserId)
	{
		if(!$this->getPartnerId())
			throw new Exception("Partner id must be set in order to load puser [$puserId]");
			
		$this->puserId = $puserId;
		$vuser = vuserPeer::getVuserByPartnerAndUid($this->getPartnerId(), $puserId, true);
		if(!$vuser)
		{
			$isAdmin = false;
//			if($puserId == vCurrentContext::$uid)
//				$isAdmin = vCurrentContext::$is_admin_session;
				
			$vuser = vuserPeer::createVuserForPartner($this->getPartnerId(), $puserId, $isAdmin);
		}
		$this->setVuserId($vuser->getId());
	}

	/* (non-PHPdoc)
	 * @see BaseShortLink::postUpdate()
	 */
	public function postUpdate(PropelPDO $con = null)
	{
		if ($this->alreadyInSave)
			return parent::postUpdate($con);
		
		$objectDeleted = false;
		if($this->isColumnModified(ShortLinkPeer::STATUS) && $this->getStatus() == ShortLinkStatus::DELETED)
			$objectDeleted = true;
			
		$ret = parent::postUpdate($con);
		
		if ($objectDeleted)
			vEventsManager::raiseEvent(new vObjectDeletedEvent($this));
			
		return $ret;
	}

	protected function calculateId()
	{
		$allChars = '0123456789abcdefghijklmnopqrstuvwxyz';
		$dcChars = str_split($allChars, strlen($allChars) / count(vDataCenterMgr::getAllDcs(true)));
		
		$dc = vDataCenterMgr::getCurrentDc();
		$dcId = (int) $dc["id"];
		$currentDcChars = $dcChars[$dcId];
		
		for ($i = 0; $i < 10; $i++)
		{
			$dcChar = substr($currentDcChars, rand(0, strlen($currentDcChars) - 1), 1);
			if(!$dcChar)
				$dcChar = '0';
				
			$id = $dcChar . vString::generateStringId(4);
			ShortLinkPeer::setUseCriteriaFilter(false);
			$existingObject = ShortLinkPeer::retrieveByPK($id);
			ShortLinkPeer::setUseCriteriaFilter(true);
			
			if ($existingObject)
				VidiunLog::log("id [$id] already exists");
			else
				return $id;
		}
		
		throw new Exception("Could not find unique id for short link");
	}

	public function save(PropelPDO $con = null, $skipReload = false)
	{
		if ($this->isNew())
			$this->setId($this->calculateId());
			
		parent::save($con, $skipReload);
	}
		
} // ShortLink
