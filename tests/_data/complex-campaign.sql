-- Insert Campaign
INSERT INTO `campaigns` 
    (`name`, `description`, `is_published`, `created_by`, `created_by_user`, `date_added`) 
VALUES 
    ('Advanced Marketing Automation Campaign', 
     'Complex campaign with multiple dependencies and triggers', 
     1, 
     1, 
     'admin', 
     NOW());

SET @campaign_id = LAST_INSERT_ID();

-- Insert Segments
INSERT INTO `lead_lists` 
    (`name`, `alias`, `is_published`, `created_by`, `created_by_user`, `date_added`) 
VALUES 
    ('New Leads', 'new_leads', 1, 1, 'admin', NOW()),
    ('Engaged Contacts', 'engaged_contacts', 1, 1, 'admin', NOW());

-- Explicitly set variables
SET @new_leads_segment_id = 1;
SET @engaged_contacts_segment_id = 2;

-- Insert Segment Filters
INSERT INTO `lead_list_filters` 
    (`list_id`, `object`, `type`, `operator`, `filter`, `value`) 
VALUES 
    (@new_leads_segment_id, 'lead', 'lead_source', '=', 'source', 'website_signup'),
    (@engaged_contacts_segment_id, 'lead', 'email_opened', '>', 'count', 3);

-- Insert Campaign Events
INSERT INTO `campaign_events` 
    (`campaign_id`, `name`, `type`, `event_type`, `trigger_mode`, `trigger_date_interval`, `trigger_interval_unit`) 
VALUES 
    (@campaign_id, 'Website Signup Trigger', 'lead_source', 'entry', 'entry', NULL, NULL),
    (@campaign_id, 'Welcome Email', 'email', 'action', 'immediate', 0, 'i'),
    (@campaign_id, 'Engagement Check', 'condition', 'condition', NULL, NULL, NULL),
    (@campaign_id, 'Engagement Reward Email', 'email', 'action', 'immediate', 0, 'i'),
    (@campaign_id, 'Re-engagement Email', 'email', 'action', 'immediate', 0, 'i'),
    (@campaign_id, 'Product Interest Survey', 'landing_page', 'action', 'immediate', 0, 'i');

SET @signup_trigger_event_id = LAST_INSERT_ID() - 5;
SET @welcome_email_event_id = LAST_INSERT_ID() - 4;
SET @engagement_check_event_id = LAST_INSERT_ID() - 3;
SET @reward_email_event_id = LAST_INSERT_ID() - 2;
SET @reeng_email_event_id = LAST_INSERT_ID() - 1;
SET @survey_event_id = LAST_INSERT_ID();

-- Insert Event Conditions
INSERT INTO `campaign_event_conditions` 
    (`event_id`, `type`, `operator`, `value`) 
VALUES 
    (@engagement_check_event_id, 'email_opened', '>', 2);

-- Insert Event Dependencies
INSERT INTO `campaign_event_dependencies` 
    (`source_event_id`, `target_event_id`) 
VALUES 
    (@signup_trigger_event_id, @welcome_email_event_id),
    (@welcome_email_event_id, @engagement_check_event_id),
    (@engagement_check_event_id, @reward_email_event_id),
    (@engagement_check_event_id, @reeng_email_event_id);

-- Insert Campaign-Segment Relationship
INSERT INTO `campaign_lead_list_xref` 
    (`campaign_id`, `leadlist_id`) 
VALUES 
    (@campaign_id, @new_leads_segment_id),
    (@campaign_id, @engaged_contacts_segment_id);

-- Insert Email Templates (if not already existing)
INSERT INTO `email_templates` 
    (`name`, `template_type`, `content`, `subject`) 
VALUES 
    ('welcome_series_1', 'custom', '<p>Welcome to our platform!</p>', 'Welcome Aboard'),
    ('engagement_reward', 'custom', '<p>Thank you for your engagement!</p>', 'You''re Awesome'),
    ('reengagement', 'custom', '<p>We miss you!</p>', 'Come Back');

-- Insert Landing Page Template
INSERT INTO `pages` 
    (`name`, `alias`, `content`, `template`) 
VALUES 
    ('Interest Survey', 'interest_survey', '<form>...</form>', 'survey_template');

-- Optional: CRM Integration Configuration
INSERT INTO `integrations` 
    (`name`, `is_published`, `type`, `supported_features`) 
VALUES 
    ('Salesforce Sync', 1, 'crm', 'a:3:{i:0;s:5:"email";i:1;s:10:"lead_score";i:2;s:13:"last_activity";}');