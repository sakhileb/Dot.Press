<?php

namespace App\Services\Ai;

class SafetyGuard
{
    /**
     * @return array{blocked: bool, reason: string|null}
     */
    public function evaluate(string $text): array
    {
        $normalized = mb_strtolower($text);

        if (mb_strlen($normalized) < 1) {
            return ['blocked' => true, 'reason' => 'Prompt is empty.'];
        }

        if (mb_strlen($normalized) > 8000) {
            return ['blocked' => true, 'reason' => 'Prompt is too long.'];
        }

        $blockedPatterns = [
            '/\b(make|build|plan|give)\b.{0,20}\b(bomb|explosive|weapon)\b/u',
            '/\b(hate speech|genocide|ethnic cleansing)\b/u',
            '/\b(sexual|explicit)\b.{0,20}\b(minor|child|teen)\b/u',
            '/\b(steal|phish|malware|ransomware)\b/u',
        ];

        foreach ($blockedPatterns as $pattern) {
            if (preg_match($pattern, $normalized) === 1) {
                return ['blocked' => true, 'reason' => 'Prompt violates AI safety policy.'];
            }
        }

        return ['blocked' => false, 'reason' => null];
    }
}
