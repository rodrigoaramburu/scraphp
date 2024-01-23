<?php

declare(strict_types=1);

namespace ScraPHP\HttpClient;

use ScraPHP\Link;
use ScraPHP\Image;
use ScraPHP\Exceptions\InvalidLinkException;
use ScraPHP\Exceptions\InvalidImageException;

interface FilteredElement
{
    /**
     * Gets the text content of the element.
     *
     * @return string The text content of the element.
     */
    public function text(): string;

    /**
     * Gets the value of the specified attribute of element.
     *
     * @param  string  $attr The name of the attribute to get.
     * @return string|null The value of the specified attribute, or null if it does not exist.
     */
    public function attr(string $attr): ?string;


    /**
     * Filters an element based on the given CSS selector.
     *
     * @param string $cssSelector The CSS selector to filter the elements.
     * @return FilteredElement|null The filtered element or null if no element is found.
     */
    public function filterCSS(string $cssSelector): ?FilteredElement;

    /**
     * Filters the elements using the given CSS selector and applies a callback
     * function to each element.
     *
     * @param string $cssSelector The CSS selector used to filter the elements.
     * @param callable $callback The callback function to be applied to each filtered element.

     * @return array<int,mixed> An array containing the results of applying the callback
     *                          function to each filtered element.
     */
    public function filterCSSEach(string $cssSelector, callable $callback): array;


    /**
     * Gets a link from a element.
     *
     * @return Link The created link object.
     *
     * @throws InvalidLinkException If unable to get the link.
     */
    public function link(): Link;

    /**
     * Gets the image from a element.
     *
     * @return Image The created image.
     *
     * @throws InvalidImageException If unable to get the image.
     */
    public function image(): Image;


    /**
     * Executes a regex match on the text and returns the first match or null.
     *
     * @param string $regex The regular expression to match
     * @param string|null $groupName The name of the capture group to return
     * @return string|null The first match or null if no match
     */
    public function regex(string $regex, ?string $groupName = null): ?string;
}
