<?php
/**
 * Native Joomla 5/6 administrator list view for prediction rounds.
 *
 * @version    5.6.0
 * @author     diddipoeler
 * @copyright  Copyright (C) diddipoeler
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
namespace Diddipoeler\Component\SportsManagement\Administrator\View\Predictionrounds;

\defined('_JEXEC') or die;

use Diddipoeler\Component\SportsManagement\Administrator\Model\PredictionroundsModel;
use Joomla\CMS\Application\AdministratorApplication;
use Diddipoeler\Component\SportsManagement\Administrator\Service\SportsManagementAdministratorApplicationResolver;
use Joomla\CMS\Language\Text;
use Joomla\CMS\MVC\View\HtmlView as BaseHtmlView;
use Joomla\CMS\Toolbar\ToolbarHelper;

/** Native Joomla 5/6 administrator list view for prediction rounds. */
final class HtmlView extends BaseHtmlView
{
    public array $items = [];
    public $pagination;
    public $state;
    public $filterForm;
    public array $activeFilters = [];
    public int $prediction_id = 0;
    public $pred_project = null;
    public $user;
    public string $sortDirection = 'ASC';
    public string $sortColumn = 'roundcode';

    /**
     * Render the prediction-round administration list and its filters.
     *
     * Reads state, pagination and rows directly from the native list model,
     * retrieves the selected prediction game and displays a translated
     * notice for empty selections before registering toolbar actions.
     *
     * @param string|null $tpl Optional Joomla layout suffix.
     * @return void
     * @throws \RuntimeException If the model is unavailable or reports errors.
     */
    public function display($tpl = null)
    {
        $app = self::administratorApplication();
        $model = $this->getModel();

        if (!$model instanceof PredictionroundsModel) {
            throw new \RuntimeException('PredictionroundsModel is unavailable.', 500);
        }

        $this->state = $model->getState();
        $this->items = $model->getItems() ?: [];
        $this->pagination = $model->getPagination();
        $this->filterForm = $model->getFilterForm();
        $this->activeFilters = $model->getActiveFilters() ?: [];
        $this->prediction_id = (int) $this->state->get('filter.prediction_id', 0);
        $this->pred_project = $model->getPredictionGame($this->prediction_id);
        $this->user = $app->getIdentity();
        $this->sortDirection = (string) $this->state->get('list.direction', 'ASC');
        $this->sortColumn = (string) $this->state->get('list.ordering', 'roundcode');

        if ($errors = $model->getErrors()) {
            throw new \RuntimeException(implode("\n", $errors), 500);
        }

        if (!$this->items) {
            if ($this->prediction_id <= 0) {
                $app->enqueueMessage(
                    Text::sprintf(
                        'COM_SPORTSMANAGEMENT_ADMIN_PGAMES_NO_PREDICTION_ID',
                        Text::_('COM_SPORTSMANAGEMENT_GLOBAL_SELECT_PRED_GAME')
                    ),
                    'warning'
                );
            } else {
                $app->enqueueMessage(
                    Text::_('COM_SPORTSMANAGEMENT_ADMIN_PGAMES_NO_PREDICTION_TIPPROUNDS'),
                    'warning'
                );
            }
        }

        $this->addToolbar();
        parent::display($tpl);
    }

    /**
     * Resolve the active administrator client through the shared service.
     * Keeps application validation consistent with other prediction views.
     * @return AdministratorApplication Validated Joomla backend application.
     */
    private static function administratorApplication(): AdministratorApplication
    {
        return SportsManagementAdministratorApplicationResolver::resolve();
    }

    /**
     * Configure prediction-round navigation and edit actions.
     * Provides publication, bulk save, import and deletion tasks for the
     * selected prediction game through Joomla's administrator toolbar.
     * @return void
     */
    private function addToolbar(): void
    {
        ToolbarHelper::title(Text::_('COM_SPORTSMANAGEMENT_ADMIN_PREDICITIONROUNDS_TITLE'), 'calendar');
        ToolbarHelper::back('JPREV', 'index.php?option=com_sportsmanagement&view=predictiongames');
        ToolbarHelper::publishList('predictionrounds.publish');
        ToolbarHelper::unpublishList('predictionrounds.unpublish');
        ToolbarHelper::apply('predictionrounds.saveshort');
        ToolbarHelper::addNew(
            'predictionrounds.populateFromProjectRounds',
            Text::_('COM_SPORTSMANAGEMENT_ADMIN_PREDICITIONROUNDS_IMPORT_BUTTON')
        );
        ToolbarHelper::deleteList('', 'predictionrounds.delete', 'JTOOLBAR_DELETE');
    }
}
