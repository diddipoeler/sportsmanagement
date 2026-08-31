<?php
namespace Diddipoeler\Component\SportsManagement\Administrator\View\Predictionrounds;

\defined('_JEXEC') or die;

use Diddipoeler\Component\SportsManagement\Administrator\Model\PredictionroundsModel;
use Joomla\CMS\Factory;
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

    public function display($tpl = null)
    {
        $model = $this->getModel();

        if (!$model instanceof PredictionroundsModel) {
            throw new \RuntimeException('PredictionroundsModel is unavailable.', 500);
        }

        $this->state = $this->get('State');
        $this->items = $this->get('Items') ?: [];
        $this->pagination = $this->get('Pagination');
        $this->filterForm = $this->get('FilterForm');
        $this->activeFilters = $this->get('ActiveFilters') ?: [];
        $this->prediction_id = (int) $this->state->get('filter.prediction_id', 0);
        $this->pred_project = $model->getPredictionGame($this->prediction_id);
        $this->user = Factory::getApplication()->getIdentity();
        $this->sortDirection = (string) $this->state->get('list.direction', 'ASC');
        $this->sortColumn = (string) $this->state->get('list.ordering', 'roundcode');

        if ($errors = $this->get('Errors')) {
            throw new \RuntimeException(implode("\n", $errors), 500);
        }

        if (!$this->items) {
            if ($this->prediction_id <= 0) {
                Factory::getApplication()->enqueueMessage(
                    Text::sprintf(
                        'COM_SPORTSMANAGEMENT_ADMIN_PGAMES_NO_PREDICTION_ID',
                        Text::_('COM_SPORTSMANAGEMENT_GLOBAL_SELECT_PRED_GAME')
                    ),
                    'warning'
                );
            } else {
                Factory::getApplication()->enqueueMessage(
                    Text::_('COM_SPORTSMANAGEMENT_ADMIN_PGAMES_NO_PREDICTION_TIPPROUNDS'),
                    'warning'
                );
            }
        }

        $this->addToolbar();
        parent::display($tpl);
    }

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
