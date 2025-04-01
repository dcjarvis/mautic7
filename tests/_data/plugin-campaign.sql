-- Plugin-Dependent Campaign

-- Plugin Configurations
INSERT INTO `plugin_integrations` 
    (`name`, `is_published`, `api_keys`, `feature_settings`) 
VALUES 
    ('Salesforce CRM Integration', 1, 
     '{"client_id": "xxx", "client_secret": "yyy"}', 
     '{"sync_fields": true, "lead_sync": "bidirectional"}'),
    
    ('Stripe Payment Integration', 1, 
     '{"api_key": "sk_test_xyz"}', 
     '{"track_purchases": true, "revenue_tracking": true}'),
    
    ('Zendesk Support Integration', 1, 
     '{"subdomain": "company", "api_token": "abc123"}', 
     '{"ticket_sync": true, "customer_support_tracking": true}');

SET @salesforce_plugin_id = LAST_INSERT_ID() - 2;
SET @stripe_plugin_id = LAST_INSERT_ID() - 1;
SET @zendesk_plugin_id = LAST_INSERT_ID();

-- Campaign with Plugin Dependencies
INSERT INTO `campaigns` 
    (`name`, `description`, `is_published`, `plugin_dependency`, `created_by`, `created_by_user`, `date_added`) 
VALUES 
    ('Enterprise Customer Lifecycle', 
     'Advanced campaign tracking customer journey across CRM, payments, and support', 
     1, 
     JSON_ARRAY(@salesforce_plugin_id, @stripe_plugin_id, @zendesk_plugin_id), 
     1, 'admin', NOW());

SET @enterprise_campaign_id = LAST_INSERT_ID();

-- Plugin-Specific Segments
INSERT INTO `lead_lists` 
    (`name`, `alias`, `is_published`, `created_by`, `created_by_user`, `date_added`) 
VALUES 
    ('High-Value Salesforce Leads', 'salesforce_high_value', 1, 1, 'admin', NOW()),
    ('Stripe Paying Customers', 'stripe_customers', 1, 1, 'admin', NOW()),
    ('Zendesk Support Escalation', 'zendesk_escalated', 1, 1, 'admin', NOW());

SET @salesforce_segment_id = LAST_INSERT_ID() - 2;
SET @stripe_segment_id = LAST_INSERT_ID() - 1;
SET @zendesk_segment_id = LAST_INSERT_ID();

-- Segment Filters Based on Plugin Data
INSERT INTO `lead_list_filters` 
    (`list_id`, `object`, `type`, `operator`, `filter`, `value`, `plugin_source`) 
VALUES 
    (@salesforce_segment_id, 'lead', 'crm_opportunity_value', '>=', 'amount', 10000, @salesforce_plugin_id),
    (@stripe_segment_id, 'lead', 'total_purchases', '>', 'count', 3, @stripe_plugin_id),
    (@zendesk_segment_id, 'lead', 'support_tickets', '>=', 'severity', 'high', @zendesk_plugin_id);

-- Campaign Events with Plugin Triggers
INSERT INTO `campaign_events` 
    (`campaign_id`, `name`, `type`, `event_type`, `trigger_mode`, `plugin_condition`) 
VALUES 
    (@enterprise_campaign_id, 'Salesforce Opportunity Upgrade', 'email', 'action', 'immediate', 
     JSON_OBJECT('plugin_id', @salesforce_plugin_id, 'condition', 'opportunity_stage_changed')),
    
    (@enterprise_campaign_id, 'Stripe Payment Milestone', 'email', 'action', 'interval', 
     JSON_OBJECT('plugin_id', @stripe_plugin_id, 'condition', 'cumulative_revenue_threshold')),
    
    (@enterprise_campaign_id, 'Zendesk Support Escalation', 'email', 'action', 'immediate', 
     JSON_OBJECT('plugin_id', @zendesk_plugin_id, 'condition', 'multiple_high_severity_tickets'));

-- Plugin Event Dependencies
INSERT INTO `campaign_event_dependencies` 
    (`campaign_event_id`, `depends_on_event_id`, `plugin_dependency`) 
VALUES 
    (LAST_INSERT_ID() - 2, LAST_INSERT_ID() - 1, @salesforce_plugin_id),
    (LAST_INSERT_ID(), LAST_INSERT_ID() - 2, @zendesk_plugin_id);

-- Dynamic Content Based on Plugin Data
INSERT INTO `dynamic_content` 
    (`name`, `description`, `is_published`, `content_html`, `plugin_source`, `created_by`, `date_added`) 
VALUES 
    ('Salesforce Opportunity Content', 'Personalized content for high-value leads', 1, 
     '<div>Special offer for our top opportunities!</div>', @salesforce_plugin_id, 1, NOW()),
    
    ('Stripe Customer Reward', 'Personalized content for loyal customers', 1, 
     '<div>Exclusive rewards for our valued customers!</div>', @stripe_plugin_id, 1, NOW());