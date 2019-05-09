<?php
/**
 * Enable the plugin to load and search extended objects and types
 * @package infra
 * @subpackage Plugins
 */
interface IVidiunPlaybackContextDataContributor
{
    /**
     * Receives the context-data result and adds an instance of VidiunPluginData to the pluginData containing
     * the specific plugins context-data.
     *
     * @param entry $entry
     * @param vPlaybackContextDataParams $entryPlayingDataParams
     * @param vPlaybackContextDataResult $result
     * @param vContextDataHelper $contextDataHelper
     * @param string $type
     */
    public function contributeToPlaybackContextDataResult(entry $entry, vPlaybackContextDataParams $entryPlayingDataParams, vPlaybackContextDataResult $result, vContextDataHelper $contextDataHelper);

    /**
     * @param $streamerType
     * @return boolean
     */
    public function isSupportStreamerTypes($streamerType);

    /**
     * @param $drmProfile
     * @param $scheme
     * @param $customDataObject
     * @return boolean
     */
    public function constructUrl($drmProfile, $scheme, $customDataObject);

}
