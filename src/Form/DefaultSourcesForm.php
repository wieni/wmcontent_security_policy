<?php

namespace Drupal\wmcontent_security_policy\Form;

use Drupal\Core\Form\FormStateInterface;
use Drupal\wmcontent_security_policy\Service\ContentSecurityPolicyService;

class DefaultSourcesForm extends BaseSourcesForm
{
    public function getFormId(): string
    {
        return 'wmcontent_security_policy_default_sources_form';
    }

    public function canEdit(): bool
    {
        return $this->currentUser()->hasPermission('administer default content security policy sources');
    }

    public function buildForm(array $form, FormStateInterface $form_state): array
    {
        $form = parent::buildForm($form, $form_state);

        $form['intro']['sources'] = [
            '#markup' => 'Using this form, you can set default sources for the different policy directives. These 
            sources should be required for the website to function properly. Sources you add here are stored in configuration.',
        ];

        return $form;
    }

    public function submitForm(array &$form, FormStateInterface $formState): void
    {
        foreach (array_keys(ContentSecurityPolicyService::POLICY_DIRECTIVES) as $directive) {
            $sources = array_map(
                static function (array $source) { return $source['container']; },
                $formState->getValue([$directive, 'sources'])
            );

            $this->service->setDefaultSources($directive, $sources);
        }

        $this->messenger()->addStatus('Successfully saved default sources.');
    }

    protected function getSourcesElement(string $directive): array
    {
        return [
            '#type' => 'multivalue',
            '#default_value' => array_map(
                static function (array $source): array {
                    return ['container' => $source];
                },
                $this->service->getDefaultSources($directive)
            ),
            '#disabled' => !$this->canEdit(),
            /** This property is added by @see https://www.drupal.org/project/drupal/issues/2264739 */
            '#orderable' => false,
        ];
    }
}
