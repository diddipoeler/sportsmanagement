<?php
namespace Diddipoeler\Module\SportsManagementBirthday\Site\Helper;

\defined('_JEXEC') or die;

use Joomla\CMS\Application\CMSApplicationInterface;
use Joomla\Registry\Registry;

require_once dirname(__DIR__, 2) . '/helper.php';

final class BirthdayHelper
{
    public function getData(Registry $params, Registry $componentParams, CMSApplicationInterface $app): array
    {
        return (new \modSportsmanagementBirthdayDataHelper())->getData($params, $componentParams, $app);
    }
}
