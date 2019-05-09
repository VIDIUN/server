<?php

/**
 * Base class that represents a row from the 'vuser' table.
 *
 * 
 *
 * @package Core
 * @subpackage model.om
 */
abstract class Basevuser extends BaseObject  implements Persistent {


	/**
	 * The Peer class.
	 * Instance provides a convenient way of calling static methods on a class
	 * that calling code may not be able to identify.
	 * @var        vuserPeer
	 */
	protected static $peer;

	/**
	 * The value for the id field.
	 * @var        int
	 */
	protected $id;

	/**
	 * The value for the login_data_id field.
	 * @var        int
	 */
	protected $login_data_id;

	/**
	 * The value for the is_admin field.
	 * @var        boolean
	 */
	protected $is_admin;

	/**
	 * The value for the screen_name field.
	 * @var        string
	 */
	protected $screen_name;

	/**
	 * The value for the full_name field.
	 * @var        string
	 */
	protected $full_name;

	/**
	 * The value for the first_name field.
	 * @var        string
	 */
	protected $first_name;

	/**
	 * The value for the last_name field.
	 * @var        string
	 */
	protected $last_name;

	/**
	 * The value for the email field.
	 * @var        string
	 */
	protected $email;

	/**
	 * The value for the sha1_password field.
	 * @var        string
	 */
	protected $sha1_password;

	/**
	 * The value for the salt field.
	 * @var        string
	 */
	protected $salt;

	/**
	 * The value for the date_of_birth field.
	 * @var        string
	 */
	protected $date_of_birth;

	/**
	 * The value for the country field.
	 * @var        string
	 */
	protected $country;

	/**
	 * The value for the state field.
	 * @var        string
	 */
	protected $state;

	/**
	 * The value for the city field.
	 * @var        string
	 */
	protected $city;

	/**
	 * The value for the zip field.
	 * @var        string
	 */
	protected $zip;

	/**
	 * The value for the url_list field.
	 * @var        string
	 */
	protected $url_list;

	/**
	 * The value for the picture field.
	 * @var        string
	 */
	protected $picture;

	/**
	 * The value for the icon field.
	 * @var        int
	 */
	protected $icon;

	/**
	 * The value for the about_me field.
	 * @var        string
	 */
	protected $about_me;

	/**
	 * The value for the tags field.
	 * @var        string
	 */
	protected $tags;

	/**
	 * The value for the tagline field.
	 * @var        string
	 */
	protected $tagline;

	/**
	 * The value for the network_highschool field.
	 * @var        string
	 */
	protected $network_highschool;

	/**
	 * The value for the network_college field.
	 * @var        string
	 */
	protected $network_college;

	/**
	 * The value for the network_other field.
	 * @var        string
	 */
	protected $network_other;

	/**
	 * The value for the mobile_num field.
	 * @var        string
	 */
	protected $mobile_num;

	/**
	 * The value for the mature_content field.
	 * @var        int
	 */
	protected $mature_content;

	/**
	 * The value for the gender field.
	 * @var        int
	 */
	protected $gender;

	/**
	 * The value for the registration_ip field.
	 * @var        int
	 */
	protected $registration_ip;

	/**
	 * The value for the registration_cookie field.
	 * @var        string
	 */
	protected $registration_cookie;

	/**
	 * The value for the im_list field.
	 * @var        string
	 */
	protected $im_list;

	/**
	 * The value for the views field.
	 * Note: this column has a database default value of: 0
	 * @var        int
	 */
	protected $views;

	/**
	 * The value for the fans field.
	 * Note: this column has a database default value of: 0
	 * @var        int
	 */
	protected $fans;

	/**
	 * The value for the entries field.
	 * Note: this column has a database default value of: 0
	 * @var        int
	 */
	protected $entries;

	/**
	 * The value for the storage_size field.
	 * Note: this column has a database default value of: 0
	 * @var        int
	 */
	protected $storage_size;

	/**
	 * The value for the produced_vshows field.
	 * Note: this column has a database default value of: 0
	 * @var        int
	 */
	protected $produced_vshows;

	/**
	 * The value for the status field.
	 * @var        int
	 */
	protected $status;

	/**
	 * The value for the created_at field.
	 * @var        string
	 */
	protected $created_at;

	/**
	 * The value for the updated_at field.
	 * @var        string
	 */
	protected $updated_at;

	/**
	 * The value for the partner_id field.
	 * Note: this column has a database default value of: 0
	 * @var        int
	 */
	protected $partner_id;

	/**
	 * The value for the display_in_search field.
	 * @var        int
	 */
	protected $display_in_search;

	/**
	 * The value for the partner_data field.
	 * @var        string
	 */
	protected $partner_data;

	/**
	 * The value for the puser_id field.
	 * @var        string
	 */
	protected $puser_id;

	/**
	 * The value for the admin_tags field.
	 * @var        string
	 */
	protected $admin_tags;

	/**
	 * The value for the indexed_partner_data_int field.
	 * @var        int
	 */
	protected $indexed_partner_data_int;

	/**
	 * The value for the indexed_partner_data_string field.
	 * @var        string
	 */
	protected $indexed_partner_data_string;

	/**
	 * The value for the custom_data field.
	 * @var        string
	 */
	protected $custom_data;

	/**
	 * The value for the type field.
	 * Note: this column has a database default value of: 0
	 * @var        int
	 */
	protected $type;

	/**
	 * @var        array vshow[] Collection to store aggregation of vshow objects.
	 */
	protected $collvshows;

	/**
	 * @var        Criteria The criteria used to select the current contents of collvshows.
	 */
	private $lastvshowCriteria = null;

	/**
	 * @var        array entry[] Collection to store aggregation of entry objects.
	 */
	protected $collentrys;

	/**
	 * @var        Criteria The criteria used to select the current contents of collentrys.
	 */
	private $lastentryCriteria = null;

	/**
	 * @var        array comment[] Collection to store aggregation of comment objects.
	 */
	protected $collcomments;

	/**
	 * @var        Criteria The criteria used to select the current contents of collcomments.
	 */
	private $lastcommentCriteria = null;

	/**
	 * @var        array flag[] Collection to store aggregation of flag objects.
	 */
	protected $collflags;

	/**
	 * @var        Criteria The criteria used to select the current contents of collflags.
	 */
	private $lastflagCriteria = null;

	/**
	 * @var        array favorite[] Collection to store aggregation of favorite objects.
	 */
	protected $collfavorites;

	/**
	 * @var        Criteria The criteria used to select the current contents of collfavorites.
	 */
	private $lastfavoriteCriteria = null;

	/**
	 * @var        array VshowVuser[] Collection to store aggregation of VshowVuser objects.
	 */
	protected $collVshowVusers;

	/**
	 * @var        Criteria The criteria used to select the current contents of collVshowVusers.
	 */
	private $lastVshowVuserCriteria = null;

	/**
	 * @var        array PuserVuser[] Collection to store aggregation of PuserVuser objects.
	 */
	protected $collPuserVusers;

	/**
	 * @var        Criteria The criteria used to select the current contents of collPuserVusers.
	 */
	private $lastPuserVuserCriteria = null;

	/**
	 * @var        array Partner[] Collection to store aggregation of Partner objects.
	 */
	protected $collPartners;

	/**
	 * @var        Criteria The criteria used to select the current contents of collPartners.
	 */
	private $lastPartnerCriteria = null;

	/**
	 * @var        array moderation[] Collection to store aggregation of moderation objects.
	 */
	protected $collmoderations;

	/**
	 * @var        Criteria The criteria used to select the current contents of collmoderations.
	 */
	private $lastmoderationCriteria = null;

	/**
	 * @var        array moderationFlag[] Collection to store aggregation of moderationFlag objects.
	 */
	protected $collmoderationFlagsRelatedByVuserId;

	/**
	 * @var        Criteria The criteria used to select the current contents of collmoderationFlagsRelatedByVuserId.
	 */
	private $lastmoderationFlagRelatedByVuserIdCriteria = null;

	/**
	 * @var        array moderationFlag[] Collection to store aggregation of moderationFlag objects.
	 */
	protected $collmoderationFlagsRelatedByFlaggedVuserId;

	/**
	 * @var        Criteria The criteria used to select the current contents of collmoderationFlagsRelatedByFlaggedVuserId.
	 */
	private $lastmoderationFlagRelatedByFlaggedVuserIdCriteria = null;

	/**
	 * @var        array categoryVuser[] Collection to store aggregation of categoryVuser objects.
	 */
	protected $collcategoryVusers;

	/**
	 * @var        Criteria The criteria used to select the current contents of collcategoryVusers.
	 */
	private $lastcategoryVuserCriteria = null;

	/**
	 * @var        array UploadToken[] Collection to store aggregation of UploadToken objects.
	 */
	protected $collUploadTokens;

	/**
	 * @var        Criteria The criteria used to select the current contents of collUploadTokens.
	 */
	private $lastUploadTokenCriteria = null;

	/**
	 * @var        array VuserToUserRole[] Collection to store aggregation of VuserToUserRole objects.
	 */
	protected $collVuserToUserRoles;

	/**
	 * @var        Criteria The criteria used to select the current contents of collVuserToUserRoles.
	 */
	private $lastVuserToUserRoleCriteria = null;

	/**
	 * @var        array VuserVgroup[] Collection to store aggregation of VuserVgroup objects.
	 */
	protected $collVuserVgroupsRelatedByVgroupId;

	/**
	 * @var        Criteria The criteria used to select the current contents of collVuserVgroupsRelatedByVgroupId.
	 */
	private $lastVuserVgroupRelatedByVgroupIdCriteria = null;

	/**
	 * @var        array VuserVgroup[] Collection to store aggregation of VuserVgroup objects.
	 */
	protected $collVuserVgroupsRelatedByVuserId;

	/**
	 * @var        Criteria The criteria used to select the current contents of collVuserVgroupsRelatedByVuserId.
	 */
	private $lastVuserVgroupRelatedByVuserIdCriteria = null;

	/**
	 * @var        array UserEntry[] Collection to store aggregation of UserEntry objects.
	 */
	protected $collUserEntrys;

	/**
	 * @var        Criteria The criteria used to select the current contents of collUserEntrys.
	 */
	private $lastUserEntryCriteria = null;

	/**
	 * Flag to prevent endless save loop, if this object is referenced
	 * by another object which falls in this transaction.
	 * @var        boolean
	 */
	protected $alreadyInSave = false;

	/**
	 * Flag to indicate if save action actually affected the db.
	 * @var        boolean
	 */
	protected $objectSaved = false;

	/**
	 * Flag to prevent endless validation loop, if this object is referenced
	 * by another object which falls in this transaction.
	 * @var        boolean
	 */
	protected $alreadyInValidation = false;

	/**
	 * Store columns old values before the changes
	 * @var        array
	 */
	protected $oldColumnsValues = array();
	
	/**
	 * @return array
	 */
	public function getColumnsOldValues()
	{
		return $this->oldColumnsValues;
	}
	
	/**
	 * @return mixed field value or null
	 */
	public function getColumnsOldValue($name)
	{
		if(isset($this->oldColumnsValues[$name]))
			return $this->oldColumnsValues[$name];
			
		return null;
	}

	/**
	 * Applies default values to this object.
	 * This method should be called from the object's constructor (or
	 * equivalent initialization method).
	 * @see        __construct()
	 */
	public function applyDefaultValues()
	{
		$this->views = 0;
		$this->fans = 0;
		$this->entries = 0;
		$this->storage_size = 0;
		$this->produced_vshows = 0;
		$this->partner_id = 0;
		$this->type = 0;
	}

	/**
	 * Initializes internal state of Basevuser object.
	 * @see        applyDefaults()
	 */
	public function __construct()
	{
		parent::__construct();
		$this->applyDefaultValues();
	}

	/**
	 * Get the [id] column value.
	 * 
	 * @return     int
	 */
	public function getId()
	{
		return $this->id;
	}

	/**
	 * Get the [login_data_id] column value.
	 * 
	 * @return     int
	 */
	public function getLoginDataId()
	{
		return $this->login_data_id;
	}

	/**
	 * Get the [is_admin] column value.
	 * 
	 * @return     boolean
	 */
	public function getIsAdmin()
	{
		return $this->is_admin;
	}

	/**
	 * Get the [screen_name] column value.
	 * 
	 * @return     string
	 */
	public function getScreenName()
	{
		return $this->screen_name;
	}

	/**
	 * Get the [full_name] column value.
	 * 
	 * @return     string
	 */
	public function getFullName()
	{
		return $this->full_name;
	}

	/**
	 * Get the [first_name] column value.
	 * 
	 * @return     string
	 */
	public function getFirstName()
	{
		return $this->first_name;
	}

	/**
	 * Get the [last_name] column value.
	 * 
	 * @return     string
	 */
	public function getLastName()
	{
		return $this->last_name;
	}

	/**
	 * Get the [email] column value.
	 * 
	 * @return     string
	 */
	public function getEmail()
	{
		return $this->email;
	}

	/**
	 * Get the [sha1_password] column value.
	 * 
	 * @return     string
	 */
	public function getSha1Password()
	{
		return $this->sha1_password;
	}

	/**
	 * Get the [salt] column value.
	 * 
	 * @return     string
	 */
	public function getSalt()
	{
		return $this->salt;
	}

	/**
	 * Get the [optionally formatted] temporal [date_of_birth] column value.
	 * 
	 * This accessor only only work with unix epoch dates.  Consider enabling the propel.useDateTimeClass
	 * option in order to avoid converstions to integers (which are limited in the dates they can express).
	 *
	 * @param      string $format The date/time format string (either date()-style or strftime()-style).
	 *							If format is NULL, then the raw unix timestamp integer will be returned.
	 * @return     mixed Formatted date/time value as string or (integer) unix timestamp (if format is NULL), NULL if column is NULL, and 0 if column value is 0000-00-00
	 * @throws     PropelException - if unable to parse/validate the date/time value.
	 */
	public function getDateOfBirth($format = '%x')
	{
		if ($this->date_of_birth === null) {
			return null;
		}


		if ($this->date_of_birth === '0000-00-00') {
			// while technically this is not a default value of NULL,
			// this seems to be closest in meaning.
			return null;
		} else {
			try {
				$dt = new DateTime($this->date_of_birth);
			} catch (Exception $x) {
				throw new PropelException("Internally stored date/time/timestamp value could not be converted to DateTime: " . var_export($this->date_of_birth, true), $x);
			}
		}

		if ($format === null) {
			// We cast here to maintain BC in API; obviously we will lose data if we're dealing with pre-/post-epoch dates.
			return (int) $dt->format('U');
		} elseif (strpos($format, '%') !== false) {
			return strftime($format, $dt->format('U'));
		} else {
			return $dt->format($format);
		}
	}

	/**
	 * Get the [country] column value.
	 * 
	 * @return     string
	 */
	public function getCountry()
	{
		return $this->country;
	}

	/**
	 * Get the [state] column value.
	 * 
	 * @return     string
	 */
	public function getState()
	{
		return $this->state;
	}

	/**
	 * Get the [city] column value.
	 * 
	 * @return     string
	 */
	public function getCity()
	{
		return $this->city;
	}

	/**
	 * Get the [zip] column value.
	 * 
	 * @return     string
	 */
	public function getZip()
	{
		return $this->zip;
	}

	/**
	 * Get the [url_list] column value.
	 * 
	 * @return     string
	 */
	public function getUrlList()
	{
		return $this->url_list;
	}

	/**
	 * Get the [picture] column value.
	 * 
	 * @return     string
	 */
	public function getPicture()
	{
		return $this->picture;
	}

	/**
	 * Get the [icon] column value.
	 * 
	 * @return     int
	 */
	public function getIcon()
	{
		return $this->icon;
	}

	/**
	 * Get the [about_me] column value.
	 * 
	 * @return     string
	 */
	public function getAboutMe()
	{
		return $this->about_me;
	}

	/**
	 * Get the [tags] column value.
	 * 
	 * @return     string
	 */
	public function getTags()
	{
		return $this->tags;
	}

	/**
	 * Get the [tagline] column value.
	 * 
	 * @return     string
	 */
	public function getTagline()
	{
		return $this->tagline;
	}

	/**
	 * Get the [network_highschool] column value.
	 * 
	 * @return     string
	 */
	public function getNetworkHighschool()
	{
		return $this->network_highschool;
	}

	/**
	 * Get the [network_college] column value.
	 * 
	 * @return     string
	 */
	public function getNetworkCollege()
	{
		return $this->network_college;
	}

	/**
	 * Get the [network_other] column value.
	 * 
	 * @return     string
	 */
	public function getNetworkOther()
	{
		return $this->network_other;
	}

	/**
	 * Get the [mobile_num] column value.
	 * 
	 * @return     string
	 */
	public function getMobileNum()
	{
		return $this->mobile_num;
	}

	/**
	 * Get the [mature_content] column value.
	 * 
	 * @return     int
	 */
	public function getMatureContent()
	{
		return $this->mature_content;
	}

	/**
	 * Get the [gender] column value.
	 * 
	 * @return     int
	 */
	public function getGender()
	{
		return $this->gender;
	}

	/**
	 * Get the [registration_ip] column value.
	 * 
	 * @return     int
	 */
	public function getRegistrationIp()
	{
		return $this->registration_ip;
	}

	/**
	 * Get the [registration_cookie] column value.
	 * 
	 * @return     string
	 */
	public function getRegistrationCookie()
	{
		return $this->registration_cookie;
	}

	/**
	 * Get the [im_list] column value.
	 * 
	 * @return     string
	 */
	public function getImList()
	{
		return $this->im_list;
	}

	/**
	 * Get the [views] column value.
	 * 
	 * @return     int
	 */
	public function getViews()
	{
		return $this->views;
	}

	/**
	 * Get the [fans] column value.
	 * 
	 * @return     int
	 */
	public function getFans()
	{
		return $this->fans;
	}

	/**
	 * Get the [entries] column value.
	 * 
	 * @return     int
	 */
	public function getEntries()
	{
		return $this->entries;
	}

	/**
	 * Get the [storage_size] column value.
	 * 
	 * @return     int
	 */
	public function getStorageSize()
	{
		return $this->storage_size;
	}

	/**
	 * Get the [produced_vshows] column value.
	 * 
	 * @return     int
	 */
	public function getProducedVshows()
	{
		return $this->produced_vshows;
	}

	/**
	 * Get the [status] column value.
	 * 
	 * @return     int
	 */
	public function getStatus()
	{
		return $this->status;
	}

	/**
	 * Get the [optionally formatted] temporal [created_at] column value.
	 * 
	 * This accessor only only work with unix epoch dates.  Consider enabling the propel.useDateTimeClass
	 * option in order to avoid converstions to integers (which are limited in the dates they can express).
	 *
	 * @param      string $format The date/time format string (either date()-style or strftime()-style).
	 *							If format is NULL, then the raw unix timestamp integer will be returned.
	 * @return     mixed Formatted date/time value as string or (integer) unix timestamp (if format is NULL), NULL if column is NULL, and 0 if column value is 0000-00-00 00:00:00
	 * @throws     PropelException - if unable to parse/validate the date/time value.
	 */
	public function getCreatedAt($format = 'Y-m-d H:i:s')
	{
		if ($this->created_at === null) {
			return null;
		}


		if ($this->created_at === '0000-00-00 00:00:00') {
			// while technically this is not a default value of NULL,
			// this seems to be closest in meaning.
			return null;
		} else {
			try {
				$dt = new DateTime($this->created_at);
			} catch (Exception $x) {
				throw new PropelException("Internally stored date/time/timestamp value could not be converted to DateTime: " . var_export($this->created_at, true), $x);
			}
		}

		if ($format === null) {
			// We cast here to maintain BC in API; obviously we will lose data if we're dealing with pre-/post-epoch dates.
			return (int) $dt->format('U');
		} elseif (strpos($format, '%') !== false) {
			return strftime($format, $dt->format('U'));
		} else {
			return $dt->format($format);
		}
	}

	/**
	 * Get the [optionally formatted] temporal [updated_at] column value.
	 * 
	 * This accessor only only work with unix epoch dates.  Consider enabling the propel.useDateTimeClass
	 * option in order to avoid converstions to integers (which are limited in the dates they can express).
	 *
	 * @param      string $format The date/time format string (either date()-style or strftime()-style).
	 *							If format is NULL, then the raw unix timestamp integer will be returned.
	 * @return     mixed Formatted date/time value as string or (integer) unix timestamp (if format is NULL), NULL if column is NULL, and 0 if column value is 0000-00-00 00:00:00
	 * @throws     PropelException - if unable to parse/validate the date/time value.
	 */
	public function getUpdatedAt($format = 'Y-m-d H:i:s')
	{
		if ($this->updated_at === null) {
			return null;
		}


		if ($this->updated_at === '0000-00-00 00:00:00') {
			// while technically this is not a default value of NULL,
			// this seems to be closest in meaning.
			return null;
		} else {
			try {
				$dt = new DateTime($this->updated_at);
			} catch (Exception $x) {
				throw new PropelException("Internally stored date/time/timestamp value could not be converted to DateTime: " . var_export($this->updated_at, true), $x);
			}
		}

		if ($format === null) {
			// We cast here to maintain BC in API; obviously we will lose data if we're dealing with pre-/post-epoch dates.
			return (int) $dt->format('U');
		} elseif (strpos($format, '%') !== false) {
			return strftime($format, $dt->format('U'));
		} else {
			return $dt->format($format);
		}
	}

	/**
	 * Get the [partner_id] column value.
	 * 
	 * @return     int
	 */
	public function getPartnerId()
	{
		return $this->partner_id;
	}

	/**
	 * Get the [display_in_search] column value.
	 * 
	 * @return     int
	 */
	public function getDisplayInSearch()
	{
		return $this->display_in_search;
	}

	/**
	 * Get the [partner_data] column value.
	 * 
	 * @return     string
	 */
	public function getPartnerData()
	{
		return $this->partner_data;
	}

	/**
	 * Get the [puser_id] column value.
	 * 
	 * @return     string
	 */
	public function getPuserId()
	{
		return $this->puser_id;
	}

	/**
	 * Get the [admin_tags] column value.
	 * 
	 * @return     string
	 */
	public function getAdminTags()
	{
		return $this->admin_tags;
	}

	/**
	 * Get the [indexed_partner_data_int] column value.
	 * 
	 * @return     int
	 */
	public function getIndexedPartnerDataInt()
	{
		return $this->indexed_partner_data_int;
	}

	/**
	 * Get the [indexed_partner_data_string] column value.
	 * 
	 * @return     string
	 */
	public function getIndexedPartnerDataString()
	{
		return $this->indexed_partner_data_string;
	}

	/**
	 * Get the [custom_data] column value.
	 * 
	 * @return     string
	 */
	public function getCustomData()
	{
		return $this->custom_data;
	}

	/**
	 * Get the [type] column value.
	 * 
	 * @return     int
	 */
	public function getType()
	{
		return $this->type;
	}

	/**
	 * Set the value of [id] column.
	 * 
	 * @param      int $v new value
	 * @return     vuser The current object (for fluent API support)
	 */
	public function setId($v)
	{
		if(!isset($this->oldColumnsValues[vuserPeer::ID]))
			$this->oldColumnsValues[vuserPeer::ID] = $this->id;

		if ($v !== null) {
			$v = (int) $v;
		}

		if ($this->id !== $v) {
			$this->id = $v;
			$this->modifiedColumns[] = vuserPeer::ID;
		}

		return $this;
	} // setId()

	/**
	 * Set the value of [login_data_id] column.
	 * 
	 * @param      int $v new value
	 * @return     vuser The current object (for fluent API support)
	 */
	public function setLoginDataId($v)
	{
		if(!isset($this->oldColumnsValues[vuserPeer::LOGIN_DATA_ID]))
			$this->oldColumnsValues[vuserPeer::LOGIN_DATA_ID] = $this->login_data_id;

		if ($v !== null) {
			$v = (int) $v;
		}

		if ($this->login_data_id !== $v) {
			$this->login_data_id = $v;
			$this->modifiedColumns[] = vuserPeer::LOGIN_DATA_ID;
		}

		return $this;
	} // setLoginDataId()

	/**
	 * Set the value of [is_admin] column.
	 * 
	 * @param      boolean $v new value
	 * @return     vuser The current object (for fluent API support)
	 */
	public function setIsAdmin($v)
	{
		if(!isset($this->oldColumnsValues[vuserPeer::IS_ADMIN]))
			$this->oldColumnsValues[vuserPeer::IS_ADMIN] = $this->is_admin;

		if ($v !== null) {
			$v = (boolean) $v;
		}

		if ($this->is_admin !== $v) {
			$this->is_admin = $v;
			$this->modifiedColumns[] = vuserPeer::IS_ADMIN;
		}

		return $this;
	} // setIsAdmin()

	/**
	 * Set the value of [screen_name] column.
	 * 
	 * @param      string $v new value
	 * @return     vuser The current object (for fluent API support)
	 */
	public function setScreenName($v)
	{
		if(!isset($this->oldColumnsValues[vuserPeer::SCREEN_NAME]))
			$this->oldColumnsValues[vuserPeer::SCREEN_NAME] = $this->screen_name;

		if ($v !== null) {
			$v = (string) $v;
		}

		if ($this->screen_name !== $v) {
			$this->screen_name = $v;
			$this->modifiedColumns[] = vuserPeer::SCREEN_NAME;
		}

		return $this;
	} // setScreenName()

	/**
	 * Set the value of [full_name] column.
	 * 
	 * @param      string $v new value
	 * @return     vuser The current object (for fluent API support)
	 */
	public function setFullName($v)
	{
		if(!isset($this->oldColumnsValues[vuserPeer::FULL_NAME]))
			$this->oldColumnsValues[vuserPeer::FULL_NAME] = $this->full_name;

		if ($v !== null) {
			$v = (string) $v;
		}

		if ($this->full_name !== $v) {
			$this->full_name = $v;
			$this->modifiedColumns[] = vuserPeer::FULL_NAME;
		}

		return $this;
	} // setFullName()

	/**
	 * Set the value of [first_name] column.
	 * 
	 * @param      string $v new value
	 * @return     vuser The current object (for fluent API support)
	 */
	public function setFirstName($v)
	{
		if(!isset($this->oldColumnsValues[vuserPeer::FIRST_NAME]))
			$this->oldColumnsValues[vuserPeer::FIRST_NAME] = $this->first_name;

		if ($v !== null) {
			$v = (string) $v;
		}

		if ($this->first_name !== $v) {
			$this->first_name = $v;
			$this->modifiedColumns[] = vuserPeer::FIRST_NAME;
		}

		return $this;
	} // setFirstName()

	/**
	 * Set the value of [last_name] column.
	 * 
	 * @param      string $v new value
	 * @return     vuser The current object (for fluent API support)
	 */
	public function setLastName($v)
	{
		if(!isset($this->oldColumnsValues[vuserPeer::LAST_NAME]))
			$this->oldColumnsValues[vuserPeer::LAST_NAME] = $this->last_name;

		if ($v !== null) {
			$v = (string) $v;
		}

		if ($this->last_name !== $v) {
			$this->last_name = $v;
			$this->modifiedColumns[] = vuserPeer::LAST_NAME;
		}

		return $this;
	} // setLastName()

	/**
	 * Set the value of [email] column.
	 * 
	 * @param      string $v new value
	 * @return     vuser The current object (for fluent API support)
	 */
	public function setEmail($v)
	{
		if(!isset($this->oldColumnsValues[vuserPeer::EMAIL]))
			$this->oldColumnsValues[vuserPeer::EMAIL] = $this->email;

		if ($v !== null) {
			$v = (string) $v;
		}

		if ($this->email !== $v) {
			$this->email = $v;
			$this->modifiedColumns[] = vuserPeer::EMAIL;
		}

		return $this;
	} // setEmail()

	/**
	 * Set the value of [sha1_password] column.
	 * 
	 * @param      string $v new value
	 * @return     vuser The current object (for fluent API support)
	 */
	public function setSha1Password($v)
	{
		if(!isset($this->oldColumnsValues[vuserPeer::SHA1_PASSWORD]))
			$this->oldColumnsValues[vuserPeer::SHA1_PASSWORD] = $this->sha1_password;

		if ($v !== null) {
			$v = (string) $v;
		}

		if ($this->sha1_password !== $v) {
			$this->sha1_password = $v;
			$this->modifiedColumns[] = vuserPeer::SHA1_PASSWORD;
		}

		return $this;
	} // setSha1Password()

	/**
	 * Set the value of [salt] column.
	 * 
	 * @param      string $v new value
	 * @return     vuser The current object (for fluent API support)
	 */
	public function setSalt($v)
	{
		if(!isset($this->oldColumnsValues[vuserPeer::SALT]))
			$this->oldColumnsValues[vuserPeer::SALT] = $this->salt;

		if ($v !== null) {
			$v = (string) $v;
		}

		if ($this->salt !== $v) {
			$this->salt = $v;
			$this->modifiedColumns[] = vuserPeer::SALT;
		}

		return $this;
	} // setSalt()

	/**
	 * Sets the value of [date_of_birth] column to a normalized version of the date/time value specified.
	 * 
	 * @param      mixed $v string, integer (timestamp), or DateTime value.  Empty string will
	 *						be treated as NULL for temporal objects.
	 * @return     vuser The current object (for fluent API support)
	 */
	public function setDateOfBirth($v)
	{
		if(!isset($this->oldColumnsValues[vuserPeer::DATE_OF_BIRTH]))
			$this->oldColumnsValues[vuserPeer::DATE_OF_BIRTH] = $this->date_of_birth;

		// we treat '' as NULL for temporal objects because DateTime('') == DateTime('now')
		// -- which is unexpected, to say the least.
		if ($v === null || $v === '') {
			$dt = null;
		} elseif ($v instanceof DateTime) {
			$dt = $v;
		} else {
			// some string/numeric value passed; we normalize that so that we can
			// validate it.
			try {
				if (is_numeric($v)) { // if it's a unix timestamp
					$dt = new DateTime('@'.$v, new DateTimeZone('UTC'));
					// We have to explicitly specify and then change the time zone because of a
					// DateTime bug: http://bugs.php.net/bug.php?id=43003
					$dt->setTimeZone(new DateTimeZone(date_default_timezone_get()));
				} else {
					$dt = new DateTime($v);
				}
			} catch (Exception $x) {
				throw new PropelException('Error parsing date/time value: ' . var_export($v, true), $x);
			}
		}

		if ( $this->date_of_birth !== null || $dt !== null ) {
			// (nested ifs are a little easier to read in this case)

			$currNorm = ($this->date_of_birth !== null && $tmpDt = new DateTime($this->date_of_birth)) ? $tmpDt->format('Y-m-d') : null;
			$newNorm = ($dt !== null) ? $dt->format('Y-m-d') : null;

			if ( ($currNorm !== $newNorm) // normalized values don't match 
					)
			{
				$this->date_of_birth = ($dt ? $dt->format('Y-m-d') : null);
				$this->modifiedColumns[] = vuserPeer::DATE_OF_BIRTH;
			}
		} // if either are not null

		return $this;
	} // setDateOfBirth()

	/**
	 * Set the value of [country] column.
	 * 
	 * @param      string $v new value
	 * @return     vuser The current object (for fluent API support)
	 */
	public function setCountry($v)
	{
		if(!isset($this->oldColumnsValues[vuserPeer::COUNTRY]))
			$this->oldColumnsValues[vuserPeer::COUNTRY] = $this->country;

		if ($v !== null) {
			$v = (string) $v;
		}

		if ($this->country !== $v) {
			$this->country = $v;
			$this->modifiedColumns[] = vuserPeer::COUNTRY;
		}

		return $this;
	} // setCountry()

	/**
	 * Set the value of [state] column.
	 * 
	 * @param      string $v new value
	 * @return     vuser The current object (for fluent API support)
	 */
	public function setState($v)
	{
		if(!isset($this->oldColumnsValues[vuserPeer::STATE]))
			$this->oldColumnsValues[vuserPeer::STATE] = $this->state;

		if ($v !== null) {
			$v = (string) $v;
		}

		if ($this->state !== $v) {
			$this->state = $v;
			$this->modifiedColumns[] = vuserPeer::STATE;
		}

		return $this;
	} // setState()

	/**
	 * Set the value of [city] column.
	 * 
	 * @param      string $v new value
	 * @return     vuser The current object (for fluent API support)
	 */
	public function setCity($v)
	{
		if(!isset($this->oldColumnsValues[vuserPeer::CITY]))
			$this->oldColumnsValues[vuserPeer::CITY] = $this->city;

		if ($v !== null) {
			$v = (string) $v;
		}

		if ($this->city !== $v) {
			$this->city = $v;
			$this->modifiedColumns[] = vuserPeer::CITY;
		}

		return $this;
	} // setCity()

	/**
	 * Set the value of [zip] column.
	 * 
	 * @param      string $v new value
	 * @return     vuser The current object (for fluent API support)
	 */
	public function setZip($v)
	{
		if(!isset($this->oldColumnsValues[vuserPeer::ZIP]))
			$this->oldColumnsValues[vuserPeer::ZIP] = $this->zip;

		if ($v !== null) {
			$v = (string) $v;
		}

		if ($this->zip !== $v) {
			$this->zip = $v;
			$this->modifiedColumns[] = vuserPeer::ZIP;
		}

		return $this;
	} // setZip()

	/**
	 * Set the value of [url_list] column.
	 * 
	 * @param      string $v new value
	 * @return     vuser The current object (for fluent API support)
	 */
	public function setUrlList($v)
	{
		if(!isset($this->oldColumnsValues[vuserPeer::URL_LIST]))
			$this->oldColumnsValues[vuserPeer::URL_LIST] = $this->url_list;

		if ($v !== null) {
			$v = (string) $v;
		}

		if ($this->url_list !== $v) {
			$this->url_list = $v;
			$this->modifiedColumns[] = vuserPeer::URL_LIST;
		}

		return $this;
	} // setUrlList()

	/**
	 * Set the value of [picture] column.
	 * 
	 * @param      string $v new value
	 * @return     vuser The current object (for fluent API support)
	 */
	public function setPicture($v)
	{
		if(!isset($this->oldColumnsValues[vuserPeer::PICTURE]))
			$this->oldColumnsValues[vuserPeer::PICTURE] = $this->picture;

		if ($v !== null) {
			$v = (string) $v;
		}

		if ($this->picture !== $v) {
			$this->picture = $v;
			$this->modifiedColumns[] = vuserPeer::PICTURE;
		}

		return $this;
	} // setPicture()

	/**
	 * Set the value of [icon] column.
	 * 
	 * @param      int $v new value
	 * @return     vuser The current object (for fluent API support)
	 */
	public function setIcon($v)
	{
		if(!isset($this->oldColumnsValues[vuserPeer::ICON]))
			$this->oldColumnsValues[vuserPeer::ICON] = $this->icon;

		if ($v !== null) {
			$v = (int) $v;
		}

		if ($this->icon !== $v) {
			$this->icon = $v;
			$this->modifiedColumns[] = vuserPeer::ICON;
		}

		return $this;
	} // setIcon()

	/**
	 * Set the value of [about_me] column.
	 * 
	 * @param      string $v new value
	 * @return     vuser The current object (for fluent API support)
	 */
	public function setAboutMe($v)
	{
		if(!isset($this->oldColumnsValues[vuserPeer::ABOUT_ME]))
			$this->oldColumnsValues[vuserPeer::ABOUT_ME] = $this->about_me;

		if ($v !== null) {
			$v = (string) $v;
		}

		if ($this->about_me !== $v) {
			$this->about_me = $v;
			$this->modifiedColumns[] = vuserPeer::ABOUT_ME;
		}

		return $this;
	} // setAboutMe()

	/**
	 * Set the value of [tags] column.
	 * 
	 * @param      string $v new value
	 * @return     vuser The current object (for fluent API support)
	 */
	public function setTags($v)
	{
		if(!isset($this->oldColumnsValues[vuserPeer::TAGS]))
			$this->oldColumnsValues[vuserPeer::TAGS] = $this->tags;

		if ($v !== null) {
			$v = (string) $v;
		}

		if ($this->tags !== $v) {
			$this->tags = $v;
			$this->modifiedColumns[] = vuserPeer::TAGS;
		}

		return $this;
	} // setTags()

	/**
	 * Set the value of [tagline] column.
	 * 
	 * @param      string $v new value
	 * @return     vuser The current object (for fluent API support)
	 */
	public function setTagline($v)
	{
		if(!isset($this->oldColumnsValues[vuserPeer::TAGLINE]))
			$this->oldColumnsValues[vuserPeer::TAGLINE] = $this->tagline;

		if ($v !== null) {
			$v = (string) $v;
		}

		if ($this->tagline !== $v) {
			$this->tagline = $v;
			$this->modifiedColumns[] = vuserPeer::TAGLINE;
		}

		return $this;
	} // setTagline()

	/**
	 * Set the value of [network_highschool] column.
	 * 
	 * @param      string $v new value
	 * @return     vuser The current object (for fluent API support)
	 */
	public function setNetworkHighschool($v)
	{
		if(!isset($this->oldColumnsValues[vuserPeer::NETWORK_HIGHSCHOOL]))
			$this->oldColumnsValues[vuserPeer::NETWORK_HIGHSCHOOL] = $this->network_highschool;

		if ($v !== null) {
			$v = (string) $v;
		}

		if ($this->network_highschool !== $v) {
			$this->network_highschool = $v;
			$this->modifiedColumns[] = vuserPeer::NETWORK_HIGHSCHOOL;
		}

		return $this;
	} // setNetworkHighschool()

	/**
	 * Set the value of [network_college] column.
	 * 
	 * @param      string $v new value
	 * @return     vuser The current object (for fluent API support)
	 */
	public function setNetworkCollege($v)
	{
		if(!isset($this->oldColumnsValues[vuserPeer::NETWORK_COLLEGE]))
			$this->oldColumnsValues[vuserPeer::NETWORK_COLLEGE] = $this->network_college;

		if ($v !== null) {
			$v = (string) $v;
		}

		if ($this->network_college !== $v) {
			$this->network_college = $v;
			$this->modifiedColumns[] = vuserPeer::NETWORK_COLLEGE;
		}

		return $this;
	} // setNetworkCollege()

	/**
	 * Set the value of [network_other] column.
	 * 
	 * @param      string $v new value
	 * @return     vuser The current object (for fluent API support)
	 */
	public function setNetworkOther($v)
	{
		if(!isset($this->oldColumnsValues[vuserPeer::NETWORK_OTHER]))
			$this->oldColumnsValues[vuserPeer::NETWORK_OTHER] = $this->network_other;

		if ($v !== null) {
			$v = (string) $v;
		}

		if ($this->network_other !== $v) {
			$this->network_other = $v;
			$this->modifiedColumns[] = vuserPeer::NETWORK_OTHER;
		}

		return $this;
	} // setNetworkOther()

	/**
	 * Set the value of [mobile_num] column.
	 * 
	 * @param      string $v new value
	 * @return     vuser The current object (for fluent API support)
	 */
	public function setMobileNum($v)
	{
		if(!isset($this->oldColumnsValues[vuserPeer::MOBILE_NUM]))
			$this->oldColumnsValues[vuserPeer::MOBILE_NUM] = $this->mobile_num;

		if ($v !== null) {
			$v = (string) $v;
		}

		if ($this->mobile_num !== $v) {
			$this->mobile_num = $v;
			$this->modifiedColumns[] = vuserPeer::MOBILE_NUM;
		}

		return $this;
	} // setMobileNum()

	/**
	 * Set the value of [mature_content] column.
	 * 
	 * @param      int $v new value
	 * @return     vuser The current object (for fluent API support)
	 */
	public function setMatureContent($v)
	{
		if(!isset($this->oldColumnsValues[vuserPeer::MATURE_CONTENT]))
			$this->oldColumnsValues[vuserPeer::MATURE_CONTENT] = $this->mature_content;

		if ($v !== null) {
			$v = (int) $v;
		}

		if ($this->mature_content !== $v) {
			$this->mature_content = $v;
			$this->modifiedColumns[] = vuserPeer::MATURE_CONTENT;
		}

		return $this;
	} // setMatureContent()

	/**
	 * Set the value of [gender] column.
	 * 
	 * @param      int $v new value
	 * @return     vuser The current object (for fluent API support)
	 */
	public function setGender($v)
	{
		if(!isset($this->oldColumnsValues[vuserPeer::GENDER]))
			$this->oldColumnsValues[vuserPeer::GENDER] = $this->gender;

		if ($v !== null) {
			$v = (int) $v;
		}

		if ($this->gender !== $v) {
			$this->gender = $v;
			$this->modifiedColumns[] = vuserPeer::GENDER;
		}

		return $this;
	} // setGender()

	/**
	 * Set the value of [registration_ip] column.
	 * 
	 * @param      int $v new value
	 * @return     vuser The current object (for fluent API support)
	 */
	public function setRegistrationIp($v)
	{
		if(!isset($this->oldColumnsValues[vuserPeer::REGISTRATION_IP]))
			$this->oldColumnsValues[vuserPeer::REGISTRATION_IP] = $this->registration_ip;

		if ($v !== null) {
			$v = (int) $v;
		}

		if ($this->registration_ip !== $v) {
			$this->registration_ip = $v;
			$this->modifiedColumns[] = vuserPeer::REGISTRATION_IP;
		}

		return $this;
	} // setRegistrationIp()

	/**
	 * Set the value of [registration_cookie] column.
	 * 
	 * @param      string $v new value
	 * @return     vuser The current object (for fluent API support)
	 */
	public function setRegistrationCookie($v)
	{
		if(!isset($this->oldColumnsValues[vuserPeer::REGISTRATION_COOKIE]))
			$this->oldColumnsValues[vuserPeer::REGISTRATION_COOKIE] = $this->registration_cookie;

		if ($v !== null) {
			$v = (string) $v;
		}

		if ($this->registration_cookie !== $v) {
			$this->registration_cookie = $v;
			$this->modifiedColumns[] = vuserPeer::REGISTRATION_COOKIE;
		}

		return $this;
	} // setRegistrationCookie()

	/**
	 * Set the value of [im_list] column.
	 * 
	 * @param      string $v new value
	 * @return     vuser The current object (for fluent API support)
	 */
	public function setImList($v)
	{
		if(!isset($this->oldColumnsValues[vuserPeer::IM_LIST]))
			$this->oldColumnsValues[vuserPeer::IM_LIST] = $this->im_list;

		if ($v !== null) {
			$v = (string) $v;
		}

		if ($this->im_list !== $v) {
			$this->im_list = $v;
			$this->modifiedColumns[] = vuserPeer::IM_LIST;
		}

		return $this;
	} // setImList()

	/**
	 * Set the value of [views] column.
	 * 
	 * @param      int $v new value
	 * @return     vuser The current object (for fluent API support)
	 */
	public function setViews($v)
	{
		if(!isset($this->oldColumnsValues[vuserPeer::VIEWS]))
			$this->oldColumnsValues[vuserPeer::VIEWS] = $this->views;

		if ($v !== null) {
			$v = (int) $v;
		}

		if ($this->views !== $v || $this->isNew()) {
			$this->views = $v;
			$this->modifiedColumns[] = vuserPeer::VIEWS;
		}

		return $this;
	} // setViews()

	/**
	 * Set the value of [fans] column.
	 * 
	 * @param      int $v new value
	 * @return     vuser The current object (for fluent API support)
	 */
	public function setFans($v)
	{
		if(!isset($this->oldColumnsValues[vuserPeer::FANS]))
			$this->oldColumnsValues[vuserPeer::FANS] = $this->fans;

		if ($v !== null) {
			$v = (int) $v;
		}

		if ($this->fans !== $v || $this->isNew()) {
			$this->fans = $v;
			$this->modifiedColumns[] = vuserPeer::FANS;
		}

		return $this;
	} // setFans()

	/**
	 * Set the value of [entries] column.
	 * 
	 * @param      int $v new value
	 * @return     vuser The current object (for fluent API support)
	 */
	public function setEntries($v)
	{
		if(!isset($this->oldColumnsValues[vuserPeer::ENTRIES]))
			$this->oldColumnsValues[vuserPeer::ENTRIES] = $this->entries;

		if ($v !== null) {
			$v = (int) $v;
		}

		if ($this->entries !== $v || $this->isNew()) {
			$this->entries = $v;
			$this->modifiedColumns[] = vuserPeer::ENTRIES;
		}

		return $this;
	} // setEntries()

	/**
	 * Set the value of [storage_size] column.
	 * 
	 * @param      int $v new value
	 * @return     vuser The current object (for fluent API support)
	 */
	public function setStorageSize($v)
	{
		if(!isset($this->oldColumnsValues[vuserPeer::STORAGE_SIZE]))
			$this->oldColumnsValues[vuserPeer::STORAGE_SIZE] = $this->storage_size;

		if ($v !== null) {
			$v = (int) $v;
		}

		if ($this->storage_size !== $v || $this->isNew()) {
			$this->storage_size = $v;
			$this->modifiedColumns[] = vuserPeer::STORAGE_SIZE;
		}

		return $this;
	} // setStorageSize()

	/**
	 * Set the value of [produced_vshows] column.
	 * 
	 * @param      int $v new value
	 * @return     vuser The current object (for fluent API support)
	 */
	public function setProducedVshows($v)
	{
		if(!isset($this->oldColumnsValues[vuserPeer::PRODUCED_VSHOWS]))
			$this->oldColumnsValues[vuserPeer::PRODUCED_VSHOWS] = $this->produced_vshows;

		if ($v !== null) {
			$v = (int) $v;
		}

		if ($this->produced_vshows !== $v || $this->isNew()) {
			$this->produced_vshows = $v;
			$this->modifiedColumns[] = vuserPeer::PRODUCED_VSHOWS;
		}

		return $this;
	} // setProducedVshows()

	/**
	 * Set the value of [status] column.
	 * 
	 * @param      int $v new value
	 * @return     vuser The current object (for fluent API support)
	 */
	public function setStatus($v)
	{
		if(!isset($this->oldColumnsValues[vuserPeer::STATUS]))
			$this->oldColumnsValues[vuserPeer::STATUS] = $this->status;

		if ($v !== null) {
			$v = (int) $v;
		}

		if ($this->status !== $v) {
			$this->status = $v;
			$this->modifiedColumns[] = vuserPeer::STATUS;
		}

		return $this;
	} // setStatus()

	/**
	 * Sets the value of [created_at] column to a normalized version of the date/time value specified.
	 * 
	 * @param      mixed $v string, integer (timestamp), or DateTime value.  Empty string will
	 *						be treated as NULL for temporal objects.
	 * @return     vuser The current object (for fluent API support)
	 */
	public function setCreatedAt($v)
	{
		// we treat '' as NULL for temporal objects because DateTime('') == DateTime('now')
		// -- which is unexpected, to say the least.
		if ($v === null || $v === '') {
			$dt = null;
		} elseif ($v instanceof DateTime) {
			$dt = $v;
		} else {
			// some string/numeric value passed; we normalize that so that we can
			// validate it.
			try {
				if (is_numeric($v)) { // if it's a unix timestamp
					$dt = new DateTime('@'.$v, new DateTimeZone('UTC'));
					// We have to explicitly specify and then change the time zone because of a
					// DateTime bug: http://bugs.php.net/bug.php?id=43003
					$dt->setTimeZone(new DateTimeZone(date_default_timezone_get()));
				} else {
					$dt = new DateTime($v);
				}
			} catch (Exception $x) {
				throw new PropelException('Error parsing date/time value: ' . var_export($v, true), $x);
			}
		}

		if ( $this->created_at !== null || $dt !== null ) {
			// (nested ifs are a little easier to read in this case)

			$currNorm = ($this->created_at !== null && $tmpDt = new DateTime($this->created_at)) ? $tmpDt->format('Y-m-d H:i:s') : null;
			$newNorm = ($dt !== null) ? $dt->format('Y-m-d H:i:s') : null;

			if ( ($currNorm !== $newNorm) // normalized values don't match 
					)
			{
				$this->created_at = ($dt ? $dt->format('Y-m-d H:i:s') : null);
				$this->modifiedColumns[] = vuserPeer::CREATED_AT;
			}
		} // if either are not null

		return $this;
	} // setCreatedAt()

	/**
	 * Sets the value of [updated_at] column to a normalized version of the date/time value specified.
	 * 
	 * @param      mixed $v string, integer (timestamp), or DateTime value.  Empty string will
	 *						be treated as NULL for temporal objects.
	 * @return     vuser The current object (for fluent API support)
	 */
	public function setUpdatedAt($v)
	{
		// we treat '' as NULL for temporal objects because DateTime('') == DateTime('now')
		// -- which is unexpected, to say the least.
		if ($v === null || $v === '') {
			$dt = null;
		} elseif ($v instanceof DateTime) {
			$dt = $v;
		} else {
			// some string/numeric value passed; we normalize that so that we can
			// validate it.
			try {
				if (is_numeric($v)) { // if it's a unix timestamp
					$dt = new DateTime('@'.$v, new DateTimeZone('UTC'));
					// We have to explicitly specify and then change the time zone because of a
					// DateTime bug: http://bugs.php.net/bug.php?id=43003
					$dt->setTimeZone(new DateTimeZone(date_default_timezone_get()));
				} else {
					$dt = new DateTime($v);
				}
			} catch (Exception $x) {
				throw new PropelException('Error parsing date/time value: ' . var_export($v, true), $x);
			}
		}

		if ( $this->updated_at !== null || $dt !== null ) {
			// (nested ifs are a little easier to read in this case)

			$currNorm = ($this->updated_at !== null && $tmpDt = new DateTime($this->updated_at)) ? $tmpDt->format('Y-m-d H:i:s') : null;
			$newNorm = ($dt !== null) ? $dt->format('Y-m-d H:i:s') : null;

			if ( ($currNorm !== $newNorm) // normalized values don't match 
					)
			{
				$this->updated_at = ($dt ? $dt->format('Y-m-d H:i:s') : null);
				$this->modifiedColumns[] = vuserPeer::UPDATED_AT;
			}
		} // if either are not null

		return $this;
	} // setUpdatedAt()

	/**
	 * Set the value of [partner_id] column.
	 * 
	 * @param      int $v new value
	 * @return     vuser The current object (for fluent API support)
	 */
	public function setPartnerId($v)
	{
		if(!isset($this->oldColumnsValues[vuserPeer::PARTNER_ID]))
			$this->oldColumnsValues[vuserPeer::PARTNER_ID] = $this->partner_id;

		if ($v !== null) {
			$v = (int) $v;
		}

		if ($this->partner_id !== $v || $this->isNew()) {
			$this->partner_id = $v;
			$this->modifiedColumns[] = vuserPeer::PARTNER_ID;
		}

		return $this;
	} // setPartnerId()

	/**
	 * Set the value of [display_in_search] column.
	 * 
	 * @param      int $v new value
	 * @return     vuser The current object (for fluent API support)
	 */
	public function setDisplayInSearch($v)
	{
		if(!isset($this->oldColumnsValues[vuserPeer::DISPLAY_IN_SEARCH]))
			$this->oldColumnsValues[vuserPeer::DISPLAY_IN_SEARCH] = $this->display_in_search;

		if ($v !== null) {
			$v = (int) $v;
		}

		if ($this->display_in_search !== $v) {
			$this->display_in_search = $v;
			$this->modifiedColumns[] = vuserPeer::DISPLAY_IN_SEARCH;
		}

		return $this;
	} // setDisplayInSearch()

	/**
	 * Set the value of [partner_data] column.
	 * 
	 * @param      string $v new value
	 * @return     vuser The current object (for fluent API support)
	 */
	public function setPartnerData($v)
	{
		if(!isset($this->oldColumnsValues[vuserPeer::PARTNER_DATA]))
			$this->oldColumnsValues[vuserPeer::PARTNER_DATA] = $this->partner_data;

		if ($v !== null) {
			$v = (string) $v;
		}

		if ($this->partner_data !== $v) {
			$this->partner_data = $v;
			$this->modifiedColumns[] = vuserPeer::PARTNER_DATA;
		}

		return $this;
	} // setPartnerData()

	/**
	 * Set the value of [puser_id] column.
	 * 
	 * @param      string $v new value
	 * @return     vuser The current object (for fluent API support)
	 */
	public function setPuserId($v)
	{
		if(!isset($this->oldColumnsValues[vuserPeer::PUSER_ID]))
			$this->oldColumnsValues[vuserPeer::PUSER_ID] = $this->puser_id;

		if ($v !== null) {
			$v = (string) $v;
		}

		if ($this->puser_id !== $v) {
			$this->puser_id = $v;
			$this->modifiedColumns[] = vuserPeer::PUSER_ID;
		}

		return $this;
	} // setPuserId()

	/**
	 * Set the value of [admin_tags] column.
	 * 
	 * @param      string $v new value
	 * @return     vuser The current object (for fluent API support)
	 */
	public function setAdminTags($v)
	{
		if(!isset($this->oldColumnsValues[vuserPeer::ADMIN_TAGS]))
			$this->oldColumnsValues[vuserPeer::ADMIN_TAGS] = $this->admin_tags;

		if ($v !== null) {
			$v = (string) $v;
		}

		if ($this->admin_tags !== $v) {
			$this->admin_tags = $v;
			$this->modifiedColumns[] = vuserPeer::ADMIN_TAGS;
		}

		return $this;
	} // setAdminTags()

	/**
	 * Set the value of [indexed_partner_data_int] column.
	 * 
	 * @param      int $v new value
	 * @return     vuser The current object (for fluent API support)
	 */
	public function setIndexedPartnerDataInt($v)
	{
		if(!isset($this->oldColumnsValues[vuserPeer::INDEXED_PARTNER_DATA_INT]))
			$this->oldColumnsValues[vuserPeer::INDEXED_PARTNER_DATA_INT] = $this->indexed_partner_data_int;

		if ($v !== null) {
			$v = (int) $v;
		}

		if ($this->indexed_partner_data_int !== $v) {
			$this->indexed_partner_data_int = $v;
			$this->modifiedColumns[] = vuserPeer::INDEXED_PARTNER_DATA_INT;
		}

		return $this;
	} // setIndexedPartnerDataInt()

	/**
	 * Set the value of [indexed_partner_data_string] column.
	 * 
	 * @param      string $v new value
	 * @return     vuser The current object (for fluent API support)
	 */
	public function setIndexedPartnerDataString($v)
	{
		if(!isset($this->oldColumnsValues[vuserPeer::INDEXED_PARTNER_DATA_STRING]))
			$this->oldColumnsValues[vuserPeer::INDEXED_PARTNER_DATA_STRING] = $this->indexed_partner_data_string;

		if ($v !== null) {
			$v = (string) $v;
		}

		if ($this->indexed_partner_data_string !== $v) {
			$this->indexed_partner_data_string = $v;
			$this->modifiedColumns[] = vuserPeer::INDEXED_PARTNER_DATA_STRING;
		}

		return $this;
	} // setIndexedPartnerDataString()

	/**
	 * Set the value of [custom_data] column.
	 * 
	 * @param      string $v new value
	 * @return     vuser The current object (for fluent API support)
	 */
	public function setCustomData($v)
	{
		if ($v !== null) {
			$v = (string) $v;
		}

		if ($this->custom_data !== $v) {
			$this->custom_data = $v;
			$this->modifiedColumns[] = vuserPeer::CUSTOM_DATA;
		}

		return $this;
	} // setCustomData()

	/**
	 * Set the value of [type] column.
	 * 
	 * @param      int $v new value
	 * @return     vuser The current object (for fluent API support)
	 */
	public function setType($v)
	{
		if(!isset($this->oldColumnsValues[vuserPeer::TYPE]))
			$this->oldColumnsValues[vuserPeer::TYPE] = $this->type;

		if ($v !== null) {
			$v = (int) $v;
		}

		if ($this->type !== $v || $this->isNew()) {
			$this->type = $v;
			$this->modifiedColumns[] = vuserPeer::TYPE;
		}

		return $this;
	} // setType()

	/**
	 * Indicates whether the columns in this object are only set to default values.
	 *
	 * This method can be used in conjunction with isModified() to indicate whether an object is both
	 * modified _and_ has some values set which are non-default.
	 *
	 * @return     boolean Whether the columns in this object are only been set with default values.
	 */
	public function hasOnlyDefaultValues()
	{
			if ($this->views !== 0) {
				return false;
			}

			if ($this->fans !== 0) {
				return false;
			}

			if ($this->entries !== 0) {
				return false;
			}

			if ($this->storage_size !== 0) {
				return false;
			}

			if ($this->produced_vshows !== 0) {
				return false;
			}

			if ($this->partner_id !== 0) {
				return false;
			}

			if ($this->type !== 0) {
				return false;
			}

		// otherwise, everything was equal, so return TRUE
		return true;
	} // hasOnlyDefaultValues()

	/**
	 * Hydrates (populates) the object variables with values from the database resultset.
	 *
	 * An offset (0-based "start column") is specified so that objects can be hydrated
	 * with a subset of the columns in the resultset rows.  This is needed, for example,
	 * for results of JOIN queries where the resultset row includes columns from two or
	 * more tables.
	 *
	 * @param      array $row The row returned by PDOStatement->fetch(PDO::FETCH_NUM)
	 * @param      int $startcol 0-based offset column which indicates which restultset column to start with.
	 * @param      boolean $rehydrate Whether this object is being re-hydrated from the database.
	 * @return     int next starting column
	 * @throws     PropelException  - Any caught Exception will be rewrapped as a PropelException.
	 */
	public function hydrate($row, $startcol = 0, $rehydrate = false)
	{
		// Nullify cached objects
		$this->m_custom_data = null;
		
		try {

			$this->id = ($row[$startcol + 0] !== null) ? (int) $row[$startcol + 0] : null;
			$this->login_data_id = ($row[$startcol + 1] !== null) ? (int) $row[$startcol + 1] : null;
			$this->is_admin = ($row[$startcol + 2] !== null) ? (boolean) $row[$startcol + 2] : null;
			$this->screen_name = ($row[$startcol + 3] !== null) ? (string) $row[$startcol + 3] : null;
			$this->full_name = ($row[$startcol + 4] !== null) ? (string) $row[$startcol + 4] : null;
			$this->first_name = ($row[$startcol + 5] !== null) ? (string) $row[$startcol + 5] : null;
			$this->last_name = ($row[$startcol + 6] !== null) ? (string) $row[$startcol + 6] : null;
			$this->email = ($row[$startcol + 7] !== null) ? (string) $row[$startcol + 7] : null;
			$this->sha1_password = ($row[$startcol + 8] !== null) ? (string) $row[$startcol + 8] : null;
			$this->salt = ($row[$startcol + 9] !== null) ? (string) $row[$startcol + 9] : null;
			$this->date_of_birth = ($row[$startcol + 10] !== null) ? (string) $row[$startcol + 10] : null;
			$this->country = ($row[$startcol + 11] !== null) ? (string) $row[$startcol + 11] : null;
			$this->state = ($row[$startcol + 12] !== null) ? (string) $row[$startcol + 12] : null;
			$this->city = ($row[$startcol + 13] !== null) ? (string) $row[$startcol + 13] : null;
			$this->zip = ($row[$startcol + 14] !== null) ? (string) $row[$startcol + 14] : null;
			$this->url_list = ($row[$startcol + 15] !== null) ? (string) $row[$startcol + 15] : null;
			$this->picture = ($row[$startcol + 16] !== null) ? (string) $row[$startcol + 16] : null;
			$this->icon = ($row[$startcol + 17] !== null) ? (int) $row[$startcol + 17] : null;
			$this->about_me = ($row[$startcol + 18] !== null) ? (string) $row[$startcol + 18] : null;
			$this->tags = ($row[$startcol + 19] !== null) ? (string) $row[$startcol + 19] : null;
			$this->tagline = ($row[$startcol + 20] !== null) ? (string) $row[$startcol + 20] : null;
			$this->network_highschool = ($row[$startcol + 21] !== null) ? (string) $row[$startcol + 21] : null;
			$this->network_college = ($row[$startcol + 22] !== null) ? (string) $row[$startcol + 22] : null;
			$this->network_other = ($row[$startcol + 23] !== null) ? (string) $row[$startcol + 23] : null;
			$this->mobile_num = ($row[$startcol + 24] !== null) ? (string) $row[$startcol + 24] : null;
			$this->mature_content = ($row[$startcol + 25] !== null) ? (int) $row[$startcol + 25] : null;
			$this->gender = ($row[$startcol + 26] !== null) ? (int) $row[$startcol + 26] : null;
			$this->registration_ip = ($row[$startcol + 27] !== null) ? (int) $row[$startcol + 27] : null;
			$this->registration_cookie = ($row[$startcol + 28] !== null) ? (string) $row[$startcol + 28] : null;
			$this->im_list = ($row[$startcol + 29] !== null) ? (string) $row[$startcol + 29] : null;
			$this->views = ($row[$startcol + 30] !== null) ? (int) $row[$startcol + 30] : null;
			$this->fans = ($row[$startcol + 31] !== null) ? (int) $row[$startcol + 31] : null;
			$this->entries = ($row[$startcol + 32] !== null) ? (int) $row[$startcol + 32] : null;
			$this->storage_size = ($row[$startcol + 33] !== null) ? (int) $row[$startcol + 33] : null;
			$this->produced_vshows = ($row[$startcol + 34] !== null) ? (int) $row[$startcol + 34] : null;
			$this->status = ($row[$startcol + 35] !== null) ? (int) $row[$startcol + 35] : null;
			$this->created_at = ($row[$startcol + 36] !== null) ? (string) $row[$startcol + 36] : null;
			$this->updated_at = ($row[$startcol + 37] !== null) ? (string) $row[$startcol + 37] : null;
			$this->partner_id = ($row[$startcol + 38] !== null) ? (int) $row[$startcol + 38] : null;
			$this->display_in_search = ($row[$startcol + 39] !== null) ? (int) $row[$startcol + 39] : null;
			$this->partner_data = ($row[$startcol + 40] !== null) ? (string) $row[$startcol + 40] : null;
			$this->puser_id = ($row[$startcol + 41] !== null) ? (string) $row[$startcol + 41] : null;
			$this->admin_tags = ($row[$startcol + 42] !== null) ? (string) $row[$startcol + 42] : null;
			$this->indexed_partner_data_int = ($row[$startcol + 43] !== null) ? (int) $row[$startcol + 43] : null;
			$this->indexed_partner_data_string = ($row[$startcol + 44] !== null) ? (string) $row[$startcol + 44] : null;
			$this->custom_data = ($row[$startcol + 45] !== null) ? (string) $row[$startcol + 45] : null;
			$this->type = ($row[$startcol + 46] !== null) ? (int) $row[$startcol + 46] : null;
			$this->resetModified();

			$this->setNew(false);

			if ($rehydrate) {
				$this->ensureConsistency();
			}

			// FIXME - using NUM_COLUMNS may be clearer.
			return $startcol + 47; // 47 = vuserPeer::NUM_COLUMNS - vuserPeer::NUM_LAZY_LOAD_COLUMNS).

		} catch (Exception $e) {
			throw new PropelException("Error populating vuser object", $e);
		}
	}

	/**
	 * Checks and repairs the internal consistency of the object.
	 *
	 * This method is executed after an already-instantiated object is re-hydrated
	 * from the database.  It exists to check any foreign keys to make sure that
	 * the objects related to the current object are correct based on foreign key.
	 *
	 * You can override this method in the stub class, but you should always invoke
	 * the base method from the overridden method (i.e. parent::ensureConsistency()),
	 * in case your model changes.
	 *
	 * @throws     PropelException
	 */
	public function ensureConsistency()
	{

	} // ensureConsistency

	/**
	 * Reloads this object from datastore based on primary key and (optionally) resets all associated objects.
	 *
	 * This will only work if the object has been saved and has a valid primary key set.
	 *
	 * @param      boolean $deep (optional) Whether to also de-associated any related objects.
	 * @param      PropelPDO $con (optional) The PropelPDO connection to use.
	 * @return     void
	 * @throws     PropelException - if this object is deleted, unsaved or doesn't have pk match in db
	 */
	public function reload($deep = false, PropelPDO $con = null)
	{
		if ($this->isDeleted()) {
			throw new PropelException("Cannot reload a deleted object.");
		}

		if ($this->isNew()) {
			throw new PropelException("Cannot reload an unsaved object.");
		}

		if ($con === null) {
			$con = Propel::getConnection(vuserPeer::DATABASE_NAME, Propel::CONNECTION_READ);
		}

		// We don't need to alter the object instance pool; we're just modifying this instance
		// already in the pool.

		vuserPeer::setUseCriteriaFilter(false);
		$criteria = $this->buildPkeyCriteria();
		vuserPeer::addSelectColumns($criteria);
		$stmt = BasePeer::doSelect($criteria, $con);
		vuserPeer::setUseCriteriaFilter(true);
		$row = $stmt->fetch(PDO::FETCH_NUM);
		$stmt->closeCursor();
		if (!$row) {
			throw new PropelException('Cannot find matching row in the database to reload object values.');
		}
		$this->hydrate($row, 0, true); // rehydrate

		if ($deep) {  // also de-associate any related objects?

			$this->collvshows = null;
			$this->lastvshowCriteria = null;

			$this->collentrys = null;
			$this->lastentryCriteria = null;

			$this->collcomments = null;
			$this->lastcommentCriteria = null;

			$this->collflags = null;
			$this->lastflagCriteria = null;

			$this->collfavorites = null;
			$this->lastfavoriteCriteria = null;

			$this->collVshowVusers = null;
			$this->lastVshowVuserCriteria = null;

			$this->collPuserVusers = null;
			$this->lastPuserVuserCriteria = null;

			$this->collPartners = null;
			$this->lastPartnerCriteria = null;

			$this->collmoderations = null;
			$this->lastmoderationCriteria = null;

			$this->collmoderationFlagsRelatedByVuserId = null;
			$this->lastmoderationFlagRelatedByVuserIdCriteria = null;

			$this->collmoderationFlagsRelatedByFlaggedVuserId = null;
			$this->lastmoderationFlagRelatedByFlaggedVuserIdCriteria = null;

			$this->collcategoryVusers = null;
			$this->lastcategoryVuserCriteria = null;

			$this->collUploadTokens = null;
			$this->lastUploadTokenCriteria = null;

			$this->collVuserToUserRoles = null;
			$this->lastVuserToUserRoleCriteria = null;

			$this->collVuserVgroupsRelatedByVgroupId = null;
			$this->lastVuserVgroupRelatedByVgroupIdCriteria = null;

			$this->collVuserVgroupsRelatedByVuserId = null;
			$this->lastVuserVgroupRelatedByVuserIdCriteria = null;

			$this->collUserEntrys = null;
			$this->lastUserEntryCriteria = null;

		} // if (deep)
	}

	/**
	 * Removes this object from datastore and sets delete attribute.
	 *
	 * @param      PropelPDO $con
	 * @return     void
	 * @throws     PropelException
	 * @see        BaseObject::setDeleted()
	 * @see        BaseObject::isDeleted()
	 */
	public function delete(PropelPDO $con = null)
	{
		if ($this->isDeleted()) {
			throw new PropelException("This object has already been deleted.");
		}

		if ($con === null) {
			$con = Propel::getConnection(vuserPeer::DATABASE_NAME, Propel::CONNECTION_WRITE);
		}
		
		$con->beginTransaction();
		try {
			$ret = $this->preDelete($con);
			if ($ret) {
				vuserPeer::doDelete($this, $con);
				$this->postDelete($con);
				$this->setDeleted(true);
				$con->commit();
			} else {
				$con->commit();
			}
		} catch (PropelException $e) {
			$con->rollBack();
			throw $e;
		}
	}

	/**
	 * Persists this object to the database.
	 *
	 * If the object is new, it inserts it; otherwise an update is performed.
	 * All modified related objects will also be persisted in the doSave()
	 * method.  This method wraps all precipitate database operations in a
	 * single transaction.
	 *
	 * @param      PropelPDO $con
	 * @return     int The number of rows affected by this insert/update and any referring fk objects' save() operations.
	 * @throws     PropelException
	 * @see        doSave()
	 */
	public function save(PropelPDO $con = null)
	{
		if ($this->isDeleted()) {
			throw new PropelException("You cannot save an object that has been deleted.");
		}

		if ($con === null) {
			$con = Propel::getConnection(vuserPeer::DATABASE_NAME, Propel::CONNECTION_WRITE);
		}
		
		$con->beginTransaction();
		$isInsert = $this->isNew();
		try {
			$ret = $this->preSave($con);
			if ($isInsert) {
				$ret = $ret && $this->preInsert($con);
			} else {
				$ret = $ret && $this->preUpdate($con);
			}
			
			if (!$ret || !$this->isModified()) {
				$con->commit();
				return 0;
			}
			
			for ($retries = 1; $retries < VidiunPDO::SAVE_MAX_RETRIES; $retries++)
			{
               $affectedRows = $this->doSave($con);
                if ($affectedRows || !$this->isColumnModified(vuserPeer::CUSTOM_DATA)) //ask if custom_data wasn't modified to avoid retry with atomic column 
                	break;

                VidiunLog::debug("was unable to save! retrying for the $retries time");
                $criteria = $this->buildPkeyCriteria();
				$criteria->addSelectColumn(vuserPeer::CUSTOM_DATA);
                $stmt = BasePeer::doSelect($criteria, $con);
                $cutsomDataArr = $stmt->fetchAll(PDO::FETCH_COLUMN);
                $newCustomData = $cutsomDataArr[0];

                $this->custom_data_md5 = is_null($newCustomData) ? null : md5($newCustomData);

                $valuesToChangeTo = $this->m_custom_data->toArray();
				$this->m_custom_data = myCustomData::fromString($newCustomData); 

				//set custom data column values we wanted to change to
				$validUpdate = true;
				$atomicCustomDataFields = vuserPeer::getAtomicCustomDataFields();
			 	foreach ($this->oldCustomDataValues as $namespace => $namespaceValues){
                	foreach($namespaceValues as $name => $oldValue)
					{
						$atomicField = false;
						if($namespace) {
							$atomicField = array_key_exists($namespace, $atomicCustomDataFields) && in_array($name, $atomicCustomDataFields[$namespace]);
						} else {
							$atomicField = in_array($name, $atomicCustomDataFields);
						}
						if($atomicField) {
							$dbValue = $this->m_custom_data->get($name, $namespace);
							if($oldValue != $dbValue) {
								$validUpdate = false;
								break;
							}
						}
						
						$newValue = null;
						if ($namespace)
						{
							if (isset ($valuesToChangeTo[$namespace][$name]))
								$newValue = $valuesToChangeTo[$namespace][$name];
						}
						else
						{ 
							$newValue = $valuesToChangeTo[$name];
						}
		
						if (is_null($newValue)) {
							$this->removeFromCustomData($name, $namespace);
						}
						else {
							$this->putInCustomData($name, $newValue, $namespace);
						}
					}
				}
                   
				if(!$validUpdate) 
					break;
					                   
				$this->setCustomData($this->m_custom_data->toString());
			}

			if ($isInsert) {
				$this->postInsert($con);
			} else {
				$this->postUpdate($con);
			}
			$this->postSave($con);
			vuserPeer::addInstanceToPool($this);
			
			$con->commit();
			return $affectedRows;
		} catch (PropelException $e) {
			$con->rollBack();
			throw $e;
		}
	}
	
	public function wasObjectSaved()
	{
		return $this->objectSaved;
	}

	/**
	 * Performs the work of inserting or updating the row in the database.
	 *
	 * If the object is new, it inserts it; otherwise an update is performed.
	 * All related objects are also updated in this method.
	 *
	 * @param      PropelPDO $con
	 * @return     int The number of rows affected by this insert/update and any referring fk objects' save() operations.
	 * @throws     PropelException
	 * @see        save()
	 */
	protected function doSave(PropelPDO $con)
	{
		$affectedRows = 0; // initialize var to track total num of affected rows
		if (!$this->alreadyInSave) {
			$this->alreadyInSave = true;

			if ($this->isNew() ) {
				$this->modifiedColumns[] = vuserPeer::ID;
			}

			// If this object has been modified, then save it to the database.
			$this->objectSaved = false;
			if ($this->isModified()) {
				if ($this->isNew()) {
					$pk = vuserPeer::doInsert($this, $con);
					$affectedRows += 1; // we are assuming that there is only 1 row per doInsert() which
										 // should always be true here (even though technically
										 // BasePeer::doInsert() can insert multiple rows).

					$this->setId($pk);  //[IMV] update autoincrement primary key

					$this->setNew(false);
					$this->objectSaved = true;
				} else {
					$affectedObjects = vuserPeer::doUpdate($this, $con);
					if($affectedObjects)
						$this->objectSaved = true;
						
					$affectedRows += $affectedObjects;
				}

				$this->resetModified(); // [HL] After being saved an object is no longer 'modified'
			}

			if ($this->collvshows !== null) {
				foreach ($this->collvshows as $referrerFK) {
					if (!$referrerFK->isDeleted()) {
						$affectedRows += $referrerFK->save($con);
					}
				}
			}

			if ($this->collentrys !== null) {
				foreach ($this->collentrys as $referrerFK) {
					if (!$referrerFK->isDeleted()) {
						$affectedRows += $referrerFK->save($con);
					}
				}
			}

			if ($this->collcomments !== null) {
				foreach ($this->collcomments as $referrerFK) {
					if (!$referrerFK->isDeleted()) {
						$affectedRows += $referrerFK->save($con);
					}
				}
			}

			if ($this->collflags !== null) {
				foreach ($this->collflags as $referrerFK) {
					if (!$referrerFK->isDeleted()) {
						$affectedRows += $referrerFK->save($con);
					}
				}
			}

			if ($this->collfavorites !== null) {
				foreach ($this->collfavorites as $referrerFK) {
					if (!$referrerFK->isDeleted()) {
						$affectedRows += $referrerFK->save($con);
					}
				}
			}

			if ($this->collVshowVusers !== null) {
				foreach ($this->collVshowVusers as $referrerFK) {
					if (!$referrerFK->isDeleted()) {
						$affectedRows += $referrerFK->save($con);
					}
				}
			}

			if ($this->collPuserVusers !== null) {
				foreach ($this->collPuserVusers as $referrerFK) {
					if (!$referrerFK->isDeleted()) {
						$affectedRows += $referrerFK->save($con);
					}
				}
			}

			if ($this->collPartners !== null) {
				foreach ($this->collPartners as $referrerFK) {
					if (!$referrerFK->isDeleted()) {
						$affectedRows += $referrerFK->save($con);
					}
				}
			}

			if ($this->collmoderations !== null) {
				foreach ($this->collmoderations as $referrerFK) {
					if (!$referrerFK->isDeleted()) {
						$affectedRows += $referrerFK->save($con);
					}
				}
			}

			if ($this->collmoderationFlagsRelatedByVuserId !== null) {
				foreach ($this->collmoderationFlagsRelatedByVuserId as $referrerFK) {
					if (!$referrerFK->isDeleted()) {
						$affectedRows += $referrerFK->save($con);
					}
				}
			}

			if ($this->collmoderationFlagsRelatedByFlaggedVuserId !== null) {
				foreach ($this->collmoderationFlagsRelatedByFlaggedVuserId as $referrerFK) {
					if (!$referrerFK->isDeleted()) {
						$affectedRows += $referrerFK->save($con);
					}
				}
			}

			if ($this->collcategoryVusers !== null) {
				foreach ($this->collcategoryVusers as $referrerFK) {
					if (!$referrerFK->isDeleted()) {
						$affectedRows += $referrerFK->save($con);
					}
				}
			}

			if ($this->collUploadTokens !== null) {
				foreach ($this->collUploadTokens as $referrerFK) {
					if (!$referrerFK->isDeleted()) {
						$affectedRows += $referrerFK->save($con);
					}
				}
			}

			if ($this->collVuserToUserRoles !== null) {
				foreach ($this->collVuserToUserRoles as $referrerFK) {
					if (!$referrerFK->isDeleted()) {
						$affectedRows += $referrerFK->save($con);
					}
				}
			}

			if ($this->collVuserVgroupsRelatedByVgroupId !== null) {
				foreach ($this->collVuserVgroupsRelatedByVgroupId as $referrerFK) {
					if (!$referrerFK->isDeleted()) {
						$affectedRows += $referrerFK->save($con);
					}
				}
			}

			if ($this->collVuserVgroupsRelatedByVuserId !== null) {
				foreach ($this->collVuserVgroupsRelatedByVuserId as $referrerFK) {
					if (!$referrerFK->isDeleted()) {
						$affectedRows += $referrerFK->save($con);
					}
				}
			}

			if ($this->collUserEntrys !== null) {
				foreach ($this->collUserEntrys as $referrerFK) {
					if (!$referrerFK->isDeleted()) {
						$affectedRows += $referrerFK->save($con);
					}
				}
			}

			$this->alreadyInSave = false;

		}
		return $affectedRows;
	} // doSave()

	/**
	 * Override in order to use the query cache.
	 * Cache invalidation keys are used to determine when cached queries are valid.
	 * Before returning a query result from the cache, the time of the cached query
	 * is compared to the time saved in the invalidation key.
	 * A cached query will only be used if it's newer than the matching invalidation key.
	 *  
	 * @return     array Array of keys that will should be updated when this object is modified.
	 */
	public function getCacheInvalidationKeys()
	{
		return array();
	}
		
	/**
	 * Code to be run before persisting the object
	 * @param PropelPDO $con
	 * @return boolean
	 */
	public function preSave(PropelPDO $con = null)
	{
		$this->setCustomDataObj();
    	
		return parent::preSave($con);
	}

	/**
	 * Code to be run after persisting the object
	 * @param PropelPDO $con
	 */
	public function postSave(PropelPDO $con = null) 
	{
		vEventsManager::raiseEvent(new vObjectSavedEvent($this));
		$this->oldColumnsValues = array();
		$this->oldCustomDataValues = array();
    	 
		parent::postSave($con);
	}
	
	/**
	 * Code to be run before inserting to database
	 * @param PropelPDO $con
	 * @return boolean
	 */
	public function preInsert(PropelPDO $con = null)
	{
		$this->setCreatedAt(time());
		$this->setUpdatedAt(time());
		return parent::preInsert($con);
	}
	
	/**
	 * Code to be run after inserting to database
	 * @param PropelPDO $con 
	 */
	public function postInsert(PropelPDO $con = null)
	{
		vQueryCache::invalidateQueryCache($this);
		
		vEventsManager::raiseEvent(new vObjectCreatedEvent($this));
		
		if($this->copiedFrom)
			vEventsManager::raiseEvent(new vObjectCopiedEvent($this->copiedFrom, $this));
		
		parent::postInsert($con);
	}

	/**
	 * Code to be run after updating the object in database
	 * @param PropelPDO $con
	 */
	public function postUpdate(PropelPDO $con = null)
	{
		if ($this->alreadyInSave)
		{
			return;
		}
	
		if($this->isModified())
		{
			vQueryCache::invalidateQueryCache($this);
			$modifiedColumns = $this->tempModifiedColumns;
			$modifiedColumns[vObjectChangedEvent::CUSTOM_DATA_OLD_VALUES] = $this->oldCustomDataValues;
			vEventsManager::raiseEvent(new vObjectChangedEvent($this, $modifiedColumns));
		}
			
		$this->tempModifiedColumns = array();
		
		parent::postUpdate($con);
	}
	/**
	 * Saves the modified columns temporarily while saving
	 * @var array
	 */
	private $tempModifiedColumns = array();
	
	/**
	 * Returns whether the object has been modified.
	 *
	 * @return     boolean True if the object has been modified.
	 */
	public function isModified()
	{
		if(!empty($this->tempModifiedColumns))
			return true;
			
		return !empty($this->modifiedColumns);
	}

	/**
	 * Has specified column been modified?
	 *
	 * @param      string $col
	 * @return     boolean True if $col has been modified.
	 */
	public function isColumnModified($col)
	{
		if(in_array($col, $this->tempModifiedColumns))
			return true;
			
		return in_array($col, $this->modifiedColumns);
	}

	/**
	 * Code to be run before updating the object in database
	 * @param PropelPDO $con
	 * @return boolean
	 */
	public function preUpdate(PropelPDO $con = null)
	{
		if ($this->alreadyInSave)
		{
			return true;
		}	
		
		
		if($this->isModified())
			$this->setUpdatedAt(time());
		
		$this->tempModifiedColumns = $this->modifiedColumns;
		return parent::preUpdate($con);
	}
	
	/**
	 * Array of ValidationFailed objects.
	 * @var        array ValidationFailed[]
	 */
	protected $validationFailures = array();

	/**
	 * Gets any ValidationFailed objects that resulted from last call to validate().
	 *
	 *
	 * @return     array ValidationFailed[]
	 * @see        validate()
	 */
	public function getValidationFailures()
	{
		return $this->validationFailures;
	}

	/**
	 * Validates the objects modified field values and all objects related to this table.
	 *
	 * If $columns is either a column name or an array of column names
	 * only those columns are validated.
	 *
	 * @param      mixed $columns Column name or an array of column names.
	 * @return     boolean Whether all columns pass validation.
	 * @see        doValidate()
	 * @see        getValidationFailures()
	 */
	public function validate($columns = null)
	{
		$res = $this->doValidate($columns);
		if ($res === true) {
			$this->validationFailures = array();
			return true;
		} else {
			$this->validationFailures = $res;
			return false;
		}
	}

	/**
	 * This function performs the validation work for complex object models.
	 *
	 * In addition to checking the current object, all related objects will
	 * also be validated.  If all pass then <code>true</code> is returned; otherwise
	 * an aggreagated array of ValidationFailed objects will be returned.
	 *
	 * @param      array $columns Array of column names to validate.
	 * @return     mixed <code>true</code> if all validations pass; array of <code>ValidationFailed</code> objets otherwise.
	 */
	protected function doValidate($columns = null)
	{
		if (!$this->alreadyInValidation) {
			$this->alreadyInValidation = true;
			$retval = null;

			$failureMap = array();


			if (($retval = vuserPeer::doValidate($this, $columns)) !== true) {
				$failureMap = array_merge($failureMap, $retval);
			}


				if ($this->collvshows !== null) {
					foreach ($this->collvshows as $referrerFK) {
						if (!$referrerFK->validate($columns)) {
							$failureMap = array_merge($failureMap, $referrerFK->getValidationFailures());
						}
					}
				}

				if ($this->collentrys !== null) {
					foreach ($this->collentrys as $referrerFK) {
						if (!$referrerFK->validate($columns)) {
							$failureMap = array_merge($failureMap, $referrerFK->getValidationFailures());
						}
					}
				}

				if ($this->collcomments !== null) {
					foreach ($this->collcomments as $referrerFK) {
						if (!$referrerFK->validate($columns)) {
							$failureMap = array_merge($failureMap, $referrerFK->getValidationFailures());
						}
					}
				}

				if ($this->collflags !== null) {
					foreach ($this->collflags as $referrerFK) {
						if (!$referrerFK->validate($columns)) {
							$failureMap = array_merge($failureMap, $referrerFK->getValidationFailures());
						}
					}
				}

				if ($this->collfavorites !== null) {
					foreach ($this->collfavorites as $referrerFK) {
						if (!$referrerFK->validate($columns)) {
							$failureMap = array_merge($failureMap, $referrerFK->getValidationFailures());
						}
					}
				}

				if ($this->collVshowVusers !== null) {
					foreach ($this->collVshowVusers as $referrerFK) {
						if (!$referrerFK->validate($columns)) {
							$failureMap = array_merge($failureMap, $referrerFK->getValidationFailures());
						}
					}
				}

				if ($this->collPuserVusers !== null) {
					foreach ($this->collPuserVusers as $referrerFK) {
						if (!$referrerFK->validate($columns)) {
							$failureMap = array_merge($failureMap, $referrerFK->getValidationFailures());
						}
					}
				}

				if ($this->collPartners !== null) {
					foreach ($this->collPartners as $referrerFK) {
						if (!$referrerFK->validate($columns)) {
							$failureMap = array_merge($failureMap, $referrerFK->getValidationFailures());
						}
					}
				}

				if ($this->collmoderations !== null) {
					foreach ($this->collmoderations as $referrerFK) {
						if (!$referrerFK->validate($columns)) {
							$failureMap = array_merge($failureMap, $referrerFK->getValidationFailures());
						}
					}
				}

				if ($this->collmoderationFlagsRelatedByVuserId !== null) {
					foreach ($this->collmoderationFlagsRelatedByVuserId as $referrerFK) {
						if (!$referrerFK->validate($columns)) {
							$failureMap = array_merge($failureMap, $referrerFK->getValidationFailures());
						}
					}
				}

				if ($this->collmoderationFlagsRelatedByFlaggedVuserId !== null) {
					foreach ($this->collmoderationFlagsRelatedByFlaggedVuserId as $referrerFK) {
						if (!$referrerFK->validate($columns)) {
							$failureMap = array_merge($failureMap, $referrerFK->getValidationFailures());
						}
					}
				}

				if ($this->collcategoryVusers !== null) {
					foreach ($this->collcategoryVusers as $referrerFK) {
						if (!$referrerFK->validate($columns)) {
							$failureMap = array_merge($failureMap, $referrerFK->getValidationFailures());
						}
					}
				}

				if ($this->collUploadTokens !== null) {
					foreach ($this->collUploadTokens as $referrerFK) {
						if (!$referrerFK->validate($columns)) {
							$failureMap = array_merge($failureMap, $referrerFK->getValidationFailures());
						}
					}
				}

				if ($this->collVuserToUserRoles !== null) {
					foreach ($this->collVuserToUserRoles as $referrerFK) {
						if (!$referrerFK->validate($columns)) {
							$failureMap = array_merge($failureMap, $referrerFK->getValidationFailures());
						}
					}
				}

				if ($this->collVuserVgroupsRelatedByVgroupId !== null) {
					foreach ($this->collVuserVgroupsRelatedByVgroupId as $referrerFK) {
						if (!$referrerFK->validate($columns)) {
							$failureMap = array_merge($failureMap, $referrerFK->getValidationFailures());
						}
					}
				}

				if ($this->collVuserVgroupsRelatedByVuserId !== null) {
					foreach ($this->collVuserVgroupsRelatedByVuserId as $referrerFK) {
						if (!$referrerFK->validate($columns)) {
							$failureMap = array_merge($failureMap, $referrerFK->getValidationFailures());
						}
					}
				}

				if ($this->collUserEntrys !== null) {
					foreach ($this->collUserEntrys as $referrerFK) {
						if (!$referrerFK->validate($columns)) {
							$failureMap = array_merge($failureMap, $referrerFK->getValidationFailures());
						}
					}
				}


			$this->alreadyInValidation = false;
		}

		return (!empty($failureMap) ? $failureMap : true);
	}

	/**
	 * Retrieves a field from the object by name passed in as a string.
	 *
	 * @param      string $name name
	 * @param      string $type The type of fieldname the $name is of:
	 *                     one of the class type constants BasePeer::TYPE_PHPNAME, BasePeer::TYPE_STUDLYPHPNAME
	 *                     BasePeer::TYPE_COLNAME, BasePeer::TYPE_FIELDNAME, BasePeer::TYPE_NUM
	 * @return     mixed Value of field.
	 */
	public function getByName($name, $type = BasePeer::TYPE_PHPNAME)
	{
		$pos = vuserPeer::translateFieldName($name, $type, BasePeer::TYPE_NUM);
		$field = $this->getByPosition($pos);
		return $field;
	}

	/**
	 * Retrieves a field from the object by Position as specified in the xml schema.
	 * Zero-based.
	 *
	 * @param      int $pos position in xml schema
	 * @return     mixed Value of field at $pos
	 */
	public function getByPosition($pos)
	{
		switch($pos) {
			case 0:
				return $this->getId();
				break;
			case 1:
				return $this->getLoginDataId();
				break;
			case 2:
				return $this->getIsAdmin();
				break;
			case 3:
				return $this->getScreenName();
				break;
			case 4:
				return $this->getFullName();
				break;
			case 5:
				return $this->getFirstName();
				break;
			case 6:
				return $this->getLastName();
				break;
			case 7:
				return $this->getEmail();
				break;
			case 8:
				return $this->getSha1Password();
				break;
			case 9:
				return $this->getSalt();
				break;
			case 10:
				return $this->getDateOfBirth();
				break;
			case 11:
				return $this->getCountry();
				break;
			case 12:
				return $this->getState();
				break;
			case 13:
				return $this->getCity();
				break;
			case 14:
				return $this->getZip();
				break;
			case 15:
				return $this->getUrlList();
				break;
			case 16:
				return $this->getPicture();
				break;
			case 17:
				return $this->getIcon();
				break;
			case 18:
				return $this->getAboutMe();
				break;
			case 19:
				return $this->getTags();
				break;
			case 20:
				return $this->getTagline();
				break;
			case 21:
				return $this->getNetworkHighschool();
				break;
			case 22:
				return $this->getNetworkCollege();
				break;
			case 23:
				return $this->getNetworkOther();
				break;
			case 24:
				return $this->getMobileNum();
				break;
			case 25:
				return $this->getMatureContent();
				break;
			case 26:
				return $this->getGender();
				break;
			case 27:
				return $this->getRegistrationIp();
				break;
			case 28:
				return $this->getRegistrationCookie();
				break;
			case 29:
				return $this->getImList();
				break;
			case 30:
				return $this->getViews();
				break;
			case 31:
				return $this->getFans();
				break;
			case 32:
				return $this->getEntries();
				break;
			case 33:
				return $this->getStorageSize();
				break;
			case 34:
				return $this->getProducedVshows();
				break;
			case 35:
				return $this->getStatus();
				break;
			case 36:
				return $this->getCreatedAt();
				break;
			case 37:
				return $this->getUpdatedAt();
				break;
			case 38:
				return $this->getPartnerId();
				break;
			case 39:
				return $this->getDisplayInSearch();
				break;
			case 40:
				return $this->getPartnerData();
				break;
			case 41:
				return $this->getPuserId();
				break;
			case 42:
				return $this->getAdminTags();
				break;
			case 43:
				return $this->getIndexedPartnerDataInt();
				break;
			case 44:
				return $this->getIndexedPartnerDataString();
				break;
			case 45:
				return $this->getCustomData();
				break;
			case 46:
				return $this->getType();
				break;
			default:
				return null;
				break;
		} // switch()
	}

	/**
	 * Exports the object as an array.
	 *
	 * You can specify the key type of the array by passing one of the class
	 * type constants.
	 *
	 * @param      string $keyType (optional) One of the class type constants BasePeer::TYPE_PHPNAME, BasePeer::TYPE_STUDLYPHPNAME
	 *                        BasePeer::TYPE_COLNAME, BasePeer::TYPE_FIELDNAME, BasePeer::TYPE_NUM. Defaults to BasePeer::TYPE_PHPNAME.
	 * @param      boolean $includeLazyLoadColumns (optional) Whether to include lazy loaded columns.  Defaults to TRUE.
	 * @return     an associative array containing the field names (as keys) and field values
	 */
	public function toArray($keyType = BasePeer::TYPE_PHPNAME, $includeLazyLoadColumns = true)
	{
		$keys = vuserPeer::getFieldNames($keyType);
		$result = array(
			$keys[0] => $this->getId(),
			$keys[1] => $this->getLoginDataId(),
			$keys[2] => $this->getIsAdmin(),
			$keys[3] => $this->getScreenName(),
			$keys[4] => $this->getFullName(),
			$keys[5] => $this->getFirstName(),
			$keys[6] => $this->getLastName(),
			$keys[7] => $this->getEmail(),
			$keys[8] => $this->getSha1Password(),
			$keys[9] => $this->getSalt(),
			$keys[10] => $this->getDateOfBirth(),
			$keys[11] => $this->getCountry(),
			$keys[12] => $this->getState(),
			$keys[13] => $this->getCity(),
			$keys[14] => $this->getZip(),
			$keys[15] => $this->getUrlList(),
			$keys[16] => $this->getPicture(),
			$keys[17] => $this->getIcon(),
			$keys[18] => $this->getAboutMe(),
			$keys[19] => $this->getTags(),
			$keys[20] => $this->getTagline(),
			$keys[21] => $this->getNetworkHighschool(),
			$keys[22] => $this->getNetworkCollege(),
			$keys[23] => $this->getNetworkOther(),
			$keys[24] => $this->getMobileNum(),
			$keys[25] => $this->getMatureContent(),
			$keys[26] => $this->getGender(),
			$keys[27] => $this->getRegistrationIp(),
			$keys[28] => $this->getRegistrationCookie(),
			$keys[29] => $this->getImList(),
			$keys[30] => $this->getViews(),
			$keys[31] => $this->getFans(),
			$keys[32] => $this->getEntries(),
			$keys[33] => $this->getStorageSize(),
			$keys[34] => $this->getProducedVshows(),
			$keys[35] => $this->getStatus(),
			$keys[36] => $this->getCreatedAt(),
			$keys[37] => $this->getUpdatedAt(),
			$keys[38] => $this->getPartnerId(),
			$keys[39] => $this->getDisplayInSearch(),
			$keys[40] => $this->getPartnerData(),
			$keys[41] => $this->getPuserId(),
			$keys[42] => $this->getAdminTags(),
			$keys[43] => $this->getIndexedPartnerDataInt(),
			$keys[44] => $this->getIndexedPartnerDataString(),
			$keys[45] => $this->getCustomData(),
			$keys[46] => $this->getType(),
		);
		return $result;
	}

	/**
	 * Sets a field from the object by name passed in as a string.
	 *
	 * @param      string $name peer name
	 * @param      mixed $value field value
	 * @param      string $type The type of fieldname the $name is of:
	 *                     one of the class type constants BasePeer::TYPE_PHPNAME, BasePeer::TYPE_STUDLYPHPNAME
	 *                     BasePeer::TYPE_COLNAME, BasePeer::TYPE_FIELDNAME, BasePeer::TYPE_NUM
	 * @return     void
	 */
	public function setByName($name, $value, $type = BasePeer::TYPE_PHPNAME)
	{
		$pos = vuserPeer::translateFieldName($name, $type, BasePeer::TYPE_NUM);
		return $this->setByPosition($pos, $value);
	}

	/**
	 * Sets a field from the object by Position as specified in the xml schema.
	 * Zero-based.
	 *
	 * @param      int $pos position in xml schema
	 * @param      mixed $value field value
	 * @return     void
	 */
	public function setByPosition($pos, $value)
	{
		switch($pos) {
			case 0:
				$this->setId($value);
				break;
			case 1:
				$this->setLoginDataId($value);
				break;
			case 2:
				$this->setIsAdmin($value);
				break;
			case 3:
				$this->setScreenName($value);
				break;
			case 4:
				$this->setFullName($value);
				break;
			case 5:
				$this->setFirstName($value);
				break;
			case 6:
				$this->setLastName($value);
				break;
			case 7:
				$this->setEmail($value);
				break;
			case 8:
				$this->setSha1Password($value);
				break;
			case 9:
				$this->setSalt($value);
				break;
			case 10:
				$this->setDateOfBirth($value);
				break;
			case 11:
				$this->setCountry($value);
				break;
			case 12:
				$this->setState($value);
				break;
			case 13:
				$this->setCity($value);
				break;
			case 14:
				$this->setZip($value);
				break;
			case 15:
				$this->setUrlList($value);
				break;
			case 16:
				$this->setPicture($value);
				break;
			case 17:
				$this->setIcon($value);
				break;
			case 18:
				$this->setAboutMe($value);
				break;
			case 19:
				$this->setTags($value);
				break;
			case 20:
				$this->setTagline($value);
				break;
			case 21:
				$this->setNetworkHighschool($value);
				break;
			case 22:
				$this->setNetworkCollege($value);
				break;
			case 23:
				$this->setNetworkOther($value);
				break;
			case 24:
				$this->setMobileNum($value);
				break;
			case 25:
				$this->setMatureContent($value);
				break;
			case 26:
				$this->setGender($value);
				break;
			case 27:
				$this->setRegistrationIp($value);
				break;
			case 28:
				$this->setRegistrationCookie($value);
				break;
			case 29:
				$this->setImList($value);
				break;
			case 30:
				$this->setViews($value);
				break;
			case 31:
				$this->setFans($value);
				break;
			case 32:
				$this->setEntries($value);
				break;
			case 33:
				$this->setStorageSize($value);
				break;
			case 34:
				$this->setProducedVshows($value);
				break;
			case 35:
				$this->setStatus($value);
				break;
			case 36:
				$this->setCreatedAt($value);
				break;
			case 37:
				$this->setUpdatedAt($value);
				break;
			case 38:
				$this->setPartnerId($value);
				break;
			case 39:
				$this->setDisplayInSearch($value);
				break;
			case 40:
				$this->setPartnerData($value);
				break;
			case 41:
				$this->setPuserId($value);
				break;
			case 42:
				$this->setAdminTags($value);
				break;
			case 43:
				$this->setIndexedPartnerDataInt($value);
				break;
			case 44:
				$this->setIndexedPartnerDataString($value);
				break;
			case 45:
				$this->setCustomData($value);
				break;
			case 46:
				$this->setType($value);
				break;
		} // switch()
	}

	/**
	 * Populates the object using an array.
	 *
	 * This is particularly useful when populating an object from one of the
	 * request arrays (e.g. $_POST).  This method goes through the column
	 * names, checking to see whether a matching key exists in populated
	 * array. If so the setByName() method is called for that column.
	 *
	 * You can specify the key type of the array by additionally passing one
	 * of the class type constants BasePeer::TYPE_PHPNAME, BasePeer::TYPE_STUDLYPHPNAME,
	 * BasePeer::TYPE_COLNAME, BasePeer::TYPE_FIELDNAME, BasePeer::TYPE_NUM.
	 * The default key type is the column's phpname (e.g. 'AuthorId')
	 *
	 * @param      array  $arr     An array to populate the object from.
	 * @param      string $keyType The type of keys the array uses.
	 * @return     void
	 */
	public function fromArray($arr, $keyType = BasePeer::TYPE_PHPNAME)
	{
		$keys = vuserPeer::getFieldNames($keyType);

		if (array_key_exists($keys[0], $arr)) $this->setId($arr[$keys[0]]);
		if (array_key_exists($keys[1], $arr)) $this->setLoginDataId($arr[$keys[1]]);
		if (array_key_exists($keys[2], $arr)) $this->setIsAdmin($arr[$keys[2]]);
		if (array_key_exists($keys[3], $arr)) $this->setScreenName($arr[$keys[3]]);
		if (array_key_exists($keys[4], $arr)) $this->setFullName($arr[$keys[4]]);
		if (array_key_exists($keys[5], $arr)) $this->setFirstName($arr[$keys[5]]);
		if (array_key_exists($keys[6], $arr)) $this->setLastName($arr[$keys[6]]);
		if (array_key_exists($keys[7], $arr)) $this->setEmail($arr[$keys[7]]);
		if (array_key_exists($keys[8], $arr)) $this->setSha1Password($arr[$keys[8]]);
		if (array_key_exists($keys[9], $arr)) $this->setSalt($arr[$keys[9]]);
		if (array_key_exists($keys[10], $arr)) $this->setDateOfBirth($arr[$keys[10]]);
		if (array_key_exists($keys[11], $arr)) $this->setCountry($arr[$keys[11]]);
		if (array_key_exists($keys[12], $arr)) $this->setState($arr[$keys[12]]);
		if (array_key_exists($keys[13], $arr)) $this->setCity($arr[$keys[13]]);
		if (array_key_exists($keys[14], $arr)) $this->setZip($arr[$keys[14]]);
		if (array_key_exists($keys[15], $arr)) $this->setUrlList($arr[$keys[15]]);
		if (array_key_exists($keys[16], $arr)) $this->setPicture($arr[$keys[16]]);
		if (array_key_exists($keys[17], $arr)) $this->setIcon($arr[$keys[17]]);
		if (array_key_exists($keys[18], $arr)) $this->setAboutMe($arr[$keys[18]]);
		if (array_key_exists($keys[19], $arr)) $this->setTags($arr[$keys[19]]);
		if (array_key_exists($keys[20], $arr)) $this->setTagline($arr[$keys[20]]);
		if (array_key_exists($keys[21], $arr)) $this->setNetworkHighschool($arr[$keys[21]]);
		if (array_key_exists($keys[22], $arr)) $this->setNetworkCollege($arr[$keys[22]]);
		if (array_key_exists($keys[23], $arr)) $this->setNetworkOther($arr[$keys[23]]);
		if (array_key_exists($keys[24], $arr)) $this->setMobileNum($arr[$keys[24]]);
		if (array_key_exists($keys[25], $arr)) $this->setMatureContent($arr[$keys[25]]);
		if (array_key_exists($keys[26], $arr)) $this->setGender($arr[$keys[26]]);
		if (array_key_exists($keys[27], $arr)) $this->setRegistrationIp($arr[$keys[27]]);
		if (array_key_exists($keys[28], $arr)) $this->setRegistrationCookie($arr[$keys[28]]);
		if (array_key_exists($keys[29], $arr)) $this->setImList($arr[$keys[29]]);
		if (array_key_exists($keys[30], $arr)) $this->setViews($arr[$keys[30]]);
		if (array_key_exists($keys[31], $arr)) $this->setFans($arr[$keys[31]]);
		if (array_key_exists($keys[32], $arr)) $this->setEntries($arr[$keys[32]]);
		if (array_key_exists($keys[33], $arr)) $this->setStorageSize($arr[$keys[33]]);
		if (array_key_exists($keys[34], $arr)) $this->setProducedVshows($arr[$keys[34]]);
		if (array_key_exists($keys[35], $arr)) $this->setStatus($arr[$keys[35]]);
		if (array_key_exists($keys[36], $arr)) $this->setCreatedAt($arr[$keys[36]]);
		if (array_key_exists($keys[37], $arr)) $this->setUpdatedAt($arr[$keys[37]]);
		if (array_key_exists($keys[38], $arr)) $this->setPartnerId($arr[$keys[38]]);
		if (array_key_exists($keys[39], $arr)) $this->setDisplayInSearch($arr[$keys[39]]);
		if (array_key_exists($keys[40], $arr)) $this->setPartnerData($arr[$keys[40]]);
		if (array_key_exists($keys[41], $arr)) $this->setPuserId($arr[$keys[41]]);
		if (array_key_exists($keys[42], $arr)) $this->setAdminTags($arr[$keys[42]]);
		if (array_key_exists($keys[43], $arr)) $this->setIndexedPartnerDataInt($arr[$keys[43]]);
		if (array_key_exists($keys[44], $arr)) $this->setIndexedPartnerDataString($arr[$keys[44]]);
		if (array_key_exists($keys[45], $arr)) $this->setCustomData($arr[$keys[45]]);
		if (array_key_exists($keys[46], $arr)) $this->setType($arr[$keys[46]]);
	}

	/**
	 * Build a Criteria object containing the values of all modified columns in this object.
	 *
	 * @return     Criteria The Criteria object containing all modified values.
	 */
	public function buildCriteria()
	{
		$criteria = new Criteria(vuserPeer::DATABASE_NAME);

		if ($this->isColumnModified(vuserPeer::ID)) $criteria->add(vuserPeer::ID, $this->id);
		if ($this->isColumnModified(vuserPeer::LOGIN_DATA_ID)) $criteria->add(vuserPeer::LOGIN_DATA_ID, $this->login_data_id);
		if ($this->isColumnModified(vuserPeer::IS_ADMIN)) $criteria->add(vuserPeer::IS_ADMIN, $this->is_admin);
		if ($this->isColumnModified(vuserPeer::SCREEN_NAME)) $criteria->add(vuserPeer::SCREEN_NAME, $this->screen_name);
		if ($this->isColumnModified(vuserPeer::FULL_NAME)) $criteria->add(vuserPeer::FULL_NAME, $this->full_name);
		if ($this->isColumnModified(vuserPeer::FIRST_NAME)) $criteria->add(vuserPeer::FIRST_NAME, $this->first_name);
		if ($this->isColumnModified(vuserPeer::LAST_NAME)) $criteria->add(vuserPeer::LAST_NAME, $this->last_name);
		if ($this->isColumnModified(vuserPeer::EMAIL)) $criteria->add(vuserPeer::EMAIL, $this->email);
		if ($this->isColumnModified(vuserPeer::SHA1_PASSWORD)) $criteria->add(vuserPeer::SHA1_PASSWORD, $this->sha1_password);
		if ($this->isColumnModified(vuserPeer::SALT)) $criteria->add(vuserPeer::SALT, $this->salt);
		if ($this->isColumnModified(vuserPeer::DATE_OF_BIRTH)) $criteria->add(vuserPeer::DATE_OF_BIRTH, $this->date_of_birth);
		if ($this->isColumnModified(vuserPeer::COUNTRY)) $criteria->add(vuserPeer::COUNTRY, $this->country);
		if ($this->isColumnModified(vuserPeer::STATE)) $criteria->add(vuserPeer::STATE, $this->state);
		if ($this->isColumnModified(vuserPeer::CITY)) $criteria->add(vuserPeer::CITY, $this->city);
		if ($this->isColumnModified(vuserPeer::ZIP)) $criteria->add(vuserPeer::ZIP, $this->zip);
		if ($this->isColumnModified(vuserPeer::URL_LIST)) $criteria->add(vuserPeer::URL_LIST, $this->url_list);
		if ($this->isColumnModified(vuserPeer::PICTURE)) $criteria->add(vuserPeer::PICTURE, $this->picture);
		if ($this->isColumnModified(vuserPeer::ICON)) $criteria->add(vuserPeer::ICON, $this->icon);
		if ($this->isColumnModified(vuserPeer::ABOUT_ME)) $criteria->add(vuserPeer::ABOUT_ME, $this->about_me);
		if ($this->isColumnModified(vuserPeer::TAGS)) $criteria->add(vuserPeer::TAGS, $this->tags);
		if ($this->isColumnModified(vuserPeer::TAGLINE)) $criteria->add(vuserPeer::TAGLINE, $this->tagline);
		if ($this->isColumnModified(vuserPeer::NETWORK_HIGHSCHOOL)) $criteria->add(vuserPeer::NETWORK_HIGHSCHOOL, $this->network_highschool);
		if ($this->isColumnModified(vuserPeer::NETWORK_COLLEGE)) $criteria->add(vuserPeer::NETWORK_COLLEGE, $this->network_college);
		if ($this->isColumnModified(vuserPeer::NETWORK_OTHER)) $criteria->add(vuserPeer::NETWORK_OTHER, $this->network_other);
		if ($this->isColumnModified(vuserPeer::MOBILE_NUM)) $criteria->add(vuserPeer::MOBILE_NUM, $this->mobile_num);
		if ($this->isColumnModified(vuserPeer::MATURE_CONTENT)) $criteria->add(vuserPeer::MATURE_CONTENT, $this->mature_content);
		if ($this->isColumnModified(vuserPeer::GENDER)) $criteria->add(vuserPeer::GENDER, $this->gender);
		if ($this->isColumnModified(vuserPeer::REGISTRATION_IP)) $criteria->add(vuserPeer::REGISTRATION_IP, $this->registration_ip);
		if ($this->isColumnModified(vuserPeer::REGISTRATION_COOKIE)) $criteria->add(vuserPeer::REGISTRATION_COOKIE, $this->registration_cookie);
		if ($this->isColumnModified(vuserPeer::IM_LIST)) $criteria->add(vuserPeer::IM_LIST, $this->im_list);
		if ($this->isColumnModified(vuserPeer::VIEWS)) $criteria->add(vuserPeer::VIEWS, $this->views);
		if ($this->isColumnModified(vuserPeer::FANS)) $criteria->add(vuserPeer::FANS, $this->fans);
		if ($this->isColumnModified(vuserPeer::ENTRIES)) $criteria->add(vuserPeer::ENTRIES, $this->entries);
		if ($this->isColumnModified(vuserPeer::STORAGE_SIZE)) $criteria->add(vuserPeer::STORAGE_SIZE, $this->storage_size);
		if ($this->isColumnModified(vuserPeer::PRODUCED_VSHOWS)) $criteria->add(vuserPeer::PRODUCED_VSHOWS, $this->produced_vshows);
		if ($this->isColumnModified(vuserPeer::STATUS)) $criteria->add(vuserPeer::STATUS, $this->status);
		if ($this->isColumnModified(vuserPeer::CREATED_AT)) $criteria->add(vuserPeer::CREATED_AT, $this->created_at);
		if ($this->isColumnModified(vuserPeer::UPDATED_AT)) $criteria->add(vuserPeer::UPDATED_AT, $this->updated_at);
		if ($this->isColumnModified(vuserPeer::PARTNER_ID)) $criteria->add(vuserPeer::PARTNER_ID, $this->partner_id);
		if ($this->isColumnModified(vuserPeer::DISPLAY_IN_SEARCH)) $criteria->add(vuserPeer::DISPLAY_IN_SEARCH, $this->display_in_search);
		if ($this->isColumnModified(vuserPeer::PARTNER_DATA)) $criteria->add(vuserPeer::PARTNER_DATA, $this->partner_data);
		if ($this->isColumnModified(vuserPeer::PUSER_ID)) $criteria->add(vuserPeer::PUSER_ID, $this->puser_id);
		if ($this->isColumnModified(vuserPeer::ADMIN_TAGS)) $criteria->add(vuserPeer::ADMIN_TAGS, $this->admin_tags);
		if ($this->isColumnModified(vuserPeer::INDEXED_PARTNER_DATA_INT)) $criteria->add(vuserPeer::INDEXED_PARTNER_DATA_INT, $this->indexed_partner_data_int);
		if ($this->isColumnModified(vuserPeer::INDEXED_PARTNER_DATA_STRING)) $criteria->add(vuserPeer::INDEXED_PARTNER_DATA_STRING, $this->indexed_partner_data_string);
		if ($this->isColumnModified(vuserPeer::CUSTOM_DATA)) $criteria->add(vuserPeer::CUSTOM_DATA, $this->custom_data);
		if ($this->isColumnModified(vuserPeer::TYPE)) $criteria->add(vuserPeer::TYPE, $this->type);

		return $criteria;
	}

	/**
	 * Builds a Criteria object containing the primary key for this object.
	 *
	 * Unlike buildCriteria() this method includes the primary key values regardless
	 * of whether or not they have been modified.
	 *
	 * @return     Criteria The Criteria object containing value(s) for primary key(s).
	 */
	public function buildPkeyCriteria()
	{
		$criteria = new Criteria(vuserPeer::DATABASE_NAME);

		$criteria->add(vuserPeer::ID, $this->id);
		
		if($this->alreadyInSave)
		{
			if ($this->isColumnModified(vuserPeer::CUSTOM_DATA))
			{
				if (!is_null($this->custom_data_md5))
					$criteria->add(vuserPeer::CUSTOM_DATA, "MD5(cast(" . vuserPeer::CUSTOM_DATA . " as char character set latin1)) = '$this->custom_data_md5'", Criteria::CUSTOM);
					//casting to latin char set to avoid mysql and php md5 difference
				else 
					$criteria->add(vuserPeer::CUSTOM_DATA, NULL, Criteria::ISNULL);
			}
			
			if (count($this->modifiedColumns) == 2 && $this->isColumnModified(vuserPeer::UPDATED_AT))
			{
				$theModifiedColumn = null;
				foreach($this->modifiedColumns as $modifiedColumn)
					if($modifiedColumn != vuserPeer::UPDATED_AT)
						$theModifiedColumn = $modifiedColumn;
						
				$atomicColumns = vuserPeer::getAtomicColumns();
				if(in_array($theModifiedColumn, $atomicColumns))
					$criteria->add($theModifiedColumn, $this->getByName($theModifiedColumn, BasePeer::TYPE_COLNAME), Criteria::NOT_EQUAL);
			}
		}		

		return $criteria;
	}

	/**
	 * Returns the primary key for this object (row).
	 * @return     int
	 */
	public function getPrimaryKey()
	{
		return $this->getId();
	}

	/**
	 * Generic method to set the primary key (id column).
	 *
	 * @param      int $key Primary key.
	 * @return     void
	 */
	public function setPrimaryKey($key)
	{
		$this->setId($key);
	}

	/**
	 * Sets contents of passed object to values from current object.
	 *
	 * If desired, this method can also make copies of all associated (fkey referrers)
	 * objects.
	 *
	 * @param      object $copyObj An object of vuser (or compatible) type.
	 * @param      boolean $deepCopy Whether to also copy all rows that refer (by fkey) to the current row.
	 * @throws     PropelException
	 */
	public function copyInto($copyObj, $deepCopy = false)
	{

		$copyObj->setLoginDataId($this->login_data_id);

		$copyObj->setIsAdmin($this->is_admin);

		$copyObj->setScreenName($this->screen_name);

		$copyObj->setFullName($this->full_name);

		$copyObj->setFirstName($this->first_name);

		$copyObj->setLastName($this->last_name);

		$copyObj->setEmail($this->email);

		$copyObj->setSha1Password($this->sha1_password);

		$copyObj->setSalt($this->salt);

		$copyObj->setDateOfBirth($this->date_of_birth);

		$copyObj->setCountry($this->country);

		$copyObj->setState($this->state);

		$copyObj->setCity($this->city);

		$copyObj->setZip($this->zip);

		$copyObj->setUrlList($this->url_list);

		$copyObj->setPicture($this->picture);

		$copyObj->setIcon($this->icon);

		$copyObj->setAboutMe($this->about_me);

		$copyObj->setTags($this->tags);

		$copyObj->setTagline($this->tagline);

		$copyObj->setNetworkHighschool($this->network_highschool);

		$copyObj->setNetworkCollege($this->network_college);

		$copyObj->setNetworkOther($this->network_other);

		$copyObj->setMobileNum($this->mobile_num);

		$copyObj->setMatureContent($this->mature_content);

		$copyObj->setGender($this->gender);

		$copyObj->setRegistrationIp($this->registration_ip);

		$copyObj->setRegistrationCookie($this->registration_cookie);

		$copyObj->setImList($this->im_list);

		$copyObj->setViews($this->views);

		$copyObj->setFans($this->fans);

		$copyObj->setEntries($this->entries);

		$copyObj->setStorageSize($this->storage_size);

		$copyObj->setProducedVshows($this->produced_vshows);

		$copyObj->setStatus($this->status);

		$copyObj->setCreatedAt($this->created_at);

		$copyObj->setUpdatedAt($this->updated_at);

		$copyObj->setPartnerId($this->partner_id);

		$copyObj->setDisplayInSearch($this->display_in_search);

		$copyObj->setPartnerData($this->partner_data);

		$copyObj->setPuserId($this->puser_id);

		$copyObj->setAdminTags($this->admin_tags);

		$copyObj->setIndexedPartnerDataInt($this->indexed_partner_data_int);

		$copyObj->setIndexedPartnerDataString($this->indexed_partner_data_string);

		$copyObj->setCustomData($this->custom_data);

		$copyObj->setType($this->type);


		if ($deepCopy) {
			// important: temporarily setNew(false) because this affects the behavior of
			// the getter/setter methods for fkey referrer objects.
			$copyObj->setNew(false);

			foreach ($this->getvshows() as $relObj) {
				if ($relObj !== $this) {  // ensure that we don't try to copy a reference to ourselves
					$copyObj->addvshow($relObj->copy($deepCopy));
				}
			}

			foreach ($this->getentrys() as $relObj) {
				if ($relObj !== $this) {  // ensure that we don't try to copy a reference to ourselves
					$copyObj->addentry($relObj->copy($deepCopy));
				}
			}

			foreach ($this->getcomments() as $relObj) {
				if ($relObj !== $this) {  // ensure that we don't try to copy a reference to ourselves
					$copyObj->addcomment($relObj->copy($deepCopy));
				}
			}

			foreach ($this->getflags() as $relObj) {
				if ($relObj !== $this) {  // ensure that we don't try to copy a reference to ourselves
					$copyObj->addflag($relObj->copy($deepCopy));
				}
			}

			foreach ($this->getfavorites() as $relObj) {
				if ($relObj !== $this) {  // ensure that we don't try to copy a reference to ourselves
					$copyObj->addfavorite($relObj->copy($deepCopy));
				}
			}

			foreach ($this->getVshowVusers() as $relObj) {
				if ($relObj !== $this) {  // ensure that we don't try to copy a reference to ourselves
					$copyObj->addVshowVuser($relObj->copy($deepCopy));
				}
			}

			foreach ($this->getPuserVusers() as $relObj) {
				if ($relObj !== $this) {  // ensure that we don't try to copy a reference to ourselves
					$copyObj->addPuserVuser($relObj->copy($deepCopy));
				}
			}

			foreach ($this->getPartners() as $relObj) {
				if ($relObj !== $this) {  // ensure that we don't try to copy a reference to ourselves
					$copyObj->addPartner($relObj->copy($deepCopy));
				}
			}

			foreach ($this->getmoderations() as $relObj) {
				if ($relObj !== $this) {  // ensure that we don't try to copy a reference to ourselves
					$copyObj->addmoderation($relObj->copy($deepCopy));
				}
			}

			foreach ($this->getmoderationFlagsRelatedByVuserId() as $relObj) {
				if ($relObj !== $this) {  // ensure that we don't try to copy a reference to ourselves
					$copyObj->addmoderationFlagRelatedByVuserId($relObj->copy($deepCopy));
				}
			}

			foreach ($this->getmoderationFlagsRelatedByFlaggedVuserId() as $relObj) {
				if ($relObj !== $this) {  // ensure that we don't try to copy a reference to ourselves
					$copyObj->addmoderationFlagRelatedByFlaggedVuserId($relObj->copy($deepCopy));
				}
			}

			foreach ($this->getcategoryVusers() as $relObj) {
				if ($relObj !== $this) {  // ensure that we don't try to copy a reference to ourselves
					$copyObj->addcategoryVuser($relObj->copy($deepCopy));
				}
			}

			foreach ($this->getUploadTokens() as $relObj) {
				if ($relObj !== $this) {  // ensure that we don't try to copy a reference to ourselves
					$copyObj->addUploadToken($relObj->copy($deepCopy));
				}
			}

			foreach ($this->getVuserToUserRoles() as $relObj) {
				if ($relObj !== $this) {  // ensure that we don't try to copy a reference to ourselves
					$copyObj->addVuserToUserRole($relObj->copy($deepCopy));
				}
			}

			foreach ($this->getVuserVgroupsRelatedByVgroupId() as $relObj) {
				if ($relObj !== $this) {  // ensure that we don't try to copy a reference to ourselves
					$copyObj->addVuserVgroupRelatedByVgroupId($relObj->copy($deepCopy));
				}
			}

			foreach ($this->getVuserVgroupsRelatedByVuserId() as $relObj) {
				if ($relObj !== $this) {  // ensure that we don't try to copy a reference to ourselves
					$copyObj->addVuserVgroupRelatedByVuserId($relObj->copy($deepCopy));
				}
			}

			foreach ($this->getUserEntrys() as $relObj) {
				if ($relObj !== $this) {  // ensure that we don't try to copy a reference to ourselves
					$copyObj->addUserEntry($relObj->copy($deepCopy));
				}
			}

		} // if ($deepCopy)


		$copyObj->setNew(true);

		$copyObj->setId(NULL); // this is a auto-increment column, so set to default value

	}

	/**
	 * Makes a copy of this object that will be inserted as a new row in table when saved.
	 * It creates a new object filling in the simple attributes, but skipping any primary
	 * keys that are defined for the table.
	 *
	 * If desired, this method can also make copies of all associated (fkey referrers)
	 * objects.
	 *
	 * @param      boolean $deepCopy Whether to also copy all rows that refer (by fkey) to the current row.
	 * @return     vuser Clone of current object.
	 * @throws     PropelException
	 */
	public function copy($deepCopy = false)
	{
		// we use get_class(), because this might be a subclass
		$clazz = get_class($this);
		$copyObj = new $clazz();
		$this->copyInto($copyObj, $deepCopy);
		$copyObj->setCopiedFrom($this);
		return $copyObj;
	}
	
	/**
	 * Stores the source object that this object copied from 
	 *
	 * @var     vuser Clone of current object.
	 */
	protected $copiedFrom = null;
	
	/**
	 * Stores the source object that this object copied from 
	 *
	 * @param      vuser $copiedFrom Clone of current object.
	 */
	public function setCopiedFrom(vuser $copiedFrom)
	{
		$this->copiedFrom = $copiedFrom;
	}

	/**
	 * Returns a peer instance associated with this om.
	 *
	 * Since Peer classes are not to have any instance attributes, this method returns the
	 * same instance for all member of this class. The method could therefore
	 * be static, but this would prevent one from overriding the behavior.
	 *
	 * @return     vuserPeer
	 */
	public function getPeer()
	{
		if (self::$peer === null) {
			self::$peer = new vuserPeer();
		}
		return self::$peer;
	}

	/**
	 * Clears out the collvshows collection (array).
	 *
	 * This does not modify the database; however, it will remove any associated objects, causing
	 * them to be refetched by subsequent calls to accessor method.
	 *
	 * @return     void
	 * @see        addvshows()
	 */
	public function clearvshows()
	{
		$this->collvshows = null; // important to set this to NULL since that means it is uninitialized
	}

	/**
	 * Initializes the collvshows collection (array).
	 *
	 * By default this just sets the collvshows collection to an empty array (like clearcollvshows());
	 * however, you may wish to override this method in your stub class to provide setting appropriate
	 * to your application -- for example, setting the initial array to the values stored in database.
	 *
	 * @return     void
	 */
	public function initvshows()
	{
		$this->collvshows = array();
	}

	/**
	 * Gets an array of vshow objects which contain a foreign key that references this object.
	 *
	 * If this collection has already been initialized with an identical Criteria, it returns the collection.
	 * Otherwise if this vuser has previously been saved, it will retrieve
	 * related vshows from storage. If this vuser is new, it will return
	 * an empty collection or the current collection, the criteria is ignored on a new object.
	 *
	 * @param      PropelPDO $con
	 * @param      Criteria $criteria
	 * @return     array vshow[]
	 * @throws     PropelException
	 */
	public function getvshows($criteria = null, PropelPDO $con = null)
	{
		if ($criteria === null) {
			$criteria = new Criteria(vuserPeer::DATABASE_NAME);
		}
		elseif ($criteria instanceof Criteria)
		{
			$criteria = clone $criteria;
		}

		if ($this->collvshows === null) {
			if ($this->isNew()) {
			   $this->collvshows = array();
			} else {

				$criteria->add(vshowPeer::PRODUCER_ID, $this->id);

				vshowPeer::addSelectColumns($criteria);
				$this->collvshows = vshowPeer::doSelect($criteria, $con);
			}
		} else {
			// criteria has no effect for a new object
			if (!$this->isNew()) {
				// the following code is to determine if a new query is
				// called for.  If the criteria is the same as the last
				// one, just return the collection.


				$criteria->add(vshowPeer::PRODUCER_ID, $this->id);

				vshowPeer::addSelectColumns($criteria);
				if (!isset($this->lastvshowCriteria) || !$this->lastvshowCriteria->equals($criteria)) {
					$this->collvshows = vshowPeer::doSelect($criteria, $con);
				}
			}
		}
		$this->lastvshowCriteria = $criteria;
		return $this->collvshows;
	}

	/**
	 * Returns the number of related vshow objects.
	 *
	 * @param      Criteria $criteria
	 * @param      boolean $distinct
	 * @param      PropelPDO $con
	 * @return     int Count of related vshow objects.
	 * @throws     PropelException
	 */
	public function countvshows(Criteria $criteria = null, $distinct = false, PropelPDO $con = null)
	{
		if ($criteria === null) {
			$criteria = new Criteria(vuserPeer::DATABASE_NAME);
		} else {
			$criteria = clone $criteria;
		}

		if ($distinct) {
			$criteria->setDistinct();
		}

		$count = null;

		if ($this->collvshows === null) {
			if ($this->isNew()) {
				$count = 0;
			} else {

				$criteria->add(vshowPeer::PRODUCER_ID, $this->id);

				$count = vshowPeer::doCount($criteria, false, $con);
			}
		} else {
			// criteria has no effect for a new object
			if (!$this->isNew()) {
				// the following code is to determine if a new query is
				// called for.  If the criteria is the same as the last
				// one, just return count of the collection.


				$criteria->add(vshowPeer::PRODUCER_ID, $this->id);

				if (!isset($this->lastvshowCriteria) || !$this->lastvshowCriteria->equals($criteria)) {
					$count = vshowPeer::doCount($criteria, false, $con);
				} else {
					$count = count($this->collvshows);
				}
			} else {
				$count = count($this->collvshows);
			}
		}
		return $count;
	}

	/**
	 * Method called to associate a vshow object to this object
	 * through the vshow foreign key attribute.
	 *
	 * @param      vshow $l vshow
	 * @return     void
	 * @throws     PropelException
	 */
	public function addvshow(vshow $l)
	{
		if ($this->collvshows === null) {
			$this->initvshows();
		}
		if (!in_array($l, $this->collvshows, true)) { // only add it if the **same** object is not already associated
			array_push($this->collvshows, $l);
			$l->setvuser($this);
		}
	}

	/**
	 * Clears out the collentrys collection (array).
	 *
	 * This does not modify the database; however, it will remove any associated objects, causing
	 * them to be refetched by subsequent calls to accessor method.
	 *
	 * @return     void
	 * @see        addentrys()
	 */
	public function clearentrys()
	{
		$this->collentrys = null; // important to set this to NULL since that means it is uninitialized
	}

	/**
	 * Initializes the collentrys collection (array).
	 *
	 * By default this just sets the collentrys collection to an empty array (like clearcollentrys());
	 * however, you may wish to override this method in your stub class to provide setting appropriate
	 * to your application -- for example, setting the initial array to the values stored in database.
	 *
	 * @return     void
	 */
	public function initentrys()
	{
		$this->collentrys = array();
	}

	/**
	 * Gets an array of entry objects which contain a foreign key that references this object.
	 *
	 * If this collection has already been initialized with an identical Criteria, it returns the collection.
	 * Otherwise if this vuser has previously been saved, it will retrieve
	 * related entrys from storage. If this vuser is new, it will return
	 * an empty collection or the current collection, the criteria is ignored on a new object.
	 *
	 * @param      PropelPDO $con
	 * @param      Criteria $criteria
	 * @return     array entry[]
	 * @throws     PropelException
	 */
	public function getentrys($criteria = null, PropelPDO $con = null)
	{
		if ($criteria === null) {
			$criteria = new Criteria(vuserPeer::DATABASE_NAME);
		}
		elseif ($criteria instanceof Criteria)
		{
			$criteria = clone $criteria;
		}

		if ($this->collentrys === null) {
			if ($this->isNew()) {
			   $this->collentrys = array();
			} else {

				$criteria->add(entryPeer::VUSER_ID, $this->id);

				entryPeer::addSelectColumns($criteria);
				$this->collentrys = entryPeer::doSelect($criteria, $con);
			}
		} else {
			// criteria has no effect for a new object
			if (!$this->isNew()) {
				// the following code is to determine if a new query is
				// called for.  If the criteria is the same as the last
				// one, just return the collection.


				$criteria->add(entryPeer::VUSER_ID, $this->id);

				entryPeer::addSelectColumns($criteria);
				if (!isset($this->lastentryCriteria) || !$this->lastentryCriteria->equals($criteria)) {
					$this->collentrys = entryPeer::doSelect($criteria, $con);
				}
			}
		}
		$this->lastentryCriteria = $criteria;
		return $this->collentrys;
	}

	/**
	 * Returns the number of related entry objects.
	 *
	 * @param      Criteria $criteria
	 * @param      boolean $distinct
	 * @param      PropelPDO $con
	 * @return     int Count of related entry objects.
	 * @throws     PropelException
	 */
	public function countentrys(Criteria $criteria = null, $distinct = false, PropelPDO $con = null)
	{
		if ($criteria === null) {
			$criteria = new Criteria(vuserPeer::DATABASE_NAME);
		} else {
			$criteria = clone $criteria;
		}

		if ($distinct) {
			$criteria->setDistinct();
		}

		$count = null;

		if ($this->collentrys === null) {
			if ($this->isNew()) {
				$count = 0;
			} else {

				$criteria->add(entryPeer::VUSER_ID, $this->id);

				$count = entryPeer::doCount($criteria, false, $con);
			}
		} else {
			// criteria has no effect for a new object
			if (!$this->isNew()) {
				// the following code is to determine if a new query is
				// called for.  If the criteria is the same as the last
				// one, just return count of the collection.


				$criteria->add(entryPeer::VUSER_ID, $this->id);

				if (!isset($this->lastentryCriteria) || !$this->lastentryCriteria->equals($criteria)) {
					$count = entryPeer::doCount($criteria, false, $con);
				} else {
					$count = count($this->collentrys);
				}
			} else {
				$count = count($this->collentrys);
			}
		}
		return $count;
	}

	/**
	 * Method called to associate a entry object to this object
	 * through the entry foreign key attribute.
	 *
	 * @param      entry $l entry
	 * @return     void
	 * @throws     PropelException
	 */
	public function addentry(entry $l)
	{
		if ($this->collentrys === null) {
			$this->initentrys();
		}
		if (!in_array($l, $this->collentrys, true)) { // only add it if the **same** object is not already associated
			array_push($this->collentrys, $l);
			$l->setvuser($this);
		}
	}

	/**
	 * Clears out the collcomments collection (array).
	 *
	 * This does not modify the database; however, it will remove any associated objects, causing
	 * them to be refetched by subsequent calls to accessor method.
	 *
	 * @return     void
	 * @see        addcomments()
	 */
	public function clearcomments()
	{
		$this->collcomments = null; // important to set this to NULL since that means it is uninitialized
	}

	/**
	 * Initializes the collcomments collection (array).
	 *
	 * By default this just sets the collcomments collection to an empty array (like clearcollcomments());
	 * however, you may wish to override this method in your stub class to provide setting appropriate
	 * to your application -- for example, setting the initial array to the values stored in database.
	 *
	 * @return     void
	 */
	public function initcomments()
	{
		$this->collcomments = array();
	}

	/**
	 * Gets an array of comment objects which contain a foreign key that references this object.
	 *
	 * If this collection has already been initialized with an identical Criteria, it returns the collection.
	 * Otherwise if this vuser has previously been saved, it will retrieve
	 * related comments from storage. If this vuser is new, it will return
	 * an empty collection or the current collection, the criteria is ignored on a new object.
	 *
	 * @param      PropelPDO $con
	 * @param      Criteria $criteria
	 * @return     array comment[]
	 * @throws     PropelException
	 */
	public function getcomments($criteria = null, PropelPDO $con = null)
	{
		if ($criteria === null) {
			$criteria = new Criteria(vuserPeer::DATABASE_NAME);
		}
		elseif ($criteria instanceof Criteria)
		{
			$criteria = clone $criteria;
		}

		if ($this->collcomments === null) {
			if ($this->isNew()) {
			   $this->collcomments = array();
			} else {

				$criteria->add(commentPeer::VUSER_ID, $this->id);

				commentPeer::addSelectColumns($criteria);
				$this->collcomments = commentPeer::doSelect($criteria, $con);
			}
		} else {
			// criteria has no effect for a new object
			if (!$this->isNew()) {
				// the following code is to determine if a new query is
				// called for.  If the criteria is the same as the last
				// one, just return the collection.


				$criteria->add(commentPeer::VUSER_ID, $this->id);

				commentPeer::addSelectColumns($criteria);
				if (!isset($this->lastcommentCriteria) || !$this->lastcommentCriteria->equals($criteria)) {
					$this->collcomments = commentPeer::doSelect($criteria, $con);
				}
			}
		}
		$this->lastcommentCriteria = $criteria;
		return $this->collcomments;
	}

	/**
	 * Returns the number of related comment objects.
	 *
	 * @param      Criteria $criteria
	 * @param      boolean $distinct
	 * @param      PropelPDO $con
	 * @return     int Count of related comment objects.
	 * @throws     PropelException
	 */
	public function countcomments(Criteria $criteria = null, $distinct = false, PropelPDO $con = null)
	{
		if ($criteria === null) {
			$criteria = new Criteria(vuserPeer::DATABASE_NAME);
		} else {
			$criteria = clone $criteria;
		}

		if ($distinct) {
			$criteria->setDistinct();
		}

		$count = null;

		if ($this->collcomments === null) {
			if ($this->isNew()) {
				$count = 0;
			} else {

				$criteria->add(commentPeer::VUSER_ID, $this->id);

				$count = commentPeer::doCount($criteria, false, $con);
			}
		} else {
			// criteria has no effect for a new object
			if (!$this->isNew()) {
				// the following code is to determine if a new query is
				// called for.  If the criteria is the same as the last
				// one, just return count of the collection.


				$criteria->add(commentPeer::VUSER_ID, $this->id);

				if (!isset($this->lastcommentCriteria) || !$this->lastcommentCriteria->equals($criteria)) {
					$count = commentPeer::doCount($criteria, false, $con);
				} else {
					$count = count($this->collcomments);
				}
			} else {
				$count = count($this->collcomments);
			}
		}
		return $count;
	}

	/**
	 * Method called to associate a comment object to this object
	 * through the comment foreign key attribute.
	 *
	 * @param      comment $l comment
	 * @return     void
	 * @throws     PropelException
	 */
	public function addcomment(comment $l)
	{
		if ($this->collcomments === null) {
			$this->initcomments();
		}
		if (!in_array($l, $this->collcomments, true)) { // only add it if the **same** object is not already associated
			array_push($this->collcomments, $l);
			$l->setvuser($this);
		}
	}

	/**
	 * Clears out the collflags collection (array).
	 *
	 * This does not modify the database; however, it will remove any associated objects, causing
	 * them to be refetched by subsequent calls to accessor method.
	 *
	 * @return     void
	 * @see        addflags()
	 */
	public function clearflags()
	{
		$this->collflags = null; // important to set this to NULL since that means it is uninitialized
	}

	/**
	 * Initializes the collflags collection (array).
	 *
	 * By default this just sets the collflags collection to an empty array (like clearcollflags());
	 * however, you may wish to override this method in your stub class to provide setting appropriate
	 * to your application -- for example, setting the initial array to the values stored in database.
	 *
	 * @return     void
	 */
	public function initflags()
	{
		$this->collflags = array();
	}

	/**
	 * Gets an array of flag objects which contain a foreign key that references this object.
	 *
	 * If this collection has already been initialized with an identical Criteria, it returns the collection.
	 * Otherwise if this vuser has previously been saved, it will retrieve
	 * related flags from storage. If this vuser is new, it will return
	 * an empty collection or the current collection, the criteria is ignored on a new object.
	 *
	 * @param      PropelPDO $con
	 * @param      Criteria $criteria
	 * @return     array flag[]
	 * @throws     PropelException
	 */
	public function getflags($criteria = null, PropelPDO $con = null)
	{
		if ($criteria === null) {
			$criteria = new Criteria(vuserPeer::DATABASE_NAME);
		}
		elseif ($criteria instanceof Criteria)
		{
			$criteria = clone $criteria;
		}

		if ($this->collflags === null) {
			if ($this->isNew()) {
			   $this->collflags = array();
			} else {

				$criteria->add(flagPeer::VUSER_ID, $this->id);

				flagPeer::addSelectColumns($criteria);
				$this->collflags = flagPeer::doSelect($criteria, $con);
			}
		} else {
			// criteria has no effect for a new object
			if (!$this->isNew()) {
				// the following code is to determine if a new query is
				// called for.  If the criteria is the same as the last
				// one, just return the collection.


				$criteria->add(flagPeer::VUSER_ID, $this->id);

				flagPeer::addSelectColumns($criteria);
				if (!isset($this->lastflagCriteria) || !$this->lastflagCriteria->equals($criteria)) {
					$this->collflags = flagPeer::doSelect($criteria, $con);
				}
			}
		}
		$this->lastflagCriteria = $criteria;
		return $this->collflags;
	}

	/**
	 * Returns the number of related flag objects.
	 *
	 * @param      Criteria $criteria
	 * @param      boolean $distinct
	 * @param      PropelPDO $con
	 * @return     int Count of related flag objects.
	 * @throws     PropelException
	 */
	public function countflags(Criteria $criteria = null, $distinct = false, PropelPDO $con = null)
	{
		if ($criteria === null) {
			$criteria = new Criteria(vuserPeer::DATABASE_NAME);
		} else {
			$criteria = clone $criteria;
		}

		if ($distinct) {
			$criteria->setDistinct();
		}

		$count = null;

		if ($this->collflags === null) {
			if ($this->isNew()) {
				$count = 0;
			} else {

				$criteria->add(flagPeer::VUSER_ID, $this->id);

				$count = flagPeer::doCount($criteria, false, $con);
			}
		} else {
			// criteria has no effect for a new object
			if (!$this->isNew()) {
				// the following code is to determine if a new query is
				// called for.  If the criteria is the same as the last
				// one, just return count of the collection.


				$criteria->add(flagPeer::VUSER_ID, $this->id);

				if (!isset($this->lastflagCriteria) || !$this->lastflagCriteria->equals($criteria)) {
					$count = flagPeer::doCount($criteria, false, $con);
				} else {
					$count = count($this->collflags);
				}
			} else {
				$count = count($this->collflags);
			}
		}
		return $count;
	}

	/**
	 * Method called to associate a flag object to this object
	 * through the flag foreign key attribute.
	 *
	 * @param      flag $l flag
	 * @return     void
	 * @throws     PropelException
	 */
	public function addflag(flag $l)
	{
		if ($this->collflags === null) {
			$this->initflags();
		}
		if (!in_array($l, $this->collflags, true)) { // only add it if the **same** object is not already associated
			array_push($this->collflags, $l);
			$l->setvuser($this);
		}
	}

	/**
	 * Clears out the collfavorites collection (array).
	 *
	 * This does not modify the database; however, it will remove any associated objects, causing
	 * them to be refetched by subsequent calls to accessor method.
	 *
	 * @return     void
	 * @see        addfavorites()
	 */
	public function clearfavorites()
	{
		$this->collfavorites = null; // important to set this to NULL since that means it is uninitialized
	}

	/**
	 * Initializes the collfavorites collection (array).
	 *
	 * By default this just sets the collfavorites collection to an empty array (like clearcollfavorites());
	 * however, you may wish to override this method in your stub class to provide setting appropriate
	 * to your application -- for example, setting the initial array to the values stored in database.
	 *
	 * @return     void
	 */
	public function initfavorites()
	{
		$this->collfavorites = array();
	}

	/**
	 * Gets an array of favorite objects which contain a foreign key that references this object.
	 *
	 * If this collection has already been initialized with an identical Criteria, it returns the collection.
	 * Otherwise if this vuser has previously been saved, it will retrieve
	 * related favorites from storage. If this vuser is new, it will return
	 * an empty collection or the current collection, the criteria is ignored on a new object.
	 *
	 * @param      PropelPDO $con
	 * @param      Criteria $criteria
	 * @return     array favorite[]
	 * @throws     PropelException
	 */
	public function getfavorites($criteria = null, PropelPDO $con = null)
	{
		if ($criteria === null) {
			$criteria = new Criteria(vuserPeer::DATABASE_NAME);
		}
		elseif ($criteria instanceof Criteria)
		{
			$criteria = clone $criteria;
		}

		if ($this->collfavorites === null) {
			if ($this->isNew()) {
			   $this->collfavorites = array();
			} else {

				$criteria->add(favoritePeer::VUSER_ID, $this->id);

				favoritePeer::addSelectColumns($criteria);
				$this->collfavorites = favoritePeer::doSelect($criteria, $con);
			}
		} else {
			// criteria has no effect for a new object
			if (!$this->isNew()) {
				// the following code is to determine if a new query is
				// called for.  If the criteria is the same as the last
				// one, just return the collection.


				$criteria->add(favoritePeer::VUSER_ID, $this->id);

				favoritePeer::addSelectColumns($criteria);
				if (!isset($this->lastfavoriteCriteria) || !$this->lastfavoriteCriteria->equals($criteria)) {
					$this->collfavorites = favoritePeer::doSelect($criteria, $con);
				}
			}
		}
		$this->lastfavoriteCriteria = $criteria;
		return $this->collfavorites;
	}

	/**
	 * Returns the number of related favorite objects.
	 *
	 * @param      Criteria $criteria
	 * @param      boolean $distinct
	 * @param      PropelPDO $con
	 * @return     int Count of related favorite objects.
	 * @throws     PropelException
	 */
	public function countfavorites(Criteria $criteria = null, $distinct = false, PropelPDO $con = null)
	{
		if ($criteria === null) {
			$criteria = new Criteria(vuserPeer::DATABASE_NAME);
		} else {
			$criteria = clone $criteria;
		}

		if ($distinct) {
			$criteria->setDistinct();
		}

		$count = null;

		if ($this->collfavorites === null) {
			if ($this->isNew()) {
				$count = 0;
			} else {

				$criteria->add(favoritePeer::VUSER_ID, $this->id);

				$count = favoritePeer::doCount($criteria, false, $con);
			}
		} else {
			// criteria has no effect for a new object
			if (!$this->isNew()) {
				// the following code is to determine if a new query is
				// called for.  If the criteria is the same as the last
				// one, just return count of the collection.


				$criteria->add(favoritePeer::VUSER_ID, $this->id);

				if (!isset($this->lastfavoriteCriteria) || !$this->lastfavoriteCriteria->equals($criteria)) {
					$count = favoritePeer::doCount($criteria, false, $con);
				} else {
					$count = count($this->collfavorites);
				}
			} else {
				$count = count($this->collfavorites);
			}
		}
		return $count;
	}

	/**
	 * Method called to associate a favorite object to this object
	 * through the favorite foreign key attribute.
	 *
	 * @param      favorite $l favorite
	 * @return     void
	 * @throws     PropelException
	 */
	public function addfavorite(favorite $l)
	{
		if ($this->collfavorites === null) {
			$this->initfavorites();
		}
		if (!in_array($l, $this->collfavorites, true)) { // only add it if the **same** object is not already associated
			array_push($this->collfavorites, $l);
			$l->setvuser($this);
		}
	}

	/**
	 * Clears out the collVshowVusers collection (array).
	 *
	 * This does not modify the database; however, it will remove any associated objects, causing
	 * them to be refetched by subsequent calls to accessor method.
	 *
	 * @return     void
	 * @see        addVshowVusers()
	 */
	public function clearVshowVusers()
	{
		$this->collVshowVusers = null; // important to set this to NULL since that means it is uninitialized
	}

	/**
	 * Initializes the collVshowVusers collection (array).
	 *
	 * By default this just sets the collVshowVusers collection to an empty array (like clearcollVshowVusers());
	 * however, you may wish to override this method in your stub class to provide setting appropriate
	 * to your application -- for example, setting the initial array to the values stored in database.
	 *
	 * @return     void
	 */
	public function initVshowVusers()
	{
		$this->collVshowVusers = array();
	}

	/**
	 * Gets an array of VshowVuser objects which contain a foreign key that references this object.
	 *
	 * If this collection has already been initialized with an identical Criteria, it returns the collection.
	 * Otherwise if this vuser has previously been saved, it will retrieve
	 * related VshowVusers from storage. If this vuser is new, it will return
	 * an empty collection or the current collection, the criteria is ignored on a new object.
	 *
	 * @param      PropelPDO $con
	 * @param      Criteria $criteria
	 * @return     array VshowVuser[]
	 * @throws     PropelException
	 */
	public function getVshowVusers($criteria = null, PropelPDO $con = null)
	{
		if ($criteria === null) {
			$criteria = new Criteria(vuserPeer::DATABASE_NAME);
		}
		elseif ($criteria instanceof Criteria)
		{
			$criteria = clone $criteria;
		}

		if ($this->collVshowVusers === null) {
			if ($this->isNew()) {
			   $this->collVshowVusers = array();
			} else {

				$criteria->add(VshowVuserPeer::VUSER_ID, $this->id);

				VshowVuserPeer::addSelectColumns($criteria);
				$this->collVshowVusers = VshowVuserPeer::doSelect($criteria, $con);
			}
		} else {
			// criteria has no effect for a new object
			if (!$this->isNew()) {
				// the following code is to determine if a new query is
				// called for.  If the criteria is the same as the last
				// one, just return the collection.


				$criteria->add(VshowVuserPeer::VUSER_ID, $this->id);

				VshowVuserPeer::addSelectColumns($criteria);
				if (!isset($this->lastVshowVuserCriteria) || !$this->lastVshowVuserCriteria->equals($criteria)) {
					$this->collVshowVusers = VshowVuserPeer::doSelect($criteria, $con);
				}
			}
		}
		$this->lastVshowVuserCriteria = $criteria;
		return $this->collVshowVusers;
	}

	/**
	 * Returns the number of related VshowVuser objects.
	 *
	 * @param      Criteria $criteria
	 * @param      boolean $distinct
	 * @param      PropelPDO $con
	 * @return     int Count of related VshowVuser objects.
	 * @throws     PropelException
	 */
	public function countVshowVusers(Criteria $criteria = null, $distinct = false, PropelPDO $con = null)
	{
		if ($criteria === null) {
			$criteria = new Criteria(vuserPeer::DATABASE_NAME);
		} else {
			$criteria = clone $criteria;
		}

		if ($distinct) {
			$criteria->setDistinct();
		}

		$count = null;

		if ($this->collVshowVusers === null) {
			if ($this->isNew()) {
				$count = 0;
			} else {

				$criteria->add(VshowVuserPeer::VUSER_ID, $this->id);

				$count = VshowVuserPeer::doCount($criteria, false, $con);
			}
		} else {
			// criteria has no effect for a new object
			if (!$this->isNew()) {
				// the following code is to determine if a new query is
				// called for.  If the criteria is the same as the last
				// one, just return count of the collection.


				$criteria->add(VshowVuserPeer::VUSER_ID, $this->id);

				if (!isset($this->lastVshowVuserCriteria) || !$this->lastVshowVuserCriteria->equals($criteria)) {
					$count = VshowVuserPeer::doCount($criteria, false, $con);
				} else {
					$count = count($this->collVshowVusers);
				}
			} else {
				$count = count($this->collVshowVusers);
			}
		}
		return $count;
	}

	/**
	 * Method called to associate a VshowVuser object to this object
	 * through the VshowVuser foreign key attribute.
	 *
	 * @param      VshowVuser $l VshowVuser
	 * @return     void
	 * @throws     PropelException
	 */
	public function addVshowVuser(VshowVuser $l)
	{
		if ($this->collVshowVusers === null) {
			$this->initVshowVusers();
		}
		if (!in_array($l, $this->collVshowVusers, true)) { // only add it if the **same** object is not already associated
			array_push($this->collVshowVusers, $l);
			$l->setvuser($this);
		}
	}


	/**
	 * If this collection has already been initialized with
	 * an identical criteria, it returns the collection.
	 * Otherwise if this vuser is new, it will return
	 * an empty collection; or if this vuser has previously
	 * been saved, it will retrieve related VshowVusers from storage.
	 *
	 * This method is protected by default in order to keep the public
	 * api reasonable.  You can provide public methods for those you
	 * actually need in vuser.
	 */
	public function getVshowVusersJoinvshow($criteria = null, $con = null, $join_behavior = Criteria::LEFT_JOIN)
	{
		if ($criteria === null) {
			$criteria = new Criteria(vuserPeer::DATABASE_NAME);
		}
		elseif ($criteria instanceof Criteria)
		{
			$criteria = clone $criteria;
		}

		if ($this->collVshowVusers === null) {
			if ($this->isNew()) {
				$this->collVshowVusers = array();
			} else {

				$criteria->add(VshowVuserPeer::VUSER_ID, $this->id);

				$this->collVshowVusers = VshowVuserPeer::doSelectJoinvshow($criteria, $con, $join_behavior);
			}
		} else {
			// the following code is to determine if a new query is
			// called for.  If the criteria is the same as the last
			// one, just return the collection.

			$criteria->add(VshowVuserPeer::VUSER_ID, $this->id);

			if (!isset($this->lastVshowVuserCriteria) || !$this->lastVshowVuserCriteria->equals($criteria)) {
				$this->collVshowVusers = VshowVuserPeer::doSelectJoinvshow($criteria, $con, $join_behavior);
			}
		}
		$this->lastVshowVuserCriteria = $criteria;

		return $this->collVshowVusers;
	}

	/**
	 * Clears out the collPuserVusers collection (array).
	 *
	 * This does not modify the database; however, it will remove any associated objects, causing
	 * them to be refetched by subsequent calls to accessor method.
	 *
	 * @return     void
	 * @see        addPuserVusers()
	 */
	public function clearPuserVusers()
	{
		$this->collPuserVusers = null; // important to set this to NULL since that means it is uninitialized
	}

	/**
	 * Initializes the collPuserVusers collection (array).
	 *
	 * By default this just sets the collPuserVusers collection to an empty array (like clearcollPuserVusers());
	 * however, you may wish to override this method in your stub class to provide setting appropriate
	 * to your application -- for example, setting the initial array to the values stored in database.
	 *
	 * @return     void
	 */
	public function initPuserVusers()
	{
		$this->collPuserVusers = array();
	}

	/**
	 * Gets an array of PuserVuser objects which contain a foreign key that references this object.
	 *
	 * If this collection has already been initialized with an identical Criteria, it returns the collection.
	 * Otherwise if this vuser has previously been saved, it will retrieve
	 * related PuserVusers from storage. If this vuser is new, it will return
	 * an empty collection or the current collection, the criteria is ignored on a new object.
	 *
	 * @param      PropelPDO $con
	 * @param      Criteria $criteria
	 * @return     array PuserVuser[]
	 * @throws     PropelException
	 */
	public function getPuserVusers($criteria = null, PropelPDO $con = null)
	{
		if ($criteria === null) {
			$criteria = new Criteria(vuserPeer::DATABASE_NAME);
		}
		elseif ($criteria instanceof Criteria)
		{
			$criteria = clone $criteria;
		}

		if ($this->collPuserVusers === null) {
			if ($this->isNew()) {
			   $this->collPuserVusers = array();
			} else {

				$criteria->add(PuserVuserPeer::VUSER_ID, $this->id);

				PuserVuserPeer::addSelectColumns($criteria);
				$this->collPuserVusers = PuserVuserPeer::doSelect($criteria, $con);
			}
		} else {
			// criteria has no effect for a new object
			if (!$this->isNew()) {
				// the following code is to determine if a new query is
				// called for.  If the criteria is the same as the last
				// one, just return the collection.


				$criteria->add(PuserVuserPeer::VUSER_ID, $this->id);

				PuserVuserPeer::addSelectColumns($criteria);
				if (!isset($this->lastPuserVuserCriteria) || !$this->lastPuserVuserCriteria->equals($criteria)) {
					$this->collPuserVusers = PuserVuserPeer::doSelect($criteria, $con);
				}
			}
		}
		$this->lastPuserVuserCriteria = $criteria;
		return $this->collPuserVusers;
	}

	/**
	 * Returns the number of related PuserVuser objects.
	 *
	 * @param      Criteria $criteria
	 * @param      boolean $distinct
	 * @param      PropelPDO $con
	 * @return     int Count of related PuserVuser objects.
	 * @throws     PropelException
	 */
	public function countPuserVusers(Criteria $criteria = null, $distinct = false, PropelPDO $con = null)
	{
		if ($criteria === null) {
			$criteria = new Criteria(vuserPeer::DATABASE_NAME);
		} else {
			$criteria = clone $criteria;
		}

		if ($distinct) {
			$criteria->setDistinct();
		}

		$count = null;

		if ($this->collPuserVusers === null) {
			if ($this->isNew()) {
				$count = 0;
			} else {

				$criteria->add(PuserVuserPeer::VUSER_ID, $this->id);

				$count = PuserVuserPeer::doCount($criteria, false, $con);
			}
		} else {
			// criteria has no effect for a new object
			if (!$this->isNew()) {
				// the following code is to determine if a new query is
				// called for.  If the criteria is the same as the last
				// one, just return count of the collection.


				$criteria->add(PuserVuserPeer::VUSER_ID, $this->id);

				if (!isset($this->lastPuserVuserCriteria) || !$this->lastPuserVuserCriteria->equals($criteria)) {
					$count = PuserVuserPeer::doCount($criteria, false, $con);
				} else {
					$count = count($this->collPuserVusers);
				}
			} else {
				$count = count($this->collPuserVusers);
			}
		}
		return $count;
	}

	/**
	 * Method called to associate a PuserVuser object to this object
	 * through the PuserVuser foreign key attribute.
	 *
	 * @param      PuserVuser $l PuserVuser
	 * @return     void
	 * @throws     PropelException
	 */
	public function addPuserVuser(PuserVuser $l)
	{
		if ($this->collPuserVusers === null) {
			$this->initPuserVusers();
		}
		if (!in_array($l, $this->collPuserVusers, true)) { // only add it if the **same** object is not already associated
			array_push($this->collPuserVusers, $l);
			$l->setvuser($this);
		}
	}

	/**
	 * Clears out the collPartners collection (array).
	 *
	 * This does not modify the database; however, it will remove any associated objects, causing
	 * them to be refetched by subsequent calls to accessor method.
	 *
	 * @return     void
	 * @see        addPartners()
	 */
	public function clearPartners()
	{
		$this->collPartners = null; // important to set this to NULL since that means it is uninitialized
	}

	/**
	 * Initializes the collPartners collection (array).
	 *
	 * By default this just sets the collPartners collection to an empty array (like clearcollPartners());
	 * however, you may wish to override this method in your stub class to provide setting appropriate
	 * to your application -- for example, setting the initial array to the values stored in database.
	 *
	 * @return     void
	 */
	public function initPartners()
	{
		$this->collPartners = array();
	}

	/**
	 * Gets an array of Partner objects which contain a foreign key that references this object.
	 *
	 * If this collection has already been initialized with an identical Criteria, it returns the collection.
	 * Otherwise if this vuser has previously been saved, it will retrieve
	 * related Partners from storage. If this vuser is new, it will return
	 * an empty collection or the current collection, the criteria is ignored on a new object.
	 *
	 * @param      PropelPDO $con
	 * @param      Criteria $criteria
	 * @return     array Partner[]
	 * @throws     PropelException
	 */
	public function getPartners($criteria = null, PropelPDO $con = null)
	{
		if ($criteria === null) {
			$criteria = new Criteria(vuserPeer::DATABASE_NAME);
		}
		elseif ($criteria instanceof Criteria)
		{
			$criteria = clone $criteria;
		}

		if ($this->collPartners === null) {
			if ($this->isNew()) {
			   $this->collPartners = array();
			} else {

				$criteria->add(PartnerPeer::ANONYMOUS_VUSER_ID, $this->id);

				PartnerPeer::addSelectColumns($criteria);
				$this->collPartners = PartnerPeer::doSelect($criteria, $con);
			}
		} else {
			// criteria has no effect for a new object
			if (!$this->isNew()) {
				// the following code is to determine if a new query is
				// called for.  If the criteria is the same as the last
				// one, just return the collection.


				$criteria->add(PartnerPeer::ANONYMOUS_VUSER_ID, $this->id);

				PartnerPeer::addSelectColumns($criteria);
				if (!isset($this->lastPartnerCriteria) || !$this->lastPartnerCriteria->equals($criteria)) {
					$this->collPartners = PartnerPeer::doSelect($criteria, $con);
				}
			}
		}
		$this->lastPartnerCriteria = $criteria;
		return $this->collPartners;
	}

	/**
	 * Returns the number of related Partner objects.
	 *
	 * @param      Criteria $criteria
	 * @param      boolean $distinct
	 * @param      PropelPDO $con
	 * @return     int Count of related Partner objects.
	 * @throws     PropelException
	 */
	public function countPartners(Criteria $criteria = null, $distinct = false, PropelPDO $con = null)
	{
		if ($criteria === null) {
			$criteria = new Criteria(vuserPeer::DATABASE_NAME);
		} else {
			$criteria = clone $criteria;
		}

		if ($distinct) {
			$criteria->setDistinct();
		}

		$count = null;

		if ($this->collPartners === null) {
			if ($this->isNew()) {
				$count = 0;
			} else {

				$criteria->add(PartnerPeer::ANONYMOUS_VUSER_ID, $this->id);

				$count = PartnerPeer::doCount($criteria, false, $con);
			}
		} else {
			// criteria has no effect for a new object
			if (!$this->isNew()) {
				// the following code is to determine if a new query is
				// called for.  If the criteria is the same as the last
				// one, just return count of the collection.


				$criteria->add(PartnerPeer::ANONYMOUS_VUSER_ID, $this->id);

				if (!isset($this->lastPartnerCriteria) || !$this->lastPartnerCriteria->equals($criteria)) {
					$count = PartnerPeer::doCount($criteria, false, $con);
				} else {
					$count = count($this->collPartners);
				}
			} else {
				$count = count($this->collPartners);
			}
		}
		return $count;
	}

	/**
	 * Method called to associate a Partner object to this object
	 * through the Partner foreign key attribute.
	 *
	 * @param      Partner $l Partner
	 * @return     void
	 * @throws     PropelException
	 */
	public function addPartner(Partner $l)
	{
		if ($this->collPartners === null) {
			$this->initPartners();
		}
		if (!in_array($l, $this->collPartners, true)) { // only add it if the **same** object is not already associated
			array_push($this->collPartners, $l);
			$l->setvuser($this);
		}
	}

	/**
	 * Clears out the collmoderations collection (array).
	 *
	 * This does not modify the database; however, it will remove any associated objects, causing
	 * them to be refetched by subsequent calls to accessor method.
	 *
	 * @return     void
	 * @see        addmoderations()
	 */
	public function clearmoderations()
	{
		$this->collmoderations = null; // important to set this to NULL since that means it is uninitialized
	}

	/**
	 * Initializes the collmoderations collection (array).
	 *
	 * By default this just sets the collmoderations collection to an empty array (like clearcollmoderations());
	 * however, you may wish to override this method in your stub class to provide setting appropriate
	 * to your application -- for example, setting the initial array to the values stored in database.
	 *
	 * @return     void
	 */
	public function initmoderations()
	{
		$this->collmoderations = array();
	}

	/**
	 * Gets an array of moderation objects which contain a foreign key that references this object.
	 *
	 * If this collection has already been initialized with an identical Criteria, it returns the collection.
	 * Otherwise if this vuser has previously been saved, it will retrieve
	 * related moderations from storage. If this vuser is new, it will return
	 * an empty collection or the current collection, the criteria is ignored on a new object.
	 *
	 * @param      PropelPDO $con
	 * @param      Criteria $criteria
	 * @return     array moderation[]
	 * @throws     PropelException
	 */
	public function getmoderations($criteria = null, PropelPDO $con = null)
	{
		if ($criteria === null) {
			$criteria = new Criteria(vuserPeer::DATABASE_NAME);
		}
		elseif ($criteria instanceof Criteria)
		{
			$criteria = clone $criteria;
		}

		if ($this->collmoderations === null) {
			if ($this->isNew()) {
			   $this->collmoderations = array();
			} else {

				$criteria->add(moderationPeer::VUSER_ID, $this->id);

				moderationPeer::addSelectColumns($criteria);
				$this->collmoderations = moderationPeer::doSelect($criteria, $con);
			}
		} else {
			// criteria has no effect for a new object
			if (!$this->isNew()) {
				// the following code is to determine if a new query is
				// called for.  If the criteria is the same as the last
				// one, just return the collection.


				$criteria->add(moderationPeer::VUSER_ID, $this->id);

				moderationPeer::addSelectColumns($criteria);
				if (!isset($this->lastmoderationCriteria) || !$this->lastmoderationCriteria->equals($criteria)) {
					$this->collmoderations = moderationPeer::doSelect($criteria, $con);
				}
			}
		}
		$this->lastmoderationCriteria = $criteria;
		return $this->collmoderations;
	}

	/**
	 * Returns the number of related moderation objects.
	 *
	 * @param      Criteria $criteria
	 * @param      boolean $distinct
	 * @param      PropelPDO $con
	 * @return     int Count of related moderation objects.
	 * @throws     PropelException
	 */
	public function countmoderations(Criteria $criteria = null, $distinct = false, PropelPDO $con = null)
	{
		if ($criteria === null) {
			$criteria = new Criteria(vuserPeer::DATABASE_NAME);
		} else {
			$criteria = clone $criteria;
		}

		if ($distinct) {
			$criteria->setDistinct();
		}

		$count = null;

		if ($this->collmoderations === null) {
			if ($this->isNew()) {
				$count = 0;
			} else {

				$criteria->add(moderationPeer::VUSER_ID, $this->id);

				$count = moderationPeer::doCount($criteria, false, $con);
			}
		} else {
			// criteria has no effect for a new object
			if (!$this->isNew()) {
				// the following code is to determine if a new query is
				// called for.  If the criteria is the same as the last
				// one, just return count of the collection.


				$criteria->add(moderationPeer::VUSER_ID, $this->id);

				if (!isset($this->lastmoderationCriteria) || !$this->lastmoderationCriteria->equals($criteria)) {
					$count = moderationPeer::doCount($criteria, false, $con);
				} else {
					$count = count($this->collmoderations);
				}
			} else {
				$count = count($this->collmoderations);
			}
		}
		return $count;
	}

	/**
	 * Method called to associate a moderation object to this object
	 * through the moderation foreign key attribute.
	 *
	 * @param      moderation $l moderation
	 * @return     void
	 * @throws     PropelException
	 */
	public function addmoderation(moderation $l)
	{
		if ($this->collmoderations === null) {
			$this->initmoderations();
		}
		if (!in_array($l, $this->collmoderations, true)) { // only add it if the **same** object is not already associated
			array_push($this->collmoderations, $l);
			$l->setvuser($this);
		}
	}

	/**
	 * Clears out the collmoderationFlagsRelatedByVuserId collection (array).
	 *
	 * This does not modify the database; however, it will remove any associated objects, causing
	 * them to be refetched by subsequent calls to accessor method.
	 *
	 * @return     void
	 * @see        addmoderationFlagsRelatedByVuserId()
	 */
	public function clearmoderationFlagsRelatedByVuserId()
	{
		$this->collmoderationFlagsRelatedByVuserId = null; // important to set this to NULL since that means it is uninitialized
	}

	/**
	 * Initializes the collmoderationFlagsRelatedByVuserId collection (array).
	 *
	 * By default this just sets the collmoderationFlagsRelatedByVuserId collection to an empty array (like clearcollmoderationFlagsRelatedByVuserId());
	 * however, you may wish to override this method in your stub class to provide setting appropriate
	 * to your application -- for example, setting the initial array to the values stored in database.
	 *
	 * @return     void
	 */
	public function initmoderationFlagsRelatedByVuserId()
	{
		$this->collmoderationFlagsRelatedByVuserId = array();
	}

	/**
	 * Gets an array of moderationFlag objects which contain a foreign key that references this object.
	 *
	 * If this collection has already been initialized with an identical Criteria, it returns the collection.
	 * Otherwise if this vuser has previously been saved, it will retrieve
	 * related moderationFlagsRelatedByVuserId from storage. If this vuser is new, it will return
	 * an empty collection or the current collection, the criteria is ignored on a new object.
	 *
	 * @param      PropelPDO $con
	 * @param      Criteria $criteria
	 * @return     array moderationFlag[]
	 * @throws     PropelException
	 */
	public function getmoderationFlagsRelatedByVuserId($criteria = null, PropelPDO $con = null)
	{
		if ($criteria === null) {
			$criteria = new Criteria(vuserPeer::DATABASE_NAME);
		}
		elseif ($criteria instanceof Criteria)
		{
			$criteria = clone $criteria;
		}

		if ($this->collmoderationFlagsRelatedByVuserId === null) {
			if ($this->isNew()) {
			   $this->collmoderationFlagsRelatedByVuserId = array();
			} else {

				$criteria->add(moderationFlagPeer::VUSER_ID, $this->id);

				moderationFlagPeer::addSelectColumns($criteria);
				$this->collmoderationFlagsRelatedByVuserId = moderationFlagPeer::doSelect($criteria, $con);
			}
		} else {
			// criteria has no effect for a new object
			if (!$this->isNew()) {
				// the following code is to determine if a new query is
				// called for.  If the criteria is the same as the last
				// one, just return the collection.


				$criteria->add(moderationFlagPeer::VUSER_ID, $this->id);

				moderationFlagPeer::addSelectColumns($criteria);
				if (!isset($this->lastmoderationFlagRelatedByVuserIdCriteria) || !$this->lastmoderationFlagRelatedByVuserIdCriteria->equals($criteria)) {
					$this->collmoderationFlagsRelatedByVuserId = moderationFlagPeer::doSelect($criteria, $con);
				}
			}
		}
		$this->lastmoderationFlagRelatedByVuserIdCriteria = $criteria;
		return $this->collmoderationFlagsRelatedByVuserId;
	}

	/**
	 * Returns the number of related moderationFlag objects.
	 *
	 * @param      Criteria $criteria
	 * @param      boolean $distinct
	 * @param      PropelPDO $con
	 * @return     int Count of related moderationFlag objects.
	 * @throws     PropelException
	 */
	public function countmoderationFlagsRelatedByVuserId(Criteria $criteria = null, $distinct = false, PropelPDO $con = null)
	{
		if ($criteria === null) {
			$criteria = new Criteria(vuserPeer::DATABASE_NAME);
		} else {
			$criteria = clone $criteria;
		}

		if ($distinct) {
			$criteria->setDistinct();
		}

		$count = null;

		if ($this->collmoderationFlagsRelatedByVuserId === null) {
			if ($this->isNew()) {
				$count = 0;
			} else {

				$criteria->add(moderationFlagPeer::VUSER_ID, $this->id);

				$count = moderationFlagPeer::doCount($criteria, false, $con);
			}
		} else {
			// criteria has no effect for a new object
			if (!$this->isNew()) {
				// the following code is to determine if a new query is
				// called for.  If the criteria is the same as the last
				// one, just return count of the collection.


				$criteria->add(moderationFlagPeer::VUSER_ID, $this->id);

				if (!isset($this->lastmoderationFlagRelatedByVuserIdCriteria) || !$this->lastmoderationFlagRelatedByVuserIdCriteria->equals($criteria)) {
					$count = moderationFlagPeer::doCount($criteria, false, $con);
				} else {
					$count = count($this->collmoderationFlagsRelatedByVuserId);
				}
			} else {
				$count = count($this->collmoderationFlagsRelatedByVuserId);
			}
		}
		return $count;
	}

	/**
	 * Method called to associate a moderationFlag object to this object
	 * through the moderationFlag foreign key attribute.
	 *
	 * @param      moderationFlag $l moderationFlag
	 * @return     void
	 * @throws     PropelException
	 */
	public function addmoderationFlagRelatedByVuserId(moderationFlag $l)
	{
		if ($this->collmoderationFlagsRelatedByVuserId === null) {
			$this->initmoderationFlagsRelatedByVuserId();
		}
		if (!in_array($l, $this->collmoderationFlagsRelatedByVuserId, true)) { // only add it if the **same** object is not already associated
			array_push($this->collmoderationFlagsRelatedByVuserId, $l);
			$l->setvuserRelatedByVuserId($this);
		}
	}


	/**
	 * If this collection has already been initialized with
	 * an identical criteria, it returns the collection.
	 * Otherwise if this vuser is new, it will return
	 * an empty collection; or if this vuser has previously
	 * been saved, it will retrieve related moderationFlagsRelatedByVuserId from storage.
	 *
	 * This method is protected by default in order to keep the public
	 * api reasonable.  You can provide public methods for those you
	 * actually need in vuser.
	 */
	public function getmoderationFlagsRelatedByVuserIdJoinentry($criteria = null, $con = null, $join_behavior = Criteria::LEFT_JOIN)
	{
		if ($criteria === null) {
			$criteria = new Criteria(vuserPeer::DATABASE_NAME);
		}
		elseif ($criteria instanceof Criteria)
		{
			$criteria = clone $criteria;
		}

		if ($this->collmoderationFlagsRelatedByVuserId === null) {
			if ($this->isNew()) {
				$this->collmoderationFlagsRelatedByVuserId = array();
			} else {

				$criteria->add(moderationFlagPeer::VUSER_ID, $this->id);

				$this->collmoderationFlagsRelatedByVuserId = moderationFlagPeer::doSelectJoinentry($criteria, $con, $join_behavior);
			}
		} else {
			// the following code is to determine if a new query is
			// called for.  If the criteria is the same as the last
			// one, just return the collection.

			$criteria->add(moderationFlagPeer::VUSER_ID, $this->id);

			if (!isset($this->lastmoderationFlagRelatedByVuserIdCriteria) || !$this->lastmoderationFlagRelatedByVuserIdCriteria->equals($criteria)) {
				$this->collmoderationFlagsRelatedByVuserId = moderationFlagPeer::doSelectJoinentry($criteria, $con, $join_behavior);
			}
		}
		$this->lastmoderationFlagRelatedByVuserIdCriteria = $criteria;

		return $this->collmoderationFlagsRelatedByVuserId;
	}

	/**
	 * Clears out the collmoderationFlagsRelatedByFlaggedVuserId collection (array).
	 *
	 * This does not modify the database; however, it will remove any associated objects, causing
	 * them to be refetched by subsequent calls to accessor method.
	 *
	 * @return     void
	 * @see        addmoderationFlagsRelatedByFlaggedVuserId()
	 */
	public function clearmoderationFlagsRelatedByFlaggedVuserId()
	{
		$this->collmoderationFlagsRelatedByFlaggedVuserId = null; // important to set this to NULL since that means it is uninitialized
	}

	/**
	 * Initializes the collmoderationFlagsRelatedByFlaggedVuserId collection (array).
	 *
	 * By default this just sets the collmoderationFlagsRelatedByFlaggedVuserId collection to an empty array (like clearcollmoderationFlagsRelatedByFlaggedVuserId());
	 * however, you may wish to override this method in your stub class to provide setting appropriate
	 * to your application -- for example, setting the initial array to the values stored in database.
	 *
	 * @return     void
	 */
	public function initmoderationFlagsRelatedByFlaggedVuserId()
	{
		$this->collmoderationFlagsRelatedByFlaggedVuserId = array();
	}

	/**
	 * Gets an array of moderationFlag objects which contain a foreign key that references this object.
	 *
	 * If this collection has already been initialized with an identical Criteria, it returns the collection.
	 * Otherwise if this vuser has previously been saved, it will retrieve
	 * related moderationFlagsRelatedByFlaggedVuserId from storage. If this vuser is new, it will return
	 * an empty collection or the current collection, the criteria is ignored on a new object.
	 *
	 * @param      PropelPDO $con
	 * @param      Criteria $criteria
	 * @return     array moderationFlag[]
	 * @throws     PropelException
	 */
	public function getmoderationFlagsRelatedByFlaggedVuserId($criteria = null, PropelPDO $con = null)
	{
		if ($criteria === null) {
			$criteria = new Criteria(vuserPeer::DATABASE_NAME);
		}
		elseif ($criteria instanceof Criteria)
		{
			$criteria = clone $criteria;
		}

		if ($this->collmoderationFlagsRelatedByFlaggedVuserId === null) {
			if ($this->isNew()) {
			   $this->collmoderationFlagsRelatedByFlaggedVuserId = array();
			} else {

				$criteria->add(moderationFlagPeer::FLAGGED_VUSER_ID, $this->id);

				moderationFlagPeer::addSelectColumns($criteria);
				$this->collmoderationFlagsRelatedByFlaggedVuserId = moderationFlagPeer::doSelect($criteria, $con);
			}
		} else {
			// criteria has no effect for a new object
			if (!$this->isNew()) {
				// the following code is to determine if a new query is
				// called for.  If the criteria is the same as the last
				// one, just return the collection.


				$criteria->add(moderationFlagPeer::FLAGGED_VUSER_ID, $this->id);

				moderationFlagPeer::addSelectColumns($criteria);
				if (!isset($this->lastmoderationFlagRelatedByFlaggedVuserIdCriteria) || !$this->lastmoderationFlagRelatedByFlaggedVuserIdCriteria->equals($criteria)) {
					$this->collmoderationFlagsRelatedByFlaggedVuserId = moderationFlagPeer::doSelect($criteria, $con);
				}
			}
		}
		$this->lastmoderationFlagRelatedByFlaggedVuserIdCriteria = $criteria;
		return $this->collmoderationFlagsRelatedByFlaggedVuserId;
	}

	/**
	 * Returns the number of related moderationFlag objects.
	 *
	 * @param      Criteria $criteria
	 * @param      boolean $distinct
	 * @param      PropelPDO $con
	 * @return     int Count of related moderationFlag objects.
	 * @throws     PropelException
	 */
	public function countmoderationFlagsRelatedByFlaggedVuserId(Criteria $criteria = null, $distinct = false, PropelPDO $con = null)
	{
		if ($criteria === null) {
			$criteria = new Criteria(vuserPeer::DATABASE_NAME);
		} else {
			$criteria = clone $criteria;
		}

		if ($distinct) {
			$criteria->setDistinct();
		}

		$count = null;

		if ($this->collmoderationFlagsRelatedByFlaggedVuserId === null) {
			if ($this->isNew()) {
				$count = 0;
			} else {

				$criteria->add(moderationFlagPeer::FLAGGED_VUSER_ID, $this->id);

				$count = moderationFlagPeer::doCount($criteria, false, $con);
			}
		} else {
			// criteria has no effect for a new object
			if (!$this->isNew()) {
				// the following code is to determine if a new query is
				// called for.  If the criteria is the same as the last
				// one, just return count of the collection.


				$criteria->add(moderationFlagPeer::FLAGGED_VUSER_ID, $this->id);

				if (!isset($this->lastmoderationFlagRelatedByFlaggedVuserIdCriteria) || !$this->lastmoderationFlagRelatedByFlaggedVuserIdCriteria->equals($criteria)) {
					$count = moderationFlagPeer::doCount($criteria, false, $con);
				} else {
					$count = count($this->collmoderationFlagsRelatedByFlaggedVuserId);
				}
			} else {
				$count = count($this->collmoderationFlagsRelatedByFlaggedVuserId);
			}
		}
		return $count;
	}

	/**
	 * Method called to associate a moderationFlag object to this object
	 * through the moderationFlag foreign key attribute.
	 *
	 * @param      moderationFlag $l moderationFlag
	 * @return     void
	 * @throws     PropelException
	 */
	public function addmoderationFlagRelatedByFlaggedVuserId(moderationFlag $l)
	{
		if ($this->collmoderationFlagsRelatedByFlaggedVuserId === null) {
			$this->initmoderationFlagsRelatedByFlaggedVuserId();
		}
		if (!in_array($l, $this->collmoderationFlagsRelatedByFlaggedVuserId, true)) { // only add it if the **same** object is not already associated
			array_push($this->collmoderationFlagsRelatedByFlaggedVuserId, $l);
			$l->setvuserRelatedByFlaggedVuserId($this);
		}
	}


	/**
	 * If this collection has already been initialized with
	 * an identical criteria, it returns the collection.
	 * Otherwise if this vuser is new, it will return
	 * an empty collection; or if this vuser has previously
	 * been saved, it will retrieve related moderationFlagsRelatedByFlaggedVuserId from storage.
	 *
	 * This method is protected by default in order to keep the public
	 * api reasonable.  You can provide public methods for those you
	 * actually need in vuser.
	 */
	public function getmoderationFlagsRelatedByFlaggedVuserIdJoinentry($criteria = null, $con = null, $join_behavior = Criteria::LEFT_JOIN)
	{
		if ($criteria === null) {
			$criteria = new Criteria(vuserPeer::DATABASE_NAME);
		}
		elseif ($criteria instanceof Criteria)
		{
			$criteria = clone $criteria;
		}

		if ($this->collmoderationFlagsRelatedByFlaggedVuserId === null) {
			if ($this->isNew()) {
				$this->collmoderationFlagsRelatedByFlaggedVuserId = array();
			} else {

				$criteria->add(moderationFlagPeer::FLAGGED_VUSER_ID, $this->id);

				$this->collmoderationFlagsRelatedByFlaggedVuserId = moderationFlagPeer::doSelectJoinentry($criteria, $con, $join_behavior);
			}
		} else {
			// the following code is to determine if a new query is
			// called for.  If the criteria is the same as the last
			// one, just return the collection.

			$criteria->add(moderationFlagPeer::FLAGGED_VUSER_ID, $this->id);

			if (!isset($this->lastmoderationFlagRelatedByFlaggedVuserIdCriteria) || !$this->lastmoderationFlagRelatedByFlaggedVuserIdCriteria->equals($criteria)) {
				$this->collmoderationFlagsRelatedByFlaggedVuserId = moderationFlagPeer::doSelectJoinentry($criteria, $con, $join_behavior);
			}
		}
		$this->lastmoderationFlagRelatedByFlaggedVuserIdCriteria = $criteria;

		return $this->collmoderationFlagsRelatedByFlaggedVuserId;
	}

	/**
	 * Clears out the collcategoryVusers collection (array).
	 *
	 * This does not modify the database; however, it will remove any associated objects, causing
	 * them to be refetched by subsequent calls to accessor method.
	 *
	 * @return     void
	 * @see        addcategoryVusers()
	 */
	public function clearcategoryVusers()
	{
		$this->collcategoryVusers = null; // important to set this to NULL since that means it is uninitialized
	}

	/**
	 * Initializes the collcategoryVusers collection (array).
	 *
	 * By default this just sets the collcategoryVusers collection to an empty array (like clearcollcategoryVusers());
	 * however, you may wish to override this method in your stub class to provide setting appropriate
	 * to your application -- for example, setting the initial array to the values stored in database.
	 *
	 * @return     void
	 */
	public function initcategoryVusers()
	{
		$this->collcategoryVusers = array();
	}

	/**
	 * Gets an array of categoryVuser objects which contain a foreign key that references this object.
	 *
	 * If this collection has already been initialized with an identical Criteria, it returns the collection.
	 * Otherwise if this vuser has previously been saved, it will retrieve
	 * related categoryVusers from storage. If this vuser is new, it will return
	 * an empty collection or the current collection, the criteria is ignored on a new object.
	 *
	 * @param      PropelPDO $con
	 * @param      Criteria $criteria
	 * @return     array categoryVuser[]
	 * @throws     PropelException
	 */
	public function getcategoryVusers($criteria = null, PropelPDO $con = null)
	{
		if ($criteria === null) {
			$criteria = new Criteria(vuserPeer::DATABASE_NAME);
		}
		elseif ($criteria instanceof Criteria)
		{
			$criteria = clone $criteria;
		}

		if ($this->collcategoryVusers === null) {
			if ($this->isNew()) {
			   $this->collcategoryVusers = array();
			} else {

				$criteria->add(categoryVuserPeer::VUSER_ID, $this->id);

				categoryVuserPeer::addSelectColumns($criteria);
				$this->collcategoryVusers = categoryVuserPeer::doSelect($criteria, $con);
			}
		} else {
			// criteria has no effect for a new object
			if (!$this->isNew()) {
				// the following code is to determine if a new query is
				// called for.  If the criteria is the same as the last
				// one, just return the collection.


				$criteria->add(categoryVuserPeer::VUSER_ID, $this->id);

				categoryVuserPeer::addSelectColumns($criteria);
				if (!isset($this->lastcategoryVuserCriteria) || !$this->lastcategoryVuserCriteria->equals($criteria)) {
					$this->collcategoryVusers = categoryVuserPeer::doSelect($criteria, $con);
				}
			}
		}
		$this->lastcategoryVuserCriteria = $criteria;
		return $this->collcategoryVusers;
	}

	/**
	 * Returns the number of related categoryVuser objects.
	 *
	 * @param      Criteria $criteria
	 * @param      boolean $distinct
	 * @param      PropelPDO $con
	 * @return     int Count of related categoryVuser objects.
	 * @throws     PropelException
	 */
	public function countcategoryVusers(Criteria $criteria = null, $distinct = false, PropelPDO $con = null)
	{
		if ($criteria === null) {
			$criteria = new Criteria(vuserPeer::DATABASE_NAME);
		} else {
			$criteria = clone $criteria;
		}

		if ($distinct) {
			$criteria->setDistinct();
		}

		$count = null;

		if ($this->collcategoryVusers === null) {
			if ($this->isNew()) {
				$count = 0;
			} else {

				$criteria->add(categoryVuserPeer::VUSER_ID, $this->id);

				$count = categoryVuserPeer::doCount($criteria, false, $con);
			}
		} else {
			// criteria has no effect for a new object
			if (!$this->isNew()) {
				// the following code is to determine if a new query is
				// called for.  If the criteria is the same as the last
				// one, just return count of the collection.


				$criteria->add(categoryVuserPeer::VUSER_ID, $this->id);

				if (!isset($this->lastcategoryVuserCriteria) || !$this->lastcategoryVuserCriteria->equals($criteria)) {
					$count = categoryVuserPeer::doCount($criteria, false, $con);
				} else {
					$count = count($this->collcategoryVusers);
				}
			} else {
				$count = count($this->collcategoryVusers);
			}
		}
		return $count;
	}

	/**
	 * Method called to associate a categoryVuser object to this object
	 * through the categoryVuser foreign key attribute.
	 *
	 * @param      categoryVuser $l categoryVuser
	 * @return     void
	 * @throws     PropelException
	 */
	public function addcategoryVuser(categoryVuser $l)
	{
		if ($this->collcategoryVusers === null) {
			$this->initcategoryVusers();
		}
		if (!in_array($l, $this->collcategoryVusers, true)) { // only add it if the **same** object is not already associated
			array_push($this->collcategoryVusers, $l);
			$l->setvuser($this);
		}
	}


	/**
	 * If this collection has already been initialized with
	 * an identical criteria, it returns the collection.
	 * Otherwise if this vuser is new, it will return
	 * an empty collection; or if this vuser has previously
	 * been saved, it will retrieve related categoryVusers from storage.
	 *
	 * This method is protected by default in order to keep the public
	 * api reasonable.  You can provide public methods for those you
	 * actually need in vuser.
	 */
	public function getcategoryVusersJoincategory($criteria = null, $con = null, $join_behavior = Criteria::LEFT_JOIN)
	{
		if ($criteria === null) {
			$criteria = new Criteria(vuserPeer::DATABASE_NAME);
		}
		elseif ($criteria instanceof Criteria)
		{
			$criteria = clone $criteria;
		}

		if ($this->collcategoryVusers === null) {
			if ($this->isNew()) {
				$this->collcategoryVusers = array();
			} else {

				$criteria->add(categoryVuserPeer::VUSER_ID, $this->id);

				$this->collcategoryVusers = categoryVuserPeer::doSelectJoincategory($criteria, $con, $join_behavior);
			}
		} else {
			// the following code is to determine if a new query is
			// called for.  If the criteria is the same as the last
			// one, just return the collection.

			$criteria->add(categoryVuserPeer::VUSER_ID, $this->id);

			if (!isset($this->lastcategoryVuserCriteria) || !$this->lastcategoryVuserCriteria->equals($criteria)) {
				$this->collcategoryVusers = categoryVuserPeer::doSelectJoincategory($criteria, $con, $join_behavior);
			}
		}
		$this->lastcategoryVuserCriteria = $criteria;

		return $this->collcategoryVusers;
	}

	/**
	 * Clears out the collUploadTokens collection (array).
	 *
	 * This does not modify the database; however, it will remove any associated objects, causing
	 * them to be refetched by subsequent calls to accessor method.
	 *
	 * @return     void
	 * @see        addUploadTokens()
	 */
	public function clearUploadTokens()
	{
		$this->collUploadTokens = null; // important to set this to NULL since that means it is uninitialized
	}

	/**
	 * Initializes the collUploadTokens collection (array).
	 *
	 * By default this just sets the collUploadTokens collection to an empty array (like clearcollUploadTokens());
	 * however, you may wish to override this method in your stub class to provide setting appropriate
	 * to your application -- for example, setting the initial array to the values stored in database.
	 *
	 * @return     void
	 */
	public function initUploadTokens()
	{
		$this->collUploadTokens = array();
	}

	/**
	 * Gets an array of UploadToken objects which contain a foreign key that references this object.
	 *
	 * If this collection has already been initialized with an identical Criteria, it returns the collection.
	 * Otherwise if this vuser has previously been saved, it will retrieve
	 * related UploadTokens from storage. If this vuser is new, it will return
	 * an empty collection or the current collection, the criteria is ignored on a new object.
	 *
	 * @param      PropelPDO $con
	 * @param      Criteria $criteria
	 * @return     array UploadToken[]
	 * @throws     PropelException
	 */
	public function getUploadTokens($criteria = null, PropelPDO $con = null)
	{
		if ($criteria === null) {
			$criteria = new Criteria(vuserPeer::DATABASE_NAME);
		}
		elseif ($criteria instanceof Criteria)
		{
			$criteria = clone $criteria;
		}

		if ($this->collUploadTokens === null) {
			if ($this->isNew()) {
			   $this->collUploadTokens = array();
			} else {

				$criteria->add(UploadTokenPeer::VUSER_ID, $this->id);

				UploadTokenPeer::addSelectColumns($criteria);
				$this->collUploadTokens = UploadTokenPeer::doSelect($criteria, $con);
			}
		} else {
			// criteria has no effect for a new object
			if (!$this->isNew()) {
				// the following code is to determine if a new query is
				// called for.  If the criteria is the same as the last
				// one, just return the collection.


				$criteria->add(UploadTokenPeer::VUSER_ID, $this->id);

				UploadTokenPeer::addSelectColumns($criteria);
				if (!isset($this->lastUploadTokenCriteria) || !$this->lastUploadTokenCriteria->equals($criteria)) {
					$this->collUploadTokens = UploadTokenPeer::doSelect($criteria, $con);
				}
			}
		}
		$this->lastUploadTokenCriteria = $criteria;
		return $this->collUploadTokens;
	}

	/**
	 * Returns the number of related UploadToken objects.
	 *
	 * @param      Criteria $criteria
	 * @param      boolean $distinct
	 * @param      PropelPDO $con
	 * @return     int Count of related UploadToken objects.
	 * @throws     PropelException
	 */
	public function countUploadTokens(Criteria $criteria = null, $distinct = false, PropelPDO $con = null)
	{
		if ($criteria === null) {
			$criteria = new Criteria(vuserPeer::DATABASE_NAME);
		} else {
			$criteria = clone $criteria;
		}

		if ($distinct) {
			$criteria->setDistinct();
		}

		$count = null;

		if ($this->collUploadTokens === null) {
			if ($this->isNew()) {
				$count = 0;
			} else {

				$criteria->add(UploadTokenPeer::VUSER_ID, $this->id);

				$count = UploadTokenPeer::doCount($criteria, false, $con);
			}
		} else {
			// criteria has no effect for a new object
			if (!$this->isNew()) {
				// the following code is to determine if a new query is
				// called for.  If the criteria is the same as the last
				// one, just return count of the collection.


				$criteria->add(UploadTokenPeer::VUSER_ID, $this->id);

				if (!isset($this->lastUploadTokenCriteria) || !$this->lastUploadTokenCriteria->equals($criteria)) {
					$count = UploadTokenPeer::doCount($criteria, false, $con);
				} else {
					$count = count($this->collUploadTokens);
				}
			} else {
				$count = count($this->collUploadTokens);
			}
		}
		return $count;
	}

	/**
	 * Method called to associate a UploadToken object to this object
	 * through the UploadToken foreign key attribute.
	 *
	 * @param      UploadToken $l UploadToken
	 * @return     void
	 * @throws     PropelException
	 */
	public function addUploadToken(UploadToken $l)
	{
		if ($this->collUploadTokens === null) {
			$this->initUploadTokens();
		}
		if (!in_array($l, $this->collUploadTokens, true)) { // only add it if the **same** object is not already associated
			array_push($this->collUploadTokens, $l);
			$l->setvuser($this);
		}
	}

	/**
	 * Clears out the collVuserToUserRoles collection (array).
	 *
	 * This does not modify the database; however, it will remove any associated objects, causing
	 * them to be refetched by subsequent calls to accessor method.
	 *
	 * @return     void
	 * @see        addVuserToUserRoles()
	 */
	public function clearVuserToUserRoles()
	{
		$this->collVuserToUserRoles = null; // important to set this to NULL since that means it is uninitialized
	}

	/**
	 * Initializes the collVuserToUserRoles collection (array).
	 *
	 * By default this just sets the collVuserToUserRoles collection to an empty array (like clearcollVuserToUserRoles());
	 * however, you may wish to override this method in your stub class to provide setting appropriate
	 * to your application -- for example, setting the initial array to the values stored in database.
	 *
	 * @return     void
	 */
	public function initVuserToUserRoles()
	{
		$this->collVuserToUserRoles = array();
	}

	/**
	 * Gets an array of VuserToUserRole objects which contain a foreign key that references this object.
	 *
	 * If this collection has already been initialized with an identical Criteria, it returns the collection.
	 * Otherwise if this vuser has previously been saved, it will retrieve
	 * related VuserToUserRoles from storage. If this vuser is new, it will return
	 * an empty collection or the current collection, the criteria is ignored on a new object.
	 *
	 * @param      PropelPDO $con
	 * @param      Criteria $criteria
	 * @return     array VuserToUserRole[]
	 * @throws     PropelException
	 */
	public function getVuserToUserRoles($criteria = null, PropelPDO $con = null)
	{
		if ($criteria === null) {
			$criteria = new Criteria(vuserPeer::DATABASE_NAME);
		}
		elseif ($criteria instanceof Criteria)
		{
			$criteria = clone $criteria;
		}

		if ($this->collVuserToUserRoles === null) {
			if ($this->isNew()) {
			   $this->collVuserToUserRoles = array();
			} else {

				$criteria->add(VuserToUserRolePeer::VUSER_ID, $this->id);

				VuserToUserRolePeer::addSelectColumns($criteria);
				$this->collVuserToUserRoles = VuserToUserRolePeer::doSelect($criteria, $con);
			}
		} else {
			// criteria has no effect for a new object
			if (!$this->isNew()) {
				// the following code is to determine if a new query is
				// called for.  If the criteria is the same as the last
				// one, just return the collection.


				$criteria->add(VuserToUserRolePeer::VUSER_ID, $this->id);

				VuserToUserRolePeer::addSelectColumns($criteria);
				if (!isset($this->lastVuserToUserRoleCriteria) || !$this->lastVuserToUserRoleCriteria->equals($criteria)) {
					$this->collVuserToUserRoles = VuserToUserRolePeer::doSelect($criteria, $con);
				}
			}
		}
		$this->lastVuserToUserRoleCriteria = $criteria;
		return $this->collVuserToUserRoles;
	}

	/**
	 * Returns the number of related VuserToUserRole objects.
	 *
	 * @param      Criteria $criteria
	 * @param      boolean $distinct
	 * @param      PropelPDO $con
	 * @return     int Count of related VuserToUserRole objects.
	 * @throws     PropelException
	 */
	public function countVuserToUserRoles(Criteria $criteria = null, $distinct = false, PropelPDO $con = null)
	{
		if ($criteria === null) {
			$criteria = new Criteria(vuserPeer::DATABASE_NAME);
		} else {
			$criteria = clone $criteria;
		}

		if ($distinct) {
			$criteria->setDistinct();
		}

		$count = null;

		if ($this->collVuserToUserRoles === null) {
			if ($this->isNew()) {
				$count = 0;
			} else {

				$criteria->add(VuserToUserRolePeer::VUSER_ID, $this->id);

				$count = VuserToUserRolePeer::doCount($criteria, false, $con);
			}
		} else {
			// criteria has no effect for a new object
			if (!$this->isNew()) {
				// the following code is to determine if a new query is
				// called for.  If the criteria is the same as the last
				// one, just return count of the collection.


				$criteria->add(VuserToUserRolePeer::VUSER_ID, $this->id);

				if (!isset($this->lastVuserToUserRoleCriteria) || !$this->lastVuserToUserRoleCriteria->equals($criteria)) {
					$count = VuserToUserRolePeer::doCount($criteria, false, $con);
				} else {
					$count = count($this->collVuserToUserRoles);
				}
			} else {
				$count = count($this->collVuserToUserRoles);
			}
		}
		return $count;
	}

	/**
	 * Method called to associate a VuserToUserRole object to this object
	 * through the VuserToUserRole foreign key attribute.
	 *
	 * @param      VuserToUserRole $l VuserToUserRole
	 * @return     void
	 * @throws     PropelException
	 */
	public function addVuserToUserRole(VuserToUserRole $l)
	{
		if ($this->collVuserToUserRoles === null) {
			$this->initVuserToUserRoles();
		}
		if (!in_array($l, $this->collVuserToUserRoles, true)) { // only add it if the **same** object is not already associated
			array_push($this->collVuserToUserRoles, $l);
			$l->setvuser($this);
		}
	}


	/**
	 * If this collection has already been initialized with
	 * an identical criteria, it returns the collection.
	 * Otherwise if this vuser is new, it will return
	 * an empty collection; or if this vuser has previously
	 * been saved, it will retrieve related VuserToUserRoles from storage.
	 *
	 * This method is protected by default in order to keep the public
	 * api reasonable.  You can provide public methods for those you
	 * actually need in vuser.
	 */
	public function getVuserToUserRolesJoinUserRole($criteria = null, $con = null, $join_behavior = Criteria::LEFT_JOIN)
	{
		if ($criteria === null) {
			$criteria = new Criteria(vuserPeer::DATABASE_NAME);
		}
		elseif ($criteria instanceof Criteria)
		{
			$criteria = clone $criteria;
		}

		if ($this->collVuserToUserRoles === null) {
			if ($this->isNew()) {
				$this->collVuserToUserRoles = array();
			} else {

				$criteria->add(VuserToUserRolePeer::VUSER_ID, $this->id);

				$this->collVuserToUserRoles = VuserToUserRolePeer::doSelectJoinUserRole($criteria, $con, $join_behavior);
			}
		} else {
			// the following code is to determine if a new query is
			// called for.  If the criteria is the same as the last
			// one, just return the collection.

			$criteria->add(VuserToUserRolePeer::VUSER_ID, $this->id);

			if (!isset($this->lastVuserToUserRoleCriteria) || !$this->lastVuserToUserRoleCriteria->equals($criteria)) {
				$this->collVuserToUserRoles = VuserToUserRolePeer::doSelectJoinUserRole($criteria, $con, $join_behavior);
			}
		}
		$this->lastVuserToUserRoleCriteria = $criteria;

		return $this->collVuserToUserRoles;
	}

	/**
	 * Clears out the collVuserVgroupsRelatedByVgroupId collection (array).
	 *
	 * This does not modify the database; however, it will remove any associated objects, causing
	 * them to be refetched by subsequent calls to accessor method.
	 *
	 * @return     void
	 * @see        addVuserVgroupsRelatedByVgroupId()
	 */
	public function clearVuserVgroupsRelatedByVgroupId()
	{
		$this->collVuserVgroupsRelatedByVgroupId = null; // important to set this to NULL since that means it is uninitialized
	}

	/**
	 * Initializes the collVuserVgroupsRelatedByVgroupId collection (array).
	 *
	 * By default this just sets the collVuserVgroupsRelatedByVgroupId collection to an empty array (like clearcollVuserVgroupsRelatedByVgroupId());
	 * however, you may wish to override this method in your stub class to provide setting appropriate
	 * to your application -- for example, setting the initial array to the values stored in database.
	 *
	 * @return     void
	 */
	public function initVuserVgroupsRelatedByVgroupId()
	{
		$this->collVuserVgroupsRelatedByVgroupId = array();
	}

	/**
	 * Gets an array of VuserVgroup objects which contain a foreign key that references this object.
	 *
	 * If this collection has already been initialized with an identical Criteria, it returns the collection.
	 * Otherwise if this vuser has previously been saved, it will retrieve
	 * related VuserVgroupsRelatedByVgroupId from storage. If this vuser is new, it will return
	 * an empty collection or the current collection, the criteria is ignored on a new object.
	 *
	 * @param      PropelPDO $con
	 * @param      Criteria $criteria
	 * @return     array VuserVgroup[]
	 * @throws     PropelException
	 */
	public function getVuserVgroupsRelatedByVgroupId($criteria = null, PropelPDO $con = null)
	{
		if ($criteria === null) {
			$criteria = new Criteria(vuserPeer::DATABASE_NAME);
		}
		elseif ($criteria instanceof Criteria)
		{
			$criteria = clone $criteria;
		}

		if ($this->collVuserVgroupsRelatedByVgroupId === null) {
			if ($this->isNew()) {
			   $this->collVuserVgroupsRelatedByVgroupId = array();
			} else {

				$criteria->add(VuserVgroupPeer::VGROUP_ID, $this->id);

				VuserVgroupPeer::addSelectColumns($criteria);
				$this->collVuserVgroupsRelatedByVgroupId = VuserVgroupPeer::doSelect($criteria, $con);
			}
		} else {
			// criteria has no effect for a new object
			if (!$this->isNew()) {
				// the following code is to determine if a new query is
				// called for.  If the criteria is the same as the last
				// one, just return the collection.


				$criteria->add(VuserVgroupPeer::VGROUP_ID, $this->id);

				VuserVgroupPeer::addSelectColumns($criteria);
				if (!isset($this->lastVuserVgroupRelatedByVgroupIdCriteria) || !$this->lastVuserVgroupRelatedByVgroupIdCriteria->equals($criteria)) {
					$this->collVuserVgroupsRelatedByVgroupId = VuserVgroupPeer::doSelect($criteria, $con);
				}
			}
		}
		$this->lastVuserVgroupRelatedByVgroupIdCriteria = $criteria;
		return $this->collVuserVgroupsRelatedByVgroupId;
	}

	/**
	 * Returns the number of related VuserVgroup objects.
	 *
	 * @param      Criteria $criteria
	 * @param      boolean $distinct
	 * @param      PropelPDO $con
	 * @return     int Count of related VuserVgroup objects.
	 * @throws     PropelException
	 */
	public function countVuserVgroupsRelatedByVgroupId(Criteria $criteria = null, $distinct = false, PropelPDO $con = null)
	{
		if ($criteria === null) {
			$criteria = new Criteria(vuserPeer::DATABASE_NAME);
		} else {
			$criteria = clone $criteria;
		}

		if ($distinct) {
			$criteria->setDistinct();
		}

		$count = null;

		if ($this->collVuserVgroupsRelatedByVgroupId === null) {
			if ($this->isNew()) {
				$count = 0;
			} else {

				$criteria->add(VuserVgroupPeer::VGROUP_ID, $this->id);

				$count = VuserVgroupPeer::doCount($criteria, false, $con);
			}
		} else {
			// criteria has no effect for a new object
			if (!$this->isNew()) {
				// the following code is to determine if a new query is
				// called for.  If the criteria is the same as the last
				// one, just return count of the collection.


				$criteria->add(VuserVgroupPeer::VGROUP_ID, $this->id);

				if (!isset($this->lastVuserVgroupRelatedByVgroupIdCriteria) || !$this->lastVuserVgroupRelatedByVgroupIdCriteria->equals($criteria)) {
					$count = VuserVgroupPeer::doCount($criteria, false, $con);
				} else {
					$count = count($this->collVuserVgroupsRelatedByVgroupId);
				}
			} else {
				$count = count($this->collVuserVgroupsRelatedByVgroupId);
			}
		}
		return $count;
	}

	/**
	 * Method called to associate a VuserVgroup object to this object
	 * through the VuserVgroup foreign key attribute.
	 *
	 * @param      VuserVgroup $l VuserVgroup
	 * @return     void
	 * @throws     PropelException
	 */
	public function addVuserVgroupRelatedByVgroupId(VuserVgroup $l)
	{
		if ($this->collVuserVgroupsRelatedByVgroupId === null) {
			$this->initVuserVgroupsRelatedByVgroupId();
		}
		if (!in_array($l, $this->collVuserVgroupsRelatedByVgroupId, true)) { // only add it if the **same** object is not already associated
			array_push($this->collVuserVgroupsRelatedByVgroupId, $l);
			$l->setvuserRelatedByVgroupId($this);
		}
	}

	/**
	 * Clears out the collVuserVgroupsRelatedByVuserId collection (array).
	 *
	 * This does not modify the database; however, it will remove any associated objects, causing
	 * them to be refetched by subsequent calls to accessor method.
	 *
	 * @return     void
	 * @see        addVuserVgroupsRelatedByVuserId()
	 */
	public function clearVuserVgroupsRelatedByVuserId()
	{
		$this->collVuserVgroupsRelatedByVuserId = null; // important to set this to NULL since that means it is uninitialized
	}

	/**
	 * Initializes the collVuserVgroupsRelatedByVuserId collection (array).
	 *
	 * By default this just sets the collVuserVgroupsRelatedByVuserId collection to an empty array (like clearcollVuserVgroupsRelatedByVuserId());
	 * however, you may wish to override this method in your stub class to provide setting appropriate
	 * to your application -- for example, setting the initial array to the values stored in database.
	 *
	 * @return     void
	 */
	public function initVuserVgroupsRelatedByVuserId()
	{
		$this->collVuserVgroupsRelatedByVuserId = array();
	}

	/**
	 * Gets an array of VuserVgroup objects which contain a foreign key that references this object.
	 *
	 * If this collection has already been initialized with an identical Criteria, it returns the collection.
	 * Otherwise if this vuser has previously been saved, it will retrieve
	 * related VuserVgroupsRelatedByVuserId from storage. If this vuser is new, it will return
	 * an empty collection or the current collection, the criteria is ignored on a new object.
	 *
	 * @param      PropelPDO $con
	 * @param      Criteria $criteria
	 * @return     array VuserVgroup[]
	 * @throws     PropelException
	 */
	public function getVuserVgroupsRelatedByVuserId($criteria = null, PropelPDO $con = null)
	{
		if ($criteria === null) {
			$criteria = new Criteria(vuserPeer::DATABASE_NAME);
		}
		elseif ($criteria instanceof Criteria)
		{
			$criteria = clone $criteria;
		}

		if ($this->collVuserVgroupsRelatedByVuserId === null) {
			if ($this->isNew()) {
			   $this->collVuserVgroupsRelatedByVuserId = array();
			} else {

				$criteria->add(VuserVgroupPeer::VUSER_ID, $this->id);

				VuserVgroupPeer::addSelectColumns($criteria);
				$this->collVuserVgroupsRelatedByVuserId = VuserVgroupPeer::doSelect($criteria, $con);
			}
		} else {
			// criteria has no effect for a new object
			if (!$this->isNew()) {
				// the following code is to determine if a new query is
				// called for.  If the criteria is the same as the last
				// one, just return the collection.


				$criteria->add(VuserVgroupPeer::VUSER_ID, $this->id);

				VuserVgroupPeer::addSelectColumns($criteria);
				if (!isset($this->lastVuserVgroupRelatedByVuserIdCriteria) || !$this->lastVuserVgroupRelatedByVuserIdCriteria->equals($criteria)) {
					$this->collVuserVgroupsRelatedByVuserId = VuserVgroupPeer::doSelect($criteria, $con);
				}
			}
		}
		$this->lastVuserVgroupRelatedByVuserIdCriteria = $criteria;
		return $this->collVuserVgroupsRelatedByVuserId;
	}

	/**
	 * Returns the number of related VuserVgroup objects.
	 *
	 * @param      Criteria $criteria
	 * @param      boolean $distinct
	 * @param      PropelPDO $con
	 * @return     int Count of related VuserVgroup objects.
	 * @throws     PropelException
	 */
	public function countVuserVgroupsRelatedByVuserId(Criteria $criteria = null, $distinct = false, PropelPDO $con = null)
	{
		if ($criteria === null) {
			$criteria = new Criteria(vuserPeer::DATABASE_NAME);
		} else {
			$criteria = clone $criteria;
		}

		if ($distinct) {
			$criteria->setDistinct();
		}

		$count = null;

		if ($this->collVuserVgroupsRelatedByVuserId === null) {
			if ($this->isNew()) {
				$count = 0;
			} else {

				$criteria->add(VuserVgroupPeer::VUSER_ID, $this->id);

				$count = VuserVgroupPeer::doCount($criteria, false, $con);
			}
		} else {
			// criteria has no effect for a new object
			if (!$this->isNew()) {
				// the following code is to determine if a new query is
				// called for.  If the criteria is the same as the last
				// one, just return count of the collection.


				$criteria->add(VuserVgroupPeer::VUSER_ID, $this->id);

				if (!isset($this->lastVuserVgroupRelatedByVuserIdCriteria) || !$this->lastVuserVgroupRelatedByVuserIdCriteria->equals($criteria)) {
					$count = VuserVgroupPeer::doCount($criteria, false, $con);
				} else {
					$count = count($this->collVuserVgroupsRelatedByVuserId);
				}
			} else {
				$count = count($this->collVuserVgroupsRelatedByVuserId);
			}
		}
		return $count;
	}

	/**
	 * Method called to associate a VuserVgroup object to this object
	 * through the VuserVgroup foreign key attribute.
	 *
	 * @param      VuserVgroup $l VuserVgroup
	 * @return     void
	 * @throws     PropelException
	 */
	public function addVuserVgroupRelatedByVuserId(VuserVgroup $l)
	{
		if ($this->collVuserVgroupsRelatedByVuserId === null) {
			$this->initVuserVgroupsRelatedByVuserId();
		}
		if (!in_array($l, $this->collVuserVgroupsRelatedByVuserId, true)) { // only add it if the **same** object is not already associated
			array_push($this->collVuserVgroupsRelatedByVuserId, $l);
			$l->setvuserRelatedByVuserId($this);
		}
	}

	/**
	 * Clears out the collUserEntrys collection (array).
	 *
	 * This does not modify the database; however, it will remove any associated objects, causing
	 * them to be refetched by subsequent calls to accessor method.
	 *
	 * @return     void
	 * @see        addUserEntrys()
	 */
	public function clearUserEntrys()
	{
		$this->collUserEntrys = null; // important to set this to NULL since that means it is uninitialized
	}

	/**
	 * Initializes the collUserEntrys collection (array).
	 *
	 * By default this just sets the collUserEntrys collection to an empty array (like clearcollUserEntrys());
	 * however, you may wish to override this method in your stub class to provide setting appropriate
	 * to your application -- for example, setting the initial array to the values stored in database.
	 *
	 * @return     void
	 */
	public function initUserEntrys()
	{
		$this->collUserEntrys = array();
	}

	/**
	 * Gets an array of UserEntry objects which contain a foreign key that references this object.
	 *
	 * If this collection has already been initialized with an identical Criteria, it returns the collection.
	 * Otherwise if this vuser has previously been saved, it will retrieve
	 * related UserEntrys from storage. If this vuser is new, it will return
	 * an empty collection or the current collection, the criteria is ignored on a new object.
	 *
	 * @param      PropelPDO $con
	 * @param      Criteria $criteria
	 * @return     array UserEntry[]
	 * @throws     PropelException
	 */
	public function getUserEntrys($criteria = null, PropelPDO $con = null)
	{
		if ($criteria === null) {
			$criteria = new Criteria(vuserPeer::DATABASE_NAME);
		}
		elseif ($criteria instanceof Criteria)
		{
			$criteria = clone $criteria;
		}

		if ($this->collUserEntrys === null) {
			if ($this->isNew()) {
			   $this->collUserEntrys = array();
			} else {

				$criteria->add(UserEntryPeer::VUSER_ID, $this->id);

				UserEntryPeer::addSelectColumns($criteria);
				$this->collUserEntrys = UserEntryPeer::doSelect($criteria, $con);
			}
		} else {
			// criteria has no effect for a new object
			if (!$this->isNew()) {
				// the following code is to determine if a new query is
				// called for.  If the criteria is the same as the last
				// one, just return the collection.


				$criteria->add(UserEntryPeer::VUSER_ID, $this->id);

				UserEntryPeer::addSelectColumns($criteria);
				if (!isset($this->lastUserEntryCriteria) || !$this->lastUserEntryCriteria->equals($criteria)) {
					$this->collUserEntrys = UserEntryPeer::doSelect($criteria, $con);
				}
			}
		}
		$this->lastUserEntryCriteria = $criteria;
		return $this->collUserEntrys;
	}

	/**
	 * Returns the number of related UserEntry objects.
	 *
	 * @param      Criteria $criteria
	 * @param      boolean $distinct
	 * @param      PropelPDO $con
	 * @return     int Count of related UserEntry objects.
	 * @throws     PropelException
	 */
	public function countUserEntrys(Criteria $criteria = null, $distinct = false, PropelPDO $con = null)
	{
		if ($criteria === null) {
			$criteria = new Criteria(vuserPeer::DATABASE_NAME);
		} else {
			$criteria = clone $criteria;
		}

		if ($distinct) {
			$criteria->setDistinct();
		}

		$count = null;

		if ($this->collUserEntrys === null) {
			if ($this->isNew()) {
				$count = 0;
			} else {

				$criteria->add(UserEntryPeer::VUSER_ID, $this->id);

				$count = UserEntryPeer::doCount($criteria, false, $con);
			}
		} else {
			// criteria has no effect for a new object
			if (!$this->isNew()) {
				// the following code is to determine if a new query is
				// called for.  If the criteria is the same as the last
				// one, just return count of the collection.


				$criteria->add(UserEntryPeer::VUSER_ID, $this->id);

				if (!isset($this->lastUserEntryCriteria) || !$this->lastUserEntryCriteria->equals($criteria)) {
					$count = UserEntryPeer::doCount($criteria, false, $con);
				} else {
					$count = count($this->collUserEntrys);
				}
			} else {
				$count = count($this->collUserEntrys);
			}
		}
		return $count;
	}

	/**
	 * Method called to associate a UserEntry object to this object
	 * through the UserEntry foreign key attribute.
	 *
	 * @param      UserEntry $l UserEntry
	 * @return     void
	 * @throws     PropelException
	 */
	public function addUserEntry(UserEntry $l)
	{
		if ($this->collUserEntrys === null) {
			$this->initUserEntrys();
		}
		if (!in_array($l, $this->collUserEntrys, true)) { // only add it if the **same** object is not already associated
			array_push($this->collUserEntrys, $l);
			$l->setvuser($this);
		}
	}


	/**
	 * If this collection has already been initialized with
	 * an identical criteria, it returns the collection.
	 * Otherwise if this vuser is new, it will return
	 * an empty collection; or if this vuser has previously
	 * been saved, it will retrieve related UserEntrys from storage.
	 *
	 * This method is protected by default in order to keep the public
	 * api reasonable.  You can provide public methods for those you
	 * actually need in vuser.
	 */
	public function getUserEntrysJoinentry($criteria = null, $con = null, $join_behavior = Criteria::LEFT_JOIN)
	{
		if ($criteria === null) {
			$criteria = new Criteria(vuserPeer::DATABASE_NAME);
		}
		elseif ($criteria instanceof Criteria)
		{
			$criteria = clone $criteria;
		}

		if ($this->collUserEntrys === null) {
			if ($this->isNew()) {
				$this->collUserEntrys = array();
			} else {

				$criteria->add(UserEntryPeer::VUSER_ID, $this->id);

				$this->collUserEntrys = UserEntryPeer::doSelectJoinentry($criteria, $con, $join_behavior);
			}
		} else {
			// the following code is to determine if a new query is
			// called for.  If the criteria is the same as the last
			// one, just return the collection.

			$criteria->add(UserEntryPeer::VUSER_ID, $this->id);

			if (!isset($this->lastUserEntryCriteria) || !$this->lastUserEntryCriteria->equals($criteria)) {
				$this->collUserEntrys = UserEntryPeer::doSelectJoinentry($criteria, $con, $join_behavior);
			}
		}
		$this->lastUserEntryCriteria = $criteria;

		return $this->collUserEntrys;
	}

	/**
	 * Resets all collections of referencing foreign keys.
	 *
	 * This method is a user-space workaround for PHP's inability to garbage collect objects
	 * with circular references.  This is currently necessary when using Propel in certain
	 * daemon or large-volumne/high-memory operations.
	 *
	 * @param      boolean $deep Whether to also clear the references on all associated objects.
	 */
	public function clearAllReferences($deep = false)
	{
		if ($deep) {
			if ($this->collvshows) {
				foreach ((array) $this->collvshows as $o) {
					$o->clearAllReferences($deep);
				}
			}
			if ($this->collentrys) {
				foreach ((array) $this->collentrys as $o) {
					$o->clearAllReferences($deep);
				}
			}
			if ($this->collcomments) {
				foreach ((array) $this->collcomments as $o) {
					$o->clearAllReferences($deep);
				}
			}
			if ($this->collflags) {
				foreach ((array) $this->collflags as $o) {
					$o->clearAllReferences($deep);
				}
			}
			if ($this->collfavorites) {
				foreach ((array) $this->collfavorites as $o) {
					$o->clearAllReferences($deep);
				}
			}
			if ($this->collVshowVusers) {
				foreach ((array) $this->collVshowVusers as $o) {
					$o->clearAllReferences($deep);
				}
			}
			if ($this->collPuserVusers) {
				foreach ((array) $this->collPuserVusers as $o) {
					$o->clearAllReferences($deep);
				}
			}
			if ($this->collPartners) {
				foreach ((array) $this->collPartners as $o) {
					$o->clearAllReferences($deep);
				}
			}
			if ($this->collmoderations) {
				foreach ((array) $this->collmoderations as $o) {
					$o->clearAllReferences($deep);
				}
			}
			if ($this->collmoderationFlagsRelatedByVuserId) {
				foreach ((array) $this->collmoderationFlagsRelatedByVuserId as $o) {
					$o->clearAllReferences($deep);
				}
			}
			if ($this->collmoderationFlagsRelatedByFlaggedVuserId) {
				foreach ((array) $this->collmoderationFlagsRelatedByFlaggedVuserId as $o) {
					$o->clearAllReferences($deep);
				}
			}
			if ($this->collcategoryVusers) {
				foreach ((array) $this->collcategoryVusers as $o) {
					$o->clearAllReferences($deep);
				}
			}
			if ($this->collUploadTokens) {
				foreach ((array) $this->collUploadTokens as $o) {
					$o->clearAllReferences($deep);
				}
			}
			if ($this->collVuserToUserRoles) {
				foreach ((array) $this->collVuserToUserRoles as $o) {
					$o->clearAllReferences($deep);
				}
			}
			if ($this->collVuserVgroupsRelatedByVgroupId) {
				foreach ((array) $this->collVuserVgroupsRelatedByVgroupId as $o) {
					$o->clearAllReferences($deep);
				}
			}
			if ($this->collVuserVgroupsRelatedByVuserId) {
				foreach ((array) $this->collVuserVgroupsRelatedByVuserId as $o) {
					$o->clearAllReferences($deep);
				}
			}
			if ($this->collUserEntrys) {
				foreach ((array) $this->collUserEntrys as $o) {
					$o->clearAllReferences($deep);
				}
			}
		} // if ($deep)

		$this->collvshows = null;
		$this->collentrys = null;
		$this->collcomments = null;
		$this->collflags = null;
		$this->collfavorites = null;
		$this->collVshowVusers = null;
		$this->collPuserVusers = null;
		$this->collPartners = null;
		$this->collmoderations = null;
		$this->collmoderationFlagsRelatedByVuserId = null;
		$this->collmoderationFlagsRelatedByFlaggedVuserId = null;
		$this->collcategoryVusers = null;
		$this->collUploadTokens = null;
		$this->collVuserToUserRoles = null;
		$this->collVuserVgroupsRelatedByVgroupId = null;
		$this->collVuserVgroupsRelatedByVuserId = null;
		$this->collUserEntrys = null;
	}

	/* ---------------------- CustomData functions ------------------------- */

	/**
	 * @var myCustomData
	 */
	protected $m_custom_data = null;
	
	/**
	 * The md5 value for the custom_data field.
	 * @var        string
	 */
	protected $custom_data_md5;

	/**
	 * Store custom data old values before the changes
	 * @var        array
	 */
	protected $oldCustomDataValues = array();
	
	/**
	 * @return array
	 */
	public function getCustomDataOldValues()
	{
		return $this->oldCustomDataValues;
	}
	
	/**
	 * @param string $name
	 * @param string $value
	 * @param string $namespace
	 * @return string
	 */
	public function putInCustomData ( $name , $value , $namespace = null )
	{
		$customData = $this->getCustomDataObj( );
		
		$currentNamespace = '';
		if($namespace)
			$currentNamespace = $namespace;
			
		if(!isset($this->oldCustomDataValues[$currentNamespace]))
			$this->oldCustomDataValues[$currentNamespace] = array();
		if(!isset($this->oldCustomDataValues[$currentNamespace][$name]))
			$this->oldCustomDataValues[$currentNamespace][$name] = $customData->get($name, $namespace);
		
		$customData->put ( $name , $value , $namespace );
	}

	/**
	 * @param string $name
	 * @param string $namespace
	 * @param string $defaultValue
	 * @return string
	 */
	public function getFromCustomData ( $name , $namespace = null , $defaultValue = null )
	{
		$customData = $this->getCustomDataObj( );
		$res = $customData->get ( $name , $namespace );
		if ( $res === null ) return $defaultValue;
		return $res;
	}

	/**
	 * @param string $name
	 * @param string $namespace
	 */
	public function removeFromCustomData ( $name , $namespace = null)
	{
		$customData = $this->getCustomDataObj();
		
		$currentNamespace = '';
		if($namespace)
			$currentNamespace = $namespace;
			
		if(!isset($this->oldCustomDataValues[$currentNamespace]))
			$this->oldCustomDataValues[$currentNamespace] = array();
		if(!isset($this->oldCustomDataValues[$currentNamespace][$name]))
			$this->oldCustomDataValues[$currentNamespace][$name] = $customData->get($name, $namespace);
		
		return $customData->remove ( $name , $namespace );
	}

	/**
	 * @param string $name
	 * @param int $delta
	 * @param string $namespace
	 * @return string
	 */
	public function incInCustomData ( $name , $delta = 1, $namespace = null)
	{
		$customData = $this->getCustomDataObj( );
		
		$currentNamespace = '';
		if($namespace)
			$currentNamespace = $namespace;
			
		if(!isset($this->oldCustomDataValues[$currentNamespace]))
			$this->oldCustomDataValues[$currentNamespace] = array();
		if(!isset($this->oldCustomDataValues[$currentNamespace][$name]))
			$this->oldCustomDataValues[$currentNamespace][$name] = $customData->get($name, $namespace);
		
		return $customData->inc ( $name , $delta , $namespace  );
	}

	/**
	 * @param string $name
	 * @param int $delta
	 * @param string $namespace
	 * @return string
	 */
	public function decInCustomData ( $name , $delta = 1, $namespace = null)
	{
		$customData = $this->getCustomDataObj(  );
		return $customData->dec ( $name , $delta , $namespace );
	}

	/**
	 * @return myCustomData
	 */
	public function getCustomDataObj( )
	{
		if ( ! $this->m_custom_data )
		{
			$this->m_custom_data = myCustomData::fromString ( $this->getCustomData() );
		}
		return $this->m_custom_data;
	}
	
	/**
	 * Must be called before saving the object
	 */
	public function setCustomDataObj()
	{
		if ( $this->m_custom_data != null )
		{
			$this->custom_data_md5 = is_null($this->custom_data) ? null : md5($this->custom_data);
			$this->setCustomData( $this->m_custom_data->toString() );
		}
	}
	
	/* ---------------------- CustomData functions ------------------------- */
	
} // Basevuser
