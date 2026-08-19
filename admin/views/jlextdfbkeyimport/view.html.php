<?php
/** SportsManagement DFB-key import administrator view. */
defined('_JEXEC') or die('Restricted access');

use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Log\Log;
use Joomla\CMS\Toolbar\ToolbarHelper;
use Joomla\CMS\Uri\Uri;

class sportsmanagementViewjlextdfbkeyimport extends sportsmanagementView
{
    public function init()
    {
        $this->division_id = $this->app->getInput()->getInt('divisionid');

        switch ($this->getLayout()) {
            case 'default':
            case 'default_3':
            case 'default_4':
                $this->setLayout('default');
                $this->_displayDefault(null);
                return;

            case 'default_createdays':
            case 'default_createdays_3':
            case 'default_createdays_4':
                $this->setLayout('default_createdays');
                $this->_displayDefaultCreatedays(null);
                return;

            case 'default_firstmatchday':
            case 'default_firstmatchday_3':
            case 'default_firstmatchday_4':
                $this->setLayout('default_firstmatchday');
                $this->_displayDefaultFirstMatchday(null);
                return;

            case 'default_savematchdays':
            case 'default_savematchdays_3':
            case 'default_savematchdays_4':
                $this->setLayout('default_savematchdays');
                $this->_displayDefaultSaveMatchdays(null);
                return;

            case 'default_getdivision':
            case 'default_getdivision_3':
            case 'default_getdivision_4':
                $this->setLayout('default_getdivision');
                $this->_displayDefaultGetDivision(null);
                return;
        }
    }

    public function _displayDefault($tpl)
    {
        $this->division_id = $this->app->getInput()->getInt('divisionid');
        $projectType = $this->model->getProjectType($this->project_id);

        if ($projectType === 'DIVISIONS_LEAGUE' && !$this->division_id) {
            $this->app->redirect(
                'index.php?option=' . $this->option . '&view=jlextdfbkeyimport&layout=default_getdivision'
            );
            return;
        }

        $this->model->checkTable();

        if (empty($this->project_id)) {
            Log::add(Text::_('COM_SPORTSMANAGEMENT_ADMIN_DFBKEYS_ERROR_1'), Log::WARNING, 'jsmerror');
            $this->app->redirect('index.php?option=' . $this->option . '&view=projects');
            return;
        }

        $projectTeams = $this->model->getProjectteams($this->project_id, $this->division_id);

        if (!$projectTeams) {
            Log::add(Text::_('COM_SPORTSMANAGEMENT_ADMIN_DFBKEYS_ERROR_5'), Log::WARNING, 'jsmerror');
            $this->app->redirect('index.php?option=' . $this->option . '&view=projectteams');
            return;
        }

        $dfbTeams = count($projectTeams);
        $dfbKey = $this->model->getDFBKey($dfbTeams, 'FIRST');

        if (!$dfbKey) {
            $country = $this->model->getCountry($this->project_id);
            $this->app->redirect(
                'index.php?option=' . $this->option . '&view=projects&return=jlextdfbkeyimporterror6'
                . '&dfbteams=' . $dfbTeams . '&dfbcountry=' . rawurlencode((string) $country)
            );
            return;
        }

        if ($this->model->getMatchdays($this->project_id)) {
            $matchCount = $this->model->getMatches($this->project_id, $this->division_id);

            if ($matchCount > 0) {
                Log::add(Text::_('COM_SPORTSMANAGEMENT_ADMIN_DFBKEYS_ERROR_2'), Log::WARNING, 'jsmerror');
                Log::add(
                    Text::sprintf('COM_SPORTSMANAGEMENT_ADMIN_DFBKEYS_ERROR_7', $matchCount),
                    Log::WARNING,
                    'jsmerror'
                );
                $this->app->redirect('index.php?option=' . $this->option . '&view=rounds');
                return;
            }

            $this->app->redirect(
                'index.php?option=' . $this->option
                . '&view=jlextdfbkeyimport&layout=default_firstmatchday&divisionid=' . $this->division_id
            );
            return;
        }

        $this->app->redirect(
            'index.php?option=' . $this->option
            . '&view=jlextdfbkeyimport&layout=default_createdays&divisionid=' . $this->division_id
        );
    }

    public function _displayDefaultCreatedays($tpl)
    {
        $this->division_id = $this->app->getInput()->getInt('divisionid');
        $this->newmatchdays = [];
        $projectTeams = $this->model->getProjectteams($this->project_id, $this->division_id);

        if ($projectTeams) {
            $this->newmatchdays = $this->model->getDFBKey(count($projectTeams), 'ALL');
        }

        $this->addImporterStylesheet();
        $this->addToolbardefault_createdays();
    }

    public function _displayDefaultFirstMatchday($tpl)
    {
        $this->division_id = $this->app->getInput()->getInt('divisionid');
        $projectTeams = $this->model->getProjectteams($this->project_id, $this->division_id);
        $options = [HTMLHelper::_('select.option', '0', '- ' . Text::_('Select projectteams') . ' -')];

        if ($projectTeams) {
            $options = array_merge($options, $projectTeams);
        }

        $this->dfbteams = max(0, count($options) - 1);
        $this->lists = [
            'projectteams' => $options,
            'dfbday' => $this->model->getDFBKey($this->dfbteams, 'FIRST'),
        ];

        $this->addImporterStylesheet();
        $this->addToolbardefault_firstmatchday();
    }

    public function _displayDefaultSaveMatchdays($tpl)
    {
        $post = $this->app->getUserState($this->option . '.first_post', []);
        $post = is_array($post) ? $post : [];
        $this->division_id = $this->app->getInput()->getInt(
            'divisionid',
            (int) ($post['divisionid'] ?? 0)
        );
        $this->import = $this->model->getSchedule($post, $this->project_id, $this->division_id);

        $this->addImporterStylesheet();
        $this->addToolbardefault_savematchdays();
    }

    public function _displayDefaultGetDivision($tpl)
    {
        $projectDivisions = $this->model->getDivisions($this->project_id);
        $divisions = [
            HTMLHelper::_('select.option', '0', Text::_('COM_SPORTSMANAGEMENT_GLOBAL_SELECT_DIVISION')),
        ];

        if ($projectDivisions) {
            $divisions = array_merge($divisions, $projectDivisions);
        }

        $this->division = 0;
        $this->lists = ['divisions' => $divisions];
        $this->addToolbardefault_getdivision();
    }

    protected function addToolbardefault()
    {
    }

    protected function addToolbardefault_createdays()
    {
        $this->title = Text::_('COM_SPORTSMANAGEMENT_ADMIN_DFBKEYS_MATCHDAY_INFO_1');
        $this->icon = 'dfbkey';
        ToolbarHelper::back('JPREV', 'index.php?option=' . $this->option . '&view=projects');
        ToolbarHelper::save('jlextdfbkeyimport.save', 'COM_SPORTSMANAGEMENT_ADMIN_DFBKEYS_INSERT_ROUNDS');
        ToolbarHelper::divider();
    }

    protected function addToolbardefault_firstmatchday()
    {
        $this->title = Text::_('COM_SPORTSMANAGEMENT_ADMIN_DFBKEYS_FIRST_MATCHDAY_INFO_1');
        $this->icon = 'dfbkey';
        ToolbarHelper::back('JPREV', 'index.php?option=' . $this->option . '&view=projects');
        ToolbarHelper::apply('jlextdfbkeyimport.apply', 'COM_SPORTSMANAGEMENT_ADMIN_DFBKEYS_INSERT_FIRST_DAY');
        ToolbarHelper::divider();
    }

    protected function addToolbardefault_savematchdays()
    {
        $this->title = Text::_('COM_SPORTSMANAGEMENT_ADMIN_DFBKEYS_SAVE_MATCHDAY_INFO_1');
        $this->icon = 'dfbkey';
        ToolbarHelper::back('JPREV', 'index.php?option=' . $this->option . '&view=projects');
        ToolbarHelper::save('jlextdfbkeyimport.insert', 'COM_SPORTSMANAGEMENT_ADMIN_DFBKEYS_INSERT_MATCHDAYS');
        ToolbarHelper::divider();
    }

    protected function addToolbardefault_getdivision()
    {
        $this->title = '';
        $this->icon = 'dfbkey';
        ToolbarHelper::back('JPREV', 'index.php?option=' . $this->option . '&view=projects');
        ToolbarHelper::save(
            'jlextdfbkeyimport.getdivisionfirst',
            'COM_SPORTSMANAGEMENT_ADMIN_DFBKEYS_USE_DIVISION'
        );
    }

    private function addImporterStylesheet(): void
    {
        $stylelink = '<link rel="stylesheet" href="'
            . Uri::root()
            . 'administrator/components/' . $this->option . '/assets/css/jlextusericons.css"
            . ' type="text/css" />' . "\n";
        $this->document->addCustomTag($stylelink);
    }
}
