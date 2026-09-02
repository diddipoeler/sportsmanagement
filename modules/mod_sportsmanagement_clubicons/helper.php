<?php
/**
 * Legacy helper adapter for callers that still instantiate modJSMClubiconsHelper.
 * The active Joomla 5/6 implementation lives in src/Helper/ClubiconsHelper.php.
 *
 * @version    4.24.00
 * @author     diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
 * @copyright  Copyright: © 2013-2023 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
\defined('_JEXEC') or die;

use Diddipoeler\Module\SportsManagementClubicons\Site\Helper\ClubiconsHelper;
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
        $app = Factory::getApplication();
        $result = (new ClubiconsHelper())->getData($params, $module, $app);
        $this->project = $result['project'];
        $this->ranking = $result['ranking'];
        $this->teams = $result['teams'];
    }
}
