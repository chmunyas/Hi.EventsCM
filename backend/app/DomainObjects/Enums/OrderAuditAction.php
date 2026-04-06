<?php

namespace HiEvents\DomainObjects\Enums;

enum OrderAuditAction: string
{
    use BaseEnum;

    case ATTENDEE_UPDATED = 'ATTENDEE_UPDATED';
    case ORDER_UPDATED = 'ORDER_UPDATED';
    case ORDER_SELF_CANCELLED = 'ORDER_SELF_CANCELLED';
    case ATTENDEE_EMAIL_RESENT = 'ATTENDEE_EMAIL_RESENT';
    case ORDER_EMAIL_RESENT = 'ORDER_EMAIL_RESENT';
}
