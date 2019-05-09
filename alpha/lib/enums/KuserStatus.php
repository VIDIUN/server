<?php
/**
 * @package Core
 * @subpackage model.enum
 */ 
interface VuserStatus extends BaseEnum
{
	const BLOCKED = 0;
	const ACTIVE = 1;
	const DELETED = 2;	
}