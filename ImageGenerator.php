<?php

use Intervention\Image\Drivers\Gd\Driver;
use Intervention\Image\Encoders\PngEncoder;
use Intervention\Image\ImageManager;

class ImageGenerator
{
    public function generate(string $title)
    {
        $width = 1200;
        $height = 630;
        $backgroundColor = '#0D6EFD';
        $padding = 50; // horizontal padding

        $manager = new ImageManager(new Driver());
        $canvas = $manager->create($width, $height)->fill($backgroundColor);

        $fontPath = fopen('./fonts/Roboto-Bold.ttf');
        $fontSize = 100; // initial font size
        $maxTextWidth = $width - $padding * 2;
        $maxTextHeight = $height - $padding * 2;

        $lines = [];

        // Function to split text into lines for given font size
        $splitLines = function($text, $fontSize) use ($fontPath, $maxTextWidth) {
            $words = explode(' ', $text);
            $lines = [];
            $currentLine = '';
            foreach ($words as $word) {
                $testLine = $currentLine === '' ? $word : $currentLine . ' ' . $word;
                $box = imagettfbbox($fontSize, 0, $fontPath, $testLine);
                $textWidth = abs($box[2] - $box[0]);
                if ($textWidth > $maxTextWidth) {
                    if ($currentLine !== '') {
                        $lines[] = $currentLine;
                    }
                    $currentLine = $word;
                } else {
                    $currentLine = $testLine;
                }
            }
            if ($currentLine !== '') {
                $lines[] = $currentLine;
            }
            return $lines;
        };

        // Reduce font size until text block fits within canvas height
        do {
            $lines = $splitLines($title, $fontSize);
            $lineHeight = $fontSize * 1.2;
            $totalHeight = count($lines) * $lineHeight;
            if ($totalHeight > $maxTextHeight) {
                $fontSize -= 5;
            } else {
                break;
            }
        } while ($fontSize > 10);

        // Vertical start position to center text block
        $y = ($height - $totalHeight) / 2;

        // Draw each line
        foreach ($lines as $line) {
            $canvas->text($line, $width / 2, $y, function ($font) use ($fontPath, $fontSize) {
                $font->file($fontPath);
                $font->size($fontSize);
                $font->color('#ffffff');
                $font->align('center');
                $font->valign('top');
            });
            $y += $lineHeight;
        }

        // Add watermark
        $this->addWatermark($canvas, $width, $height, $fontPath);

        return response($canvas->encode(new PngEncoder()))
            ->header('Content-Type', 'image/png');
    }

    private function addWatermark($canvas, $width, $height, $fontPath)
    {
        $watermarkText = config('app.url');
        $watermarkFontSize = 30;
        $watermarkOpacity = 0.5;

        // Position watermark at bottom right
        $watermarkX = $width - 30; // 30px from right edge
        $watermarkY = $height - 20; // 20px from bottom edge

        // Convert hex color with opacity to RGBA
        $watermarkColor = sprintf('rgba(255, 255, 255, %.2f)', $watermarkOpacity);

        $canvas->text($watermarkText, $watermarkX, $watermarkY, function ($font) use ($fontPath, $watermarkFontSize, $watermarkColor) {
            $font->file($fontPath);
            $font->size($watermarkFontSize);
            $font->color($watermarkColor);
            $font->align('right');
            $font->valign('bottom');
        });
    }

    // Alternative method for diagonal watermark (left to right across the image)
    private function addDiagonalWatermark($canvas, $width, $height, $fontPath)
    {
        $watermarkText = config('app.name');
        $watermarkFontSize = 36;
        $watermarkOpacity = 0.15; // 15% opacity for diagonal watermark

        // Calculate diagonal positioning
        $centerX = $width / 2;
        $centerY = $height / 2;

        // Convert hex color with opacity to RGBA
        $watermarkColor = sprintf('rgba(255, 255, 255, %.2f)', $watermarkOpacity);

        $canvas->text($watermarkText, $centerX, $centerY, function ($font) use ($fontPath, $watermarkFontSize, $watermarkColor) {
            $font->file($fontPath);
            $font->size($watermarkFontSize);
            $font->color($watermarkColor);
            $font->align('center');
            $font->valign('middle');
            $font->angle(-15); // Rotate text 15 degrees counterclockwise
        });
    }

    // Alternative method for repeating watermark pattern
    private function addRepeatingWatermark($canvas, $width, $height, $fontPath)
    {
        $watermarkText = config('app.name');
        $watermarkFontSize = 20;
        $watermarkOpacity = 0.1; // 10% opacity for repeating pattern

        $spacingX = 200; // Horizontal spacing between watermarks
        $spacingY = 150; // Vertical spacing between watermarks

        // Convert hex color with opacity to RGBA
        $watermarkColor = sprintf('rgba(255, 255, 255, %.2f)', $watermarkOpacity);

        for ($x = 0; $x < $width; $x += $spacingX) {
            for ($y = 0; $y < $height; $y += $spacingY) {
                $canvas->text($watermarkText, $x + 100, $y + 75, function ($font) use ($fontPath, $watermarkFontSize, $watermarkColor) {
                    $font->file($fontPath);
                    $font->size($watermarkFontSize);
                    $font->color($watermarkColor);
                    $font->align('center');
                    $font->valign('middle');
                    $font->angle(-20); // Rotate text 20 degrees
                });
            }
        }
    }
}

