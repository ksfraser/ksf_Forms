# Business Requirements - ksf_Forms

## Document Information
- **Module**: ksf_Forms
- **Version**: 1.0.0
- **Date**: 2026-05-13
- **Status**: Implemented
- **Author**: KSFII Development Team

---

## 1. Project Overview

### 1.1 Purpose
The ksf_Forms module provides a comprehensive form builder and submission management system for the KSF ecosystem. It enables dynamic creation of marketing, sales, and support forms with seamless integration to Contact Form 7 (CF7) for WordPress deployments.

### 1.2 Business Problem Statement
Organizations need to capture leads, support requests, and contact information through custom web forms. Manual form creation is time-consuming and error-prone. The ksf_Forms module provides:
- Centralized form definition management
- Standardized form templates for common use cases
- Automated lead capture and contact creation
- Webhook-based integration with external systems
- CF7 compatibility for WordPress deployments

### 1.3 Scope
| Category | Included |
|----------|----------|
| Form Builder | Yes |
| Field Types | Text, Email, Tel, Textarea, Select, Checkbox, Radio, File, Date, Hidden |
| Validation | Yes |
| Webhook Integration | Yes |
| Lead Capture | Yes |
| CF7 Export | Yes |
| Multi-language | No (Future) |

---

## 2. Module Architecture

### 2.1 Namespace Structure
```
Ksfraser\Forms\
├── Entity\
│   ├── Form.php         # Form definition entity
│   ├── FormField.php    # Individual field definition
│   └── FormSubmission.php # Submission data
└── Service\
    └── FormService.php  # Business logic and CF7 integration
```

### 2.2 Core Entities

#### Form Entity
The `Form` class encapsulates form metadata and field collection:

| Property | Type | Description |
|----------|------|-------------|
| id | string | Unique form identifier |
| name | string | Display name |
| description | string | Form description |
| formType | string | TYPE_CONTACT, TYPE_LEAD, TYPE_SUPPORT, TYPE_CUSTOM |
| fields | array | Collection of FormField entities |
| settings | array | Form configuration |
| isActive | bool | Active/inactive status |
| cf7Shortcode | ?string | Generated CF7 shortcode |
| webhooks | array | External webhook endpoints |

#### FormField Entity
Represents individual form fields with validation:

| Property | Type | Description |
|----------|------|-------------|
| id | string | Field identifier |
| name | string | Field name (form submission key) |
| type | string | Field type (text, email, select, etc.) |
| label | string | Display label |
| required | bool | Required field flag |
| defaultValue | ?string | Default value |
| validation | array | Validation rules |
| options | array | Select/radio/checkbox options |
| placeholder | ?string | HTML placeholder text |

#### FormSubmission Entity
Captures form submission data:

| Property | Type | Description |
|----------|------|-------------|
| id | string | Submission identifier |
| formId | string | Parent form ID |
| visitorId | ?string | Anonymous visitor ID |
| contactId | ?string | Linked contact ID |
| data | array | Field values |
| status | string | PENDING, PROCESSED, FAILED |
| ipAddress | ?string | Submitter IP |
| userAgent | ?string | Browser user agent |
| createdAt | string | Timestamp |

---

## 3. Functional Features

### 3.1 Form Types

| Type | Constant | Use Case |
|------|----------|----------|
| Contact | `TYPE_CONTACT` | General inquiries, customer support |
| Lead | `TYPE_LEAD` | Sales lead capture with company info |
| Support | `TYPE_SUPPORT` | Technical support requests |
| Custom | `TYPE_CUSTOM` | User-defined forms |

### 3.2 Supported Field Types

| Type | Description | Validation |
|------|-------------|------------|
| text | Single-line text | min/max length |
| email | Email address | RFC 5322 format |
| tel | Phone number | Pattern matching |
| textarea | Multi-line text | min/max length |
| select | Dropdown list | Required selection |
| checkbox | Checkbox group | At least one checked |
| radio | Radio button group | Required selection |
| file | File upload | Type/size limits |
| date | Date picker | Valid date format |
| hidden | Hidden field | N/A |

### 3.3 Standard Form Templates

The module provides pre-built form templates:

#### Contact Form Template
- Name field
- Email field (required)
- Phone field
- Subject field
- Message textarea

#### Lead Form Template
- Name field
- Email field (required)
- Phone field
- Company field
- Company size dropdown (1-10, 11-50, 51-200, 200+)
- Message textarea

### 3.4 Webhook Integration
Forms can trigger external webhooks on submission:

```json
{
  "event": "submit",
  "url": "https://api.example.com/webhook",
  "headers": {
    "Content-Type": "application/json",
    "X-KSF-Form": "<form_id>"
  }
}
```

---

## 4. Integration Dependencies

### 4.1 Provided To

| Module | Data Exposed | Events |
|--------|--------------|--------|
| ksf_Forms_UI | Form definitions, submissions | form.created, form.submitted |
| ksf_FA_Forms | Form data | form.* |

### 4.2 External Integrations

| System | Integration Type | Description |
|--------|------------------|-------------|
| Contact Form 7 | Export | Generate CF7-compatible shortcodes |
| WordPress | Deployment | Deploy forms to WordPress sites |
| CRM | Webhook | Forward submissions to CRM systems |

### 4.3 Repository Integration

The FormService supports optional repository injection for:
- `findContactByEmail()` - Look up existing contacts
- `createContact()` - Create new contact records

---

## 5. Data Flow

### 5.1 Form Submission Flow

```
User submits form
    ↓
HTTP POST to endpoint
    ↓
FormService::processSubmission()
    ↓
Validate POST data
    ↓
Create FormSubmission entity
    ↓
Extract email → findOrCreateContact()
    ↓
Update submission with contact_id
    ↓
Set status to PROCESSED
    ↓
Trigger webhooks (async)
    ↓
Return FormSubmission
```

### 5.2 CF7 Export Flow

```
Form definition created
    ↓
FormService::generateCf7Form()
    ↓
Map field types to CF7 tags
    ↓
Generate CF7 shortcode syntax
    ↓
Return CF7-formatted string
    ↓
[cf7form id="..."]...[field definitions]...[/cf7form]
```

---

## 6. Configuration

### 6.1 Form Settings

| Setting | Type | Default | Description |
|---------|------|---------|-------------|
| submit_button_text | string | "Submit" | Submit button label |
| success_message | string | "Thank you" | Success message |
| error_message | string | "Error" | Error message |
| redirect_url | ?string | null | Redirect after submit |
| notify_email | ?string | null | Email notification |

### 6.2 Validation Rules

| Rule | Format | Example |
|------|--------|---------|
| required | boolean | `true` |
| min_length | integer | `5` |
| max_length | integer | `100` |
| pattern | regex | `[A-Z]{2}[0-9]{4}` |

---

## 7. Non-Functional Requirements

### 7.1 Performance
- Form rendering: < 50ms
- Submission processing: < 200ms
- Webhook timeout: 10 seconds

### 7.2 Security
- CSRF protection via form tokens
- XSS prevention via output escaping
- SQL injection prevention via parameterized queries
- Rate limiting recommended at infrastructure level

### 7.3 Scalability
- Stateless service design
- Horizontal scaling via webhook queues
- Database sharding for high-volume submissions

---

## 8. Future Enhancements

| Feature | Priority | Description |
|---------|----------|-------------|
| Multi-language | Medium | i18n support |
| Form analytics | Low | View tracking, A/B testing |
| Conditional logic | Medium | Show/hide fields based on values |
| File attachments | Medium | Handle file uploads |
| Payment integration | Low | Stripe, PayPal |

---

## 9. Sign-off

| Role | Name | Date | Signature |
|------|------|------|-----------|
| Business Analyst | | | |
| Technical Lead | | | |
| QA Lead | | | |

---

*Document Version: 1.0.0*
*Last Updated: 2026-05-13*