<?php

namespace HiEvents\DomainObjects;

use HiEvents\DomainObjects\Generated\EventSeriesDomainObjectAbstract;
use Illuminate\Support\Collection;

class EventSeriesDomainObject extends EventSeriesDomainObjectAbstract
{
    private ?Collection $occurrences = null;

    public function getOccurrences(): ?Collection
    {
        return $this->occurrences;
    }

    public function setOccurrences(?Collection $occurrences): self
    {
        $this->occurrences = $occurrences;
        return $this;
    }
}
