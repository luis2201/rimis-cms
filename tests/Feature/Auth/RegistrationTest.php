<?php
namespace Tests\Feature\Auth;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
class RegistrationTest extends TestCase
{
    use RefreshDatabase;
    public function test_direct_registration_is_disabled():void{$this->get('/register')->assertNotFound();}
    public function test_subscription_pages_replace_registration():void{$this->get(route('subscriptions.index'))->assertOk()->assertSee('Suscripción profesional')->assertSee('Suscripción institucional');$this->assertGuest();}
}
