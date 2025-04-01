-- Complex Campaign with Escaped Characters

-- Campaigns with special characters
INSERT INTO `campaigns` 
    (`name`, `description`, `is_published`, `created_by`, `created_by_user`, `date_added`) 
VALUES 
    ('Campaign with "Quotes" & Symbols', 
     'Description with \\ backslash, "quoted text", and special chars like © § ¶', 
     1, 
     1, 
     'admin', 
     NOW());

SET @campaign_id = LAST_INSERT_ID();

-- Segments with escaped content
INSERT INTO `lead_lists` 
    (`name`, `alias`, `is_published`, `created_by`, `created_by_user`, `date_added`) 
VALUES 
    ('New "Lead\" Group', 'new_leads_escaped', 1, 1, 'admin', NOW()),
    ('Engaged & Special Contacts', 'engaged_contacts_special', 1, 1, 'admin', NOW());

-- Email templates with complex content
INSERT INTO `email_templates` 
    (`name`, `template_type`, `content`, `subject`) 
VALUES 
    ('Escaped "Template"', 'custom', 
     '<p>Hello with \\ backslash and "quotes"</p>\n<script>alert("XSS test");</script>', 
     'Subject with © and § symbols'),
    ('Unicode Test', 'custom', 
     '<div>Japanese: こんにちは\nChinese: 你好\nEmoji: 😀</div>', 
     'Multilingual & Emoji Subject 🌍');

-- Campaign events with escaped names
INSERT INTO `campaign_events` 
    (`campaign_id`, `name`, `type`, `event_type`, `trigger_mode`) 
VALUES 
    (@campaign_id, 'Event with "Quotes"', 'email', 'action', 'immediate'),
    (@campaign_id, 'Escaped \ Backslash Event', 'condition', 'condition', NULL);

-- Landing page with complex content
INSERT INTO `pages` 
    (`name`, `alias`, `content`, `template`) 
VALUES 
    ('Survey with "Quotes"', 'escaped_survey', 
     '<form>\n    <input type="text" name="name" placeholder="Enter \"Name\"" />\n</form>', 
     'escaped_template');