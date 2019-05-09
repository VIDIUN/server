<?php
/**
 * @package plugins.varConsole
 * @subpackage api.types
 */
class VidiunPartnerUsageListResponse extends VidiunListResponse
{
    /**
     * @var VidiunVarPartnerUsageItem
     */
    public $total;
    /**
     * @var VidiunVarPartnerUsageArray
     */
    public $objects;
}