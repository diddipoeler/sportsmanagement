<?php
namespace Diddipoeler\Component\SportsManagement\Site\Model;

\defined('_JEXEC') or die;

use Diddipoeler\Component\SportsManagement\Site\Service\SportsManagementDatabaseResolver;
use Joomla\CMS\Factory;
use Joomla\CMS\MVC\Factory\MVCFactoryInterface;
use Joomla\Database\DatabaseInterface;

if (!class_exists('sportsmanagementModeltournamentbracket', false)) {
    require_once JPATH_SITE . '/components/com_sportsmanagement/models/tournamentbracket.php';
}

/**
 * Joomla 5/6 adapter for the tournament-bracket calculation.
 *
 * The historic bracket-building algorithm is intentionally retained for now,
 * while all database access is rebound to the native SportsManagement resolver.
 */
final class TournamentbracketModel extends \sportsmanagementModeltournamentbracket
{
    public function __construct($config = [], ?MVCFactoryInterface $factory = null)
    {
        parent::__construct($config, $factory);

        /** @var DatabaseInterface $joomlaDatabase */
        $joomlaDatabase = Factory::getContainer()->get(DatabaseInterface::class);
        $selector = Factory::getApplication()->getInput()->getInt('cfg_which_database', 0) === 1 ? 1 : 0;

        $this->setDatabase(SportsManagementDatabaseResolver::resolve($joomlaDatabase, $selector));
    }

    /**
     * Compatibility for the legacy algorithm on Joomla 6 where getDbo() is no
     * longer part of the model API.
     */
    public function getDbo(): DatabaseInterface
    {
        return $this->getDatabase();
    }
}
