<?php

namespace Drupal\wmcontent_security_policy\Form;

use Drupal\Core\Form\FormStateInterface;
use Drupal\wmcontent_security_policy\Service\ContentSecurityPolicyService;

class CustomSourcesForm extends BaseSourcesForm
{
    public function getFormId(): string
    {
        return 'wmcontent_security_policy_custom_sources_form';
    }

    public function canEdit(): bool
    {
        return $this->currentUser()->hasPermission('administer custom content security policy sources');
    }

    public function submitForm(array &$form, FormStateInterface $formState): void
    {
        foreach (array_keys(ContentSecurityPolicyService::POLICY_DIRECTIVES) as $directive) {
            $sources = array_map(
                static function (array $source) { return $source['container']; },
                $formState->getValue([$directive, 'sources'])
            );

            $this->service->setSources($directive, $sources);
        }

        $this->messenger()->addStatus('Successfully saved custom sources. All caches are rebuilt.');

        drupal_flush_all_caches();
    }

    protected function getSourcesElement(string $directive): array
    {
        return [
            '#type' => 'multivalue',
            '#title' => 'Custom sources',
            '#description' => 'Additional sources to allow.',
            '#default_value' => array_map(
                static function (array $source): array {
                    return ['container' => $source];
                },
                $this->service->getSources($directive)
            ),
            '#disabled' => !$this->canEdit(),
            /** This property is added by @see https://www.drupal.org/project/drupal/issues/2264739 */
            '#orderable' => false,
        ];
    }
}
