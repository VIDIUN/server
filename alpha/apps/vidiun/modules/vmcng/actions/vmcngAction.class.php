<?php
/**
 * @package    Core
 * @subpackage VMCNG
 */
class vmcngAction extends vidiunAction
{
	const LIVE_ANALYTICS_UICONF_TAG = 'livea_player';
	const PLAYER_V3_VERSIONS_TAG = 'playerV3Versions';

	public function execute()
	{
		if (!vConf::hasParam('vmcng'))
		{
			VidiunLog::warning("vmcng config doesn't exist in configuration.");
			return sfView::ERROR;
		}

		$vmcngParams = vConf::get('vmcng');
		$isSecuredLogin = vConf::get('vmc_secured_login');
		$enforceSecureProtocol = isset($isSecuredLogin) && $isSecuredLogin == "1";
		$requestSecureProtocol = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] == 'on');

		// Check for forced HTTPS

		if ($enforceSecureProtocol)
		{
			if (!$requestSecureProtocol)
			{
				header("Location: " . infraRequestUtils::PROTOCOL_HTTPS . "://" . $_SERVER['SERVER_NAME'] . $_SERVER["REQUEST_URI"]);
				die();
			}
			header("Strict-Transport-Security: max-age=63072000; includeSubdomains; preload");
		}

		header("X-XSS-Protection: 1; mode=block");

		//disable cache
		header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
		header("Cache-Control: post-check=0, pre-check=0", false);
		header("Pragma: no-cache");

		if (!isset($vmcngParams["vmcng_version"]))
		{
			VidiunLog::warning("vmcng_version doesn't exist in configuration.");
			return sfView::ERROR;
		}

		$vmcngVersion = $vmcngParams["vmcng_version"];
		$baseDir = vConf::get("BASE_DIR", 'system');
		$basePath = $baseDir . "/apps/vmcng/$vmcngVersion/";
		$deployUrl = "/apps/vmcng/$vmcngVersion/";

		$path = $basePath . "index.html";
		$content = file_get_contents($path);
		if ($content === false)
		{
			VidiunLog::warning("Couldn't locate vmcng path: $path");
			return sfView::ERROR;
		}

		$config = $this->initConfig($deployUrl, $vmcngParams, $enforceSecureProtocol, $requestSecureProtocol);
		$config = json_encode($config);
		$config = str_replace("\\/", '/', $config);

		$content = str_replace("<base href=\"/\">", "<base href=\"/index.php/vmcng/\">", $content);
		$content = preg_replace("/src=\"(?!(http:)|(https:)|\/)/i", "src=\"{$deployUrl}", $content);
		$content = preg_replace("/href=\"(?!(http:)|(https:)|\/)/i", "href=\"{$deployUrl}", $content);

		$content = str_replace("var vmcConfig = null", "var vmcConfig = " . $config, $content);
		echo $content;
	}

	private function initConfig($deployUrl, $vmcngParams, $enforceSecureProtocol, $requestSecureProtocol)
	{
		$this->liveAUiConf = uiConfPeer::getUiconfByTagAndVersion(self::LIVE_ANALYTICS_UICONF_TAG, vConf::get("liveanalytics_version"));
		$this->contentUiconfsLivea = isset($this->liveAUiConf) ? array_values($this->liveAUiConf) : null;
		$this->contentUiconfLivea = (is_array($this->contentUiconfsLivea) && reset($this->contentUiconfsLivea)) ? reset($this->contentUiconfsLivea) : null;

		$this->previewUIConf = uiConfPeer::getUiconfByTagAndVersion('VMCng', $vmcngParams["vmcng_version"]);
		$this->contentUiconfsPreview = isset($this->previewUIConf) ? array_values($this->previewUIConf) : null;
		$this->contentUiconfPreview = (is_array($this->contentUiconfsPreview) && reset($this->contentUiconfsPreview)) ? reset($this->contentUiconfsPreview) : null;

		$secureCDNServerUri = "https://" . vConf::get("cdn_api_host_https");
		if (!$enforceSecureProtocol && !$requestSecureProtocol)
			$secureCDNServerUri = "http://" . vConf::get("cdn_api_host");

		$serverAPIUri = vConf::get("www_host");
		if (isset($vmcngParams["vmcng_custom_uri"]))
			$serverAPIUri = $vmcngParams["vmcng_custom_uri"];

		$this->playerV3VersionsUiConf = uiConfPeer::getUiconfByTagAndVersion(self::PLAYER_V3_VERSIONS_TAG, "latest");
		$this->content_uiconfs_player_v3_versions = isset($this->playerV3VersionsUiConf) ? array_values($this->playerV3VersionsUiConf) : null;
		$this->content_uiconf_player_v3_versions = (is_array($this->content_uiconfs_player_v3_versions) && reset($this->content_uiconfs_player_v3_versions)) ? reset($this->content_uiconfs_player_v3_versions) : null;


		$studio = null;
		if (vConf::hasParam("studio_version") && vConf::hasParam("html5_version"))
		{
			$studio = array(
				"uri" => '/apps/studio/' . vConf::get("studio_version") . "/index.html",
				"html5_version" => vConf::get("html5_version"),
				"html5lib" => $secureCDNServerUri . "/html5/html5lib/" . vConf::get("html5_version") . "/mwEmbedLoader.php"
			);
		}

		$studioV3 = null;
		if (vConf::hasParam("studio_v3_version") && vConf::hasParam("html5_version"))
		{
			$studioV3 = array(
				"uri" => '/apps/studioV3/' . vConf::get("studio_v3_version") . "/index.html",
				"html5_version" => vConf::get("html5_version"),
				"html5lib" => $secureCDNServerUri . "/html5/html5lib/" . vConf::get("html5_version") . "/mwEmbedLoader.php",
				"playerVersionsMap" => isset($this->content_uiconf_player_v3_versions) ? $this->content_uiconf_player_v3_versions->getConfig() : ''
			);
		}

		$liveAnalytics = null;
		if (vConf::hasParam("liveanalytics_version"))
		{
			$liveAnalytics = array(
				"uri" => '/apps/liveanalytics/' . vConf::get("liveanalytics_version") . "/index.html",
				"uiConfId" => isset($this->contentUiconfLivea) ? strval($this->contentUiconfLivea->getId()) : null,
				"mapUrls" => vConf::hasParam ("cdn_static_hosts") ? array_map(function($s) {return "$s/content/static/maps/v1";}, vConf::get ("cdn_static_hosts")) : array(),
                "mapZoomLevels" => vConf::hasParam("map_zoom_levels") ? vConf::get("map_zoom_levels") : ''
			);
		}

		$liveDashboard = null;
		if (vConf::hasParam("live_dashboard_version"))
		{
			$liveDashboard = array(
				"uri" => '/apps/liveDashboard/' . vConf::get("live_dashboard_version") . "/index.html"
			);
		}

		$editor = null;
		if (isset($vmcngParams["vmcng_vea_version"]))
		{
			$editor = array(
				"uri" => '/apps/vea/' . $vmcngParams["vmcng_vea_version"] . "/index.html"
			);
		}

		$reach = null;
		if (isset($vmcngParams["vmcng_reach_version"]))
		{
			$reach = array(
				"uri" => '/apps/reach/' . $vmcngParams["vmcng_reach_version"] . "/index.html"
			);
		}

		$usageDashboard = null;
		if (vConf::hasParam("usagedashboard_version"))
		{
			$usageDashboard = array(
				"uri" => '/apps/usage-dashboard/' . vConf::get("usagedashboard_version") . "/index.html"
			);
		}

		$vmcAnalytics = null;
		if (vConf::hasParam("vmc_analytics_version"))
		{
			$vmcAnalytics = array(
				"uri" => '/apps/vmc-analytics/' . vConf::get("vmc_analytics_version") . "/index.html"
			);
		}

		$config = array(
			'vidiunServer' => array(
				'uri' => $serverAPIUri,
				'deployUrl' => $deployUrl,
				'resetPasswordUri'=> "/index.php/vmcng/resetpassword/setpasshashkey/{hash}",
				'previewUIConf' => $this->contentUiconfPreview->getId(),
				),
			'cdnServers' => array(
				'serverUri' => "http://" . vConf::get("cdn_api_host"),
				'securedServerUri' => $secureCDNServerUri
			),
			"externalApps" => array(
				"studioV2" => $studio,
				"studioV3" => $studioV3,
				"liveAnalytics" => $liveAnalytics,
				"liveDashboard" => $liveDashboard,
				"usageDashboard" => $usageDashboard,
				"editor" => $editor,
				"reach" => $reach,
				"vmcAnalytics" => $vmcAnalytics
			),
			"externalLinks" => array(
				"previewAndEmbed" => $vmcngParams['previewAndEmbed'],
				"vidiun" => $vmcngParams['vidiun'],
				"entitlements" => $vmcngParams['entitlements'],
				"uploads" => $vmcngParams['uploads'],
				"live" => $vmcngParams['live']
			)
		);

		return $config;
	}
}
