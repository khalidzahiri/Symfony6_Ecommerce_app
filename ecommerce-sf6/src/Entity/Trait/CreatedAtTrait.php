<?php

namespace App\Entity\Trait;

use Doctrine\ORM\Mapping\Column;

trait CreatedAtTrait
{

    #[Column(options: ['default => CURRANT_TIMESTAMP'])]
    private ?\DateTimeImmutable $created_at = null;

    public function getCreatedAt(): ?\DateTimeImmutable
    {
        return $this->created_at;
    }

    public function setCreatedAt(\DateTimeImmutable $created_at): self
    {
        $this->created_at = $created_at;

        return $this;
    }
}
