<?php
namespace Diddipoeler\Component\SportsManagement\Site\View;

\defined('_JEXEC') or die;

use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\Factory;
use Joomla\CMS\MVC\View\HtmlView;
use Joomla\CMS\Uri\Uri;

abstract class SportsManagementHtmlView extends HtmlView
{
    public $app;
    public $input;
    public $params;
    public $uri;
    public int $databaseSelector = 0;

    public function __construct($config = [])
    {
        $this->option = 'com_sportsmanagement';
        parent::__construct($config);

        $this->app = Factory::getApplication();
        $this->input = $this->app->getInput();
        $this->params = ComponentHelper::getParams($this->option);
        $this->uri = Uri::getInstance();
        $this->databaseSelector = $this->input->getInt('cfg_which_database', 0);

        $this->getDocument()->getWebAssetManager()->registerAndUseScript(
            'com_sportsmanagement.site.modal-image-popup',
            Uri::root(true) . '/components/com_sportsmanagement/assets/js/modal-image-popup.js',
            ['version' => 'auto'],
            ['defer' => true]
        );
    }
}
