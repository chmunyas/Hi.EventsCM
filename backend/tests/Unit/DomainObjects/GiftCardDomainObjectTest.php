<?php

declare(strict_types=1);

namespace Tests\Unit\DomainObjects;

use HiEvents\DomainObjects\GiftCardDomainObject;
use Tests\TestCase;

class GiftCardDomainObjectTest extends TestCase
{
    private function makeCard(array $overrides = []): GiftCardDomainObject
    {
        $data = array_merge([
            'id' => 1,
            'account_id' => 1,
            'code' => 'ABCD-EFGH-IJKL',
            'original_amount' => 100.00,
            'balance' => 75.50,
            'currency' => 'USD',
            'status' => 'active',
            'purchaser_name' => 'Admin',
            'purchaser_email' => 'admin@example.com',
            'recipient_name' => 'Jane',
            'recipient_email' => 'jane@example.com',
            'personal_message' => 'Happy birthday!',
            'expires_at' => null,
            'sent_at' => null,
        ], $overrides);

        return GiftCardDomainObject::hydrateFromArray($data);
    }

    public function test_active_card_is_active(): void
    {
        $card = $this->makeCard(['status' => 'active', 'balance' => 50.00]);

        $this->assertTrue($card->isActive());
    }

    public function test_disabled_card_is_not_active(): void
    {
        $card = $this->makeCard(['status' => 'disabled']);

        $this->assertFalse($card->isActive());
    }

    public function test_depleted_card_is_not_active(): void
    {
        $card = $this->makeCard(['status' => 'depleted', 'balance' => 0]);

        $this->assertFalse($card->isActive());
    }

    public function test_zero_balance_card_is_not_active(): void
    {
        $card = $this->makeCard(['status' => 'active', 'balance' => 0]);

        $this->assertFalse($card->isActive());
    }

    public function test_expired_card_is_not_active(): void
    {
        $card = $this->makeCard([
            'status' => 'active',
            'balance' => 50.00,
            'expires_at' => '2020-01-01 00:00:00',
        ]);

        $this->assertTrue($card->isExpired());
        $this->assertFalse($card->isActive());
    }

    public function test_future_expiry_card_is_active(): void
    {
        $card = $this->makeCard([
            'status' => 'active',
            'balance' => 50.00,
            'expires_at' => '2099-12-31 23:59:59',
        ]);

        $this->assertFalse($card->isExpired());
        $this->assertTrue($card->isActive());
    }

    public function test_null_expiry_means_not_expired(): void
    {
        $card = $this->makeCard(['expires_at' => null]);

        $this->assertFalse($card->isExpired());
    }

    public function test_balance_and_currency_accessible(): void
    {
        $card = $this->makeCard(['balance' => 75.50, 'currency' => 'GBP']);

        $this->assertEquals(75.50, $card->getBalance());
        $this->assertEquals('GBP', $card->getCurrency());
    }
}
