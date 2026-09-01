<?php
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

    public function __construct($config = [])
    {
        parent::__construct($config);
        $this->addTemplatePath(JPATH_SITE . '/components/com_sportsmanagement/tmpl/globalviews');
    }

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
