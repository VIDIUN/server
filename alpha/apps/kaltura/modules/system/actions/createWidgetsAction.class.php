<?php
/**
 * @package    Core
 * @subpackage system
 * @deprecated
 */
require_once ( __DIR__ . "/vidiunSystemAction.class.php" );

/**
 * @package    Core
 * @subpackage system
 * @deprecated
 */
class createWidgetsAction extends vidiunSystemAction
{
	
	/**
	 * 
	 */
	public function execute()
	{
		$this->forceSystemAuthentication();
		
		$vshow_ids = $this->getP ( "vshow_ids") ;
		$partner_id = $this->getP ( "partner_id") ;
//		$subp_id = $this->getP ( "subp_id") ;
		$source_widget_id= $this->getP ( "source_widget_id" , 201 ) ;
		$submitted = $this->getP ( "submitted");
		$method = $this->getP ( "method" , "partner" );
		$create = $this->getP ( "create" );
		$limit = $this->getP ( "limit" , 20 );
		if ( $limit > 300 ) $limit = 300;
		
		$this->vshow_ids = $vshow_ids;
		$this->partner_id = $partner_id;
//		$this->subp_id = $subp_id;
		$this->source_widget_id = $source_widget_id;
		$this->method = $method;
		$this->create = $create;
		$this->limit = $limit;
		
		$errors = array( );
		$res = array();
		$this->errors = $errors;
		
		if ( $submitted )
		{
			// fetch all vshows that don't have widgets
			$c = new Criteria();
			$c->setLimit ( $limit );
			if ( $method == "list" )
			{
				$c->add ( vshowPeer::ID , @explode ( "," , $vshow_ids ) , Criteria::IN );				
			}
			else
			{
				$c->add ( vshowPeer::PARTNER_ID , $partner_id );
				if ( $create )
				{
					// because we want to create - select those vshows that are not marked as "have widgets"
					$c->add ( vshowPeer::INDEXED_CUSTOM_DATA_3 , NULL , Criteria::EQUAL );
				}
			}
			$c->addAscendingOrderByColumn( vshowPeer::CREATED_AT );
			// start at a specific int_id
			// TODO
			$vshows = vshowPeer::doSelect( $c );
			$vshow_id_list = $this->getIdList ( $vshows , $partner_id , $errors );
			
			$fixed_vshows = array();
			
//			$res [] = print_r ( $vshow_id_list ,true );
			$this->res = $res;			//return;
			$this->errors = $errors;
			
			if ( $vshow_id_list )
			{
			//	$vshow_id_list_copy = array_  $vshow_id_list ;
				$widget_c = new Criteria();
				$widget_c->add ( widgetPeer::PARTNER_ID , $partner_id );
				$widget_c->add ( widgetPeer::VSHOW_ID , $vshow_id_list , Criteria::IN );
				$widgets = widgetPeer::doSelect( $widget_c );
				
				// - IMPORTANT - add the vshow->setIndexedCustomData3 ( $widget_id ) for wikis

				
				foreach ( $widgets as $widget )
				{
					$vshow_id = $widget->getVshowId();
					if ( in_array ( $vshow_id, $fixed_vshows ) ) continue;
					// mark the vshow as one that has a widget
					$vshow = $this->getVshow ( $vshows , $vshow_id );
					$vshow->setIndexedCustomData3( $widget->getId());
					$vshow->save();
					unset ( $vshow_id_list[$vshow_id]);
					$fixed_vshows[$vshow_id]=$vshow_id;
//					print_r ( $vshow_id_list );
				}

			// create widgets for those who are still on the list === don't have a widget				
				foreach ( $vshow_id_list as $vshow_id )
				{
					if ( in_array ( $vshow_id, $fixed_vshows ) ) continue;
					$vshow = $this->getVshow ( $vshows , $vshow_id );
					$widget = widget::createWidget( $vshow , null , $source_widget_id ,null);
					$vshow->setIndexedCustomData3( $widget->getId());
					$vshow->save();
					$fixed_vshows[$vshow_id]=$vshow_id;
				}
			
			}
			
					
			// create a log file of the vidiun-widget tagss for wiki
			$partner = PartnerPeer::retrieveByPK( $partner_id );
			if  ( $partner )
			{
				$secret = $partner->getSecret ();	
				foreach ( $vshows as $vshow )
				{
					$vshow_id = $vshow->getId();
					$article_name = "Video $vshow_id";
					$widget_id = $vshow->getIndexedCustomData3(); // by now this vshow should have the widget id 
					$subp_id = $vshow->getSubpId();
					$md5 = md5 ( $vshow_id  . $partner_id  .$subp_id . $article_name . $widget_id .  $secret );
					$hash = substr ( $md5 , 1 , 10 );
					$values = array ( $vshow_id , $partner_id , $subp_id , $article_name ,$widget_id , $hash);
					
					$str = implode ( "|" , $values);
					$base64_str = base64_encode( $str );
					
					$res [] = "vidiunid='$vshow_id'	vwid='$base64_str'	'$str'\n";
				}
			}
		}
		
		$this->res = $res;
	}
	
	
	private function getIdList ( $objs , $partner_id , &$errors )
	{
		if ( is_array ( $objs  ))
		{
			$id = array();
			foreach ( $objs as $obj )
			{
				if ( $partner_id == $obj->getPartnerId() )
				{
					$id[] = $obj->getId();
				}
				else
				{
					$errors[] = $obj->getId() . " is of partner " . $obj->getPartnerId() . " instead of $partner_id";
				}
			}
			return $id;
		}
		return null;
	}
	
	private function getVshow ( $vshows , $vshow_id )
	{
		foreach ( $vshows as $vshow )
		{
			if( $vshow_id == $vshow->getId() ) return $vshow;
		}
		return null;
	}
}

 
?>