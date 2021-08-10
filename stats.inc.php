<?php

/**
 *------
 * BGA framework: © Gregory Isabelli <gisabelli@boardgamearena.com> & Emmanuel Colin <ecolin@boardgamearena.com>
 * diceforge implementation : © Thibaut Brissard <docthib@hotmail.com> & Vincent Toper <vincent.toper@gmail.com>
 *
 * This code has been produced on the BGA studio platform for use on http://boardgamearena.com.
 * See http://en.boardgamearena.com/#!doc/Studio for more information.
 * -----
 *
 * stats.inc.php
 *
 * diceforge game statistics description
 *
 */

/*
    In this file, you are describing game statistics, that will be displayed at the end of the
    game.
    
    !! After modifying this file, you must use "Reload  statistics configuration" in BGA Studio backoffice
    ("Control Panel" / "Manage Game" / "Your Game")
    
    There are 2 types of statistics:
    _ table statistics, that are not associated to a specific player (ie: 1 value for each game).
    _ player statistics, that are associated to each players (ie: 1 value for each player in the game).

    Statistics types can be "int" for integer, "float" for floating point values, and "bool" for boolean
    
    Once you defined your statistics there, you can start using "initStat", "setStat" and "incStat" method
    in your game logic, using statistics names defined below.
    
    !! It is not a good idea to modify this file when a game is running !!

    If your game is already public on BGA, please read the following before any change:
    http://en.doc.boardgamearena.com/Post-release_phase#Changes_that_breaks_the_games_in_progress
    
    Notes:
    * Statistic index is the reference used in setStat/incStat/initStat PHP method
    * Statistic index must contains alphanumerical characters and no space. Example: 'turn_played'
    * Statistics IDs must be >=10
    * Two table statistics can't share the same ID, two player statistics can't share the same ID
    * A table statistic can have the same ID than a player statistics
    * Statistics ID is the reference used by BGA website. If you change the ID, you lost all historical statistic data. Do NOT re-use an ID of a deleted statistic
    * Statistic name is the English description of the statistic as shown to players
    
*/

$stats_type = array(

    // Statistics global to table
    "table" => array(

        "turns_number" => array("id"=> 10,
                    "name" => totranslate("Number of turns"),
                    "type" => "int" ), 
    ),
    
    // Statistics existing for each player
    "player" => array(
		"nb_vp_die" => array("id"=> 11,
                    "name" => totranslate("VP from Dice"),
                    "type" => "int" ),
        "nb_vp_exploit" => array("id"=> 12,
                    "name" => totranslate("VP from Heroic feats"),
                    "type" => "int" ),
		"nb_vp_hammer" => array("id"=> 13,
                    "name" => totranslate("VP from Hammer"),
                    "type" => "int" ),
        "nb_vp_ancient" => array("id"=> 14,
                    "name" => totranslate("VP from the Elder"),
                    "type" => "int" ),
        "nb_vp_nymph" => array("id"=> 48,
                    "name" => totranslate("VP from the Nymph"),
                    "type" => "int" ),
        "nb_vp_bear" => array("id"=> 15,
                    "name" => totranslate("VP from Bear"),
                    "type" => "int" ),
		"nb_vp_guardian" => array("id"=> 16,
                    "name" => totranslate("VP from Guardian's Shield"),
                    "type" => "int" ),
        "nb_vp_typhon" => array("id"=> 17,
                    "name" => totranslate("VP from Typhon"),
                    "type" => "int" ),
        "nb_vp_boar" => array("id"=> 46,
                    "name" => totranslate("VP from Tenacious boar"),
                    "type" => "int" ),
        "nb_vp_tree" => array("id"=> 49,
                    "name" => totranslate("VP from Tree"),
                    "type" => "int" ),
        "nb_throws" => array("id"=> 22,
                    "name" => totranslate("Number of dice throws"),
                    "type" => "int" ),
        "nb_boar" => array("id"=> 47,
                    "name" => totranslate("Number of times Tenacious Boar was activated"),
                    "type" => "int" ),
        "nb_twins" => array("id"=> 50,
                    "name" => totranslate("Number of times Twins was activated"),
                    "type" => "int" ),
		"nb_side_forge" => array("id"=> 23,
                    "name" => totranslate("Number of forged sides"),
                    "type" => "int" ),
        "nb_exploit_buy" => array("id"=> 24,
                    "name" => totranslate("Number of bought heroic feats"),
                    "type" => "int" ),
        "nb_reinforcement" => array("id"=> 25,
                    "name" => totranslate("Number of played reinforcements"),
                    "type" => "int" ),
        
        "nb_won_gold" => array("id"=> 26,
                    "name" => totranslate("Amount of gold from the dice"),
                    "type" => "int" ),
        "nb_loss_gold" => array("id"=> 27,
                    "name" => totranslate("Amount of lost gold"),
                    "type" => "int" ),
        "nb_used_gold" => array("id"=> 28,
                    "name" => totranslate("Number of used gold"),
                    "type" => "int" ),
        "nb_won_fireshard" => array("id"=> 29,
                    "name" => totranslate("Amount of fireshard from the dice"),
                    "type" => "int" ),
        "nb_loss_fireshard" => array("id"=> 30,
                    "name" => totranslate("Amount of lost fireshard"),
                    "type" => "int" ),
        "nb_used_fireshard" => array("id"=> 31,
                    "name" => totranslate("Number of used fireshard"),
                    "type" => "int" ),
        "nb_won_moonshard" => array("id"=> 32,
                    "name" => totranslate("Amount of moonshard from the dice"),
                    "type" => "int" ),
        "nb_loss_moonshard" => array("id"=> 33,
                    "name" => totranslate("Amount of lost moonshard"),
                    "type" => "int" ),
        "nb_used_moonshard" => array("id"=> 34,
                    "name" => totranslate("Number of used moonshard"),
                    "type" => "int" ),
    
        "nb_second_action" => array("id"=> 35,
                    "name" => totranslate("Number of second action taken"),
                    "type" => "int" ),
        "nb_has_ousted" => array("id"=> 36,
                    "name" => totranslate("Number of times player has ousted another player"),
                    "type" => "int" ),
        "nb_been_ousted" => array("id"=> 37,
                    "name" => totranslate("Number of times player has been ousted"),
                    "type" => "int" ),
        "nb_action_forge" => array("id"=> 38,
                    "name" => totranslate("Number of forge action"),
                    "type" => "int" ),
        "nb_action_exploit" => array("id"=> 39,
                    "name" => totranslate("Number of heroic feat action"),
                    "type" => "int" ),
        "nb_ressource_choice" => array("id"=> 40,
                    "name" => totranslate("Number of times player choosed a ressource"),
                    "type" => "int" ),
        "nb_minotaur_vp" => array("id"=> 41,
                    "name" => totranslate("Minotaur: Amount of lost VP"),
                    "type" => "int" ),			
		 "nb_minotaur_gold" => array("id"=> 42,
                    "name" => totranslate("Minotaur: Amount of lost gold"),
                    "type" => "int" ),
        "nb_minotaur_fireshard" => array("id"=> 43,
                    "name" => totranslate("Minotaur: Amount of lost fireshard"),
                    "type" => "int" ),
        "nb_minotaur_moonshard" => array("id"=> 44,
                    "name" => totranslate("Minotaur: Amount of lost moonshard"),
                    "type" => "int" ),
       
		"turns_number" => array("id"=> 45,
                    "name" => totranslate("Number of turns"),
                    "type" => "int" ),
        "nb_won_ancientshard" => array("id"=> 52,
                    "name" => totranslate("Amount of moonshard from the dice"),
                    "type" => "int" ),
        "nb_loss_ancientshard" => array("id"=> 53,
                    "name" => totranslate("Amount of lost moonshard"),
                    "type" => "int" ),
        "nb_used_ancientshard" => array("id"=> 54,
                    "name" => totranslate("Number of used moonshard"),
                    "type" => "int" ),
    
/*
    Nombre de ressources récoltées via dé (pour chaque)
    Nombre de deuxième action prises - ok
    Nombre de face achetées - ok
    

*/    
    )

);
