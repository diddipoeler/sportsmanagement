<?php
/**
 * Native Joomla 5/6 frontend image selector and upload view.
 *
 * @version    5.6.0
 * @author     diddipoeler
 * @copyright  Copyright (C) diddipoeler
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
namespace Diddipoeler\Component\SportsManagement\Site\View\Imagehandler;

\defined('_JEXEC') or die;

use Diddipoeler\Component\SportsManagement\Site\Helper\ImageSelectHelper;
use Diddipoeler\Component\SportsManagement\Site\Model\ImagehandlerModel;
use Joomla\CMS\Application\SiteApplication;
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

    /**
     * Initialise the Joomla 5/6 frontend image selector view.
     *
     * Configure the historical imagehandler template directory so existing
     * layout overrides continue to resolve, delegate initialisation to
     * Joomla's base view and prepare an empty selected-image placeholder.
     *
     * @param array $config Joomla MVC view configuration.
     * @return void
     */
    public function __construct($config = [])
    {
        $config['template_path'] = JPATH_SITE . '/components/com_sportsmanagement/views/imagehandler/tmpl';
        parent::__construct($config);
        $this->_tmp_img = new \stdClass();
    }

    /**
     * Render the image browser or upload interface for frontend editors.
     *
     * Resolve the native imagehandler model and site request, select the
     * upload layout when requested, or collect the allowed image folder,
     * search state, image rows and pagination. When the selected folder
     * has no images, fall back to the upload layout after logging a notice.
     *
     * @param string|null $tpl Optional Joomla layout suffix.
     * @return void The parent view renders the chosen template.
     * @throws \RuntimeException If the imagehandler model is missing.
     */
    public function display($tpl = null): void
    {
        $model = $this->getModel();

        if (!$model instanceof ImagehandlerModel) {
            throw new \RuntimeException('ImagehandlerModel is unavailable.', 500);
        }

        $app = $this->siteApplication();
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

    /**
     * Select the image record used by legacy imagehandler templates.
     *
     * Normalise the requested array index to a nonnegative integer and
     * retain only object-valued image records. Use an empty object when
     * the index is missing or the data provider returns an invalid row.
     *
     * @param int|string $index Index into the prepared image listing.
     * @return void The temporary image property is updated.
     */
    public function setImage($index = 0): void
    {
        $index = max(0, (int) $index);
        $image = $this->images[$index] ?? null;

        // Imported image lists can contain invalid row types; keep the
        // view property object-typed to avoid PHP 8 TypeError exceptions.
        $this->_tmp_img = is_object($image) ? $image : new \stdClass();
    }

    /**
     * Prepare the Joomla frontend upload form and request context.
     *
     * Resolve the permitted image folder and legacy target-field values,
     * collect component settings and FTP credentials, hide the main menu
     * and expose the model state required by the upload template.
     *
     * @param ImagehandlerModel $model Native frontend imagehandler model.
     * @return void Upload view properties and Joomla input are updated.
     */
    private function prepareUpload(ImagehandlerModel $model): void
    {
        $app = $this->siteApplication();
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

    /**
     * Obtain the concrete Joomla frontend application from the container.
     *
     * Resolve the site application through Joomla dependency injection
     * and reject an unavailable or incompatible service before reading
     * request input or showing editor layouts.
     *
     * @return SiteApplication Active Joomla frontend application.
     * @throws \RuntimeException If no valid site service is available.
     */
    private function siteApplication(): SiteApplication
    {
        /** @var SiteApplication $app */
        $app = Factory::getContainer()->get(SiteApplication::class);

        if (!$app instanceof SiteApplication) {
            throw new \RuntimeException('SportsManagement image handler requires the Joomla site application.', 500);
        }

        return $app;
    }
}
