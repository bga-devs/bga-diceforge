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
 * gameoptions.inc.php
 *
 * diceforge game options description
 * 
 * In this file, you can define your game options (= game variants).
 *   
 * Note: If your game has no variant, you don't have to modify this file.
 *
 * Note²: All options defined in this file should have a corresponding "game state labels"
 *        with the same ID (see "initGameStateLabels" in diceforge.game.php)
 *
 * !! It is not a good idea to modify this file when a game is running !!
 *
 */

$game_options = array(

    /* Example of game variant:
    
    
    // note: game variant ID should start at 100 (ie: 100, 101, 102, ...). The maximum is 199.
    100 => array(
                'name' => totranslate('my game option'),    
                'values' => array(

                            // A simple value for this option:
                            1 => array( 'name' => totranslate('option 1') )

                            // A simple value for this option.
                            // If this value is chosen, the value of "tmdisplay" is displayed in the game lobby
                            2 => array( 'name' => totranslate('option 2'), 'tmdisplay' => totranslate('option 2') ),

                            // Another value, with other options:
                            //  beta=true => this option is in beta version right now.
                            //  nobeginner=true  =>  this option is not recommended for beginners
                            3 => array( 'name' => totranslate('option 3'),  'beta' => true, 'nobeginner' => true ),) )
                        )
            )

    */
    100 => array(
        'name'   => totranslate('Exploit set'),
        'values' => array(
            1   => array('name' => totranslate('Beginner cards'), 'tmdisplay' => totranslate('Beginner cards') ),
            99  => array('name' => totranslate('Advanced cards'), 'nobeginner' => true, 'tmdisplay' => totranslate('Advanced cards')),
            2   => array('name' => totranslate('Random'), 'nobeginner' => true, 'tmdisplay' => totranslate('Random') ),
            3   => array('name' => totranslate('Draft'),  'nobeginner' => true, 'tmdisplay' => totranslate('Draft') ),
            4   => array('name' => totranslate('Recommended (extension)'),  'beta' => true, 'nobeginner' => true, 'tmdisplay' => totranslate('Recommended set (extension)') ),
            5   => array('name' => totranslate('Perfect for 2 players'), 'beta' => true,   'nobeginner' => true, 'tmdisplay' => totranslate('setup Perfect for 2 players') ),
            6   => array('name' => totranslate('Again and again'), 'beta' => true,   'nobeginner' => true, 'tmdisplay' => totranslate('setup Again and again') ),
            7   => array('name' => totranslate('May the forge be with you'),  'beta' => true, 'nobeginner' => true, 'tmdisplay' => totranslate('setup May the forge be with you') ),
            8   => array('name' => totranslate('Versatility'), 'beta' => true,  'nobeginner' => true, 'tmdisplay' => totranslate('setup Versatility') ),
            9   => array('name' => totranslate('2020 : Tournament setup 1'), 'beta' => true,   'nobeginner' => true, 'tmdisplay' => totranslate('2020 : Tournament setup 1') ),
           10   => array('name' => totranslate('2020 : Tournament setup 2'), 'beta' => true,  'nobeginner' => true, 'tmdisplay' => totranslate('2020 : Tournament setup 2') ),
        ),
        'startcondition'    => array(
          1 => array(
            array(
                'type'      => 'otheroptionisnot',
                'id'        => 102,
                'value'     => '2',
                'message'   => totranslate('You cannot use this option without a module (necessary to match Beginner cards)')
                ),
          ),
          99 => array(
            array(
                'type'      => 'otheroptionisnot',
                'id'        => 102,
                'value'     => '2',
                'message'   => totranslate('You cannot use this option without a module (necessary to match Advanced cards)')
                ),
            ),
          2 => array(),
          3 => array(),
          4 => array(
            array(
                'type'      => 'otheroptionisnot',
                'id'        => 102,
                'value'     => '1',
                'message'   => totranslate('You cannot use this option with the extension disabled')
                ),
            array(
                'type'      => 'otheroptionisnot',
                'id'        => 102,
                'value'     => '2',
                'message'   => totranslate('You cannot use this option without a module (as the recommended sets are linked to a module)')
                ),
          ),
          5 => array(
            array(
                'type'      => 'otheroptionisnot',
                'id'        => 102,
                'value'     => '1',
                'message'   => totranslate('You must enable Rebellion with "Cards only" option')
                ),
            array(
                'type'      => 'otheroptionisnot',
                'id'        => 102,
                'value'     => '3',
                'message'   => totranslate('You must enable Rebellion with "Cards only" option')
                ),
            array(
                'type'      => 'otheroptionisnot',
                'id'        => 102,
                'value'     => '4',
                'message'   => totranslate('You must enable Rebellion with "Cards only" option')
                ),
          ),
          6 => array(
            array(
                'type'      => 'otheroptionisnot',
                'id'        => 102,
                'value'     => '1',
                'message'   => totranslate('You must enable Rebellion with "Cards only" option')
                ),
            array(
                'type'      => 'otheroptionisnot',
                'id'        => 102,
                'value'     => '3',
                'message'   => totranslate('You must enable Rebellion with "Cards only" option')
                ),
            array(
                'type'      => 'otheroptionisnot',
                'id'        => 102,
                'value'     => '4',
                'message'   => totranslate('You must enable Rebellion with "Cards only" option')
                ),
          ),
          7 => array(
            array(
                'type'      => 'otheroptionisnot',
                'id'        => 102,
                'value'     => '1',
                'message'   => totranslate('You must enable Rebellion with "Cards only" option')
                ),
            array(
                'type'      => 'otheroptionisnot',
                'id'        => 102,
                'value'     => '3',
                'message'   => totranslate('You must enable Rebellion with "Cards only" option')
                ),
            array(
                'type'      => 'otheroptionisnot',
                'id'        => 102,
                'value'     => '4',
                'message'   => totranslate('You must enable Rebellion with "Cards only" option')
                ),
          ),
          8 => array(
            array(
                'type'      => 'otheroptionisnot',
                'id'        => 102,
                'value'     => '1',
                'message'   => totranslate('You must enable Rebellion with "Cards only" option')
                ),
            array(
                'type'      => 'otheroptionisnot',
                'id'        => 102,
                'value'     => '3',
                'message'   => totranslate('You must enable Rebellion with "Cards only" option')
                ),
            array(
                'type'      => 'otheroptionisnot',
                'id'        => 102,
                'value'     => '4',
                'message'   => totranslate('You must enable Rebellion with "Cards only" option')
                ),
          ),
          9 => array(
            array(
                'type'      => 'otheroptionisnot',
                'id'        => 102,
                'value'     => '1',
                'message'   => totranslate('You must enable Rebellion with "Cards only" option')
                ),
            array(
                'type'      => 'otheroptionisnot',
                'id'        => 102,
                'value'     => '3',
                'message'   => totranslate('You must enable Rebellion with "Cards only" option')
                ),
            array(
                'type'      => 'otheroptionisnot',
                'id'        => 102,
                'value'     => '4',
                'message'   => totranslate('You must enable Rebellion with "Cards only" option')
                ),
          ),
          10 => array(
            array(
                'type'      => 'otheroptionisnot',
                'id'        => 102,
                'value'     => '1',
                'message'   => totranslate('You must enable Rebellion with "Cards only" option')
                ),
            array(
                'type'      => 'otheroptionisnot',
                'id'        => 102,
                'value'     => '3',
                'message'   => totranslate('You must enable Rebellion with "Cards only" option')
                ),
            array(
                'type'      => 'otheroptionisnot',
                'id'        => 102,
                'value'     => '4',
                'message'   => totranslate('You must enable Rebellion with "Cards only" option')
                ),
          ),
        ),
    ),
    103 => array (
        'name'   => totranslate('Tiebreaker'),
        'values' => array(
            0  => array('name' => totranslate('No') ),
            1  => array('name' => totranslate('Yes'), 'nobeginner' => true, 'tmdisplay' => totranslate('with tiebreaker')),
        ),
    ),
    102 => array(
        'name'   => totranslate('Dice Forge Rebellion'),
        'values' => array(
            1   => array('name' => totranslate('Off')),
            2   => array('name' => totranslate('Cards only'), 'nobeginner' => true, 'beta' => true, 'tmdisplay' => totranslate('extensions card')),
            //3   => array('name' => totranslate('Module Titan'), 'nobeginner' => true, 'tmdisplay' => totranslate('Titan\'s module')),
            4   => array('name' => totranslate('Module Goddess'), 'nobeginner' => true, 'beta' => true, 'tmdisplay' => totranslate('Goddess\' module') ),
        ),
        'disable' => true,
    ),
    101 => array(
        'name'   => totranslate('Promo cards'),
        'values' => array(
            0  => array('name' => totranslate('No') ),
            1  => array('name' => totranslate('Yes'), 'nobeginner' => true, 'tmdisplay' => totranslate('with Promo cards')),
        ),
        'displaycondition' => array(
            array(
                'type'  => 'otheroption',
                'id'    => 100,
                'value' => array(2, 3),
            ),
        ),
    ),

);

$game_preferences = array(
    100 => array(
        'name'       => totranslate('Active player action buttons'),
        'needReload' => true, // after user changes this preference game interface would auto-reload
        'values'     => array(
                // 1 => array( 'name' => totranslate( 'Classic' ), 'cssPref' => 'notation_classic' ),
                1 => array( 'name' => totranslate( 'With' ) ),
                2 => array( 'name' => totranslate( 'Without' ) ),
        ),
    ),
);


