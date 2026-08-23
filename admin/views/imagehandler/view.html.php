<?php
/**
 * SportsManagement administrator image handler view.
 */

defined('_JEXEC') or die('Restricted access');

use Diddipoeler\Component\SportsManagement\Site\Helper\ImageSelectHelper;
use Joomla\CMS\Client\ClientHelper;
use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\Language\Text;

class sportsmanagementViewImagehandler extends sportsmanagementView
{
    public function init()
    {
        $this->ensureImageSelectHelper();

        $input  = $this->app->getInput();
        $data   = $input->getArray();
        $layout = $this->getLayout();

        if (in_array($layout, ['upload', 'upload_3', 'upload_4'], true)) {
            $this->displayUpload();
            return;
        }

        if (in_array($layout, ['uploaddraganddrop', 'uploaddraganddrop_3', 'uploaddraganddrop_4'], true)) {
            $this->folder    = ImageSelectHelper::getFolder((string) ($data['type'] ?? ''));
            $this->pid       = max(0, (int) ($data['pid'] ?? 0));
            $this->mid       = max(0, (int) ($data['mid'] ?? 0));
            $this->imagelist = !empty($data['imagelist']) ? 1 : 0;
            $this->setLayout('uploaddraganddrop');
            return;
        }

        if (in_array($layout, ['default_3', 'default_4'], true)) {
            $this->setLayout('default');
        }

        $type    = (string) ($data['type'] ?? '');
        $folder  = ImageSelectHelper::getFolder($type);
        $field   = (string) ($data['field'] ?? '');
        $fieldId = (string) ($data['fieldid'] ?? '');
        $search  = trim(mb_strtolower((string) $this->app->getUserStateFromRequest(
            'com_sportsmanagement.imageselect',
            'search',
            '',
            'string'
        )));

        $input->set('folder', $folder);
        $this->model->setState('folder', $folder);
        $this->model->setState('search', $search);

        $images  = $this->model->getImages();
        $pageNav = $this->model->getPagination();

        if ($images || $search !== '') {
            $this->images  = $images;
            $this->type    = $type;
            $this->folder  = $folder;
            $this->search  = $search;
            $this->state   = $this->model->getState();
            $this->pageNav = $pageNav;
            $this->field   = $field;
            $this->fieldid = $fieldId;
            return;
        }

        $this->app->enqueueMessage(Text::_('COM_SPORTSMANAGEMENT_ADMIN_IMAGEHANDLER_NO_IMAGES'), 'warning');
        $this->displayUpload();
    }

    private function displayUpload(): void
    {
        $this->ensureImageSelectHelper();

        $input  = $this->app->getInput();
        $option = $input->getCmd('option', 'com_sportsmanagement');
        $type   = $input->getCmd('type');

        $this->params  = ComponentHelper::getParams($option);
        $this->ftp     = ClientHelper::setCredentialsFromRequest('ftp');
        $this->folder  = ImageSelectHelper::getFolder($type);
        $this->field   = $input->getCmd('field');
        $this->fieldid = $input->getCmd('fieldid');
        $input->set('hidemainmenu', 1);
        $this->menu    = 1;
        $this->setLayout('upload');
    }

    public function setImage($index = 0)
    {
        $this->_tmp_img = $this->images[$index] ?? new \stdClass();
    }

    private function ensureImageSelectHelper(): void
    {
        if (class_exists(ImageSelectHelper::class)) {
            return;
        }

        $helperFile = JPATH_SITE . '/components/com_sportsmanagement/src/Helper/ImageSelectHelper.php';

        if (is_file($helperFile)) {
            require_once $helperFile;
        }

        if (!class_exists(ImageSelectHelper::class)) {
            throw new \RuntimeException('SportsManagement image select helper is unavailable.', 500);
        }
    }
}
