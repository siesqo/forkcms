<?php

namespace Backend\Modules\Settings\Ajax;

use Backend\Core\Engine\Base\AjaxAction as BackendBaseAJAXAction;
use Backend\Core\Language\Language as BL;
use Common\Mailer\TransportFactory;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Mailer\Exception\TransportExceptionInterface;
use Symfony\Component\Mailer\Mailer;
use Symfony\Component\Mime\Address;
use Symfony\Component\Mime\Email;

/**
 * This test-email-action will test the mail-connection
 */
class TestEmailConnection extends BackendBaseAJAXAction
{
    public function execute(): void
    {
        parent::execute();

        $fromEmail = $this->getRequest()->request->get('mailer_from_email', '');
        $fromName = $this->getRequest()->request->get('mailer_from_name', '');
        $toEmail = $this->getRequest()->request->get('mailer_to_email', '');
        $toName = $this->getRequest()->request->get('mailer_to_name', '');
        $replyToEmail = $this->getRequest()->request->get('mailer_reply_to_email', '');
        $replyToName = $this->getRequest()->request->get('mailer_reply_to_name', '');

        // init validation
        $errors = [];

        // validate
        if (!filter_var($fromEmail, FILTER_VALIDATE_EMAIL)) {
            $errors['from'] = BL::err('EmailIsInvalid');
        }
        if (!filter_var($toEmail, FILTER_VALIDATE_EMAIL)) {
            $errors['to'] = BL::err('EmailIsInvalid');
        }
        if (!filter_var($replyToEmail, FILTER_VALIDATE_EMAIL)) {
            $errors['reply'] = BL::err('EmailIsInvalid');
        }

        // got errors?
        if (!empty($errors)) {
            $this->output(
                Response::HTTP_BAD_REQUEST,
                ['errors' => $errors],
                'invalid fields'
            );

            return;
        }

        // Create the email message
        $message = (new Email())
            ->from(new Address($fromEmail, $fromName))
            ->to(new Address($toEmail, $toName))
            ->replyTo(new Address($replyToEmail, $replyToName))
            ->subject('Test')
            ->text(BL::msg('TestMessage'));

        $mailerType = $this->getRequest()->request->get('mailer_type');
        if (!in_array($mailerType, ['smtp', 'sendmail'])) {
            $mailerType = 'sendmail';
        }
        $transport = TransportFactory::create(
            $mailerType,
            $this->getRequest()->request->get('smtp_server', ''),
            $this->getRequest()->request->getInt('smtp_port', 25),
            $this->getRequest()->request->get('smtp_username', ''),
            $this->getRequest()->request->get('smtp_password', ''),
            $this->getRequest()->request->get('smtp_secure_layer', '')
        );
        $mailer = new Mailer($transport);

        try {
            $mailer->send($message);
            $this->output(Response::HTTP_OK, null, '');

            return;
        } catch (TransportExceptionInterface $e) {
            $this->output(Response::HTTP_INTERNAL_SERVER_ERROR, null, $e->getMessage());
        } catch (\Exception $e) {
            $this->output(Response::HTTP_INTERNAL_SERVER_ERROR, null, $e->getMessage());
        }
    }
}
