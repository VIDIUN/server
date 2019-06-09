<?php
/**
 * @package    Core
 * @subpackage vEditorServices
 */
class defVeditorservicesAction extends vidiunAction
{
	//	protected $vshow_id;
	//	protected $vshow;
	// the objects bellow are actually the user's session 
	protected $partner_id;
	protected $subp_id;
	protected $vs_str;
	protected $uid; 
	
	protected function fetchVshow()
	{
		return true;
	}
	/**
	 * This function will be implemented in eash of the derived convrete classes which represent a service
	 * for Veditor.
	 * To simplifu work - it will be passed the $this->vshow which will never be null.
	 */
/*
	abstract protected function executeImpl( $vshow ); 

	abstract protected function noSuchVshow ( $vshow_id );
	*/
		
	public function execute()
	{
		//$this->debug = @$_REQUEST["debug"];
		$this->debug = false;

		$entry_id = @$_REQUEST["entry_id"];
		if ( $entry_id == NULL || !$entry_id || $entry_id < 0 )
		{
			$vshow_id = @$_REQUEST["vshow_id"];
			if ($vshow_id)
			{
				$vshow = vshowPeer::retrieveByPK( $vshow_id );
				if ( ! $vshow ) return; // request for non-existing vshow_id
				$entry_id = $vshow->getShowEntryId();
			}
		}
		
		if ( $entry_id == NULL || !$entry_id || $entry_id < 0 )
			return;
		
		$this->partner_id = $this->getRequestParameter( "partner_id" ); 
		$this->subp_id = $this->getRequestParameter( "subp_id" );
		$this->vs_str = $this->getRequestParameter( "vs" );
		$this->uid = $this->getRequestParameter( "uid" );
		
		$this->entry_id = $entry_id;
		$entry = entryPeer::retrieveByPK($entry_id);
		
		if ( $entry == NULL )
		{
			$this->noSuchEntry( $entry_id );
			return;
		}
		
		if ( $this->fetchVshow() )
		{
			$vshow_id = $entry->getVshowId();
			
			//$vshow_id = @$_REQUEST["vshow_id"];
			$this->vshow_id = $vshow_id;
	
			if ( $vshow_id == NULL || !$vshow_id ) return;
	
			$vshow = vshowPeer::retrieveByPK( $vshow_id );
	// TODO - PRIVILEGES
	/*		$user_ok = $this->forceEditPermissions( $vshow , $vshow_id , false);
			
			if ( ! $user_ok )
			{
				return $this->securityViolation( $vshow_id ); 
			}
	*/
			if ( $vshow == NULL )
			{
				$this->noSuchVshow ( $vshow_id );
				return;
			}
		}
		else
		{
			
			$vshow = new vshow();
			$vshow_id = $entry->getVshowId();
			$this->vshow_id = $vshow_id;
		}
		
		// TODO
		// validate editor has proper privileges !
		//$this->forceAuthentication();

		$this->entry = $entry;
		$this->vshow = $vshow;
		$duration = 0;
		
//		$this->logMessage ( __CLASS__ . " 888 $vshow_id"  , "err");
		
		$result = $this->executeImpl( $this->vshow, $this->entry );
		
		if ( $result != NULL )
		{
			$this->getResponse()->setHttpHeader ( "Content-Type" , $result );
		}
		else
		{
			$this->getResponse()->setHttpHeader ( "Content-Type" , "text/xml; charset=utf-8" );
		}
		
		$this->getController()->setRenderMode ( sfView::RENDER_CLIENT );
	}
	
	protected function executeImpl( vshow $vshow, entry &$entry)
	{
		return "text/html; charset=utf-8";
	}

	protected function noSuchEntry ( $entry_id )
	{
		$this->xml_content = "No such entry [$entry_id]";
	}
	
	protected function noSuchVshow ( $vshow_id )
	{
		$this->xml_content = "No such show [$vshow_id]";
	}
	
	
	protected function  securityViolation( $vshow_id )
	{
		$xml = "<xml><vshow id=\"$vshow_id\" securityViolation=\"true\"/></xml>";
		$this->getResponse()->setHttpHeader ( "Content-Type" , "text/xml; charset=utf-8" );
		$this->getController()->setRenderMode ( sfView::RENDER_NONE );
		return $this->renderText( $xml );
	}
	
	
	/**
	 * Supports backward compatibility
	 * returns all vusers of the puser
	 */
	protected function getLoggedInUserIds ( )
	{
		$ret = array($this->getLoggedInPuserId());
		
		$c = new Criteria();
		$c->add(vuserPeer::PUSER_ID, $this->uid);
		$vusers = vuserPeer::doSelect($c);
		
		foreach($vusers as $vuser)
			$ret[] = $vuser->getId();
			
		return $ret;
	}
	
	protected function getLoggedInUserId ( )
	{
		if ( $this->partner_id )
		{
			// this part overhere should be in a more generic place - part of the services
			$vs = "";
			// TODO - for now ignore the session
			$valid = true; // ( 0 >= vSessionUtils::validateVSession ( $this->partner_id , $this->uid , $this->vs_str ,&$vs ) );
			if ( $valid )
			{
				$puser_id = $this->uid;
				// actually the better user indicator will be placed in the vs - TODO - use it !! 
				// $puser_id = $vs->user; 
				
				$vuser_name = $puser_name = $this->getP ( "user_name" );
				if ( ! $puser_name )
				{
					$vuser_name = myPartnerUtils::getPrefix( $this->partner_id ) . $puser_id;
				}
				// will return the existing one if any, will create is none
				$puser_vuser = PuserVuserPeer::createPuserVuser ( $this->partner_id , $this->subp_id, $puser_id , $vuser_name , $puser_name, false  );
				$livuser_id = $puser_vuser->getVuserId(); // from now on  - this will be considered the logged in user
				return $livuser_id;
			}

		}
		else
		{	
			return parent::getLoggedInUserId();
		}
	}
	
	protected function 	allowMultipleRoughcuts ( )
	{	
		$this->logMessage( "allowMultipleRoughcuts: [" . $this->partner_id . "]");
		if ( $this->partner_id == null ) return true;
		else
		{
			// this part overhere should be in a more generic place - part of the services
			$multiple_roghcuts = Partner::allowMultipleRoughcuts( $this->partner_id );
			return $multiple_roghcuts;
		}
	}		
}


?>