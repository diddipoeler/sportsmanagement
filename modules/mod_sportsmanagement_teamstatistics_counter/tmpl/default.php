<?php
/**
 * Native Joomla 5/6 layout for the SportsManagement Team Statistics Counter module.
 *
 * @version    5.6.0
 * @author     diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
 * @copyright  Copyright: © 2013-2023 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

\defined('_JEXEC') or die;

use Joomla\CMS\Language\Text;

$team = $data['team'] ?? null;
$project = $data['project'] ?? null;
$stats = is_array($data['stats'] ?? null) ? $data['stats'] : [];

if (!$team || !$project) {
    return;
}

$home = $stats['totalshome'] ?? (object) [];
$away = $stats['totalsaway'] ?? (object) [];
$results = is_array($stats['results'] ?? null) ? $stats['results'] : [];

$playedMatches = (int) ($home->playedmatches ?? 0) + (int) ($away->playedmatches ?? 0);
$totalGoals = (int) ($home->totalgoals ?? 0) + (int) ($away->totalgoals ?? 0);
$goalsFor = (int) ($home->goalsfor ?? 0) + (int) ($away->goalsfor ?? 0);
$goalsAgainst = (int) ($home->goalsagainst ?? 0) + (int) ($away->goalsagainst ?? 0);

$perMatch = static fn (int $value, int $matches): float|int =>
    $matches > 0 ? round($value / $matches, 2) : 0;

$metrics = [
    [
        'enabled' => (bool) $params->get('show_round_numbers', 1),
        'icon' => 'startroster.png',
        'value' => (int) ($stats['totalrounds'] ?? 0),
        'label' => 'MOD_SPORTSMANAGEMENT_TEAMSTATISTICS_COUNTER_ROUND_NUMBERS',
    ],
    [
        'enabled' => (bool) $params->get('show_played_matches', 1),
        'icon' => 'shirt.png',
        'value' => $playedMatches,
        'label' => 'MOD_SPORTSMANAGEMENT_TEAMSTATISTICS_COUNTER_PLAYED_MATCHES',
    ],
    [
        'enabled' => (bool) $params->get('show_wins', 1),
        'icon' => 'win.png',
        'value' => count($results['win'] ?? []),
        'label' => 'MOD_SPORTSMANAGEMENT_TEAMSTATISTICS_COUNTER_WINS',
    ],
    [
        'enabled' => (bool) $params->get('show_draws', 1),
        'icon' => 'draw.png',
        'value' => count($results['tie'] ?? []),
        'label' => 'MOD_SPORTSMANAGEMENT_TEAMSTATISTICS_COUNTER_DRAWS',
    ],
    [
        'enabled' => (bool) $params->get('show_loses', 1),
        'icon' => 'lose.png',
        'value' => count($results['loss'] ?? []),
        'label' => 'MOD_SPORTSMANAGEMENT_TEAMSTATISTICS_COUNTER_LOSES',
    ],
    [
        'enabled' => (bool) $params->get('show_goals', 1),
        'icon' => 'goal.png',
        'value' => $totalGoals,
        'label' => 'MOD_SPORTSMANAGEMENT_TEAMSTATISTICS_COUNTER_GOALS',
    ],
    [
        'enabled' => (bool) $params->get('show_goals_per_match', 1),
        'icon' => 'goal.png',
        'value' => $perMatch($totalGoals, $playedMatches),
        'label' => 'MOD_SPORTSMANAGEMENT_TEAMSTATISTICS_COUNTER_GOALS_PER_MATCH',
    ],
    [
        'enabled' => (bool) $params->get('show_scoring_goals', 1),
        'icon' => 'goal.png',
        'value' => $goalsFor,
        'label' => 'MOD_SPORTSMANAGEMENT_TEAMSTATISTICS_COUNTER_SCORING_GOALS',
    ],
    [
        'enabled' => (bool) $params->get('show_scoring_goals_per_match', 1),
        'icon' => 'goal.png',
        'value' => $perMatch($goalsFor, $playedMatches),
        'label' => 'MOD_SPORTSMANAGEMENT_TEAMSTATISTICS_COUNTER_SCORING_GOALS_PER_MATCH',
    ],
    [
        'enabled' => (bool) $params->get('show_against_goals', 1),
        'icon' => 'own_goal.png',
        'value' => $goalsAgainst,
        'label' => 'MOD_SPORTSMANAGEMENT_TEAMSTATISTICS_COUNTER_AGAINST_GOALS',
    ],
    [
        'enabled' => (bool) $params->get('show_against_goals_per_match', 1),
        'icon' => 'own_goal.png',
        'value' => $perMatch($goalsAgainst, $playedMatches),
        'label' => 'MOD_SPORTSMANAGEMENT_TEAMSTATISTICS_COUNTER_AGAINST_GOALS_PER_MATCH',
    ],
    [
        'enabled' => (bool) $params->get('show_clean_sheets', 1),
        'icon' => 'clean-sheets.png',
        'value' => (int) (($stats['nogoals_against']->totalzero ?? 0)),
        'label' => 'MOD_SPORTSMANAGEMENT_TEAMSTATISTICS_COUNTER_CLEAN_SHEETS',
    ],
];

$metrics = array_values(array_filter($metrics, static fn (array $metric): bool => $metric['enabled']));

$escape = static fn (mixed $value): string =>
    htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');

$normaliseColor = static function (mixed $value, string $fallback): string {
    $value = trim((string) $value);

    return preg_match('/^(?:#[0-9a-fA-F]{3,8}|[a-zA-Z]+)$/', $value) ? $value : $fallback;
};

$mode = strtoupper((string) $params->get('mode', 'S'));
$moduleClass = trim((string) $params->get('moduleclass_sfx', ''));
$moduleClass = $moduleClass !== '' ? ' ' . $escape($moduleClass) : '';

$iconBase = 'images/com_sportsmanagement/database/events/';
?>
<div class="jsm-teamstatistics-counter<?php echo $moduleClass; ?>">
    <?php if ((bool) $params->get('show_project_name', 1)) : ?>
        <h4 class="jsm-teamstatistics-counter-project"><?php echo $escape($project->name ?? ''); ?></h4>
    <?php endif; ?>

    <?php if ((bool) $params->get('show_team_name', 1)) : ?>
        <h4 class="jsm-teamstatistics-counter-team"><?php echo $escape($team->name ?? ''); ?></h4>
    <?php endif; ?>

    <?php if ($mode === 'C') : ?>
        <section class="counter">
            <div class="main_counter_area">
                <div class="overlay p-y-3">
                    <div class="container-fluid">
                        <div class="row g-3 main_counter_content text-center">
                            <?php foreach ($metrics as $metric) : ?>
                                <div class="col-12 col-sm-6 col-lg-3">
                                    <div class="single_counter p-y-2 h-100">
                                        <img
                                            src="<?php echo $escape($iconBase . $metric['icon']); ?>"
                                            class="jsm-teamstatistics-counter-icon"
                                            alt=""
                                            loading="lazy"
                                        >
                                        <h2 class="statistic-counter"><?php echo $escape($metric['value']); ?></h2>
                                        <p class="mb-0"><?php echo Text::_($metric['label']); ?></p>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>
            </div>
        </section>
    <?php else : ?>
        <?php
        $borderColor = $normaliseColor($params->get('border_color', '#41008a'), '#41008a');
        $backgroundColor = $normaliseColor($params->get('background_color', '#eeeeee'), '#eeeeee');
        $titleColor = $normaliseColor($params->get('title_color', '#000000'), '#000000');
        $textColor = $normaliseColor($params->get('text_color', '#000000'), '#000000');
        $titleSize = max(1, min(72, (int) $params->get('title_size', 18)));
        $textSize = max(1, min(72, (int) $params->get('text_size', 14)));

        $stickerStyle = [
            '--jsm-counter-background:' . $backgroundColor,
            '--jsm-counter-title-color:' . $titleColor,
            '--jsm-counter-text-color:' . $textColor,
            '--jsm-counter-title-size:' . $titleSize . 'px',
            '--jsm-counter-text-size:' . $textSize . 'px',
        ];

        if ((bool) $params->get('border', 0)) {
            $stickerStyle[] = '--jsm-counter-border:1px solid ' . $borderColor;
        }
        if ((bool) $params->get('border_rounded', 0)) {
            $stickerStyle[] = '--jsm-counter-radius:20px';
        }
        if ((bool) $params->get('border_shadow', 0)) {
            $stickerStyle[] = '--jsm-counter-shadow:10px 10px 6px 3px #474747';
        }
        ?>
        <div
            class="jsm-teamstatistics-counter-sticker"
            style="<?php echo $escape(implode(';', $stickerStyle)); ?>"
        >
            <?php foreach ($metrics as $metric) : ?>
                <div class="jsm-teamstatistics-counter-sticker-row">
                    <img
                        src="<?php echo $escape($iconBase . $metric['icon']); ?>"
                        class="jsm-teamstatistics-counter-icon"
                        alt=""
                        loading="lazy"
                    >
                    <p>
                        <strong><?php echo $escape($metric['value']); ?></strong>
                        <?php echo Text::_($metric['label']); ?>
                    </p>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>
