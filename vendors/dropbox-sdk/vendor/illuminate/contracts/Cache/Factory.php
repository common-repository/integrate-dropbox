<?php

namespace CodeConfig\IntegrateDropbox\vendor\Illuminate\Contracts\Cache;

interface Factory {
    /**
     * Get a cache store instance by name.
     *
     * @param  string|null  $name
     * @return \CodeConfig\IntegrateDropbox\vendor\Illuminate\Contracts\Cache\Repository
     */
    public function store( $name = null );
}
