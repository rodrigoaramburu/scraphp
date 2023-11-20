<?php

declare(strict_types=1);

namespace ScraPHP;

abstract class ProcessPage
{
    private ScraPHP $scraphp;

    abstract public function process(Page $page): void;

    /**
     * Set the ScraPHP instance.
     *
     * @param ScraPHP $scraphp The ScraPHP instance to set.
     * @return self
     */
    public function withScraPHP(ScraPHP $scraphp): self
    {
        $this->scraphp = $scraphp;
        return $this;
    }

    /**
     * Calls a method on the 'scraphp' object dynamically.
     *
     * @param string $name The name of the method to call.
     * @param array $arguments The arguments to pass to the method.
     */
    public function __call($name, $arguments)
    {
        return $this->scraphp->{$name}(...$arguments);
    }
}
