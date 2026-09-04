<?php
/**
 * Joomla 5/6 administrator list view for clubs.
 *
 * @version    5.6.0
 * @author     diddipoeler
 * @copyright  Copyright (C) diddipoeler
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 * @package    SportsManagement
 * @subpackage com_sportsmanagement
 */

namespace Diddipoeler\Component\SportsManagement\Administrator\View\Clubs;

\defined('_JEXEC') or die;

use Diddipoeler\Component\SportsManagement\Administrator\Helper\CountryOptionsHelper;
use Diddipoeler\Component\SportsManagement\Administrator\Model\ClubsModel;
use Diddipoeler\Component\SportsManagement\Administrator\Model\JlextassociationsModel;
use Diddipoeler\Component\SportsManagement\Administrator\Model\SeasonsModel;
use Diddipoeler\Component\SportsManagement\Administrator\Table\ClubTable;
use Joomla\CMS\Application\AdministratorApplication;
use Joomla\CMS\Factory;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\MVC\View\HtmlView as BaseHtmlView;
use Joomla\CMS\Toolbar\ToolbarHelper;

/** Native Joomla 5/6 clubs list view. */
final class HtmlView extends BaseHtmlView
{
    public array $items = [];
    public $pagination;
    public $state;
    public array $lists = [];
    public array $season = [];
    public array $association = [];
    public array $search_nation = [];
    public $modelclub = null;
    public ?ClubTable $table = null;

    public function display($tpl = null)
    {
        $model = $this->getModel();

        if (!$model instanceof ClubsModel) {
            throw new \RuntimeException('Clubs model could not be loaded.', 500);
        }

        $this->items = $model->getItems() ?: [];
        $this->pagination = $model->getPagination();
        $this->state = $model->getState();

        if ($errors = $model->getErrors()) {
            throw new \RuntimeException(implode("\n", $errors), 500);
        }

        $app = Factory::getContainer()->get(AdministratorApplication::class);
        $factory = $app->bootComponent('com_sportsmanagement')->getMVCFactory();
        $this->modelclub = $factory->createModel('Club', 'Administrator');
        $this->table = new ClubTable($model->getSportsManagementDatabase());
        $this->lists = $this->buildFilterLists($factory, $model);

        $this->addToolbar();
        parent::display($tpl);
    }

    private function buildFilterLists($factory, ClubsModel $model): array
    {
        $lists = [];
        $seasonOptions = [
            HTMLHelper::_('select.option', '0', Text::_('COM_SPORTSMANAGEMENT_ADMIN_PROJECTS_SEASON_FILTER'), 'id', 'name'),
        ];
        $seasonsModel = $factory->createModel('Seasons', 'Administrator');
        $this->season = $seasonsModel instanceof SeasonsModel ? $seasonsModel->getSeasons() : [];
        $seasonOptions = array_merge($seasonOptions, $this->season);
        $lists['seasons'] = HTMLHelper::_(
            'select.genericlist',
            $seasonOptions,
            'filter_season',
            'class="form-select" onchange="this.form.submit();"',
            'id',
            'name',
            $this->state->get('filter.season')
        );

        $nationOptions = [
            HTMLHelper::_('select.option', '0', Text::_('COM_SPORTSMANAGEMENT_GLOBAL_SELECT_COUNTRY')),
        ];
        $this->search_nation = CountryOptionsHelper::getOptions($model->getSportsManagementDatabase());
        $nationOptions = array_merge($nationOptions, $this->search_nation);
        $lists['nation'] = $nationOptions;
        $lists['nation2'] = HTMLHelper::_(
            'select.genericlist',
            $nationOptions,
            'filter_search_nation',
            'class="form-select" onchange="this.form.submit();"',
            'value',
            'text',
            $this->state->get('filter.search_nation')
        );

        if ($this->state->get('filter.search_nation')) {
            $associationsModel = $factory->createModel('Jlextassociations', 'Administrator');
            if ($associationsModel instanceof JlextassociationsModel) {
                $this->association = $associationsModel->getAssociations();
            }
        }

        $associationOptions = [
            HTMLHelper::_('select.option', '0', Text::_('COM_SPORTSMANAGEMENT_GLOBAL_SELECT_ASSOCIATION'), 'value', 'text'),
            ...$this->association,
        ];
        $lists['association'] = HTMLHelper::_(
            'select.genericlist',
            $associationOptions,
            'filter_search_association',
            'class="form-select" onchange="this.form.submit();"',
            'value',
            'text',
            $this->state->get('filter.search_association')
        );
        $lists['search_mode'] = '';

        return $lists;
    }

    private function addToolbar(): void
    {
        ToolbarHelper::title(Text::_('COM_SPORTSMANAGEMENT_ADMIN_CLUBS_TITLE'), 'users');
        ToolbarHelper::apply('clubs.saveshort');
        ToolbarHelper::divider();
        ToolbarHelper::addNew('club.add');
        ToolbarHelper::editList('club.edit');
        ToolbarHelper::custom('club.import', 'upload', 'upload', Text::_('JTOOLBAR_UPLOAD'), false);
        ToolbarHelper::archiveList('club.export', Text::_('JTOOLBAR_EXPORT'));
    }
}
