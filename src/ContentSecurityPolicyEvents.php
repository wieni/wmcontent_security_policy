<?php

namespace Drupal\wmcontent_security_policy;

final class ContentSecurityPolicyEvents
{
    /**
     * Allows you to dynamically add sources right before the
     * header is built.
     *
     * The event object is an instance of
     * @uses \Drupal\wmcontent_security_policy\Event\SourcesAlterEvent
     */
    public const SOURCES_ALTER = 'wmcontent_security_policy.sources.alter';
}
