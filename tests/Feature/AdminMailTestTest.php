<?php

namespace Tests\Feature;

use App\Mail\MailTest;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Mail\Events\MessageSending;
use Illuminate\Mail\Events\MessageSent;
use Illuminate\Mail\Transport\ArrayTransport;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Mail;
use Mockery;
use Symfony\Component\Mailer\Exception\TransportException;
use Symfony\Component\Mailer\Transport\TransportInterface;
use Tests\TestCase;

class AdminMailTestTest extends TestCase
{
    use RefreshDatabase;

    public function test_super_admin_can_open_the_form_with_their_email_and_safe_configuration_details(): void
    {
        config(['mail.mailers.array.password' => 'do-not-display-this-secret']);
        $admin = User::factory()->create(['role' => 'super_admin']);

        $this->actingAs($admin)->get(route('admin.emailTest'))
            ->assertOk()
            ->assertSee(route('admin.emailTest.send'), false)
            ->assertSee('value="'.$admin->email.'"', false)
            ->assertSee(config('mail.from.address'))
            ->assertSee('array')
            ->assertDontSee('do-not-display-this-secret');
    }

    public function test_super_admin_can_send_an_immediate_test_to_the_selected_recipient(): void
    {
        $transport = $this->useDeliveryTransport();
        Event::fake([MessageSent::class]);
        $this->travelTo(now()->startOfSecond());
        $admin = User::factory()->create(['role' => 'super_admin']);

        $this->actingAs($admin)->post(route('admin.emailTest.send'), ['email' => 'check@example.com'])
            ->assertRedirect(route('admin.emailTest'))
            ->assertSessionHas('success')
            ->assertSessionHasInput('email', 'check@example.com');

        $this->assertCount(1, $transport->messages());
        Event::assertDispatched(MessageSent::class, function (MessageSent $event): bool {
            return $event->message->getTo()[0]->getAddress() === 'check@example.com'
                && $event->message->getFrom()[0]->getAddress() === config('mail.from.address')
                && $event->message->getSubject() === 'FNU Job Placement - Test Email'
                && str_contains($event->message->getHtmlBody(), now()->toIso8601String())
                && str_contains($event->message->getHtmlBody(), 'No action is required.');
        });
    }

    public function test_other_roles_and_guests_cannot_open_the_page_or_send_email(): void
    {
        Mail::fake();

        $this->get(route('admin.emailTest'))->assertRedirect(route('home'));
        $this->post(route('admin.emailTest.send'), ['email' => 'check@example.com'])
            ->assertRedirect(route('home'));

        foreach (['admin', 'management', 'employer', 'student'] as $role) {
            $this->actingAs(User::factory()->create(['role' => $role]));
            $page = $this->get(route('admin.emailTest'));
            $send = $this->post(route('admin.emailTest.send'), ['email' => 'check@example.com']);

            if ($role === 'management') {
                $page->assertForbidden();
                $send->assertForbidden();
            } else {
                $page->assertRedirect();
                $send->assertRedirect();
            }
        }

        Mail::assertNothingSent();
    }

    public function test_sidebar_link_is_only_visible_to_super_admins(): void
    {
        foreach (['super_admin', 'admin', 'management'] as $role) {
            $this->actingAs(User::factory()->create(['role' => $role]));
            $response = $this->get(route('admin.users.students'))->assertOk();

            if ($role === 'super_admin') {
                $response->assertSee(route('admin.emailTest'), false);
            } else {
                $response->assertDontSee(route('admin.emailTest'), false);
            }
        }
    }

    public function test_invalid_or_missing_recipients_do_not_send_email(): void
    {
        Mail::fake();
        $this->actingAs(User::factory()->create(['role' => 'super_admin']));

        foreach ([[], ['email' => 'not-an-email'], ['email' => ['check@example.com']]] as $data) {
            $this->from(route('admin.emailTest'))->post(route('admin.emailTest.send'), $data)
                ->assertRedirect(route('admin.emailTest'))
                ->assertSessionHasErrors('email');
        }

        Mail::assertNothingSent();
    }

    public function test_local_only_mailers_do_not_report_delivery_success(): void
    {
        Mail::fake();
        $this->actingAs(User::factory()->create(['role' => 'super_admin']));

        foreach (['log', 'array'] as $driver) {
            config(['mail.default' => 'local-test', 'mail.mailers.local-test.transport' => $driver]);

            $this->post(route('admin.emailTest.send'), ['email' => 'check@example.com'])
                ->assertSessionHas('error', fn (string $message) => str_contains($message, 'does not deliver email'))
                ->assertSessionMissing('success');
        }

        Mail::assertNothingSent();
    }

    public function test_transport_failures_are_reported_without_exposing_server_details(): void
    {
        config(['mail.default' => 'broken', 'mail.mailers.broken.transport' => 'broken']);
        $transport = Mockery::mock(TransportInterface::class);
        $transport->shouldReceive('send')->once()->andThrow(new TransportException('private SMTP password details'));
        Mail::extend('broken', fn () => $transport);
        $this->actingAs(User::factory()->create(['role' => 'super_admin']));

        $this->post(route('admin.emailTest.send'), ['email' => 'check@example.com'])
            ->assertSessionHas('error', fn (string $message) => str_contains($message, 'could not be sent')
                && ! str_contains($message, 'private SMTP'))
            ->assertSessionMissing('success');
    }

    public function test_cancelled_messages_are_not_reported_as_sent(): void
    {
        $transport = $this->useDeliveryTransport();
        Event::listen(MessageSending::class, fn () => false);
        $this->actingAs(User::factory()->create(['role' => 'super_admin']));

        $this->post(route('admin.emailTest.send'), ['email' => 'check@example.com'])
            ->assertSessionHas('error', 'The test email was not sent. Check the application logs and mail configuration.')
            ->assertSessionMissing('success');

        $this->assertCount(0, $transport->messages());
    }

    public function test_local_fallback_does_not_produce_a_success_result(): void
    {
        config(['mail.default' => 'failover', 'mail.mailers.failover.mailers' => ['array']]);
        $this->actingAs(User::factory()->create(['role' => 'super_admin']));

        $this->post(route('admin.emailTest.send'), ['email' => 'check@example.com'])
            ->assertSessionHas('mail_test_warning', fn (string $message) => str_contains($message, 'does not confirm delivery'))
            ->assertSessionMissing('success');

        $this->get(route('admin.emailTest'))->assertOk()->assertSee('does not confirm delivery');
    }

    public function test_send_attempts_are_rate_limited(): void
    {
        $transport = $this->useDeliveryTransport();
        $this->actingAs(User::factory()->create(['role' => 'super_admin']));

        for ($attempt = 0; $attempt < 3; $attempt++) {
            $this->post(route('admin.emailTest.send'), ['email' => 'check@example.com'])
                ->assertRedirect(route('admin.emailTest'));
        }

        $this->post(route('admin.emailTest.send'), ['email' => 'check@example.com'])->assertStatus(429);
        $this->assertCount(3, $transport->messages());
    }

    public function test_mailable_identifies_the_test_and_its_timestamp(): void
    {
        $mail = new MailTest('2026-10-23T10:00:00+12:00');

        $mail->assertHasSubject('FNU Job Placement - Test Email');
        $mail->assertSeeInHtml('2026-10-23T10:00:00+12:00');
        $mail->assertSeeInHtml('If you received this message');
    }

    private function useDeliveryTransport(): ArrayTransport
    {
        $transport = new ArrayTransport;
        config(['mail.default' => 'test-delivery', 'mail.mailers.test-delivery.transport' => 'test-delivery']);
        Mail::extend('test-delivery', fn () => $transport);

        return $transport;
    }
}
