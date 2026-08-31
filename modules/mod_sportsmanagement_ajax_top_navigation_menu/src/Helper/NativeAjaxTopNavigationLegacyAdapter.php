<?php
namespace Diddipoeler\Module\SportsManagementAjaxTopNavigationMenu\Site\Helper;

\defined('_JEXEC') or die;

use Joomla\CMS\Application\CMSApplicationInterface;
use Joomla\Database\DatabaseInterface;
use Joomla\Registry\Registry;

/**
 * Bridge the native Joomla 5/6 module dispatcher to the remaining legacy
 * query/link implementation while keeping application and database injection.
 */
final class NativeAjaxTopNavigationLegacyAdapter extends \modSportsmanagementAjaxTopNavigationMenuHelper
{
    public function __construct(
        Registry $params,
        CMSApplicationInterface $app,
        DatabaseInterface $database
    ) {
        parent::__construct($params, $app, $database);
    }
}
