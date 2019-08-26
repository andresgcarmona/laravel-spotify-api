<?php

    namespace Polaris\Entities\Collections;

    use Polaris\Entities\Track;

    /**
     * Represents a collection of tracks that were recently played by the user.
     *
     * Class RecentlyPlayedCollection
     *
     * @package Polaris\Entities
     */
    class RecentlyPlayedCollection extends BaseCollection
    {
        /**
         * The entity of this collection.
         *
         * @var string
         */
        protected $entity = Track::class;

        protected $extras = [
            'next',
            'previous',
            'cursors',
            'href',
        ];
    }