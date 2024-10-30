<?php

namespace CodeConfig\IntegrateDropbox\vendor\Illuminate\Contracts\Mail;

interface Factory {
    /**
     * Get a mailer instance by name.
     *
     * @param  string|null  $name
     * @return \CodeConfig\IntegrateDropbox\vendor\Illuminate\Contracts\Mail\Mailer
     */
    public function mailer( $name = null );
}
