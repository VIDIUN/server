<?php


/**
 * This class defines the structure of the 'flag' table.
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
class flagTableMap extends TableMap {

	/**
	 * The (dot-path) name of this class
	 */
	const CLASS_NAME = 'Core.flagTableMap';

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
		$this->setName('flag');
		$this->setPhpName('flag');
		$this->setClassname('flag');
		$this->setPackage('Core');
		$this->setUseIdGenerator(true);
		// columns
		$this->addPrimaryKey('ID', 'Id', 'INTEGER', true, null, null);
		$this->addForeignKey('VUSER_ID', 'VuserId', 'INTEGER', 'vuser', 'ID', false, null, null);
		$this->addColumn('SUBJECT_TYPE', 'SubjectType', 'INTEGER', false, null, null);
		$this->addColumn('SUBJECT_ID', 'SubjectId', 'INTEGER', false, null, null);
		$this->addColumn('FLAG_TYPE', 'FlagType', 'INTEGER', false, null, null);
		$this->addColumn('OTHER', 'Other', 'VARCHAR', false, 60, null);
		$this->addColumn('COMMENT', 'Comment', 'VARCHAR', false, 2048, null);
		$this->addColumn('CREATED_AT', 'CreatedAt', 'TIMESTAMP', false, null, null);
		// validators
	} // initialize()

	/**
	 * Build the RelationMap objects for this table relationships
	 */
	public function buildRelations()
	{
    $this->addRelation('vuser', 'vuser', RelationMap::MANY_TO_ONE, array('vuser_id' => 'id', ), null, null);
	} // buildRelations()

} // flagTableMap
