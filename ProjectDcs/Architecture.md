# Architecture - ksf_Forms

## Document Information
- **Module**: ksf_Forms
- **Version**: 1.0.0
- **Date**: 2026-05-13
- **Status**: Implemented
- **Author**: KSFII Development Team

---

## 1. Technical Architecture

### 1.1 High-Level Architecture

```
┌─────────────────────────────────────────────────────────────┐
│                     ksf_Forms Module                        │
├─────────────────────────────────────────────────────────────┤
│                                                             │
│  ┌─────────────┐    ┌─────────────────┐                   │
│  │   Entities   │    │     Service      │                   │
│  ├─────────────┤    ├─────────────────┤                   │
│  │ Form        │◄──►│ FormService     │                   │
│  │ FormField   │    │ - createForm()  │                   │
│  │ FormSubmission│   │ - processSub()  │                   │
│  └─────────────┘    │ - generateCf7() │                   │
│                     └────────┬────────┘                   │
│                              │                             │
│         ┌────────────────────┼────────────────────┐        │
│         │                    │                    │        │
│         ▼                    ▼                    ▼        │
│  ┌─────────────┐    ┌───────────────┐    ┌──────────┐    │
│  │ Repository  │    │   Webhooks    │    │   CF7    │    │
│  │ (Optional)   │    │   Dispatcher  │    │  Export  │    │
│  └─────────────┘    └───────────────┘    └──────────┘    │
│                                                             │
└─────────────────────────────────────────────────────────────┘
         │                    │                    │
         ▼                    ▼                    ▼
┌─────────────┐    ┌───────────────┐    ┌──────────────┐
│   Platform  │    │  External     │    │   Contact    │
│   Adapter   │    │  Systems      │    │   Form 7     │
└─────────────┘    └───────────────┘    └──────────────┘
```

### 1.2 Class Diagram

```
┌─────────────────────────────────────────────────────────────────────┐
│                            <<interface>>                            │
│                         JsonSerializable                            │
├─────────────────────────────────────────────────────────────────────┤
│ + jsonSerialize(): array                                            │
└─────────────────────────────────────────────────────────────────────┘
                                    ▲
                                    │ implements
        ┌───────────────────────────┼───────────────────────────┐
        │                           │                           │
        ▼                           ▼                           ▼
┌───────────────────────┐  ┌───────────────────────┐  ┌───────────────────────┐
│        Form           │  │     FormField         │  │   FormSubmission      │
├───────────────────────┤  ├───────────────────────┤  ├───────────────────────┤
│ - id: string          │  │ - id: string          │  │ - id: string          │
│ - name: string        │  │ - name: string        │  │ - formId: string      │
│ - description: string│  │ - type: string        │  │ - visitorId: ?string  │
│ - formType: string    │  │ - label: string       │  │ - contactId: ?string  │
│ - fields: array       │  │ - required: bool      │  │ - data: array         │
│ - settings: array    │  │ - options: array       │  │ - status: string      │
│ - isActive: bool      │  │ - validation: array   │  │ - ipAddress: ?string  │
│ - cf7Shortcode: ?str  │  │ - placeholder: ?string│  │ - userAgent: ?string  │
│ - webhooks: array     │  │                       │  │ - createdAt: string   │
├───────────────────────┤  ├───────────────────────┤  ├───────────────────────┤
│ + addField()          │  │ + setLabel()          │  │ + setVisitorId()      │
│ + removeField()      │  │ + setRequired()       │  │ + setContactId()      │
│ + setSetting()       │  │ + addOption()         │  │ + getEmail()          │
│ + addWebhook()       │  │ + setPlaceholder()    │  │ + getFullName()       │
│ + isValid()          │  │ + toCf7Field()        │  │ + getFieldValue()     │
│ + generateCf7()      │  │                       │  │                       │
└───────────────────────┘  └───────────────────────┘  └───────────────────────┘
        ▲                           ▲
        │                           │
        │ parent                    │ parent
        │                           │
        │                           │
┌───────────────────────┐
│     FormService       │
├───────────────────────┤
│ - forms: array        │
│ - repository: ?       │
├───────────────────────┤
│ + createForm()        │
│ + getForm()           │
│ + generateCf7Form()   │
│ + renderCf7Shortcode()│
│ + processSubmission() │
│ + findOrCreateContact │
│ + triggerWebhooks()   │
│ + getStandardFields() │
│ + createLeadForm()    │
│ + createContactForm() │
└───────────────────────┘
```

---

## 2. Data Flow Diagrams

### 2.1 Form Creation Flow

```
┌──────────┐      ┌──────────────┐      ┌────────────┐      ┌──────────┐
│  Client  │ ───► │  Platform    │ ───► │ FormService│ ───► │   Form   │
│          │      │   Adapter    │      │            │      │  Entity  │
└──────────┘      └──────────────┘      └────────────┘      └──────────┘
                                                           │
                                                           ▼
                                                    ┌────────────┐
                                                    │  CF7       │
                                                    │  Export    │
                                                    └────────────┘
```

### 2.2 Submission Processing Flow

```
┌──────────┐     ┌──────────────┐     ┌────────────┐     ┌─────────────┐
│  Browser │ ──► │  HTTP POST   │ ──► │  Validate  │ ──► │  Extract    │
│          │     │              │     │  Data      │     │  Email      │
└──────────┘     └──────────────┘     └────────────┘     └─────────────┘
                                                                │
                                                                ▼
                                                         ┌─────────────┐
                                                         │ Find/Create │
                                                         │  Contact    │
                                                         └─────────────┘
                                                                │
                                                                ▼
                                                         ┌─────────────┐
                                                         │ FormSubmis │
                                                         │   sion      │
                                                         └─────────────┘
                                                                │
                                           ┌────────────────────┼────────────────────┐
                                           ▼                    ▼                    ▼
                                    ┌─────────────┐     ┌─────────────┐     ┌─────────────┐
                                    │   Update    │     │  Webhooks   │     │   Return    │
                                    │   Status    │     │  Dispatch   │     │  Response   │
                                    └─────────────┘     └─────────────┘     └─────────────┘
```

---

## 3. Database Schema

### 3.1 Forms Table (Platform-Provided)

```sql
CREATE TABLE `{PREFIX}forms` (
    `id` VARCHAR(32) PRIMARY KEY,
    `name` VARCHAR(255) NOT NULL,
    `description` TEXT,
    `form_type` ENUM('contact', 'lead', 'support', 'custom') DEFAULT 'contact',
    `fields` JSON NOT NULL,
    `settings` JSON,
    `is_active` TINYINT(1) DEFAULT 1,
    `cf7_shortcode` VARCHAR(255),
    `webhooks` JSON,
    `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
    `updated_at` DATETIME ON UPDATE CURRENT_TIMESTAMP,
    INDEX `idx_form_type` (`form_type`),
    INDEX `idx_is_active` (`is_active`)
);
```

### 3.2 Form Submissions Table (Platform-Provided)

```sql
CREATE TABLE `{PREFIX}form_submissions` (
    `id` VARCHAR(32) PRIMARY KEY,
    `form_id` VARCHAR(32) NOT NULL,
    `visitor_id` VARCHAR(64),
    `contact_id` VARCHAR(32),
    `data` JSON NOT NULL,
    `status` ENUM('pending', 'processed', 'failed') DEFAULT 'pending',
    `ip_address` VARCHAR(45),
    `user_agent` VARCHAR(512),
    `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
    INDEX `idx_form_id` (`form_id`),
    INDEX `idx_status` (`status`),
    INDEX `idx_created_at` (`created_at`),
    FOREIGN KEY (`form_id`) REFERENCES `{PREFIX}forms`(`id`) ON DELETE CASCADE
);
```

---

## 4. API Design

### 4.1 Public Methods

#### FormService

```php
class FormService
{
    // Form Management
    public function createForm(string $id, string $name, string $type = Form::TYPE_CONTACT): Form;
    public function getForm(string $id): ?Form;
    public function getAllForms(): array;
    
    // CF7 Integration
    public function generateCf7Form(Form $form): string;
    public function renderCf7Shortcode(Form $form): string;
    
    // Submission Processing
    public function processSubmission(Form $form, array $postData, array $serverData = []): FormSubmission;
    
    // Templates
    public function getStandardFields(): array;
    public function createLeadForm(string $id, string $name): Form;
    public function createContactForm(string $id, string $name): Form;
}
```

#### Form Entity

```php
class Form implements JsonSerializable
{
    // Constants
    public const TYPE_CONTACT = 'contact';
    public const TYPE_LEAD = 'lead';
    public const TYPE_SUPPORT = 'support';
    public const TYPE_CUSTOM = 'custom';
    
    // Getters
    public function getId(): string;
    public function getName(): string;
    public function getFormType(): string;
    public function getFields(): array;
    public function getSettings(): array;
    public function isActive(): bool;
    public function getCf7Shortcode(): ?string;
    public function getWebhooks(): array;
    
    // Setters/Modifiers
    public function setName(string $name): self;
    public function setDescription(string $description): self;
    public function setActive(bool $active): self;
    public function setCf7Shortcode(?string $shortcode): self;
    public function addField(FormField $field): self;
    public function removeField(string $fieldId): self;
    public function setSetting(string $key, $value): self;
    public function addWebhook(string $url, string $event = 'submit'): self;
    
    // Validation
    public function isValid(): bool;
    public function generateCf7Shortcode(): string;
    
    // Serialization
    public static function fromArray(array $data): self;
    public function jsonSerialize(): array;
}
```

#### FormField Entity

```php
class FormField implements JsonSerializable
{
    // Type Constants
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
    
    // Methods
    public function setLabel(string $label): self;
    public function setRequired(bool $required): self;
    public function setDefaultValue(?string $defaultValue): self;
    public function setValidation(array $validation): self;
    public function addOption(string $value, string $label): self;
    public function setPlaceholder(?string $placeholder): self;
    public function toCf7Field(): string;
    public function jsonSerialize(): array;
}
```

#### FormSubmission Entity

```php
class FormSubmission implements JsonSerializable
{
    // Status Constants
    public const STATUS_PENDING = 'pending';
    public const STATUS_PROCESSED = 'processed';
    public const STATUS_FAILED = 'failed';
    
    // Methods
    public function setVisitorId(?string $visitorId): self;
    public function setContactId(?string $contactId): self;
    public function setData(array $data): self;
    public function setStatus(string $status): self;
    public function setIpAddress(?string $ipAddress): self;
    public function setUserAgent(?string $userAgent): self;
    public function getFieldValue(string $field, $default = null);
    public function getEmail(): ?string;
    public function getFullName(): ?string;
    public function jsonSerialize(): array;
}
```

---

## 5. Sequence Diagrams

### 5.1 Contact Form Submission

```
Client          Adapter         FormService       Repository       Webhook
  │                │                │                │               │
  │ Submit POST    │                │                │               │
  │───────────────>│                │                │               │
  │                │ processSubmission()              │               │
  │                │───────────────>│                │               │
  │                │                │ Validate fields│               │
  │                │                │──────┐         │               │
  │                │                │      │ OK       │               │
  │                │                │<─────┘         │               │
  │                │                │                │               │
  │                │                │ findOrCreateContact()           │
  │                │                │───────────────>│               │
  │                │                │               │               │
  │                │                │<──────────────│               │
  │                │                │               │               │
  │                │                │ Update status │               │
  │                │                │──────┐        │               │
  │                │                │      │ Done   │               │
  │                │                │<─────┘        │               │
  │                │                │               │               │
  │                │                │ Trigger webhooks              │
  │                │                │──────────────────────────────>│
  │                │                │               │               │
  │ Response       │<───────────────│               │               │
  │<───────────────│                │               │               │
```

---

## 6. Error Handling

### 6.1 Exception Handling Strategy

| Scenario | Response | Status Code |
|----------|----------|-------------|
| Invalid form ID | 404 Not Found | 404 |
| Validation failure | 400 Bad Request | 400 |
| Repository error | 500 Internal Error | 500 |
| Webhook timeout | Log & continue | N/A |
| Missing required field | 400 Bad Request | 400 |

### 6.2 Validation Error Response

```json
{
  "success": false,
  "errors": {
    "email": "Invalid email format",
    "phone": "Required field"
  }
}
```

---

## 7. Security Considerations

### 7.1 Input Validation
- All inputs sanitized before processing
- Email format validation using filter_var()
- XSS prevention via htmlspecialchars()

### 7.2 CSRF Protection
- Form submissions require CSRF token
- Token validated on server-side

### 7.3 Rate Limiting
- Recommended: 100 requests/minute per IP
- Webhook failures logged for monitoring

---

## 8. Deployment Configuration

### 8.1 Composer Requirements

```json
{
  "require": {
    "php": ">=7.3"
  },
  "autoload": {
    "psr-4": {
      "Ksfraser\\Forms\\": "src/Ksfraser/Forms/"
    }
  }
}
```

### 8.2 Environment Variables

| Variable | Description | Default |
|----------|-------------|---------|
| FORM_WEBHOOK_TIMEOUT | Webhook request timeout | 10 |
| FORM_MAX_SUBMISSIONS | Rate limit per minute | 100 |

---

*Document Version: 1.0.0*
*Last Updated: 2026-05-13*