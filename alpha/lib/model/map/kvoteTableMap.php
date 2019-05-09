<?php


/**
 * This class defines the structure of the 'vvote' table.
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
class vvoteTableMap extends TableMap {

	/**
	 * The (dot-path) name of this class
	 */
	const CLASS_NAME = 'Core.vvoteTableMap';

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
		$this->setName('vvote');
		$this->setPhpName('vvote');
		$this->setClassname('vvote');
		$this->setPackage('Core');
		$this->setUseIdGenerator(true);
		// columns
		$this->addPrimaryKey('ID', 'Id', 'INTEGER', true, null, null);
		$this->addForeignKey('VSHOW_ID', 'VshowId', 'VARCHAR', 'vshow', 'ID', false, 20, null);
		$this->addForeignKey('ENTRY_ID', 'EntryId', 'VARCHAR', 'entry', 'ID', false, 20, null);
		$this->addForeignKey('VUSER_ID', 'VuserId', 'INTEGER', 'vshow', 'ID', false, null, null);
		$this->addColumn('PUSER_ID', 'PuserId', 'VARCHAR', false, 100, null);
		$this->addColumn('PARTNER_ID', 'PartnerId', 'INTEGER', false, null, null);
		$this->addColumn('RANK', 'Rank', 'INTEGER', false, null, null);
		$this->addColumn('STATUS', 'Status', 'INTEGER', false, null, null);
		$this->addColumn('VVOTE_TYPE', 'VvoteType', 'INTEGER', false, null, 1);
		$this->addColumn('CREATED_AT', 'CreatedAt', 'TIMESTAMP', false, null, null);
		$this->addColumn('CUSTOM_DATA', 'CustomData', 'LONGVARCHAR', false, null, null);
		// validators
	} // initialize()

	/**
	 * Build the RelationMap objects for this table relationships
	 */
	public function buildRelations()
	{
    $this->addRelation('vshowRelatedByVshowId', 'vshow', RelationMap::MANY_TO_ONE, array('vshow_id' => 'id', ), null, null);
    $this->addRelation('entry', 'entry', RelationMap::MANY_TO_ONE, array('entry_id' => 'id', ), null, null);
    $this->addRelation('vshowRelatedByVuserId', 'vshow', RelationMap::MANY_TO_ONE, array('vuser_id' => 'id', ), null, null);
	} // buildRelations()

} // vvoteTableMap
