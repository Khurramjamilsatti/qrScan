<?php

namespace App\Support;

use Stripe\ApiRequestor;
use Stripe\HttpClient\CurlClient;
use Stripe\Stripe;

class StripeHttpClientConfigurator
{
    private static bool $configured = false;

    public static function configure(): void
    {
        if (self::$configured || ! filled(config('stripe.secret'))) {
            return;
        }

        $disableProxy = self::stripeFlag('stripe.disable_proxy', true);
        $disableHttp2 = self::stripeFlag('stripe.disable_http2', true);

        if ($disableProxy) {
            self::clearProxyEnvironment();
        }

        $curlClient = new CurlClient(function () use ($disableProxy, $disableHttp2) {
            $opts = [];

            if ($disableProxy) {
                $opts[CURLOPT_PROXY] = '';
                $opts[CURLOPT_HTTPPROXYTUNNEL] = 0;

                if (defined('CURLOPT_NOPROXY')) {
                    $opts[CURLOPT_NOPROXY] = '*';
                }
            }

            if ($disableHttp2) {
                $opts[CURLOPT_HTTP_VERSION] = CURL_HTTP_VERSION_1_1;
            }

            return $opts;
        });
        $curlClient->setEnableHttp2(! $disableHttp2);

        ApiRequestor::setHttpClient($curlClient);
        Stripe::setMaxNetworkRetries(2);

        self::$configured = true;
    }

    protected static function stripeFlag(string $key, bool $default): bool
    {
        $value = config($key, $default);

        if ($value === null) {
            return $default;
        }

        return filter_var($value, FILTER_VALIDATE_BOOLEAN);
    }

    protected static function clearProxyEnvironment(): void
    {
        foreach ([
            'HTTP_PROXY',
            'HTTPS_PROXY',
            'http_proxy',
            'https_proxy',
            'ALL_PROXY',
            'all_proxy',
            'SOCKS_PROXY',
            'SOCKS5_PROXY',
            'socks_proxy',
            'socks5_proxy',
            'GIT_HTTP_PROXY',
            'GIT_HTTPS_PROXY',
        ] as $var) {
            putenv($var);
            unset($_ENV[$var], $_SERVER[$var]);
        }
    }
}
