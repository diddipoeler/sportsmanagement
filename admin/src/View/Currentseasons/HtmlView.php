<?php
/**
 * Current-season project dashboard for Joomla 5/6.
 *
 * @version    5.6.0
 * @author     diddipoeler
 * @copyright  Copyright (C) diddipoeler
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
namespace Diddipoeler\Component\SportsManagement\Administrator\View\Currentseasons;

\defined('_JEXEC') or die;

use Diddipoeler\Component\SportsManagement\Administrator\Model\CurrentseasonsModel;
use Joomla\CMS\Document\HtmlDocument;
use Joomla\CMS\Language\Text;
use Joomla\CMS\MVC\View\HtmlView as BaseHtmlView;
use Joomla\CMS\Toolbar\ToolbarHelper;

/**
 * Current-season project dashboard for Joomla 5/6.
 */
final class HtmlView extends BaseHtmlView
{
    public array $items = [];
    public $pagination;
    public $state;

    /**
     * Populate and render the current-season projects administration view.
     *
     * Require the native current-seasons model, load its rows, pagination
     * and filter state, and reject model errors. Enable Joomla's Bootstrap
     * collapse script for the dashboard, set its translated toolbar title,
     * and delegate HTML rendering to the base Joomla view.
     *
     * @param string|null $tpl Optional dashboard template suffix.
     * @return void The administrator dashboard is rendered.
     * @throws \RuntimeException If the MVC model fails to load or reports errors.
     */
    public function display($tpl = null)
    {
        $model = $this->getModel();

        if (!$model instanceof CurrentseasonsModel) {
            throw new \RuntimeException('Currentseasons view requires CurrentseasonsModel.', 500);
        }

        $this->items = $model->getItems() ?: [];
        $this->pagination = $model->getPagination();
        $this->state = $model->getState();

        if ($errors = $this->get('Errors')) {
            throw new \RuntimeException(implode("\n", $errors), 500);
        }

        $document = $this->getDocument();

        if ($document instanceof HtmlDocument) {
            $document->getWebAssetManager()->useScript('bootstrap.collapse');
        }
        ToolbarHelper::title(Text::_('COM_SPORTSMANAGEMENT_ADMIN_PROJECTS_TITLE'), 'calendar');

        parent::display($tpl);
    }
}
