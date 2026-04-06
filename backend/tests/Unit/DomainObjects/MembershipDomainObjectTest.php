<?php

declare(strict_types=1);

namespace Tests\Unit\DomainObjects;

use HiEvents\DomainObjects\MembershipDomainObject;
use Tests\TestCase;

class MembershipDomainObjectTest extends TestCase
{
    private function makeMembership(array $overrides = []): MembershipDomainObject
    {
        $data = array_merge([
            'id' => 1,
            'membership_plan_id' => 1,
            'account_id' => 1,
            'member_email' => 'member@example.com',
            'member_name' => 'Jane Doe',
            'membership_number' => 'MEM-ABCD1234',
            'status' => 'active',
            'starts_at' => '2026-01-01 00:00:00',
            'expires_at' => null,
            'auto_renew' => false,
            'events_used' => 0,
            'stripe_subscription_id' => null,
            'notes' => null,
        ], $overrides);

        return MembershipDomainObject::hydrateFromArray($data);
    }

    public function test_active_membership_is_active(): void
    {
        $m = $this->makeMembership(['status' => 'active']);

        $this->assertTrue($m->isActive());
    }

    public function test_cancelled_membership_is_not_active(): void
    {
        $m = $this->makeMembership(['status' => 'cancelled']);

        $this->assertFalse($m->isActive());
    }

    public function test_suspended_membership_is_not_active(): void
    {
        $m = $this->makeMembership(['status' => 'suspended']);

        $this->assertFalse($m->isActive());
    }

    public function test_expired_membership_is_not_active(): void
    {
        $m = $this->makeMembership([
            'status' => 'active',
            'expires_at' => '2020-01-01 00:00:00',
        ]);

        $this->assertFalse($m->isActive());
    }

    public function test_future_expiry_membership_is_active(): void
    {
        $m = $this->makeMembership([
            'status' => 'active',
            'expires_at' => '2099-12-31 23:59:59',
        ]);

        $this->assertTrue($m->isActive());
    }

    public function test_null_expiry_membership_is_active(): void
    {
        $m = $this->makeMembership([
            'status' => 'active',
            'expires_at' => null,
        ]);

        $this->assertTrue($m->isActive());
    }

    public function test_membership_fields_accessible(): void
    {
        $m = $this->makeMembership([
            'member_name' => 'John Smith',
            'membership_number' => 'MEM-TEST0001',
            'member_email' => 'john@test.com',
        ]);

        $this->assertEquals('John Smith', $m->getMemberName());
        $this->assertEquals('MEM-TEST0001', $m->getMembershipNumber());
        $this->assertEquals('john@test.com', $m->getMemberEmail());
    }

    public function test_has_event_capacity_returns_true(): void
    {
        $m = $this->makeMembership();

        $this->assertTrue($m->hasEventCapacity());
    }
}
