<?php
/**
 * @package api
 * @subpackage v3
 */
class VidiunServiceActionItem
{
    /**
     * @var string
     */
    public $serviceId;
    
    /**
     * @var string
     */
    public $serviceClass;
    
    /**
     * @var VidiunDocCommentParser
     */
    public $serviceInfo;
    
    /**
     * @var array
     */
    public $actionMap;
    
    public static function cloneItem (VidiunServiceActionItem $item)
    {
        $serviceActionItem = new VidiunServiceActionItem();
        $serviceActionItem->serviceId = $item->serviceId;
        $serviceActionItem->serviceClass = $item->serviceClass;
        $serviceActionItem->serviceInfo = $item->serviceInfo;
        $serviceActionItem->actionMap = $item->actionMap;
        return $serviceActionItem;
    }

}
