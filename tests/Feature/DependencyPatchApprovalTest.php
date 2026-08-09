<?php

namespace Tests\Feature;

use App\Jobs\ApplyDependencyPatchJob;
use App\Models\DependencyPatchProposal;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Queue;
use Tests\TestCase;

class DependencyPatchApprovalTest extends TestCase
{
    use RefreshDatabase;

    private function operator(): User
    {
        $operator = User::factory()->create();
        $operator->forceFill(['is_platform_operator' => true])->save();

        return $operator;
    }

    private function pendingProposal(): DependencyPatchProposal
    {
        return DependencyPatchProposal::create([
            'manager' => 'composer',
            'advisories' => [['package' => 'league/commonmark', 'severity' => 'moderate', 'title' => 'x', 'identifier' => 'y']],
            'risk_summary' => '1 advisory: 1 moderate',
            'proposed_command' => 'composer update --with-dependencies',
        ]);
    }

    public function test_operator_can_view_pending_proposals(): void
    {
        $proposal = $this->pendingProposal();

        $this->actingAs($this->operator())
            ->get(route('operator.dependency-patches.index'))
            ->assertOk();
    }

    public function test_approving_dispatches_the_apply_job(): void
    {
        Queue::fake();
        $proposal = $this->pendingProposal();
        $operator = $this->operator();

        $this->actingAs($operator)
            ->post(route('operator.dependency-patches.approve', $proposal))
            ->assertRedirect();

        $fresh = $proposal->fresh();
        $this->assertSame('approved', $fresh->status);
        $this->assertSame($operator->id, $fresh->reviewed_by);
        Queue::assertPushed(ApplyDependencyPatchJob::class, fn ($job) => $job->proposal->is($fresh));
    }

    public function test_rejecting_without_a_reason_is_blocked(): void
    {
        Queue::fake();
        $proposal = $this->pendingProposal();

        $this->actingAs($this->operator())
            ->post(route('operator.dependency-patches.reject', $proposal), [])
            ->assertSessionHasErrors('reason');

        $this->assertSame('pending_approval', $proposal->fresh()->status);
        Queue::assertNotPushed(ApplyDependencyPatchJob::class);
    }

    public function test_rejecting_with_a_reason_marks_it_rejected(): void
    {
        Queue::fake();
        $proposal = $this->pendingProposal();

        $this->actingAs($this->operator())
            ->post(route('operator.dependency-patches.reject', $proposal), ['reason' => 'Will patch manually next release.'])
            ->assertRedirect();

        $fresh = $proposal->fresh();
        $this->assertSame('rejected', $fresh->status);
        $this->assertSame('Will patch manually next release.', $fresh->rejected_reason);
        Queue::assertNotPushed(ApplyDependencyPatchJob::class);
    }

    public function test_non_operator_is_refused_on_index_and_every_action(): void
    {
        $proposal = $this->pendingProposal();
        $nonOperator = User::factory()->create();

        $this->actingAs($nonOperator)
            ->get(route('operator.dependency-patches.index'))
            ->assertForbidden();

        $this->actingAs($nonOperator)
            ->post(route('operator.dependency-patches.approve', $proposal))
            ->assertForbidden();

        $this->assertSame('pending_approval', $proposal->fresh()->status);
    }

    public function test_approving_an_already_decided_proposal_is_refused(): void
    {
        Queue::fake();
        $proposal = $this->pendingProposal();
        $proposal->update(['status' => 'rejected', 'rejected_reason' => 'Already handled.']);

        $this->actingAs($this->operator())
            ->post(route('operator.dependency-patches.approve', $proposal))
            ->assertSessionHasErrors();

        $this->assertSame('rejected', $proposal->fresh()->status);
        Queue::assertNotPushed(ApplyDependencyPatchJob::class);
    }
}
