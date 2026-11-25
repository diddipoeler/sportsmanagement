<?php
/**
 * Legacy Joomla 5/6 DFB-key import administrator view.
 *
 * @version    5.6.0
 * @author     diddipoeler
 * @copyright  Copyright (C) diddipoeler
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
/** SportsManagement DFB-key import administrator view. */
defined('_JEXEC') or die('Restricted access');

use Joomla\CMS\Document\HtmlDocument;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Log\Log;
use Joomla\CMS\Toolbar\ToolbarHelper;

class sportsmanagementViewjlextdfbkeyimport extends sportsmanagementView
{
    /**
     * Read the selected division and dispatch historical layout aliases to the corresponding DFB-key import preparation method.
     * @return void
     */
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

    /**
     * Check the project type, available teams, DFB schedule key and existing rounds or matches. Redirect to the appropriate division selection, round creation, first-matchday or administration screen.
     * @param mixed $tpl Retained template argument, currently unused.
     * @return void
     */
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

    /**
     * Load all DFB-key matchdays for the selected project and division's team count, then initialise the stylesheet and round-creation toolbar.
     * @param mixed $tpl Retained template argument, currently unused.
     * @return void
     */
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

    /**
     * Prepare the project-team selection options and corresponding first-matchday schedule key for fixture assignment.
     * @param mixed $tpl Retained template argument, currently unused.
     * @return void
     */
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

    /**
     * Restore the first-matchday form data from user state and generate the schedule preview for the selected project and division.
     * @param mixed $tpl Retained template argument, currently unused.
     * @return void
     */
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

    /**
     * Prepare the project division options with an initial selection prompt and configure the division-selection toolbar.
     * @param mixed $tpl Retained template argument, currently unused.
     * @return void
     */
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

    /**
     * Retain the default toolbar hook for the legacy view without adding any toolbar actions.
     * @return void
     */
    protected function addToolbardefault()
    {
    }

    /**
     * Set the translated round-creation title and configure back and insert-rounds toolbar actions.
     * @return void
     */
    protected function addToolbardefault_createdays()
    {
        $this->title = Text::_('COM_SPORTSMANAGEMENT_ADMIN_DFBKEYS_MATCHDAY_INFO_1');
        $this->icon = 'dfbkey';
        ToolbarHelper::back('JPREV', 'index.php?option=' . $this->option . '&view=projects');
        ToolbarHelper::save('jlextdfbkeyimport.save', 'COM_SPORTSMANAGEMENT_ADMIN_DFBKEYS_INSERT_ROUNDS');
        ToolbarHelper::divider();
    }

    /**
     * Set the first-matchday title and configure the back and apply actions for initial fixture assignments.
     * @return void
     */
    protected function addToolbardefault_firstmatchday()
    {
        $this->title = Text::_('COM_SPORTSMANAGEMENT_ADMIN_DFBKEYS_FIRST_MATCHDAY_INFO_1');
        $this->icon = 'dfbkey';
        ToolbarHelper::back('JPREV', 'index.php?option=' . $this->option . '&view=projects');
        ToolbarHelper::apply('jlextdfbkeyimport.apply', 'COM_SPORTSMANAGEMENT_ADMIN_DFBKEYS_INSERT_FIRST_DAY');
        ToolbarHelper::divider();
    }

    /**
     * Set the schedule insertion title and configure navigation and matchday insertion actions.
     * @return void
     */
    protected function addToolbardefault_savematchdays()
    {
        $this->title = Text::_('COM_SPORTSMANAGEMENT_ADMIN_DFBKEYS_SAVE_MATCHDAY_INFO_1');
        $this->icon = 'dfbkey';
        ToolbarHelper::back('JPREV', 'index.php?option=' . $this->option . '&view=projects');
        ToolbarHelper::save('jlextdfbkeyimport.insert', 'COM_SPORTSMANAGEMENT_ADMIN_DFBKEYS_INSERT_MATCHDAYS');
        ToolbarHelper::divider();
    }

    /**
     * Configure navigation and the action that confirms the selected division, leaving the toolbar title empty.
     * @return void
     */
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

    /**
     * Register and activate the importer stylesheet through Joomla's Web Asset Manager when the document is an HTML document.
     * @return void
     */
    private function addImporterStylesheet(): void
    {
        if ($this->document instanceof HtmlDocument) {
            $this->document->getWebAssetManager()->registerAndUseStyle(
                'com_sportsmanagement.jlextdfbkeyimport',
                'administrator/components/' . $this->option . '/assets/css/jlextusericons.css',
                ['version' => 'auto']
            );
        }
    }
}
