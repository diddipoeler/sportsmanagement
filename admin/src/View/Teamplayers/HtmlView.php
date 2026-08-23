<?php
namespace Diddipoeler\Component\SportsManagement\Administrator\View\Teamplayers;

\defined('_JEXEC') or die;

use Diddipoeler\Component\SportsManagement\Administrator\Model\TeamplayersModel;
use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\Factory;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Layout\FileLayout;
use Joomla\CMS\MVC\View\HtmlView as BaseHtmlView;
use Joomla\CMS\Router\Route;
use Joomla\CMS\Toolbar\Toolbar;
use Joomla\CMS\Toolbar\ToolbarHelper;

/** Native Joomla 5/6 administrator roster view for team players and staff. */
final class HtmlView extends BaseHtmlView
{
    public array $items = [];
    public $pagination;
    public $state;
    public $filterForm;
    public array $activeFilters = [];
    public ?object $project = null;
    public ?object $teamContext = null;
    public array $positionOptions = [];
    public array $contextParams = [];
    public int $personType = 1;
    public int $modalwidth = 900;
    public int $modalheight = 600;
    public string $assignModal = '';
    public string $assignClubModal = '';

    public function display($tpl = null)
    {
        $app = Factory::getApplication();
        $model = $this->getModel();

        if (!$model instanceof TeamplayersModel) {
            throw new \RuntimeException('TeamplayersModel is unavailable.', 500);
        }

        $this->items = $this->get('Items') ?: [];
        $this->pagination = $this->get('Pagination');
        $this->state = $this->get('State');
        $this->project = $model->getProjectContext();
        $this->teamContext = $model->getTeamContext();
        $this->positionOptions = $model->getProjectPositionOptions();
        $this->contextParams = $model->getContextParams();
        $this->personType = (int) $this->state->get('filter.persontype', 1);

        if (!$this->project || !$this->teamContext) {
            throw new \RuntimeException(Text::_('JERROR_AN_ERROR_HAS_OCCURRED'), 404);
        }

        if ($errors = $this->get('Errors')) {
            throw new \RuntimeException(implode("\n", $errors), 500);
        }

        try {
            $this->filterForm = $this->get('FilterForm');
            $this->activeFilters = $this->get('ActiveFilters') ?: [];
        } catch (\Throwable $e) {
            $this->filterForm = null;
            $this->activeFilters = [];
            $app->enqueueMessage($e->getMessage(), 'warning');
        }

        $params = ComponentHelper::getParams('com_sportsmanagement');
        $this->modalheight = (int) $params->get('modal_popup_height', 600);
        $this->modalwidth = (int) $params->get('modal_popup_width', 900);

        $this->getDocument()->getWebAssetManager()->useScript('multiselect');
        $this->addToolbar();
        $this->prepareModals((bool) $params->get('assign_club_position_to_player', 0));

        parent::display($tpl);
    }

    private function addToolbar(): void
    {
        $titleKey = $this->personType === 2
            ? 'COM_SPORTSMANAGEMENT_ADMIN_TSTAFFS_TITLE'
            : 'COM_SPORTSMANAGEMENT_ADMIN_TPLAYERS_TITLE';
        ToolbarHelper::title(
            Text::_($titleKey) . ' ' . (string) $this->teamContext->team_name,
            'users'
        );
        ToolbarHelper::back(
            'COM_SPORTSMANAGEMENT_ADMIN_TPLAYERS_BACK',
            Route::_(
                'index.php?option=com_sportsmanagement&view=projectteams&pid=' . (int) $this->project->id,
                false
            )
        );
        ToolbarHelper::apply(
            'teamplayers.saveshort',
            Text::_('COM_SPORTSMANAGEMENT_ADMIN_TPLAYERS_APPLY')
        );
        ToolbarHelper::publish('teamplayers.publish', 'JTOOLBAR_PUBLISH', true);
        ToolbarHelper::unpublish('teamplayers.unpublish', 'JTOOLBAR_UNPUBLISH', true);
        ToolbarHelper::archiveList('teamplayers.archive');
        ToolbarHelper::trash('teamplayers.trash');
        ToolbarHelper::deleteList('', 'teamplayers.delete');

        $toolbar = Toolbar::getInstance('toolbar');
        $toolbar->appendButton(
            'Custom',
            (new FileLayout(
                'assignpersons',
                JPATH_ADMINISTRATOR . '/components/com_sportsmanagement/layouts'
            ))->render(),
            'upload'
        );

        if ((bool) ComponentHelper::getParams('com_sportsmanagement')->get('assign_club_position_to_player', 0)) {
            $toolbar->appendButton(
                'Custom',
                (new FileLayout(
                    'assignpersonsclub',
                    JPATH_ADMINISTRATOR . '/components/com_sportsmanagement/layouts'
                ))->render(),
                'upload'
            );
        }

        ToolbarHelper::apply(
            'teamplayers.assignplayerscountry',
            Text::_('COM_SPORTSMANAGEMENT_ADMIN_TPLAYERS_ASSIGN_COUNTRY')
        );
    }

    private function prepareModals(bool $includeClubModal): void
    {
        $baseQuery = [
            'option' => 'com_sportsmanagement',
            'view' => 'players',
            'tmpl' => 'component',
            'layout' => 'assignpersons',
            'type' => $this->personType === 2 ? 1 : 0,
            'pid' => (int) $this->project->id,
            'team_id' => (int) ($this->teamContext->team_id ?? 0),
            'persontype' => $this->personType,
            'season_id' => (int) ($this->project->season_id ?? 0),
            'whichview' => 'teamplayers',
        ];

        $this->assignModal = HTMLHelper::_('bootstrap.renderModal', 'collapseModalassignPersons', [
            'url' => 'index.php?' . http_build_query($baseQuery),
            'height' => $this->modalheight,
            'width' => $this->modalwidth,
            'modalWidth' => '70',
        ]);

        if (!$includeClubModal) {
            return;
        }

        $baseQuery['layout'] = 'assignpersonsclub';
        $baseQuery['assignclub'] = 1;
        $this->assignClubModal = HTMLHelper::_('bootstrap.renderModal', 'collapseModalassignPersonsClub', [
            'url' => 'index.php?' . http_build_query($baseQuery),
            'height' => $this->modalheight,
            'width' => $this->modalwidth,
            'modalWidth' => '70',
        ]);
    }
}
