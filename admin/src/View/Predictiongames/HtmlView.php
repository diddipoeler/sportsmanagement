<?php
/**
 * Native Joomla 5/6 administrator list view for prediction games.
 *
 * @version    5.6.0
 * @author     diddipoeler
 * @copyright  Copyright (C) diddipoeler
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
namespace Diddipoeler\Component\SportsManagement\Administrator\View\Predictiongames;

\defined('_JEXEC') or die;

use Diddipoeler\Component\SportsManagement\Administrator\Model\PredictiongamesModel;
use Joomla\CMS\Application\AdministratorApplication;
use Diddipoeler\Component\SportsManagement\Administrator\Service\SportsManagementAdministratorApplicationResolver;
use Joomla\CMS\Language\Text;
use Joomla\CMS\MVC\View\HtmlView as BaseHtmlView;
use Joomla\CMS\Toolbar\ToolbarHelper;

/** Native Joomla 5/6 administrator list view for prediction games. */
final class HtmlView extends BaseHtmlView
{
    public array $items = [];
    public $pagination;
    public $state;
    public int $prediction_id = 0;
    public array $predictionProjects = [];
    public $pred_project = null;
    public array $predictionOptions = [];
    public array $projectCounts = [];
    public array $adminCounts = [];
    public array $activeRoundCounts = [];
    public array $projectRoundCounts = [];
    public $user;
    public string $sortDirection = 'ASC';
    public string $sortColumn = 'pre.name';

    /**
     * Render the administrator list of prediction games.
     * Reads list rows, filter state and pagination from the typed native
     * model, prepares project and administrator counts, and supplies
     * prediction-round relations for the selected game before rendering.
     * @param string|null $tpl Optional layout suffix.
     * @return void
     * @throws \RuntimeException If the required model is unavailable or reports errors.
     */
    public function display($tpl = null)
    {
        $model = $this->getModel();

        if (!$model instanceof PredictiongamesModel) {
            throw new \RuntimeException('PredictiongamesModel is unavailable.', 500);
        }

        $this->state = $model->getState();
        $this->items = $model->getItems() ?: [];
        $this->pagination = $model->getPagination();
        $this->prediction_id = (int) $this->state->get('filter.prediction_id', 0);
        $this->pred_project = $model->getPredictionGame($this->prediction_id);
        $this->predictionOptions = $model->getPredictionGames();
        $this->user = self::administratorApplication()->getIdentity();
        $this->sortDirection = (string) $this->state->get('list.direction', 'ASC');
        $this->sortColumn = (string) $this->state->get('list.ordering', 'pre.name');

        foreach ($this->items as $item) {
            $id = (int) ($item->id ?? 0);
            $this->projectCounts[$id] = count($model->getChilds($id));
            $this->adminCounts[$id] = count((array) $model->getAdmins($id));
        }

        if ($this->prediction_id > 0) {
            $this->predictionProjects = array_values($model->getChilds($this->prediction_id));

            foreach ($this->predictionProjects as $relation) {
                $relationId = (int) ($relation['id'] ?? 0);
                $predictionId = (int) ($relation['prediction_id'] ?? $this->prediction_id);
                $projectId = (int) ($relation['project_id'] ?? 0);
                $this->activeRoundCounts[$relationId] = $model->getActivePredictionRoundsCount($predictionId);
                $this->projectRoundCounts[$relationId] = $model->getProjectRoundsCount($projectId);
            }
        }

        if ($errors = $model->getErrors()) {
            throw new \RuntimeException(implode("\n", $errors), 500);
        }

        if (!$this->items) {
            self::administratorApplication()->enqueueMessage(
                Text::_('COM_SPORTSMANAGEMENT_ADMIN_PGAMES_NO_GAMES'),
                'warning'
            );
        }

        $this->addToolbar();
        parent::display($tpl);
    }

    /**
     * Create prediction game list actions in the administrator toolbar.
     * @return void
     */
    private function addToolbar(): void
    {
        ToolbarHelper::title(Text::_('COM_SPORTSMANAGEMENT_ADMIN_PGAMES_TITLE'), 'list');
        ToolbarHelper::publish('predictiongames.publish', 'JTOOLBAR_PUBLISH', true);
        ToolbarHelper::unpublish('predictiongames.unpublish', 'JTOOLBAR_UNPUBLISH', true);
        ToolbarHelper::editList('predictiongame.edit');
        ToolbarHelper::addNew('predictiongame.add');
    }
    /**
     * Resolve the Joomla administrator application through the shared service.
     * Reuses the component's client and dependency validation for toolbar
     * permissions and administrator notifications.
     * @return AdministratorApplication Active backend application.
     */
    private static function administratorApplication(): AdministratorApplication
    {
        return SportsManagementAdministratorApplicationResolver::resolve();
    }

}
