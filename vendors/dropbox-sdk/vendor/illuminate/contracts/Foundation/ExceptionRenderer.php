<?php

namespace CodeConfig\IntegrateDropbox\vendor\Illuminate\Contracts\Foundation;

interface ExceptionRenderer {
    /**
     * Renders the given exception as HTML.
     *
     * @param  \Throwable  $throwable
     * @return string
     */
    public function render( $throwable );
}
