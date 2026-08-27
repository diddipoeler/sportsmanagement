<?php
/**
 * SportsManagement ein Programm zur Verwaltung für alle Sportarten
 *
 * @version    1.0.05
 * @package    Sportsmanagement
 * @subpackage tournamentbracket
 * @file       default.php
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
defined('_JEXEC') or die('Restricted access');

$templatesToLoad = ['globalviews'];
sportsmanagementHelper::addTemplatePaths($templatesToLoad, $this);
echo $this->loadTemplate('projectheading');

$matchInfo = [];
$bracketInfo = [];
$penaltyInfo = (string) ($this->bracket['elfmeter'][0] ?? '');

foreach (explode('#', $penaltyInfo) as $encodedMatch) {
    if ($encodedMatch === '') {
        continue;
    }

    $decoded = json_decode($encodedMatch, true);
    if (!is_array($decoded)) {
        continue;
    }

    $description = trim((string) ($decoded[3] ?? ''));
    if ($description === '') {
        $bracketInfo[] = '';
        $bracketInfo[] = '';
        continue;
    }

    $parts = array_map('trim', explode('-', $description, 2));
    $bracketInfo[] = $parts[0] ?? '';
    $bracketInfo[] = $parts[1] ?? '';
}

if (!$bracketInfo) {
    $bracketInfo = ['', ''];
}

foreach (array_values($bracketInfo) as $index => $value) {
    $matchInfo[$index + 1] = $value;
}

$matchInfoJson = json_encode($matchInfo, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) ?: '{}';
$teamsJson = (string) ($this->bracket['teams'] ?? '[]');
$resultsJson = (string) ($this->bracket['results'] ?? '[]');
$roundsJson = (string) ($this->bracket['runden'] ?? '[]');

$wa = $this->document->getWebAssetManager();
$wa->registerAndUseScript(
    'com_sportsmanagement.jquery-bracket',
    'https://cdnjs.cloudflare.com/ajax/libs/jquery-bracket/0.11.1/jquery.bracket.min.js',
    [],
    [
        'integrity' => 'sha512-BgJKmxJA3rvUEa00GOdL9BJm5+lu6V7Gx2K0qWDitRi0trcA+kS/VYJuzlqlwGJ0eUeIopW4T9faczsg8hzE/g==',
        'crossorigin' => 'anonymous',
        'referrerpolicy' => 'no-referrer',
    ],
    ['jquery']
);
$wa->registerAndUseStyle(
    'com_sportsmanagement.jquery-bracket',
    'https://cdnjs.cloudflare.com/ajax/libs/jquery-bracket/0.11.1/jquery.bracket.min.css',
    [],
    [
        'integrity' => 'sha512-8QbEO8yS//4kwUDxGu/AS49R2nVILw83kYCtgxBYk+Uw0B9S4R0RgSwvhGLwMaZuYzhhR5ZHR9dA2cDgphRTgg==',
        'crossorigin' => 'anonymous',
        'referrerpolicy' => 'no-referrer',
    ]
);
?>

<div class="<?php echo $this->escape($this->divclasscontainer); ?>" id="tournamentbracket">
    <div class="row-fluid">
        <div style="margin-bottom: 5px; font-size: 16px;"><span id="matchCallback"></span></div>

        <div id="resize" class="col-12">
            <h3>Resizing</h3>
            <label class="rangePicker" for="teamWidthInput">
                teamWidth: <span id="teamWidthValue">200</span>;
                <input id="teamWidthInput" type="range" min="30" max="400" step="1" value="200">
            </label>
            <label class="rangePicker" for="scoreWidthInput">
                scoreWidth: <span id="scoreWidthValue">90</span>;
                <input id="scoreWidthInput" type="range" min="20" max="100" step="1" value="90">
            </label>
            <label class="rangePicker" for="matchMarginInput">
                matchMargin: <span id="matchMarginValue">60</span>;
                <input id="matchMarginInput" type="range" min="0" max="100" step="1" value="60">
            </label>
            <label class="rangePicker" for="roundMarginInput">
                roundMargin: <span id="roundMarginValue">60</span>;
                <input id="roundMarginInput" type="range" min="3" max="100" step="1" value="60">
            </label>
        </div>

        <div id="minimal" class="col-12">
            <div class="roundnames" style="display:flex"></div>
            <div class="demo col-12"></div>
        </div>
    </div>
</div>

<script>
(() => {
    'use strict';

    const $ = window.jQuery;
    if (!$ || !$.fn || typeof $.fn.bracket !== 'function') {
        return;
    }

    const matchInfo = <?php echo $matchInfoJson; ?>;
    const wholeArray = Object.keys(matchInfo).map((key) => matchInfo[key]);
    const minimalData = {
        teams: <?php echo $teamsJson; ?>,
        results: <?php echo $resultsJson; ?>
    };
    const roundArray = <?php echo $roundsJson; ?>;
    const container = $('#minimal .demo');

    const resizeParameters = {
        scoreWidth: 90,
        matchMargin: 60,
        roundMargin: 60,
        teamWidth: 200,
        init: minimalData,
        skipConsolationRound: true,
        onMatchClick: () => {},
        onMatchHover: () => {}
    };

    const controls = {
        teamWidth: ['teamWidthInput', 'teamWidthValue'],
        scoreWidth: ['scoreWidthInput', 'scoreWidthValue'],
        matchMargin: ['matchMarginInput', 'matchMarginValue'],
        roundMargin: ['roundMarginInput', 'roundMarginValue']
    };

    function addRoundAndResultInfo() {
        const resultElements = document.querySelectorAll('[data-resultid]');

        resultElements.forEach((element, index) => {
            const resultId = `result-${index + 1}`;
            const score = document.querySelector(`.score[data-resultid="${resultId}"]`);
            if (!score) {
                return;
            }

            const info = wholeArray[index];
            if (info) {
                score.textContent = info;
            }
        });

        document.querySelectorAll('#minimal .round').forEach((round, index) => {
            const oldLabel = round.querySelector(':scope > .jsm-round-name');
            if (oldLabel) {
                oldLabel.remove();
            }

            if (!Array.isArray(roundArray) || !roundArray[index]) {
                return;
            }

            const wrapper = document.createElement('div');
            wrapper.className = 'jsm-round-name';
            wrapper.style.width = '0';
            wrapper.style.height = '0';

            const label = document.createElement('label');
            label.style.position = 'absolute';
            label.style.width = '250px';
            label.style.fontSize = '100%';
            label.style.fontWeight = 'bold';
            label.style.textAlign = 'center';
            label.style.left = '10px';
            label.style.top = '-5px';
            label.style.padding = '0';
            label.style.color = 'rgba(0,0,0,0.6)';
            label.textContent = String(roundArray[index]);

            wrapper.append(label);
            round.append(wrapper);
        });
    }

    function updateBracket() {
        container.bracket(resizeParameters);
        addRoundAndResultInfo();
    }

    Object.entries(controls).forEach(([property, ids]) => {
        const input = document.getElementById(ids[0]);
        const output = document.getElementById(ids[1]);
        if (!input || !output) {
            return;
        }

        input.value = String(resizeParameters[property]);
        output.textContent = input.value;
        input.addEventListener('input', () => {
            resizeParameters[property] = Number.parseInt(input.value, 10) || 0;
            output.textContent = input.value;
            updateBracket();
        });
    });

    $(updateBracket);
})();
</script>
