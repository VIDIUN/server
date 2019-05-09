<?php
/**
 * @package Core
 * @subpackage externalWidgets
 */
require_once ( MODULES . "/partnerservices2/actions/getwidgetAction.class.php" );

/**
 * @package Core
 * @subpackage externalWidgets
 */
class vwidgetAction extends sfAction
{
	/**
	 * Will forward to the regular swf player according to the widget_id 
	 */
	public function execute()
	{
		// check if this is a request for the vdp without a wrapper
		// in case of an application loading the vdp (e.g. vmc)
		$nowrapper = $this->getRequestParameter( "nowrapper", false);
		
		// allow caching if either the cache start time (cache_st) parameter
		// wasn't specified or if it is past the specified time
		$cache_st = $this->getRequestParameter( "cache_st" );
		$allowCache = !$cache_st || $cache_st < time();

		$referer = @$_SERVER['HTTP_REFERER'];

		$externalInterfaceDisabled = (
		strstr($referer, "bebo.com") === false &&
		strstr($referer, "myspace.com") === false &&
		strstr($referer, "current.com") === false &&
		strstr($referer, "myyearbook.com") === false &&
		strstr($referer, "facebook.com") === false &&
		strstr($referer, "friendster.com") === false) ? "" : "&externalInterfaceDisabled=1";
		
		// if there is no wrapper the loader is responsible for setting extra params to the vdp
		$noncached_params = "";
		if (!$nowrapper)
			$noncached_params =	$externalInterfaceDisabled."&referer=".urlencode($referer);

		$protocol = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] == 'on') ? "https" : "http";
		$requestKey = $protocol.$_SERVER["REQUEST_URI"];
		
		// check if we cached the redirect url
		$cache_redirect = new myCache("vwidget", 10 * 60); // 10 minutes
		$cachedResponse  = $cache_redirect->get($requestKey);
		if ($allowCache && $cachedResponse) // dont use cache if we want to force no caching
		{
			header("X-Vidiun:cached-action");

			header("Expires: Sun, 19 Nov 2000 08:52:00 GMT");
			header( "Cache-Control: no-store, no-cache, must-revalidate, post-check=0, pre-check=0");
			header( "Pragma: no-cache" );
			
			header("Location:$cachedResponse".$noncached_params);
				
			VExternalErrors::dieGracefully();
		}
		
		// check if we cached the patched swf with flashvars
		$cache_swfdata = new myCache("vwidgetswf", 10 * 60); // 10 minutes
		$cachedResponse  = $cache_swfdata->get($requestKey);
		if ($allowCache && $cachedResponse) // dont use cache if we want to force no caching
		{
			header("X-Vidiun:cached-action");
			requestUtils::sendCdnHeaders("swf", strlen($cachedResponse), 60 * 10, null, true, time());
			echo $cachedResponse;
			VExternalErrors::dieGracefully();
		}
		
		$widget_id = $this->getRequestParameter( "wid" );
		$show_version = $this->getRequestParameter( "v" );
		$debug_vdp = $this->getRequestParameter( "debug_vdp" , false );

		$widget = widgetPeer::retrieveByPK( $widget_id );

		if ( !$widget )
		{
			VExternalErrors::dieGracefully();
		}
		
		myPartnerUtils::blockInactivePartner($widget->getPartnerId());

		// because of the routing rule - the entry_id & vmedia_type WILL exist. be sure to ignore them if smaller than 0
		$vshow_id= $widget->getVshowId();
		$entry_id= $widget->getEntryId();
		$gallery_widget = !$vshow_id && !$entry_id;

		if( !$entry_id  ) $entry_id = -1;

		if ( $widget->getSecurityType () != widget::WIDGET_SECURITY_TYPE_TIMEHASH  )
		{
			// try eid - if failed entry_id
			$eid = $this->getRequestParameter( "eid" , $this->getRequestParameter( "entry_id" ) );
			// try kid - if failed vshow_id
			$kid = $this->getRequestParameter( "kid" , $this->getRequestParameter( "vshow_id" ) );
			if ( $eid != null )
			$entry_id =  $eid ;
			// allow vshow to be overriden by dynamic one
			elseif ( $kid != null )
			$vshow_id = $kid ;
		}

		if ( $widget->getSecurityType () == widget::WIDGET_SECURITY_TYPE_MATCH_IP  )
		{
			$allowCache = false;

			// here we'll attemp to match the ip of the request with that from the customData of the widget
			$custom_data = $widget->getCustomData();
			$valid_country  = false;

			if ( $custom_data )
			{
				// in this case the custom_data should be of format:
				//  valid_county_1,valid_country_2,...,valid_country_n;falback_entry_id
				$arr = explode ( ";" , $custom_data );
				$countries_str = $arr[0]; 
				$fallback_entry_id = (isset($arr[1]) ? $arr[1] : null);
				$fallback_vshow_id = (isset($arr[2]) ? $arr[2] : null);
				$current_country = "";

				$valid_country = requestUtils::matchIpCountry( $countries_str , $current_country );
				if ( ! $valid_country )
				{
					VidiunLog::log ( "vwidgetAction: Attempting to access widget [$widget_id] and entry [$entry_id] from country [$current_country]. Retrning entry_id: [$fallback_entry_id] vshow_id [$fallback_vshow_id]" );
					$entry_id= $fallback_entry_id;
					$vshow_id = $fallback_vshow_id;
				}
			}
		}
		elseif ( $widget->getSecurityType () == widget::WIDGET_SECURITY_TYPE_FORCE_VS )
		{

		}


		$vmedia_type= -1;

		// support either uiconf_id or ui_conf_id
		$uiconf_id =  $this->getRequestParameter( "uiconf_id" );
		if ( !$uiconf_id ) $uiconf_id =  $this->getRequestParameter( "ui_conf_id" );

		if ( $uiconf_id )
		{
			$widget_type = $uiconf_id;
			$uiconf_id_str = "&uiconf_id=$uiconf_id";
		}
		else
		{
			$widget_type = $widget->getUiConfId() ;
			$uiconf_id_str = "";
		}


		if ( empty ( $widget_type ) )
		$widget_type = 3;
		$vdata = $widget->getCustomData();

		$partner_host = myPartnerUtils::getHost($widget->getPartnerId());
		$partner_cdnHost = myPartnerUtils::getCdnHost($widget->getPartnerId());

		$host = $partner_host;

		if ( $widget_type == 10)
		$swf_url = $host . "/swf/weplay.swf";
		else
		$swf_url = $host . "/swf/vplayer.swf";

		$partner_id = $widget->getPartnerId();
		$subp_id = $widget->getSubpId();
		if (!$subp_id)
		$subp_id = 0;

		$uiConf = uiConfPeer::retrieveByPK($widget_type);
		// new ui_confs which are deleted should stop the script
		// the check for >100000 is for supporting very old mediawiki and such players
		if (!$uiConf && $widget_type>100000)
	        VExternalErrors::dieGracefully();
	        
		if ($uiConf)
		{
			$ui_conf_swf_url = $uiConf->getSwfUrl();
			if( vString::beginsWith( $ui_conf_swf_url , "http") )
			{
				$swf_url = 	$ui_conf_swf_url; // absolute URL
			}
			else
			{
				$use_cdn = $uiConf->getUseCdn();
				$host = $use_cdn ?  $partner_cdnHost : $partner_host;
				$swf_url =  $host . myPartnerUtils::getUrlForPartner ( $partner_id , $subp_id ) . $ui_conf_swf_url;
			}

			if ( $debug_vdp )
			{
				$swf_url = str_replace( "/vdp/" , "/vdp_debug/" , $swf_url );
			}
		}

		if ( $show_version < 0 )
		$show_version = null;


		$ip = requestUtils::getRemoteAddress();// to convert back, use long2ip

		// the widget log should change to reflect the new data, but for now - i used $widget_id instead of the widgget_type
		//		WidgetLog::createWidgetLog( $referer , $ip , $vshow_id , $entry_id , $vmedia_type , $widget_id );

		if ( $entry_id == -1 ) $entry_id = null;

		$vdp3 = false;
		$base_wrapper_swf = myContentStorage::getFSFlashRootPath ()."/vdpwrapper/".vConf::get('vdp_wrapper_version')."/vdpwrapper.swf";
		$widgetIdStr = "widget_id=$widget_id";
		$partnerIdStr = "partner_id=$partner_id&subp_id=$subp_id";
		
		$entryVarName = 'entryId';
			
		if($widget->getIsPlayList())
			$entryVarName = 'playlistId';
		
		if ($uiConf)
		{
			$vs_flashvars = "";
			$conf_vars = $uiConf->getConfVars();
			if ($conf_vars)
			$conf_vars = "&".$conf_vars;

			$wrapper_swf = $base_wrapper_swf;

			$partner = PartnerPeer::retrieveByPK($partner_id);

			if( $partner )
			{
				$partner_type = $partner->getType();
			}

			if (version_compare($uiConf->getSwfUrlVersion(), "3.0", ">="))
			{
				$vdp3 = true;
				// further in the code, $wrapper_swf is being used and not $base_wrapper_swf
				$wrapper_swf = $base_wrapper_swf = myContentStorage::getFSFlashRootPath ().'/vdp3wrapper/'.vConf::get('vdp3_wrapper_version').'/vdp3wrapper.swf';
				$widgetIdStr = "widgetId=$widget_id";
				$uiconf_id_str = "&uiConfId=$uiconf_id";
				$partnerIdStr = "partnerId=$partner_id&subpId=$subp_id";

			}
			
			// if we are loaded without a wrapper (directly in flex)
			// 1. dont create the vs - keep url the same for caching
			// 2. dont patch the uiconf - patching is done only to wrapper anyway
			if ($nowrapper)
			{
				$dynamic_date = 
					$widgetIdStr.
					"&host=" . str_replace("http://", "", str_replace("https://", "", $partner_host)).
					"&cdnHost=" . str_replace("http://", "", str_replace("https://", "", $partner_cdnHost)).
					$uiconf_id_str  . // will be empty if nothing to add
					$conf_vars;

				$url = "$swf_url?$dynamic_date";
			}
			else
			{
				$swf_data = null;
				
				// if vdp version >= 2.5
				if (version_compare($uiConf->getSwfUrlVersion(), "2.5", ">="))
				{
					// create an anonymous session
					$vs = "";
					
					$privileges = "view:*,widget:1";
					if($widget->getIsPlayList())
						$privileges = "list:*,widget:1";
						
					if(PermissionPeer::isValidForPartner(PermissionName::FEATURE_ENTITLEMENT, $partner_id) &&
						!$widget->getEnforceEntitlement() && $widget->getEntryId())
						$privileges .= ','. vSessionBase::PRIVILEGE_DISABLE_ENTITLEMENT_FOR_ENTRY . ':' . $widget->getEntryId();
						
					if(PermissionPeer::isValidForPartner(PermissionName::FEATURE_ENTITLEMENT, $partner_id) &&
						!is_null($widget->getPrivacyContext()) && $widget->getPrivacyContext() != '' )
						$privileges .= ','. vSessionBase::PRIVILEGE_PRIVACY_CONTEXT . ':' . $widget->getPrivacyContext();
						
					$result = vSessionUtils::createVSessionNoValidations ( $partner_id , 0 , $vs , 86400 , false , "" , $privileges );
					$vs_flashvars = "&$partnerIdStr&uid=0&ts=".microtime(true);
					if($widget->getSecurityType () != widget::WIDGET_SECURITY_TYPE_FORCE_VS)
					{
						$vs_flashvars = "&vs=$vs".$vs_flashvars;
					}
					
		
					// patch vdpwrapper with getwidget and getuiconf
					$root = myContentStorage::getFSContentRootPath();
					$confFile_mtime = $uiConf->getUpdatedAt(null);
					$swf_key = "widget_{$widget_id}_{$widget_type}_{$confFile_mtime}_".md5($base_wrapper_swf.$swf_url).".swf";
					
					$cache = vCacheManager::getSingleLayerCache(vCacheManager::CACHE_TYPE_VWIDGET_SWF);
					
					if ($cache)
						$swf_data = $cache->get($swf_key);

					if (!$swf_data)
					{
						require_once(SF_ROOT_DIR . DIRECTORY_SEPARATOR . ".." . DIRECTORY_SEPARATOR . "api_v3" . DIRECTORY_SEPARATOR . "bootstrap.php");
						$dispatcher = VidiunDispatcher::getInstance();
						try
						{
							$widget_result = $dispatcher->dispatch("widget", "get", array("vs"=> $vs, "id" => $widget_id));
							$ui_conf_result = $dispatcher->dispatch("uiConf", "get", array("vs"=> $vs, "id" => $widget_type));
						}
						catch(Exception $ex)
						{
							VExternalErrors::dieGracefully();
						}
						
						if (!$ui_conf_result->confFile)
							VExternalErrors::dieGracefully();
							
						$serializer = new VidiunXmlSerializer(false);
						$widget_xml = $serializer->serialize($widget_result);

						$serializer = new VidiunXmlSerializer(false);
						$ui_conf_xml = $serializer->serialize($ui_conf_result);

						$result = "<xml><result>$widget_xml</result><result>$ui_conf_xml</result></xml>";
						
						$patcher = new vPatchSwf( file_get_contents($root . $base_wrapper_swf));
						$swf_data = $patcher->patch($result);
						
						if ($cache)
							$cache->set($swf_key, $swf_data);
					}
				}
				
	
				$vdp_version_2 = strpos($swf_url, "vdp/v2." ) > 0;
				if ($partner_host == "http://www.vidiun.com" && !$vdp_version_2 && !$vdp3)
				{
					$partner_host = 1; // otherwise the vdp will try going to cdnwww.vidiun.com
				}
				
				$track_wrapper = '';
				if (vConf::get('track_vdpwrapper') && vConf::get('vdpwrapper_track_url')) {
					$track_wrapper = "&wrapper_tracker_url=".urlencode(vConf::get('vdpwrapper_track_url')."?activation_key=".vConf::get('vidiun_activation_key')."&package_version=".vConf::get('vidiun_version'));
				}
			
				$optimizedConfVars = null;
				$optimizedHost = null;
				if (vConf::hasMap("optimized_playback"))
				{
					$optimizedPlayback = vConf::getMap("optimized_playback");
					if (array_key_exists($partner_id, $optimizedPlayback))
					{
						// force a specific vdp for the partner
						$params = $optimizedPlayback[$partner_id];
						if (array_key_exists('vdp_version', $params))
							$swf_url =  $partner_cdnHost . myPartnerUtils::getUrlForPartner ( $partner_id , $subp_id ) . "/flash/vdp3/".$params['vdp_version']."/vdp3.swf";
							
						if (array_key_exists('conf_vars', $params))
							$optimizedConfVars = $params['conf_vars'];
							
						if (array_key_exists('host', $params))
							$optimizedHost = $params['host'];
							
						// cache immidiately
						$cache_st =0;
						$allowCache = true;
					}
				}

				if ($optimizedConfVars === null)
					$optimizedConfVars = "clientDefaultMethod=GET";

				$conf_vars = "&$optimizedConfVars&" . $conf_vars;
	
				$stats_host = ($protocol == "https") ? vConf::get("stats_host_https") : vConf::get("stats_host");	
				$wrapper_stats = vConf::get('vdp3_wrapper_stats_url') ? "&wrapper_stats_url=$protocol://$stats_host".
					urlencode(str_replace("{partnerId}", $partner_id, vConf::get('vdp3_wrapper_stats_url'))) : "";

				$partner_host = str_replace("http://", "", str_replace("https://", "", $partner_host));
				// if the host is the default www domain use the cdn api domain
				if ($partner_host == vConf::get("www_host") && $optimizedHost === null)
					$partner_host = vConf::get("cdn_api_host");
				else if ($optimizedHost)
					$partner_host = $optimizedHost;

				if ($protocol == "https" && $partner_host = vConf::get("cdn_api_host"))
					$partner_host = vConf::get("cdn_api_host_https");
	
				$dynamic_date = $widgetIdStr .
					$track_wrapper.
					$wrapper_stats.
					"&vdpUrl=".urlencode($swf_url).
					"&host=" . $partner_host .
					"&cdnHost=" . str_replace("http://", "", str_replace("https://", "", $partner_cdnHost)).
					"&statistics.statsDomain=$stats_host".
					( $show_version ? "&entryVersion=$show_version" : "" ) .
					( $vshow_id ? "&vshowId=$vshow_id" : "" ).
					( $entry_id ? "&$entryVarName=$entry_id" : "" ) .
					$uiconf_id_str  . // will be empty if nothing to add
					$vs_flashvars.
					($cache_st ? "&clientTag=cache_st:$cache_st" : "").
					$conf_vars;
					
				// patch wrapper with flashvars and dump to browser
				if (version_compare($uiConf->getSwfUrlVersion(), "2.6.6", ">="))
				{
					$startTime = microtime(true);
					$patcher = new vPatchSwf( $swf_data, "VIDIUN_FLASHVARS_DATA");
					$wrapper_data = $patcher->patch($dynamic_date."&referer=".urlencode($referer));
					VidiunLog::log('Patching took '. (microtime(true) - $startTime));
						
					requestUtils::sendCdnHeaders("swf", strlen($wrapper_data), $allowCache ? 60 * 10 : 0, null, true, time());
					
					if ($_SERVER["REQUEST_METHOD"] == "HEAD")
						header('Content-Length: '.strlen($wrapper_data));
					else
						echo $wrapper_data;
					
					if ($allowCache)
					{
						$cache_swfdata->put($requestKey, $wrapper_data);
					}
					VExternalErrors::dieGracefully();
				}

				if ($swf_data)
				{				
					$md5 = md5($swf_key);
					$wrapper_swf = "content/cacheswf/".substr($md5, 0, 2)."/".substr($md5, 2, 2)."/".$swf_key;
					$wrapper_swf_path = "$root/$wrapper_swf";				
					if (!file_exists($wrapper_swf_path))
					{
						vFile::fullMkdir($wrapper_swf_path);
						file_put_contents($wrapper_swf_path, $swf_data);
					}
				}
				
				// for now changed back to $host since vdp version prior to 1.0.15 didnt support loading by external domain vdpwrapper
				$url =  $host . myPartnerUtils::getUrlForPartner( $partner_id , $subp_id ) . "/$wrapper_swf?$dynamic_date";
			}
		}
		else
		{
			$dynamic_date = "vshowId=$vshow_id" .
			"&host=" . requestUtils::getRequestHostId() .
			( $show_version ? "&entryVersion=$show_version" : "" ) .
			( $entry_id ? "&$entryVarName=$entry_id" : "" ) .
			( $entry_id ? "&VmediaType=$vmedia_type" : "");
			$dynamic_date .= "&isWidget=$widget_type&referer=".urlencode($referer);
			$dynamic_date .= "&vdata=$vdata";
			$url = "$swf_url?$dynamic_date";
		}

		// if referer has a query string an IE bug will prevent out flashvars to propagate
		// when nowrapper is true we cant use /swfparams either as there isnt a vdpwrapper
		if (!$nowrapper && $uiConf && version_compare($uiConf->getSwfUrlVersion(), "2.6.6", ">="))
		{
			// apart from the /swfparam/ format, add .swf suffix to the end of the stream in case
			// a corporate firewall looks at the file suffix
			$pos = strpos($url, "?");
			$url = substr($url, 0, $pos)."/swfparams/".urlencode(substr($url, $pos + 1)).".swf";			
		}

		if ($allowCache)
			$cache_redirect->put($requestKey, $url);

		if (strpos($url, "/swfparams/") > 0)
			$url = substr($url, 0, -4).urlencode($noncached_params).".swf";
		else
			$url .= $noncached_params;

		VExternalErrors::terminateDispatch();
		$this->redirect( $url );
	}
}
