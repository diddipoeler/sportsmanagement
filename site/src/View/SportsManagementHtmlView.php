<?php
/**
 * Base Joomla 5/6 HTML view for the SportsManagement site application.
 *
 * @version    4.24.00
 * @author     diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
 * @copyright  Copyright: © 2013-2023 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
namespace Diddipoeler\Component\SportsManagement\Site\View;

\defined('_JEXEC') or die;

use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\Document\Document;
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
    }

    /**
     * Joomla injects the active document after the MVC factory has created the view.
     * Register shared assets only after that injection has happened.
     */
    public function setDocument(Document $document): void
    {
        parent::setDocument($document);

        $document->getWebAssetManager()->registerAndUseScript(
            'com_sportsmanagement.site.modal-image-popup',
            Uri::root(true) . '/components/com_sportsmanagement/assets/js/modal-image-popup.js',
            ['version' => 'auto'],
            ['defer' => true]
        );
    }
}
