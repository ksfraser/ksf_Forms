<?php
/**
 * Form Service
 *
 * Handles form rendering, CF7 integration, and submission processing
 *
 * @package Ksfraser\Forms\Service
 * @author KSFII
 * @license MIT
 */

declare(strict_types=1);

namespace Ksfraser\Forms\Service;

use Ksfraser\Forms\Entity\Form;
use Ksfraser\Forms\Entity\FormField;
use Ksfraser\Forms\Entity\FormSubmission;

class FormService
{
    private array $forms = [];
    private ?string $repository;

    public function __construct($repository = null)
    {
        $this->repository = $repository;
    }

    public function createForm(string $id, string $name, string $type = Form::TYPE_CONTACT): Form
    {
        $form = new Form($id, $name, $type);
        $this->forms[$id] = $form;
        return $form;
    }

    public function getForm(string $id): ?Form
    {
        return $this->forms[$id] ?? null;
    }

    public function getAllForms(): array
    {
        return $this->forms;
    }

    public function generateCf7Form(Form $form): string
    {
        $output = '[cf7form id="' . $form->getId() . '" title="' . $form->getName() . '"]' . "\n";
        
        $formClass = match($form->getFormType()) {
            Form::TYPE_LEAD => 'ksf-form-lead',
            Form::TYPE_SUPPORT => 'ksf-form-support',
            default => 'ksf-form-contact',
        };
        
        $output .= '<div class="ksf-form ' . $formClass . '">' . "\n";
        
        foreach ($form->getFields() as $field) {
            $output .= '<div class="ksf-form-group">' . "\n";
            
            $output .= '<label for="' . $field->getName() . '">' . $field->getLabel();
            if ($field->isRequired()) {
                $output .= ' *';
            }
            $output .= "</label>\n";
            
            if (in_array($field->getType(), [FormField::TYPE_SELECT]) && !empty($field->getOptions())) {
                $output .= '<select name="' . $field->getName() . '" id="' . $field->getName() . '"';
                if ($field->isRequired()) echo ' required';
                $output .= '>' . "\n";
                $output .= '<option value="">-- Select --</option>' . "\n";
                foreach ($field->getOptions() as $opt) {
                    $output .= '<option value="' . $opt['value'] . '">' . $opt['label'] . '</option>' . "\n";
                }
                $output .= '</select>' . "\n";
            } elseif (in_array($field->getType(), [FormField::TYPE_TEXTAREA])) {
                $output .= '<textarea name="' . $field->getName() . '" id="' . $field->getName() . '"';
                if ($field->getPlaceholder()) {
                    $output .= ' placeholder="' . $field->getPlaceholder() . '"';
                }
                if ($field->isRequired()) echo ' required';
                $output .= '></textarea>' . "\n";
            } else {
                $type = $field->getType();
                $output .= '<input type="' . $type . '" name="' . $field->getName() . '" id="' . $field->getName() . '"';
                if ($field->getPlaceholder()) {
                    $output .= ' placeholder="' . $field->getPlaceholder() . '"';
                }
                if ($field->isRequired()) echo ' required';
                $output .= '>' . "\n";
            }
            
            $output .= '</div>' . "\n";
        }
        
        $output .= '<div class="ksf-form-group">' . "\n";
        $output .= '<button type="submit">Submit</button>' . "\n";
        $output .= '</div>' . "\n";
        
        $output .= '</div>' . "\n";
        $output .= '[/cf7form]' . "\n";
        
        return $output;
    }

    public function renderCf7Shortcode(Form $form): string
    {
        $content = $this->generateCf7Form($form);
        return $content;
    }

    public function processSubmission(Form $form, array $postData, array $serverData = []): FormSubmission
    {
        $submission = new FormSubmission(
            uniqid('sub_'),
            $form->getId()
        );

        $submission->setData($postData);

        if (isset($serverData['REMOTE_ADDR'])) {
            $submission->setIpAddress($serverData['REMOTE_ADDR']);
        }
        if (isset($serverData['HTTP_USER_AGENT'])) {
            $submission->setUserAgent($serverData['HTTP_USER_AGENT']);
        }

        $email = $submission->getEmail();
        if ($email) {
            $contactId = $this->findOrCreateContact($form, $submission);
            $submission->setContactId($contactId);
        }

        $submission->setStatus(FormSubmission::STATUS_PROCESSED);

        if ($form->getWebhooks()) {
            $this->triggerWebhooks($form, $submission);
        }

        return $submission;
    }

    private function findOrCreateContact(Form $form, FormSubmission $submission): ?string
    {
        $email = $submission->getEmail();
        $fullName = $submission->getFullName();
        
        if (!$email) {
            return null;
        }

        $contactId = 'c_' . substr(md5($email), 0, 12);
        
        if ($this->repository && method_exists($this->repository, 'findContactByEmail')) {
            $existing = $this->repository->findContactByEmail($email);
            if ($existing) {
                return $existing;
            }
        }

        if ($this->repository && method_exists($this->repository, 'createContact')) {
            return $this->repository->createContact([
                'email' => $email,
                'name' => $fullName ?? $email,
                'source' => 'form_' . $form->getId(),
                'data' => $submission->getData(),
            ]);
        }

        return $contactId;
    }

    private function triggerWebhooks(Form $form, FormSubmission $submission): void
    {
        foreach ($form->getWebhooks() as $webhook) {
            if ($webhook['event'] !== 'submit') {
                continue;
            }

            $data = json_encode([
                'form_id' => $form->getId(),
                'form_name' => $form->getName(),
                'submission' => $submission->jsonSerialize(),
                'timestamp' => date('c'),
            ]);

            $ch = curl_init($webhook['url']);
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
            curl_setopt($ch, CURLOPT_HTTPHEADER, [
                'Content-Type: application/json',
                'X-KSF-Form: ' . $form->getId(),
            ]);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_TIMEOUT, 10);
            curl_exec($ch);
            curl_close($ch);
        }
    }

    public function getStandardFields(): array
    {
        return [
            'name' => (new FormField('name', 'name', FormField::TYPE_TEXT))->setLabel('Full Name')->setPlaceholder('Your name'),
            'email' => (new FormField('email', 'email', FormField::TYPE_EMAIL))->setLabel('Email')->setRequired(true),
            'phone' => (new FormField('phone', 'phone', FormField::TYPE_TEL))->setLabel('Phone'),
            'company' => (new FormField('company', 'company', FormField::TYPE_TEXT))->setLabel('Company'),
            'subject' => (new FormField('subject', 'subject', FormField::TYPE_TEXT))->setLabel('Subject'),
            'message' => (new FormField('message', 'message', FormField::TYPE_TEXTAREA))->setLabel('Message'),
        ];
    }

    public function createLeadForm(string $id, string $name): Form
    {
        $form = $this->createForm($id, $name, Form::TYPE_LEAD);
        
        $form->addField(clone $this->getStandardFields()['name']);
        $form->addField(clone $this->getStandardFields()['email']);
        $form->addField(clone $this->getStandardFields()['phone']);
        $form->addField(clone $this->getStandardFields()['company']);
        
        $companySize = new FormField('company_size', 'company_size', FormField::TYPE_SELECT);
        $companySize->setLabel('Company Size');
        $companySize->addOption('1-10', '1-10 employees');
        $companySize->addOption('11-50', '11-50 employees');
        $companySize->addOption('51-200', '51-200 employees');
        $companySize->addOption('200+', '200+ employees');
        $form->addField($companySize);
        
        $message = clone $this->getStandardFields()['message'];
        $message->setLabel('How can we help?');
        $form->addField($message);

        return $form;
    }

    public function createContactForm(string $id, string $name): Form
    {
        $form = $this->createForm($id, $name, Form::TYPE_CONTACT);
        
        $form->addField(clone $this->getStandardFields()['name']);
        $form->addField(clone $this->getStandardFields()['email']);
        $form->addField(clone $this->getStandardFields()['phone']);
        $form->addField(clone $this->getStandardFields()['subject']);
        $form->addField(clone $this->getStandardFields()['message']);

        return $form;
    }
}