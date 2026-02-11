<?php

namespace App\Services\Certificate;

use App\Models\CustomFont;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class CertificateFontCssGenerator
{
    private const GOOGLE_CSS_HOST_ALLOWLIST = [
        "fonts.googleapis.com",
    ];

    private const GOOGLE_FONT_FILE_HOST_ALLOWLIST = [
        "fonts.gstatic.com",
    ];

    public function __construct(private readonly CertificateSettingsNormalizer $normalizer)
    {
    }

    public function buildFontCss(?array $certificateSettings, bool $isPreview): string
    {
        $normalized = $this->normalizer->normalize($certificateSettings);
        $elements = $normalized["elements"] ?? [];

        $usedFamilies = $this->collectUsedFontFamilies($elements);
        if (count($usedFamilies) === 0) {
            return "";
        }

        $customFonts = CustomFont::query()
            ->where("is_active", true)
            ->whereIn("font_family", $usedFamilies)
            ->get();

        $css = "";
        foreach ($customFonts as $font) {
            if ($font->type === "file") {
                $css .= $this->buildUploadedFontFaceCss($font, $isPreview);
                continue;
            }

            if ($font->type === "google_link") {
                $css .= $this->buildGoogleFontFaceCss($font, $isPreview);
                continue;
            }
        }

        return trim($css);
    }

    /**
     * @param array<string,array> $elements
     * @return string[]
     */
    private function collectUsedFontFamilies(array $elements): array
    {
        $families = [];

        foreach ($elements as $element) {
            if (!is_array($element)) {
                continue;
            }

            $raw = $element["fontFamily"] ?? null;
            if (!is_string($raw) || trim($raw) === "") {
                continue;
            }

            $primary = $this->extractPrimaryFontFamily($raw);
            if ($primary !== "") {
                $families[$primary] = true;
            }
        }

        return array_keys($families);
    }

    private function extractPrimaryFontFamily(string $fontFamilyCss): string
    {
        $first = explode(",", $fontFamilyCss)[0] ?? "";
        $first = trim($first);
        $first = trim($first, "\"'");
        return $first;
    }

    private function buildUploadedFontFaceCss(CustomFont $font, bool $isPreview): string
    {
        $relPath = ltrim((string) $font->file_path, "/");
        if ($relPath === "") {
            return "";
        }

        $ext = strtolower(pathinfo($relPath, PATHINFO_EXTENSION));
        if ($ext === "woff2") {
            // Dompdf (php-font-lib) does not support WOFF2
            Log::warning("Certificate font skipped: unsupported WOFF2 for PDF", [
                "font_id" => $font->id,
                "font_family" => $font->font_family,
                "file_path" => $font->file_path,
            ]);
            return "";
        }

        $format = match ($ext) {
            "ttf" => "truetype",
            "otf" => "opentype",
            "woff" => "woff",
            default => null,
        };

        if ($format === null) {
            return "";
        }

        $src = $isPreview ? ("/" . $relPath) : $this->normalizeFsPathForCss(public_path($relPath));
        $family = $this->escapeCssString((string) $font->font_family);

        return "@font-face{font-family:'{$family}';src:url('{$src}') format('{$format}');font-style:normal;font-weight:normal;}\n";
    }

    private function buildGoogleFontFaceCss(CustomFont $font, bool $isPreview): string
    {
        $url = (string) $font->google_font_url;
        if (!$this->isAllowedGoogleCssUrl($url)) {
            Log::warning("Certificate google font rejected: host not allowlisted", [
                "font_id" => $font->id,
                "font_family" => $font->font_family,
                "google_font_url" => $font->google_font_url,
            ]);
            return "";
        }

        $faces = $this->ensureGoogleFontCached($font);
        if (count($faces) === 0) {
            return "";
        }

        $family = $this->escapeCssString((string) $font->font_family);
        $css = "";

        foreach ($faces as $face) {
            $relPath = $face["relPath"] ?? null;
            $format = $face["format"] ?? null;
            $weight = $face["weight"] ?? "normal";
            $style = $face["style"] ?? "normal";

            if (!is_string($relPath) || !is_string($format)) {
                continue;
            }

            $src = $isPreview ? ("/" . ltrim($relPath, "/")) : $this->normalizeFsPathForCss(public_path(ltrim($relPath, "/")));

            $css .= "@font-face{font-family:'{$family}';src:url('{$src}') format('{$format}');font-style:{$style};font-weight:{$weight};}\n";
        }

        return $css;
    }

    private function isAllowedGoogleCssUrl(string $url): bool
    {
        $parts = parse_url($url);
        if (!is_array($parts)) {
            return false;
        }

        $host = strtolower((string) ($parts["host"] ?? ""));
        $scheme = strtolower((string) ($parts["scheme"] ?? ""));

        if ($scheme !== "https") {
            return false;
        }

        return in_array($host, self::GOOGLE_CSS_HOST_ALLOWLIST, true);
    }

    /**
     * Ensures a given Google Font URL has cached PDF-compatible font files.
     *
     * Returns a list of font-face descriptors:
     * [
     *   [ 'relPath' => 'uploads/fonts-cache/...', 'format' => 'woff', 'weight' => '400', 'style' => 'normal' ],
     *   ...
     * ]
     *
     * @return array<int,array{relPath:string,format:string,weight:string,style:string}>
     */
    private function ensureGoogleFontCached(CustomFont $font): array
    {
        $familySlug = Str::slug((string) $font->font_family);
        if ($familySlug === "") {
            $familySlug = "google-font";
        }

        $cacheRelDir = "uploads/fonts-cache/google/{$familySlug}";
        $cacheDir = public_path($cacheRelDir);
        if (!is_dir($cacheDir)) {
            @mkdir($cacheDir, 0775, true);
        }

        $manifestName = "manifest_" . sha1((string) $font->google_font_url) . ".json";
        $manifestPath = $cacheDir . DIRECTORY_SEPARATOR . $manifestName;

        $existing = $this->readManifest($manifestPath);
        if ($existing !== null) {
            return $existing;
        }

        $css = $this->fetchGoogleCss((string) $font->google_font_url);
        if ($css === null) {
            return [];
        }

        $faces = $this->parseGoogleCssFaces($css, (string) $font->font_family);
        $cachedFaces = [];

        foreach ($faces as $face) {
            $fileUrl = $face["url"];
            $format = $face["format"];
            $weight = $face["weight"];
            $style = $face["style"];

            if (!$this->isAllowedGoogleFontFileUrl($fileUrl)) {
                continue;
            }

            // Skip unsupported formats (most notably woff2).
            if (!in_array($format, ["woff", "truetype", "opentype"], true)) {
                continue;
            }

            $ext = match ($format) {
                "woff" => "woff",
                "truetype" => "ttf",
                "opentype" => "otf",
            };

            $fileName = Str::slug($familySlug . "-" . $weight . "-" . $style) . "." . $ext;
            if ($fileName === "." . $ext) {
                $fileName = sha1($fileUrl) . "." . $ext;
            }

            $relPath = $cacheRelDir . "/" . $fileName;
            $absPath = public_path($relPath);

            if (!file_exists($absPath)) {
                $bytes = $this->fetchBinary($fileUrl);
                if ($bytes === null) {
                    continue;
                }
                file_put_contents($absPath, $bytes);
            }

            $cachedFaces[] = [
                "relPath" => $relPath,
                "format" => $format === "truetype" ? "truetype" : ($format === "opentype" ? "opentype" : "woff"),
                "weight" => (string) $weight,
                "style" => (string) $style,
            ];
        }

        $this->writeManifest($manifestPath, $cachedFaces);

        return $cachedFaces;
    }

    /**
     * @return array<int,array{url:string,format:string,weight:string,style:string}>
     */
    private function parseGoogleCssFaces(string $css, string $expectedFamily): array
    {
        $bestByWeightStyle = [];

        if (!preg_match_all("/@font-face\\s*\\{[^}]*\\}/i", $css, $matches)) {
            return [];
        }

        foreach ($matches[0] as $block) {
            $family = $this->matchCssProp($block, "font-family");
            if ($family === null) {
                continue;
            }

            $family = trim($family, "\"'");
            if (strcasecmp($family, $expectedFamily) !== 0) {
                continue;
            }

            $style = $this->matchCssProp($block, "font-style") ?? "normal";
            $weight = $this->matchCssProp($block, "font-weight") ?? "normal";
            $unicodeRange = $this->matchCssProp($block, "unicode-range") ?? "";

            $srcCandidates = $this->parseCssSrcCandidates($block);
            $best = $this->pickBestSrc($srcCandidates);
            if ($best === null) {
                continue;
            }

            $score = 2;
            if (stripos($unicodeRange, "U+0000-00FF") !== false) {
                $score = 0;
            } elseif (stripos($unicodeRange, "U+0000") !== false) {
                $score = 1;
            }

            $key = (string) $weight . "|" . (string) $style;
            $candidate = [
                "url" => $best["url"],
                "format" => $best["format"],
                "weight" => (string) $weight,
                "style" => (string) $style,
            ];

            if (!isset($bestByWeightStyle[$key]) || $score < $bestByWeightStyle[$key]["score"]) {
                $bestByWeightStyle[$key] = ["score" => $score, "face" => $candidate];
            }
        }

        $out = [];
        foreach ($bestByWeightStyle as $entry) {
            $face = $entry["face"] ?? null;
            if (is_array($face)) {
                $out[] = $face;
            }
        }

        return $out;
    }

    private function matchCssProp(string $block, string $prop): ?string
    {
        $pattern = "/{$prop}\\s*:\\s*([^;]+);/i";
        if (!preg_match($pattern, $block, $m)) {
            return null;
        }

        return trim((string) $m[1]);
    }

    /**
     * @return array<int,array{url:string,format:string}>
     */
    private function parseCssSrcCandidates(string $block): array
    {
        $candidates = [];
        if (!preg_match("/src\\s*:\\s*([^;]+);/i", $block, $m)) {
            return [];
        }

        $src = (string) $m[1];
        if (!preg_match_all("/url\\(([^)]+)\\)\\s*format\\(['\"]?([^'\")]+)['\"]?\\)/i", $src, $matches, PREG_SET_ORDER)) {
            return [];
        }

        foreach ($matches as $match) {
            $url = trim((string) $match[1], " \"'");
            $format = strtolower(trim((string) $match[2]));
            $candidates[] = ["url" => $url, "format" => $format];
        }

        return $candidates;
    }

    private function pickBestSrc(array $candidates): ?array
    {
        $priority = ["woff" => 1, "truetype" => 2, "opentype" => 3, "woff2" => 99];
        $best = null;
        $bestRank = 1000;

        foreach ($candidates as $c) {
            if (!isset($c["url"], $c["format"])) {
                continue;
            }
            $format = (string) $c["format"];
            $rank = $priority[$format] ?? 50;
            if ($rank < $bestRank) {
                $bestRank = $rank;
                $best = $c;
            }
        }

        // Ignore if only woff2 is available.
        if ($best !== null && ((string) ($best["format"] ?? "")) === "woff2") {
            return null;
        }

        return $best;
    }

    private function isAllowedGoogleFontFileUrl(string $url): bool
    {
        $parts = parse_url($url);
        if (!is_array($parts)) {
            return false;
        }

        $host = strtolower((string) ($parts["host"] ?? ""));
        $scheme = strtolower((string) ($parts["scheme"] ?? ""));
        if ($scheme !== "https") {
            return false;
        }

        return in_array($host, self::GOOGLE_FONT_FILE_HOST_ALLOWLIST, true);
    }

    private function fetchGoogleCss(string $url): ?string
    {
        try {
            // UA chosen to increase chances of receiving WOFF (not WOFF2) when available.
            $response = Http::withHeaders([
                "User-Agent" => "Mozilla/5.0 (Windows NT 6.1; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/49.0.2623.112 Safari/537.36",
                "Accept" => "text/css,*/*;q=0.1",
            ])->timeout(10)->get($url);

            if (!$response->ok()) {
                return null;
            }

            return (string) $response->body();
        } catch (\Throwable $e) {
            Log::warning("Failed to fetch Google Fonts CSS", [
                "url" => $url,
                "error" => $e->getMessage(),
            ]);
            return null;
        }
    }

    private function fetchBinary(string $url): ?string
    {
        try {
            $response = Http::withHeaders([
                "User-Agent" => "Mozilla/5.0",
            ])->timeout(15)->get($url);

            if (!$response->ok()) {
                return null;
            }

            return $response->body();
        } catch (\Throwable $e) {
            Log::warning("Failed to fetch Google Fonts binary", [
                "url" => $url,
                "error" => $e->getMessage(),
            ]);
            return null;
        }
    }

    private function readManifest(string $manifestPath): ?array
    {
        if (!file_exists($manifestPath)) {
            return null;
        }

        $raw = file_get_contents($manifestPath);
        if (!is_string($raw) || trim($raw) === "") {
            return null;
        }

        $decoded = json_decode($raw, true);
        if (!is_array($decoded)) {
            return null;
        }

        return $decoded;
    }

    private function writeManifest(string $manifestPath, array $faces): void
    {
        @file_put_contents($manifestPath, json_encode($faces, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
    }

    private function normalizeFsPathForCss(string $path): string
    {
        return str_replace("\\", "/", $path);
    }

    private function escapeCssString(string $value): string
    {
        return str_replace(["\\", "'"], ["\\\\", "\\'"], $value);
    }
}
