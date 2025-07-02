<?php

declare(strict_types=1);

namespace Tests\Mails\Smtp;

use App\Mails\Smtp\BladeMailTemplateResolver;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Illuminate\Contracts\View\Factory as ViewFactory;
use App\Mails\Smtp\MailTemplate;
use Illuminate\Contracts\View\View;
use PHPUnit\Framework\MockObject\MockObject;

final class BladeMailTemplateResolverTest extends TestCase
{
    private ViewFactory&MockObject $viewFactory;
    private BladeMailTemplateResolver $resolver;

    protected function setUp(): void
    {
        $this->viewFactory = $this->createMock(ViewFactory::class);
        $this->resolver = new BladeMailTemplateResolver($this->viewFactory);
    }

    #[DataProvider('subjectProvider')]
    public function testGetSubjectInterpolateCorrectly(MailTemplate $template, array $variables, string $expected): void
    {
        $this->assertSame($expected, $this->resolver->getSubject($template, $variables));
    }

    public static function subjectProvider(): array
    {
        return [
            'integration activated' => [
                MailTemplate::INTEGRATION_ACTIVATED,
                ['integrationName' => 'Mijn Integratie'],
                'Je integratie Mijn Integratie is geactiveerd',
            ],
            'uitpas requested' => [
                MailTemplate::ORGANISATION_UITPAS_REQUESTED,
                ['integrationName' => 'XYZ', 'organizerName' => 'De Roma'],
                'Activatieaanvraag met integratie XYZ voor De Roma',
            ],
            'uitpas approved' => [
                MailTemplate::ORGANISATION_UITPAS_APPROVED,
                ['integrationName' => 'XYZ', 'organizerName' => 'KVS'],
                'Je integratie XYZ voor KVS is geactiveerd',
            ],
            'uitpas rejected' => [
                MailTemplate::ORGANISATION_UITPAS_REJECTED,
                ['integrationName' => 'XYZ', 'organizerName' => 'KVS'],
                'Je integratie XYZ voor KVS is afgekeurd',
            ],
            'missing variable fallback' => [
                MailTemplate::ORGANISATION_UITPAS_APPROVED,
                ['integrationName' => 'ABC'],
                'Je integratie ABC voor {{ $organizerName }} is geactiveerd',
            ],
        ];
    }

    public function testRenderReturnsViewHtml(): void
    {
        $view = $this->createMock(View::class);
        $view->expects($this->once())
            ->method('render')
            ->willReturn('<html>content</html>');

        $this->viewFactory
            ->expects($this->once())
            ->method('make')
            ->with('mails.integration.activated', ['foo' => 'bar'])
            ->willReturn($view);

        $output = $this->resolver->render(
            MailTemplate::INTEGRATION_ACTIVATED,
            ['foo' => 'bar']
        );

        $this->assertSame('<html>content</html>', $output);
    }
}
