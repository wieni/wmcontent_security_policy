<?php

namespace Drupal\wmcontent_security_policy\Form;

use Drupal\Component\Utility\Html;
use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Render\Markup;
use Drupal\wmcontent_security_policy\Service\ContentSecurityPolicyService;
use Symfony\Component\DependencyInjection\ContainerInterface;

class DefaultSourcesForm extends FormBase
{
    /** @var ContentSecurityPolicyService */
    protected $service;

    public static function create(ContainerInterface $container)
    {
        $instance = parent::create($container);
        $instance->service = $container->get('wmcontent_security_policy.content_security_policy');

        return $instance;
    }

    public function getFormId(): string
    {
        return 'wmcontent_security_policy_default_sources_form';
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
                '#description' => Html::escape($description),
                '#tree' => true,
            ];

            $form[$directive]['defaults'] = [
                '#type' => 'multivalue',
                '#title' => 'Default sources',
                '#description' => 'Sources which are required for the website to function properly.',
                '#add_more_label' => 'Add another source',
                '#default_value' => array_map(
                    static function (array $source) { return ['container' => $source]; },
                    $this->service->getDefaultSources($directive)
                ),
            ];

            $form[$directive]['defaults']['container'] = [
                '#type' => 'container',
                '#attributes' => ['class' => ['form-items-inline']],
            ];

            $form[$directive]['defaults']['container']['source'] = [
                '#type' => 'textfield',
                '#title' => 'Source to allow',
                '#size' => 50,
            ];

            $form[$directive]['defaults']['container']['comment'] = [
                '#type' => 'textfield',
                '#title' => 'Comment',
                '#size' => 50,
            ];
        }

        $form['actions']['submit'] = [
            '#type' => 'submit',
            '#value' => $this->t('Save'),
        ];

        return $form;
    }

    public function submitForm(array &$form, FormStateInterface $formState): void
    {
        foreach (array_keys(ContentSecurityPolicyService::POLICY_DIRECTIVES) as $directive) {
            $sources = array_map(
                static function (array $source) { return $source['container']; },
                $formState->getValue([$directive, 'defaults'])
            );

            $this->service->setDefaultSources($directive, $sources);
        }

        drupal_flush_all_caches();
    }
}
