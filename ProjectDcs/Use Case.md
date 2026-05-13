# Use Case - ksf_Forms

## Document Information
- **Module**: ksf_Forms
- **Version**: 1.0.0
- **Date**: 2026-05-13
- **Status**: Implemented
- **Author**: KSFII Development Team

---

## 1. Use Case Overview

| Use Case ID | Use Case Name | Actor | Priority |
|-------------|---------------|-------|----------|
| UC-FORM-001 | Create Form | Form Manager | High |
| UC-FORM-002 | Add Form Field | Form Manager | High |
| UC-FORM-003 | Generate CF7 Shortcode | Developer | High |
| UC-FORM-004 | Submit Form | Website Visitor | High |
| UC-FORM-005 | Process Submission | System | High |
| UC-FORM-006 | Configure Webhook | Form Manager | Medium |
| UC-FORM-007 | Create Lead Form | Form Manager | Medium |
| UC-FORM-008 | Track Submission | System | Low |

---

## 2. Use Case Details

### UC-FORM-001: Create Form

**Actor**: Form Manager
**Priority**: High
**Preconditions**:
- User has access to form management interface
- Form ID is unique

**Basic Flow**:
1. User selects "Create Form"
2. System displays form creation form
3. User enters form ID, name, and type
4. User clicks "Create"
5. System validates input
6. System creates Form entity
7. System returns created form

**Alternative Flows**:
- **Duplicate ID**: Display error "Form ID already exists"
- **Invalid Name**: Display error "Form name is required"

**Postconditions**:
- Form entity exists in system
- Form is ready to receive fields

**Business Rules**:
- Form ID must be alphanumeric with underscores
- Form name max 255 characters
- Form type must be valid constant

---

### UC-FORM-002: Add Form Field

**Actor**: Form Manager
**Priority**: High
**Preconditions**:
- Form exists in system

**Basic Flow**:
1. User selects form
2. User clicks "Add Field"
3. System displays field configuration form
4. User enters field properties (name, type, label, etc.)
5. User configures validation rules (optional)
6. User clicks "Save"
7. System creates FormField entity
8. System adds field to form
9. System updates form validation

**Alternative Flows**:
- **Invalid Field Name**: Display error and return to form
- **Duplicate Field**: Display error "Field name already exists"

**Postconditions**:
- FormField added to form's field collection
- Form re-validated for completeness

---

### UC-FORM-003: Generate CF7 Shortcode

**Actor**: Developer
**Priority**: High
**Preconditions**:
- Form exists with at least one field

**Basic Flow**:
1. Developer calls FormService::generateCf7Form(form)
2. System maps form type to CSS class
3. System iterates through fields
4. For each field, system:
   - Generates label HTML
   - Generates input/select/textarea HTML
   - Maps field type to HTML input type
   - Includes validation attributes
   - Handles options for select/checkbox/radio
5. System wraps in CF7 shortcode tags
6. System returns complete CF7-formatted string

**Output Example**:
```
[cf7form id="form_001" title="Contact Us"]
<div class="ksf-form ksf-form-contact">
  <div class="ksf-form-group">
    <label for="email">Email *</label>
    <input type="email*" name="email" id="email" required>
  </div>
</div>
[/cf7form]
```

**Postconditions**:
- CF7 shortcode can be pasted into WordPress

---

### UC-FORM-004: Submit Form

**Actor**: Website Visitor
**Priority**: High
**Preconditions**:
- Form is published on website
- Form is active

**Basic Flow**:
1. Visitor fills form fields
2. Visitor clicks "Submit"
3. Browser sends POST request
4. Platform adapter receives request
5. Adapter calls FormService::processSubmission()
6. System creates submission record
7. System captures IP and user agent
8. System validates required fields
9. System extracts email address
10. System updates submission with contact ID
11. System triggers webhooks
12. System returns success response

**Alternative Flows**:
- **Missing Required**: Display validation errors
- **Webhook Failure**: Log error, continue processing
- **Invalid Email**: Display error "Invalid email format"

**Postconditions**:
- FormSubmission stored in database
- Contact record linked (if found/created)
- Webhook notifications sent

---

### UC-FORM-005: Process Submission

**Actor**: System
**Priority**: High
**Preconditions**:
- Form submission received

**Basic Flow**:
1. System receives POST data
2. System validates all fields
3. System creates FormSubmission entity
4. System extracts email using getEmail()
5. System checks repository for existing contact
6. If no contact and email exists, create contact
7. System links contact ID to submission
8. System sets status to PROCESSED
9. System dispatches webhooks
10. System returns submission entity

**Error Handling**:
- Repository unavailable: Continue without contact linkage
- Webhook timeout: Log error, do not fail submission
- Invalid data: Set status to FAILED

**Postconditions**:
- Submission status is PROCESSED or FAILED
- Contact ID linked (if applicable)

---

### UC-FORM-006: Configure Webhook

**Actor**: Form Manager
**Priority**: Medium
**Preconditions**:
- Form exists in system

**Basic Flow**:
1. User selects form
2. User navigates to "Webhooks" tab
3. User clicks "Add Webhook"
4. User enters webhook URL
5. User selects trigger event (default: submit)
6. User clicks "Save"
7. System validates URL format
8. System adds webhook to form

**Alternative Flows**:
- **Invalid URL**: Display error "Invalid webhook URL"
- **Connection Failed**: Display warning but allow save

**Postconditions**:
- Webhook registered on form
- Webhook will fire on configured event

---

### UC-FORM-007: Create Lead Form

**Actor**: Form Manager
**Priority**: Medium
**Preconditions**:
- None

**Basic Flow**:
1. User selects "Create Lead Form"
2. User enters form ID and name
3. System calls FormService::createLeadForm(id, name)
4. System creates new Form with TYPE_LEAD
5. System adds standard fields:
   - name (text)
   - email (email, required)
   - phone (tel)
   - company (text)
   - company_size (select with options)
   - message (textarea)
6. System returns populated form

**Postconditions**:
- Lead form ready with pre-configured fields

---

### UC-FORM-008: Track Submission

**Actor**: System
**Priority**: Low
**Preconditions**:
- Submission created

**Basic Flow**:
1. System captures visitor ID from session
2. System captures IP address from server
3. System captures user agent string
4. System stores all metadata with submission

**Postconditions**:
- Full audit trail stored with submission

---

## 3. Sequence Diagrams

### UC-FORM-004: Submit Form

```
┌─────────────┐    ┌─────────────┐    ┌─────────────┐    ┌─────────────┐
│  Visitor    │    │  Platform   │    │ FormService │    │ Repository  │
└─────────────┘    └─────────────┘    └─────────────┘    └─────────────┘
       │                 │                 │                 │
       │ Submit POST     │                 │                 │
       │────────────────>│                 │                 │
       │                 │                 │                 │
       │                 │ processSubmission()                │
       │                 │───────────────>│                 │
       │                 │                 │                 │
       │                 │                 │ Validate        │
       │                 │                 │──────┐          │
       │                 │                 │      │ OK      │
       │                 │                 │<─────┘         │
       │                 │                 │                 │
       │                 │                 │ getEmail()      │
       │                 │                 │──────┐          │
       │                 │                 │      │ email    │
       │                 │                 │<─────┘         │
       │                 │                 │                 │
       │                 │                 │ findContactByEmail()
       │                 │                 │────────────────>│
       │                 │                 │                 │
       │                 │                 │<────────────────│
       │                 │                 │                 │
       │                 │                 │ Update status   │
       │                 │                 │                 │
       │                 │                 │ triggerWebhooks │
       │                 │                 │                 │
       │                 │<───────────────│                 │
       │                 │                 │                 │
       │ Success         │                 │                 │
       │<────────────────│                 │                 │
```

---

## 4. Activity Diagram

### Form Submission Process

```
[Start] ──> [Fill Form Fields]
                    │
                    ▼
         ┌──────────────────┐
         │ All Required     │
         │ Fields Filled?   │
         └──────────────────┘
              │        │
             No       Yes
              │        │
              ▼        ▼
     ┌──────────┐   [Submit]
     │Show Errors│        │
     └──────────┘        ▼
          │         [POST Request]
          │              │
          ▼              ▼
    [User Fixes]    [Validate Data]
          │              │
          └──────┬───────┘
                 │
                 ▼
        ┌─────────────────┐
        │ Valid Data?     │
        └─────────────────┘
              │       │
             No      Yes
              │       │
              ▼       ▼
      ┌──────────┐ [Process]
      │Return 400│      │
      └──────────┘      ▼
                        │ [Create Submission]
                        │
                        ▼
                  [Extract Email]
                        │
                        ▼
              ┌──────────────────┐
              │ Contact Exists?  │
              └──────────────────┘
                   │         │
                  No        Yes
                   │         │
                   ▼         ▼
            [Create]    [Link Existing]
              │              │
              └──────┬───────┘
                     │
                     ▼
            [Update Status: PROCESSED]
                     │
                     ▼
              [Trigger Webhooks]
                     │
                     ▼
              [Return Response]
                     │
                     ▼
                   [End]
```

---

## 5. Data Requirements

### 5.1 Input Data

| Data | Source | Required | Format |
|------|--------|----------|--------|
| Form ID | User | Yes | String (max 32) |
| Form Name | User | Yes | String (max 255) |
| Form Type | User | Yes | Enum |
| Field Name | User | Yes | String |
| Field Type | User | Yes | Enum |
| Field Label | User | No | String |
| Email | Form Submit | Conditional | Email format |
| POST Data | HTTP POST | Yes | Array |

### 5.2 Output Data

| Data | Destination | Format |
|------|-------------|--------|
| Form Entity | Display | Object |
| CF7 Shortcode | WordPress | String |
| Submission Record | Database | Object |
| Webhook Payload | External API | JSON |
| Success/Error | Browser | HTML/JSON |

---

## 6. Non-Functional Requirements

### 6.1 Performance
- Form rendering: < 50ms
- Submission processing: < 200ms
- Webhook dispatch: < 10s timeout

### 6.2 Availability
- Form submissions must not be lost
- Webhook failures must be logged

### 6.3 Security
- CSRF tokens required
- Input sanitization on all fields
- XSS prevention via output encoding

---

## 7. Use Case Traceability

| UC ID | Related FR | Related Test |
|-------|------------|-------------|
| UC-FORM-001 | FR-FORM-001 | FORM-CREATE-001 |
| UC-FORM-002 | FR-FIELD-001, FR-FIELD-002 | FIELD-TYPE-001 |
| UC-FORM-003 | FR-CF7-001, FR-CF7-002 | CF7-SC-001 |
| UC-FORM-004 | FR-SUB-001, FR-SUB-002 | SUB-CREATE-001 |
| UC-FORM-005 | FR-SUB-003, FR-SUB-004 | SUB-PROCESS-001 |
| UC-FORM-006 | FR-WH-001, FR-WH-002 | WH-CONFIG-001 |
| UC-FORM-007 | FR-TPL-002, FR-TPL-003 | TPL-LEAD-001 |
| UC-FORM-008 | FR-SUB-001 | SUB-TRACK-001 |

---

*Document Version: 1.0.0*
*Last Updated: 2026-05-13*