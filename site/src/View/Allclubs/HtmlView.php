<?php
/**
 * Joomla 5/6 site list view for clubs.
 *
 * @version    5.6.0
 * @author     diddipoeler
 * @copyright  Copyright (C) diddipoeler
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
namespace Diddipoeler\Component\SportsManagement\Site\View\Allclubs;

\defined('_JEXEC') or die;

use Diddipoeler\Component\SportsManagement\Site\Helper\CountryPresentationHelper;
use Diddipoeler\Component\SportsManagement\Site\Model\AllclubsModel;
use Diddipoeler\Component\SportsManagement\Site\View\SportsManagementHtmlView;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;

final class HtmlView extends SportsManagementHtmlView
{
    public $state = null;
    public array $items = [];
    public $pagination = null;
    public string $tableclass = 'table';
    public int $use_jquery_modal = 2;
    public int $sports_type = 0;
    public string $filter = '';
    public object $form;
    public string $sortDirection = 'ASC';
    public string $sortColumn = 'v.name';
    public array $lists = [];
    public $user = null;
    public int $modalheight = 600;
    public int $modalwidth = 900;

    /**
     * Initialise the native club directory with shared component layouts.
     *
     * Preserves Joomla's parent view setup and registers the global
     * SportsManagement templates used by the historic club listing.
     *
     * @param array $config Joomla view configuration.
     */
    public function __construct($config = [])
    {
        parent::__construct($config);
        $this->addTemplatePath(JPATH_SITE . '/components/com_sportsmanagement/tmpl/globalviews');
    }

    /**
     * Prepare and display the frontend club directory.
     *
     * Requires the native AllclubsModel, reads filtered and paginated
     * clubs, applies the configured sport type and safe table CSS class,
     * and sets the translated Joomla browser document title.
     *
     * @param string|null $tpl Optional Joomla template layout.
     * @return mixed Result of the parent view renderer.
     * @throws \RuntimeException When the club model is unavailable.
     */
    public function display($tpl = null)
    {
        $model = $this->getModel();

        if (!$model instanceof AllclubsModel) {
            throw new \RuntimeException('Allclubs view requires AllclubsModel.', 500);
        }

        $tableClass = trim($this->input->getString('table_class', 'table'));
        $this->tableclass = preg_replace('/[^A-Za-z0-9_\- ]/', '', $tableClass) ?: 'table';
        $this->use_jquery_modal = $this->input->getInt('use_jquery_modal', 2);
        $this->sports_type = (int) $this->params->get('sports_type', 0);
        $this->input->set('sports_type', $this->sports_type);
        $this->modalheight = (int) $this->params->get('modal_popup_height', 600);
        $this->modalwidth = (int) $this->params->get('modal_popup_width', 900);

        $this->state = $model->getState();
        $this->items = $model->getItems() ?: [];
        $this->pagination = $model->getPagination();
        $this->filter = (string) $this->state->get('filter.search', '');
        $this->sortDirection = (string) $this->state->get('filter_order_Dir', 'ASC');
        $this->sortColumn = (string) $this->state->get('filter_order', 'v.name');
        $this->user = $this->app->getIdentity();
        $this->lists = $this->buildFilterLists($model);
        $this->form = (object) ['limitField' => $this->pagination->getLimitBox()];

        $this->getDocument()->setTitle(Text::_('COM_SPORTSMANAGEMENT_ALLCLUBS_PAGE_TITLE'));

        parent::display($tpl);
    }

    /**
     * Create the country selector for the club directory.
     *
     * Reads available country options from the component database
     * and returns the options and Joomla's rendered select control.
     *
     * @param AllclubsModel $model Native club listing model.
     * @return array Options and HTML for the country filter.
     */
    private function buildFilterLists(AllclubsModel $model): array
    {
        $options = [
            HTMLHelper::_('select.option', '0', Text::_('COM_SPORTSMANAGEMENT_GLOBAL_SELECT_COUNTRY')),
            ...CountryPresentationHelper::options($model->getSportsManagementDatabase()),
        ];

        return [
            'nation' => $options,
            'nation2' => HTMLHelper::_(
                'select.genericlist',
                $options,
                'filter_search_nation',
                'class="inputbox" style="width:140px;" onchange="this.form.submit();"',
                'value',
                'text',
                $this->state->get('filter.search_nation')
            ),
        ];
    }
}