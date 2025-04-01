-- Complex Campaign with Diverse Email Addresses

-- Campaigns
INSERT INTO `campaigns` 
    (`name`, `description`, `is_published`, `created_by`, `created_by_user`, `date_added`) 
VALUES 
    ('Email Campaign Diversity', 
     'Campaign testing various email address formats', 
     1, 
     1, 
     'admin', 
     NOW());

SET @campaign_id = LAST_INSERT_ID();

-- Lead Lists with Email-based Segments
INSERT INTO `lead_lists` 
    (`name`, `alias`, `is_published`, `created_by`, `created_by_user`, `date_added`) 
VALUES 
    ('Tech Professionals', 'tech_emails', 1, 1, 'admin', NOW()),
    ('International Contacts', 'global_emails', 1, 1, 'admin', NOW());

SET @tech_segment_id = LAST_INSERT_ID() - 1;
SET @international_segment_id = LAST_INSERT_ID();

-- Email Templates with Diverse Addresses
INSERT INTO `email_templates` 
    (`name`, `template_type`, `content`, `subject`, `from_name`, `from_address`) 
VALUES 
    ('Tech Professionals Invite', 'custom', 
     '<p>Invitation for tech innovators</p>', 
     'Join Our Tech Community', 
     'Tech Innovations', 
     'noreply+tech@company.innovations.com'),
    ('Global Newsletter', 'custom', 
     '<p>International updates</p>', 
     'Worldwide Connections', 
     'Global Insights', 
     'newsletter@international-network.org');

-- Segment Filters with Email Criteria
INSERT INTO `lead_list_filters` 
    (`list_id`, `object`, `type`, `operator`, `filter`, `value`) 
VALUES 
    (@tech_segment_id, 'lead', 'email_domain', '=', 'domain', 'tech.com'),
    (@international_segment_id, 'lead', 'email_tld', '=', 'tld', 'org');

-- Campaign Events
INSERT INTO `campaign_events` 
    (`campaign_id`, `name`, `type`, `event_type`, `trigger_mode`) 
VALUES 
    (@campaign_id, 'Tech Professional Outreach', 'email', 'action', 'immediate'),
    (@campaign_id, 'International Engagement', 'email', 'action', 'immediate');

-- Sample Email Addresses for Testing
INSERT INTO `leads` 
    (`email`, `firstname`, `lastname`, `company`) 
VALUES 
    ('john.doe@tech.com', 'John', 'Doe', 'Tech Innovations'),
    ('maria.silva@global.org', 'Maria', 'Silva', 'International Network'),
    ('alex.kim+work@startup.tech.com', 'Alex', 'Kim', 'Startup Collective'),
    ('emma.watson.personal@gmail.com', 'Emma', 'Watson', 'Freelance'),
    ('support+billing@company-name.co.uk', 'Support', 'Team', 'Corporate Services'),
    ('user123@xn--mailtest-9qa.com', 'Internationalized', 'Domain', 'IDN Test'),
    ('very.common@example.com', 'Common', 'User', 'Example Corp'),
    ('disposable.style.email@example.com', 'Disposable', 'Email', 'Temp Services'),
    ('other.email-with-hyphen@example.com', 'Hyphenated', 'Email', 'Hyphen Corp'),
    ('fully-qualified-domain@example.com', 'Fully', 'Qualified', 'Domain Inc');