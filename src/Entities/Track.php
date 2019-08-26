<?php

    namespace Polaris\Entities;

    use Polaris\Entity;

    class Track extends Entity
    {
        /**
         * Casts this attributes.
         *
         * @var array
         */
        protected $casts = [
            'album'   => Album::class,
            // 'artists' => ArtistsCollection::class,
        ];

        /**
         * The key in the data for this entity.
         *
         * @var string
         */
        protected $key = 'track';

        /**
         * Attributes to merge.
         *
         * @var array
         */
        protected $extras = [
            'played_at',
            'context',
        ];

    }