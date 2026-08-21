<?php
defined('_JEXEC') or die('Restricted access');

use Diddipoeler\Component\SportsManagement\Administrator\Helper\ExtraSelectOptionsHelper;
use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Toolbar\ToolbarHelper;
use Joomla\CMS\Uri\Uri;

/** Joomla 5/6 administrator list view for matches. */
class sportsmanagementViewMatches extends sportsmanagementView
{
    public function init()
    {
        $params = ComponentHelper::getParams($this->option);

        $this->state = $this->get('State');
        $this->items = $this->get('Items') ?: [];
        $this->pagination = $this->get('Pagination');
        $this->total = $this->get('Total');
        $this->sortDirection = (string) $this->state->get('list.direction', 'ASC');
        $this->sortColumn = (string) $this->state->get('list.ordering', 'mc.match_date');

        $this->projectteamsel = (int) $this->state->get('context.project_team_id', 0);
        $this->project_id = (int) $this->state->get('context.project_id', 0);
        $this->rid = (int) $this->state->get('context.round_id', 0);
        $this->projectws = $this->model->getProject($this->project_id);

        if (!$this->projectws) {
            $this->app->enqueueMessage(Text::_('JGLOBAL_NO_MATCHING_RESULTS'), 'warning');
            $this->roundws = (object) ['id' => 0, 'round_date_first' => null];
            $this->lists = $this->emptyLists();
            $this->matches = [];

            return;
        }

        // Keep the historical view property used by the existing match templates.
        $this->projectws->sports_type_name = (string) (
            $this->projectws->sports_type_name
            ?? $this->projectws->sport_type_name
            ?? ''
        );
        $this->project_art_id = (int) ($this->projectws->project_art_id ?? 0);
        $seasonId = (int) ($this->projectws->season_id ?? 0);
        if ($seasonId > 0) {
            $this->model->setState('context.season_id', $seasonId);
            $this->app->setUserState($this->option . '.season_id', $seasonId);
        }
        $this->app->setUserState($this->option . '.pid', $this->project_id);
        if ($this->rid > 0) {
            $this->app->setUserState($this->option . '.rid', $this->rid);
        }

        $this->roundws = $this->model->getRound($this->rid)
            ?: (object) ['id' => 0, 'round_date_first' => null];
        $this->ress = $this->model->getRounds($this->project_id);
        $this->lists = $this->buildRoundLists($this->ress);
        $this->lists['project_change_rounds'] = $this->ress;

        $this->document->addStyleSheet(
            Uri::root() . 'administrator/components/com_sportsmanagement/assets/css/form_control.css'
        );
        $this->document->addScript(
            Uri::base() . 'components/' . $this->option . '/assets/js/matches.js'
        );

        $allProjectTeams = $this->model->getProjectTeamOptions($this->project_id);
        $this->lists['projectteams'] = $this->withTeamPlaceholder($allProjectTeams);

        foreach ($this->items as $row) {
            $homeDivision = (int) ($row->divhomeid ?? 0);
            $awayDivision = (int) ($row->divawayid ?? 0);
            $divisionId = $homeDivision > 0 && $homeDivision === $awayDivision ? $homeDivision : 0;
            $key = 'teams_' . $divisionId;

            if (!isset($this->lists[$key])) {
                $this->lists[$key] = $this->withTeamPlaceholder(
                    $this->model->getProjectTeamOptions($this->project_id, $divisionId)
                );
            }

            $this->model->checkMatchPicturePath((int) ($row->id ?? 0));
        }

        $this->lists['match_result_type'] = [
            HTMLHelper::_('select.option', 0, Text::_('COM_SPORTSMANAGEMENT_ADMIN_MATCHES_RT')),
            HTMLHelper::_('select.option', 1, Text::_('COM_SPORTSMANAGEMENT_ADMIN_MATCHES_OT')),
            HTMLHelper::_('select.option', 2, Text::_('COM_SPORTSMANAGEMENT_ADMIN_MATCHES_SO')),
        ];

        $articles = [HTMLHelper::_('select.option', 0, Text::_('COM_SPORTSMANAGEMENT_GLOBAL_SELECT_ARTICLE'))];
        $articleRows = sportsmanagementHelper::getArticleList((int) ($this->projectws->category_id ?? 0));
        if ($articleRows) {
            $articles = array_merge($articles, $articleRows);
        }
        $this->lists['articles'] = $articles;

        $this->lists['divisions'] = array_merge(
            [HTMLHelper::_('select.option', 0, Text::_('COM_SPORTSMANAGEMENT_GLOBAL_SELECT_DIVISION'))],
            $this->model->getDivisionOptions($this->project_id)
        );
        $this->playgrounds = array_merge(
            [HTMLHelper::_('select.option', 0, Text::_('COM_SPORTSMANAGEMENT_GLOBAL_SELECT_PLAYGROUND'))],
            $this->model->getPlaygroundOptions($allProjectTeams)
        );

        $this->selectlist = [];
        $extraSelectOptions = new ExtraSelectOptionsHelper();
        foreach ($this->model->getMatchTableColumns() as $field => $definition) {
            $selectOptions = $extraSelectOptions->getOptions('matches', (string) $field);
            if (!$selectOptions) {
                continue;
            }
            $this->selectlist[$field] = array_merge(
                [HTMLHelper::_('select.option', 0, Text::_('COM_SPORTSMANAGEMENT_GLOBAL_SELECT'))],
                $selectOptions
            );
        }

        $this->user = $this->app->getIdentity();
        $this->matches = $this->model->prepareItems($this->items);
        $this->prefill = $params->get('use_prefilled_match_roster', 0);
        $this->lists += [
            'search_mode' => '',
            'createTypes' => '',
            'addToRound' => '',
            'autoPublish' => '',
        ];

        $layout = preg_replace('/_(3|4)$/', '', (string) $this->getLayout());
        if ($layout === 'massadd') {
            $this->buildMassAddLists();
            $this->setLayout('massadd');
        }
    }

    protected function addToolbar()
    {
        $massadd = $this->app->getInput()->getInt('massadd', 0);
        $this->title = Text::_('COM_SPORTSMANAGEMENT_ADMIN_MATCHES_TITLE');

        if (!$massadd) {
            ToolbarHelper::back('JPREV', 'index.php?option=com_sportsmanagement&view=rounds');
            ToolbarHelper::publish('match.insertgooglecalendar', 'JLIB_HTML_CALENDAR', true);
            ToolbarHelper::divider();
            ToolbarHelper::publish(
                'matches.count_result_yes',
                'COM_SPORTSMANAGEMENT_ADMIN_MATCH_F_AD_INCL',
                true
            );
            ToolbarHelper::unpublish(
                'matches.count_result_no',
                'COM_SPORTSMANAGEMENT_ADMIN_MATCH_F_AD_INCL',
                true
            );
            ToolbarHelper::divider();
            ToolbarHelper::publish('matches.publish', 'JTOOLBAR_PUBLISH', true);
            ToolbarHelper::unpublish('matches.unpublish', 'JTOOLBAR_UNPUBLISH', true);
            ToolbarHelper::divider();
            ToolbarHelper::apply('matches.saveshort');
            ToolbarHelper::divider();
            ToolbarHelper::custom(
                'match.massadd',
                'new.png',
                'new_f2.png',
                Text::_('COM_SPORTSMANAGEMENT_ADMIN_MATCHES_MASSADD_MATCHES'),
                false
            );
            ToolbarHelper::addNew(
                'match.addmatch',
                Text::_('COM_SPORTSMANAGEMENT_ADMIN_MATCHES_MASSADD_ADD_MATCH')
            );
        } else {
            ToolbarHelper::custom(
                'match.cancelmassadd',
                'cancel.png',
                'cancel_f2.png',
                Text::_('COM_SPORTSMANAGEMENT_ADMIN_MATCHES_MASSADD_CANCEL_MATCHADD'),
                false
            );
        }

        parent::addToolbar();
    }

    private function buildRoundLists(array $rounds): array
    {
        $options = [];
        foreach ($rounds as $round) {
            $dateRange = sportsmanagementHelper::convertDate($round->round_date_first, 1)
                . ' - ' . sportsmanagementHelper::convertDate($round->round_date_last, 1);
            $options[] = HTMLHelper::_(
                'select.option',
                (int) $round->id,
                sprintf('%s (%s)', $round->name, $dateRange)
            );
        }

        $selected = (int) ($this->roundws->id ?? 0);
        return [
            'project_rounds' => HTMLHelper::_(
                'select.genericlist',
                $options,
                'rid',
                'class="form-select" onchange="document.getElementById(\'short_act\').value=\'rounds\';document.roundForm.submit();"',
                'value',
                'text',
                $selected
            ),
            'project_rounds2' => HTMLHelper::_(
                'select.genericlist',
                $options,
                'rid',
                'class="form-select"',
                'value',
                'text',
                $selected
            ),
        ];
    }

    private function withTeamPlaceholder(array $teams): array
    {
        $label = $this->project_art_id === 3
            ? Text::_('COM_SPORTSMANAGEMENT_GLOBAL_SELECT_PERSON')
            : Text::_('COM_SPORTSMANAGEMENT_GLOBAL_SELECT_TEAM');

        return array_merge([HTMLHelper::_('select.option', 0, $label)], $teams);
    }

    private function buildMassAddLists(): void
    {
        $createTypes = [
            0 => Text::_('COM_SPORTSMANAGEMENT_ADMIN_MATCHES_MASSADD'),
            1 => Text::_('COM_SPORTSMANAGEMENT_ADMIN_MATCHES_MASSADD_1'),
            2 => Text::_('COM_SPORTSMANAGEMENT_ADMIN_MATCHES_MASSADD_2'),
        ];
        $createOptions = [];
        foreach ($createTypes as $value => $text) {
            $createOptions[] = HTMLHelper::_('select.option', $value, $text);
        }
        $this->lists['createTypes'] = HTMLHelper::_(
            'select.genericlist',
            $createOptions,
            'ct[]',
            'class="form-select" onchange="displayTypeView();"',
            'value',
            'text',
            1,
            'ct'
        );

        $yesNo = [
            HTMLHelper::_('select.option', 0, Text::_('JNO')),
            HTMLHelper::_('select.option', 1, Text::_('JYES')),
        ];
        $this->lists['addToRound'] = HTMLHelper::_(
            'select.radiolist',
            $yesNo,
            'addToRound',
            'class="form-check-input"',
            'value',
            'text',
            0
        );
        $this->lists['autoPublish'] = HTMLHelper::_(
            'select.radiolist',
            $yesNo,
            'autoPublish',
            'class="form-check-input"',
            'value',
            'text',
            1
        );
    }

    private function emptyLists(): array
    {
        return [
            'project_rounds' => '',
            'project_rounds2' => '',
            'project_change_rounds' => [],
            'projectteams' => [],
            'match_result_type' => [],
            'articles' => [],
            'divisions' => [],
            'search_mode' => '',
            'createTypes' => '',
            'addToRound' => '',
            'autoPublish' => '',
        ];
    }
}
