<?php
namespace Diddipoeler\Component\SportsManagement\Site\Helper;

\defined('_JEXEC') or die;

use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;

/**
 * Render Clubinfo fusion/history relations without legacy route or tree helpers.
 */
final class ClubHistoryPresentationHelper
{
    public static function renderPredecessorTree(
        array $relations,
        int $clubId,
        int $treeMode,
        int $databaseSelector
    ): string {
        $children = self::childrenBySuccessor($relations);

        return self::renderChildren($children, $clubId, $treeMode, $databaseSelector, []);
    }

    public static function renderSuccessorHistory(
        array $relations,
        int $clubId,
        ?object $successor,
        int $databaseSelector
    ): string {
        if (!$successor) {
            return '';
        }

        $link = self::clubLink($successor, $databaseSelector);
        $title = Text::_('COM_SPORTSMANAGEMENT_CLUBINFO_HISTORY_FROM');
        $predecessors = self::renderHistoryList(
            self::childrenBySuccessor($relations),
            $clubId,
            $databaseSelector,
            []
        );

        return '<ul><li>'
            . HTMLHelper::image(
                'media/com_sportsmanagement/jl_images/club_from.png',
                $title,
                ['title' => $title]
            )
            . '&nbsp;' . HTMLHelper::link($link, self::escape((string) ($successor->name ?? '')))
            . ($predecessors !== '' ? '<ul>' . $predecessors . '</ul>' : '')
            . '</li></ul>';
    }

    /** @return array<int, array<int, object>> */
    private static function childrenBySuccessor(array $relations): array
    {
        $children = [];
        foreach ($relations as $club) {
            if (!is_object($club)) {
                continue;
            }
            $successorId = (int) ($club->new_club_id ?? 0);
            $clubId = (int) ($club->id ?? 0);
            if ($successorId > 0 && $clubId > 0 && $successorId !== $clubId) {
                $children[$successorId][] = $club;
            }
        }

        foreach ($children as &$clubs) {
            usort(
                $clubs,
                static fn (object $a, object $b): int => strnatcasecmp(
                    (string) ($a->name ?? ''),
                    (string) ($b->name ?? '')
                )
            );
        }
        unset($clubs);

        return $children;
    }

    private static function renderChildren(
        array $children,
        int $parentId,
        int $treeMode,
        int $databaseSelector,
        array $seen
    ): string {
        if ($parentId <= 0 || isset($seen[$parentId]) || empty($children[$parentId])) {
            return '';
        }
        $seen[$parentId] = true;

        $output = '<ul>';
        foreach ($children[$parentId] as $club) {
            $id = (int) ($club->id ?? 0);
            if ($id <= 0 || isset($seen[$id])) {
                continue;
            }

            $name = trim((string) ($club->name ?? ''));
            $year = trim((string) ($club->founded_year ?? ''));
            $label = $year !== '' ? $name . ' (' . $year . ')' : $name;
            $toggle = $treeMode === 0 && !empty($children[$id])
                ? '<button type="button" class="btn btn-sm p-0 border-0 bg-transparent"'
                    . ' data-jsm-clubinfo-tree-toggle aria-expanded="true">'
                    . '<i class="icon-minus-sign" aria-hidden="true"></i>'
                    . HTMLHelper::image('media/com_sportsmanagement/jl_images/arrow_left.png', '')
                    . '<span class="visually-hidden">' . self::escape($name) . '</span>'
                    . '</button>'
                : '';
            $logo = trim((string) ($club->logo_big ?? ''));
            $image = $logo !== ''
                ? HTMLHelper::image($logo, $name, ['width' => 30]) . ' '
                : '';

            $output .= '<li>' . $toggle
                . '<span><a href="' . self::escape(self::clubLink($club, $databaseSelector)) . '">'
                . $image . self::escape($label)
                . '</a></span>'
                . self::renderChildren($children, $id, $treeMode, $databaseSelector, $seen)
                . '</li>';
        }

        return $output . '</ul>';
    }

    private static function renderHistoryList(
        array $children,
        int $parentId,
        int $databaseSelector,
        array $seen
    ): string {
        if ($parentId <= 0 || isset($seen[$parentId]) || empty($children[$parentId])) {
            return '';
        }
        $seen[$parentId] = true;
        $output = '';
        $title = Text::_('COM_SPORTSMANAGEMENT_CLUBINFO_HISTORY_FROM');

        foreach ($children[$parentId] as $club) {
            $id = (int) ($club->id ?? 0);
            if ($id <= 0 || isset($seen[$id])) {
                continue;
            }

            $output .= '<li>'
                . HTMLHelper::image(
                    'media/com_sportsmanagement/jl_images/club_from.png',
                    $title,
                    ['title' => $title]
                )
                . '&nbsp;' . HTMLHelper::link(
                    self::clubLink($club, $databaseSelector),
                    self::escape((string) ($club->name ?? ''))
                )
                . '</li>';
            $nested = self::renderHistoryList($children, $id, $databaseSelector, $seen);
            if ($nested !== '') {
                $output .= '<ul>' . $nested . '</ul>';
            }
        }

        return $output;
    }

    private static function clubLink(object $club, int $databaseSelector): string
    {
        return SiteRouteHelper::view('clubinfo', [
            'cfg_which_database' => $databaseSelector,
            'p' => (string) ($club->project_slug ?? '0'),
            'cid' => (string) ($club->slug ?? $club->id ?? 0),
        ]);
    }

    private static function escape(string $value): string
    {
        return htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
    }
}
