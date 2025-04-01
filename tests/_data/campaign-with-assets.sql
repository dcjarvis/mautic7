-- Mautic Campaign with Rich Email Content

-- Assets
INSERT INTO `assets` 
    (`title`, `path`, `remote_path`, `storage_location`, `file_size`, `mime`, `created_by`, `date_added`) 
VALUES 
    ('Marketing Whitepaper', '/assets/whitepaper.pdf', NULL, 'local', 2048576, 'application/pdf', 1, NOW()),
    ('Product Brochure', '/assets/brochure.jpg', NULL, 'local', 1024000, 'image/jpeg', 1, NOW()),
    ('Webinar Recording', '/assets/webinar.mp4', NULL, 'local', 51200000, 'video/mp4', 1, NOW());

SET @whitepaper_asset_id = LAST_INSERT_ID() - 2;
SET @brochure_asset_id = LAST_INSERT_ID() - 1;
SET @webinar_asset_id = LAST_INSERT_ID();

-- Dynamic Content
INSERT INTO `dynamic_content` 
    (`name`, `description`, `is_published`, `content_html`, `content_type`, `created_by`, `date_added`) 
VALUES 
    ('Tech Industry Segment', 'Personalized content for tech professionals', 1, 
     '<div class="tech-content">{{tech_content}}</div>', 'html', 1, NOW()),
    ('Startup Segment', 'Content tailored for startup founders', 1, 
     '<div class="startup-content">{{startup_content}}</div>', 'html', 1, NOW());

SET @tech_dynamic_content_id = LAST_INSERT_ID() - 1;
SET @startup_dynamic_content_id = LAST_INSERT_ID();

-- Email Templates with Advanced Tokens
INSERT INTO `email_templates` 
    (`name`, `template_type`, `content`, `subject`, `from_name`, `from_address`, 
     `dynamic_content_id`, `tokens`) 
VALUES 
    ('Tech Professional Nurture', 'custom', 
     '<!DOCTYPE html>
     <html>
     <body>
         <h1>Hi {leadfield=firstname}!</h1>
         
         {{tech_content}}
         
         <p>Download our latest resources:</p>
         <ul>
             <li><a href="{asset='.$whitepaper_asset_id.'}">Tech Whitepaper</a></li>
             <li><a href="{asset='.$webinar_asset_id.'}">Industry Webinar</a></li>
         </ul>
         
         <p>Personalized Offer: {token=custom_discount}</p>
     </body>
     </html>', 
     'Exclusive Insights for Tech Innovators', 
     'Tech Innovations Team', 
     'noreply@techinnovations.com', 
     @tech_dynamic_content_id,
     '{"custom_discount": "15% off first consultation"}'),
    
    ('Startup Founder Engagement', 'custom', 
     '<!DOCTYPE html>
     <html>
     <body>
         <h1>Hello {leadfield=firstname}!</h1>
         
         {{startup_content}}
         
         <p>Exclusive Resources:</p>
         <ul>
             <li><a href="{asset='.$brochure_asset_id.'}">Startup Success Guide</a></li>
         </ul>
         
         <p>Special Offer: {token=founder_bonus}</p>
     </body>
     </html>', 
     'Accelerate Your Startup Journey', 
     'Startup Accelerator', 
     'founders@startupaccel.com', 
     @startup_dynamic_content_id,
     '{"founder_bonus": "Free 1-hour mentorship"}');

-- Dynamic Content Variants
INSERT INTO `dynamic_content_variants` 
    (`dynamic_content_id`, `variant_content`, `weight`, `is_default`) 
VALUES 
    (@tech_dynamic_content_id, 
     '<p>Latest trends in AI and machine learning for tech professionals.</p>', 
     50, 0),
    (@tech_dynamic_content_id, 
     '<p>Cutting-edge cybersecurity strategies for tech leaders.</p>', 
     50, 1),
    (@startup_dynamic_content_id, 
     '<p>Fundraising strategies for early-stage startups.</p>', 
     50, 0),
    (@startup_dynamic_content_id, 
     '<p>Building a scalable business model.</p>', 
     50, 1);

-- Email Asset Associations
INSERT INTO `email_assets` 
    (`email_id`, `asset_id`, `type`) 
VALUES 
    (LAST_INSERT_ID() - 1, @whitepaper_asset_id, 'attachment'),
    (LAST_INSERT_ID() - 1, @webinar_asset_id, 'download'),
    (LAST_INSERT_ID(), @brochure_asset_id, 'attachment');

-- Tokens for Personalization
INSERT INTO `lead_fields` 
    (`label`, `alias`, `type`, `is_published`, `is_required`, `is_unique`) 
VALUES 
    ('Custom Discount', 'custom_discount', 'text', 1, 0, 0),
    ('Founder Bonus', 'founder_bonus', 'text', 1, 0, 0);