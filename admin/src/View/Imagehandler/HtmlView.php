<?php
namespace Diddipoeler\Component\SportsManagement\Administrator\View\Imagehandler;

\defined('_JEXEC') or die;

use Diddipoeler\Component\SportsManagement\Administrator\Model\ImagehandlerModel;
use Diddipoeler\Component\SportsManagement\Site\Helper\ImageSelectHelper;
use Joomla\CMS\Client\ClientHelper;
use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\MVC\View\HtmlView as BaseHtmlView;
use Joomla\CMS\Uri\Uri;

/** Native Joomla 5/6 administrator image selector and upload view. */
final class HtmlView extends BaseHtmlView
{
    public string $option = 'com_sportsmanagement';
    public string $request_url = '';
    public array $images = [];
    public string $type = '';
    public string $folder = '';
    public string $search = '';
    public $state;
    public $pageNav;
    public string $field = '';
    public string $fieldid = '';
    public $params;
    public $ftp = false;
    public int $menu = 0;
    public int $pid = 0;
    public int $mid = 0;
    public int $imagelist = 0;
    public object $_tmp_img;
    public string $bootstrap_fileinput_version = '5.1.0';
    public string $bootstrap_fileinput_popperversion = '2.10.2';
    public string $bootstrap_fileinput_bootstrapversion = '5.1.0';

    public function display($tpl = null)
    {
        $this->ensureImageSelectHelper();

        $app = $this->getApplication();
        $input = $app->getInput();
        $model = $this->getModel();

        if (!$model instanceof ImagehandlerModel) {
            throw new \RuntimeException('ImagehandlerModel is unavailable.', 500);
        }

        $this->request_url = Uri::getInstance()->toString();
        $layout = strtolower((string) $this->getLayout());
        $data = $input->getArray();

        if (in_array($layout, ['upload', 'upload_3', 'upload_4'], true)) {
            $this->prepareUpload();
        } elseif (in_array($layout, ['uploaddraganddrop', 'uploaddraganddrop_3', 'uploaddraganddrop_4'], true)) {
            $this->folder = ImageSelectHelper::getFolder((string) ($data['type'] ?? ''));
            $this->pid = max(0, (int) ($data['pid'] ?? 0));
            $this->mid = max(0, (int) ($data['mid'] ?? 0));
            $this->imagelist = !empty($data['imagelist']) ? 1 : 0;
            $this->setLayout('uploaddraganddrop');
        } else {
            if (in_array($layout, ['default_3', 'default_4'], true)) {
                $this->setLayout('default');
            }

            $this->type = (string) ($data['type'] ?? '');
            $this->folder = ImageSelectHelper::getFolder($this->type);
            $this->field = (string) ($data['field'] ?? '');
            $this->fieldid = (string) ($data['fieldid'] ?? '');
            $this->search = trim(mb_strtolower((string) $app->getUserStateFromRequest(
                'com_sportsmanagement.imageselect',
                'search',
                '',
                'string'
            )));

            $input->set('folder', $this->folder);
            $model->setState('folder', $this->folder);
            $model->setState('search', $this->search);

            $this->images = $model->getImages();
            $this->pageNav = $model->getPagination();
            $this->state = $model->getState();

            if (!$this->images && $this->search === '') {
                $app->enqueueMessage(Text::_('COM_SPORTSMANAGEMENT_ADMIN_IMAGEHANDLER_NO_IMAGES'), 'warning');
                $this->prepareUpload();
            }
        }

        parent::display($tpl);
    }

    public function setImage($index = 0): void
    {
        $this->_tmp_img = $this->images[(int) $index] ?? new \stdClass();
    }

    private function prepareUpload(): void
    {
        $input = $this->getApplication()->getInput();
        $this->option = $input->getCmd('option', 'com_sportsmanagement');
        $type = $input->getCmd('type');

        $this->params = ComponentHelper::getParams($this->option);
        $this->ftp = ClientHelper::setCredentialsFromRequest('ftp');
        $this->folder = ImageSelectHelper::getFolder($type);
        $this->field = $input->getCmd('field');
        $this->fieldid = $input->getCmd('fieldid');
        $input->set('hidemainmenu', 1);
        $this->menu = 1;
        $this->setLayout('upload');
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
