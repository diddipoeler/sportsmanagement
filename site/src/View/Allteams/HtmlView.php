<?php
/**
 * Joomla 5/6 site list view for teams.
 *
 * @version    5.6.0
 * @author     diddipoeler
 * @copyright  Copyright (C) diddipoeler
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
namespace Diddipoeler\Component\SportsManagement\Site\View\Allteams;

\defined('_JEXEC') or die;

use Diddipoeler\Component\SportsManagement\Site\Helper\CountryPresentationHelper;
use Diddipoeler\Component\SportsManagement\Site\Model\AllteamsModel;
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
    public string $filter = '';
    public object $form;
    public string $sortDirection = 'ASC';
    public string $sortColumn = 'v.name';
    public array $lists = [];
    public string $divclasscontainer = 'container-fluid';
    public string $divclassrow = 'row';
    public int $modalheight = 600;
    public int $modalwidth = 900;

    /**
     * Initialise the teams directory view and legacy global templates.
     *
     * Forwards Joomla view configuration to its base class and registers
     * the shared SportsManagement directory template location.
     *
     * @param array $config Joomla view configuration options.
     */
    public function __construct($config = [])
    {
        parent::__construct($config);
        $this->addTemplatePath(JPATH_SITE . '/components/com_sportsmanagement/tmpl/globalviews');
    }

    /**
     * Prepare the public teams directory and render its Joomla layout.
     *
     * Ensures the expected AllteamsModel is available, loads paginated
     * records and filtering state, sanitises the table CSS class, builds
     * country filter widgets and sets the translated document title.
     *
     * @param string|null $tpl Optional layout template name.
     * @return mixed Parent view rendering result.
     * @throws \RuntimeException If the directory model is unavailable.
     */
    public function display($tpl = null)
    {
        $model = $this->getModel();

        if (!$model instanceof AllteamsModel) {
            throw new \RuntimeException('Allteams view requires AllteamsModel.', 500);
        }

        $tableClass = trim($this->input->getString('table_class', 'table'));
        $this->tableclass = preg_replace('/[^A-Za-z0-9_\- ]/', '', $tableClass) ?: 'table';
        $this->use_jquery_modal = $this->input->getInt('use_jquery_modal', 2);
        $this->modalheight = (int) $this->params->get('modal_popup_height', 600);
        $this->modalwidth = (int) $this->params->get('modal_popup_width', 900);

        $this->state = $model->getState();
        $this->items = $model->getItems() ?: [];
        $this->pagination = $model->getPagination();
        $this->filter = (string) $this->state->get('filter.search', '');
        $this->sortDirection = (string) $this->state->get('filter_order_Dir', 'ASC');
        $this->sortColumn = (string) $this->state->get('filter_order', 'v.name');
        $this->lists = $this->buildFilterLists($model);
        $this->form = (object) ['limitField' => $this->pagination->getLimitBox()];

        $this->getDocument()->setTitle(Text::_('COM_SPORTSMANAGEMENT_ALLTEAMS_PAGE_TITLE'));

        parent::display($tpl);
    }

    /**
     * Build the country selection filter for the public teams directory.
     *
     * Uses the active SportsManagement database to populate country
     * options and renders a Joomla-compatible select control for the
     * existing frontend directory template.
     *
     * @param AllteamsModel $model The native directory model.
     * @return array Country options and rendered filter HTML.
     */
    private function buildFilterLists(AllteamsModel $model): array
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