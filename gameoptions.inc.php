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

$game_options = [
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
  100 => [
    'name' => totranslate('Exploit set'),
    'values' => [
      1 => ['name' => totranslate('Beginner cards'), 'tmdisplay' => totranslate('Beginner cards')],
      99 => [
        'name' => totranslate('Advanced cards'),
        'nobeginner' => true,
        'tmdisplay' => totranslate('Advanced cards'),
      ],
      2 => ['name' => totranslate('Random'), 'nobeginner' => true, 'tmdisplay' => totranslate('Random')],
      3 => ['name' => totranslate('Draft'), 'nobeginner' => true, 'tmdisplay' => totranslate('Draft')],
      4 => [
        'name' => totranslate('Recommended (extension)'),
        'nobeginner' => true,
        'tmdisplay' => totranslate('Recommended set (extension)'),
        'description' => totranslate('Perfect set to discover the expansion')
      ],
      5 => [
        'name' => totranslate('Perfect for 2 players'),
        'nobeginner' => true,
        'tmdisplay' => totranslate('setup Perfect for 2 players'),
      ],
      6 => [
        'name' => totranslate('Again and again'),
        'nobeginner' => true,
        'tmdisplay' => totranslate('setup Again and again'),
      ],
      7 => [
        'name' => totranslate('May the forge be with you'),
        'nobeginner' => true,
        'tmdisplay' => totranslate('setup May the forge be with you'),
      ],
      8 => [
        'name' => totranslate('Versatility'),
        'nobeginner' => true,
        'tmdisplay' => totranslate('setup Versatility'),
      ],
      9 => [
        'name' => totranslate('2020 : Tournament setup 1'),
        'nobeginner' => true,
        'tmdisplay' => totranslate('2020 : Tournament setup 1'),
      ],
      10 => [
        'name' => totranslate('2020 : Tournament setup 2'),
        'nobeginner' => true,
        'tmdisplay' => totranslate('2020 : Tournament setup 2'),
      ],
      11 => [
        'name' => totranslate('2021 : Challenge of the gods'),
        'nobeginner' => true,
        'tmdisplay' => totranslate('2021 : Challenge of the gods'),
      ],
      12 => [
        'name' => totranslate('2021 : Challenge of the gods - Final'),
        'nobeginner' => true,
        'tmdisplay' => totranslate('2021 : Challenge of the gods - Final'),
      ],
    ],
    'startcondition' => [
      1 => [
        [
          'type' => 'otheroptionisnot',
          'id' => 102,
          'value' => '2',
          'message' => totranslate('You cannot use this option without a module (necessary to match Beginner cards)'),
        ],
      ],
      99 => [
        [
          'type' => 'otheroptionisnot',
          'id' => 102,
          'value' => '2',
          'message' => totranslate('You cannot use this option without a module (necessary to match Advanced cards)'),
        ],
      ],
      2 => [],
      3 => [],
      4 => [
        [
          'type' => 'otheroptionisnot',
          'id' => 102,
          'value' => '1',
          'message' => totranslate('You cannot use this option with the extension disabled'),
        ],
        [
          'type' => 'otheroptionisnot',
          'id' => 102,
          'value' => '2',
          'message' => totranslate(
            'You cannot use this option without a module (as the recommended sets are linked to a module)'
          ),
        ],
      ],
      5 => [
        [
          'type' => 'otheroptionisnot',
          'id' => 102,
          'value' => '1',
          'message' => totranslate('You must enable Rebellion with "Cards only" option'),
        ],
        [
          'type' => 'otheroptionisnot',
          'id' => 102,
          'value' => '3',
          'message' => totranslate('You must enable Rebellion with "Cards only" option'),
        ],
        [
          'type' => 'otheroptionisnot',
          'id' => 102,
          'value' => '4',
          'message' => totranslate('You must enable Rebellion with "Cards only" option'),
        ],
      ],
      6 => [
        [
          'type' => 'otheroptionisnot',
          'id' => 102,
          'value' => '1',
          'message' => totranslate('You must enable Rebellion with "Cards only" option'),
        ],
        [
          'type' => 'otheroptionisnot',
          'id' => 102,
          'value' => '3',
          'message' => totranslate('You must enable Rebellion with "Cards only" option'),
        ],
        [
          'type' => 'otheroptionisnot',
          'id' => 102,
          'value' => '4',
          'message' => totranslate('You must enable Rebellion with "Cards only" option'),
        ],
      ],
      7 => [
        [
          'type' => 'otheroptionisnot',
          'id' => 102,
          'value' => '1',
          'message' => totranslate('You must enable Rebellion with "Cards only" option'),
        ],
        [
          'type' => 'otheroptionisnot',
          'id' => 102,
          'value' => '3',
          'message' => totranslate('You must enable Rebellion with "Cards only" option'),
        ],
        [
          'type' => 'otheroptionisnot',
          'id' => 102,
          'value' => '4',
          'message' => totranslate('You must enable Rebellion with "Cards only" option'),
        ],
      ],
      8 => [
        [
          'type' => 'otheroptionisnot',
          'id' => 102,
          'value' => '1',
          'message' => totranslate('You must enable Rebellion with "Cards only" option'),
        ],
        [
          'type' => 'otheroptionisnot',
          'id' => 102,
          'value' => '3',
          'message' => totranslate('You must enable Rebellion with "Cards only" option'),
        ],
        [
          'type' => 'otheroptionisnot',
          'id' => 102,
          'value' => '4',
          'message' => totranslate('You must enable Rebellion with "Cards only" option'),
        ],
      ],
      9 => [
        [
          'type' => 'otheroptionisnot',
          'id' => 102,
          'value' => '1',
          'message' => totranslate('You must enable Rebellion with "Cards only" option'),
        ],
        [
          'type' => 'otheroptionisnot',
          'id' => 102,
          'value' => '3',
          'message' => totranslate('You must enable Rebellion with "Cards only" option'),
        ],
        [
          'type' => 'otheroptionisnot',
          'id' => 102,
          'value' => '4',
          'message' => totranslate('You must enable Rebellion with "Cards only" option'),
        ],
      ],
      10 => [
        [
          'type' => 'otheroptionisnot',
          'id' => 102,
          'value' => '1',
          'message' => totranslate('You must enable Rebellion with "Cards only" option'),
        ],
        [
          'type' => 'otheroptionisnot',
          'id' => 102,
          'value' => '3',
          'message' => totranslate('You must enable Rebellion with "Cards only" option'),
        ],
        [
          'type' => 'otheroptionisnot',
          'id' => 102,
          'value' => '4',
          'message' => totranslate('You must enable Rebellion with "Cards only" option'),
        ],
      ],
      11 => [
        [
          'type' => 'otheroptionisnot',
          'id' => 102,
          'value' => '1',
          'message' => totranslate('You must enable Rebellion with "Cards only" option'),
        ],
        [
          'type' => 'otheroptionisnot',
          'id' => 102,
          'value' => '3',
          'message' => totranslate('You must enable Rebellion with "Cards only" option'),
        ],
        [
          'type' => 'otheroptionisnot',
          'id' => 102,
          'value' => '4',
          'message' => totranslate('You must enable Rebellion with "Cards only" option'),
        ],
        [
          'type' => 'maxplayers',
          'value' => 2,
          'message' => totranslate('This setup is for a 2 players game only'),
        ],
        [
          'type' => 'minplayers',
          'value' => 2,
          'message' => totranslate('This setup is for a 2 players game only'),
        ],
      ],
      12 => [
        [
          'type' => 'otheroptionisnot',
          'id' => 102,
          'value' => '1',
          'message' => totranslate('You must enable Rebellion with "Cards only" option'),
        ],
        [
          'type' => 'otheroptionisnot',
          'id' => 102,
          'value' => '3',
          'message' => totranslate('You must enable Rebellion with "Cards only" option'),
        ],
        [
          'type' => 'otheroptionisnot',
          'id' => 102,
          'value' => '4',
          'message' => totranslate('You must enable Rebellion with "Cards only" option'),
        ],
        [
          'type' => 'maxplayers',
          'value' => 2,
          'message' => totranslate('This setup is for a 2 players game only'),
        ],
        [
          'type' => 'minplayers',
          'value' => 2,
          'message' => totranslate('This setup is for a 2 players game only'),
        ],
      ],
    ],
  ],
  103 => [
    'name' => totranslate('Tiebreaker'),
    'values' => [
      0 => ['name' => totranslate('No')],
      1 => ['name' => totranslate('Yes'), 'nobeginner' => true, 'tmdisplay' => totranslate('with tiebreaker')],
    ],
  ],
  102 => [
    'name' => totranslate('Dice Forge Rebellion'),
    'values' => [
      1 => ['name' => totranslate('Off')],
      2 => ['name' => totranslate('Cards only'), 'nobeginner' => true, 'tmdisplay' => totranslate('extensions card')],
      3 => ['name' => totranslate('Module Titan'), 'nobeginner' => true, 'tmdisplay' => totranslate('Titan\'s module')],
      4 => [
        'name' => totranslate('Module Goddess'),
        'nobeginner' => true,
        'tmdisplay' => totranslate('Goddess\' module'),
      ],
    ],
    'disable' => true,
  ],
  101 => [
    'name' => totranslate('Promo cards'),
    'values' => [
      0 => ['name' => totranslate('No')],
      1 => ['name' => totranslate('Yes'), 'nobeginner' => true, 'tmdisplay' => totranslate('with Promo cards')],
    ],
    'displaycondition' => [
      [
        'type' => 'otheroption',
        'id' => 100,
        'value' => [2, 3],
      ],
    ],
  ],
  104 => [
      'name' => totranslate('Remove 6 gold die face'),
      'values' => [
        0 => ['name' => totranslate('No')],
        1 => ['name' => totranslate('Yes')],
      ],
      'displaycondition' => [
          [
            'type' => 'maxplayers',
            'value' => 2,
          ],
          [
            'type' => 'minplayers',
            'value' => 2,
          ],
      ],
  ],
];

$game_preferences = [
  100 => [
    'name' => totranslate('Active player action buttons'),
    'needReload' => true, // after user changes this preference game interface would auto-reload
    'values' => [
      // 1 => array( 'name' => totranslate( 'Classic' ), 'cssPref' => 'notation_classic' ),
      1 => ['name' => totranslate('With')],
      2 => ['name' => totranslate('Without')],
    ],
  ],
];
