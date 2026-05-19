<?php

namespace Backend\Modules\Policies\Actions;

use Backend\Core\Engine\Base\ActionIndex;
use Backend\Modules\Policies\Domain\Settings\Command\SaveSettings;
use Backend\Modules\Policies\Domain\Settings\SettingsType;
use Backend\Core\Engine\Model as BackendModel;
use Common\ModulesSettings;
use Symfony\Component\Form\Form;
use Symfony\Component\HttpKernel\KernelInterface;
use Symfony\Component\Messenger\MessageBusInterface;

final class Settings extends ActionIndex
{
    private MessageBusInterface $messageBus;

    /** @var ModulesSettings */
    private $settings;

    public function setKernel(?KernelInterface $kernel = null): void
    {
        parent::setKernel($kernel);

        $this->messageBus = $this->get('messenger.default_bus');
        $this->settings = $this->get('fork.settings');
    }

    public function execute(): void
    {
        parent::execute();

        $this->handleAuthentication();
        $this->handleSettings();
        $this->parse();
        $this->display();
    }

    private function handleSettings(): void
    {
        $form = $this->getSettingsForm();

        if (!$form->isSubmitted() || !$form->isValid()) {
            $this->template->assign('form', $form->createView());

            return;
        }

        $this->handleSettingsForm($form);
    }

    private function handleSettingsForm(Form $form): void
    {
        $saveSettings = $form->getData();

        $this->get('messenger.default_bus')->dispatch($saveSettings);

        $this->redirect(
            $this->getBackLink(
                [
                    'report' => 'saved',
                ]
            )
        );
    }

    private function getSettingsForm(): Form
    {
        $form = $this->createForm(SettingsType::class, new SaveSettings($this->get('fork.settings')));

        $form->handleRequest($this->getRequest());

        return $form;
    }

    private function getBackLink(array $parameters = []): string
    {
        return BackendModel::createUrlForAction(
            'Settings',
            null,
            null,
            $parameters
        );
    }

    private function handleAuthentication(): void
    {
        if ($this->settings->get($this->getModule(), 'appId') === null) {
            return;
        }

        $queryString = $this->getRequest()->query;
        if ($queryString->has('code')) {
            $settings = new SaveSettings($this->get('fork.settings'));
            $this->messageBus->dispatch($settings);

            $this->redirect(
                $this->getBackLink(
                    [
                        'report' => 'authenticated',
                    ]
                )
            );
        }
    }
}
