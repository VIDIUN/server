<?php
/**
 * @package Core
 * @subpackage model.enum
 */
interface EntryDisplayInSearchType extends BaseEnum
{
    const SYSTEM = -1;
    const NONE = 0;
    const PARTNER_ONLY = 1;
    const VIDIUN_NETWORK = 2;
}
