<?php

namespace CodeConfig\IntegrateDropbox\vendor\Illuminate\Contracts\Auth;

interface PasswordBrokerFactory {
    /**
     * Get a password broker instance by name.
     *
     * @param  string|null  $name
     * @return \CodeConfig\IntegrateDropbox\vendor\Illuminate\Contracts\Auth\PasswordBroker
     */
    public function broker( $name = null );
}
