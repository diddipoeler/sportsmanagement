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

    return LayoutHelper::render($layoutId, ['list' => $list, 'options' => $options]);
}
  
}
