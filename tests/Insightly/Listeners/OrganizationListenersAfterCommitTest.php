<?php

declare(strict_types=1);

namespace Tests\Insightly\Listeners;

use App\Domain\Organizations\Address;
use App\Domain\Organizations\Events\OrganizationCreated;
use App\Domain\Organizations\Events\OrganizationDeleted;
use App\Domain\Organizations\Events\OrganizationUpdated;
use App\Domain\Organizations\Models\OrganizationModel;
use App\Domain\Organizations\Organization;
use App\Domain\Organizations\Repositories\OrganizationRepository;
use App\Insightly\Listeners\CreateOrganization;
use App\Insightly\Listeners\DeleteOrganization;
use App\Insightly\Listeners\UpdateOrganization;
use Illuminate\Events\CallQueuedListener;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Queue\Events\JobQueued;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Event;
use Ramsey\Uuid\Uuid;
use RuntimeException;
use Tests\TestCase;

final class OrganizationListenersAfterCommitTest extends TestCase
{
    use RefreshDatabase;

    private OrganizationRepository $organizationRepository;

    /** @var string[] */
    private array $queuedListeners = [];

    protected function setUp(): void
    {
        parent::setUp();

        Event::fakeExcept([
            'eloquent.created: ' . OrganizationModel::class,
            'eloquent.updated: ' . OrganizationModel::class,
            'eloquent.deleted: ' . OrganizationModel::class,
            OrganizationCreated::class,
            OrganizationUpdated::class,
            OrganizationDeleted::class,
            JobQueued::class,
        ]);
        Event::listen(OrganizationCreated::class, [CreateOrganization::class, 'handle']);
        Event::listen(OrganizationUpdated::class, [UpdateOrganization::class, 'handle']);
        Event::listen(OrganizationDeleted::class, [DeleteOrganization::class, 'handle']);

        Event::listen(JobQueued::class, function (JobQueued $event): void {
            if ($event->job instanceof CallQueuedListener) {
                $this->queuedListeners[] = $event->job->displayName();
            }
        });

        config(['queue.default' => 'database']);

        $this->organizationRepository = $this->app->get(OrganizationRepository::class);
    }

    public function test_it_queues_the_listeners_after_the_transaction_commits(): void
    {
        $organization = $this->givenAnOrganization();

        DB::transaction(function () use ($organization): void {
            $this->organizationRepository->save($organization);

            $this->assertSame([], $this->queuedListeners);
        });

        $this->assertSame([CreateOrganization::class], $this->queuedListeners);
    }

    public function test_it_does_not_queue_the_listeners_when_the_transaction_rolls_back(): void
    {
        $existingOrganization = $this->givenAnOrganization();
        $this->organizationRepository->save($existingOrganization);
        $this->queuedListeners = [];

        try {
            DB::transaction(function () use ($existingOrganization): void {
                $this->organizationRepository->save($this->givenAnOrganization());
                $this->organizationRepository->save(new Organization(
                    $existingOrganization->id,
                    'Updated name',
                    $existingOrganization->invoiceEmail,
                    $existingOrganization->vat,
                    $existingOrganization->address
                ));
                OrganizationModel::query()->findOrFail($existingOrganization->id->toString())->delete();

                throw new RuntimeException('Rollback');
            });
        } catch (RuntimeException) {
        }

        $this->assertSame([], $this->queuedListeners);
    }

    private function givenAnOrganization(): Organization
    {
        return new Organization(
            Uuid::uuid4(),
            'publiq vzw',
            'facturatie@publiq.be',
            'BE 0475 250 609',
            new Address('Henegouwenkaai 41-43', '1080', 'Brussel', 'Belgium')
        );
    }
}
