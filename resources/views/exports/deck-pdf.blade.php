<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>{{ $deck->title }}</title>
    <style>
        body { font-family: DejaVu Sans, sans-serif; margin: 0; padding: 0; color: #0f172a; }
        .page { page-break-after: always; padding: 24px; }
        .page:last-child { page-break-after: auto; }
        h1 { font-size: 24px; margin: 0 0 6px; }
        h2 { font-size: 16px; margin: 0 0 14px; color: #334155; }
        .canvas { position: relative; width: 1200px; height: 675px; border: 1px solid #cbd5e1; background: #fff; overflow: hidden; }
        .text { position: absolute; white-space: pre-wrap; word-break: break-word; }
        .shape { position: absolute; }
    </style>
</head>
<body>
@foreach ($slides as $slide)
    @php($elements = $slide->canvas_state['elements'] ?? [])
    <section class="page">
        <h1>{{ $deck->title }}</h1>
        <h2>{{ $slide->title ?: 'Slide '.($loop->index + 1) }}</h2>

        <div class="canvas">
            @foreach ($elements as $element)
                @if (($element['type'] ?? '') === 'text')
                    <div
                        class="text"
                        style="left: {{ (int) ($element['x'] ?? 0) }}px; top: {{ (int) ($element['y'] ?? 0) }}px; width: {{ (int) ($element['width'] ?? 200) }}px; min-height: {{ (int) ($element['height'] ?? 40) }}px; font-size: {{ (int) ($element['fontSize'] ?? 24) }}px; color: {{ $element['fill'] ?? '#111827' }};"
                    >{{ $element['text'] ?? '' }}</div>
                @else
                    <div
                        class="shape"
                        style="left: {{ (int) ($element['x'] ?? 0) }}px; top: {{ (int) ($element['y'] ?? 0) }}px; width: {{ (int) ($element['width'] ?? 120) }}px; height: {{ (int) ($element['height'] ?? 80) }}px; background: {{ $element['fill'] ?? 'transparent' }}; border: {{ (int) ($element['strokeWidth'] ?? 1) }}px solid {{ $element['stroke'] ?? 'transparent' }};"
                    ></div>
                @endif
            @endforeach
        </div>
    </section>
@endforeach
</body>
</html>
