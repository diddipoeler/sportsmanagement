<?php
namespace Diddipoeler\Component\SportsManagement\Site\View\Imagehandler;

\defined('_JEXEC') or die;

use Diddipoeler\Component\SportsManagement\Site\Helper\ImageSelectHelper;
use Diddipoeler\Component\SportsManagement\Site\Model\ImagehandlerModel;
use Joomla\CMS\Client\ClientHelper;
use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Log\Log;
use Joomla\CMS\MVC\View\HtmlView as BaseHtmlView;
use Joomla\CMS\Pagination\Pagination;
use Joomla\CMS\Uri\Uri;
use Joomla\Registry\Registry;

/** Joomla 5/6 frontend image selector and upload view. */
final class HtmlView extends BaseHtmlView
{
    public array $images = [];
    public string $type = '';
    public string $folder = '';
    public string $search = '';
    public string $field = '';
    public string $fieldid = '';
    public string $request_url = '';
    public ?Pagination $pageNav = null;
    public ?Registry $params = null;
    public mixed $ftp = false;
    public mixed $state = null;
    public int $menu = 0;
    public object $_tmp_img;

    public function __construct($config = [])
    {
        $config['template_path'] = JPATH_SITE . '/components/com_sportsmanagement/views/imagehandler/tmpl';
        parent::__construct($config);
        $this->_tmp_img = new \stdClass();
    }

    public function display($tpl = null): void
    {
        $model = $this->getModel();

        if (!$model instanceof ImagehandlerModel) {
            throw new \RuntimeException('ImagehandlerModel is unavailable.', 500);
        }

        $app = Factory::getApplication();
        $input = $app->getInput();
        $layout = strtolower((string) $this->getLayout());

        if (in_array($layout, ['upload', 'uploaddraganddrop'], true)) {
            // The frontend package only ships the standard upload template.
            if ($layout !== 'upload') {
                $this->setLayout('upload');
            }

            $this->prepareUpload($model);
            parent::display($tpl);
            return;
        }

        $this->type = $input->getCmd('type', '');
        $this->folder = ImageSelectHelper::getFolder($this->type);
        $this->field = $input->getString('field', '');
        $this->fieldid = $input->getString('fieldid', '');
        $this->request_url = Uri::getInstance()->toString();

        $model->setFolder($this->folder);
        $this->images = $model->getImages();
        $this->pageNav = $model->getPagination();
        $this->search = (string) $model->getState('search', '');
        $this->state = $model->getState();

        if ($this->images || $this->search !== '') {
            parent::display($tpl);
            return;
        }

        Log::add(Text::_('COM_SPORTSMANAGEMENT_ADMIN_IMAGEHANDLER_NO_IMAGES'), Log::INFO, 'jsmerror');
        $this->setLayout('upload');
        $this->prepareUpload($model);
        parent::display($tpl);
    }

    public function setImage($index = 0): void
    {
        $index = max(0, (int) $index);
        $this->_tmp_img = $this->images[$index] ?? new \stdClass();
    }

    private function prepareUpload(ImagehandlerModel $model): void
    {
        $app = Factory::getApplication();
        $input = $app->getInput();

        $this->type = $input->getCmd('type', $this->type);
        $this->folder = ImageSelectHelper::getFolder($this->type);
        $this->field = $input->getString('field', $this->field);
        $this->fieldid = $input->getString('fieldid', $this->fieldid);
        $this->request_url = Uri::getInstance()->toString();
        $this->params = ComponentHelper::getParams('com_sportsmanagement');
        $this->ftp = ClientHelper::setCredentialsFromRequest('ftp');
        $this->menu = 1;
        $input->set('hidemainmenu', 1);
        $model->setFolder($this->folder);
        $this->state = $model->getState();
    }
}
