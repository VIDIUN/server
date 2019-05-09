<?php

/**
 * Subclass for representing a row from the 'vuser' table.
 *
 * 
 *
 * @package Core
 * @subpackage model
 */ 
class vuser extends Basevuser implements IIndexable, IRelatedObject, IElasticIndexable
{
	public function __construct()
	{
		$this->roleIds = null;
		$this->roleIdsChanged = false;
	}
	
	const BULK_UPLOAD_ID = "bulk_upload_id";
	const USER_MODE = 'user_mode';
	const MEMBERS_COUNT = 'members_count';
	
	const ANONYMOUS_PUSER_ID = "KALANONYM";
	
	const MINIMUM_ID_TO_DISPLAY = 8999;
		
	const VUSER_VIDIUN = 0;
	
	const VUSER_ID_THAT_DOES_NOT_EXIST = 0;
	
	// different sort orders for browsing kswhos
	const VUSER_SORT_MOST_VIEWED = 1;  
	const VUSER_SORT_MOST_RECENT = 2;  
	const VUSER_SORT_NAME = 3;
	const VUSER_SORT_AGE = 4;
	const VUSER_SORT_COUNTRY = 5;
	const VUSER_SORT_CITY = 6;
	const VUSER_SORT_GENDER = 7;
	const VUSER_SORT_MOST_FANS = 8;
	const VUSER_SORT_MOST_ENTRIES = 9;
	const VUSER_SORT_PRODUCED_VSHOWS = 10;
	
	const PUSER_ID_REGEXP = '/^[A-Za-z0-9,!#\$%&\'\*\+\?\^_`\{\|}~.@-]{1,320}$/';
	const URL_PATTERN = '://';
	
	const PARTNER_INDEX_PREFIX  = 'p';
	const PERMISSION_NAMES_INDEX_PREFIX = 'pn';
	const ROLE_IDS_INDEX_PREFIX = 'ri';
	
	const UNIVERSAL_PERMISSION = '__ALL__';
	
	private $roughcut_count = -1;
	
	public static function getColumnNames()	{	return array ( 
		"screen_name" , "full_name" , "url_list" , "tags" , 
		"about_me" , "network_highschool" , "network_college" ,"network_other") ; 
	}
	public static function getSearchableColumnName () { return "search_text" ; }

	public function save(PropelPDO $con = null)
	{
		myPartnerUtils::setPartnerIdForObj( $this );
		
		mySearchUtils::setDisplayInSearch( $this );
		
		if (!$this->getIsAdmin()) {
			$this->setIsAdmin(false);
		}
					
		return parent::save( $con );	
	}
	
	public function preSave(PropelPDO $con = null)
	{
		// verify that all role ids set are valid
		if ($this->roleIdsChanged)
		{
			// add new roles
			$idsArray = explode(',',$this->roleIds);
			foreach ($idsArray as $id)
			{				
				if (!is_null($id) && $id != '')
				{
					// check if user role item exists
					$userRole = UserRolePeer::retrieveByPK($id);
					if (!$userRole || !in_array($userRole->getPartnerId(),array($this->getPartnerId(),PartnerPeer::GLOBAL_PARTNER) ) )
					{
						throw new vPermissionException("A user role with ID [$id] does not exist", vPermissionException::USER_ROLE_NOT_FOUND);
					}
				}
			}

			if ($this->getIsAccountOwner())
			{
				$adminRoleId = $this->getPartner()->getAdminSessionRoleId();
				if (!(in_array($adminRoleId, $idsArray)))
				{
				 	throw new vPermissionException('Account owner must be set with a partner administrator role', vPermissionException::ACCOUNT_OWNER_NEEDS_PARTNER_ADMIN_ROLE);	
				}
			}
		$this->setUpdatedAt(time());
		}
		return parent::preSave($con);
	}
	
	
	public function postSave(PropelPDO $con = null) 
	{
		if ($this->roleIdsChanged)
		{
			// delete old roles
			$c = new Criteria();
			$c->addAnd(VuserToUserRolePeer::VUSER_ID, $this->getId(), Criteria::EQUAL);
			VuserToUserRolePeer::doDelete($c);
			
			// add new roles
			$idsArray = explode(',',$this->roleIds);
			foreach ($idsArray as $id)
			{				
				if (!is_null($id) && $id != '')
				{
					$vuserToRole = new VuserToUserRole();
					$vuserToRole->setUserRoleId($id);
					$vuserToRole->setVuserId($this->getId());
					$vuserToRole->save();
				}
			}
		}
		
		$this->roleIdsChanged = false;
		
		//update all categoryVuser object with vuser
		
		//TODO - need to check if vuser needs to add job
			
		return parent::postSave();	
	}
	

	/* (non-PHPdoc)
	 * @see lib/model/om/Basevuser#postUpdate()
	 */
	public function postUpdate(PropelPDO $con = null)
	{
		if ($this->alreadyInSave)
			return parent::postUpdate($con);
		
		$objectUpdated = $this->isModified();
		$objectDeleted = false;
		if($this->isColumnModified(vuserPeer::STATUS) && $this->getStatus() == VuserStatus::DELETED) {
			$objectDeleted = true;
		}
			
		$oldLoginDataId = null;
		if ($this->isColumnModified(vuserPeer::LOGIN_DATA_ID)) {
			$oldLoginDataId = $this->oldColumnsValues[vuserPeer::LOGIN_DATA_ID];
		}

		if($this->isColumnModified(vuserPeer::PUSER_ID) &&
			categoryVuserPeer::isCategroyVuserExistsForVuser($this->getId()))
		{
			$userId = $this->getId();
			$puserId = $this->getPuserId();
			$this->updateCategoryVuser($userId, $puserId);
		}
		
		if ($this->isColumnModified(vuserPeer::EMAIL) && $this->getIsAccountOwner() && isset($this->oldColumnsValues[vuserPeer::EMAIL]) && !is_null($this->oldColumnsValues[vuserPeer::EMAIL])) {
			myPartnerUtils::emailChangedEmail($this->getPartnerId(), $this->oldColumnsValues[vuserPeer::EMAIL], $this->getEmail(), $this->getPartner()->getName() , PartnerPeer::VIDIUNS_PARTNER_EMAIL_CHANGE );
		}

		if ($this->getIsAccountOwner() && ( $this->isColumnModified(vuserPeer::EMAIL) || $this->isColumnModified(vuserPeer::FIRST_NAME) || $this->isColumnModified(vuserPeer::LAST_NAME) ))
		{
			$partner = $this->getPartner();
			$partner->setAccountOwnerVuserId($this->getId(), false);
			$partner->save();
		}

		if (($this->isColumnModified(vuserPeer::SCREEN_NAME) || $this->isColumnModified(vuserPeer::PUSER_ID))
			&& categoryVuserPeer::isCategroyVuserExistsForVuser($this->getId()))
		{
			$featureStatusToRemoveIndex = new vFeatureStatus();
			$featureStatusToRemoveIndex->setType(IndexObjectType::CATEGORY_USER);
			
			$featureStatusesToRemove = array();
			$featureStatusesToRemove[] = $featureStatusToRemoveIndex;
			
			$filter = new categoryVuserFilter();
			$filter->setUserIdEqual($this->getPuserId());
	
			vJobsManager::addIndexJob($this->getPartnerId(), IndexObjectType::CATEGORY_USER, $filter, true, $featureStatusesToRemove);
		}
				
		$ret = parent::postUpdate($con);
		
		if ($objectDeleted)
		{
			vEventsManager::raiseEvent(new vObjectDeletedEvent($this));
			// if user is deleted - check if shoult also delete login data
			UserLoginDataPeer::notifyOneLessUser($this->getLoginDataId());
		}

		if($objectUpdated)
		{
		    vEventsManager::raiseEvent(new vObjectUpdatedEvent($this));
		    if (!$objectDeleted && !is_null($oldLoginDataId) && is_null($this->getLoginDataId()))
		    {
			    // if login was disabled - check if should also delete login data
			    UserLoginDataPeer::notifyOneLessUser($oldLoginDataId);
		    }
		}
			
		return $ret;
	}
		
	public function setRoughcutCount ( $count )
	{
		$this->roughcut_count = $count ;
	}
	
	// TODO - move implementation to vuserPeer - i'm not doing so now because there are changes i don't want to commit 
	public function getRoughcutCount ()
	{
		if ( $this->roughcut_count == -1  )
		{
			$c = new Criteria();
			$c->add ( entryPeer::TYPE , entryType::MIX );
			$c->add ( entryPeer::VUSER_ID , $this->getId() );
			$this->roughcut_count = entryPeer::doCount( $c );
		}
		return $this->roughcut_count;
	}

	
	static public function getVuserById ( $id )
	{
		$c = new Criteria();
		$c->add(vuserPeer::ID, $id );
		return vuserPeer::doSelectOne($c);
	}

	// TODO - PERFORMANCE DB - move to use cache !!
	// will increment the views by 1
	public function incViews ( $should_save = true  )
	{
		myStatisticsMgr::incVuserViews( $this );
/*		
		$v = $this->getViews ( );
		if ( ! is_numeric( $v ) ) $v=0;
		$this->setViews( $v + 1 );
		
		if ( $should_save) $this->save();
*/
	}

	// TODO - PERFORMANCE DB - move to use cache !!
	// will update the number of fans of vuser according the fans table
	// this should not be called ! - it is handled via myStatisticsMgr
	private  function updateFans ( $should_save = true )
	{
		// select all the people who favor me (ignore privacy)
		$c = new Criteria();
		$c->add(favoritePeer::SUBJECT_ID, $this->getId());
		$c->add(favoritePeer::SUBJECT_TYPE, favorite::SUBJECT_TYPE_USER);
//		$c->add(favoritePeer::PRIVACY, $privacy);
		$c->setDistinct();		
		$fan_count = favoritePeer::doCount( $c );
		$this->setFans( $fan_count );
		
		if ( $should_save) $this->save();
		
		return $fan_count;
	}

/*	
	// TODO - PERFORMANCE DB - move to use cache !!
	// will update the number of entries of vuser 
	// should be called every time this vuser contributes
	public function incEntries ( $should_save = true )
	{
		$v = $this->getEntries();
		if ( ! is_numeric( $v ) ) $v=0;
		$this->setEntries( $v + 1 );
		
		if ( $should_save) $this->save();
	}

	// TODO - PERFORMANCE DB - move to use cache !!
	// will update the number of produced_vshows of vuser 
	// should be called every time this vuser publishes a vshow
	public function incProducedVshows ( $should_save = true )
	{
		$v = $this->getProducedVshows();
		if ( ! is_numeric( $v ) ) $v=0;
		$this->setProducedVshows( $v + 1 );
		
		if ( $should_save) $this->save();
	}

*/

	public function getBlockEmailStr()
	{
		return myBlockedEmailUtils::createBlockEmailStr ( $this->getEmail() );	
	}

	public function getBlockEmailUrl()
	{
		return myBlockedEmailUtils::createBlockEmailUrl ( $this->getEmail() );	
	}
		
	public function getPicturePath() 
	{ 
		$picfile = $this->getPicture();
		$picfile = substr( $picfile, strpos( $picfile, '^'));		
		return myContentStorage::getGeneralEntityPath("vuser/pic", $this->getId(), $this->getId(), $picfile);
	}

	public function setPicture($filename )
	{
		if (vCurrentContext::isApiV3Context())
		{
			parent::setPicture($filename);
			return;
		}
		
		parent::setPicture( myContentStorage::generateRandomFileName( $filename, $this->getPicture() ) );
	}

	public function getPictureUrl() 
	{ 
		if ( parent::getPicture() == null ) return "";
		$path = $this->getPicturePath ( );
		$url = requestUtils::getHost() . $path ;
		return $url;
	}
	
	public function setTags($tags , $update_db = true )
	{
		if ($this->tags !== $tags) {
			$tags = vtagword::updateTags($this->tags, $tags , $update_db );
			parent::setTags($tags);
		}
	} 
	

	public function getCreatedAtAsInt ()
	{
		return $this->getCreatedAt( null );
	}

	public function getUpdateAtAsInt ()
	{
		return $this->getUpdatedAt( null );
	}
	
	public function getFormattedCreatedAt( $format = dateUtils::VIDIUN_FORMAT )
	{
		return dateUtils::formatVidiunDate( $this , 'getCreatedAt' , $format );
	}

	public function getFormattedUpdatedAt( $format = dateUtils::VIDIUN_FORMAT )
	{
		return dateUtils::formatVidiunDate( $this , 'getUpdatedAt' , $format );
	}
	
	public function getHomepageURL()
	{
		if ( $this->getURLlist() != null && $this->getURLlist() != "" )
		{
			$urls = explode( "|" , $this->getURLlist()  );
			if ( count ($urls) > 0 ) return $urls[0];
		}
		else return null;
	}

	public function getMyspaceURL()
	{
		if ( $this->getUrlList() != null && $this->getUrlList() != "" )
		{
			$urls = explode( "|" , $this->getURLlist()  );
			if ( count ($urls) > 1 ) return $urls[1];
		}
		else return null;
	}
	
	public function setHomepageMyspace( $homepage, $myspace )
	{
		$this->setUrlList( vString::add_http( $homepage ).'|'. vString::add_http( $myspace) );
	}
	
	public function getIMArray()
	{
		if ( $this->getImList() != null && $this->getImList() != "" )
		{
			$ims = explode( "|" , $this->getImList()  );
			if ( count( $ims ) == 6 )
			{	$ims_final = array( "AIM"=>$ims[0], "MSN"=>$ims[1], "ICQ"=>$ims[2], "Skype"=>$ims[3], "Yahoo"=>$ims[4], "Google"=>$ims[5]);
				return $ims_final; }
				else return null;
		}
		else return null;
	}
	
	public function setIMArray( $aim, $msn, $icq, $skype, $yahoo, $google)
	{
		$this->setImList( $aim.'|'.$msn.'|'.$icq.'|'.$skype.'|'.$yahoo.'|'.$google);
	}
	
	public function getMobileArray()
	{
		if ( $this->getMobileNum() != null && $this->getMobileNum() != "" )
		{
			$mobilearr = explode( "|" , $this->getMobileNum()  );
			if ( count( $mobilearr ) == 3 )
			{	$mobile_final = array( "countrycode"=>$mobilearr[0], "areacode"=>$mobilearr[1], "number"=>$mobilearr[2]);
				return $mobile_final; }
				else return null;
		}
		else return null;
	}
	
	public function setMobileArray( $country, $area, $number )
	{
		$this->setMobileNum( $country.'|'.$area.'|'.$number);
		
	}
	
	
	public function getTagsArray()
	{
		return vtagword::getTagsArray ( $this->getTags() );
	}
	
	public function getDateOfBirth($format = '%x')
	{
		$dateStr = parent::getDateOfBirth();
		if ($dateStr  === null)
			return null;
			
		return strtotime($dateStr); 
	}
	
	public function setDateOfBirth($v)
	{
		// keep only the date from the timestamp
		$year = (int)date("Y", $v);
		$day = (int)date("j", $v);
		$month = (int)date("n", $v);
		$dateOnly = mktime(0, 0, 0, $month, $day, $year);
		parent::setDateOfBirth(date("Y-m-d", $dateOnly));
	}
	
	/**
	 * caluculates the age of the vuser according to his date_of_birth
	 */
	public function getAge ()
	{
  		$now_year = date("Y");
		$dateFormat = new sfDateFormat();
		$dob = $this->getDateOfBirth();
		if ( $dob != null && $dob != "0000-00-00" )
		{
			$yob = $dateFormat->format( $dob , 'Y');
			return $now_year-$yob;
		} 
		return null;
	}

	public function setGenderByText ( $gender_text )
	{
		$gender_text = strtolower ( $gender_text );
		if ( $gender_text == "male" ) $this->setGender( 1 );
		elseif ( $gender_text == "female" ) $this->setGender( 2 );
		else $this->setGender( 0 );
	}
	
	public function getGenderText ()
	{
		$gender = $this->getGender();
		if ( $gender === 1 ) return "Male";
		if ( $gender === 2 ) return "Female";
		return "";
	}
	
	public function setCountryByLongIp ( $long_ip )
	{
		$ip = long2ip ( $this->getRegistrationIp() ) ;
		return $this->setCountyByIp ( $ip );
	}
	
	public function setCountyByIp ( $ip )
	{
		//long2ip ( $user->getRegistrationIp() ) ;
		// for first time use, geocode registration IP address
		$myGeoCoder = new myIPGeocoder();
		$country_code = $myGeoCoder->iptocountry( $ip );
		if ( $country_code == null || $country_code == "" || $country_code == "ZZ" ) $country_code  = "US"; //if we can't identify, assume the US
		if ( $this->getCountry() == "" ) $this->setCountry( $country_code );
		
		return $this->getCountry();
	}
	
	public function getLastVshowId( )
	{
		// return the last vshow_id created by this vuser
		$c = new Criteria();
		$c->add ( vshowPeer::PRODUCER_ID , $this->getId() );
		$c->addDescendingOrderByColumn( vshowPeer::ID );
		$vshow = vshowPeer::doSelectOne();

		if ( $vshow )
		{
			return $vshow->getId();
		}
		else
		{
			return 0;
		}
	}

	public function getLastVshowUrl( )
	{
		// return the last vshow_id created by this vuser
		$c = new Criteria();
		$c->add ( vshowPeer::PRODUCER_ID , $this->getId() );
		$c->addDescendingOrderByColumn( vshowPeer::ID );
		$vshow = vshowPeer::doSelectOne( $c );
				
		$host = requestUtils::getHost() . "/";
		
		if ( $vshow )
		{
			return "<a href='" . $host . "/id/" . $vshow->getId() . "'>" . $vshow->getName() . "</a>";
		}
		else
		{
			// This should never happen
			return "<a href='" . $host . "'>Vidiun</a>";
		}
	}
	
	
	public function disable ( $disable_all_content = false )
	{
		$this->setStatus ( VuserStatus::BLOCKED );
	}

	public function moderate ($new_moderation_status) 
	{
		$error_msg = "Moderation status [$new_moderation_status] not supported by user object	";
		switch($new_moderation_status) 
		{
			case moderation::MODERATION_STATUS_APPROVED:
				throw new Exception($error_msg);
				break;			
			case moderation::MODERATION_STATUS_BLOCK:
				myNotificationMgr::createNotification(vNotificationJobData::NOTIFICATION_TYPE_USER_BANNED , $this );		
				break;
			case moderation::MODERATION_STATUS_DELETE:
				throw new Exception($error_msg);
				break;
			case moderation::MODERATION_STATUS_PENDING:
				throw new Exception($error_msg);
				break;
			case moderation::MODERATION_STATUS_REVIEW:
				myNotificationMgr::createNotification(vNotificationJobData::NOTIFICATION_TYPE_USER_BANNED , $this );
//				throw new Exception($error_msg);
				break;
			default:
				throw new Exception($error_msg);
				break;
		}
		
		$this->save();
	}
	
	// TODO - fix when we enable the blocking of users
	public function getModerationStatus()
	{
//		if ( $this->getStatus() == VuserStatus::BLOCKED )
//			return moderation::MODERATION_STATUS_BLOCK;
		return moderation::MODERATION_STATUS_APPROVED;
	}

	
	public function getPuserId()
	{
		$puserId = parent::getPuserId();
		if (is_null($puserId) && !vCurrentContext::isApiV3Context())
			$puserId = PuserVuserPeer::getPuserIdFromVuserId ( $this->getPartnerId(), $this->getId() );
		
		return $puserId;
	}
	
	// this will make sure that the extra data set in the search_text won't leak out 
	public function getSearchText()	{	return '';	}
	
	public function getVuserId()
	{
		return $this->getId();
	}
	
	/**
	 * Set last_login_time parameter to $time (in custom_data)
	 * @param int $time timestamp
	 */
	public function setLastLoginTime($time)
	{
		$this->putInCustomData('last_login_time', $time);
	}
	
	/**
	 * @return last_login_time parameter from custom_data
	 */
	public function getLastLoginTime()
	{
		return $this->getFromCustomData('last_login_time');
	}
	
	/**
	 * Set allowed_partner_ids parameter to $allowedPartnerIds (in custom_data)
	 * @param string $allowed_partner_ids
	 */
	public function setAllowedPartners($allowedPartnerIds)
	{
		$this->putInCustomData('allowed_partner_ids', $allowedPartnerIds);
	}
	
	/**
	 * @return allowed_partner_ids parameter from custom_data
	 */
	public function getAllowedPartners()
	{
		return $this->getFromCustomData('allowed_partner_ids');
	}
	
	/**
	 * Set allowed_partner_packages parameter to $allowedPartnerPackages (in custom_data)
	 * @param string $allowed_partner_packages
	 */
	public function setAllowedPartnerPackages($allowedPartnerPackags)
	{
		$this->putInCustomData('allowed_partner_packages', $allowedPartnerPackags);
	}
	
	/**
	 * @return allowed_partner_packages parameter from custom_data
	 */
	public function getAllowedPartnerPackages()
	{
		return $this->getFromCustomData('allowed_partner_packages');
	}

	//TODO: check if needed
	public function getIsAdmin()
	{
		return parent::getisAdmin() == true;
	}
	
	/**
	 * Set language parameter to $language (in custom_data)
	 * @param string $language
	 */
	public function setLanguage($language)
	{
		$this->putInCustomData('language', $language);
	}
	
	/**
	 * @return language parameter from custom_data
	 */
	public function getLanguage()
	{
		return $this->getFromCustomData('language');
	}
	
	
	/**
	 * @return Vuser's full name = first_name + last_name
	 */
	public function getFullName()
	{
		if (!$this->getFirstName() && parent::getFullName())
		{
			// full_name is deprecated - this is for backward compatibiliy and for migration
			VidiunLog::alert('Field [full_name] on object [vuser] is deprecated but still being read');
			return parent::getFullName();
		}
		return trim($this->getFirstName().' '.$this->getLastName());
	}
	
	
	private function setStatusUpdatedAt($v)
	{		
		// we treat '' as NULL for temporal objects because DateTime('') == DateTime('now')
		// -- which is unexpected, to say the least.
		if ($v === null || $v === '') {
			$dt = null;
		} elseif ($v instanceof DateTime) {
			$dt = $v;
		} else {
			// some string/numeric value passed; we normalize that so that we can
			// validate it.
			try {
				if (is_numeric($v)) { // if it's a unix timestamp
					$dt = new DateTime('@'.$v, new DateTimeZone('UTC'));
					// We have to explicitly specify and then change the time zone because of a
					// DateTime bug: http://bugs.php.net/bug.php?id=43003
					$dt->setTimeZone(new DateTimeZone(date_default_timezone_get()));
				} else {
					$dt = new DateTime($v);
				}
			} catch (Exception $x) {
				throw new PropelException('Error parsing date/time value: ' . var_export($v, true), $x);
			}
		}

		$curValue = $this->getStatusUpdatedAt();
		if ( $curValue !== null || $dt !== null ) {
			// (nested ifs are a little easier to read in this case)

			$currNorm = ($curValue !== null && $tmpDt = new DateTime($curValue)) ? $tmpDt->format('Y-m-d H:i:s') : null;
			$newNorm = ($dt !== null) ? $dt->format('Y-m-d H:i:s') : null;

			if ( ($currNorm !== $newNorm) // normalized values don't match 
					)
			{
				$newValue = ($dt ? $dt->format('Y-m-d H:i:s') : null);
				$this->putInCustomData('status_updated_at', $newValue, null);
			}
		} // if either are not null

		return $this;
	}
	
	public function getStatusUpdatedAt($format = 'Y-m-d H:i:s')
	{
		$value = $this->getFromCustomData('status_updated_at', null, null);
		
		if ($value === null) {
			return null;
		}

		if ($value === '0000-00-00 00:00:00') {
			// while technically this is not a default value of NULL,
			// this seems to be closest in meaning.
			return null;
		} else {
			try {
				$dt = new DateTime($value);
			} catch (Exception $x) {
				throw new PropelException("Internally stored date/time/timestamp value could not be converted to DateTime: " . var_export($value, true), $x);
			}
		}

		if ($format === null) {
			// We cast here to maintain BC in API; obviously we will lose data if we're dealing with pre-/post-epoch dates.
			return (int) $dt->format('U');
		} elseif (strpos($format, '%') !== false) {
			return strftime($format, $dt->format('U'));
		} else {
			return $dt->format($format);
		}
	}
	 
	
	private function setDeletedAt($v)
	{		
		// we treat '' as NULL for temporal objects because DateTime('') == DateTime('now')
		// -- which is unexpected, to say the least.
		if ($v === null || $v === '') {
			$dt = null;
		} elseif ($v instanceof DateTime) {
			$dt = $v;
		} else {
			// some string/numeric value passed; we normalize that so that we can
			// validate it.
			try {
				if (is_numeric($v)) { // if it's a unix timestamp
					$dt = new DateTime('@'.$v, new DateTimeZone('UTC'));
					// We have to explicitly specify and then change the time zone because of a
					// DateTime bug: http://bugs.php.net/bug.php?id=43003
					$dt->setTimeZone(new DateTimeZone(date_default_timezone_get()));
				} else {
					$dt = new DateTime($v);
				}
			} catch (Exception $x) {
				throw new PropelException('Error parsing date/time value: ' . var_export($v, true), $x);
			}
		}

		$curValue = $this->getDeletedAt();
		if ( $curValue !== null || $dt !== null ) {
			// (nested ifs are a little easier to read in this case)

			$currNorm = ($curValue !== null && $tmpDt = new DateTime($curValue)) ? $tmpDt->format('Y-m-d H:i:s') : null;
			$newNorm = ($dt !== null) ? $dt->format('Y-m-d H:i:s') : null;

			if ( ($currNorm !== $newNorm) // normalized values don't match 
					)
			{
				$newValue = ($dt ? $dt->format('Y-m-d H:i:s') : null);
				$this->putInCustomData('deleted_at', $newValue, null);
			}
		} // if either are not null

		return $this;
	}
	
	public function getDeletedAt($format = 'Y-m-d H:i:s')
	{
		$value = $this->getFromCustomData('deleted_at', null, null);
		
		if ($value === null) {
			return null;
		}

		if ($value === '0000-00-00 00:00:00') {
			// while technically this is not a default value of NULL,
			// this seems to be closest in meaning.
			return null;
		} else {
			try {
				$dt = new DateTime($value);
			} catch (Exception $x) {
				throw new PropelException("Internally stored date/time/timestamp value could not be converted to DateTime: " . var_export($value, true), $x);
			}
		}

		if ($format === null) {
			// We cast here to maintain BC in API; obviously we will lose data if we're dealing with pre-/post-epoch dates.
			return (int) $dt->format('U');
		} elseif (strpos($format, '%') !== false) {
			return strftime($format, $dt->format('U'));
		} else {
			return $dt->format($format);
		}
	}
	
	/**
	 * Set status and statusUpdatedAt fields
	 * @see Basevuser::setStatus()
	 * @throws vUserException::CANNOT_DELETE_OR_BLOCK_ROOT_ADMIN_USER
	 */
	public function setStatus($status)
	{
		if (($status == VuserStatus::DELETED || $status == VuserStatus::BLOCKED) && $this->getIsAccountOwner()) {
			throw new vUserException('', vUserException::CANNOT_DELETE_OR_BLOCK_ROOT_ADMIN_USER);
		}
				
		parent::setStatus($status);
		$this->setStatusUpdatedAt(time());
		if ($status == VuserStatus::DELETED) {
			$this->setDeletedAt(time());
		}
	}
	
	/**
	 * Return user's login data object if valid
	 */
	public function getLoginData()
	{
		$loginDataId = $this->getLoginDataId();
		if (!$loginDataId) {
			return null;
		}
		return UserLoginDataPeer::retrieveByPK($loginDataId);
	}
	
	public function getAllowedPartnerIds(partnerFilter $partnerFilter = null)
	{
		$currentLoginDataId = $this->getLoginDataId();
		if (!$currentLoginDataId) {
			return array($this->getPartnerId());
		}
		$c = new Criteria();
		$c->addSelectColumn(vuserPeer::PARTNER_ID);
		$c->addAnd(vuserPeer::LOGIN_DATA_ID, $currentLoginDataId, Criteria::EQUAL);
		$c->addAnd(vuserPeer::STATUS, VuserStatus::ACTIVE, Criteria::EQUAL);
		$c->addAnd(vuserPeer::IS_ADMIN, true, Criteria::EQUAL);
		vuserPeer::setUseCriteriaFilter(false);
		$stmt = vuserPeer::doSelectStmt($c);
		$ids = $stmt->fetchAll(PDO::FETCH_COLUMN);
		vuserPeer::setUseCriteriaFilter(true);
		
		// apply filter on partner ids
		if ($partnerFilter)
		{
    		$c = new Criteria();
    		$c->addSelectColumn(PartnerPeer::ID);
    		$partnerFilter->setIdIn($ids);
    		$partnerFilter->attachToCriteria($c);
    		$stmt = PartnerPeer::doSelectStmt($c);
    		$ids = $stmt->fetchAll(PDO::FETCH_COLUMN);
		}
		
		return $ids;
	}
	

	
	// -- start of deprecated functions
	
	public function setSalt($v)
	{
		// salt column is deprecated
		VidiunLog::alert('Field [salt] on object [vuser] is deprecated');
		throw new Exception('Field [salt] on object [vuser] is deprecated');
	}
	
	public function setSha1Password($v)
	{
		// sha1_password column is deprecated
		VidiunLog::alert('Field [sha1_password] on object [vuser] is deprecated - trace: ');
		throw new Exception('Field [sha1_password] on object [vuser] is deprecated');
	}
	
	public function getSalt()
	{
		// salt column is deprecated
		VidiunLog::alert('Field [salt] on object [vuser] is deprecated - getSalt should be removed from schema after migration');
		return parent::getSalt();
	}
	
	public function getSha1Password()
	{
		// sha1_password column is deprecated
		VidiunLog::alert('Field [sha1_password] on object [vuser] is deprecated - getSha1Password should be removed from schema after migration');
		return parent::getSha1Password();
	}
	
	public function setFullName($v)
	{
		// full_name column is deprecated
		VidiunLog::alert('Field [full_name] on object [vuser] is deprecated');
		list($firstName, $lastName) = vString::nameSplit($v);
		$this->setFirstName($firstName);
		$this->setLastName($lastName);
	}
	
	// -- end of deprecated functions
	
	
	/**
	 * Disable user login
	 * @throws vUserException::USER_LOGIN_ALREADY_DISABLED
	 * @throws vUserException::CANNOT_DISABLE_LOGIN_FOR_ADMIN_USER
	 */
	public function disableLogin()
	{
		if ($this->getIsAdmin())
		{
			throw new vUserException('', vUserException::CANNOT_DISABLE_LOGIN_FOR_ADMIN_USER);
		}
		
		if (!$this->getLoginDataId())
		{
			throw new vUserException('', vUserException::USER_LOGIN_ALREADY_DISABLED);
		}
		
		$loginDataId = $this->getLoginDataId();
		$this->setLoginDataId(null);
		$this->save();
		
		return true;	
	}
	
	/**
	 * Enable user login 
	 * @param string $loginId
	 * @param string $password
	 * @param bool $checkPasswordStructure
	 * @throws vUserException::USER_LOGIN_ALREADY_ENABLED
	 * @throws vUserException::INVALID_EMAIL
	 * @throws vUserException::INVALID_PARTNER
	 * @throws vUserException::ADMIN_LOGIN_USERS_QUOTA_EXCEEDED
	 * @throws vUserException::PASSWORD_STRUCTURE_INVALID
	 * @throws vUserException::LOGIN_ID_ALREADY_USED
	 */
	public function enableLogin($loginId, $password = null, $checkPasswordStructure = true, $sendEmail = null)
	{
		if (!$password)
		{
			$password = UserLoginDataPeer::generateNewPassword();
			if (is_null($sendEmail)) {
				$sendEmail = true;
			}
		}
				
		if ($this->getLoginDataId())
		{
			throw new vUserException('', vUserException::USER_LOGIN_ALREADY_ENABLED);
		}
		
		$loginDataExisted = null;
		$loginData = UserLoginDataPeer::addLoginData($loginId, $password, $this->getPartnerId(), $this->getFirstName(), $this->getLastName(), $this->getIsAdmin(), $checkPasswordStructure, $loginDataExisted);	
		if (!$loginData)
		{
			throw new vUserException('', vUserException::LOGIN_DATA_NOT_FOUND);
		}
		
		$this->setLoginDataId($loginData->getId());

		//Email notification on user creation is sent while using vuser email so make sure this field is set before enabling login
		//if not than set the email to be the $loginId provided to this action (we now know this is a valid email since "addLoginData" verifies this)
		if(!$this->getEmail()) {
			$this->setEmail($loginId);
		}
		
		if ($sendEmail)
		{
			if ($loginDataExisted) {
				vuserPeer::sendNewUserMail($this, true);
			}
			else {
				vuserPeer::sendNewUserMail($this, false);
			}
			if(!PermissionPeer::isValidForPartner(PermissionName::FEATURE_DISABLE_NEW_USER_EMAIL, $this->getPartnerId()))
				vuserPeer::sendNewUserMailToAdmins($this);
		}	
		return $this;
	}
	
	
	public function getIsAccountOwner()
	{
		if ($this->isNew()) {
			return false;
		}
		try {
			$partner = $this->getPartner();
		}
		catch (Exception $e) {
			return false;
		}
		if (!$partner) {
			return false;
		}
		else {
			return $this->getId() == $partner->getAccountOwnerVuserId();
		}
	}
	
	/**
	 * @return Partner
	 */
	public function getPartner()
	{
		$partner = PartnerPeer::retrieveByPK($this->getPartnerId());
		return $partner;
	}
	
	
	// ----------------------------------------
	// -- start of user role handling functions
	// ----------------------------------------
		
	private $roleIds = null;
	private $roleIdsChanged = false;
				
	/**
	 * @return string Comma seperated string of role names associated to the current user
	 */
	public function getUserRoleNames()
	{		
		if (!$this->getRoleIds())
			return '';
		
		$c = new Criteria();
		$c->add(UserRolePeer::ID, explode(',',$this->getRoleIds()), Criteria::IN);
		$roles = UserRolePeer::doSelect($c);
		$names = array();
		foreach ($roles as $role)
		{
			$names[] = $role->getName();
		}
		return implode(',', $names);
	}
	
	/**
	 * @return string Comma seperated string of role ids associated to the current user
	 */
	public function getRoleIds()
	{
		if (is_null($this->roleIds))
		{
			$this->roleIds = '';
			$c = new Criteria();
			$c->addAnd(VuserToUserRolePeer::VUSER_ID, $this->getId(), Criteria::EQUAL);
			$selectResults = VuserToUserRolePeer::doSelect($c);
			foreach ($selectResults as $selectResult)
			{
				if ($this->roleIds != '')
				{
					$this->roleIds .= ',';
				}
				$this->roleIds .= $selectResult->getUserRoleId();
			}  
		}
		return $this->roleIds;
	}

	/**
	 * Set the roles of the current vuser
	 * @param string $idsString A comma seperated string of user role IDs
	 */
	public function setRoleIds($idsString)
	{		
		$this->roleIds = $idsString;
		$this->roleIdsChanged = true;
	}

		
	/**
	 * Checks if the current user has one of the permissions with the given names
	 * @param array $permissionNamesArray Permission names
	 * @return true or false
	 */
	public function hasPermissionOr(array $permissionNamesArray)
	{
		$roleIds = explode(',', $this->getRoleIds());
		foreach ($roleIds as $roleId)
		{
			$userRole = UserRolePeer::retrieveByPK($roleId);
			if ($userRole) {
				$permissions = explode(',', $userRole->getPermissionNames());
				foreach ($permissionNamesArray as $permissionName) {
					if (in_array($permissionName, $permissions)) {
						return true;
					}
				}
			}
		}
		return false;
	}
	

	public function getCacheInvalidationKeys()
	{
		return array("vuser:id=".strtolower($this->getId()), "vuser:partnerId=".strtolower($this->getPartnerId()).",puserid=".strtolower($this->getPuserId()), "vuser:loginDataId=".strtolower($this->getLoginDataId()));
	}
	
	/* (non-PHPdoc)
     * @see IIndexable::getIntId()
     */
    public function getIntId ()
    {
        return $this->id;
        
    }

	/* (non-PHPdoc)
     * @see IIndexable::getEntryId()
     */
    public function getEntryId ()
    {
        // TODO Auto-generated method stub
        
    }

    public function getIndexObjectName() {
    	return "vuserIndex";
    }
	
	/* (non-PHPdoc)
	 * @see IIndexable::indexToSearchIndex()
	 */
	public function indexToSearchIndex()
	{
		vEventsManager::raiseEventDeferred(new vObjectReadyForIndexEvent($this));
	}
	
	public function getSphinxIndexName()
	{
		return kSphinxSearchManager::getSphinxIndexName(kuserIndex::getObjectIndexName());
	}
    
	/* (non-PHPdoc)
	 * @see lib/model/om/Baseentry#postInsert()
	 */
	public function postInsert(PropelPDO $con = null)
	{
		parent::postInsert($con);
	
		if (!$this->alreadyInSave)
			vEventsManager::raiseEvent(new vObjectAddedEvent($this));
	}
	
	// --------------------------------------
	// -- end of user role handling functions
	// --------------------------------------
	
	//Custom data functions
	
    public function setBulkUploadId ($bulkUploadId){$this->putInCustomData (self::BULK_UPLOAD_ID, $bulkUploadId);}
	public function getBulkUploadId (){return $this->getFromCustomData(self::BULK_UPLOAD_ID);}

	public function setUserMode ($v){$this->putInCustomData (self::USER_MODE, $v);}
	public function getUserMode (){return $this->getFromCustomData(self::USER_MODE, null, VuserMode::NONE);}

	public function setMembersCount ($v){$this->putInCustomData (self::MEMBERS_COUNT, $v);}
	public function getMembersCount (){return $this->getFromCustomData(self::MEMBERS_COUNT, null, 0);}

	/**
	 * Force modifiedColumns to be affected even if the value not changed
	 * 
	 * @see Basevuser::setUpdatedAt()
	 */
	public function setUpdatedAt($v)
	{
		parent::setUpdatedAt($v);
		if(!in_array(vuserPeer::UPDATED_AT, $this->modifiedColumns, false))
			$this->modifiedColumns[] = vuserPeer::UPDATED_AT;
			
		return $this;
	}
	
	/**
	 * Getter returns the indexed version of the permission names on the role of the vuser separated by commas
	 * @return string
	 */
	public function getIndexedPermissionNames ()
	{
		$permissionNamesArray  = array();
		if ($this->getRoleIds())
		{
			$roleIds = explode(",", $this->getRoleIds());
			$roles = UserRolePeer::retrieveByPKs($roleIds);
			foreach($roles as $role)
			{
				$permissionNames = $role->getPermissionNames(null, true);
				$permissionNames = str_replace("*", self::UNIVERSAL_PERMISSION, $permissionNames);
				$permissionNamesArray = array_merge($permissionNamesArray, explode(",", $permissionNames));
			}			
		}		
		
		return self::getIndexedFieldValue('vuserPeer::PERMISSION_NAMES', implode(',', $permissionNamesArray), $this->getPartnerId());
	}	
	
	/**
	 * Get the indexed value for the role ids to index to the search engine
	 * @param string $roleIds
	 * @return string
	 */
	public function getIndexedRoleIds ()
	{
		return self::getIndexedFieldValue('vuserPeer::ROLE_IDS', $this->getRoleIds(), $this->getPartnerId());
	}
	
	/**
	 * Returns indexed value for field
	 * @param string $fieldName
	 * @param string $fieldValue
	 * @param string $partnerId
	 * @return string
	 */
	public static function getIndexedFieldValue ($fieldName, $fieldValue, $partnerId)
	{
		if ($fieldName == "vuserPeer::ROLE_IDS")
		{
			$prefix = self::ROLE_IDS_INDEX_PREFIX;
		}
		else if($fieldName == 'vuserPeer::PERMISSION_NAMES')
		{
			$prefix = self::PERMISSION_NAMES_INDEX_PREFIX;
		}

		$fieldValuesArr = explode(',', $fieldValue);
		foreach ($fieldValuesArr as &$singleValue)
		{
			$singleValue = $partnerId . $prefix . $singleValue;
		}
		return implode(',', $fieldValuesArr);				
	}

	/**
	 * check if CategoryVuser need also to be update and if so does it
	 *
	 * @param int $userId The user's unique identifier in the partner's system
	 * @param string $puserId The user parameters to update
	 *
	 */
	private function updateCategoryVuser($userId,  $puserId)
	{
		$dbCategoryVuserArray = categoryVuserPeer::retrieveByVuserId($userId);
		foreach ($dbCategoryVuserArray as $dbCategoryVuser) {
			$dbCategoryVuser->updateVuser($puserId);
			$dbCategoryVuser->save();
		}
	}

	/**
	 * return the name of the elasticsearch index for this object
	 */
	public function getElasticIndexName()
	{
		return ElasticIndexMap::ELASTIC_VUSER_INDEX;
	}

	/**
	 * return the name of the elasticsearch type for this object
	 */
	public function getElasticObjectType()
	{
		return ElasticIndexMap::ELASTIC_VUSER_TYPE;
	}

	/**
	 * return the elasticsearch id for this object
	 */
	public function getElasticId()
	{
		return $this->getId();
	}

	/**
	 * return the elasticsearch parent id or null if no parent
	 */
	public function getElasticParentId()
	{
		return null;
	}

	/**
	 * get the params we index to elasticsearch for this object
	 */
	public function getObjectParams($params = null)
	{
		$body = array(
			'partner_id' => $this->getPartnerId(),
			'status' => $this->getStatus(),
			'partner_status' => elasticSearchUtils::formatPartnerStatus($this->getPartnerId(), $this->getStatus()),
			'screen_name' => $this->getScreenName(),
			'vuser_type' => $this->getType(),
			'email' => $this->getEmail(),
			'tags' => $this->getTagsArray(), //todo - check
			'created_at' => $this->getCreatedAtAsInt(),
			'updated_at' => $this->getUpdateAtAsInt(),
			'first_name_ft' => $this->getFirstName(),
			'last_name_ft' => $this->getLastName(),
			'role_ids' => explode(',',$this->getRoleIds()), //todo - maybe add help to elastic here
			'permission_names' => $this->getIndexedPermissionNames(), //todo - replace to array
			'puser_id' => $this->getPuserId(),
			'members_count' => $this->getMembersCount()
		);
		$this->addGroupUserDataToObjectParams($body);
		elasticSearchUtils::cleanEmptyValues($body);

		return $body;
	}


	protected function addGroupUserDataToObjectParams(&$body)
	{
		$vgroupIds = array();
		$groupUserData = array();

		$vuserVgroups =  VuserVgroupPeer::retrieveVgroupByVuserIdAndPartnerId($this->getVuserId(), $this->getPartnerId());
		if (!$vuserVgroups)
		{
			return;
		}
		foreach ($vuserVgroups as $vuserVgroup)
		{
			/* @var $vuserVgroup VuserVgroup */
			$vgroupIds[] = $vuserVgroup->getVgroupId();
			$groupUserData[] = elasticSearchUtils::formatGroupIdCreationMode($vuserVgroup->getVgroupId(), $vuserVgroup->getCreationMode());
		}


		$body['group_ids'] = $vgroupIds;
		$body['group_user_data'] = $groupUserData;
	}

	/**
	 * return the save method to elastic: ElasticMethodType::INDEX or ElasticMethodType::UPDATE
	 */
	public function getElasticSaveMethod()
	{
		return ElasticMethodType::INDEX;
	}

	/**
	 * Index the object into elasticsearch
	 */
	public function indexToElastic($params = null)
	{
		vEventsManager::raiseEventDeferred(new vObjectReadyForElasticIndexEvent($this));
	}

	/**
	 * return true if the object needs to be deleted from elastic
	 */
	public function shouldDeleteFromElastic()
	{
		if($this->getStatus() == VuserStatus::DELETED)
			return true;
		return false;
	}

	/**
	 * return the name of the object we are indexing
	 */
	public function getElasticObjectName()
	{
		return 'vuser';
	}

	public function getElasticEntryId()
	{
		return null;
	}

}
