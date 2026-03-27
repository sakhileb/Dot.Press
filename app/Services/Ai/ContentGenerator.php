<?php

namespace App\Services\Ai;

use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Http;
use RuntimeException;

class ContentGenerator
{
    public function generateSlide(string $prompt): array
    {
        $provider = config('ai.provider', 'mock');

        if ($provider === 'anthropic') {
            return $this->generateSlideWithAnthropic($prompt);
        }

        return $this->generateSlideMock($prompt);
    }

    public function rewriteText(string $input, string $mode, ?string $tone = null): string
    {
        $provider = config('ai.provider', 'mock');

        if ($provider === 'anthropic') {
            return $this->rewriteTextWithAnthropic($input, $mode, $tone);
        }

        return $this->rewriteTextMock($input, $mode, $tone);
    }

    private function generateSlideMock(string $prompt): array
    {
        $title = trim(mb_substr($prompt, 0, 70));
        $headline = $title !== '' ? $title : 'New AI Slide';

        return [
            'title' => $headline,
            'elements' => [
                [
                    'id' => 'ai-title-'.uniqid(),
                    'type' => 'text',
                    'x' => 90,
                    'y' => 80,
                    'width' => 1100,
                    'height' => 120,
                    'rotation' => 0,
                    'text' => $headline,
                    'fontSize' => 54,
                    'fill' => '#0f172a',
                    'fontStyle' => 'bold',
                    'textAlign' => 'left',
                    'lineHeight' => 1.2,
                    'textIndent' => 0,
                    'richContent' => [
                        'type' => 'doc',
                        'content' => [[
                            'type' => 'paragraph',
                            'content' => [[
                                'type' => 'text',
                                'text' => $headline,
                            ]],
                        ]],
                    ],
                ],
                [
                    'id' => 'ai-body-'.uniqid(),
                    'type' => 'text',
                    'x' => 90,
                    'y' => 235,
                    'width' => 780,
                    'height' => 320,
                    'rotation' => 0,
                    'text' => "Generated from prompt:\n\n".$prompt,
                    'fontSize' => 26,
                    'fill' => '#334155',
                    'fontStyle' => 'normal',
                    'textAlign' => 'left',
                    'lineHeight' => 1.4,
                    'textIndent' => 0,
                    'richContent' => [
                        'type' => 'doc',
                        'content' => [[
                            'type' => 'paragraph',
                            'content' => [[
                                'type' => 'text',
                                'text' => "Generated from prompt:\n\n".$prompt,
                            ]],
                        ]],
                    ],
                ],
                [
                    'id' => 'ai-visual-'.uniqid(),
                    'type' => 'rect',
                    'x' => 900,
                    'y' => 235,
                    'width' => 290,
                    'height' => 320,
                    'rotation' => 0,
                    'fill' => '#dbeafe',
                    'stroke' => '#1d4ed8',
                    'strokeWidth' => 2,
                    'radius' => 14,
                ],
            ],
            'usage' => [
                'input_tokens' => 0,
                'output_tokens' => 0,
            ],
        ];
    }

    private function rewriteTextMock(string $input, string $mode, ?string $tone = null): string
    {
        return match ($mode) {
            'shorten' => mb_strimwidth($input, 0, 220, '...'),
            'expand' => trim($input).' This point matters because it drives clarity, alignment, and decision quality for the audience.',
            'rephrase' => 'In other words: '.trim($input),
            'tone' => $tone ? "[{$tone} tone] ".trim($input) : trim($input),
            default => trim($input),
        };
    }

    private function generateSlideWithAnthropic(string $prompt): array
    {
        $instruction = <<<TXT
You are generating one presentation slide.
Return valid JSON with this exact shape:
{
  "title": "...",
  "elements": [
    {
      "type": "text",
      "x": 0,
      "y": 0,
      "width": 0,
      "height": 0,
      "rotation": 0,
      "text": "...",
      "fontSize": 0,
      "fill": "#000000",
      "fontStyle": "normal",
      "textAlign": "left",
      "lineHeight": 1.3,
      "textIndent": 0,
      "richContent": {"type":"doc","content":[{"type":"paragraph","content":[{"type":"text","text":"..."}]}]}
    }
  ]
}
Use a 1280x720 coordinate system.
TXT;

        $payload = $this->callAnthropic($instruction, $prompt);
        $json = $this->extractJson($payload);

        $decoded = json_decode($json, true);

        if (! is_array($decoded)) {
            throw new RuntimeException('AI slide response is not valid JSON.');
        }

        return [
            'title' => Arr::get($decoded, 'title', 'AI Slide'),
            'elements' => Arr::get($decoded, 'elements', []),
            'usage' => Arr::get($payload, 'usage', []),
        ];
    }

    private function rewriteTextWithAnthropic(string $input, string $mode, ?string $tone): string
    {
        $instruction = 'Rewrite the text according to the requested mode. Return plain text only.';
        $prompt = "Mode: {$mode}\nTone: ".($tone ?? 'default')."\n\nText:\n{$input}";

        $payload = $this->callAnthropic($instruction, $prompt);

        return trim(Arr::get($payload, 'content.0.text', $input));
    }

    private function callAnthropic(string $system, string $prompt): array
    {
        $apiKey = config('ai.anthropic.api_key');

        if (! $apiKey) {
            throw new RuntimeException('ANTHROPIC_API_KEY is missing.');
        }

        $response = Http::timeout((int) config('ai.anthropic.timeout_seconds', 20))
            ->withHeaders([
                'x-api-key' => $apiKey,
                'anthropic-version' => '2023-06-01',
                'content-type' => 'application/json',
            ])
            ->post('https://api.anthropic.com/v1/messages', [
                'model' => config('ai.anthropic.model'),
                'max_tokens' => (int) config('ai.anthropic.max_tokens', 1200),
                'system' => $system,
                'messages' => [
                    ['role' => 'user', 'content' => $prompt],
                ],
            ]);

        if (! $response->successful()) {
            throw new RuntimeException('Anthropic request failed with status '.$response->status().'.');
        }

        return $response->json();
    }

    private function extractJson(array $payload): string
    {
        $content = Arr::get($payload, 'content.0.text', '');

        $jsonStart = strpos($content, '{');
        $jsonEnd = strrpos($content, '}');

        if ($jsonStart === false || $jsonEnd === false || $jsonEnd <= $jsonStart) {
            throw new RuntimeException('AI response did not include JSON.');
        }

        return substr($content, $jsonStart, $jsonEnd - $jsonStart + 1);
    }
}
