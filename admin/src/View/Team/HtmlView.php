<?php
namespace Diddipoeler\Component\SportsManagement\Administrator\View\Team;

\defined('_JEXEC') or die;

use Diddipoeler\Component\SportsManagement\Administrator\Model\TeamModel;
use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\CMS\MVC\View\HtmlView as BaseHtmlView;
use Joomla\CMS\Router\Route;
use Joomla\CMS\Toolbar\ToolbarHelper;

/** Native Joomla 5/6 administrator form view for a team. */
final class HtmlView extends BaseHtmlView
{
    public $form;
    public $item;
    public $state;
    public $user;
    public $extended = null;
    public $extendeduser = null;
    public array $lists = [];
    public array $trainingData = [];
    public array $daysOfWeek = [];
    public int $checkextrafields = 0;
    public bool $changeTrainingDate = false;
    public string $tmpl = '';

    public function display($tpl = null)
    {
        $app = Factory::getApplication();
        $input = $app->getInput();
        $input->set('hidemainmenu', true);
        $this->option = 'com_sportsmanagement';

        $this->user = $app->getIdentity();
        $this->tmpl = $input->getCmd('tmpl', '');
        $this->setLayout('edit');

        $model = $this->getModel();
        if (!$model instanceof TeamModel) {
            throw new \RuntimeException('TeamModel is unavailable.', 500);
        }

        $this->form = $this->get('Form');
        $this->item = $this->get('Item');
        $this->state = $this->get('State');

        if ($errors = $this->get('Errors')) {
            throw new \RuntimeException(implode("\n", $errors), 500);
        }
        if (!$this->form || !$this->item) {
            throw new \RuntimeException('Team form could not be loaded.', 500);
        }

        $teamId = (int) ($this->item->id ?? 0);
        if ($teamId === 0) {
            $clubId = (int) $app->getUserState($this->option . '.club_id', 0);
            if ($clubId > 0) {
                $this->form->setValue('club_id', null, $clubId);
                $this->item->club_id = $clubId;
            }
        }

        if (!class_exists('sportsmanagementHelper', false)) {
            require_once JPATH_ADMINISTRATOR . '/components/com_sportsmanagement/helpers/sportsmanagement.php';
        }

        $this->extended = \sportsmanagementHelper::getExtended(
            (string) ($this->item->extended ?? ''),
            'team'
        );
        $this->extendeduser = \sportsmanagementHelper::getExtendedUser(
            (string) ($this->item->extendeduser ?? ''),
            'team'
        );
        $this->checkextrafields = (int) \sportsmanagementHelper::checkUserExtraFields('backend', 0, 'team');

        if ($this->checkextrafields && $teamId > 0) {
            $this->lists['ext_fields'] = \sportsmanagementHelper::getUserExtraFields(
                $teamId,
                'backend',
                0,
                'team'
            );
        }

        if ($teamId > 0) {
            $this->trainingData = $model->getTrainigData($teamId) ?: [];
        }

        $this->daysOfWeek = [
            0 => Text::_('COM_SPORTSMANAGEMENT_GLOBAL_SELECT'),
            1 => Text::_('MONDAY'),
            2 => Text::_('TUESDAY'),
            3 => Text::_('WEDNESDAY'),
            4 => Text::_('THURSDAY'),
            5 => Text::_('FRIDAY'),
            6 => Text::_('SATURDAY'),
            7 => Text::_('SUNDAY'),
        ];
        $this->changeTrainingDate = (bool) $app->getUserState($this->option . '.change_training_date', false);

        $this->addToolbar();
        parent::display($tpl);
    }

    private function addToolbar(): void
    {
        $isNew = (int) ($this->item->id ?? 0) === 0;
        $canEdit = $this->user->authorise('core.edit', $this->option);
        $canCreate = $this->user->authorise('core.create', $this->option);

        ToolbarHelper::title(
            $isNew
                ? Text::_('COM_SPORTSMANAGEMENT_ADMIN_TEAM_ADD_NEW')
                : Text::_('COM_SPORTSMANAGEMENT_ADMIN_TEAM_EDIT'),
            'users'
        );

        if (($isNew && $canCreate) || (!$isNew && $canEdit)) {
            ToolbarHelper::apply('team.apply');
            ToolbarHelper::save('team.save');
        }

        if ($canCreate) {
            ToolbarHelper::save2new('team.save2new');
            if (!$isNew) {
                ToolbarHelper::save2copy('team.save2copy');
            }
        }

        ToolbarHelper::cancel('team.cancel', $isNew ? 'JTOOLBAR_CANCEL' : 'JTOOLBAR_CLOSE');
        ToolbarHelper::back(
            Text::_('COM_SPORTSMANAGEMENT_ADMIN_TEAMS_TITLE'),
            Route::_('index.php?option=com_sportsmanagement&view=teams', false)
        );
    }
}
