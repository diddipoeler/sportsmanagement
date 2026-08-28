<?php
/**
 * SportsManagement legacy comments compatibility bridge for Joomla 5/6.
 */
defined('_JEXEC') or die('Restricted access');

use Diddipoeler\Component\SportsManagement\Site\Helper\MatchCommentsHelper;
use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\Database\DatabaseInterface;

/**
 * Historical comments facade kept for legacy templates and Kunena integrations.
 *
 * The rendering implementation now lives in MatchCommentsHelper.
 */
class sportsmanagementModelComments
{
    protected array $config = [];

    public function __construct(&$config = [])
    {
        $this->config = (array) $config;
    }

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
     * Kept public because existing Kunena customisations call this method.
     */
    public static function getForumSubjectFromMatchID($match_id)
    {
        $matchId = max(0, (int) $match_id);
        if ($matchId <= 0) {
            return Text::_('COM_SPORTSMANAGEMENT_MATCHREPORT_UNKNOWN');
        }

        /** @var DatabaseInterface $db */
        $db = Factory::getContainer()->get(DatabaseInterface::class);
        $query = $db->getQuery(true)
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
            ->where($db->quoteName('m.id') . ' = ' . $matchId);

        try {
            $db->setQuery($query, 0, 1);
            $teams = $db->loadObject();
        } catch (Throwable) {
            return Text::_('COM_SPORTSMANAGEMENT_MATCHREPORT_UNKNOWN');
        }

        if (!$teams) {
            return Text::_('COM_SPORTSMANAGEMENT_MATCHREPORT_UNKNOWN');
        }

        return trim((string) ($teams->home ?? '') . ' - ' . (string) ($teams->away ?? ''));
    }

    public function isEnabled()
    {
        return false;
    }

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

    public function showMatchComments(&$match, &$hometeam, &$guestteam, &$config, &$project)
    {
        return $this->showMatchCommentIcon($match, $hometeam, $guestteam, $config, $project);
    }
}

/** @deprecated Use MatchCommentsHelper directly in namespaced code. */
class sportsmanagementModelCommentsKunena extends sportsmanagementModelComments
{
    public function isEnabled()
    {
        return ComponentHelper::isEnabled('com_kunena');
    }
}

/** @deprecated Use MatchCommentsHelper directly in namespaced code. */
class sportsmanagementModelCommentsJSMJComments extends sportsmanagementModelComments
{
    public function isEnabled()
    {
        return ComponentHelper::isEnabled('com_jcomments');
    }
}
