<?php
/**
 * @package     SportsManagement
 * @subpackage  com_sportsmanagement
 */

namespace Diddipoeler\Component\SportsManagement\Administrator\View\Divisions;

\defined('_JEXEC') or die;

use Joomla\CMS\Language\Text;
use Joomla\CMS\MVC\View\HtmlView as BaseHtmlView;
use Joomla\CMS\Toolbar\ToolbarHelper;

/**
 * Native Joomla 5/6 divisions list view.
 */
class HtmlView extends BaseHtmlView
{
    public $items = [];
    public $pagination;
    public $state;
    public $project;
    public int $projectId = 0;

    public function display($tpl = null)
    {
        $this->items = $this->get('Items') ?: [];
        $this->pagination = $this->get('Pagination');
        $this->state = $this->get('State');
        $this->project = $this->get('Project');
        $this->projectId = (int) $this->get('ProjectId');

        if ($errors = $this->get('Errors')) {
            throw new \RuntimeException(implode("\n", $errors), 500);
        }

        $title = Text::_('COM_SPORTSMANAGEMENT_ADMIN_DIVS_TITLE');

        if ($this->project && !empty($this->project->name)) {
            $title .= ': ' . $this->project->name;
        }

        ToolbarHelper::title($title, 'tree');

        if ($this->projectId > 0) {
            ToolbarHelper::back(
                'JPREV',
                'index.php?option=com_sportsmanagement&view=project&layout=panel&id=' . $this->projectId
            );
        }

        ToolbarHelper::publish('divisions.publish', 'JTOOLBAR_PUBLISH', true);
        ToolbarHelper::unpublish('divisions.unpublish', 'JTOOLBAR_UNPUBLISH', true);
        ToolbarHelper::checkin('divisions.checkin');
        ToolbarHelper::addNew('division.add');
        ToolbarHelper::editList('division.edit');

        parent::display($tpl);
    }
}
