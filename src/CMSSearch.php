<?php
/**
 * Created by PhpStorm.
 * User: onysko
 * Date: 20.10.2014
 * Time: 10:43
 */

namespace samsonos\cms\search;

use samson\activerecord\Condition;
use samson\activerecord\dbRelation;


class CMSSearch {
    /** @var array Collection of field identifiers for searching */
    public $searchFields = array();

    /** @var string Value for searching */
    public $key = '';

    /** @var  \samson\pager\Pager */
    public $pager;

    /** @var array Utr get parameters */
    public $getParams = array();

    /** @var callable|null External query handler */
    public $externalHandler = null;

    /** @var int Count of items for pager */
    public $itemsOnPage = 10;

    /** @var string Url prefix for pager links */
    public $pagerPrefix = 'search';

    /**
     * @param string $searchKey Value for searching
     * @param array $searchFields Collection of field identifiers for searching
     * @param array $getParams Get params
     * @param int $itemsOnPage Count of items for pager
     * @param string $pagerPrefix Url prefix for pager links
     * @param callable $handler External query handler
     */
    public function __construct($searchKey,
                                array $searchFields = array(),
                                array $getParams = array(),
                                $itemsOnPage = 8,
                                $pagerPrefix = 'search',
                                $handler = null)
    {
        $this->searchFields = $searchFields;
        $this->key = $searchKey;
        $this->getParams = $getParams;
        $this->itemsOnPage = $itemsOnPage;
        $this->pagerPrefix = $pagerPrefix;

        if (is_callable($handler)) {
            $this->externalHandler = $handler;
        }
    }

    public function searchMaterials($page = 1)
    {
        // Create condition for searching
        $conditionOR = new Condition('OR');

        $conditionOR->add('Value', '%'.$this->key.'%', dbRelation::LIKE);

        $words = explode(' ', $this->key);
        // If we have many words in searching value
        if (sizeof($words) > 1) {
            foreach ($words as $word) {
                $conditionOR->add('Value', '%'.$word.'%', dbRelation::LIKE);
            }
        }

        if (is_numeric($this->key)) {
            $conditionOR->add('numeric_value', intval($this->key));
        }

        // Get collection of founded material identifiers
        $materialIds = dbQuery('materialfield')
            ->cond($conditionOR)
            ->cond('FieldID', $this->searchFields)
            ->cond('Active', 1)
            ->join('material')
            ->cond('material.Active', 1)
            ->cond('material.Published', 1)
            ->cond('material.Draft', 0)
            ->group_by('MaterialID');

        if (isset($this->externalHandler)) {
            call_user_func($this->externalHandler, array(&$materialIds));
        }

        // Clone for count query
        $materialsCount = clone $materialIds;

        // Create pager
        $this->pager = new \samson\pager\Pager($page, $this->itemsOnPage, $this->pagerPrefix, null, $this->getParams);
        $this->pager->update($materialsCount->count());
        $materialIds->limit($this->pager->start, $this->pager->end);

        $result = array();

        // Try to get founded materials
        if ($materialIds->fields('MaterialID', $arrayIds)) {
            $result = dbQuery('\samson\cms\CMSMaterial')
                ->cond('MaterialID', $arrayIds)
                ->join('gallery')
                ->exec();
        }

        // Return query result
        return $result;
    }
}
