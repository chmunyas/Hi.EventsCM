<?php

namespace Tests\Unit\Services\Domain\Attendee;

use HiEvents\Services\Domain\Attendee\BulkAttendeeImportService;
use Tests\TestCase;

class BulkAttendeeImportServiceTest extends TestCase
{
    private BulkAttendeeImportService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = new BulkAttendeeImportService();
    }

    public function test_parse_valid_csv_returns_valid_rows(): void
    {
        $csv = "first_name,last_name,email\nAlice,Smith,alice@example.com\nBob,Jones,bob@test.com";

        $result = $this->service->parseAndValidate($csv, 1);

        $this->assertEmpty($result['errors']);
        $this->assertCount(2, $result['valid']);
        $this->assertEquals('Alice', $result['valid'][0]['first_name']);
        $this->assertEquals('bob@test.com', $result['valid'][1]['email']);
    }

    public function test_parse_missing_required_headers_returns_error(): void
    {
        $csv = "name,email\nAlice,alice@test.com";

        $result = $this->service->parseAndValidate($csv, 1);

        $this->assertNotEmpty($result['errors']);
        $this->assertStringContainsString('Missing required columns', $result['errors'][0]);
        $this->assertEmpty($result['valid']);
    }

    public function test_parse_empty_csv_returns_error(): void
    {
        $csv = "first_name,last_name,email";

        $result = $this->service->parseAndValidate($csv, 1);

        $this->assertNotEmpty($result['errors']);
        $this->assertStringContainsString('at least one data row', $result['errors'][0]);
    }

    public function test_parse_invalid_email_reports_row_error(): void
    {
        $csv = "first_name,last_name,email\nAlice,Smith,not-an-email";

        $result = $this->service->parseAndValidate($csv, 1);

        $this->assertNotEmpty($result['errors']);
        $this->assertStringContainsString('Row 2', $result['errors'][0]);
        $this->assertEmpty($result['valid']);
    }

    public function test_parse_column_count_mismatch_reports_error(): void
    {
        $csv = "first_name,last_name,email\nAlice,Smith";

        $result = $this->service->parseAndValidate($csv, 1);

        $this->assertNotEmpty($result['errors']);
        $this->assertStringContainsString('Column count mismatch', $result['errors'][0]);
    }

    public function test_parse_csv_with_optional_product_id(): void
    {
        $csv = "first_name,last_name,email,product_id\nAlice,Smith,alice@test.com,42";

        $result = $this->service->parseAndValidate($csv, 1);

        $this->assertEmpty($result['errors']);
        $this->assertCount(1, $result['valid']);
        $this->assertEquals('42', $result['valid'][0]['product_id']);
    }

    public function test_parse_mixed_valid_and_invalid_rows(): void
    {
        $csv = "first_name,last_name,email\nAlice,Smith,alice@test.com\n,,\nBob,Jones,bob@test.com";

        $result = $this->service->parseAndValidate($csv, 1);

        $this->assertCount(2, $result['valid']);
        $this->assertCount(1, $result['errors']);
        $this->assertStringContainsString('Row 3', $result['errors'][0]);
    }

    public function test_parse_trims_whitespace_from_headers(): void
    {
        $csv = " first_name , last_name , email \nAlice,Smith,alice@test.com";

        $result = $this->service->parseAndValidate($csv, 1);

        $this->assertEmpty($result['errors']);
        $this->assertCount(1, $result['valid']);
    }
}
