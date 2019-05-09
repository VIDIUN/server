<?php
/**
 * VSR - Vidiun Screencast Recorder
 * This action is used for integrating the VSR widget into web pages, by returning a JS code that provides everything the integrator needs in order to load the widget
 * the VSR widget is a JAVA applet that allows the user to record the screen, and then it uploads the recording to Vidiun.
 * the JS code which is returned to the page is constructed from a template which is part of a version of the widget (e.g. flash/vsr/v1.0.32/js/*) and it is constructed with values stored in the uiconf XML.
 *
 * @package Core
 * @subpackage externalWidgets
 */

class vsrAction extends sfAction 
{
    const SOM_JS_FILENAME = 'som.js';
    const SOM_DETECT_JS_FILENAME = 'som-detect.js';
    const VIDIUN_LIB_JS_FILENAME = 'lib.js';
    const VIDIUN_LIB_API_JS_FILENAME = 'api.js';
    const JS_PATH_IN_JARS_FOLDER = 'js';
    
    private $jsTemplateParams = array(
        /** environment options **/
        'VIDIUN_SERVER' => array( 'method' => '_getVidiunHost', ), // comes from local.ini
        'JAR_HOST_PATH' => array( 'method' => '_buildJarsHostPath' ), // CDN host + swf_url [ conf object +  ]
        'SOM_PARTNER_ID' => array( 'method' => '_getSomPartnerInfo', 'param' => 'id', ), // comes from local.ini
        'SOM_PARTNER_SITE' => array( 'method' => '_getSomPartnerInfo', 'param' => 'site', ), // comes from local.ini, empty by default
        'SOM_PARTNER_KEY' => array( 'method' => '_getSomPartnerInfo', 'param' => 'key', ),// comes from local.ini

        /** uiconf object originated options **/
        'SOM_JAR_RUN' => array( 'method' => '_getRunJarNameFromSwfUrl' ), // parse swf_url for filename.jar

        /** uiconf XML originated options **/
        'VIDIUN_VIDEOBITRATE' => array( 'value' => 0, 'method' => '_getFromXml', 'param' => '/uiconf/vidiun/videoBitRate', ),
        'VIDIUN_CATEGORY' => array( 'method' => '_getFromXml', 'param' => '/uiconf/vidiun/category', ),
        'VIDIUN_CONVERSIONPROFILEID' => array( 'method' => '_getFromXml', 'param' => '/uiconf/vidiun/conversionProfileId', ),
        'VIDIUN_SUBMIT_TITLE_VALUE' => array( 'method' => '_getFromXml', 'param' => '/uiconf/vidiun/submit/title/value', ),
        'VIDIUN_SUBMIT_DESCRIPTION_VALUE' => array( 'method' => '_getFromXml', 'param' => '/uiconf/vidiun/submit/description/value', ),
        'VIDIUN_SUBMIT_TAGS_VALUE' => array( 'method' => '_getFromXml', 'param' => '/uiconf/vidiun/submit/tags/value', ),
        'VIDIUN_SUBMIT_TITLE_ENABLED' => array( 'method' => '_getFromXml', 'param' => '/uiconf/vidiun/submit/title/enabled', ),
        'VIDIUN_SUBMIT_DESCRIPTION_ENABLED' => array( 'method' => '_getFromXml', 'param' => '/uiconf/vidiun/submit/description/enabled', ),
        'VIDIUN_SUBMIT_TAGS_ENABLED' => array( 'method' => '_getFromXml', 'param' => '/uiconf/vidiun/submit/tags/enabled', ),
        
        'VIDIUN_ERROR_MESSAGES' => array( 'value' => '', 'method' => '_getErrorMessagesFromXml'),
        'SOM_CAPTURE_ID' => array( 'method' => '_getFromXml', 'param' => '/uiconf/som/captureId', ),
        'SOM_MAC_NAME' => array( 'method' => '_getFromXml', 'param' => '/uiconf/som/macName', ),
        'SOM_SIDE_PANEL_ONLY' => array(
            'value' => 'true', // default value here, due to JS no wrapped in quotes
            'method' => '_getFromXml', 'param' => '/uiconf/som/sidePanelOnly',
        ),
        'SOM_JARS' => array( 'method' => '_getJarsFromXml', ),
        'SOM_RECORDER_OPTIONS_SKIN0' => array( 'method' => '_getFromXml', 'param' => '/uiconf/som/recorderOptions/skin0', ),
        'SOM_RECORDER_OPTIONS_MAXCAPTURESEC' => array(
            'value' => 7200, // default value here, due to JS not wrapped in quotes
            'method' => '_getFromXml',  'param' => '/uiconf/som/recorderOptions/maxCaptureSec',
        ), 
    );

    private $uiconfObj;
    private $uiconfXmlObj;
    private $jsResult = '';

    /**
     * Will return a JS library for integrating the VSR (similar to HTML5 in concept)
     * uiconfId specifies from which uiconf to fetch different settings that should be replaced in the JS
     */
    public function execute()
    {
        // make sure output is not parsed as HTML
        header("Content-type: application/x-javascript");
        
        $uiconfId = $this->getRequestParameter("uiconfId"); // replace all $_GET with $this->getRequestParameter()
        // load uiconf from DB.

        $this->uiconfObj = uiConfPeer::retrieveByPK($uiconfId);
	if(!$this->uiconfObj)
	{
		VExternalErrors::dieError(VExternalErrors::UI_CONF_NOT_FOUND);
	}

	$ui_conf_swf_url = $this->uiconfObj->getSwfUrl();
	if (!$ui_conf_swf_url)
	{
		VExternalErrors::dieError(VExternalErrors::ILLEGAL_UI_CONF, "SWF URL not found in UI conf");
	}
        
        @libxml_use_internal_errors(true);
        try
        {
            $this->uiconfXmlObj = new SimpleXMLElement(trim($this->uiconfObj->getConfFile()));
        }
        catch(Exception $e)
        {
            VidiunLog::err("malformed uiconf XML - base64 encoded: [".base64_encode(trim($this->uiconfObj->getConfFile()))."]");
        }
        if(!($this->uiconfXmlObj instanceof SimpleXMLElement))
        {
            // no xml or invalid XML, so throw exception
            throw new Exception('uiconf XML is invalid');
        }
        // unsupress the xml errors
        @libxml_use_internal_errors(false);


        $this->_initReplacementTokens();;
        $this->_prepareLibJs();
        $this->_prepareJs();

        echo $this->jsResult;
	die;
    }
    
    private function _initReplacementTokens()
    {
        foreach($this->jsTemplateParams as $token => $settings)
        {
            if(!isset($settings['value'])) $this->jsTemplateParams[$token]['value'] = ''; // init empty value where needed
            $method = $settings['method'];
            $param = (isset($settings['param']))? $settings['param']: null;

            $value = $this->$method($param);
            if($value !== false)
            {
                $this->jsTemplateParams[$token]['value'] = $value;
            }
        }
    }

    private function _getJarsPathFromSwfUrl()
    {
        $lastSlash = strrpos($this->uiconfObj->getSwfUrl(), '/');
        return substr($this->uiconfObj->getSwfUrl(), 0, $lastSlash);
    }

    private function _getRunJarNameFromSwfUrl()
    {
        $lastSlash = strrpos($this->uiconfObj->getSwfUrl(), '/');
        return substr($this->uiconfObj->getSwfUrl(), $lastSlash+1);
    }

    
    private function _buildJarsHostPath()
    {
        $baseUrl = myPartnerUtils::getCdnHost($this->uiconfObj->getPartnerId());

        $jarPath = $this->_getJarsPathFromSwfUrl();

        $scheme = parse_url($jarPath, PHP_URL_SCHEME);
        if(!is_null($scheme)) // $jarsPath is absolute URL -just return it.
        {
            return $jarPath;
        }
        else
        {
            $jarPath = ltrim($jarPath, '/');
            $fullUrl = $baseUrl .'/'. $jarPath;;
            return $fullUrl;
        }
    }

    private function _getVidiunHost()
    {
        $proto='http';
        $vidiunHost = vConf::get('www_host');
        if (infraRequestUtils::getProtocol() == infraRequestUtils::PROTOCOL_HTTPS){
            $proto='https';
            if(vConf::hasParam('www_host_https')){
                $vidiunHost = vConf::get('www_host_https');
            }
        }
        $url = $proto .'://'. $vidiunHost;
        return $url;
    }

    private function _getSomPartnerInfo($what)
    {
        switch($what)
        {
            case 'id':   return vConf::get('vsr_id');
            case 'site': return vConf::get('vsr_site');
            case 'key':  return vConf::get('vsr_key');
        }
    }

    private function _getFromXml($xpath)
    {
        $xpathArr = $this->uiconfXmlObj->xpath($xpath);
        if (is_array($xpathArr) && count($xpathArr))
        {
            return (string)$xpathArr[0];
        }
        else
        {
            return false;
        }
    }

    private function _getJarsFromXml()
    {
        $jarsStr = '';
        $xpath = '/uiconf/jars/jar';

        $xpathArr = $this->uiconfXmlObj->xpath($xpath);
        if (is_array($xpathArr) && count($xpathArr))
        {
            foreach($xpathArr as $jar)
            {
                $jarsStr .= PHP_EOL."'".(string)$jar."',";
            }
            $jarsStr = rtrim($jarsStr, ',').PHP_EOL;
        }
        return $jarsStr;
    }

    // this is the only place where this code "knows" the JS because we want to loop dynamically over all error messages override in uiconf
    private function _getErrorMessagesFromXml()
    {
        $errormsgs = array();
        $xpath = '/uiconf/vidiun/errorMessages/*';

        $xpathArr = $this->uiconfXmlObj->xpath($xpath);
        if (is_array($xpathArr) && count($xpathArr))
        {
            foreach($xpathArr as $key => $msgNode)
            {
                $msgDetails = (array)$msgNode->children();
                if(isset($msgDetails['starts']) && isset($msgDetails['replace']))
                {
                    $starts = $msgDetails['starts'];
                    $replace = $msgDetails['replace'];
                    $errormsgs[] = 'name = "vidiun.error.messages.'.$key.'.starts";'.PHP_EOL;
                    $errormsgs[] = "vidiunScreenRecord.errorMessages[name] = '".$starts."';".PHP_EOL;
                    $errormsgs[] = 'name = "vidiun.error.messages.'.$key.'.replace";'.PHP_EOL;
                    $errormsgs[] = "vidiunScreenRecord.errorMessages[name] = '".$replace."';".PHP_EOL;
                }
            }
        }
        $returnStr = implode('', $errormsgs);
        return $returnStr;
    }

    private function _getJsFilesPath()
    {
        $jarsPath = $this->_getJarsPathFromSwfUrl();
        $scheme = parse_url($jarsPath, PHP_URL_SCHEME);
        if(!is_null($scheme))
        {
            // TODO - do we want to handle loading the JS file from remote URL?
            // or artenatively find a way to get them locally?
            throw new Exception("cannot load JS files from absolute URL");
        }

        // TODO - find a way to extract this value from an .ini file
        $baseServerPath = rtrim(myContentStorage::getFSContentRootPath(), '/').'/';
        return $baseServerPath.$jarsPath.'/'.self::JS_PATH_IN_JARS_FOLDER .'/';
    }
    
    private function _prepareLibJs()
    {
	$filePath = $this->_getJsFilesPath(). self::VIDIUN_LIB_JS_FILENAME;
	if(!file_exists($filePath))
	{
		VExternalErrors::dieError(VExternalErrors::ILLEGAL_UI_CONF, "Required file is missing");
	}
        $this->jsResult = file_get_contents($filePath);

        foreach($this->jsTemplateParams as $token => $info)
        {
            $value = $info['value'];
            $this->jsResult = str_replace($token, $value, $this->jsResult);
        }
    }

    private function _prepareJs()
    {
	$baseFilePath = $this->_getJsFilesPath();
	$somDetectJsPath = $baseFilePath. self::SOM_DETECT_JS_FILENAME;
	$somJsPath = $baseFilePath. self::SOM_JS_FILENAME;
	$apiJsPath = $baseFilePath. self::VIDIUN_LIB_API_JS_FILENAME;
	
	if(!file_exists($somDetectJsPath) || !file_exists($somJsPath) || !file_exists($apiJsPath))
	{
		VExternalErrors::dieError(VExternalErrors::ILLEGAL_UI_CONF, "Required file is missing");
	}
	
        $somDetectJs = file_get_contents($somDetectJsPath);
        $somJs = file_get_contents($somJsPath);
        $apiJs = file_get_contents($apiJsPath);
        $fullJs = $somDetectJs. PHP_EOL. $somJs . PHP_EOL . $this->jsResult . PHP_EOL . $apiJs;
        $this->jsResult = $fullJs;
    }
}
