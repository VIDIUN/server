<?php
require_once ( __DIR__ . "/vidiunWebserviceRenderer.class.php" );
/**
 * This class will make common tasks in the action classes much easier.
 *
 */
abstract class vidiunBaseWebserviceAction extends vidiunAction
{
	protected static $escape_text = false;
	
	protected $response_type = vidiunWebserviceRenderer::RESPONSE_TYPE_XML;
	
	protected function renderDataInRequestedFormat( $response_params , $return_value = false )
	{
		$renderer = new vidiunWebserviceRenderer( $this );
		list ( $response , $content_type ) = $renderer->renderDataInRequestedFormat( $response_params , $this->response_type,  self::$escape_text );

		$this->getResponse()->setHttpHeader ( "Content-Type"  , $content_type  );
		
		if ( $return_value )
		{
			return $response ;
		}
		else
		{
			return $this->renderText( $response ) ;
		}
	}
	
}



?>