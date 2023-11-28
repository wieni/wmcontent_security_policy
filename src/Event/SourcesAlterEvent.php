<?php

namespace Drupal\wmcontent_security_policy\Event;

use Drupal\Component\EventDispatcher\Event;

class SourcesAlterEvent extends Event
{
    /** @var array */
    protected $sources;

    public function __construct(
        array &$sources
    ) {
        $this->sources = &$sources;
    }

    public function getSources(): array
    {
        return $this->sources;
    }

    public function hasSource(string $directive, string $source): bool
    {
        return isset($this->sources[$directive])
            && in_array($source, $this->sources[$directive], true);
    }

    public function addSource(string $directive, string $source): self
    {
        $this->sources[$directive][] = $source;

        return $this;
    }

    public function removeSource(string $directive, string $source): self
    {
        if (!isset($this->sources[$directive])) {
            return $this;
        }

        $key = array_search($source, $this->sources[$directive], true);

        if ($key !== false) {
            unset($this->sources[$directive][$key]);
        }

        return $this;
    }
}
