<?php
/** Administrator project edit and panel view. */
defined('_JEXEC') or die('Restricted access');

use Diddipoeler\Component\SportsManagement\Administrator\Service\ProjectPanelService;
use Diddipoeler\Component\SportsManagement\Site\Service\SportsManagementDatabaseResolver;
use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Toolbar\Toolbar;
use Joomla\Database\DatabaseInterface;

class sportsmanagementViewProject extends sportsmanagementView
{
    public function init()
    {
        if (in_array($this->getLayout(), ['panel', 'panel_3', 'panel_4'], true)) {
            if (ComponentHelper::getParams($this->option)->get('show_jsm_tips')) {
                $this->notes[] = Text::_('COM_SPORTSMANAGEMENT_ADMIN_PROJECT_NOTES');
            }

            $this->_displayPanel('');
            return;
        }

        $input = $this->app->getInput();
        $input->set('hidemainmenu', true);
        $lists = [];

        if (empty($this->item->admin)) {
            $this->form->setValue('admin', null, (int) $this->user->id);
        }
        if (empty($this->item->editor)) {
            $this->form->setValue('editor', null, (int) $this->user->id);
        }

        $this->form->setValue('sports_type_id', null, (int) ($this->item->sports_type_id ?? 0));
        $this->form->setValue('agegroup_id', null, (int) ($this->item->agegroup_id ?? 0));
        $this->extended = sportsmanagementHelper::getExtended((string) ($this->item->extended ?? ''), 'project');
        $this->extendeduser = sportsmanagementHelper::getExtendedUser(
            (string) ($this->item->extendeduser ?? ''),
            'project'
        );

        $isNew = (int) ($this->item->id ?? 0) === 0;

        if ($isNew) {
            $this->form->setValue('start_date', null, '');
            $this->form->setValue('start_time', null, '18:00');
            $this->form->setValue('admin', null, (int) $this->user->id);
            $this->form->setValue('editor', null, (int) $this->user->id);
        } else {
            if ((string) ($this->item->start_date ?? '') === '0000-00-00') {
                $this->item->start_date = '';
                $this->form->setValue('start_date', null, '');
            }

            $picture = trim((string) ($this->item->picture ?? ''));
            if ($picture === '' || basename($picture) === '') {
                $this->item->picture = 'images/com_sportsmanagement/database/placeholders/placeholder_450_2.png';
                $this->form->setValue('picture', null, $this->item->picture);
            }
        }

        $view = $input->getCmd('view', 'project');
        $this->checkextrafields = sportsmanagementHelper::checkUserExtraFields('backend', 0, $view);

        if ($this->checkextrafields && !$isNew) {
            $lists['ext_fields'] = sportsmanagementHelper::getUserExtraFields(
                (int) $this->item->id,
                'backend',
                0,
                $view
            );
        }

        $favTeams = trim((string) ($this->item->fav_team ?? ''));
        $this->form->setValue('fav_team', null, $favTeams === '' ? [] : explode(',', $favTeams));
        $this->lists = $lists;
    }

    protected function _displayPanel($tpl)
    {
        $this->item = $this->get('Item');

        if (!$this->item || (int) $this->item->id <= 0) {
            $this->project = $this->item;
            $this->count_projectdivisions = 0;
            $this->count_projectpositions = 0;
            $this->count_projectreferees = 0;
            $this->count_projectteams = 0;
            $this->count_matchdays = 0;
            return;
        }

        /** @var DatabaseInterface $joomlaDatabase */
        $joomlaDatabase = Factory::getContainer()->get(DatabaseInterface::class);
        $databaseSelector = $this->app->getInput()->getInt(
            'cfg_which_database',
            (int) $this->app->getUserState($this->option . '.cfg_which_database', 0)
        );
        $sportsManagementDatabase = SportsManagementDatabaseResolver::resolve(
            $joomlaDatabase,
            $databaseSelector
        );
        $service = new ProjectPanelService($sportsManagementDatabase);
        $counts = $service->getCounts($this->item);

        $this->project = $this->item;
        $this->count_projectdivisions = (int) $counts['divisions'];
        $this->count_projectpositions = (int) $counts['positions'];
        $this->count_projectreferees = (int) $counts['referees'];
        $this->count_projectteams = (int) $counts['teams'];
        $this->count_matchdays = (int) $counts['rounds'];

        $this->app->setUserState($this->option . '.pid', (int) $this->item->id);
        $this->app->setUserState($this->option . '.season_id', (int) $this->item->season_id);
        $this->app->setUserState($this->option . '.project_art_id', (int) $this->item->project_art_id);
        $this->app->setUserState($this->option . '.sports_type_id', (int) $this->item->sports_type_id);
    }

    protected function addToolbar()
    {
        $this->title = (int) ($this->item->id ?? 0) > 0
            ? Text::sprintf('COM_SPORTSMANAGEMENT_ADMIN_PROJECT_EDIT', (string) $this->item->name)
            : Text::_('COM_SPORTSMANAGEMENT_ADMIN_PROJECT_ADD_NEW');
        $this->icon = 'project';

        $bar = Toolbar::getInstance('toolbar');
        switch (ComponentHelper::getParams($this->option)->get('which_article_component')) {
            case 'com_content':
                $bar->appendButton('Link', 'featured', 'Kategorie', 'index.php?option=com_categories&extension=com_content');
                break;
            case 'com_k2':
                $bar->appendButton('Link', 'featured', 'Kategorie', 'index.php?option=com_k2&view=categories');
                break;
        }

        parent::addToolbar();
    }
}
