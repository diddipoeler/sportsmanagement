<?php
/**
 * Native Joomla 5/6 adapter for optional Kunena/JComments match comments.
 *
 * @version    5.6.0
 * @author     diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
 * @copyright  Copyright: © 2013-2023 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
namespace Diddipoeler\Component\SportsManagement\Site\Helper;

\defined('_JEXEC') or die;

use Joomla\CMS\Application\SiteApplication;
use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\Factory;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Log\Log;
use Joomla\CMS\Plugin\PluginHelper;
use Joomla\CMS\Router\Route;
use Joomla\CMS\Uri\Uri;
use Joomla\Database\DatabaseInterface;
use Joomla\Database\ParameterType;
use Joomla\Registry\Registry;

/**
 * Joomla 5/6 adapter for optional Kunena/JComments match comments.
 */
final class MatchCommentsHelper
{
    /**
     * Resolve the Joomla site application for comment plugin events.
     * Uses the public Joomla service container and verifies the returned
     * application type before invoking optional content plugin events.
     * @return SiteApplication Validated Joomla site application.
     * @throws \RuntimeException When the service is unavailable.
     */
    private static function siteApplication(): SiteApplication
    {
        $application = Factory::getContainer()->get(SiteApplication::class);

        if (!$application instanceof SiteApplication) {
            throw new \RuntimeException('SportsManagement comments site application is unavailable.', 500);
        }

        return $application;
    }

    /**
     * Resolve Joomla's database for optional comments integrations.
     * The Kunena tables belong to the Joomla installation, not to a
     * separately selected SportsManagement external database.
     * @return DatabaseInterface Validated Joomla database service.
     * @throws \RuntimeException When no database service is available.
     */
    private static function joomlaDatabase(): DatabaseInterface
    {
        $database = Factory::getContainer()->get(DatabaseInterface::class);

        if (!$database instanceof DatabaseInterface) {
            throw new \RuntimeException('SportsManagement comments database service is unavailable.', 500);
        }

        return $database;
    }

    private static ?bool $kunenaEnabled = null;
    private static ?bool $jcommentsEnabled = null;
    private static bool $kunenaItemResolved = false;
    private static int $kunenaItemId = 0;
    private static array $kunenaTopics = [];
    private static bool $jcommentsPrepared = false;
    private static bool $jcommentsAvailable = false;
    private static bool $separateComments = false;

    /**
     * Render enabled Kunena or JComments content for a match.
     * Falls back to JComments when the optional Kunena integration fails.
     * Optional extension errors are logged by exception class only, so SQL,
     * credentials and local paths cannot leak into shared Joomla diagnostics.
     * @param object $match Current match record.
     * @param object $homeTeam Home team record.
     * @param object $awayTeam Away team record.
     * @param array $config Match comments presentation options.
     * @param object|null $project Optional project configuration.
     * @return string Comment markup or an empty string.
     */
    public static function render(object $match, object $homeTeam, object $awayTeam, array $config, ?object $project): string
    {
        $preview = $match->preview ?? '';

        if (self::commentsDisabled(is_string($preview) ? $preview : '')) {
            return '';
        }

        self::$kunenaEnabled ??= ComponentHelper::isEnabled('com_kunena');
        if (!empty($config['show_project_kunena_link']) && self::$kunenaEnabled) {
            try {
                return self::renderKunena($match, $homeTeam, $awayTeam, $config, $project);
            } catch (\Throwable $e) {
                // Kunena is optional. A missing table or incompatible plugin
                // must not break the match page; try JComments if available.
                Log::add(__METHOD__ . ': Kunena integration failed (' . get_class($e) . ').', Log::ERROR, 'com_sportsmanagement');
            }
        }

        self::$jcommentsEnabled ??= ComponentHelper::isEnabled('com_jcomments');
        if (self::$jcommentsEnabled) {
            try {
                return self::renderJComments($match, $homeTeam, $awayTeam);
            } catch (\Throwable $e) {
                Log::add(__METHOD__ . ': JComments integration failed (' . get_class($e) . ').', Log::ERROR, 'com_sportsmanagement');
            }
        }

        return Text::_('Comments not available');
    }

    /**
     * Build a Kunena topic link and count for the current match.
     * @param object $match Current match.
     * @param object $homeTeam Home team record.
     * @param object $awayTeam Away team record.
     * @param array $config Comments display configuration.
     * @param object|null $project Kunena category settings.
     * @return string Kunena topic/comment link markup.
     */
    private static function renderKunena(object $match, object $homeTeam, object $awayTeam, array $config, ?object $project): string
    {
        $categoryId = (int) ($project->sb_catid ?? 0);
        if ($categoryId <= 0) {
            return '';
        }

        $db = self::joomlaDatabase();
        $itemId = self::getKunenaItemId($db);
        $homeName = trim(strip_tags((string) ($homeTeam->name ?? '')));
        $awayName = trim(strip_tags((string) ($awayTeam->name ?? '')));
        $subject = trim($homeName . ' - ' . $awayName);
        $topicKey = $categoryId . ':' . $subject;

        if (!array_key_exists($topicKey, self::$kunenaTopics)) {
            $topicQuery = $db->createQuery()
                ->select([$db->quoteName('id'), $db->quoteName('posts')])
                ->from($db->quoteName('#__kunena_topics'))
                ->where($db->quoteName('category_id') . ' = :categoryId')
                ->where($db->quoteName('subject') . ' = :subject')
                ->bind(':categoryId', $categoryId, ParameterType::INTEGER)
                ->bind(':subject', $subject, ParameterType::STRING);
            $db->setQuery($topicQuery, 0, 1);
            self::$kunenaTopics[$topicKey] = $db->loadObject() ?: null;
        }

        $topic = self::$kunenaTopics[$topicKey];
        $count = (int) ($topic->posts ?? 0);
        $label = self::commentCountMarkup($count, (int) ($config['show_comments_count'] ?? 2));

        if ($topic) {
            $url = 'index.php?option=com_kunena&view=topic&catid=' . $categoryId . '&Itemid=' . $itemId . '&id=' . (int) $topic->id;
        } else {
            $url = 'index.php?option=com_kunena&view=topic&catid=' . $categoryId . '&Itemid=' . $itemId . '&layout=create&CommentMatchID=' . (int) ($match->id ?? 0);
        }

        return HTMLHelper::link(Route::_($url), $label);
    }

    /**
     * Run the JComments plugin event for a match if it is available.
     * @param object $match Current match.
     * @param object $homeTeam Home team record.
     * @param object $awayTeam Away team record.
     * @return string Combined plugin-provided comments markup.
     */
    private static function renderJComments(object $match, object $homeTeam, object $awayTeam): string
    {
        if (!self::prepareJComments()) {
            return '';
        }

        $eventName = self::$separateComments ? 'onMatchReportComments' : 'onMatchComments';
        $comments = [];
        $title = trim((string) ($homeTeam->name ?? '') . ' - ' . (string) ($awayTeam->name ?? ''));
        $app = self::siteApplication();
        $results = $app->triggerEvent($eventName, [$match, $title, &$comments]);

        $output = [];
        foreach (array_merge((array) $comments, (array) $results) as $value) {
            if (is_string($value) && trim($value) !== '') {
                $output[] = $value;
            }
        }

        return implode('', array_unique($output));
    }

    /**
     * Resolve and cache the Kunena Joomla menu item identifier.
     * Cache the lookup only after a successful database read, including a
     * genuine missing menu item; a transient failure must not poison later
     * match comment rendering in the same request.
     * @param DatabaseInterface $db Joomla database connection.
     * @return int Kunena menu item ID or zero when unavailable.
     */
    private static function getKunenaItemId(DatabaseInterface $db): int
    {
        if (self::$kunenaItemResolved) {
            return self::$kunenaItemId;
        }

        $menuLink = 'index.php?option=com_kunena&view=home%';
        $menuQuery = $db->createQuery()
            ->select($db->quoteName('id'))
            ->from($db->quoteName('#__menu'))
            ->where($db->quoteName('link') . ' LIKE :menuLink')
            ->order($db->quoteName('id') . ' ASC')
            ->bind(':menuLink', $menuLink, ParameterType::STRING);
        $db->setQuery($menuQuery, 0, 1);
        self::$kunenaItemId = (int) $db->loadResult();
        self::$kunenaItemResolved = true;

        return self::$kunenaItemId;
    }

    /**
     * Prepare the optional SportsManagement JComments content plugin once.
     *
     * Checks that the installed JComments component supplies its expected
     * entry file, imports the content plugin through Joomla and reads the
     * separate-comments option to select the appropriate match event.
     * Reuses the preparation result during subsequent match renders in the
     * same request rather than importing the extension repeatedly. A failed
     * plugin import is not cached as a successful preparation attempt.
     *
     * @return bool Whether the comments integration can be used.
     */
    private static function prepareJComments(): bool
    {
        if (self::$jcommentsPrepared) {
            return self::$jcommentsAvailable;
        }

        $root = JPATH_ROOT . '/components/com_jcomments';
        if (!is_file($root . '/jcomments.class.php')) {
            self::$jcommentsPrepared = true;
            return false;
        }

        PluginHelper::importPlugin('content', 'sportsmanagement_comments');
        $plugin = PluginHelper::getPlugin('content', 'sportsmanagement_comments');
        $params = new Registry(is_object($plugin) ? (string) ($plugin->params ?? '') : '');
        self::$separateComments = (bool) $params->get('separate_comments', 0);
        self::$jcommentsAvailable = true;
        self::$jcommentsPrepared = true;

        return true;
    }

    /**
     * Render the match comment count as a label or icon.
     * @param int $count Number of match comments.
     * @param int $mode Comment count image or text display mode.
     * @return string Escaped HTML for the comment count.
     */
    private static function commentCountMarkup(int $count, int $mode): string
    {
        $label = match (true) {
            $count === 1 => $count . ' ' . Text::_('COM_SPORTSMANAGEMENT_TEAMPLAN_COMMENTS_COUNT_SINGULAR'),
            $count > 1 => $count . ' ' . Text::_('COM_SPORTSMANAGEMENT_TEAMPLAN_COMMENTS_COUNT_PLURAL'),
            default => Text::_('COM_SPORTSMANAGEMENT_TEAMPLAN_COMMENTS_COUNT_NOCOMMENT'),
        };

        if ($mode === 1) {
            $image = $count > 0 ? 'discuss_active.gif' : 'discuss.gif';
            return HTMLHelper::image(Uri::root() . 'media/com_sportsmanagement/jl_images/' . $image, $label, ['title' => $label, 'style' => 'vertical-align: middle']);
        }

        return '<span title="' . htmlspecialchars($label, ENT_QUOTES, 'UTF-8') . '">(' . $count . ')</span>';
    }

    /**
     * Detect a JComments disable or lock marker in match preview text.
     * @param string $preview Current match preview content.
     * @return bool True when comments are explicitly disabled.
     */
    private static function commentsDisabled(string $preview): bool
    {
        return $preview !== '' && preg_match('/{jcomments\s+(off|lock)}/is', $preview) === 1;
    }
}
