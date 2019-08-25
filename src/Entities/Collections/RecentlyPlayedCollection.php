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

        /**
         * RecentlyPlayedCollection constructor.
         *
         * @param  array  $data
         */
        public function __construct($data)
        {
            // Normalize data as array.
            if($data instanceof \stdClass) {
                $data = json_decode(json_encode($data), true);
            }

            $tracks = $data['items'];

            // Add this data to track item itself, so it is accesible from inside the Track entity.
            foreach ($tracks as $k => $track) {
                // Assign updated track back into the tracks array.
                $tracks[$k] = array_merge($tracks[$k], $track['track']);

                unset($tracks[$k]['track']);
            }

            // Reassign items in data to the modified version of tracks.
            $data['items'] = $tracks;

            // Call parent constructor.
            parent::__construct($data);
        }
    }