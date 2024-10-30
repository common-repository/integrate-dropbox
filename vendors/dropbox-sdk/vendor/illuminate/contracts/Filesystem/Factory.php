<?php

namespace CodeConfig\IntegrateDropbox\vendor\Illuminate\Contracts\Filesystem;

interface Factory {
    /**
     * Get a filesystem implementation.
     *
     * @param  string|null  $name
     * @return \CodeConfig\IntegrateDropbox\vendor\Illuminate\Contracts\Filesystem\Filesystem
     */
    public function disk( $name = null );
}
