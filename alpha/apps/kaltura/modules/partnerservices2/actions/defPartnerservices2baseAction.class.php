<?php
/**
 * @package api
 * @subpackage ps2
 */
class defPartnerservices2baseAction extends vidiunAction
{
	protected static $_useCache = true;	

	protected static $allowedActions = array(
		'addbulkuploadAction.class.php',
		'addconversionprofileAction.class.php',
		'adddataentryAction.class.php',
		'adddownloadAction.class.php',
		'adddvdentryAction.class.php',
		'adddvdjobAction.class.php',
		'addentryAction.class.php',
		'addentrybaseAction.class.php',
		'addvshowAction.class.php',
		'addmoderationAction.class.php',
		'addpartnerentryAction.class.php',
		'addplaylistAction.class.php',
		'addroughcutentryAction.class.php',
		'adduiconfAction.class.php',
		'adduserAction.class.php',
		'addwidgetAction.class.php',
		'adminloginAction.class.php',
		'appendentrytoroughcutAction.class.php',
		'clonevshowAction.class.php',
		'cloneroughcutAction.class.php',
		'cloneuiconfAction.class.php',
		'collectstatsAction.class.php',
		'contactsalesforceAction.class.php',
		'deletedataentryAction.class.php',
		'deleteentryAction.class.php',
		'deletevshowAction.class.php',
		'deleteplaylistAction.class.php',
		'deleteuiconfAction.class.php',
		'deleteuserAction.class.php',
		'executeplaylistAction.class.php',
		'executeplaylistfromcontentAction.class.php',
		'generatewidgetAction.class.php',
		'getadmintagsAction.class.php',
		'getallentriesAction.class.php',
		'getdataentryAction.class.php',
		'getdefaultwidgetAction.class.php',
		'getdvdentryAction.class.php',
		'getentriesAction.class.php',
		'getentryAction.class.php',
		'getentryroughcutsAction.class.php',
		'getfilehashAction.class.php',
		'getvshowAction.class.php',
		'getlastversionsinfoAction.class.php',
		'getmetadataAction.class.php',
		'getpartnerAction.class.php',
		'getpartnerinfoAction.class.php',
		'getpartnerusageAction.class.php',
		'getplaylistAction.class.php',
		'getplayliststatsfromcontentAction.class.php',
		'getroughcutAction.class.php',
		'getthumbnailAction.class.php',
		'getuiconfAction.class.php',
		'getuserAction.class.php',
		'getwidgetAction.class.php',
		'handlemoderationAction.class.php',
		'indexAction.class.php',
		'listbulkuploadsAction.class.php',
		'listconversionprofilesAction.class.php',
		'listdataentriesAction.class.php',
		'listdownloadsAction.class.php',
		'listdvdentriesAction.class.php',
		'listentriesAction.class.php',
		'listvshowsAction.class.php',
		'listmoderationsAction.class.php',
		'listmydvdentriesAction.class.php',
		'listmyentriesAction.class.php',
		'listmyvshowsAction.class.php',
		'listpartnerentriesAction.class.php',
		'listpartnerpackagesAction.class.php',
		'listplaylistsAction.class.php',
		'listuiconfsAction.class.php',
		'listusersAction.class.php',
		'mrssAction.class.php',
		'objdetailsAction.class.php',
		'pingAction.class.php',
		'queuependingbatchjobAction.class.php',
		'rankvshowAction.class.php',
		'registerpartnerAction.class.php',
		'reportentryAction.class.php',
		'reporterrorAction.class.php',
		'reportvshowAction.class.php',
		'reportuserAction.class.php',
		'resetadminpasswordAction.class.php',
		'rollbackvshowAction.class.php',
		'searchAction.class.php',
		'searchauthdataAction.class.php',
		'searchfromurlAction.class.php',
		'searchmediainfoAction.class.php',
		'searchmediaprovidersAction.class.php',
		'setmetadataAction.class.php',
		'startsessionAction.class.php',
		'startwidgetsessionAction.class.php',
		'testmeAction.class.php',
		'testnotificationAction.class.php',
		'updateadminpasswordAction.class.php',
		'updatebatchjobAction.class.php',
		'updatedataentryAction.class.php',
		'updatedvdentryAction.class.php',
		'updateentriesthumbnailsAction.class.php',
		'updateentryAction.class.php',
		'updateentrymoderationAction.class.php',
		'updateentrythumbnailAction.class.php',
		'updateentrythumbnailjpegAction.class.php',
		'updatevshowAction.class.php',
		'updatevshowownerAction.class.php',
		'updatepartnerAction.class.php',
		'updateplaylistAction.class.php',
		'updateuiconfAction.class.php',
		'updateuserAction.class.php',
		'updateuseridAction.class.php',
		'uploadAction.class.php',
		'uploadjpegAction.class.php',
	);

	public static function disableCache()
	{
		self::$_useCache = false;
	}
	
	public function execute()
	{
		// can't read using $_REQUEST because the 'myaction' paramter is created in a routing.yml rule
		$service_name = $this->getRequestParameter( "myaction" );

		// remove all '_' and set to lowercase
		$myaction_name = trim( strtolower( str_replace ( "_" , "" , $service_name ) ) );
		$clazz_name = $myaction_name . "Action";
//		echo "[$myaction_name] [$clazz_name]<br>";

//		$clazz = get_class ( $clazz_name );

		//$multi_request = $this->getRequestParameter( "multirequest" , null );
		$multi_request = $myaction_name ==  "multirequest" ;
		if ( $multi_request  )
		{
			$multi_request = new myMultiRequest ( $_REQUEST, $this );
			$response = $multi_request->execute();
		}
		else
		{
			$include_result = null;
			$fileName = "{$clazz_name}.class.php";
			if(in_array($fileName, self::$allowedActions))
				$include_result = @include_once ($fileName);

			if ( $include_result )
			{
				$myaction = new $clazz_name( $this );
				$myaction->setInputParams ( $_REQUEST );
				$response = $myaction->execute( );
				vEventsManager::flushEvents();
			}
			else
			{
				$format = $this->getP ( "format" );
				$response = "Error: Invalid service [".htmlentities($service_name)."]";
			}
		}

		$format = $this->getP ( "format" );
		if ( $format == vidiunWebserviceRenderer::RESPONSE_TYPE_PHP_ARRAY || $format == vidiunWebserviceRenderer::RESPONSE_TYPE_PHP_OBJECT )
		{
			//$this->setHttpHeader ( "Content-Type" , "text/html; charset=utf-8" );
			$response = "<pre>" . print_r ( $response , true ) . "</pre>" ;
		}

		// uncomment in order to cache api responses
		if(vConf::get('enable_cache'))
		{
			$this->cacheResponse($response);
		}

		
        $ret = $this->renderText( $response );
        VExternalErrors::terminateDispatch();
        return $ret;
	}

	protected function shouldCacheResonse()
	{
		return self::$_useCache;	 
	}
	
	public function cacheResponse($response)
	{
		if (!$this->shouldCacheResonse() )
		{
			return;	
		}
		$isStartSession = (@$params['service'] == 'startsession' || strpos($_SERVER['PATH_INFO'],'startsession'));		

		$params = $_GET + $_POST;
		
		$vs = isset($params['vs']) ? $params['vs'] : '';
		if ($vs)
		{ 
			$vsData = $this->getVsData($vs);
			$uid = @$vsData["userId"];
			$validUntil = @$vsData["validUntil"];
		}
		else
		{
			$uid = @$params['uid'];
			$validUntil = 0;
		}
		
		if ($validUntil && $validUntil < time())
			return;
			
		if ($uid != "0" && $uid != "" && !$isStartSession)
			return;
	
		unset($params['vs']);
		unset($params['vidsig']);
		$params['uri'] = $_SERVER['PATH_INFO'];
		if (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] == 'on')
			$params['__protocol'] = 'https';
		else 	
			$params['__protocol'] = 'http';
		ksort($params);
		
		$keys = array_keys($params);
		$key = md5(implode("|", $params).implode("|", $keys));

		if (!file_exists("/tmp/cache_v2"))
			mkdir("/tmp/cache_v2");	
		file_put_contents("/tmp/cache_v2/cache-$key.log", "cachekey: $key\n".print_r($params, true)."\n".$response); // sync - OK
		file_put_contents("/tmp/cache_v2/cache-$key.headers", $this->getResponse()->getHttpHeader  ( "Content-Type" )); // sync - OK
		file_put_contents("/tmp/cache_v2/cache-$key", $response); // sync - OK
	}

	public function setHttpHeader ( $hdr_name , $hdr_value  )
	{
		$this->getResponse()->setHttpHeader ( $hdr_name , $hdr_value  );
	}
	
	private function getVsData($vs)
	{
		$partnerId = null;
		$userId = null;
		$validUntil = null;
		
		$vsObj = vSessionBase::getVSObject($vs);
		if ($vsObj)
		{
			$partnerId = $vsObj->partner_id;
			$userId = $vsObj->user;
			$validUntil = $vsObj->valid_until;
		}
		
		return array("partnerId" => $partnerId, "userId" => $userId, "validUntil" => $validUntil );
	}
}
