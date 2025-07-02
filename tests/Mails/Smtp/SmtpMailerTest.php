<?php

declare(strict_types=1);

namespace Tests\Mails\Smtp;

use App\Mails\Smtp\MailTemplate;
use App\Mails\Smtp\MailerTemplateResolver;
use App\Mails\Smtp\SmtpMailer;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;
use Symfony\Component\Mailer\Exception\TransportException;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Address;
use Symfony\Component\Mime\Email;

final class SmtpMailerTest extends TestCase
{
    private MailerInterface&MockObject $mailer;
    private MailerTemplateResolver&MockObject $mailerTemplateResolver;
    private LoggerInterface&MockObject $logger;
    private SmtpMailer $smtpMailer;

    protected function setUp(): void
    {
        $this->mailer = $this->createMock(MailerInterface::class);
        $this->mailerTemplateResolver = $this->createMock(MailerTemplateResolver::class);
        $this->logger = $this->createMock(LoggerInterface::class);

        $this->smtpMailer = new SmtpMailer(
            $this->mailer,
            $this->mailerTemplateResolver,
            $this->logger
        );
    }

    public function testMailSendSuccessful(): void
    {
        $from = new Address('from@publiq.be');
        $to = new Address('to@publiq.be');
        $templateId = 0;
        $subject = 'Welkom bij Publiq';
        $html = '<p>Hello</p>';

        $this->mailerTemplateResolver
            ->expects($this->once())
            ->method('getSubject')
            ->with(MailTemplate::from($templateId))
            ->willReturn($subject);

        $this->mailerTemplateResolver
            ->expects($this->once())
            ->method('render')
            ->with(MailTemplate::from($templateId), $this->arrayHasKey('subject'))
            ->willReturn($html);

        $this->mailer
            ->expects($this->once())
            ->method('send')
            ->with($this->callback(function (Email $email) use ($from, $to, $subject, $html) {
                return $email->getFrom()[0]->getAddress() === $from->getAddress()
                    && $email->getTo()[0]->getAddress() === $to->getAddress()
                    && $email->getSubject() === $subject
                    && $email->getHtmlBody() === $html;
            }));

        $this->logger
            ->expects($this->once())
            ->method('info')
            ->with(sprintf('Sent mail "%s" to %s', $subject, $to->toString()));

        $this->smtpMailer->send($from, $to, $templateId);
    }

    public function testMailFailedToSendWithTransportException(): void
    {
        $from = new Address('from@publiq.be');
        $to = new Address('to@publiq.be');
        $templateId = 1;
        $subject = 'Transport Error';
        $html = '<p>Oops</p>';

        $this->mailerTemplateResolver
            ->method('getSubject')
            ->willReturn($subject);
        $this->mailerTemplateResolver
            ->method('render')
            ->willReturn($html);

        $this->mailer
            ->method('send')
            ->willThrowException(new TransportException('Transport failed'));

        $this->logger
            ->expects($this->once())
            ->method('critical')
            ->with(sprintf('[TransportException] Failed to sent "%s" to %s: %s', $subject, $to->toString(), 'Transport failed'));

        $this->smtpMailer->send($from, $to, $templateId);
    }

    public function testMailFailedToSendWithGenericThrowable(): void
    {
        $from = new Address('from@publiq.be');
        $to = new Address('to@publiq.be');
        $templateId = 2;
        $subject = 'Throwable Error';
        $html = '<p>Oops</p>';

        $this->mailerTemplateResolver
            ->method('getSubject')
            ->willReturn($subject);
        $this->mailerTemplateResolver
            ->method('render')
            ->willReturn($html);

        $this->mailer
            ->method('send')
            ->willThrowException(new \DomainException('something broke'));

        $this->logger
            ->expects($this->once())
            ->method('critical')
            ->with(sprintf('[Error] Failed to sent "%s" to %s: %s', $subject, $to->toString(), 'something broke'));

        $this->smtpMailer->send($from, $to, $templateId);
    }
}
