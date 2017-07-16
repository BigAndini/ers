-- add roles
INSERT INTO `role` (`id`, `parent_id`, `roleId`, `active`, `updated`, `created`) VALUES 
(NULL, NULL, 'onsitereg',         1, '2017-02-11 13:37:00', '2017-02-11 13:37:00'),
(NULL, NULL, 'supradm',           1, '2017-02-11 13:37:00', '2017-02-11 13:37:00'),
(NULL, NULL, 'preregcoordinator', 1, '2017-02-11 13:37:00', '2017-02-11 13:37:00'),
(NULL, NULL, 'user',              1, '2017-02-11 13:37:00', '2017-02-11 13:37:00'),
(NULL, NULL, 'admin',             1, '2017-02-11 13:37:00', '2017-02-11 13:37:00'),
(NULL, NULL, 'guest',             1, '2017-02-11 13:37:00', '2017-02-11 13:37:00'),
(NULL, NULL, 'participant',       1, '2017-02-11 13:37:00', '2017-02-11 13:37:00'); 
(NULL, NULL, 'buyer',             1, '2017-02-11 13:37:00', '2017-02-11 13:37:00'); 

-- add admin user: admin@ers.inbaz.org
-- tax examples
-- deadline examples
-- agegroup examples

-- add status
INSERT INTO `status` (`id`, `position`, `value`, `description`, `updated`, `created`, `active`) VALUES
(1, 1, 'order pending', '', NULL, NULL, 0),
(2, 2, 'ordered', '', NULL, NULL, 1),
(3, 3, 'paid', '', NULL, NULL, 1),
(4, 4, 'shipped', '', NULL, NULL, 1),
(5, 5, 'cancelled', '', NULL, NULL, 0),
(6, 6, 'transferred', '', NULL, NULL, 0);

INSERT INTO `user` (`id`, `username`, `email`, `email_status`, `display_name`, `firstname`, `surname`, `gender`, `Country_id`, `password`, `hashkey`, `state`, `active`, `birthday`, `login_count`, `newsletter`, `updated`, `created`) VALUES 
(NULL, NULL, 'andi@inbaz.org', NULL, NULL, 'Andi', 'Nitsche', NULL, NULL, NULL, NULL, NULL, '1', NULL, NULL, NULL, '2017-02-11 00:00:00', '2017-02-11 00:00:00');

INSERT INTO `user_has_role` (`user_id`, `role_id`) VALUES 
('1', '5'); 