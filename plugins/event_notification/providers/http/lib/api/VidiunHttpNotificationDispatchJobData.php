<?php
/**
 * @package plugins.httpNotification
 * @subpackage api.objects
 */
class VidiunHttpNotificationDispatchJobData extends VidiunEventNotificationDispatchJobData
{
	/**
	 * Remote server URL
	 * @var string
	 */
	public $url;
	
	/**
	 * Request method.
	 * @var VidiunHttpNotificationMethod
	 */
	public $method;
	
	/**
	 * Data to send.
	 * 
	 * @var string
	 */
	public $data;
	
	/**
	 * The maximum number of seconds to allow cURL functions to execute.
	 * 
	 * @see CURLOPT_TIMEOUT
	 * @var int
	 */
	public $timeout;
	
	/**
	 * The number of seconds to wait while trying to connect.
	 * Must be larger than zero.
	 * 
	 * @see CURLOPT_CONNECTTIMEOUT
	 * @var int
	 */
	public $connectTimeout;
	
	/**
	 * A username to use for the connection.
	 * 
	 * @see CURLOPT_USERPWD
	 * @var string
	 */
	public $username;
	
	/**
	 * A password to use for the connection.
	 * 
	 * @see CURLOPT_USERPWD
	 * @var string
	 */
	public $password;
	
	/**
	 * The HTTP authentication method to use.
	 * 
	 * @see CURLOPT_HTTPAUTH
	 * @var VidiunHttpNotificationAuthenticationMethod
	 */
	public $authenticationMethod;
	
	/**
	 * The SSL version (2 or 3) to use.
	 * By default PHP will try to determine this itself, although in some cases this must be set manually.
	 * 
	 * @see CURLOPT_SSLVERSION
	 * @var VidiunHttpNotificationSslVersion
	 */
	public $sslVersion;
	
	/**
	 * SSL certificate to verify the peer with.
	 * 
	 * @see CURLOPT_CAINFO / CURLOPT_SSLCERT
	 * @var string
	 */
	public $sslCertificate;
	
	/**
	 * The format of the certificate.
	 * 
	 * @see CURLOPT_SSLCERTTYPE
	 * @var VidiunHttpNotificationCertificateType
	 */
	public $sslCertificateType;
	
	/**
	 * The password required to use the certificate.
	 * 
	 * @see CURLOPT_SSLCERTPASSWD
	 * @var string
	 */
	public $sslCertificatePassword;
	
	/**
	 * The identifier for the crypto engine of the private SSL key specified in ssl key.
	 * 
	 * @see CURLOPT_SSLENGINE
	 * @var string
	 */
	public $sslEngine;
	
	/**
	 * The identifier for the crypto engine used for asymmetric crypto operations.
	 * 
	 * @see CURLOPT_SSLENGINE_DEFAULT
	 * @var string
	 */
	public $sslEngineDefault;
	
	/**
	 * The key type of the private SSL key specified in ssl key - PEM / DER / ENG.
	 * 
	 * @see CURLOPT_SSLKEYTYPE
	 * @var VidiunHttpNotificationSslKeyType
	 */
	public $sslKeyType;
	
	/**
	 * Private SSL key.
	 * 
	 * @see CURLOPT_SSLKEY
	 * @var string
	 */
	public $sslKey;
	
	/**
	 * The secret password needed to use the private SSL key specified in ssl key.
	 * 
	 * @see CURLOPT_SSLKEYPASSWD
	 * @var string
	 */
	public $sslKeyPassword;
	
	/**
	 * Adds a e-mail custom header
	 * 
	 * @var VidiunKeyValueArray
	 */
	public $customHeaders;
	
	/**
	 * The secret to sign the notification with
	 * @var string
	 */
	public $signSecret;
	
	private static $map_between_objects = array
	(
		'url',
		'method',
		'data',
		'timeout',
		'connectTimeout',
		'username',
		'password',
		'authenticationMethod',
		'sslVersion',
		'sslCertificate',
		'sslCertificateType',
		'sslCertificatePassword',
		'sslEngine',
		'sslEngineDefault',
		'sslKeyType',
		'sslKey',
		'sslKeyPassword',
		'customHeaders',
		'signSecret',
	);

	/* (non-PHPdoc)
	 * @see VidiunEventNotificationDispatchJobData::getMapBetweenObjects()
	 */
	public function getMapBetweenObjects ( )
	{
		return array_merge ( parent::getMapBetweenObjects() , self::$map_between_objects );
	}
	
	/* (non-PHPdoc)
	 * @see VidiunObject::fromObject($srcObj)
	 */
	public function doFromObject($srcObj, VidiunDetachedResponseProfile $responseProfile = null)
	{
		/* @var $srcObj vHttpNotificationDispatchJobData */
		parent::doFromObject($srcObj, $responseProfile);
		
		if(is_null($this->data) && $srcObj->getDataObject())
		{
			$dataObject = VidiunHttpNotificationData::getInstance($srcObj->getDataObject());
			if($dataObject)
				$this->data = $dataObject->getData($srcObj);
		}
	}
}
