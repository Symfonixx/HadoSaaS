<?php

namespace Tests\Feature\Security;

use App\Providers\TelescopeServiceProvider;
use Laravel\Telescope\Telescope;
use Laravel\Telescope\EntryType;
use Laravel\Telescope\IncomingEntry;
use Tests\TestCase;

class TelescopeRedactionTest extends TestCase
{
    public function test_sensitive_request_parameters_and_headers_are_redacted_in_local_environment_too(): void
    {
        config(['app.env' => 'local']);
        $provider = new class(app()) extends TelescopeServiceProvider
        {
            public function applySensitiveRedaction(): void
            {
                $this->hideSensitiveRequestDetails();
            }
        };

        $provider->applySensitiveRedaction();

        foreach (['password', 'current-password', 'access_token', 'api_key', 'client_secret', 'two_factor_secret'] as $parameter) {
            $this->assertContains($parameter, Telescope::$hiddenRequestParameters);
        }

        foreach (['authorization', 'proxy-authorization', 'x-api-key', 'x-auth-token', 'cookie'] as $header) {
            $this->assertContains($header, Telescope::$hiddenRequestHeaders);
        }

        $entry = IncomingEntry::make(['uri' => '/reset?access_token=secret-value&safe=visible'])->type(EntryType::REQUEST);
        $hook = Telescope::$afterRecordingHook;
        $hook(new Telescope, $entry);

        $this->assertStringNotContainsString('secret-value', $entry->content['uri']);
        $this->assertStringContainsString('safe=visible', $entry->content['uri']);
    }
}
