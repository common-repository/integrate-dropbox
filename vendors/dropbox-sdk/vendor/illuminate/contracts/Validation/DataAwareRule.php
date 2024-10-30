<?php

namespace CodeConfig\IntegrateDropbox\vendor\Illuminate\Contracts\Validation;

interface DataAwareRule {
    /**
     * Set the data under validation.
     *
     * @param  array  $data
     * @return $this
     */
    public function setEditData( array $data );
}
