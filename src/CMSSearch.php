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

    /** @var array Url get parameters */
    public $getParams = array();

    /** @var callable|null External materialfield query handler */
    public $MatFieldExternalHandler = null;

    /** @var callable|null External material query handler */
    public $MaterialExternalHandler = null;

    /** @var int Count of items for pager */
    public $itemsOnPage = 10;

    /** @var string Url prefix for pager links */
    public $pagerPrefix = 'search';

    public $structures;

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
                                $matFieldHandler = null,
                                $materialHandler = null,
                                $structures = null)
    {
        $this->searchFields = $searchFields;
        $this->key = $searchKey;
        $this->getParams = $getParams;
        $this->itemsOnPage = $itemsOnPage;
        $this->pagerPrefix = $pagerPrefix;

        if (is_callable($matFieldHandler)) {
            $this->MatFieldExternalHandler = $matFieldHandler;
        }
        if (is_callable($materialHandler)) {
            $this->MaterialExternalHandler = $materialHandler;
        }

        if (isset($structures)) {
            $this->structures = is_array($structures) ? $structures : array($structures);
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

        // Set external query handler
        if (isset($this->MatFieldExternalHandler)) {
            $materialIds->handler($this->MatFieldExternalHandler);
        }

        $result = dbQuery('\samson\cms\CMSMaterial');

        // Try to get founded materials
        $arrayIds = array();
        if ($materialIds->fieldsNew('MaterialID', $arrayIds)) {
            $result = dbQuery('\samson\cms\CMSMaterial')
                ->cond('MaterialID', $arrayIds)
                ->join('gallery');

            if (isset($this->structures)) {
                $result->join('\samson\cms\CMSNavMaterial')->cond('structurematerial_StructureID', $this->structures);
            }
        } else { // Create 100% empty condition
            $result->cond('MaterialID', 0);
        }

        // Call external material handler
        if (isset($this->MaterialExternalHandler)) {
            $result->handler($this->MaterialExternalHandler);
        }

        // Clone for count query
        $materialsCount = clone $result;

        // Create pager
        $this->pager = new \samson\pager\Pager($page, $this->itemsOnPage, $this->pagerPrefix, null, $this->getParams);
        $this->pager->update($materialsCount->count());
        $result->limit($this->pager->start, $this->pager->end);

        // Return query result
        return $result->exec();
    }
}
