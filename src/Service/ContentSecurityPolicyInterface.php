<?php

namespace Drupal\wmcontent_security_policy\Service;

interface ContentSecurityPolicyInterface
{
    // @see https://developer.mozilla.org/en-US/docs/Web/HTTP/Headers/Content-Security-Policy#directives
    public const POLICY_DIRECTIVES = [
        // Fetch directives.
        // Fetch directives control the locations from which certain resource types may be loaded.
        'child-src' => ' Defines the valid sources for web workers and nested browsing contexts loaded using elements such as <frame> and <iframe>.',
        'connect-src' => 'Restricts the URLs which can be loaded using script interfaces such as <a>, Fetch and XMLHttpRequest',
        'default-src' => 'A fallback for other resource types when they don\'t have policies of their own',
        'font-src' => 'Specifies valid sources for fonts loaded using @font-face',
        'frame-src' => 'Specifies valid sources for nested browsing contexts loading using elements such as <iframe>',
        'img-src' => 'Specifies valid sources of images and favicons',
        'manifest-src' => 'Specifies valid sources of application manifest files.',
        'media-src' => 'Specifies valid sources for loading media using the <audio> and <video> elements',
        'object-src' => 'Specifies valid sources for the <object>, <embed>, and <applet> elements.',
        'script-src' => 'Specifies valid sources for JavaScript',
        'script-src-attr' => 'Specifies valid sources for JavaScript inline event handlers.',
        'script-src-elem' => 'Specifies valid sources for JavaScript <script> elements.',
        'style-src' => 'Specifies valid sources for sources for stylesheets',
        'style-src-attr' => 'Specifies valid sources for inline styles applied to individual DOM elements.',
        'style-src-elem' => 'Specifies valid sources for stylesheets <style> elements and <link> elements with rel="stylesheet".',
        'worker-src' => 'Specifies valid sources for Worker, SharedWorker, or ServiceWorker scripts.',

        // Document directives.
        // Document directives govern the properties of a document or worker environment to which a policy applies.
        'base-uri' => 'Restricts the URLs which can be used in a document\'s <base> element.',
        'sandbox' => 'Enables a sandbox for the requested resource similar to the <iframe> sandbox attribute.',

        // Navigation directives.
        //  Navigation directives govern to which locations a user can navigate or submit a form, for example.
        'form-action' => 'Restricts the URLs which can be used as the target of a form submissions from a given context.',
        'frame-ancestors' => 'Specifies valid parents that may embed a page using <frame>, <iframe>, <object>, or <embed>.',

        // Other directives.
        'upgrade-insecure-requests' => 'Instructs user agents to treat all of a site\'s insecure URLs (those served over HTTP) as though they have been replaced with secure URLs (those served over HTTPS). This directive is intended for websites with large numbers of insecure legacy URLs that need to be rewritten.',
    ];

    public const REPORT_TO_CSP_ENDPOINT_NAME = 'csp-endpoint';

    public function getDefaultSources(string $directive): array;

    public function setDefaultSources(string $directive, array $sources = []): void;

    public function getSources(string $directive);

    public function setSources(string $directive, array $sources = []): void;

    public function addSource(string $directive, string $source): void;

    public function getHeader(): string;

    public function addScriptHash(string $hash): void;

    public function getReportTo(): ?string;

    public function setReportTo(?string $url): void;
}
