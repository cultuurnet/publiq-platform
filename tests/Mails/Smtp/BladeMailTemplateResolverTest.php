<?php

declare(strict_types=1);

namespace Tests\Mails\Smtp;

use App\Domain\Integrations\IntegrationType;
use App\Mails\Smtp\BladeMailTemplateResolver;
use App\Mails\Template\MailTemplate;
use App\Mails\Template\TemplateName;
use Illuminate\Contracts\View\Factory as ViewFactory;
use Illuminate\Contracts\View\View;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

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
                new MailTemplate(TemplateName::INTEGRATION_ACTIVATED, IntegrationType::EntryApi),
                ['integrationName' => 'Mijn Integratie'],
                'Je integratie Mijn Integratie is geactiveerd!',
            ],
            'missing variable fallback' => [
                new MailTemplate(TemplateName::ORGANISATION_UITPAS_APPROVED, IntegrationType::UiTPAS),
                ['integrationName' => 'ABC'],
                'Je integratie ABC voor {{ $organizerName }} is geactiveerd!',
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
            new MailTemplate(TemplateName::INTEGRATION_ACTIVATED, IntegrationType::EntryApi),
            ['foo' => 'bar']
        );

        $this->assertSame('<html>content</html>', $output);
    }

    public function testRenderUsesSpecificTemplateIfExists(): void
    {
        $template = new MailTemplate(
            TemplateName::INTEGRATION_ACTIVATED,
            IntegrationType::EntryApi
        );
        $variables = ['foo' => 'bar'];
        $specificTemplate = 'mails.entry-api.integration.activated';

        $view = $this->createMock(View::class);
        $view->expects($this->once())
            ->method('render')
            ->willReturn('<html>specific</html>');

        $this->viewFactory
            ->expects($this->once())
            ->method('exists')
            ->with($specificTemplate)
            ->willReturn(true);

        $this->viewFactory
            ->expects($this->once())
            ->method('make')
            ->with($specificTemplate, $variables)
            ->willReturn($view);

        $output = $this->resolver->render($template, $variables);

        $this->assertSame('<html>specific</html>', $output);
    }

    public function testRenderUsesGenericTemplateIfSpecificDoesNotExist(): void
    {
        $template = new MailTemplate(
            TemplateName::INTEGRATION_ACTIVATED,
            IntegrationType::EntryApi
        );
        $variables = ['foo' => 'bar'];
        $specificTemplate = 'mails.entry-api.integration.activated';
        $genericTemplate = 'mails.integration.activated';

        $view = $this->createMock(View::class);
        $view->expects($this->once())
            ->method('render')
            ->willReturn('<html>generic</html>');

        $this->viewFactory
            ->expects($this->once())
            ->method('exists')
            ->with($specificTemplate)
            ->willReturn(false);

        $this->viewFactory
            ->expects($this->once())
            ->method('make')
            ->with($genericTemplate, $variables)
            ->willReturn($view);

        $output = $this->resolver->render($template, $variables);

        $this->assertSame('<html>generic</html>', $output);
    }
}
