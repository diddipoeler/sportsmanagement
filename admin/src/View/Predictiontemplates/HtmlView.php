<?php
namespace Diddipoeler\Component\SportsManagement\Administrator\View\Predictiontemplates;

\defined('_JEXEC') or die;

use Diddipoeler\Component\SportsManagement\Administrator\Model\PredictiontemplatesModel;
use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\CMS\MVC\View\HtmlView as BaseHtmlView;
use Joomla\CMS\Toolbar\ToolbarHelper;

/** Native Joomla 5/6 administrator list view for prediction template settings. */
final class HtmlView extends BaseHtmlView
{
    public array $items = [];
    public $pagination;
    public $state;
    public $filterForm;
    public array $activeFilters = [];
    public int $prediction_id = 0;
    public $predictiongame = null;
    public $masterPredictionGame = null;
    public array $masterTemplates = [];
    public $user;
    public string $sortDirection = 'ASC';
    public string $sortColumn = 'tmpl.title';

    public function display($tpl = null)
    {
        $model = $this->getModel();

        if (!$model instanceof PredictiontemplatesModel) {
            throw new \RuntimeException('PredictiontemplatesModel is unavailable.', 500);
        }

        $this->state = $this->get('State');
        $this->prediction_id = (int) $this->state->get('filter.prediction_id', 0);

        if ($this->prediction_id > 0 && !$model->checklist($this->prediction_id)) {
            Factory::getApplication()->enqueueMessage(
                $model->getError() ?: Text::_('JLIB_APPLICATION_ERROR_SAVE_FAILED'),
                'error'
            );
        }

        $this->items = $this->get('Items') ?: [];
        $this->pagination = $this->get('Pagination');
        $this->filterForm = $this->get('FilterForm');
        $this->activeFilters = $this->get('ActiveFilters') ?: [];
        $this->predictiongame = $model->getPredictionGame($this->prediction_id);
        $this->user = Factory::getApplication()->getIdentity();
        $this->sortDirection = (string) $this->state->get('list.direction', 'ASC');
        $this->sortColumn = (string) $this->state->get('list.ordering', 'tmpl.title');

        $masterId = (int) ($this->predictiongame->master_template ?? 0);

        if ($masterId > 0) {
            $this->masterPredictionGame = $model->getPredictionGame($masterId);
            $this->masterTemplates = $model->getAvailableMasterTemplates($this->prediction_id);
        }

        if ($errors = $this->get('Errors')) {
            throw new \RuntimeException(implode("\n", $errors), 500);
        }

        $this->addToolbar();
        parent::display($tpl);
    }

    private function addToolbar(): void
    {
        ToolbarHelper::title(Text::_('COM_SPORTSMANAGEMENT_ADMIN_PTMPLS'), 'options');
        ToolbarHelper::back('JPREV', 'index.php?option=com_sportsmanagement&view=predictiongames');

        if ($this->masterPredictionGame && $this->items) {
            ToolbarHelper::deleteList('', 'predictiontemplates.delete', 'JTOOLBAR_DELETE');
        }
    }
}
