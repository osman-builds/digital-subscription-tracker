<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Subscription;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CategoryTest extends TestCase
{
    use RefreshDatabase;

    private const COLOR_PRIMARY = '#112233';

    private const COLOR_SECONDARY = '#223344';

    public function test_users_can_view_system_categories_and_their_own(): void
    {
        $userA = User::factory()->create();
        $userB = User::factory()->create();

        Category::create(['name' => 'System Streaming', 'color' => self::COLOR_PRIMARY, 'user_id' => null]);
        Category::create(['name' => 'Custom A', 'color' => self::COLOR_SECONDARY, 'user_id' => $userA->id]);
        Category::create(['name' => 'Custom B', 'color' => '#334455', 'user_id' => $userB->id]);

        $response = $this->actingAs($userA)->get(route('categories.index'));

        $response->assertOk();
        $response->assertSee('System Streaming');
        $response->assertSee('Custom A');
        $response->assertDontSee('Custom B');
    }

    public function test_category_subscription_count_only_counts_authenticated_users_subscriptions(): void
    {
        $userA = User::factory()->create();
        $userB = User::factory()->create();

        $category = Category::create(['name' => 'Music', 'color' => '#123456', 'user_id' => null]);

        // 1 subscription for user A
        Subscription::create([
            'user_id' => $userA->id,
            'category_id' => $category->id,
            'name' => 'Spotify A',
            'amount' => 10,
            'currency' => 'MYR',
            'amount_myr' => 10,
            'billing_cycle' => 'monthly',
            'start_date' => now()->toDateString(),
            'next_billing_date' => now()->addMonth()->toDateString(),
            'status' => 'active',
        ]);

        // 2 subscriptions for user B
        Subscription::create([
            'user_id' => $userB->id,
            'category_id' => $category->id,
            'name' => 'Spotify B1',
            'amount' => 15,
            'currency' => 'MYR',
            'amount_myr' => 15,
            'billing_cycle' => 'monthly',
            'start_date' => now()->toDateString(),
            'next_billing_date' => now()->addMonth()->toDateString(),
            'status' => 'active',
        ]);

        Subscription::create([
            'user_id' => $userB->id,
            'category_id' => $category->id,
            'name' => 'Spotify B2',
            'amount' => 25,
            'currency' => 'MYR',
            'amount_myr' => 25,
            'billing_cycle' => 'monthly',
            'start_date' => now()->toDateString(),
            'next_billing_date' => now()->addMonth()->toDateString(),
            'status' => 'active',
        ]);

        // User A should see count = 1
        $responseA = $this->actingAs($userA)->get(route('categories.index'));
        $responseA->assertOk();
        $responseA->assertSee('1 subscription');

        // User B should see count = 2
        $responseB = $this->actingAs($userB)->get(route('categories.index'));
        $responseB->assertOk();
        $responseB->assertSee('2 subscriptions');
    }

    public function test_users_can_create_custom_category(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->post(route('categories.store'), [
            'name' => 'Gym & Fitness',
            'color' => '#10b981',
        ]);

        $response->assertRedirect(route('categories.index'));
        $this->assertDatabaseHas('categories', [
            'name' => 'Gym & Fitness',
            'color' => '#10b981',
            'user_id' => $user->id,
        ]);
    }

    public function test_system_categories_cannot_be_updated_or_deleted(): void
    {
        $user = User::factory()->create();
        $systemCat = Category::create(['name' => 'System Category', 'color' => self::COLOR_PRIMARY, 'user_id' => null]);

        $updateResponse = $this->actingAs($user)->put(route('categories.update', $systemCat), [
            'name' => 'Hacked Name',
            'color' => '#999999',
        ]);
        $updateResponse->assertForbidden();

        $deleteResponse = $this->actingAs($user)->delete(route('categories.destroy', $systemCat));
        $deleteResponse->assertForbidden();

        $this->assertDatabaseHas('categories', ['id' => $systemCat->id, 'name' => 'System Category']);
    }

    public function test_users_cannot_update_or_delete_other_users_categories(): void
    {
        $userA = User::factory()->create();
        $userB = User::factory()->create();

        $catB = Category::create(['name' => 'Private B', 'color' => self::COLOR_PRIMARY, 'user_id' => $userB->id]);

        $updateResponse = $this->actingAs($userA)->put(route('categories.update', $catB), [
            'name' => 'Tampered',
            'color' => '#999999',
        ]);
        $updateResponse->assertForbidden();

        $deleteResponse = $this->actingAs($userA)->delete(route('categories.destroy', $catB));
        $deleteResponse->assertForbidden();

        $this->assertDatabaseHas('categories', ['id' => $catB->id, 'name' => 'Private B']);
    }

    public function test_users_can_update_and_delete_their_own_custom_category(): void
    {
        $user = User::factory()->create();
        $cat = Category::create(['name' => 'My Cat', 'color' => self::COLOR_PRIMARY, 'user_id' => $user->id]);

        $updateResponse = $this->actingAs($user)->put(route('categories.update', $cat), [
            'name' => 'Updated Cat',
            'color' => self::COLOR_SECONDARY,
        ]);
        $updateResponse->assertRedirect(route('categories.index'));
        $this->assertDatabaseHas('categories', ['id' => $cat->id, 'name' => 'Updated Cat', 'color' => self::COLOR_SECONDARY]);

        $deleteResponse = $this->actingAs($user)->delete(route('categories.destroy', $cat));
        $deleteResponse->assertRedirect(route('categories.index'));
        $this->assertDatabaseMissing('categories', ['id' => $cat->id]);
    }
}
