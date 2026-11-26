<?php
/**
 * Base Joomla 5/6 HTML view for the SportsManagement site application.
 *
 * @version    5.6.0
 * @author     diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
 * @copyright  Copyright: © 2013-2023 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
namespace Diddipoeler\Component\SportsManagement\Site\View;

\defined('_JEXEC') or die;

use Diddipoeler\Component\SportsManagement\Site\Service\SportsManagementSiteApplicationResolver;
use Joomla\CMS\Application\CMSApplicationInterface;
use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\Document\Document;
use Joomla\CMS\Factory;
use Joomla\CMS\MVC\View\HtmlView;
use Joomla\CMS\Uri\Uri;
use Joomla\Database\DatabaseInterface;

abstract class SportsManagementHtmlView extends HtmlView
{
    public $app;
    public $input;
    public $params;
    public $uri;
    public int $databaseSelector = 0;

    /**
     * Resolve the active frontend application through the shared service.
     * Rejects unsupported application clients before view initialization.
     * @return CMSApplicationInterface Validated Joomla site application.
     */
    private static function siteApplication(): CMSApplicationInterface
    {
        return SportsManagementSiteApplicationResolver::resolve();
    }

    /**
     * Obtain the native Joomla database service for frontend view helpers.
     * Verifies the container result before passing the driver to services.
     * @return DatabaseInterface Joomla default database connection.
     * @throws \RuntimeException When the service is invalid.
     */
    protected static function joomlaDatabase(): DatabaseInterface
    {
        $database = Factory::getContainer()->get(DatabaseInterface::class);

        if (!$database instanceof DatabaseInterface) {
            throw new \RuntimeException('SportsManagement site view database service is unavailable.', 500);
        }

        return $database;
    }

    /**
     * Initialize view context without accessing a document before injection.
     * Sets shared template paths, component configuration and the validated
     * request database mode using the same interpretation as site models.
     * @param array $config Joomla MVC view configuration.
     */
    public function __construct($config = [])
    {
        $this->option = 'com_sportsmanagement';
        parent::__construct($config);
        $this->addTemplatePath(JPATH_SITE . '/components/com_sportsmanagement/tmpl/globalviews');

        $this->app = self::siteApplication();
        $this->input = $this->app->getInput();
        $this->params = ComponentHelper::getParams($this->option);
        $this->uri = Uri::getInstance();
        $raw = $this->input->get('cfg_which_database', 0, 'raw');
        $this->databaseSelector = $raw === 1 || (is_string($raw) && strlen($raw) <= 64
            && preg_match('/^0*1$/D', $raw) === 1) ? 1 : 0;
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
