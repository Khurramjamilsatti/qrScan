<?php

namespace App\Services;

use App\Models\CustomDomain;

class CustomDomainVerificationService
{
    public function check(CustomDomain $domain): array
    {
        if (config('app.domain_verification_bypass')) {
            return [
                'verified' => true,
                'checks' => ['txt' => true, 'cname' => true],
                'warnings' => [],
                'message' => __('messages.domain_verification_bypassed'),
                'bypassed' => true,
            ];
        }

        $instructions = app(DomainUrlService::class)->dnsInstructions($domain);
        $checks = [
            'txt' => $this->txtRecordContains($instructions['txt_host'], $domain->verification_token)
                || $this->txtRecordContains($domain->domain, $domain->verification_token),
            'cname' => $this->cnamePointsTo($domain->domain, $instructions['value']),
        ];

        $warnings = [];
        $verified = $checks['txt'];

        if ($verified && ! $checks['cname']) {
            $warnings[] = __('messages.domain_cname_not_detected', [
                'domain' => $domain->domain,
                'target' => $instructions['value'],
            ]);
        }

        if (! $verified) {
            $message = __('messages.domain_txt_not_found', [
                'host' => $instructions['txt_host'],
                'token' => $domain->verification_token,
            ]);

            if ($checks['cname']) {
                $message = __('messages.domain_cname_but_txt_missing', [
                    'host' => $instructions['txt_host'],
                    'token' => $domain->verification_token,
                ]);
            }

            return [
                'verified' => false,
                'checks' => $checks,
                'warnings' => $warnings,
                'message' => $message,
                'dns' => $instructions,
            ];
        }

        return [
            'verified' => true,
            'checks' => $checks,
            'warnings' => $warnings,
            'message' => __('messages.domain_verified'),
            'dns' => $instructions,
        ];
    }

    private function txtRecordContains(string $host, string $expected): bool
    {
        $records = @dns_get_record($host, DNS_TXT);

        if (! is_array($records) || $records === []) {
            return false;
        }

        $expectedNorm = $this->normalizeTxt($expected);

        foreach ($records as $record) {
            $chunks = $record['entries'] ?? (isset($record['txt']) ? [$record['txt']] : []);

            foreach ($chunks as $chunk) {
                if ($this->normalizeTxt((string) $chunk) === $expectedNorm) {
                    return true;
                }
            }
        }

        return false;
    }

    private function cnamePointsTo(string $host, string $target): bool
    {
        $target = rtrim(strtolower($target), '.');

        $records = @dns_get_record($host, DNS_CNAME);

        if (! is_array($records)) {
            return false;
        }

        foreach ($records as $record) {
            $recordTarget = rtrim(strtolower($record['target'] ?? ''), '.');

            if ($recordTarget === $target) {
                return true;
            }
        }

        return false;
    }

    private function normalizeTxt(string $value): string
    {
        return strtolower(trim($value, " \t\n\r\0\x0B\""));
    }
}
