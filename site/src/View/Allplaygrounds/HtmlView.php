<?php
namespace Diddipoeler\Component\SportsManagement\Site\View\Allplaygrounds;

\defined('_JEXEC') or die;

use Diddipoeler\Component\SportsManagement\Site\Model\AllplaygroundsModel;
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
    public string $divclassrow = 'row-fluid';
    public int $modalheight = 600;
    public int $modalwidth = 900;

    public function __construct($config = [])
    {
        parent::__construct($config);
        $this->addTemplatePath(JPATH_SITE . '/components/com_sportsmanagement/tmpl/globalviews');
        $this->loadPresentationDependencies();
    }

    public function display($tpl = null)
    {
        $model = $this->getModel();

        if (!$model instanceof AllplaygroundsModel) {
            throw new \RuntimeException('Allplaygrounds view requires AllplaygroundsModel.', 500);
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
        $this->lists = $this->buildFilterLists();
        $this->form = (object) ['limitField' => $this->pagination->getLimitBox()];

        $this->getDocument()->setTitle(Text::_('COM_SPORTSMANAGEMENT_ALLPLAYGROUNDS_PAGE_TITLE'));

        parent::display($tpl);
    }

    private function buildFilterLists(): array
    {
        $options = [
            HTMLHelper::_('select.option', '0', Text::_('COM_SPORTSMANAGEMENT_GLOBAL_SELECT_COUNTRY')),
        ];

        if (class_exists('JSMCountries') && ($countries = \JSMCountries::getCountryOptions())) {
            $options = array_merge($options, $countries);
        }

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

    private function loadPresentationDependencies(): void
    {
        $classes = [
            'sportsmanagementHelper' => JPATH_ADMINISTRATOR . '/components/com_sportsmanagement/helpers/sportsmanagement.php',
            'sportsmanagementHelperHtml' => JPATH_SITE . '/components/com_sportsmanagement/helpers/html.php',
            'sportsmanagementHelperRoute' => JPATH_SITE . '/components/com_sportsmanagement/helpers/route.php',
            'JSMCountries' => JPATH_SITE . '/components/com_sportsmanagement/helpers/countries.php',
        ];

        foreach ($classes as $class => $path) {
            if (!class_exists($class, false) && is_file($path)) {
                require_once $path;
            }
        }
    }
}
