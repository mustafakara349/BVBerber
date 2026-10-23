<?php

namespace Tests\Unit;

use App\Models\AuditLog;
use Tests\TestCase;

class AuditLogTest extends TestCase
{
    public function test_audit_log_action_labels_and_badges(): void
    {
        $log = new AuditLog([
            'action' => 'appointment.created',
            'model_type' => 'App\\Models\\Appointment',
            'model_id' => 10,
            'status' => 'success',
        ]);

        $this->assertEquals('Oluşturuldu', $log->action_label_tr);
        $this->assertEquals('Randevu', $log->model_name_tr);
        $this->assertStringContainsString('bg-success-subtle', $log->action_badge['class']);

        $authLog = new AuditLog([
            'action' => 'auth.login.success',
            'model_type' => 'App\\Models\\User',
            'model_id' => 1,
            'status' => 'success',
        ]);
        $this->assertEquals('Giriş Başarılı', $authLog->action_label_tr);
        $this->assertEquals('Oturum', $authLog->action_badge['label']);
        $this->assertStringContainsString('bg-indigo-subtle', $authLog->action_badge['class']);
        $this->assertStringContainsString('text-indigo', $authLog->action_badge['class']);
    }

    public function test_audit_log_exception_badge(): void
    {
        $log = new AuditLog([
            'action' => 'system.exception',
            'model_type' => 'exception',
            'model_id' => 0,
            'status' => 'error',
        ]);

        $this->assertEquals('Sistem Hatası', $log->action_label_tr);
        $this->assertStringContainsString('bg-danger-subtle', $log->action_badge['class']);
    }

    public function test_sensitive_data_redaction(): void
    {
        $data = [
            'username' => 'john_doe',
            'password' => 'secret_123',
            'token' => 'bearer-xyz',
            'card_number' => '4111222233334444',
            'nested' => [
                'api_secret' => 'super-secret',
                'normal' => 'safe_value',
            ]
        ];

        $redacted = AuditLog::redactSensitiveData($data);

        $this->assertEquals('john_doe', $redacted['username']);
        $this->assertEquals('[REDACTED]', $redacted['password']);
        $this->assertEquals('[REDACTED]', $redacted['token']);
        $this->assertEquals('[REDACTED]', $redacted['card_number']);
        $this->assertEquals('[REDACTED]', $redacted['nested']['api_secret']);
        $this->assertEquals('safe_value', $redacted['nested']['normal']);
    }

    public function test_parsed_new_and_old_values_decoding(): void
    {
        $log = new AuditLog([
            'new_values' => json_encode(['status' => 'success', 'data' => ['price' => 150]]),
            'old_values' => json_encode(['price' => 120]),
        ]);

        $this->assertIsArray($log->parsed_new_values);
        $this->assertEquals('success', $log->parsed_new_values['status']);
        $this->assertEquals(150, $log->parsed_new_values['data']['price']);

        $this->assertIsArray($log->parsed_old_values);
        $this->assertEquals(120, $log->parsed_old_values['price']);
    }

    public function test_target_url_resolution(): void
    {
        $appointmentLog = new AuditLog([
            'model_type' => 'App\\Models\\Appointment',
            'model_id' => 99,
        ]);
        $this->assertEquals(route('appointments.show', 99), $appointmentLog->target_url);

        $settingLog = new AuditLog([
            'model_type' => 'App\\Models\\Setting',
            'model_id' => 1,
        ]);
        $this->assertEquals(route('settings.index'), $settingLog->target_url);
    }

    public function test_human_description_accessor(): void
    {
        $log1 = new AuditLog([
            'action' => 'transaction.created',
            'model_type' => 'App\\Models\\Transaction',
            'model_id' => 113,
            'status' => 'success',
            'new_values' => json_encode(['details' => 'Model created event for App\\Models\\Transaction #113']),
        ]);
        $this->assertEquals('Yeni Kasa Hareketi kaydı başarıyla oluşturuldu.', $log1->human_description);
        $this->assertStringContainsString('border-0', $log1->action_badge['class']);

        $log2 = new AuditLog([
            'action' => 'auth.login.success',
            'model_type' => 'App\\Models\\User',
            'model_id' => 1,
            'status' => 'success',
        ]);
        $this->assertEquals('Kullanıcı sisteme başarıyla giriş yaptı.', $log2->human_description);

        $log3 = new AuditLog([
            'action' => 'post:appointments/complete-payment',
            'model_type' => 'http_request',
            'model_id' => 0,
            'status' => 'success',
            'new_values' => json_encode(['details' => 'HTTP POST appointments/uuid/complete-payment (Status: 302)']),
        ]);
        $this->assertStringContainsString('Ödeme Tamamlama İşlemi', $log3->human_description);
    }

    public function test_audit_log_created_event_payload_and_channel(): void
    {
        $log = new AuditLog([
            'action' => 'user.updated',
            'model_type' => 'App\\Models\\User',
            'model_id' => 5,
            'status' => 'success',
            'ip_address' => '192.168.1.50',
            'new_values' => json_encode(['details' => 'Model updated event for App\\Models\\User #5']),
        ]);
        $log->id = 999;

        $event = new \App\Events\AuditLogCreated($log);

        $this->assertEquals('audit.created', $event->broadcastAs());
        $channels = $event->broadcastOn();
        $this->assertCount(1, $channels);
        $this->assertEquals('private-audit-logs', $channels[0]->name);

        $payload = $event->broadcastWith();
        $this->assertEquals(999, $payload['id']);
        $this->assertEquals('user.updated', $payload['action']);
        $this->assertEquals('Güncellendi', $payload['action_label']);
        $this->assertEquals('Kullanıcı / Müşteri', $payload['model_name']);
        $this->assertEquals('192.168.1.50', $payload['ip_address']);
        $this->assertFalse($payload['is_error']);
        $this->assertEquals('models', $payload['tab']);
    }

    public function test_audit_retention_configuration(): void
    {
        $this->assertEquals(60, config('bv.audit_default_days'));
    }
}
