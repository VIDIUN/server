<?php
/**
 * @package Core
 * @subpackage model.enum
 */
interface VVoteStatus extends BaseEnum
{
    const REVOKED = 1;
    
    const VOTED = 2;
}