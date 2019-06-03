<?php


/**
 * This class defines the structure of the 'vuser_to_user_role' table.
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
class VuserToUserRoleTableMap extends TableMap {

	/**
	 * The (dot-path) name of this class
	 */
	const CLASS_NAME = 'Core.VuserToUserRoleTableMap';

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
		$this->setName('vuser_to_user_role');
		$this->setPhpName('VuserToUserRole');
		$this->setClassname('VuserToUserRole');
		$this->setPackage('Core');
		$this->setUseIdGenerator(true);
		// columns
		$this->addPrimaryKey('ID', 'Id', 'INTEGER', true, null, null);
		$this->addForeignKey('VUSER_ID', 'VuserId', 'INTEGER', 'vuser', 'ID', true, null, null);
		$this->addForeignKey('USER_ROLE_ID', 'UserRoleId', 'INTEGER', 'user_role', 'ID', true, null, null);
		$this->addColumn('CREATED_AT', 'CreatedAt', 'TIMESTAMP', false, null, null);
		$this->addColumn('UPDATED_AT', 'UpdatedAt', 'TIMESTAMP', false, null, null);
		// validators
	} // initialize()

	/**
	 * Build the RelationMap objects for this table relationships
	 */
	public function buildRelations()
	{
    $this->addRelation('vuser', 'vuser', RelationMap::MANY_TO_ONE, array('vuser_id' => 'id', ), null, null);
    $this->addRelation('UserRole', 'UserRole', RelationMap::MANY_TO_ONE, array('user_role_id' => 'id', ), null, null);
	} // buildRelations()

} // VuserToUserRoleTableMap
