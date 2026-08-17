<?php
namespace Diddipoeler\Component\SportsManagement\Administrator\View\Rosterpositions;
\defined('_JEXEC') or die;
use Joomla\CMS\Language\Text; use Joomla\CMS\MVC\View\HtmlView as BaseHtmlView; use Joomla\CMS\Toolbar\ToolbarHelper;
final class HtmlView extends BaseHtmlView
{
    public $items=[]; public $pagination; public $state; public $filterForm; public $activeFilters=[];
    public function display($tpl=null)
    {
        $this->items=$this->get('Items')?:[]; $this->pagination=$this->get('Pagination'); $this->state=$this->get('State'); $this->filterForm=$this->get('FilterForm'); $this->activeFilters=$this->get('ActiveFilters')?:[];
        if ($errors=$this->get('Errors')) throw new \RuntimeException(implode("\n",$errors),500);
        ToolbarHelper::title(Text::_('COM_SPORTSMANAGEMENT_ADMIN_ROSTERPOSITIONS_TITLE'),'grid'); ToolbarHelper::custom('rosterpositions.addhome','new','new',Text::_('COM_SPORTSMAMAGEMENT_ADMIN_ROSTERPOSITIONS_HOME'),false); ToolbarHelper::custom('rosterpositions.addaway','new','new',Text::_('COM_SPORTSMAMAGEMENT_ADMIN_ROSTERPOSITIONS_AWAY'),false); ToolbarHelper::editList('rosterposition.edit'); ToolbarHelper::publish('rosterpositions.publish','JTOOLBAR_PUBLISH',true); ToolbarHelper::unpublish('rosterpositions.unpublish','JTOOLBAR_UNPUBLISH',true); ToolbarHelper::checkin('rosterpositions.checkin'); ToolbarHelper::trash('rosterpositions.trash'); parent::display($tpl);
    }
}
