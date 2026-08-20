<?php
/** Native Joomla 5/6 administrator view for image-package imports. */
defined('_JEXEC') or die('Restricted access');

use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Toolbar\ToolbarHelper;

class sportsmanagementViewsmimageimports extends sportsmanagementView
{
    public function init()
    {
        // Refresh and synchronize the package manifest before rendering the list.
        $this->model->getimagesxml();
        $this->model->getXMLFiles();
        $this->items = $this->model->getItems();
        $this->total = $this->model->getTotal();
        $this->pagination = $this->model->getPagination();

        $folders = [
            HTMLHelper::_('select.option', '', Text::_('COM_SPORTSMANAGEMENT_ADMIN_IMAGE_FOLDER'), 'id', 'name'),
        ];
        $folders = array_merge($folders, $this->model->getXMLFolder());
        $this->lists = [
            'folders' => HTMLHelper::_(
                'select.genericList',
                $folders,
                'filter_image_folder',
                'class="form-select" onchange="this.form.submit();"',
                'id',
                'name',
                (string) $this->state->get('filter.image_folder')
            ),
        ];
    }

    protected function addToolbar()
    {
        $this->title = Text::_('COM_SPORTSMANAGEMENT_ADMIN_IMAGES_IMPORT');
        $this->icon = 'images-import';
        ToolbarHelper::custom('smimageimports.import', 'upload', 'upload', Text::_('JTOOLBAR_UPLOAD'), false);
        ToolbarHelper::divider();
        parent::addToolbar();
    }
}
