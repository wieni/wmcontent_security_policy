<?php

namespace Drupal\wmcontent_security_policy\Form;

use Drupal\Core\Cache\CacheTagsInvalidatorInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\wmcontent_security_policy\Service\ContentSecurityPolicyInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

class CustomSourcesForm extends BaseSourcesForm
{
    /** @var CacheTagsInvalidatorInterface */
    protected $cacheTagsInvalidator;

    public static function create(ContainerInterface $container)
    {
        $instance = parent::create($container);
        $instance->cacheTagsInvalidator = $container->get('cache_tags.invalidator');

        return $instance;
    }

    public function getFormId(): string
    {
        return 'wmcontent_security_policy_custom_sources_form';
    }

    public function canEdit(): bool
    {
        return $this->currentUser()->hasPermission('administer custom content security policy sources');
    }

    public function buildForm(array $form, FormStateInterface $form_state): array
    {
        $form = parent::buildForm($form, $form_state);

        $form['intro']['sources'] = [
            '#markup' => 'Using this form, you can add custom sources to the different policy directives. Sources you
            add here are stored in the database and will not be exported with configuration.',
        ];

        return $form;
    }

    public function submitForm(array &$form, FormStateInterface $formState): void
    {
        foreach (array_keys(ContentSecurityPolicyInterface::POLICY_DIRECTIVES) as $directive) {
            $sources = array_map(
                static function (array $source) { return $source['container']; },
                $formState->getValue([$directive, 'sources'])
            );

            $this->contentSecurityPolicy->setSources($directive, $sources);
        }

        $this->messenger()->addStatus('Successfully saved custom sources.');

        $this->cacheTagsInvalidator->invalidateTags([
            'wmcontent_security_policy.custom_sources',
        ]);
    }

    protected function getSourcesElement(string $directive): array
    {
        return [
            '#type' => 'multivalue',
            '#default_value' => array_map(
                static function (array $source): array {
                    return ['container' => $source];
                },
                $this->contentSecurityPolicy->getSources($directive)
            ),
            '#disabled' => !$this->canEdit(),
            '#orderable' => false,
        ];
    }
}
