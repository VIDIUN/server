<?php
/**
 * @package    Core
 * @subpackage externalServices
 */
class plymediaAction extends vidiunAction
{
	public function execute()
	{
		$hdr = "text/xml; charset=utf-8";
		$this->response->setHttpHeader ( "Content-Type" ,  $hdr );
	
		$movie = $this->getP ( "movie" );
		if ( $movie ) 
		{
			//@list ( $obj_type , $id ) = explode ( "_" , $movie );
			// Gonen 28/03/2010: changed code to support both Andromeda style entry ID (0_XXX....) and old style entry ID (chars only)
			$obj_type = @substr($movie, 0, strpos($movie, '_'));
			$id = @str_replace($obj_type.'_', '', $movie);
			
			if ( $obj_type == "entry" )
			{
				return $this->renderText( self::renderEntry ( $id ) );
			}
			else
			{
				$vshow = vshowPeer::retrieveByPK( $id );
				if ( $vshow )
				{
					return $this->renderText( self::renderEntry ( $vshow->getShowEntryId() ) );
				}
			}
		}

		return $this->renderText("OK");
	}
	
	private static function renderEntry ( $entry_id )
	{
		$xml = '<?xml version="1.0" encoding="ISO-8859-1"?>';
		$xml .= "<Video>";
		$entry = entryPeer::retrieveByPK( $entry_id );
		if ( $entry ) 
		{
			$seconds = (int)($entry->getLengthInMsecs()/1000);
			$xml .= "<PartnerId>" . $entry->getPartnerId() . "</PartnerId>" .
				"<Duration>" . $seconds . "</Duration>";
		}
		$xml .= "</Video>";


		return $xml;
	}
}
