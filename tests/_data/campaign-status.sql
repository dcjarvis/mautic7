-- Campaigns with Diverse Statuses and Configurations

-- Campaign 1: Active Global Marketing Campaign
INSERT INTO `campaigns` 
    (`name`, `description`, `is_published`, `status`, `created_by`, `created_by_user`, `date_added`) 
VALUES 
    ('Global Customer Acquisition', 'Active worldwide marketing campaign', 1, 'active', 1, 'admin', NOW());

SET @global_acquisition_campaign_id = LAST_INSERT_ID();

-- Campaign 2: Paused Seasonal Campaign
INSERT INTO `campaigns` 
    (`name`, `description`, `is_published`, `status`, `created_by`, `created_by_user`, `date_added`) 
VALUES 
    ('Holiday Season Promo', 'Seasonal campaign currently on hold', 1, 'paused', 1, 'admin', NOW());

SET @holiday_campaign_id = LAST_INSERT_ID();

-- Campaign 3: Draft Experimental Campaign
INSERT INTO `campaigns` 
    (`name`, `description`, `is_published`, `status`, `created_by`, `created_by_user`, `date_added`) 
VALUES 
    ('AI-Driven Engagement Experiment', 'Experimental campaign in development', 0, 'draft', 1, 'admin', NOW());

SET @ai_experiment_campaign_id = LAST_INSERT_ID();

-- Campaign 4: Completed Retrospective Campaign
INSERT INTO `campaigns` 
    (`name`, `description`, `is_published`, `status`, `created_by`, `created_by_user`, `date_added`, `date_ended`) 
VALUES 
    ('Q1 2025 Marketing Review', 'Completed marketing campaign analysis', 1, 'completed', 1, 'admin', NOW(), NOW());

SET @q1_review_campaign_id = LAST_INSERT_ID();

-- Campaign 5: Archived Legacy Campaign
INSERT INTO `campaigns` 
    (`name`, `description`, `is_published`, `status`, `created_by`, `created_by_user`, `date_added`, `date_ended`) 
VALUES 
    ('Legacy Customer Outreach', 'Historical campaign preserved for reference', 0, 'archived', 1, 'admin', DATE_SUB(NOW(), INTERVAL 6 MONTH), NOW());

SET @legacy_campaign_id = LAST_INSERT_ID();

-- Campaign Events for Each Campaign
-- Global Acquisition Campaign Events
INSERT INTO `campaign_events` 
    (`campaign_id`, `name`, `type`, `event_type`, `trigger_mode`) 
VALUES 
    (@global_acquisition_campaign_id, 'Welcome Email', 'email', 'action', 'immediate'),
    (@global_acquisition_campaign_id, 'Follow-up Sequence', 'email', 'action', 'interval');

-- Holiday Promo Campaign Events
INSERT INTO `campaign_events` 
    (`campaign_id`, `name`, `type`, `event_type`, `trigger_mode`) 
VALUES 
    (@holiday_campaign_id, 'Seasonal Offer', 'email', 'action', 'scheduled'),
    (@holiday_campaign_id, 'Last Chance Reminder', 'email', 'action', 'interval');

-- AI Experiment Campaign Events
INSERT INTO `campaign_events` 
    (`campaign_id`, `name`, `type`, `event_type`, `trigger_mode`) 
VALUES 
    (@ai_experiment_campaign_id, 'Prototype Engagement', 'email', 'action', 'immediate'),
    (@ai_experiment_campaign_id, 'Feedback Collection', 'form', 'action', 'interval');

-- Q1 Review Campaign Events
INSERT INTO `campaign_events` 
    (`campaign_id`, `name`, `type`, `event_type`, `trigger_mode`) 
VALUES 
    (@q1_review_campaign_id, 'Performance Summary', 'email', 'action', 'immediate'),
    (@q1_review_campaign_id, 'Insights Report', 'asset', 'action', 'interval');

-- Legacy Campaign Events
INSERT INTO `campaign_events` 
    (`campaign_id`, `name`, `type`, `event_type`, `trigger_mode`) 
VALUES 
    (@legacy_campaign_id, 'Historical Engagement', 'email', 'action', 'immediate'),
    (@legacy_campaign_id, 'Archival Notification', 'email', 'action', 'interval');

-- Campaign Segments
INSERT INTO `lead_lists` 
    (`name`, `alias`, `is_published`, `created_by`, `created_by_user`, `date_added`) 
VALUES 
    ('Global Acquisition Segment', 'global_acquisition', 1, 1, 'admin', NOW()),
    ('Holiday Promo Segment', 'holiday_promo', 1, 1, 'admin', NOW()),
    ('AI Experiment Segment', 'ai_experiment', 0, 1, 'admin', NOW()),
    ('Q1 Review Segment', 'q1_review', 1, 1, 'admin', NOW()),
    ('Legacy Customer Segment', 'legacy_customers', 0, 1, 'admin', NOW());