<?php
namespace Diddipoeler\Component\SportsManagement\Site\View\Allprojects;

\defined('_JEXEC') or die;

use Diddipoeler\Component\SportsManagement\Site\Helper\CountryPresentationHelper;
use Diddipoeler\Component\SportsManagement\Site\Model\AllprojectsModel;
use Diddipoeler\Component\SportsManagement\Site\View\SportsManagementHtmlView;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;

final class HtmlView extends SportsManagementHtmlView
{
    public $state = null;
    public array $items = [];
    public $pagination = null;
    public string $tableclass = 'table';
    public string $template = 'ranking';
    public int $use_jquery_modal = 2;
    public string $filter = '';
    public object $form;
    public $user = null;
    public string $sortDirection = 'ASC';
    public string $sortColumn = 'v.name';
    public array $lists = [];
    public string $divclasscontainer = 'container-fluid';
    public string $divclassrow = 'row-fluid';
    public int $modalheight = 600;
    public int $modalwidth = 900;

    public function __construct($config = [])
    {
        parent::__construct($config);
        $this->addTemplatePath(JPATH_SITE . '/components/com_sportsmanagement/tmpl/globalviews');
    }

    public function display($tpl = null)
    {
        $model = $this->getModel();

        if (!$model instanceof AllprojectsModel) {
            throw new \RuntimeException('Allprojects view requires AllprojectsModel.', 500);
        }

        $tableClass = trim($this->input->getString('table_class', 'table'));
        $this->tableclass = preg_replace('/[^A-Za-z0-9_\- ]/', '', $tableClass) ?: 'table';
        $this->template = $this->input->getCmd('template', 'ranking') ?: 'ranking';
        $this->use_jquery_modal = $this->input->getInt('use_jquery_modal', 2);
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

        $this->getDocument()->setTitle(Text::_('COM_SPORTSMANAGEMENT_ALLPROJECTS_PAGE_TITLE'));
        parent::display($tpl);
    }

    private function buildFilterLists(AllprojectsModel $model): array
    {
        $countryOptions = [
            HTMLHelper::_('select.option', '0', Text::_('COM_SPORTSMANAGEMENT_GLOBAL_SELECT_COUNTRY')),
            ...CountryPresentationHelper::options($model->getDatabase()),
        ];
        $lists = [
            'nation2' => HTMLHelper::_(
                'select.genericlist',
                $countryOptions,
                'filter_search_nation',
                'class="inputbox" style="width:140px;" onchange="this.form.submit();"',
                'value',
                'text',
                $this->state->get('filter.search_nation')
            ),
        ];

        $leagueOptions = [
            HTMLHelper::_('select.option', '', Text::_('COM_SPORTSMANAGEMENT_GLOBAL_SELECT_LEAGUES'), 'id', 'name'),
        ];
        $leagueOptions = array_merge($leagueOptions, $model->getLeagueOptions());
        $lists['leagues'] = HTMLHelper::_(
            'select.genericlist',
            $leagueOptions,
            'filter_search_leagues',
            'class="inputbox" style="width:140px;" onchange="this.form.submit();"',
            'id',
            'name',
            $this->state->get('filter.search_leagues')
        );

        $seasonOptions = [
            HTMLHelper::_('select.option', '', Text::_('COM_SPORTSMANAGEMENT_GLOBAL_SELECT_SEASONS'), 'id', 'name'),
        ];
        $seasonOptions = array_merge($seasonOptions, $model->getSeasonOptions());
        $lists['seasons'] = HTMLHelper::_(
            'select.genericlist',
            $seasonOptions,
            'filter_search_seasons',
            'class="inputbox" style="width:140px;" onchange="this.form.submit();"',
            'id',
            'name',
            $this->state->get('filter.search_seasons')
        );

        return $lists;
    }
}
