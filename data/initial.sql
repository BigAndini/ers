-- add roles
INSERT INTO `role` (`id`, `parent_id`, `roleId`, `active`, `updated`, `created`) VALUES 
(NULL, NULL, 'onsitereg',         1, '2017-02-11 13:37:00', '2017-02-11 13:37:00'),
(NULL, NULL, 'supradm',           1, '2017-02-11 13:37:00', '2017-02-11 13:37:00'),
(NULL, NULL, 'preregcoordinator', 1, '2017-02-11 13:37:00', '2017-02-11 13:37:00'),
(NULL, NULL, 'user',              1, '2017-02-11 13:37:00', '2017-02-11 13:37:00'),
(NULL, NULL, 'admin',             1, '2017-02-11 13:37:00', '2017-02-11 13:37:00'),
(NULL, NULL, 'guest',             1, '2017-02-11 13:37:00', '2017-02-11 13:37:00'),
(NULL, NULL, 'participant',       1, '2017-02-11 13:37:00', '2017-02-11 13:37:00'),
(NULL, NULL, 'buyer',             1, '2017-02-11 13:37:00', '2017-02-11 13:37:00'); 

-- add admin user: admin@ers.inbaz.org
-- tax examples
-- deadline examples
-- agegroup examples

-- add status
INSERT INTO `status` (`id`, `position`, `active`, `value`, `description`, `updated`, `created`, `valid`) VALUES
(1, 1, 0, 'order pending', '', NULL, NULL, 0),
(2, 2, 1, 'ordered', '', NULL, NULL, 0),
(3, 3, 1, 'paid', '', NULL, NULL, 1),
(4, 4, 1, 'shipped', '', NULL, NULL, 0),
(5, 5, 0, 'cancelled', '', NULL, NULL, 0),
(6, 6, 0, 'transferred', '', NULL, NULL, 0),
(7, 3, 1, 'free', '', NULL, NULL, 1),
(8, 3, 1, 'bar', '', NULL, NULL, 1),
(9, 5, 1, 'overpaid', '', NULL, NULL, 0),
(10, 3, 1, 'partly paid', '', NULL, NULL, 0);

INSERT INTO `user` (`id`, `username`, `email`, `email_status`, `display_name`, `firstname`, `surname`, `gender`, `Country_id`, `password`, `hashkey`, `state`, `active`, `birthday`, `login_count`, `newsletter`, `updated`, `created`) VALUES 
(NULL, NULL, 'andi@inbaz.org', NULL, NULL, 'Andi', 'Nitsche', NULL, NULL, NULL, NULL, NULL, '1', NULL, NULL, NULL, '2017-02-11 00:00:00', '2017-02-11 00:00:00');

INSERT INTO `user_has_role` (`user_id`, `role_id`) VALUES 
('1', '5'); 