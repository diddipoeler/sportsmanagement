<?php
namespace Diddipoeler\Module\SportsManagementClubBirthday\Site\Helper;

\defined('_JEXEC') or die;

use Joomla\Registry\Registry;

require_once dirname(__DIR__, 2) . '/helper.php';

final class ClubBirthdayHelper
{
    public function getData(Registry $params): array
    {
        return \modSportsmanagementClubBirthdayHelper::getData($params);
    }
}
