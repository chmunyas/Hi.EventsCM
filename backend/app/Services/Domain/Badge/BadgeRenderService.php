<?php

declare(strict_types=1);

namespace HiEvents\Services\Domain\Badge;

use HiEvents\DomainObjects\AttendeeDomainObject;

class BadgeRenderService
{
    private const DEFAULT_PLACEHOLDERS = [
        '{{first_name}}',
        '{{last_name}}',
        '{{email}}',
        '{{product_name}}',
        '{{short_id}}',
        '{{public_id}}',
        '{{status}}',
        '{{qr_code}}',
    ];

    /**
     * Render a single badge by substituting placeholders in the template content.
     */
    public function renderBadge(string $templateContent, AttendeeDomainObject $attendee): string
    {
        $shortId = $attendee->getShortId() ?? '';

        $replacements = [
            '{{first_name}}' => e($attendee->getFirstName() ?? ''),
            '{{last_name}}' => e($attendee->getLastName() ?? ''),
            '{{email}}' => e($attendee->getEmail() ?? ''),
            '{{product_name}}' => e($attendee->getProduct()?->getTitle() ?? ''),
            '{{short_id}}' => e($shortId),
            '{{public_id}}' => e($attendee->getPublicId() ?? ''),
            '{{status}}' => e($attendee->getStatus() ?? ''),
            '{{qr_code}}' => $this->generateQrCodeDataUri($shortId),
        ];

        return str_replace(
            array_keys($replacements),
            array_values($replacements),
            $templateContent,
        );
    }

    /**
     * Render badges for multiple attendees, wrapping each in a page-break div.
     */
    public function renderBadges(string $templateContent, array $attendees): string
    {
        $badges = [];

        foreach ($attendees as $attendee) {
            $badges[] = '<div class="badge" style="page-break-after: always;">'
                . $this->renderBadge($templateContent, $attendee)
                . '</div>';
        }

        return $this->wrapInPrintableHtml(implode("\n", $badges));
    }

    /**
     * Return the list of supported placeholder tokens.
     */
    public function getAvailablePlaceholders(): array
    {
        return self::DEFAULT_PLACEHOLDERS;
    }

    private function generateQrCodeDataUri(string $data): string
    {
        if (empty($data)) {
            return '';
        }

        // Generate a Google Charts QR code URL as a lightweight fallback.
        // In production this could be replaced with a local QR library.
        return 'https://chart.googleapis.com/chart?cht=qr&chs=200x200&chl=' . urlencode($data);
    }

    private function wrapInPrintableHtml(string $body): string
    {
        return <<<HTML
<!DOCTYPE html>
<html>
<head>
<meta charset="utf-8">
<title>Badges</title>
<style>
@media print {
  .badge { page-break-after: always; }
}
body { margin: 0; padding: 0; font-family: sans-serif; }
</style>
</head>
<body>
{$body}
</body>
</html>
HTML;
    }
}
