<?php


/**
 * This class defines the structure of the 'moderation_flag' table.
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
class moderationFlagTableMap extends TableMap {

	/**
	 * The (dot-path) name of this class
	 */
	const CLASS_NAME = 'Core.moderationFlagTableMap';

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
		$this->setName('moderation_flag');
		$this->setPhpName('moderationFlag');
		$this->setClassname('moderationFlag');
		$this->setPackage('Core');
		$this->setUseIdGenerator(true);
		// columns
		$this->addPrimaryKey('ID', 'Id', 'INTEGER', true, null, null);
		$this->addColumn('PARTNER_ID', 'PartnerId', 'INTEGER', false, null, null);
		$this->addForeignKey('VUSER_ID', 'VuserId', 'INTEGER', 'vuser', 'ID', false, null, null);
		$this->addColumn('OBJECT_TYPE', 'ObjectType', 'SMALLINT', false, null, null);
		$this->addForeignKey('FLAGGED_ENTRY_ID', 'FlaggedEntryId', 'VARCHAR', 'entry', 'ID', false, 20, null);
		$this->addForeignKey('FLAGGED_VUSER_ID', 'FlaggedVuserId', 'INTEGER', 'vuser', 'ID', false, null, null);
		$this->addColumn('STATUS', 'Status', 'INTEGER', false, null, null);
		$this->addColumn('CREATED_AT', 'CreatedAt', 'TIMESTAMP', false, null, null);
		$this->addColumn('UPDATED_AT', 'UpdatedAt', 'TIMESTAMP', false, null, null);
		$this->addColumn('COMMENTS', 'Comments', 'VARCHAR', false, 1024, null);
		$this->addColumn('FLAG_TYPE', 'FlagType', 'INTEGER', false, null, null);
		// validators
	} // initialize()

	/**
	 * Build the RelationMap objects for this table relationships
	 */
	public function buildRelations()
	{
    $this->addRelation('vuserRelatedByVuserId', 'vuser', RelationMap::MANY_TO_ONE, array('vuser_id' => 'id', ), null, null);
    $this->addRelation('entry', 'entry', RelationMap::MANY_TO_ONE, array('flagged_entry_id' => 'id', ), null, null);
    $this->addRelation('vuserRelatedByFlaggedVuserId', 'vuser', RelationMap::MANY_TO_ONE, array('flagged_vuser_id' => 'id', ), null, null);
	} // buildRelations()

} // moderationFlagTableMap
