<?php

namespace Frontend\Modules\FormBuilder\EventListener;

use Common\Mailer\Configurator;
use Common\Mailer\Message;
use Common\ModulesSettings;
use Frontend\Core\Language\Language;
use Frontend\Core\Engine\Model as FrontendModel;
use Frontend\Modules\FormBuilder\Event\FormBuilderSubmittedEvent;
use Symfony\Component\Mailer\Mailer;
use Symfony\Component\Mime\Address;
use Symfony\Component\Mime\RawMessage;

/**
 * A FormBuilder submitted event subscriber that will send an email if needed
 */
final class FormBuilderSubmittedMailSubscriber
{
    /**
     * @var ModulesSettings
     */
    protected $modulesSettings;

    /**
     * @var Configurator
     */
    protected $mailer_configurator;

    public function __construct(
        Configurator $mailer_configurator,
        ModulesSettings $modulesSettings
    ) {
        $this->mailer_configurator = $mailer_configurator;
        $this->modulesSettings = $modulesSettings;
    }

    public function onFormSubmitted(FormBuilderSubmittedEvent $event): void
    {
        $form = $event->getForm();
        $fieldData = $this->getEmailFields($event->getData());

        $mailer = new Mailer($this->mailer_configurator->getTransport());

        // need to send mail
        if ($form['method'] === 'database_email' || $form['method'] === 'email') {
            $mailer->send($this->getMessage($form, $fieldData, $form['email_subject']));
        }

        // check if we need to send confirmation mails
        foreach ($form['fields'] as $field) {
            if (array_key_exists('send_confirmation_mail_to', $field['settings']) &&
                $field['settings']['send_confirmation_mail_to'] === true
            ) {
                $to = $fieldData[$field['id']]['value'];
                $from = FrontendModel::get('fork.settings')->get('Core', 'mailer_from');
                $replyTo = FrontendModel::get('fork.settings')->get('Core', 'mailer_reply_to');
                $message = Message::newInstance($field['settings']['confirmation_mail_subject'])
                    ->from(new Address($from['email'], $from['name']))
                    ->to($to)
                    ->replyTo(new Address($replyTo['email'], $replyTo['name']))
                    ->parseHtml(
                        '/Core/Layout/Templates/Mails/Notification.html.twig',
                        ['message' => $field['settings']['confirmation_mail_message']],
                        true
                    )
                ;
                $mailer->send($message);
            }
        }
    }

    /**
     * @param array $form
     * @param array $fieldData
     * @param string|null $subject
     * @param string|array|null $to
     * @param bool $isConfirmationMail
     *
     * @return RawMessage
     */
    private function getMessage(
        array  $form,
        array  $fieldData,
        string $subject = null,
        $to = null,
        bool   $isConfirmationMail = false
    ): RawMessage {
        if ($subject === null) {
            $subject = Language::getMessage('FormBuilderSubject');
        }

        $from = $this->modulesSettings->get('Core', 'mailer_from');

        $message = Message::newInstance(sprintf($subject, $form['name']))
            ->parseHtml(
                '/FormBuilder/Layout/Templates/Mails/' . $form['email_template'],
                [
                    'subject' => $subject,
                    'sentOn' => time(),
                    'name' => $form['name'],
                    'fields' => array_map(
                        function (array $field): array {
                            $field['value'] = html_entity_decode($field['value']);

                            return $field;
                        },
                        $fieldData
                    ),
                    'is_confirmation_mail' => $isConfirmationMail,
                ],
                true
            )
            ->from(new Address($from['email'], $from['name']))
        ;

        if ($to === null) {
            foreach ($form['email'] as $recipient) {
                $message->addTo(new Address($recipient));
            }
        } else {
            $message->to($to);
        }

        // check if we have a replyTo email set
        foreach ($form['fields'] as $field) {
            if (array_key_exists('reply_to', $field['settings']) &&
                $field['settings']['reply_to'] === true
            ) {
                $email = $fieldData[$field['id']]['value'];
                $message->replyTo($email);
            }
        }
        if (empty($message->getReplyTo())) {
            $replyTo = $this->modulesSettings->get('Core', 'mailer_reply_to');
            $message->replyTo(new Address($replyTo['email'], $replyTo['name']));
        }

        return $message;
    }

    /**
     * Converts the data to make sure it is nicely usable in the email
     *
     * @param array $data
     *
     * @return array
     */
    protected function getEmailFields(array $data): array
    {
        return array_map(
            function ($item): array {
                $value = unserialize($item['value'], ['allowed_classes' => false]);

                return [
                    'label' => $item['label'],
                    'value' => (
                    is_array($value)
                        ? implode(',', $value)
                        : nl2br($value)
                    ),
                ];
            },
            $data
        );
    }
}
