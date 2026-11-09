<?php
/**
 * Native Joomla 5/6 statistic definition for frontend match editing.
 *
 * @version    5.6.0
 * @author     diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
 * @copyright  Copyright: © 2013-2023 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
namespace Diddipoeler\Component\SportsManagement\Site\Service;

\defined('_JEXEC') or die;

use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;

/**
 * Lightweight statistic definition used by the frontend match statistics form.
 *
 * The historical editstats templates only require the public identity fields,
 * getCalculated() and getImage(); keeping that contract here avoids loading the
 * legacy administrator statistic framework for a simple input form.
 */
final class EditmatchStatisticDefinition
{
    private static array $calculatedByClass = [];

    public int $id;
    public string $name;
    public string $short;
    public string $icon;
    public int $position_id;
    private int $calculated;

    /**
     * Populate the frontend statistic definition from a database row.
     *
     * Preserves its identifier, name, abbreviated label and icon, and
     * resolves calculated-statistic metadata without loading legacy classes.
     *
     * @param object $row Statistic row returned by the Joomla model.
     * @return void
     */
    public function __construct(object $row)
    {
        $this->id = (int) ($row->id ?? 0);
        $this->name = (string) ($row->name ?? '');
        $this->short = (string) ($row->short ?? '');
        $this->icon = (string) ($row->icon ?? '');
        $this->position_id = (int) ($row->posid ?? $row->position_id ?? 0);
        $this->calculated = self::resolveCalculated(
            (string) ($row->class ?? ''),
            (int) ($row->calculated ?? 0)
        );
    }

    /**
     * Return whether this statistic is configured as a calculated value.
     *
     * @return int Stored calculation indicator used by the editmatch form.
     */
    public function getCalculated(): int
    {
        return $this->calculated;
    }

    /**
     * Render the statistic icon or an escaped abbreviated text fallback.
     *
     * Bounds the configured image height to avoid unsafe CSS dimensions and
     * uses Joomla's HTML image helper for the configured statistic icon.
     *
     * @param int $eventsPictureHeight Requested image height in pixels.
     * @return string Statistic icon markup or escaped short-name fallback.
     */
    public function getImage(int $eventsPictureHeight = 30): string
    {
        $eventsPictureHeight = min(512, max(1, $eventsPictureHeight));

        if ($this->icon !== '') {
            $iconPath = $this->icon;

            if (!str_contains($iconPath, '/')) {
                $iconPath = 'images/com_sportsmanagement/database/statistics/' . $iconPath;
            }

            return HTMLHelper::_(
                'image',
                $iconPath,
                Text::_($this->name),
                [
                    'title' => Text::_($this->name),
                    'style' => 'width: auto;height: ' . $eventsPictureHeight . 'px',
                ]
            );
        }

        return '<span class="stat-alternate hasTip" title="'
            . htmlspecialchars(Text::_($this->name), ENT_QUOTES, 'UTF-8')
            . '">'
            . htmlspecialchars(Text::_($this->short), ENT_QUOTES, 'UTF-8')
            . '</span>';
    }

    /**
     * Read the legacy statistic class's calculated flag without executing it.
     *
     * Resolves the configured class basename beneath the administrator
     * statistics directory and caches the stored calculation metadata.
     * Rejects path fragments and unexpected class-name characters.
     * Missing or unrecognized classes retain the database fallback flag.
     *
     * @param string $class Legacy statistic class name or filename stem.
     * @param int $fallback Calculated flag stored in the database record.
     * @return int Resolved calculation flag or the database fallback.
     */
    private static function resolveCalculated(string $class, int $fallback): int
    {
        // The stored class is a filename stem, not a filesystem path.
        // Only bundled statistic class identifiers may be inspected.
        $class = trim($class);

        if ($class === '' || preg_match('/^[A-Za-z][A-Za-z0-9_]*$/D', $class) !== 1) {
            return $fallback;
        }

        if (array_key_exists($class, self::$calculatedByClass)) {
            return self::$calculatedByClass[$class];
        }

        $file = JPATH_ADMINISTRATOR . '/components/com_sportsmanagement/statistics/' . $class . '.php';

        if (!is_file($file)) {
            return self::$calculatedByClass[$class] = $fallback;
        }

        $source = file_get_contents($file);

        if ($source !== false && preg_match('/\$_calculated\s*=\s*([01])\s*;/', $source, $matches)) {
            return self::$calculatedByClass[$class] = (int) $matches[1];
        }

        return self::$calculatedByClass[$class] = $fallback;
    }
}
