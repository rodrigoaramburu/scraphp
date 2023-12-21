<?php

declare(strict_types=1);

namespace ScraPHP;

use ScraPHP\HttpClient\Page;

abstract class ProcessPage
{
    private ScraPHP $scraphp;


    abstract public function process(Page $page): void;

    /**
     * Set the ScraPHP instance.
     *
     * @param  ScraPHP  $scraphp The ScraPHP instance to set.
     */
    public function withScraPHP(ScraPHP $scraphp): self
    {
        $this->scraphp = $scraphp;

        return $this;
    }

    /**
     * Calls a method on the 'scraphp' object dynamically.
     *
     * @param  string  $name The name of the method to call.
     * @param  array<mixed>  $arguments The arguments to pass to the method.
     *
     * @return mixed The result of the method call.
     */
    public function __call(string $name, array $arguments): mixed
    {
        return $this->scraphp->{$name}(...$arguments);
    }
}
