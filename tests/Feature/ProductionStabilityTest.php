<?php
namespace Tests\Feature;
use App\Models\User;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Route;
use Illuminate\Http\Request;
use Tests\TestCase;
class ProductionStabilityTest extends TestCase
{
 use RefreshDatabase;
 protected function setUp():void{parent::setUp();$this->seed(RolesAndPermissionsSeeder::class);RateLimiter::clear('test');}
 public function test_health_checks_database_without_exposing_configuration():void{$response=$this->getJson(route('health'))->assertOk()->assertJson(['status'=>'ok','application'=>'RIMIS']);$response->assertJsonMissing(['password'=>config('database.connections.mysql.password')]);$this->assertArrayNotHasKey('database',$response->json());}
 public function test_security_headers_and_private_noindex_are_applied():void{$this->get('/')->assertHeader('X-Content-Type-Options','nosniff')->assertHeader('X-Frame-Options','SAMEORIGIN')->assertHeader('Referrer-Policy','strict-origin-when-cross-origin');$this->get('/login')->assertHeader('X-Robots-Tag','noindex, nofollow');}
 public function test_role_permission_matrix_blocks_privilege_escalation():void{$basic=$this->role('USUARIO');$researcher=$this->role('INVESTIGADOR');$webmaster=$this->role('WEBMASTER');$admin=$this->role('ADMINISTRADOR');$this->assertFalse($basic->can('submissions.view'));$this->assertFalse($basic->can('research-publications.create'));$this->assertTrue($researcher->can('research-publications.create'));$this->assertFalse($researcher->can('submissions.approve'));$this->assertFalse($researcher->can('users.view'));$this->assertTrue($webmaster->can('submissions.approve'));$this->assertFalse($webmaster->can('users.delete'));$this->assertTrue($admin->can('users.delete'));}
 public function test_sensitive_routes_require_authentication_or_public_visibility():void{$this->get('/admin/submissions')->assertRedirect('/login');$this->get('/investigador/publicaciones')->assertRedirect('/login');$this->get('/profile')->assertRedirect('/login');$this->get('/investigaciones/no-existe')->assertNotFound();$this->get('/investigadores/no-existe')->assertNotFound();}
 public function test_critical_routes_have_throttling_and_server_authorization():void{$this->assertContains('throttle:authentication',Route::getRoutes()->match(Request::create('/login','POST'))->gatherMiddleware());$this->assertContains('throttle:public-search',Route::getRoutes()->getByName('researchers.index')->gatherMiddleware());$this->assertContains('auth',Route::getRoutes()->getByName('admin.submissions.index')->gatherMiddleware());$this->assertContains('can:submissions.view',Route::getRoutes()->getByName('admin.submissions.index')->gatherMiddleware());}
 private function role(string $role):User{$u=User::factory()->create();$u->assignRole($role);return $u;}
}
