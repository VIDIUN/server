<?php
/**
 * @package plugins.crossVidiunDistribution
 * @subpackage lib.batch
 */
class CrossVidiunEntryObjectsContainer
{
    /**
     * @var VidiunBaseEntry
     */
    public $entry;
        
    /**
     * @var array<VidiunMetadata>
     */
    public $metadataObjects;
    
    /**
     * @var array<VidiunFlavorAsset>
     */
    public $flavorAssets;
    
    /**
     * @var array<VidiunContentResource>
     */
    public $flavorAssetsContent;
    
    /**
     * @var array<VidiunThumbAsset>
     */
    public $thumbAssets;
    
    /**
     * @var array<VidiunContentResource>
     */
    public $thumbAssetsContent;

    /**
     * @var array<VidiunTimedThumbAsset>
     */
    public $timedThumbAssets;

    /**
     * @var array<VidiunCaptionAsset>
     */
    public $captionAssets;
    
    /**
     * @var array<VidiunContentResource>
     */
    public $captionAssetsContent;
    
    /**
     * @var array<VidiunCuePoint>
     */
    public $cuePoints;

    /**
     * @var array<VidiunThumbCuePoint>
     */
    public $thumbCuePoints;

    /**
     * Initialize all member variables
     */
    public function __construct()
    {
        $this->entry = null;
        $this->metadataObjects = array();
        $this->flavorAssets = array();
        $this->flavorAssetsContent = array();
        $this->thumbAssets = array();
        $this->timedThumbAssets = array();		
        $this->thumbAssetsContent = array();
        $this->captionAssets = array();
        $this->captionAssetsContent = array();
        $this->cuePoints = array();
        $this->thumbCuePoints = array();
    }
}