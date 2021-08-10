<?php

/*
    From this file, you can edit the various meta-information of your game.

    Once you modified the file, don't forget to click on "Reload game informations" from the Control Panel in order in can be taken into account.

    See documentation about this file here:
    http://en.doc.boardgamearena.com/Game_meta-information:_gameinfos.inc.php

*/

$gameinfos = array( 

// Name of the game in English (will serve as the basis for translation) 
'game_name' => clienttranslate("Dice Forge"),

// Game designer (or game designers, separated by commas)
'designer' => 'Régis Bonnessée',       

// Game artist (or game artists, separated by commas)
'artist' => 'Biboun',         

// Year of FIRST publication of this game. Can be negative.
'year' => 2017,                 

// Game publisher
'publisher' => 'Libellud',                     

// Url of game publisher website
'publisher_website' => 'http://www.libellud.com',   

// Board Game Geek ID of the publisher
'publisher_bgg_id' => 9051,

// Board game geek ID of the game
'bgg_id' => 194594,


// Players configuration that can be played (ex: 2 to 4 players)
'players' => array( 2,3,4 ),    

// Suggest players to play with this number of players. Must be null if there is no such advice, or if there is only one possible player configuration.
'suggest_player_number' => null,

// Discourage players to play with these numbers of players. Must be null if there is no such advice.
'not_recommend_player_number' => null,
// 'not_recommend_player_number' => array( 2, 3 ),      // <= example: this is not recommended to play this game with 2 or 3 players


// Estimated game duration, in minutes (used only for the launch, afterward the real duration is computed)
'estimated_duration' => 30,           

// Time in second add to a player when "giveExtraTime" is called (speed profile = fast)
'fast_additional_time' => 15,           

// Time in second add to a player when "giveExtraTime" is called (speed profile = medium)
'medium_additional_time' => 30,           

// Time in second add to a player when "giveExtraTime" is called (speed profile = slow)
'slow_additional_time' => 45,           

// If you are using a tie breaker in your game (using "player_score_aux"), you must describe here
// the formula used to compute "player_score_aux". This description will be used as a tooltip to explain
// the tie breaker to the players.
// Note: if you are NOT using any tie breaker, leave the empty string.
//
// Example: 'tie_breaker_description' => totranslate( "Number of remaining cards in hand" ),
'tie_breaker_description' => totranslate("In the event of a tie and if enabled, the following tie breakers are implemented:  1/ Number of die faces forged  2/ Number of Heroic Feats accomplished  3/ Number of remaining resources  4/The player having started the game the earliest"),

// Game is "beta". A game MUST set is_beta=1 when published on BGA for the first time, and must remains like this until all bugs are fixed.
'is_beta' => 1,                     

// Is this game cooperative (all players wins together or loose together)
'is_coop' => 0, 


// Complexity of the game, from 0 (extremely simple) to 5 (extremely complex)
'complexity' => 2,    

// Luck of the game, from 0 (absolutely no luck in this game) to 5 (totally luck driven)
'luck' => 4,    

// Strategy of the game, from 0 (no strategy can be setup) to 5 (totally based on strategy)
'strategy' => 2,    

// Diplomacy of the game, from 0 (no interaction in this game) to 5 (totally based on interaction and discussion between players)
'diplomacy' => 0,    

// Colors attributed to players
// orange, green, blue, black
'player_colors' => array( "D56F12", "B6B525", "5D8688", "000000" ),

// Favorite colors support : if set to "true", support attribution of favorite colors based on player's preferences (see reattributeColorsBasedOnPreferences PHP method)
'favorite_colors_support' => false,

// Game interface width range (pixels)
// Note: game interface = space on the left side, without the column on the right
'game_interface_width' => array(

    // Minimum width
    //  default: 740
    //  maximum possible value: 740 (ie: your game interface should fit with a 740px width (correspond to a 1024px screen)
    //  minimum possible value: 320 (the lowest value you specify, the better the display is on mobile)
    'min' => 800,

    // Maximum width
    //  default: null (ie: no limit, the game interface is as big as the player's screen allows it).
    //  maximum possible value: unlimited
    //  minimum possible value: 740
    'max' => null
),

// Games categories
//  You can attribute a maximum of FIVE "tags" for your game.
//  Each tag has a specific ID (ex: 22 for the category "Prototype", 101 for the tag "Science-fiction theme game")
//  Please see the "Game meta information" entry in the BGA Studio documentation for a full list of available tags:
//  http://en.doc.boardgamearena.com/Game_meta-information:_gameinfos.inc.php
//  IMPORTANT: this list should be ORDERED, with the most important tag first.
//  IMPORTANT: it is mandatory that the FIRST tag is 1, 2, 3 and 4 (= game category)
'tags' => array( 2, 11, 201, 207 ),


// Game presentation
// Short game presentation text (6-9 lines) that will appear on the game description page, structured as an array of paragraphs.
// Each paragraph must be wrapped with totranslate() for translation and should not contain html (plain text without formatting).
// A good length for this text is between 100 and 150 words (about 6 to 9 lines on a standard display)

'presentation' => array(
    totranslate("Dice Forge is a development game featuring innovative mechanics based on dice with removable faces."),
    totranslate("In this dice crafting game, players build their own dice. Roll your dice, manage your resources, complete ordeals before your opponents and explore multiple winning strategies."),
    totranslate("Now you control the luck of the dice!"),
    totranslate("Forge your destiny!"),
  ),  
    


//////// BGA SANDBOX ONLY PARAMETERS (DO NOT MODIFY)

// simple : A plays, B plays, C plays, A plays, B plays, ...
// circuit : A plays and choose the next player C, C plays and choose the next player D, ...
// complex : A+B+C plays and says that the next player is A+B
'is_sandbox' => false,
'turnControl' => 'simple'

////////
);
