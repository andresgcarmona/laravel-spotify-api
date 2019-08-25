<?php

    namespace Polaris\Entities;

    use Polaris\Entities\Collections\ArtistsCollection;
    use Polaris\Entity;

    class Album extends Entity
    {
        protected $casts = [
            'artists' => ArtistsCollection::class,
        ];
    }