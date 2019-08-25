<?php

    namespace Polaris\Entities;

    use Polaris\Entity;

    class Track extends Entity
    {
        protected $casts = [
            'album' => Album::class,
        ];
    }