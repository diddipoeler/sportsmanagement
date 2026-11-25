<?php
/**
 * SportsManagement Joomla 5/6 file metadata.
 *
 * @version    5.6.0
 * @author     diddipoeler
 * @copyright  Copyright (C) diddipoeler
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Diddipoeler\Component\SportsManagement\Site\Legacy;

\defined('_JEXEC') or die;

use Diddipoeler\Component\SportsManagement\Site\Helper\SiteRouteHelper;
use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\Factory;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Log\Log;
use Joomla\CMS\Plugin\PluginHelper;
use Joomla\CMS\Router\Route;
use Joomla\CMS\Uri\Uri;
use Joomla\Database\DatabaseInterface;
use Joomla\Registry\Registry;

/**
 * Narrow comments compatibility facade for the historical teamplan template.
 *
 * The template only needs CreateInstance() and showMatchCommentIcon(). Keep
 * that surface while using Joomla 5/6 APIs and the native component route
 * helper instead of loading site/helpers/comments.php.
 */
final class TeamplanCommentsFacade
{
    private const MODE_NONE = 'none';
    private const MODE_KUNENA = 'kunena';
    private const MODE_JCOMMENTS = 'jcomments';

    private string $mode = self::MODE_NONE;
    private int $kunenaItemId = 0;
    private bool $separateComments = false;
    private bool $jcommentsAvailable = false;

    /**
     * Select the installed comment integration for the team plan. Prefers Kunena when configured, otherwise initialises JComments or logs the absence of a supported component.
     * @param array $config Project comment display settings.
     */
    private function __construct(array $config)
    {
        if (!empty($config['show_project_kunena_link']) && ComponentHelper::isEnabled('com_kunena')) {
            $this->mode = self::MODE_KUNENA;
            $this->kunenaItemId = $this->findKunenaItemId();
            return;
        }

        if (ComponentHelper::isEnabled('com_jcomments')) {
            $this->mode = self::MODE_JCOMMENTS;
            $this->initialiseJComments();
            return;
        }

        Log::add(Text::_('Es ist keine Kommentarkomponente installiert'));
    }

    /**
     * Create a comment facade through the historical template factory interface. Converts the supplied configuration to an array before selecting the integration.
     * @param mixed $config Legacy project configuration passed by reference.
     * @return self Configured comment facade.
     */
    public static function CreateInstance(&$config): self
    {
        return new self((array) $config);
    }

    /**
     * Determine whether the selected comment integration is available. Kunena is enabled by selection; JComments additionally requires its model to load.
     * @return bool Whether comments can be displayed.
     */
    public function isEnabled(): bool
    {
        return match ($this->mode) {
            self::MODE_KUNENA => true,
            self::MODE_JCOMMENTS => $this->jcommentsAvailable,
            default => false,
        };
    }

    /**
     * Dispatch match comment rendering to the active integration or unavailable fallback.
     * @param object $match Match record.
     * @param object $hometeam Home team record.
     * @param object $guestteam Away team record.
     * @param array $config Comment display options.
     * @param object $project Project routing and forum settings.
     * @return string Comment link markup.
     */
    public function showMatchCommentIcon(&$match, &$hometeam, &$guestteam, &$config, &$project): string
    {
        return match ($this->mode) {
            self::MODE_KUNENA => $this->showKunenaCommentIcon($match, $hometeam, $guestteam, $config, $project),
            self::MODE_JCOMMENTS => $this->showJCommentsIcon($match, $config, $project),
            default => $this->showUnavailableIcon($match, $config, $project),
        };
    }

    /**
     * Look up the forum topic matching the home and away team names in the project category. Link to the existing topic or its creation form and show the recorded post count.
     * @param object $match Match record.
     * @param object $hometeam Home team.
     * @param object $guestteam Away team.
     * @param array $config Comment display options.
     * @param object $project Project forum category.
     * @return string Forum link markup.
     */
    private function showKunenaCommentIcon(object $match, object $hometeam, object $guestteam, array $config, object $project): string
    {
        $matchHome = preg_replace('|<[^>]*>|', '', (string) ($hometeam->name ?? '')) ?? '';
        $matchAway = preg_replace('|<[^>]*>|', '', (string) ($guestteam->name ?? '')) ?? '';
        $categoryId = (int) ($project->sb_catid ?? 0);
        $db = Factory::getContainer()->get(DatabaseInterface::class);
        $query = $db->getQuery(true)
            ->select([$db->quoteName('id'), $db->quoteName('posts')])
            ->from($db->quoteName('#__kunena_topics'))
            ->where($db->quoteName('category_id') . ' = ' . $categoryId)
            ->where($db->quoteName('subject') . ' = ' . $db->quote(sprintf('%s - %s', $matchHome, $matchAway)));
        $db->setQuery($query, 0, 1);
        $topic = $db->loadAssoc();
        $count = is_array($topic) ? (int) ($topic['posts'] ?? 0) : 0;
        $hrefText = $this->getHrefText($count, $config);

        if (!is_array($topic)) {
            $link = Route::_(
                'index.php?option=com_kunena&view=topic&catid=' . $categoryId
                . '&Itemid=' . $this->kunenaItemId
                . '&layout=create&CommentMatchID=' . (int) ($match->id ?? 0)
            );
        } else {
            $link = Route::_(
                'index.php?option=com_kunena&view=topic&catid=' . $categoryId
                . '&Itemid=' . $this->kunenaItemId
                . '&id=' . (int) ($topic['id'] ?? 0)
            );
        }

        return HTMLHelper::link($link, $hrefText);
    }

    /**
     * Count published comments for the match using the configured shared or separate object group. Falls back to the unavailable icon if the JComments model cannot be used.
     * @param object $match Match record.
     * @param array $config Comment display options.
     * @param object $project Project route context.
     * @return string Match comment link markup.
     */
    private function showJCommentsIcon(object $match, array $config, object $project): string
    {
        if (!$this->jcommentsAvailable || !class_exists('JCommentsModel')) {
            return $this->showUnavailableIcon($match, $config, $project);
        }

        if ($this->separateComments) {
            $objectGroup = isset($match->team1_result)
                ? 'com_sportsmanagement_matchreport'
                : 'com_sportsmanagement_nextmatch';
        } else {
            $objectGroup = 'com_sportsmanagement';
        }

        $count = (int) \JCommentsModel::getCommentsCount([
            'object_id' => (int) ($match->id ?? 0),
            'object_group' => $objectGroup,
            'published' => 1,
        ]);

        return HTMLHelper::link(
            $this->getSportsManagementMatchLink($match, $project),
            $this->getHrefText($count, $config)
        );
    }

    /**
     * Render the zero-count label or inactive discussion image linking to the match when no comment integration is available.
     * @param object $match Match record.
     * @param array $config Comment display options.
     * @param object $project Project route context.
     * @return string Fallback link markup.
     */
    private function showUnavailableIcon(object $match, array $config, object $project): string
    {
        $title = 'Comments not available';
        $hrefText = ((int) ($config['show_comments_count'] ?? 0) === 2)
            ? '<span title="' . htmlspecialchars($title, ENT_QUOTES, 'UTF-8') . '">(0)</span>'
            : HTMLHelper::image(
                Uri::root() . 'media/com_sportsmanagement/jl_images/discuss.gif',
                $title,
                ['title' => $title, 'border' => 0, 'style' => 'vertical-align: middle']
            );

        return HTMLHelper::link($this->getSportsManagementMatchLink($match, $project), $hrefText);
    }

    /**
     * Build the component match report or next-match route according to whether a result exists, preserving request database and season context.
     * @param object $match Match record.
     * @param object $project Project containing the route slug.
     * @return string Routed match URL.
     */
    private function getSportsManagementMatchLink(object $match, object $project): string
    {
        $input = Factory::getApplication()->getInput();
        $parameters = [
            'cfg_which_database' => $input->getInt('cfg_which_database', 0),
            's' => $input->getInt('s', 0),
            'p' => (string) ($project->slug ?? ''),
            'mid' => (int) ($match->id ?? 0),
        ];

        return SiteRouteHelper::view(isset($match->team1_result) ? 'matchreport' : 'nextmatch', $parameters);
    }

    /**
     * Build a translated singular, plural or no-comment tooltip and render either a count or discussion icon according to the template setting.
     * @param int $count Number of published comments or forum posts.
     * @param array $config Comment count display settings.
     * @return string Count or image markup.
     */
    private function getHrefText(int $count, array $config): string
    {
        if ($count === 1) {
            $title = $count . ' ' . Text::_('COM_SPORTSMANAGEMENT_TEAMPLAN_COMMENTS_COUNT_SINGULAR');
        } elseif ($count > 1) {
            $title = $count . ' ' . Text::_('COM_SPORTSMANAGEMENT_TEAMPLAN_COMMENTS_COUNT_PLURAL');
        } else {
            $title = Text::_('COM_SPORTSMANAGEMENT_TEAMPLAN_COMMENTS_COUNT_NOCOMMENT');
        }

        if ((int) ($config['show_comments_count'] ?? 0) === 2) {
            return '<span title="' . htmlspecialchars($title, ENT_QUOTES, 'UTF-8') . '">(' . $count . ')</span>';
        }

        $image = $count > 0 ? 'discuss_active.gif' : 'discuss.gif';

        return HTMLHelper::image(
            Uri::root() . 'media/com_sportsmanagement/jl_images/' . $image,
            $title,
            ['title' => $title, 'border' => 0, 'style' => 'vertical-align: middle']
        );
    }

    /**
     * Find the first menu entry whose link targets the Kunena home view on the Joomla database.
     * @return int Menu identifier or zero if absent.
     */
    private function findKunenaItemId(): int
    {
        $db = Factory::getContainer()->get(DatabaseInterface::class);
        $query = $db->getQuery(true)
            ->select($db->quoteName('id'))
            ->from($db->quoteName('#__menu'))
            ->where($db->quoteName('link') . ' LIKE ' . $db->quote('index.php?option=com_kunena&view=home%'));
        $db->setQuery($query, 0, 1);

        return (int) $db->loadResult();
    }

    /**
     * Load the optional JComments configuration and model files when present, then read the SportsManagement content plugin setting for separate match comment groups.
     * @return void
     */
    private function initialiseJComments(): void
    {
        $configFile = JPATH_ROOT . '/components/com_jcomments/classes/config.php';
        $classFile = JPATH_ROOT . '/components/com_jcomments/jcomments.class.php';
        $modelFile = JPATH_ROOT . '/components/com_jcomments/models/jcomments.php';

        if (!is_file($configFile) || !is_file($classFile) || !is_file($modelFile)) {
            $this->jcommentsAvailable = false;
            return;
        }

        require_once $configFile;
        require_once $classFile;
        require_once $modelFile;
        $this->jcommentsAvailable = class_exists('JCommentsModel');

        PluginHelper::importPlugin('content', 'sportsmanagement_comments');
        $plugin = PluginHelper::getPlugin('content', 'sportsmanagement_comments');
        $params = new Registry(is_object($plugin) ? (string) ($plugin->params ?? '') : '');
        $this->separateComments = (bool) $params->get('separate_comments', 0);
    }
}
