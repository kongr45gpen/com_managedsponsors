ALTER TABLE `orspa_managedsponsors_sponsor` ADD `margin` VARCHAR(50) NULL DEFAULT '' AFTER `website`, ADD `padding` VARCHAR(50) NULL DEFAULT '' AFTER `margin`, ADD `size` FLOAT NOT NULL DEFAULT '100' AFTER `padding`; 
