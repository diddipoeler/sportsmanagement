<?php
/**
 * SportsManagement ein Programm zur Verwaltung für alle Sportarten
 * @version    1.0.05
 * @package    Sportsmanagement
 * @subpackage teamplan
 * @file       view.html.php
 * @author     diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
 * @copyright  Copyright: © 2013-2023 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
defined('_JEXEC') or die('Restricted access');

use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Uri\Uri;

/**
 * sportsmanagementViewTeamPlan
 *
 * @package
 * @author
 * @copyright diddi
 * @version   2014
 * @access    public
 */
class sportsmanagementViewTeamPlan extends sportsmanagementView
{
    /**
     * sportsmanagementViewTeamPlan::init()
     *
     * @return void
     */
    public function init()
    {
        $this->document->addScript(Uri::root(true) . '/components/' . $this->option . '/assets/js/smsportsmanagement.js');
        $this->document->addScript(Uri::root(true) . '/components/' . $this->option . '/assets/js/printPreview.js');
        $this->document->addStyleSheet(Uri::base() . 'components/' . $this->option . '/assets/css/modalwithoutjs.css');

        sportsmanagementHelperHtml::$project = $this->project;

        if ($this->config['show_date_image']) {
            $this->document->addStyleSheet(Uri::base() . 'components/' . $this->option . '/assets/css/calendar.css');
        }

        if (isset($this->project)) {
            $this->rounds = $this->model->getPlanRounds((string) ($this->config['plan_order'] ?? 'ASC'));
            $this->teams = $this->model->getPlanTeams();
            $this->favteams = $this->model->getPlanFavTeams();
            $this->division = $this->model->getPlanDivision();
            $this->ptid = $this->model->getProjectTeamId();
            $this->projectevents = $this->model->getPlanProjectEvents();
            $this->matches = $this->model->getMatches($this->config);
            $this->matches_refering = $this->model->getMatchesRefering($this->config);
            $this->matchesperround = $this->model->getMatchesPerRound($this->config, $this->rounds);
        }

        /** Set page title */
        if (empty($this->ptid)) {
            $pageTitle = !empty($this->project->id) ? $this->project->name : '';
        } elseif (isset($this->project) && isset($this->teams[$this->ptid])) {
            $pageTitle = $this->teams[$this->ptid]->name;
        } else {
            $pageTitle = '';
        }

        $this->document->setTitle(Text::sprintf('COM_SPORTSMANAGEMENT_TEAMPLAN_PAGE_TITLE', $pageTitle));

        if (!isset($this->config['table_class'])) {
            $this->config['table_class'] = 'table';
        }
    }
}
