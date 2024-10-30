<?php

namespace CodeConfig\IntegrateDropbox\vendor\Illuminate\Contracts\Support;

interface DeferringDisplayableValue {
    /**
     * Resolve the displayable value that the class is deferring.
     *
     * @return \CodeConfig\IntegrateDropbox\vendor\Illuminate\Contracts\Support\Htmlable|string
     */
    public function resolveDisplayableValue();
}
