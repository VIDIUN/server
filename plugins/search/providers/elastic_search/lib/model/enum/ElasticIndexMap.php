<?php
/**
 * @package plugins.elasticSearch
 * @subpackage model.enum
 */
interface ElasticIndexMap extends BaseEnum
{
    const ELASTIC_ENTRY_INDEX = 'vidiun_entry';
    const ELASTIC_ENTRY_TYPE = 'entry';
    const ELASTIC_CATEGORY_INDEX = 'vidiun_category';
    const ELASTIC_CATEGORY_TYPE = 'category';
    const ELASTIC_VUSER_INDEX = 'vidiun_vuser';
    const ELASTIC_VUSER_TYPE = 'vuser';
}
