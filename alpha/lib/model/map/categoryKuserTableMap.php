<?php


/**
 * This class defines the structure of the 'category_vuser' table.
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
class categoryVuserTableMap extends TableMap {

	/**
	 * The (dot-path) name of this class
	 */
	const CLASS_NAME = 'Core.categoryVuserTableMap';

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
		$this->setName('category_vuser');
		$this->setPhpName('categoryVuser');
		$this->setClassname('categoryVuser');
		$this->setPackage('Core');
		$this->setUseIdGenerator(true);
		// columns
		$this->addPrimaryKey('ID', 'Id', 'INTEGER', true, null, null);
		$this->addForeignKey('CATEGORY_ID', 'CategoryId', 'INTEGER', 'category', 'ID', true, null, null);
		$this->addForeignKey('VUSER_ID', 'VuserId', 'INTEGER', 'vuser', 'ID', true, null, null);
		$this->addColumn('PUSER_ID', 'PuserId', 'VARCHAR', true, 100, null);
		$this->addColumn('SCREEN_NAME', 'ScreenName', 'VARCHAR', true, 100, null);
		$this->addColumn('PARTNER_ID', 'PartnerId', 'INTEGER', true, null, null);
		$this->addColumn('PERMISSION_LEVEL', 'PermissionLevel', 'TINYINT', false, null, null);
		$this->addColumn('STATUS', 'Status', 'TINYINT', false, null, null);
		$this->addColumn('INHERIT_FROM_CATEGORY', 'InheritFromCategory', 'INTEGER', false, null, null);
		$this->addColumn('UPDATE_METHOD', 'UpdateMethod', 'INTEGER', false, null, null);
		$this->addColumn('CREATED_AT', 'CreatedAt', 'TIMESTAMP', false, null, null);
		$this->addColumn('UPDATED_AT', 'UpdatedAt', 'TIMESTAMP', false, null, null);
		$this->addColumn('CUSTOM_DATA', 'CustomData', 'LONGVARCHAR', false, null, null);
		$this->addColumn('CATEGORY_FULL_IDS', 'CategoryFullIds', 'LONGVARCHAR', false, null, null);
		$this->addColumn('PERMISSION_NAMES', 'PermissionNames', 'LONGVARCHAR', false, null, null);
		// validators
	} // initialize()

	/**
	 * Build the RelationMap objects for this table relationships
	 */
	public function buildRelations()
	{
    $this->addRelation('category', 'category', RelationMap::MANY_TO_ONE, array('category_id' => 'id', ), null, null);
    $this->addRelation('vuser', 'vuser', RelationMap::MANY_TO_ONE, array('vuser_id' => 'id', ), null, null);
	} // buildRelations()

} // categoryVuserTableMap
