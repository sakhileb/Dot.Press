<?php

namespace App\Http\Controllers;

use App\Models\Deck;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Response;
use PhpOffice\PhpPresentation\IOFactory;
use PhpOffice\PhpPresentation\PhpPresentation;
use PhpOffice\PhpPresentation\Shape\RichText;

class ExportController extends Controller
{
    public function pdf(Deck $deck): Response
    {
        $deck->loadMissing([
            'project',
            'slides' => fn ($query) => $query->orderBy('sort_order'),
        ]);
        $this->authorize('view', $deck);

        $pdf = Pdf::loadView('exports.deck-pdf', [
            'deck' => $deck,
            'slides' => $deck->slides,
        ])->setPaper('a4', 'landscape');

        return response($pdf->output(), 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'attachment; filename="'.str($deck->title)->slug('-').'.pdf"',
        ]);
    }

    public function pptx(Deck $deck): Response
    {
        $deck->loadMissing([
            'project',
            'slides' => fn ($query) => $query->orderBy('sort_order'),
        ]);
        $this->authorize('view', $deck);

        $presentation = new PhpPresentation();
        $presentation->removeSlideByIndex(0);

        if ($deck->slides->isEmpty()) {
            $slide = $presentation->createSlide();
            $shape = new RichText();
            $shape->setHeight(200);
            $shape->setWidth(900);
            $shape->setOffsetX(20);
            $shape->setOffsetY(80);
            $shape->createTextRun('No slides available for export.');
            $slide->addShape($shape);
        }

        foreach ($deck->slides as $slideModel) {
            $slide = $presentation->createSlide();
            $elements = $slideModel->canvas_state['elements'] ?? [];

            $title = new RichText();
            $title->setHeight(40);
            $title->setWidth(900);
            $title->setOffsetX(20);
            $title->setOffsetY(10);
            $title->createTextRun((string) ($slideModel->title ?: 'Slide'));
            $slide->addShape($title);

            $lines = [];

            foreach ((array) $elements as $element) {
                if (($element['type'] ?? '') !== 'text') {
                    continue;
                }

                $text = trim((string) ($element['text'] ?? ''));

                if ($text !== '') {
                    $lines[] = $text;
                }
            }

            if ($lines === []) {
                $lines[] = 'No text content on this slide.';
            }

            $body = new RichText();
            $body->setHeight(520);
            $body->setWidth(900);
            $body->setOffsetX(20);
            $body->setOffsetY(80);
            $body->createTextRun(implode("\n\n", $lines));
            $slide->addShape($body);
        }

        if (! class_exists('ZipArchive')) {
            abort(503, 'PPTX export requires the PHP zip extension (ext-zip).');
        }

        $writer = IOFactory::createWriter($presentation, 'PowerPoint2007');

        $tempPath = storage_path('app/tmp/'.uniqid('deck-export-', true).'.pptx');

        if (! is_dir(dirname($tempPath))) {
            mkdir(dirname($tempPath), 0755, true);
        }

        $writer->save($tempPath);
        $contents = file_get_contents($tempPath) ?: '';
        @unlink($tempPath);

        return response($contents, 200, [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.presentationml.presentation',
            'Content-Disposition' => 'attachment; filename="'.str($deck->title)->slug('-').'.pptx"',
        ]);
    }
}
