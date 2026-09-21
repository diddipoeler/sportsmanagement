<?php
/**
 * SportsManagement tournament tree legacy view for Joomla 5/6.
 *
 * @version    5.6.0
 * @author     diddipoeler
 * @copyright  Copyright (C) diddipoeler
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
defined('_JEXEC') or die('Restricted access');

use Joomla\CMS\Application\SiteApplication;
use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\Document\HtmlDocument;
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

        if ($this->document instanceof HtmlDocument) {
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
        }

        if (ComponentHelper::getParams($this->option)->get('show_debug_info_frontend')) {
            /** @var SiteApplication $app */
            $app = Factory::getContainer()->get(SiteApplication::class);

            if (!$app->isClient('site')) {
                throw new \RuntimeException('SportsManagement tournament tree requires the Joomla site application.', 500);
            }

            $app->enqueueMessage(
                __METHOD__ . ' ' . __LINE__ . ' config <pre>' . print_r($this->config, true) . '</pre>',
                'notice'
            );
        }
    }
}
