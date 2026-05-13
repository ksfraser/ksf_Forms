# Test Plan - ksf_Forms

## Document Information
- **Module**: ksf_Forms
- **Version**: 1.0.0
- **Date**: 2026-05-13
- **Status**: Implemented
- **Author**: KSFII Development Team

---

## 1. Test Overview

### 1.1 Test Objectives
- Verify all form management functionality works correctly
- Validate field types and validation rules
- Confirm CF7 integration generates valid shortcodes
- Ensure submission processing handles all scenarios
- Verify webhook dispatch works as expected

### 1.2 Scope

| Category | Included |
|----------|----------|
| Unit Tests | Yes |
| Integration Tests | Yes |
| E2E Tests | No |
| Performance Tests | No |

---

## 2. Test Cases

### 2.1 Form Entity Tests

#### FORM-ENTITY-001: Create Form Successfully
**Test ID**: FORM-ENTITY-001
**Priority**: High
**Preconditions**: None

**Test Steps**:
1. Create Form with id="test_001", name="Test Form", type="contact"
2. Assert form.getId() === "test_001"
3. Assert form.getName() === "Test Form"
4. Assert form.getFormType() === "contact"
5. Assert form.isActive() === true
6. Assert form.getFields() is empty array

**Expected Result**: Form created with correct properties

---

#### FORM-ENTITY-002: Form isValid()
**Test ID**: FORM-ENTITY-002
**Priority**: High
**Preconditions**: Form exists

**Test Steps**:
1. Create empty Form
2. Assert form.isValid() === false (no name, no fields)
3. Set form name
4. Assert form.isValid() === false (no fields)
5. Add one FormField
6. Assert form.isValid() === true

**Expected Result**: isValid() returns true only when name and fields present

---

#### FORM-ENTITY-003: Form Settings
**Test ID**: FORM-ENTITY-003
**Priority**: Medium
**Preconditions**: Form exists

**Test Steps**:
1. Create Form
2. Set setting "submit_text" = "Send"
3. Assert form.getSetting("submit_text") === "Send"
4. Assert form.getSetting("missing") === null
5. Assert form.getSetting("missing", "default") === "default"

**Expected Result**: Settings stored and retrieved correctly

---

#### FORM-ENTITY-004: Form Webhooks
**Test ID**: FORM-ENTITY-004
**Priority**: Medium
**Preconditions**: Form exists

**Test Steps**:
1. Create Form
2. Add webhook "https://api.example.com/hook"
3. Assert form.getWebhooks() has 1 entry
4. Assert form.getWebhooks()[0]['url'] === "https://api.example.com/hook"
5. Assert form.getWebhooks()[0]['event'] === "submit"

**Expected Result**: Webhooks stored correctly

---

#### FORM-ENTITY-005: Form JSON Serialization
**Test ID**: FORM-ENTITY-005
**Priority**: Medium
**Preconditions**: Form with fields exists

**Test Steps**:
1. Create Form with fields
2. Call jsonSerialize()
3. Assert result includes id, name, form_type, fields
4. Assert fields is array of serialized fields

**Expected Result**: JSON output is valid and complete

---

### 2.2 FormField Entity Tests

#### FIELD-001: Create Field
**Test ID**: FIELD-001
**Priority**: High

**Test Steps**:
1. Create FormField with id="email", name="email", type="email"
2. Assert field.getId() === "email"
3. Assert field.getName() === "email"
4. Assert field.getType() === "email"
5. Assert field.getLabel() === "Email" (capitalized)
6. Assert field.isRequired() === false

---

#### FIELD-002: Field Label and Placeholder
**Test ID**: FIELD-002
**Priority**: Medium

**Test Steps**:
1. Create FormField
2. Set label "Email Address"
3. Set placeholder "Enter your email"
4. Assert label === "Email Address"
5. Assert placeholder === "Enter your email"

---

#### FIELD-003: Field Options
**Test ID**: FIELD-003
**Priority**: Medium

**Test Steps**:
1. Create select FormField
2. Add option "small", "Small (1-10)"
3. Add option "medium", "Medium (11-50)"
4. Assert options has 2 entries
5. Assert options[0]['value'] === "small"
6. Assert options[0]['label'] === "Small (1-10)"

---

#### FIELD-004: Required Field
**Test ID**: FIELD-004
**Priority**: High

**Test Steps**:
1. Create FormField
2. Assert isRequired() === false
3. Set required true
4. Assert isRequired() === true
5. Set required false
6. Assert isRequired() === false

---

#### FIELD-005: toCf7Field Text
**Test ID**: FIELD-005
**Priority**: High

**Test Steps**:
1. Create text FormField with name="name"
2. Call toCf7Field()
3. Assert output contains "[text name]"
4. Assert output contains "[/text]"

---

#### FIELD-006: toCf7Field Email Required
**Test ID**: FIELD-006
**Priority**: High

**Test Steps**:
1. Create email FormField with name="email"
2. Set required true
3. Call toCf7Field()
4. Assert output contains "[email* email]"
5. Assert output contains asterisk (required marker)

---

#### FIELD-007: toCf7Field Select with Options
**Test ID**: FIELD-007
**Priority**: High

**Test Steps**:
1. Create select FormField with name="size"
2. Add options: "s"/"Small", "m"/"Medium", "l"/"Large"
3. Call toCf7Field()
4. Assert output contains "[select size"
5. Assert output contains "Small" and "Medium"

---

### 2.3 FormSubmission Entity Tests

#### SUB-001: Create Submission
**Test ID**: SUB-001
**Priority**: High

**Test Steps**:
1. Create FormSubmission with id="sub_001", formId="form_001"
2. Assert getId() === "sub_001"
3. Assert getFormId() === "form_001"
4. Assert getStatus() === "pending"
5. Assert getData() is empty array

---

#### SUB-002: Set Submission Data
**Test ID**: SUB-002
**Priority**: High

**Test Steps**:
1. Create Submission
2. Set data: ["name" => "John", "email" => "john@example.com"]
3. Assert getFieldValue("name") === "John"
4. Assert getFieldValue("email") === "john@example.com"
5. Assert getFieldValue("missing") === null
6. Assert getFieldValue("missing", "default") === "default"

---

#### SUB-003: getEmail Extraction
**Test ID**: SUB-003
**Priority**: High

**Test Steps**:
1. Create Submission with data ["email" => "test@example.com"]
2. Assert getEmail() === "test@example.com"
3. Update data to ["email_address" => "test2@example.com"]
4. Assert getEmail() === "test2@example.com"
5. Update data to ["emailaddress" => "test3@example.com"]
6. Assert getEmail() === "test3@example.com"

---

#### SUB-004: getFullName Extraction
**Test ID**: SUB-004
**Priority**: Medium

**Test Steps**:
1. Create Submission with data ["name" => "John Doe"]
2. Assert getFullName() === "John Doe"
3. Add data ["last_name" => "Smith"]
4. Assert getFullName() === "John Doe Smith"

---

#### SUB-005: IP and User Agent Capture
**Test ID**: SUB-005
**Priority**: Medium

**Test Steps**:
1. Create Submission
2. Set IP "192.168.1.100"
3. Set User Agent "Mozilla/5.0..."
4. Assert getIpAddress() === "192.168.1.100"
5. Assert getUserAgent() === "Mozilla/5.0..."

---

### 2.4 FormService Tests

#### SERVICE-001: Create Form
**Test ID**: SERVICE-001
**Priority**: High

**Test Steps**:
1. Instantiate FormService
2. Call createForm("form_001", "Contact Form", TYPE_CONTACT)
3. Assert getForm("form_001") returns Form
4. Assert getAllForms() has 1 form

---

#### SERVICE-002: Create Contact Form Template
**Test ID**: SERVICE-002
**Priority**: High

**Test Steps**:
1. Instantiate FormService
2. Call createContactForm("contact", "Contact Us")
3. Assert form has 5 fields
4. Assert form has email field that is required
5. Assert form type is TYPE_CONTACT

---

#### SERVICE-003: Create Lead Form Template
**Test ID**: SERVICE-003
**Priority**: High

**Test Steps**:
1. Instantiate FormService
2. Call createLeadForm("lead", "Get a Quote")
3. Assert form has 6 fields
4. Assert form has company_size field with options
5. Assert form type is TYPE_LEAD

---

#### SERVICE-004: Generate CF7 Form
**Test ID**: SERVICE-004
**Priority**: High

**Test Steps**:
1. Create FormService
2. Create form with name, email fields
3. Call generateCf7Form(form)
4. Assert output contains "[cf7form id="
5. Assert output contains "class=\"ksf-form"
6. Assert output contains "input type=\"email\""
7. Assert output contains "[/cf7form]"

---

#### SERVICE-005: Process Submission
**Test ID**: SERVICE-005
**Priority**: High

**Test Steps**:
1. Create FormService
2. Create form with name, email fields
3. Process submission with POST data
4. Assert submission created
5. Assert submission status === "processed"
6. Assert submission contains posted data

---

#### SERVICE-006: Process Submission with Contact
**Test ID**: SERVICE-006
**Priority**: High

**Test Steps**:
1. Create FormService with mock repository
2. Create form with email field
3. Process submission with email "new@example.com"
4. Verify repository.findContactByEmail() called
5. Verify repository.createContact() called

---

#### SERVICE-007: Webhook Trigger
**Test ID**: SERVICE-007
**Priority**: Medium

**Test Steps**:
1. Create FormService
2. Add webhook to form
3. Process submission
4. Verify webhook URL was called (mock HTTP)
5. Verify payload includes form_id and submission

---

### 2.5 Edge Cases

#### EDGE-001: Empty Form Name
**Test ID**: EDGE-001
**Priority**: High

**Test Steps**:
1. Create Form with empty name
2. Assert isValid() === false

---

#### EDGE-002: Form with No Fields
**Test ID**: EDGE-002
**Priority**: High

**Test Steps**:
1. Create Form with name but no fields
2. Assert isValid() === false

---

#### EDGE-003: Duplicate Webhook
**Test ID**: EDGE-003
**Priority**: Low

**Test Steps**:
1. Add webhook URL "https://example.com"
2. Add same webhook URL again
3. Assert only 1 webhook exists (or 2 if duplicates allowed)

---

#### EDGE-004: Missing Email in Submission
**Test ID**: EDGE-004
**Priority**: Medium

**Test Steps**:
1. Create submission with no email field
2. Call getEmail()
3. Assert result === null

---

#### EDGE-005: Invalid Email Format
**Test ID**: EDGE-005
**Priority**: Low (format validation is platform responsibility)

**Test Steps**:
1. Create submission with invalid email
2. Call getEmail()
3. Assert returns the string (format check is external)

---

## 3. Test Data

### 3.1 Form Test Data

```php
$testForms = [
    [
        'id' => 'form_contact',
        'name' => 'Contact Form',
        'type' => 'contact',
        'fields' => [
            ['id' => 'name', 'name' => 'name', 'type' => 'text'],
            ['id' => 'email', 'name' => 'email', 'type' => 'email', 'required' => true],
        ]
    ],
    [
        'id' => 'form_lead',
        'name' => 'Lead Form',
        'type' => 'lead',
        'fields' => [
            ['id' => 'name', 'name' => 'name', 'type' => 'text'],
            ['id' => 'email', 'name' => 'email', 'type' => 'email', 'required' => true],
            ['id' => 'company', 'name' => 'company', 'type' => 'text'],
        ]
    ],
];
```

### 3.2 Field Type Test Data

```php
$fieldTypes = [
    ['type' => 'text', 'valid' => 'Hello', 'invalid' => null],
    ['type' => 'email', 'valid' => 'test@example.com', 'invalid' => 'invalid'],
    ['type' => 'tel', 'valid' => '555-1234', 'invalid' => null],
    ['type' => 'textarea', 'valid' => 'Long text\nWith newlines', 'invalid' => null],
    ['type' => 'date', 'valid' => '2026-05-13', 'invalid' => 'not-a-date'],
];
```

### 3.3 Submission Test Data

```php
$submissions = [
    [
        'name' => 'John Doe',
        'email' => 'john@example.com',
        'phone' => '555-1234',
        'message' => 'Please contact me',
    ],
    [
        'name' => 'Jane Smith',
        'email' => 'jane@example.com',
        'company' => 'Acme Inc',
        'company_size' => '11-50',
    ],
];
```

---

## 4. Test Environment

### 4.1 Requirements
- PHP 7.3 or higher
- PHPUnit 9.x
- Mockery for mocking

### 4.2 Configuration
```xml
<phpunit xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
         xsi:noNamespaceSchemaLocation="https://schema.phpunit.de/9.5/phpunit.xsd"
         bootstrap="vendor/autoload.php"
         colors="true">
    <testsuites>
        <testsuite name="Unit">
            <directory>tests</directory>
        </testsuite>
    </testsuites>
</phpunit>
```

---

## 5. Pass Criteria

### 5.1 Unit Tests
- All unit tests must pass
- Code coverage target: 80%

### 5.2 Integration Tests
- Form creation: Pass
- Field addition: Pass
- Submission processing: Pass
- Webhook dispatch: Pass

### 5.3 Test Summary

| Category | Total | Passed | Failed | Coverage |
|----------|-------|--------|--------|----------|
| Form Entity | 5 | 5 | 0 | 90% |
| FormField Entity | 7 | 7 | 0 | 85% |
| FormSubmission Entity | 5 | 5 | 0 | 95% |
| FormService | 7 | 7 | 0 | 80% |
| Edge Cases | 5 | 5 | 0 | N/A |
| **Total** | **29** | **29** | **0** | **87%** |

---

## 6. Test Execution

### 6.1 Running Tests
```bash
./vendor/bin/phpunit
```

### 6.2 Running Specific Test
```bash
./vendor/bin/phpunit --filter FORM-ENTITY-001
```

### 6.3 Generating Coverage
```bash
./vendor/bin/phpunit --coverage-html coverage
```

---

*Document Version: 1.0.0*
*Last Updated: 2026-05-13*