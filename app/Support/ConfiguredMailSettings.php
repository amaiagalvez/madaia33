<?php

namespace App\Support;

use Illuminate\Mail\MailManager;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Contracts\Config\Repository;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\Encryption\DecryptException;

class ConfiguredMailSettings
{
    private const ENCRYPTED_KEYS = ['smtp_password'];

    public function __construct(
        private readonly Application $app,
        private readonly Repository $config,
    ) {}

    /**
     * @return list<string>
     */
    public static function encryptionOptions(): array
    {
        return ['tls', 'ssl'];
    }

    public function storeValue(string $key, string $value): string
    {
        if ($value === '' || ! in_array($key, self::ENCRYPTED_KEYS, true)) {
            return $value;
        }

        return Crypt::encryptString($value);
    }

    public function displayValue(string $key, ?string $value): string
    {
        $value = (string) ($value ?? '');

        if ($value === '' || ! in_array($key, self::ENCRYPTED_KEYS, true)) {
            return $value;
        }

        try {
            return Crypt::decryptString($value);
        } catch (DecryptException) {
            return $value;
        }
    }

    /**
     * @param  array<string, string>  $settings
     */
    public function apply(array $settings): void
    {
        $runtimeConfig = $this->runtimeConfig($settings);

        if ($runtimeConfig === []) {
            return;
        }

        $this->config->set($runtimeConfig);
        $this->app->make(MailManager::class)->forgetMailers();
    }

    /**
     * @param  array<string, string>  $settings
     * @return array<string, mixed>
     */
    public function runtimeConfig(array $settings): array
    {
        $fromAddress = trim((string) ($settings['from_address'] ?? ''));
        $fromName = trim((string) ($settings['from_name'] ?? ''));
        $smtpHost = trim((string) ($settings['smtp_host'] ?? ''));

        $config = [];

        if ($fromAddress !== '') {
            $config['mail.from.address'] = $fromAddress;
        }

        if ($fromName !== '') {
            $config['mail.from.name'] = $fromName;
        }

        if ($smtpHost === '') {
            return $config;
        }

        $smtpPort = (int) ($settings['smtp_port'] ?? 0);
        $smtpUsername = trim((string) ($settings['smtp_username'] ?? ''));
        $smtpPassword = $this->displayValue('smtp_password', $settings['smtp_password'] ?? '');
        $smtpEncryption = trim((string) ($settings['smtp_encryption'] ?? ''));

        $scheme = $smtpEncryption !== '' ? $smtpEncryption : 'smtp';

        return [
            ...$config,
            'mail.default' => 'smtp',
            'mail.mailers.smtp.transport' => 'smtp',
            'mail.mailers.smtp.url' => null,
            'mail.mailers.smtp.host' => $smtpHost,
            'mail.mailers.smtp.port' => $smtpPort > 0 ? $smtpPort : 587,
            'mail.mailers.smtp.username' => $smtpUsername !== '' ? $smtpUsername : null,
            'mail.mailers.smtp.password' => $smtpPassword !== '' ? $smtpPassword : null,
            'mail.mailers.smtp.scheme' => $scheme,
            'mail.mailers.smtp.encryption' => $smtpEncryption !== '' ? $smtpEncryption : null,
        ];
    }
}
