<?php
/**
 * Regression tests for Top Tipper input validation in Joomla 5/6.
 *
 * @version    5.6.0
 * @author     diddipoeler
 * @copyright  Copyright (C) diddipoeler
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
declare(strict_types=1);

define('_JEXEC', 1);
require dirname(__DIR__, 2) . '/modules/mod_sportsmanagement_top_tipper/src/Helper/TopTipperHelper.php';

$class = \Diddipoeler\Module\SportsManagementTopTipper\Site\Helper\TopTipperHelper::class;
$helper = (new ReflectionClass($class))->newInstanceWithoutConstructor();
$extractId = new ReflectionMethod($class, 'extractId');
$boundedLimit = new ReflectionMethod($class, 'boundedLimit');
$databaseSelector = new ReflectionMethod($class, 'databaseSelector');

foreach ([['12', 12], ['0012:team', 12], [['7'], 7], ['12bad', 0], ['0', 0], [PHP_INT_MAX . '0', 0], [new stdClass(), 0]] as [$value, $expected]) {
    if ($extractId->invoke($helper, $value) !== $expected) {
        fwrite(STDERR, 'Top Tipper ID validation regression.' . PHP_EOL);
        exit(1);
    }
}

foreach ([[0, 1], ['0007', 7], [999, 100], ['invalid', 5], [[], 5]] as [$value, $expected]) {
    if ($boundedLimit->invoke(null, $value) !== $expected) {
        fwrite(STDERR, 'Top Tipper limit validation regression.' . PHP_EOL);
        exit(1);
    }
}

foreach ([['0001', 1], [0, 0], ['-1', null], [['1'], null], [PHP_INT_MAX . '0', null]] as [$value, $expected]) {
    if ($databaseSelector->invoke(null, $value) !== $expected) {
        fwrite(STDERR, 'Top Tipper database selector regression.' . PHP_EOL);
        exit(1);
    }
}

$rankingType = new ReflectionMethod($class, 'rankingType');
foreach ([
    [0, 0], [1, 1], [2, 2], ['1', 1], ['0002', 2],
    ['000', 0], ['3', 0], [-1, 0], ['2invalid', 0],
    [' 2', 0], [true, 0], [[2], 0], [PHP_INT_MAX . '0', 0],
] as [$raw, $expected]) {
    if ($rankingType->invoke(null, $raw) !== $expected) {
        fwrite(STDERR, 'Top Tipper ranking type validation regression.' . PHP_EOL);
        exit(1);
    }
}

$enabledSwitch = new ReflectionMethod($class, 'enabledSwitch');
foreach ([
    [1, true], ['1', true], [0, false], ['0', false],
    [true, false], [false, false], ['1invalid', false],
    [['1'], false], ['01', false], [null, false],
] as [$raw, $expected]) {
    if ($enabledSwitch->invoke(null, $raw) !== $expected) {
        fwrite(STDERR, 'Top Tipper round switch validation regression.' . PHP_EOL);
        exit(1);
    }
}

$safeAvatarPath = new ReflectionMethod($class, 'safeAvatarPath');
foreach ([
    ['images/avatars/club player.png', 'images/avatars/club player.png'],
    ['media/avatars/münchen.webp', 'media/avatars/münchen.webp'],
    ['image.JPG', 'image.JPG'],
    ['../configuration.php', ''],
    ['images/../secret.png', ''],
    ['images/./photo.png', ''],
    ['/images/avatar.png', ''],
    ['//example.org/avatar.png', ''],
    ['https://example.org/photo.png', ''],
    ['images/%2e%2e/photo.png', ''],
    ['images\\\\avatar.png', ''],
    ['images/avatar.png?size=2', ''],
    ['images/avatar.png#hash', ''],
    ['images//avatar.png', ''],
    ["images/photo\\n.png", ''],
    ['images/file.svg', ''],
    [str_repeat('a', 1025) . '.png', ''],
    [false, ''],
    [['avatar.png'], ''],
] as [$raw, $expected]) {
    if ($safeAvatarPath->invoke(null, $raw) !== $expected) {
        fwrite(STDERR, 'Top Tipper avatar path regression.' . PHP_EOL);
        exit(1);
    }
}

if ($class::avatarUrl('../configuration.php') !== ''
    || $class::avatarUrl(['photo.png']) !== '') {
    fwrite(STDERR, 'Top Tipper invalid avatar URL regression.' . PHP_EOL);
    exit(1);
}

foreach ([
    [7, 7], ['0007', 7], ['7', 7],
    [0, 0], ['000', 0], [-1, 0],
    ['7:alias', 0], ['7invalid', 0], [' 7', 0],
    [true, 0], [false, 0], [['7'], 0],
    [PHP_INT_MAX . '0', 0], [str_repeat('0', 256) . '7', 0],
] as [$raw, $expected]) {
    if ($class::validMemberId($raw) !== $expected) {
        fwrite(STDERR, 'Top Tipper ranking member ID regression.' . PHP_EOL);
        exit(1);
    }
}

foreach ([
    [0, 7, 0],
    [7, 0, 0],
    [-1, 7, 0],
    [7, 7, -1],
] as [$gameId, $memberId, $selector]) {
    if ($helper->memberUrl($gameId, $memberId, $selector) !== '') {
        fwrite(STDERR, 'Top Tipper invalid member link regression.' . PHP_EOL);
        exit(1);
    }
}

foreach ([
    [0, 7, 3, 2, 0],
    [7, 0, 3, 2, 0],
    [7, 7, 0, 2, 0],
    [7, 7, 3, -1, 0],
    [7, 7, 3, 2, -1],
] as [$gameId, $memberId, $projectId, $roundId, $selector]) {
    if ($helper->detailsUrl($gameId, $memberId, $projectId, $roundId, $selector) !== '') {
        fwrite(STDERR, 'Top Tipper invalid details link regression.' . PHP_EOL);
        exit(1);
    }
}

$validRankingRows = new ReflectionMethod($class, 'validRankingRows');
$firstMember = ['member' => (object) ['id' => 7], 'totalPoints' => 3];
$secondMember = ['member' => (object) ['id' => 8], 'totalPoints' => 5];
$inputRows = [
    'bogus' => $firstMember,
    0 => $firstMember,
    7 => ['member' => ['id' => 7]],
    8 => 'invalid',
    '0007' => $firstMember,
    9 => $secondMember,
];
$expectedRows = ['0007' => $firstMember, 9 => $secondMember];
if ($validRankingRows->invoke(null, $inputRows) !== $expectedRows
    || $validRankingRows->invoke(null, new stdClass()) !== []) {
    fwrite(STDERR, 'Top Tipper malformed ranking rows regression.' . PHP_EOL);
    exit(1);
}

foreach ([
    [7, false, 7], ['0007', false, 7], ['0', false, 0],
    [-1, false, 0], [-1, true, -1], ['-0005', true, -5],
    ['-0', true, 0], ['-2', false, 0],
    ['7invalid', false, 0], [' 7', false, 0],
    [1.5, false, 0], [true, false, 0],
    [['7'], false, 0], [PHP_INT_MAX . '0', true, 0],
    [str_repeat('0', 256), false, 0],
] as [$raw, $allowNegative, $expected]) {
    if ($class::rankingInteger($raw, $allowNegative) !== $expected) {
        fwrite(STDERR, 'Top Tipper ranking numeric field regression.' . PHP_EOL);
        exit(1);
    }
}

foreach ([
    [7, 7], ['0007', 7], ['0008:round-one', 8],
    ['0', 0], ['0:round', 0], ['8garbage', 0],
    ['8:bad/path', 0], [true, 0], [false, 0],
    [['8'], 0], [PHP_INT_MAX . '0', 0],
] as [$value, $expected]) {
    if ($class::roundOptionId($value) !== $expected) {
        fwrite(STDERR, 'Top Tipper single round option ID regression.' . PHP_EOL);
        exit(1);
    }
}

$validRoundOptions = new ReflectionMethod($class, 'validRoundOptions');
$sourceFirst = (object) ['value' => '0007:round-one', 'text' => 'Round seven'];
$sourceDuplicate = (object) ['value' => 7, 'text' => 'Duplicate'];
$sourceSecond = (object) ['value' => '8', 'text' => 'Round eight'];
$roundOptions = $validRoundOptions->invoke(null, [
    $sourceFirst,
    (object) ['value' => ['9'], 'text' => 'Malformed'],
    $sourceDuplicate,
    (object) ['value' => '8garbage', 'text' => 'Malformed'],
    $sourceSecond,
    ['value' => 10],
]);
if (count($roundOptions) !== 2
    || $roundOptions[0]->value !== 7 || $roundOptions[1]->value !== 8
    || $sourceFirst->value !== '0007:round-one'
    || $roundOptions[0] === $sourceFirst
    || $validRoundOptions->invoke(null, new stdClass()) !== []) {
    fwrite(STDERR, 'Top Tipper invalid and duplicate round options regression.' . PHP_EOL);
    exit(1);
}

echo "Top Tipper input checks passed.\n";
