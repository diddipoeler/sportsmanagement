<?php
/**
 * Legacy helper adapter for callers that still instantiate modJSMClubiconsHelper.
 * The active Joomla 5/6 implementation lives in src/Helper/ClubiconsHelper.php.
 */
\defined('_JEXEC') or die;

use Diddipoeler\Module\SportsManagementClubicons\Site\Helper\ClubiconsHelper;
use Joomla\CMS\Application\SiteApplication;
use Joomla\CMS\Factory;

if (!class_exists(ClubiconsHelper::class)) {
    require_once __DIR__ . '/src/Helper/ClubiconsHelper.php';
}

class modJSMClubiconsHelper
{
    public ?object $project = null;
    public array $ranking = [];
    public array $teams = [];

    public function __construct($params, $module)
    {
        $container = Factory::getContainer();
        /** @var SiteApplication $app */
        $app = $container->get(SiteApplication::class);
        $result = (new ClubiconsHelper())->getData($params, $module, $app);
        $this->project = $result['project'];
        $this->ranking = $result['ranking'];
        $this->teams = $result['teams'];
    }
}
