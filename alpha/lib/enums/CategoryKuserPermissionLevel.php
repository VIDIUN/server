<?php
/**
 * @package Core
 * @subpackage model.enum
 */ 
interface CategoryVuserPermissionLevel extends BaseEnum
{
	const MANAGER = 0;
	const MODERATOR = 1;
	const CONTRIBUTOR = 2;
	const MEMBER = 3;
	const NONE = 4;
}
