<?php


/**
 * Skeleton subclass for performing query and update operations on the 'cue_point' table.
 *
 *
 *
 * You should add additional methods to this class to meet the
 * application requirements.  This class will only be generated as
 * long as it does not already exist in the output directory.
 *
 * @package plugins.cuePoint
 * @subpackage model
 */
class CuePointPeer extends BaseCuePointPeer implements IMetadataPeer, IRelatedObjectPeer
{
	const MAX_TEXT_LENGTH = 32700;
	const MAX_TAGS_LENGTH = 255;

	// the search index column names for additional fields
	const ROOTS = 'cue_point.ROOTS';
	const STR_ENTRY_ID = 'cue_point.STR_ENTRY_ID';
	const STR_CUE_POINT_ID = 'cue_point.STR_CUE_POINT_ID';
	const FORCE_STOP = 'cue_point.FORCE_STOP';
	const DURATION = 'cue_point.DURATION';
	const IS_PUBLIC = 'cue_point.IS_PUBLIC';

	// cache classes by their type
	protected static $class_types_cache = array();

	private static $userContentOnly = false;

	public static function setUserContentOnly($contentOnly)
	{
		self::$userContentOnly = $contentOnly;
	}

	public static function getUserContentOnly()
	{
		return self::$userContentOnly;
	}

	/* (non-PHPdoc)
	 * @see BaseCuePointPeer::setDefaultCriteriaFilter()
	 */
	public static function setDefaultCriteriaFilter()
	{
		if (self::$s_criteria_filter == null)
			self::$s_criteria_filter = new criteriaFilter();

		$c = VidiunCriteria::create(CuePointPeer::OM_CLASS);
		$c->addAnd(CuePointPeer::STATUS, CuePointStatus::DELETED, Criteria::NOT_EQUAL);

		if (self::$userContentOnly)
		{
			$puserId = vCurrentContext::$vs_uid;
			$partnerId = vCurrentContext::$vs_partner_id;
			if ($puserId && $partnerId)
			{
				$vuser = vuserPeer::getVuserByPartnerAndUid($partnerId, $puserId);
				if (!$vuser)
					$vuser = vuserPeer::createVuserForPartner($partnerId, $puserId);

				// Temporarily change user filter to (user==vuser OR cuepoint of type THUMB/CODE). Long term fix will be accomplished
				// by adding a public property on the cuepoint object and checking (user==vuser OR is public)
//				$c->addAnd(CuePointPeer::VUSER_ID, $vuser->getId());
				$criteria = $c->getNewCriterion(self::VUSER_ID, $vuser->getId());
				$criteria->addOr($c->getNewCriterion(self::IS_PUBLIC, CuePoint::getIndexPrefix($partnerId).true, Criteria::EQUAL));
				$criteria->addTag(VidiunCriterion::TAG_USER_SESSION);
				$criteria->addOr(
					$c->getNewCriterion(
						CuePointPeer::TYPE,
						array(
							ThumbCuePointPlugin::getCuePointTypeCoreValue(ThumbCuePointType::THUMB),
							CodeCuePointPlugin::getCuePointTypeCoreValue(CodeCuePointType::CODE),
							AdCuePointPlugin::getCuePointTypeCoreValue(AdCuePointType::AD),
							),
						Criteria::IN
					)
				);
				$vs = vCurrentContext::$vs_object;
				if ($vs)
				{
					$values = $vs->getPrivilegeValues(vs::PRIVILEGE_LIST);
					if ($values && count($values) > 0)
						$criteria->addOr($c->getNewCriterion(CuePointPeer::ENTRY_ID, $values[0], Criteria::EQUAL));
				}

				$c->addAnd($criteria);
			}
			else if (!$puserId)
			{
				$criterionIsPublic = $c->getNewCriterion(self::IS_PUBLIC, CuePoint::getIndexPrefix($partnerId).true, Criteria::EQUAL);
				$criterionIsPublic->addTag(VidiunCriterion::TAG_WIDGET_SESSION);
				$c->add($criterionIsPublic);
			}
		}

		self::$s_criteria_filter->setFilter($c);
	}

	/* (non-PHPdoc)
	 * @see BaseCuePointPeer::getOMClass()
	 */
	public static function getOMClass($row, $colnum)
	{
		$assetType = null;
		if ($row)
		{
			$colnum += self::translateFieldName(self::TYPE, BasePeer::TYPE_COLNAME, BasePeer::TYPE_NUM);
			$assetType = $row[$colnum];
			if (isset(self::$class_types_cache[$assetType]))
				return self::$class_types_cache[$assetType];

			$extendedCls = VidiunPluginManager::getObjectClass(self::OM_CLASS, $assetType);
			if ($extendedCls)
			{
				self::$class_types_cache[$assetType] = $extendedCls;
				return $extendedCls;
			}
		}

		throw new Exception("Can't instantiate un-typed [$assetType] cue point [" . print_r($row, true) . "]");
	}

	/**
	 * Override in order to filter objects returned from doSelect.
	 *
	 * @param      array $selectResults The array of objects to filter.
	 * @param          Criteria $criteria
	 */
	public static function filterSelectResults(&$selectResults, Criteria $criteria)
	{
		if (!empty($selectResults) && self::$userContentOnly)
		{
			$vs = vCurrentContext::$vs_object;
			$privilagedEntryId = null;
			if ($vs)
			{
				$values = $vs->getPrivilegeValues(vs::PRIVILEGE_LIST);
				if ($values && count($values) > 0)
					$privilagedEntryId = $values[0];
			}

			$removedRecordsCount = 0;
			foreach ($selectResults as $key => $cuePoint)
			{
				/* @var $cuePoint CuePoint */
				if	(vCurrentContext::$vs_uid &&
					strtolower($cuePoint->getPuserId()) !== strtolower(vCurrentContext::$vs_uid) &&
					!$cuePoint->getIsPublic() &&
					$cuePoint->getEntryId() != $privilagedEntryId)
				{
					VidiunLog::warning("Filtering cuePoint select result with the following: [vs_uid -" . vCurrentContext::$vs_uid . "] [puserId - " . $cuePoint->getPuserId() . "] [isPublic - " . $cuePoint->getIsPublicStr() . "] [cuepointEntryId -  " . $cuePoint->getEntryId() . "] [privilagedEntryId - " . $privilagedEntryId . "] ");
					unset($selectResults[$key]);
					$removedRecordsCount++;
				}
			}

			if ($criteria instanceof VidiunCriteria)
			{
				$recordsCount = $criteria->getRecordsCount();
				$criteria->setRecordsCount($recordsCount - $removedRecordsCount);
			}
		}

		parent::filterSelectResults($selectResults, $criteria);
	}

	public static function retrieveByPK($pk, PropelPDO $con = null)
	{
		VidiunCriterion::disableTags(array(VidiunCriterion::TAG_USER_SESSION, VidiunCriterion::TAG_WIDGET_SESSION));
		$res = parent::retrieveByPK($pk, $con);
		VidiunCriterion::restoreTags(array(VidiunCriterion::TAG_USER_SESSION, VidiunCriterion::TAG_WIDGET_SESSION));

		return $res;
	}

	/* (non-PHPdoc)
	 * @see BaseCuePointPeer::doSelect()
	 */
	public static function doSelect(Criteria $criteria, PropelPDO $con = null)
	{
		$c = clone $criteria;

		if ($c instanceof VidiunCriteria)
		{
			$c->applyFilters();
			$criteria->setRecordsCount($c->getRecordsCount());
		}

		return parent::doSelect($c, $con);
	}

	/**
	 * Retrieve a single object by system name.
	 * The cue point system name is unique per entry
	 *
	 * @param      string $entryId the entry id.
	 * @param      string $systemName the system name.
	 * @param      PropelPDO $con the connection to use
	 * @return     CuePoint
	 */
	public static function retrieveBySystemName($entryId, $systemName, PropelPDO $con = null)
	{
		$criteria = VidiunCriteria::create(CuePointPeer::OM_CLASS);
		$criteria->add(CuePointPeer::ENTRY_ID, $entryId);
		$criteria->add(CuePointPeer::SYSTEM_NAME, $systemName);

		return CuePointPeer::doSelectOne($criteria, $con);
	}

	/**
	 * Retrieve multiple objects by entry id.
	 *
	 * @param      string $entryId the entry id.
	 * @param      array $types the cue point types from CuePointType enum
	 * @param      PropelPDO $con the connection to use
	 * @return     CuePoint
	 */
	public static function retrieveByEntryId($entryId, $types = null, PropelPDO $con = null)
	{
		$criteria = VidiunCriteria::create(CuePointPeer::OM_CLASS);
		$criteria->add(CuePointPeer::ENTRY_ID, $entryId);
		$criteria->add(CuePointPeer::STATUS, CuePointStatus::DELETED, Criteria::NOT_EQUAL);

		if (!is_null($types))
			$criteria->add(CuePointPeer::TYPE, $types, Criteria::IN);
		$criteria->addAscendingOrderByColumn(CuePointPeer::CREATED_AT);
		$criteria->addAscendingOrderByColumn(CuePointPeer::START_TIME);
		return CuePointPeer::doSelect($criteria, $con);
	}
	
	/**
	 * @param 	string 		$entryId		the entry id.
	 * @param 	array 		$types			the cue point types from CuePointType enum
	 * @param	PropelPDO 	$con	 		the connection to use
	 * @return 	array<CuePoint>
	 */
	public static function countByEntryIdAndTypes($entryId, array $types = null, PropelPDO $con = null)
	{
		$criteria = VidiunCriteria::create(CuePointPeer::OM_CLASS);
		$criteria->add(CuePointPeer::ENTRY_ID, $entryId);
		$criteria->add(CuePointPeer::STATUS, CuePointStatus::DELETED, Criteria::NOT_EQUAL);
		
		if(count($types))
			$criteria->add(CuePointPeer::TYPE, $types, Criteria::IN);
	
		return CuePointPeer::doCount($criteria);
	}

	/**
	 * @param 	string 		$entryId		the entry id.
	 * @param	PropelPDO 	$con	 		the connection to use
	 * @return 	boolean
	 */
	public static function hasReadyCuePointOnEntry($entryId, PropelPDO $con = null)
	{
		$criteria = VidiunCriteria::create(CuePointPeer::OM_CLASS);
		$criteria->add( CuePointPeer::ENTRY_ID, $entryId );
		$criteria->add( CuePointPeer::STATUS, CuePointStatus::READY ); // READY, but not yet HANDLED
		$cuePoint = CuePointPeer::doSelectOne($criteria, $con);
		if ($cuePoint)
			return true;
		return false;
	}

	/**
	 * Retrieve multiple objects by entry id.
	 *
	 * @param	string 		$entryId 		the entry id.
	 * @param	int 		$limit		 	select limit amount
	 * @param	int			$offest			the offset to fetch from
	 * @param	array 		$types 			the cue point types from CuePointType enum
	 * @param	PropelPDO 	$con	 		the connection to use
	 * @return	CuePoints
	 */
	public static function retrieveByEntryIdTypeAndLimit($partnerId, $entryId, $limit, $offset, $types = array(), PropelPDO $con = null)
	{
		$criteria = VidiunCriteria::create(CuePointPeer::OM_CLASS);
		$criteria->add(CuePointPeer::ENTRY_ID, $entryId);
		$criteria->add(CuePointPeer::PARTNER_ID, $partnerId);
		$criteria->add(CuePointPeer::STATUS, CuePointStatus::DELETED, Criteria::NOT_EQUAL);
		$criteria->setLimit($limit);
		$criteria->setOffset($offset);
		$criteria->dontCount();

		if(count($types))
			$criteria->add(CuePointPeer::TYPE, $types, Criteria::IN);
		
		$criteria->addDescendingOrderByColumn(CuePointPeer::UPDATED_AT);
		return CuePointPeer::doSelect($criteria, $con);
	}
	
	public static function getCacheInvalidationKeys()
	{
		return array(array("cuePoint:id=%s", self::ID), array("cuePoint:entryId=%s", self::ENTRY_ID));
	}

	/**
	 * Retrieve a single object by pkey.
	 *
	 * @param      string $pk the primary key.
	 * @param      PropelPDO $con the connection to use
	 * @return     CuePoint
	 */
	public static function retrieveByPKNoFilter($pk, PropelPDO $con = null)
	{
		self::setUseCriteriaFilter(false);
		$res = self::retrieveByPK($pk, $con);
		self::setUseCriteriaFilter(true);
		return $res;
	}

	public static function validateMetadataObjects($profileField, $objectIds, &$errorMessage)
	{
		return true;
	}

	public static function getEntry($objectId)
	{
		$cuePoint = self::retrieveByPK($objectId);
		if (!$cuePoint)
			return null;
		return $cuePoint ? entryPeer::retrieveByPK($cuePoint->getEntryId()) : null;
	}

	/* (non-PHPdoc)
	 * @see IRelatedObjectPeer::getRootObjects()
	 */
	public function getRootObjects(IRelatedObject $object)
	{
		/* @var $object CuePoint */
		$entry = entryPeer::retrieveByPK($object->getEntryId());
		if ($entry)
			return array($entry);

		return array();
	}

	/* (non-PHPdoc)
	 * @see IRelatedObjectPeer::isReferenced()
	 */
	public function isReferenced(IRelatedObject $object)
	{
		return false;
	}
	
	public static function validateMetadataObjectAccess($objectId)
	{
		$cuePointDb = self::retrieveByPK($objectId);
		if(!$cuePointDb)
		{
			VidiunLog::debug("Metadata object id with id [$objectId] not found");
			return false;
		}
		
		/* @var $cuePointDb CuePoint */
		//check if we have a limitEntry set on the VS, and if so verify that it is the same entry we work on
		$limitEntry = vCurrentContext::$vs_object->getLimitEntry();
		if ($limitEntry && $limitEntry != $cuePointDb->getEntryId())
		{
			throw new VidiunAPIException(VidiunCuePointErrors::NO_PERMISSION_ON_ENTRY, $cuePointDb->getEntryId());
		}
	
		return true;
	}
}
