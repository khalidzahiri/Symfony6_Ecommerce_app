<?php

namespace App\Entity\Trait;

use Doctrine\ORM\Mapping\Column;

trait SlugTrait
{
    #[Column(type: 'string', length: 255 )]
    private  $slug = null;

    public function getSlug(): ?string
    {
        return $this->slug;
    }

    public function setSlug(string $slug): self
    {
        $this->slug = $slug;

        return $this;
    }
}
