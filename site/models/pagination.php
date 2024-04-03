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
use Joomla\CMS\Pagination\PaginationObject;
use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Router\Route;

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

  public function getPagesLinks()
    {
        // Build the page navigation list.
        $data = $this->_buildDataObject();

      //Factory::getApplication()->enqueueMessage('data <pre>'.print_r($data,true).'</pre>', 'type');
      
        $list           = [];
        $list['prefix'] = $this->prefix;

        $chromePath = JPATH_THEMES . '/' . $this->app->getTemplate() . '/html/pagination.php';

        if (is_file($chromePath)) {
            include_once $chromePath;
        }

        // Build the select list
        if ($data->all->base !== null) {
            $list['all']['active'] = true;
            $list['all']['data']   = $this->_item_active($data->all);
        } else {
            $list['all']['active'] = false;
            $list['all']['data']   = $this->_item_inactive($data->all);
        }

        if ($data->start->base !== null) {
            $list['start']['active'] = true;
            $list['start']['data']   = $this->_item_active($data->start);
        } else {
            $list['start']['active'] = false;
            $list['start']['data']   = $this->_item_inactive($data->start);
        }

        if ($data->previous->base !== null) {
            $list['previous']['active'] = true;
            $list['previous']['data']   = $this->_item_active($data->previous);
        } else {
            $list['previous']['active'] = false;
            $list['previous']['data']   = $this->_item_inactive($data->previous);
        }

        // Make sure it exists
        $list['pages'] = [];

        foreach ($data->pages as $i => $page) {
            if ($page->base !== null) {
                $list['pages'][$i]['active'] = true;
                $list['pages'][$i]['data']   = $this->_item_active($page);
            } else {
                $list['pages'][$i]['active'] = false;
                $list['pages'][$i]['data']   = $this->_item_inactive($page);
            }
        }

        if ($data->next->base !== null) {
            $list['next']['active'] = true;
            $list['next']['data']   = $this->_item_active($data->next);
        } else {
            $list['next']['active'] = false;
            $list['next']['data']   = $this->_item_inactive($data->next);
        }

        if ($data->end->base !== null) {
            $list['end']['active'] = true;
            $list['end']['data']   = $this->_item_active($data->end);
        } else {
            $list['end']['active'] = false;
            $list['end']['data']   = $this->_item_inactive($data->end);
        }

      
      //Factory::getApplication()->enqueueMessage('<pre>'.print_r($list['pages'],true).'</pre>', 'notice');
      
      
        if ($this->total > $this->limit) {
            return $this->_list_render($list);
        } else {
            return '';
        }
    }
  
  
  
  
protected function _buildDataObject()
    {
        $data = new \stdClass();

  $menu = Factory::getApplication()->getMenu();
$item = $menu->getActive();
//Factory::getApplication()->enqueueMessage('item<pre>'.print_r($item->id,true).'</pre>', 'notice'); 
//Factory::getApplication()->enqueueMessage('link<pre>'.print_r($item->link,true).'</pre>', 'notice');
  
  
  $itemid = Factory::getApplication()->input->getVar('Itemid');
  //Factory::getApplication()->enqueueMessage('item id<pre>'.print_r($itemid,true).'</pre>', 'notice'); 
  //$menu->setDefault($itemid);
  
        // Build the additional URL parameters string.
        $params = '';
        $params .= '&Itemid=' . $itemid;
        if (!empty($this->additionalUrlParams)) {
            foreach ($this->additionalUrlParams as $key => $value) {
                $params .= '&' . $key . '=' . $value;
            }
        }

  //Factory::getApplication()->enqueueMessage('params<pre>'.print_r($params,true).'</pre>', 'notice');
  //Factory::getApplication()->enqueueMessage('prefix<pre>'.print_r($this->prefix,true).'</pre>', 'notice');
  
        $data->all = new PaginationObject(Text::_('JLIB_HTML_VIEW_ALL'), $this->prefix);
//Factory::getApplication()->enqueueMessage(__LINE__.' all <pre>'.print_r($data->all,true).'</pre>', 'notice'); 
  
  
        if (!$this->viewall) {
            $data->all->base = '0';
            $data->all->link = Route::_($params . '&' . $this->prefix . 'limitstart=');
  //Factory::getApplication()->enqueueMessage(__LINE__.' all link<pre>'.print_r($data->all->link,true).'</pre>', 'notice');         
          //$data->all->link = $itemid < 0 ? preg_replace('Itemid='.$item->id, 'Itemid='.$itemid, $data->all->link) : $data->all->link;
          
          
        }

        // Set the start and previous data objects.
        $data->start    = new PaginationObject(Text::_('JLIB_HTML_START'), $this->prefix);
        $data->previous = new PaginationObject(Text::_('JPREV'), $this->prefix);

//$data->all->link = preg_replace('Itemid='.$item->id, 'Itemid='.$itemid, $data->all->link);  
  
//Factory::getApplication()->enqueueMessage(__LINE__.' all<pre>'.print_r($data->all,true).'</pre>', 'notice');  
//Factory::getApplication()->enqueueMessage(__LINE__.' all link<pre>'.print_r($data->all->link,true).'</pre>', 'notice');  
//Factory::getApplication()->enqueueMessage(__LINE__.' start<pre>'.print_r($data->start,true).'</pre>', 'notice');  
//Factory::getApplication()->enqueueMessage(__LINE__.' previous<pre>'.print_r($data->previous,true).'</pre>', 'notice');  
  
        if ($this->pagesCurrent > 1) {
            $page = ($this->pagesCurrent - 2) * $this->limit;

            if ($this->hideEmptyLimitstart) {
                $data->start->link = Route::_($params . '&' . $this->prefix . 'limitstart=');
            } else {
                $data->start->link = Route::_($params . '&' . $this->prefix . 'limitstart=0');
            }

            $data->start->base    = '0';
            $data->previous->base = $page;

            if ($page === 0 && $this->hideEmptyLimitstart) {
                $data->previous->link = $data->start->link;
            } else {
                $data->previous->link = Route::_($params . '&' . $this->prefix . 'limitstart=' . $page);
            }
        }

        // Set the next and end data objects.
        $data->next = new PaginationObject(Text::_('JNEXT'), $this->prefix);
        $data->end  = new PaginationObject(Text::_('JLIB_HTML_END'), $this->prefix);

  
//Factory::getApplication()->enqueueMessage('next<pre>'.print_r($data->next,true).'</pre>', 'notice');  
//Factory::getApplication()->enqueueMessage('end<pre>'.print_r($data->end,true).'</pre>', 'notice');    
  
  
  
  
  
  
  
  
  
        if ($this->pagesCurrent < $this->pagesTotal) {
            $next = $this->pagesCurrent * $this->limit;
            $end  = ($this->pagesTotal - 1) * $this->limit;

            $data->next->base = $next;
            $data->next->link = Route::_($params . '&' . $this->prefix . 'limitstart=' . $next);
            $data->end->base  = $end;
            $data->end->link  = Route::_($params . '&' . $this->prefix . 'limitstart=' . $end);
        }

        $data->pages = [];
        $stop        = $this->pagesStop;

        for ($i = $this->pagesStart; $i <= $stop; $i++) {
            $offset = ($i - 1) * $this->limit;

            $data->pages[$i] = new PaginationObject($i, $this->prefix);

            if ($i != $this->pagesCurrent || $this->viewall) {
                $data->pages[$i]->base = $offset;

                if ($offset === 0 && $this->hideEmptyLimitstart) {
                    $data->pages[$i]->link = $data->start->link;
                } else {
                    $data->pages[$i]->link = Route::_($params . '&' . $this->prefix . 'limitstart=' . $offset);
                }
            } else {
                $data->pages[$i]->active = true;
            }
        }

        return $data;
    }  
  
  
  
  
  
  
  
  
  
  
  
  
  
  
  
  
  
  
  
  
  
  
  
  
    
  
  
}
