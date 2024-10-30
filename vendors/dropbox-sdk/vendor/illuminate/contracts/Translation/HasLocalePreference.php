<?php

namespace CodeConfig\IntegrateDropbox\vendor\Illuminate\Contracts\Translation;

interface HasLocalePreference {
    /**
     * Get the preferred locale of the entity.
     *
     * @return string|null
     */
    public function preferredLocale();
}
