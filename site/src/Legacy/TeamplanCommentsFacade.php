<?php
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

    public static function CreateInstance(&$config): self
    {
        return new self((array) $config);
    }

    public function isEnabled(): bool
    {
        return match ($this->mode) {
            self::MODE_KUNENA => true,
            self::MODE_JCOMMENTS => $this->jcommentsAvailable,
            default => false,
        };
    }

    public function showMatchCommentIcon(&$match, &$hometeam, &$guestteam, &$config, &$project): string
    {
        return match ($this->mode) {
            self::MODE_KUNENA => $this->showKunenaCommentIcon($match, $hometeam, $guestteam, $config, $project),
            self::MODE_JCOMMENTS => $this->showJCommentsIcon($match, $config, $project),
            default => $this->showUnavailableIcon($match, $config, $project),
        };
    }

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
