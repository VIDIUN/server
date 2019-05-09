<?php


/**
 * This class defines the structure of the 'vshow_vuser' table.
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
class VshowVuserTableMap extends TableMap {

	/**
	 * The (dot-path) name of this class
	 */
	const CLASS_NAME = 'Core.VshowVuserTableMap';

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
		$this->setName('vshow_vuser');
		$this->setPhpName('VshowVuser');
		$this->setClassname('VshowVuser');
		$this->setPackage('Core');
		$this->setUseIdGenerator(true);
		// columns
		$this->addForeignKey('VSHOW_ID', 'VshowId', 'VARCHAR', 'vshow', 'ID', false, 20, null);
		$this->addForeignKey('VUSER_ID', 'VuserId', 'INTEGER', 'vuser', 'ID', false, null, null);
		$this->addColumn('SUBSCRIPTION_TYPE', 'SubscriptionType', 'INTEGER', false, null, null);
		$this->addColumn('ALERT_TYPE', 'AlertType', 'INTEGER', false, null, null);
		$this->addPrimaryKey('ID', 'Id', 'INTEGER', true, null, null);
		// validators
	} // initialize()

	/**
	 * Build the RelationMap objects for this table relationships
	 */
	public function buildRelations()
	{
    $this->addRelation('vshow', 'vshow', RelationMap::MANY_TO_ONE, array('vshow_id' => 'id', ), null, null);
    $this->addRelation('vuser', 'vuser', RelationMap::MANY_TO_ONE, array('vuser_id' => 'id', ), null, null);
	} // buildRelations()

} // VshowVuserTableMap
