<?php
/**
 * Native Joomla 5/6 administrator list view for prediction groups.
 *
 * @version    5.6.0
 * @author     diddipoeler
 * @copyright  Copyright (C) diddipoeler
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
namespace Diddipoeler\Component\SportsManagement\Administrator\View\Predictiongroups;

\defined('_JEXEC') or die;

use Joomla\CMS\Application\AdministratorApplication;
use Diddipoeler\Component\SportsManagement\Administrator\Model\PredictiongroupsModel;
use Diddipoeler\Component\SportsManagement\Administrator\Service\SportsManagementAdministratorApplicationResolver;
use Joomla\CMS\Log\Log;
use Joomla\CMS\Language\Text;
use Joomla\CMS\MVC\View\HtmlView as BaseHtmlView;
use Joomla\CMS\Toolbar\ToolbarHelper;

/** Native Joomla 5/6 administrator list view for prediction groups. */
final class HtmlView extends BaseHtmlView
{
    public array $items = [];
    public $pagination;
    public $state;
    public $filterForm;
    public array $activeFilters = [];
    public $user;
    public string $sortDirection = 'ASC';
    public string $sortColumn = 's.name';

    /**
     * Load and render the administrator prediction-groups overview.
     *
     * Requires the native prediction-groups model, reads records, pagination
     * and filters directly, obtains the current identity,
     * resolve ordering from model state, report loading errors and show a
     * warning if no prediction groups exist. Configure the relevant list
     * toolbar before delegating output to Joomla's base HTML view.
     *
     * @param string|null $tpl Optional Joomla layout suffix.
     * @return void The list is rendered by Joomla.
     * @throws \RuntimeException If the view model reports errors.
     */
    public function display($tpl = null)
    {
        $model = $this->getModel();

        if (!$model instanceof PredictiongroupsModel) {
            throw new \RuntimeException('PredictiongroupsModel is unavailable.', 500);
        }

        $this->items = $model->getItems() ?: [];
        $this->pagination = $model->getPagination();
        $this->state = $model->getState();
        $this->filterForm = $model->getFilterForm();
        $this->activeFilters = $model->getActiveFilters() ?: [];
        $this->user = self::administratorApplication()->getIdentity();
        $this->sortDirection = (string) $this->state->get('list.direction', 'ASC');
        $this->sortColumn = (string) $this->state->get('list.ordering', 's.name');

        if ($errors = $model->getErrors()) {
            Log::add(__METHOD__ . ': model reported ' . count((array) $errors) . ' errors.', Log::ERROR, 'com_sportsmanagement');
            throw new \RuntimeException(Text::_('JERROR_AN_ERROR_HAS_OCCURRED'), 500);
        }

        if (!$this->items) {
            self::administratorApplication()->enqueueMessage(
                Text::_('COM_SPORTSMANAGEMENT_ADMIN_PGAMES_NO_GROUPS'),
                'warning'
            );
        }

        $this->addToolbar();
        parent::display($tpl);
    }

    /**
     * Configure administrator operations for prediction groups.
     *
     * Add the translated list title as well as create, edit, delete
     * and check-in actions mapped to their Joomla controller tasks.
     *
     * @return void Toolbar buttons are registered.
     */
    private function addToolbar(): void
    {
        ToolbarHelper::title(Text::_('COM_SPORTSMANAGEMENT_ADMIN_PREDICTIONGROUPS_TITLE'), 'users');
        ToolbarHelper::addNew('predictiongroup.add');
        ToolbarHelper::editList('predictiongroup.edit');
        ToolbarHelper::deleteList('', 'predictiongroups.delete', 'JTOOLBAR_DELETE');
        ToolbarHelper::checkin('predictiongroups.checkin');
    }
    /**
     * Resolve the active Joomla backend application.
     *
     * Reuse the component's validated administrator application service
     * and reject non-administrator execution before accessing
     * the user's identity and notification queue.
     *
     * @return AdministratorApplication Valid backend application.
     * @throws \RuntimeException If not running in the administrator client.
     */
    private static function administratorApplication(): AdministratorApplication
    {
        return SportsManagementAdministratorApplicationResolver::resolve();
    }

}
