<?php
/**
 * Native Joomla 5/6 administrator view for prediction-game members.
 *
 * @version    5.6.0
 * @author     diddipoeler
 * @copyright  Copyright (C) diddipoeler
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
namespace Diddipoeler\Component\SportsManagement\Administrator\View\Predictionmembers;

\defined('_JEXEC') or die;

use Diddipoeler\Component\SportsManagement\Administrator\Model\PredictionmembersModel;
use Joomla\CMS\Application\AdministratorApplication;
use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\CMS\MVC\View\HtmlView as BaseHtmlView;
use Joomla\CMS\Toolbar\ToolbarHelper;

/** Native Joomla 5/6 administrator view for prediction-game members. */
final class HtmlView extends BaseHtmlView
{
    public array $items = [];
    public $pagination;
    public $state;
    public $filterForm;
    public array $activeFilters = [];
    public int $prediction_id = 0;
    public string $prediction_name = '';
    public array $assignedMembers = [];
    public array $availableMembers = [];
    public $user;
    public string $sortDirection = 'ASC';
    public string $sortColumn = 'u.username';

    public function display($tpl = null)
    {
        $model = $this->getModel();

        if (!$model instanceof PredictionmembersModel) {
            throw new \RuntimeException('PredictionmembersModel is unavailable.', 500);
        }

        $app = self::administratorApplication();
        $this->user = $app->getIdentity();
        $this->state = $this->get('State');
        $layout = strtolower((string) $this->getLayout());

        if (in_array($layout, ['editlist_3', 'editlist_4'], true)) {
            $layout = 'editlist';
            $this->setLayout($layout);
        } elseif (in_array($layout, ['default_3', 'default_4'], true)) {
            $layout = 'default';
            $this->setLayout($layout);
        }

        if ($layout === 'editlist') {
            $this->prepareEditList($model, $app);
        } else {
            $this->prepareList($app);
            $this->addToolbar();
        }

        if ($errors = $this->get('Errors')) {
            throw new \RuntimeException(implode("\n", $errors), 500);
        }

        parent::display($tpl);
    }

    private function prepareList(AdministratorApplication $app): void
    {
        $this->items = $this->get('Items') ?: [];
        $this->pagination = $this->get('Pagination');
        $this->filterForm = $this->get('FilterForm');
        $this->activeFilters = $this->get('ActiveFilters') ?: [];
        $this->prediction_id = (int) $this->state->get('filter.prediction_id', 0);
        $this->sortDirection = (string) $this->state->get('list.direction', 'ASC');
        $this->sortColumn = (string) $this->state->get('list.ordering', 'u.username');

        $app->setUserState('com_sportsmanagement.prediction_id', $this->prediction_id);
    }

    private function prepareEditList(PredictionmembersModel $model, AdministratorApplication $app): void
    {
        $this->prediction_id = (int) $app->getUserState('com_sportsmanagement.prediction_id', 0);

        if ($this->prediction_id <= 0) {
            $this->prediction_id = (int) $this->state->get('filter.prediction_id', 0);
        }

        if ($this->prediction_id <= 0) {
            throw new \RuntimeException(Text::_('COM_SPORTSMANAGEMENT_GLOBAL_SELECT_PRED_GAME'), 400);
        }

        $this->prediction_name = (string) $model->getPredictionProjectName($this->prediction_id);
        $this->assignedMembers = $model->getPredictionMembers($this->prediction_id);
        $this->availableMembers = $model->getJLUsers($this->prediction_id);
    }

    private static function administratorApplication(): AdministratorApplication
    {
        /** @var AdministratorApplication $app */
        $app = Factory::getContainer()->get(AdministratorApplication::class);

        if (!$app->isClient('administrator')) {
            throw new \RuntimeException('SportsManagement prediction members view requires the Joomla administrator application.', 500);
        }

        return $app;
    }

    private function addToolbar(): void
    {
        ToolbarHelper::title(Text::_('COM_SPORTSMANAGEMENT_ADMIN_PMEMBERS_TITLE'), 'users');
        ToolbarHelper::custom(
            'predictionmembers.reminder',
            'mail',
            'mail',
            Text::_('COM_SPORTSMANAGEMENT_ADMIN_PMEMBERS_SEND_REMINDER'),
            true
        );

        if ($this->prediction_id > 0) {
            ToolbarHelper::custom(
                'predictionmembers.editlist',
                'new',
                'new',
                Text::_('COM_SPORTSMANAGEMENT_ADMIN_PMEMBERS_BUTTON_ASSIGN'),
                false
            );
            ToolbarHelper::publish('predictionmembers.publish', 'COM_SPORTSMANAGEMENT_ADMIN_PMEMBERS_APPROVE', true);
            ToolbarHelper::unpublish('predictionmembers.unpublish', 'COM_SPORTSMANAGEMENT_ADMIN_PMEMBERS_REJECT', true);
            ToolbarHelper::deleteList('', 'predictionmembers.remove');
        }

        ToolbarHelper::checkin('predictionmembers.checkin');
    }
}
