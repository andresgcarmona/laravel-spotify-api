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
         * Stores the cursor information (For pagination purposes.
         *
         * @var
         */
        protected $cursors;

        /**
         * Holds the next url, to get the next results from server. Timestamp based.
         *
         * @var
         */
        protected $next;

        /**
         * Holds the previous url, to get the previous results from server. Timestamp based.
         *
         * @var
         */
        protected $previous;

        /**
         * The url from where this collection is going to get the results.
         *
         * @var
         */
        protected $href;

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