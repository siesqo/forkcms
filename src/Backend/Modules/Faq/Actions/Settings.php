<?php

namespace Backend\Modules\Faq\Actions;

use Backend\Core\Engine\Base\ActionIndex;
use Backend\Core\Engine\Model as BackendModel;
use Backend\Modules\Faq\Domain\Settings\SaveSettings;
use Backend\Modules\Faq\Domain\Settings\SettingsType;
use Symfony\Component\Form\Form;

final class Settings extends ActionIndex
{
    public function execute(): void
    {
        parent::execute();

        $form = $this->getForm();

        if (!$form->isSubmitted() || !$form->isValid()) {
            $this->template->assign('form', $form->createView());

            $this->parse();
            $this->display();

            return;
        }

        $this->handleForm($form);
    }

    private function handleForm(Form $form): void
    {
        $this->get('messenger.default_bus')->dispatch($form->getData());

        $this->redirect($this->getBackLink(['report' => 'saved']));
    }

    private function getForm(): Form
    {
        $form = $this->createForm(SettingsType::class, new SaveSettings($this->get('fork.settings')));
        $form->handleRequest($this->getRequest());

        return $form;
    }

    private function getBackLink(array $parameters = []): string
    {
        return BackendModel::createUrlForAction('Settings', null, null, $parameters);
    }
}
