<?php

namespace CodeConfig\IntegrateDropbox\vendor\Illuminate\Contracts\Queue;

interface ClearableQueue {
    /**
     * Delete all of the jobs from the queue.
     *
     * @param  string  $queue
     * @return int
     */
    public function clear( $queue );
}
