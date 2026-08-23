<?php
/**
 * SportsManagement ein Programm zur Verwaltung für Sportarten
 * @version    1.0.05
 * @package    Sportsmanagement
 * @subpackage clubs
 * @file       view.html.php
 * @author     diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
 * @copyright  Copyright: © 2013-2023 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
defined('_JEXEC') or die('Restricted access');

use Diddipoeler\Component\SportsManagement\Administrator\Table\ClubTable;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Toolbar\ToolbarHelper;

class sportsmanagementViewClubs extends sportsmanagementView
{
    public function init(): void
    {
        $factory = $this->app->bootComponent('com_sportsmanagement')->getMVCFactory();
        $this->modelclub = $factory->createModel('Club', 'Administrator');
        $this->search_nation = '';
        $this->association = '';
        $this->table = new ClubTable($this->model->getDatabase());
        $lists = [];

        $seasons = [
            HTMLHelper::_('select.option', '0', Text::_('COM_SPORTSMANAGEMENT_ADMIN_PROJECTS_SEASON_FILTER'), 'id', 'name'),
        ];
        $seasonsModel = $factory->createModel('Seasons', 'Administrator');
        $allSeasons = $seasonsModel ? $seasonsModel->getSeasons() : [];
        $seasons = array_merge($seasons, $allSeasons);
        $this->season = $allSeasons;
        $lists['seasons'] = HTMLHelper::_(
            'select.genericList',
            $seasons,
            'filter_season',
            'class="inputbox" onChange="this.form.submit();" style="width:120px"',
            'id',
            'name',
            $this->state->get('filter.season')
        );

        $nation = [
            HTMLHelper::_('select.option', '0', Text::_('COM_SPORTSMANAGEMENT_GLOBAL_SELECT_COUNTRY')),
        ];

        if ($countryOptions = JSMCountries::getCountryOptions()) {
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

        if ($this->state->get('filter.search_nation')) {
            $associationsModel = $factory->createModel('Jlextassociations', 'Administrator');
            $this->association = $associationsModel ? $associationsModel->getAssociations() : [];
        }

        $lists['search_mode'] = '';
        $this->lists = $lists;
    }

    protected function addToolbar()
    {
        $this->title = Text::_('COM_SPORTSMANAGEMENT_ADMIN_CLUBS_TITLE');
        ToolbarHelper::apply('clubs.saveshort');
        ToolbarHelper::divider();
        ToolbarHelper::addNew('club.add');
        ToolbarHelper::editList('club.edit');
        ToolbarHelper::custom('club.import', 'upload', 'upload', Text::_('JTOOLBAR_UPLOAD'), false);
        ToolbarHelper::archiveList('club.export', Text::_('JTOOLBAR_EXPORT'));
        parent::addToolbar();
    }
}
