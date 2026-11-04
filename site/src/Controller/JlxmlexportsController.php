<?php    /**
     * Support the historic XML export ranking task alias.
     *
     * Selects the jlxmlexports view explicitly and invokes Joomla's base
     * display flow without attempting a separate export operation.
     *
     * @return void
     */
    public function showranking(): void
    {
        $this->input->set('view', 'jlxmlexports');
        parent::display();
    }
}
