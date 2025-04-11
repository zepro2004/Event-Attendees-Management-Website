-- Insert test users (password for all users is 'Password123')
INSERT INTO users (username, password, email, first_name, last_name, last_login) VALUES
('admin', '$2y$10$LOWEBWZ.tMqUB2MYtDK7teM./NELixvCQE8mZhJ6CQWN99cILmpHC', 'admin@example.com', 'Admin', 'User', NOW()),
('sarah_smith', '$2y$10$LOWEBWZ.tMqUB2MYtDK7teM./NELixvCQE8mZhJ6CQWN99cILmpHC', 'sarah@example.com', 'Sarah', 'Smith', NOW()),
('mike_jones', '$2y$10$LOWEBWZ.tMqUB2MYtDK7teM./NELixvCQE8mZhJ6CQWN99cILmpHC', 'mike@example.com', 'Mike', 'Jones', NOW()),
('elena_rodriguez', '$2y$10$LOWEBWZ.tMqUB2MYtDK7teM./NELixvCQE8mZhJ6CQWN99cILmpHC', 'elena@example.com', 'Elena', 'Rodriguez', NOW()),
('david_chen', '$2y$10$LOWEBWZ.tMqUB2MYtDK7teM./NELixvCQE8mZhJ6CQWN99cILmpHC', 'david@example.com', 'David', 'Chen', NOW()),
('priya_patel', '$2y$10$LOWEBWZ.tMqUB2MYtDK7teM./NELixvCQE8mZhJ6CQWN99cILmpHC', 'priya@example.com', 'Priya', 'Patel', NOW()),
('james_wilson', '$2y$10$LOWEBWZ.tMqUB2MYtDK7teM./NELixvCQE8mZhJ6CQWN99cILmpHC', 'james@example.com', 'James', 'Wilson', NOW());

-- Insert event types
INSERT INTO event_types (name) VALUES
('Conference'),
('Workshop'),
('Social Gathering'),
('Fundraiser'),
('Concert'),
('Sports Event'),
('Tech Meetup'),
('Food Festival'),
('Art Exhibition'),
('Academic Lecture');

-- Insert events (with dates spanning the next 6 months)
INSERT INTO events (title, description, date, end_date, address, city, state, postal_code, country, event_type_id, user_id, max_attendees, image_url) VALUES
('Tech Innovation Summit', 'Join us for the premier tech innovation event of the year! Featuring keynote speakers from top tech companies and breakout sessions on AI, blockchain, and more.', '2025-04-25 09:00:00', '2025-04-27 17:00:00', '123 Conference Center Dr', 'Ottawa', 'Ontario', 'K1P 1J1', 'Canada', 1, 1, 500, '/Assignment2/uploads/tech_summit.jpg'),

('Coding Workshop: Web Development Basics', 'Learn the fundamentals of HTML, CSS, and JavaScript in this hands-on workshop suitable for beginners. Bring your own laptop!', '2025-05-10 10:00:00', '2025-05-10 16:00:00', '789 Learning Ave', 'Ottawa', 'Ontario', 'K1G 3N2', 'Canada', 2, 2, 30, '/Assignment2/uploads/coding_workshop.jpg'),

('Ottawa Networking Night', 'Connect with professionals from various industries in a casual setting. Great opportunity to expand your network and discover new opportunities.', '2025-04-18 18:00:00', '2025-04-18 21:00:00', '456 Social Blvd', 'Ottawa', 'Ontario', 'K1Z 7T3', 'Canada', 3, 3, 100, '/Assignment2/uploads/networking_event.jpg'),

('Charity Run for Children\'s Hospital', 'Annual 5K run supporting the Children\'s Hospital of Ottawa. Registration includes t-shirt and post-race refreshments.', '2025-06-05 08:00:00', '2025-06-05 12:00:00', 'Rideau Canal Pathway', 'Ottawa', 'Ontario', 'K1S 5B6', 'Canada', 4, 4, 200, '/Assignment2/uploads/charity_run.jpg'),

('Summer Jazz Festival', 'Enjoy performances by renowned jazz musicians and emerging talents. Food and beverages available for purchase.', '2025-07-15 17:00:00', '2025-07-17 23:00:00', 'City Park Amphitheater', 'Toronto', 'Ontario', 'M4W 2G8', 'Canada', 5, 5, 1000, '/Assignment2/uploads/jazz_festival.jpg'),

('Hockey Tournament', 'Annual amateur hockey tournament with teams from across Ontario. Spectators welcome!', '2025-05-20 09:00:00', '2025-05-22 18:00:00', 'Community Ice Rink', 'Montreal', 'Quebec', 'H3A 2T5', 'Canada', 6, 6, 300, '/Assignment2/uploads/hockey_tournament.jpg'),

('AI and Machine Learning Meetup', 'Monthly gathering of AI enthusiasts and professionals. This month\'s topic: Natural Language Processing advancements.', '2025-04-30 18:30:00', '2025-04-30 20:30:00', 'Tech Hub Coworking', 'Vancouver', 'British Columbia', 'V6B 5K2', 'Canada', 7, 7, 50, '/Assignment2/uploads/ai_meetup.jpg'),

('International Food Festival', 'Experience culinary delights from over 20 countries! Cooking demonstrations, tastings, and cultural performances.', '2025-06-25 11:00:00', '2025-06-27 20:00:00', 'Exhibition Grounds', 'Calgary', 'Alberta', 'T2G 4T3', 'Canada', 8, 1, 5000, '/Assignment2/uploads/food_festival.jpg'),

('Modern Art Showcase', 'Exhibition featuring works by emerging Canadian artists exploring themes of identity and environment.', '2025-05-15 10:00:00', '2025-06-15 18:00:00', 'Contemporary Gallery', 'Ottawa', 'Ontario', 'K1N 5T6', 'Canada', 9, 2, NULL, '/Assignment2/uploads/art_showcase.jpg'),

('Guest Lecture: Climate Science', 'Distinguished professor Dr. Emily Chen discusses latest research on climate change mitigation strategies.', '2025-04-22 15:00:00', '2025-04-22 17:00:00', 'University Main Hall', 'Ottawa', 'Ontario', 'K1S 5S3', 'Canada', 10, 3, 120, '/Assignment2/uploads/climate_lecture.jpg'),

('Python Programming Workshop', 'Intermediate level workshop covering data analysis with pandas and visualization with matplotlib.', '2025-06-12 09:30:00', '2025-06-12 15:30:00', 'Digital Learning Center', 'Ottawa', 'Ontario', 'K2P 0A5', 'Canada', 2, 4, 25, '/Assignment2/uploads/python_workshop.jpg'),

('Business Networking Breakfast', 'Early morning networking event for entrepreneurs and business professionals. Continental breakfast provided.', '2025-05-07 07:30:00', '2025-05-07 09:00:00', 'Downtown Hotel', 'Ottawa', 'Ontario', 'K1P 5H8', 'Canada', 3, 5, 75, '/Assignment2/uploads/business_breakfast.jpg');

-- Insert RSVPs
INSERT INTO rsvps (event_id, user_id, status, rsvp_date) VALUES
(1, 2, 'attending', NOW() - INTERVAL 5 DAY),
(1, 3, 'attending', NOW() - INTERVAL 4 DAY),
(1, 4, 'maybe', NOW() - INTERVAL 3 DAY),
(1, 5, 'attending', NOW() - INTERVAL 2 DAY),
(1, 6, 'attending', NOW() - INTERVAL 1 DAY),
(2, 1, 'attending', NOW() - INTERVAL 7 DAY),
(2, 3, 'attending', NOW() - INTERVAL 6 DAY),
(2, 7, 'maybe', NOW() - INTERVAL 5 DAY),
(3, 1, 'attending', NOW() - INTERVAL 8 DAY),
(3, 2, 'attending', NOW() - INTERVAL 7 DAY),
(3, 4, 'not_attending', NOW() - INTERVAL 6 DAY),
(3, 5, 'attending', NOW() - INTERVAL 5 DAY),
(4, 1, 'attending', NOW() - INTERVAL 10 DAY),
(4, 2, 'attending', NOW() - INTERVAL 9 DAY),
(4, 3, 'attending', NOW() - INTERVAL 8 DAY),
(4, 5, 'maybe', NOW() - INTERVAL 7 DAY),
(4, 6, 'attending', NOW() - INTERVAL 6 DAY),
(4, 7, 'attending', NOW() - INTERVAL 5 DAY),
(5, 1, 'attending', NOW() - INTERVAL 12 DAY),
(6, 2, 'attending', NOW() - INTERVAL 11 DAY),
(7, 3, 'attending', NOW() - INTERVAL 10 DAY),
(8, 4, 'attending', NOW() - INTERVAL 9 DAY),
(9, 5, 'attending', NOW() - INTERVAL 8 DAY),
(10, 6, 'attending', NOW() - INTERVAL 7 DAY),
(11, 7, 'attending', NOW() - INTERVAL 6 DAY),
(12, 1, 'attending', NOW() - INTERVAL 5 DAY),
(12, 2, 'maybe', NOW() - INTERVAL 4 DAY),
(12, 3, 'attending', NOW() - INTERVAL 3 DAY);