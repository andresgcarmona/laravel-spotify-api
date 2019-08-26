<?php

    namespace Polaris\Entities\Collections;

    use Polaris\Entities\Artist;

    class ArtistsCollection extends BaseCollection
    {
        protected $entity = Artist::class;

        protected $key = null;
    }