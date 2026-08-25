<?php
namespace Diddipoeler\Component\SportsManagement\Site\View\Hitlist;

\defined('_JEXEC') or die;

use Diddipoeler\Component\SportsManagement\Site\Model\HitlistModel;
use Diddipoeler\Component\SportsManagement\Site\View\SportsManagementHtmlView;

final class HtmlView extends SportsManagementHtmlView
{
    public string $tableclass = 'table';
    public array $model_hits = [];

    public function display($tpl = null)
    {
        /** @var HitlistModel $model */
        $model = $this->getModel();

        if (!$model instanceof HitlistModel) {
            throw new \RuntimeException('Hitlist view requires HitlistModel.', 500);
        }

        $this->tableclass = $this->input->getString('table_class', 'table');
        $maxHits = max(0, $this->input->getInt('max_hits', 20));

        foreach ([
            'project' => $this->input->getBool('show_project', true),
            'club' => $this->input->getBool('show_club', true),
            'team' => $this->input->getBool('show_team', true),
            'person' => $this->input->getBool('show_person', true),
            'playground' => $this->input->getBool('show_playground', true),
        ] as $table => $enabled) {
            if ($enabled) {
                $model->getSportsmanagementHits(null, $maxHits, $table);
            }
        }

        $this->model_hits = HitlistModel::$_success_text;

        return parent::display($tpl);
    }
}
