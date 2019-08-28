<?php

    namespace Polaris\Entities\Collections;

    use Polaris\Entity;
    use Polaris\EntityCollection;

    abstract class BaseCollection extends EntityCollection
    {

        /**
         * The entity that this collection holds inside.
         *
         * @var Entity
         */
        protected $entity;

        /**
         * RecentlyPlayedCollection constructor.
         *
         * @param  mixed  $data
         */
        public function __construct($data = [])
        {
            // Normalize data as array.
            if ($data instanceof \stdClass) {
                $data = json_decode(json_encode($data), true);
            }

            parent::__construct($data);
        }

        public function next()
        {
        }
    }