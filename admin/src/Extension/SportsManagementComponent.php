<?php
/**
 * @package     SportsManagement
 * @subpackage  com_sportsmanagement
 *
 * Joomla 5/6 migration scaffold.
 *
 * @version    5.6.0
 * @author     diddipoeler
 * @copyright  Copyright (C) diddipoeler
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Diddipoeler\Component\SportsManagement\Administrator\Extension;

defined('_JEXEC') or die;

use Diddipoeler\Component\SportsManagement\Administrator\Helper\ExtensionLanguageHelper;
use Joomla\CMS\Application\CMSApplicationInterface;
use Joomla\CMS\Component\Router\RouterInterface;
use Joomla\CMS\Component\Router\RouterServiceInterface;
use Joomla\CMS\Component\Router\RouterServiceTrait;
use Joomla\CMS\Extension\BootableExtensionInterface;
use Joomla\CMS\Extension\MVCComponent;
use Joomla\CMS\Factory;
use Joomla\CMS\HTML\HTMLRegistryAwareTrait;
use Joomla\CMS\Menu\AbstractMenu;
use Psr\Container\ContainerInterface;

/**
 * Component extension class for the Joomla 5/6 architecture.
 *
 * This class is introduced before the modern dispatcher is activated so that
 * the existing SportsManagement entry points can continue to run while the MVC
 * classes are migrated incrementally.
 */
final class SportsManagementComponent extends MVCComponent implements RouterServiceInterface, BootableExtensionInterface
{
    use HTMLRegistryAwareTrait;
    use RouterServiceTrait {
        createRouter as private createFactoryRouter;
    }

    /**
     * Load the language sources which were historically loaded from the legacy
     * component entry points. Native Joomla 5/6 dispatcher requests bypass those
     * entry points, so the language bootstrap has to live on the component itself.
     */
    public function boot(ContainerInterface $container): void
    {
        $app = Factory::getApplication();
        $language = $app->getLanguage();
        $tag = $language->getTag();

        // Load both component language files. The active client is loaded last so
        // client-specific translations win while shared historical keys remain available.
        $basePaths = $app->isClient('administrator')
            ? [JPATH_SITE, JPATH_ADMINISTRATOR]
            : [JPATH_ADMINISTRATOR, JPATH_SITE];

        foreach ($basePaths as $basePath) {
            $language->load('com_sportsmanagement', $basePath, $tag, true);
        }

        $language->load(
            'com_sportsmanagement_countries',
            JPATH_ADMINISTRATOR,
            $tag,
            true
        );

        if ($app->isClient('administrator')) {
            foreach (ExtensionLanguageHelper::forView($app->getInput()->getCmd('view', '')) as $extensionName) {
                $basePath = JPATH_SITE
                    . '/components/com_sportsmanagement/extensions/'
                    . $extensionName
                    . '/admin';

                if (is_dir($basePath)) {
                    $language->load(
                        'com_sportsmanagement_' . $extensionName,
                        $basePath,
                        $tag,
                        true
                    );
                }
            }
        }
    }

    /**
     * Joomla 5 keeps a few legacy routing semantics around menu parsing which
     * are no longer relevant on Joomla 6. Use the compatibility wrapper there,
     * while Joomla 6 continues to use the normal RouterFactory service.
     */
    public function createRouter(CMSApplicationInterface $application, AbstractMenu $menu): RouterInterface
    {
        if (version_compare(JVERSION, '6.0.0', 'lt')) {
            $routerFile = JPATH_SITE . '/components/com_sportsmanagement/router.php';

            if (is_file($routerFile)) {
                require_once $routerFile;
            }

            if (class_exists('SportsmanagementRouter', false)) {
                return new \SportsmanagementRouter($application, $menu);
            }
        }

        return $this->createFactoryRouter($application, $menu);
    }
}
