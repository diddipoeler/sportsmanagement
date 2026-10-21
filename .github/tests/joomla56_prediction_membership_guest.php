<?php
/**
 * Joomla 5/6 prediction membership guest access regression tests.
 *
 * @version    5.6.0
 * @author     diddipoeler
 * @copyright  Copyright (C) diddipoeler
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
declare(strict_types=1);

namespace Joomla\CMS\Language {
    class Text {
        public static function _(string $key): string { return $key; }
    }
}

namespace Diddipoeler\Component\SportsManagement\Site\Model {
    class PredictionentryModel {
        protected int $predictionGameId = 8;
        public static ?object $identity = null;
        public static int $gameLookups = 0;

        protected function siteApplication(): object {
            return new class {
                public function getIdentity(): ?object {
                    return PredictionentryModel::$identity;
                }
            };
        }
        public function getPredictionGame(): ?object {
            ++self::$gameLookups;
            return null;
        }
    }
}

namespace {
    define('_JEXEC', 1);
    require_once dirname(__DIR__, 2) . '/site/src/Model/PredictionmembershipModel.php';

    $base = \Diddipoeler\Component\SportsManagement\Site\Model\PredictionentryModel::class;
    $model = new \Diddipoeler\Component\SportsManagement\Site\Model\PredictionmembershipModel();

    foreach ([null, (object) ['id' => 0], (object) ['id' => -1], (object) []] as $identity) {
        $base::$identity = $identity;
        $base::$gameLookups = 0;
        try {
            $model->registerCurrentUser();
            throw new \RuntimeException('Anonymous user was registered.');
        } catch (\RuntimeException $error) {
            if ($error->getCode() !== 403) { throw $error; }
        }

        if ($base::$gameLookups !== 0) {
            throw new \RuntimeException('Guest registration loaded prediction game data.');
        }
    }

    $base::$identity = (object) ['id' => 17];
    try {
        $model->registerCurrentUser();
        throw new \RuntimeException('Missing prediction game should deny registration.');
    } catch (\RuntimeException $error) {
        if ($error->getCode() !== 403) { throw $error; }
    }

    if ($base::$gameLookups !== 1) {
        throw new \RuntimeException('Authenticated registration stopped before the game check.');
    }

    echo "Joomla 5/6 prediction membership guest permissions OK\n";
}
