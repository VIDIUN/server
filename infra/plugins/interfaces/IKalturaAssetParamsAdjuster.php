<?php
/**
 * Enable the plugin to modify asset params
 * @package infra
 * @subpackage Plugins
 */
interface IVidiunAssetParamsAdjuster extends IVidiunBase
{
	/**
	 * @param string $entryId
	 * @param array<assetParams> $flavors
	 */
	public function adjustAssetParams($entryId, array &$flavors);	
}