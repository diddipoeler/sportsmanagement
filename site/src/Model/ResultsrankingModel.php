<?php
namespace Diddipoeler\Component\SportsManagement\Site\Model;

\defined('_JEXEC') or die;

use Joomla\CMS\MVC\Factory\MVCFactoryInterface;

final class ResultsrankingModel extends SportsManagementProjectModel
{
    public static int $divisionid = 0;
    public static int $roundid = 0;
    public static int $projectid = 0;
    public static int $cfg_which_database = 0;
    public static int $show_ranking_reiter = 0;

    public function __construct($config = [], ?MVCFactoryInterface $factory = null)
    {
        parent::__construct($config, $factory);

        $input = $this->siteApplication()->getInput();
        self::$divisionid = $this->divisionId;
        self::$roundid = $input->getInt('r', 0);
        self::$projectid = $this->projectId;
        self::$cfg_which_database = $input->getInt('cfg_which_database', 0);
        self::$show_ranking_reiter = $input->getInt('show_ranking_reiter', 0);

        if (class_exists('sportsmanagementModelProject')) {
            \sportsmanagementModelProject::$projectid = self::$projectid;
            \sportsmanagementModelProject::$cfg_which_database = self::$cfg_which_database;
        }
    }
}
