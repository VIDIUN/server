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
class viewLogsAction extends vidiunSystemAction
{
	const LOG_DIR = "/web/logs/";
	/**
	 * Gives a system applicative snapsot
	 */
	public function execute()
	{
		$this->forceSystemAuthentication();
		
		$kbytes = $this->getRequestParameter( "size" , 1 );
		
		if ( $kbytes > 100 ) $kbytes = 100;
		
		$bytes = $kbytes * 1024;
		
		$this->batch_client = self::readLog( "batchConvertClient.log" , $bytes );
		$this->batch_server = self::readLog( "batchConvertServer.log" , $bytes );
		$this->batch_import= self::readLog( "batchImportServer.log" , $bytes );		
		$this->batch_email = self::readLog( "batchEmailServer.log" , $bytes );
	}
	
	private static function readLog ( $name , $size )
	{
		$pattern = "/" . $name . "$/";
		$files = vFile::recursiveDirList( self::LOG_DIR , true , false , $pattern );
		
		$result = array();
		if ( $files )
		{
			foreach ( $files as $file )
			{
				$data = vFile::getFileData( $file );
				$data->content = vFile::readLastBytesFromFile ( $file,  $size );
				$result[] = $data;
			}
		}
		
		return $result;
	}
}
?>