<?php

namespace Drupal\wmcontent_security_policy\Form;

use Drupal\Component\Utility\Html;
use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\wmcontent_security_policy\Service\ContentSecurityPolicyService;
use Symfony\Component\DependencyInjection\ContainerInterface;

abstract class BaseSourcesForm extends FormBase
{
    /** @var ContentSecurityPolicyService */
    protected $service;

    public static function create(ContainerInterface $container)
    {
        $instance = parent::create($container);
        $instance->service = $container->get('wmcontent_security_policy.content_security_policy');

        return $instance;
    }

    abstract public function canEdit(): bool;

    abstract protected function getSourcesElement(string $directive): array;

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

            $form[$directive]['sources'] = $this->getSourcesElement($directive);

            $form[$directive]['sources']['container'] = [
                '#type' => 'container',
                '#attributes' => ['class' => ['form-items-inline']],
            ];

            $form[$directive]['sources']['container']['source'] = [
                '#type' => 'textfield',
                '#title' => 'Source to allow',
                '#size' => 50,
            ];

            $form[$directive]['sources']['container']['comment'] = [
                '#type' => 'textfield',
                '#title' => 'Comment',
                '#size' => 50,
            ];
        }

        $form['actions']['submit'] = [
            '#type' => 'submit',
            '#value' => $this->t('Save'),
            '#access' => $this->canEdit(),
        ];

        return $form;
    }
}