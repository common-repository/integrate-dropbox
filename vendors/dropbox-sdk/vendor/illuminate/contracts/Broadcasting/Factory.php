<?php

namespace CodeConfig\IntegrateDropbox\vendor\Illuminate\Contracts\Broadcasting;

interface Factory {
    /**
     * Get a broadcaster implementation by name.
     *
     * @param  string|null  $name
     * @return \CodeConfig\IntegrateDropbox\vendor\Illuminate\Contracts\Broadcasting\Broadcaster
     */
    public function connection( $name = null );
}
