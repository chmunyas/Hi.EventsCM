<?php

namespace HiEvents\Services\Domain\SelfService\DTO;

class CancelOrderResultDTO
{
    public function __construct(
        public readonly bool $success,
        public readonly bool $refunded,
    ) {
    }
}
