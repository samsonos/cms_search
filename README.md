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
