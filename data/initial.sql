/* add roles */
INSERT INTO `role` (`id`, `parent_id`, `roleId`, `active`, `updated`, `created`) VALUES (NULL, NULL, 'onsitereg', '', '', ''); 
INSERT INTO `role` (`id`, `parent_id`, `roleId`, `active`, `updated`, `created`) VALUES (NULL, NULL, 'supradm', '', '', ''); 
INSERT INTO `role` (`id`, `parent_id`, `roleId`, `active`, `updated`, `created`) VALUES (NULL, NULL, 'preregcoordinator', '', '', ''); 
INSERT INTO `role` (`id`, `parent_id`, `roleId`, `active`, `updated`, `created`) VALUES (NULL, NULL, 'user', '', '', ''); 
INSERT INTO `role` (`id`, `parent_id`, `roleId`, `active`, `updated`, `created`) VALUES (NULL, NULL, 'admin', '', '', ''); 
INSERT INTO `role` (`id`, `parent_id`, `roleId`, `active`, `updated`, `created`) VALUES (NULL, NULL, 'guest', '', '', ''); 
INSERT INTO `role` (`id`, `parent_id`, `roleId`, `active`, `updated`, `created`) VALUES (NULL, NULL, 'participant', '', '', ''); 

/* add admin user: admin@ers.inbaz.org */
/* tax examples */
/* deadline examples */
/* agegroup examples */

/* add status */
INSERT INTO `status` (`id`, `position`, `value`, `description`, `updated`, `created`, `active`) VALUES
(1, 1, 'order pending', '', NULL, NULL, 0),
(2, 2, 'ordered', '', NULL, NULL, 1),
(3, 3, 'paid', '', NULL, NULL, 1),
(4, 4, 'shipped', '', NULL, NULL, 1),
(5, 5, 'cancelled', '', NULL, NULL, 0),
(6, 6, 'transferred', '', NULL, NULL, 0);