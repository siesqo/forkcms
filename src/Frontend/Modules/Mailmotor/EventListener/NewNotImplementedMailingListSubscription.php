<?php

namespace Frontend\Modules\Mailmotor\EventListener;

use Common\Language;
use Common\Mailer\Configurator;
use Common\Mailer\Message;
use Frontend\Modules\Mailmotor\Domain\Subscription\Event\NotImplementedSubscribedEvent;
use Common\ModulesSettings;
use Symfony\Component\Mailer\Mailer;
use Symfony\Component\Mime\Address;

/**
 * New mailing list subscription
 *
 * This will send a mail to the administrator
 * to let them know that they have to manually subscribe a person.
 * Because the mail engine is "not_implemented".
 */
final class NewNotImplementedMailingListSubscription
{
    /**
     * @var ModulesSettings
     */
    private $modulesSettings;

    /**
     * @var Configurator
     */
    private $mailer_configurator;

    public function __construct(Configurator $mailer_configurator, ModulesSettings $modulesSettings)
    {
        $this->mailer_configurator = $mailer_configurator;
        $this->modulesSettings = $modulesSettings;
    }

    public function onNotImplementedSubscribedEvent(NotImplementedSubscribedEvent $event): void
    {
        $title = sprintf(
            Language::lbl('MailTitleSubscribeSubscriber'),
            $event->getSubscription()->email,
            strtoupper((string) $event->getSubscription()->locale)
        );

        $to = $this->modulesSettings->get('Core', 'mailer_to');
        $from = $this->modulesSettings->get('Core', 'mailer_from');
        $replyTo = $this->modulesSettings->get('Core', 'mailer_reply_to');

        $message = Message::newInstance($title)
            ->from(new Address($from['email'], $from['name']))
            ->to(new Address($to['email'], $to['name']))
            ->replyTo(new Address($replyTo['email'], $replyTo['name']))
            ->parseHtml(
                FRONTEND_CORE_PATH . '/Layout/Templates/Mails/Notification.html.twig',
                [
                    'message' => $title,
                ],
                true
            )
        ;

        $mailer = new Mailer($this->mailer_configurator->getTransport());
        $mailer->send($message);
    }
}
