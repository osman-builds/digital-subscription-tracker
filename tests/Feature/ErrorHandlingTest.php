<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Route;
use RuntimeException;
use Tests\TestCase;

class ErrorHandlingTest extends TestCase
{
    use RefreshDatabase;

    public function test_missing_web_route_renders_branded_404_without_leaking_route_details(): void
    {
        $response = $this->get('/nonexistent-page-check-404');

        $response->assertNotFound();
        $response->assertSee('ERROR 404');
        $response->assertSee('Page Not Found');
        $response->assertSee('Return to SubShield');
        $response->assertDontSee('The route');
        $response->assertDontSee('Symfony');
    }

    public function test_missing_api_route_returns_generic_json_without_route_echo(): void
    {
        $response = $this->getJson('/api/nonexistent-endpoint');

        $response->assertNotFound();
        $response->assertExactJson(['message' => 'Not found.']);
    }

    public function test_unsupported_http_method_does_not_leak_supported_methods(): void
    {
        $response = $this->get('/logout');

        $response->assertStatus(405);
        $response->assertDontSee('Supported methods');
        $response->assertDontSee('MethodNotAllowedHttpException');

        $jsonResponse = $this->getJson('/logout');
        $jsonResponse->assertStatus(405);
        $jsonResponse->assertExactJson(['message' => 'Method not allowed.']);
    }

    public function test_forbidden_action_renders_branded_403_without_internal_reason(): void
    {
        $user = User::factory()->create();
        $systemCategory = Category::create([
            'user_id' => null,
            'name' => 'System Test',
            'color' => '#112233',
        ]);

        $response = $this->actingAs($user)->delete(route('categories.destroy', $systemCategory));

        $response->assertForbidden();
        $response->assertSee('ERROR 403');
        $response->assertSee('Access Denied');
        $response->assertDontSee('System categories cannot be deleted.');
    }

    public function test_unhandled_server_exception_never_leaks_trace_or_message_when_debug_is_disabled(): void
    {
        config(['app.debug' => false]);

        Route::get('/_test-internal-error', function (): void {
            throw new RuntimeException('SENSITIVE_INTERNAL_DB_FAILURE_TOKEN');
        });

        $htmlResponse = $this->get('/_test-internal-error');
        $htmlResponse->assertStatus(500);
        $htmlResponse->assertSee('ERROR 500');
        $htmlResponse->assertSee('Something Went Wrong');
        $htmlResponse->assertDontSee('SENSITIVE_INTERNAL_DB_FAILURE_TOKEN');
        $htmlResponse->assertDontSee('RuntimeException');

        $jsonResponse = $this->getJson('/_test-internal-error');
        $jsonResponse->assertStatus(500);
        $jsonResponse->assertExactJson(['message' => 'Something went wrong.']);
    }

    public function test_auth_pages_use_bootstrap_and_do_not_reference_vite_or_breeze_assets(): void
    {
        $user = User::factory()->create();

        $forgotResponse = $this->get(route('password.request'));
        $forgotResponse->assertOk();
        $forgotResponse->assertSee('bootstrap@5.3.3');
        $forgotResponse->assertDontSee('/build/assets');

        $confirmResponse = $this->actingAs($user)->get(route('password.confirm'));
        $confirmResponse->assertOk();
        $confirmResponse->assertSee('bootstrap@5.3.3');
        $confirmResponse->assertDontSee('/build/assets');
    }
}
