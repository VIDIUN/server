<?php


/**
 * This class defines the structure of the 'puser_role' table.
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
class PuserRoleTableMap extends TableMap {

	/**
	 * The (dot-path) name of this class
	 */
	const CLASS_NAME = 'Core.PuserRoleTableMap';

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
		$this->setName('puser_role');
		$this->setPhpName('PuserRole');
		$this->setClassname('PuserRole');
		$this->setPackage('Core');
		$this->setUseIdGenerator(true);
		// columns
		$this->addPrimaryKey('ID', 'Id', 'INTEGER', true, null, null);
		$this->addForeignKey('VSHOW_ID', 'VshowId', 'VARCHAR', 'vshow', 'ID', false, 20, null);
		$this->addForeignKey('PARTNER_ID', 'PartnerId', 'INTEGER', 'puser_vuser', 'PARTNER_ID', false, null, null);
		$this->addForeignKey('PUSER_ID', 'PuserId', 'VARCHAR', 'puser_vuser', 'PUSER_ID', false, 64, null);
		$this->addColumn('ROLE', 'Role', 'INTEGER', false, null, null);
		$this->addColumn('CREATED_AT', 'CreatedAt', 'TIMESTAMP', false, null, null);
		$this->addColumn('UPDATED_AT', 'UpdatedAt', 'TIMESTAMP', false, null, null);
		$this->addColumn('SUBP_ID', 'SubpId', 'INTEGER', false, null, 0);
		// validators
	} // initialize()

	/**
	 * Build the RelationMap objects for this table relationships
	 */
	public function buildRelations()
	{
    $this->addRelation('vshow', 'vshow', RelationMap::MANY_TO_ONE, array('vshow_id' => 'id', ), null, null);
    $this->addRelation('PuserVuserRelatedByPartnerId', 'PuserVuser', RelationMap::MANY_TO_ONE, array('partner_id' => 'partner_id', ), null, null);
    $this->addRelation('PuserVuserRelatedByPuserId', 'PuserVuser', RelationMap::MANY_TO_ONE, array('puser_id' => 'puser_id', ), null, null);
	} // buildRelations()

} // PuserRoleTableMap
