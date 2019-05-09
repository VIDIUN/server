<?php
/**
 * @package api
 * @subpackage objects
 */
class VidiunEntryServerNodeArray extends VidiunTypedArray
{
	public static function fromDbArray($arr)
	{
		$newArr = new VidiunEntryServerNodeArray();
		foreach($arr as $obj)
		{
			/* @var $obj VidiunEntryServerNode */
			$nObj = VidiunEntryServerNode::getInstance($obj);
			if (!$nObj)
			{
				throw new VidiunAPIException(VidiunErrors::ENTRY_SERVER_NODE_OBJECT_TYPE_ERROR, $obj->getServerType(), $obj->getId());
			}
			$nObj->fromObject($obj);
			$newArr[] = $nObj;
		}

		return $newArr;
	}

	public function __construct()
	{
		return parent::__construct("VidiunEntryServerNode");
	}

}