<?php


/**
 * Skeleton subclass for performing query and update operations on the 'invalid_session' table.
 *
 * 
 *
 * You should add additional methods to this class to meet the
 * application requirements.  This class will only be generated as
 * long as it does not already exist in the output directory.
 *
 * @package Core
 * @subpackage model
 */
class invalidSessionPeer extends BaseinvalidSessionPeer {
	
	/**
	 * @param      vs $vs
	 * @param	   int $limit
	 * @return     invalidSession
	 */
	public static function actionsLimitVs(vs $vs, $limit)
	{
		$invalidSession = new invalidSession();
		$invalidSession->setVs($vs->getHash());
		$invalidSession->setActionsLimit($limit);
		$invalidSession->setVsValidUntil($vs->valid_until);
		$invalidSession->setType(invalidSession::INVALID_SESSION_TYPE_VS);
		$invalidSession->save();
		
		return $invalidSession;
	}
	
	/**
	 * @param      vs $vs
	 * @return     invalidSession
	 */
	public static function invalidateVs(vs $vs, PropelPDO $con = null)
	{
		$result = self::invalidateByKey($vs->getHash(), invalidSession::INVALID_SESSION_TYPE_VS, $vs->valid_until, $con);
		$sessionId = $vs->getSessionIdHash();
		if($sessionId) {
			self::invalidateByKey($sessionId, invalidSession::INVALID_SESSION_TYPE_SESSION_ID, time() + (24 * 60 * 60), $con);
		}
		
		return $result;
	}
	
	public static function invalidateByKey($key, $type, $validUntil, PropelPDO $con = null) {
		$criteria = new Criteria();
		$criteria->add(invalidSessionPeer::VS, $key);
		$criteria->add(invalidSessionPeer::TYPE, $type);
		$invalidSession = invalidSessionPeer::doSelectOne($criteria, $con);
		
		if(!$invalidSession){
			$invalidSession = new invalidSession();
			$invalidSession->setVs($key);
			$invalidSession->setType($type);
			$invalidSession->setVsValidUntil($validUntil);
		}
		
		$invalidSession->setActionsLimit(null);
		$invalidSession->save();
		
		return $invalidSession;
	}
	
	public static function getCacheInvalidationKeys()
	{
		return array(array("invalidSession:vs=%s", self::VS));		
	}
	
} // invalidSessionPeer
