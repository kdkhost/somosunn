<?php

namespace App\Services\Certificate;

class CertificateSettingsNormalizer
{
    public const SCHEMA_VERSION = 2;

    /**
     * Normalizes legacy and v2 certificate_settings into schema v2:
     * [
     *   'schemaVersion' => 2,
     *   'meta' => [ 'backgroundFit' => 'cover'|'stretch', ... ],
     *   'elements' => [ '<tag>' => [ ...elementProps ] ]
     * ]
     *
     * This method MUST NOT mutate saved layout coordinates (x/y) or other numeric
     * values, except for providing deterministic defaults when missing.
     *
     * @param array|null $raw
     * @return array{schemaVersion:int,meta:array,elements:array<string,array>}
     */
    public function normalize(?array $raw): array
    {
        $raw = is_array($raw) ? $raw : [];

        $isV2 = ($raw["schemaVersion"] ?? null) === self::SCHEMA_VERSION
            && isset($raw["elements"])
            && is_array($raw["elements"]);

        if ($isV2) {
            $meta = isset($raw["meta"]) && is_array($raw["meta"]) ? $raw["meta"] : [];
            $elements = $raw["elements"];
        } else {
            $meta = $this->extractLegacyMeta($raw);
            $elements = $this->extractLegacyElements($raw);
        }

        $meta = $this->normalizeMeta($meta);
        $elements = $this->normalizeElements($elements);

        return [
            "schemaVersion" => self::SCHEMA_VERSION,
            "meta" => $meta,
            "elements" => $elements,
        ];
    }

    private function extractLegacyMeta(array $raw): array
    {
        $meta = [];

        if (isset($raw["backgroundFit"]) && is_string($raw["backgroundFit"])) {
            $meta["backgroundFit"] = $raw["backgroundFit"];
        }

        $title = null;
        if (isset($raw["custom_title"]) && is_string($raw["custom_title"])) {
            $title = $raw["custom_title"];
        } elseif (isset($raw["title"]) && is_string($raw["title"])) {
            $title = $raw["title"];
        }
        if ($title !== null) {
            $meta["titleText"] = $title;
        }

        $presentation = null;
        if (isset($raw["custom_presentation_text"]) && is_string($raw["custom_presentation_text"])) {
            $presentation = $raw["custom_presentation_text"];
        } elseif (isset($raw["presentation_text"]) && is_string($raw["presentation_text"])) {
            $presentation = $raw["presentation_text"];
        }
        if ($presentation !== null) {
            $meta["presentationText"] = $presentation;
        }

        return $meta;
    }

    /**
     * @return array<string,array>
     */
    private function extractLegacyElements(array $raw): array
    {
        $elements = [];

        foreach ($raw as $key => $value) {
            if (!is_string($key) || !is_array($value)) {
                continue;
            }

            // A legacy "element" is an array with x/y coordinates.
            if (!array_key_exists("x", $value) || !array_key_exists("y", $value)) {
                continue;
            }

            $elements[$key] = $value;
        }

        return $elements;
    }

    private function normalizeMeta(array $meta): array
    {
        $backgroundFit = $meta["backgroundFit"] ?? "cover";
        if (!in_array($backgroundFit, ["cover", "stretch"], true)) {
            $backgroundFit = "cover";
        }

        $normalized = [
            "backgroundFit" => $backgroundFit,
        ];

        if (isset($meta["titleText"]) && is_string($meta["titleText"])) {
            $normalized["titleText"] = $meta["titleText"];
        }

        if (isset($meta["presentationText"]) && is_string($meta["presentationText"])) {
            $normalized["presentationText"] = $meta["presentationText"];
        }

        return $normalized;
    }

    /**
     * @param array<string,mixed> $elements
     * @return array<string,array>
     */
    private function normalizeElements(array $elements): array
    {
        $normalized = [];

        foreach ($elements as $key => $element) {
            if (!is_string($key) || !is_array($element)) {
                continue;
            }

            // Require x/y to consider it renderable.
            if (!array_key_exists("x", $element) || !array_key_exists("y", $element)) {
                continue;
            }

            // Provide deterministic defaults without changing existing values.
            if (!array_key_exists("visible", $element)) {
                $element["visible"] = true;
            }
            if (!array_key_exists("locked", $element)) {
                $element["locked"] = false;
            }
            if (!array_key_exists("zIndex", $element)) {
                $element["zIndex"] = 10;
            }
            if (!array_key_exists("fontFamily", $element)) {
                $element["fontFamily"] = "Arial, sans-serif";
            }
            if (!array_key_exists("fontSize", $element)) {
                $element["fontSize"] = 16;
            }
            if (!array_key_exists("fontWeight", $element)) {
                $element["fontWeight"] = "normal";
            }
            if (!array_key_exists("color", $element)) {
                $element["color"] = "#000000";
            }

            if ($key === "platform_logo") {
                $element["mandatory"] = true;
                $element["visible"] = true;
                if (!array_key_exists("width", $element)) {
                    $element["width"] = 120;
                }
                if (!array_key_exists("height", $element)) {
                    $element["height"] = 60;
                }
            }

            $normalized[$key] = $element;
        }

        // Enforce mandatory platform logo defaults if missing.
        if (!isset($normalized["platform_logo"])) {
            $normalized["platform_logo"] = [
                "x" => 50,
                "y" => 10,
                "width" => 120,
                "height" => 60,
                "zIndex" => 20,
                "visible" => true,
                "locked" => false,
                "mandatory" => true,
            ];
        }

        return $normalized;
    }
}

