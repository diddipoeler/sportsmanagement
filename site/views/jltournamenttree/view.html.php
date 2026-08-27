<?php
/**
 * SportsManagement tournament tree legacy view for Joomla 5/6.
 */
defined('_JEXEC') or die('Restricted access');

use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\Factory;

class sportsmanagementViewjltournamenttree extends sportsmanagementView
{
    public array $rounds = [];
    public string $color_from = '#FFFFFF';
    public string $color_to = '#0000FF';
    public int $font_size = 14;
    public string $projectname = '';
    public string $bracket_rounds = '[]';
    public string $bracket_teams = '';
    public string $bracket_results = '';
    public string $which_first_round = 'scrollLeft()';
    public int $jl_tree_bracket_round_width = 300;
    public int $jl_tree_bracket_teamb_width = 210;
    public int $jl_tree_bracket_width = 340;

    public function init(): void
    {
        if (!isset($this->project)
            || !in_array((string) $this->project->project_type, ['TOURNAMENT_MODE', 'DIVISIONS_LEAGUE'], true)
        ) {
            return;
        }

        $this->rounds = $this->model->getTournamentRounds();
        $this->color_from = $this->model->getColorFrom();
        $this->color_to = $this->model->getColorTo();
        $this->font_size = $this->model->getFontSize();
        $this->projectname = (string) $this->project->name;
        $this->bracket_rounds = $this->model->getTournamentBracketRounds($this->rounds);
        $this->bracket_teams = $this->model->getTournamentMatches($this->rounds);
        $this->bracket_results = $this->model->getTournamentResults($this->rounds);
        $this->which_first_round = $this->model->getWhichShowFirstRound();
        $this->jl_tree_bracket_round_width = $this->model->getTreeBracketRoundWidth();
        $this->jl_tree_bracket_teamb_width = $this->model->getTreeBracketTeambWidth();
        $this->jl_tree_bracket_width = $this->model->getTreeBracketWidth();

        $assets = $this->document->getWebAssetManager();
        $assets
            ->registerAndUseScript(
                'com_sportsmanagement.tournament-bracket',
                'components/com_sportsmanagement/assets/js/jquery.bracket.min.js',
                ['version' => 'auto']
            )
            ->registerAndUseStyle(
                'com_sportsmanagement.tournament-bracket',
                'components/com_sportsmanagement/assets/css/jquery.bracket.min.css',
                ['version' => 'auto']
            );

        if (ComponentHelper::getParams($this->option)->get('show_debug_info_frontend')) {
            Factory::getApplication()->enqueueMessage(
                __METHOD__ . ' ' . __LINE__ . ' config <pre>' . print_r($this->config, true) . '</pre>',
                ''
            );
        }
    }
}
