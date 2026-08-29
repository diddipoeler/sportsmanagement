<?php
namespace Diddipoeler\Component\SportsManagement\Administrator\View\Smimageimports;

\defined('_JEXEC') or die;

use Diddipoeler\Component\SportsManagement\Administrator\Model\SmimageimportsModel;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\MVC\View\HtmlView as BaseHtmlView;
use Joomla\CMS\Toolbar\ToolbarHelper;
use Joomla\CMS\Uri\Uri;

/** Native Joomla 5/6 administrator view for image-package imports. */
final class HtmlView extends BaseHtmlView
{
    public $state;
    public $pagination;
    public array $items = [];
    public int $total = 0;
    public array $lists = [];
    public string $request_url = '';
    public string $sortDirection = 'ASC';
    public string $sortColumn = 'obj.name';

    public function __construct($config = [])
    {
        parent::__construct($config);
        $this->addTemplatePath(JPATH_ADMINISTRATOR . '/components/com_sportsmanagement/views/footer/tmpl');
    }

    public function display($tpl = null)
    {
        $model = $this->getModel();

        if (!$model instanceof SmimageimportsModel) {
            throw new \RuntimeException('SmimageimportsModel is required.', 500);
        }

        $model->getimagesxml();
        $model->getXMLFiles();

        $this->state = $this->get('State');
        $this->items = $this->get('Items') ?: [];
        $this->total = (int) $this->get('Total');
        $this->pagination = $this->get('Pagination');
        $this->request_url = Uri::getInstance()->toString();
        $this->sortDirection = (string) $this->state->get('list.direction', 'ASC');
        $this->sortColumn = (string) $this->state->get('list.ordering', 'obj.name');

        $folders = [
            HTMLHelper::_('select.option', '', Text::_('COM_SPORTSMANAGEMENT_ADMIN_IMAGE_FOLDER'), 'id', 'name'),
        ];
        $folders = array_merge($folders, $model->getXMLFolder());
        $this->lists['folders'] = HTMLHelper::_(
            'select.genericList',
            $folders,
            'filter_image_folder',
            'class="form-select" onchange="this.form.submit();"',
            'id',
            'name',
            (string) $this->state->get('filter.image_folder')
        );

        ToolbarHelper::title(Text::_('COM_SPORTSMANAGEMENT_ADMIN_IMAGES_IMPORT'), 'images-import');
        ToolbarHelper::custom('smimageimports.import', 'upload', 'upload', Text::_('JTOOLBAR_UPLOAD'), false);
        ToolbarHelper::divider();

        parent::display($tpl);
    }
}
