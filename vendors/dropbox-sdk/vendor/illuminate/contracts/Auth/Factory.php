<?php

namespace CodeConfig\IntegrateDropbox\vendor\Illuminate\Contracts\Auth;

interface Factory {
    /**
     * Get a guard instance by name.
     *
     * @param  string|null  $name
     * @return \CodeConfig\IntegrateDropbox\vendor\Illuminate\Contracts\Auth\Guard|\CodeConfig\IntegrateDropbox\vendor\Illuminate\Contracts\Auth\StatefulGuard
     */
    public function guard( $name = null );

    /**
     * Set the default guard the factory should serve.
     *
     * @param  string  $name
     * @return void
     */
    public function shouldUse( $name );
}
