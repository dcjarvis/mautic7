-- Multilingual Campaign with Language-Specific Settings

-- Language Configurations
INSERT INTO `lead_fields` 
    (`label`, `alias`, `type`, `is_published`, `is_required`, `is_unique`) 
VALUES 
    ('Preferred Language', 'preferred_language', 'select', 1, 0, 0),
    ('Timezone', 'timezone', 'select', 1, 0, 0);

-- Language Choices
INSERT INTO `lead_field_choices` 
    (`field_id`, `label`, `value`) 
VALUES 
    (LAST_INSERT_ID() - 1, 'English', 'en'),
    (LAST_INSERT_ID() - 1, 'Spanish', 'es'),
    (LAST_INSERT_ID() - 1, 'French', 'fr'),
    (LAST_INSERT_ID() - 1, 'German', 'de'),
    (LAST_INSERT_ID() - 1, 'Chinese', 'zh');

-- Multilingual Segments
INSERT INTO `lead_lists` 
    (`name`, `alias`, `is_published`, `created_by`, `created_by_user`, `date_added`) 
VALUES 
    ('English Speakers', 'english_speakers', 1, 1, 'admin', NOW()),
    ('Spanish Speakers', 'spanish_speakers', 1, 1, 'admin', NOW()),
    ('Multilingual Contacts', 'multilingual_contacts', 1, 1, 'admin', NOW());

SET @english_segment_id = LAST_INSERT_ID() - 2;
SET @spanish_segment_id = LAST_INSERT_ID() - 1;
SET @multilingual_segment_id = LAST_INSERT_ID();

-- Segment Language Filters
INSERT INTO `lead_list_filters` 
    (`list_id`, `object`, `type`, `operator`, `filter`, `value`) 
VALUES 
    (@english_segment_id, 'lead', 'preferred_language', '=', 'language', 'en'),
    (@spanish_segment_id, 'lead', 'preferred_language', '=', 'language', 'es'),
    (@multil_ingual_segment_id, 'lead', 'preferred_language', 'IN', 'languages', 'en,es,fr');

-- Multilingual Campaign
INSERT INTO `campaigns` 
    (`name`, `description`, `is_published`, `created_by`, `created_by_user`, `date_added`) 
VALUES 
    ('Global Customer Engagement', 'Personalized campaign across languages', 1, 1, 'admin', NOW());

SET @global_campaign_id = LAST_INSERT_ID();

-- Multilingual Email Templates
INSERT INTO `email_templates` 
    (`name`, `template_type`, `content`, `subject`, `language`) 
VALUES 
    ('Welcome Email - English', 'custom', 
     '<!DOCTYPE html>
     <html lang="en">
     <body>
         <h1>Welcome to Our Global Community!</h1>
         <p>We\'re excited to have you join us.</p>
     </body>
     </html>', 
     'Welcome Aboard!', 'en'),
    
    ('Bienvenida - Spanish', 'custom', 
     '<!DOCTYPE html>
     <html lang="es">
     <body>
         <h1>¡Bienvenido a Nuestra Comunidad Global!</h1>
         <p>Estamos emocionados de que te unas a nosotros.</p>
     </body>
     </html>', 
     '¡Bienvenido a Bordo!', 'es'),
    
    ('Bienvenue - French', 'custom', 
     '<!DOCTYPE html>
     <html lang="fr">
     <body>
         <h1>Bienvenue dans Notre Communauté Mondiale !</h1>
         <p>Nous sommes ravis que vous nous rejoigniez.</p>
     </body>
     </html>', 
     'Bienvenue à Bord !', 'fr');

-- Campaign Events with Language Routing
INSERT INTO `campaign_events` 
    (`campaign_id`, `name`, `type`, `event_type`, `trigger_mode`, `language_condition`) 
VALUES 
    (@global_campaign_id, 'English Welcome Flow', 'email', 'action', 'immediate', 'en'),
    (@global_campaign_id, 'Spanish Welcome Flow', 'email', 'action', 'immediate', 'es'),
    (@global_campaign_id, 'Multilingual Fallback', 'email', 'action', 'interval', 'default');

-- Localized Dynamic Content
INSERT INTO `dynamic_content` 
    (`name`, `description`, `is_published`, `content_html`, `language`, `created_by`, `date_added`) 
VALUES 
    ('English Localized Content', 'Personalized content for English speakers', 1, 
     '<div class="localized-content">Welcome to our English community!</div>', 'en', 1, NOW()),
    ('Spanish Localized Content', 'Contenido personalizado para hispanohablantes', 1, 
     '<div class="localized-content">¡Bienvenido a nuestra comunidad de habla hispana!</div>', 'es', 1, NOW());

-- Timezone-Based Scheduling
INSERT INTO `campaign_events` 
    (`campaign_id`, `name`, `type`, `event_type`, `trigger_mode`, `timezone_condition`) 
VALUES 
    (@global_campaign_id, 'Americas Timing', 'email', 'action', 'scheduled', 'America/New_York'),
    (@global_campaign_id, 'Europe Timing', 'email', 'action', 'scheduled', 'Europe/London'),
    (@global_campaign_id, 'Asia Timing', 'email', 'action', 'scheduled', 'Asia/Tokyo');

    -- Existing code remains the same...

-- Add German and Chinese Segments
INSERT INTO `lead_lists` 
    (`name`, `alias`, `is_published`, `created_by`, `created_by_user`, `date_added`) 
VALUES 
    ('German Speakers', 'german_speakers', 1, 1, 'admin', NOW()),
    ('Chinese Speakers', 'chinese_speakers', 1, 1, 'admin', NOW());

SET @german_segment_id = LAST_INSERT_ID() - 1;
SET @chinese_segment_id = LAST_INSERT_ID();

-- Segment Language Filters (Update existing filters)
INSERT INTO `lead_list_filters` 
    (`list_id`, `object`, `type`, `operator`, `filter`, `value`) 
VALUES 
    (@german_segment_id, 'lead', 'preferred_language', '=', 'language', 'de'),
    (@chinese_segment_id, 'lead', 'preferred_language', '=', 'language', 'zh');

-- Multilingual Email Templates (Add German and Chinese)
INSERT INTO `email_templates` 
    (`name`, `template_type`, `content`, `subject`, `language`) 
VALUES 
    ('Willkommen - German', 'custom', 
     '<!DOCTYPE html>
     <html lang="de">
     <body>
         <h1>Willkommen in unserer globalen Gemeinschaft!</h1>
         <p>Wir freuen uns, dass Sie dabei sind.</p>
     </body>
     </html>', 
     'Willkommen an Bord!', 'de'),
    
    ('欢迎 - Chinese', 'custom', 
     '<!DOCTYPE html>
     <html lang="zh">
     <body>
         <h1>欢迎加入我们的全球社区！</h1>
         <p>我们很高兴您加入我们。</p>
     </body>
     </html>', 
     '欢迎aboard！', 'zh');

-- Localized Dynamic Content
INSERT INTO `dynamic_content` 
    (`name`, `description`, `is_published`, `content_html`, `language`, `created_by`, `date_added`) 
VALUES 
    ('German Localized Content', 'Personalisierter Inhalt für deutschsprachige Nutzer', 1, 
     '<div class="localized-content">Willkommen in unserer deutschsprachigen Community!</div>', 'de', 1, NOW()),
    ('Chinese Localized Content', '为中文用户定制的个性化内容', 1, 
     '<div class="localized-content">欢迎加入我们的中文社区！</div>', 'zh', 1, NOW());

-- Timezone-Based Scheduling (Add German and Chinese Timezones)
INSERT INTO `campaign_events` 
    (`campaign_id`, `name`, `type`, `event_type`, `trigger_mode`, `timezone_condition`) 
VALUES 
    (@global_campaign_id, 'Germany Timing', 'email', 'action', 'scheduled', 'Europe/Berlin'),
    (@global_campaign_id, 'China Timing', 'email', 'action', 'scheduled', 'Asia/Shanghai');