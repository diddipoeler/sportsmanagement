<?php
namespace Diddipoeler\Component\SportsManagement\Site\Model;

\defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\MVC\Factory\MVCFactoryInterface;

final class PlaygroundModel extends SportsManagementProjectModel
{
    /**
     * Legacy public static state retained for existing views/extensions.
     */
    public static int $playgroundid = 0;
    public static int $projectid = 0;
    public static int $cfg_which_database = 0;

    /**
     * Legacy public property retained for compatibility.
     */
    public $playground = null;

    public function __construct($config = [], ?MVCFactoryInterface $factory = null)
    {
        parent::__construct($config, $factory);

        $input = Factory::getApplication()->getInput();
        self::$projectid = $this->projectId;
        self::$playgroundid = $input->getInt('pgid', 0);
        self::$cfg_which_database = $input->getInt('cfg_which_database', 0);

        if (class_exists('sportsmanagementModelProject')) {
            \sportsmanagementModelProject::$projectid = self::$projectid;
        }
    }
}
