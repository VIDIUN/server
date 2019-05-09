<?php
/**
 * Enable the plugin to load and search extended objects and types
 * @package infra
 * @subpackage Plugins
 */
interface IVidiunEntryContextDataContributor {

    /**
     * Receives the context-data result and adds an instance of VidiunPluginData to the pluginData containing
     * the specific plugins context-data.
     *
     * @param entry $entry
     * @param accessControlScope $contextDataParams
     * @param contributeToEntryContextDataResult $result
     * @return PluginData
     */
    public function contributeToEntryContextDataResult(entry $entry, accessControlScope $contextDataParams, vContextDataHelper $contextDataHelper);
}