<?php

declare(strict_types=1);

namespace App\Mails\Smtp;

use App\Domain\Mail\Mailer;
use Psr\Log\LoggerInterface;
use Symfony\Component\Mailer\Exception\TransportExceptionInterface;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Address;
use Symfony\Component\Mime\Email;
use Throwable;

final readonly class SmtpMailer implements Mailer
{
    public function __construct(private MailerInterface $mailer, private MailerTemplateResolver $mailerTemplateResolver, private LoggerInterface $logger)
    {
    }

    public function send(Address $from, Address $to, int $templateId, array $variables = []): void
    {
        $subject = $this->mailerTemplateResolver->getSubject(MailTemplate::from($templateId), $variables);
        $variables['subject'] = $subject;

        try {
            $email = (new Email())
                ->from($from)
                ->to($to->toString())
                ->subject($subject)
                ->html($this->mailerTemplateResolver->render(MailTemplate::from($templateId), $variables));

            $this->mailer->send($email);

            $this->logger->info(sprintf('Sent mail "%s" to %s', $subject, $to->toString()));
        } catch (TransportExceptionInterface $e) {
            $this->logger->critical(sprintf('[TransportException] Failed to sent "%s" to %s: %s', $subject, $to->toString(), $e->getMessage()));
        } catch (Throwable $e) {
            $this->logger->critical(sprintf('[Error] Failed to sent "%s" to %s: %s', $subject, $to->toString(), $e->getMessage()));
        }
    }
}
