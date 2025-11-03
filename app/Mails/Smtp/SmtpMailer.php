<?php

declare(strict_types=1);

namespace App\Mails\Smtp;

use App\Domain\Mail\Mailer;
use App\Mails\Template\MailTemplate;
use Psr\Log\LoggerInterface;
use Symfony\Component\Mailer\Exception\TransportExceptionInterface;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Address;
use Symfony\Component\Mime\Email;
use Throwable;

final readonly class SmtpMailer implements Mailer
{
    public function __construct(private MailerInterface $mailer, private MailTemplateResolver $mailerTemplateResolver, private LoggerInterface $logger)
    {
    }

    public function send(Address $from, Address $to, MailTemplate $mailTemplate, array $variables = []): void
    {
        $subject = $this->mailerTemplateResolver->getSubject($mailTemplate, $variables);
        $variables['subject'] = $subject;

        try {
            $email = (new Email())
                ->from($from)
                ->to($to->toString())
                ->subject($subject)
                ->html($this->mailerTemplateResolver->render($mailTemplate, $variables));

            $this->mailer->send($email);

            $this->logger->info(sprintf('Sent mail "%s" to %s', $subject, $to->toString()));
        } catch (TransportExceptionInterface $e) {
            $this->logger->error(sprintf('[TransportException] Failed to sent "%s" to %s: %s', $subject, $to->toString(), $e->getMessage()));
        } catch (Throwable $e) {
            $this->logger->error(sprintf('[Error] Failed to sent "%s" to %s: %s', $subject, $to->toString(), $e->getMessage()));
        }
    }
}
