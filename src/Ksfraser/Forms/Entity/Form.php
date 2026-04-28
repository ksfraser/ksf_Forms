<?php
/**
 * Form Builder Entity
 *
 * @package Ksfraser\Forms\Entity
 * @author KSFII
 * @license MIT
 */

declare(strict_types=1);

namespace Ksfraser\Forms\Entity;

use JsonSerializable;

/**
 * Form - Marketing/sales form definition
 */
class Form implements JsonSerializable
{
    private string $id;
    private string $name;
    private string $description;
    private string $formType;
    private array $fields;
    private array $settings;
    private bool $isActive;
    private ?string $cf7Shortcode;
    private array $webhooks;
    private ?string $createdAt;
    private ?string $updatedAt;

    public const TYPE_CONTACT = 'contact';
    public const TYPE_LEAD = 'lead';
    public const TYPE_SUPPORT = 'support';
    public const TYPE_CUSTOM = 'custom';

    public function __construct(string $id, string $name, string $formType = self::TYPE_CONTACT)
    {
        $this->id = $id;
        $this->name = $name;
        $this->formType = $formType;
        $this->fields = [];
        $this->settings = [];
        $this->isActive = true;
        $this->webhooks = [];
    }

    public function getId(): string { return $this->id; }
    public function getName(): string { return $this->name; }
    public function setName(string $name): self { $this->name = $name; return $this; }
    public function getDescription(): string { return $this->description; }
    public function setDescription(string $description): self { $this->description = $description; return $this; }
    public function getFormType(): string { return $this->formType; }
    public function isActive(): bool { return $this->isActive; }
    public function setActive(bool $active): self { $this->isActive = $active; return $this; }

    public function addField(FormField $field): self
    {
        $this->fields[$field->getId()] = $field;
        return $this;
    }

    public function removeField(string $fieldId): self
    {
        unset($this->fields[$fieldId]);
        return $this;
    }

    public function getFields(): array { return $this->fields; }
    public function getField(string $id): ?FormField { return $this->fields[$id] ?? null; }

    public function setSetting(string $key, $value): self
    {
        $this->settings[$key] = $value;
        return $this;
    }

    public function getSetting(string $key, $default = null)
    {
        return $this->settings[$key] ?? $default;
    }

    public function getSettings(): array { return $this->settings; }

    public function setCf7Shortcode(?string $shortcode): self
    {
        $this->cf7Shortcode = $shortcode;
        return $this;
    }

    public function getCf7Shortcode(): ?string
    {
        return $this->cf7Shortcode;
    }

    public function addWebhook(string $url, string $event = 'submit'): self
    {
        $this->webhooks[] = ['url' => $url, 'event' => $event];
        return $this;
    }

    public function getWebhooks(): array { return $this->webhooks; }

    public function isValid(): bool
    {
        return !empty($this->name) && !empty($this->fields);
    }

    public function generateCf7Shortcode(): string
    {
        return '[cf7form id="' . $this->id . '" title="' . $this->name . '"]';
    }

    public function jsonSerialize(): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'description' => $this->description,
            'form_type' => $this->formType,
            'fields' => array_map(fn($f) => $f->jsonSerialize(), $this->fields),
            'settings' => $this->settings,
            'is_active' => $this->isActive,
            'cf7_shortcode' => $this->cf7Shortcode,
            'webhooks' => $this->webhooks,
        ];
    }

    public static function fromArray(array $data): self
    {
        $form = new self($data['id'], $data['name'], $data['form_type'] ?? self::TYPE_CONTACT);
        
        if (isset($data['description'])) {
            $form->setDescription($data['description']);
        }
        if (isset($data['settings'])) {
            foreach ($data['settings'] as $k => $v) {
                $form->setSetting($k, $v);
            }
        }
        if (isset($data['is_active'])) {
            $form->setActive($data['is_active']);
        }

        return $form;
    }
}

/**
 * FormField - Individual form field
 */
class FormField implements JsonSerializable
{
    private string $id;
    private string $name;
    private string $type;
    private string $label;
    private bool $required;
    private ?string $defaultValue;
    private array $validation;
    private array $options;
    private ?string $placeholder;

    public const TYPE_TEXT = 'text';
    public const TYPE_EMAIL = 'email';
    public const TYPE_TEL = 'tel';
    public const TYPE_TEXTAREA = 'textarea';
    public const TYPE_SELECT = 'select';
    public const TYPE_CHECKBOX = 'checkbox';
    public const TYPE_RADIO = 'radio';
    public const TYPE_FILE = 'file';
    public const TYPE_DATE = 'date';
    public const TYPE_HIDDEN = 'hidden';

    public function __construct(string $id, string $name, string $type = self::TYPE_TEXT)
    {
        $this->id = $id;
        $this->name = $name;
        $this->type = $type;
        $this->label = ucfirst($name);
        $this->required = false;
        $this->validation = [];
        $this->options = [];
    }

    public function getId(): string { return $this->id; }
    public function getName(): string { return $this->name; }
    public function getType(): string { return $this->type; }
    public function getLabel(): string { return $this->label; }
    public function setLabel(string $label): self { $this->label = $label; return $this; }
    public function isRequired(): bool { return $this->required; }
    public function setRequired(bool $required): self { $this->required = $required; return $this; }
    public function getDefaultValue(): ?string { return $this->defaultValue; }
    public function setDefaultValue(?string $defaultValue): self { $this->defaultValue = $defaultValue; return $this; }
    public function getValidation(): array { return $this->validation; }
    public function setValidation(array $validation): self { $this->validation = $validation; return $this; }
    public function getOptions(): array { return $this->options; }
    public function addOption(string $value, string $label): self { $this->options[] = ['value' => $value, 'label' => $label]; return $this; }
    public function getPlaceholder(): ?string { return $this->placeholder; }
    public function setPlaceholder(?string $placeholder): self { $this->placeholder = $placeholder; return $this; }

    public function toCf7Field(): string
    {
        $cf7Type = match($this->type) {
            self::TYPE_TEXT => 'text',
            self::TYPE_EMAIL => 'email*',
            self::TYPE_TEL => 'tel',
            self::TYPE_TEXTAREA => 'textarea',
            self::TYPE_SELECT => 'select',
            self::TYPE_CHECKBOX => 'checkbox',
            self::TYPE_RADIO => 'radio',
            self::TYPE_DATE => 'date',
            default => 'text',
        };

        $attrs = [];
        if ($this->required) {
            $cf7Type .= '*';
        }
        if ($this->placeholder) {
            $attrs[] = 'placeholder "' . $this->placeholder . '"';
        }

        $options = '';
        if ($this->type === self::TYPE_SELECT && !empty($this->options)) {
            $options = "\n  first_as_label " . $this->options[0]['label'] ?? '';
            foreach ($this->options as $opt) {
                $options .= "\n  " . $opt['value'] . " " . $opt['label'];
            }
        }

        $validationRules = implode(' ', array_map(fn($r) => $r['rule'], $this->validation));
        
        return "[{$cf7Type} {$this->name} " . ($validationRules ? "$validationRules " : '') . ($attrs ? implode(' ', $attrs) : '') . "]\n{$this->label}" . ($options ? "\n$options" : "") . "\n[/{$cf7Type}]";
    }

    public function jsonSerialize(): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'type' => $this->type,
            'label' => $this->label,
            'required' => $this->required,
            'default_value' => $this->defaultValue,
            'validation' => $this->validation,
            'options' => $this->options,
            'placeholder' => $this->placeholder,
        ];
    }
}

/**
 * FormSubmission - Form submission data
 */
class FormSubmission implements JsonSerializable
{
    private string $id;
    private string $formId;
    private ?string $visitorId;
    private ?string $contactId;
    private array $data;
    private string $status;
    private ?string $ipAddress;
    private ?string $userAgent;
    private string $createdAt;

    public const STATUS_PENDING = 'pending';
    public const STATUS_PROCESSED = 'processed';
    public const STATUS_FAILED = 'failed';

    public function __construct(string $id, string $formId)
    {
        $this->id = $id;
        $this->formId = $formId;
        $this->status = self::STATUS_PENDING;
        $this->data = [];
        $this->createdAt = date('Y-m-d H:i:s');
    }

    public function getId(): string { return $this->id; }
    public function getFormId(): string { return $this->formId; }
    public function getVisitorId(): ?string { return $this->visitorId; }
    public function setVisitorId(?string $visitorId): self { $this->visitorId = $visitorId; return $this; }
    public function getContactId(): ?string { return $this->contactId; }
    public function setContactId(?string $contactId): self { $this->contactId = $contactId; return $this; }
    public function getData(): array { return $this->data; }
    public function setData(array $data): self { $this->data = $data; return $this; }
    public function getStatus(): string { return $this->status; }
    public function setStatus(string $status): self { $this->status = $status; return $this; }
    public function getIpAddress(): ?string { return $this->ipAddress; }
    public function setIpAddress(?string $ipAddress): self { $this->ipAddress = $ipAddress; return $this; }
    public function getUserAgent(): ?string { return $this->userAgent; }
    public function setUserAgent(?string $userAgent): self { $this->userAgent = $userAgent; return $this; }
    public function getCreatedAt(): string { return $this->createdAt; }

    public function getFieldValue(string $field, $default = null)
    {
        return $this->data[$field] ?? $default;
    }

    public function getEmail(): ?string
    {
        foreach (['email', 'email_address', 'emailaddress', 'contact_email'] as $field) {
            if (isset($this->data[$field])) {
                return $this->data[$field];
            }
        }
        return null;
    }

    public function getFullName(): ?string
    {
        $nameFields = ['name', 'full_name', 'fullname', 'first_name'];
        $lastName = $this->data['last_name'] ?? $this->data['lastname'] ?? '';
        
        foreach ($nameFields as $field) {
            if (isset($this->data[$field])) {
                return $lastName ? $this->data[$field] . ' ' . $lastName : $this->data[$field];
            }
        }
        return null;
    }

    public function jsonSerialize(): array
    {
        return [
            'id' => $this->id,
            'form_id' => $this->formId,
            'visitor_id' => $this->visitorId,
            'contact_id' => $this->contactId,
            'data' => $this->data,
            'status' => $this->status,
            'ip_address' => $this->ipAddress,
            'user_agent' => $this->userAgent,
            'created_at' => $this->createdAt,
        ];
    }
}