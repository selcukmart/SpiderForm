<?php

declare(strict_types=1);

namespace FormGenerator\V2\Contracts;

/**
 * Builder Interface for Chain Pattern
 *
 * @author selcukmart
 * @since 2.0.0
 */
interface BuilderInterface
{
    /**
     * Set form name
     */
    public function setName(string $name): self;

    /**
     * Get form name
     */
    public function getName(): string;

    /**
     * Set form method (GET/POST)
     */
    public function setMethod(string $method): self;

    /**
     * Set form action URL
     */
    public function setAction(string $action): self;

    /**
     * Set form scope (add/edit/view)
     */
    public function setScope(ScopeType $scope): self;

    /**
     * Set data provider
     */
    public function setDataProvider(DataProviderInterface $provider): self;

    /**
     * Set renderer engine
     */
    public function setRenderer(RendererInterface $renderer): self;

    /**
     * Set theme
     */
    public function setTheme(ThemeInterface $theme): self;

    /**
     * Build and return form HTML
     */
    public function build(): string;

    /**
     * Get form configuration as array
     *
     * @return array<string, mixed>
     */
    public function toArray(): array;
}
