<?php
/**
 * @package     SportsManagement
 * @subpackage  com_sportsmanagement
 *
 * Joomla 5/6 migration scaffold.
 */

namespace Diddipoeler\Component\SportsManagement\Administrator\Extension;

defined('_JEXEC') or die;

use Joomla\CMS\Application\CMSApplicationInterface;
use Joomla\CMS\Component\Router\RouterInterface;
use Joomla\CMS\Component\Router\RouterServiceInterface;
use Joomla\CMS\Component\Router\RouterServiceTrait;
use Joomla\CMS\Extension\MVCComponent;
use Joomla\CMS\HTML\HTMLRegistryAwareTrait;
use Joomla\CMS\Menu\AbstractMenu;

/**
 * Component extension class for the Joomla 5/6 architecture.
 *
 * This class is introduced before the modern dispatcher is activated so that
 * the existing SportsManagement entry points can continue to run while the MVC
 * classes are migrated incrementally.
 */
final class SportsManagementComponent extends MVCComponent implements RouterServiceInterface
{
    use HTMLRegistryAwareTrait;
    use RouterServiceTrait {
        createRouter as private createFactoryRouter;
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
