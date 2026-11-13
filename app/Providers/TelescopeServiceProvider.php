<?php

namespace App\Providers;

use Illuminate\Support\Facades\Gate;
use Laravel\Telescope\IncomingEntry;
use Laravel\Telescope\Telescope;
use Laravel\Telescope\TelescopeApplicationServiceProvider;

class TelescopeServiceProvider extends TelescopeApplicationServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        // Telescope::night();

        $this->hideSensitiveRequestDetails();

        $isLocal = $this->app->environment('local');

        Telescope::filter(function (IncomingEntry $entry) use ($isLocal) {
            return $isLocal ||
                   $entry->isReportableException() ||
                   $entry->isFailedRequest() ||
                   $entry->isFailedJob() ||
                   $entry->isScheduledTask() ||
                   $entry->hasMonitoredTag();
        });
    }

    /**
     * Prevent sensitive request details from being logged by Telescope.
     */
    protected function hideSensitiveRequestDetails(): void
    {
        Telescope::hideRequestParameters([
            '_token', 'password', '_password', 'password_confirmation', 'password-confirmation',
            'current_password', 'current-password', 'new_password', 'new-password',
            'token', 'access_token', 'refresh_token', 'api_token', 'api_key', 'api-key',
            'client_secret', 'secret', 'authorization', 'otp', 'verification_code',
            'two_factor_secret', 'two_factor_recovery_codes', 'remember_token',
        ]);

        Telescope::hideRequestHeaders([
            'cookie',
            'authorization',
            'proxy-authorization',
            'x-api-key',
            'x-auth-token',
            'x-access-token',
            'x-csrf-token',
            'x-xsrf-token',
            'set-cookie',
        ]);

        Telescope::afterRecording(function (Telescope $telescope, IncomingEntry $entry) {
            $entry->content = $this->redactSensitiveContent($entry->content);
        });
    }

    private function redactSensitiveContent(mixed $value): mixed
    {
        if (is_array($value)) {
            foreach ($value as $key => $nested) {
                if ($this->isSensitiveName((string) $key)) {
                    $value[$key] = '********';
                } else {
                    $value[$key] = $this->redactSensitiveContent($nested);
                }
            }

            return $value;
        }

        if (! is_string($value)) {
            return $value;
        }

        return preg_replace_callback(
            '/([?&])([^=&#\s]+)=([^&#\s]*)/',
            function (array $matches): string {
                $key = urldecode($matches[2]);

                return $matches[1].$matches[2].'='.($this->isSensitiveName($key) ? '********' : $matches[3]);
            },
            $value
        ) ?? $value;
    }

    private function isSensitiveName(string $key): bool
    {
        $sensitiveNames = [
            'password', 'passwordconfirmation', 'currentpassword', 'newpassword',
            'token', 'accesstoken', 'refreshtoken', 'apitoken', 'apikey', 'clientsecret',
            'secret', 'authorization', 'otp', 'verificationcode', 'signature', 'sig', 'credential',
            'twofactorsecret', 'twofactorrecoverycodes', 'remembertoken',
        ];

        $normalizedKey = preg_replace('/[^a-z0-9]/', '', strtolower($key));

        return in_array($normalizedKey, $sensitiveNames, true);
    }

    /**
     * Register the Telescope gate.
     *
     * This gate determines who can access Telescope in non-local environments.
     */
    protected function gate(): void
    {
        Gate::define('viewTelescope', function ($user) {
            return in_array($user->email, [

            ]);
        });
    }
}
