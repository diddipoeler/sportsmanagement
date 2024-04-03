<?php

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

//echo 'getLimitBox<pre>.print_r($this->getLimitBox(),true).'</pre>;
//echo 'getPagesCounter<pre>.print_r($this->getPagesCounter(),true).'</pre>;
//echo 'getPaginationPages<pre>.print_r($this->getPaginationPages(),true).'</pre>;
//echo 'pagesTotal<pre>.print_r($this->pagesTotal(),true).'</pre>;
    
    return LayoutHelper::render($layoutId, ['list' => $list, 'options' => $options]);
}
  
}
