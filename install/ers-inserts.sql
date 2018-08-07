
-- -----------------------------------------------------
-- Data for table `ers`.`Tax`
-- -----------------------------------------------------
START TRANSACTION;
INSERT INTO `ers`.`Tax` (`id`, `name`, `percentage`, `updated`, `created`) VALUES (1, 'no tax', 0, NULL, NULL);
INSERT INTO `ers`.`Tax` (`id`, `name`, `percentage`, `updated`, `created`) VALUES (2, 'food', 7, NULL, NULL);
INSERT INTO `ers`.`Tax` (`id`, `name`, `percentage`, `updated`, `created`) VALUES (3, 'non-food', 19, NULL, NULL);

COMMIT;


-- -----------------------------------------------------
-- Data for table `ers`.`Currency`
-- -----------------------------------------------------
START TRANSACTION;
INSERT INTO `ers`.`Currency` (`id`, `name`, `symbol`, `exchange2euro`, `short`, `updated`, `created`) VALUES (NULL, 'Euro', '€', NULL, 'EUR', NULL, NULL);
INSERT INTO `ers`.`Currency` (`id`, `name`, `symbol`, `exchange2euro`, `short`, `updated`, `created`) VALUES (NULL, 'Pound', '£', NULL, 'GBP', NULL, NULL);
INSERT INTO `ers`.`Currency` (`id`, `name`, `symbol`, `exchange2euro`, `short`, `updated`, `created`) VALUES (NULL, 'Dollar', '$', NULL, 'USD', NULL, NULL);

COMMIT;

-- -----------------------------------------------------
-- Data for table `ers`.`Product`
-- -----------------------------------------------------
START TRANSACTION;
INSERT INTO `ers`.`Product` (`id`, `Tax_id`, `name`, `shortDescription`, `longDescription`, `updated`, `created`) VALUES (1, 1, 'Week Ticket Adult', 'Stay the whole week', 'Stay the whole week!', NULL, NULL);

COMMIT;

-- -----------------------------------------------------
-- Data for table `ers`.`ProductPrice`
-- -----------------------------------------------------
START TRANSACTION;
INSERT INTO `ers`.`ProductPrice` (`id`, `Product_id`, `charge`, `updated`, `created`) VALUES (1, 1, '100', NULL, NULL);

COMMIT;


-- -----------------------------------------------------
-- Data for table `ers`.`PriceLimit`
-- -----------------------------------------------------
/*START TRANSACTION;
INSERT INTO `ers`.`PriceLimit` (`id`, `type`, `value`) VALUES (NULL, 'agegroup', NULL);
INSERT INTO `ers`.`PriceLimit` (`id`, `type`, `value`) VALUES (NULL, 'deadline', NULL);
INSERT INTO `ers`.`PriceLimit` (`id`, `type`, `value`) VALUES (NULL, 'counter', NULL);
INSERT INTO `ers`.`PriceLimit` (`id`, `type`, `value`) VALUES (NULL, 'agegroup', NULL);

COMMIT;*/

