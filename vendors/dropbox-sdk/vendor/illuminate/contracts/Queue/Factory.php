<?php

namespace CodeConfig\IntegrateDropbox\vendor\Illuminate\Contracts\Queue;

interface Factory {
    /**
     * Resolve a queue connection instance.
     *
     * @param  string|null  $name
     * @return \CodeConfig\IntegrateDropbox\vendor\Illuminate\Contracts\Queue\Queue
     */
    public function connection( $name = null );
}
