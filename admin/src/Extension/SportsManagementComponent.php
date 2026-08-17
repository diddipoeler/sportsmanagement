<?php
/**
 * @package     SportsManagement
 * @subpackage  com_sportsmanagement
 *
 * Joomla 5/6 migration scaffold.
 */

namespace Diddipoeler\Component\SportsManagement\Administrator\Extension;

defined('_JEXEC') or die;

use Joomla\CMS\Extension\MVCComponent;
use Joomla\CMS\HTML\HTMLRegistryAwareTrait;
use Joomla\CMS\MVC\Factory\MVCFactoryServiceInterface;

/**
 * Component extension class for the Joomla 5/6 architecture.
 *
 * This class is introduced before the modern dispatcher is activated so that
 * the existing SportsManagement entry points can continue to run while the MVC
 * classes are migrated incrementally.
 */
final class SportsManagementComponent extends MVCComponent implements MVCFactoryServiceInterface
{
    use HTMLRegistryAwareTrait;
}
