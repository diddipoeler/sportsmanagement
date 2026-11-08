<?php
/**
 * SportsManagement legacy comments compatibility bridge for Joomla 5/6.
 *
 * @version    5.6.0
 * @author     diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
 * @copyright  Copyright: © 2013-2023 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
\defined('_JEXEC') or die;

use Diddipoeler\Component\SportsManagement\Site\Helper\MatchCommentsHelper;
use Diddipoeler\Component\SportsManagement\Site\Service\SportsManagementDatabaseResolver;
use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\Factory;
use Joomla\Database\DatabaseInterface;
use Joomla\Database\ParameterType;

foreach ([
    MatchCommentsHelper::class => JPATH_SITE . '/components/com_sportsmanagement/src/Helper/MatchCommentsHelper.php',
    SportsManagementDatabaseResolver::class => JPATH_SITE . '/components/com_sportsmanagement/src/Service/SportsManagementDatabaseResolver.php',
] as $nativeClass => $nativeFile) {
    if (!class_exists($nativeClass, false) && is_file($nativeFile)) {
        require_once $nativeFile;
    }
}

if (!class_exists(MatchCommentsHelper::class) || !class_exists(SportsManagementDatabaseResolver::class)) {
    throw new \RuntimeException('SportsManagement native MatchComments helper could not be loaded.', 500);
}

/**
 * Historical comments facade kept for legacy templates and Kunena integrations.
 *
 * The rendering implementation now lives in MatchCommentsHelper.
 */
class sportsmanagementModelComments
{
    protected array $config = [];

    /**
     * Initialise the legacy comment facade with configuration supplied by a site view.
     * Stores a defensive array copy so later calls can choose the active integration without depending on external array mutations.
     *
     * @param array $config Comment-system configuration passed by reference.
     * @return void
     */
    public function __construct(&$config = [])
    {
        $this->config = (array) $config;
    }

    /**
     * Choose the appropriate comment integration for the current Joomla installation.
     * Prefers the project's Kunena link when enabled, otherwise JComments when available, and falls back to the no-op facade.
     *
     * @param array $config Comment and project integration settings.
     * @return sportsmanagementModelComments Compatible comments facade.
     */
    public static function CreateInstance(&$config)
    {
        if (!empty($config['show_project_kunena_link']) && ComponentHelper::isEnabled('com_kunena')) {
            return new sportsmanagementModelCommentsKunena($config);
        }

        if (ComponentHelper::isEnabled('com_jcomments')) {
            return new sportsmanagementModelCommentsJSMJComments($config);
        }

        return new sportsmanagementModelComments($config);
    }

    /**
     * Build a human-readable forum subject for a specific match ID.
     *
     * Queries the home and away team names using a bound integer ID and falls back
     * to the historical placeholder if the match is missing or the query fails.
     *
     * @param int|string $match_id Identifier of the match used for the Kunena subject.
     * @return string Team pairing or a default subject when no names are available.
     */
    public static function getForumSubjectFromMatchID($match_id)
    {
        $fallback = 'Bitte Spielpaarung hier eingeben!';
        $matchId = max(0, (int) $match_id);
        if ($matchId <= 0) {
            return $fallback;
        }

        $db = self::database();
        $query = $db->createQuery()
            ->select([
                $db->quoteName('t1.name', 'home'),
                $db->quoteName('t2.name', 'away'),
            ])
            ->from($db->quoteName('#__sportsmanagement_match', 'm'))
            ->join(
                'LEFT',
                $db->quoteName('#__sportsmanagement_project_team', 'pt1')
                . ' ON ' . $db->quoteName('pt1.id') . ' = ' . $db->quoteName('m.projectteam1_id')
            )
            ->join(
                'LEFT',
                $db->quoteName('#__sportsmanagement_season_team_id', 'st1')
                . ' ON ' . $db->quoteName('st1.id') . ' = ' . $db->quoteName('pt1.team_id')
            )
            ->join(
                'LEFT',
                $db->quoteName('#__sportsmanagement_team', 't1')
                . ' ON ' . $db->quoteName('t1.id') . ' = ' . $db->quoteName('st1.team_id')
            )
            ->join(
                'LEFT',
                $db->quoteName('#__sportsmanagement_project_team', 'pt2')
                . ' ON ' . $db->quoteName('pt2.id') . ' = ' . $db->quoteName('m.projectteam2_id')
            )
            ->join(
                'LEFT',
                $db->quoteName('#__sportsmanagement_season_team_id', 'st2')
                . ' ON ' . $db->quoteName('st2.id') . ' = ' . $db->quoteName('pt2.team_id')
            )
            ->join(
                'LEFT',
                $db->quoteName('#__sportsmanagement_team', 't2')
                . ' ON ' . $db->quoteName('t2.id') . ' = ' . $db->quoteName('st2.team_id')
            )
            ->where($db->quoteName('m.id') . ' = :commentsMatchId')
            ->bind(':commentsMatchId', $matchId, ParameterType::INTEGER);

        try {
            $db->setQuery($query, 0, 1);
            $teams = $db->loadObject();
        } catch (Throwable) {
            return $fallback;
        }

        if (!$teams) {
            return $fallback;
        }

        $subject = trim((string) ($teams->home ?? '') . ' - ' . (string) ($teams->away ?? ''));

        return $subject !== '-' ? $subject : $fallback;
    }

    /**
     * Resolve the SportsManagement database selected by the component.
     * Delegates database selection to the native Joomla 5/6 resolver while preserving the legacy helper API.
     *
     * @return DatabaseInterface Configured SportsManagement database driver.
     */
    private static function database(): DatabaseInterface
    {
        return SportsManagementDatabaseResolver::resolve(self::joomlaDatabase());
    }

    /**
     * Retrieve and validate the Joomla database connection from the service container.
     * Supplies the typed database service to the component resolver, reporting an explicit error if it is unavailable.
     *
     * @return DatabaseInterface Joomla database driver.
     */
    private static function joomlaDatabase(): DatabaseInterface
    {
        $database = Factory::getContainer()->get(DatabaseInterface::class);

        if (!$database instanceof DatabaseInterface) {
            throw new \RuntimeException('SportsManagement Comments database service is unavailable.', 500);
        }

        return $database;
    }

    /**
     * Report whether the base comments integration is available.
     * The base fallback has no external comment system, so it always returns false and hides integration controls.
     *
     * @return bool False for the built-in no-op implementation.
     */
    public function isEnabled()
    {
        return false;
    }

    /**
     * Render the comment access icon for a match through the native helper.
     * Preserves the legacy view parameters and ensures non-object project data becomes null before rendering.
     *
     * @param mixed $match Current match.
     * @param mixed $hometeam Home team.
     * @param mixed $guestteam Away team.
     * @param array $config Comment display settings.
     * @param mixed $project Optional current project.
     * @return mixed Rendered comment link or icon output.
     */
    public function showMatchCommentIcon(&$match, &$hometeam, &$guestteam, &$config, &$project)
    {
        return MatchCommentsHelper::render(
            $match,
            $hometeam,
            $guestteam,
            (array) $config,
            is_object($project) ? $project : null
        );
    }

    /**
     * Render the match comments using the compatibility icon entry point.
     * Keeps the older method name functional for site templates while reusing native rendering and filtering.
     *
     * @param mixed $match Current match.
     * @param mixed $hometeam Home team.
     * @param mixed $guestteam Away team.
     * @param array $config Comment display settings.
     * @param mixed $project Optional project.
     * @return mixed Rendered comment output.
     */
    public function showMatchComments(&$match, &$hometeam, &$guestteam, &$config, &$project)
    {
        return $this->showMatchCommentIcon($match, $hometeam, $guestteam, $config, $project);
    }
}

/** @deprecated Use MatchCommentsHelper directly in namespaced code. */
class sportsmanagementModelCommentsKunena extends sportsmanagementModelComments
{
    /**
     * Check whether Kunena is installed and enabled.
     * Used by old Kunena template overrides to decide whether to offer forum comment links.
     *
     * @return bool True when the Kunena component is enabled.
     */
    public function isEnabled()
    {
        return ComponentHelper::isEnabled('com_kunena');
    }
}

/** @deprecated Use MatchCommentsHelper directly in namespaced code. */
class sportsmanagementModelCommentsJSMJComments extends sportsmanagementModelComments
{
    /**
     * Check whether the JComments integration is enabled.
     * Allows existing site view overrides to suppress comments controls when JComments is not available.
     *
     * @return bool True when the JComments component is enabled.
     */
    public function isEnabled()
    {
        return ComponentHelper::isEnabled('com_jcomments');
    }
}
