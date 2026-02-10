<?php

namespace Tests\Unit\Services;

use App\Models\ActivityLog;
use App\Models\User;
use App\Services\ActivityLogService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Log;
use Tests\TestCase;

class ActivityLogServiceTest extends TestCase
{
    use RefreshDatabase;

    // ─── Database Logging ───────────────────────────────────────────

    public function test_log_creates_database_entry(): void
    {
        $log = ActivityLogService::log('test_action', 'TestEntity', 42, ['key' => 'value']);

        $this->assertInstanceOf(ActivityLog::class, $log);
        $this->assertDatabaseHas('activity_logs', [
            'action' => 'test_action',
            'entity_type' => 'TestEntity',
            'entity_id' => 42,
        ]);
    }

    public function test_log_stores_details_as_json(): void
    {
        $details = ['pizza_name' => 'Margherita', 'quantity' => 3];
        $log = ActivityLogService::log('cart_add', 'CartItem', 1, $details);

        $this->assertEquals($details, $log->details);
    }

    public function test_log_stores_session_id(): void
    {
        $log = ActivityLogService::log('test_action');

        $this->assertNotNull($log->session_id);
    }

    public function test_log_stores_user_id_when_authenticated(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $log = ActivityLogService::log('user_action', 'Order', 5);

        $this->assertEquals($user->id, $log->user_id);
    }

    public function test_log_stores_null_user_id_for_guests(): void
    {
        $log = ActivityLogService::log('guest_action');

        $this->assertNull($log->user_id);
    }

    public function test_log_with_nullable_entity(): void
    {
        $log = ActivityLogService::log('cart_cleared', null, null, ['items_removed' => 3]);

        $this->assertNull($log->entity_type);
        $this->assertNull($log->entity_id);
        $this->assertEquals(['items_removed' => 3], $log->details);
    }

    // ─── File Logging ───────────────────────────────────────────────

    public function test_log_writes_to_activity_channel(): void
    {
        Log::shouldReceive('channel')
            ->with('activity')
            ->once()
            ->andReturnSelf();

        Log::shouldReceive('info')
            ->once()
            ->withArgs(function ($action, $context) {
                return $action === 'file_log_test'
                    && isset($context['entity_type'])
                    && $context['entity_type'] === 'Pizza';
            });

        ActivityLogService::log('file_log_test', 'Pizza', 1, ['name' => 'Test']);
    }

    // ─── Model Relationship ─────────────────────────────────────────

    public function test_activity_log_belongs_to_user(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $log = ActivityLogService::log('relationship_test');

        $this->assertNotNull($log->user);
        $this->assertEquals($user->id, $log->user->id);
    }
}
