<?php

final class RequestGuard
{
    public static function isHoneypotTriggered(array $payload, string $field = 'website'): bool
    {
        return trim((string) ($payload[$field] ?? '')) !== '';
    }

    public static function hasValidSubmissionTiming(array $payload, int $minAgeSeconds = 2, int $maxAgeSeconds = 86400): bool
    {
        $startedAt = (string) ($payload['form_started_at'] ?? '');
        if ($startedAt === '' || !ctype_digit($startedAt)) {
            return true;
        }

        $nowMs = (int) round(microtime(true) * 1000);
        $elapsedMs = $nowMs - (int) $startedAt;

        return $elapsedMs >= ($minAgeSeconds * 1000) && $elapsedMs <= ($maxAgeSeconds * 1000);
    }

    public static function isRateLimited(string $bucket, int $maxRequests, int $windowSeconds): bool
    {
        $ipAddress = self::getClientIp();
        $storageDir = dirname(__DIR__) . '/storage/rate-limit';

        if (!is_dir($storageDir) && !mkdir($storageDir, 0755, true) && !is_dir($storageDir)) {
            return false;
        }

        $storageFile = $storageDir . '/' . sha1($bucket . '|' . $ipAddress) . '.json';
        $now = time();
        $windowStart = $now - $windowSeconds;
        $timestamps = [];

        if (is_file($storageFile)) {
            $decoded = json_decode((string) file_get_contents($storageFile), true);
            if (is_array($decoded)) {
                $timestamps = array_values(array_filter($decoded, static function ($timestamp) use ($windowStart) {
                    return is_int($timestamp) && $timestamp >= $windowStart;
                }));
            }
        }

        if (count($timestamps) >= $maxRequests) {
            return true;
        }

        $timestamps[] = $now;
        file_put_contents($storageFile, json_encode($timestamps), LOCK_EX);

        return false;
    }

    private static function getClientIp(): string
    {
        return trim((string) ($_SERVER['REMOTE_ADDR'] ?? 'unknown'));
    }
}
