
-- ------
-- BGA framework: © Gregory Isabelli <gisabelli@boardgamearena.com> & Emmanuel Colin <ecolin@boardgamearena.com>
-- diceforge implementation : © Thibaut Brissard <docthib@hotmail.com> & Vincent Toper <vincent.toper@gmail.com>
-- 
-- This code has been produced on the BGA studio platform for use on http://boardgamearena.com.
-- See http://en.boardgamearena.com/#!doc/Studio for more information.
-- -----

-- dbmodel.sql

-- This is the file where you are describing the database schema of your game
-- Basically, you just have to export from PhpMyAdmin your table structure and copy/paste
-- this export here.
-- Note that the database itself and the standard tables ("global", "stats", "gamelog" and "player") are
-- already created and must not be created here

-- Note: The database schema is created from this file when the game starts. If you modify this file,
--       you have to restart a game to see your changes in database.

-- Example 1: create a standard "card" table to be used with the "Deck" tools (see example game "hearts"):

-- CREATE TABLE IF NOT EXISTS `card` (
--   `card_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
--   `card_type` varchar(16) NOT NULL,
--   `card_type_arg` int(11) NOT NULL,
--   `card_location` varchar(16) NOT NULL,
--   `card_location_arg` int(11) NOT NULL,
--   PRIMARY KEY (`card_id`)
-- ) ENGINE=InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;


-- Example 2: add a custom field to the standard "player" table
-- ALTER TABLE `player` ADD `player_my_custom_field` INT UNSIGNED NOT NULL DEFAULT '0';

--- ### PLAYER INFORMATION ###
--- Gold
ALTER TABLE `player` ADD `res_gold` int(2) NOT NULL DEFAULT '0';
--- Fire
ALTER TABLE `player` ADD `res_fire` int(2) NOT NULL DEFAULT '0';
--- Moon
ALTER TABLE `player` ADD `res_moon` int(2) NOT NULL DEFAULT '0';
--- Position
ALTER TABLE `player` ADD `position` varchar(10) NOT NULL DEFAULT 'begin';
--- Coffre
ALTER TABLE `player` ADD `coffre` int(1) NOT NULL DEFAULT '0'; -- A RETIRER IMO
--- Hammer
ALTER TABLE `player` ADD `hammer_position` int(2) DEFAULT NULL;
--- Cerberus token
ALTER TABLE `player` ADD `cerberus_token` int(1) DEFAULT 0;
--- Triton token
ALTER TABLE `player` ADD `triton_token` int(1) DEFAULT 0;
--- Does it need to make a ressource choice
ALTER TABLE `player` ADD `ressource_choice` int(1) DEFAULT -1;

ALTER TABLE `player` ADD `side_choice_1` varchar(20) DEFAULT '0';
ALTER TABLE `player` ADD `side_choice_2` varchar(20) DEFAULT '0';

ALTER TABLE `player` ADD `forge` varchar(200) DEFAULT '';

ALTER TABLE `player` ADD `boar` int(1) DEFAULT 0;

ALTER TABLE `player` ADD `throw_1` varchar(20) DEFAULT '0';
ALTER TABLE `player` ADD `throw_2` varchar(20) DEFAULT '0';

ALTER TABLE `player` ADD `hammer_auto` int(1) DEFAULT 0;
ALTER TABLE `player` ADD `rolled` int(1) DEFAULT 0;

ALTER TABLE `player` ADD `twins` int(1) DEFAULT 0;

ALTER TABLE `player` ADD `res_ancient` int(1) DEFAULT 0;

--- ### EXPLOIT CARDS ###
--- card_type : exploit name, from material $this->exploit_types
--- card_type_arg : not used
--- card_location : F1, F2, F3,... (position in the slots on the board) or table (hammer not ended) / pile1 (heroic feats done) / pile2 (automatic effects: hourglass) / pile3 (reinforcement)
--- card_location_arg : player_id if filled in
CREATE TABLE IF NOT EXISTS `exploit` (
  `card_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `card_type` varchar(16) NOT NULL, 
  `card_type_arg` int(11) NOT NULL, 
  `card_location` varchar(40) NOT NULL,  
  `card_location_arg` int(11) NOT NULL, 
  PRIMARY KEY (`card_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

--- ### Dice management ###
CREATE TABLE IF NOT EXISTS `sides` (
  `card_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `card_type` varchar(20) NOT NULL,
  `card_type_arg` int(11) NOT NULL, 
  `card_location` varchar(40) NOT NULL,  
  `card_location_arg` int(11) NOT NULL, 
  PRIMARY KEY (`card_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

--- ## Rebellion
CREATE TABLE IF NOT EXISTS `token` (
 `token_key` varchar(32) NOT NULL,
 `token_location` varchar(32) NOT NULL,
 `token_state` int(10),
 PRIMARY KEY (`token_key`)
 ) ENGINE=InnoDB DEFAULT CHARSET=utf8;
-- sceptre => card_location_ag
-- position jetons memory
-- position trésor
-- companion => card_location_arg