<?php


/**
 * Skeleton subclass for performing query and update operations on the 'vuser_vgroup' table.
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
class VuserVgroupPeer extends BaseVuserVgroupPeer implements IRelatedObjectPeer
{
	private static $vgroupIdsByVuserId = array();

	/**
	 * Creates default criteria filter
	 */
	public static function setDefaultCriteriaFilter()
	{
		if(self::$s_criteria_filter == null)
			self::$s_criteria_filter = new criteriaFilter();

		$c =  VidiunCriteria::create(VuserVgroupPeer::OM_CLASS);
		$c->addAnd ( VuserVgroupPeer::STATUS, array(VuserVgroupStatus::DELETED), Criteria::NOT_IN);
		$partnerId = vCurrentContext::getCurrentPartnerId();
		if($partnerId)
			$c->addAnd ( VuserVgroupPeer::PARTNER_ID, $partnerId, Criteria::EQUAL );
		self::$s_criteria_filter->setFilter($c);
	}


	/**
	 * @param int $vuserId
	 * @param int $vgroupId
	 */
	static public function retrieveByVuserIdAndVgroupId ($vuserId, $vgroupId){

		$criteria = new Criteria();
		$criteria->add(VuserVgroupPeer::VUSER_ID, $vuserId);
		$criteria->add(VuserVgroupPeer::VGROUP_ID, $vgroupId);
		$criteria->add(VuserVgroupPeer::STATUS, VuserVgroupStatus::ACTIVE);

		return VuserVgroupPeer::doSelectOne($criteria);
	}

	/**
	 * delete all vuserVgroups that belong to vuserId
	 *
	 * @param int $vuserId
	 */
	public static function deleteByVuserId($vuserId){
		$vuserVgroups = self::retrieveByVuserIds(array($vuserId));
		foreach($vuserVgroups as $vuserVgroup) {
			/* @var $vuserVgroup VuserVgroup */
			$vuserVgroup->setStatus(VuserVgroupStatus::DELETED);
			$vuserVgroup->save();
		}
	}

	/**
	 * get vgroups by vusers
	 *
	 * @param array $vuserIds
	 * @return array
	 */
	public static function retrieveByVuserIds($vuserIds){
		$c = new Criteria();
		$c->add(VuserVgroupPeer::VUSER_ID, $vuserIds, Criteria::IN);
		return VuserVgroupPeer::doSelect($c);
	}

	/**
	 * @param array $vuserIds
	 * @return array
	 */
	public static function retrieveVgroupIdsByVuserIds($vuserIds){
		$vuserVgroups = self::retrieveByVuserIds($vuserIds);
		$vgroupIds = array();
		foreach ($vuserVgroups as $vuserVgroup){
			/* @var $vuserVgroup VuserVgroup */
			$vgroupIds[] = $vuserVgroup->getVgroupId();
		}
		return $vgroupIds;
	}

	/**
	 * @param int $vuserId
	 * @return array
	 */
	public static function retrieveVgroupIdsByVuserId($vuserId){
		if (isset(self::$vgroupIdsByVuserId[$vuserId])){
			return self::$vgroupIdsByVuserId[$vuserId];
		}

		self::$vgroupIdsByVuserId[$vuserId] = self::retrieveVgroupIdsByVuserIds(array($vuserId));

		return self::$vgroupIdsByVuserId[$vuserId];
	}

	/**
	 * @param $vuserId
	 * @param $partnerId
	 * @return array|mixed
	 */
	public static function retrieveVgroupByVuserIdAndPartnerId($vuserId, $partnerId)
	{
		//remove default criteria
		self::setUseCriteriaFilter(false);
		$c = new Criteria();
		$c->add(VuserVgroupPeer::VUSER_ID, array($vuserId), Criteria::IN);
		$c->addAnd ( VuserVgroupPeer::PARTNER_ID, $partnerId, Criteria::EQUAL );
		$c->addAnd ( VuserVgroupPeer::STATUS, array(VuserVgroupStatus::DELETED), Criteria::NOT_IN);

		$vuserVgroups = VuserVgroupPeer::doSelect($c);
		self::setUseCriteriaFilter(true);

		return $vuserVgroups;

	}

	/* (non-PHPdoc)
	 * @see IRelatedObjectPeer::getRootObjects()
	 */
	public function getRootObjects(IRelatedObject $object)
	{
		return array(
			vuserPeer::retrieveByPK($object->getVuserId()),
			vuserPeer::retrieveByPK($object->getVgroupId()),
		);
	}

	/* (non-PHPdoc)
	 * @see IRelatedObjectPeer::isReferenced()
	 */
	public function isReferenced(IRelatedObject $object)
	{
		return false;
	}

	public static function getCacheInvalidationKeys()
	{
		return array(array("vuserVgroup:vuserId=%s", self::VUSER_ID), array("vuserVgroup:vgroupId=%s", self::VGROUP_ID));		
	}

	/**
	 * @param int $vgroupId
	 */
	static public function retrieveVuserVgroupByVgroupId ($vgroupId)
	{

		$criteria = new Criteria();
		$criteria->add(VuserVgroupPeer::VGROUP_ID, $vgroupId);
		$criteria->add(VuserVgroupPeer::STATUS, VuserVgroupStatus::ACTIVE);

		return VuserVgroupPeer::doSelect($criteria);
	}
} // VuserVgroupPeer
