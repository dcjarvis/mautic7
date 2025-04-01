-- Advanced Marketing Campaign with Comprehensive Features

-- Lead Scoring Rules
INSERT INTO `lead_scoring_rules` 
    (`name`, `description`, `is_published`) 
VALUES 
    ('Engagement Scoring Model', 'Comprehensive lead qualification system', 1);

SET @scoring_rule_id = LAST_INSERT_ID();

-- Scoring Rule Details
INSERT INTO `lead_scoring_rule_details` 
    (`rule_id`, `action`, `points`, `type`) 
VALUES 
    (@scoring_rule_id, 'email_open', 5, 'positive'),
    (@scoring_rule_id, 'email_click', 10, 'positive'),
    (@scoring_rule_id, 'page_visit', 3, 'positive'),
    (@scoring_rule_id, 'form_submit', 15, 'positive'),
    (@scoring_rule_id, 'unsubscribe', -20, 'negative');

-- Campaign Creation
INSERT INTO `campaigns` 
    (`name`, `description`, `is_published`, `scoring_rule_id`, `created_by`, `created_by_user`, `date_added`) 
VALUES 
    ('Advanced Engagement Optimization', 
     'Comprehensive campaign with dynamic segmentation and A/B testing', 
     1, 
     @scoring_rule_id, 
     1, 'admin', NOW());

SET @optimization_campaign_id = LAST_INSERT_ID();

-- Segmentation Layers
INSERT INTO `lead_lists` 
    (`name`, `alias`, `is_published`, `created_by`, `created_by_user`, `date_added`) 
VALUES 
    ('High-Potential Leads', 'high_potential', 1, 1, 'admin', NOW()),
    ('Nurture Track', 'nurture_track', 1, 1, 'admin', NOW()),
    ('Conversion Segment', 'conversion_ready', 1, 1, 'admin', NOW());

SET @high_potential_segment_id = LAST_INSERT_ID() - 2;
SET @nurture_segment_id = LAST_INSERT_ID() - 1;
SET @conversion_segment_id = LAST_INSERT_ID();

-- Segment Filters with Complex Criteria
INSERT INTO `lead_list_filters` 
    (`list_id`, `object`, `type`, `operator`, `filter`, `value`) 
VALUES 
    (@high_potential_segment_id, 'lead', 'lead_score', '>=', 'score', 50),
    (@nurture_segment_id, 'lead', 'lead_score', 'BETWEEN', 'score_range', '20,49'),
    (@conversion_segment_id, 'lead', 'lead_score', '>=', 'score', 75);

-- A/B Test Configurations
INSERT INTO `ab_tests` 
    (`campaign_id`, `name`, `description`, `test_type`, `traffic_allocation`) 
VALUES 
    (@optimization_campaign_id, 'Email Subject Line Test', 
     'Comparing subject line effectiveness', 'email_subject', 
     '{"variant_a": 50, "variant_b": 50}');

SET @ab_test_id = LAST_INSERT_ID();

-- A/B Test Variants
INSERT INTO `ab_test_variants` 
    (`ab_test_id`, `variant_name`, `content`, `conversion_metric`) 
VALUES 
    (@ab_test_id, 'Variant A', 
     '{"subject_line": "Unlock Your Potential Today"}', 
     'email_open_rate'),
    (@ab_test_id, 'Variant B', 
     '{"subject_line": "Your Exclusive Opportunity Awaits"}', 
     'email_click_rate');

-- Campaign Events with Dynamic Routing
INSERT INTO `campaign_events` 
    (`campaign_id`, `name`, `type`, `event_type`, `trigger_mode`, `segment_condition`) 
VALUES 
    (@optimization_campaign_id, 'High-Potential Welcome', 'email', 'action', 'immediate', 
     JSON_OBJECT('segment_id', @high_potential_segment_id)),
    
    (@optimization_campaign_id, 'Nurture Track Engagement', 'email', 'action', 'interval', 
     JSON_OBJECT('segment_id', @nurture_segment_id)),
    
    (@optimization_campaign_id, 'Conversion Offer', 'email', 'action', 'immediate', 
     JSON_OBJECT('segment_id', @conversion_segment_id));

-- Dynamic Content Based on Segmentation
INSERT INTO `dynamic_content` 
    (`name`, `description`, `is_published`, `content_html`, `segment_condition`, `created_by`, `date_added`) 
VALUES 
    ('High-Potential Content', 'Personalized content for top leads', 1, 
     '<div>Exclusive insights for our top performers!</div>', 
     JSON_OBJECT('segment_id', @high_potential_segment_id), 1, NOW()),
    
    ('Nurture Track Content', 'Guided learning content', 1, 
     '<div>Your path to success starts here</div>', 
     JSON_OBJECT('segment_id', @nurture_segment_id), 1, NOW());

-- Lead List Management Rules
INSERT INTO `lead_list_management_rules` 
    (`campaign_id`, `name`, `description`, `trigger_condition`, `action`) 
VALUES 
    (@optimization_campaign_id, 'Score-Based Segment Migration', 
     'Automatically move leads between segments based on score', 
     JSON_OBJECT('lead_score', '>=50'), 
     'move_to_segment');