<?php
/**
I needed to override the default pagination in a component view template. Normally the pagination is shown with the line 
<?php echo $this->pagination->getListFooter(); ?>
But in this instance I wanted to show the total number of filtered items.
After trawling through the files: libraries/src/Pagination/Pagination.php and layouts/joomla/pagination/links.php the answer was simple
Create a folder layouts/pagination in your component administrator folder. Copy the links.php file into that folder.
Instead of the usual getListFooter() line shown above use:
<?php echo $this->pagination->getPaginationLinks('pagination.links'); ?>
The getPaginationLinks function has the signature public function getPaginationLinks($layoutId = 'joomla.pagination.links', $options = []) so it will take an alternative layout to use.
Now you can modify the links.php file for your component to get exactly the output you require.
*/

namespace sportsmanagement\Site\Model;

\defined('_JEXEC') or die();


use Joomla\CMS\Pagination\Pagination;

class JSMSportsmanagementPagination extends Pagination 
{

public function getPaginationLinks($layoutId = 'joomla.pagination.links', $options = [])
{
    // Include your own layout below:
    $layoutId = 'my.pagination.links';

    $list = [
        'prefix'       => $this->prefix,
        'limit'        => $this->limit,
        'limitstart'   => $this->limitstart,
        'total'        => $this->total,
        'limitfield'   => $this->getLimitBox(),
        'pagescounter' => $this->getPagesCounter(),
        'pages'        => $this->getPaginationPages(),
        'pagesTotal'   => $this->pagesTotal,
    ];

//echo 'getLimitBox<pre>'.print_r($this->getLimitBox(),true).'</pre>';
//echo 'getPagesCounter<pre>'.print_r($this->getPagesCounter(),true).'</pre>';
//echo 'getPaginationPages<pre>'.print_r($this->getPaginationPages(),true).'</pre>';
//echo 'pagesTotal<pre>'.print_r($this->pagesTotal(),true).'</pre>';
    
    return LayoutHelper::render($layoutId, ['list' => $list, 'options' => $options]);
}
  
}
