<?php

/**
 * Subclass for performing query and update operations on the 'puser_vuser' table.
 *
 * 
 *
 * @package Core
 * @subpackage model
 */ 
class PuserVuserPeer extends BasePuserVuserPeer
{
	public static function retrieveByPartnerAndUid ( $partner_id , $subp_id, $puser_id , $join_vuser = false )
	{
		$c = new Criteria();
		myCriteria::addComment( $c , "PuserVuserPeer::retrieveByPartnerAndUid" );
		$c->add ( self::PARTNER_ID , $partner_id );
		if ($subp_id)
			$c->add ( self::SUBP_ID , $subp_id );
		$c->add ( self::PUSER_ID , $puser_id );
		if ( $join_vuser )
			$puser_vusers = self::doSelectJoinvuser( $c );
		else
			$puser_vusers = self::doSelect( $c );

		if ( count ( $puser_vusers ) > 0 )
		{
			$puser_vuser = $puser_vusers[0];
		}
		else
		{
			$puser_vuser = null;
		}
		
		return $puser_vuser;
	}
	
	/**
		Returns newly created puser - after creating it's corresponding vuser.
		If the puser_vuser already exists && $verify_not_exists==true , don't create a new one and return the existing one
	*/
	public static function createPuserVuser ( $partner_id , $subp_id, $puser_id , $vuser_name , $puser_name, $create_vuser = false, $vuser = null)
	{		
		$puser_vuser = self::retrieveByPartnerAndUid ( $partner_id , $subp_id, $puser_id , true );
		if (!$vuser) {
			$vuser = vuserPeer::getVuserByPartnerAndUid($partner_id, $puser_id, true); // don't create an existing vuser!
		}
		
		if ( $puser_vuser )
		{
			if ( !$create_vuser )
			{
				// if the puser_vuser already exists - don't re-create it
				$puser_vuser->exists = true;
				return $puser_vuser;
			}
			else
			{
				// puser_vuser exists but it's OK
				// this might be the case where we don't mind creating a new one each time
			}
		}
		else
		{
			$puser_vuser = new PuserVuser();
		}
		
		$c = new Criteria();
		$c->add ( self::PARTNER_ID , $partner_id );
		$c->add ( self::PUSER_ID , $puser_id );
		$partner_puser_vuser = self::doSelectOne( $c );
		
		if ($vuser !== null)
		{
			$vuser_id = $vuser->getId();
		}
		else
		{
			if ($partner_puser_vuser)
			{
				$vuser_id = $partner_puser_vuser->getVuserId();
				$vuser = vuserPeer::retrieveByPK($vuser_id);
			}
			else
			{
				// create vuser for this puser
				$vuser = new vuser ();
				$vuser->setScreenName( $vuser_name );
				
				list($firstName, $lastName) = vString::nameSplit($vuser_name);
				$vuser->setFirstName($firstName);
				$vuser->setLastName($lastName);

				$vuser->setPartnerId( $partner_id );
				// set puserId for forward compatibility with PS3
				$vuser->setPuserId( $puser_id );
				$vuser->setStatus( VuserStatus::ACTIVE ); // so he won't appear in the search
				$vuser->save();
				$vuser_id = $vuser->getId();
			}
		}
		
		$puser_vuser->setPartnerId( $partner_id );
		$puser_vuser->setSubpId( $subp_id );
		$puser_vuser->setPuserId( $puser_id );
		$puser_vuser->setVuserId( $vuser_id );
		$puser_vuser->setPuserName($puser_name );
		$puser_vuser->save();
		$puser_vuser->setvuser( $vuser );
		
		return $puser_vuser;
	}
	
	// depending on return_type :
	// 0 - return puser_vuser
	// 1 - return puser_id 
	public static function getByVuserId ( $vuser_id , $return_type = 0 )
	{
		$c = new Criteria();
		$c->add ( self::VUSER_ID , $vuser_id );
		$puser_vuser = self::doSelectOne( $c );
		if ( $return_type == 0 )		return  $puser_vuser;
		if ( $return_type == 1 )
		{
			if ( $puser_vuser )
			{
				return $puser_vuser->getPuserId();		
			}
			return null;
		}
	}
	
	public static  function removeFromCache ( $object )
	{
		$cache = new myObjectCache ( );
		$key = $object->getPartnerId() ."|" . $object->getVuserId();
		$puser_id = $cache->remove ( "puser_vuser_id" , $key );
	}
	
	public static  function getVuserIdFromPuserId ( $partner_id , $puser_id )
	{
		$cache = new myObjectCache ( );
		$key = $partner_id ."|" . $puser_id;
		$vuser_id = $cache->get ( "vuser_puser_id" , $key );
		if($vuser_id)
			return $vuser_id;
		
		$c = new Criteria();
		$c->add ( self::PARTNER_ID , $partner_id );
		$c->add ( self::PUSER_ID , $puser_id );
		$puser_vusers = self::doSelect( $c );
		if(!count($puser_vusers))
			return null;

		$puser_vuser = reset($puser_vusers);
		$vuser_id = $puser_vuser->getVuserId();
		$cache->putValue ( "vuser_puser_id" , $key , null , $vuser_id );
		return $vuser_id;
	}
	
	public static  function getVuserIdFromPuserIds ( $partner_id , array $puser_ids )
	{
		$vuser_ids = array();
		foreach($puser_ids as $puser_id)
			$vuser_ids[] = self::getVuserIdFromPuserId($partner_id, $puser_id);
			
		return $vuser_ids;	
	}
	
	public static  function getPuserIdFromVuserId ( $partner_id , $vuser_id )
	{
		$cache = new myObjectCache ( );
		$key = $partner_id ."|" . $vuser_id;
		$puser_id = $cache->get ( "puser_vuser_id" , $key );

		if ( $puser_id == null )
		{
			$c = new Criteria();
			$c->add ( self::PARTNER_ID , $partner_id );
			$c->add ( self::VUSER_ID , $vuser_id );
			$puser_vusers = self::doSelect( $c );
	
			if ( count ( $puser_vusers ) > 0 )
			{
				$puser_vuser = $puser_vusers[0];
				$puser_id = $puser_vuser->getPuserId();
			}
			else
			{
				$puser_vuser = null;
				$puser_id = "null"; // set the string null so this will be set in the cache
			}

			$cache->putValue ( "puser_vuser_id" , $key , null , $puser_id );
		}
		
		if ( $puser_id == "null" ) return null; // return the null object not the "null" string
		return $puser_id;
	}
	
	public static  function getPuserIdFromVuserIds ( $partner_id , array $vuser_ids )
	{
		if( $vuser_ids == null || count($vuser_ids))
		{
			return array();
		}
		$c = new Criteria();
		$c->add ( self::PARTNER_ID , $partner_id );
		$c->add ( self::VUSER_ID , $vuser_ids , Criteria::IN );
		$puser_vusers = self::doSelect( $c );
		return $puser_vusers;
	}	
}
