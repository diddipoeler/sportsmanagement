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
use Diddipoeler\Component\SportsManagement\Administrator\Service\SportsManagementAdministratorApplicationResolver;
use Diddipoeler\Component\SportsManagement\Administrator\Table\ClubTable;
use Joomla\CMS\Application\CMSApplicationInterface;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\MVC\View\HtmlView as BaseHtmlView;
use Joomla\CMS\Toolbar\ToolbarHelper;

/** Native Joomla 5/6 clubs list view. */
final class HtmlView extends BaseHtmlView
{
    /**
     * Resolve the native Joomla administrator application for the club view.
     * Uses the component application service instead of obsolete
     * static application factories when creating dependent MVC models.
     * @return CMSApplicationInterface Active Joomla administrator application.
     */
    private static function administratorApplication(): CMSApplicationInterface
    {
        return SportsManagementAdministratorApplicationResolver::resolve();
    }

    public array $items = [];
    /** Escaped country names and flags indexed by ISO alpha-3 code. */
    public array $countryPresentation = [];
    public $pagination;
    public $state;
    public array $lists = [];
    public array $season = [];
    public array $association = [];
    public array $search_nation = [];
    public $modelclub = null;
    public ?ClubTable $table = null;

    /**
     * Render the native club list using Joomla's MVC view API.
     * Loads the ClubsModel, pagination and state, verifies model errors,
     * initialises dependent club/season/association models, and prepares
     * the toolbar and filters before delegating to parent display().
     * @param mixed $tpl Optional Joomla layout override.
     * @return mixed Result returned by Joomla's base HTML view.
     */
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

        $app = self::administratorApplication();
        $factory = $app->bootComponent('com_sportsmanagement')->getMVCFactory();
        $this->modelclub = $factory->createModel('Club', 'Administrator');
        $this->table = new ClubTable($model->getSportsManagementDatabase());
        $this->lists = $this->buildFilterLists($factory, $model);
        $this->countryPresentation = CountryOptionsHelper::getListPresentation($model->getSportsManagementDatabase());

        $this->addToolbar();
        parent::display($tpl);
    }

    /**
     * Prepare the season, nation and association filter widgets.
     * Creates selector options via namespaced Joomla MVC models, keeps
     * the current list-state choices and returns HTML controls used by
     * the frontend-compatible administrator template.
     * @param mixed $factory Active Joomla MVCFactory.
     * @param ClubsModel $model Club list model supplying filters and database.
     * @return array<string,mixed> Prepared filter widget collection.
     */
    private function buildFilterLists($factory, ClubsModel $model): array
    {
        $lists = [];
        $seasonOptions = [
            HTMLHelper::_('select.option', '0', Text::_('COM_SPORTSMANAGEMENT_ADMIN_PROJECTS_SEASON_FILTER'), 'id', 'name'),
        ];
        $seasonsModel = $factory->createModel('Seasons', 'Administrator');
        $this->season = $seasonsModel instanceof SeasonsModel ? $seasonsModel->getSeasons() : [];
        $seasonOptions = array_merge($seasonOptions, $this->season);
        $lists['seasons'] = '<joomla-field-fancy-select search-placeholder="' 
            . htmlspecialchars(Text::_('JGLOBAL_TYPE_OR_SELECT_SOME_OPTIONS'), ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8')
            . '">' . HTMLHelper::_(
            'select.genericlist',
            $seasonOptions,
            'filter_season',
            'class="form-select" onchange="this.form.submit();"',
            'id',
            'name',
            $this->state->get('filter.season')
        ) . '</joomla-field-fancy-select>';

        $nationOptions = [
            HTMLHelper::_('select.option', '0', Text::_('COM_SPORTSMANAGEMENT_GLOBAL_SELECT_COUNTRY')),
        ];
        $this->search_nation = CountryOptionsHelper::getOptions($model->getSportsManagementDatabase());
        $nationOptions = array_merge($nationOptions, $this->search_nation);
        $lists['nation'] = $nationOptions;
        Text::script('JGLOBAL_SELECT_NO_RESULTS_MATCH');
        Text::script('JGLOBAL_SELECT_PRESS_TO_SELECT');
        $this->getDocument()->getWebAssetManager()
            ->usePreset('choicesjs')
            ->useScript('webcomponent.field-fancy-select');

        $lists['nation2'] = '<joomla-field-fancy-select search-placeholder="'
            . htmlspecialchars(Text::_('JGLOBAL_TYPE_OR_SELECT_SOME_OPTIONS'), ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8')
            . '">' . HTMLHelper::_(
            'select.genericlist',
            $nationOptions,
            'filter_search_nation',
            'class="form-select" onchange="this.form.submit();"',
            'value',
            'text',
            $this->state->get('filter.search_nation')
        ) . '</joomla-field-fancy-select>';

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
        $lists['association'] = '<joomla-field-fancy-select search-placeholder="' 
            . htmlspecialchars(Text::_('JGLOBAL_TYPE_OR_SELECT_SOME_OPTIONS'), ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8')
            . '">' . HTMLHelper::_(
            'select.genericlist',
            $associationOptions,
            'filter_search_association',
            'class="form-select" onchange="this.form.submit();"',
            'value',
            'text',
            $this->state->get('filter.search_association')
        ) . '</joomla-field-fancy-select>';
        $lists['search_mode'] = '';

        return $lists;
    }

    /**
     * Build the administrator toolbar for club management.
     * Adds list title and inline-save, create, edit, import and export
     * actions using native Joomla ToolbarHelper commands.
     * @return void
     */
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
