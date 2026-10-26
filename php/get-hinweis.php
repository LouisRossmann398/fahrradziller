<?php

declare(strict_types=1);

/**
 * Liest hinweise/aktueller-hinweis.docx und liefert den Text als JSON.
 * Ohne Datei oder bei leerem Inhalt: { "ok": false }
 */

header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: no-store, no-cache, must-revalidate');
header('Pragma: no-cache');

if (($_SERVER['REQUEST_METHOD'] ?? 'GET') !== 'GET') {
    http_response_code(405);
    echo json_encode(['ok' => false, 'error' => 'Nicht erlaubt.'], JSON_UNESCAPED_UNICODE);
    exit;
}

$docxPath = dirname(__DIR__) . '/hinweise/aktueller-hinweis.docx';

if (!is_file($docxPath) || !is_readable($docxPath)) {
    echo json_encode(['ok' => false], JSON_UNESCAPED_UNICODE);
    exit;
}

if (!class_exists('ZipArchive')) {
    http_response_code(500);
    echo json_encode(['ok' => false, 'error' => 'ZipArchive fehlt.'], JSON_UNESCAPED_UNICODE);
    exit;
}

$paragraphs = rz_extract_docx_paragraphs($docxPath);

if ($paragraphs === []) {
    echo json_encode(['ok' => false], JSON_UNESCAPED_UNICODE);
    exit;
}

echo json_encode(
    ['ok' => true, 'paragraphs' => $paragraphs],
    JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES
);

/**
 * @return list<string>
 */
function rz_extract_docx_paragraphs(string $path): array
{
    $zip = new ZipArchive();
    if ($zip->open($path) !== true) {
        return [];
    }

    $xml = $zip->getFromName('word/document.xml');
    $zip->close();

    if ($xml === false || $xml === '') {
        return [];
    }

    // Soft line breaks innerhalb eines Absatzes
    $xml = preg_replace('/<w:br\b[^>]*\/?>/i', "\n", $xml) ?? $xml;

    $chunks = preg_split('/<\/w:p>/i', $xml);
    if ($chunks === false) {
        return [];
    }

    $paragraphs = [];

    foreach ($chunks as $chunk) {
        // Nur Text aus w:t-Knoten – vermeidet Müll aus anderen Tags
        if (!preg_match_all('/<w:t(?:\s[^>]*)?>(.*?)<\/w:t>/s', $chunk, $matches)) {
            continue;
        }

        $parts = [];
        foreach ($matches[1] as $raw) {
            $parts[] = html_entity_decode($raw, ENT_QUOTES | ENT_XML1, 'UTF-8');
        }

        $text = implode('', $parts);
        $text = preg_replace("/[ \t]+/u", ' ', $text) ?? $text;
        $text = preg_replace("/\n{3,}/u", "\n\n", $text) ?? $text;
        $text = trim($text);

        if ($text !== '') {
            $paragraphs[] = $text;
        }
    }

    return $paragraphs;
}
