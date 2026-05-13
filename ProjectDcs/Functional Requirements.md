# Functional Requirements - ksf_Forms

## Document Information
- **Module**: ksf_Forms
- **Version**: 1.0.0
- **Date**: 2026-05-13
- **Status**: Implemented
- **Author**: KSFII Development Team

---

## 1. Overview

This document details the functional requirements for the ksf_Forms module, including form management, field types, validation, and submission processing.

---

## 2. Form Management

### FR-FORM-001: Form Creation
**Priority**: High
**Description**: System shall allow creation of new form definitions with unique ID, name, and type.

**Acceptance Criteria**:
- [ ] Forms can be created with unique string ID
- [ ] Form name is required, max 255 characters
- [ ] Form type must be one of: contact, lead, support, custom
- [ ] New forms are active by default
- [ ] Form timestamps (created_at, updated_at) are recorded

**Test Data**:
| Input | Expected Result |
|-------|-----------------|
| ID: "form_001", Name: "Contact Us", Type: contact | Form created successfully |
| ID: "form_001" (duplicate) | Error: duplicate ID |

---

### FR-FORM-002: Form Retrieval
**Priority**: High
**Description**: System shall retrieve form definitions by ID or list all forms.

**Acceptance Criteria**:
- [ ] getForm(id) returns Form entity or null
- [ ] getAllForms() returns array of all forms
- [ ] Forms maintain field collection on retrieval

---

### FR-FORM-003: Form Modification
**Priority**: High
**Description**: System shall allow modification of form properties.

**Acceptance Criteria**:
- [ ] Form name can be updated
- [ ] Form description can be set
- [ ] Active status can be toggled
- [ ] Settings can be added/updated
- [ ] CF7 shortcode can be assigned

---

### FR-FORM-004: Form Deletion
**Priority**: Medium
**Description**: System shall remove form fields.

**Acceptance Criteria**:
- [ ] Field can be removed by ID
- [ ] Removing non-existent field has no effect
- [ ] Form remains valid after field removal

---

### FR-FORM-005: Form Validation
**Priority**: High
**Description**: System shall validate form completeness.

**Acceptance Criteria**:
- [ ] Form is valid if name is not empty
- [ ] Form is valid if at least one field exists
- [ ] isValid() returns boolean

---

## 3. Field Management

### FR-FIELD-001: Field Types
**Priority**: High
**Description**: System shall support the following field types.

| Type | Constant | Description |
|------|----------|-------------|
| Text | TYPE_TEXT | Single-line text input |
| Email | TYPE_EMAIL | Email address input |
| Tel | TYPE_TEL | Telephone number input |
| Textarea | TYPE_TEXTAREA | Multi-line text area |
| Select | TYPE_SELECT | Dropdown selection |
| Checkbox | TYPE_CHECKBOX | Checkbox group |
| Radio | TYPE_RADIO | Radio button group |
| File | TYPE_FILE | File upload input |
| Date | TYPE_DATE | Date picker |
| Hidden | TYPE_HIDDEN | Hidden input field |

**Test Data**:
| Type | HTML Input |
|------|------------|
| text | `<input type="text">` |
| email | `<input type="email">` |
| tel | `<input type="tel">` |
| textarea | `<textarea></textarea>` |
| select | `<select></select>` |
| checkbox | `<input type="checkbox">` |
| radio | `<input type="radio">` |
| file | `<input type="file">` |
| date | `<input type="date">` |
| hidden | `<input type="hidden">` |

---

### FR-FIELD-002: Field Properties
**Priority**: High
**Description**: Each field shall have configurable properties.

| Property | Type | Description |
|----------|------|-------------|
| id | string | Unique field identifier |
| name | string | Field name for submission |
| type | string | Field type constant |
| label | string | Display label |
| required | bool | Required field flag |
| defaultValue | ?string | Default value |
| validation | array | Validation rules |
| options | array | Select/radio/checkbox options |
| placeholder | ?string | HTML placeholder text |

---

### FR-FIELD-003: Field Options
**Priority**: Medium
**Description**: Select, checkbox, and radio fields shall support options.

**Acceptance Criteria**:
- [ ] Options added via addOption(value, label)
- [ ] Options stored as array of {value, label} pairs
- [ ] Options rendered in CF7 export

---

### FR-FIELD-004: Field Validation Rules
**Priority**: High
**Description**: Fields shall support validation rules.

**Supported Rules**:
| Rule | Format | Description |
|------|--------|-------------|
| required | boolean | Field is required |
| min_length | integer | Minimum string length |
| max_length | integer | Maximum string length |
| pattern | string | Regex pattern |

---

## 4. Contact Form 7 Integration

### FR-CF7-001: CF7 Shortcode Generation
**Priority**: High
**Description**: System shall generate Contact Form 7 compatible shortcodes.

**Acceptance Criteria**:
- [ ] generateCf7Shortcode() returns `[cf7form id="..." title="..."]`
- [ ] Fields converted to CF7 tag format
- [ ] Required fields marked with asterisk
- [ ] Placeholder text included where set

**Example Output**:
```
[cf7form id="form_001" title="Contact Us"]
<div class="ksf-form ksf-form-contact">
  <div class="ksf-form-group">
    <label for="name">Name</label>
    <input type="text" name="name" id="name">
  </div>
  <div class="ksf-form-group">
    <label for="email">Email *</label>
    <input type="email*" name="email" id="email" required>
  </div>
</div>
[/cf7form]
```

---

### FR-CF7-002: CF7 Field Mapping
**Priority**: High
**Description**: System shall map internal field types to CF7 tags.

| Internal Type | CF7 Tag | Example |
|---------------|---------|---------|
| text | text | `[text name "Name"]` |
| email | email* | `[email* email "email@example.com"]` |
| tel | tel | `[tel phone]` |
| textarea | textarea | `[textarea message]` |
| select | select | `[select menu "Option1|Option2"]` |
| checkbox | checkbox | `[checkbox agree "1"]` |
| radio | radio | `[radio priority "High"|"Medium"|"Low"]` |
| date | date | `[date appointment]` |

---

### FR-CF7-003: Field to CF7 Conversion
**Priority**: High
**Description**: Individual fields shall convert to CF7 format.

**Acceptance Criteria**:
- [ ] toCf7Field() returns CF7 tag string
- [ ] Required fields append asterisk to type
- [ ] Options formatted as pipe-delimited string
- [ ] Validation rules included in tag

---

## 5. Submission Processing

### FR-SUB-001: Submission Creation
**Priority**: High
**Description**: System shall create submission records from POST data.

**Acceptance Criteria**:
- [ ] Submission ID generated as unique string
- [ ] Form ID linked to submission
- [ ] POST data stored as field-value pairs
- [ ] IP address captured from server data
- [ ] User agent captured from server data
- [ ] Timestamp recorded on creation

---

### FR-SUB-002: Data Extraction
**Priority**: Medium
**Description**: System shall extract common fields from submission.

**Acceptance Criteria**:
- [ ] getEmail() finds email from common field names
- [ ] Supported names: email, email_address, emailaddress, contact_email
- [ ] getFullName() combines name fields
- [ ] getFieldValue() returns specific field value
- [ ] Default value returned if field not found

---

### FR-SUB-003: Contact Integration
**Priority**: Medium
**Description**: System shall link submissions to contacts.

**Acceptance Criteria**:
- [ ] Email extracted from submission data
- [ ] Repository checked for existing contact
- [ ] New contact created if not found
- [ ] Submission linked to contact ID
- [ ] Contact creation fails gracefully

---

### FR-SUB-004: Submission Status
**Priority**: High
**Description**: System shall track submission processing status.

| Status | Description |
|--------|-------------|
| pending | Created, not yet processed |
| processed | Successfully processed |
| failed | Processing failed |

**Acceptance Criteria**:
- [ ] New submissions default to PENDING
- [ ] Status updated to PROCESSED on success
- [ ] Status updated to FAILED on error

---

## 6. Webhook Integration

### FR-WH-001: Webhook Configuration
**Priority**: Medium
**Description**: Forms shall support webhook endpoints.

**Acceptance Criteria**:
- [ ] addWebhook(url, event) adds webhook
- [ ] Multiple webhooks supported per form
- [ ] Event filter supported (submit, etc.)

---

### FR-WH-002: Webhook Dispatch
**Priority**: Medium
**Description**: System shall dispatch webhooks on events.

**Payload Format**:
```json
{
  "form_id": "form_001",
  "form_name": "Contact Us",
  "submission": {
    "id": "sub_abc123",
    "form_id": "form_001",
    "data": {...},
    "status": "processed",
    "ip_address": "192.168.1.1",
    "created_at": "2026-05-13T10:30:00Z"
  },
  "timestamp": "2026-05-13T10:30:00+00:00"
}
```

**Acceptance Criteria**:
- [ ] POST request sent to webhook URL
- [ ] Content-Type: application/json header
- [ ] X-KSF-Form header with form ID
- [ ] 10-second timeout
- [ ] Failures logged, not blocking

---

## 7. Form Templates

### FR-TPL-001: Standard Fields
**Priority**: Medium
**Description**: System shall provide standard field definitions.

**Standard Fields**:
| Name | Type | Required | Placeholder |
|------|------|----------|-------------|
| name | text | No | Your name |
| email | email | Yes | - |
| phone | tel | No | - |
| company | text | No | - |
| subject | text | No | - |
| message | textarea | No | - |

---

### FR-TPL-002: Contact Form Template
**Priority**: Medium
**Description**: System shall create contact form with standard fields.

**Fields**:
1. name (text)
2. email (email, required)
3. phone (tel)
4. subject (text)
5. message (textarea)

---

### FR-TPL-003: Lead Form Template
**Priority**: Medium
**Description**: System shall create lead capture form.

**Fields**:
1. name (text)
2. email (email, required)
3. phone (tel)
4. company (text)
5. company_size (select): 1-10, 11-50, 51-200, 200+
6. message (textarea)

---

## 8. Form Rendering

### FR-RENDER-001: HTML Generation
**Priority**: High
**Description**: System shall generate HTML forms.

**Acceptance Criteria**:
- [ ] Form wrapper div with class ksf-form
- [ ] Form type class: ksf-form-contact, ksf-form-lead, ksf-form-support
- [ ] Field groups wrapped in ksf-form-group
- [ ] Labels include required asterisk
- [ ] Select options rendered
- [ ] Textarea rendered
- [ ] Submit button included

---

### FR-RENDER-002: CSS Classes
**Priority**: Low
**Description**: Generated forms shall include CSS classes.

| Element | Class |
|---------|-------|
| Form container | ksf-form, ksf-form-{type} |
| Field group | ksf-form-group |
| Label | - |
| Input/Select/Textarea | - |
| Submit button | - |

---

## 9. Error Handling

### FR-ERR-001: Validation Errors
**Priority**: High
**Description**: System shall handle validation errors gracefully.

**Acceptance Criteria**:
- [ ] Empty name returns invalid
- [ ] Empty fields returns invalid
- [ ] Required field validation on submission
- [ ] Error messages returned for invalid fields

---

### FR-ERR-002: Repository Errors
**Priority**: Medium
**Description**: System shall handle repository errors.

**Acceptance Criteria**:
- [ ] Missing repository methods handled
- [ ] Contact creation failures logged
- [ ] Submission continues without contact linkage

---

## 10. Acceptance Test Matrix

| FR ID | Requirement | Test Cases | Status |
|-------|-------------|------------|--------|
| FR-FORM-001 | Form Creation | FORM-CREATE-001 | ✓ |
| FR-FORM-002 | Form Retrieval | FORM-GET-001 | ✓ |
| FR-FORM-003 | Form Modification | FORM-MOD-001 | ✓ |
| FR-FIELD-001 | Field Types | FIELD-TYPE-001 | ✓ |
| FR-FIELD-002 | Field Properties | FIELD-PROP-001 | ✓ |
| FR-FIELD-003 | Field Options | FIELD-OPT-001 | ✓ |
| FR-CF7-001 | CF7 Shortcode | CF7-SC-001 | ✓ |
| FR-CF7-002 | CF7 Field Mapping | CF7-MAP-001 | ✓ |
| FR-SUB-001 | Submission Creation | SUB-CREATE-001 | ✓ |
| FR-SUB-003 | Contact Integration | SUB-CONTACT-001 | ✓ |
| FR-WH-001 | Webhook Config | WH-CONFIG-001 | ✓ |
| FR-TPL-001 | Standard Fields | TPL-STANDARD-001 | ✓ |

---

*Document Version: 1.0.0*
*Last Updated: 2026-05-13*