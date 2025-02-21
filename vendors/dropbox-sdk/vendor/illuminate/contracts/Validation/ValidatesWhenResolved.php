<?php

namespace CodeConfig\IntegrateDropbox\vendor\Illuminate\Contracts\Validation;

interface ValidatesWhenResolved {
    /**
     * Validate the given class instance.
     *
     * @return void
     */
    public function validateResolved();
}
