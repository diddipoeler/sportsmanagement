<?php
namespace Diddipoeler\Component\SportsManagement\Site\Helper;

\defined('_JEXEC') or die;

use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\Factory;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Plugin\PluginHelper;
use Joomla\CMS\Router\Route;
use Joomla\CMS\Uri\Uri;
use Joomla\Database\DatabaseInterface;
use Joomla\Registry\Registry;

/**
 * Joomla 5/6 adapter for optional Kunena/JComments match comments.
 */
final class MatchCommentsHelper
{
    private static ?bool $kunenaEnabled = null;
    private static ?bool $jcommentsEnabled = null;
    private static bool $kunenaItemResolved = false;
    private static int $kunenaItemId = 0;
    private static array $kunenaTopics = [];
    private static bool $jcommentsPrepared = false;
    private static bool $jcommentsAvailable = false;
    private static bool $separateComments = false;

    public static function render(object $match, object $homeTeam, object $awayTeam, array $config, ?object $project): string
    {
        if (self::commentsDisabled((string) ($match->preview ?? ''))) {
            return '';
        }

        self::$kunenaEnabled ??= ComponentHelper::isEnabled('com_kunena');
        if (!empty($config['show_project_kunena_link']) && self::$kunenaEnabled) {
            return self::renderKunena($match, $homeTeam, $awayTeam, $config, $project);
        }

        self::$jcommentsEnabled ??= ComponentHelper::isEnabled('com_jcomments');
        if (self::$jcommentsEnabled) {
            return self::renderJComments($match, $homeTeam, $awayTeam);
        }

        return Text::_('Comments not available');
    }

    private static function renderKunena(object $match, object $homeTeam, object $awayTeam, array $config, ?object $project): string
    {
        $categoryId = (int) ($project->sb_catid ?? 0);
        if ($categoryId <= 0) {
            return '';
        }

        $app = Factory::getApplication();
        /** @var DatabaseInterface $db */
        $db = $app->getContainer()->get(DatabaseInterface::class);
        $itemId = self::getKunenaItemId($db);
        $homeName = trim(strip_tags((string) ($homeTeam->name ?? '')));
        $awayName = trim(strip_tags((string) ($awayTeam->name ?? '')));
        $subject = trim($homeName . ' - ' . $awayName);
        $topicKey = $categoryId . ':' . $subject;

        if (!array_key_exists($topicKey, self::$kunenaTopics)) {
            $topicQuery = $db->getQuery(true)
                ->select([$db->quoteName('id'), $db->quoteName('posts')])
                ->from($db->quoteName('#__kunena_topics'))
                ->where($db->quoteName('category_id') . ' = ' . $categoryId)
                ->where($db->quoteName('subject') . ' = ' . $db->quote($subject));
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

    private static function renderJComments(object $match, object $homeTeam, object $awayTeam): string
    {
        if (!self::prepareJComments()) {
            return '';
        }

        $eventName = self::$separateComments ? 'onMatchReportComments' : 'onMatchComments';
        $comments = [];
        $title = trim((string) ($homeTeam->name ?? '') . ' - ' . (string) ($awayTeam->name ?? ''));
        $results = Factory::getApplication()->triggerEvent($eventName, [$match, $title, &$comments]);

        $output = [];
        foreach (array_merge((array) $comments, (array) $results) as $value) {
            if (is_string($value) && trim($value) !== '') {
                $output[] = $value;
            }
        }

        return implode('', array_unique($output));
    }

    private static function getKunenaItemId(DatabaseInterface $db): int
    {
        if (self::$kunenaItemResolved) {
            return self::$kunenaItemId;
        }

        self::$kunenaItemResolved = true;
        $menuQuery = $db->getQuery(true)
            ->select($db->quoteName('id'))
            ->from($db->quoteName('#__menu'))
            ->where($db->quoteName('link') . ' LIKE ' . $db->quote('index.php?option=com_kunena&view=home%'))
            ->order($db->quoteName('id') . ' ASC');
        $db->setQuery($menuQuery, 0, 1);
        self::$kunenaItemId = (int) $db->loadResult();

        return self::$kunenaItemId;
    }

    private static function prepareJComments(): bool
    {
        if (self::$jcommentsPrepared) {
            return self::$jcommentsAvailable;
        }

        self::$jcommentsPrepared = true;
        $root = JPATH_ROOT . '/components/com_jcomments';
        if (!is_file($root . '/jcomments.class.php')) {
            return false;
        }

        PluginHelper::importPlugin('content', 'sportsmanagement_comments');
        $plugin = PluginHelper::getPlugin('content', 'sportsmanagement_comments');
        $params = new Registry(is_object($plugin) ? (string) ($plugin->params ?? '') : '');
        self::$separateComments = (bool) $params->get('separate_comments', 0);
        self::$jcommentsAvailable = true;

        return true;
    }

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

    private static function commentsDisabled(string $preview): bool
    {
        return $preview !== '' && preg_match('/{jcomments\\s+(off|lock)}/is', $preview) === 1;
    }
}
