<?php

    namespace Polaris\Entities;

    use Polaris\Entities\Collections\TracksCollection;
    use Polaris\Entity;

    class RecentlyPlayed extends Entity
    {
        protected $casts = [
            'items' => TracksCollection::class,
        ];
    }