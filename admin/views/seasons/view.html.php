<?php
/**
 * SportsManagement ein Programm zur Verwaltung für Sportarten
 * @version    1.0.05
 * @package    Sportsmanagement
 * @subpackage seasons
 * @file       view.html.php
 * @author     diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
 * @copyright  Copyright: © 2013-2023 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
defined('_JEXEC') or die('Restricted access');

use Diddipoeler\Component\SportsManagement\Administrator\Helper\CountryOptionsHelper;
use Diddipoeler\Component\SportsManagement\Administrator\Table\SeasonTable;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Toolbar\ToolbarHelper;

class sportsmanagementViewSeasons extends sportsmanagementView
{
    public function init(): void
    {
        $input = $this->app->getInput();
        $this->season_id = $input->getInt('id') ?: $input->getInt('season_id');
        $this->table = new SeasonTable($this->model->getDatabase());
        $lists = [];

        $nation = [
            HTMLHelper::_('select.option', '0', Text::_('COM_SPORTSMANAGEMENT_GLOBAL_SELECT_COUNTRY')),
        ];
        $countryOptions = CountryOptionsHelper::getOptions($this->model->getDatabase());

        if ($countryOptions) {
            $nation = array_merge($nation, $countryOptions);
            $this->search_nation = $countryOptions;
        }

        $lists['nation'] = $nation;
        $lists['nation2'] = HTMLHelper::_(
            'select.genericlist',
            $nation,
            'filter_search_nation',
            'class="inputbox" style="width:140px;" onchange="this.form.submit();"',
            'value',
            'text',
            $this->state->get('filter.search_nation')
        );
        $this->lists = $lists;

        $layout = $this->getLayout();

        if (in_array($layout, ['assignteams', 'assignteams_3', 'assignteams_4'], true)) {
            $this->setLayout('assignteams');

            return;
        }

        if (in_array(
            $layout,
            ['assignpersons', 'assignpersons_3', 'assignpersons_4', 'assignpersonsclub', 'assignpersonsclub_3', 'assignpersonsclub_4'],
            true
        )) {
            $seasonTeams = [
                HTMLHelper::_('select.option', '0', Text::_('COM_SPORTSMANAGEMENT_GLOBAL_SELECT_TEAM')),
            ];
            $seasonTeams = array_merge(
                $seasonTeams,
                (array) $this->model->getSeasonTeams($this->season_id)
            );
            $lists['season_teams'] = $seasonTeams;
            $this->lists = $lists;
            $this->setLayout(str_starts_with($layout, 'assignpersonsclub') ? 'assignpersonsclub' : 'assignpersons');
        }
    }

    protected function addToolbar()
    {
        $canDo = sportsmanagementHelper::getActions();
        $this->title = Text::_('COM_SPORTSMANAGEMENT_ADMIN_SEASONS_TITLE');

        if ($canDo->get('core.create')) {
            ToolbarHelper::addNew('season.add', 'JTOOLBAR_NEW');
        }

        if ($canDo->get('core.edit')) {
            ToolbarHelper::editList('season.edit', 'JTOOLBAR_EDIT');
        }

        parent::addToolbar();
    }
}
