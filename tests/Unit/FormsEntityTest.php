<?php
/**
 * FormsEntityTest
 *
 * @package Ksfraser\Forms\Tests
 * @author KSFII
 */

declare(strict_types=1);

namespace Ksfraser\Forms\Tests\Unit;

use PHPUnit\Framework\TestCase;
use Ksfraser\Forms\Entity\Form;
use Ksfraser\Forms\Entity\FormField;
use Ksfraser\Forms\Entity\FormSubmission;

final class FormsEntityTest extends TestCase
{
    public function testFormCreation(): void
    {
        $form = new Form('contact_1', 'Contact Form', Form::TYPE_CONTACT);
        
        $this->assertEquals('contact_1', $form->getId());
        $this->assertEquals('Contact Form', $form->getName());
        $this->assertEquals(Form::TYPE_CONTACT, $form->getFormType());
    }

    public function testFormAddField(): void
    {
        $form = new Form('test_1', 'Test Form');
        $field = new FormField('email', 'email', FormField::TYPE_EMAIL);
        $field->setRequired(true);
        
        $form->addField($field);
        
        $this->assertTrue($form->getField('email')->isRequired());
    }

    public function testFormValidation(): void
    {
        $form = new Form('test_1', 'Test Form');
        
        $this->assertFalse($form->isValid());
        
        $field = new FormField('email', 'email', FormField::TYPE_EMAIL);
        $form->addField($field);
        
        $this->assertTrue($form->isValid());
    }

    public function testFormSettings(): void
    {
        $form = new Form('test_1', 'Test Form');
        $form->setSetting('redirect_url', '/thank-you');
        $form->setSetting('notify_email', 'admin@example.com');
        
        $this->assertEquals('/thank-you', $form->getSetting('redirect_url'));
        $this->assertEquals('admin@example.com', $form->getSetting('notify_email'));
    }

    public function testFormSubmission(): void
    {
        $submission = new FormSubmission('sub_001', 'contact_1');
        $submission->setData([
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'message' => 'Hello',
        ]);
        
        $this->assertEquals('john@example.com', $submission->getEmail());
        $this->assertEquals('John Doe', $submission->getFullName());
    }

    public function testCf7ShortcodeGeneration(): void
    {
        $form = new Form('contact_1', 'Contact Form');
        $shortcode = $form->generateCf7Shortcode();
        
        $this->assertEquals('[cf7form id="contact_1" title="Contact Form"]', $shortcode);
    }

    public function testFieldCf7Output(): void
    {
        $field = new FormField('email', 'email', FormField::TYPE_EMAIL);
        $field->setLabel('Email Address')->setRequired(true)->setPlaceholder('your@email.com');
        
        $output = $field->toCf7Field();
        
        $this->assertStringContainsString('email*', $output);
        $this->assertStringContainsString('Email Address', $output);
    }

    public function testLeadFormType(): void
    {
        $form = new Form('lead_1', 'Get Quote', Form::TYPE_LEAD);
        
        $this->assertEquals(Form::TYPE_LEAD, $form->getFormType());
    }

    public function testSubmissionStatus(): void
    {
        $submission = new FormSubmission('sub_001', 'contact_1');
        
        $this->assertEquals(FormSubmission::STATUS_PENDING, $submission->getStatus());
        
        $submission->setStatus(FormSubmission::STATUS_PROCESSED);
        
        $this->assertEquals(FormSubmission::STATUS_PROCESSED, $submission->getStatus());
    }
}