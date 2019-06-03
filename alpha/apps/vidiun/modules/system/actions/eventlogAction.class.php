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
class eventlogAction extends vidiunSystemAction
{
	public function execute()
	{
		$this->forceSystemAuthentication();
		$secret = "";
		$this->lines_str = $this->getP ( "lines" );
		$this->line_sep = $this->getP ( "line_sep" , "n" );

		$this->type = $this->getP ( "type" , "vdp" );
		
		$line_sep_val = $this->line_sep == "rn" ? "\r\n" : "\n"; 
		
		$this->lines = explode ( $line_sep_val , $this->lines_str ) ;
		
	}
	
	private static function formatThisData ( $time )
	{
		return strftime( "%d/%m %H:%M:%S" , $time );	
	}
}
?>