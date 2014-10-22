# Material searcher for [SamsonPHP](http://samsonphp.com) framework
If you want to find some materials for a given key - the fastest way is to use class CMSSearch

## Construct parameters

In CMSSearch constructor you must define at least 2 first parameters

* **$searchKey** – required Value for searching
* **$searchFields** – required Array of identifiers from table field for searching
* **$getParams** – Url GET parameters 
* **$itemsOnPage** – Count of objects on one page for php_pager
* **$pagerPrefix** – Prefix in pager links url
* **$handler** – External handler for editing query

## Example

For example if you want to find some materials with key 'first' and create search in fields with identifiers 5, 2 and 7, you can use follow code

PHP code:

```php
// Define fields array
$fields = array(5, 2, 7);
// Create search object
$search = new samsonos\cms\search\CMSSearch('first', $fields, array('key'=>$search));

// Create view of founded objects
$materialHTML = '';
foreach ($search->searchMaterials() as $product) {
	$materialHTML .= m()->view('search/item')->product($product)->output();
}
$searchBlock = m()->view('search/items')
	->set($search->pager)
	->searchItems($materialHTML)
	->output();
```

## Crete simple preview of founded items

Thanks to CMSSearch module you can create a simple preview using your own async controller that must be defined in attribute 'preview-action' of search input.
For using that you need add class 'samson_CMS_searchInput' to your search input.
If you want to show loader while you are waiting for ajax response, you can add block with class samson_CMS_searchLoader to your html code.

## Preview Example

HTML code:

```html
<form action="<?php url_base('search') ?>"  method="get">
    <input class="samson_CMS_searchInput" preview-action="<?php url_base('search/preview') ?>" type="text" name="key" placeholder="Search some here">

    <div class="samson_CMS_searchLoader" style="display: none">
        <div id="searchLoader">
            <div class="f_circleG" id="frotateG_01">
            </div>
            <div class="f_circleG" id="frotateG_02">
            </div>
            <div class="f_circleG" id="frotateG_03">
            </div>
            <div class="f_circleG" id="frotateG_04">
            </div>
            <div class="f_circleG" id="frotateG_05">
            </div>
            <div class="f_circleG" id="frotateG_06">
            </div>
            <div class="f_circleG" id="frotateG_07">
            </div>
            <div class="f_circleG" id="frotateG_08">
            </div>
        </div>
    </div>

    <button type="submit">Search</button>
</form>
```

LESS code:

```less
.samson_CMS_searchLoader {
    top: 0;
    width: 100%;
    height: 100%;
    border-radius: 5px;
    text-align: center;
    background-color: rgba(0,0,0,0.45);
    position: absolute;
}
.samson_CMS_searchPreview {
  position: absolute;
  box-shadow: 0 0 10px rgba(0,0,0,0.5);
  width: 100%;
  top: 40px;
  border-radius: 5px;
  height: 300px;
  background: #ffffff;
  z-index: 90;

  .samson_CMS_searchPreviewItems {
    margin: 15px;
  }
}
```

PHP code:

```php
/**
* Async controller for rendering search preview
*/
function search_async_preview()
{
    $search = new \samsonos\cms\search\CMSSearch($_GET['key'], array(85, 49), array(), 5, 'search', 'searchExternalHandler');

    $response = array('status' => 1);

    $itemView = '';

    foreach ($search->searchMaterials() as $item) {
        $itemView .= m()->view('search/preview_item')->item($item)->output();
    }

    $response['html'] = $itemView;

    return $response;
}

/**
* Search external handler for modifying query
*/
function searchExternalHandler(& $query)
{
    $query = $query[0];

    $query->cond('material.MyField', 1);
}
```
