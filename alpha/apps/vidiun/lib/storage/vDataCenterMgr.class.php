<?php
/**
 * @package Core
 * @subpackage storage
 */
class vDataCenterMgr
{
	private static $s_current_dc;
	private static $is_multi_dc = null;
	const REMOTE_FILE_GET_CONTENTS_TIMEOUT = 60;


	/**
	 * @var StorageProfile
	 */
	private static $currentStorageProfile = null;
	
	/**
	 * @return StorageProfile
	 */
	public static function getCurrentStorageProfile()
	{
		if(self::$currentStorageProfile)
			return self::$currentStorageProfile;
			
		self::$currentStorageProfile = StorageProfilePeer::retrieveByPK(self::getCurrentDcId());
		return self::$currentStorageProfile;
	}
	
	// TODO - remove ! this is ony a way to test multiple datacenters on the same machine
	public static function setCurrentDc( $current_dc)
	{
		self::$s_current_dc = $current_dc;
	}
	
	/**
	 * @return int the configured id of current data center
	 */
	public static function getCurrentDcId () 
	{
		$dc = self::getCurrentDc();
		return $dc["id"];
	}
		
	public static function getCurrentDcUrl () 
	{
		$dc = self::getCurrentDc();
		return $dc["url"];
	}
		
	public static function getCurrentDcDomain () 
	{
		$dc = self::getCurrentDc();
		return $dc["domain"];
	}

	public static function getCurrentDcName ()
	{
		$dc = self::getCurrentDc();
		return $dc["name"];
	}

	public static function isMultiDc()
	{
		if (is_null(self::$is_multi_dc))
		{
			$ids = self::getDcIds();
			self::$is_multi_dc = count($ids) > 1;
		}
		return self::$is_multi_dc;
	}
	
	public static function getCurrentDc () 
	{
		$dc_config = vConf::getMap("dc_config");
		// find the current
		if ( self::$s_current_dc )
			return self::getDcById( self::$s_current_dc );
		return self::getDcById( $dc_config["current"] );
	}

	// returns a tupple with the id and the DC's properties
	public static function getDcById ( $dc_id ) 
	{
		$dc_config = vConf::getMap("dc_config");
		// find the dc with the desired id
		$dc_list = $dc_config["list"];
		if ( isset( $dc_list[$dc_id] ) )		
			$dc = $dc_list[$dc_id];
		else
			throw new Exception ( "Cannot find DC with id [$dc_id]" );
		
		$dc["id"]=$dc_id;
		return $dc;
//		return array ( $dc_id , $dc );
	}

	public static function getDcIds()
	{
		$dc_config = vConf::getMap("dc_config");
		$dc_list = $dc_config["list"];
		return array_keys($dc_list);
	}
		
	public static function getAllDcs( $include_current = false )
	{
		$dc_config = vConf::getMap("dc_config");
		
		$dc_list = $dc_config["list"];
		
		if ( $include_current == false )
		{
			unset ( $dc_list[$dc_config["current"]]);
		}
		
		$fixed_list = array();
		foreach ( $dc_list as $dc_id => $dc_props  )
		{
			$dc_props["id"]=$dc_id;
			$fixed_list[] = $dc_props;
		}
		return $fixed_list;
	}
	
	public static function getRemoteDcExternalUrl ( FileSync $file_sync )
	{
		VidiunLog::log("File Sync [{$file_sync->getId()}]");
		$dc_id = $file_sync->getDc();		
		$dc = self::getDcById ( $dc_id );
		$url = $dc["url"];
		return $url;
	}

	public static function getRemoteDcExternalUrlByDcId ( $dc_id )
	{
		VidiunLog::log("DC id [{$dc_id}]");
		$dc = self::getDcById ( $dc_id );
		$url = $dc["url"];
		return $url;
	}
	
	public static function getRedirectExternalUrl ( FileSync $file_sync , $additional_url = null )
	{
		$remote_url = self::getRemoteDcExternalUrl ( $file_sync );
		$remote_url =  $remote_url . $_SERVER['REQUEST_URI'];
		$remote_url = preg_replace('/^https?:\/\//', '', $remote_url);
		$remote_url = infraRequestUtils::getProtocol() . '://' . $remote_url;
		
		VidiunLog::log ("URL to redirect to [$remote_url]" );
		
		return $remote_url;
	}
	
	public static function createCmdForRemoteDataCenter(FileSync $fileSync)
	{
		VidiunLog::log("File Sync [{$fileSync->getId()}]");
		$remoteUrl = self::getInternalRemoteUrl($fileSync);
		$locaFilePath = self::getLocalTempPathForFileSync($fileSync);
		$timeOut = vConf::getArrayValue("remote_file_get_contents_timeout", "params", "dc_config", self::REMOTE_FILE_GET_CONTENTS_TIMEOUT);
		$cmdLine = vConf::get( "bin_path_curl" ) . ' -m ' . $timeOut . ' -f -s -L -o"' . $locaFilePath . '" "' . $remoteUrl . '"';
		return $cmdLine;
	}
	
	public static function getLocalTempPathForFileSync(FileSync $fileSync) 
	{
		return DIRECTORY_SEPARATOR . "tmp" . DIRECTORY_SEPARATOR . "file_sync-" .  $fileSync->getId();
	}
	
	public static function getInternalRemoteUrl(FileSync $file_sync)
	{
		VidiunLog::log("File Sync [{$file_sync->getId()}]");
		// LOG retrieval

		$dc =  self::getDcById ( $file_sync->getDc() );
		
		$file_sync_id = $file_sync->getId();
		$file_hash = md5( $dc["secret" ] .  $file_sync_id );	// will be verified on the other side to make sure not some attack or external invalid request  
		
		$filename = 'f.' . $file_sync->getFileExt();
		
		$build_remote_url = $dc["url"] . "/index.php/extwidget/servefile/id/$file_sync_id/hash/$file_hash/f/$filename"; // or something similar 
		
		return $build_remote_url;
	}
		
	/**
	 * Will fetch the content of the $file_sync.
	 * If the $local_file_path is specifid, will place the cotnet there
	 * @param FileSync $file_sync
	 * @return string
	 */
	public static function retrieveFileFromRemoteDataCenter ( FileSync $file_sync )
	{
		VidiunLog::log("File sync [{$file_sync->getId()}]");
		// LOG retrieval

		$cmd_line = self::createCmdForRemoteDataCenter($file_sync);
		$local_file_path = self::getLocalTempPathForFileSync($file_sync);
		
		if (!file_exists($local_file_path)) // don't need to fetch twice 
		{ 
			VidiunLog::log("Executing " . $cmd_line);
			exec($cmd_line);
			
			clearstatcache();
			if (!file_exists($local_file_path))
			{
				VidiunLog::err("Temp file not retrieved [$local_file_path]");
				return false;
			}
		}
		else {
			VidiunLog::log("Already exists in temp folder [{$local_file_path}]");
		}

		return vFile::getFileContent($local_file_path);
	}

	/*
	 * will handle the serving of the file assuming a remote DC (other than the current) requested it
	 */
	public static function serveFileToRemoteDataCenter ( $file_sync , $file_hash, $file_name )
	{
		$file_sync_id = $file_sync->getId();
		
		VidiunLog::log("File sync id [$file_sync_id], file_hash [$file_hash], file_name [$file_name]");
		// TODO - verify security
		
		$current_dc = self::getCurrentDc();
		$current_dc_id = $current_dc["id"];

		if ( $file_sync->getDc() != $current_dc_id )
		{
			$error = "DC[$current_dc_id]: FileSync with id [$file_sync_id] does not belong to this DC";
			VidiunLog::err($error); 
			VExternalErrors::dieError(VExternalErrors::BAD_QUERY);
		}
		
		// resolve if file_sync is link
		$file_sync_resolved = $file_sync;
		
		$file_sync_resolved = vFileSyncUtils::resolve($file_sync);
		
		// check if file sync path leads to a file or a directory
		$resolvedPath = $file_sync_resolved->getFullPath();
		$fileSyncIsDir = is_dir($resolvedPath);
		if ($fileSyncIsDir && $file_name) {
			$resolvedPath .= '/'.$file_name;
		}
		
		if (!file_exists($resolvedPath))
		{
			$file_name_msg = $file_name ? "file name [$file_name] " : '';
			$error = "DC[$current_dc_id]: Path for fileSync id [$file_sync_id] ".$file_name_msg."does not exist, resolved path [$resolvedPath]";
			VidiunLog::err($error); 
			VExternalErrors::dieError(VExternalErrors::FILE_NOT_FOUND);
		}
		
		// validate the hash
		$expected_file_hash = md5( $current_dc["secret" ] .  $file_sync_id );	// will be verified on the other side to make sure not some attack or external invalid request
		if ( $file_hash != $expected_file_hash )  
		{
			$error = "DC[$current_dc_id]: FileSync with id [$file_sync_id] - invalid hash";
			VidiunLog::err($error); 
			VExternalErrors::dieError(VExternalErrors::INVALID_TOKEN);
		}
				
		if ($fileSyncIsDir && is_dir($resolvedPath))
		{
			VidiunLog::log("Serving directory content from [".$resolvedPath."]");
			$contents = vFile::listDir($resolvedPath);
			usort($contents, array("vDataCenterMgr", "sortListDirFiles"));
			$contents = serialize($contents);
			header("file-sync-type: dir");
			echo $contents;
			VExternalErrors::dieGracefully();
		}
		else
		{
			VidiunLog::log("Serving file from [".$resolvedPath."]");
			//This case handles file sync that links to file but its type is directory
			$fileSize = $file_sync_resolved->getIsDir() ? null : $file_sync_resolved->getFileSize();
			vFileUtils::dumpFile( $resolvedPath , null, null, 0 ,$file_sync_resolved->getEncryptionKey(), $file_sync_resolved->getIv(),$fileSize);
		}
		
	}
	
	/**
	 * Custom function to sort result of vFile::listDir based on fileName
	 * Example output of listDir:
	 * Array ( [0] => Array ( [0] => Slide_0_6b7fixqe_0_igzhilpq_2_0001.jpg [1] => file [2] => 26087 )
	 */
	private static function sortListDirFiles($a, $b)
	{
		return strnatcasecmp($a[0], $b[0]);
	}
	
	/**
	 * return the DC index from the objectId. (for example: for objectId='1_7hdf78fn' the function will return '1') 
	 * for old objects without a dc prefix return null or the current dc id according to the $useCurrentDcAsDefault parameter
	 * @param string $objectId
	 * @param boolean $useCurrentDcAsDefault
	 */
	public static function getDCByObjectId($objectId, $useCurrentDcAsDefault = false){
		$objectIdDc = explode('_', $objectId);
		$dcId = $objectIdDc[0];
		if (!in_array($dcId, self::getDcIds())) {
			$dcId = $useCurrentDcAsDefault ? self::getCurrentDcId() : null;
		}
		
		return $dcId;
	}
	
	/**
	 * @param int $dcId
	 * @return bool true/false
	 */
	public static function dcExists($dcId)
	{
		$tempDc = null;
		try { 
			$tempDc = self::getDcById($dcId);
		}
		catch (Exception $e) {
			$tempDc = null;
		}
		return !is_null($tempDc);
	}
	
	public static function incrementVersion($version = 0) 
	{
		return (ceil(intval($version) / 10) * 10) + 2 - self::getCurrentDcId();		
	}
}
