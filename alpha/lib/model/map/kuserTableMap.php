<?php


/**
 * This class defines the structure of the 'vuser' table.
 *
 *
 *
 * This map class is used by Propel to do runtime db structure discovery.
 * For example, the createSelectSql() method checks the type of a given column used in an
 * ORDER BY clause to know whether it needs to apply SQL to make the ORDER BY case-insensitive
 * (i.e. if it's a text column type).
 *
 * @package Core
 * @subpackage model.map
 */
class vuserTableMap extends TableMap {

	/**
	 * The (dot-path) name of this class
	 */
	const CLASS_NAME = 'Core.vuserTableMap';

	/**
	 * Initialize the table attributes, columns and validators
	 * Relations are not initialized by this method since they are lazy loaded
	 *
	 * @return     void
	 * @throws     PropelException
	 */
	public function initialize()
	{
	  // attributes
		$this->setName('vuser');
		$this->setPhpName('vuser');
		$this->setClassname('vuser');
		$this->setPackage('Core');
		$this->setUseIdGenerator(true);
		// columns
		$this->addPrimaryKey('ID', 'Id', 'INTEGER', true, null, null);
		$this->addColumn('LOGIN_DATA_ID', 'LoginDataId', 'INTEGER', false, null, null);
		$this->addColumn('IS_ADMIN', 'IsAdmin', 'BOOLEAN', false, null, null);
		$this->addColumn('SCREEN_NAME', 'ScreenName', 'VARCHAR', false, 100, null);
		$this->addColumn('FULL_NAME', 'FullName', 'VARCHAR', false, 40, null);
		$this->addColumn('FIRST_NAME', 'FirstName', 'VARCHAR', false, 40, null);
		$this->addColumn('LAST_NAME', 'LastName', 'VARCHAR', false, 40, null);
		$this->addColumn('EMAIL', 'Email', 'VARCHAR', false, 100, null);
		$this->addColumn('SHA1_PASSWORD', 'Sha1Password', 'VARCHAR', false, 40, null);
		$this->addColumn('SALT', 'Salt', 'VARCHAR', false, 32, null);
		$this->addColumn('DATE_OF_BIRTH', 'DateOfBirth', 'DATE', false, null, null);
		$this->addColumn('COUNTRY', 'Country', 'VARCHAR', false, 2, null);
		$this->addColumn('STATE', 'State', 'VARCHAR', false, 16, null);
		$this->addColumn('CITY', 'City', 'VARCHAR', false, 30, null);
		$this->addColumn('ZIP', 'Zip', 'VARCHAR', false, 10, null);
		$this->addColumn('URL_LIST', 'UrlList', 'VARCHAR', false, 256, null);
		$this->addColumn('PICTURE', 'Picture', 'VARCHAR', false, 1024, null);
		$this->addColumn('ICON', 'Icon', 'TINYINT', false, null, null);
		$this->addColumn('ABOUT_ME', 'AboutMe', 'VARCHAR', false, 4096, null);
		$this->addColumn('TAGS', 'Tags', 'LONGVARCHAR', false, null, null);
		$this->addColumn('TAGLINE', 'Tagline', 'VARCHAR', false, 256, null);
		$this->addColumn('NETWORK_HIGHSCHOOL', 'NetworkHighschool', 'VARCHAR', false, 30, null);
		$this->addColumn('NETWORK_COLLEGE', 'NetworkCollege', 'VARCHAR', false, 30, null);
		$this->addColumn('NETWORK_OTHER', 'NetworkOther', 'VARCHAR', false, 30, null);
		$this->addColumn('MOBILE_NUM', 'MobileNum', 'VARCHAR', false, 16, null);
		$this->addColumn('MATURE_CONTENT', 'MatureContent', 'TINYINT', false, null, null);
		$this->addColumn('GENDER', 'Gender', 'TINYINT', false, null, null);
		$this->addColumn('REGISTRATION_IP', 'RegistrationIp', 'INTEGER', false, null, null);
		$this->addColumn('REGISTRATION_COOKIE', 'RegistrationCookie', 'VARCHAR', false, 256, null);
		$this->addColumn('IM_LIST', 'ImList', 'VARCHAR', false, 256, null);
		$this->addColumn('VIEWS', 'Views', 'INTEGER', false, null, 0);
		$this->addColumn('FANS', 'Fans', 'INTEGER', false, null, 0);
		$this->addColumn('ENTRIES', 'Entries', 'INTEGER', false, null, 0);
		$this->addColumn('STORAGE_SIZE', 'StorageSize', 'INTEGER', false, null, 0);
		$this->addColumn('PRODUCED_VSHOWS', 'ProducedVshows', 'INTEGER', false, null, 0);
		$this->addColumn('STATUS', 'Status', 'INTEGER', false, null, null);
		$this->addColumn('CREATED_AT', 'CreatedAt', 'TIMESTAMP', false, null, null);
		$this->addColumn('UPDATED_AT', 'UpdatedAt', 'TIMESTAMP', false, null, null);
		$this->addColumn('PARTNER_ID', 'PartnerId', 'INTEGER', false, null, 0);
		$this->addColumn('DISPLAY_IN_SEARCH', 'DisplayInSearch', 'TINYINT', false, null, null);
		$this->addColumn('PARTNER_DATA', 'PartnerData', 'VARCHAR', false, 4096, null);
		$this->addColumn('PUSER_ID', 'PuserId', 'VARCHAR', false, 100, null);
		$this->addColumn('ADMIN_TAGS', 'AdminTags', 'LONGVARCHAR', false, null, null);
		$this->addColumn('INDEXED_PARTNER_DATA_INT', 'IndexedPartnerDataInt', 'INTEGER', false, null, null);
		$this->addColumn('INDEXED_PARTNER_DATA_STRING', 'IndexedPartnerDataString', 'VARCHAR', false, 64, null);
		$this->addColumn('CUSTOM_DATA', 'CustomData', 'LONGVARCHAR', false, null, null);
		$this->addColumn('TYPE', 'Type', 'INTEGER', false, null, 0);
		// validators
	} // initialize()

	/**
	 * Build the RelationMap objects for this table relationships
	 */
	public function buildRelations()
	{
    $this->addRelation('vshow', 'vshow', RelationMap::ONE_TO_MANY, array('id' => 'producer_id', ), null, null);
    $this->addRelation('entry', 'entry', RelationMap::ONE_TO_MANY, array('id' => 'vuser_id', ), null, null);
    $this->addRelation('comment', 'comment', RelationMap::ONE_TO_MANY, array('id' => 'vuser_id', ), null, null);
    $this->addRelation('flag', 'flag', RelationMap::ONE_TO_MANY, array('id' => 'vuser_id', ), null, null);
    $this->addRelation('favorite', 'favorite', RelationMap::ONE_TO_MANY, array('id' => 'vuser_id', ), null, null);
    $this->addRelation('VshowVuser', 'VshowVuser', RelationMap::ONE_TO_MANY, array('id' => 'vuser_id', ), null, null);
    $this->addRelation('PuserVuser', 'PuserVuser', RelationMap::ONE_TO_MANY, array('id' => 'vuser_id', ), null, null);
    $this->addRelation('Partner', 'Partner', RelationMap::ONE_TO_MANY, array('id' => 'anonymous_vuser_id', ), null, null);
    $this->addRelation('moderation', 'moderation', RelationMap::ONE_TO_MANY, array('id' => 'vuser_id', ), null, null);
    $this->addRelation('moderationFlagRelatedByVuserId', 'moderationFlag', RelationMap::ONE_TO_MANY, array('id' => 'vuser_id', ), null, null);
    $this->addRelation('moderationFlagRelatedByFlaggedVuserId', 'moderationFlag', RelationMap::ONE_TO_MANY, array('id' => 'flagged_vuser_id', ), null, null);
    $this->addRelation('categoryVuser', 'categoryVuser', RelationMap::ONE_TO_MANY, array('id' => 'vuser_id', ), null, null);
    $this->addRelation('UploadToken', 'UploadToken', RelationMap::ONE_TO_MANY, array('id' => 'vuser_id', ), null, null);
    $this->addRelation('VuserToUserRole', 'VuserToUserRole', RelationMap::ONE_TO_MANY, array('id' => 'vuser_id', ), null, null);
    $this->addRelation('VuserVgroupRelatedByVgroupId', 'VuserVgroup', RelationMap::ONE_TO_MANY, array('id' => 'vgroup_id', ), null, null);
    $this->addRelation('VuserVgroupRelatedByVuserId', 'VuserVgroup', RelationMap::ONE_TO_MANY, array('id' => 'vuser_id', ), null, null);
    $this->addRelation('UserEntry', 'UserEntry', RelationMap::ONE_TO_MANY, array('id' => 'vuser_id', ), null, null);
	} // buildRelations()

} // vuserTableMap
