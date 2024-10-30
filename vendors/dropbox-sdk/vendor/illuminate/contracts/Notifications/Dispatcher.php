<?php

namespace CodeConfig\IntegrateDropbox\vendor\Illuminate\Contracts\Notifications;

interface Dispatcher {
    /**
     * Send the given notification to the given notifiable entities.
     *
     * @param  \CodeConfig\IntegrateDropbox\vendor\Illuminate\Support\Collection|array|mixed  $notifiables
     * @param  mixed  $notification
     * @return void
     */
    public function send( $notifiables, $notification );

    /**
     * Send the given notification immediately.
     *
     * @param  \CodeConfig\IntegrateDropbox\vendor\Illuminate\Support\Collection|array|mixed  $notifiables
     * @param  mixed  $notification
     * @return void
     */
    public function sendNow( $notifiables, $notification );
}
