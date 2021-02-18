<?php

namespace Drupal\wmcontent_security_policy\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\wmcontent_security_policy\Service\ContentSecurityPolicyService;
use Symfony\Component\DependencyInjection\ContainerInterface;

class ContentSecurityPolicyForm extends FormBase
{
    /** @var ContentSecurityPolicyService */
    protected $service;

    public function __construct(
        ContentSecurityPolicyService $service
    ) {
        $this->service = $service;
    }

    public static function create(ContainerInterface $container)
    {
        $instance = parent::create($container);
        $instance->service = $container->get('wmcontent_security_policy.content_security_policy');

        return $instance;
    }

    public function getFormId(): string
    {
        return 'wmcontent_security_policy_content_security_policy_form';
    }

    public function buildForm(array $form, FormStateInterface $form_state): array
    {
        $form['intro'] = [
            '#markup' => '<p>The HTTP Content-Security-Policy response header allows web site administrators to control resources the user agent is allowed to load for a given page. With a few exceptions, policies mostly involve specifying server origins and script endpoints. This helps guard against cross-site scripting attacks (XSS).
                <br><br>For more information, see also <a href="https://developer.mozilla.org/en-US/docs/Web/HTTP/Headers/Content-Security-Policy">the MDN web docs</a>.</p><br>',
        ];

        $form['tabs'] = [
            '#type' => 'vertical_tabs',
        ];

        foreach (ContentSecurityPolicyService::POLICY_DIRECTIVES as $directive => $description) {
            $form[$directive] = [
                '#type' => 'details',
                '#group' => 'tabs',
                '#title' => $directive,
                '#description' => $description,
                '#tree' => true,
            ];

            $form[$directive]['defaults'] = [
                '#type' => 'textfield',
                '#title' => 'Defaults',
                '#description' => 'Sources which are required for the website to function properly.',
                '#default_value' => implode(' ', ContentSecurityPolicyService::getDefaultSources()[$directive] ?? []),
                '#disabled' => true,
                '#maxlength' => 1000,
            ];

            $form[$directive]['custom'] = [
                '#type' => 'textfield',
                '#title' => 'Custom sources',
                '#description' => 'A space-separated list of additional sources to allow.',
                '#default_value' => implode(' ', $this->service->getSources($directive)),
                '#maxlength' => 1000,
            ];
        }

        $form['actions']['submit'] = [
            '#type' => 'submit',
            '#value' => $this->t('Submit'),
        ];

        return $form;
    }

    public function submitForm(array &$form, FormStateInterface $formState): void
    {
        foreach (array_keys(ContentSecurityPolicyService::POLICY_DIRECTIVES) as $directive) {
            if ($value = $formState->getValue([$directive, 'custom'])) {
                $sources = explode(' ', $value);
                $sources = array_map('trim', $sources);
                $this->service->setSources($directive, $sources);
            }
        }

        drupal_flush_all_caches();
    }
}
