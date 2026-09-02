<?php
/** Native Joomla 5/6 ranking layout. */
defined('_JEXEC') or die('Restricted access');

use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;

if (!$this->project) {
    return;
}

$tables = [];
if ($this->type === 1) {
    $tables[] = ['id' => 'ranking-home-filter', 'title' => Text::_('COM_SPORTSMANAGEMENT_RANKING_HOME_RANKING'), 'data' => $this->currentRanking];
} elseif ($this->type === 2) {
    $tables[] = ['id' => 'ranking-away-filter', 'title' => Text::_('COM_SPORTSMANAGEMENT_RANKING_AWAY_RANKING'), 'data' => $this->currentRanking];
} else {
    $configured = !empty($this->config['show_table_1'])
        || !empty($this->config['show_table_2'])
        || !empty($this->config['show_table_3'])
        || !empty($this->config['show_table_4'])
        || !empty($this->config['show_table_5']);

    if (!$configured || !empty($this->config['show_table_1'])) {
        $tables[] = [
            'id' => 'ranking-current',
            'title' => Text::_((string) ($this->config['table_text_1'] ?? 'COM_SPORTSMANAGEMENT_XML_RANKING_LAYOUT_TITLE')),
            'data' => $this->currentRanking,
        ];
    }
    if (!empty($this->config['show_table_2']) && $this->homeRank !== []) {
        $tables[] = [
            'id' => 'ranking-home',
            'title' => Text::_((string) ($this->config['table_text_2'] ?? 'COM_SPORTSMANAGEMENT_RANKING_HOME_RANKING')),
            'data' => $this->homeRank,
        ];
    }
    if (!empty($this->config['show_table_3']) && $this->awayRank !== []) {
        $tables[] = [
            'id' => 'ranking-away',
            'title' => Text::_((string) ($this->config['table_text_3'] ?? 'COM_SPORTSMANAGEMENT_RANKING_AWAY_RANKING')),
            'data' => $this->awayRank,
        ];
    }
    if (!empty($this->config['show_table_4']) && $this->firstRank !== []) {
        $tables[] = [
            'id' => 'ranking-first',
            'title' => Text::_((string) ($this->config['table_text_4'] ?? 'COM_SPORTSMANAGEMENT_RANKING_FIRST_HALF')),
            'data' => $this->firstRank,
        ];
    }
    if (!empty($this->config['show_table_5']) && $this->secondRank !== []) {
        $tables[] = [
            'id' => 'ranking-second',
            'title' => Text::_((string) ($this->config['table_text_5'] ?? 'COM_SPORTSMANAGEMENT_RANKING_SECOND_HALF')),
            'data' => $this->secondRank,
        ];
    }
}

$pdfUri = clone $this->uri;
$pdfUri->setVar('format', 'pdf');
$pdfUri->delVar('tmpl');
$roundMap = [];
foreach ($this->rounds as $index => $round) {
    $roundMap[(int) ($round->id ?? 0)] = $index;
}
$currentRoundIndex = $roundMap[$this->round] ?? null;
?>
<div class="<?php echo $this->escape($this->divclasscontainer); ?>" id="defaultranking" data-jsm-ranking>
    <?php echo $this->loadTemplate('projectheading'); ?>

    <?php foreach ($this->warnings as $message) : ?>
        <div class="alert alert-warning" role="alert"><?php echo $this->escape((string) $message); ?></div>
    <?php endforeach; ?>
    <?php foreach ($this->tips as $message) : ?>
        <div class="alert alert-info" role="note"><?php echo $this->escape((string) $message); ?></div>
    <?php endforeach; ?>
    <?php foreach ($this->notes as $message) : ?>
        <div class="alert alert-secondary" role="note"><?php echo $this->escape((string) $message); ?></div>
    <?php endforeach; ?>

    <?php if (!empty($this->config['show_sectionheader'])) : ?>
        <header class="<?php echo $this->escape($this->divclassrow); ?> mb-3" id="ranking-sectionheader">
            <h2 class="h4 mb-1"><?php echo Text::_('COM_SPORTSMANAGEMENT_XML_RANKING_LAYOUT_TITLE'); ?></h2>
            <?php if ($this->from > 0 || $this->to > 0) : ?>
                <div class="text-muted small">
                    <?php echo Text::sprintf('COM_SPORTSMANAGEMENT_RANKING_FROM_TO', $this->from, $this->to); ?>
                </div>
            <?php endif; ?>
        </header>
    <?php endif; ?>

    <?php if (!empty($this->config['show_rankingnav'])) : ?>
        <?php echo $this->loadTemplate('nav'); ?>
    <?php endif; ?>

    <?php if (!empty($this->config['show_ranking']) && $tables !== []) : ?>
        <div class="d-flex flex-wrap gap-2 mb-3 ranking-export-controls">
            <?php if (!empty($this->config['show_button_download_pdf'])) : ?>
                <?php
                echo HTMLHelper::link(
                    $pdfUri->toString(),
                    HTMLHelper::image(
                        'media/com_sportsmanagement/jl_images/pdf.png',
                        Text::_('COM_SPORTSMANAGEMENT_FES_OVERALL_PARAM_LABEL_SHOW_BUTTON_DOWNLOAD_PDF'),
                        ['width' => 24]
                    ) . ' PDF',
                    ['class' => $this->escape((string) ($this->config['button_style'] ?? 'btn btn-secondary'))]
                );
                ?>
            <?php endif; ?>
            <?php if (!empty($this->config['show_button_download_excel'])) : ?>
                <button type="button" class="<?php echo $this->escape((string) ($this->config['button_style'] ?? 'btn btn-secondary')); ?>" data-ranking-export="excel">
                    <?php echo HTMLHelper::image('media/com_sportsmanagement/jl_images/excel.png', 'Excel', ['width' => 24]); ?> Excel
                </button>
            <?php endif; ?>
            <?php if (!empty($this->config['show_button_download_mediawiki'])) : ?>
                <button type="button" class="<?php echo $this->escape((string) ($this->config['button_style'] ?? 'btn btn-secondary')); ?>" data-ranking-export="mediawiki">
                    <?php echo HTMLHelper::image('media/com_sportsmanagement/jl_images/mediawiki.png', 'MediaWiki', ['width' => 24]); ?> MediaWiki
                </button>
            <?php endif; ?>
        </div>

        <?php if (count($tables) > 1) : ?>
            <?php echo HTMLHelper::_('bootstrap.startTabSet', 'ranking-tabs', ['active' => $tables[0]['id']]); ?>
            <?php foreach ($tables as $table) : ?>
                <?php echo HTMLHelper::_('bootstrap.addTab', 'ranking-tabs', $table['id'], $table['title']); ?>
                <?php
                $this->activeRanking = (array) $table['data'];
                $this->activeTableId = (string) $table['id'];
                $this->activeTableTitle = (string) $table['title'];
                echo $this->loadTemplate('table');
                ?>
                <?php echo HTMLHelper::_('bootstrap.endTab'); ?>
            <?php endforeach; ?>
            <?php echo HTMLHelper::_('bootstrap.endTabSet'); ?>
        <?php else : ?>
            <?php
            $table = $tables[0];
            $this->activeRanking = (array) $table['data'];
            $this->activeTableId = (string) $table['id'];
            $this->activeTableTitle = (string) $table['title'];
            echo $this->loadTemplate('table');
            ?>
        <?php endif; ?>
    <?php endif; ?>

    <?php if (!empty($this->config['show_colorlegend'])) : ?>
        <?php echo $this->loadTemplate('legend'); ?>
    <?php endif; ?>

    <?php if (!empty($this->config['show_explanation']) && $this->columns !== []) : ?>
        <section class="ranking-explanation my-3" aria-labelledby="ranking-explanation-title">
            <h3 class="h6" id="ranking-explanation-title"><?php echo Text::_('COM_SPORTSMANAGEMENT_RANKING_EXPLANATION'); ?></h3>
            <dl class="row mb-0">
                <?php foreach ($this->columns as $column) : ?>
                    <dt class="col-sm-2"><?php echo $this->escape((string) $column['label']); ?></dt>
                    <dd class="col-sm-10"><?php echo Text::_('COM_SPORTSMANAGEMENT_' . $column['code']); ?></dd>
                <?php endforeach; ?>
            </dl>
        </section>
    <?php endif; ?>

    <?php if (!empty($this->config['show_pagnav']) && is_int($currentRoundIndex)) : ?>
        <nav class="d-flex justify-content-between my-3" aria-label="<?php echo $this->escape(Text::_('COM_SPORTSMANAGEMENT_RANKING')); ?>">
            <span>
                <?php if ($currentRoundIndex > 0) : ?>
                    <?php
                    $previousRound = $this->rounds[$currentRoundIndex - 1];
                    $previousUri = clone $this->uri;
                    $previousUri->setVar('r', (int) $previousRound->id);
                    $previousUri->setVar('to', (int) $previousRound->id);
                    echo HTMLHelper::link($previousUri->toString(), '&larr; ' . Text::_('COM_SPORTSMANAGEMENT_GLOBAL_PREV'), ['class' => 'btn btn-outline-secondary']);
                    ?>
                <?php endif; ?>
            </span>
            <span>
                <?php if ($currentRoundIndex < count($this->rounds) - 1) : ?>
                    <?php
                    $nextRound = $this->rounds[$currentRoundIndex + 1];
                    $nextUri = clone $this->uri;
                    $nextUri->setVar('r', (int) $nextRound->id);
                    $nextUri->setVar('to', (int) $nextRound->id);
                    echo HTMLHelper::link($nextUri->toString(), Text::_('COM_SPORTSMANAGEMENT_GLOBAL_NEXT') . ' &rarr;', ['class' => 'btn btn-outline-secondary']);
                    ?>
                <?php endif; ?>
            </span>
        </nav>
    <?php endif; ?>

    <?php if (!empty($this->config['show_projectinfo']) && trim((string) ($this->project->projectinfo ?? '')) !== '') : ?>
        <section class="ranking-projectinfo card card-body my-3">
            <?php echo HTMLHelper::_('content.prepare', (string) $this->project->projectinfo); ?>
        </section>
    <?php endif; ?>

    <?php if (!empty($this->config['show_notes'])) : ?>
        <?php echo $this->loadTemplate('notes'); ?>
    <?php endif; ?>

    <?php if (!empty($this->config['show_ranking_maps']) && $this->mapTeams !== []) : ?>
        <?php echo $this->loadTemplate('map'); ?>
    <?php endif; ?>

    <?php if (!empty($this->overallconfig['show_project_rss_feed']) && $this->rssfeeditems) : ?>
        <?php echo $this->loadTemplate('rssfeed'); ?>
    <?php endif; ?>

    <?php echo $this->loadTemplate('jsminfo'); ?>
</div>
