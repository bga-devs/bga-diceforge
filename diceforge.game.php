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
  * diceforge.game.php
  *
  * This is the main file for your game logic.
  *
  * In this PHP file, you are going to defines the rules of the game.
  *
  */

require_once( APP_GAMEMODULE_PATH.'module/table/table.game.php' );
require_once ('modules/tokens.php');

class diceforge extends Table
{
    const MAX_GOLD            = 12;
    const MAX_FIRESHARD       = 6;
    const MAX_MOONSHARD       = 6;
    const MAX_ANCIENTSHARD    = 6;
    const CHEST_GOLD          = 4;
    const CHEST_FIRESHARD     = 3;
    const CHEST_MOONSHARD     = 3;
    const HAMMER_MAX_POSITION = 30;
    const RC_NOTHING_TODO     = -1;
    const RC_RESSOURCE        = 1;
    const RC_FORGESHIP        = 2;
    const RC_ACTION_CHOICE    = 3;
    const RC_SIDE_CHOICE      = 4;
    const RC_MAZE             = 5;
    const RC_MISFORTUNE       = 6;
    private $players_info;

    function __construct( )
    {
        // Your global variables labels:
        //  Here, you can assign labels to global variables you are using for this game.
        //  You can use any number of global variables with IDs between 10 and 99.
        //  If your game has options (variants), you also have to associate here a label to
        //  the corresponding ID in gameoptions.inc.php.
        // Note: afterwards, you can get/set the global variables with getGameStateValue/setGameStateInitialValue/setGameStateValue
        parent::__construct();
        self::initGameStateLabels( array(
                "firstPlayerId"          => 10,
                "diceThrows"             => 11,
                "nbPlayers"              => 12,
                "nbTurns"                => 13,
                "turnCount"              => 14,
                "turnPlayerId"           => 15, // afin de stocker a qui c'est le tour
                "currentPlayerNum"       => 16,
                "secondActionTaken"      => 17,
                "nbThrows"               => 18,
                "exploitBought"          => 19,
                "oustedPlayerId"         => 20,
                "exploitRemainingThrows" => 21,
                "enigmaDieNumber"        => 22,
                "monoRessourceChoice"    => 23,
                "isGameSetup"            => 24,
                "slotBeingDrafted"       => 25,
                "magicSeagullCount"      => 26,
                "magicSeagullActive"     => 27,
                "celestialDieSide"       => 28,
                "eternalFire"            => 29,
                "hasOusted"              => 30,
                "celestialRunning"       => 31,
                "scepterFireshard"       => 32,
                "scepterMoonshard"       => 33,
                "forgeClassical"         => 34,
                "mazeForge"              => 35,
                "doubleCelestialRoll"    => 36,
                "mazeForcePath"          => 37,
                "mazeFinished"           => 38,
                "firstFinish"            => 39,
                "timeGolem"              => 40,
                "goddessChoice"          => 41,
                "oracleReinforcement"    => 42,
                "usedScepterFireshard"   => 43,
                "usedScepterMoonshard"   => 44,
                "deckOption"             => 100,
                "promoCards"             => 101,
                "rebellion"              => 102,
                "tieBreaker"             => 103,
                "remove6G"               => 104,
                'cornucopia'             => 105,
                'diceTower'              => 106,
        ) );

        // Card init
        $this->exploits = self::getNew( "module.common.deck" );
        $this->exploits->init( "exploit" );

        // Dice & pool init
        $this->sides = self::getNew( "module.common.deck" );
        $this->sides->init( "sides" );

        // Tokens
        $this->tokens = new Tokens();
    }

    protected function getGameName( )
    {
        // Used for translations and stuff. Please do not modify.
        return "diceforge";
    }

    /*
        setupNewGame:

        This method is called only once, when a new game is launched.
        In this method, you must setup the game according to the game rules, so that
        the game is ready to be played.
    */
    protected function setupNewGame( $players, $options = array() )
    {
        // Set the colors of the players with HTML color code
        // The default below is red/green/blue/orange/brown
        // The number of colors defined here must correspond to the maximum number of players allowed for the gams
        $gameinfos = self::getGameinfos();
        $default_colors = $gameinfos['player_colors'];
        $tokensToInit = array();

        // Deck management
        $deckOption = $this->getGameStateValue( "deckOption");
        $promoCards = $this->getGameStateValue( "promoCards");
        $rebellion = $this->getGameStateValue( "rebellion");
        $cornucopia = $this->getGameStateValue( "cornucopia");
        $diceTower =  $this->getGameStateValue( "diceTower");

        // Create players
        // Note: if you added some extra field on "player" table in the database (dbmodel.sql), you can initialize it there.
        $sql = "INSERT INTO player (player_id, player_color, player_canal, player_name, player_avatar, player_score) VALUES ";
        $values = array();
        foreach( $players as $player_id => $player )
        {
            $color = array_shift( $default_colors );
            $values[] = "('".$player_id."','$color','".$player['player_canal']."','".addslashes( $player['player_name'] )."','".addslashes( $player['player_avatar'] )."',0)";

            // player tokens init
            // triple / throw 1 / throw 2
            $tokensToInit[] = array ('key' => 'triple_' . $player_id, 'nbr' => 1, 'state' => 0);
            $tokensToInit[] = array ('key' => 'throw1_' . $player_id, 'nbr' => 1, 'state' => 0);
            $tokensToInit[] = array ('key' => 'throw2_' . $player_id, 'nbr' => 1, 'state' => 0);
            $tokensToInit[] = array ('key' => 'mirror1_' . $player_id, 'nbr' => 1, 'state' => 0);
            $tokensToInit[] = array ('key' => 'mirror2_' . $player_id, 'nbr' => 1, 'state' => 0);
            $tokensToInit[] = array ('key' => 'gold_' . $player_id, 'nbr' => 1, 'state' => 0);
            $tokensToInit[] = array ('key' => 'vp_' . $player_id, 'nbr' => 1, 'state' => 0);
            $tokensToInit[] = array ('key' => 'moonshard_' . $player_id, 'nbr' => 1, 'state' => 0);
            $tokensToInit[] = array ('key' => 'fireshard_' . $player_id, 'nbr' => 1, 'state' => 0);
            $tokensToInit[] = array ('key' => 'ancientshard_' . $player_id, 'nbr' => 1, 'state' => 0);
            $tokensToInit[] = array ('key' => 'loyalty_' . $player_id, 'nbr' => 1, 'state' => 0);
            $tokensToInit[] = array ('key' => 'cerberus_' . $player_id, 'nbr' => 1, 'state' => 0);
            $tokensToInit[] = array ('key' => 'resolveTwin_' . $player_id, 'nbr' => 1, 'state' => 0);
            $tokensToInit[] = array ('key' => 'twinChoice_' . $player_id, 'nbr' => 1, 'state' => 0);

            // if goddess
            if ($rebellion == 4) {
                $tokensToInit[] = array ('key' => 'position_' . $player_id, 'nbr' => 1, 'state' => 1);
                $tokensToInit[] = array ('key' => 'mazestock_' . $player_id, 'nbr' => 1, 'state' => 0);
                $tokensToInit[] = array ('key' => 'mazechoice_' . $player_id, 'nbr' => 1, 'state' => 0, 'location' => "0");
                $tokensToInit[] = array ('key' => 'puzzle_' . $player_id, 'nbr' => 1, 'state' => 0);
            }
            // module Titan
            elseif ($rebellion == 3) {
                $tokensToInit[] = array ('key' => 'position_' . $player_id, 'nbr' => 1, 'state' => 16);
                $tokensToInit[] = array ('key' => 'movetitan_' . $player_id, 'nbr' => 1, 'state' => 0);
            }


        }

        $tokensToInit[] = array ('key' => 'celestial_choice', 'nbr' => 1, 'state' => 0, 'location' => '0');

        // if goddess
        if ($rebellion == 4) {
            $tokensToInit[] = array ('key' => 'treasure_fireshard', 'nbr' => 1, 'state' => 0);
            $tokensToInit[] = array ('key' => 'treasure_moonshard', 'nbr' => 1, 'state' => 0);
            $tokensToInit[] = array ('key' => 'treasure_vp', 'nbr' => 1, 'state' => 0);
        }
        // if Titan
        elseif ($rebellion == 3) {
            // misfortune token to know who needs to resolve Misfortune
            // Will store the player that needs to resolve the misfortune : state : player_id, location = linked card
            $tokensToInit[] = array ('key' => 'resolveMisfortune', 'nbr' => 1, 'state' => 0);
            // will store the sides to resolve
            $tokensToInit[] = array ('key' => 'misfortune_1', 'nbr' => 1, 'state' => 0);
            $tokensToInit[] = array ('key' => 'misfortune_2', 'nbr' => 1, 'state' => 0);
            $tokensToInit[] = array ('key' => 'stateMisfortune', 'nbr' => 1, 'state' => 0);


        }

        $tokensToInit[] = array ('key' => 'wheel_1', 'nbr' => 1, 'state' => 0);
        $tokensToInit[] = array ('key' => 'wheel_2', 'nbr' => 1, 'state' => 0);
        $tokensToInit[] = array ('key' => 'pegasus_remaining', 'nbr' => 1, 'state' => 0);
        $tokensToInit[] = array ('key' => 'pegasus_player', 'nbr' => 1, 'state' => 0);


        $sql .= implode( $values, ',' );
        self::DbQuery( $sql );
        self::reattributeColorsBasedOnPreferences( $players, $gameinfos['player_colors'] );
        self::reloadPlayersBasicInfos();

        $this->tokens->createTokens($tokensToInit, "none");

        $players_turn = $this->getNextPlayerTable();
        $this->setGameStateInitialValue( "firstPlayerId", $players_turn["0"] );
        /************ Start the game initialization *****/
        $nb_players = count($players);
        $this->setGameStateInitialValue( "nbPlayers", $nb_players);
        $this->setGameStateInitialValue( "isGameSetup", 1 );

        // creation of objects for setup
        $setup_cards = $this->getExploitsMaterial($nb_players);

        $slot_taken = array();

        if ($rebellion == 3 || $rebellion == 4) {
            if ($rebellion == 3)
                $mandatory_deck = $setup_cards['titan_mandatory'];
            elseif ($rebellion == 4)
                $mandatory_deck = $setup_cards['goddess_mandatory'];

            foreach ($mandatory_deck as $island => $cards) {
                $test = 0;
                $c = array();
                $slot_taken[$island] = $island;
                if (substr($cards[0]['type'], -10) == 'Misfortune') {
                    // we need to put all available boar
                    foreach ($cards as $pos => $card)
                        if (substr($card['type'], -10) == 'Misfortune' && $test < $nb_players) {
                            $c[] = $card;
                            $test++;
                        }
                }
                elseif (substr($cards[0]['type'], -6) == 'Memory') {
                    // we need to put all available memory
                    foreach ($cards as $pos => $card)
                        if (substr($card['type'], -6) == 'Memory' && $test < $nb_players) {
                            $c[] = $card;
                            $test++;
                        }
                }
                else
                    $c[0] = $cards[0];
                $this->exploits->createCards($c, $island);
            }
        }

        $available_cards = $this->getAvailableExploits($setup_cards);

        // not draft
        if ($deckOption != 3) {
            foreach ($available_cards as $island => $cards) {
                $hydraPromo = ['harpy', 'chimera', 'monsterMother', 'shadowgoddess'];

                if (in_array($island, $slot_taken))
                    continue ;
                // random
                if ($deckOption == 2) {
                    $toPick = bga_rand(0, count($cards)-1);
                }
                else {
                    $toPick = 0;
                }
                $test = 0;
                $c = array();
                if (substr($cards[$toPick]['type'], -4) == 'Boar') {
                    // we need to put all available boar
                    foreach ($cards as $pos => $card)
                        if (substr($card['type'], -4) == 'Boar' && $test < $nb_players) {
                            $c[] = $card;
                            $test++;
                        }
                }
                elseif ($cards[$toPick]['type'] == 'hydraPromo') {
                    foreach ($hydraPromo as $pos => $hydPm)
                        if ($test < $nb_players) {
                            $c = array();
                            $c[] = array('type' => $hydPm, 'type_arg' => 0, 'nbr' => 1);
                            $this->exploits->createCards($c, $island);
                            $test++;
                        }
                }
                else
                    $c[0] = $cards[$toPick];

                if ($cards[$toPick]['type'] != 'hydraPromo')
                    $this->exploits->createCards($c, $island);

            }

            if ($cornucopia == 1) {
                $this->exploits->createCards([['type' => 'cornucopia', 'type_arg' => 0, 'nbr' => 1]], 2);
            }
            if ($diceTower == 1) {
                $this->exploits->createCards([['type' => 'diceTower', 'type_arg' => 0, 'nbr' => 1]], 6);
            }
        }
        // draft
        else {
            // we create only where 1 possibility + not created
            $this->setGameStateValue( "isGameSetup", 0 );
            foreach ($available_cards as $island => $cards) {
                if (in_array($island, $slot_taken))
                    continue ;
                // if we only have one card available for the slot, no need to draft
                if (count($cards) == 1) {
                    $this->exploits->createCards($cards, $island);
                }
            }
        }

        // Initialisation of the pools
        $remove6G = $this->getGameStateValue( "remove6G") == 1;
        foreach ($this->sides_init as $pool_number => $content) {
            $sides = array();
            foreach ($content as $side_info) {
                if ($side_info != 'G6' || ($side_info == 'G6' && !$remove6G))
                    $sides[]= array('type' => $side_info, 'type_arg' => '', 'nbr' => '1');
            }
            if ($sides)
                $this->sides->createCards($sides, $pool_number);
        }

        // Initialisation of the dice for each player
        foreach($this->loadPlayersBasicInfos() as $player_id => $players) {
            // titan
            if ($rebellion == 3) {
                // dice 1
                $sides = array();
                $sides[]= array('type' => "AS1", 'type_arg' => '', 'nbr' => '1');
                $sides[]= array('type' => "G1", 'type_arg' => '','nbr' => '5');
                $this->sides->createCards($sides, "dice1-p". $player_id);

                // dice 2
                $sides = array();
                $sides[]= array('type' => "MS1", 'type_arg' => '', 'nbr' => '1');
                $sides[]= array('type' => "L1V1", 'type_arg' => '', 'nbr' => '1');
                $sides[]= array('type' => "G1", 'type_arg' => '','nbr' => '4');
                $this->sides->createCards($sides, "dice2-p". $player_id);
            }
            // Goddess
            elseif ($rebellion == 4) {
                // dice 1
                $sides = array();
                $sides[]= array('type' => "FS1", 'type_arg' => '', 'nbr' => '1');
                $sides[]= array('type' => "G1", 'type_arg' => '','nbr' => '5');
                $this->sides->createCards($sides, "dice1-p". $player_id);

                // dice 2
                $sides = array();
                $sides[]= array('type' => "moonGolem", 'type_arg' => '', 'nbr' => '1');
                $sides[]= array('type' => "V2", 'type_arg' => '', 'nbr' => '1');
                $sides[]= array('type' => "G1", 'type_arg' => '','nbr' => '4');
                $this->sides->createCards($sides, "dice2-p". $player_id);
            }
            else {
                // dice 1
                $sides = array();
                $sides[]= array('type' => "FS1", 'type_arg' => '', 'nbr' => '1');
                $sides[]= array('type' => "G1", 'type_arg' => '','nbr' => '5');
                $this->sides->createCards($sides, "dice1-p". $player_id);

                // dice 2
                $sides = array();
                $sides[]= array('type' => "MS1", 'type_arg' => '', 'nbr' => '1');
                $sides[]= array('type' => "V2", 'type_arg' => '', 'nbr' => '1');
                $sides[]= array('type' => "G1", 'type_arg' => '','nbr' => '4');
                $this->sides->createCards($sides, "dice2-p". $player_id);
            }
        }

        // set of correct location arg
        $sql="UPDATE sides SET card_location_arg = card_location_arg-1 WHERE card_location LIKE 'dice%'";
        self::DbQuery( $sql );

        // Init gold ressources for players
        // 3 for 1st, 2 for 2nd, 1 for 3rd
        $sql="UPDATE player SET res_gold=3 where player_id =".$players_turn["0"];
        self::DbQuery( $sql );
        $player_init= $players_turn["0"];
        for($i=1; $i<$nb_players;$i++) {
            $sql="UPDATE player SET res_gold=".(3-$i)." where player_id =".$this->getPlayerAfter($player_init);
            self::DbQuery( $sql );
            $player_init = $this->getPlayerAfter($player_init);
        }


        // ######## Two players mode
        // if we are two players, two throws are made each time
        // deletion of two sides for each pool
        if ($nb_players == 2) {
            $this->setGameStateInitialValue( "diceThrows", 2);
            $this->setGameStateInitialValue( "nbTurns", 9 );
            for ($i = 1; $i <= 10; $i++) {
                $del = $this->sides->getCardsInLocation($i, null, 'card_id');
                if (count($del) == 4) {
                    $throw = bga_rand(0, count($del)-1);
                    $this->sides->moveCard($del[$throw]['id'], "discard");
                }

                $del = $this->sides->getCardsInLocation($i, null, 'card_id');
                if (count($del) == 3) {
                    $throw = bga_rand(0, count($del)-1);
                    $this->sides->moveCard($del[$throw]['id'], "discard");
                }
            }
        } else {
            $this->setGameStateInitialValue( "diceThrows", 1);

            if ($nb_players == 3)
                $this->setGameStateInitialValue( "nbTurns", 10 );
            else
                $this->setGameStateInitialValue( "nbTurns", 9 );
        }

        // Initiatlisation of tokens if available:
        $this->initTokens();

        $this->setGameStateInitialValue( "turnCount", 1 );
        $this->setGameStateInitialValue( "enigmaDieNumber", -1 );
        $this->setGameStateInitialValue( "exploitBought", -1 );
        $this->setGameStateInitialValue( "monoRessourceChoice", 0);
        $this->setGameStateInitialValue( "magicSeagullCount", 0);
        $this->setGameStateInitialValue( "magicSeagullActive", 0);
        $this->setGameStateInitialValue( "celestialDieSide", 0);
        $this->setGameStateValue('oustedPlayerId', -1);


        // INIT GAME STATISTIC
        $all_stats = $this->getStatTypes();
        $player_stats = $all_stats ['player'];

        foreach ( $player_stats as $key => $value ) {
            if (substr($key, 0, 3)== 'nb_') {
                $this->initStat('player', $key, 0);
            }
        }

        $this->initStat('player', 'turns_number', 0);
        $this->initStat('table', 'turns_number', 0);
        // Activate first player (which is in general a good idea :) )
        //$this->activeNextPlayer();
        /************ End of the game initialization *****/
    }

    /*
        getAllDatas:

        Gather all informations about current game situation (visible by the current player).

        The method is called each time the game interface is displayed to a player, ie:
        _ when the game starts
        _ when a player refreshes the game page (F5)
    */
    protected function getAllDatas()
    {
        $result = array( 'players' => array(), 'counters' => array() );

        $current_player_id = self::getCurrentPlayerId();    // !! We must only return informations visible by this player !!

        // Get information about players
        // Note: you can retrieve some extra field you added for "player" table in "dbmodel.sql" if you need it.
        $sql = "SELECT player_id id, player_score score, hammer_position, position, player_color color, player_name name, triton_token triton, cerberus_token cerberus, hammer_auto FROM player ";
        $result['players']           = self::getCollectionFromDb( $sql );
        $result['counters']          = $this->getPlayersRessources();
        $result['secondActionTaken'] = $this->getGameStateValue('secondActionTaken');
        $result['turnPlayerId']      = $this->getGameStateValue('turnPlayerId');
        $result['exploitTypes']      = $this->exploit_types;
        $result['dice_sides']        = $this->dice_sides;
        $result['nbTurns']           = self::getGameStateValue( 'nbTurns' );
        $result['turnCount']         = self::getGameStateValue( 'turnCount' );
        $result['remainingTurns']    = self::getGameStateValue( 'nbTurns' ) - self::getGameStateValue( 'turnCount' ) + 1;
        $result['turnOrder']         = $this->getTableOrder();
        $result['exploits']          = [];
        $result['powerTokens']       = [];
        $result['sides_init']        = $this->sides_init;
        $result['initPools']         = $this->pools;
        $result['celestial']         = $this->getCelestial();
        $result['celestialInfo']     = $this->celestialInfo;
        $result['convertedScepter']  = ['fire' => self::getGameStateValue('scepterFireshard'), 'moon' => self::getGameStateValue('scepterMoonshard')];
        // Exploit info (hand or position)
        foreach($this->exploits->countCardsInLocations() as $location => $number) {
            foreach($this->exploits->getCardsInLocation($location) as $card) {
                    $result['exploits'][$location][$card['id']]=$card;
            }
        }

        // Sides information
        foreach($this->sides->countCardsInLocations() as $location => $number) {
            if(substr($location, 0, 2)!== 'di'  && $location !== 'forging') {
                foreach($this->sides->getCardsInLocation($location) as $card) {
                        $result['sides'][$location][]=$card;
                }
            }
        }

        // Dice for current player
        foreach($result['players'] as $player_id => $player) {
            // Dice 1
            foreach($this->sides->getCardsInLocation('dice1-p'.$player_id, null, 'card_location_arg') as $card) {
                if(!isset($result['playersDice'][$player_id]['dice1']))
                    $result['playersDice'][$player_id]['dice1'] = array();

                $result['playersDice'][$player_id]['dice1'][] = $card;
            }
            // Dice 2
            foreach($this->sides->getCardsInLocation('dice2-p'.$player_id, null, 'card_location_arg') as $card) {
                if(!isset($result['playersDice'][$player_id]['dice2']))
                    $result['playersDice'][$player_id]['dice2'] = array();

                $result['playersDice'][$player_id]['dice2'][] = $card;
            }

            // ajouter le nb de marteaux de chaque joueur (pour renseigner le JS)
            $result['players'][$player_id]['nbHammer'] = $this->hasActiveHammer( $player_id );
            $result['players'][$player_id]['remainingHammer'] = ceil($this->remainingHammer( $player_id ) / self::HAMMER_MAX_POSITION);
            $player_chests = $this->hasChest( $player_id );
            $result['players'][$player_id]['gold_max'] = self::MAX_GOLD + ( self::CHEST_GOLD * $player_chests );
            $result['players'][$player_id]['moon_max'] = self::MAX_MOONSHARD + ( self::CHEST_MOONSHARD * $player_chests );
            $result['players'][$player_id]['fire_max'] = self::MAX_FIRESHARD + ( self::CHEST_FIRESHARD * $player_chests );

            $result['powerTokens'][$player_id] = $this->tokens->getTokensOfTypeInLocation(null, $player_id);
        }

        $result['rebellion'] = $this->getGameStateValue( "rebellion");
        $result['hasCelestial'] = count($this->exploits->getCardsOfType("celestial")) > 0 ? true : false;
        if ($result['rebellion'] >= 3) {
            $result['zones'] = $this->tokens->getTokensOfTypeInLocation('position_%');
        }

        if ($result['rebellion'] == 3) {
            $result['titanPassives'] = $this->titan_passives;
            $result['memoryTokens'] = $this->tokens->getTokensOfTypeInLocation('%Memory%_');
        }


        if ($result['rebellion'] == 4) {
            $result['treasures'] = $this->tokens->getTokensOfTypeInLocation('treasure_%');
            $result['maze'] = $this->maze;
        }

        return $result;
    }

    function getTableOrder() {
        $table  = $this->getNextPlayerTable();
        $result = array();

        $nb_players = count($table) - 1;
        $previous = $table[0];

        for ($i = 1 ; $i <= $nb_players ; $i++ ) {
            $result[ $previous ] = $i;
            $previous = $table[ $previous ];
        }

        return $result;
    }

    function debugTest() {
        print_r($this->getNextPlayerTable());
    }

    /*
        Get the ressources from the table
        @return : array for the counters
    */
     function getPlayersRessources( $player_id = null ) {

        $counters = [];
        $rebellion = $this->getGameStateValue( "rebellion");

        $sql = "SELECT player_id id, res_fire fire, res_moon moon, res_gold gold, res_ancient ancient, hammer_position%" . ( self::HAMMER_MAX_POSITION / 2 ) ." as hammer_position, player_score FROM player";
        if ( $player_id ) {
            $sql.= " WHERE player_id = " . (int) $player_id;
        }

        $query_arr = self::getNonEmptyCollectionFromDB( $sql );

        foreach ($query_arr as $player_id => $player) {
            $current_player = $player['id'];

            $counters["goldcount_p".$current_player] = [
                'counter_name' => "goldcount_p".$current_player,
                'counter_value' => $player['gold'],
            ];
            $counters["mooncount_p".$current_player] = [
                'counter_name' => "mooncount_p".$current_player,
                'counter_value' => $player['moon'],
            ];
            $counters["firecount_p".$current_player] = [
                'counter_name' => "firecount_p".$current_player,
                'counter_value' => $player['fire'],
            ];

            if ($rebellion == 3) {
                $counters["ancientshardcount_p".$current_player] = [
                    'counter_name'  => "ancientshardcount_p".$current_player,
                    'counter_value' => $player['ancient'],
                ];
            }

            if ($this->canFillHammer($player_id))
                $counters["hammercount_p".$current_player] = [
                    'counter_name'  => "hammercount_p".$current_player,
                    'counter_value' => $player['hammer_position'],
                ];

            $counters["player_score_".$current_player] = [
                'counter_name'  => "player_score_".$current_player,
                'counter_value' => $player['player_score'],
            ];

            $counters["discarded_sides_p".$current_player] = [
                'counter_name'  => "discarded_sides_p".$current_player,
                'counter_value' => count($this->sides->getCardsInLocation('discard', $player_id)),
            ];

            $counters["card_counter_p".$current_player] = [
                'counter_name'  => "card_counter_p".$current_player,
                'counter_value' => count($this->exploits->getCardsInLocation('table-' . $current_player))
                                + count($this->exploits->getCardsInLocation('pile1-' . $current_player))
                                + count($this->exploits->getCardsInLocation('pile2-' . $current_player))
                                + count($this->exploits->getCardsInLocation('pile3-' . $current_player)),
            ];
        }

        foreach ($this->tokens->getTokensOfTypeInLocation('scepter') as $key => $token) {
            if ($token['location'] != 'deck') {
                $counters["count$key"] = [
                    'counter_name'  => "count$key",
                    'counter_value' => $token['state']
                ];
            }
        }

        return $counters;
    }

    /*
     * give the number of reinforcement cards available for a player
     */
    function getPlayerReinforcements( $player_id )
    {
        $data = array();

        if ( $nb = $this->hasHind( $player_id ) )
            $data['doe'] = $nb;

        if ( $nb = $this->hasElder( $player_id ) )
            $data['ancient'] = $nb;

        if ( $nb = $this->hasOwl( $player_id ) )
            $data['owl'] = $nb;

        return $data;
    }

    /*
        getGameProgression:

        Compute and return the current game progression.
    */
    function getGameProgression()
    {
        $nbTurns            = self::getGameStateValue('nbTurns');
        $nbPlayers          = self::getGameStateValue('nbPlayers');
        $turnCount          = self::getGameStateValue('turnCount');
        // condition is required, for draft
        $currentPlayerNum   = self::getGameStateValue('currentPlayerNum') == 0 ? 1 : self::getGameStateValue('currentPlayerNum');
        $progressionTurn    = 100 / $nbTurns;
        $progressionPlayer  = $progressionTurn / $nbPlayers;
        $currentProgression = ( $turnCount - 1 ) * $progressionTurn + ( ( $currentPlayerNum - 1 ) * $progressionPlayer );

        return $currentProgression;
        //return 90;
    }


//////////////////////////////////////////////////////////////////////////////
//////////// Utility functions
////////////

    function getSideIdFromType($side)
    {
        $sql = "SELECT card_id FROM sides WHERE card_type = '$side' LIMIT 1";

        return $this->getUniqueValueFromDB($sql);
    }

    function hasMazeStock($player_id = null)
    {
        $sql = "SELECT DISTINCT 1 FROM token WHERE token_state != 0 AND token_key LIKE 'mazestock_%'";

        if ($player_id !== null)
            $sql .= " AND token_key = 'mazestock_" . $player_id . "'";

        $dbres = self::getUniqueValueFromDB($sql);

        if ($this->getGameStateValue("timeGolem") != 0)
            return true;

        if ($dbres == 0)
            return false;
        else
            return true;
    }

    function generateActionChoiceArgs($player_id, $sides)
    {
        $players_info = $this->getPlayersAdditionnalInfo();

        if ($this->getGameStateValue('celestialRunning')){
            // twins are available and reroll possible of celestial die
             $sides[$player_id]['action'] = 'actionChoice';
             $sides[$player_id]['celestialRunning'] = true;
             $sides[$player_id]['celestialDie'] = $this->getCelestial();
             if (!$this->tokens->getTokenState("cerberus_$player_id"))
                $sides[$player_id]['twins'] = $this->canUseTwins($player_id);
             else
                $sides[$player_id]['twins'] = 0;
        }
        elseif ($players_info[$player_id]['side_choice_1'] != "0" || $players_info[$player_id]['side_choice_2'] != "0") {
            //$i = 0;
            $sides[$player_id]['action'] = 'actionChoice';
            if (!$this->tokens->getTokenState("cerberus_$player_id"))
                $sides[$player_id]['twins'] = $this->canUseTwins($player_id);
            else
                $sides[$player_id]['twins'] = 0;
            if ($players_info[$player_id]['side_choice_1'] != '0')
                $sides[$player_id]['sides'][0] = $players_info[$player_id]['side_choice_1'];

            if ($players_info[$player_id]['side_choice_2'] != '0')
                $sides[$player_id]['sides'][1] = $players_info[$player_id]['side_choice_2'];
        }
        else {
            // The player has a cerberus token
            if ($this->hasCerberusToken($player_id)) {
                $sides[$player_id]['action'] = 'cerberusToken';
            }
        }
        return $sides;
    }

    function generateMazeChoiceArgs($player_id, $sides)
    {
        // do we manage a ressource choice?
        if ($this->tokens->getTokenLocation("mazechoice_" . $player_id) != "0") {
            $sides[$player_id]['action'] = 'mazeRessource';
            $sides[$player_id]['sides'][0] = array('id' => '', 'type' => $this->tokens->getTokenLocation("mazechoice_" . $player_id), 'num' => 99);
            $sideNum = 99;
            //var_dump(7);
            $sides[$player_id]['possibilities'] = $this->calculatePossibilities($player_id, $sides[$player_id]['sides'][0]['type'], $sideNum, "");
        }
        elseif ($this->maze[$this->tokens->getTokenState("position_" . $player_id)]['reward'] == "treasure"
                    && count($this->tokens->getTokensOfTypeInLocation("treasure%", $this->tokens->getTokenState("position_" . $player_id))) == 0 ){
            $sides[$player_id]['action'] = 'mazeTreasure';
            $sides[$player_id]['avTreasure'] = $this->tokens->getTokensOfTypeInLocation("treasure%", "none");
        }
        elseif ($this->tokens->getTokenState("mazestock_" . $player_id) != 0 && $this->tokens->getTokenState("puzzle_" . $player_id) && !$this->getGameStateValue("mazeForcePath")) {
            $sides[$player_id]['action'] = 'mazePuzzle';
        }
        elseif ((count($this->maze[$this->tokens->getTokenState("position_" . $player_id)]['path']) > 1 && $this->tokens->getTokenState("mazestock_" . $player_id) > 0) ||
                (count($this->maze[$this->tokens->getTokenState("position_" . $player_id)]['reverse']) > 1 && (/*$this->tokens->getTokenState("mazestock_" . $player_id) + */$this->getGameStateValue("timeGolem")) < 0)) {
        //else {
            $sides[$player_id]['action'] = 'maze';
            if ($this->getGameStateValue("timeGolem") < 0)
                $way = $this->maze[$this->tokens->getTokenState("position_" . $player_id)]['reverse'];
            //if ($this->tokens->getTokenState("mazestock_" . $player_id) > 0)
            else
                $way = $this->maze[$this->tokens->getTokenState("position_" . $player_id)]['path'];

            $mazePath = [];
            foreach ($way as $i) {
                $mazePath[$i] = $this->maze[$i]['reward'];

                //if ($this->maze[$i]['reward'] == 'treasure') {
                //    if (count($this->tokens->getTokensOfTypeInLocation("treasure%", $i)) == 1) {
                //        $tok = $this->tokens->getTokensOfTypeInLocation("treasure%", $i);
                //        $type = array_keys($tok)[0];
                //        switch ($type) {
                //            case "treasure_fireshard":
                //                $trez = "1 [FS]";
                //                break ;
                //            case "treasure_moonshard":
                //                $trez = "1 [MS]";
                //                break ;
                //            case "treasure_vp":
                //                $trez = "2 [VP]";
                //                break ;
                //        }
                //        $sides[$player_id]['mazeTreasure'] = $trez;
                //    }
                //}

                if ($this->maze[$i]['reward'] == 'treasure') {
                   // if reward on path is a treasure, we need to define what it is
                   // was it taken and set, or not?
                   $tok = $this->tokens->getTokensOfTypeInLocation("treasure%", $i);
                   $type = array_keys($tok)[0] ?? 'nothing';

                   switch ($type) {
                       case 'treasure_fireshard':
                           $trez = '[maze-fs1]';
                           break ;
                       case 'treasure_moonshard':
                           $trez = '[maze-ms1]';
                           break ;
                       case 'treasure_vp':
                           $trez = '[maze-vp2]';
                           break ;
                       default:
                           $trez = '[mtreasure]';
                           break;
                   }

                   $sides[$player_id]['mazeTreasure'][$i] = $trez;
                }

            }
            $sides[$player_id]['mazePath'] = $mazePath;
        }
        else {
            $sides[$player_id]['action'] = 'mazeConfirm';
            $sides[$player_id]['reward'] = $this->maze[$this->tokens->getTokenState("position_" . $player_id)]['reward'];
        }

        return $sides;
    }

    function resetThrowTokens($player_id = null)
    {
        if ($player_id == null) {
            $sql = "UPDATE token SET token_state = 0 WHERE (
                token_key LIKE 'triple%' or
                token_key LIKE 'throw%' or
                token_key LIKE 'mirror%' or
                token_key LIKE 'gold%' or
                token_key LIKE 'vp%' or
                token_key LIKE 'fireshard%' or
                token_key LIKE 'moonshard%' or
                token_key LIKE 'cerberus%' or
                token_key LIKE 'puzzle%'
            )";
        }
        else {
            $sql = "UPDATE token SET token_state = 0 WHERE (
                token_key LIKE 'triple%$player_id' or
                token_key LIKE 'throw%$player_id' or
                token_key LIKE 'mirror%$player_id' or
                token_key LIKE 'gold%$player_id' or
                token_key LIKE 'vp%$player_id' or
                token_key LIKE 'fireshard%$player_id' or
                token_key LIKE 'moonshard%$player_id' or
                token_key LIKE 'cerberus%$player_id' or
                token_key LIKE 'puzzle%$player_id'
            )";
        }

        self::dbQuery($sql);
        self::notifyAllPlayers("notifThrowToken", "",
                array(
                )
            );
    }


    // Will check that the "tobe forged" die face in the correct pool (from the die face to be replaced)
    // return : nothing, exception is thrown if not possible
    function checkUpgrade($nbUpgrade, $old_side_id, $new_side_id, $bonus = 0, $arrSelected = null)
    {
         $pool = 0;
         $bigPool = 0;

         // check number of increased pool
        $old_side_type = $this->sides->getCard($old_side_id)['type'];
        $new_side_type = $this->sides->getCard($new_side_id)['type'];
        $new_side_location = $this->sides->getCard($new_side_id)['location'];

        if ($arrSelected == null)
            $arrSelected = array();

        foreach ($this->sides_init as $i => $pools) {
            if (in_array($old_side_type, $pools)) {
                $pool = $i;
                break ;
            }
        }

        // definition of bigger pool
        if ($pool == 0)
            $bigPool = 0;
        else {
            foreach ($this->pools as $i => $pools) {
                if (in_array($pool, $pools)) {
                    $bigPool = $i;
                    break ;
                }
            }
        }

        $sourceBigPool = $bigPool;

        $bigPool += $nbUpgrade + $bonus;
        if ($bigPool > 7)
            $bigPool = 7;

        $newBonus = $bonus;
        $nb = 0;
        if ($bonus == 0) {
            for ($i = $sourceBigPool + 1; $i <= $bigPool; $i++) {
                $nb = 0;

                foreach ($this->pools[$i] as $poolTo => $poolNumber) {
                    $nb += $this->sides->countCardInLocation($poolNumber);
                }
                if ($nb != 0)
                    $arrSelected[] = $i;
                //if ($nb == 0)
                //    $newBonus++;
                //else
                //    $arrSelected[] = $i;
            }
        }

        // if empty slots, recursive
        if ($bonus != $newBonus) {
            $this->checkUpgrade($nbUpgrade, $old_side_id, $new_side_id, $newBonus, $arrSelected);
            return ;
        }

        if ($bonus != 0)
            $arrSelected[] = $bigPool;

        $nb = 0;
        foreach ($this->pools[$bigPool] as $i => $poolNumber) {
            $nb += $this->sides->countCardInLocation($poolNumber);
        }

        if ($nb == 0 && $bigPool < 7) {
            $this->checkUpgrade($nbUpgrade, $old_side_id, $new_side_id, $bonus +1);
            return  ;
        }
        elseif ($nb == 0 && $bigPool == 7)
            throw new BgaVisibleSystemException("No upgrade is possible");
        // temp exception

        // check new side matches
        $found = false;

        foreach ($arrSelected as $i => $bigPoolNumber) {
            if (in_array($new_side_location, $this->pools[$bigPoolNumber]))
                $found = true;
        }

        //if (!in_array($new_side_location, $this->pools[$bigPool]))
        if ($found == false)
            throw new BgaVisibleSystemException("The side is not the pool matching the upgrade");
        // temp exception

        return ;

        //$pools = array(0, 2, 3, 4, 5, 6, 8, 12);
        //$pools_info = array ( 2 => array (1, 2),
        //                      3 => array (3, 4),
        //                      4 => array (5),
        //                      5 => array (6),
        //                      6 => array (7),
        //                      8 => array (8, 9),
        //                      12 => array (10)
        //                    );
        //// check number of increased pool
        //$old_side_type = $this->sides->getCard($old_side_id)['type'];
        //$new_side_type = $this->sides->getCard($new_side_id)['type'];
        //
        //$old_cost = $this->dice_sides[$old_side_type]['cost'];
        //$new_cost = $this->dice_sides[$new_side_type]['cost'];
        //
        //$pos = array_search($old_cost, $pools);
        //$new_pos = $pos + $nbUpgrade;
        //
        //// If pool empty => can take next one
        //$found = false;
        //do {
        //    foreach ($pools_info[$new_pos] as $pos => $pool) {
        //        if (count($this->sides->countCardsByLocationArgs($pool)) != 0) {
        //            $found = true;
        //        }
        //    }
        //
        //    if (!$found) {
        //        $newpos++;
        //        if (!isset($pools[$new_pos]))
        //            throw new feException("You cannot upgrade to this level this die face");
        //    }
        //} while (!$found);
        //
        //if ($new_cost > $pools[$new_pos])
        //    throw new feException("The side is not the pool matching the upgrade");
        //
    }

    function initNotif($player_id)
    {
        $notifPlayerArgs                  = array();
        $notifPlayerArgs['player_id']     = $player_id;
        $notifPlayerArgs['player_name']   = $this->loadPlayersBasicInfos()[$player_id]['player_name'];
        $notifPlayerArgs['gold']          = 0;
        $notifPlayerArgs['vp']            = 0;
        $notifPlayerArgs['moonshard']     = 0;
        $notifPlayerArgs['fireshard']     = 0;
        $notifPlayerArgs['ancientshard']  = 0;
        $notifPlayerArgs['loyalty']       = 0;
        $notifPlayerArgs['hammer']        = 0;
        $notifPlayerArgs['scepter']       = 0;
        $notifPlayerArgs['maze']          = 0;
        $notifPlayerArgs['choice']        = false;

        return $notifPlayerArgs;
    }

    function possibilityAddText ($possibilities)
    {
        foreach ($possibilities as $pos => $possibility) {
                            $text = "";
                            foreach ($possibility as $key => $value)
                                if ($value != 0 && $key != 'text' && $key != 'num')
                                        $text .= $value . $key . ' ';

                                $possibilities[$pos]['text'] = $text;
        }
        return $possibilities;
    }

    // return only gold, as it is used for specific cards
    function getGoldResource($player_id, $type = 'all')
    {
        $gold = 0;
        $player_info = $this->getPlayersAdditionnalInfo()[$player_id];
        $gold = $player_info['res_gold'];

        return $gold;
    }

    function getNextVPPool()
    {
        for ($i=1; $i <= 11; $i++) {
            $sides = $this->sides->getCardsInLocation($i);
            foreach ($sides as $card_id => $card) {
                //throw new feException(print_r($this->dice_sides[$card['type']]['ressource']));
                if (isset($this->dice_sides[$card['type']]['ressource']['vp']))
                    return $i;
            }
        }
        return null;
    }

    // return pools that cannot be bought with cards in the game
    function getTridentSides()
    {
        $sql = "SELECT card_type, COUNT(*) FROM exploit GROUP BY card_type";
        $poolList = [1, 2, 3, 4, 5, 6, 7, 8, 9, 10]; // 11, 12, 13, 14, 15, 16, 17, 18, 19, 20];

        $res = self::getCollectionFromDB($sql, true);

        if (!isset($res['mirror']))
            $poolList[] = 11;
        if (!isset($res['shield']))
            $poolList[] = 12;
        if (!isset($res['ship']))
            $poolList[] = 13;
        if (!isset($res['redBoar']) && !isset($res['yellowBoar']) && !isset($res['greenBoar']) && !isset($res['blueBoar']))
            $poolList[] = 14;
        if (!isset($res['invisible']))
            $poolList[] = 15;
        //if ($this->getGameStateValue('rebellion') > 1) {
        //    if (!isset($res['blueMisfortune']) && !isset($res['redMisfortune']) && !isset($res['greenMisfortune']) && !isset($res['yellowMisfortune']))
        //        $poolList[] = 16;
        //    if (!isset($res['sunGolem']))
        //        $poolList[] = 17;
        //    if (!isset($res['chaos']))
        //        $poolList[] = 18;
        //    if (!isset($res['moonGolem']))
        //        $poolList[] = 19;
        //    if (!isset($res['dogged']))
        //        $poolList[] = 20;
        //}

        return $poolList;
    }

    function initTokens()
    {
        // companion
        // scepter
        $token_to_init = array ('companion', 'scepter');
        foreach ($token_to_init as $ind => $value) {
            $sql = "select concat(card_type, '_', card_id) AS 'key', '1' AS 'nbr', 'deck' AS 'location', '0' AS 'state' FROM exploit WHERE card_type = '$value'";
            $res = self::getObjectListFromDB($sql);

            if (count($res) != 0)
                $this->tokens->createTokens($res, "none");
        }

    }

    function calculateTieBreaker()
    {
         // tie breaker implementation
        $tiebreaker = $this->getGameStateValue( "tieBreaker");
        if ($tiebreaker == 1) {
            $tiebreak = array ("sides", "exploit", "resources", "1st");
            $sql = "SELECT player_score FROM player GROUP BY player_score HAVING COUNT(player_score) > 1";
            $scores = self::getObjectListFromDB($sql, true);

            foreach ($scores as $score) {
                $sql = "SELECT player_id FROM player WHERE player_score = $score";
                $tied_players = self::getObjectListFromDB($sql, true);

                foreach ($tiebreak as $act) {
                    $test = array();
                    $dupli = false;
                    $players_info = $this->getPlayersAdditionnalInfo();

                    $sql = "SELECT player_score_aux FROM player WHERE player_score = $score group by player_score_aux having count(player_score_aux) > 1";
                    $aux = self::getObjectListFromDB($sql, true);

                    foreach ($aux as $score_aux) {
                        $sql = "SELECT player_id FROM player WHERE player_score_aux = $score_aux and player_score = $score";
                        $players_being_tied = self::getObjectListFromDB($sql, true);

                        foreach ($players_being_tied as $player_id) {
                            switch ($act) {
                                case 'sides':
                                    $sql = "UPDATE player SET player_score_aux = 300 + " . count($this->sides->getCardsInLocation('discard', $player_id)) . " WHERE player_id = $player_id";
                                    self::dbQuery($sql);
                                    break ;
                                case "exploit":
                                    $sql = "UPDATE player SET player_score_aux = 200 + (SELECT count(1) FROM exploit WHERE card_location like '%-$player_id') WHERE player_id = $player_id";
                                    self::dbQuery($sql);
                                    break ;
                                case "resources":
                                    $sql = "UPDATE player SET player_score_aux = 100 + res_fire + res_moon + res_gold WHERE player_id = $player_id";
                                    self::dbQuery($sql);
                                    break ;
                                case "1st":
                                    $sql = "UPDATE player SET player_score_aux = 100 - player_no WHERE player_id = $player_id";
                                    self::dbQuery($sql);
                                    break ;
                            }
                        }
                    }
                }
            }
            $sql = "UPDATE player SET player_score_aux = 0 WHERE player_score_aux = 300 OR player_score_aux = 200 OR player_score_aux = 100";
            self::dbQuery($sql);
        }
    }

    /*
        In this space, you can put any utility methods useful for your game logic
    */

    function getExploitsMaterial($nb_players)
    {
        $setup_cards = array();

        foreach ($this->decks as $type => $cards) {
            foreach ($cards as $name) {
                $card = $this->exploit_types[$name];
                if ((substr($name, -4) == 'Boar') || (substr($name, -10) == 'Misfortune') || (substr($name, -6) == 'Memory')) {
                    if (!isset($setup_cards[$type][$card['position']]) || count($setup_cards[$type][$card['position']]) <= $nb_players)
                        $setup_cards[$type][$card['position']][] = array('type' => $name, 'type_arg' => '0', 'nbr' => 1);
                }
                elseif ($type == 'promo' && $card['position'] == 'M8')
                    $setup_cards[$type][$card['position']][] = array('type' => $name, 'type_arg' => '0', 'nbr' => 1);
                else
                    $setup_cards[$type][$card['position']][] = array('type' => $name, 'type_arg' => '0', 'nbr' => $nb_players);
            }
        }

        return $setup_cards;
    }

    function getAvailableExploits($setup_cards)
    {
        $deckOption = $this->getGameStateValue("deckOption");
        $promoCards = $this->getGameStateValue("promoCards");
        $rebellion = $this->getGameStateValue("rebellion");

        // beginner
        if ($deckOption == 1)
            $available_cards = $setup_cards['beginner'];
        // random without promo
        elseif ($deckOption == 2 && $promoCards == 0)
            $available_cards = $setup_cards['random'];
        // Random with promo
        elseif ($deckOption == 2 && $promoCards == 1)
            $available_cards = array_merge_recursive($setup_cards['random'], $setup_cards['promo']);
        // draft with promo
        elseif ($deckOption == 3 && $promoCards == 1)
            $available_cards = array_merge_recursive($setup_cards['random'], $setup_cards['promo']);
        // draft without promo
        elseif ($deckOption == 3 && $promoCards == 0)
            $available_cards = $setup_cards['random'];
        // alternate
        elseif ($deckOption == 99)
            $available_cards = $setup_cards['alternate'];
        // recommended set
        elseif ($deckOption == 4) {
            if ($rebellion == 3)
                $available_cards = $setup_cards['titan_reco'];
            elseif ($rebellion == 4)
                $available_cards = $setup_cards['goddess_reco'];
        }
        //"2players" => a
        //"bisRepetita" =
        //"forge" => arra
        //"polyvalence" =
        //"tournament1" =
        //"tournament2"
        // 2 players
        elseif ($deckOption == 5) {
            $available_cards = $setup_cards['2players'];
        }
        // Bis Repetita
        elseif ($deckOption == 6) {
            $available_cards = $setup_cards['bisRepetita'];
        }
        // Forge
        elseif ($deckOption == 7) {
            $available_cards = $setup_cards['forge'];
        }
        // Polyvalence
        elseif ($deckOption == 8) {
            $available_cards = $setup_cards['polyvalence'];
        }
        // Tournament 1
        elseif ($deckOption == 9) {
            $available_cards = $setup_cards['tournament1'];
        }
        // Tournament 2
        elseif ($deckOption == 10) {
            $available_cards = $setup_cards['tournament2'];
        }
        // Challenge of the gods
        elseif ($deckOption == 11) {
            $available_cards = $setup_cards['challenge2021'];
        }
        // Challenge of the gods Final
        elseif ($deckOption == 12) {
            $available_cards = $setup_cards['challenge2021final'];
        }

        // add rebellion cards to the random/draft (also with modules)
        if ($rebellion == 2 || (($rebellion == 3 || $rebellion == 4) && ($deckOption == 2 || $deckOption == 3)))
            $available_cards = array_merge_recursive($available_cards, $setup_cards['rebellion_cards']);

        return $available_cards;
    }

    function draftSlot()
    {
        $slot_to_draft = $this->exploit_slot;

        for ($i=0; $i < count($this->exploit_slot); $i++) {
            $slot = array_rand($slot_to_draft);

            if ($this->exploits->countCardInLocation($slot_to_draft[$slot]) == 0)
                return $slot_to_draft[$slot];

            unset($slot_to_draft[$slot]);
        //if (($key = array_search($slot, $slot_to_draft)) !== false) {
        //        unset($slot_to_draft[$key]);
        //    }
        }

        /*foreach ($this->exploit_slot as $num => $slot) {
            if ($this->exploits->countCardInLocation($slot) == 0) {
                return $slot;
            }
        }*/
        return null;
    }

    /*
     * Function that roll a dice
     * @return the side that has been rolled
     */
    function rollDice( $player_id, $dice_num )
    {
        // do not use the shuffle function
        $value = bga_rand(0, 5);
        if ($value != 0) {
            $old_side = $this->sides->getCardsInLocation('dice'.$dice_num .'-p'.$player_id, 0);
            $old_side = reset($old_side);

            $new_side = $this->sides->getCardsInLocation('dice'.$dice_num .'-p'.$player_id, $value);
            $new_side = reset($new_side);

            $this->sides->moveCard($old_side['id'], 'dice'.$dice_num .'-p'.$player_id, $value);
            $this->sides->moveCard($new_side['id'], 'dice'.$dice_num .'-p'.$player_id, 0);
        }

        $this->incStat(1, 'nb_throws', $player_id);
        // Reset returns the first item of an array
        $result = $this->sides->getCardsInLocation('dice'.$dice_num .'-p'.$player_id, 0);
        $this->dbUpdateThrow($player_id, $dice_num, reset($result)['id']);
        return reset($result);

    }

    function getCelestial()
    {
        $value = $this->getGameStateValue( "celestialDieSide");
        return $this->celestialDie[$value];
    }

    function rollCelestial($player_id, $roll = true)
    {
        $notifPlayerArgs = $this->initNotif($player_id);
        if ($roll) {
            $value = bga_rand(0, 5);
            $this->setGameStateValue( "celestialDieSide", $value);
            $this->setGameStateValue( "celestialRunning", 1);

            // debug
            // $this->celestialDie = array ("G12", "V5", "V3fFS1orMS1", "celestialMirror", "chooseSide", "doubleUpgrade");
            //  $value = 4;
            //  $this->setGameStateInitialValue( "celestialDieSide", $value);

            $side = $this->celestialDie[$value];
            $side_info = $this->dice_sides[$side];

            self::notifyAllPlayers("notifCelestialRoll", clienttranslate('${player_name} rolls the Celestial Die → ${side_type}'),
                array(
                    'player_name' => $this->loadPlayersBasicInfos()[$player_id]['player_name'],
                    'side_type'   => $side,
                    'sideCelestial' => $side
                )
            );

            if ($this->canUseTwins($player_id)) {
                // can be rerolled
                $this->dbSetChoice($player_id, self::RC_ACTION_CHOICE);
                return null;
            }
        }

        // we consider that the player do not want to reroll the die so we flag all the twins as used
        if (!$roll) {
            $this->resetTwins($player_id, true);
        }
        $side = $this->getCelestial();
        $notifPlayerArgs = $this->affectRessource($player_id, $notifPlayerArgs, 98, $side, false, 1, false,  false, false);

        if ($notifPlayerArgs['choice'] == true) {
            $this->tokens->moveToken("celestial_choice", $side);
        }
        else {
        	#30177
            $notifPlayerArgs = $this->checkPuzzle($notifPlayerArgs);
        }
        //throw new feException($this->tokens->getTokenLocation("celestial_choice"));
        //throw new feException(print_r($notifPlayerArgs));
        switch ($side) {
            case 'celestialMirror':
                //$this->dbSetSideChoice($player_id, 1, -1);
                $this->tokens->moveToken("celestial_choice", "celestialMirror");
                $this->dbSetChoice($player_id, self::RC_SIDE_CHOICE);
                $notifPlayerArgs['choice'] = true;
                break ;
            case 'chooseSide':
                //$this->dbSetSideChoice($player_id, 1, -1);
                //$this->dbSetSideChoice($player_id, 2, -1);
                $this->tokens->moveToken("celestial_choice", "chooseSide");
                $this->dbSetChoice($player_id, self::RC_SIDE_CHOICE);
                $notifPlayerArgs['choice'] = true;
                break ;
            case 'doubleUpgrade':
                $notifPlayerArgs['choice'] = true;
                break ;
        }

        if (!($notifPlayerArgs['gold'] == 0 && $notifPlayerArgs['vp'] == 0 && $notifPlayerArgs['moonshard'] == 0 && $notifPlayerArgs['fireshard'] == 0 && $notifPlayerArgs['hammer'] == 0  && $notifPlayerArgs['scepter'] == 0  && $notifPlayerArgs['maze'] == 0 && $notifPlayerArgs['loyalty'] == 0 && $notifPlayerArgs['ancientshard'] == 0))
        {
            // petit hack
            if ( $notifPlayerArgs['vp'] == 0 )
            {
                unset( $notifPlayerArgs['vp'] );
            }
            $notifPlayerArgs['ressources'] = $this->buildRessourceNotif( $notifPlayerArgs );
            self::notifyAllPlayers("notifBlessing", clienttranslate('${player_name} gets ${ressources}'), $notifPlayerArgs );
        }
        else
            self::notifyAllPlayers("notifBlessing", '', $notifPlayerArgs);

        if (isset($notifPlayerArgs['notifHammer']) && count($notifPlayerArgs['notifHammer']) != 0) {
            $this->generateNotifHammer($player_id, $notifPlayerArgs['notifHammer']);
        }

        if ($notifPlayerArgs['choice'] == false) {
            $this->setGameStateValue( "celestialRunning", 0);
            $this->resolveTwin ($player_id, true);

        }


        return $side;

    }

    function getRessourcesToTransform()
    {
        $transform = array('ressource' => array(''), 'vp' => 1);
        $card_id = $this->getGameStateValue( "exploitBought");
        if ($card_id == -1) {
            //  throw new BgaUserException("Cheater exception : you are not processing an exploit");
            return array('ressource' => array(), 'vp' => 1);
        }
        $card = $this->exploits->getCard($card_id);
        $card_info = $this->exploit_types[$card['type']];

        switch($card_info['action']) {
            case 'fullThrow2Transform':
                $transform = array('ressource' => array('fireshard', 'moonshard'), 'vp' => 2);
                break ;
            case '4ThrowsTransform':
                $transform = array('ressource' => array('gold'), 'vp' => 1);
                break ;
        }

        return $transform;
    }

    /*
     * Cartesian product
     * input = array('gold' => array(0,1,2), 'hammer' => array(1,2,3))
     * @return : array of array with the values
     */
    function cartesian($input)
    {
        // filter out empty values
        //$input = array_filter($input);

        $result = array(array());

        foreach ($input as $key => $values) {
            $append = array();
            $values = array_unique($values, SORT_NUMERIC);

            foreach($result as $product) {
                foreach($values as $item) {
                    $product[$key] = $item;
                    $append[] = $product;
                }
            }

            $result = $append;
        }

        return $result;
    }

    function filterCartesian($input, $maxValue, $vpDivide = 1, $hasTransformed, $loyaltyBonus = 0)
    {
        $retour = array();
        foreach($input as $key => $values) {
            $add = 0;
            foreach ($values as $res => $value) {
                if ($res == '[VP]' && $hasTransformed)
                    $add += $value / $vpDivide;
                else
                    $add += $value;
            }

            if ($add == ($maxValue + $loyaltyBonus))
                $retour[] = $values;
        }
        return $retour;
    }

    function formatCartesian($cartesian, $possibilities, $sideNum, $combine = false)
    {
        $iteration = 1;
        $pos = 0;
        $possibility_new = array();
        if ($combine) {
            $iteration = count ($possibilities);
            if ($iteration == 0) {
                $combine = false;
                $iteration = 1;
            }
        }

        for($i = 0; $i<$iteration; $i++) {
            foreach ($cartesian as $key => $values) {
                if ($combine)
                    $possibility_new[$pos] = $possibilities[$i];
                else
                    $possibility_new[$pos] = array("[G]" => 0, "[H]" => 0, "[FS]" => 0, "[MS]" => 0, "[VP]" => 0, "[AS]" => 0, "[M]" => 0, "[L]" => 0, 'text' => '', 'num' => $sideNum);

                if (isset($values['[G]']) && $values['[G]'] != 0)
                    $possibility_new[$pos]['[G]'] = $values['[G]'];

                if (isset($values['[H]']) && $values['[H]'] != 0)
                    $possibility_new[$pos]['[H]'] = $values['[H]'];

                if (isset($values['[FS]']) && $values['[FS]'] != 0)
                    $possibility_new[$pos]['[FS]'] = $values['[FS]'];

                if (isset($values['[MS]']) && $values['[MS]'] != 0)
                    $possibility_new[$pos]['[MS]'] = $values['[MS]'];

                if (isset($values['[VP]']) && $values['[VP]'] != 0)
                    $possibility_new[$pos]['[VP]'] = $values['[VP]'];

                if (isset($values['[AS]']) && $values['[AS]'] != 0)
                    $possibilities[$pos]['[AS]'] = $values['[AS]'];

                if (isset($values['[L]']) && $values['[L]'] != 0)
                    $possibilities[$pos]['[L]'] = $values['[L]'];

                if (isset($values['[M]']) && $values['[M]'] != 0)
                    $possibilities[$pos]['[M]'] = $values['[M]'];


                $pos++;
            }
        }

        return $possibility_new;
    }

    function calculatePossibilities($player_id, $sideType, $sideNum, $cardAction, $triple = 1, $misfortune = false)
    {
        // var_dump($player_id, $sideType, $sideNum, $cardAction, $triple);
        $side_definition = $this->dice_sides[$sideType];
        $remainingHammer = $this->remainingHammer($player_id);
        $possibilities = [];
        $hasHammer = $this->canFillHammer($player_id);
        $hasTransformed = false;
        $processOthers = false;

        if ($sideNum < 3 && $this->gamestate->state()['name'] == 'exploitRessource' && !$misfortune)
            $toTransform = $this->getRessourcesToTransform();
        else
            $toTransform = array('ressource' => array(''), 'vp' => 1);

        //$ressourceToText = array ('gold' => '[G]', 'moonshard' => '[MS]', 'fireshard' => '[FS]', 'hammer' => '[H]', 'vp' => '[VP]');
        $ressourceToText = array ('gold' => '[G]', 'moonshard' => '[MS]', 'fireshard' => '[FS]', 'hammer' => '[H]', 'vp' => '[VP]', 'loyalty' => '[L]', 'ancientshard' => '[AS]', 'maze' => '[M]');

        if($side_definition['type'] == 'simple') {
            $pos = 0;
            if (count(array_intersect(array_keys($side_definition['ressource']), $toTransform['ressource'])) != 0) {
                $processOthers = true;
            }

            //foreach ($side_definition['ressource'] as $res => $value) {
                // only gold with hammer or transform gold
                if (isset($side_definition['ressource']['gold']) && $side_definition['ressource']['gold'] != 0 && ($hasHammer || in_array('gold', $toTransform['ressource']))) {
                    if ($cardAction != 'looseThrow') {
                        $gold = $side_definition['ressource']['gold'] * $triple;

                        $ressources = array();
                        $loyaltyBonus = 0;

                        // we add loyalty possibility for the gold
                        //if (!in_array('gold', $toTransform['ressource'])) {
                            $loyaltyReward = $this->getLoyaltyArg($player_id, $sideNum, 'gold', $misfortune);
                            $gold += $loyaltyReward['gold'];
                            $ressources['[VP]'][] = $loyaltyReward['vp'];
                            $loyaltyBonus = $loyaltyReward['vp'];
                        //}

                        if ($gold - $remainingHammer < 0)
                            $incr = 0;
                        else
                            $incr = $gold - $remainingHammer;

                        //
                        for ($i=0; $i <= $gold; $i++)
                            $ressources['[G]'][] = $i;
                        for ($i=0; $i <= $gold - $incr; $i++)
                            $ressources['[H]'][] = $i;

                        if (in_array('gold', $toTransform['ressource'])) {
                            $hasTransformed = true;
                            //for ($i=0; $i <= $gold; $i++)
                                $ressources['[VP]'][] = $gold;
                                $ressources['[VP]'][] = 0;
                        }

                        //throw new BgaUserException(print_r($ressources));
                        //throw new BgaUserException(print_r($this->cartesian($ressources)));
                        $cartesian = $this->filterCartesian($this->cartesian($ressources), $gold, $toTransform['vp'], $hasTransformed, $loyaltyBonus);

                        foreach ($cartesian as $key => $values) {
                            $possibilities[$pos] = array("[G]" => 0, "[H]" => 0, "[FS]" => 0, "[MS]" => 0, "[VP]" => 0, "[AS]" => 0, "[M]" => 0, "[L]" => 0, 'text' => '', 'num' => $sideNum);
                            if (isset($values['[G]']) && $values['[G]'] != 0)
                                $possibilities[$pos]['[G]'] = $values['[G]'];

                            if (isset($values['[H]']) && $values['[H]'] != 0)
                                $possibilities[$pos]['[H]'] = $values['[H]'];

                            if (isset($values['[FS]']) && $values['[FS]'] != 0)
                                $possibilities[$pos]['[FS]'] = $values['[FS]'];

                            if (isset($values['[MS]']) && $values['[MS]'] != 0)
                                $possibilities[$pos]['[MS]'] = $values['[MS]'];

                            if (isset($values['[VP]']) && $values['[VP]'] != 0)
                                $possibilities[$pos]['[VP]'] = $values['[VP]'];

                            if (isset($values['[AS]']) && $values['[AS]'] != 0)
                                $possibilities[$pos]['[AS]'] = $values['[AS]'];

                            if (isset($values['[L]']) && $values['[L]'] != 0)
                                $possibilities[$pos]['[L]'] = $values['[L]'];

                            if (isset($values['[M]']) && $values['[M]'] != 0)
                                $possibilities[$pos]['[M]'] = $values['[M]'];

                            $pos++;
                        }
                    }
                    else {
                        $gold = $side_definition['ressource']['gold'] * $triple;
                        $possibilities[$pos] = array("[G]" => $gold, "[H]" => 0, "[FS]" => 0, "[MS]" => 0, "[VP]" => 0, 'text' => $gold . '[G]', 'num' => $sideNum );
                        $pos++;
                    }
                }
                //elseif ($processOthers && !in_array($res,$toTransform['ressource']) && $side_definition['ressource'][$res] != 0) {
                //    $value = $side_definition['ressource'][$res] * $triple;
                //    foreach ($possibilities as $key => $values) {
                //        $possibilities[$key][$ressourceToText[$res]] += $value;
                //    }
                //}

            //}
            //elseif (isset($side_definition['ressource']['gold']) && $side_definition['ressource']['gold'] != 0 ) {
            //      $gold = $side_definition['ressource']['gold'] * $triple;
            //      $possibilities[$pos] = array("[G]" => $gold, "[H]" => 0, "[FS]" => 0, "[MS]" => 0, "[VP]" => 0, 'text' => $gold . '[G]', 'num' => $sideNum );
            //      $pos++;
            //}

            $ressources = array();
            $maxValue = 0;

            //$vppos = 999;

            // TODO: update for transform
            foreach ($toTransform['ressource'] as $num => $res) {
                // do not process gold as it has been processed before
                if ($res == 'gold')
                    continue ;

                $ressources['[VP]'][0] = 0;

                if (isset($side_definition['ressource'][$res]) && $side_definition['ressource'][$res] != 0) {
                    $value = $side_definition['ressource'][$res] * $triple;
                    $maxValue += $value;
                    $vp = $toTransform['vp'];
                    $hasTransformed = true;

                    //for ($i=0; $i <= $value; $i++)
                    $ressources[$ressourceToText[$res]][] = $value;
                    $ressources[$ressourceToText[$res]][] = 0;

                    if (isset($ressources['[VP]'][1])) {
                        //$existVP = count($ressources['[VP]']);
                        //for ($i=0; $i <= $value; $i++)
                        //  $ressources['[VP]'][] = ($value * $vp) + $existVP;
                        $ressources['[VP]'][1] = ($value * $vp) + $ressources['[VP]'][1];
                    }
                    else {
                        //for ($i=0; $i <= $value; $i++)
                        $ressources['[VP]'][1] = $value * $vp;
                        //$ressources['[VP]'][] = 0;
                    }


                    if (in_array('gold', $toTransform['ressource'])) {
                        //for ($i=0; $i <= $gold; $i++)
                        $ressources['[VP]'][1] = $gold;
                        //$ressources['[VP]'][] = 0;
                    }
                }
            }
            $cartesian = $this->filterCartesian($this->cartesian($ressources), $maxValue, $toTransform['vp'], $hasTransformed);
            $possibilities = $this->formatCartesian($cartesian, $possibilities, $sideNum, true);
            //throw new feException( "process other $processOthers " );
            foreach ($side_definition['ressource'] as $res => $value) {
                if ($processOthers && !in_array($res, $toTransform['ressource']) && $value != 0 && (($res == 'gold' && !$hasHammer) || $res != 'gold')) {
                    $value = $side_definition['ressource'][$res] * $triple;
                    foreach ($possibilities as $key => $values) {
                        $possibilities[$key][$ressourceToText[$res]] += $value;
                    }
                }
            }

        }
        elseif ($side_definition['type'] == 'choice' || $side_definition['type'] == 'dependent') {
            $pos = 0;


            foreach($side_definition['ressource'] as $element => $ressource) {
                $loyaltyBonus = 0;
                $value = $ressource * $triple;
                $ressourceToText = array ('gold' => '[G]', 'moonshard' => '[MS]', 'fireshard' => '[FS]', 'hammer' => '[H]', 'vp' => '[VP]', 'loyalty' => '[L]', 'ancientshard' => '[AS]', 'maze' => '[M]');

                $ressources = array();

                if ($element == 'gold' && $hasHammer && $cardAction != 'looseThrow') {

                    //if (in_array('gold', $toTransform['ressource'])) {
                        $loyaltyReward = $this->getLoyaltyArg($player_id, $sideNum, 'gold', $misfortune);
                        $value += $loyaltyReward['gold'];
                        $ressources['[VP]'][] = $loyaltyReward['vp'];
                        $loyaltyBonus = $loyaltyReward['vp'];

                    //}

                    for ($i=0; $i <= $value; $i++)
                        $ressources[$ressourceToText[$element]][] = $i;

                    if ($value - $remainingHammer < 0)
                        $incr = 0;
                    else
                        $incr = $value - $remainingHammer;

                    for ($i=0; $i <= $value - $incr; $i++)
                        $ressources['[H]'][] = $i;

                    if (in_array($element, $toTransform['ressource'])) {
                        $hasTransformed = true;
                        //for ($i=0; $i <= $value; $i++)
                        $ressources['[VP]'][] = $value * $toTransform['vp'];
                        $ressources['[VP]'][] = 0;
                    }

                }
                else{
                    if ($cardAction != 'looseThrow') {
                        if ($element == 'gold') {
                            $ressources[$ressourceToText[$element]][] = $value + $this->getLoyaltyArg($player_id, $sideNum, 'gold', $misfortune)['gold'];
                            $value += $this->getLoyaltyArg($player_id, $sideNum, 'gold', $misfortune)['gold'];
                            $ressources['[VP]'][] = $this->getLoyaltyArg($player_id, $sideNum, 'gold', $misfortune)['vp'];
                            $loyaltyBonus = $this->getLoyaltyArg($player_id, $sideNum, 'gold', $misfortune)['vp'];

                        }
                        elseif ($element == 'vp') {
                            $ressources[$ressourceToText[$element]][] = $value + $this->getLoyaltyArg($player_id, $sideNum, 'vp', $misfortune)['vp'];
                            $value += $this->getLoyaltyArg($player_id, $sideNum, 'vp', $misfortune)['vp'];
                            //$loyaltyBonus = $this->getLoyaltyArg($player_id, $sideNum, 'vp')['vp'];
                            //throw new BgaUserException(print_r($ressources));
                            //throw new BgaUserException(print_r($this->cartesian($ressources)));
                            //throw new feException(print_r($this->filterCartesian($this->cartesian($ressources), $value, $toTransform['vp'], $hasTransformed, $loyaltyBonus)));
                        }
                        else
                            $ressources[$ressourceToText[$element]][] = $value;
                    }
                    else {
                        if ($element == 'gold') {
                            $ressources[$ressourceToText[$element]][] = $value;
                        }
                        elseif ($element == 'vp') {
                            $ressources[$ressourceToText[$element]][] = $value;
                        }
                        else
                            $ressources[$ressourceToText[$element]][] = $value;
                    }

                    if (in_array($element, $toTransform['ressource'])) {
                        $hasTransformed = true;
                        //for ($i=0; $i <= $value; $i++)
                        $ressources['[VP]'][] = $value * $toTransform['vp'];
                        $ressources['[VP]'][] = 0;
                        //for ($i=0; $i < $value; $i++)
                        $ressources[$ressourceToText[$element]][] = $value;
                        $ressources[$ressourceToText[$element]][] = 0;
                    }
                }
                //throw new BgaUserException(print_r($ressources));
                //throw new BgaUserException(print_r($this->cartesian($ressources)));
                $cartesian = $this->filterCartesian($this->cartesian($ressources), $value, $toTransform['vp'], $hasTransformed, $loyaltyBonus);

                foreach ($cartesian as $key => $values) {
                    $text = "";
                    $possibilities[$pos] = array("[G]" => 0, "[H]" => 0, "[FS]" => 0, "[MS]" => 0, "[VP]" => 0, "[AS]" => 0, "[M]" => 0, "[L]" => 0, 'text' => '', 'num' => $sideNum);
                    if (isset($values['[G]']) && $values['[G]'] != 0)
                        $possibilities[$pos]['[G]'] = $values['[G]'];

                    if (isset($values['[H]']) && $values['[H]'] != 0)
                        $possibilities[$pos]['[H]'] = $values['[H]'];

                    if (isset($values['[FS]']) && $values['[FS]'] != 0)
                        $possibilities[$pos]['[FS]'] = $values['[FS]'];

                    if (isset($values['[MS]']) && $values['[MS]'] != 0)
                        $possibilities[$pos]['[MS]'] = $values['[MS]'];

                    if (isset($values['[VP]']) && $values['[VP]'] != 0)
                        $possibilities[$pos]['[VP]'] = $values['[VP]'];

                    if (isset($values['[AS]']) && $values['[AS]'] != 0)
                        $possibilities[$pos]['[AS]'] = $values['[AS]'];

                    if (isset($values['[L]']) && $values['[L]'] != 0)
                        $possibilities[$pos]['[L]'] = $values['[L]'];

                    if (isset($values['[M]']) && $values['[M]'] != 0)
                        $possibilities[$pos]['[M]'] = $values['[M]'];

                    $pos++;
                }
            }
        }
        //throw new feException(print_r($possibilities));
        $possibilityFiltered = array();
        $listText = array();
        $index = 0;

        foreach ($possibilities as $pos => $possibility) {
            $text = "";
            foreach ($possibility as $key => $value)
                if ($value != 0 && $key != 'text' && $key != 'num')
                    if ($cardAction == 'looseThrow' && $sideNum != 3)
                        $text .= '-' . $value . $key . ' ';
                    else
                        $text .= $value . $key . ' ';

            // Only put unique values
            if (!in_array($text, $listText)) {
                $listText[] = $text;
                $possibilityFiltered[$index] =  $possibilities[$pos];
                $possibilityFiltered[$index]['text'] = $text;
                $index++;
            }
        }
        //throw new feException(print_r($possibilityFiltered));
        // throw new feException("toto");
        return $possibilityFiltered;
    }

    /*
     * Check if the less expensive available side (not yet forged) can be bought
     * @return : true if can forge, else false
     */
    function canForgeSides($player_id, $remainingGold, $alreadyBought)
    {
        $sql = "SELECT MIN(card_id) FROM sides WHERE card_location BETWEEN 1 AND 10";

        if (count($alreadyBought) != 0)
            $sql .= " AND card_type NOT IN ('" . implode("','", $alreadyBought) . "')";

        $side_id  = self::getUniqueValueFromDB($sql);

        if ($side_id == null)
            return false;

        $side = $this->sides->getCard($side_id);
        $tritonGold = $this->hasTritonToken($player_id) * 6;

        // Scepters gold included in remaining gold
        if (($remainingGold + $tritonGold) < $this->dice_sides[$side['type']]['cost'])
            return false;
        else
            return true;
    }

    /*
     * List the "type" of sides that can be forged only through an available exploit
     */
    function listSidesWithExploits()
    {
        $sql = "SELECT DISTINCT card_type FROM exploit";
        $garden = array ('shield', 'triple', 'mirror', 'ship', 'boar');
        $exploits = self::getObjectListFromDB($sql, true);
        $info = array();

        foreach ($garden as $i => $gard) {
            switch ($gard) {
                case 'triple':
                    if (in_array('invisible', $exploits))
                        $info[] = $gard;
                    break ;
                case 'boar':
                    if (in_array('redBoar', $exploits) || in_array('greenBoar', $exploits) || in_array('yellowBoar', $exploits) || in_array('blueBoar', $exploits)) {
                        //$info[] = $gard;
                        $info[] = 'redBoar';
                        $info[] = 'yellowBoar';
                        $info[] = 'greenBoar';
                        $info[] = 'blueBoar';
                    }
                    break ;
                case 'shield':
                    if (in_array($gard, $exploits)) {
                        $info[] = 'redShield';
                        $info[] = 'yellowShield';
                        $info[] = 'greenShield';
                        $info[] = 'blueShield';
                    }
                    break ;
                default:
                    if (in_array($gard, $exploits))
                        $info[] = $gard;
            }
        }

        return $info;
    }

    /*
     * Give the next state in fonction of ressource choice field in the player table
     *
     */
    function getNextState($ressourceChoice = null)
    {
        $stateName = $this->gamestate->state()['name'];
        $endMultiState = "";

        switch ($stateName) {
            case 'ressourceChoice':
            case 'forgeShip':
                $endMultiState = 'blessing';
                break;
            case 'doeRessourceChoice':
            case 'doeForgeShip':
                $endMultiState = 'nextState';
                break;
            case 'playerOustingChoice':
            case 'oustedForgeShip':
                $endMultiState = 'nextState';
                break;
            case 'exploitRessource':
            case 'exploitForgeShip':
            case 'exploitEffect':
                $endMultiState = "nextState";
                break;
            case 'playerOusting':
            case 'exploitForgeBoar':
                if ($this->tokens->getTokenState('pegasus_remaining') != 0) {
                    $endMultiState = 'pegasus';
                } else {
                    $endMultiState = 'exploitEffect';
                }
                break;
        }

        return $endMultiState;
    }

    function generateNotifLoyalty($notifPlayerArgs) {
        if (isset($notifPlayerArgs['notifLoyalty'])) {
            $notifLoyalty = $notifPlayerArgs['notifLoyalty'];

            if (!($notifLoyalty['gold'] == 0 && $notifLoyalty['vp'] == 0 && $notifLoyalty['hammer'] == 0)) {
                // petit hack
                if ( $notifLoyalty['vp'] == 0 )
                {
                    unset( $notifLoyalty['vp'] );
                }
                $notifLoyalty['ressources'] = $this->buildRessourceNotif( $notifLoyalty );
                self::notifyAllPlayers("notifMessage", clienttranslate('${player_name} gets ${ressources} (Loyalty effect)'), $notifLoyalty );
            }
        }
    }

    function getTitanReward ($player_id, $card_type) {
        $uniqueMessage = _('${player_name} gets ${ressources} from the Titans for being the first to do accomplish this exploit');
        $firstMessage = _('${player_name} gets ${ressources} from the Titans for accomplishing this exploit for the first time');
        $message = "";
        // based on position
        if ($this->getGameStateValue( "rebellion") == 3) {
            $position = $this->tokens->getTokenState("position_" . $player_id);
            $reward = $this->titan_board[$position]['bonus'];

            $uniqueVP = 5;
            $firstVP = 3;

            // no Titan reward
            if ($position >= 13)
                return ;

            $notif = $this->initNotif($player_id);

            if ($reward == 'titanPassive2') {
                $uniqueVP = 3;
                $firstVP = 2;
            }

            if (substr($card_type, -4) == 'Boar')
                $card_type = 'Boar';
            elseif (substr($card_type, -10) == 'Misfortune')
                $card_type = 'Misfortune';
            elseif (substr($card_type, -6) == 'Memory')
                $card_type = 'Memory';

            // get total bought by players
            $sql = "SELECT count(*) FROM exploit WHERE card_type LIKE '%$card_type%' and card_location NOT LIKE 'M%' AND card_location NOT LIKE 'F%'";
            $totalAlreadyBought = self::getUniqueValueFromDB($sql) - 1;

            $sql = "SELECT count(*) FROM exploit WHERE card_type LIKE '%$card_type%' and card_location LIKE '%$player_id%'";
            $playerBought = self::getUniqueValueFromDB($sql) - 1;

            // if 1st to buy a card max VP
            if ($totalAlreadyBought == 0) {
                $this->increaseVP($player_id, $uniqueVP);
                $notif['vp'] = $uniqueVP;
                $message = $uniqueMessage;
            }
            // If 1st time for player,
            elseif ($playerBought == 0) {
                $this->increaseVP($player_id, $firstVP);
                $notif['vp'] = $firstVP;
                $message = $firstMessage;
            }

            $notif['ressources'] = $this->buildRessourceNotif( $notif );
            self::notifyAllPlayers("notifBlessing", $message, $notif );

        }

        return ;
    }

    function getLoyaltyReward ($player_id, $sideNum, $resourceType, $notifPlayerArgs) {
        if ($this->getGameStateValue( "rebellion") == 3) {
            if ($this->isThrown ($player_id, $sideNum)) {
                $position = $this->tokens->getTokenState("position_" . $player_id);
                $reward = $this->titan_board[$position]['bonus'];

                // Nothing to do for Loyalty before this
                if ($position <= 19)
                    return $notifPlayerArgs;

                if (!isset($notifPlayerArgs['notifLoyalty']))
                    $notifLoyalty = $this->initNotif ($player_id);
                else
                    $notifLoyalty = $notifPlayerArgs['notifLoyalty'];
                switch ($reward) {
                    case "loyaltyPassive1":
                        if ($resourceType == 'gold') {
                            // automatic hammer
                            if ($this->canFillHammer($player_id) && $this->hasAutoHammer($player_id)) {
                                $notifPlayerArgs['notifHammer'][] = $this->increaseGold($player_id, 1, 'hammer');
                                $notifLoyalty['hammer'] = $notifLoyalty['hammer'] + 1;
                            }
                            else {
                                $scepter = $this->increaseGold($player_id, 1, 'scepter');
                                $this->increaseGold($player_id, 1 - $scepter);

                                $notifLoyalty['gold'] = $notifLoyalty['gold'] + 1 - $scepter;
                                $notifLoyalty['scepter'] = $notifLoyalty['scepter'] + $scepter;
                            }
                        }
                        elseif ($resourceType == 'hammer') {
                            $notifPlayerArgs['notifHammer'][] = $this->increaseGold($player_id, 1, 'hammer');
                            $notifLoyalty['hammer'] = $notifLoyalty['hammer'] + 1;
                        }
                        break ;
                    case "loyaltyPassive2":
                        if ($resourceType == 'gold') {
                            // automatic hammer
                            if ($this->canFillHammer($player_id) && $this->hasAutoHammer($player_id)) {
                                $notifPlayerArgs['notifHammer'][] = $this->increaseGold($player_id, 1, 'hammer');
                                $notifLoyalty['hammer'] = $notifLoyalty['hammer'] + 1;
                            }
                            else {
                                $scepter = $this->increaseGold($player_id, 1, 'scepter');
                                $this->increaseGold($player_id, 1 - $scepter);

                                $notifLoyalty['gold'] = $notifLoyalty['gold'] + 1 - $scepter;
                                $notifLoyalty['scepter'] = $notifLoyalty['scepter'] + $scepter;
                            }
                            $this->increaseVP($player_id, 1);
                            $notifLoyalty['vp'] = $notifLoyalty['vp'] + 1;
                        }
                        elseif ($resourceType == 'hammer') {
                            $notifPlayerArgs['notifHammer'][] = $this->increaseGold($player_id, 1, 'hammer');
                            $notifLoyalty['hammer'] = $notifLoyalty['hammer'] + 1;

                            $this->increaseVP($player_id, 1);
                            $notifLoyalty['vp'] = $notifLoyalty['vp'] + 1;
                        }
                        break ;
                    case "loyaltyPassive3":
                         if ($resourceType == 'gold') {
                            // automatic hammer
                            if ($this->canFillHammer($player_id) && $this->hasAutoHammer($player_id)) {
                                $notifPlayerArgs['notifHammer'][] = $this->increaseGold($player_id, 1, 'hammer');
                                $notifLoyalty['hammer'] = $notifLoyalty['hammer'] + 1;
                            }
                            else {
                                $scepter = $this->increaseGold($player_id, 1, 'scepter');
                                $this->increaseGold($player_id, 1 - $scepter);

                                $notifLoyalty['gold'] = $notifLoyalty['gold'] + 1 - $scepter;
                                $notifLoyalty['scepter'] = $notifLoyalty['scepter'] + $scepter;
                            }
                            $this->increaseVP($player_id, 1);
                            $notifLoyalty['vp'] = $notifLoyalty['vp'] + 1;
                        }
                        elseif ($resourceType == 'hammer') {
                            $notifPlayerArgs['notifHammer'][] = $this->increaseGold($player_id, 1, 'hammer');
                            $notifLoyalty['hammer'] = $notifLoyalty['hammer'] + 1;

                            $this->increaseVP($player_id, 1);
                            $notifLoyalty['vp'] = $notifLoyalty['vp'] + 1;
                        }

                        if ($resourceType == 'vp') {
                            $this->increaseVP($player_id, 1);
                            $notifLoyalty['vp'] = $notifLoyalty['vp'] + 1;
                        }
                        break ;

                }
                $notifPlayerArgs['notifLoyalty'] = $notifLoyalty;
            }
        }

        return $notifPlayerArgs;
    }

    function getLoyaltyArg ($player_id, $sideNum, $resourceType, $misfortune = false) {
        $argument = array('gold' => 0, 'vp' => 0    );

        if ($this->getGameStateValue( "rebellion") == 3) {
            if (($this->isThrown ($player_id, $sideNum) && !$misfortune) || $misfortune) {
                $position = $this->tokens->getTokenState("position_" . $player_id);
                $reward = $this->titan_board[$position]['bonus'];

                // Nothing to do for Loyalty before this
                if ($position <= 19)
                    return $argument;

                switch ($reward) {
                    case "loyaltyPassive1":
                        if ($resourceType == 'gold') {
                                $argument['gold'] = 1;
                            }
                        break ;
                    case "loyaltyPassive2":
                        if ($resourceType == 'gold') {
                            $argument['gold'] = 1;
                            $argument['vp'] = 1;
                        }
                        break ;
                    case "loyaltyPassive3":
                        if ($resourceType == 'gold') {
                            $argument['gold'] = 1;
                            $argument['vp'] = 1;
                        }

                        if ($resourceType == 'vp') {
                            $argument['vp'] = 1;
                        }
                        break ;

                }

            }
        }
        return $argument;
    }

    /*
     * Will try to affect the ressource of the side automatically
     * If a user choice is necessary, the field ressourceChoice will be put to 1
     * @return : notification array
     */
    function affectRessource($player_id, $notifPlayerArgs, $sideNum, $sideType, $triple, $multiple, $ship, $allocateShield = false, $exploit = false, $doNotSetChoice = false)
    {
        $choice_tmp = false;
        $ignore = false;
        $notifPlayerArgs['choice2'] = false;
        $celestial = false;
        $ignoreSetChoice = false;

        // if we process an exploit we get the transformation
        if ($exploit)
            $toTransform = $this->getRessourcesToTransform();
        else
            $toTransform = array('ressource' => array(), 'vp' => 1);

        //throw new feException(debug_print_backtrace());

        if ($sideNum == 98)
            $celestial = true;
        //throw new feException($exploit);
        $side_definition = $this->dice_sides[$sideType];
        //  no user action needed (except hammer)
        if($side_definition['type'] == 'simple' || ($side_definition['type'] == 'dependent' && $allocateShield)) {
            // update ressource + notification

            // 20201122 #20827
            // Ressource to be transformed.To avoid issues, no automatic allocation
            //throw new feException(print_r(array_keys($side_definition['ressource']))); //, $toTransform['ressource'])));
            //throw new feException(count(array_intersect(array_keys($side_definition['ressource']), $toTransform['ressource'])));
            if (count(array_intersect(array_keys($side_definition['ressource']), $toTransform['ressource'])) != 0) {
                if (!$doNotSetChoice) {
                    $this->dbSetSideChoice($player_id, $sideNum, $sideType);
                    //$this->setChoice($player_id, self::RC_RESSOURCE);
                    $this->dbSetChoice($player_id, self::RC_RESSOURCE);
                }
                $choice_tmp = true;
                $ignoreSetChoice = true;
                $notifPlayerArgs['choice2'] = true;
            }
            else {
                foreach($side_definition['ressource'] as $element => $ressource) {
                    if ($triple)
                        $value = $ressource * 3 * $multiple;
                    else
                        $value = $ressource * $multiple;

                    $resToTransform = in_array($element, $toTransform['ressource']);

                    if ($element == 'fireshard') {
                        if ($resToTransform) {
                            if (!$doNotSetChoice) {
                                $this->dbSetSideChoice($player_id, $sideNum, $sideType);
                                //$this->setChoice($player_id, self::RC_RESSOURCE);
                                $this->dbSetChoice($player_id, self::RC_RESSOURCE);
                            }
                            $choice_tmp = true;
                            $ignoreSetChoice = true;
                        }
                        elseif ($multiple != -1) {
                            $this->tokens->setTokenState($element . "_" . $player_id, 1);
                            $this->increaseFireShard($player_id, $value);
                        }
                        else{
                            $this->tokens->setTokenState($element . "_" . $player_id, 1);
                            $this->decreaseFireShard($player_id, $value * -1, 'minotaur');
                        }
                    } elseif ($element == 'gold') {
                        if ($multiple != -1 && (($this->canFillHammer($player_id) && !$this->hasAutoHammer($player_id))  || $resToTransform)) {
                            if (!$doNotSetChoice) {
                                $this->dbSetSideChoice($player_id, $sideNum, $sideType);
                                //throw new feException("toto");
                                $this->dbSetChoice($player_id, self::RC_RESSOURCE);
                            }
                            $choice_tmp = true;
                            $ignoreSetChoice = true;
                        }
                        elseif ($multiple != -1 && (($ship && $this->isGoldLimitReached($player_id, $value)))) {
                            if (!$doNotSetChoice) {
                                $this->dbSetSideChoice($player_id, $sideNum, $sideType);
                                //throw new feException("fuck");
                                $this->setChoice($player_id, self::RC_RESSOURCE);
                            }
                            $choice_tmp = true;
                        }
                        // if automatic hammer, affect everything to hammer
                        elseif($multiple != -1 && $this->canFillHammer($player_id) && $this->hasAutoHammer($player_id)) {
                            // If too much hammer has been sent => send only what is necessary remaining put to gold
                            $remainingGold = $this->maxHammer($player_id, $value);
                            $hammer = $value - $remainingGold;

                            $notifPlayerArgs['notifHammer'][] = $this->increaseGold($player_id, $value - $remainingGold, 'hammer');
                            $scepter = $this->increaseGold($player_id, $remainingGold, 'scepter');
                            $this->increaseGold($player_id, $remainingGold - $scepter);
                            $this->tokens->setTokenState($element . "_" . $player_id, 1);
                            $ignore = true;
                            if (array_key_exists('gold', $notifPlayerArgs))
                                $notifPlayerArgs['gold'] = $notifPlayerArgs['gold'] + $remainingGold;
                            else
                                $notifPlayerArgs['gold'] = $remainingGold - $scepter;

                            if (array_key_exists('scepter', $notifPlayerArgs))
                                $notifPlayerArgs['scepter'] = $notifPlayerArgs['scepter'] + $scepter;
                            else
                                $notifPlayerArgs['scepter'] = $scepter;

                            if (array_key_exists('hammer', $notifPlayerArgs))
                                $notifPlayerArgs['hammer'] = $notifPlayerArgs['hammer'] + $hammer;
                            else
                                $notifPlayerArgs['hammer'] = $hammer;

                            $notifPlayerArgs = $this->getLoyaltyReward($player_id, $sideNum, 'gold', $notifPlayerArgs);
                        }
                        elseif ($multiple == -1) {
                            $this->tokens->setTokenState($element . "_" . $player_id, 1);
                            $notif = $this->decreaseGold($player_id, $value * -1, 'minotaur');
                            if (array_key_exists('scepter', $notifPlayerArgs))
                                $notifPlayerArgs['scepter'] = $notifPlayerArgs['scepter'] + $notif['scepter'];
                            else
                                $notifPlayerArgs['scepter'] = $notif['scepter'];

                            if (array_key_exists('gold', $notifPlayerArgs))
                                $notifPlayerArgs['gold'] = $notifPlayerArgs['gold'] + $notif['gold'];
                            else
                                $notifPlayerArgs['gold'] = $notif['gold'];
                        }
                        else {
                            $this->tokens->setTokenState($element . "_" . $player_id, 1);
                            $scepter = $this->increaseGold($player_id, $value, 'scepter');
                            $this->increaseGold($player_id, $value - $scepter);

                            if (array_key_exists('gold', $notifPlayerArgs))
                                $notifPlayerArgs['gold'] = $notifPlayerArgs['gold'] + $value - $scepter;
                            else
                                $notifPlayerArgs['gold'] = $remainingGold - $value - $scepter;

                            if (array_key_exists('scepter', $notifPlayerArgs))
                                $notifPlayerArgs['scepter'] = $notifPlayerArgs['scepter'] + $scepter;
                            else
                                $notifPlayerArgs['scepter'] = $scepter;

                            $notifPlayerArgs = $this->getLoyaltyReward($player_id, $sideNum, 'gold', $notifPlayerArgs);

                        }
                    } elseif ($element == 'moonshard') {
                        if ($resToTransform) {
                            if (!$doNotSetChoice) {
                                $this->dbSetSideChoice($player_id, $sideNum, $sideType);
                                $this->dbSetChoice($player_id, self::RC_RESSOURCE);
                            }
                            $choice_tmp = true;
                            $ignoreSetChoice = true;
                        } elseif ($multiple != -1) {
                            $this->tokens->setTokenState($element . "_" . $player_id, 1);
                            $this->increaseMoonShard($player_id, $value);
                        } else {
                            $this->tokens->setTokenState($element . "_" . $player_id, 1);
                            $this->decreaseMoonShard($player_id, $value * -1, 'minotaur');
                        }
                    } elseif ($element == 'ancientshard') {
                        // TODO: check if sentinel effect applies
                        //if ($resToTransform) {
                        //    $this->dbSetSideChoice($player_id, $sideNum, $sideType);
                        //    $this->dbSetChoice($player_id, self::RC_RESSOURCE);
                        //    $choice_tmp = true;
                        //    $ignoreSetChoice = true;
                        //}
                        //else
                        if ($multiple != -1) {
                            $this->tokens->setTokenState($element . "_" . $player_id, 1);
                            $this->increaseAncientShard($player_id, $value);
                            // value * -1 because ancient shard must move back
                            $this->tokens->incTokenState("movetitan_$player_id", $value * -1);
                        }
                        else{
                            $this->tokens->setTokenState($element . "_" . $player_id, 1);
                            $this->decreaseAncientShard($player_id, $value * -1, 'minotaur');
                            // no loss with minotaure
                        }
                    } elseif ($element == 'loyalty') {
                        if ($multiple != -1) {
                            $this->tokens->setTokenState($element . "_" . $player_id, 1);
                            $this->tokens->incTokenState("movetitan_$player_id", $value);
                        }
                        else{
                            $this->tokens->setTokenState($element . "_" . $player_id, 1);
                            // nothing to do : loss of loyalty does not generate movement
                        }
                    } elseif ($element == 'vp') {
                        if ($multiple != -1) {
                            $this->tokens->setTokenState($element . "_" . $player_id, 1);
                            $this->increaseVP($player_id, $value);
                            $this->incStat($value, 'nb_vp_die', $player_id);

                            $notifPlayerArgs = $this->getLoyaltyReward($player_id, $sideNum, 'vp', $notifPlayerArgs);
                        }
                        else{
                            $this->tokens->setTokenState($element . "_" . $player_id, 1);
                            $this->decreaseVP($player_id, $value * -1);
                            $this->incStat($value * -1, 'nb_minotaur_vp', $player_id);
                        }
                    }

                    if (! $choice_tmp && ! $resToTransform && ! $ignore && $element != 'gold') {
                        // add of the addition for notif + JS processing
                        if (array_key_exists($element, $notifPlayerArgs))
                            $notifPlayerArgs[$element] = $notifPlayerArgs[$element] + $value;
                        else
                            $notifPlayerArgs[$element] = $value;
                    }

                    if ($choice_tmp)
                        $notifPlayerArgs['choice2'] = true;

                    $ignore = false;
                    $choice_tmp = false;
                }
            }

            // Feed the stack of the maze
            if ($multiple != -1 && isset($side_definition['maze'])) {
                if ($triple) {
                    $this->tokens->incTokenState("mazestock_$player_id", $side_definition['maze'] *3);
                    $notifPlayerArgs['maze'] += $side_definition['maze'] * 3;
                }
                else {
                    $this->tokens->incTokenState("mazestock_$player_id", $side_definition['maze']);
                    $notifPlayerArgs['maze'] += $side_definition['maze'];
                }


                $this->setGameStateValue("monoRessourceChoice", 1);

            }

            //if ($choice_tmp)
            //    $notifPlayerArgs['choice'] = true;
            //else {
            if ($notifPlayerArgs['choice2'] == false) {
                if (!$doNotSetChoice) {
                    $this->dbSetSideChoice($player_id, $sideNum, "0");

                    if ($triple) {
                        $this->tokens->getTokenState("triple_$player_id", 0);
                        if ($sideNum == 1)
                            $this->dbSetSideChoice($player_id, 2, "0");
                        else
                            $this->dbSetSideChoice($player_id, 1, "0");
                    }
                }
            }
            else
                $notifPlayerArgs['choice'] = true;
            // reset ressource choice if for simple if is different

            if ($this->getPlayersAdditionnalInfo()[$player_id]['boar'] != 0)
                $ignoreSetChoice = true;

            if (!$ship && !$ignoreSetChoice && !$doNotSetChoice)
                //$this->setChoice($player_id, self::RC_NOTHING_TODO);
                $this->setChoice($player_id, self::RC_RESSOURCE);

        }
        elseif ($side_definition['type'] == 'choice') {
            if (!$doNotSetChoice) {
                $this->dbSetSideChoice($player_id, $sideNum, $sideType);
                $this->setChoice($player_id, self::RC_RESSOURCE);
            }
            $notifPlayerArgs['choice'] = true;
        }
        elseif ($side_definition['type'] == 'mirror') {
            if (!$doNotSetChoice) {
                $this->dbSetSideChoice($player_id, $sideNum, -1);
                $this->setChoice($player_id, self::RC_SIDE_CHOICE);
            }
            $notifPlayerArgs['choice'] = true;
        }
        elseif ($side_definition['type'] == 'forge') {
            if ($multiple != -1 && !$doNotSetChoice) {
                $this->dbSetSideChoice($player_id, $sideNum, $sideType);
                $this->setChoice($player_id, self::RC_FORGESHIP);
            }
        }
        elseif ($side_definition['type'] == 'dependent' && !$allocateShield && !$doNotSetChoice) {
            $this->dbSetSideChoice($player_id, $sideNum, "0");
            $this->setChoice($player_id, null);
            //$notifPlayerArgs['choice'] = true;
        }

        // management of some specific sides
        if (isset($side_definition['bonus'])) {
            foreach($side_definition['bonus'] as $element => $ressource) {
                if ($ressource != 0) {
                    switch ($element) {
                        case 'vp':
                            $this->tokens->setTokenState("vp_$player_id", 1);
                            $this->increaseVP($player_id, $ressource);
                            $this->incStat($ressource, 'nb_vp_die', $player_id);

                            $notifPlayerArgs = $this->getLoyaltyReward($player_id, $sideNum, 'vp', $notifPlayerArgs);
                            break ;
                    }

                    if (array_key_exists($element, $notifPlayerArgs))
                        $notifPlayerArgs[$element] = $notifPlayerArgs[$element] + $ressource;
                    else
                        $notifPlayerArgs[$element] = $ressource;
                }
            }
        }
        //elseif ($side_definition['type'] == 'triple') {
        //  $this->dbSetSideChoice($player_id, $sideNum, '0');
        //  $this->setChoice($player_id, self::RC_NOTHING_TODO);
        //}

        //if ($notifPlayerArgs['choice'] == false)
        //    $this->titanMove ($player_id);
        //throw new feException(print_r($notifPlayerArgs));

        return $notifPlayerArgs;
    }

    /*
     * Used to affect resources from a triple chosen after having used twins.
     * Resources will be affected twice
     *
     */
    function affectTriple($player_id, $notifPlayerArgs, $sideNum, $sideType, $triple, $multiple, $ship, $allocateShield = false, $exploit = false)
    {
        $choice_tmp = false;
        $ignore = false;
        $notifPlayerArgs['choice2'] = false;
        $ignoreSetChoice = false;

        // if we process an exploit we get the transformation
        if ($exploit)
            $toTransform = $this->getRessourcesToTransform();
        else
            $toTransform = array('ressource' => array(), 'vp' => 1);

        //throw new feException($exploit);
        $side_definition = $this->dice_sides[$sideType];

        foreach($side_definition['ressource'] as $element => $ressource) {
            if ($this->tokens->getTokenState($element . "_" . $player_id) == 1) {
                $value = $ressource * 2;
                if ($element == 'gold') {
                    if ($multiple != -1) {
                        $this->increaseGold($player_id, $value);
                    }
                    else {
                        $this->decreaseGold($player_id, $value * -1, 'minotaur');
                    }

                }
                elseif ($element == 'fireshard') {
                    if ($multiple != -1) {
                        $this->increaseFireShard($player_id, $value);
                    }
                    else{
                        $this->decreaseFireShard($player_id, $value * -1, 'minotaur');
                    }
                }
                elseif ($element == 'moonshard') {
                    if ($multiple != -1) {
                        $this->increaseMoonShard($player_id, $value);
                    }
                    else{
                        $this->decreaseMoonShard($player_id, $value * -1, 'minotaur');
                    }
                }
                elseif ($element == 'vp') {
                    // TODO : add gestion VP transform
                    if ($multiple != -1) {
                        $this->increaseVP($player_id, $value);
                        $this->incStat($value, 'nb_vp_die', $player_id);

                        $notifPlayerArgs = $this->getLoyaltyReward($player_id, $sideNum, 'vp', $notifPlayerArgs);
                    }
                    else{
                        $this->decreaseVP($player_id, $value * -1);
                        $this->incStat($value * -1, 'nb_minotaur_vp', $player_id);
                    }
                }
                elseif ($element == 'ancientshard') {
                    if ($multiple != -1) {
                            $this->increaseAncientShard($player_id, $value);
                            // value * -1 because ancient shard must move back
                            $this->tokens->incTokenState("movetitan_$player_id", $value * -1);
                    }
                    else{
                        $this->decreaseAncientShard($player_id, $value * -1, 'minotaur');
                        // no move with minotaure
                    }
                }
                elseif ($element == 'loyalty') {
                    if ($multiple != -1) {
                        $this->tokens->incTokenState("movetitan_$player_id", $value);
                    }
                    else{
                        // no move with minotaure
                    }
                }
                $notifPlayerArgs[$element] += $value;
            }
            // have we transformed something into VP?
            if (in_array($element, $toTransform['ressource']) && $this->tokens->getTokenState("vp_" . $player_id == 1)) {
                $vp = $toTransform['vp'];
                $value = $ressource * 2;

                $notifPlayerArgs['vp'] += $value * $vp;
                $this->increaseVP($player_id, $value * $vp);
                $this->incStat($value * $vp, 'nb_vp_die', $player_id);
            }
        }

            // Feed the stack of the maze
            if ($multiple != -1 && isset($side_definition['maze'])) {
                $this->tokens->incTokenState("mazestock_$player_id", $side_definition['maze'] *2);
                $notifPlayerArgs['maze'] += $side_definition['maze'] * 2;
                $this->setGameStateValue("monoRessourceChoice", 1);

            }


            //if ($notifPlayerArgs['choice2'] == false) {
            //    $this->dbSetSideChoice($player_id, $sideNum, "0");
            //
            //    if ($triple) {
            //        $this->tokens->getTokenState("triple_$player_id", 0);
            //        if ($sideNum == 1)
            //            $this->dbSetSideChoice($player_id, 2, "0");
            //        else
            //            $this->dbSetSideChoice($player_id, 1, "0");
            //    }
            //}
            //else
            //    $notifPlayerArgs['choice'] = true;
            // reset ressource choice if for simple if is different

            //if ($this->getPlayersAdditionnalInfo()[$player_id]['boar'] != 0)
            //    $ignoreSetChoice = true;

            //if (!$ship && !$ignoreSetChoice)
            //    //$this->setChoice($player_id, self::RC_NOTHING_TODO);
            //    $this->setChoice($player_id, self::RC_RESSOURCE);



        //elseif ($side_definition['type'] == 'triple') {
        //  $this->dbSetSideChoice($player_id, $sideNum, '0');
        //  $this->setChoice($player_id, self::RC_NOTHING_TODO);
        //}

        //if ($notifPlayerArgs['choice'] == false)
        //    $this->titanMove ($player_id);
        //throw new feException(print_r($notifPlayerArgs));

        return $notifPlayerArgs;
    }


    function sceptersToFill($player_id) {
        $max_value = 6;
        $scepters = array();

        //$playerTokens = $this->tokens->getTokensInLocation($player_id);
        //getTokensOfTypeInLocation($type, $location = null, $state = null, $order_by = null, $asc = true)
        $playerTokens = $this->tokens->getTokensOfTypeInLocation('scepter%', $player_id, null, 'token_state', false);
        //throw new feException(print_r($playerTokens));
        foreach ($playerTokens as $tokID => $tok) {
            //if (substr($tokID, 0, 7) == 'scepter')
                $scepters[$tok['key']] = $max_value - $tok['state'];
        }

        return $scepters;
    }

    //function sceptersGold($player_id) {
    //    $scepter = 0;
    //
    //    $playerTokens = $this->tokens->getTokensInLocation($player_id);
    //    foreach ($playerTokens as $tokID => $tok) {
    //        if (substr($tokID, 0, 7) == 'scepter')
    //            $scepter = $tok['state'];
    //    }
    //    return $scepter;
    //}

    /*
     * Increase the gold or hammer of the user
     * @return : true if everything worked :)
     */
    function increaseGold( $player_id, $quantity, $where = "stock" )
    {
        $this->players_info = null;
        $players_info = $this->getPlayersAdditionnalInfo();
        $lost         = 0; // pour les stats
        $max_scepter_value = 6;

        $this->incStat($quantity, 'nb_won_gold', $player_id);

        if ($where == 'scepter') {
            $scepter = 0;
            // scepter management
            $scepters = $this->sceptersToFill($player_id);
            if (count($scepters) != 0) {
                foreach ($scepters as $scepter_id => $remaining) {
                    $tokenValue = $this->tokens->getTokenState($scepter_id);
                    if ($quantity != 0) {
                        if ($remaining >= $quantity) {
                            $this->tokens->setTokenState($scepter_id, $tokenValue + $quantity);
                            $scepter += $quantity;
                            $quantity = 0;
                        }
                        elseif ($remaining < $quantity ) {
                            $this->tokens->setTokenState($scepter_id, $max_scepter_value);
                            $scepter += $remaining;
                            $quantity = $quantity - $remaining;

                        }
                    }
                }
            }
            return $scepter;
        }
        elseif ( $where == "stock" )
        {
            $player_gold   = $players_info[ $player_id ]['res_gold'];
            $player_chests = $this->hasChest( $player_id );

            $max_gold = self::MAX_GOLD + ( self::CHEST_GOLD * $player_chests );
            $new_gold = $player_gold + $quantity;

            if ( $new_gold > $max_gold )
            {
                $lost     = $new_gold - $max_gold;
                $new_gold = $max_gold;
            }

            if ($lost != 0)
                $this->incStat($lost, 'nb_loss_gold', $player_id);

            return $this->dbSetGold( $player_id, $new_gold );
        }
        elseif ( $where == "hammer" )
        {
            $hammer_position  = $players_info[ $player_id ]['hammer_position'];
            $remaining_hammer = $this->hasActiveHammer( $player_id ) * self::HAMMER_MAX_POSITION - $hammer_position;

            if ( $hammer_position < 0 )
            {
                throw new BgaVisibleSystemException  ("You cannot allocate gold to the hammer, you do not have one");
                return false; // impossible, player should not have choosen hammer
            }

            if ( $quantity > $remaining_hammer )
            {
                throw new BgaVisibleSystemException ("You have allocated more gold than can be put in the hammer. Please reduce the amount.");
                return false; // gold put in hammer can't exceed, hammer capacity
            }

            return $this->increaseHammerPosition( $player_id, $quantity );
        }
    }

    function decreaseGold( $player_id, $quantity, $action = null)
    {
        $players_info = $this->getPlayersAdditionnalInfo();
        $res = array ('gold' => 0, 'scepter' => 0);

        // decrease gold reserve first
        $player_gold  = $players_info[ $player_id ]['res_gold'];
        $new_gold     = ( $player_gold < $quantity ) ? 0 : $player_gold - $quantity;

        if ($action == 'minotaur')
            $this->incStat($quantity, 'nb_minotaur_gold', $player_id);
        else
            $this->incStat($quantity, 'nb_used_gold', $player_id);

        $this->dbSetGold( $player_id, $new_gold );
        $res['gold'] += ( $player_gold < $quantity ) ? $player_gold : $quantity;
        $remaining_to_reduce = $quantity - $player_gold;

        if ($remaining_to_reduce <= 0)
            return $res;

        if ($action != 'minotaur') {
            // decrease scepters afterwards
            //$playerTokens = $this->tokens->getTokensInLocation($player_id);
            // #35213 - change to take smallest first
            $playerTokens = $this->tokens->getTokensOfTypeInLocation('scepter_', $player_id, null, 'token_state', true);
            foreach ($playerTokens as $tokID => $tok) {
                //if (substr($tokID, 0, 7) == 'scepter') {
                    $new_gold     = ( $tok['state'] < $remaining_to_reduce ) ? 0 : $tok['state'] - $remaining_to_reduce;
                    $this->tokens->setTokenState($tok['key'], $new_gold );

                    $res['scepter'] += ( $tok['state'] < $remaining_to_reduce ) ? $tok['state'] : $remaining_to_reduce;
                        $remaining_to_reduce = $remaining_to_reduce - $tok['state'];

                    if ($remaining_to_reduce <= 0)
                        return $res;
                //}
            }
        }

    }

    function increaseMoonShard( $player_id, $quantity )
    {
        $players_info = $this->getPlayersAdditionnalInfo();
        $lost = 0;

        $this->incStat($quantity, 'nb_won_moonshard', $player_id);

        $player_moon   = $players_info[ $player_id ]['res_moon'];
        $player_chests = $this->hasChest( $player_id );

        $max_moon = self::MAX_MOONSHARD + ( self::CHEST_MOONSHARD * $player_chests );
        $new_moon = $player_moon + $quantity;

        if ( $new_moon > $max_moon )
        {
            $lost     = $new_moon - $max_moon;
            $new_moon = $max_moon;
        }

        if ($lost != 0)
            $this->incStat($lost, 'nb_loss_moonshard', $player_id);

        return $this->dbSetMoonShard( $player_id, $new_moon );
    }

    function decreaseMoonShard( $player_id, $quantity, $action = null )
    {
        $players_info = $this->getPlayersAdditionnalInfo();
        $player_moon  = $players_info[ $player_id ]['res_moon'];
        $new_moon     = ( $player_moon < $quantity ) ? 0 : $player_moon - $quantity;

        if ($action == 'minotaur')
            $this->incStat($quantity, 'nb_minotaur_moonshard', $player_id);
        else
            $this->incStat($quantity, 'nb_used_moonshard', $player_id);

        return $this->dbSetMoonShard( $player_id, $new_moon );
    }

    function increaseAncientShard( $player_id, $quantity )
    {
        $players_info = $this->getPlayersAdditionnalInfo();
        $lost = 0;

        $this->incStat($quantity, 'nb_won_ancientshard', $player_id);

        $player_ancient   = $players_info[ $player_id ]['res_ancient'];
        //$player_chests = $this->hasChest( $player_id );

        $max_ancient = self::MAX_ANCIENTSHARD; // + ( self::CHEST_MOONSHARD * $player_chests );
        $new_ancient = $player_ancient + $quantity;

        if ( $new_ancient > $max_ancient )
        {
            $lost     = $new_ancient - $max_ancient;
            $new_ancient = $max_ancient;
        }

        if ($lost != 0)
            $this->incStat($lost, 'nb_loss_ancientshard', $player_id);

        return $this->dbSetAncientShard( $player_id, $new_ancient );
    }


    function decreaseAncientShard( $player_id, $quantity, $action = null )
    {
        $players_info = $this->getPlayersAdditionnalInfo();
        $player_ancient  = $players_info[ $player_id ]['res_ancient'];
        $new_ancient    = ( $player_ancient < $quantity ) ? 0 : $player_ancient - $quantity;

        if ($action == 'minotaur') {
            //$this->incStat($quantity, 'nb_minotaur_ancientshard', $player_id);
        }
        else
            $this->incStat($quantity, 'nb_used_ancientshard', $player_id);

        return $this->dbSetAncientShard( $player_id, $new_ancient );
    }

    function increaseFireShard( $player_id, $quantity )
    {
        $players_info = $this->getPlayersAdditionnalInfo();
        $lost = 0;

        $this->incStat($quantity, 'nb_won_fireshard', $player_id);

        $player_fire   = $players_info[ $player_id ]['res_fire'];
        $player_chests = $this->hasChest( $player_id );

        $max_fire = self::MAX_FIRESHARD + ( self::CHEST_FIRESHARD * $player_chests );
        $new_fire = $player_fire + $quantity;

        if ( $new_fire > $max_fire )
        {
            $lost     = $new_fire - $max_fire;
            $new_fire = $max_fire;
        }

        if ($lost != 0)
            $this->incStat($lost, 'nb_loss_fireshard', $player_id);

        return $this->dbSetFireShard( $player_id, $new_fire );
    }

    //function decreaseFireShard( $player_id, $quantity, $action = null )
    //{
    //    $players_info = $this->getPlayersAdditionnalInfo();
    //    $player_fire  = $players_info[ $player_id ]['res_fire'];
    //    $scepterFire = $this->getGameStateValue("scepterFireshard");
    //    $new_fire     = ( $player_fire < $quantity ) ? 0 : $player_fire - $quantity;
    //
    //    if ($action == 'minotaur')
    //        $this->incStat($quantity, 'nb_minotaur_fireshard', $player_id);
    //    else
    //        $this->incStat($quantity, 'nb_used_fireshard', $player_id);
    //
    //    if ($action != 'minotaur') {
    //        $new_scepter = ($scepterFire < $quantity) ? 0 : $scepterFire - $quantity;
    //        $new_quantity = ($scepterFire < $quantity) ? $quantity - $scepterFire : 0;
    //
    //        $new_fire     = ( $player_fire < $new_quantity ) ? 0 : $player_fire - $new_quantity;
    //    }
    //
    //    return $this->dbSetFireShard( $player_id, $new_fire );
    //}

    function decreaseFireShard( $player_id, $quantity, $action = null )
    {
        $players_info = $this->getPlayersAdditionnalInfo();
        $player_fire  = $players_info[ $player_id ]['res_fire'];
        $new_fire     = ( $player_fire < $quantity ) ? 0 : $player_fire - $quantity;

        if ($action == 'minotaur')
            $this->incStat($quantity, 'nb_minotaur_fireshard', $player_id);
        else
            $this->incStat($quantity, 'nb_used_fireshard', $player_id);

        return $this->dbSetFireShard( $player_id, $new_fire );
    }

    function increaseVP( $player_id, $quantity )
    {
        return $this->dbIncreaseVP( $player_id, $quantity );
    }

    function decreaseVP( $player_id, $quantity )
    {
        return $this->dbDecreaseVP( $player_id, $quantity );
    }

    /*
     * will increase the hammer
     * if the user completes a hammer, score is added
     * @return : notification for the hammer
     */
    function increaseHammerPosition( $player_id, $quantity )
    {
        $nbHammer = $this->hasActiveHammer($player_id);
        $hammer_pos = $this->dbGetHammerPosition($player_id);
        $new_pos = $hammer_pos + $quantity;
        $notifHammer = [];

        // count of the hammer
        $previous_hammer = intdiv($hammer_pos, self::HAMMER_MAX_POSITION / 2 ) + 1;
        $new_hammer = intdiv($new_pos, self::HAMMER_MAX_POSITION / 2 ) + 1;

        $diff = $new_hammer - $previous_hammer;

        if ($diff!= 0) {
            // VP to score
            if ($previous_hammer % 2 == 1)
                $nextVp = 10;
            else
                $nextVp = 15;

            for ($i = 0; $i < $diff; $i++) {
                $this->increaseVP($player_id, $nextVp);
                $this->incStat($nextVp, 'nb_vp_hammer', $player_id);

                if ($nextVp == 10)
                    $phase = 1;
                else
                    $phase = 2;

                $notifHammer[] = array(
                        'player_name'  => $this->loadPlayersBasicInfos()[$player_id]['player_name'],
                        'player_id'    => $player_id,
                        'hammer_phase' => $phase,
                        'ressources'   => $this->buildRessourceNotif( array("vp" => $nextVp) ));
                if ($nextVp == 10)
                    $nextVp = 15;
                else
                    $nextVp = 10;
            }
        }

        $ret = $this->dbIncreaseHammer( $player_id, $quantity );

        return $notifHammer;

    }

    /*
     * Check if the player has a Ship side that needs to be resolved
     * @return : boolean
     */
    function hasUnusedShip($player_id = null)
    {
        $sql = "SELECT distinct 1 FROM player WHERE (side_choice_1 = 'ship' OR side_choice_2 = 'ship')";

        //$sql = "SELECT count(card_id) FROM sides WHERE card_type_arg = 1 AND card_location_arg = 0";

        if ($player_id != null)
            $sql .= " AND player_id = " . $player_id;
        $dbres = self::getUniqueValueFromDB($sql);

        if ($dbres == 0) {
            if ($player_id !== null && $this->getGameStateValue("mazeForge") == 1 && $this->getRessourceChoice($player_id) == self::RC_FORGESHIP)
                return true;

            if ($player_id !== null && $this->getGameStateValue("celestialRunning") == 1 && $this->getRessourceChoice($player_id) == self::RC_FORGESHIP)
                return true;
            // side not valid
            return false;
        }
        else
            return true;

    }

    function processedExploit() {
        $card_id = $this->getGameStateValue( "exploitBought");
        if ($card_id != -1 && ($this->gamestate->state()['name'] == 'exploitRessource' ||$this->gamestate->state()['name'] == 'exploitEffect' )) {
            $card = $this->exploits->getCard($card_id);
            $card_info = $this->exploit_types[$card['type']];
            //$exploit = true;

            return $card_info['action'];
        }
        return false;
    }

    function hasResolutionConflict() {
        $sql = "SELECT player_id, card_type from sides, player where (card_id = player.throw_1 or card_id = player.throw_2) and (card_type = 'mirror' or card_type = 'ship')";
        $dbres = self::getObjectListFromDB($sql);

        $ship_owned = array();
        $mirror_owned = array();

        foreach ($dbres as $num => $result) {
            if ($result['card_type'] == 'mirror') {
                if (!in_array($result['player_id'], $mirror_owned))
                    $mirror_owned[] = $result['player_id'];

                if (count($ship_owned) != 0 && (count($ship_owned) == 1 && !in_array($result['player_id'], $ship_owned)))
                    return true;
            }
            elseif ($result['card_type'] == 'ship') {
                if (!in_array($result['player_id'], $ship_owned))
                    $ship_owned[] = $result['player_id'];

                if (count($mirror_owned) != 0 && (count($mirror_owned) == 1 && !in_array($result['player_id'], $mirror_owned)))
                    return true;

                if (count ($ship_owned) > 1)
                    return true;
            }

        }

        // if people that have a twin have mirror or ship on their die => monoresolution
        $sql = "SELECT count(*) FROM sides WHERE (card_type = 'mirror' or card_type = 'ship') AND ";
        $sql .= "(card_location in (SELECT DISTINCT CONCAT('dice1-p', SUBSTRING(card_location, 7, 99)) FROM exploit WHERE card_type = 'twins' AND card_location like 'pile%') OR ";
        $sql .= "card_location in (SELECT DISTINCT CONCAT('dice2-p', SUBSTRING(card_location, 7, 99)) FROM exploit WHERE card_type = 'twins' AND card_location like 'pile%')) ";
        $dbres = self::getUniqueValueFromDB($sql);
        if ($dbres > 0)
            return true;

        // if we have a mirror & someone else owns a twin
        $sql = "SELECT DISTINCT SUBSTRING(card_location, 7, 99) FROM exploit WHERE card_type = 'twins' AND card_location LIKE 'pile%'";
        $dbres = self::getObjectListFromDB($sql, true);

        foreach ($dbres as $twin) {
            // one mirror shown and not the one that have the twin
            if (count($mirror_owned) != 0 && count($mirror_owned) == 1 && !in_array($twin, $mirror_owned))
                return true;

            if (count($mirror_owned) > 1)
                return true;
        }

        // Misfortune
        // If we have two owners of misfortune triggered at the same time, mono resolution
        $sql = "SELECT distinct card_type_arg from sides, player where (card_id = player.throw_1 or card_id = player.throw_2) and (card_type like '%Misfortune')";
        $dbres = self::getObjectListFromDB($sql, true);
        // if we roll a misfortune, mono resolution (test)
        //if (count($dbres) > 1)
        if (count($dbres) > 0)
            return true;

        return false;
    }

    /*
     * Check if any player has a need for choice
     * @return : boolean
     */
    function isRessourceChoice($action = null, $player_id = null)
    {
        $sql = "SELECT distinct 1 FROM player WHERE ";

        if ($action == null)
            $sql .= " ressource_choice != -1";
        else
            $sql .= " ressource_choice = " . $action;

        if ($player_id != null)
            $sql .= " AND player_id = $player_id";

        $dbres = self::getUniqueValueFromDB($sql);

        if ($dbres == 0)
            // no choice
            return false;
        else
            return true;

    }

    function hasUnresolvedSides ($player_id = null) {
        $sql = "SELECT distinct 1 FROM player WHERE (side_choice_1 != '0' OR side_choice_2 != '0') ";

        if ($player_id != null)
            $sql .= " AND player_id = " . $player_id;

        $dbres = self::getUniqueValueFromDB($sql);
        if ($dbres == 0)
            // no choice
            return false;
        else
            return true;
    }

    function isMazeChoice($player_id = null) {
        $sql = "SELECT distinct 1 FROM token WHERE token_location != '0' ";

        if ($player_id != null)
            $sql .= "AND player_id = $player_id";

        $dbres = self::getUniqueValueFromDB($sql);

        if ($dbres == 0)
            // no choice
            return false;
        else
            return true;
    }

    function hasChest( $player_id )
    {
        return $this->countExploitInLocation( "chest", "table-". $player_id );
    }

    function hasActiveHammer( $player_id )
    {
        return $this->countExploitInLocation( "hammer", "table-" . $player_id );
    }

    function hasBear( $player_id )
    {
        return $this->countExploitInLocation( "bear", "pile2-". $player_id );
    }

    function hasPegasus($player_id) {
        return $this->countExploitInLocation( "pegasus", "pile2-". $player_id );
    }

    function resolveTwin ($player_id, $toResolve) {
        if ($toResolve && $this->getPlayersAdditionnalInfo()[$player_id]['twins'] != 0)
            $this->tokens->setTokenState("resolveTwin_" . $player_id, 1);
        elseif (!$toResolve)
            $this->tokens->setTokenState("resolveTwin_" . $player_id, 0);

        return ;
    }

    function getBoar($boar) {
        // send player_id having the boar side
        $side = $this->sides->getCardsOfType($boar);
        if (count($side) == 0)
            throw new BgaVisibleSystemException ("Should not happen, boar must be owned");
        $side = reset($side);
        return $side['type_arg'];

    }

    function hasHind( $player_id )
    {
        return $this->countExploitInLocation( "doe", "pile3-" . $player_id );
    }

    function hasElder( $player_id )
    {
        return $this->countExploitInLocation( "ancien", "pile3-" . $player_id );
    }

    function hasOwl( $player_id )
    {
        return $this->countExploitInLocation( "owl", "pile3-" . $player_id );
    }

    function hasCerberusToken( $player_id = null )
    {
        $sql = "SELECT max(cerberus_token) FROM player, exploit WHERE card_location = concat('pile1-', player_id) AND exploit.card_type_arg = 0 ";
        $sql.= "AND card_type = 'cerberus' AND cerberus_token > 0 AND rolled = 1";
        if ($player_id != null)
            $sql .= " AND player_id = " . $player_id;
        //$players = $this->getPlayersAdditionnalInfo();
        //return $players[ $player_id ]['cerberus_token'];

        $dbres = self::getUniqueValueFromDB($sql);

        if ($dbres == 0)
            return false;
        else
            return true;

    }

    function getGold($player_id)
    {
        $sql = "select sum(gold) from (";
        $sql .= "select res_gold  gold from player where player_id = $player_id ";
        $sql .= "union all ";
        $sql .= "select  sum(token_state) gold  from token where token_location = $player_id AND token_key like 'scepter%') aa";

        return self::getUniqueValueFromDB($sql);
    }

    function hasCompanionToken($player_id) {
        $sql = "SELECT SUM(token_state) FROM token WHERE token_key LIKE 'companion%' AND token_location = '$player_id' AND token_state <= 5";

        $dbres = self::getUniqueValueFromDB($sql);

        if ($dbres == 0)
            return false;
        else
            return true;
    }

    function hasTritonToken( $player_id )
    {
        $players = $this->getPlayersAdditionnalInfo();
        return $players[ $player_id ]['triton_token'];
    }

    // Will automatically affect the triton token to hammer/gold if we are on the last turn
    function autoAffectTritonToken($player_id) {
        $nbToken = $this->hasTritonToken($player_id);
        $value = 6;

        for ($i = 1; $i <= $nbToken; $i++) {
            $notifPlayerArgs = $this->initNotif($player_id);
            // decrease token
            $this->dbIncTriton($player_id, false);

            // has a hammer
            if ($this->canFillHammer($player_id)) {
                // If too much hammer has been sent => send only what is necessary remaining put to gold
                $remainingGold = $this->maxHammer($player_id, $value);
                $hammer = $value - $remainingGold;

                $notifPlayerArgs['notifHammer'][] = $this->increaseGold($player_id, $hammer, 'hammer');
                $notifPlayerArgs['hammer'] = $notifPlayerArgs['hammer'] + $hammer;
                $this->increaseGold($player_id, $remainingGold);
                $notifPlayerArgs['gold'] = $notifPlayerArgs['gold'] + $remainingGold;
            }
            else {
               $this->increaseGold($player_id, $value);
               $notifPlayerArgs['gold'] = $notifPlayerArgs['gold'] + $value;
            }

             // petit hack
            if ( $notifPlayerArgs['vp'] == 0 )
            {
                unset( $notifPlayerArgs['vp'] );
            }

            $notifPlayerArgs['ressources'] = $this->buildRessourceNotif( $notifPlayerArgs );
            self::notifyAllPlayers("notifUseTritonToken", clienttranslate('${player_name} uses a Triton token and gets ${ressources}'),
                $notifPlayerArgs
            );

            if (isset($notifPlayerArgs['notifHammer']) && count($notifPlayerArgs['notifHammer']) != 0) {
                $this->generateNotifHammer($player_id, $notifPlayerArgs['notifHammer']);
            }
        }


        return ;
    }

    // get Non Used Reinforcements
    function getActiveReinforcements( $player_id )
    {
        $sql = "SELECT card_id AS id, card_type AS type
        FROM exploit
        WHERE card_type_arg = 0
        AND card_location = 'pile3-" . $player_id . "'";

        return self::getCollectionFromDb( $sql );
    }

    function canUseTwins($player_id) {
        // Scepters & twins cannot be at the same time on play ==> check of only gold
        $sql = "SELECT count(card_id) FROM exploit, player WHERE card_type = 'twins' AND card_type_arg = 0 AND card_location LIKE '%-$player_id' AND res_gold >= 3 AND player_id = $player_id";
        $dbres = self::getUniqueValueFromDB($sql);

        if ($dbres == 0)
            return false;
        else
            return true;
    }

    function updateAvailableTwin($player_id, $used = true) {
        $sql = "SELECT card_id FROM exploit WHERE card_type = 'twins' AND card_type_arg = 0 AND card_location LIKE '%-$player_id' LIMIT 1";
        $card_id = self::getUniqueValueFromDB($sql);

        if ($card_id == null)
            throw new feException('Error in the updateAvailableTwin function');
        else {
            $sql = "UPDATE exploit SET card_type_arg = $used WHERE card_id = " . $card_id;
            self::dbQuery($sql);
            return $card_id;
        }
    }

    function resetTwins($player_id = null, $used = false) {
        if ($used == false) {
            $sql = "UPDATE exploit SET card_type_arg = 0 WHERE card_type = 'twins'";
            self::notifyAllPlayers('notifResetTwins', '', []);
        } else {
            $sql = "UPDATE exploit SET card_type_arg = 1 WHERE card_type = 'twins'";
        }

        if ($player_id != null)
            $sql .= " AND card_location LIKE '%-$player_id'";
        return (self::dbQuery($sql));
    }

    function checkExploitId($card_id, $card_position) {
        $sql = "SELECT min(card_id) FROM exploit WHERE card_location = '" . $card_position . "'";

        $id = self::getUniqueValueFromDB($sql);
        //if ($id != $card_id)
        //    throw new BgaVisibleSystemException ( "You are not buying the first available card");
        //else
            return true;
        return false;
    }

    // get every info added to player TABLE
    function getPlayersAdditionnalInfo()
    {
        $this->players_info = null;

        if ( empty( $this->players_info ) )
        {
            $sql = "SELECT player_id AS id, player.* FROM player";
            $this->players_info = self::getCollectionFromDb( $sql );
        }

        return $this->players_info;
    }

    function dbIncBoar($player_id, $add = true) {
        //throw new feException(debug_print_backtrace());
        if ($add)
            $sql = 'UPDATE player set boar = boar + 1 WHERE player_id = ' . $player_id;
        else
            $sql = 'UPDATE player set boar = boar - 1 WHERE player_id = ' . $player_id;

        if ($add)
            $this->incStat(1, 'nb_boar', $player_id);

        self::dbQuery($sql);
    }

    //function dbIncMisfortune($player_id, $add = true) {
    //    if ($add)
    //        $sql = 'UPDATE player set misfortune = misfortune + 1 WHERE player_id = ' . $player_id;
    //    else
    //        $sql = 'UPDATE player set misfortune = misfortune - 1 WHERE player_id = ' . $player_id;
    //
    //    // TODO
    //    //if ($add)
    //    //    $this->incStat(1, 'nb_boar', $player_id);
    //
    //    self::dbQuery($sql);
    //}

    // return player_id owning the card
    // or false
    function isMisfortune($side) {
        if (substr($side, -10) == 'Misfortune' && $this->getBoar($side) != 0) {
            //throw new feException(debug_print_backtrace());
            return $this->getBoar($side);
        }
        else
            return false;
    }

    function dbIncTwins($player_id, $add = true) {
        if ($add)
            $sql = 'UPDATE player set twins = twins + 1 WHERE player_id = ' . $player_id;
        else
            $sql = 'UPDATE player set twins = twins - 1 WHERE player_id = ' . $player_id;

        if ($add)
            $this->incStat(1, 'nb_twins', $player_id);

        self::dbQuery($sql);
    }

    function dbUpdateThrow($player_id, $throwNum, $side_id) {
        $sql = "UPDATE player set throw_" . $throwNum . " = '" . $side_id . "' WHERE player_id = " . $player_id;
        self::dbQuery($sql);
    }

    function dbIncTriton($player_id, $add = true) {
        if ($add)
            $sql = 'UPDATE player set triton_token = triton_token + 1 WHERE player_id = ' . $player_id;
        else
            $sql = 'UPDATE player set triton_token = triton_token - 1 WHERE player_id = ' . $player_id;

        self::dbQuery($sql);
    }

    function dbIncCerberus($player_id, $add = true) {
        if ($add)
            $sql = 'UPDATE player set cerberus_token = cerberus_token + 1 WHERE player_id = ' . $player_id;
        else
            $sql = 'UPDATE player set cerberus_token = cerberus_token - 1 WHERE player_id = ' . $player_id;

        self::dbQuery($sql);
    }

    function dbUpdateExploitPlayed($card_id, $played) {
        if ($played)
            $sql = "UPDATE exploit SET card_type_arg = 1 WHERE card_id = " . $card_id;
        else
            $sql = "UPDATE exploit SET card_type_arg = 0 WHERE card_id = " . $card_id;
        return self::DbQuery( $sql );
    }

    function dbUpdateTokenPlayed($player_id, $token, $played) {
        if ($played)
            $sql = "UPDATE exploit SET card_type_arg = 1 WHERE card_type = '" . $token . "' AND card_location = 'pile1-" . $player_id . "'";
        else
            $sql = "UPDATE exploit SET card_type_arg = 0 WHERE card_type = '" . $token . "' AND card_location = 'pile1-" . $player_id . "'";
        return self::DbQuery( $sql );
    }

    function dbUpdateRolled($player_id, $rolled) {
        if ($rolled)
            $sql = "UPDATE player SET rolled = 1 where player_id = " . $player_id;
        else
            $sql = "UPDATE player SET rolled = 0 where player_id = " . $player_id;
        return self::DbQuery( $sql );
    }

    function updateAllThrows() {
        $sql  = "UPDATE token SET token_state = 0 WHERE token_key like 'throw%'";
        self::DbQuery($sql);

        $sql  = "UPDATE token SET token_state = 0 WHERE token_key like 'mirror%'";
        return self::DbQuery($sql);
    }

    /*
     * Update all the players that have no choice to do (therefore no cerberus token) and not rolled
     */
    function dbUpdateUnrolled() {
        $sql = "UPDATE player set rolled = 0 where ressource_choice = -1 AND rolled = 1 AND side_choice_1 = '0' AND side_choice_2 = '0'";
        return self::DbQuery( $sql );
    }

    function hasRolled ($player_id) {
        $sql = "SELECT rolled FROM player WHERE player_id = " . $player_id;
        $dbres = self::getUniqueValueFromDB($sql);

        if ($dbres == 0)
            return false;
        else
            return true;
    }

    function debugRessourcesAll() {
        $sql = "UPDATE player set res_gold = 12, res_fire=6, res_moon = 6";
        self::DbQuery( $sql );
        self::notifyAllPlayers("updateCounters", "", $this->getPlayersRessources());
    }

    function setChoice($player_id, $action, $boarAction = false, $debug= false)
    {
        $player = $this->getPlayersAdditionnalInfo()[$player_id];
        $old_value = $player['ressource_choice'];

        if ($action == self::RC_NOTHING_TODO && $this->hasCerberusToken($player_id)) { // && $player['side_choice_1'] == '0' && $player['side_choice_2'] == '0') {
            $this->dbSetChoice($player_id, self::RC_ACTION_CHOICE);
        }
        elseif ($action == self::RC_NOTHING_TODO || $action == self::RC_SIDE_CHOICE) { // || $old_value == RC_NOTHING_TODO) {
            $this->dbSetChoice($player_id, $action);
            return $action;
        }
        elseif ($action == self::RC_ACTION_CHOICE) {
            $this->dbSetChoice($player_id, $action);
            return $action;
        }
        //elseif (($old_value == RC_FORGESHIP && $action == RC_RESSOURCE) || ($old_value == RC_RESSOURCE && $action == RC_FORGESHIP)) {
        elseif ($action == self::RC_RESSOURCE || $action == self::RC_FORGESHIP || $action == null) {
            $player_chests = $this->hasChest( $player_id );
            $max_gold = self::MAX_GOLD + ( self::CHEST_GOLD * $player_chests ) + $this->tokens->countTokensInLocAndKey('scepter', $player_id) * 6;
            //$player_gold = $player['res_gold'];
            $player_gold = $this->getGold($player_id);
            $res_gold = 0;

            $side1 = $player['side_choice_1'];
            $side2 = $player['side_choice_2'];
            $boar = $player['boar'];
            $twins = $player['twins'];
            $resolveTwin = $this->tokens->getTokenState("resolveTwin_" . $player_id);
            $maze = false;
            $mazestock = $this->tokens->getTokenState('mazestock_' . $player_id);
            $puzzle = $this->tokens->getTokenState('puzzle_' . $player_id);
            $celestial = $this->tokens->getTokenState('celestialRunning');
            $celestialChoice = $this->tokens->getTokenLocation("celestial_choice");
            $misfortune = 0;

            //throw new feException($this->hasCerberusToken($player_id));

            if (strpos($side1, "Golem") !== false || strpos($side2, "Golem") !== false || $mazestock != 0)
                $maze = true;
            //if ($boar == 0)
            //throw new BgaUserException("side 1 " . $side1 . " side 2 " . $side2 . " boar " . $boar . " maze $maze");
            //throw new feException("side1 = $side1, side2 = $side2, boar = $boar, can use twins = " . $this->canUseTwins($player_id). ", throw1 = " . $this->tokens->getTokenState("throw1_$player_id") . ", throw2 = " . $this->tokens->getTokenState("throw2_$player_id") .", maze " . $maze . ", celestialChoice=" .$celestialChoice . ",");

            if ($resolveTwin == true) {
                //throw new feException("titi");
                $this->dbSetChoice($player_id, self::RC_RESSOURCE);
                return self::RC_RESSOURCE;
            }
            elseif (!$this->tokens->getTokenState("cerberus_" . $player_id) && $this->canUseTwins($player_id) &&
                (($side1 != '0' && $side1 != '-1' && $this->tokens->getTokenState("throw1_$player_id")) ||
                 ($side2 != '0' && $side2 != '-1' && $this->tokens->getTokenState("throw2_$player_id"))) //||
                 //$this->tokens->getTokenState("puzzle_" . $player_id) == 1)
               )
            {
                //throw new feException("top");
                $this->dbSetChoice($player_id, self::RC_ACTION_CHOICE);
                return self::RC_ACTION_CHOICE;
            }
            elseif ($celestialChoice != "0" && $celestialChoice == "ship") {
                //throw new feException('tt');
                $this->dbSetChoice($player_id, self::RC_FORGESHIP);
                return self::RC_FORGESHIP;
            }
            elseif ($celestialChoice != "0" && $celestialChoice != "" && $celestialChoice != null && $celestialChoice != "mirror") {
                //throw new feException('tt');
                $this->dbSetChoice($player_id, self::RC_RESSOURCE);
                return self::RC_RESSOURCE;
            }
            elseif ($celestialChoice == "mirror") {
                //throw new feException('tt');
                $this->dbSetChoice($player_id, self::RC_SIDE_CHOICE);
                return self::RC_SIDE_CHOICE;
            }
            elseif ($celestial && $this->canUseTwins($player_id)) {
                $this->dbSetChoice($player_id, self::RC_ACTION_CHOICE);
                return self::RC_ACTION_CHOICE;
            }
            elseif ($side1 == '-1' || $side2 == '-1') {
                $this->dbSetChoice($player_id, self::RC_SIDE_CHOICE);
                return self::RC_SIDE_CHOICE;
            }
            //elseif ($misfortune != 0) {
            //    // trigger misfortune
            //    $this->dbSetChoice($player_id, self::RC_MISFORTUNE);
            //    return self::RC_MISFORTUNE;
            //}
            // add double celestial roll
            elseif ($maze && $celestialChoice == "0" && $this->getGameStateValue("doubleCelestialRoll") == 1 && $action != self::RC_RESSOURCE) {
                //throw new feException("cele double");
                $this->dbSetChoice($player_id, self::RC_NOTHING_TODO);
                return self::RC_NOTHING_TODO;
            }
            elseif (!$maze && $side1 != 'ship' && $side2 != 'ship' && ($side1 != '0' || $side2 != '0') && $action != self::RC_RESSOURCE
                    && (!$this->canUseTwins($player_id) || !$this->tokens->getTokenState("cerberus_" . $player_id))) {
                //throw new feException (debug_print_backtrace());
                //throw new feException("bbbbb " . $action);
                $this->dbSetChoice($player_id, self::RC_NOTHING_TODO);
                return self::RC_NOTHING_TODO;
            }
            elseif (!$maze && $side1 != 'ship' && $side2 != 'ship' && ($side1 != '0' || $side2 != '0' || $boar != 0 )
                    && (!$this->canUseTwins($player_id) || !$this->tokens->getTokenState("cerberus_" . $player_id))) {
                //if ($player_id == 2305535)
                //if ($debug)
                //    throw new feException("side 1 $side1 side 2 $side2 boar $boar");
                //    throw new feException("bb");
                $this->dbSetChoice($player_id, self::RC_RESSOURCE);
                return self::RC_RESSOURCE;
                //$this->dbSetChoice($player_id, self::RC_NOTHING_TODO);
                //return self::RC_NOTHING_TODO;
            }
            elseif ($maze && ($side1 == 'ship' || $side2 == 'ship')) {
                $this->dbSetChoice($player_id, self::RC_ACTION_CHOICE);
                return self::RC_ACTION_CHOICE;
            }
            elseif ($side1 == 'ship' && ($side2 == '0' || $side2 == 'triple') && $boar == 0 && $twins == 0 && $misfortune == 0) {
                $this->dbSetChoice($player_id, self::RC_FORGESHIP);
                return self::RC_FORGESHIP;
            }
            elseif ($side2 == 'ship' && ($side1 == '0' || $side1 == 'triple') && $boar == 0 && $twins == 0 && $misfortune == 0) {
                $this->dbSetChoice($player_id, self::RC_FORGESHIP);
                return self::RC_FORGESHIP;
            }
            // ask use of token IF has a valid token & has not been enabled by boar
            //elseif ($side1 == '0' && $side2 == '0' && $boar == 0 && !$boarAction && $this->hasCerberusToken($player_id)) {
            elseif ($side1 == '0' && $side2 == '0' && $boar == 0 && $this->hasCerberusToken($player_id) && $twins == 0) {
                $this->dbSetChoice($player_id, self::RC_ACTION_CHOICE);
                //throw new feException("tt");
                return self::RC_ACTION_CHOICE;
            }
            elseif ($side1 == '0' && $side2 == '0' && $boar == 0 && $twins == 0 && $mazestock == 0 && $misfortune == 0) {
                //throw new feException("aa");
                $this->dbSetChoice($player_id, self::RC_NOTHING_TODO);
                return self::RC_NOTHING_TODO;
            }
            elseif (($boar != 0 || $twins != 0) && $side1 != 'ship' && $side2 != 'ship') {
                $this->dbSetChoice($player_id, self::RC_RESSOURCE);
                return self::RC_RESSOURCE;
            }
            elseif (($boar != 0  || $twins != 0) && (($side1 == 'ship' && $side2 == '0') || ($side1 == '0' && $side2 == 'ship'))) {
                $this->dbSetChoice($player_id, self::RC_RESSOURCE);
                return self::RC_RESSOURCE;
            }
            elseif($side2 == 'ship' && $side1 == 'ship') {
                $this->dbSetChoice($player_id, self::RC_FORGESHIP);
                return self::RC_FORGESHIP;
            }
            elseif (($side1 == "0" && $side2 != "0") || ($side2 == "0" && $side1 != "0")) {
                $this->dbSetChoice($player_id, self::RC_RESSOURCE);
                return self::RC_RESSOURCE;
            }
            elseif ($mazestock != 0 && $puzzle == 1) {
                $this->dbSetChoice($player_id, self::RC_MAZE);
                return self::RC_MAZE;
            }
            elseif ($this->tokens->getTokenLocation("mazechoice_" . $player_id) == "0" && $maze){
                //throw new feException('machin');
                $this->dbSetChoice($player_id, self::RC_NOTHING_TODO);
                return self::RC_NOTHING_TODO;
            }
            elseif ($side1 != 'ship' && $side1 != '0')
                $side = $side1;
            elseif ($side2 != 'ship' && $side2 != '0')
                $side = $side2;

            if (isset($side)) {
                $side_definition = $this->dice_sides[$side];

                if (isset($side_definition['ressource']['gold']) && $side_definition['ressource']['gold'] != 0)
                    $res_gold = $side_definition['ressource']['gold'];

                if ($max_gold - $player_gold < $res_gold) {
                    $this->dbSetChoice($player_id, self::RC_ACTION_CHOICE);
                    //throw new feException("toto6");
                    return self::RC_ACTION_CHOICE;
                }
                else{
                    $this->dbSetChoice($player_id, self::RC_RESSOURCE);
                    return self::RC_RESSOURCE;
                }
            }

        }

    }

    function isGoldLimitReached($player_id, $res_gold)
    {
        $player_chests = $this->hasChest( $player_id );
        $max_gold = self::MAX_GOLD + ( self::CHEST_GOLD * $player_chests ) + $this->tokens->countTokensInLocAndKey('scepter', $player_id) * 6;
        $player_gold = $this->getGold($player_id);
        //$max_gold = self::MAX_GOLD + ( self::CHEST_GOLD * $player_chests );
        //$player_gold = $this->getPlayersAdditionnalInfo()[$player_id]['res_gold'];

        if ($max_gold - $player_gold > $res_gold)
            return false;
        else
            return true;
    }

    // Depending on the value, different meaning
    //  value = -1 => no choice necessary
    //  value = 1 => Ressource
    //  value = 2 => Ship to forge
    //  value = 3 => Action Choice necessary
    //  value = 4 => Side Choice
    function dbSetChoice( $player_id, $value )
    {
        $sql = "UPDATE player SET ressource_choice = " . $value . " WHERE player_id = " . $player_id;
        return self::DbQuery( $sql );
    }

    function dbSetForge( $player_id, $value )
    {
        $sql = "UPDATE player SET forge = '" . $value . "' WHERE player_id = " . $player_id;
        return self::DbQuery( $sql );
    }

    // if $value == -1 : Side to choose
    // if $value == 0, nothing to choose
    // if $value == type of side, ok for ressourceChoice
    function dbSetSideChoice( $player_id, $side_num, $value )
    {
        if ($side_num < 50) {
            $sql = "UPDATE player SET side_choice_". $side_num . " = '" . $value . "' WHERE player_id = " . $player_id;
            return self::DbQuery( $sql );
        }
        elseif ($side_num == 99) {
            //if ($value == "0")
            //    $value = "none";
            $this->tokens->moveToken("mazechoice_" . $player_id, $value);
        }
        elseif ($side_num == 98) {
            if ($value == -1)
                $value = 'mirror';
            $this->tokens->moveToken("celestial_choice", $value);
        }
    }

    function dbSetPosition( $player_id, $value )
    {
        $sql = "UPDATE player SET position = '" . $value . "' WHERE player_id = " . $player_id;
        return self::DbQuery( $sql );
    }

    function getRessourceChoice($player_id)
    {
        $sql = "SELECT ressource_choice FROM player";

        if ($player_id != null)
            $sql .= ' WHERE player_id = ' . $player_id;

        $dbres = self::getUniqueValueFromDB($sql, true);

        return $dbres;
    }

    function dbSetGold( $player_id, $value )
    {
        $sql = "UPDATE player SET res_gold = " . $value . " WHERE player_id = " . $player_id;
        return self::DbQuery( $sql );
    }

    function dbSetMoonShard( $player_id, $value )
    {
        $sql = "UPDATE player SET res_moon = " . $value . " WHERE player_id = " . $player_id;
        return self::DbQuery( $sql );
    }

    function dbSetAncientShard( $player_id, $value )
    {
        $sql = "UPDATE player SET res_ancient = " . $value . " WHERE player_id = " . $player_id;
        return self::DbQuery( $sql );
    }

    function dbSetFireShard( $player_id, $value )
    {
        $sql = "UPDATE player SET res_fire = " . $value . " WHERE player_id = " . $player_id;
        return self::DbQuery( $sql );
    }

    function dbIncreaseVP( $player_id, $value )
    {
        $sql = "UPDATE player SET player_score = player_score + " . $value . " WHERE player_id = " . $player_id;
        return self::DbQuery( $sql );
    }

    function dbDecreaseVP( $player_id, $value )
    {
        $players_info = $this->getPlayersAdditionnalInfo();
        $player_score  = $players_info[ $player_id ]['player_score'];
        $new_score     = ( $player_score < $value ) ? 0 : $player_score - $value;


        $sql = "UPDATE player SET player_score = " . $new_score . " WHERE player_id = " . $player_id;
        return self::DbQuery( $sql );
    }

    function dbIncreaseHammer( $player_id, $value )
    {
        $sql = "UPDATE player SET hammer_position = hammer_position + " . $value . " WHERE player_id = " . $player_id;
        return self::DbQuery( $sql );
    }

    function dbInitHammer( $player_id )
    {
        $sql = "UPDATE player SET hammer_position = 0 WHERE player_id = " . $player_id . " and hammer_position is NULL";
        return self::DbQuery( $sql );
    }

    function hasAutoHammer ($player_id) {
        $sql = "SELECT hammer_auto FROM player where player_id = " . $player_id;
        return self::getUniqueValueFromDB($sql);
    }

    function setAutoHammer( $player_id, $enable )
    {
        if ($enable == 'enable')
            $sql = "UPDATE player SET hammer_auto = 1  WHERE player_id = " . $player_id;
        else
            $sql = "UPDATE player SET hammer_auto = 0 WHERE player_id = " . $player_id;
        return self::DbQuery( $sql );
    }

    function dbGetHammerPosition ($player_id)
    {
        $sql = "select hammer_position from player where player_id = " . $player_id;

        return (self::getUniqueValueFromDB($sql));
    }

    function countExploitInLocation( $type, $location, $location_arg = null )
    {
        $sql = "SELECT COUNT(card_id) AS nb FROM exploit
        WHERE card_type = '" . $type . "'
        AND card_location = '" . $location ."'";

        if ( $location_arg != null )
        {
            $sql .= " AND card_location_arg = '" . $location_arg . "'";
        }

        $dbres = self::DbQuery( $sql );
        $res   = mysql_fetch_assoc($dbres);

        return $res['nb'];
    }

    function checkValidSide ($side, $player_id = null, $exclude_player_id = null) {
        $sql = "SELECT distinct card_id from sides, player where (card_id = player.throw_1 or card_id = player.throw_2) and card_type = '" . $side . "' ";

        if ($player_id != null)
            $sql .= ' AND player_id = ' . $player_id;

        if ($exclude_player_id != null)
            $sql .= ' AND player_id != ' . $exclude_player_id;

        $dbres = self::getCollectionFromDB($sql);

        if ($dbres == null)
            // side not valid
            return false;
        else
            return true;

    }

    function checkValidVisibleSide ($side, $player_id = null, $exclude_player_id = null) {
        $sql = "SELECT distinct card_id from sides where card_type = '" . $side ."' and card_location_arg = 0";

        if ($player_id == null)
            $sql .= " and card_location like 'dice%'";
        else
            $sql .= " and card_location like 'dice%p". $player_id . "'";

        if ($exclude_player_id != null)
            $sql .= " and card_location not like 'dice%-p" . $exclude_player_id ."'";

        //$sql = "SELECT distinct card_id from sides, player where (card_id = player.throw_1 or card_id = player.throw_2) and card_type = '" . $side . "' ";
        //
        //if ($player_id != null)
        //  $sql .= ' AND player_id = ' . $player_id;
        //
        //if ($exclude_player_id != null)
        //  $sql .= ' AND player_id != ' . $exclude_player_id;

        $dbres = self::getCollectionFromDB($sql);

        if ($dbres == null)
            // side not valid
            return false;
        else
            return true;

    }

    function takeRessource2 ($player_id, $mode = 'blessing', $sideNum, $side, $ressource, $misfortune = false)
    {
        $triple = 1;
        $notifPlayerArgs                = [];
        $notifPlayerArgs['player_id']   = $player_id;
        $notifPlayerArgs['player_name'] = $this->loadPlayersBasicInfos()[$player_id]['player_name'];
        $notifPlayerArgs['choice']      = false;
        $couldFillHammer                = $this->canFillHammer($player_id);
        $player_info = $this->getPlayersAdditionnalInfo()[$player_id];
        $mirror = false;
        $multiple = 1;
        $exploit = false;
        $boar = false;
        $twins = false;
        $tripleBool = false;
        $maze = false;
        $twinsFinished = false;
        $celestial = false;

		// TODO : manage bonus if only is dice roll

        if ($sideNum == 3 && $player_info['boar'] != 0)
            $boar = true;

        if ($sideNum == 4 && $player_info['twins'] != 0)
            $twins = true;

        if ($sideNum == 1)
            $otherSide = 2;
        elseif ($sideNum == 99)
            $maze = true;
        elseif ($sideNum == 98)
            $celestial = true;
        else
            $otherSide = 1;

        if ($mode == 'loose')
            $multiple = -1;

        if ($boar){
            $multiple = 1;
            $mode = 'blessing';

        }

        if (!in_array($player_id, $this->gamestate->getActivePlayerList()))
            throw new BgaVisibleSystemException ( "Player not active!");
        //throw new feException($maze);
        // check side in side_choice
        if (!$boar && !$twins && !$maze && !$celestial) {
            if ($player_info['side_choice_' . $sideNum] != $side && !$misfortune)
                throw new BgaUserException(self::_("This side cannot be choosen"));
            elseif ($misfortune && $this->tokens->getTokenLocation('misfortune_' . $sideNum) != $side)
                throw new BgaUserException(self::_("This side cannot be choosen"));

            if ($this->checkValidSide("mirror", $player_id))
                $mirror = true;

            // check triple
            if ($this->tokens->getTokenState("triple_$player_id") == 1 || ($this->checkValidSide("triple", $player_id) && $mode != 'steal2' && !$this->canUseTwins($player_id))) {
                $triple = 3;
                $tripleBool = true;
            }

            // override triple as it cannot happen
            if ($misfortune) {
                $triple = 1;
                $tripleBool = false;
            }
        }
        // check in the token
        elseif ($maze) {
            if ($this->tokens->getTokenLocation("mazechoice_" . $player_id) != $side)
                //throw new feException($this->tokens->getTokenLocation("mazechoice_" . $player_id) . " " . $side);
                throw new BgaUserException(self::_("This side cannot be choosen"));
        }
        elseif ($celestial) {
            $celestialSide = $this->tokens->getTokenLocation("celestial_choice");
            if ($celestialSide != $side)
                throw new BgaUserException(self::_("This side cannot be choosen"));
        }
        // affect ressources
        $notifHammer = [];

        // check that ressource to update = ressource available
        $gold      = 0;
        $moonshard = 0;
        $fireshard = 0;
        $vp        = 0;
        $hammer    = 0;
        $scepters   = 0;
        $loyalty    = 0;
        $ancientshard = 0;

        foreach ($ressource as $res_name => $value) {
            if (isset($ressource[$res_name]) && $ressource[$res_name] != 0) {
                $value = $ressource[$res_name];

                if ($mode == 'loose')
                    $value = $value * -1;

                $res_choosen = $res_name;
                switch ($res_name) {
                    case 'gold':
                        $gold = $gold + $value;
                        if ($value > 0) {
                            $scepter = $this->increaseGold($player_id, $value, 'scepter');
                            $scepters += $scepter;

                            $this->increaseGold($player_id, $value - $scepter);

                            // affect gold/hammer only if it is linked to choice, else it has already been allocated
                            //if (!$celestial && $this->dice_sides[$side]['type'] != 'simple')
                            //    $notifPlayerArgs = $this->getLoyaltyReward($player_id, $sideNum, 'gold', $notifPlayerArgs);
                            //$gold = $gold + $value - $scepter;
                        }
                        else {
                            //$gold = $gold + $value;
                            $res = $this->decreaseGold($player_id, $value * -1, 'minotaur');
                            $gold = $gold - $res['scepter'];
                            $scepters = $res['scepter'];
                        }
                        $this->tokens->setTokenState("gold_" . $player_id, 1);
                        break;

                    case 'hammer':
                        if (!$this->canFillHammer($player_id)) {
                            //throw new BgaVisibleSystemException ("The player does not have an available hammer!");
                            //$gold = $gold + $value;
                            if ($value > 0) {
                                $scepter = $this->increaseGold($player_id, $value, 'scepter');
                                $scepters += $scepter;

                                $this->increaseGold($player_id, $value - $scepter);
                                $gold = $gold + $value - $scepter;

                                // affect gold/hammer only if it is linked to choice, else it has already been allocated
                                //if (!$celestial && $this->dice_sides[$side]['type'] != 'simple')
                                //    $notifPlayerArgs = $this->getLoyaltyReward($player_id, $sideNum, 'gold', $notifPlayerArgs);

                            }
                            else {
                                $gold = $gold + $value;
                                $this->decreaseGold($player_id, $value * -1, 'minotaur');
                            }
                        }
                        elseif($this->canFillHammer($player_id)) {
                            if ($mode == 'loose')
                                throw new BgaUserException(self::_("You cannot allocate a hammer"));

                            $gold = $gold + $value;
                            $hammer = $hammer + $value;

                            // If too much hammer has been sent => send only what is necessary remaining put to gold
                            $remainingGold = $this->maxHammer($player_id, $value);
                            $hammer = $hammer - $remainingGold;

                            $notifHammer[] = $this->increaseGold($player_id, $value - $remainingGold, 'hammer');

                            $scepter = $this->increaseGold($player_id, $remainingGold, 'scepter');
                            $scepters += $scepter;

                            $this->increaseGold($player_id, $remainingGold - $scepter);

                            // affect gold/hammer only if it is linked to choice, else it has already been allocated
                            //if (!$celestial && $this->dice_sides[$side]['type'] != 'simple') {
                            //    if ($this->canFillHammer($player_id))
                            //        $notifPlayerArgs = $this->getLoyaltyReward($player_id, $sideNum, 'hammer', $notifPlayerArgs);
                            //    else
                            //        $notifPlayerArgs = $this->getLoyaltyReward($player_id, $sideNum, 'gold', $notifPlayerArgs);
                            //}

                        }
                        $this->tokens->setTokenState("gold_" . $player_id, 1);
                        break;

                    case 'moonshard':
                        $moonshard = $moonshard + $value;
                        if ($value > 0)
                            $this->increaseMoonShard($player_id, $value);
                        else {
                            $this->decreaseMoonShard($player_id, $value * -1, 'minotaur');
                        }
                        $this->tokens->setTokenState("moonshard_" . $player_id, 1);
                        break;

                    case 'fireshard':
                        $fireshard = $fireshard + $value;
                        if ($value > 0)
                            $this->increaseFireShard($player_id, $value);
                        else {
                            $this->decreaseFireShard($player_id, $value * -1, 'minotaur');
                        }
                        $this->tokens->setTokenState("fireshard_" . $player_id, 1);
                        break;

                    case 'ancientshard':
                        $ancientshard = $ancientshard + $value;
                        if ($value > 0) {
                            $this->increaseAncientShard($player_id, $value);
                            // value * -1 as AncientShard moves you down
                            $this->tokens->incTokenState("movetitan_$player_id", $value * -1);
                        }
                        else {
                            $this->decreaseAncientShard($player_id, $value * -1, 'minotaur');
                            // no move as minotaur doesn't move loyalty
                        }
                        $this->tokens->setTokenState("ancientshard_" . $player_id, 1);
                        break;

                    case 'loyalty':
                        $loyalty = $loyalty + $value;
                        if ($value > 0) {
                            //$this->titanMove($player_id, $value);
                            $this->tokens->incTokenState("movetitan_$player_id", $value);
                        }
                        else {
                            // no move as minotaur doesn't move loyalty
                        }
                        $this->tokens->setTokenState("loyalty_" . $player_id, 1);
                        break ;

                    case 'vp':
                        $vp = $vp + $value;
                        if ($value > 0){
                            $this->increaseVP($player_id, $value);
                            if ($sideNum == 3)
                                $this->incStat($value, 'nb_vp_boar', $player_id);
                            else
                                $this->incStat($value, 'nb_vp_die', $player_id);

                            //if (!$celestial)
                            //    $notifPlayerArgs = $this->getLoyaltyReward($player_id, $sideNum, 'vp', $notifPlayerArgs);

                        }
                        else {
                            $this->decreaseVP($player_id, $value * -1);
                            $this->incStat($value * -1, 'nb_minotaur_vp', $player_id);
                        }
                        // check corresponding ressource
                        if ($mode == 'transform') {
                            $toTransform = $this->getRessourcesToTransform();
                            foreach ($this->dice_sides[$side]['ressource'] as $res => $value2) {
                                //throw new feException(print_r(in_array($res, $toTransform['ressource'])));
                                if (!isset($this->dice_sides[$side]['ressource']['vp']) && in_array($res, $toTransform['ressource'])) {
                                    //throw new feException("toto");
                                    $this->tokens->setTokenState($res . "_" . $player_id, 1);
                                }
                                elseif (in_array($res, $toTransform['ressource']) && isset($this->dice_sides[$side]['ressource']['vp']) && $value != $this->dice_sides[$side]['ressource']['vp']) {
                                    $this->tokens->setTokenState($res . "_" . $player_id, 1);
                                    //throw new feException("tota");
                                }
                                else
                                    $this->tokens->setTokenState("vp_" . $player_id, 1);
                            }

                            //$this->dice_sides[$side]['ressource']

                        }
                        else
                            $this->tokens->setTokenState("vp_" . $player_id, 1);
                        break;
                }
            }
        }


        $notifPlayerArgs['gold']      = $gold - $hammer - $scepters;
        $notifPlayerArgs['moonshard'] = $moonshard;
        $notifPlayerArgs['vp']        = $vp;
        $notifPlayerArgs['hammer']    = $hammer;
        $notifPlayerArgs['fireshard'] = $fireshard;
        $notifPlayerArgs['ancientshard'] = $ancientshard;
        $notifPlayerArgs['loyalty'] = $loyalty;
        $notifPlayerArgs['scepter'] = $scepters;
        $notifPlayerArgs['maze'] = 0;

        if ($side == 'moonGolem' || $side == 'sunGolem') {
            $this->tokens->incTokenState("mazestock_$player_id", 1);
            $notifPlayerArgs['maze'] = 1;
            $this->setGameStateValue( "monoRessourceChoice", 1);
        }

        // Control that only one ressource has been choosen
        if ($this->dice_sides[$side]['type'] == 'choice'
            && $mode != 'transform'
            && (($gold != 0 && ($moonshard != 0 || $fireshard != 0 || ($vp != 0 && $this->getLoyaltyArg($player_id, $sideNum, 'gold')['vp'] != 1))) || ($moonshard != 0 && ($gold != 0 || $fireshard != 0 || $vp != 0)) ||
                   ($fireshard != 0 && ($moonshard != 0 || $gold != 0 || $vp != 0)) ||
                   ($vp != 0 && $this->getLoyaltyArg($player_id, $sideNum, 'gold')['vp'] != 1 && ($moonshard != 0 || $fireshard != 0 || $gold != 0))))
           throw new BgaVisibleSystemException ("You can only select one resource.");

        if ($this->dice_sides[$side]['type'] == 'simple' && !$mirror && count($this->dice_sides[$side]['ressource']) != 1 && ($moonshard != 0 || $fireshard != 0 || ($vp != 0 && $this->getLoyaltyArg($player_id, $sideNum, 'gold')['vp'] != 1)) && $mode != 'steal2' && $mode != 'transform')
            throw new BgaUserException(self::_("You can only allocate gold"));

        if ($mode != 'loose' && $mode != 'transform') {
            if ( ((isset($this->dice_sides[$side]['ressource']['gold']) && $this->dice_sides[$side]['ressource']['gold'] * $triple + $this->getLoyaltyArg($player_id, $sideNum, 'gold', $misfortune)['gold'] < $gold) ||
                (isset($this->dice_sides[$side]['ressource']['moonshard']) && $this->dice_sides[$side]['ressource']['moonshard'] * $triple < $moonshard) ||
                (isset($this->dice_sides[$side]['ressource']['fireshard']) && $this->dice_sides[$side]['ressource']['fireshard'] * $triple < $fireshard) ||
                (isset($this->dice_sides[$side]['ressource']['vp']) && $this->dice_sides[$side]['ressource']['vp'] * $triple + $this->getLoyaltyArg($player_id, $sideNum, 'gold', $misfortune)['vp'] + $this->getLoyaltyArg($player_id, $sideNum, 'vp', $misfortune)['vp'] < $vp)) ||
                (!isset($this->dice_sides[$side]['ressource']['gold']) && $gold > 0) ||
                (!isset($this->dice_sides[$side]['ressource']['moonshard']) && $moonshard > 0) ||
                (!isset($this->dice_sides[$side]['ressource']['fireshard']) && $fireshard > 0) ||
                (!isset($this->dice_sides[$side]['ressource']['vp']) && $vp > $this->getLoyaltyArg($player_id, $sideNum, 'gold', $misfortune)['vp'])
               )
                   throw new BgaVisibleSystemException ("More resource allocated than authorized!");
        }
        elseif ($mode == 'loose') {
            // check that everything is allocated
            if (isset($this->dice_sides[$side]['ressource'][$res_choosen]) && $this->dice_sides[$side]['ressource'][$res_choosen] * $triple *-1!= $notifPlayerArgs[$res_choosen])
                   throw new BgaVisibleSystemException ("You need to allocate all the resources");
        }

        if (!$boar && !$twins && !$misfortune) {
            // clear sideNum
            $this->dbSetSideChoice($player_id, $sideNum, 0);
        }
        elseif ($boar){
            // decrease of boar
            $this->dbIncBoar($player_id, false);
        }
        elseif ($twins) {
            $this->dbIncTwins($player_id, false);
            if ($player_info['twins'] -1 == 0) {
                $this->resolveTwin($player_id, false);
                $twinsFinished = true;
            }
        }
        elseif ($maze) {
            $this->tokens->moveToken("mazechoice_" . $player_id, "0");
        }
        elseif ($celestial) {
            $this->tokens->moveToken("celestial_choice", "0");
        }

        if (!$maze && !$celestial && !$misfortune) {
            // Guardian's shield Management
            $otherSideType = $player_info['side_choice_' . $otherSide];
            if ($mode == 'transform') {
                $toTransform = $this->getRessourcesToTransform();
                $exploit = true;
            }
            else
                $toTransform = array('ressource' => array(), 'vp' => 1);

            if ($this->processedExploit() != "")
                $exploit = true;

            if (($this->doubleThrow($player_id) || $this->processedExploit() == 'steal2') && !$boar && !$twins && !in_array($side, $this->shields) && $otherSideType == "0") {
                $notifPlayerArgs = $this->checkShield($notifPlayerArgs, $multiple, false, $exploit);
                //$notifPlayerArgs = $this->checkPuzzle($notifPlayerArgs);
            }
            elseif (!$notifPlayerArgs['choice']) {
                //if ($otherSideType == 'redShield' || $otherSideType == 'greenShield' || $otherSideType == 'blueShield' || $otherSideType == 'yellowShield') {
                if (in_array($otherSideType, $this->shields)) {
                    $notifPlayerArgs = $this->affectRessource($player_id, $notifPlayerArgs, $otherSide, $otherSideType, $tripleBool, $multiple, false, true, $exploit);
                }
            }
        }
        //throw new feException(print_r($notifPlayerArgs));
        if (!($notifPlayerArgs['gold'] == 0 && $notifPlayerArgs['vp'] == 0 && $notifPlayerArgs['moonshard'] == 0 && $notifPlayerArgs['fireshard'] == 0 && $notifPlayerArgs['hammer'] == 0  && $notifPlayerArgs['scepter'] == 0 && $notifPlayerArgs['loyalty'] == 0 && $notifPlayerArgs['maze'] == 0 && $notifPlayerArgs['ancientshard'] == 0)) {
            // petit hack
            if ( $notifPlayerArgs['vp'] == 0 )
            {
                unset( $notifPlayerArgs['vp'] );
            }
            $notifPlayerArgs['ressources'] = $this->buildRessourceNotif( $notifPlayerArgs );
            if ( $mode == "loose" )
                self::notifyAllPlayers("notifBlessing", '${player_name} looses ${ressources}', $notifPlayerArgs );
            else
                self::notifyAllPlayers("notifBlessing", '${player_name} gets ${ressources}', $notifPlayerArgs );

        }
        else
            self::notifyAllPlayers("notifBlessing", '', $notifPlayerArgs);

        $this->generateNotifLoyalty ($notifPlayerArgs);

        $this->generateNotifHammer($player_id, $notifHammer);

        $this->titanMove($player_id);
                //throw new feException(debug_print_backtrace ());
        self::notifyAllPlayers("updateCounters", "", $this->getPlayersRessources());

        if (!$misfortune) {
            // reset as with celestial, only one choice can be done
            $this->setGameStateValue('celestialRunning', 0);


            $this->resolveTwin($player_id, true);
            $this->tokens->setTokenState("twinChoice_" . $player_id, 0);
        }
        else {
            $this->tokens->moveToken('misfortune_' . $sideNum, 'none');
            return false;
        }

        //throw new feException($twins . " " .$twinsFinished . " x". !$this->canUseTwins($player_id) . " x". $player_info['side_choice_'. $otherSide]);

        // necessary to get latest info
        $player_info = $this->getPlayersAdditionnalInfo()[$player_id];
        if ($twins && !$twinsFinished) {
            return false;
        }
        elseif ($maze) {
            $action = $this->setChoice($player_id, null, $boar);
            if ($action == self::RC_NOTHING_TODO || $action == self::RC_FORGESHIP)
                return true;
        }
        elseif ($celestial) {
            if ($player_info['twins'] > 0)
                $this->dbSetChoice($player_id, self::RC_RESSOURCE);
            else
                $this->dbSetChoice($player_id, self::RC_NOTHING_TODO);
            return true;
        }
        elseif ($player_info['side_choice_' . $otherSide] == "0" || $triple == 3){
            $this->dbSetSideChoice($player_id, $otherSide, 0);
            $this->tokens->setTokenState("triple_$player_id", 0);
            $action = $this->setChoice($player_id, null, $boar);
            $this->dbUpdateUnrolled();

            if ($action == self::RC_NOTHING_TODO || $action == self::RC_FORGESHIP)
                return true;
        }
        elseif ($player_info['side_choice_' . $otherSide] != "0" && $this->canUseTwins($player_id)) {
            $this->setChoice($player_id, null);
            return false;
        }
        //elseif ($player_info['side_choice_' . $otherSide] == "ship" && $player_info['boar'] == 0){
        elseif ((($player_info['side_choice_1'] == "ship"  && ($player_info['side_choice_2'] == "0" || $player_info['side_choice_2'] == "triple") ) ||
                 ($player_info['side_choice_2'] == "ship"  && ($player_info['side_choice_1'] == "0" || $player_info['side_choice_1'] == "triple")) ||
                 ($player_info['side_choice_1'] == "ship"  && $player_info['side_choice_2'] == "ship" ))
                && $player_info['boar'] == 0){
            // if ship, other state will be triggered. Disable the user
            $this->setChoice($player_id, self::RC_FORGESHIP);
            $this->dbUpdateUnrolled();
            return true;
        }
        // we need to change something for mirror if twins are all allocated and another twin cannot be played
        elseif ($twinsFinished && !$this->canUseTwins($player_id) && ($player_info['side_choice_1'] == "mirror" || $player_info['side_choice_2'] == "mirror")) {
            //throw new feException("mirror twin");
            if ($player_info['side_choice_1'] == "mirror" )
                $otherSide = 1;
            else
                $otherSide = 2;
            $this->dbSetSideChoice($player_id, $otherSide, -1);
            $this->setChoice($player_id, self::RC_SIDE_CHOICE);
        }
        elseif ((($twins && $twinsFinished) || !$twins) && !$this->canUseTwins($player_id) && $player_info['side_choice_'. $otherSide] != "0") {
            //throw new feException('test');
            $this->setChoice($player_id, self::RC_NOTHING_TODO);
            return true;
        }
//throw new feException("stop");
        return false;

    }

    function affectGoldOnly($player_id, $mode = 'blessing', $otherSideType, $sideNum) {
        $side_definition = $this->dice_sides[$otherSideType];
        $notifPlayerArgs                = [];
        $notifPlayerArgs['player_id']   = $player_id;
        $notifPlayerArgs['player_name'] = $this->loadPlayersBasicInfos()[$player_id]['player_name'];

        if ($side_definition['type'] == 'simple' && isset($side_definition['ressource']['gold']) && $side_definition['ressource']['gold'] != 0) {
            // we try to affect the ressources as some gold may need to be affected
            $notifPlayerArgs['gold']      = 0;
            $notifPlayerArgs['moonshard'] = 0;
            $notifPlayerArgs['vp']        = 0;
            $notifPlayerArgs['hammer']    = 0;
            $notifPlayerArgs['fireshard'] = 0;

            $value = $side_definition['ressource']['gold'];

            if ($mode == 'loose')
                $value = $value * -1;

            $notifPlayerArgs['gold'] = $value;
            if ($value > 0)
                $this->increaseGold($player_id, $value);
            else {
                $this->decreaseGold($player_id, $value * -1, 'minotaur');
            }

            if (!($notifPlayerArgs['gold'] == 0 && $notifPlayerArgs['vp'] == 0 && $notifPlayerArgs['moonshard'] == 0 && $notifPlayerArgs['fireshard'] == 0 && $notifPlayerArgs['hammer'] == 0  && $notifPlayerArgs['scepter'] == 0 && $notifPlayerArgs['maze'] == 0 && $notifPlayerArgs['loyalty'] == 0 && $notifPlayerArgs['ancientshard'] == 0)) {
            // petit hack
                if ( $notifPlayerArgs['vp'] == 0 )
                {
                    unset( $notifPlayerArgs['vp'] );
                }
                $notifPlayerArgs['ressources'] = $this->buildRessourceNotif( $notifPlayerArgs );
                if ( $mode == "loose" )
                    self::notifyAllPlayers("notifBlessing", '${player_name} looses ${ressources}', $notifPlayerArgs );
                else
                    self::notifyAllPlayers("notifBlessing", '${player_name} gets ${ressources}', $notifPlayerArgs );
            }

            $this->dbSetSideChoice($player_id, $sideNum, '0');
            return true;
        }
        return false;
    }

    function generateNotifHammer($player_id, $notifHammer) {
        if (count($notifHammer) != 0) {
            foreach ($notifHammer as $v => $notif)
                foreach ($notif as $v2 => $notifDef)
                if (count($notifDef) != 0)
                    self::notifyAllPlayers("notifHammerVP", clienttranslate('${player_name} completes the hammer (phase ${hammer_phase}) and scores ${ressources}'),
                        $notifDef);

            if(!$this->canFillHammer($player_id)) {
                self::notifyAllPlayers("notifRemoveHammer", '',
                    array(
                        'player_id' => $player_id
                    )
                );
            }
        }
    }

    function maxHammer($player_id, $value) {
        $hammer_position  = $this->getPlayersAdditionnalInfo()[ $player_id ]['hammer_position'];
        $remaining_hammer = $this->hasActiveHammer( $player_id ) * self::HAMMER_MAX_POSITION - $hammer_position;

        if ($value > $remaining_hammer)
            return $value - $remaining_hammer;
        else
            return 0;
    }

    function remainingHammer($player_id) {
        $hammer_position  = $this->getPlayersAdditionnalInfo()[ $player_id ]['hammer_position'];
        return $this->hasActiveHammer( $player_id ) * self::HAMMER_MAX_POSITION - $hammer_position;
    }

    /*
     * Format the notificaiton array text from the calculation of various functions
     * @return : array for the notification function
     */
    function buildRessourceNotif($args) {
        $hasScepter = false;
        $toAdd= '';

        //if (isset($args['player_id']) && $this->tokens->getTokensOfTypeInLocation('scepter_%', $args['player_id']) > 0)
        //    $toAdd = "/[S]";

        $notif = array();
        if ( isset($args['gold']) && $args['gold'] != 0 )
            $notif[] = abs( $args['gold'] ) . " [G]" . $toAdd;

        if ( isset($args['moonshard']) && $args['moonshard'] != 0 )
            $notif[] = abs( $args['moonshard'] ) . " [MS]";

        if ( isset($args['fireshard']) && $args['fireshard'] != 0 )
            $notif[] = abs( $args['fireshard'] ) . " [FS]";

        if ( isset($args['hammer']) && $args['hammer'] != 0 )
            $notif[] = abs( $args['hammer'] ) . " [H]";

        if ( isset($args['vp']) )
            $notif[] = abs( $args['vp'] ) . " [VP]";

        if ( isset($args['scepter']) &&  $args['scepter'] != 0)
            $notif[] = abs( $args['scepter'] ) . " [S]";

        if ( isset($args['ancientshard'])  && $args['ancientshard'] != 0)
            $notif[] = abs( $args['ancientshard'] ) . " [AS]";

        if (isset($args['maze']) && $args['maze'] != 0 )
            $notif[] = abs ($args['maze']) . " [M]";

        if (isset($args['loyalty']) && $args['loyalty'] != 0 )
            $notif[] = abs ($args['loyalty']) . " [L]";

        return implode(" ", $notif);
    }

    function canTakeSecondAction ($player_id) {
        if ($this->getGameStateValue( "secondActionTaken") != 0)
            return false;

        $sql = "SELECT res_fire from player WHERE player_id = '" . $player_id . "'";
        $res = self::getUniqueValueFromDB( $sql );
        $firePotential = $res;

        $sql = "SELECT res_ancient from player WHERE player_id = '" . $player_id . "'";
        $res = self::getUniqueValueFromDB( $sql );
        $firePotential += $res;

        $scepterFire = $this->getGameStateValue("scepterFireshard");
        $firePotential += $scepterFire;

        // test of waiting scepters
        $toConvert = 0;
        $scepters = $this->tokens->getTokensOfTypeInLocation("scepter_%", $player_id);
        foreach ($scepters as $key => $token) {
            $toConvert += $token['state'];
        }

        // can convert in 2FS
        if ($toConvert >= 6)
            $firePotential += 2;
        // can convert 1 scepter + use of one resource
        elseif ($toConvert >= 4)
            $firePotential += 1;

        // if triton token
        $sql = "SELECT triton_token from player WHERE player_id = '" . $player_id . "'";
        $res = self::getUniqueValueFromDB( $sql );

        if ($res > 0)
            $firePotential += 2;

        // companion
        $companion = $this->tokens->getTokensOfTypeInLocation("companion_%", $player_id);
        foreach ($companion as $key => $token) {
            // only if companion has not been used
            if ($token['state'] <= 5)
                $firePotential += $token['state'];
        }

        if ($firePotential >= 2)
            return true;

        return false;
    }


    function haveEnoughRessource ($player_id, $fireshard, $moonshard) {
        $sql = "SELECT res_fire as fire, res_moon as moon, res_ancient as ancient from player WHERE player_id = '" . $player_id . "'";
        $dbres = self::getObjectFromDB( $sql );

        $scepterFire = $this->getGameStateValue("scepterFireshard");
        $scepterMoon = $this->getGameStateValue("scepterMoonshard");

        if (($dbres['fire'] + $scepterFire + $dbres['ancient']) >= $fireshard && ($dbres['moon'] + $scepterMoon + $dbres['ancient']) >= $moonshard)
            return true;
        else
            return false;
    }

    function isIslandUsed ($player_id, $island) {
        $sql = "SELECT player_id FROM `player` where position = '" . $island . "' and player_id != '" . $player_id . "'";
        $res = self::getUniqueValueFromDB( $sql );

        return $res;
    }

    function canFillHammer($player_id) {
        if ($this->hasActiveHammer( $player_id ) * self::HAMMER_MAX_POSITION > $this->dbGetHammerPosition($player_id))
            return true;
        else
            return false;
    }

    // Will roll the dices and affect the ressources if possible
    // return true if a choice is needed
    function blessing($player_id, $dice1 = true, $dice2 = true, $multiple = 1, $exploit = false, $roll = true, $cerberus = false)
    {
        $notifPlayerArgs = array();
        $triple = $this->tokens->getTokenState("triple_$player_id");
        $choice = false;
        $ship = false;
        $maze = false;
        $toTransform = array();
        $player_info      = $this->getPlayersAdditionnalInfo()[$player_id];
        $otherSide = "0";
        $additionalResource = false;
        $doubleThrow = false;

        $notifPlayerArgs['player_id'] = $player_id;
        $notifPlayerArgs['player_name'] = $this->loadPlayersBasicInfos()[$player_id]['player_name'];
        $notifPlayerArgs['vp']          = 0;
        $notifPlayerArgs['gold']        = 0;
        $notifPlayerArgs['moonshard']   = 0;
        $notifPlayerArgs['fireshard']   = 0;
        $notifPlayerArgs['hammer']      = 0;
        $notifPlayerArgs['scepter']     = 0;
        $notifPlayerArgs['maze']        = 0;
        $notifPlayerArgs['loyalty']     = 0;
        $notifPlayerArgs['ancientshard']= 0;
        $notifPlayerArgs['choice']      = false;
        $notifPlayerArgs['roll']        = $roll;

        // init of token as not played
        if ($roll) {
            $this->dbUpdateTokenPlayed($player_id, 'cerberus', false);
            $this->dbUpdateRolled($player_id, true);
            //$this->updateAllThrows();
        }

        // Rolling of the player's dice
        if ($dice1 && $dice2 && $roll) {
            $result_sides= array($this->rollDice($player_id, 1),$this->rollDice($player_id, 2));

            $this->tokens->setTokenState("throw1_$player_id", 1);
            $this->tokens->setTokenState("throw2_$player_id", 1);

            $notifPlayerArgs['dice1'] = $result_sides[0]['type'];
            $notifPlayerArgs['dice2'] = $result_sides[1]['type'];
        }
        elseif ($dice1 && !$dice2 && $roll) {
            $result_sides= array($this->rollDice($player_id, 1));
            $this->tokens->setTokenState("throw1_$player_id", 1);
            $notifPlayerArgs['dice1'] = $result_sides[0]['type'];
            $otherSide = $player_info['side_choice_2'];
        }
        elseif (!$dice1 && $dice2 && $roll) {
            $result_sides= array($this->rollDice($player_id, 2));
            $this->tokens->setTokenState("throw2_$player_id", 1);
            $otherSide = $player_info['side_choice_1'];
            $notifPlayerArgs['dice2'] = $result_sides[0]['type'];
        }
        elseif ($dice1 && $dice2 && !$roll) {
            $side = array ('type' => $player_info['side_choice_1']);
            if ($side['type'] == '0')
                $side = $this->sides->getCard($player_info['throw_1']);
            $result_sides[0] = $side;
            //$side = $this->sides->getCard($player_info['throw_1']);
            //$result_sides[0] = $side;
            $side = array ('type' => $player_info['side_choice_2']);
            if ($side['type'] == '0')
                $side = $this->sides->getCard($player_info['throw_2']);
            //$side = $this->sides->getCard($player_info['throw_2']);
            $result_sides[1] = $side;

            $notifPlayerArgs['dice1'] = $result_sides[0]['type'];
            $notifPlayerArgs['dice2'] = $result_sides[1]['type'];
        }
        elseif ($dice1 && !$dice2 && !$roll) {
            $side = array ('type' => $player_info['side_choice_1']);
            $otherSide = $player_info['side_choice_2'];
            if ($side['type'] == '0')
                $side = $this->sides->getCard($player_info['throw_1']);
            $result_sides[0] = $side;
            $notifPlayerArgs['dice1'] = $result_sides[0]['type'];
        }
        elseif (!$dice1 && $dice2 && !$roll) {
            $side = array ('type' => $player_info['side_choice_2']);
            $otherSide = $player_info['side_choice_1'];
            //throw new feException(print_r($side));
            if ($side['type'] == '0')
                $side = $this->sides->getCard($player_info['throw_2']);
            $result_sides[0] = $side;
            $notifPlayerArgs['dice2'] = $result_sides[0]['type'];
        }
        else
            $result_sides = array();

        // Management of twins and special sides where we need to add already affected resources
        // only when rolling only one die
        if (($dice1 && !$dice2) || (!$dice1 && $dice2)) {
            if (!$dice1)
                $otherDie = 1;
            else
                $otherDie = 2;

            if ($this->tokens->getTokenState("throw$otherDie" . "_" . $player_id) && $player_info["side_choice_$otherDie"] == "0" && $result_sides[0]['type'] == 'triple') {
                $additionalSide = $this->sides->getCard($player_info["throw_$otherDie"]);
                $additionalResource = true;
            }
        }

        if ($this->tokens->getTokenState("throw1_$player_id") == 1 && $this->tokens->getTokenState("throw2_$player_id"))
            $doubleThrow = true;

        $card_id = $this->getGameStateValue( "exploitBought");
        if ($card_id != -1 && $exploit) {
            $card = $this->exploits->getCard($card_id);
            $card_info = $this->exploit_types[$card['type']];

            switch($card_info['action']) {
                case 'oustAll':
                    $ousted_player_id = $this->getGameStateValue('oustedPlayerId');
                    $ousted_info      = $this->getPlayersAdditionnalInfo()[$ousted_player_id];

                    if ($dice1 && $dice2) {
                        $side = $this->sides->getCard($ousted_info['throw_1']);
                        $result_sides[0] = $side;
                        $side = $this->sides->getCard($ousted_info['throw_2']);
                        $result_sides[1] = $side;

                        $notifPlayerArgs['dice1'] = $result_sides[0]['type'];
                        $notifPlayerArgs['dice2'] = $result_sides[1]['type'];
                    }
                    elseif ($dice1 && !$dice2) {
                        $side = $this->sides->getCard($ousted_info['throw_1']);
                        $result_sides[0] = $side;
                        $notifPlayerArgs['dice1'] = $result_sides[0]['type'];
                        $otherSide =  $this->sides->getCard($ousted_info['throw_2'])['type'];
                    }
                    elseif (!$dice1 && $dice2) {
                        $side = $this->sides->getCard($ousted_info['throw_2']);
                        $result_sides[0] = $side;
                        $notifPlayerArgs['dice2'] = $result_sides[0]['type'];
                        $otherSide =  $this->sides->getCard($ousted_info['throw_1'])['type'];
                    }
                    break ;
            }
        }

        if ($roll) {
            $text = clienttranslate('${player_name} rolls ${sides_rolled}');

            $displaySides = "";

            if ($dice1)
                $displaySides .= $notifPlayerArgs['dice1'];
            if ($dice1 && $dice2)
                $displaySides .= ",";
            if ($dice2)
                $displaySides .= $notifPlayerArgs['dice2'];

            self::notifyAllPlayers("notifMessage", $text,
                array(
                    'player_name' => $notifPlayerArgs['player_name'],
                    'sides_rolled' => $displaySides,
                )
            );
        }

        // If a user has drawn two triple sides
        if(!$additionalResource && (($dice1 && !$dice2 && $notifPlayerArgs['dice1'] == 'triple') || (!$dice1 && $dice2 && $notifPlayerArgs['dice2'] == 'triple')
           || ($dice1 && $dice2 && $notifPlayerArgs['dice1'] == 'triple' && $notifPlayerArgs['dice2'] == 'triple'))) {
            self::notifyAllPlayers("notifBlessing", '', $notifPlayerArgs);
            $this->tokens->setTokenState("triple_$player_id", 1);
            if ($dice1)
                $this->dbSetSideChoice($player_id, "1", 0);
            if ($dice2)
                $this->dbSetSideChoice($player_id, "2", 0);

            return false;
        }

        // Twins
        // no allocation to be done if twin available & at least 3G
        if ($roll && $this->canUseTwins($player_id)) {
            $this->tokens->setTokenState("triple_$player_id", 0);
           if ($dice1)
                $this->dbSetSideChoice($player_id, "1", $notifPlayerArgs['dice1']);
           if ($dice2)
                $this->dbSetSideChoice($player_id, "2", $notifPlayerArgs['dice2']);
            $this->setChoice($player_id, self::RC_ACTION_CHOICE);
            self::notifyAllPlayers("notifBlessing", '', $notifPlayerArgs);
            return true;
        }

        // if a user has a drawn a mirror (in classical blessing), nothing must be done before the choice
        // (case of selection of a triple side for the other ressource)
        if ($dice1 && $dice2 && ($result_sides[0]['type'] == 'mirror' || $result_sides[1]['type'] == 'mirror')) {
            $this->dbSetChoice($player_id, self::RC_SIDE_CHOICE);
            if($result_sides[0]['type'] == 'mirror') {
                $this->dbSetSideChoice($player_id, "1", -1);

                if (substr($result_sides[1]['type'], -4) == 'Boar'  && $this->getBoar($result_sides[1]['type']) != 0) {
                    $boarId = $this->getBoar($result_sides[1]['type']);
                    $this->dbIncBoar($boarId, true);
                    $this->setChoice($boarId, self::RC_RESSOURCE);
                }

                // misfortune : nothing to do, mirror & misfortune cannot be together
            }
            // bug # 13272
            //elseif ($result_sides[0]['type'] != 'triple')
            else
                $this->dbSetSideChoice($player_id, "1", $result_sides[0]['type']);

            if($result_sides[1]['type'] == 'mirror') {
                $this->dbSetSideChoice($player_id, "2", -1);

                if (substr($result_sides[0]['type'], -4) == 'Boar'  && $this->getBoar($result_sides[0]['type']) != 0) {
                    $boarId = $this->getBoar($result_sides[0]['type']);
                    $this->dbIncBoar($boarId, true);
                    $this->setChoice($boarId, self::RC_RESSOURCE);
                }

                // misfortune : nothing to do, mirror & misfortune cannot be together
            }
            // bug # 13272
            //elseif ($result_sides[1]['type'] != 'triple')
            else
                $this->dbSetSideChoice($player_id, "2", $result_sides[1]['type']);

            self::notifyAllPlayers("notifBlessing", '', $notifPlayerArgs);
            return true;
        }

        // #Rebellion
        // If 2 dice rolled, if loyalty/ancient on one face and gold in the other, action_choice must be triggered
        if ($dice1 && $dice2) {
            $hasGold = false;
            $hasTitan = false;
            foreach ($result_sides as $id => $side) {
                $side_definition = $this->dice_sides[$side['type']];
                if (isset($side_definition['ressource']['gold']) && $side_definition['ressource']['gold'] != 0)
                    $hasGold = true;

                if (isset($side_definition['ressource']['loyalty']) && $side_definition['ressource']['loyalty'] != 0 && $side_definition['type'] != 'dependent')
                    $hasTitan = true;

                if (isset($side_definition['ressource']['ancientshard']) && $side_definition['ressource']['ancientshard'] != 0  && $side_definition['type'] != 'dependent')
                    $hasTitan = true;
            }

            if ($hasGold && $hasTitan) {
                $this->dbSetSideChoice($player_id, "1", $notifPlayerArgs['dice1']);
                $this->dbSetSideChoice($player_id, "2", $notifPlayerArgs['dice2']);
                // check if we need to process misfortune
                if ($this->setChoice($player_id, null) != self::RC_MISFORTUNE)
                    $this->dbSetChoice($player_id, self::RC_ACTION_CHOICE);
                self::notifyAllPlayers("notifBlessing", '', $notifPlayerArgs);
                return true;
            }

        }

        // Test specific sides
        foreach($result_sides as $side_num => $side) {
            //if (substr($side['type'], -4) == 'Boar')
            //    var_dump($this->getBoar($side['type']));
            if ($side['type'] == 'triple') {
                $triple = true;
                $this->tokens->setTokenState("triple_$player_id", 1);
            } elseif ($side['type'] == 'ship') {
                $ship = true;
            }

            // if a side is a boar, increment boar counter of the given player)
            // enable of boar here only if dice has been rolled or use of cerberus token
            elseif (substr($side['type'], -4) == 'Boar' && $this->getBoar($side['type']) != 0 && ($roll || $cerberus)) {
                //var_dump("toto");
                //throw new feException(debug_print_backtrace());
                $this->dbIncBoar($this->getBoar($side['type']), true);
                $this->setChoice($this->getBoar($side['type']), self::RC_RESSOURCE);
            }
            // do we have to move in the maze?
            elseif (strpos($side['type'], "Golem") !== false) {
                $maze = true;
            }
            // Misfortune
            elseif (self::isMisfortune($side['type']) != false) {
                $mis_player_id = self::isMisfortune($side['type']);
                $this->tokens->moveToken('resolveMisfortune', $side['type'], $mis_player_id);
                //$this->dbIncMisfortune($mis_player_id, true);
                //$this->setChoice($mis_player_id, self::RC_RESSOURCE);
            }

        }

        // trigger behavior coming from sides coming from the twins
        if (!$this->canUseTwins($player_id) && $otherSide != "0") {
            if ($otherSide == 'triple') {
                $triple = true;
                $this->tokens->setTokenState("triple_$player_id", 1);
            }
            elseif ($otherSide == 'ship')
                $ship = true;
            // #32759 : If twins were rolled (>= 1) then we need to enable
            // if a side is a boar, increment boar counter of the given player)
            elseif ((substr($otherSide, -4) == 'Boar' && $this->getBoar($otherSide) != 0) && $this->getPlayersAdditionnalInfo()[$player_id]['twins'] >= 1) {
                $this->dbIncBoar($this->getBoar($otherSide), true);
                $this->setChoice($this->getBoar($otherSide), self::RC_RESSOURCE);
            }

        }

        //if ($player_id == 2303388)
        //throw new feException("phoque");
        // if triple, the player owning the boar card must choose 2 more times
        if ($triple) {
            foreach($result_sides as $side_num => $side) {
                // Boar
                if (substr($side['type'], -4) == 'Boar' && $this->getBoar($side['type']) != 0) {
                    $this->dbIncBoar($this->getBoar($side['type']), true);
                    $this->dbIncBoar($this->getBoar($side['type']), true);
                }
                // Misfortune : nothing to do, user will get the triple result
            }
        }

        // if ship && gold limit reached, do not allocate anything
        // VTO 190228
        if (($ship || $maze) && $multiple != -1) {
            foreach ($result_sides as $id => $side) {
                $res_gold = 0;
                $side_definition = $this->dice_sides[$side['type']];

                if (isset($side_definition['ressource']['gold']) && $side_definition['ressource']['gold'] != 0)
                    $res_gold = $side_definition['ressource']['gold'];

                if ($this->isGoldLimitReached($player_id, $res_gold) && $res_gold > 0) {
                    if ($dice1)
                        $this->dbSetSideChoice($player_id, "1", $notifPlayerArgs['dice1']);
                    if ($dice2)
                        $this->dbSetSideChoice($player_id, "2", $notifPlayerArgs['dice2']);

                    // $this->setChoice($player_id, null);
                    $this->dbSetChoice($player_id, self::RC_ACTION_CHOICE);
                    self::notifyAllPlayers("notifBlessing", '', $notifPlayerArgs);
                    return true;
                }
            }
        }

        // Ressource allocation if possible
        if ($dice1 && $dice2) {
            $notifPlayerArgs = $this->affectRessource($player_id, $notifPlayerArgs, 1, $result_sides[0]['type'], $triple, $multiple, $ship, false, $exploit);
            $notifPlayerArgs = $this->affectRessource($player_id, $notifPlayerArgs, 2, $result_sides[1]['type'], $triple, $multiple, $ship, false, $exploit);
        }
        elseif ($dice1 && !$dice2) {
            if ($doubleThrow)
                $notifPlayerArgs = $this->affectRessource($player_id, $notifPlayerArgs, 1, $result_sides[0]['type'], $triple, $multiple, $ship, false, $exploit);
            else
                $notifPlayerArgs = $this->affectRessource($player_id, $notifPlayerArgs, 1, $result_sides[0]['type'], $triple, $multiple, $ship, true, $exploit);
        }
        elseif (!$dice1 && $dice2) {
                if ($doubleThrow)
                    $notifPlayerArgs = $this->affectRessource($player_id, $notifPlayerArgs, 2, $result_sides[0]['type'], $triple, $multiple, $ship, false, $exploit);
                else
                    $notifPlayerArgs = $this->affectRessource($player_id, $notifPlayerArgs, 2, $result_sides[0]['type'], $triple, $multiple, $ship, true, $exploit);
        }

        //  We need to affect twice the previous side because the player has selected triple after having taken the first side
        if ($additionalResource) {
            $notifPlayerArgs = $this->affectRessource($player_id, $notifPlayerArgs, $otherDie, $additionalSide['type'], false, $multiple, $ship, true, $exploit);
            $notifPlayerArgs = $this->affectRessource($player_id, $notifPlayerArgs, $otherDie, $additionalSide['type'], false, $multiple, $ship, true, $exploit);
            // choice can only exist linked to twins reroll
            if ($notifPlayerArgs['choice'] == true) {
                $notifPlayerArgs = $this->affectTriple($player_id, $notifPlayerArgs, $otherDie, $additionalSide['type'], false, $multiple, $ship, true, $exploit);
                $notifPlayerArgs['choice'] = false;
            }
            $this->dbSetSideChoice($player_id, 1, "0");
            $this->dbSetSideChoice($player_id, 2, "0");
        }

        // only when affect of one ressource but double Throw (Twins)
        if ($doubleThrow && (($dice1 && !$dice2) || (!$dice1 && $dice2))) {
            // if shield/titan, do nothing, will be tested later on
            if (in_array($result_sides[0]['type'], array('blueShield', 'redShield', 'greenShield', 'yellowShield', 'titanBlueShield', 'titanYellowShield', 'titanGreenShield', 'titanRedShield'))) {
                if ($dice1)
                    $this->dbSetSideChoice($player_id, "1", "0");
                else
                    $this->dbSetSideChoice($player_id, "2", "0");
            }
        }

        if ($notifPlayerArgs['choice'])
            $choice = true;

        $notifPlayerArgs = $this->checkShield($notifPlayerArgs, $multiple, $ship, $exploit);
        $notifPlayerArgs = $this->checkPuzzle($notifPlayerArgs);
        $choice = $notifPlayerArgs['choice'];

        // deprecated use of triple token
        //// If ressourcechoice is necessary & triple, we need to put the triple info
        //if(($choice || $ship) && $dice1 && $notifPlayerArgs['dice1'] == 'triple')
        //    $this->dbSetSideChoice($player_id, 1, 'triple');
        //elseif (($choice || $ship) && $dice2 && $notifPlayerArgs['dice2'] == 'triple')
        //    $this->dbSetSideChoice($player_id, 2, 'triple');

        if (!($notifPlayerArgs['gold'] == 0 && $notifPlayerArgs['vp'] == 0 && $notifPlayerArgs['moonshard'] == 0 && $notifPlayerArgs['fireshard'] == 0 && $notifPlayerArgs['hammer'] == 0 && $notifPlayerArgs['scepter'] == 0 && $notifPlayerArgs['maze'] == 0 && $notifPlayerArgs['loyalty'] == 0 && $notifPlayerArgs['ancientshard'] == 0))
        {
            // petit hack
            if ( $notifPlayerArgs['vp'] == 0 )
            {
                unset( $notifPlayerArgs['vp'] );
            }
            $notifPlayerArgs['ressources'] = $this->buildRessourceNotif( $notifPlayerArgs );
            if ( $multiple == -1 )
                self::notifyAllPlayers("notifBlessing", clienttranslate('${player_name} looses ${ressources}'), $notifPlayerArgs );
            else {
                if ($additionalResource) {
                    $notifPlayerArgs['side_type'] = "triple";
                    self::notifyAllPlayers("notifBlessing", clienttranslate('${player_name} gets additional ${ressources} due to ${side_type} selection'), $notifPlayerArgs );
                }
                else
                    self::notifyAllPlayers("notifBlessing", clienttranslate('${player_name} gets ${ressources}'), $notifPlayerArgs );
                if ( $exploit )
                    self::notifyAllPlayers("notifPauseDice", "", array());
            }
        }
        else
            self::notifyAllPlayers("notifBlessing", '', $notifPlayerArgs);

        $this->generateNotifLoyalty ($notifPlayerArgs);

        if (isset($notifPlayerArgs['notifHammer']) && count($notifPlayerArgs['notifHammer']) != 0) {
            $this->generateNotifHammer($player_id, $notifPlayerArgs['notifHammer']);
        }

        $this->titanMove($player_id);

        // Oracle reinforcement being used
        if ($this->getGameStateValue("oracleReinforcement") == 1 && ($notifPlayerArgs['loyalty'] != 0 || $notifPlayerArgs['ancientshard'] != 0)) {
            $this->oracleMove($player_id);
            $this->setGameStateValue("oracleReinforcement", 0);
        }
        // Maze management
        // No move in the maze if there is a previous choice
        //if (!$choice)
        //    $this->mazeManagement($player_id);

        $this->dbUpdateUnrolled();

        return $choice;
    }

    /*
     * @return true if it can continue, false if we have a choice
     */
    function mazeManagement($player_id, $new_position = null, $onlyOne = false)
    {
        $mazestock   = $this->tokens->getTokenState('mazestock_' . $player_id);
        $timeGolem   = $this->getGameStateValue("timeGolem");
        $player_info = $this->getPlayersAdditionnalInfo()[$player_id];
        $position = 0;

        // only when we have puzzle effect to autorize celestial roll between
        if ($onlyOne)
            $mazestock = 1;

        if ($mazestock < 0 || $timeGolem < 0) {
            $way = "decrease";
        } elseif ($mazestock > 0) {
            $way = "increase";
        } else {
            if ($player_info['ressource_choice'] == self::RC_MAZE)
                $this->dbSetChoice($player_id, self::RC_NOTHING_TODO);

            return true;
        }

        for ($i = 0; $i < (abs($mazestock) + abs($timeGolem)); $i++) {
            $position = $this->tokens->getTokenState("position_" . $player_id);
            $this->dbSetChoice($player_id, self::RC_NOTHING_TODO);

            // nothing to do as we are on the last square
            if ($position == 36) {
                $this->tokens->setTokenState("mazestock_" . $player_id, 0);
                $this->setGameStateValue("timeGolem", 0);
                break ;
                //return true;
            }
            // moving before first case
            elseif ($position == 1) {
                if ($mazestock < 0)
                    $this->tokens->setTokenState("mazestock_" . $player_id, 0);
                if ($timeGolem < 0)
                    $this->setGameStateValue("timeGolem", 0);
                if ($timeGolem <= 0 && $mazestock <= 0)
                    break ;
            }

            if ($mazestock < 0 || $timeGolem < 0) {
                $way = "decrease";
            } elseif ($mazestock > 0) {
                $way = "increase";
            }

            // check if decision => setchoice
            //$choice = $this->mazeHasChoice($player_id, $position, $way);
            //if ($choice !== false) {
            //    $this->dbSetChoice($player_id, self::RC_MAZE);
            //    $this->setGameStateInitialValue("monoRessourceChoice", 1);
            //    return false;
            //}
            $mazeSquare = $this->maze[$position];

            // choice of path is necessary?
            if ($new_position == null) {

                if (($way == "increase" && count($mazeSquare['path']) > 1) || ($way == "decrease" && isset($mazeSquare['reverse']) && count($mazeSquare['reverse']) > 1)) {
                    $this->dbSetChoice($player_id, self::RC_MAZE);
                    $this->setGameStateValue("mazeForcePath", 1);
                    $this->setGameStateInitialValue("monoRessourceChoice", 1);

                    return false;
                }

                if ($way == 'increase')
                    $new_position = $this->maze[$position]['path'][0];
                else
                    $new_position = $this->maze[$position]['reverse'][0];

            }

            //// if player side choice => attendre
            // should not happen
            //if (in_array($player_info['ressource_choice'], array(self::RC_RESSOURCE, self::RC_SIDE_CHOICE, self::RC_ACTION_CHOICE)))
            //    throw new feException("truru");

            // move & decrease stock
            // if we are on double roll, we do not move on the second throw
            if ($this->getGameStateValue("doubleCelestialRoll") == 0) {
                $this->mazeMove($player_id, $position, $new_position);
                if ($way == 'decrease' && $timeGolem != 0)
                    $timeGolem++;
                elseif ($way == 'decrease' && $mazestock < 0)
                    $mazestock++;
                else
                    $mazestock--;
            }

            // first time that we will roll the celestial roll
            if ($this->getGameStateValue("doubleCelestialRoll") == 0 && $this->maze[$new_position]['reward'] == 'celestialRollx2' ) {
                if ($way == 'increase') {
                    $mazestock++;
                    //$this->tokens->setTokenState("mazestock_" .$player_id, $mazestock - $i - 1);
                    $this->tokens->setTokenState("mazestock_" .$player_id, $mazestock - $i );
                }
                else {
                    if ($timeGolem != 0) {
                        $timeGolem--;
                        $this->incGameStateValue("timeGolem", -1);
                    }
                    else {
                        $mazestock--;
                        $this->tokens->setTokenState("mazestock_" .$player_id, $mazestock - ($i * -1));
                    }
                }

            }
            elseif ($this->getGameStateValue("doubleCelestialRoll") == 1) { // && $this->maze[$position]['reward'] == 'celestialRollx2' ) {
                if ($way == 'increase')
                    $this->tokens->incTokenState("mazestock_" . $player_id, -1);
                else {
                    if ($timeGolem != 0){
                        $this->incGameStateValue("timeGolem", 1);
                    }
                    else {
                        $this->tokens->incTokenState("mazestock_" . $player_id, 1);
                    }
                }
            }

            // trigger the effect
            $choice = $this->mazeEffect($player_id);
            $position = $this->tokens->getTokenState("position_" . $player_id);
            // nothing to do as we have reached the last square
            if ($position == 36) {
                $this->tokens->setTokenState("mazestock_" . $player_id, 0);
            }


            $player_info = $this->getPlayersAdditionnalInfo()[$player_id];

            if ($player_info['ressource_choice'] == self::RC_FORGESHIP) {
                break ;
            }
             //$player_info = $this->getPlayersAdditionnalInfo()[$player_id];
             //throw new feException($player_info['ressource_choice'] . " " . $choice);
            if ($choice) {
                //$player_info = $this->getPlayersAdditionnalInfo()[$player_id];
                //throw new feException($player_info['ressource_choice'] . " " . $choice);
                return false;
            }

            // if possibility to launch the puzzle effect, choice needed
            if ($this->tokens->getTokenState("puzzle_" . $player_id) == 1 && $this->tokens->getTokenState("mazestock_" . $player_id) != 0 ) {
                $this->dbSetChoice($player_id, self::RC_MAZE);
                return false;
            }

            // for next run
            $new_position = null;
        }

        //throw new feException("titi12");
        $player_info = $this->getPlayersAdditionnalInfo()[$player_id];

        if ($player_info['ressource_choice'] != self::RC_FORGESHIP) {
            $this->triggerCelestialWhenMaze0($player_id);
        }
        //throw new feException(print_r($player_info));
        return true;
    }

    // will roll the celestial die only if no more maze & puzzle effect confirmed
    function triggerCelestialWhenMaze0($player_id) {
        // trigger celestial if no more mazestock
        if ($this->tokens->getTokenState("puzzle_" . $player_id) == 1 && $this->tokens->getTokenState("mazestock_" . $player_id) == 0) {
            $this->actPuzzleCelestial(true);
            return true;
        }
        return false;
    }

    function mazeMove($player_id, $position, $next_position)
    {
        $increase = true;
        $timeGolem = $this->getGameStateValue("timeGolem");

        // check position
        $DBposition = $this->tokens->getTokenState("position_" . $player_id);
        if ($DBposition != $position)
            throw new BgaVisibleSystemException("Incorrect position");

        // check that next position is possible
        $mazeSquare = $this->maze[$position];

        if ($this->tokens->getTokenState("mazestock_" . $player_id) > 0 && $timeGolem == 0)
            $increase = true;
        else
            $increase = false;

        if (!((in_array($next_position, $mazeSquare['path']) && $increase) ||
            (in_array($next_position, $mazeSquare['reverse']) && !$increase)))
                throw new BgaVisibleSystemException("The next square is incorrect");

        // check at least one of movement
        if ((abs($this->tokens->getTokenState("mazestock_" . $player_id)) + abs($timeGolem)) < 1)
            throw new BgaVisibleSystemException("No move in the maze can be done");

        $this->tokens->setTokenState("position_" . $player_id, $next_position);

        if ($increase)
            $this->tokens->incTokenState("mazestock_" . $player_id, -1);
        elseif ($timeGolem != 0)
            $this->incGameStateValue("timeGolem", 1);
        else
            $this->tokens->incTokenState("mazestock_" . $player_id, 1);

        if ($this->getGameStateValue("doubleCelestialRoll") == 1)
            $msg = "";
        else
            $msg = clienttranslate('${player_name} moves in the Goddess\' Labyrinth to ${position}');

        self::notifyAllPlayers("notifMazeMove", $msg, [
            'player_name'  => $this->loadPlayersBasicInfos()[$player_id]['player_name'],
            'player_color' => $this->loadPlayersBasicInfos()[$player_id]['player_color'],
            'player_id'    => $player_id,
            'position'     => $next_position
        ]);

    }

    function mazeEffect($player_id)
    {
        $position        = $this->tokens->getTokenState("position_" . $player_id);
        $mazeSquare      = $this->maze[$position];
        $notifPlayerArgs = $this->initNotif($player_id);
        $forge           = false;
        $firstFinish     = false;

        $reward = $mazeSquare['reward'];

        $this->tokens->setTokenState("triple_". $player_id, 0);

		// On maze, no loyalty effect should be triggered
        switch ($reward) {
            case "FS1":
            case "V3":
            case "V5":
            case "FS1MS1V3":
            case "G6":
            case "G6orV3":
            case "MS1":
            case "G3orMS1orFS1":
            case "MS2orV3":
            case "MS2orFS2":
                $notifPlayerArgs = $this->affectRessource($player_id, $notifPlayerArgs, 99, $reward, false, 1, false, false, false);
                break ;
            case "steal2VP":
                 $players_info = $this->getPlayersAdditionnalInfo();
                $gain['vp'] = 0;
                foreach ($players_info as $aff_player_id => $player_info) {
                    if ($player_id != $aff_player_id) {
                        $lost = array();
                        if ($player_info['player_score'] >= 2) {
                            $this->decreaseVP($aff_player_id, 2);
                            $lost['vp'] = 2;
                            $gain['vp'] += 2;
                        }

                        if (count($lost) != 0) {
                            self::notifyAllPlayers("notifEffect", clienttranslate('${player_name} looses ${ressources} due to the maze square'),
                                array(
                                    'player_name' => $player_info['player_name'],
                                    'ressources' => $this->buildRessourceNotif($lost)
                                )
                            );
                        }
                    }
                }

                if ($gain['vp'] != 0) {
                    $this->increaseVP($player_id, $gain['vp']);
                    self::notifyAllPlayers("notifEffect", clienttranslate('${player_name} gains ${ressources} due to the maze square'),
                        array(
                            'player_name' => $players_info[$player_id]['player_name'],
                            'ressources' => $this->buildRessourceNotif($gain)
                        )
                    );
                }
                break ;
            case "treasure":

                // Not the first to be here => get resource
                if (count($this->tokens->getTokensOfTypeInLocation("treasure%", $position)) == 1) {
                    $tok = $this->tokens->getTokensOfTypeInLocation("treasure%", $position);
                    $type = array_keys($tok)[0];

                    switch ($type) {
                        case "treasure_fireshard":
                            $notifPlayerArgs = $this->affectRessource($player_id, $notifPlayerArgs, 99, "FS1", false, 1, false, false, false);
                            break ;
                        case "treasure_moonshard":
                            $notifPlayerArgs = $this->affectRessource($player_id, $notifPlayerArgs, 99, "MS1", false, 1, false, false, false);
                            break ;
                        case "treasure_vp":
                            $notifPlayerArgs = $this->affectRessource($player_id, $notifPlayerArgs, 99, "V2", false, 1, false, false, false);
                            break ;
                    }
                } else { // choice is necessary
                    $notifPlayerArgs['choice'] = true;
                }
                break ;
            case "celestialRoll":
                $this->resetTwins();
                $this->resetThrowTokens($player_id);
                // trigger roll
                $celestial = $this->rollCelestial($player_id, true);
                if ($celestial == 'doubleUpgrade'){
                    //goto Forge ship
                    $this->dbSetChoice($player_id, self::RC_FORGESHIP);
                    $notifPlayerArgs['choice'] = true;
                    $forge = true;
                    //$this->setChoice($player_id, null);
                    //$toto = true;
                }
                elseif (in_array($celestial, array("V3G3orFS1orMS1", "celestialMirror", "chooseSide"))) {
                    $notifPlayerArgs['choice'] = true;
                    $forge = true;
                }
                else if ($celestial == 'G12' && $this->canFillHammer($player_id) && !$this->hasAutoHammer($player_id)) {
                    $notifPlayerArgs['choice'] = true;
                    $forge = true;
                }
                // Twins => reroll possible
                elseif ($celestial === null) {
                    $notifPlayerArgs['choice'] = true;
                    $this->setGameStateInitialValue("monoRessourceChoice", 1);
                    $this->dbSetChoice($player_id, self::RC_ACTION_CHOICE);
                    return true;
                }
                break ;
            case "celestialRollx2":
                $this->resetTwins();
                $this->resetThrowTokens($player_id);
                // update global to say have been rolled once
                $secondRoll = $this->getGameStateValue("doubleCelestialRoll");
                if ($this->getGameStateValue("doubleCelestialRoll") == 0) {
                    $this->setGameStateValue("doubleCelestialRoll", 1);
                    // update position & mazestock to go one step back
                    //$this->tokens->incTokenState("position_" . $player_id, -1);
                    //$this->tokens->incTokenState("mazestock_" . $player_id, 1);
                }
                // in function mazemove, do not notify if double celestial roll
                // trigger roll
                $celestial = $this->rollCelestial($player_id, true);

                if ($celestial == 'doubleUpgrade'){
                    //goto Forge ship
                    $this->dbSetChoice($player_id, self::RC_FORGESHIP);
                    $notifPlayerArgs['choice'] = true;
                    $forge = true;
                    //$this->setChoice($player_id, null);
                    //$toto = true;
                }
                elseif (in_array($celestial, array("V3G3orFS1orMS1", "celestialMirror", "chooseSide"))) {
                    $notifPlayerArgs['choice'] = true;
                    $forge = true;
                }
                else if ($celestial == 'G12' && $this->canFillHammer($player_id) && !$this->hasAutoHammer($player_id)) {
                    $notifPlayerArgs['choice'] = true;
                    $forge = true;
                }
                // Twins => reroll possible
                elseif ($celestial === null) {
                    $notifPlayerArgs['choice'] = true;
                    $this->setGameStateInitialValue("monoRessourceChoice", 1);
                    $this->dbSetChoice($player_id, self::RC_ACTION_CHOICE);

                    if ($secondRoll)
                        $this->setGameStateValue("doubleCelestialRoll", 0);

                    return true;
                }

                if ($secondRoll)
                    $this->setGameStateValue("doubleCelestialRoll", 0);
                break ;
            case "forgeShip":
                $this->dbSetChoice($player_id, self::RC_FORGESHIP);
                $this->setGameStateValue("mazeForge", 1);
                // Only due to exploit management
                if ($this->gamestate->state()['name'] == 'exploitEffect' || $this->gamestate->state()['name'] == 'playerOustingChoice')
                    $notifPlayerArgs['choice'] = true;
                else
                    $notifPlayerArgs['choice'] = false;
                break ;
            case "forge":
                $this->setGameStateValue("forgeClassical", 1);
                $this->setGameStateValue("mazeForge", 1);
                $this->dbSetChoice($player_id, self::RC_FORGESHIP);
                // no choice to trigger as we want to go to forge state
                // Only due to exploit management
                if ($this->gamestate->state()['name'] == 'exploitEffect' || $this->gamestate->state()['name'] == 'playerOustingChoice')
                    $notifPlayerArgs['choice'] = true;
                else
                    $notifPlayerArgs['choice'] = false;
                //$notifPlayerArgs['choice'] = false;
                break ;
            case "convert6Gto6VP":
                // does player has 6 gold
                if ($this->getGold($player_id) >= 6) {
                    // confirmation is mandatory
                    $notifPlayerArgs['choice'] = true;
                    $this->setGameStateValue("mazeForcePath", 1);
                }
                break ;
            case "convertMS2to8VP":
                //$scepterMoon = $this->getGameStateValue("scepterMoonshard");

                //if (($scepterMoon + $this->getPlayersAdditionnalInfo()[$player_id]['res_moon']) >= 2) {
                if (($this->getPlayersAdditionnalInfo()[$player_id]['res_moon']) >= 2 || $this->hasTritonToken($player_id)) {
                    $notifPlayerArgs['choice'] = true;
                    $this->setGameStateValue("mazeForcePath", 1);
                }
                break ;
            case "none":
                return false;
                break ;
            case "scoreForgedSides":
                $this->scoreForgedSides($player_id);
                break ;
            case "V15":
                $notifPlayerArgs = $this->affectRessource($player_id, $notifPlayerArgs, 99, $reward, false, 1, false, false, false);
                if ($this->getGameStateValue("mazeFinished") == 0) {
                    // first to finish the maze, can apply the Goddess effect
                    $this->setGameStateValue("mazeFinished", 1);
                    $this->dbSetSideChoice($player_id, 1, -1);
                    $this->dbSetSideChoice($player_id, 2, -1);
                    $this->dbSetChoice($player_id, self::RC_SIDE_CHOICE);
                    $notifPlayerArgs['choice'] = true;
                    $firstFinish = true;
                    $this->setGameStateValue("firstFinish", $player_id);

                    self::notifyAllPlayers("notifMessage", clienttranslate('${player_name} is the first to complete the maze. ${player_name} chooses the visible face of each die'),
                            array(
                                'player_name' => $this->loadPlayersBasicInfos()[$player_id]['player_name'],
                            )
                        );

                }
                break ;
            default:
                break ;
        }

        // notify
        if (! ($notifPlayerArgs['gold'] == 0
            && $notifPlayerArgs['vp'] == 0
            && $notifPlayerArgs['moonshard'] == 0
            && $notifPlayerArgs['fireshard'] == 0
            && $notifPlayerArgs['hammer'] == 0
            && $notifPlayerArgs['scepter'] == 0
            && $notifPlayerArgs['maze'] == 0
            && $notifPlayerArgs['loyalty'] == 0
            && $notifPlayerArgs['ancientshard'] == 0)
        ) {
            // petit hack
            if ( $notifPlayerArgs['vp'] == 0 ) {
                unset( $notifPlayerArgs['vp'] );
            }

            $notifPlayerArgs['ressources'] = $this->buildRessourceNotif( $notifPlayerArgs );
            self::notifyAllPlayers("notifBlessing", clienttranslate('${player_name} gets ${ressources}'), $notifPlayerArgs );
        }

        $this->generateNotifLoyalty ($notifPlayerArgs);

        if (isset($notifPlayerArgs['notifHammer']) && count($notifPlayerArgs['notifHammer']) != 0) {
            $this->generateNotifHammer($player_id, $notifPlayerArgs['notifHammer']);
        }

        self::notifyAllPlayers("updateCounters", "", $this->getPlayersRessources());


        if ($notifPlayerArgs['choice'] == true) {
            if ($this->getGameStateValue("mazeForge") == 0 && !$forge && !$firstFinish) {
                $this->dbSetChoice($player_id, self::RC_MAZE);
            }

            $this->setGameStateInitialValue("monoRessourceChoice", 1);
        }

        return $notifPlayerArgs['choice'];
    }

    function mazeHasChoice($player_id, $mazePosition, $way) {
        // TODO: check if choice is needed => (hammer & gold, road, treasure, celestial?, ship)
        $mazeSquare = $this->maze[$mazePosition];

        // choice of path is necessary
        if ($way == "increase" && count($mazeSquare['path']) > 1)
            return "way";
        elseif ($way == "decrease" && isset($mazeSquare['reverse']) && count($mazeSquare['reverse']) > 1)
            return "way";

        $nextSquare = $this->maze[$mazeSquare['path'][0]];

        // Gold must be choosen as there is a hammer
        if ($nextSquare['hasGold'] && $this->canFillHammer($player_id) && !$this->hasAutoHammer($player_id) )
            return "gold";

        // do we need a choice?
        //if ($nextSquare['needChoice'])
        //    return "choice";

        // We are on a treasure square and no token has been choosen
        if ($nextSquare['reward'] == "treasure" && count($this->tokens->getTokensOfTypeInLocation("treasure_%", $mazeSquare['path'][0])) == 0)
            return "treasure";

        // check ship?
        // check forge?
        // what about celestial?

        return false;

    }

    function doubleThrow($player_id) {
        if ($this->tokens->getTokenState("throw1_$player_id") && $this->tokens->getTokenState("throw2_$player_id"))
            return true;
        else
            return false;
    }

	function isThrown ($player_id, $side) {
		if ($this->tokens->getTokenState("throw" . $side . "_" . $player_id))
            return true;
        else
            return false;
	}

    // check if puzzle is ok. If OK, get celestial Roll
    function checkPuzzle($notifPlayerArgs)
    {
        $player_id = $notifPlayerArgs['player_id'];
        $player_info      = $this->getPlayersAdditionnalInfo()[$player_id];

        $doubleThrow = false;
        $choice = $notifPlayerArgs['choice'];

        if (($this->tokens->getTokenState("throw1_$player_id") && $this->tokens->getTokenState("throw2_$player_id")) || $this->processedExploit() == 'steal2')
            $doubleThrow = true;

        if ($doubleThrow == false)
            return $notifPlayerArgs;

        // check all side choosen
        if ($player_info["side_choice_1"] != "0" || $player_info["side_choice_2"] != "0")
            return $notifPlayerArgs;

        $result_sides = array (1 => $this->sides->getCard($player_info['throw_1']), 2 => $this->sides->getCard($player_info['throw_2']));

        // mirror + twins management
        if ($this->tokens->getTokenState("mirror1_$player_id") != 0)
            $result_sides[1] = $this->sides->getCard($this->tokens->getTokenState("mirror1_$player_id"));

        if ($this->tokens->getTokenState("mirror2_$player_id") != 0)
            $result_sides[2] = $this->sides->getCard($this->tokens->getTokenState("mirror2_$player_id"));

        //throw new feException(print_r($result_sides));
        if (($result_sides[1]['type'] == 'moonGolem' && $result_sides[2]['type'] == 'sunGolem')
            || ($result_sides[2]['type'] == 'moonGolem' && $result_sides[1]['type'] == 'sunGolem')
        ) {
            if ($this->tokens->getTokenState("puzzle_" . $player_id) == 0) {
                $this->tokens->setTokenState("puzzle_" . $player_id, 1);
                $this->setChoice($player_id, null);
            }
            //throw new feException($this->getPlayersAdditionnalInfo()[$player_id]['ressource_choice']);
        }

        //throw new feException (debug_print_backtrace());
        return $notifPlayerArgs;
    }

    function checkShield ($notifPlayerArgs, $multiple, $ship, $exploit)
    {
        $player_id = $notifPlayerArgs['player_id'];
        $triple = $this->tokens->getTokenState("triple_$player_id");
        //$toTransform = array();
        $player_info      = $this->getPlayersAdditionnalInfo()[$player_id];
        $otherSide = "0";
        $additionalResource = false;
        $doubleThrow = false;
        $choice = $notifPlayerArgs['choice'];

        if (($this->tokens->getTokenState("throw1_$player_id") && $this->tokens->getTokenState("throw2_$player_id")) || $this->processedExploit() == 'steal2' || $this->processedExploit() == 'oustAll')
            $doubleThrow = true;

        // check all side choosen
        if ($player_info["side_choice_1"] != "0" || $player_info["side_choice_2"] != "0") {
            // bug Misfortune trigerred twice
            if (self::isMisfortune($player_info["side_choice_1"]) != 0 || self::isMisfortune($player_info["side_choice_2"]) != 0 || ($choice && $multiple == -1))
                $this->setChoice($player_id, self::RC_RESSOURCE);
            return $notifPlayerArgs;
        }

        $result_sides = array (1 => $this->sides->getCard($player_info['throw_1']), 2 => $this->sides->getCard($player_info['throw_2']));

        // mirror + twins management
        if ($this->tokens->getTokenState("mirror1_$player_id") != 0)
            $result_sides[1] = $this->sides->getCard($this->tokens->getTokenState("mirror1_$player_id"));

        if ($this->tokens->getTokenState("mirror2_$player_id") != 0)
            $result_sides[2] = $this->sides->getCard($this->tokens->getTokenState("mirror2_$player_id"));

        /******************** Guardian's shield management *******************/
        // if god blessing and guardian's shield, shield has not been affected and no choice is necessary
        if ($doubleThrow) {
            $thrownSide = null;

            foreach ($result_sides as $side_num => $side) {
                $side_definition = $this->dice_sides[$result_sides[$side_num]['type']];
                if ($side_definition['type'] == 'simple') {
                    $thrownSide = $side_definition;
                }
            }

            foreach ($result_sides as $side_num => $side) {
                $side_definition = $this->dice_sides[$result_sides[$side_num]['type']];
                if ($side_definition['type'] != 'dependent') {
                    continue ;
                }
                //throw new feException($this->tokens->getTokenState("fireshard_$player_id"));
                //throw new BgaUserException(print_r($thrownSide));
                // OLD if we have the associated ressource on the side (simple sides only)
                //if ($thrownSide != null && isset($thrownSide['ressource'][$side_definition['dependentRessource']]) && $thrownSide['ressource'][$side_definition['dependentRessource']] != 0) {
                // if we have affected the dependentRessource
                if ($this->tokens->getTokenState($side_definition['dependentRessource'] . "_$player_id")) {
                    foreach ($side_definition['gainA'] as $res => $value) {
                        switch ($res) {
                            case 'vp':
                                if ($multiple != -1) {
                                    $this->increaseVP($player_id, $value);
                                    $this->incStat($value, 'nb_vp_guardian', $player_id);
                                    $notifPlayerArgs = $this->getLoyaltyReward($player_id, $side_num, 'vp', $notifPlayerArgs);
                                }
                                else{
                                    $this->decreaseVP($player_id, $value);
                                    $this->incStat($value, 'nb_minotaur_vp', $player_id);
                                }
                                $notifPlayerArgs['vp'] = $notifPlayerArgs['vp'] + $value * $multiple;

                                break;
                            case 'loyalty':
                                if ($multiple != -1) {
                                    $this->tokens->incTokenState("movetitan_$player_id", $value);
                                }
                                $notifPlayerArgs['loyalty'] = $notifPlayerArgs['loyalty'] + $value * $multiple;
                                break ;
                            case 'ancientshard':
                                if ($multiple != -1) {
                                    $this->increaseAncientShard($player_id, $value);
                                    // value * -1 because ancient shard must move back
                                    $this->tokens->incTokenState("movetitan_$player_id", $value * -1);
                                }
                                else{
                                    $this->decreaseAncientShard($player_id, $value * -1, 'minotaur');
                                }
                                $notifPlayerArgs['ancientshard'] = $notifPlayerArgs['ancientshard'] + $value * $multiple;
                                break ;
                        }
                    }
                    $this->dbSetSideChoice($player_id, $side_num, 0);
                }
                // else we affect the "standard" ressource
                elseif (!$choice) {
                    $notifPlayerArgs = $this->affectRessource($player_id, $notifPlayerArgs, $side_num, $side['type'], $triple, $multiple, $ship, true, $exploit);
                }
                //if (!$notifPlayerArgs['choice'])
                //  $this->dbSetSideChoice($player_id, $side_num +1, 0);
            }

            if ($this->getPlayersAdditionnalInfo()[$player_id]['boar'] == 0 && $multiple != -1) {
                $this->setChoice($player_id, null);
            }
            elseif  ($multiple == -1 && $choice) {
                $this->setChoice($player_id, self::RC_RESSOURCE);
            }
        }
        return $notifPlayerArgs;
    }

    function scoreForgedSides($player_id, $exploit = true) {
        $discardedSides = count($this->sides->getCardsInLocation('discard', $player_id));
        $this->increaseVP($player_id, $discardedSides);
        if ($exploit)
            $this->incStat($discardedSides, 'nb_vp_typhon', $player_id);
        self::notifyAllPlayers("notifMessage", clienttranslate('${player_name} has forged ${discardedSides} sides therefore gains ${ressources}'),
            array(
                'player_name' => $this->getPlayersAdditionnalInfo()[$player_id]['player_name'],
                'discardedSides'   => $discardedSides,
                'ressources'    => $discardedSides . ' [VP]'
            )
        );
    }

    function titanMove ($player_id) {
        if ($this->getGameStateValue( "rebellion") == 3) {
            $titanStock = $this->tokens->getTokenState("movetitan_" . $player_id);
            $player_info = $this->getPlayersAdditionnalInfo()[$player_id];
            $initialPosition = $this->tokens->getTokenState("position_" . $player_id);
            $lastPosition = $initialPosition;

            if ($titanStock < 0) {
                $way = "decrease";
            } elseif ($titanStock > 0) {
                $way = "increase";
            }

            for ($i = 0; $i < abs($titanStock); $i++) {
                $position = $this->tokens->getTokenState("position_" . $player_id);

                if (($position == 1 && $way == 'decrease') || ($position == 31 && $way == 'increase')) {
                    // we are at the last spot, do nothing
                    $this->tokens->setTokenState("movetitan_" . $player_id, 0);
                    continue ;
                }

                if ($way == 'decrease') {
                    $this->tokens->setTokenState("position_" . $player_id, $this->titan_board[$position]['toDark']);
                    $this->tokens->incTokenState("movetitan_" . $player_id, 1);
                }
                elseif ($way == 'increase') {
                    $this->tokens->setTokenState("position_" . $player_id, $this->titan_board[$position]['toLight']);
                    $this->tokens->incTokenState("movetitan_" . $player_id, -1);
                }
                $lastPosition = $this->tokens->getTokenState("position_" . $player_id);;
            }

            if ($lastPosition != $initialPosition) {
                // notif move
                self::notifyAllPlayers("notifTitanMove", clienttranslate('${player_name} moves in the Titan path to ${position}'), [
                    'player_name'  => $this->loadPlayersBasicInfos()[$player_id]['player_name'],
                    'player_color' => $this->loadPlayersBasicInfos()[$player_id]['player_color'],
                    'player_id'    => $player_id,
                    'position'     => $lastPosition
                ]);
            }

        }

        return ;
    }

//////////////////////////////////////////////////////////////////////////////
//////////// Player actions
////////////

    // Will launch the diceRoll and notify the results to the JS
    function actRollDice()
    {
        self::checkAction( "actRollDice" );
        $this->gamestate->nextState('blessing');
    }

    function actDraft($exploit) {
        self::checkAction( "actDraft" );
        $player_id = self::getActivePlayerId();
        $nb_players = $this->getGameStateValue( "nbPlayers");
        $boars = ['redBoar', 'yellowBoar', 'blueBoar', 'greenBoar'];
        $hydraPromo = ['harpy', 'chimera', 'monsterMother', 'shadowgoddess'];

        //throw new BgaUserException($this->exploit_slot[array_search('M3', $this->exploit_slot)]);

        // get the slot
        $slot = $this->exploit_slot[$this->getGameStateValue('slotBeingDrafted')];

        // check exploit is linked to the slot
        if ($this->exploit_types[$exploit]['position'] != $slot)
            throw new BgaVisibleSystemException ("This exploit is not linked to the slot being drafted!");

        // init the cards
        $test = 0;
        $c = array();
        if (substr($exploit, -4) == 'Boar') {
            // we need to put all available boar
            foreach ($boars as $pos => $boar)
                if ($test < $nb_players) {
                    $c[] = array('type' => $boar, 'type_arg' => '0', 'nbr' => 1);
                    $test++;
                }
        }
        elseif ($exploit == 'hydraPromo') {
            foreach ($hydraPromo as $pos => $hydPm)
                if ($test < $nb_players) {
                    $c = array();
                    $c[] = array('type' => $hydPm, 'type_arg' => 0, 'nbr' => 1);
                    $this->exploits->createCards($c, $slot);
                    $test++;
                }
        }
        else
            $c[0] = array('type' => $exploit, 'type_arg' => '0', 'nbr' => $nb_players);

        if ($exploit != 'hydraPromo')
            $this->exploits->createCards($c, $slot);

        // notify
        self::notifyAllPlayers("notifDraft", clienttranslate('${player_name} has choosen ${exploit_type}'),
            array(
                'i18n'         => array('exploit_type'),
                'exploit_type' => $this->exploit_types[$exploit]['name'],
                'exploit'      => $this->exploits->getCardsInLocation($slot),
                'slot'         => $slot,
                'exploitName'  => $exploit,
                'player_name'  => self::getActivePlayerName(),
            )
        );
        // if no more available slot => start game
        $this->gamestate->nextState('draft');

    }

    function actAutoHammer($todo) {
        $player_id = self::getCurrentPlayerId();
        $this->setAutoHammer($player_id, $todo);
        self::notifyPlayer( $player_id, 'notifAutoHammer', '', array('done' => $todo));
        return ;
    }

    // $ressources : array of the choosen ressources
    //  WARNING  in the JS, only the side with choice must be managed.
    // if side with gold and FS (for example), the FS will not be sent by the JS
    function actTakeRessource($sideNum, $side, $ressources)
    {
        self::checkAction( "actRessourceChoice" );
        $disable = false;
        $player_id = self::getCurrentPlayerId();
        $twins = false;

        if ($this->getPlayersAdditionnalInfo()[$player_id]['twins'] != 0)
            $twins = true;

        $disable = $this->takeRessource2($player_id, 'blessing', $sideNum, $side, $ressources);
        $this->incStat(1, 'nb_ressource_choice', $player_id);

        if ($disable) {
            // do not unflag triple, as it may be needed if we are managing twins
            if (!$twins)
                $this->tokens->setTokenState("triple_$player_id", 0);
            $this->triggerCelestialWhenMaze0($player_id);
            //throw new feException($this->getPlayersAdditionnalInfo()[$player_id]['ressource_choice']);
            $this->gamestate->setPlayerNonMultiactive($player_id, 'blessing');
        }
        else
             //throw new feException($this->getPlayersAdditionnalInfo()[$player_id]['ressource_choice']);
            $this->gamestate->nextState('choice');
    }

    function actMisfortuneChoice($sideNum, $side, $ressources)
    {
        self::checkAction( "actMisfortuneChoice" );
        $disable = false;
        $player_id = self::getCurrentPlayerId();


        $disable = $this->takeRessource2($player_id, 'blessing', $sideNum, $side, $ressources, true);

        $side1 = $this->tokens->getTokenLocation('misfortune_1');
        $side2 = $this->tokens->getTokenLocation('misfortune_2');

        if ($side1 == 'none' && $side2 == 'none') {
            $this->gamestate->setPlayerNonMultiactive($player_id, 'choice');
        }
        else
            $this->gamestate->nextState('choice');
    }

    // Type of the sides to choose
    function actSideChoice($side1, $side2, $side98)
    {
        self::checkAction( "actSideChoice" );

        $player_id = self::getCurrentPlayerId();
        $player_info      = $this->getPlayersAdditionnalInfo()[$player_id];

        $notifPlayerArgs = $this->initNotif($player_id);

        //$notifPlayerArgs['player_id'] = $player_id;
        //$notifPlayerArgs['player_name'] = $player_info['player_name'];
        //$notifPlayerArgs['vp']        = 0;
        //$notifPlayerArgs['gold']      = 0;
        //$notifPlayerArgs['moonshard'] = 0;
        //$notifPlayerArgs['fireshard'] = 0;
        //$notifPlayerArgs['hammer']    = 0;
        $notifPlayerArgs['choice']    = false;
        $monoResolution = $this->getGameStateValue( "monoRessourceChoice");

        $userException = clienttranslate("This side does not need a choice");

        $multiple      = 1;
        $triple        = false;
        $checkMirror_1 = false;
        $checkMirror_2 = false;
        $checkMirror_celest = false;
        $doubleMirror  = $this->checkValidVisibleSide("mirror", null, $player_id);
        $steal2        = false;
        $ship          = false;
        $exploit       = false;
        $cycl          = false;
        $chooseSides   = false;
        $celestialMirror = false;
        $celestial      = false;
        $celestialChoice = "";
        $celestialDieNum = 0;
        $maze           = false;
        $oustAll        = false;
        $firstFinish    = false;
        $celestialSide  = "";
        $celestial_choice = $this->tokens->getTokenLocation('celestial_choice');
        $cerberus = $this->tokens->getTokenState("cerberus_" . $player_id);


        //throw new feException("side 1 $side1 & side 2 $side2");
        if ($this->getGameStateValue("celestialRunning")) {
            $celestialSide = $this->celestialDie[$this->getGameStateValue( "celestialDieSide")];
            $celestial = true;
            if ( $celestialSide == 'celestialMirror') {
                $celestialMirror = true;
                $steal2 = true;
                $doubleMirror = true;
            }
            elseif ( $celestialSide == 'chooseSide') {
                $chooseSides = true;
            }
        }

        if ($this->getGameStateValue("firstFinish") == $player_id) {
            $chooseSides = true;
            $firstFinish = true;
            //$this->setGameStateValue("goddessChoice", 0);
            if ($side1 == null || $side2 == null)
                throw new feException("Please select 2 die sides");
        }

        // Are we processing an exploit?
        $card_id = $this->getGameStateValue( "exploitBought");

        // #27008 add !$celestial to not manage cards choice if choice comes from a celestial roll
        if ($card_id != -1 && $this->gamestate->state()['name'] == 'exploitRessource' && !$celestial) {
            $card = $this->exploits->getCard($card_id);
            $card_info = $this->exploit_types[$card['type']];
            $exploit = true;

            switch($card_info['action']) {
                case 'looseThrow':
                    $multiple = -1;
                    break ;
                case 'steal2':
                    $steal2 = true;
                    break ;
                case 'chooseSides':
                    if ($this->getGameStateValue("goddessChoice") == 1) {
                        $chooseSides = true;
                        if ($side1 == null || $side2 == null)
                            throw new feException("Please select 2 die sides");
                        $this->setGameStateValue("goddessChoice", 0);
                        // #29420 : enabling Cerberus with Goddess
                        $this->dbUpdateTokenPlayed($player_id, 'cerberus', false);
                        $this->dbUpdateRolled($player_id, true);
                    }
                    break ;
                //case 'throwCelestialDie':
                //    $celestialSide = $this->celestialDie[$this->getGameStateValue( "celestialDieSide")];
                //    if ( $celestialSide == 'celestialMirror') {
                //        $celestialMirror = true;
                //        $steal2 = true;
                //        $doubleMirror = true;
                //    }
                //    elseif ( $celestialSide == 'chooseSide') {
                //        $chooseSides = true;
                //    }
                //    break ;
                case '4ThrowsTransform':
                case '4Throws':
                    $cycl = true;
                    break;
                case 'oustAll':
                    $oustAll = true;
                    $ousted = $this->getGameStateValue('oustedPlayerId');
                    break ;
                case 'fortuneWheel':
                    if ($this->tokens->getTokenLocation('wheel_1') != 'none') {
                        break ;
                    }

                    // We trigger this code only when no sides have been choosen
                    $this->tokens->moveToken('wheel_1', $side1);
                    $this->tokens->moveToken('wheel_2', $side2);
                    self::notifyAllPlayers("notifMessage", clienttranslate('${player_name} predicts that the sides ${sides_rolled} will roll'),
                            ['player_name' => $notifPlayerArgs['player_name'],
                            'sides_rolled' => $side1 . ',' . $side2,]
                    );
                    $this->dbSetSideChoice($player_id, 1, "0");
                    $this->dbSetSideChoice($player_id, 2, "0");
                    $this->dbSetChoice($player_id, self::RC_NOTHING_TODO);
                    $this->gamestate->nextState('nextState');
                    return ;
                    break;
            }
        }

        //if ($steal2 && ($side1 == null || $side2 == null))
        //    throw new BgaVisibleSystemException("With Satyr, 2 sides are mandatory");;
        if ($celestial) {
            if ($side1 != null) {
                $checkMirror_celest = true;

                $this->tokens->setTokenState("mirror1_$player_id", $this->getSideIdFromType($side1));

                $celestialChoice = $side1;
                $celestialDieNum = 1;
                $side1 = "0";
                // if a side is a boar, increment boar counter of the given player)
                if (substr($side1, -4) == 'Boar' && $this->getBoar($side1) != 0) {
                    $boarId = $this->getBoar($side1);
                    $this->dbIncBoar($boarId, true);
                    $this->setChoice($boarId, self::RC_RESSOURCE);

                    // activate player if inactive and only if there is no conflict
                    if (!in_array($boarId, $this->gamestate->getActivePlayerList()) && $monoResolution == 0)
                         $this->gamestate->setPlayersMultiactive( array($boarId), '' );
                }
                // Misfortune
                elseif (self::isMisfortune($side1) != false) {
                    $mis_player_id = self::isMisfortune($side1);
                    //$this->dbIncMisfortune($mis_player_id, true);
                    $this->tokens->moveToken('resolveMisfortune', $side1, $mis_player_id);
                    $this->setChoice($mis_player_id, self::RC_RESSOURCE);

                    // activate player if inactive and only if there is no conflict
                    if (!in_array($mis_player_id, $this->gamestate->getActivePlayerList()) && $monoResolution == 0)
                         $this->gamestate->setPlayersMultiactive( array($mis_player_id), '' );
                }
            }

            if ($side2 != null) {
                $checkMirror_celest = true;

                $this->tokens->setTokenState("mirror2_$player_id", $this->getSideIdFromType($side2));
                $celestialChoice = $side2;
                $side2 = "0";
                $celestialDieNum = 2;
                // if a side is a boar, increment boar counter of the given player)
                if (substr($side2, -4) == 'Boar' && $this->getBoar($side2) != 0) {
                    $boarId = $this->getBoar($side2);
                    $this->dbIncBoar($boarId, true);
                    $this->setChoice($boarId, self::RC_RESSOURCE);

                    // activate player if inactive and only if there is no conflict
                    if (!in_array($boarId, $this->gamestate->getActivePlayerList()) && $monoResolution == 0)
                         $this->gamestate->setPlayersMultiactive( array($boarId), '' );
                }
                // Misfortune
                elseif (self::isMisfortune($side2) != false) {
                    $mis_player_id = self::isMisfortune($side2);
                    //$this->dbIncMisfortune($mis_player_id, true);
                    $this->tokens->moveToken('resolveMisfortune', $side2, $mis_player_id);
                    $this->setChoice($mis_player_id, self::RC_RESSOURCE);

                    // activate player if inactive and only if there is no conflict
                    if (!in_array($mis_player_id, $this->gamestate->getActivePlayerList()) && $monoResolution == 0)
                         $this->gamestate->setPlayersMultiactive( array($mis_player_id), '' );
                }
            }

        }

        if ($side98 != null) {
            $checkMirror_celest = true;
            //$this->tokens->setTokenState("mirror2_$player_id", $this->getSideIdFromType($side98));
            $celestialChoice = $side98;
            $celestialMirror = true;
            $doubleMirror = true;
            $side98 = "0";
            //$celestialDieNum = 2;
            // if a side is a boar, increment boar counter of the given player)
            if (substr($side98, -4) == 'Boar' && $this->getBoar($side98) != 0) {
                $boarId = $this->getBoar($side98);
                $this->dbIncBoar($boarId, true);
                $this->setChoice($boarId, self::RC_RESSOURCE);

                // activate player if inactive and only if there is no conflict
                if (!in_array($boarId, $this->gamestate->getActivePlayerList()) && $monoResolution == 0)
                     $this->gamestate->setPlayersMultiactive( array($boarId), '' );
            }
            // Misfortune
            elseif (self::isMisfortune($side98) != false) {
                $mis_player_id = self::isMisfortune($side98);
                //$this->dbIncMisfortune($mis_player_id, true);
                $this->tokens->moveToken('resolveMisfortune', $side98, $mis_player_id);
                $this->setChoice($mis_player_id, self::RC_RESSOURCE);

                // activate player if inactive and only if there is no conflict
                if (!in_array($mis_player_id, $this->gamestate->getActivePlayerList()) && $monoResolution == 0)
                     $this->gamestate->setPlayersMultiactive( array($mis_player_id), '' );
            }
        }

        // check of the sides
        if ($side1 != null && !$celestial) {
            if ($player_info['side_choice_1'] != "-1")
                throw new BgaUserException($userException);
            $checkMirror_1 = true;

            $this->tokens->setTokenState("mirror1_$player_id", $this->getSideIdFromType($side1));

            // if a side is a boar, increment boar counter of the given player)
            if (substr($side1, -4) == 'Boar' && $this->getBoar($side1) != 0) {
                $boarId = $this->getBoar($side1);
                $this->dbIncBoar($boarId, true);
                $this->setChoice($boarId, self::RC_RESSOURCE);

                // activate player if inactive and only if there is no conflict
                if (!in_array($boarId, $this->gamestate->getActivePlayerList()) && $monoResolution == 0)
                     $this->gamestate->setPlayersMultiactive( array($boarId), '' );
            }
            // Misfortune : nothing to do, mirror & misfortune cannot be together
        }
        elseif (!$celestial) {
            if ($player_info['side_choice_1'] != "0" && !$this->canUseTwins($player_id))
                $side1 = $player_info['side_choice_1'];
        }

        if ($side2 != null && !$celestial) {
            if ($player_info['side_choice_2'] != "-1")
                throw new BgaUserException($userException);
            $checkMirror_2 = true;

            $this->tokens->setTokenState("mirror2_$player_id", $this->getSideIdFromType($side2));

            // if a side is a boar, increment boar counter of the given player)
            if (substr($side2, -4) == 'Boar' && $this->getBoar($side2) != 0) {
                $boarId = $this->getBoar($side2);
                $this->dbIncBoar($boarId, true);
                $this->setChoice($boarId, self::RC_RESSOURCE);

                // activate player if inactive and only if there is no conflict
                if (!in_array($boarId, $this->gamestate->getActivePlayerList()) && $monoResolution == 0)
                     $this->gamestate->setPlayersMultiactive( array($boarId), '' );
            }
            // Misfortune : nothing to do, mirror & misfortune cannot be together
        }
        elseif (!$celestial) {
            if ($player_info['side_choice_2'] != "0" && !$this->canUseTwins($player_id))
                $side2 = $player_info['side_choice_2'];
        }


        $triple = $this->tokens->getTokenState("triple_$player_id");
        // triple identification
        if ($side1 == 'triple' || $side2 == 'triple' || $player_info['side_choice_1'] == 'triple' || $player_info['side_choice_2'] == 'triple' ) {
            $triple = true;
            $this->tokens->setTokenState("triple_$player_id", 1);
            if ($side1 == 'triple')
                $this->dbSetSideChoice($player_id, 1, "0");

            if ($side2 == 'triple')
                $this->dbSetSideChoice($player_id, 2, "0");
        }

        // if triple, the player owning the boar card must choose 2 more times
        if ($triple) {
            if (substr($side1, -4) == 'Boar'  && $this->getBoar($side1) != 0) {
                $boarId = $this->getBoar($side1);
                $this->dbIncBoar($boarId, true);
                $this->dbIncBoar($boarId, true);
                $this->setChoice($boarId, self::RC_RESSOURCE);

                // activate player if inactive and only if there is no conflict
                if (!in_array($boarId, $this->gamestate->getActivePlayerList()) && $monoResolution == 0)
                     $this->gamestate->setPlayersMultiactive( array($boarId), '' );
            }
            elseif(substr($side2, -4) == 'Boar'  && $this->getBoar($side2) != 0) {
                $boarId = $this->getBoar($side2);
                $this->dbIncBoar($boarId, true);
                $this->dbIncBoar($boarId, true);
                $this->setChoice($boarId, self::RC_RESSOURCE);

                // activate player if inactive and only if there is no conflict
                if (!in_array($boarId, $this->gamestate->getActivePlayerList()) && $monoResolution == 0)
                     $this->gamestate->setPlayersMultiactive( array($boarId), '' );
            }
            // Misfortune : nothing to do as for triple, classical process
        }


        if (($chooseSides && !$celestial)) {
            // notif to roll dice (faceup)
            $notifPlayerArgs['player_id'] = $player_id;
            //throw new feException("s1 $side1 s2 $side2");
            if ($side1 != null && $side1 != -1) {
                $notifPlayerArgs['dice1'] = $side1;
                $notifPlayerArgs['roll'] = true;

                $sql = "SELECT DISTINCT card_id, card_location_arg from sides WHERE card_type = '$side1' AND card_location = 'dice1-p$player_id'";
                $roll = self::getCollectionFromDB($sql);
                $roll = reset ($roll);


                $old_side = $this->sides->getCardsInLocation('dice1-p'.$player_id, 0);
                $old_side = reset($old_side);

                $this->sides->moveCard($old_side['id'], 'dice1-p'.$player_id, $roll['card_location_arg']);
                $this->sides->moveCard($roll['card_id'], 'dice1-p'.$player_id, 0);
                $this->dbUpdateThrow($player_id, 1, $roll['card_id']);
                $this->tokens->setTokenState("throw1_$player_id", 1);

            }
            else {
                $side1 = null;
                $this->dbSetSideChoice($player_id, 1, "0");
            }

            if ($side2 != null && $side2 != -1) {
                $notifPlayerArgs['dice2'] = $side2;
                $notifPlayerArgs['roll'] = true;
                $sql = "SELECT DISTINCT card_id, card_location_arg from sides WHERE card_type = '$side2' AND card_location = 'dice2-p$player_id'";
                $roll = self::getCollectionFromDB($sql);
                $roll = reset ($roll);

                $old_side = $this->sides->getCardsInLocation('dice2-p'.$player_id, 0);
                $old_side = reset($old_side);

                $this->sides->moveCard($old_side['id'], 'dice2-p'.$player_id, $roll['card_location_arg']);
                $this->sides->moveCard($roll['card_id'], 'dice2-p'.$player_id, 0);
                $this->dbUpdateThrow($player_id, 2, $roll['card_id']);
                $this->tokens->setTokenState("throw2_$player_id", 1);
            }
            else {
                $side2 = null;
                $this->dbSetSideChoice($player_id, 2, "0");
            }
        }
        elseif ($celestialSide == 'chooseSide') {
            $notifPlayerArgs['dice' . $celestialDieNum] = $celestialChoice;
            $notifPlayerArgs['roll'] = true;
            $sql = "SELECT DISTINCT card_id, card_location_arg from sides WHERE card_type = '$celestialChoice' AND card_location = 'dice$celestialDieNum-p$player_id'";
            $roll = self::getCollectionFromDB($sql);
            $roll = reset ($roll);

            $old_side = $this->sides->getCardsInLocation('dice' . $celestialDieNum . '-p'.$player_id, 0);
            $old_side = reset($old_side);

            $this->sides->moveCard($old_side['id'], 'dice' . $celestialDieNum . '-p'.$player_id, $roll['card_location_arg']);
            $this->sides->moveCard($roll['card_id'], 'dice' . $celestialDieNum . '-p'.$player_id, 0);
            $this->dbUpdateThrow($player_id, $celestialDieNum, $roll['card_id']);
            $this->tokens->setTokenState("throw" . $celestialDieNum  . "_$player_id", 1);
        }

        if ($steal2 || $chooseSides || $celestialMirror) {
            $text = clienttranslate('${player_name} selects ${sides_rolled}');

            $displaySides = "";

            if ($side1)
                $displaySides = $side1 . ",";

            if ($side2)
                $displaySides .= $side2 . ",";

            if ($celestialChoice)
                $displaySides .= $celestialChoice . ",";

            $displaySides = substr($displaySides, 0, strlen($displaySides) -1);

            self::notifyAllPlayers("notifMessage", $text,
                array(
                    'player_name' => $notifPlayerArgs['player_name'],
                    'sides_rolled' => $displaySides,
                )
            );

            self::notifyAllPlayers("notifBlessing", '', $notifPlayerArgs);

        }
        else {
            $text = clienttranslate('${player_name} selects ${old_side_type} → ${side_type}');
            if ($checkMirror_1) {
                self::notifyAllPlayers("notifMessage", $text,
                    array(
                        'player_name'   => $notifPlayerArgs['player_name'],
                        'old_side_type' => 'mirror',
                        'side_type'     => $side1
                    )
                );
            }

            if ($checkMirror_2) {
                self::notifyAllPlayers("notifMessage", $text,
                    array(
                        'player_name'   => $notifPlayerArgs['player_name'],
                        'old_side_type' => 'mirror',
                        'side_type'     => $side2
                    )
                );
            }
        }

        // check that the side has been rolled
        if ((!$cerberus || ($cerberus && $side1 != 'ship')) && !$oustAll && !$checkMirror_1 && !$steal2 && $side1 && !$this->checkValidVisibleSide($side1, $player_id))
            throw new BgaVisibleSystemException ("This side has not been rolled");
        //throw new feException($checkMirror_1 ." , ". $steal2 . " , ".$chooseSides);
        if (($checkMirror_celest) && !$chooseSides) {
            //throw new feException($celestialChoice);
            if (!$doubleMirror && !$this->checkValidVisibleSide($celestialChoice, null, $player_id))
                throw new BgaVisibleSystemException ("This side has not been rolled (mirror effect celestial)");
            elseif ($doubleMirror && !$this->checkValidVisibleSide($celestialChoice))
                throw new BgaVisibleSystemException ("This side has not been rolled (double mirror effect celestial)");
        }

        if (($checkMirror_1 || ($steal2 && !$celestial) || $oustAll) && !$chooseSides) {
            if (!$oustAll && !$doubleMirror && !$this->checkValidVisibleSide($side1, null, $player_id))
                throw new BgaVisibleSystemException ("This side has not been rolled (mirror effect 1)");
            // case of left hand we must check the ousted player
            elseif ($oustAll && $checkMirror_1 && !$doubleMirror && !$this->checkValidVisibleSide($side1, null, $ousted))
                throw new BgaVisibleSystemException ("This side has not been rolled (mirror effect 1 ousted)");
            elseif ($doubleMirror && !$this->checkValidVisibleSide($side1))
                throw new BgaVisibleSystemException ("This side has not been rolled (double mirror effect 1 )");
        }

        if ((!$cerberus || ($cerberus && $side2 != 'ship')) && !$oustAll && !$checkMirror_2 && !$steal2 && $side2 && !$this->checkValidVisibleSide($side2, $player_id))
            throw new BgaVisibleSystemException ("This side has not been rolled");

        //$this->debugVTO($player_id);
        //throw new feException($side2 . " " . $checkMirror_2);

        if (($checkMirror_2 || $steal2 || $oustAll) && !$chooseSides && $side2) {
            if (!$oustAll && !$doubleMirror && !$this->checkValidVisibleSide($side2, null, $player_id))
                throw new BgaVisibleSystemException ("This side has not been rolled (mirror effect 2)");
            elseif ($oustAll && $checkMirror_2 && !$doubleMirror && !$this->checkValidVisibleSide($side2, null, $ousted))
                throw new BgaVisibleSystemException ("This side has not been rolled (mirror effect 2 ousted)");
            elseif ($doubleMirror && !$this->checkValidVisibleSide($side2))
                throw new BgaVisibleSystemException ("This side has not been rolled (double mirror effect 2)");
        }

        // if only triple is selected, disable the user
        if (($side1 == 'triple' && $side2 == 'triple') ||
                ($side1 == 'triple' && $side2 == null) ||
                ($side1 == null && $side2 == 'triple') || $celestialChoice == "triple") {
            $this->dbSetChoice($player_id, self::RC_NOTHING_TODO);
            if ($celestialChoice == 'triple')
                $this->tokens->moveToken("celestial_choice", '0');
            $this->tokens->setTokenState("triple_$player_id", 0);
            $this->setGameStateValue("celestialRunning", 0);
            $this->setGameStateValue("firstFinish", 0);
            $this->gamestate->setPlayerNonMultiactive($player_id, $this->getNextState());
            return ;
        }

        // deprecated : use of triple token
        //// affect triple side to manage choice
        //if ($side1 == 'triple')
        //    $this->dbSetSideChoice($player_id, 1, 'triple');
        //elseif ($side2 == 'triple')
        //    $this->dbSetSideChoice($player_id, 2, 'triple');

        // Affect of the info if ship (as necessary in affectRessource)
        if ($side1 == "ship" && $multiple != -1) {
            $this->dbSetSideChoice($player_id, 1, "ship");
            $ship = true;
        }
        else if ($side1 == "ship" && $multiple == -1) {
            $this->dbSetSideChoice($player_id, 1, "0");
        }

        if ($side2 == "ship" && $multiple != -1) {
            $this->dbSetSideChoice($player_id, 2, "ship");
            $ship = true;
        }
        elseif ($side2 == "ship" && $multiple == -1) {
            $this->dbSetSideChoice($player_id, 2, "0");
        }

        // Maze management
        if (strpos($side1, "Golem") && $multiple != -1) {
            $this->dbSetSideChoice($player_id, 1, $side1);
            $maze = true;
        }
        else if (strpos($side1, "Golem") && $multiple == -1) {
            $this->dbSetSideChoice($player_id, 1, "0");
        }

        if (strpos($side2, "Golem") && $multiple != -1) {
            $this->dbSetSideChoice($player_id, 2, $side2);
            $maze = true;
        }
        else if (strpos($side2, "Golem") && $multiple == -1) {
            $this->dbSetSideChoice($player_id, 2, "0");
        }

        if ($celestialChoice == 'ship') {
            $this->tokens->moveToken("celestial_choice", $celestialChoice);
            $ship = true;
        }
        //elseif ($celestial_choice == 'triple' && $celestial && $chooseSides) {
        //    $this->tokens->moveToken("celestial_choice", '0');
        //    $celestial_choice = '';
        //}

        // if ship & gold limit reached, do not allocate anything and wait for a user choice
        if (($ship && !$celestial) || $maze) {
            //$result_sides = array (0 => $side1, 1 => $side2);
            if ($side1 != null)
                $result_sides[] = $side1;
            if ($side2 != null)
                $result_sides[] = $side2;

            $goldLimitReached = false;
            foreach ($result_sides as $id => $side) {
                $res_gold = 0;
                $side_definition = $this->dice_sides[$side];
                if (isset($side_definition['ressource']['gold']) && $side_definition['ressource']['gold'] != 0) {
                    $res_gold = $side_definition['ressource']['gold'];
                }

                if ($this->isGoldLimitReached($player_id, $res_gold) && $res_gold > 0) {
                    if ($id == 0)
                        $this->dbSetSideChoice($player_id, "1", $side1);
                    elseif ($id == 1)
                        $this->dbSetSideChoice($player_id, "2", $side2);
                    $goldLimitReached = true;
                }
            }

            if ($goldLimitReached) {
                //$this->setChoice($player_id, null);
                $this->dbSetChoice($player_id, self::RC_ACTION_CHOICE);
                self::notifyAllPlayers("notifBlessing", '', $notifPlayerArgs);
                $this->gamestate->nextState('choice');
                //$this->setGameStateValue("firstFinish", 0);
                //throw new feException('chose');
                return true;
            }
        }

        if ($side1 != null && $side1 != "triple" && $side1 != "ship" && $side1 != "0") {
			if ($celestial)
				$notifPlayerArgs = $this->affectRessource($player_id, $notifPlayerArgs, 1, $side1, $triple, $multiple, $ship, false, $exploit);
			else
				$notifPlayerArgs = $this->affectRessource($player_id, $notifPlayerArgs, 1, $side1, $triple, $multiple, $ship, false, $exploit);
        }
        if ($side2 != null && $side2 != "triple" && $side2 != "ship" && $side2 != "0"){
			if ($celestial)
				$notifPlayerArgs = $this->affectRessource($player_id, $notifPlayerArgs, 2, $side2, $triple, $multiple, $ship, false, $exploit);
			else
				$notifPlayerArgs = $this->affectRessource($player_id, $notifPlayerArgs, 2, $side2, $triple, $multiple, $ship, false, $exploit);
        }

        if ($celestialChoice != "" && $celestialChoice != 'ship') {
            $notifPlayerArgs = $this->affectRessource($player_id, $notifPlayerArgs, 98, $celestialChoice, $triple, $multiple, $ship, false, $exploit);
        }
        //throw new feException($this->getPlayersAdditionnalInfo()[$player_id]['side_choice_2']);

         //throw new BgaUserException(print_r($notifPlayerArgs));

        if ($exploit)
            $toTransform = $this->getRessourcesToTransform();
        else
            $toTransform = array('ressource' => array(), 'vp' => 1);

        // Guardian's shield management
        if (!$celestial && ($this->doubleThrow($player_id) || $steal2)) {
            $notifPlayerArgs = $this->checkShield($notifPlayerArgs, $multiple, $ship, $exploit);
            if (!$notifPlayerArgs['choice'])
                $this->resolveTwin($player_id, true);
        }
        elseif (!$notifPlayerArgs['choice']) {
            if ($side1 == 'redShield' || $side1 == 'greenShield' || $side1 == 'blueShield' || $side1 == 'yellowShield') {
                $notifPlayerArgs = $this->affectRessource($player_id, $notifPlayerArgs, 1, $side1, $triple, $multiple, false, true, $exploit);
            }

            if ($side2 == 'redShield' || $side2 == 'greenShield' || $side2 == 'blueShield' || $side2 == 'yellowShield') {
                $notifPlayerArgs = $this->affectRessource($player_id, $notifPlayerArgs, 2, $side2, $triple, $multiple, false, true, $exploit);
            }
            $this->resolveTwin($player_id, true);
        }
        elseif ($celestial && $notifPlayerArgs['choice']) {
            //throw new feException("zut");
            $this->tokens->moveToken("celestial_choice", $celestialChoice);
        }
        elseif ($celestial && !$notifPlayerArgs['choice']) {
            //throw new feException("...");
            $this->resolveTwin($player_id, true);
            $this->tokens->moveToken("celestial_choice", '0');
        }

        //$this->setGameStateValue("firstFinish", 0);

        // We check if the new roll was for 2 dice and see if there is a need for puzzle effect
        // #31257 - addition of $steal2 condition
        if ((isset($notifPlayerArgs['dice1']) && isset($notifPlayerArgs['dice2']) && $chooseSides) || $steal2) {
            $notifPlayerArgs = $this->checkPuzzle($notifPlayerArgs);
        }

        // If triple & 4Throws, we need to flag it as nothing to do
        if ($cycl && ($side1 == 'triple' || $side2 == 'triple')) {
            $this->dbSetSideChoice($player_id, 1, '0');
            $this->dbSetSideChoice($player_id, 2, '0');
            $this->tokens->setTokenState("triple_$player_id", 0);
        }

        $this->setChoice($player_id, null, false, true);

        // #30296 - hammer needs to be forced as ressource (for simple side)
        if ($notifPlayerArgs['choice'] && $this->canFillHammer($player_id)) {
            if ($side1 != null)
                $result_sides[] = $side1;
            if ($side2 != null)
                $result_sides[] = $side2;

            foreach ($result_sides as $id => $side) {
                $side_definition = $this->dice_sides[$side];
                if (isset($side_definition['ressource']['gold']) && $side_definition['ressource']['gold'] != 0 && $side_definition['type'] == 'simple') {
                    $this->setChoice($player_id, self::RC_RESSOURCE, false, true);
                }
            }
        }

        //$this->debugVTO($player_id);
        //throw new feException("stop");
        if (!($notifPlayerArgs['gold'] == 0 && $notifPlayerArgs['vp'] == 0 && $notifPlayerArgs['moonshard'] == 0 && $notifPlayerArgs['fireshard'] == 0 && $notifPlayerArgs['hammer'] == 0 && $notifPlayerArgs['scepter'] == 0  && $notifPlayerArgs['maze'] == 0 && $notifPlayerArgs['loyalty'] == 0 && $notifPlayerArgs['ancientshard'] == 0))
        {
            // petit hack
            if ( $notifPlayerArgs['vp'] == 0 )
            {
                unset( $notifPlayerArgs['vp'] );
            }
            $notifPlayerArgs['ressources'] = $this->buildRessourceNotif( $notifPlayerArgs );
            if ( $multiple == -1 )
                self::notifyAllPlayers("notifBlessing", clienttranslate('${player_name} looses ${ressources}'), $notifPlayerArgs );
            else
                self::notifyAllPlayers("notifBlessing", clienttranslate('${player_name} gets ${ressources}'), $notifPlayerArgs );
        }

        self::notifyAllPlayers("updateCounters", "", $this->getPlayersRessources());

        $this->generateNotifLoyalty ($notifPlayerArgs);

        if (isset($notifPlayerArgs['notifHammer']) && count($notifPlayerArgs['notifHammer']) != 0) {
            $this->generateNotifHammer($player_id, $notifPlayerArgs['notifHammer']);
        }

        $this->titanMove ($player_id);

        // #35055
        if ($firstFinish) {
            $this->setGameStateValue("firstFinish", 0);
        }

        // Oracle reinforcement being used
        if ($this->getGameStateValue("oracleReinforcement") == 1 && ($notifPlayerArgs['loyalty'] != 0 || $notifPlayerArgs['ancientshard'] != 0)) {
            $this->oracleMove($player_id);
            $this->setGameStateValue("oracleReinforcement", 0);
        }

        if (!$notifPlayerArgs['choice'])
            $this->setGameStateValue("oracleReinforcement", 0);

        $player_info      = $this->getPlayersAdditionnalInfo()[$player_id];

        if ($player_info['ressource_choice'] == self::RC_NOTHING_TODO) {
            //$this->dbSetSideChoice($player_id, 1, '0');
            //$this->dbSetSideChoice($player_id, 2, '0');
        }

        //throw new feException(print_r($notifPlayerArgs));

        if ($ship)
            $this->setChoice($player_id, self::RC_FORGESHIP);

        $this->dbUpdateUnrolled();
        $this->setGameStateValue("celestialRunning", 0);

        if ($this->gamestate->state()['name'] == 'doeRessourceChoice' && ($player_info['side_choice_1'] != '0' || $player_info['side_choice_2'] != '0')) {
            $this->setGameStateValue( "celestialRunning", 0);
            $this->setChoice($player_id, self::RC_RESSOURCE);
            $this->gamestate->nextState('choice');
        }
        elseif ($player_info['ressource_choice'] == self::RC_RESSOURCE || $player_info['ressource_choice'] == self::RC_ACTION_CHOICE) {
            $this->setGameStateValue( "celestialRunning", 0);
            $this->gamestate->nextState('choice');
        }
        // Necessary for maze & ousting as no way to trigger it
        elseif ($this->gamestate->state()['name'] == 'playerOustingChoice' && $this->tokens->getTokenState("mazestock_$player_id") != 0) {
            //// trigger maze management for ousting after a choice
            //$canContinue = $this->mazeManagement($player_id);
            //if ($canContinue) {
            //    $this->setGameStateValue( "celestialRunning", 0);
            //    $this->gamestate->setPlayerNonMultiactive($player_id, 'nextState');
            //}
            //elseif ($this->getGameStateValue("mazeForge") == 1)
            //    $this->gamestate->nextState('forgeShip');
            //else
            //    $this->gamestate->nextState('choice');
            $this->oustingMazeManagement($player_id);

        }
        else {
            // we disable the user
            switch ($this->gamestate->state()['name']) {
                case 'ressourceChoice':
                    $endMultiState = 'blessing';
                    break;
                case 'doeRessourceChoice':
                    $endMultiState = 'nextState';
                    break;
                case 'playerOustingChoice':
                    $endMultiState = 'nextState';
                    break;
                case 'exploitRessource':
                    $endMultiState = "nextState";
                    break;
            }
            //$this->tokens->setTokenState("triple_$player_id", 0);
            $this->setGameStateValue( "celestialRunning", 0);
            $this->gamestate->setPlayerNonMultiactive($player_id, $endMultiState);
        }
    }

    function oustingMazeManagement($player_id) {
        // trigger maze management for ousting after a choice
            $this->setGameStateValue( "celestialRunning", 0);
            $canContinue = $this->mazeManagement($player_id);
            if ($canContinue) {
                $this->gamestate->setPlayerNonMultiactive($player_id, 'nextState');
            }
            elseif ($this->getGameStateValue("mazeForge") == 1)
                $this->gamestate->nextState('forgeShip');
            else
                $this->gamestate->nextState('choice');
    }

    function actForgeNymphPass() {
        self::checkAction("actForgeNymphPass");
        $player_id = self::getCurrentPlayerId();

        self::notifyAllPlayers("notifMessage", clienttranslate('${player_name} does not forge a side'),
            array(
                'player_name' => self::getCurrentPlayerName()
            )
        );

        $next_state = $this->getNextState();
        //$this->gamestate->setPlayerNonMultiactive($player_id, $next_state);
        $this->gamestate->nextState('nextState');
        return ;
    }

    function actMemoryToken($token, $island, $choice) {
        self::checkAction("actMemoryToken");
        $player_id = self::getCurrentPlayerId();

        if ($choice == 'memorySun')
            $side = 1;
        else
            $side = 0;

        // check that token is not already in this island
        if (count($this->tokens->getTokensOfTypeInLocation(explode('_', $token)[0], $island)) != 0)
            throw new BgaVisibleSystemException(_("You need to select another island than the previous one"));

        $this->tokens->moveToken($token, $island, $side);
        self::notifyAllPlayers("notifMemorySetup", clienttranslate('${player_name} places a Memory token on an island'),
            array(
                'player_name' => self::getCurrentPlayerName(),
                'player_id'   => $player_id,
                'token'       => $token,
                'side'        => $side,
                'island'      => $island

            )
        );

        $this->gamestate->nextState('exploitEffect');
    }

    function actForgeShipPass($sideNum)
    {
        self::checkAction("actForgeShipPass");
        $player_id = self::getCurrentPlayerId();
        $exploit = false;

        // Disable of the side
        $this->dbSetSideChoice($player_id, $sideNum, 0);
        $player_info = $this->getPlayersAdditionnalInfo()[$player_id];

        //if ($sideNum == 1)
        //    $otherSide = 2;
        //else
        //    $otherSide = 1;

        if ($player_info['side_choice_1'] == "0")
            $otherSide = 2;
        else
            $otherSide = 1;

        if ($player_info['side_choice_' . $otherSide] == "0" || $player_info['side_choice_' . $otherSide] == "triple") {
            $this->dbSetSideChoice($player_id, $otherSide, 0);
            $this->tokens->setTokenState("triple_$player_id", 0);
        }

        self::notifyAllPlayers("notifMessage", clienttranslate('${player_name} does not forge a side with ${side_type}'),
            array(
                'player_name' => self::getCurrentPlayerName(),
                'side_type'   => 'ship'
            )
        );

        $previousExploitId = $this->getGameStateValue( "exploitBought");
        if ($previousExploitId != -1)
            $exploit = true;

        if ($player_info['side_choice_' . $otherSide] != "0" && $player_info['side_choice_' . $otherSide] != "triple" && $player_info['side_choice_' . $otherSide] != "ship" && !$this->canUseTwins($player_id)) {
            //$this->takeRessource2($player_id, 'blessing', $sideNum, '0', array());
            //$this->affectGoldOnly($player_id, 'blessing', $player_info['side_choice_' . $otherSide], $otherSide);
            //throw new feException("titi");
            if ($otherSide == 1)
                $this->blessing($player_id, true, false, 1, $exploit, false);
            else
                $this->blessing($player_id, false, true, 1, $exploit, false);
        }
//throw new feException("stop $otherSide $sideNum");
        $this->setGameStateValue("forgeClassical", 0);
        $this->setGameStateValue("mazeForge", 0);

        // we let the system determine the correct ressource choice
        $ressourceChoice = $this->setChoice($player_id, null);

        if ($ressourceChoice != self::RC_FORGESHIP) {
            // Go to correct State
            // depends on the state name
            $next_state = $this->getNextState();
            $this->tokens->setTokenState("triple_$player_id", 0);
            //if (!$this->triggerCelestialWhenMaze0($player_id))
            $this->triggerCelestialWhenMaze0($player_id);
            $this->gamestate->setPlayerNonMultiactive($player_id, $next_state);
        }
        else
            $this->gamestate->nextState('forgeShip');
    }

    function actUseScepter($scepter_id, $resource) {
        self::checkAction("actUseScepter");
        $player_id = self::getCurrentPlayerId();
        $tokenId = "scepter_$scepter_id";

        // check scepter is own
        if ($this->tokens->getTokenLocation($tokenId) != $player_id)
            throw new feException("You do not own this scepter");

        // check that value is higher than 3
        $state = $this->tokens->getTokenState($tokenId);
        if ($state < 4)
            throw new BgaUserException (self::_("The Blacksmith's Scepter cannot be used if the value is less than 4"));
        elseif ($state < 6)
            $amount = 1;
        elseif ($state == 6)
            $amount = 2;

        // update variable
        if ($resource == 'fireshard')
            self::incGameStateValue('scepterFireshard', $amount);
        elseif ($resource == 'moonshard')
            self::incGameStateValue('scepterMoonshard', $amount);
        else
            throw new feException('invalid parameter');

        // reduce state of token
        $this->tokens->setTokenState($tokenId, 0);
        // notify
        self::notifyAllPlayers("updateCounters", "", $this->getPlayersRessources());

        // add notif somewhere
        self::notifyScepters ($resource, $amount);
        //self::notifyAllPlayers("notifUseScepter", clienttranslate('${player_name} converts its Blacksmith\'s scepter reserve in ${ressources}'),
        //    array(
        //        'player_name' => $this->loadPlayersBasicInfos()[$player_id]['player_name'],
        //        'player_id'     => $player_id,
        //        'ressources' => $this->buildRessourceNotif(array ($resource => $amount)),
        //        'moonshard' => self::getGameStateValue('scepterMoonshard'),
        //        'fireshard' => self::getGameStateValue('scepterFireshard'),
        //        )
        //    );

        $this->gamestate->nextState('current');

    }

    function notifyScepters($resource = '', $amount = 0) {
        $player_id = self::getActivePlayerId();

        if ($resource == '') {
            self::notifyAllPlayers("notifUseScepter", '',
                array(
                    'player_name' => $this->loadPlayersBasicInfos()[$player_id]['player_name'],
                    'player_id'     => $player_id,
                    'moonshard' => self::getGameStateValue('scepterMoonshard'),
                    'fireshard' => self::getGameStateValue('scepterFireshard'),
                    )
                );
        }
        elseif ($resource == 'reset') {
            self::notifyAllPlayers("notifUseScepter", clienttranslate('${player_name} resets its Blacksmith\'s scepter(s) position'),
                array(
                    'player_name' => $this->loadPlayersBasicInfos()[$player_id]['player_name'],
                    'player_id'     => $player_id,
                    'moonshard' => self::getGameStateValue('scepterMoonshard'),
                    'fireshard' => self::getGameStateValue('scepterFireshard'),
                    )
                );
        }
        else {
           self::notifyAllPlayers("notifUseScepter", clienttranslate('${player_name} converts its Blacksmith\'s scepter reserve in ${ressources}'),
            array(
                'player_name' => $this->loadPlayersBasicInfos()[$player_id]['player_name'],
                'player_id'     => $player_id,
                'ressources' => $this->buildRessourceNotif(array ($resource => $amount)),
                'moonshard' => self::getGameStateValue('scepterMoonshard'),
                'fireshard' => self::getGameStateValue('scepterFireshard'),
                )
            );
        }

    }

    // return amount to reduce, if an uneven amount was used
    function checkScepterLoss($type) {
        $remaining = self::getGameStateValue('scepter' . $type);
        $used = self::getGameStateValue('usedScepter' . $type);
        $initial = $remaining + $used;

        //throw new feException($remaining . " " . $used . " " . $initial);

        // if uneven number
        if ($initial&1)
            return 0;
        // even number
        else {
            // we check that the amount used is even. else it means that one should be lost
            if ($used&1)
                return -1;
            return 0;
        }
    }

    function reallocateScepter($player_id, $value) {
        $scepters = $this->tokens->getTokensOfTypeInLocation("scepter_%", $player_id, 0);
        foreach ($scepters as $key => $token) {
            if ($value >= 2) {
                $this->tokens->setTokenState($key, 6);
                $value -= 2;
            }
            elseif ($value > 0) {
                $this->tokens->setTokenState($key, 4);
                $value -= 1;
            }
        }
    }

    function actCancelAllScepters($force = false) {
        if (!$force)
            self::checkAction("actCancelScepter");
        $player_id = self::getCurrentPlayerId();
        $fireLoss = self::checkScepterLoss('Fireshard');
        $moonLoss = self::checkScepterLoss('Moonshard');

        // read variable
        $resource = self::getGameStateValue('scepterFireshard') + self::getGameStateValue('scepterMoonshard');

        // nothing to do
        if ($resource == 0)
            return ;

        // fireshard usage
        $fireToConvert = self::getGameStateValue('scepterFireshard') + $fireLoss;
        self::reallocateScepter($player_id, $fireToConvert);

        // moonshard usage
        $moonToConvert = self::getGameStateValue('scepterMoonshard') + $moonLoss;
        self::reallocateScepter($player_id, $moonToConvert);

        if ($force) {
            self::setGameStateValue('scepterFireshard', 0);
            self::setGameStateValue('scepterMoonshard', 0);
            self::getGameStateValue('usedScepterFireshard', 0);
            self::getGameStateValue('usedScepterMoonshard', 0);
        }
        else {
            self::setGameStateValue('scepterFireshard', -$fireLoss);
            self::setGameStateValue('scepterMoonshard', -$moonLoss);
        }

        //notify
        self::notifyScepters('reset');
        //self::notifyAllPlayers("notifResetScepter", clienttranslate('${player_name} resets its Blacksmith\'s scepter(s) position'),
        //    array(
        //        'player_name' => $this->loadPlayersBasicInfos()[$player_id]['player_name'],
        //        'player_id'     => $player_id
        //        )
        //    );
        self::notifyAllPlayers("updateCounters", "", $this->getPlayersRessources());

        if (!$force)
            $this->gamestate->nextState('current');

    }


    function actUseTritonToken($ressources) {
        self::checkAction("actUseTritonToken");
        $player_id = self::getCurrentPlayerId();
        $player_name = $this->loadPlayersBasicInfos()[$player_id]['player_name'];
        $player_info = $this->getPlayersAdditionnalInfo()[$player_id];

        $turnPlayerId = $this->getGameStateValue("turnPlayerId");

        if (($player_id != $turnPlayerId && $player_info['ressource_choice'] != self::RC_MAZE))
            throw new BgaVisibleSystemException ("You cannot use this action you are not active");

        if (!$this->hasTritonToken($player_id))
                throw new BgaVisibleSystemException ("You do not have an available Triton token.");

        // decrease token
        $this->dbIncTriton($player_id, false);

        $notifPlayerArgs                = [];
        $notifPlayerArgs['player_id']   = $player_id;
        $notifPlayerArgs['player_name'] = $this->loadPlayersBasicInfos()[$player_id]['player_name'];
        $couldFillHammer                = $this->canFillHammer($player_id);
        // affect ressources
        $notifHammer = [];

        // check that ressource to update = ressource available
        $gold      = 0;
        $moonshard = 0;
        $fireshard = 0;
        $vp        = 0;
        $hammer    = 0;
        $scepters  = 0;

        foreach ($ressources as $res_name => $value) {
            if (isset($ressources[$res_name]) && $ressources[$res_name] != 0) {
                $value = $ressources[$res_name];

                $res_choosen = $res_name;
                switch ($res_name) {
                    case 'gold':
                        $gold = $gold + $value;
                        $scepter = $this->increaseGold($player_id, $value, 'scepter');
                        $scepters += $scepter;

                        $this->increaseGold($player_id, $value - $scepter);
                        break;

                    case 'hammer':
                        if (!$this->canFillHammer($player_id))
                            throw new BgaVisibleSystemException ("The player does not have an available hammer!");
                        elseif($this->canFillHammer($player_id)) {
                            $gold = $gold + $value;
                            $hammer = $hammer + $value;

                            // If too much hammer has been sent => send only what is necessary remaining put to gold
                            $remainingGold = $this->maxHammer($player_id, $value);
                            $hammer = $hammer - $remainingGold;

                            $notifHammer[] = $this->increaseGold($player_id, $value - $remainingGold, 'hammer');

                            $scepter = $this->increaseGold($player_id, $remainingGold, 'scepter');
                            $scepters += $scepter;

                            $this->increaseGold($player_id, $remainingGold - $scepters);
                        }
                        break;

                    case 'moonshard':
                        $moonshard = $moonshard + $value;
                        $this->increaseMoonShard($player_id, $value);
                        break;

                    case 'fireshard':
                        $fireshard = $fireshard + $value;
                        $this->increaseFireShard($player_id, $value);
                        break;
                }
            }
        }

        $notifPlayerArgs['gold']      = $gold - $hammer - $scepters;
        $notifPlayerArgs['moonshard'] = $moonshard;
        //$notifPlayerArgs['vp']        = $vp;
        $notifPlayerArgs['hammer']    = $hammer;
        $notifPlayerArgs['fireshard'] = $fireshard;
        $notifPlayerArgs['scepter'] = $scepters;

        // Control that only one ressource has been choosen
        if ((($gold != 0 && ($moonshard != 0 || $fireshard != 0 || $vp != 0)) ||
                   ($moonshard != 0 && ($gold != 0 || $fireshard != 0 || $vp != 0)) ||
                   ($fireshard != 0 && ($moonshard != 0 || $gold != 0 || $vp != 0)) ||
                   ($vp != 0 && ($moonshard != 0 || $fireshard != 0 || $gold != 0))))
           throw new BgaVisibleSystemException ("You can only select one ressource.");

        if ( $gold > 6 || $fireshard > 2 || $moonshard > 2)
               throw new BgaVisibleSystemException ("More resource allocated than authorized!");

        $notifPlayerArgs['ressources'] = $this->buildRessourceNotif( $notifPlayerArgs );
        self::notifyAllPlayers("notifUseTritonToken", clienttranslate('${player_name} uses a Triton token and gets ${ressources}'),
            $notifPlayerArgs
            );

        $this->generateNotifHammer($player_id, $notifHammer);

        self::notifyAllPlayers("updateCounters", "", $this->getPlayersRessources());

        if ($this->gamestate->state()['name'] == 'secondAction' && !$this->hasTritonToken($player_id) && !$this->canTakeSecondAction($player_id))
            $this->gamestate->nextState('endPlayerTurn');
        elseif ($this->gamestate->state()['name'] == 'reinforcement')
            $this->gamestate->nextState('reinforcement');
        else
            $this->gamestate->nextState('current');

    }

    function actUseCompanion($card_id) {
        self::checkAction("actUseCompanion");
        $player_id = self::getCurrentPlayerId();
        $player_name = $this->loadPlayersBasicInfos()[$player_id]['player_name'];

        $turnPlayerId = $this->getGameStateValue("turnPlayerId");
        if ($player_id != $turnPlayerId)
            throw new BgaVisibleSystemException ("You cannot use this action you are not active");

        $token_key = 'companion_' . $card_id;

        $companion_info = $this->tokens->getTokenInfo($token_key);

        if ($companion_info['state'] > 5)
            throw new BgaVisibleSystemException ("The Companion has already been used");
        elseif ($companion_info['state'] == 0)
            throw new BgaUserException (self::_("Companion's token is 0. No advantage to use it"));

        if ($companion_info['location'] != $player_id)
            throw new BgaVisibleSystemException ("You do not own this Companion's card");

        // Set card as used
        $this->tokens->setTokenState($token_key, 9999);

        $notifPlayerArgs                = [];
        $notifPlayerArgs['player_id']   = $player_id;
        $notifPlayerArgs['player_name'] = $this->loadPlayersBasicInfos()[$player_id]['player_name'];

        $this->increaseFireShard($player_id, $companion_info['state']);
        $this->increaseVP($player_id, $companion_info['state']);

        $notifPlayerArgs['gold']      = 0;
        $notifPlayerArgs['moonshard'] = 0;
        $notifPlayerArgs['vp']        = $companion_info['state'];
        $notifPlayerArgs['hammer']    = 0;
        $notifPlayerArgs['fireshard'] = $companion_info['state'];
        $notifPlayerArgs['card'] = $card_id;

        $notifPlayerArgs['ressources'] = $this->buildRessourceNotif( $notifPlayerArgs );
        self::notifyAllPlayers("notifUseCompanion", clienttranslate('${player_name} uses its Companion and gets ${ressources}'),
            $notifPlayerArgs
            );


        self::notifyAllPlayers("updateCounters", "", $this->getPlayersRessources());

        if ($this->gamestate->state()['name'] == 'secondAction' && !$this->hasCompanionToken($player_id) && !$this->canTakeSecondAction($player_id)) {
            $this->gamestate->nextState('endPlayerTurn');
        }
        else
            $this->gamestate->nextState('current');
    }

    /*
     * @param booelan $use  true if token is used
     */
    function actUseCerberusToken($use) {
        self::checkAction("actUseCerberusToken");
        $player_id = self::getCurrentPlayerId();
        $player_name = $this->loadPlayersBasicInfos()[$player_id]['player_name'];
        $player_info = $this->getPlayersAdditionnalInfo()[$player_id];

        if (!$use) {
            // flag card as "used"
            $this->dbUpdateTokenPlayed($player_id, 'cerberus', true);
            // notify no use of the token
//          self::notifyAllPlayers("doNothing", clienttranslate('${player_name} does not use a Cerberus token'),
//                        array(
//                            'player_name' => $player_name,
//                          )
//                      );
            // reset choice
            $this->dbSetChoice($player_id, self::RC_NOTHING_TODO);

            // we save the face if there was a modification
            $result = $this->sides->getCardsInLocation('dice1-p'.$player_id, 0);
            $this->dbUpdateThrow($player_id, 1, reset($result)['id']);
            $result = $this->sides->getCardsInLocation('dice2-p'.$player_id, 0);
            $this->dbUpdateThrow($player_id, 2, reset($result)['id']);

            if ($this->gamestate->state()['name'] == 'doeRessourceChoice')
                $this->setGameStateValue("enigmaDieNumber", -1);

            // disable user
            $next_state = $this->getNextState();
            $this->tokens->setTokenState("triple_$player_id", 0);
            $this->gamestate->setPlayerNonMultiactive($player_id, $next_state);
        }
        else {
            $choice = false;
            // check that token is available
            if (!$this->hasCerberusToken($player_id))
                throw new BgaVisibleSystemException ("You do not have an available Cerberus token.");

            // reinit resources token to avoid shield issues
            $this->tokens->setTokenState("fireshard_$player_id", 0);
            $this->tokens->setTokenState("gold_$player_id", 0);
            $this->tokens->setTokenState("moonshard_$player_id", 0);
            $this->tokens->setTokenState("ancientshard_$player_id", 0);
            $this->tokens->setTokenState("vp_$player_id", 0);
            $this->tokens->setTokenState("triple_$player_id", 0);
            $this->tokens->setTokenState("loyalty_$player_id", 0);
            $this->tokens->setTokenState("ancientshard_$player_id", 0);

            // flag card as used
            $this->dbUpdateTokenPlayed($player_id, 'cerberus', true);
            $this->tokens->setTokenState("cerberus_" . $player_id, 1);
            // decrease token
            $this->dbIncCerberus($player_id, false);

            $dice_number = $this->getGameStateValue("enigmaDieNumber");
            if ($dice_number == 1) {
                $displaySides = $this->sides->getCard($player_info['throw_1'])['type'];
            }
            elseif ($dice_number == 2) {
                $displaySides = $this->sides->getCard($player_info['throw_2'])['type'];
            }
            else {
                $displaySides = $this->sides->getCard($player_info['throw_1'])['type'] . "," .  $this->sides->getCard($player_info['throw_2'])['type'];
            }


            //$displaySides = $this->sides->getCard($player_info['throw_1'])['type'] . "," .  $this->sides->getCard($player_info['throw_2'])['type'];

            // notify deletion of token
            self::notifyAllPlayers("notifUseCerberusToken", clienttranslate('${player_name} uses a Cerberus token. Throw is replayed → ${sides_rolled}'),
                        array(
                            'player_name' => $player_name,
                            'player_id'     => $player_id,
                            'sides_rolled'  => $displaySides
                            )
                        );

            // launch blessing without rolling
            // Which dice should be launched?
            // if blessing : both
            // if reinforcement : one  (enigma die)
            // if ousted : both
            // card bought : depends on the card
            $stateName = $this->gamestate->state()['name'];
            // blessing
            switch ($stateName) {
                case 'ressourceChoice':
                case 'playerOustingChoice':
                    $choice = $this->blessing($player_id, true, true, 1, false, false, true);
                    break ;
                case 'doeRessourceChoice':
                    // TODO: check new cards, impacts?
                    $dice_number = $this->getGameStateValue("enigmaDieNumber");
                    if ($dice_number == -1)
                        throw new BgaVisibleSystemException ("Error, no doe is chosen");
                    elseif($dice_number == 1)
                        $choice = $this->blessing($player_id, true, false, 1, false, false, true);
                    elseif($dice_number == 2)
                        $choice = $this->blessing($player_id, false, true, 1, false, false, true);

                    $this->setGameStateValue("enigmaDieNumber", -1);

                    break ;
                case 'exploitRessource':
                    $card_id   = $this->getGameStateValue( "exploitBought");
                    $card      = $this->exploits->getCard($card_id);
                    $card_info = $this->exploit_types[$card['type']];

                    switch ($card_info['action']) {
                        // TODO: add new cards (celestial?) ==> TBC by Léa
                        case '4Throws':
                        case '4ThrowsTransform':
                            $die_number =  $this->getGameStateValue( "enigmaDieNumber");
                            if($die_number == 1)
                                $choice = $this->blessing($player_id, true, false, 1, true, false, true);
                            elseif($die_number == 2)
                                $choice = $this->blessing($player_id, false, true, 1, true, false, true);
                            break;
                        case 'fullThrow2':
                        case 'fullThrow2Transform':
                            $choice = $this->blessing($player_id, true, true, 1, true, false, true);
                            break;
                        case 'looseThrow':
                                $choice = $this->blessing($player_id, true, true, -1, true, false, true);
                                break;
                        case 'chooseSides':
                            $choice = $this->blessing($player_id, true, true, 1, true, false, true);
                            break;
                        default:
                            $this->dbSetChoice($player_id, self::RC_NOTHING_TODO);
                            break;
                        }
                    break ;
            }

            // we save the face if there was a modification
            $result = $this->sides->getCardsInLocation('dice1-p'.$player_id, 0);
            $this->dbUpdateThrow($player_id, 1, reset($result)['id']);
            $result = $this->sides->getCardsInLocation('dice2-p'.$player_id, 0);
            $this->dbUpdateThrow($player_id, 2, reset($result)['id']);

            $player_info = $this->getPlayersAdditionnalInfo()[$player_id];

            self::notifyAllPlayers("updateCounters", "", $this->getPlayersRessources());
            if ($choice && $player_info['ressource_choice'] == -1)
                $this->setChoice($player_id, self::RC_RESSOURCE);

            if ($choice)
                $this->gamestate->nextState('choice');
            else {
                // disable user
                $next_state = $this->getNextState();
                $this->gamestate->setPlayerNonMultiactive($player_id, $next_state);
            }


        }
    }

    function actActionChoice($action, $die, $goldSources = null)
    {
        self::checkAction("actActionChoice");
        $player_id = self::getCurrentPlayerId();
        $player_info = $this->getPlayersAdditionnalInfo()[$player_id];
        $multiple = 1;

        $exploit = false;
        $card_id = $this->getGameStateValue( "exploitBought");
        if ($card_id != -1 && $this->gamestate->state()['name'] == 'exploitRessource') {
            $exploit = true;
            $card = $this->exploits->getCard($card_id);
            $card_info = $this->exploit_types[$card['type']];
            if ($card_info['action'] == 'looseThrow') {
                $multiple = -1;
            }
        }

        // management of celestial dice
        if ($action == 'celestial') {
            // Twins are not played
            $roll = $this->rollCelestial($player_id, false);
            //$this->setChoice($player_id, null);

            // Tweak for double upgrade, need to force entering again the effect
            //if ($this->getCelestial() == "doubleUpgrade")
            //    $this->incGameStateValue( "exploitRemainingThrows", 1);
            //$this->debugVTO($player_id);
            if ($roll == 'doubleUpgrade') {
                $this->dbSetChoice($player_id, self::RC_FORGESHIP);
            }

            $this->gamestate->nextState("nextState");
        }
        elseif ($action == 'rerollCelestial') {
            // increase twin
            $this->dbIncTwins($player_id);
            // decrease gold
            // Scepter & twins cannot be together
            $this->decreaseGold($player_id, 3);

            $card_id = $this->updateAvailableTwin($player_id);
            $card  = $this->exploits->getCard($card_id);
            $card_info = $this->exploit_types[$card['type']];
            // TODO: notif, pour maj icone + i118n

            self::notifyAllPlayers("twinUpdate", clienttranslate('${player_name} rerolls Celestial die using ${card_name}\' power'),
                                    array('i18n' => array ('card_name'),
                                          'player_name' => $player_info['player_name'],
                                          'player_id' => $player_id,
                                          'die_num' => $die,
                                          'card_name' => $card_info['name'],
                                          'card_id' => $card_id
                                          )
                                    );

            $this->setChoice($player_id, null);
            $side = $this->rollCelestial($player_id, true);

            if (!$this->canUseTwins($player_id)) {
                // resolde twin
                $this->resolveTwin($player_id, true);
            }

            // Tweak for double upgrade, need to force entering again the effect
            //if ($this->getCelestial() == "doubleUpgrade")
            //    $this->incGameStateValue( "exploitRemainingThrows", 1);
            if ($side == 'doubleUpgrade') {
                $this->dbSetChoice($player_id, self::RC_FORGESHIP);
            }

            $this->gamestate->nextState("nextState");

        }
        elseif ($die != 0) {
            $side = $player_info['side_choice_' . $die];
            if ($die == 1)
                $otherDie = 2;
            else
                $otherDie = 1;
            $otherSide = $player_info['side_choice_' . $otherDie];
            switch ($action) {
                case 'ressource':
                    if ($side == 'ship') {
                        $this->dbSetChoice($player_id, self::RC_FORGESHIP);
                        $next_state = $this->getNextState(self::RC_FORGESHIP);
                        $this->gamestate->setPlayerNonMultiactive($player_id, $next_state);
                    }
                    else {
                        if ($die == 1)
                            $did = $this->blessing($player_id, true, false, $multiple, $exploit, false);
                        else
                            $did = $this->blessing($player_id, false, true, $multiple, $exploit, false);

                        if ($side == 'triple') {
                            $this->tokens->setTokenState("triple_$player_id", 1);
                            $this->dbSetSideChoice($player_id, $die,'0');
                        }
                        elseif (substr($side, -4) == 'Boar' && $this->getBoar($side) != 0) {
                            $this->dbIncBoar($this->getBoar($side), true);
                            $this->setChoice($this->getBoar($side), self::RC_RESSOURCE);
                        }
                        //// Misfortune : do not do as it will be assign though blessing
                        //elseif (self::isMisfortune($side) != false) {
                        //    $mis_player_id = self::isMisfortune($side);
                        //    $this->dbIncMisfortune($mis_player_id, true);
                        //    $this->setChoice($mis_player_id, self::RC_RESSOURCE);
                        //}

                        $this->tokens->setTokenState("twinChoice_" . $player_id, 0);

                        $player_info = $this->getPlayersAdditionnalInfo()[$player_id];
                        $side = $player_info['side_choice_' . $die];
                         //throw new feException($player_info['ressource_choice'] . " " . $did . " " . $side);
                         //$this->debugVTO(2305535);
                        // Mirror choice
                        if ($did && $side == '-1') {
                            $this->dbSetChoice($player_id, self::RC_SIDE_CHOICE);
                            $this->gamestate->nextState("choice");
                        }
                        // ressource choice
                        elseif ($did) {
                            $this->tokens->setTokenState("twinChoice_" . $player_id, $die);
                            $this->dbSetChoice($player_id, self::RC_RESSOURCE);
                            $this->gamestate->nextState("choice");
                        }
                        // we need to choose the twin resource
                        elseif ($player_info['twins'] != 0) {
                            $this->resolveTwin($player_id, true);
                            $this->dbSetChoice($player_id, self::RC_RESSOURCE);
                            $this->gamestate->nextState("choice");
                        }
                        // if we have some maze, we need to move in it
                        elseif ($this->tokens->getTokenState("mazestock_" . $player_id) != 0) {
                            $this->dbSetChoice($player_id, self::RC_NOTHING_TODO);
                            //$ret = $this->dbSetChoice($player_id, self::RC_MAZE);
                            //throw new feException ($ret);
                            $this->gamestate->nextState("nextState");
                        }
                        // other side to reroll
                        elseif ($this->canUseTwins($player_id) && $otherSide != "0") {
                            $this->setChoice($player_id, null);
                            //$this->debugVTO($player_id);
                            $this->titanMove($player_id);
                            $this->gamestate->nextState("choice");
                        }
                        // other side is ship
                        elseif ($otherSide == 'ship') {
                            $this->dbSetChoice($player_id, self::RC_FORGESHIP);
                            $this->titanMove($player_id);
                            $next_state = $this->getNextState(self::RC_FORGESHIP);
                            $this->gamestate->setPlayerNonMultiactive($player_id, $next_state);
                        }
                        // another side to choose
                        elseif ($otherSide != "0") {
                            $this->titanMove($player_id);
                            $this->actActionChoice('ressource', $otherDie);
                        }
                        // nothing to do
                        else {
                            $this->titanMove($player_id);
                            $res = $this->setChoice($player_id, null);
                            $this->gamestate->setPlayerNonMultiactive($player_id, 'nextState');
                        }
                    }
                    //throw new feException("stio");
                    break ;
                case 'reroll':
                    // increase twin
                    $this->dbIncTwins($player_id);
                    // decrease gold
                    // Scepter & twins cannot be together
                    $this->decreaseGold($player_id, 3);
                    // notify reroll

                    $card_id = $this->updateAvailableTwin($player_id);
                    $card  = $this->exploits->getCard($card_id);
                    $card_info = $this->exploit_types[$card['type']];

                    self::notifyAllPlayers("twinUpdate", clienttranslate('${player_name} rerolls die #${die_num} using ${card_name}\' power'),
                                            array('i18n' => array ('card_name'),
                                                  'player_name' => $player_info['player_name'],
                                                  'player_id' => $player_id,
                                                  'die_num' => $die,
                                                  'card_name' => $card_info['name'],
                                                  'card_id' => $card_id
                                                  )
                                            );

                    // reroll
                    if ($die == 1)
                        $choice = $this->blessing ($player_id, true, false);
                    elseif ($die == 2)
                        $choice = $this->blessing ($player_id, false, true);

                    self::notifyAllPlayers("updateCounters", "", $this->getPlayersRessources());
                    // No choice => value has been affected: flag twin token
                    if (!$choice)
                        $this->resolveTwin($player_id, true);

                    // get latest information
                    $player_info = $this->getPlayersAdditionnalInfo()[$player_id];

                    // go to next state
                    // if we need to choose (ressource or twin effect)
                    if ($choice) {
                        $this->tokens->setTokenState("twinChoice_" . $player_id, $die);
                        $this->gamestate->nextState("choice");
                    }
                    // if we have some maze, we need to move in it
                        elseif ($this->tokens->getTokenState("mazestock_" . $player_id) != 0) {
                            $this->dbSetChoice($player_id, self::RC_NOTHING_TODO);
                            //$ret = $this->dbSetChoice($player_id, self::RC_MAZE);
                            //throw new feException ($ret);
                            $this->gamestate->nextState("blessing");
                    }
                    elseif ($this->canUseTwins($player_id)) {
                        $this->gamestate->nextState("choice");
                    }
                    // side has been affected, need to ask for twin ressources
                    elseif ($this->tokens->getTokenState("resolveTwin_" . $player_id)) {
                        $this->setChoice($player_id, null);
                        $this->gamestate->nextState("choice");
                    }
                    //// we need to choose as we have a maze and another side
                    //elseif ($player_info['ressource_choice'] == self::RC_SIDE_CHOICE) {
                    //    $this->gamestate->nextState("choice");
                    //}
                    // cannot use twins, we trigger ressource allocation
                    elseif ($otherSide != "-1" && $otherSide != "0" && $otherSide != "triple" && $otherSide != "ship") {
                        $this->actActionChoice('ressource', $otherDie);
                    }
                    elseif ($otherSide == 'ship') {
                            $this->dbSetChoice($player_id, self::RC_FORGESHIP);
                            $next_state = $this->getNextState(self::RC_FORGESHIP);
                            $this->gamestate->setPlayerNonMultiactive($player_id, $next_state);
                    }
                    else {
                        // choice must be enabled as choice between 1MS / 1 VP
                        $this->gamestate->nextState("choice");
                        //$this->gamestate->setPlayerNonMultiactive($player_id, 'nextState');
                    }
                    break;
            }
            // do the next thing
        }
        self::notifyAllPlayers("updateCounters", "", $this->getPlayersRessources());

        //if ($action == 'forge') {
        //    $this->dbSetChoice($player_id, self::RC_FORGESHIP);
        //    // Go to correct State
        //    // depends on the state name
        //    $next_state = $this->getNextState(self::RC_FORGESHIP);
        //    $this->gamestate->setPlayerNonMultiactive($player_id, $next_state);
        //    //$this->gamestate->setPlayersMultiactive(self::getCurrentPlayerId(), $next_state );
        //}
        //else {
        //    //$this->dbSetChoice($player_id, self::RC_RESSOURCE);
        //
        //    $player_info = $this->getPlayersAdditionnalInfo()[$player_id];
        //
        //    if ($player_info['side_choice_1'] != 'ship'){
        //        $side = $player_info['side_choice_1'];
        //        $sideNum = 1;
        //    }
        //    else{
        //        $side = $player_info['side_choice_2'];
        //        $sideNum = 2;
        //    }
        //
        //    $did = $this->affectGoldOnly($player_id, 'blessing', $side, $sideNum);
        //
        //    // nothing done, so choice
        //    if (!$did) {
        //        $this->dbSetChoice($player_id, self::RC_RESSOURCE);
        //        $this->gamestate->nextState("choice");
        //    }
        //    else
        //        $this->actActionChoice('forge');
        //}


    }


    function actActionMisfortune($action, $die, $goldSources = null)
    {
        self::checkAction("actActionMisfortune");
        $player_id = self::getCurrentPlayerId();
        $player_info = $this->getPlayersAdditionnalInfo()[$player_id];
        $notifPlayerArgs = $this->initNotif($player_id);

        $exploit = false;

       if ($die != 0) {
            $side = $this->tokens->getTokenLocation('misfortune_' . $die);
            if ($die == 1)
                $otherDie = 2;
            else
                $otherDie = 1;

            $otherSide = $this->tokens->getTokenLocation('misfortune_' . $otherDie);
            switch ($action) {
                case 'ressource':
                    // allocation of the side
                    $notifPlayerArgs = $this->affectRessource($player_id, $notifPlayerArgs, 1, $side, false, 1, false, true, false, true);

                    // if no choice we affect second side
                    if (!$notifPlayerArgs['choice']) {
                        $this->tokens->moveToken('misfortune_' . $die, 'none');
                        $notifPlayerArgs = $this->affectRessource($player_id, $notifPlayerArgs, 1, $otherSide, false, 1, false, true, false, true);
                         if (!$notifPlayerArgs['choice']) {
                            $this->tokens->moveToken('misfortune_' . $otherDie, 'none');
                         }
                         else {
                            $this->tokens->setTokenState('misfortune_' . $otherDie, 1);
                         }
                    }
                    else {
                        $this->tokens->setTokenState('misfortune_' . $die, 1);
                    }

                    if (!($notifPlayerArgs['gold'] == 0 && $notifPlayerArgs['vp'] == 0 && $notifPlayerArgs['moonshard'] == 0 && $notifPlayerArgs['fireshard'] == 0 && $notifPlayerArgs['hammer'] == 0 && $notifPlayerArgs['scepter'] == 0 && $notifPlayerArgs['maze'] == 0 && $notifPlayerArgs['loyalty'] == 0 && $notifPlayerArgs['ancientshard'] == 0))
                    {
                        // petit hack
                        if ( $notifPlayerArgs['vp'] == 0 )
                        {
                            unset( $notifPlayerArgs['vp'] );
                        }
                        $notifPlayerArgs['ressources'] = $this->buildRessourceNotif( $notifPlayerArgs );
                        self::notifyAllPlayers("notifBlessing", clienttranslate('${player_name} gets ${ressources} from the Mirror of Misfortune '), $notifPlayerArgs );
                    }
                    else
                        self::notifyAllPlayers("notifBlessing", '', $notifPlayerArgs);

                    $this->generateNotifLoyalty ($notifPlayerArgs);

                    if (isset($notifPlayerArgs['notifHammer']) && count($notifPlayerArgs['notifHammer']) != 0) {
                        $this->generateNotifHammer($player_id, $notifPlayerArgs['notifHammer']);
                    }

                    $this->titanMove($player_id);
                    break ;

            }

        }
        self::notifyAllPlayers("updateCounters", "", $this->getPlayersRessources());
        // a choice will be mandatory
        $this->gamestate->nextState("nextState");

    }


    /*
     * Actions of the reinforcement
     *
     */
    function actReinforcement($card_id, $owl_parameters = null, $doe_parameters = null, $merchant_parameters = null, $force = false)
    {
        if (!$force)
            self::checkAction( "actReinforcement");

        $current_player_id = $this->getGameStateValue( "turnPlayerId" );
        $player_id         = self::getActivePlayerId();
        $notifHammer       = [];
        $dice1             = false;
        $dice2             = false;
        $players_info      = $this->getPlayersAdditionnalInfo();
        $choice            = false;
        $exploit_card      = $this->exploits->getCard($card_id);
        $this->resetThrowTokens();

        if ($player_id != $current_player_id)
            throw new BgaVisibleSystemException ("This is not your turn");

        // Check that the card is owned by the player
        if($exploit_card['location'] != "pile3-" . $player_id)
            throw new BgaVisibleSystemException ("You do not own this card");

        // Check that the card has not been already played
        if ($exploit_card['type_arg'] != 0)
        //throw new feException (debug_print_backtrace());
            throw new BgaVisibleSystemException ("You have already played this card");

        if ($card_id != 0){
            $card_type = $this->exploits->getCard($card_id)['type'];
            $card = $this->exploit_types[$card_type];

            if ($card['actionType'] != "recurrent")
                throw new BgaVisibleSystemException ("This card is not a reinforcement!");

            switch ($card_type) {
                case "doe":
                    $this->resetTwins();
                    // doe_parameter send the dice number
                    if ($doe_parameters == 1) {
                        $dice1 = true;
                        $this->setGameStateValue('enigmaDieNumber', 1);
                    }
                    else {
                        $dice2 = true;
                        $this->setGameStateValue('enigmaDieNumber', 2);
                    }
                    self::notifyAllPlayers("doNothing", clienttranslate('${player_name} use the Silver Hind (die ${die_num})'),
                        array(
                            'player_name' => self::getActivePlayerName(),
                            'die_num'     => $doe_parameters )
                    );
                    $choice = $this->blessing($player_id, $dice1, $dice2);
                    break;

                case "owl":
                    if ($owl_parameters == null)
                        throw new BgaVisibleSystemException ("Wrong parameters!");

                    $notifArgs = [];
                    // owl_parameters: contain only the name of the ressource
                    switch ($owl_parameters) {
                        case 'gold':
                            $scepter = $this->increaseGold($player_id, 1, 'scepter');
                            $this->increaseGold ($player_id, 1 - $scepter);
                            $notifArgs['gold'] = 1 - $scepter;
                            $notifArgs['scepter'] = $scepter;
                            break;

                        case 'hammer':
                            if (!$this->canFillHammer($player_id))
                                throw new BgaVisibleSystemException("The player does not have an available hammer!");
                            $notifHammer[] = $this->increaseGold($player_id, 1, 'hammer');
                            $notifArgs['hammer'] = 1;
                            break;

                        case 'moonshard':
                            $this->increaseMoonShard ($player_id, 1);
                            $notifArgs['moonshard'] = 1;
                            break;

                        case 'fireshard':
                            $this->increaseFireShard ($player_id, 1);
                            $notifArgs['fireshard'] = 1;
                            break;

                        default:
                            throw new BgaVisibleSystemException("This resource is not authorized");
                            break;
                    }
                    self::notifyAllPlayers("notifRessource", clienttranslate('${player_name} uses the Owl and gets ${ressources}'),
                        array(
                            'player_name' => self::getActivePlayerName(),
                            'ressources'  => $this->buildRessourceNotif( $notifArgs ),
                        )
                    );
                    $this->generateNotifHammer($player_id, $notifHammer);
                    break;

                case "ancient":
                    //if ($players_info[$player_id]['res_gold'] < 3)
                    if ($this->getGold($player_id) < 3)
                        throw new BgaVisibleSystemException("You do not have enough gold");
                    $this->decreaseGold($player_id, 3);
                    $this->increaseVP($player_id, 4);
                    $this->incStat(4, 'nb_vp_ancient', $player_id);
                    self::notifyAllPlayers("notifAncient", clienttranslate('${player_name} use the ${card_name_trans} and gets ${ressources}'),
                        array(
                            'i18n'              => array ('card_name_trans'),
                            'player_name'       => self::getActivePlayerName(),
                            'card_name_trans'   => $card['name'],
                            'ressources'        => $this->buildRessourceNotif( array("vp" => 4) ),
                        )
                    );
                    break;
                case 'nymphe':
                    if ($players_info[$player_id]['res_moon'] < 5)
                        $vp = 2;
                    else
                        $vp = 4;

                    $this->increaseVP($player_id, $vp);
                    $this->incStat($vp, 'nb_vp_nymph', $player_id);

                    self::notifyAllPlayers("notifAncient", clienttranslate('${player_name} use the ${card_name_trans} and gets ${ressources}'),
                        array(
                            'i18n'              => array ('card_name_trans'),
                            'player_name'       => self::getActivePlayerName(),
                            'card_name_trans'   => $card['name'],
                            'ressources'        => $this->buildRessourceNotif( array("vp" => $vp) ),
                        )
                    );
                    break;
                case 'companion':
                    // increase 1 max 5
                    $token_key = 'companion_' . $card_id;
                    $val = $this->tokens->getTokenState($token_key);
                    if ($val < 5) {
                        $this->tokens->setTokenState($token_key, ++$val);
                        self::notifyAllPlayers("notifCompanion", clienttranslate('${player_name} moves ${card_name_trans}\'s token to ${val}'),
                            array(
                                'i18n'              => array ('card_name_trans'),
                                'player_name'       => self::getActivePlayerName(),
                                'card_name_trans'   => $card['name'],
                                'card_id'           => 'power-' . $card_id,
                                'val'               => $val,
                            )
                        );
                    }

                    break ;
                case 'tree':
                    // automatic choice
                    if ($owl_parameters == null) {
                        if ($players_info[$player_id]['res_gold'] < 8) {
                        //if ($this->getGold($player_id) < 8) {
                            $notifPlayerArgs['vp'] = 1;

                            if ($this->canFillHammer($player_id)) {
                                $ressourceChoice = true;
                                $this->dbSetChoice($player_id, 1);
                                $this->dbSetSideChoice($player_id, 1,'G3');
                            }
                            else {
                                //$this->increaseGold($player_id, 3);
                                $scepter = $this->increaseGold($player_id, 3, 'scepter');

                                $this->increaseGold($player_id, 3 - $scepter);
                                $notifPlayerArgs['gold'] = 3 - $scepter;
                                $notifPlayerArgs['scepter'] = $scepter;
                            }
                        }
                        else {
                            $notifPlayerArgs['vp'] = 2;
                        }
                    }
                    elseif ($owl_parameters == 'vp') {
                        if ($this->getGold($player_id) < 8 || $players_info[$player_id]['res_gold'] > 8) {
                            throw new feException("Tree choice should not happen");
                        }

                        $notifPlayerArgs['vp'] = 2;

                    }
                    elseif ($owl_parameters == 'gold') {
                        if ($this->getGold($player_id) < 8 || $players_info[$player_id]['res_gold'] > 8)
                            throw new feException("Tree choice should not happen");

                         $notifPlayerArgs['vp'] = 1;
                        if ($this->canFillHammer($player_id)) {
                            $ressourceChoice = true;
                            $this->dbSetChoice($player_id, 1);
                            $this->dbSetSideChoice($player_id, 1,'G3');
                        }
                        else {
                            //$this->increaseGold($player_id, 3);
                            $scepter = $this->increaseGold($player_id, 3, 'scepter');

                            $this->increaseGold($player_id, 3 - $scepter);
                            $notifPlayerArgs['gold'] = 3 - $scepter;
                            $notifPlayerArgs['scepter'] = $scepter;
                        }
                    }

                    $this->increaseVP($player_id, $notifPlayerArgs['vp']);
                    $this->incStat($notifPlayerArgs['vp'], 'nb_vp_tree', $player_id);

                    $notifPlayerArgs['player_id']   = $player_id;
                    $notifPlayerArgs['player_name'] = $this->loadPlayersBasicInfos()[$player_id]['player_name'];

                    $notifPlayerArgs['ressources'] = $this->buildRessourceNotif( $notifPlayerArgs );

                    self::notifyAllPlayers("notifAncient", clienttranslate('${player_name} uses ${card_name_trans} and gets ${ressources}'),
                        array(
                            'i18n'              => array ('card_name_trans'),
                            'player_name'       => self::getActivePlayerName(),
                            'card_name_trans'   => $card['name'],
                            'ressources'        => $notifPlayerArgs['ressources'],
                        )
                    );
                    break ;
                case 'merchant':
                    // Gain X VP
                    // upgrade once one side x level
                    $nbMerchant = $this->countExploitInLocation('merchant', "pile3-$player_id");
                    $nbVp = $nbMerchant - $merchant_parameters['nbUpgrade'];

                    if ($merchant_parameters['nbUpgrade'] > $nbMerchant)
                        throw new BgaVisibleSystemException("More merchants sent than owned");
                    // temp exception

                    if ($merchant_parameters['nbUpgrade'] > 0) {

                        $this->checkUpgrade($merchant_parameters['nbUpgrade'], $merchant_parameters['old_side'], $merchant_parameters['new_side']);
                        //// upgrade the side
                        //$pools = array(0, 2, 3, 4, 5, 6, 8, 12);
                        //$pools_info = array ( 2 => array (1, 2),
                        //                      3 => array (3, 4),
                        //                      4 => array (5),
                        //                      5 => array (6),
                        //                      6 => array (7),
                        //                      8 => array (8, 9),
                        //                      12 => array (10)
                        //                    );
                        //// check number of increased pool
                        //$old_side_id = $merchant_parameters['old_side'];
                        //$old_side_type = $this->sides->getCard($old_side_id)['type'];
                        //$new_side_id = $merchant_parameters['new_side'];
                        //$new_side_type = $this->sides->getCard($new_side_id)['type'];
                        //
                        //$old_cost = $this->dice_sides[$old_side_type]['cost'];
                        //$new_cost = $this->dice_sides[$new_side_type]['cost'];
                        //
                        //$pos = array_search($old_cost, $pool);
                        //$new_pos = $pos + $merchant_parameters['nbUpgrade'];
                        //
                        //// If pool empty => can take next one
                        //$found = false;
                        //do {
                        //    foreach ($pools_info[$new_pos] as $pos => $pool) {
                        //        if (count($this->sides->countCardsByLocationArgs($pool)) != 0) {
                        //            $found = true;
                        //        }
                        //    }
                        //
                        //    if (!$found) {
                        //        $newpos++;
                        //        if (!isset($pools[$new_pos]))
                        //            throw new feException("You cannot upgrade to this level this die face");
                        //    }
                        //} while (!$found);
                        //
                        //if ($new_code != $pools[$new_pos])
                        //    throw new feException("The side is not the pool matching the upgrade");
                        //
                        $this->actBuyForge($merchant_parameters['new_side'], $merchant_parameters['old_side'], 'upgrade');
                    }

                    if ($nbVp > 0) {
                        // gain 2 * X VP
                        $notifPlayerArgs = $this->initNotif($player_id);
                        $notifPlayerArgs['vp'] = 2 * $nbVp;
                        $notifPlayerArgs['ressources'] = $this->buildRessourceNotif( $notifPlayerArgs );
                        $this->increaseVP($player_id, $notifPlayerArgs['vp']);

                        self::notifyAllPlayers("notifAncient", clienttranslate('${player_name} uses ${card_name_trans} and gets ${ressources}'),
                            array(
                                'i18n'              => array ('card_name_trans'),
                                'player_name'       => self::getActivePlayerName(),
                                'card_name_trans'   => $card['name'],
                                'ressources'        => $notifPlayerArgs['ressources'],
                            )
                        );

                    }

                    // we must flag all merchants
                    foreach($this->exploits->getCardsOfType("merchant") as $card) {
                        if ($card['location'] == "pile3-" . $player_id)
                            $this->dbUpdateExploitPlayed($card['id'], true);
                    }
                    break ;
                case 'light':
                    $side = $this->sides->getCard($doe_parameters);
                    // check side up
                    if (!$this->checkValidVisibleSide($side['type']))
                        throw new feException("This side is not visible");
                    // reduce 3G
                    //if ($players_info[$player_id]['res_gold'] < 3)
                    if ($this->getGold($player_id) < 3)
                        throw new BgaVisibleSystemException("You do not have enough gold");
                    $this->decreaseGold($player_id, 3);
                    // apply effect
                    $this->dbUpdateThrow($player_id, 1, $doe_parameters);

                    if (substr($side['type'], -4) == 'Boar' && $this->getBoar($side['type']) != 0) {
                        $this->dbIncBoar($this->getBoar($side['type']), true);
                        $this->setChoice($this->getBoar($side['type']), self::RC_RESSOURCE);
                    }
                    // Misfortune
                    elseif (self::isMisfortune($side['type']) != false) {
                        $mis_player_id = self::isMisfortune($side['type']);
                        //$this->dbIncMisfortune($mis_player_id, true);
                        $this->tokens->moveToken('resolveMisfortune', $side['type'], $mis_player_id);
                        $this->setChoice($mis_player_id, self::RC_RESSOURCE);
                    }


                    self::notifyAllPlayers("notifMessage", clienttranslate('${player_name} use the Light and copy the side ${side_type}'),
                        array(
                            'player_name'   => self::getActivePlayerName(),
                            'side_type'     => $side['type']
                        )
                    );
                    // TODO: bonus doit être déclenché? ==> pour moi non
                    $choice = $this->blessing($player_id, true, false, 1, false, false);

                    break ;
                case "guardian":
                    if ($owl_parameters == null)
                        throw new BgaVisibleSystemException ("Wrong parameters!");

                    $notifArgs = $this->initNotif($player_id);
                    unset($notifArgs['vp']);
                    // owl_parameters: contain only the name of the ressource
                    switch ($owl_parameters) {
                        case 'ancient':
                            $this->increaseAncientShard($player_id, 1);
                            // value * -1 because ancient shard must move back
                            $this->tokens->incTokenState("movetitan_$player_id", -1);
                            $notifArgs['ancientshard'] = 1;
                            break;

                        case 'loyalty':
                            $this->tokens->incTokenState("movetitan_$player_id", 1);
                            $notifArgs['loyalty'] = 1;
                            break;

                        default:
                            throw new BgaVisibleSystemException("This resource is not authorized");
                            break;
                    }

                    self::notifyAllPlayers("notifRessource", clienttranslate('${player_name} uses the Guardian and gets ${ressources}'),
                        array(
                            'player_name' => self::getActivePlayerName(),
                            'ressources'  => $this->buildRessourceNotif( $notifArgs ),
                        )
                    );
                    $this->titanMove($player_id);
                    break;
                case "oracle":
                    $this->resetTwins();
                    $this->setGameStateValue("oracleReinforcement", 1);
                    // doe_parameter send the dice number
                    if ($doe_parameters == 1) {
                        $dice1 = true;
                        $this->setGameStateValue('enigmaDieNumber', 1);
                    }
                    else {
                        $dice2 = true;
                        $this->setGameStateValue('enigmaDieNumber', 2);
                    }
                    self::notifyAllPlayers("doNothing", clienttranslate('${player_name} use the Oracle (die ${die_num})'),
                        array(
                            'player_name' => self::getActivePlayerName(),
                            'die_num'     => $doe_parameters )
                    );
                    $choice = $this->blessing($player_id, $dice1, $dice2);
                    // TODO add move of all players  in choice also
                    break;
            }
            $this->dbUpdateExploitPlayed($card_id, true);
            self::notifyAllPlayers("updateCounters", "", $this->getPlayersRessources());
            $this->incStat(1, 'nb_reinforcement', $player_id);
        }

        $this->gamestate->nextState("reinforcement");
    }

    // If there is a ressource to choose
    function actDoeTakeRessource($sideNum, $side, $ressources)
    {
        self::checkAction("actDoeTakeRessource");

        $current_player_id = $this->getGameStateValue( "turnPlayerId" );
        //$player_id = self::getActivePlayerId();
        $player_id = self::getCurrentPlayerId();
        $dice_number = $this->getGameStateValue("enigmaDieNumber");
        $disable = $this->takeRessource2($player_id, 'dice' . $dice_number, $sideNum, $side, $ressources);

        $this->incStat(1, 'nb_ressource_choice', $player_id);

        $this->setGameStateValue("enigmaDieNumber", -1);

        if ($this->getGameStateValue("oracleReinforcement") == 1) {
            if ($ressources['loyalty'] != 0 || $ressources['ancientshard'] != 0)
                $this->oracleMove($current_player_id);
            $this->setGameStateValue("oracleReinforcement", 0);
        }

        if ($disable)
            $this->gamestate->setPlayerNonMultiactive($player_id, 'nextState');
        else
            $this->gamestate->nextState('choice');
    }

    // Called when Oracle reinforcement is played
    // all players except the player_id in parameter should move 1 slot to the left
    function oracleMove($player_id) {
        $players_info = $this->getPlayersAdditionnalInfo();

        self::notifyAllPlayers("notifMessage", clienttranslate("Oracle effect: All other players move one step to Titan's side "),
               array(
               )
           );

        foreach ($players_info as $curr_player_id => $player_info)  {
            if ($curr_player_id != $player_id) {
                $this->tokens->incTokenState("movetitan_$curr_player_id", -1);
                $this->titanMove($curr_player_id);
            }
        }
    }

    // If the player wish to pass his reinforcement
    function actReinforcementPass() {
        self::checkAction("actReinforcementPass");

        self::notifyAllPlayers("notifReinforcementPass", clienttranslate('${player_name} has decided to not play reinforcement cards'),
            array(
                'player_name' => self::getActivePlayerName()
            )
        );
        $this->gamestate->nextState("playerAction");
    }


    // Launched when a player buys an exploit
    // will trigger the next state
    function actBuyExploit($card_id, $ressources = null) {
        self::checkAction ("actBuyExploit");

        $player_id = self::getActivePlayerId();
        $card = $this->exploits->getCard($card_id);
        $card_info = $this->exploit_types[$card['type']];
        $hasPreviousExploit = false;
        $scepterFire = 0;
        $scepterMoon = 0;
        $pegasus = false;
        //throw new feException(print_r($ressources));
        if ($this->getPlayersAdditionnalInfo()[$player_id]['forge'] != '')
            throw new BgaVisibleSystemException("You cannot buy an exploit as you have started to forge");

        $previousExploitId = $this->getGameStateValue( "exploitBought");
        if ($previousExploitId != -1) {
            $previousExploit = $this->exploit_types[$this->exploits->getCard($previousExploitId)['type']];
            if ($previousExploit['action'] == 'freeExploit') {
                $hasPreviousExploit = true;
                if (!in_array($card_info['position'], array ('F1', 'F2', 'M1', 'M2')))
                    throw new BgaUserException(self::_("Please select an exploit that costs 1 sunshard or 1 moonshard"));
            }
        }

        $this->checkExploitId($card_id, $card_info['position']);

        // check that card is available
        if (strstr($card['location'], 'pile') || strstr($card['location'], 'table'))
            throw new BgaVisibleSystemException("This exploit has already been bought. Select a valid exploit!");


        $this->incStat(1, 'nb_exploit_buy', $player_id);

        if (!$hasPreviousExploit) {
            $this->incStat(1, 'nb_action_exploit', $player_id);
            $costFire = $card_info['costFire'];
            $costMoon = $card_info['costMoon'];
            $costAncient = 0;

            // check that the player has enough ressources
            if (!$this->haveEnoughRessource($player_id, $card_info['costFire'], $card_info['costMoon']))
                throw new BgaUserException(self::_("You do not have enough resources to buy this card"));

            // Card has been bought with ancient shard
            if ($ressources['ancientshard'] != null && $ressources['ancientshard'] != 0) {
                $costFire = $ressources['fireshard'];
                $costMoon = $ressources['moonshard'];
                $costAncient = $ressources['ancientshard'];

                if (($costFire + $costMoon + $costAncient) != ($card_info['costFire'] + $card_info['costMoon']))
                    throw new feException("Error in allocation of shard to buy exploit");
            }
            else {
                $ressources['fireshard'] = $costFire;
                $ressources['moonshard'] = $costMoon;
                $ressources['ancientshard'] = 0;
            }

            $scepterFire = $this->getGameStateValue("scepterFireshard");
            $scepterMoon = $this->getGameStateValue("scepterMoonshard");

            if ($costFire >= $scepterFire) {
                $this->setGameStateValue("scepterFireshard", 0);
                $this->setGameStateValue("usedScepterFireshard", $scepterFire);
            }
            else {
                $this->incGameStateValue("scepterFireshard", -$costFire);
                $this->setGameStateValue("usedScepterFireshard", $costFire);
                $scepterFire = $costFire;
            }

            if ($costMoon >= $scepterMoon) {
                $this->setGameStateValue("scepterMoonshard", 0);
                $this->setGameStateValue("usedScepterMoonshard", $scepterMoon);
            }
            else {
                $this->incGameStateValue("scepterMoonshard", -$costMoon);
                $this->setGameStateValue("usedScepterMoonshard", $costMoon);
                $scepterMoon = $costMoon;
            }

            $this->decreaseFireShard ($player_id, $costFire - $scepterFire);
            $this->decreaseMoonShard ($player_id, $costMoon - $scepterMoon);
            $this->decreaseAncientShard ($player_id, $costAncient);

            $ousted_player = $this->isIslandUsed($player_id, $card_info['island']);

            // in progress

            // Bear management
            if ($this->hasBear($player_id) != 0 && isset($ousted_player) && $ousted_player != null) {
                $vp = $this->hasBear($player_id) * 3;
                $this->increaseVP($player_id, $vp);
                $this->incStat($vp, 'nb_vp_bear', $player_id);
                self::notifyAllPlayers("notifBlessing", clienttranslate('${player_name} gets ${ressources} from the Great Bear'), array(
                            'player_name'   => $this->getActivePlayerName(),
                            'ressources'    => $vp . ' [VP]') );
            }

            if ($this->hasBear($ousted_player) != 0 && isset($ousted_player) && $ousted_player != null) {
                $vp = $this->hasBear($ousted_player) * 3;
                $this->increaseVP($ousted_player, $vp);
                $this->incStat($vp, 'nb_vp_bear', $ousted_player);
                self::notifyAllPlayers("notifBlessing", clienttranslate('${player_name} gets ${ressources} from the Great Bear'), array(
                            'player_name'   => $this->loadPlayersBasicInfos()[$ousted_player]['player_name'],
                            'ressources'    => $vp . ' [VP]') );

            }

            // pegasus management
            if (isset($ousted_player) && $ousted_player != null && $this->hasPegasus($ousted_player) ) {
                $this->tokens->setTokenState('pegasus_remaining', $this->hasPegasus($ousted_player));
                $this->tokens->setTokenState('pegasus_player', $ousted_player);

                $pegasus = true;
            }

            if (isset($ousted_player) && $ousted_player != null) {
                // move ousted player
                $this->dbSetPosition ($ousted_player, 'begin');
                $this->setGameStateValue('oustedPlayerId', $ousted_player);
                self::notifyAllPlayers("notifOusting", clienttranslate('${player_name} has ousted ${ousted_player_name} who gains a divine blessing'),
                    array(
                        'player_name'        => self::getActivePlayerName(),
                        'ousted_player'      => $this->loadPlayersBasicInfos()[$ousted_player]['player_color'],
                        'ousted_player_name' => $this->loadPlayersBasicInfos()[$ousted_player]['player_name']
                    )
                );
                $this->incStat(1, 'nb_has_ousted', $player_id);
                $this->incStat(1, 'nb_been_ousted', $ousted_player);
            }

            // move current player pawn
            $this->dbSetPosition ($player_id, $card_info['island']);

            self::notifyAllPlayers("notifMovePawn", '',
                array(
                    'player_color' => $this->loadPlayersBasicInfos()[$player_id]['player_color'],
                    'island'       => $card_info['island']
                )
            );
        }

        if ($card['type'] == 'hammer' || $card['type'] == 'chest')
            $pile = 'table';
        elseif ($card_info['actionType'] == 'immediate')
            $pile = 'pile1';
        elseif ($card_info['actionType'] == 'recurrent')
            $pile = 'pile3';
        else
            $pile = 'pile2';

        // move the card
        $pile = $pile . "-" . $player_id;
        $this->exploits->insertCardOnExtremePosition($card_id, $pile, true);

        if (($scepterFire + $scepterMoon) > 0)
            self::notifyAllPlayers("notifMessage", clienttranslate('${player_name} uses its Blacksmith\'s scepter'),
                array('player_name' => self::getActivePlayerName())
            );

        if (!$hasPreviousExploit)
            $msg = clienttranslate('${player_name} has bought ${card_name_trans} with ${ressources}');
        else
            $msg = clienttranslate('${player_name} has bought ${card_name_trans} for free');

        self::notifyAllPlayers("notifExploitBuy", $msg,
            array(
                'i18n'           => array ('card_name_trans'),
                'player_name'    => self::getActivePlayerName(),
                'card_name'      => $card_info['name'],
                'card_name_trans'=> $card_info['name'],
                'card_id'        => $card_id,
                'card_pos'       => $card_info['position'],
                'card_type'      => $card['type'],
                'player_id'      => $player_id,
                'pile'           => "pile-" . $player_id,
                'ressources'     => $this->buildRessourceNotif($ressources),
            )
        );

        if ($card_info["actionType"] == 'recurrent')
            self::notifyAllPlayers("notifAddReinforcement", clienttranslate('${player_name} gains the reinforcement of ${card_name_trans}'),
                array(
                    'i18n'              => array ('card_name_trans'),
                    "player_id"         => $player_id,
                    "card_id"           => $card_id,
                    'player_name'       => self::getActivePlayerName(),
                    'card_name'         => $card_info['name'],
                    'card_name_trans'   => $card_info['name'],
                    "power"             => $card['type']
                )
            );

        // notify
        self::notifyAllPlayers("updateCounters", "", $this->getPlayersRessources());
        self::notifyScepters();

        $this->getTitanReward ($player_id, $card['type']);

        $this->setGameStateValue( "exploitBought", $card_id );
        $this->setGameStateValue( "exploitRemainingThrows", $card_info['nbStep'] );

        if ($pegasus) {
            $this->gamestate->nextState('pegasusOusting');
        }
        elseif (isset($ousted_player) && $ousted_player != null)
            $this->gamestate->nextState('playerOusting');
        else
            $this->gamestate->nextState('exploitEffect');

    }

    // Triggered when new sides are bought
    // $side = array of side_id
    function actBuyForge($toForge, $toReplace, $mode = 'classic')
    {
        if ($mode == 'classic')
            self::checkAction ("actBuyForge");

        $player_id    = self::getCurrentPlayerId();
        $sides_bought = array();
        $sides_id     = array();
        $player_info = $this->getPlayersAdditionnalInfo()[$player_id];
        $gold         = 0;
        $ship         = false;
        $exploit      = false;
        $type_arg     = 0;
        $celestialExploit = false;

        $this->resolveTwin($player_id, true);

        //throw new feException("$toForge, $toReplace, $mode ");

        // Check if user active. No translation as only a cheater will see that
        if (!in_array($player_id, $this->gamestate->getActivePlayerList()))
            throw new BgaVisibleSystemException ("You are not active to forge a side");

        $forgedSides = $player_info['forge'];
        if ($forgedSides != "")
            $sides_bought = explode(',', $forgedSides);

        $side_card = $this->sides->getCard($toForge);
        $side_type = $side_card["type"];

        // User cannot by twice the same side on the same action
        if (in_array($side_type, $sides_bought))
            throw new BgaUserException(self::_("You cannot buy twice the same side!"));

        // check if side is available
        if (strstr($side_card['location'], 'di') || $side_card['location'] == 'forging')
            throw new BgaVisibleSystemException("This side is not available to be forged.");

        $sides_bought[] = $side_type;

        $cost = $this->dice_sides[$side_type]['cost'];
        // reduce cost if ship / ship & triple
        if ($player_info['side_choice_1'] == 'ship' || $player_info['side_choice_2'] == 'ship' || $this->getGameStateValue("mazeForge") == 1 || $this->tokens->getTokenLocation("celestial_choice") == 'ship') {
            $ship = true;
            if ($this->getGameStateValue("forgeClassical") == 0) {
                $cost = $cost - 2;
                // deprecated: triple token
                //if ($player_info['side_choice_1'] == 'triple' || $player_info['side_choice_2'] == 'triple')
                if ($this->tokens->getTokenState("triple_$player_id") == 1 && $this->tokens->getTokenLocation("celestial_choice") != 'ship')
                    $cost = $cost - 4;
                if ($cost < 0)
                    $cost = 0;
            }
        }

        if ($mode == 'upgrade' || $mode == 'celestialUpgrade')
            $cost = 0;

        // get old side
        $old_side = $this->sides->getCard($toReplace);

        if ($old_side['location'] != 'dice1-p' . $player_id && $old_side['location'] != 'dice2-p' . $player_id)
            throw new BgaVisibleSystemException("This side is not on your die");

        $die_number = substr($old_side['location'], 4, 1);

        $exploitBought = $this->getGameStateValue('exploitBought');

        if ($exploitBought != -1 && !$ship) {
            $exploit = true;
            $card = $this->exploits->getCard($exploitBought);

            if ((substr($card['type'], -4) == 'Boar') || (substr($card['type'], -10) == 'Misfortune'))
                $type_arg = $this->getGameStateValue('turnPlayerId');

            switch ($card['type']) {
                case 'ancestor':
                    $cost = 0;
                    if ($this->getNextVPPool() != $side_card['location'])
                        throw new feException("You must forge a side from the correct pool");
                    if (!isset($this->dice_sides[$side_type]['ressource']['vp']))
                        throw new BgaUserException(self::_("You must select a die face with the Victory Point symbol"));
                    $this->setGameStateValue( "enigmaDieNumber", $die_number);
                    break ;
                case 'trident':
                    $cost = 0;
                    // check that side can be bought (not linked to an existing card)
                    if (in_array($side_type, $this->listSidesWithExploits()))
                        throw new BgaUserException(self::_("This die face cannot be bought as it can be forged through an exploit"));
                    // force no owner of the side
                    $type_arg = 0;
                    break ;
                case 'celestial':
                    $celestialExploit = true;
                    break ;

                // @todo: add a control on exploit forge so a player cannot take another die face than expected!
            }
        }

        if (!$exploit && $side_card['location'] > 10)
            throw new BgaVisibleSystemException("This side is not available to be forged.");

        //if ($player_info['res_gold'] < $cost && $this->hasTritonToken($player_id))
        if ($this->getGold($player_id) < $cost && $this->hasTritonToken($player_id))
            throw new BgaUserException(self::_("You do not have enough gold. You need to use your Triton token for more gold."));
        //elseif ($player_info['res_gold'] < $cost)
        elseif ($this->getGold($player_id) < $cost)
            throw new BgaUserException(self::_("You do not have enough gold!"));

        // reduce ressource
        if ($cost != 0)
            $aff = $this->decreaseGold($player_id, $cost);
        else
            $aff = "";

        // if side previously forged as boar
        if ($old_side['type_arg'] != 0)
            throw new BgaUserException (self::_("You cannot remove this side from your die"));

        // Discard old side
        $this->sides->moveCard($toReplace, 'discard', $player_id);
        // Forge the new one
        $this->sides->insertCardOnExtremePosition($toForge, 'dice'. $die_number .'-p'. $player_id, false);
        // if boar, must put user_id on the type_arg
        if ($type_arg != 0) {
            $sql = 'UPDATE sides SET card_type_arg = ' . $type_arg . ' WHERE card_id = ' . $toForge;
            self::DbQuery($sql);
        }

        // cleanup of card_location_arg
        $sql = "set @i=0;";
        self::DbQuery($sql);
        $sql = "set @Count=(SELECT COUNT(*) from sides where card_location = 'dice" . $die_number .'-p'. $player_id ."');";
        self::DbQuery($sql);
        $sql = "UPDATE sides SET card_location_arg = @Count-(@i:=@i+1) where card_location = 'dice" . $die_number .'-p'. $player_id ."' ORDER BY card_location_arg DESC;";
        self::DbQuery($sql);

        $desc = clienttranslate('${player_name} has forged ${side_type} for ${ressources} on dice ${dice_number}, ${old_side_type} is discarded');
        if ($cost == 0)
            $desc = clienttranslate('${player_name} has forged ${side_type} on dice ${dice_number}, ${old_side_type} is discarded');

        // notify the players that the side has been forged
        self::notifyAllPlayers("notifSideForged", $desc,
            array(
                'player_id'      => $player_id,
                'player_name'    => $player_info['player_name'], //self::getActivePlayerName(),
                'side_type'      => $side_type,
                'side_type_name' => $side_type,
                'side'           => $toForge,
                'dice_number'    => $die_number,
                'old_side_type'  => $old_side['type'],
                'old_side'       => $toReplace,
                'ressources'     => $this->buildRessourceNotif($aff)
            )
        );

        self::notifyAllPlayers("updateCounters", "", $this->getPlayersRessources());

        $forgedSides = implode (',', $sides_bought);

        if ($mode == 'classic') {
            $this->dbSetForge($player_id, $forgedSides);
            $this->incStat(1, 'nb_action_forge', $player_id);
        }

        $this->incStat(1, 'nb_side_forge', $player_id);

        if ($ship) {
            if ($exploitBought != -1)
                $exploit = true;

            $this->dbSetForge($player_id, '');
            if ($player_info['side_choice_1'] == 'ship')
                $this->dbSetSideChoice($player_id, 1, "0");
            elseif ($player_info['side_choice_2'] == 'ship')
                $this->dbSetSideChoice($player_id, 2, "0");
            elseif ($this->tokens->getTokenLocation("celestial_choice") == 'ship') {
                $this->tokens->moveToken("celestial_choice", 0);
            }

            // deprecated: token triple
            //if ($player_info['side_choice_1'] == 'triple')
            //    $this->dbSetSideChoice($player_id, 1, "0");
            //if ($player_info['side_choice_2'] == 'triple')
            //    $this->dbSetSideChoice($player_id, 2, "0");

            $this->tokens->setTokenState("triple_$player_id", 0);
            $this->setGameStateValue("forgeClassical", 0);
            $this->setGameStateValue("mazeForge", 0);

            $player_info = $this->getPlayersAdditionnalInfo()[$player_id];

            $ressourceChoice = $this->setChoice($player_id, null);

            if ($ressourceChoice != self::RC_FORGESHIP) {
                // Go to correct State
                // depends on the state name
                $next_state = $this->getNextState($ressourceChoice);

                // samething if ousting => triggers again mazemanagement, etc.
                if ($this->gamestate->state()['name'] == 'oustedForgeShip' && $this->tokens->getTokenState("mazestock_$player_id") != 0) {
                    if ($this->getGameStateValue('celestialRunning')) {
                        $this->setGameStateValue('celestialRunning', 0);
                    }
                    $this->oustingMazeManagement($player_id);
                }
                else {
                    $this->setGameStateValue("celestialRunning", 0);
                    $this->triggerCelestialWhenMaze0($player_id);
                    $this->gamestate->setPlayerNonMultiactive($player_id, $next_state);
                }

            }
            else
                $this->gamestate->nextState('forgeShip');

        }
        elseif (($exploit && $mode != 'celestialUpgrade') || $celestialExploit) {
            $this->dbSetForge($player_id, '');
            if ($this->getGameStateValue('celestialRunning')) {
                $this->setGameStateValue('celestialRunning', 0);
                $this->incGameStateValue('exploitRemainingThrows', -1);
                $this->dbSetChoice($player_id, self::RC_NOTHING_TODO);
            }
            //throw new feException("truc dd" . $this->getNextState());
            $this->gamestate->nextState($this->getNextState());
        }
        elseif($mode == 'celestialUpgrade') {
            $this->dbSetForge($player_id, '');
            $this->setChoice($player_id, null);

            //// if we have maze and we are on double celestial roll
            //if ($this->getGameStateValue( "rebellion") == 4) {
            //    $mazePosition = $this->tokens->getTokenState("position_" . $player_id);
            //    if (!($this->getGameStateValue("doubleCelestialRoll") == 0 && $this->maze[$mazePosition]['reward'] == 'celestialRollx2'))
            //        $this->setGameStateValue( "celestialRunning", 0);
            if ($this->getGameStateValue('doubleCelestialRoll') == 0)
                    $this->setGameStateValue( "celestialRunning", 0);
            //}
            //else
            //    $this->setGameStateValue( "celestialRunning", 0);

            $this->gamestate->setPlayerNonMultiactive($player_id, $this->getNextState());
            //throw new feException($this->tokens->getTokenState("mazestock_" . $player_id));
        }
        elseif ($mode == "upgrade")
            $this->gamestate->nextState("reinforcement");
        // Check if another side is available to be forged
        //elseif ($this->canForgeSides($player_id, $player_info['res_gold'] - $cost, $sides_bought)) {
        elseif ($this->canForgeSides($player_id, $this->getGold($player_id), $sides_bought)) {
            $this->gamestate->nextState("playerAction");
        }
        // If the player does not have enough ressource for another action => end of turn
        elseif ($this->canTakeSecondAction($player_id)) {
            $this->dbSetForge($player_id, '');
            $this->gamestate->nextState("playerSecondAction");
        }
        else {
            $this->dbSetForge($player_id, '');
            $this->gamestate->nextState('endPlayerTurn');
        }

    }

    function actAncestorSelect($die_number) {
        self::checkAction ("actAncestorSelect");
        $player_id = self::getCurrentPlayerId();
        $this->setGameStateValue( "enigmaDieNumber", $die_number);



        $this->gamestate->nextState($this->getNextState());
    }

    // cancel the upgrade coming from the celestial die
    function actCancelCelestial()
    {
        $player_id = self::getCurrentPlayerId();
        $this->setChoice($player_id, null);
        if ($this->getGameStateValue("doubleCelestialRoll") == 0)
            $this->setGameStateValue( "celestialRunning", 0);

        //notify
        self::notifyAllPlayers("doNothing", '${player_name} does not upgrade a side',
            array(
                'player_name' => $this->loadPlayersBasicInfos()[$player_id]['player_name']
            )
        );

        $card_id = $this->getGameStateValue( "exploitBought");
        if ($card_id != -1) {
            $card = $this->exploits->getCard($card_id);
            $card_info = $this->exploit_types[$card['type']];

            if ($card_info['action'] == 'throwCelestialDie' && $this->getGameStateValue('exploitRemainingThrows') > 0)
                $this->incGameStateValue('exploitRemainingThrows', -1);
        }
        //if ($this->getGameStateValue('exploitRemainingThrows') > 0)
        //    $this->incGameStateValue('exploitRemainingThrows', -1);
        $this->setChoice($player_id, null);


        $this->gamestate->setPlayerNonMultiactive($player_id, $this->getNextState());
    }

    function actEndForge()
    {
        $player_id = self::getCurrentPlayerId();

        if ($this->canTakeSecondAction($player_id)) {
            $this->dbSetForge($player_id, '');
            $this->gamestate->nextState("playerSecondAction");
        }
        else {
            $this->dbSetForge($player_id, '');
            $this->gamestate->nextState('endPlayerTurn');
        }
    }

    function actChooseMazePath($new_position)
    {
       $player_id = self::getCurrentPlayerId();
       self::checkAction( "actChooseMazePath");

        if ($this->tokens->getTokenLocation("mazechoice_" . $player_id) != "0")
            throw new BgaVisibleSystemException("You cannot move in the maze, must choose the ressource");

       $this->setGameStateValue("mazeForcePath", 0);

       $canContinue = $this->mazeManagement($player_id, $new_position);

       //TODO: Stat
       //$this->incStat(1, 'nb_ressource_choice', $player_id);
       //throw new feException("can continue $canContinue");
        if ($canContinue)
            $this->gamestate->setPlayerNonMultiactive($player_id, 'nextState');
        else
            $this->gamestate->nextState('choice');
    }

    function actChooseTreasure($treasure)
    {
        $player_id = self::getCurrentPlayerId();
        self::checkAction( "actChooseTreasure");
        $notifPlayerArgs = $this->initNotif($player_id);

        if ($this->tokens->getTokenLocation("treasure_" . $treasure) != "none")
            throw new BgaVisibleSystemException("This treasure has already been selected");

        $position = $this->tokens->getTokenState("position_" . $player_id);

        // move token
        $this->tokens->moveToken("treasure_" . $treasure, $position);

        switch ($treasure) {
            case 'fireshard':
                $disp = '[FS]';
                break ;
            case 'moonshard':
                $disp = '[MS]';
                break ;
            case 'vp':
                $disp = '[VP]';
                break ;
        }

        self::notifyAllPlayers("notifMazeTreasure", clienttranslate('${player_name} selects the ${ressources} treasure'),
            array(
                'player_id'      => $player_id,
                'player_name'    => $notifPlayerArgs['player_name'],
                'treasure'       => $treasure,
                'ressources'     => $disp,
                'position'       => $position
            )
        );

        // get ressource
        switch($treasure) {
            case "fireshard":
                $gain = "FS4";
                break;
            case "moonshard":
                $gain = "MS4";
                break;
            case "vp":
                $gain = "V10";
                break;
        }
        $notifPlayerArgs = $this->affectRessource($player_id, $notifPlayerArgs, 99, $gain, false, 1, false, false, false);

        if ( $notifPlayerArgs['vp'] == 0 )
        {
            unset( $notifPlayerArgs['vp'] );
        }
        $notifPlayerArgs['ressources'] = $this->buildRessourceNotif( $notifPlayerArgs );
        self::notifyAllPlayers("notifBlessing", clienttranslate('${player_name} gets ${ressources}'), $notifPlayerArgs );
        self::notifyAllPlayers("updateCounters", "", $this->getPlayersRessources());

        $this->triggerCelestialWhenMaze0($player_id);

        $this->gamestate->setPlayerNonMultiactive($player_id, 'nextState');
        return ;
    }

    function actPuzzleCelestial($auto = false) {
        $player_id = self::getCurrentPlayerId();
        $player_name = $this->loadPlayersBasicInfos()[$player_id]['player_name'];

        $this->resetTwins($player_id, false);

        if ($this->gamestate->state()['type'] != 'game')
            self::checkAction( "actPuzzleCelestial");

        $notifPlayerArgs = $this->initNotif($player_id);

        self::notifyAllPlayers('notifMessage', clienttranslate('${player_name} triggers the Puzzle effect'),  array(
                            'player_name' => $player_name));

        $roll = $this->rollCelestial($player_id);
        $this->tokens->setTokenState("puzzle_" . $player_id, -1);

        if ($roll != null) {
            $ret = $this->setChoice($player_id, null);
        }

        if ($roll == 'doubleUpgrade' && !$this->canUseTwins($player_id)) {
            $this->dbSetChoice($player_id, self::RC_FORGESHIP);
        }

        if (!$auto)
            $this->gamestate->setPlayerNonMultiactive($player_id, 'nextState');
    }

    function actPuzzleMaze() {
        $player_id = self::getCurrentPlayerId();
        self::checkAction( "actPuzzleMaze");
        $notifPlayerArgs = $this->initNotif($player_id);

        $continue = $this->mazeManagement($player_id, null, true);
        //if ($continue) {
        //    $this->setChoice($player_id, null);
        //}
        //throw new feException("truc");
        $this->gamestate->setPlayerNonMultiactive($player_id, 'nextState');

        return ;
    }

    function actMazePowerConfirm($willDo) {
        $player_id = self::getCurrentPlayerId();
        self::checkAction( "actMazePowerConfirm");
        $notifPlayerArgs = $this->initNotif($player_id);

        $position = $this->tokens->getTokenState("position_" . $player_id);
        $mazeSquare = $this->maze[$position];

        $this->setGameStateValue("mazeForcePath", 0);

        // refuse conversion
        if (!$willDo) {
            //$this->dbSetChoice($player_id, self::RC_NOTHING_TODO);
            $this->setChoice($player_id, null);
            $this->gamestate->setPlayerNonMultiactive($player_id, 'nextState');
        }
        else {
            switch ($mazeSquare['reward']) {
                case 'convert6Gto6VP':
                    if ($this->getGold($player_id) < 6)
                        throw new BgaVisibleSystemException("You do not have enough gold");
                    $this->decreaseGold($player_id, 6);
                    $this->increaseVP($player_id, 6);

                    self::notifyAllPlayers("notifEffect", clienttranslate('${player_name} converts ${gold} in ${vp}'),
                        array(
                            'player_name' => $notifPlayerArgs['player_name'],
                            'gold'          => "6 [G]",
                            'vp'            => "6 [VP]"
                        )
                    );

                    break ;
                case 'convertMS2to8VP':
                    // disabling ot scepter at this step
                    //$scepterMoon = $this->getGameStateValue("scepterMoonshard");
                    $scepterMoon = 0;
                    $costMoon = 2;


                    if (($scepterMoon + $this->getPlayersAdditionnalInfo()[$player_id]['res_moon']) < 2) {
                        if ($this->hasTritonToken($player_id))
                            throw new BgaVisibleSystemException(clienttranslate("You need to convert your Triton token"));
                        else
                            throw new BgaVisibleSystemException("You do not have enough moonshard");
                    }

                    if ($costMoon >= $scepterMoon) {
                        $this->setGameStateValue("scepterMoonshard", 0);
                    }
                    else {
                        $this->incGameStateValue("scepterMoonshard", -$costMoon);
                        $scepterMoon = 2;
                    }

                    $this->decreaseMoonShard ($player_id, 2 - $scepterMoon);
                    $this->increaseVP($player_id, 8);

                    self::notifyAllPlayers("notifEffect", clienttranslate('${player_name} converts ${moonshard} in ${vp}'),
                        array(
                            'player_name' => $notifPlayerArgs['player_name'],
                            'moonshard'          => "2 [MS]",
                            'vp'            => "8 [VP]"
                        )
                    );
                    break ;
            }

            $this->setChoice($player_id, null);
            $this->triggerCelestialWhenMaze0($player_id);
            self::notifyAllPlayers("updateCounters", "", $this->getPlayersRessources());
            $this->gamestate->setPlayerNonMultiactive($player_id, 'nextState');
        }
    }

    // $sides structures
    // Array : new_side_id => [old_side, dice_number]
    //function actForgeDice ($sides){
    //    self::checkAction( "actForgeDice" );
    //    $player_id = self::getActivePlayerId();
    //
    //    $sides_to_forge = $this->sides->getCardsInLocation('forging');
    //
    //    foreach ($sides as $new_side_id => $new_side) {
    //        if (!array_key_exists($new_side_id, $sides_to_forge))
    //            throw new BgaUserException(self::_("You did not buy this side!"));
    //
    //        $old_sides = $this->sides->getCardsInLocation('dice'. $new_side['dice_number'] .'-p'. $player_id);
    //        if (!array_key_exists($new_side['old_side'], $old_sides))
    //            throw new BgaUserException(self::_("This side is not on your die!"));
    //
    //        // Discard old side
    //        $this->sides->moveCard($new_side['old_side'], 'discard', $player_id);
    //        // Forge the new one
    //        $this->sides->insertCardOnExtremePosition($new_side_id, 'dice'. $new_side['dice_number'] .'-p'. $player_id, false);
    //
    //        // cleanup of card_location_arg
    //        $sql = "set @i=0;";
    //        self::DbQuery($sql);
    //        $sql = "set @Count=(SELECT COUNT(*) from sides where card_location = 'dice" . $new_side['dice_number'] .'-p'. $player_id ."');";
    //        self::DbQuery($sql);
    //        $sql = "UPDATE sides SET card_location_arg = @Count-(@i:=@i+1) where card_location = 'dice" . $new_side['dice_number'] .'-p'. $player_id ."' ORDER BY card_location_arg DESC;";
    //        self::DbQuery($sql);
    //
    //        // notify the players that the side has been forged
    //        self::notifyAllPlayers("notifSideForged", clienttranslate('${player_name} has forged ${side_type} on their dice ${dice_number}, ${old_side_type} is discarded'),
    //            array(
    //                'player_name'   => self::getActivePlayerName(),
    //                'side_type'     => $sides_to_forge[$new_side_id]['type'],
    //                'side'          => $sides_to_forge[$new_side_id]['id'],
    //                'dice_number'   => $new_side['dice_number'],
    //                'old_side_type' => $old_sides[$new_side['old_side']]['type'],
    //                'old_side'      => $old_sides[$new_side['old_side']]['id'],
    //            )
    //        );
    //    }
    //
    //    // If the player does not have enough ressource for a next turn => end of turn
    //    if ($this->canTakeSecondAction($player_id))
    //        $this->gamestate->nextState("playerSecondAction");
    //    else
    //        $this->gamestate->nextState('endPlayerTurn');
    //}

    // $ressources : array of the choosen ressources
    // if side with gold and FS (for example), the FS will not be sent by the JS
    function actOustedRessources($sideNum, $side, $ressources)
    {
        self::checkAction( "actOustedRessources" );

        $disable = false;
        $player_id = self::getCurrentPlayerId();
        //throw new feException($sideNum . " " . $side);
        $disable = $this->takeRessource2($player_id, 'blessing', $sideNum, $side, $ressources);

        $this->incStat(1, 'nb_ressource_choice', $player_id);

        if ($disable) {
            $card_id = $this->getGameStateValue( "exploitBought");

            if ($card_id != -1) {
                $card = $this->exploits->getCard($card_id);
                if ($card['type'] == 'leftHand') {
                     $this->gamestate->nextState('pegasus');
                    return ;
                }
            }
            $this->gamestate->setPlayerNonMultiactive($player_id, 'nextState');
        }
        else
            $this->gamestate->nextState('choice');
    }

    //----------------------------------------------- Exploit action functions begin
    function actExploitEnigma($die_number)
    {
        self::checkAction("actExploitEnigma");

        $enigma = $this->getGameStateValue( "enigmaDieNumber" );

        if ($enigma == -1) {
            $this->setGameStateValue( "enigmaDieNumber", $die_number );
            $this->setGameStateValue( "exploitRemainingThrows", 4 );
        }

        $this->gamestate->nextState("exploitEffect");
    }

    function actExploitBoar ($forgePlayerId) {
        self::checkAction("actExploitBoar");

        $player_id = self::getCurrentPlayerId();

        $card_id = $this->getGameStateValue( "exploitBought");
        $card = $this->exploits->getCard($card_id);

        // notify the choice
        self::notifyAllPlayers("notifBoarChoice", clienttranslate('${player_name} has choosen ${ousted_player_name} to forge ${side_type}'),
                array(
                    'player_name'   => self::getActivePlayerName(),
                    'ousted_player'      => $this->loadPlayersBasicInfos()[$forgePlayerId]['player_color'],
                    'ousted_player_name' => $this->loadPlayersBasicInfos()[$forgePlayerId]['player_name'],
                    'side_type'         =>  $card['type']));

        // activation of correct player that needs to forge the side
        $this->gamestate->setPlayersMultiactive(array($forgePlayerId), '');
        self::giveExtraTime( $forgePlayerId, 60 );

        // disable active user
        $this->gamestate->setPlayerNonMultiactive( $player_id, '' );
        // go to next state
        $this->gamestate->nextState("forgeBoar");
    }

    function actCelestialUpgrade($old_side_id, $new_side_id) {
        self::checkAction("actCelestialUpgrade");

        $player_id = self::getCurrentPlayerId();

        if ($this->getGameStateValue( "celestialRunning") != 1)
            throw new feException("Celestial die has not been rolled");

        $celestial_side = $this->getCelestial();
        if ($celestial_side != 'doubleUpgrade')
            throw new feException("This celestial die face is not up");


        $this->checkUpgrade(2, $old_side_id, $new_side_id);

        $this->actBuyForge($new_side_id, $old_side_id, 'celestialUpgrade');

        //$this->setGameStateValue( "celestialRunning", 0);
    }

    // $side contains old_side, die_number
    //function actExploitForging($sides)
    //{
    //    self::checkAction( "actExploitForging" );
    //    $player_id = self::getActivePlayerId();
    //    $side_type = "";
    //
    //    $card_id = $this->getGameStateValue( "exploitBought");
    //    $card = $this->exploits->getCard($card_id);
    //
    //    $this->incStat(count($sides), 'nb_side_forge', $player_id);
    //
    //    switch ($card['type']) {
    //        case "mirror":
    //            $side_type = "mirror";
    //            break;
    //        case "invisible":
    //            $side_type = "triple";
    //            break;
    //    }
    //
    //    if (count($sides) != 1)
    //        throw new BgaUserException(self::_("Invalid number of sides"));
    //
    //    foreach ($sides as $new_side_id => $new_side) {
    //        $new_side_db = $this->sides->getCard($new_side_id);
    //
    //        if ($new_side_db['type'] != $side_type)
    //            throw new BgaUserException(self::_("This side is not linked to the exploit bought"));
    //
    //        if (strstr($new_side_db['location'], 'di'))
    //            throw new BgaUserException(self::_("This side is not available to be forged!"));
    //
    //        $old_sides = $this->sides->getCardsInLocation('dice'. $new_side['dice_number'] .'-p'. $player_id);
    //        if (!array_key_exists($new_side['old_side'], $old_sides))
    //            throw new BgaUserException(self::_("This side is not on your die!"));
    //
    //        // Discard old side
    //        $this->sides->moveCard($new_side['old_side'], 'discard', $player_id);
    //        // Forge the new one
    //        $this->sides->insertCardOnExtremePosition($new_side_id, 'dice'. $new_side['dice_number'] .'-p'. $player_id, false);
    //
    //        // cleanup of card_location_arg
    //        $sql = "set @i=0;";
    //        self::DbQuery($sql);
    //        $sql = "set @Count=(SELECT COUNT(*) from sides where card_location = 'dice" . $new_side['dice_number'] .'-p'. $player_id ."');";
    //        self::DbQuery($sql);
    //        $sql = "UPDATE sides SET card_location_arg = @Count-(@i:=@i+1) where card_location = 'dice" . $new_side['dice_number'] .'-p'. $player_id ."' ORDER BY card_location_arg DESC;";
    //        self::DbQuery($sql);
    //
    //        // notify the players that the side has been forged
    //        self::notifyAllPlayers("notifSideForged", clienttranslate('${player_name} has forged ${side_type} on their dice ${dice_number}, ${old_side_type} is discarded'),
    //            array(
    //                'player_name'   => self::getActivePlayerName(),
    //                'side_type'     => $new_side_db['type'],
    //                'side'          => $new_side_id,
    //                'dice_number'   => $new_side['dice_number'],
    //                'old_side_type' => $old_sides[$new_side['old_side']]['type'],
    //                'old_side'      => $old_sides[$new_side['old_side']]['id'],
    //            )
    //        );
    //    }
    //
    //    // cleaning of the card bought
    //    $this->setGameStateValue( "exploitBought", -1 );
    //
    //    if ($this->canTakeSecondAction($player_id))
    //        $this->gamestate->nextState("playerSecondAction");
    //    else
    //        $this->gamestate->nextState('endPlayerTurn');
    //}

    // ---------------------------------------------- Exploit action functions end


    // Choice of the ressource
    // $ressources : array of the choosen ressources for each side (key)
    function actExploitRessource($sideNum, $side, $ressources)
    {
        self::checkAction( "actExploitRessource" );

        $player_id = self::getCurrentPlayerId();

        $card_id   = $this->getGameStateValue( "exploitBought");
        $card      = $this->exploits->getCard($card_id);
        $card_info = $this->exploit_types[$card['type']];
        $firstFinish = $this->getGameStateValue('firstFinish');

        $disable = false;

        // boar
        if ($sideNum == 3)
             $disable = $this->takeRessource2($player_id, 'blessing', $sideNum, $side, $ressources);
        else if ($this->getGameStateValue('celestialRunning')) {
            $mode = 'dice1';
            if ($card_info['action'] == 'fullThrow2Transform' || $card_info['action'] == '4ThrowsTransform')
                $mode = 'transform';
            $disable = $this->takeRessource2($player_id, $mode, $sideNum, $side, $ressources);
        }
        else if ($firstFinish == $player_id) {
            $mode = 'dice1';
            if ($card_info['action'] == 'fullThrow2Transform' || $card_info['action'] == '4ThrowsTransform')
                $mode = 'transform';
            $disable = $this->takeRessource2($player_id, $mode, $sideNum, $side, $ressources);
        }
        else {
            switch ($card_info['action']) {
                case 'steal2':
                    $disable = $this->takeRessource2($player_id, 'steal2', $sideNum, $side, $ressources);
                    break;
                case '3G3M':
                case 'forge4G':
                case 'forgeVP':
                case 'chooseSides':
                case 'greatGolem':
                case 'timeGolem':
                case 'oustAll':
                    // mode die1
                    $disable = $this->takeRessource2($player_id, 'dice1', $sideNum, $side, $ressources);
                    // Even if player has a cerberus token, should not be asked to rethrow
                    $this->dbSetChoice($player_id, self::RC_NOTHING_TODO);
                    $disable = true;
                    break;
                //case 'oustAll':
                //    // mode die1
                //    $disable = $this->takeRessource2($player_id, 'blessing', $sideNum, $side, $ressources);
                //    // Even if player has a cerberus token, should not be asked to rethrow
                //    $this->dbSetChoice($player_id, self::RC_NOTHING_TODO);
                //    $disable = true;
                //    break;
                case 'throwCelestialDie':
                    $disable = $this->takeRessource2($player_id, 'dice1', $sideNum, $side, $ressources);
                    break ;
                case 'looseThrow':
                    // all ressources are affected
                    $disable = $this->takeRessource2($player_id, 'loose', $sideNum, $side, $ressources);
                    break;
                case '4Throws':
                    $die_number =  $this->getGameStateValue( "enigmaDieNumber");
                    if ($sideNum > 2)
                        $die_number = $sideNum;
                    $disable = $this->takeRessource2($player_id, 'blessing', $die_number, $side, $ressources);
                    break;
                case 'fullThrow2':
                    $disable = $this->takeRessource2($player_id, 'blessing', $sideNum, $side, $ressources);
                    break;
                case 'fullThrow2Transform':
                    $disable = $this->takeRessource2($player_id, 'transform', $sideNum, $side, $ressources);
                    break;
                case '4ThrowsTransform':
                    $die_number =  $this->getGameStateValue( "enigmaDieNumber");
                    if ($sideNum > 2)
                        $die_number = $sideNum;
                    $disable = $this->takeRessource2($player_id, 'transform', $die_number, $side, $ressources);
                    break;
                case 'throwAllChooseResources':
                    $players_info = $this->getPlayersAdditionnalInfo();
                    $notifHammer = array();
                    $check['vp'] = 0;
                    $check['loyalty'] = 0;
                    $check['fireshard'] = 0;
                    $check['moonshard'] = 0;
                    $check['gold'] = 0;
                    $check['ancientshard'] = 0;
                    $notifPlayerArgs                = $this->initNotif($player_id);
                    //$notifPlayerArgs['player_id']   = $player_id;
                    //$notifPlayerArgs['player_name'] = $this->loadPlayersBasicInfos()[$player_id]['player_name'];


                    foreach ($players_info as $it_player_id => $player_info)  {
                            for ($i = 1; $i < 3; $i++) {
                                $side = $this->sides->getCard($player_info['throw_' . $i]);
                                $side_info = $this->dice_sides[$side['type']];

                                foreach ($side_info['ressource'] as $res => $value) {
                                    $check[$res] = $check[$res] + $value;
                                }
                            }
                    }

                    $scepter = 0;

                    if ($sideNum != 5) {
                        foreach ($ressources as $res => $value) {
                            if ($value == 1) {
                                switch ($res) {
                                    case 'moonshard':
                                        $this->increaseMoonShard($player_id, $value);
                                        break ;
                                    case 'vp':
                                        $this->increaseVP($player_id, $value);
                                        break ;
                                }
                                $notifPlayerArgs[$res] = $value;
                            }
                        }
                    }
                    else {
                        foreach ($ressources as $res => $value) {
                            if ($value > 0) {
                                switch ($res) {
                                    case 'fireshard':
                                        if ($check['fireshard'] != $value)
                                            throw new feException("Too many to affect");
                                        $this->increaseFireShard($player_id, $value);
                                        break ;
                                    case 'gold':
                                        if ($check['gold'] != $value + $ressources['hammer'])
                                            throw new feException("Too many to affect");
                                        $scepter = $this->increaseGold($player_id, $value, 'scepter');

                                        $this->increaseGold($player_id, $value - $scepter);
                                        break ;
                                    case 'hammer':
                                        if ($check['gold'] != $value + $ressources['gold'])
                                            throw new feException("Too many to affect");
                                        $notifHammer = $this->increaseGold($player_id, $value, 'hammer');
                                        break ;
                                    case 'moonshard':
                                        if ($check['moonshard'] != $value)
                                            throw new feException("Too many to affect");
                                        $this->increaseMoonShard($player_id, $value);
                                        break ;
                                    case 'loyalty':
                                        if ($check['loyalty'] != $value)
                                            throw new feException("Too many to affect");
                                        $this->tokens->incTokenState("movetitan_$player_id", $value);
                                        break ;
                                    case 'ancientshard':
                                        if ($check['ancientshard'] != $value)
                                            throw new feException("Too many to affect");
                                        $this->increaseAncientShard($player_id, $value);
                                        // value * -1 because ancient shard must move back
                                        $this->tokens->incTokenState("movetitan_$player_id", $value * -1);
                                        break ;
                                    case 'vp':
                                        if ($check['vp'] != $value)
                                            throw new feException("Too many to affect");
                                        $this->increaseVP($player_id, $value);
                                        break ;
                                }
                            }
                            $notifPlayerArgs[$res] = $value;
                        }
                    }

                    if (isset($notifPlayerArgs['gold'])) {
                        $notifPlayerArgs['gold'] -= $scepter;
                        $notifPlayerArgs['scepter'] = $scepter;
                    }

                    if (!($notifPlayerArgs['gold'] == 0 && $notifPlayerArgs['vp'] == 0 && $notifPlayerArgs['moonshard'] == 0 && $notifPlayerArgs['fireshard'] == 0 && $notifPlayerArgs['hammer'] == 0 &&  $notifPlayerArgs['scepter'] == 0  && $notifPlayerArgs['maze'] == 0 && $notifPlayerArgs['loyalty'] == 0 && $notifPlayerArgs['ancientshard'] == 0)) {
                        // petit hack
                        if ( $notifPlayerArgs['vp'] == 0 )
                        {
                            unset( $notifPlayerArgs['vp'] );
                        }
                        $notifPlayerArgs['ressources'] = $this->buildRessourceNotif( $notifPlayerArgs );
                        self::notifyAllPlayers("notifBlessing", '${player_name} gets ${ressources}', $notifPlayerArgs );

                    }

                    $this->generateNotifHammer($player_id, $notifHammer);

                    $this->titanMove($player_id);

                    self::notifyAllPlayers("updateCounters", "", $this->getPlayersRessources());
                    if ($sideNum == 5) {
                        $this->dbSetChoice($player_id, self::RC_NOTHING_TODO);
                        $disable = true;
                    }
                    else
                        $disable = false;
                    break ;
                case 'convertGoldToVP':
                    //$player_gold = $this->getPlayersAdditionnalInfo()[$player_id]['res_gold'];
                    $player_gold = $this->getGold($player_id);
                    $notifPlayerArgs                = [];
                    $notifPlayerArgs['player_id']   = $player_id;
                    $notifPlayerArgs['player_name'] = $this->loadPlayersBasicInfos()[$player_id]['player_name'];
                    foreach ($ressources as $res => $value) {
                        if ($value != 0) {
                            switch ($res) {
                                case 'gold':
                                    if ($value > $player_gold)
                                        throw new feException("More gold than possible");
                                    $this->decreaseGold($player_id, $value * -1);
                                    break ;
                                case 'vp':
                                    $this->increaseVP($player_id, $value);
                                    break ;
                                default:
                                    throw new feException("Resource not authorized to be reaffected");
                                    break ;
                            }
                            $notifPlayerArgs[$res] = abs($value) . " " . $this->ressourceToText[$res];
                        }
                    }
                    $this->dbSetChoice($player_id, self::RC_NOTHING_TODO);
                    $disable = true;

                    self::notifyAllPlayers("notifBlessing", clienttranslate('${player_name} converts ${gold} into ${vp}'), $notifPlayerArgs );
                    self::notifyAllPlayers("updateCounters", "", $this->getPlayersRessources());

                    break ;

            }
        }

        $this->incStat(1, 'nb_ressource_choice', $player_id);

        if ($disable) {
            $this->gamestate->setPlayerNonMultiactive($player_id, 'nextState');
        }
        else
            $this->gamestate->nextState('choice');
    }

    function actSecondAction($to_play, $resources)
    {
        self::checkAction( "actSecondAction" );

        $player_id = self::getActivePlayerId();

        if ($to_play) {
            $fireToPay = $resources['fireshard'];
            $ancientToPay = $resources['ancientshard'];

            // check enough FS
            if (($this->tokens->countTokensInLocAndKey('scepter_', $player_id) != 0 || $this->tokens->countTokensInLocAndKey('companion_', $player_id) != 0 )  && !$this->haveEnoughRessource($player_id, 2, 0))
                throw new BgaUserException (self::_("You do not have enough resources. Please look at the various cards that provide fireshard (Blacksmith's Scepter, Companion)."));
            elseif ($this->hasTritonToken($player_id) > 0 && !$this->haveEnoughRessource($player_id, 2, 0))
                throw new BgaUserException (self::_("You do not have enough resources. You need to use your Triton token."));
            elseif (!$this->haveEnoughRessource($player_id, 2, 0))
                throw new BgaUserException (self::_("You do not have enough resources to play a second action"));

             $scepterFire = $this->getGameStateValue("scepterFireshard");

            if ($fireToPay >= $scepterFire) {
                $this->setGameStateValue("scepterFireshard", 0);
                $this->setGameStateValue("usedScepterFireshard", $scepterFire);
            }
            else {
                $this->incGameStateValue("scepterFireshard", -$fireToPay);
                $this->setGameStateValue("usedScepterFireshard", $fireToPay);
                $scepterFire = 2;
            }

            $this->decreaseAncientShard($player_id, $ancientToPay);
            $this->decreaseFireShard($player_id, $fireToPay - $scepterFire);
            self::setGameStateValue( 'secondActionTaken', 1 );
            self::notifyAllPlayers("notifSecondAction", clienttranslate('${player_name} uses ${ressources} to play a second action'),
                array(
                    'player_name' => self::getActivePlayerName(),
                    'ressources'  => $this->buildRessourceNotif( array( "fireshard" => $fireToPay, "ancientshard" =>  $ancientToPay) ),
                )
            );
            self::notifyAllPlayers("updateCounters", "", $this->getPlayersRessources());
            self::notifyScepters();
            $this->incStat(1, 'nb_second_action', $player_id);

            $this->gamestate->nextState('playerAction');
        }
        else {
            self::notifyAllPlayers("notifSecondActionPass", clienttranslate('${player_name} does not play a second action'),
                array(
                    'player_name' => self::getActivePlayerName(),
                )
            );
            $this->gamestate->nextState("endPlayerTurn");
        }
    }

    function actEndPlayerTurn() {
        self::notifyAllPlayers("notifMessage", clienttranslate('${player_name} does not play an action'),
            array(
                'player_name' => self::getActivePlayerName()
            )
        );
        $this->gamestate->nextState("endPlayerTurn");
    }


    function misfortuneAllocation() {
        // We enable the user who owns misfortune
        $tok = $this->tokens->getTokenInfo('resolveMisfortune');
        $side_misfortune = $this->sides->getCardsOfType($tok['location']);
        $side_misfortune = reset ($side_misfortune);
        $player_owning = substr($side_misfortune['location'], 7, 99);
        $player_to_play = $tok['state'];
        $player_inf =$this->getPlayersAdditionnalInfo()[$player_owning];

        $notifPlayerArgs = $this->initNotif($player_to_play);

        $side1 = $this->sides->getCard($player_inf['throw_1']);
        $side2 = $this->sides->getCard($player_inf['throw_2']);

        if ($this->isMisfortune($side1['type']) != 0) {
            $this->tokens->moveToken('misfortune_1', 'otherMisfor');
        }
        else {
            if (substr($side1['type'], -4) == 'Boar'  && $this->getBoar($side1['type']) != 0) {
                    $boarId = $this->getBoar($side1['type']);
                    $this->dbIncBoar($boarId, true);
                    $this->setChoice($boarId, self::RC_RESSOURCE);
            }
            $side_def= $this->dice_sides[$side1['type']];
            if ((isset($side_def['gold']) && $side_def['gold'] != 0 ) || (isset($side_def['vp']) && $side_def['vp'] != 0 )) {
                $this->tokens->moveToken('misfortune_1', $side1['type']);
            }
            else {
                $notifPlayerArgs = $this->affectRessource($player_to_play, $notifPlayerArgs, 1, $side1['type'], false, 1, false, true, false, true);

                if ($notifPlayerArgs['choice'] == true)
                    $this->tokens->moveToken('misfortune_1', $side1['type']);
            }
        }

        if ($this->isMisfortune($side2['type']) != 0) {
            $this->tokens->moveToken('misfortune_2', 'otherMisfor');
        }
        else {
            if (substr($side2['type'], -4) == 'Boar'  && $this->getBoar($side2['type']) != 0) {
                    $boarId = $this->getBoar($side2['type']);
                    $this->dbIncBoar($boarId, true);
                    $this->setChoice($boarId, self::RC_RESSOURCE);
            }
            $side_def= $this->dice_sides[$side2['type']];
             if ((isset($side_def['gold']) && $side_def['gold'] != 0 ) || (isset($side_def['vp']) && $side_def['vp'] != 0 )) {
                $this->tokens->moveToken('misfortune_2', $side2['type']);
            }
            else {
                $notifPlayerArgs = $this->affectRessource($player_to_play, $notifPlayerArgs, 1, $side2['type'], false, 1, false, true, false, true);

                if ($notifPlayerArgs['choice'] == true)
                    $this->tokens->moveToken('misfortune_2', $side2['type']);
            }
        }

        if (!($notifPlayerArgs['gold'] == 0 && $notifPlayerArgs['vp'] == 0 && $notifPlayerArgs['moonshard'] == 0 && $notifPlayerArgs['fireshard'] == 0 && $notifPlayerArgs['hammer'] == 0 && $notifPlayerArgs['scepter'] == 0 && $notifPlayerArgs['maze'] == 0 && $notifPlayerArgs['loyalty'] == 0 && $notifPlayerArgs['ancientshard'] == 0))
        {
            // petit hack
            if ( $notifPlayerArgs['vp'] == 0 )
            {
                unset( $notifPlayerArgs['vp'] );
            }
            $notifPlayerArgs['ressources'] = $this->buildRessourceNotif( $notifPlayerArgs );
            self::notifyAllPlayers("notifBlessing", clienttranslate('${player_name} gets ${ressources} from the Mirror of Misfortune '), $notifPlayerArgs );
        }
        else
            self::notifyAllPlayers("notifBlessing", '', $notifPlayerArgs);

        $this->generateNotifLoyalty ($notifPlayerArgs);

        if (isset($notifPlayerArgs['notifHammer']) && count($notifPlayerArgs['notifHammer']) != 0) {
            $this->generateNotifHammer($player_to_play, $notifPlayerArgs['notifHammer']);
        }

        $this->titanMove($player_to_play);

        // saving transition state
        $stateName = $this->gamestate->state()['name'];
        $transition = "";
        switch ($stateName) {
            case 'divineBlessing':
            case 'ressourceChoice':
                $transition = 'blessing';
                break ;
            case 'reinforcement':
            case 'doeRessourceChoice':
                $transition = 'reinforcement';
                break ;
            case 'playerOustingChoice':
            case 'playerOusting':
                $transition = 'ousting';
                break ;
            case 'exploitRessource':
            case 'exploitEffect':
                $transition = 'exploitEffect';
                break ;
        }
        $this->tokens->moveToken("stateMisfortune", $transition);

        return $player_to_play;

    }
//////////////////////////////////////////////////////////////////////////////
//////////// Game state arguments
////////////

    /*
        Here, you can create methods defined as "game state arguments" (see "args" property in states.inc.php).
        These methods function is to return some additional information that is specific to the current
        game state.

        Information will be sent to the JS
    */

    function argsReinforcement() {
        $player_id    = self::getActivePlayerId();
        $players_info = $this->getPlayersAdditionnalInfo();

        // if ancient, check if player has enough gold
        $reinforcements = $this->getActiveReinforcements( $player_id );

        foreach ($reinforcements as $key => $reinforcement)
        {
            switch ( $reinforcement['type'] )
            {
                case "ancient":
                case "light":
                    //if ($players_info[ $player_id ]['res_gold'] < 3)
                    if ($this->getGold($player_id) < 3)
                        $reinforcements[$key]['unusable'] = true;
                    break ;
                case "companion":
                    if ($this->tokens->getTokenState('companion_' . $key) >= 5)
                        $reinforcements[$key]['unusable'] = true;
                    break ;
                case 'tree':
                    $reinforcements[$key]['choice'] = 'false';
                    if ($this->getGold($player_id) >= 8 && $players_info[$player_id]['res_gold'] < 8) {
                        $reinforcements[$key]['choice'] = 'true';
                    }
                    // if total of gold (including scepter) is less than 8 => 1 VP + 3G
                    // if total of gold (includng scepter) >= 8 and gold reserve < 8, give choice between 1VP + 3G or 2 VP
                    // if player has hammer, possibilities
                    // if gold reserve > 8, 2 VP
                    break ;
            }
        }
        return $reinforcements;
    }

    function argsPlayerAction() {
        // Say to JS if player is currently forging ? from global or ?

        //$this->exploits->getCardsOfType();
        $companions = array();
        foreach($this->exploits->getCardsOfType("companion") as $card) {
                if ($card['location'] == "pile3-" . $this->getActivePlayerId())
                $companions[$card['id']]= array("id" => $card['id'], "type" => $card['type']);
        }

        return array(
            "isForging"   => $this->getPlayersAdditionnalInfo()[$this->getActivePlayerId()]['forge'] == '' ? false : true,
            "currentTurn" => self::getGameStateValue( 'turnCount' ),
            "maxTurn"     => self::getGameStateValue( 'nbTurns' ),
            "scepters"    => self::getGameStateValue( 'scepterFireshard' ) + self::getGameStateValue( 'scepterMoonshard' ),
            "companion"   => $companions
        );
    }

    function argsSecondAction() {
        $companions = array();
        foreach($this->exploits->getCardsOfType("companion") as $card) {
                if ($card['location'] == "pile3-" . $this->getActivePlayerId())
                $companions[$card['id']]= array("id" => $card['id'], "type" => $card['type']);
        }
        // Say to JS if player is currently forging ? from global or ?
        return array(
            "scepters"    => self::getGameStateValue( 'scepterFireshard' ) + self::getGameStateValue( 'scepterMoonshard' ),
            "companion"   => $companions
        );
    }

    function argsDraft() {
        $draft = array();
        $promoCards = $this->getGameStateValue( "promoCards");

        $draft['slot'] = $this->exploit_slot[$this->getGameStateValue('slotBeingDrafted')];

        $setup_cards = $this->getExploitsMaterial(1);
        //if ($promoCards == 0)
        //    $setupTmp = $setup_cards['random'];
        //else
        //    $setupTmp = array_merge_recursive($setup_cards['random'], $setup_cards['promo']);
        $available_cards = $this->getAvailableExploits($setup_cards);
        $draft['exploits'] = $available_cards[$draft['slot']];
        return $draft;
    }

    function argsMisfortune() {
        $msgLoyalty = clienttranslate("(including Loyalty reward)");
        $actionChoice = false;

        // if there are no active players just exit
        if ( count( $this->gamestate->getActivePlayerList() ) == 0 )
        {
            return [];
        }

        $players_info = $this->getPlayersAdditionnalInfo();
        $sides = [];
        $sides['loyalty'] = "";

        $side1 = $this->tokens->getTokenLocation('misfortune_1');
        $side2 = $this->tokens->getTokenLocation('misfortune_2');
        $side1_state = $this->tokens->getTokenState('misfortune_1');
        $side2_state = $this->tokens->getTokenState('misfortune_2');

        if ($side1 != 'none' && $side2 != 'none')
            $actionChoice = true;

        foreach ($this->gamestate->getActivePlayerList() as $player_id)
        {
            if ($actionChoice && ($side1_state + $side2_state) == 0) {
                // action choice
                $sides[$player_id]['action'] = 'actionChoice';
                $sides[$player_id]['sides'][0]  = $side1;
                $sides[$player_id]['sides'][1]  = $side2;
            }
            else {
                $sides[$player_id]['action'] = 'ressource';
                if ($actionChoice) {
                    if ($side1_state) {
                        $sides[$player_id]['sides'][0] = array('id' => '', 'type' => $side1, 'num' => 1);
                        $sideNum = 1;
                    }
                    elseif ($side2_state) {
                        $sides[$player_id]['sides'][0] = array('id' => '', 'type' => $side2, 'num' => 1);
                        $sideNum = 2;
                    }
                }
                else {
                    if ($side1 != 'none') {
                        $sides[$player_id]['sides'][0] = array('id' => '', 'type' => $side1, 'num' => 1);
                        $sideNum = 1;
                    }
                    else {
                        $sides[$player_id]['sides'][0] = array('id' => '', 'type' => $side2, 'num' => 2);
                        $sideNum = 2;
                    }
                }

                // We add the message to notice the users that loyalty reward is included
                if (((isset($this->dice_sides[$sides[$player_id]['sides'][0]['type']]['ressource']['gold']) && $this->getLoyaltyArg($player_id, $sideNum, 'gold', true)['gold'] != 0)
                        || (isset($this->dice_sides[$sides[$player_id]['sides'][0]['type']]['ressource']['vp']) && $this->getLoyaltyArg($player_id, $sideNum, 'vp', true)['vp'] != 0))) {
                    $sides['loyalty'] = $msgLoyalty;
                    $sides['i18n'] = ['loyalty'];
                }

                $sides[$player_id]['possibilities'] = $this->calculatePossibilities($player_id, $sides[$player_id]['sides'][0]['type'], $sideNum, "", 1, true);
            }
        }
        return $sides;

    }

    function argsRessourceChoice() {

        $msgLoyalty = clienttranslate("(including Loyalty reward)");

        // if there are no active players just exit
        if ( count( $this->gamestate->getActivePlayerList() ) == 0 )
        {
            return [];
        }

        $players_info = $this->getPlayersAdditionnalInfo();
        $sides = [];
        $sides['loyalty'] = "";

        if ($this->getGameStateValue( "celestialRunning"))
            $sides['celestial'] = $this->celestialDie[$this->getGameStateValue( "celestialDieSide")];
        else
            $sides['celestial'] = "";

        foreach ($this->gamestate->getActivePlayerList() as $player_id)
        {
            $player_info = $players_info[$player_id];

            // if we are in a sideChoice
            if ($player_info['ressource_choice'] == self::RC_SIDE_CHOICE)
            {
                $sides[$player_id]['action'] = 'side';
                $dice_number = $this->getGameStateValue("enigmaDieNumber");
                // used to trigger last effect of the maze
                if ($this->getGameStateValue("firstFinish") == $player_id || $this->getGameStateValue("goddessChoice") == 1)
                    $sides[$player_id]['firstFinish'] = 1;
                else
                    $sides[$player_id]['firstFinish'] = 0;
                $nbSideChoice = 0;
                $side1 = false;
                $side2 = false;
                $side98 = false;
                $mirror = 0;

                if ($players_info[$player_id]['side_choice_1'] == -1) {
                    $nbSideChoice++;
                    $side1 = true;
                }
                if ($players_info[$player_id]['side_choice_2'] == -1) {
                    $nbSideChoice++;
                    $side2 = true;
                }
                if ($this->tokens->getTokenLocation("celestial_choice") == "mirror") {
                    $nbSideChoice++;
                    $side98 = true;
                }

                $sides[$player_id]['side_choice']['side1'] = $side1;
                $sides[$player_id]['side_choice']['side2'] = $side2;
                $sides[$player_id]['side_choice']['side98'] = $side98;

                // nbSideChoice should not equal 0

                if ($side1) {
                    $tmp = $this->sides->getCardsInLocation('dice1-p'.$player_id, 0);
                    $tmp = reset ($tmp);

                    if ($tmp['type'] == 'mirror')
                        $mirror++;
                }

                if ($side2) {
                    $tmp = $this->sides->getCardsInLocation('dice2-p'.$player_id, 0);
                    $tmp = reset($tmp);
                    if ($tmp['type'] == 'mirror')
                        $mirror++;
                }

                // If player has mirror & other player has mirror => send true to authorize all connexions
                if ($mirror != 0  && $this->checkValidSide('mirror', null, $player_id))
                    $sides[$player_id]['allMirror'] = true;
                else
                    $sides[$player_id]['allMirror'] = false;

                $sides[$player_id]['mirror'] = $nbSideChoice;
            }
            elseif ($player_info['ressource_choice'] == self::RC_RESSOURCE)
            {
                $sides[$player_id]['action'] = 'ressource';
                $sideNum = 0;
                // If we are in Ressource Choice
                $sides[$player_id]['sides'] = [];
                $sides[$player_id]['triple'] = 1;

                if ($this->tokens->getTokenState("resolveTwin_" . $player_id) && $players_info[$player_id]['twins'] != 0) {
                    $sides[$player_id]['sides'][0] = array('id' => '', 'type' => 'twins', 'num' => 4);
                    $sideNum = 4;
                    //var_dump(1);
                    $sides[$player_id]['possibilities'] = $this->calculatePossibilities($player_id, $sides[$player_id]['sides'][0]['type'], $sideNum, "", $sides[$player_id]['triple']);
                    return $sides;
                }

                // celestial die specific sides
                if ($this->tokens->getTokenLocation("celestial_choice") != '0'  && $this->tokens->getTokenLocation("celestial_choice") != null) {
                    $sides[$player_id]['sides'][0] = array('id' => '', 'type' => $this->tokens->getTokenLocation("celestial_choice"), 'num' => 98);
                    $sideNum = 98;
                    //var_dump(2);
                    $sides[$player_id]['possibilities'] = $this->calculatePossibilities($player_id, $sides[$player_id]['sides'][0]['type'], $sideNum, "", $sides[$player_id]['triple']);
                    return $sides;
                }

                $choiceExclude = array('-1', '0', 'triple', 'ship', 'redShield', 'yellowShield', 'greenShield', 'blueShield');
                $twinChoice = $this->tokens->getTokenState("twinChoice_" . $player_id);
                if ($twinChoice != 0) {
                    $sides[$player_id]['sides'][0] = array('id' => '', 'type' => $players_info[$player_id]['side_choice_' . $twinChoice], 'num' => $twinChoice);
                    $sideNum = $twinChoice;
                }
                // we send only one side at a time
                elseif (!in_array($players_info[$player_id]['side_choice_1'], $choiceExclude)) {
                    $sides[$player_id]['sides'][0] = array('id' => '', 'type' => $players_info[$player_id]['side_choice_1'], 'num' => 1);
                    $sideNum = 1;
                }
                elseif (!in_array($players_info[$player_id]['side_choice_2'], $choiceExclude)) {
                    $sides[$player_id]['sides'][0] = array('id' => '', 'type' => $players_info[$player_id]['side_choice_2'], 'num' => 2);
                    $sideNum = 2;
                }

                // We are managing Shield as they are excluded before
                if ($sideNum == 0) {
                    $choiceExclude = array('-1', '0', 'triple', 'ship');
                    if (!in_array($players_info[$player_id]['side_choice_1'], $choiceExclude)) {
                        $sides[$player_id]['sides'][0] = array('id' => '', 'type' => $players_info[$player_id]['side_choice_1'], 'num' => 1);
                        $sideNum = 1;
                    }
                    elseif (!in_array($players_info[$player_id]['side_choice_2'], $choiceExclude)) {
                        $sides[$player_id]['sides'][0] = array('id' => '', 'type' => $players_info[$player_id]['side_choice_2'], 'num' => 2);
                        $sideNum = 2;
                    }
                }

                // No choice, boar?
                if ($sideNum == 0 && $players_info[$player_id]['boar'] != 0) {
                    $sides[$player_id]['sides'][0] = array('id' => '', 'type' => 'boar', 'num' => 3);
                    $sideNum = 3;
                }

                // No choice, twins?
                if ($sideNum == 0 && $players_info[$player_id]['twins'] != 0) {
                    $sides[$player_id]['sides'][0] = array('id' => '', 'type' => 'twins', 'num' => 4);
                    $sideNum = 4;
                }

                // deprecated : triple token
                //if (($players_info[$player_id]['side_choice_1'] == 'triple' || $players_info[$player_id]['side_choice_2'] == 'triple') && $sideNum != 3 )
                if ($this->tokens->getTokenState("triple_$player_id") == 1 && $sideNum < 3)
                    $sides[$player_id]['triple'] = 3;

                // We add the message to notice the users that loyalty reward is included
                if (($sideNum == 1 || $sideNum == 2) &&
                    ((isset($this->dice_sides[$sides[$player_id]['sides'][0]['type']]['ressource']['gold']) && $this->getLoyaltyArg($player_id, $sideNum, 'gold')['gold'] != 0)
                        || (isset($this->dice_sides[$sides[$player_id]['sides'][0]['type']]['ressource']['vp']) && $this->getLoyaltyArg($player_id, $sideNum, 'vp')['vp'] != 0))) {
                    $sides['loyalty'] = $msgLoyalty;
                    $sides['i18n'] = ['loyalty'];
                }


               //var_dump(3);
                $sides[$player_id]['possibilities'] = $this->calculatePossibilities($player_id, $sides[$player_id]['sides'][0]['type'], $sideNum, "", $sides[$player_id]['triple']);
                // bug 10866
                //$sides[$player_id]['sideNum'] = $sideNum;

            }
            elseif ($player_info['ressource_choice'] == self::RC_ACTION_CHOICE)
            {
                $sides = $this->generateActionChoiceArgs($player_id, $sides);

            } elseif ($player_info['ressource_choice'] == self::RC_MAZE) {
                $sides = $this->generateMazeChoiceArgs($player_id, $sides);

            }
        }

        return $sides;
    }

    //function argsForgeDice() {
    //    $player_id = self::getActivePlayerId();
    //
    //    return $sides_to_forge = $this->sides->getCardsInLocation('forging');
    //}

    // return info available ressource including reduction
    function argsForgeShip() {
        //$player_id = self::getCurrentPlayerId();
        $retour = array();
        $retour['ship'] = "ship";

        foreach ($this->gamestate->getActivePlayerList() as $player_id)  {

            $player_info = $this->getPlayersAdditionnalInfo()[$player_id];

            $retour[$player_id]['nbSideToForge'] = 1;
            // if triple or has choosen triple
            // deprecated: triple token
            //if ($player_info['side_choice_1'] == 'triple' || $player_info['side_choice_2'] == 'triple')
            if ($this->tokens->getTokenState("triple_$player_id") == 1)
                $retour[$player_id]['minusCost'] = 3 * -2;
            else
                $retour[$player_id]['minusCost'] = -2;

            if ($this->getGameStateValue("forgeClassical"))
                $retour[$player_id]['minusCost'] = 0;

            $retour[$player_id]['call'] = 'actForgeShip';

            if ($this->getGameStateValue("mazeForge")) {
                $retour[$player_id]['sides'][0] = array('id' => '', 'type' => 'maze-ship-g2', 'num' => 99);
                $retour['ship'] = "mShip";
                if ($this->getGameStateValue("forgeClassical")) {
                    $retour[$player_id]['sides'][0] = array('id' => '', 'type' => 'maze-ship', 'num' => 99);
                    $retour['ship'] = "mForge";
                }
            }
            elseif ($this->getGameStateValue( "celestialRunning") && $this->celestialDie[$this->getGameStateValue( "celestialDieSide")] == "doubleUpgrade") {
                $retour['ship'] = "doubleUpgrade";
                $retour[$player_id]['sides'][0] = array('id' => '', 'type' => 'side-double-upgrade', 'num' => -1);
                $retour[$player_id]['minusCost'] = 0;
            }
            elseif ($player_info['side_choice_1'] == 'ship') {
                $retour[$player_id]['sides'][0] = array('id' => '', 'type' => $player_info['side_choice_1'], 'num' => 1);
            }
            elseif($player_info['side_choice_2'] == 'ship') {
                $retour[$player_id]['sides'][0] = array('id' => '', 'type' => $player_info['side_choice_2'], 'num' => 2);
            }
            //$retour[$player_id]['gold'] = $player_info['res_gold'];
            $retour[$player_id]['gold'] = $this->getGold($player_id);
        }

        $retour['minusCost'] = $retour[$player_id]['minusCost'] == 0 ? "" : $retour[$player_id]['minusCost'] . " [G]";
        return $retour;

    }

    function argsForgeBoar() {
        $card_id = $this->getGameStateValue( "exploitBought");
        $sideToForge = "";
        $card_type = "";
        $card_name = "";

        if ($card_id != -1) {
            $card = $this->exploits->getCard($card_id);

            $sideToForge = $this->sides->getCardsOfType($card['type']);
            if (count($sideToForge) != 0) {
                $sideToForge = reset($sideToForge)['id'];
            }

            if (substr($card['type'], -4) == 'Boar')
                $card_type = 'boar';
            elseif (substr($card['type'], -10) == 'Misfortune')
                $card_type = 'misfortune';

            $card_name = $this->exploit_types[$card['type']]['name'];
        }
        return array('id' => $sideToForge, 'type' => $card_type, 'card_name' => $card_name, 'i18n' => ['card_name']);
    }

    function argExploitEffect()
    {
        $card_id = $this->getGameStateValue( "exploitBought");
        $pow = '';
        $pow_you = '';
        $retour = array();

        if ($card_id != -1) {
            $card = $this->exploits->getCard($card_id);
            $card_info = $this->exploit_types[$card['type']];

            $retour['celestial'] = "";
            $retour['card'] = $card_info;

            if ($this->getGameStateValue( "enigmaDieNumber") != -1)
                $retour['effectRunning'] = true;
            else
                $retour['effectRunning'] = false;

            if (isset($this->powers[$card_info['action']]))
                $pow = clienttranslate($this->powers[$card_info['action']]);
            if (isset($this->powers_you[$card_info['action']]))
                $pow_you = clienttranslate($this->powers_you[$card_info['action']]);

            switch ($card_info['action']) {
                case 'forgeVP':
                    $die_number =  $this->getGameStateValue( "enigmaDieNumber");
                    if ($die_number == 1  || $die_number == 2)
                        $retour['poolToForge'] = -1;
                    else
                        $retour['poolToForge'] = $this->getNextVPPool();
                    break ;
                case 'forgeEverywhere':
                    $retour['poolList'] = $this->getTridentSides();
                    break ;
                case 'memoryTokens':
                    $retour['memory'] = $this->tokens->getTokensOfTypeInLocation($card['type']. '%_' . self::getActivePlayerId(), 'none');
                    break ;
                //case "throwCelestialDie":
                //    $retour['celestial'] = $this->celestialDie[$this->getGameStateValue( "celestialDieSide")];
                //    break ;
            }

            $retour['info'] = array('player' => self::getActivePlayerName(), 'power_desc' => $pow, 'power_you' => $pow_you);

            if ($this->getGameStateValue( "celestialRunning"))
                $retour['celestial'] = $this->celestialDie[$this->getGameStateValue( "celestialDieSide")];
            else
                $retour['celestial'] = "";
        }

        return $retour;
    }

    function argExploitRessource() {
        $card_id = $this->getGameStateValue( "exploitBought");
        $players_info = $this->getPlayersAdditionnalInfo();
        $card = $this->exploits->getCard($card_id);
        $card_info = $this->exploit_types[$card['type']];
        $pow = '';
        $pow_you = '';
        $msgLoyalty = clienttranslate("(including Loyalty reward)");

        $ressourceToText = $this->ressourceToText; //array ('gold' => '[G]', 'moonshard' => '[MS]', 'fireshard' => '[FS]', 'hammer' => '[H]', 'vp' => '[VP]');
        $retour['loyalty'] = "";
        $retour['card'] = $card_info;
        if (isset($this->powers[$card_info['action']]))
            $pow = $this->powers[$card_info['action']];
        if (isset($this->powers_you[$card_info['action']]))
            $pow_you = $this->powers_you[$card_info['action']];

        $retour['info'] = array('player' => self::getActivePlayerName(), 'power_desc' => $pow, 'power_you' => $pow_you);

        if ($this->getGameStateValue( "celestialRunning"))
            $retour['celestial'] = $this->celestialDie[$this->getGameStateValue( "celestialDieSide")];
        else
            $retour['celestial'] = "";

        //if ($retour['celestial'] == "doubleUpgrade") {
        //    $this->gamestate->nextState("nextState");
        //    return ;
        //}
        foreach ($this->gamestate->getActivePlayerList() as $player_id)
        {
            $retour[$player_id]['sides'] = [];
            $retour[$player_id]['triple'] = 1;

             $player_info = $players_info[$player_id];
             $retour[$player_id]['reschoi'] = $player_info['ressource_choice'];
            //throw new feException(print_r($player_info));
            // if we are in a sideChoice
            if ($player_info['ressource_choice'] == self::RC_SIDE_CHOICE)
            {
                $retour[$player_id]['action'] = 'side';
                if ($card_info['action'] == 'oustAll')
                    $retour[$player_id]['oustedPlayerId'] = $this->getGameStateValue('oustedPlayerId');
                $dice_number = $this->getGameStateValue("enigmaDieNumber");

                // used to trigger last effect of the maze
                if ($this->getGameStateValue("firstFinish") == $player_id || $this->getGameStateValue("goddessChoice") == 1)
                    $retour[$player_id]['firstFinish'] = 1;
                else
                    $retour[$player_id]['firstFinish'] = 0;

                $nbSideChoice = 0;
                $side1 = false;
                $side2 = false;
                $side98 = false;
                $mirror = 0;

                if ($players_info[$player_id]['side_choice_1'] == -1) {
                    $nbSideChoice++;
                    $side1 = true;
                }
                if ($players_info[$player_id]['side_choice_2'] == -1) {
                    $nbSideChoice++;
                    $side2 = true;
                }
                if ($this->tokens->getTokenLocation("celestial_choice") == "mirror") {
                    $nbSideChoice++;
                    $side98 = true;
                    $mirror++;
                }

                $retour[$player_id]['side_choice']['side1'] = $side1;
                $retour[$player_id]['side_choice']['side2'] = $side2;
                $retour[$player_id]['side_choice']['side98'] = $side98;

                // nbSideChoice should not equal 0

                if ($side1) {
                    $tmp = $this->sides->getCardsInLocation('dice1-p'.$player_id, 0);
                    $tmp = reset ($tmp);

                    if ($tmp['type'] == 'mirror')
                        $mirror++;
                    elseif ($card_info['action'] == 'oustAll' && $players_info[$player_id]['side_choice_1'] == -1)
                        $mirror++;
                }

                if ($side2) {
                    $tmp = $this->sides->getCardsInLocation('dice2-p'.$player_id, 0);
                    $tmp = reset($tmp);
                    if ($tmp['type'] == 'mirror')
                        $mirror++;
                    elseif ($card_info['action'] == 'oustAll' && $players_info[$player_id]['side_choice_2'] == -1)
                        $mirror++;
                }

                if (($card_info['action'] == '4Throws' || $card_info['action'] == '4ThrowsTransform') && $mirror > 1)
                    $mirror = 1;

                // If player has mirror & other player has mirror => send true to authorize all connexions
                if ($mirror != 0  && $this->checkValidSide('mirror', null, $player_id) && $card_info['action'] != 'oustAll')
                    $retour[$player_id]['allMirror'] = true;
                elseif ($mirror != 0  && $this->checkValidSide('mirror', null, $this->getGameStateValue('oustedPlayerId')) && $card_info['action'] == 'oustAll')
                    $retour[$player_id]['allMirror'] = true;
                else
                    $retour[$player_id]['allMirror'] = false;

                $retour[$player_id]['mirror'] = $mirror;
            }
            elseif ($player_info['ressource_choice'] == self::RC_RESSOURCE)
            {
                $retour[$player_id]['action'] = 'ressource';
                $choiceExclude = array('-1', '0', 'triple', 'ship', 'redShield', 'yellowShield', 'greenShield', 'blueShield');

                $toTransform = $this->getRessourcesToTransform();

                //// We are running the celestial die
                //if ($this->getGameStateValue('celestialRunning')) {
                //    $sideNum = 1;
                //    $retour[$player_id]['sides'][0] = array('id' => '', 'type' => $players_info[$player_id]['side_choice_1'], 'num' => 1);
                //    //var_dump(4);
                //    $retour[$player_id]['possibilities'] = $this->calculatePossibilities($player_id, $retour[$player_id]['sides'][0]['type'], $sideNum, "", $retour[$player_id]['triple']);
                //    break ;
                //}

                if ($this->tokens->getTokenState("resolveTwin_" . $player_id) && $players_info[$player_id]['twins'] != 0) {
                    $retour[$player_id]['sides'][0] = array('id' => '', 'type' => 'twins', 'num' => 4);
                    $sideNum = 4;
                    //var_dump(5);
                    $retour[$player_id]['possibilities'] = $this->calculatePossibilities($player_id, $retour[$player_id]['sides'][0]['type'], $sideNum, $card_info['action'], $retour[$player_id]['triple']);
                    return $retour;
                }
                // celestial die specific sides
                if ($this->tokens->getTokenLocation("celestial_choice") != '0' && $this->tokens->getTokenLocation("celestial_choice") != null) {
                    $retour[$player_id]['sides'][0] = array('id' => '', 'type' => $this->tokens->getTokenLocation("celestial_choice"), 'num' => 98);
                    $sideNum = 98;
                    //var_dump(2);
                    $retour[$player_id]['possibilities'] = $this->calculatePossibilities($player_id, $retour[$player_id]['sides'][0]['type'], $sideNum, "", $retour[$player_id]['triple']);
                    return $retour;
                }

                switch ($card_info['action']) {
                    case '4Throws':
                        //$die_number =  $this->getGameStateValue( "enigmaDieNumber");
                        //$retour[$player_id]['sides'][0] = array('id' => '', 'type' => $players_info[$player_id]['side_choice_' . $die_number], 'num' => $die_number);
                        //$sideNum = $die_number;
                        //break;
                    case 'fullThrow2':
                    case 'looseThrow':
                    case '4ThrowsTransform':
                    case 'fullThrow2Transform':
                    case '3G3M':
                    case 'steal2':
                    case 'forge4G':
                    case 'forgeVP':
                    case 'oustAll':
                    case 'throwCelestialDie':
                    case 'chooseSides':
                    case 'greatGolem':
                    case 'timeGolem':
                        $sideNum = 0;
                        $twinChoice = $this->tokens->getTokenState("twinChoice_" . $player_id);

                        if ($twinChoice != 0) {
                            $retour[$player_id]['sides'][0] = array('id' => '', 'type' => $players_info[$player_id]['side_choice_' . $twinChoice], 'num' => $twinChoice);
                            $sideNum = $twinChoice;
                        }
                        elseif (!in_array($players_info[$player_id]['side_choice_1'], $choiceExclude)) {
                            $retour[$player_id]['sides'][0] = array('id' => '', 'type' => $players_info[$player_id]['side_choice_1'], 'num' => 1);
                            $sideNum = 1;
                        }
                        elseif (!in_array($players_info[$player_id]['side_choice_2'], $choiceExclude)) {
                            $retour[$player_id]['sides'][0] = array('id' => '', 'type' => $players_info[$player_id]['side_choice_2'], 'num' => 2);
                            $sideNum = 2;
                        }

                        if ($sideNum == 0) {
                            $choiceExclude = array('-1', '0', 'triple', 'ship');
                            if (!in_array($players_info[$player_id]['side_choice_1'], $choiceExclude)) {
                                $retour[$player_id]['sides'][0] = array('id' => '', 'type' => $players_info[$player_id]['side_choice_1'], 'num' => 1);
                                $sideNum = 1;
                            }
                            elseif (!in_array($players_info[$player_id]['side_choice_2'], $choiceExclude)) {
                                $retour[$player_id]['sides'][0] = array('id' => '', 'type' => $players_info[$player_id]['side_choice_2'], 'num' => 2);
                                $sideNum = 2;
                            }
                        }

                        // No choice, boar?
                        if ($sideNum == 0 && $players_info[$player_id]['boar'] != 0) {
                            $retour[$player_id]['sides'][0] = array('id' => '', 'type' => 'boar', 'num' => 3);
                            $sideNum = 3;
                        }

                        // twin change
                        //// No choice, twins?
                        //if ($sideNum == 0 && $players_info[$player_id]['twins'] != 0) {
                        //    $retour[$player_id]['sides'][0] = array('id' => '', 'type' => 'twins', 'num' => 4);
                        //    $sideNum = 4;
                        //}

                        // deprecated: triple token
                        //if ($players_info[$player_id]['side_choice_1'] == 'triple' || $players_info[$player_id]['side_choice_2'] == 'triple' )
                        if ($this->tokens->getTokenState("triple_$player_id") == 1 && $sideNum < 3)
                            $retour[$player_id]['triple'] = 3;

                        //var_dump(6);
                        $retour[$player_id]['possibilities'] = $this->calculatePossibilities($player_id, $retour[$player_id]['sides'][0]['type'], $sideNum, $card_info['action'], $retour[$player_id]['triple']);

                        if (($sideNum == 1 || $sideNum == 2) &&
                            ((isset($this->dice_sides[$retour[$player_id]['sides'][0]['type']]['ressource']['gold']) && $this->getLoyaltyArg($player_id, $sideNum, 'gold')['gold'] != 0)
                                || (isset($this->dice_sides[$retour[$player_id]['sides'][0]['type']]['ressource']['vp']) && $this->getLoyaltyArg($player_id, $sideNum, 'vp')['vp'] != 0)) &&
                            $card_info['action'] != 'looseThrow') {
                            $retour['loyalty'] = $msgLoyalty;
                            $retour['i18n'] = ['loyalty'];
                        }

                        break;
                    case 'throwAllChooseResources':
                        // combo of all resources
                        $resource['vp'] = 0;
                        $resource['loyalty'] = 0;
                        $resource['fireshard'] = 0;
                        $resource['moonshard'] = 0;
                        $resource['gold'] = 0;
                        $resource['ancientshard'] = 0;
                        $hasHammer = $this->canFillHammer($player_id);

                        $pos = 0;
                        // take all side up
                        foreach ($players_info as $it_player_id => $player_info)  {
                            for ($i = 1; $i < 3; $i++) {
                                $side = $this->sides->getCard($player_info['throw_' . $i]);
                                $side_info = $this->dice_sides[$side['type']];

                                foreach ($side_info['ressource'] as $res => $value) {
                                    $resource[$res] = $resource[$res] + $value;
                                }
                            }
                        }

                        $remainingHammer = $this->remainingHammer($player_id);
                        $hasHammer = $this->canFillHammer($player_id);

                        $ressourceToText = $this->ressourceToText;

                        if ($resource['gold'] != 0 && $hasHammer) {
                            $gold = $resource['gold'];

                            if ($gold - $remainingHammer < 0)
                                $incr = 0;
                            else
                                $incr = $gold - $remainingHammer;

                            $ressources = array();
                            for ($i=0; $i <= $gold; $i++)
                                $ressources['[G]'][] = $i;
                            for ($i=0; $i <= $gold - $incr; $i++)
                                $ressources['[H]'][] = $i;

                            $cartesian = $this->filterCartesian($this->cartesian($ressources), $gold, $toTransform['vp'], false);

                            foreach ($cartesian as $key => $values) {
                                $possibilities[$pos] = array("[G]" => 0, "[H]" => 0, "[FS]" => 0, "[MS]" => 0, "[VP]" => 0, "[AS]" => 0, "[M]" => 0, "[L]" => 0, 'text' => '', 'num' => 5);
                                if (isset($values['[G]']) && $values['[G]'] != 0)
                                    $possibilities[$pos]['[G]'] = $values['[G]'];

                                if (isset($values['[H]']) && $values['[H]'] != 0)
                                    $possibilities[$pos]['[H]'] = $values['[H]'];

                                if (isset($values['[FS]']) && $values['[FS]'] != 0)
                                    $possibilities[$pos]['[FS]'] = $values['[FS]'];

                                if (isset($values['[MS]']) && $values['[MS]'] != 0)
                                    $possibilities[$pos]['[MS]'] = $values['[MS]'];

                                if (isset($values['[VP]']) && $values['[VP]'] != 0)
                                    $possibilities[$pos]['[VP]'] = $values['[VP]'];

                                if (isset($values['[L]']) && $values['[L]'] != 0)
                                    $possibilities[$pos]['[L]'] = $values['[L]'];

                                if (isset($values['[AS]']) && $values['[AS]'] != 0)
                                    $possibilities[$pos]['[AS]'] = $values['[AS]'];

                                $pos++;
                            }
                        }
                        else
                            $possibilities[$pos++] = array("[G]" => $resource['gold'], "[H]" => 0, "[FS]" => 0, "[MS]" => 0, "[VP]" => 0, "[AS]" => 0, "[M]" => 0, "[L]" => 0, 'text' => $resource['gold'] .' [G]', 'num' => 5);

                        if ($resource['fireshard'] != 0)
                            $possibilities[$pos++] = array("[G]" => 0, "[H]" => 0, "[FS]" => $resource['fireshard'], "[MS]" => 0, "[VP]" => 0, "[AS]" => 0, "[M]" => 0, "[L]" => 0, 'text' => $resource['fireshard'] .' [FS]', 'num' => 5);
                        if ($resource['moonshard'] != 0)
                            $possibilities[$pos++] = array("[G]" => 0, "[H]" => 0, "[FS]" => 0, "[MS]" => $resource['moonshard'], "[VP]" => 0, "[AS]" => 0, "[M]" => 0, "[L]" => 0, 'text' => $resource['moonshard'] .' [MS]', 'num' => 5);
                        if ($resource['vp'] != 0)
                            $possibilities[$pos++] = array("[G]" => 0, "[H]" => 0, "[FS]" => 0, "[MS]" => 0, "[VP]" => $resource['vp'], "[AS]" => 0, "[M]" => 0, "[L]" => 0, 'text' => $resource['vp'] .' [VP]', 'num' => 5);
                        if ($resource['loyalty'] != 0)
                            $possibilities[$pos++] = array("[G]" => 0, "[H]" => 0, "[FS]" => 0, "[MS]" => 0, "[VP]" => 0, "[AS]" => 0, "[M]" => 0, "[L]" => $resource['loyalty'], 'text' => $resource['loyalty'] .' [L]', 'num' => 5);
                        if ($resource['ancientshard'] != 0)
                            $possibilities[$pos++] = array("[G]" => 0, "[H]" => 0, "[FS]" => 0, "[MS]" => 0, "[VP]" => 0, "[L]" => 0, "[M]" => 0, "[AS]" => $resource['ancientshard'],'text' => $resource['ancientshard'] .' [AS]', 'num' => 5);

                        $possibilities = $this->possibilityAddText($possibilities);

                        $retour[$player_id]['possibilities'] = $possibilities;

                        $retour[$player_id]['sides'][0] = array('id' => '', 'type' => 'wind', 'num' => 5);

                        break ;
                    case 'convertGoldToVP':
                        $retour[$player_id]['sides'][0] = array('id' => '', 'type' => 'rightHand', 'num' => 5);
                        $possibilities = array ();
                        $pos = 0;
                        //$gold = $this->getGoldResource($player_id, 'reserve');
                        $gold = $this->getGold($player_id);
                        for ($i = 1; $i <= $gold; $i++) {
                            $possibilities[$pos++] = array("[G]" => -1 * $i, "[H]" => 0, "[FS]" => 0, "[MS]" => 0, "[VP]" => $i, "[L]" => 0, "[AS]" => 0, "[M]" => 0, 'text' => '', 'num' => 5);
                        }
                        $retour[$player_id]['possibilities'] = $this->possibilityAddText($possibilities);
                        break ;
                }


                //$retour[$player_id]['possibilities'] = $this->calculatePossibilities($player_id, $retour[$player_id]['sides'][0]['type'], $sideNum, $card_info['action'], $retour[$player_id]['triple']);
            }
            elseif ($player_info['ressource_choice'] == self::RC_ACTION_CHOICE)
            {
                $retour = $this->generateActionChoiceArgs($player_id, $retour);
                //if ($players_info[$player_id]['side_choice_1'] != "0" && $players_info[$player_id]['side_choice_2'] != "0") {
                //    $retour[$player_id]['action'] = 'actionChoice';
                //    if ($players_info[$player_id]['side_choice_1'] != 'ship')
                //        $retour[$player_id]['sides'][0] = $players_info[$player_id]['side_choice_1'];
                //    else
                //        $retour[$player_id]['sides'][0] = $players_info[$player_id]['side_choice_2'];
                //}
                //else {
                //    // The player has a  cerberus token
                //    if ($this->hasCerberusToken($player_id)) {
                //        $retour[$player_id]['action'] = 'cerberusToken';
                //    }
                //}
            }
            elseif ($player_info['ressource_choice'] == self::RC_MAZE)
            {
                $retour = $this->generateMazeChoiceArgs($player_id, $retour);
                //// do we manage a ressource choice?
                //if ($this->tokens->getTokenLocation("mazechoice_" . $player_id) != "0") {
                //    $retour[$player_id]['action'] = 'mazeRessource';
                //    $retour[$player_id]['sides'][0] = array('id' => '', 'type' => $this->tokens->getTokenLocation("mazechoice_" . $player_id), 'num' => 99);
                //    $sideNum = 99;
                //    $retour[$player_id]['possibilities'] = $this->calculatePossibilities($player_id, $retour[$player_id]['sides'][0]['type'], $sideNum, "");
                //}
                //else {
                //    $retour[$player_id]['action'] = 'maze';
                //    if ($this->tokens->getTokenState("mazestock_" . $player_id) > 0)
                //        $retour[$player_id]['mazePath'] = $this->maze[$this->tokens->getTokenState("position_" . $player_id)]['path'];
                //    else
                //        $retour[$player_id]['mazePath'] = $this->maze[$this->tokens->getTokenState("position_" . $player_id)]['reverse'];
                //}
            }
        }
        return $retour;
    }

//////////////////////////////////////////////////////////////////////////////
//////////// Game state actions
////////////

    /*
        Here, you can create methods defined as "game state actions" (see "action" property in states.inc.php).
        The action method of state X is called everytime the current game state is set to X.
    */

    function stBeginTurn()
    {
        $isGameSetup = $this->getGameStateValue( "isGameSetup");

        if ($isGameSetup) {
            $player_id = self::getGameStateValue( 'firstPlayerId' );
            //$magicSeagull = self::getGameStateValue( 'magicSeagullCount' );
            //$magicSeagullActive = self::getGameStateValue( 'magicSeagullActive' );


            $this->gamestate->changeActivePlayer( $player_id );

            $this->setGameStateValue( "currentPlayerNum", 1 );

            // Calculation of remaining turns
            $turnCount      = self::getGameStateValue( 'turnCount' );
            $remainingTurns = self::getGameStateValue( 'nbTurns' ) - self::getGameStateValue( 'turnCount' ) + 1;

            if ($remainingTurns == 0) {
                $this->gamestate->nextState('gameEnd');
            } else {
                //// Seagull management
                //if ($magicSeagullActive == 1 && $magicSeagull == 0) {
                //  // We must switch back the dices
                //  $table = $this->getNextPlayerTable();
                //
                //  $sql = "UPDATE sides set card_location = concat('zz', card_location) where card_location like 'dice%'";
                //  self::DbQuery( $sql );
                //
                //  foreach($table as $player => $previous_player) {
                //      $sql = "UPDATE sides set card_location = 'dice1-p" . $player . "' WHERE card_location = 'zzdice1-p". $previous_player . "'";
                //      self::DbQuery( $sql );
                //      $sql = "UPDATE sides set card_location = 'dice2-p" . $player . "' WHERE card_location = 'zzdice2-p". $previous_player . "'";
                //      self::DbQuery( $sql );
                //  }
                //
                //  self::notifyAllPlayers("notifDiceSwitch", "The dice go back to their owners",
                //      array(
                //          'switch' => $table,
                //      )
                //  );
                //
                //  // flag magicSeagullActive
                //  self::setGameStateValue( 'magicSeagullActive', 0);
                //}
                //elseif ($magicSeagullActive == 0 && $magicSeagull > 0) {
                //  $table = $this->getPrevPlayerTable();
                //
                //  $sql = "UPDATE sides set card_location = concat('zz', card_location) where card_location like 'dice%'";
                //  self::DbQuery( $sql );
                //
                //  foreach($table as $player => $previous_player) {
                //      $sql = "UPDATE sides set card_location = 'dice1-p" . $player . "' WHERE card_location = 'zzdice1-p". $previous_player . "'";
                //      self::DbQuery( $sql );
                //      $sql = "UPDATE sides set card_location = 'dice2-p" . $player . "' WHERE card_location = 'zzdice2-p". $previous_player . "'";
                //      self::DbQuery( $sql );
                //  }
                //
                //  self::notifyAllPlayers("notifDiceSwitch", "You take the dice of the previous player",
                //      array(
                //          'switch' => $table,
                //      )
                //  );
                //
                //  // flag magicSeagullActive
                //  self::setGameStateValue( 'magicSeagullActive', 1);
                //}
                //
                //if ($magicSeagull > 0) {
                //  $magicSeagull--;
                //  self::setGameStateValue( 'magicSeagullCount', $magicSeagull);
                //}


                if ($remainingTurns == 1) {
                    self::notifyAllPlayers("notifBeginTurn", "",
                        array(
                            'turn' => $turnCount,
                        )
                    );
                    self::notifyAllPlayers("notifLastTurn", clienttranslate('This is the last turn'),
                        array(
                        )
                    );


                }
                else {
                    // Notify beggining of turn
                    self::notifyAllPlayers("notifBeginTurn", clienttranslate('Turn ${turn}/${totalTurns} begins'),
                        array(
                            'totalTurns' => self::getGameStateValue( 'nbTurns' ),
                            'turn'           => $turnCount,
                        )
                    );
                }

                $this->incStat(1, 'turns_number');
                $this->gamestate->nextState( "beginPlayerTurn" );
            }
        }
        else {

            $this->activeNextPlayer();
            $this->gamestate->nextState( "draft" );
        }
    }

    function stDraftGame()
    {
        $slot = $this->draftSlot();
        if ($slot == null) {
            $this->setGameStateValue( "isGameSetup", 1);
            $this->initTokens();
            $this->gamestate->nextState('beginTurn');
        }
        else {
            $this->setGameStateValue('slotBeingDrafted', array_search($slot, $this->exploit_slot));
            self::activeNextPlayer();
            $this->gamestate->nextState('draft');
        }
    }

    function stBeginPlayerTurn()
    {
        self::setGameStateValue( 'secondActionTaken', 0 );

        // Eternal Fire
        $eternalFire = $this->getGameStateValue( "eternalFire" );

        // Extra time to the player
        $player_id = self::getActivePlayerId();
        self::giveExtraTime( $player_id );

        self::setGameStateValue('turnPlayerId', $player_id);
        self::setGameStateValue('nbThrows', 0);
        self::setGameStateValue('scepterFireshard', 0);
        self::setGameStateValue('scepterMoonshard', 0);

        if (!$eternalFire) {
            // Notify everyone whose turn it is
            self::notifyAllPlayers("notifBeginPlayerTurn", clienttranslate('It is ${player_name}\'s  turn'), array(
                'player_name' => self::getActivePlayerName(),
                'player_id'   => $player_id,
            ));
        }
        $this->incStat(1, 'turns_number', $player_id);

        // TODO : Eternal fire doit déclencher le swap?

        // Seagull management
        $magicSeagull = self::getGameStateValue( 'magicSeagullCount' );
        $magicSeagullActive = self::getGameStateValue( 'magicSeagullActive' );

        if ($magicSeagullActive == 1 && $magicSeagull == 0) {
            // We must switch back the dices
            $table = $this->getNextPlayerTable();

            $sql = "UPDATE sides set card_location = concat('zz', card_location) where card_location like 'dice%'";
            self::DbQuery( $sql );

            foreach($table as $player => $previous_player) {
                $sql = "UPDATE sides set card_location = 'dice1-p" . $player . "' WHERE card_location = 'zzdice1-p". $previous_player . "'";
                self::DbQuery( $sql );
                $sql = "UPDATE sides set card_location = 'dice2-p" . $player . "' WHERE card_location = 'zzdice2-p". $previous_player . "'";
                self::DbQuery( $sql );
            }

            self::notifyAllPlayers("notifDiceSwitch", clienttranslate("The dice go back to their owners"),
                array(
                    'playerSwitch' => $table,
                )
            );

            // flag magicSeagullActive
            self::setGameStateValue( 'magicSeagullActive', 0);
        }
        elseif ($magicSeagullActive == 0 && $magicSeagull > 0) {
            $table = $this->getPrevPlayerTable();

            $sql = "UPDATE sides set card_location = concat('zz', card_location) where card_location like 'dice%'";
            self::DbQuery( $sql );

            foreach($table as $player => $previous_player) {
                $sql = "UPDATE sides set card_location = 'dice1-p" . $player . "' WHERE card_location = 'zzdice1-p". $previous_player . "'";
                self::DbQuery( $sql );
                $sql = "UPDATE sides set card_location = 'dice2-p" . $player . "' WHERE card_location = 'zzdice2-p". $previous_player . "'";
                self::DbQuery( $sql );
            }

            self::notifyAllPlayers("notifDiceSwitch", clienttranslate("You take the dice of the previous player"),
                array(
                    'playerSwitch' => $table,
                )
            );

            // flag magicSeagullActive
            self::setGameStateValue( 'magicSeagullActive', 1);
        }

        if ($magicSeagull > 0) {
            $magicSeagull--;
            self::setGameStateValue( 'magicSeagullCount', $magicSeagull);
        }


        $this->gamestate->nextState("blessing");
    }

    // Trigger the rolling of the dices
    // Will handle the autoamtic resolution
    // Notify corresponsing users
    // if no choice to make 2 choices : blessing or choice
    function stBlessing()
    {
        $diceThrows = self::getGameStateValue('diceThrows');
        $nbThrows = self::getGameStateValue('nbThrows');
        $monoResolution = $this->getGameStateValue( "monoRessourceChoice");
        // Eternal Fire
        $eternalFire = $this->getGameStateValue( "eternalFire" );
        $choice = false;

        // there is a ressource Choice on one of the players
        // #35073 : add check of misfortune
        if ($this->isRessourceChoice() || $this->hasMazeStock() || $this->hasUnresolvedSides() || $this->misfortuneState() != 0) {
            // No conflict : all the necessary players are activated
            // Ship management, only one at a time

            // disable all players
            self::DbQuery('UPDATE player SET player_is_multiactive = 0');

            if ($monoResolution == 0) {
                // if action ressource to allocate or choice => ressource choice
                if ($this->isRessourceChoice(self::RC_RESSOURCE) || $this->isRessourceChoice(self::RC_ACTION_CHOICE) || $this->isRessourceChoice(self::RC_SIDE_CHOICE) || $this->isRessourceChoice(self::RC_MAZE) || $this->isRessourceChoice(self::RC_MISFORTUNE)) {
                    // activation of players needging a choice
                    $this->players_info = null;
                    $players_info = $this->getPlayersAdditionnalInfo();
                    $multi = array();
                    $i = 0;

                    foreach($players_info as $player_id => $players) {
                        if ($players['ressource_choice'] == self::RC_SIDE_CHOICE|| $players['ressource_choice'] == self::RC_RESSOURCE || $players['ressource_choice'] == self::RC_ACTION_CHOICE || $players['ressource_choice'] == self::RC_MAZE || $players['ressource_choice'] == self::RC_MISFORTUNE ) {
                            $multi[$i++] = $player_id;
                            self::giveExtraTime( $player_id, 60 );
                        }
                    }
                    $this->gamestate->setPlayersMultiactive ($multi, 'blessing');
                    $this->gamestate->nextState('ressourceChoice');
                    return ;
                }
                // If ship needs to be managed
                elseif ($this->isRessourceChoice(self::RC_FORGESHIP)) {
                //if ($this->hasUnusedShip()) {
                    $player_id = self::getActivePlayerId();
                    for($i=1; $i<=$this->getPlayersNumber(); $i++) {
                        // if player has a ship => enable only this user
                        if ($this->hasUnusedShip($player_id)) {
                            self::giveExtraTime( $player_id, 45 );
                            $this->gamestate->setPlayersMultiactive (array($player_id), 'blessing');
                            $this->gamestate->nextState('forgeShip');
                            return ;
                        }
                        $player_id = $this->getNextPlayerTable()[$player_id];
                    }
                }
                elseif ($this->hasUnresolvedSides()) {
                    $player_id = self::getActivePlayerId();
                    $players_info = $this->getPlayersAdditionnalInfo();
                    for($i=1; $i<=$this->getPlayersNumber(); $i++) {
                        if ($players_info[$player_id]['side_choice_1'] != '0') {
                            $did = $this->blessing($player_id, true, false, 1, false, false);
                            $this->setChoice($player_id, self::RC_RESSOURCE);
                        }
                        if ($players_info[$player_id]['side_choice_2'] != '0') {
                            $did = $this->blessing($player_id, false, true, 1, false, false);
                            $this->setChoice($player_id, self::RC_RESSOURCE);
                        }
                        $player_id = $this->getNextPlayerTable()[$player_id];
                    }
                    self::notifyAllPlayers("updateCounters", "", $this->getPlayersRessources());
                    $this->gamestate->nextState('blessing');
                }
                elseif ($this->misfortuneState() != 0) {
                    $toEnable = $this->misfortuneAllocation();

                    // allocate if choice ==> token

                    self::giveExtraTime( $toEnable, 45 );
                    $this->gamestate->setPlayersMultiactive (array($toEnable), 'blessing');
                    $this->tokens->moveToken('resolveMisfortune', 'none', 0);
                    $this->gamestate->nextState('misfortune');
                    return ;
                }
            } else { // Only one active player at a time. full resolution must happen before activating next player
                $players_info = $this->getPlayersAdditionnalInfo();
                $player_id = self::getActivePlayerId();

                for($i=1; $i<=$this->getPlayersNumber(); $i++) {
                    $res_choice = $players_info[$player_id]['ressource_choice'];

                    // if player has a ressource choice
                    if ($res_choice == self::RC_RESSOURCE || $res_choice == self::RC_ACTION_CHOICE || $res_choice == self::RC_SIDE_CHOICE || $res_choice == self::RC_MAZE) { // || $res_choice == self::RC_MISFORTUNE) {
                        self::giveExtraTime( $player_id, 60 );
                        $this->gamestate->setPlayersMultiactive (array($player_id), 'blessing');
                        $this->gamestate->nextState('ressourceChoice');
                        return ;
                    }
                    // if player has a ship => enable only this user
                    //elseif ($this->hasUnusedShip($player_id)) {
                    elseif ($this->isRessourceChoice(self::RC_FORGESHIP, $player_id)) {
                        self::giveExtraTime( $player_id, 45 );
                        $this->gamestate->setPlayersMultiactive (array($player_id), 'blessing');
                        $this->gamestate->nextState('forgeShip');
                        return ;
                    }
                    elseif ($this->hasMazeStock($player_id)) {
                        //if (!$this->mazeManagement($player_id)) {
                        //    $this->gamestate->nextState('blessing');
                        //    return ;
                        //}
                        $this->mazeManagement($player_id);
                        //throw new feException($this->hasUnresolvedSides($player_id));
                        $this->gamestate->nextState('blessing');
                        return ;

                    }
                    elseif ($this->hasUnresolvedSides($player_id)) {
                                    //throw new feException("titi");
                        if ($players_info[$player_id]['side_choice_1'] != '0') {
                            $did = $this->blessing($player_id, true, false, 1, false, false);
                        }
                        if ($players_info[$player_id]['side_choice_2'] != '0') {
                            $did = $this->blessing($player_id, false, true, 1, false, false);
                        }
                        $this->setChoice($player_id, self::RC_RESSOURCE);
                        //throw new feException(print_r($this->getPlayersAdditionnalInfo()[$player_id]));
                        self::notifyAllPlayers("updateCounters", "", $this->getPlayersRessources());
                        $this->gamestate->nextState('blessing');
                        return ;
                    }
                    elseif ($this->misfortuneState() != 0) {

                        $toEnable = $this->misfortuneAllocation();

                        // allocate if choice ==> token

                        self::giveExtraTime( $toEnable, 45 );
                        $this->gamestate->setPlayersMultiactive (array($toEnable), 'blessing');
                        $this->tokens->moveToken('resolveMisfortune', 'none', 0);
                        $this->gamestate->nextState('misfortune');
                        return ;
                    }

                    $player_id = $this->getNextPlayerTable()[$player_id];
                }
            }
        }
        //elseif ($this->hasMazeStock()) {
        //    // Maze movement
        //    $player_id = self::getActivePlayerId();
        //
        //    for($i=1; $i<=$this->getPlayersNumber(); $i++) {
        //        if (!$this->mazeManagement($player_id)) {
        //            $this->gamestate->nextState('blessing');
        //            return ;
        //        }
        //        $player_id = $this->getNextPlayerTable()[$player_id];
        //    }
        //    $this->gamestate->nextState('blessing');
        //}
        elseif($nbThrows >= $diceThrows) {
            // enable multi ressource resolution
            $this->setGameStateValue( "monoRessourceChoice", 0);
            $this->setGameStateValue( "eternalFire", 0);
            $this->setGameStateValue( "celestialRunning", 0);
            $this->setGameStateValue("oracleReinforcement", 0);
            $this->setGameStateValue("firstFinish", 0);
            $this->resetThrowTokens();
            // update throw token
            $this->updateAllThrows();
            $this->resetTwins();
            $this->gamestate->nextState('reinforcement');
        }
        else {
            self::setGameStateValue('nbThrows', ++$nbThrows);
            $this->resetTwins();
            $this->resetThrowTokens();
            $this->setGameStateValue( "celestialRunning", 0);
            $this->setGameStateValue("oracleReinforcement", 0);
            $this->setGameStateValue("firstFinish", 0);

            if (!$eternalFire) {
                // roll of dice for each player, in turn order
                //foreach($this->loadPlayersBasicInfos() as $player_id => $players) {
                $player_id = self::getActivePlayerId();

                for($i=1; $i<=$this->getPlayersNumber(); $i++) {
                    $choice2 = $this->blessing ($player_id);
                    $player_id = $this->getNextPlayerTable()[$player_id];
                }
            }
            else {
                $choice2 = $this->blessing (self::getActivePlayerId());
            }

            self::notifyAllPlayers("updateCounters", "", $this->getPlayersRessources());

            // 2 players specific and first throw only
            if ( $diceThrows == 2 && $nbThrows == 1 )
                self::notifyAllPlayers("notifPauseDice", "", array());

            // test if there is a conflict - ship && mirror - on two different players
            if ($this->hasResolutionConflict())
                $this->setGameStateValue( "monoRessourceChoice", 1);

            $this->gamestate->nextState('blessing');
        }
        //throw new feException("stop");
    }

    // Activation of correct players
    function stRessourceChoice()
    {
        $stateName = $this->gamestate->state()['name'];
        $nextState = "";
        $endMultiState = "";

        // go to next State when no more active player
        // checks are done on the action + args
        if (count($this->gamestate->getActivePlayerList()) == 0) {
            //throw new feException("toto");
            $this->resetThrowTokens();
            switch ($stateName) {
                case 'ressourceChoice':
                    $endMultiState = 'blessing';
                    break;
                case 'doeRessourceChoice':
                    $endMultiState = 'reinforcement';
                    break;
                case 'playerOustingChoice':
                    $endMultiState = 'exploitEffect';
                    break;
                case 'exploitRessource':
                    $endMultiState = "nextState";
                    break;
            }
            $this->gamestate->nextState($endMultiState);
            return ;
        }

        $this->players_info = null;
        $players_info = $this->getPlayersAdditionnalInfo();
        $i = 0;

        foreach($players_info as $player_id => $players) {
            if ($players['ressource_choice'] == self::RC_NOTHING_TODO && $players['side_choice_1'] == "0" && $players['side_choice_2'] == "0") {
                $this->gamestate->setPlayerNonMultiactive($player_id, $endMultiState);
            }
        }

    }

    function stMisfortuneChoice() {
        // if nothing to choose => we disable and send back to initial state
        $side1 = $this->tokens->getTokenLocation('misfortune_1');
        $side2 = $this->tokens->getTokenLocation('misfortune_2');

        if ($side1 == 'none' && $side2 == 'none') {
            $transition = $this->tokens->getTokenLocation('stateMisfortune');
            $this->tokens->moveToken('stateMisfortune', 'none');
            $this->gamestate->nextState($transition);
        }
    }

    function stRessourceChoiceAdvanced($activeplayers =  null, $continue = false)
    {
        // there is a ressource Choice on one of the players
        if ($this->isRessourceChoice() && ($activeplayers == null || count($activeplayers) == 0)) {
            // disable all players
            self::DbQuery('UPDATE player SET player_is_multiactive = 0');
            // if action ressource to allocate or choice => ressource choice
            if ($this->isRessourceChoice(self::RC_RESSOURCE) || $this->isRessourceChoice(self::RC_ACTION_CHOICE) || $this->isRessourceChoice(self::RC_SIDE_CHOICE) || $this->isRessourceChoice(self::RC_MAZE) || $this->isRessourceChoice(self::RC_MISFORTUNE)) {
                // activation of players needging a choice
                $this->players_info = null;
                $players_info = $this->getPlayersAdditionnalInfo();
                $multi = array();
                $i = 0;

                foreach($players_info as $player_id => $players) {
                    if ($players['ressource_choice'] == self::RC_SIDE_CHOICE|| $players['ressource_choice'] == self::RC_RESSOURCE || $players['ressource_choice'] == self::RC_ACTION_CHOICE || $players['ressource_choice'] == self::RC_MAZE || $players['ressource_choice'] == self::RC_MISFORTUNE) {
                        $multi[$i++] = $player_id;
                        self::giveExtraTime( $player_id, 60 );
                    }
                }
                $this->gamestate->setPlayersMultiactive ($multi, 'choice');
                $this->gamestate->nextState('choice');
                return true;
            }
            // If ship needs to be managed
            elseif ($this->isRessourceChoice(self::RC_FORGESHIP)) {
            //if ($this->hasUnusedShip()) {
                $player_id = self::getActivePlayerId();
                for($i=1; $i<=$this->getPlayersNumber(); $i++) {
                    // if player has a ship => enable only this user
                    //if ($this->hasUnusedShip($player_id)) {
                    if ($this->getPlayersAdditionnalInfo()[$player_id]['ressource_choice'] == self::RC_FORGESHIP) {
                        self::giveExtraTime( $player_id, 45 );
                        $this->gamestate->setPlayersMultiactive (array($player_id), $this->getNextState());
                        $this->gamestate->nextState('forgeShip');
                        return true;
                    }
                    $player_id = $this->getNextPlayerTable()[$player_id];
                }
            }
            elseif ($this->hasMazeStock()) {
                $player_id = self::getActivePlayerId();
                for($i=1; $i<=$this->getPlayersNumber(); $i++) {
                    if ($this->tokens->getTokenState("mazestock_" . $player_id) != 0) {
                        $this->mazeManagement($player_id);
                        //throw new feException($this->hasUnresolvedSides($player_id));
                        $this->gamestate->nextState('nextState');
                        return true;
                    }
                    $player_id = $this->getNextPlayerTable()[$player_id];
                }

            }
            elseif ($this->hasUnresolvedSides()) {
                    $player_id = self::getActivePlayerId();
                    $players_info = $this->getPlayersAdditionnalInfo();
                    for($i=1; $i<=$this->getPlayersNumber(); $i++) {
                        if ($players_info[$player_id]['side_choice_1'] != '0') {
                            $did = $this->blessing($player_id, true, false, 1, false, false);
                            $this->setChoice($player_id, self::RC_RESSOURCE);
                        }
                        if ($players_info[$player_id]['side_choice_2'] != '0') {
                            $did = $this->blessing($player_id, false, true, 1, false, false);
                            $this->setChoice($player_id, self::RC_RESSOURCE);
                        }

                        $player_id = $this->getNextPlayerTable()[$player_id];
                    }
                    self::notifyAllPlayers("updateCounters", "", $this->getPlayersRessources());
                    $this->gamestate->nextState('nextState');
                    return true;
                }
            elseif ($continue) {
                $this->gamestate->nextState($this->getNextState());
                return true;
            }
        }
        elseif ($continue) {
            if ($this->hasMazeStock()) {
                $player_id = self::getActivePlayerId();
                for($i=1; $i<=$this->getPlayersNumber(); $i++) {
                    if ($this->tokens->getTokenState("mazestock_" . $player_id) != 0) {
                        $this->mazeManagement($player_id);
                        //throw new feException($this->hasUnresolvedSides($player_id));
                        $this->gamestate->nextState('nextState');
                        return true;
                    }
                    $player_id = $this->getNextPlayerTable()[$player_id];
                }

            }
            elseif ($this->hasUnresolvedSides()) {
                $player_id = self::getActivePlayerId();
                $players_info = $this->getPlayersAdditionnalInfo();
                for($i=1; $i<=$this->getPlayersNumber(); $i++) {
                    if ($players_info[$player_id]['side_choice_1'] != '0') {
                        $did = $this->blessing($player_id, true, false, 1, false, false);
                        $this->setChoice($player_id, self::RC_RESSOURCE);
                    }
                    if ($players_info[$player_id]['side_choice_2'] != '0') {
                        $did = $this->blessing($player_id, false, true, 1, false, false);
                        $this->setChoice($player_id, self::RC_RESSOURCE);
                    }
                    $player_id = $this->getNextPlayerTable()[$player_id];
                }
                self::notifyAllPlayers("updateCounters", "", $this->getPlayersRessources());
                $this->gamestate->nextState('nextState');
                return true;
            }
            $this->gamestate->nextState($this->getNextState());
            return true;
        }
        return false;
    }

    function stReinforcement()
    {
        $player_id = $this->getGameStateValue( "turnPlayerId" );
        $players_info = $this->getPlayersAdditionnalInfo();

        // if players need to choose, go to correct state
        if ($this->isRessourceChoice()) {
            $continue = $this->stRessourceChoiceAdvanced(null,false);
            if ($continue)
                return ;
        }
        elseif ($this->hasMazeStock($player_id)) {
            // Maze movement
            if (!$this->mazeManagement($player_id)) {
                $this->gamestate->nextState('reinforcement');
                return ;
            }
        }
        elseif ($this->misfortuneState() != 0) {

            $toEnable = $this->misfortuneAllocation();

            // allocate if choice ==> token

            self::giveExtraTime( $toEnable, 45 );
            $this->gamestate->setPlayersMultiactive (array($toEnable), 'blessing');
            $this->tokens->moveToken('resolveMisfortune', 'none', 0);
            $this->gamestate->nextState('misfortune');
            return ;
        }

        $this->setGameStateValue( "enigmaDieNumber", -1);
        $reinforcementToPlay = false;
        $this->setGameStateValue("oracleReinforcement", 0);
        $this->setGameStateValue("firstFinish", 0);
        $this->resetTwins();

         //$test = "";
        // have all the reinforcement cards have been played?
        //throw new feException(print_r($this->exploits->getCardsInLocation( "pile3-".$player_id, null, "card_type_arg")));
        foreach ($this->exploits->getCardsInLocation( "pile3-".$player_id, null, "card_type_arg") as $card_id => $reinforcement) {
            if ($reinforcement['type_arg'] == '0' && $reinforcement['type'] == 'companion' and $this->tokens->getTokenState("companion_" . $reinforcement['id']) < 5) {
                $this->actReinforcement($reinforcement['id'], null, null, null, true);
                // return as actReinforcement triggers a change of state
                return ;
            }

            //$test .= $reinforcement['id'] . " ";
            //if ($reinforcement['type_arg'] == '0' && ((($reinforcement['type'] == 'ancient' || $reinforcement['type'] == 'light') && $players_info[$player_id]['res_gold']>= 3) || ($reinforcement['type'] != 'ancient')))
            if ($reinforcement['type_arg'] == '0' && ((($reinforcement['type'] == 'ancient' || $reinforcement['type'] == 'light') && ($this->getGold($player_id)>= 3 || $this->hasTritonToken($player_id))) || ($reinforcement['type'] != 'ancient'))) {
                $reinforcementToPlay = true;
            }
        }

        if (!$reinforcementToPlay)
            $this->gamestate->nextState("playerAction");
    }

    // for the ousted player, divine blessing
    function stOusting()
    {
        $player_id = $this->getGameStateValue('oustedPlayerId');
        $this->setGameStateValue("oracleReinforcement", 0);

        if ($this->misfortuneState() != 0) {

            $toEnable = $this->misfortuneAllocation();

            // allocate if choice ==> token

            self::giveExtraTime( $toEnable, 45 );
            $this->gamestate->setPlayersMultiactive (array($toEnable), 'blessing');
            $this->tokens->moveToken('resolveMisfortune', 'none', 0);
            $this->gamestate->nextState('misfortune');
            return ;
        }
        elseif ($player_id == -1)
            $this->stRessourceChoiceAdvanced($this->gamestate->getActivePlayerList(), true);
        else {
            $choice = $this->blessing($player_id);
            $act = $this->setChoice($player_id, self::RC_RESSOURCE);

            if ($act == self::RC_NOTHING_TODO && $this->hasMazeStock($player_id))
                $this->mazeManagement($player_id);

            $player_info = $this->getPlayersAdditionnalInfo()[$player_id];
            //throw new feException (print_r($player_info));
            if ($player_info['side_choice_1'] != '0' && $player_info['side_choice_2'] != '0') {
                $hasGold = false;
                $hasTitan = false;

                for ($i = 1; $i <= 2; $i++) {
                    $side_definition = $this->dice_sides[$player_info['side_choice_' . $i]];
                    if (isset($side_definition['ressource']['gold']) && $side_definition['ressource']['gold'] != 0)
                        $hasGold = true;

                    if (isset($side_definition['ressource']['loyalty']) && $side_definition['ressource']['loyalty'] != 0)
                        $hasTitan = true;

                    if (isset($side_definition['ressource']['ancientshard']) && $side_definition['ressource']['ancientshard'] != 0)
                        $hasTitan = true;
                }

                if ($hasGold && $hasTitan) {
                    $this->dbSetChoice($player_id, self::RC_ACTION_CHOICE);
                }
            }

            self::notifyAllPlayers("updateCounters", "", $this->getPlayersRessources());

            $this->setGameStateValue('oustedPlayerId', -1);

            $player_info = $this->getPlayersAdditionnalInfo()[$player_id];

            if($player_info['ressource_choice'] == self::RC_RESSOURCE || $player_info['ressource_choice'] == self::RC_SIDE_CHOICE || $player_info['ressource_choice'] == self::RC_MAZE ||
                    $player_info['ressource_choice'] == self::RC_ACTION_CHOICE || $player_info['ressource_choice'] == self::RC_MISFORTUNE || ($player_info['ressource_choice'] != self::RC_FORGESHIP && $this->hasCerberusToken($player_id))) {
                $multi = array();
                $i = 0;

                // only one player should be set as multiactive
                $multi[0] = $player_id;
                self::giveExtraTime( $player_id, 60 );

                $this->gamestate->setPlayersMultiactive ($multi, 'exploitEffect');
                $this->gamestate->nextState('choice');
            }
            elseif ($player_info['ressource_choice'] == self::RC_FORGESHIP) {
                $multi = array();
                $i = 0;

                // only one player should be set as multiactive
                $multi[0] = $player_id;
                self::giveExtraTime( $player_id, 60 );

                $this->gamestate->setPlayersMultiactive ($multi, 'exploitEffect');
                $this->gamestate->nextState('forgeShip');
            }
            else
                //$this->gamestate->nextState('exploitEffect');
                if ($this->tokens->getTokenState('pegasus_remaining') != 0) {
                    $this->gamestate->nextState('pegasus');
                } else {
                    $this->gamestate->nextState('nextState');
                }
        }
    }

    // Switch of user that owns the Pegasus
    function stPegasusChange() {
        $player_id = $this->getGameStateValue('oustedPlayerId');
        $this->setGameStateValue("oracleReinforcement", 0);
        self::giveExtraTime( [$player_id], 45 );
        $this->gamestate->setPlayersMultiactive ([$player_id], 'stOusting');
        $this->gamestate->nextState('nextState');
    }

    function argsPegasusIsland() {
        $islands = [1,2,3,4,5,6,7];

        return ['islands' => array_values(array_diff($islands, self::getObjectListFromDB("SELECT position FROM player WHERE position != 'begin'", true)))];
    }

    function actPegasusIsland($island) {
        self::checkAction('actPegasusIsland');
        $player_id = self::getCurrentPlayerId();
        $args = $this->argsPegasusIsland();

        if (!in_array($island, $args['islands'])) {
            throw new \BgaVisibleSystemException('The island is occupied. Should not happen');
        }

        $this->dbSetPosition ($player_id, $island);
        $playerInfo = $this->loadPlayersBasicInfos()[$player_id];

        self::notifyAllPlayers("notifMovePawn", clienttranslate('Pegasus: ${player_name} moves to island ${island}'),
            array(
                'player_color' => $playerInfo['player_color'],
                'player_name' => $playerInfo['player_name'],
                'island'       => $island
            )
        );
        $card_id = $this->getGameStateValue( "exploitBought");

        if ($card_id != -1) {
            $card = $this->exploits->getCard($card_id);
            if ($card['type'] == 'leftHand') {
                $this->gamestate->nextState('minor');
                return ;
            }
        }
        $this->gamestate->nextState('ousting');
    }

    function stPegasusMinor() {
        $player_id = self::getCurrentPlayerId();
        // if players need to choose, go to correct state
        if ($this->isRessourceChoice()) {
            $continue = $this->stRessourceChoiceAdvanced(null,false);
            if ($continue)
                return ;
        }
        elseif ($this->hasMazeStock($player_id)) {
            // Maze movement
            if (!$this->mazeManagement($player_id)) {
                $this->gamestate->nextState('pegasus');
                return ;
            }
        }
        elseif ($this->misfortuneState() != 0) {

            $toEnable = $this->misfortuneAllocation();

            // allocate if choice ==> token

            self::giveExtraTime( $toEnable, 45 );
            $this->gamestate->setPlayersMultiactive (array($toEnable), 'blessing');
            $this->tokens->moveToken('resolveMisfortune', 'none', 0);
            $this->gamestate->nextState('misfortune');
            return ;
        } elseif ($this->tokens->getTokenState('pegasus_remaining') == 0) {
            $this->tokens->setTokenState('pegasus_player', 0);
            $card_id = $this->getGameStateValue( "exploitBought");
            if ($card_id != -1) {
                $card = $this->exploits->getCard($card_id);
                if ($card['type'] == 'leftHand') {
                    $this->gamestate->nextState('leftHand');
                    return ;
                }
            }
            $this->gamestate->nextState('ousting');
            return ;
        } elseif (count($this->gamestate->getActivePlayerList()) == 0) {
            $this->gamestate->setPlayersMultiactive ([$this->tokens->getTokenState('pegasus_player')], 'nextState');
            $this->gamestate->nextState('nextState');
        }

    }

    function argsPegasusMinor() {
        return ['canDo'=>$this->tokens->getTokenState('pegasus_remaining') != 0];
    }

    function actPegasusMinor($dice) {
        self::checkAction( "actPegasusMinor");
        $player_id         = self::getCurrentPlayerId();
        $this->resetThrowTokens();
        $this->resetTwins();
        $dice1 = false;
        $dice2 = false;

        $this->tokens->incTokenState('pegasus_remaining', -1);

        if ($dice == 1) {
            $dice1 = true;
            $this->setGameStateValue('enigmaDieNumber', 1);
        }
        else {
            $dice2 = true;
            $this->setGameStateValue('enigmaDieNumber', 2);
        }

        $choice = $this->blessing($player_id, $dice1, $dice2);
        self::notifyAllPlayers("updateCounters", "", $this->getPlayersRessources());
        $this->gamestate->nextState('nextState');
    }

    //function stExploitRessource() {
    //    $celestialRunning = $this->getGameStateValue( "celestialRunning");
    //    $player_id = self::getActivePlayerId();
    //
    //    if ($celestialRunning) {
    //        if ($this->getCelestial() == "doubleUpgrade")
    //            $this->dbSetChoice($player_id, self::RC_NOTHING_TODO);
    //            $this->gamestate->nextState("nextState");
    //    }
    //}

    // Generate Args for JS for forging
    // If no need for user input=> do the actions
    // else wait for action
    function stEffectExploit()
    {
        $player_id = self::getActivePlayerId();
        $forge = false;
        $ressourceChoice = false;
        $notifPlayerArgs = array();
        $newTurn = false;
        $goAgain = false;
        $pegasus = false;
        $this->setGameStateValue("oracleReinforcement", 0);
        // throw new \feException(print_r(debug_print_backtrace()));

        $card_id = $this->getGameStateValue( "exploitBought");
        $remainingThrows = $this->getGameStateValue( "exploitRemainingThrows");
        $celestialRunning = $this->getGameStateValue( "celestialRunning");
        $players_info = $this->getPlayersAdditionnalInfo();

        if ($this->isRessourceChoice()) {
            $continue = $this->stRessourceChoiceAdvanced(null, false);
            if ($continue)
                return ;
        }
        elseif ($this->hasMazeStock($player_id)) {
            // Maze movement
            if (!$this->mazeManagement($player_id)) {
                $this->gamestate->nextState("exploitEffect");
                return ;
            }
            //throw new feException ($this->hasMazeStock($player_id));
            //$this->gamestate->nextState("exploitEffect");
            //return ;

        }
        //elseif ($this->hasUnresolvedSides($player_id)) {
        //    $toTransform = $this->getRessourcesToTransform();
        //    if ($players_info[$player_id]['side_choice_1'] != '0') {
        //        if (count(array_intersect(array_keys($this->dice_sides[$players_info[$player_id]['side_choice_1']]['ressource']), $toTransform['ressource'])) != 0) {
        //            $this->setChoice($player_id, self::RC_RESSOURCE);
        //        }
        //        else {
        //            $did = $this->blessing($player_id, true, false, 1, false, false);
        //            $this->setChoice($player_id, self::RC_RESSOURCE);
        //        }
        //    }
        //    if ($players_info[$player_id]['side_choice_2'] != '0') {
        //        if (count(array_intersect(array_keys($this->dice_sides[$players_info[$player_id]['side_choice_2']]['ressource']), $toTransform['ressource'])) != 0) {
        //            $this->setChoice($player_id, self::RC_RESSOURCE);
        //        }
        //        else {
        //            $did = $this->blessing($player_id, false, true, 1, false, false);
        //            $this->setChoice($player_id, self::RC_RESSOURCE);
        //        }
        //    }
        //    //$this->setChoice($player_id, null);
        //    $this->gamestate->nextState('nextState');
        //    return ;
        //}
        // #32238
        elseif ($this->hasUnresolvedSides()) {
            $cur_player_id = $player_id;
            $players_info = $this->getPlayersAdditionnalInfo();
            for($i=1; $i<=$this->getPlayersNumber(); $i++) {
                $toTransform = $this->getRessourcesToTransform();
                if ($players_info[$cur_player_id]['side_choice_1'] != '0') {
                    if (count(array_intersect(array_keys($this->dice_sides[$players_info[$cur_player_id]['side_choice_1']]['ressource']), $toTransform['ressource'])) != 0) {
                        $this->setChoice($cur_player_id, self::RC_RESSOURCE);
                    }
                    else {
                        $did = $this->blessing($cur_player_id, true, false, 1, false, false);
                        $this->setChoice($cur_player_id, self::RC_RESSOURCE);
                    }
                }
                if ($players_info[$cur_player_id]['side_choice_2'] != '0') {
                    if (count(array_intersect(array_keys($this->dice_sides[$players_info[$cur_player_id]['side_choice_2']]['ressource']), $toTransform['ressource'])) != 0) {
                        $this->setChoice($cur_player_id, self::RC_RESSOURCE);
                    }
                    else {
                        $did = $this->blessing($cur_player_id, false, true, 1, false, false);
                        $this->setChoice($cur_player_id, self::RC_RESSOURCE);
                    }
                }
                $cur_player_id = $this->getNextPlayerTable()[$cur_player_id];
                //$this->setChoice($player_id, null);
            }
            $this->gamestate->nextState('nextState');
            return ;
        }
        elseif ($this->misfortuneState() != 0) {

            $toEnable = $this->misfortuneAllocation();

            // allocate if choice ==> token

            self::giveExtraTime( $toEnable, 45 );
            $this->gamestate->setPlayersMultiactive (array($toEnable), 'nextState');
            $this->tokens->moveToken('resolveMisfortune', 'none', 0);
            $this->gamestate->nextState('misfortune');
            return ;
        } elseif ($this->tokens->getTokenState('pegasus_remaining') != 0) {
            $this->gamestate->nextState('pegasus');
            return ;
        }
        elseif ($card_id != -1 && $remainingThrows > 0 && !$celestialRunning) {
            $this->resetThrowTokens();
            $this->setGameStateValue("firstFinish", 0);
            $card = $this->exploits->getCard($card_id);
            $card_info = $this->exploit_types[$card['type']];

            $this->resetTwins(null, true);

            // Something needs to be done only for immediate
            if ($card_info['actionType'] == 'immediate' || $card_info['actionType'] == 'recurrent') {
                switch ($card_info['action']) {
                    case 'initHammer':
                        // Addition of the hamer
                        $this->dbInitHammer($player_id);
                        // if ($this->hasActiveHammer($player_id) == 1)
                        //     self::notifyAllPlayers("notifInitHammer", '',  array('player_id' => $player_id));
                        break;

                    case 'increaseResLimit':
                        // Nothing to do
                        break;

                    case 'steal2':
                        // All the other players throw
                        // The player select 2 sides + ressource (if choice)
                        // roll of all the dice (except active player)
                        self::notifyAllPlayers("notifEffectSatyres", clienttranslate('${player_name} uses the ${card_name_trans} effect'),
                            array(
                                'i18n'        => array('card_name_trans'),
                                'player_name' => self::getActivePlayerName(),
                                'card_name'   => $card_info['name'],
                                'card_name_trans'   => $card_info['name'],
                            )
                        );

                        foreach($this->loadPlayersBasicInfos() as $all_player_id => $players) {
                            if ($player_id != $all_player_id) {
                                $notifPlayerArgs['player_id'] = $all_player_id;
                                $side = $this->rollDice($all_player_id, 1);
                                $notifPlayerArgs['dice1'] = $side['type'];
                                $side = $this->rollDice($all_player_id, 2);
                                $notifPlayerArgs['dice2'] = $side['type'];
                                $notifPlayerArgs['roll'] = true;

                                self::notifyAllPlayers("notifBlessing", "", $notifPlayerArgs);
                            }

                        }
                        $this->dbSetSideChoice($player_id, 1, -1);
                        $this->dbSetSideChoice($player_id, 2, -1);
                        $this->dbSetChoice($player_id, self::RC_SIDE_CHOICE);
                        $ressourceChoice = true;
                        break;

                    case 'side3x':        // Forge of a side x3
                    case 'sideMirror':    // Forge of a side mirror
                    case 'sideShip':      // Forge a celestial ship
                    case 'boarForge':     // Forge a boar side
                    case 'shieldForge':   // Forge a guardian shield side
                    case 'sideMoonGolem': // Forge a moon golem side
                    case 'sideSunGolem':  // Forge a sun golem side
                    case 'sideDogged': // forge a dogged side
                    case 'sideShieldRebellion': // forge a shield for titan
                    case 'sideMisfortune': // for misfortune side
                        $forge = true;
                        break;

                    case 'fullThrow2':
                        // Active player generates twice the blessing
                        // $ressourceChoice = true; or false :)
                        $this->resetTwins();
                        $ressourceChoice = $this->blessing($player_id, true, true, 1, true);
                        //$this->debugVTO($player_id);
                        break;

                    case '3G3M':
                        // addition of 3G and 3MS
                        $this->increaseMoonShard($player_id, 3);
                        if ($this->canFillHammer($player_id)) {
                            $ressourceChoice = true;
                            $this->dbSetChoice($player_id, 1);
                            $this->dbSetSideChoice($player_id, 1,'G3');
                        }
                        else {
                            $scepter = $this->increaseGold($player_id, 3, 'scepter');

                            $this->increaseGold($player_id, 3 - $scepter);
                        }

                        $notifPlayerArgs = [];
                        $notifPlayerArgs['player_id']   = $player_id;
                        $notifPlayerArgs['player_name'] = $this->loadPlayersBasicInfos()[$player_id]['player_name'];
                        $notifPlayerArgs['moonshard'] = 3;
                        if (!$this->canFillHammer($player_id)) {
                            $notifPlayerArgs['gold'] = 3 - $scepter;
                            $notifPlayerArgs['scepter'] = $scepter;
                        }
                        $notifPlayerArgs['ressources'] = $this->buildRessourceNotif( $notifPlayerArgs );
                        self::notifyAllPlayers("notifBlessing", '${player_name} gets ${ressources}', $notifPlayerArgs );

                        break;

                    case 'looseThrow':
                        // All the other players throw
                        // The players loose the ressources (+ choice of ressource)
                        // all must be allocated
						// TODO: no bonus
                        foreach($this->loadPlayersBasicInfos() as $all_player_id => $players) {
                            if ($player_id != $all_player_id) {
                                $res = $this->blessing($all_player_id, true, true, -1);
                            }
                        }
                        break;



                    case '4Throws':
                        //#34904
                        //$this->resetTwins(null, true);
                        $this->resetTwins();
                        // The user select a die
                        // Die is launched 4 times
                        $die_number =  $this->getGameStateValue( "enigmaDieNumber");
                        if ($die_number == 1)
                            $ressourceChoice = $this->blessing($player_id, true, false, 1, true);
                        elseif ($die_number == 2)
                            $ressourceChoice = $this->blessing($player_id, false, true, 1, true);
                        else
                            // action necessary
                            $forge = true;
                        break;

                    case 'scoreForgedSides':
                        $this->scoreForgedSides($player_id);
                        //$discardedSides = count($this->sides->getCardsInLocation('discard', $player_id));
                        //$this->increaseVP($player_id, $discardedSides);
                        //$this->incStat($discardedSides, 'nb_vp_typhon', $player_id);
                        //self::notifyAllPlayers("notifEffectSatyres", clienttranslate('${player_name} has forged ${discardedSides} sides therefore gains ${ressources}'),
                        //    array(
                        //        'player_name' => self::getActivePlayerName(),
                        //        'discardedSides'   => $discardedSides,
                        //        'ressources'    => $discardedSides . ' [VP]'
                        //    )
                        //);

                        break ;
                    case '4ThrowsTransform':
                        //#34904
                        //$this->resetTwins(null, true);
                        $this->resetTwins();
                        // The user select a die
                        // Die is launched 4 times
                        $die_number =  $this->getGameStateValue( "enigmaDieNumber");
                        if ($die_number == 1) {
                            $ressourceChoice = $this->blessing($player_id, true, false, 1, true);
                            //$this->debugVTO($player_id);
                        }
                        elseif ($die_number == 2)
                            $ressourceChoice = $this->blessing($player_id, false, true, 1, true);
                        else
                            // action necessary
                            $forge = true;
                        break;
                        break ;
                    case 'tokenTriton':
                        $this->dbIncTriton($player_id);

                        self::notifyAllPlayers("notifAddToken", clienttranslate('${player_name} gains a Triton token'),
                            array(
                                'player_name' => self::getActivePlayerName(),
                                'player_id' => $player_id,
                                'token'         => 'triton',
                                'card_id'       => $card_id,
                                "power"       => $card['type']
                            )
                        );
                        break;

                    case 'fullThrow2Transform':
                        //#34904
                        //$this->resetTwins(null, true);
                        $this->resetTwins();
                        // launch of blessing
                        // force choice if MS or FS
                        $ressourceChoice = $this->blessing($player_id, true, true, 1, true);
                        break ;
                    case 'tokenCerberus':
                        $this->dbIncCerberus($player_id);

                        self::notifyAllPlayers("notifAddToken", clienttranslate('${player_name} gains a Cerberus token'),
                            array(
                                'player_name' => self::getActivePlayerName(),
                                'player_id' => $player_id,
                                'token'         => 'cerberus',
                                'card_id'       => $card_id,
                                "power"       => $card['type']
                            )
                        );
                        break ;

                    case 'diceSwap':
                        self::incGameStateValue('magicSeagullCount', 1);
                        $magicActive = self::getGameStateValue('magicSeagullActive');
                        if ($magicActive == 0) {
                            self::notifyAllPlayers("notifMessage", clienttranslate('On the next turn, dice will be swapped!'),
                                array()
                                );
                        }
                        else
                        {
                            self::notifyAllPlayers("notifMessage", clienttranslate('Dice will stay on this position for one more turn'),
                                    array()
                                    );
                        }
                        break;
                    // Rebellion
                    case 'initCompanion':
                        // token if put on the player and init to 0
                        $this->tokens->moveToken('companion_' . $card_id, $player_id, 0);
                        break ;
                    case 'initScepter':
                        // token if put on the player and init to 0
                        $this->tokens->moveToken('scepter_' . $card_id, $player_id, 0);
                        self::notifyAllPlayers("notifAddTokenScepter", '',
                            array(
                                'player_name' => self::getActivePlayerName(),
                                'player_id' => $player_id,
                                'token'         => 'scepter',
                                'card_id'       => $card_id,
                                "power"       => $card['type']
                            )
                        );
                        break ;
                    case 'throwAllChooseResources':
                        //wind
                        self::notifyAllPlayers("notifEffect", clienttranslate('${player_name} uses the ${card_name_trans} effect'),
                            array(
                                'i18n'        => array('card_name_trans'),
                                'player_name' => self::getActivePlayerName(),
                                'card_name'   => $card_info['name'],
                                'card_name_trans'   => $card_info['name'],
                            )
                        );

                        foreach($this->loadPlayersBasicInfos() as $all_player_id => $players) {
                            $notifPlayerArgs['player_id'] = $all_player_id;
                            $side = $this->rollDice($all_player_id, 1);
                            $notifPlayerArgs['dice1'] = $side['type'];
                            $side = $this->rollDice($all_player_id, 2);
                            $notifPlayerArgs['dice2'] = $side['type'];
                            $notifPlayerArgs['roll'] = true;

                            self::notifyAllPlayers("notifBlessing", "", $notifPlayerArgs);

                        }
                        // player has to choose the resource
                        $this->dbSetChoice($player_id, self::RC_RESSOURCE);
                        $ressourceChoice = true;
                        break ;
                    case 'throwCelestialDie':
                        $this->resetTwins();
                        $side = $this->rollCelestial($player_id);

                        if (in_array($side, array ("doubleUpgrade"))) {
                            $remainingThrows++;
                            $goAgain = true;
                            $this->dbSetChoice($player_id, self::RC_FORGESHIP);
                            //$this->incGameStateValue("exploitRemainingThrows", 1);
                            //$this->dbSetChoice($player_id, self::RC_RESSOURCE);
                            //$ressourceChoice = true;
                        }
                        //    $forge = true;
                        break ;
                    case 'forgeVP':
                        // ancestor
                        $this->resetTwins(null, true);
                        // The user select a die if no forge else automatically put during the forge
                        $die_number =  $this->getGameStateValue( "enigmaDieNumber");

                        if ($die_number == 1 || $die_number == 2) {
                            self::notifyAllPlayers("notifEffect", clienttranslate('${player_name} gains a minor blessing due to ${card_name_trans} effect'),
                                    array(
                                        'i18n'        => array('card_name_trans'),
                                        'player_name' => $players_info[$player_id]['player_name'],
                                        'card_name'   => $card_info['name'],
                                        'card_name_trans'   => $card_info['name']
                                    )
                                );
                        }

                        if ($die_number == 1) {
                            $ressourceChoice = $this->blessing($player_id, true, false, 1, true);
                            $remainingThrows = 1;
                        }
                        elseif ($die_number == 2) {
                            $ressourceChoice = $this->blessing($player_id, false, true, 1, true);
                            $remainingThrows = 1;
                        }
                        else
                            // action necessary
                            $forge = true;

                        break ;
                    case 'stealLessGold':
                        //mists
                        // steal VP from player(s) with the less gold
                        $players_info = $this->getPlayersAdditionnalInfo();
                        $vp = 0;
                        $min_gold = -1;
                        $sql = "select player_id, sum(gold) gold from ( select player_id, res_gold gold from player union all select token_location player_id, token_state gold from token where token_key like 'scepter%' and token_location != 'deck') aa  group by player_id ORDER BY sum(gold) ASC";
                        $players = self::getObjectListFromDB($sql);
                        foreach ($players as $aff_player_id => $player) {
                            if ($min_gold == -1)
                                $min_gold = $player['gold'];

                            if ($min_gold != $player['gold'])
                                break ;

                            $aff_player_id = $player['player_id'];

                            $av_vp = $players_info[$aff_player_id]['player_score'];
                            $reduce = 0;

                            if ($av_vp < 5) {
                                $vp += $av_vp;
                                $this->decreaseVP($aff_player_id, $av_vp);
                                $reduce = $av_vp;
                            }
                            elseif ($av_vp != 0) {
                                $vp += 5;
                                $this->decreaseVP($aff_player_id, 5);
                                $reduce = 5;
                            }

                            if ($reduce != 0) {
                                self::notifyAllPlayers("notifEffect", clienttranslate('${player_name} looses ${ressources} due to ${card_name_trans} effect'),
                                    array(
                                        'i18n'        => array('card_name_trans'),
                                        'player_name' => $players_info[$aff_player_id]['player_name'],
                                        'card_name'   => $card_info['name'],
                                        'card_name_trans'   => $card_info['name'],
                                        'ressources' => $this->buildRessourceNotif(array ('vp' => $reduce))
                                    )
                                );
                            }
                        }

                        if ($vp != 0) {
                            $this->increaseVP($player_id, $vp);
                            self::notifyAllPlayers("notifEffect", clienttranslate('${player_name} gains ${ressources} due to ${card_name_trans} effect'),
                                array(
                                    'i18n'        => array('card_name_trans'),
                                    'player_name' => $players_info[$player_id]['player_name'],
                                    'card_name'   => $card_info['name'],
                                    'card_name_trans'   => $card_info['name'],
                                    'ressources' => $this->buildRessourceNotif(array ('vp' => $vp))
                                )
                            );
                        }


                        break ;
                    case 'stealFireMoon':
                        // eternal night
                        $players_info = $this->getPlayersAdditionnalInfo();
                        $gain['fireshard'] = 0;
                        $gain['moonshard'] = 0;
                        foreach ($players_info as $aff_player_id => $player_info) {
                            if ($player_id != $aff_player_id) {
                                $lost = array();
                                if ($player_info['res_fire'] >= 1) {
                                    $this->decreaseFireShard($aff_player_id, 1);
                                    $lost['fireshard'] = 1;
                                    $gain['fireshard'] += 1;
                                }

                                if ($player_info['res_moon'] >= 1) {
                                    $this->decreaseMoonShard($aff_player_id, 1);
                                    $lost['moonshard'] = 1;
                                    $gain['moonshard'] += 1;
                                }

                                if (count($lost) != 0) {
                                    self::notifyAllPlayers("notifEffect", clienttranslate('${player_name} looses ${ressources} due to ${card_name_trans} effect'),
                                        array(
                                            'i18n'        => array('card_name_trans'),
                                            'player_name' => $player_info['player_name'],
                                            'card_name'   => $card_info['name'],
                                            'card_name_trans'   => $card_info['name'],
                                            'ressources' => $this->buildRessourceNotif($lost)
                                        )
                                    );
                                }
                            }
                        }

                        if ($gain['fireshard'] != 0 || $gain['moonshard'] != 0) {
                            $this->increaseFireShard($player_id, $gain['fireshard']);
                            $this->increaseMoonShard($player_id, $gain['moonshard']);
                            self::notifyAllPlayers("notifEffect", clienttranslate('${player_name} gains ${ressources} due to ${card_name_trans} effect'),
                                array(
                                    'i18n'        => array('card_name_trans'),
                                    'player_name' => $players_info[$player_id]['player_name'],
                                    'card_name'   => $card_info['name'],
                                    'card_name_trans'   => $card_info['name'],
                                    'ressources' => $this->buildRessourceNotif($gain)
                                )
                            );
                        }

                        break ;
                    case 'convertGoldToVP':
                        // right hand
                        // #35764 replace of getgoldResource to getGold
                        if ($this->getGold($player_id) > 0) {
                            $this->dbSetChoice($player_id, self::RC_RESSOURCE);
                            $ressourceChoice = true;
                        }
                        break ;
                    case 'forge4G':
                        // wood nymph
                        if ($remainingThrows == 1) {
                            // add 4G (choice hammer / scepter)
                            if ($this->canFillHammer($player_id)) {
                                $ressourceChoice = true;
                                $this->dbSetChoice($player_id, 1);
                                $this->dbSetSideChoice($player_id, 1,'G4');
                                $remainingThrows = 100;
                                break ;
                            }
                            else {
                                $scepter = $this->increaseGold($player_id, 4, 'scepter');

                                $this->increaseGold($player_id, 4 - $scepter);
                                $notifPlayerArgs = $this->initNotif($player_id);
                                $notifPlayerArgs['gold'] = 4 - $scepter;
                                $notifPlayerArgs['scepter'] = $scepter;
                                if ( $notifPlayerArgs['vp'] == 0 )
                                {
                                    unset( $notifPlayerArgs['vp'] );
                                }
                                $notifPlayerArgs['ressources'] = $this->buildRessourceNotif( $notifPlayerArgs );
                                self::notifyAllPlayers("notifBlessing", '${player_name} gets ${ressources}', $notifPlayerArgs );
                            }
                        }
                        // trigger forge
                        $remainingThrows = 1;
                        $forge = true;
                        break ;
                    case 'countFeats':
                        // goldsmith
                        $sql = "SELECT COUNT(DISTINCT card_type) FROM exploit WHERE card_location LIKE '%-$player_id'";
                        $nbFeats = $this->getUniqueValueFromDB($sql);

                        $this->increaseVP($player_id, $nbFeats * 2);

                        $notifPlayerArgs = $this->initNotif($player_id);
                        $notifPlayerArgs['vp'] = $nbFeats * 2;
                        $notifPlayerArgs['ressources'] = $this->buildRessourceNotif( $notifPlayerArgs );
                        $notifPlayerArgs['nbFeats'] = $nbFeats;
                        self::notifyAllPlayers("notifBlessing", clienttranslate('${player_name} gets ${ressources} (${nbFeats} exploit(s))'), $notifPlayerArgs );
                        break ;
                    case 'countVP':
                        // omniscient
                        $nbSide = 0;

                        for ($num = 1; $num <=2; $num++) {
                            foreach ($this->sides->getCardsInLocation('dice' . $num . '-p' . $player_id) as $id => $side) {
                                $side_info = $this->dice_sides[$side['type']];

                                if (isset($side_info['ressource']['vp']) && $side_info['ressource']['vp'] != 0)
                                    $nbSide++;
                                elseif (isset($side_info['gainA']['vp']) && $side_info['gainA']['vp'] != 0)
                                    $nbSide++;
                            }
                        }
                        if ($nbSide != 0) {
                            $this->increaseVP($player_id, $nbSide * 2);
                        }

                        $notifPlayerArgs = $this->initNotif($player_id);
                        $notifPlayerArgs['vp'] = $nbSide * 2;
                        $notifPlayerArgs['ressources'] = $this->buildRessourceNotif( $notifPlayerArgs );
                        $notifPlayerArgs['nbSide'] = $nbSide;
                        $notifPlayerArgs['vp'] = '[VP]';
                        self::notifyAllPlayers("notifBlessing", '${player_name} gets ${ressources} (${nbSide} die face(s) with ${vp})', $notifPlayerArgs );

                        break ;
                    case 'forgeEverywhere':
                        //trident
                        // loose all gold
                        $gold = $this->getPlayersAdditionnalInfo()[$player_id]['res_gold'];
                        $this->decreaseGold($player_id, $gold);

                        $notifPlayerArgs = $this->initNotif($player_id);
                        $notifPlayerArgs['gold'] = $gold;
                        $notifPlayerArgs['vp'] = null;
                        $notifPlayerArgs['ressources'] = $this->buildRessourceNotif( $notifPlayerArgs );
                        self::notifyAllPlayers("notifBlessing", clienttranslate('${player_name} uses ${ressources} to forge any die face'), $notifPlayerArgs );
                        // trigger the forge
                        $forge = true;
                        break ;
                    case 'gainTurn':
                        // eternal Fire
                        self::notifyAllPlayers("notifEffect", clienttranslate('${player_name} gets a new turn due to ${card_name_trans} effect'),
                                array(
                                    'i18n'        => array('card_name_trans'),
                                    'player_name' => self::getActivePlayerName(),
                                    'card_name'   => $card_info['name'],
                                    'card_name_trans'   => $card_info['name'],
                                )
                            );
                        $this->setGameStateValue( "eternalFire", 1);
                        $newTurn = true;
                        break ;
                    case 'oustAll':

                        $hasOusted = $this->getGameStateValue('hasOusted');
                        $tableOrder = $this->getNextPlayerTable();
                        $old_ousted = $this->getGameStateValue('oustedPlayerId');
                        $players_info = $this->getPlayersAdditionnalInfo();


                        // only ousted players roll dice
                        // => current player
                        if ($old_ousted == '-1') {
                            $players = $players_info[$player_id];
                            $this->resetTwins(null, true);
                        }
                        else
                            $players = $players_info[$tableOrder[$old_ousted]];


                        if ($old_ousted == '-1' || $tableOrder[$old_ousted] != $player_id) {
                            $all_player_id = $players['player_id'];

                            $this->setGameStateValue('oustedPlayerId', $all_player_id);
                            $remainingThrows = 2;

                            if ($players['position'] != 'begin') {

                                //left hand
                                self::notifyAllPlayers("notifEffect", clienttranslate('${player_name} uses the ${card_name_trans} effect. ${ousted_player_name} is ousted'),
                                    array(
                                        'i18n'        => array('card_name_trans'),
                                        'player_name' => self::getActivePlayerName(),
                                        'ousted_player'      => $players['player_color'],
                                        'ousted_player_name' => $players['player_name'],
                                        'card_name'   => $card_info['name'],
                                        'card_name_trans'   => $card_info['name'],
                                    )
                                );

                                // Bear management
                                if ($this->hasBear($player_id) != 0 && $hasOusted = "-1" && $all_player_id != $player_id) {
                                    $vp = $this->hasBear($player_id) * 3;
                                    $this->increaseVP($player_id, $vp);
                                    $this->incStat($vp, 'nb_vp_bear', $player_id);
                                    self::notifyAllPlayers("notifBlessing", clienttranslate('${player_name} gets ${ressources} from the Great Bear'), array(
                                                'player_name'   => $this->getActivePlayerName(),
                                                'ressources'    => $vp . ' [VP]') );

                                    $this->setGameStateValue('hasOusted', "1");
                                }

                                if ($this->hasBear($all_player_id) != 0 && $all_player_id != $player_id) {
                                    $vp = $this->hasBear($all_player_id) * 3;
                                    $this->increaseVP($all_player_id, $vp);
                                    $this->incStat($vp, 'nb_vp_bear', $all_player_id);
                                    self::notifyAllPlayers("notifBlessing", clienttranslate('${player_name} gets ${ressources} from the Great Bear'), array(
                                                'player_name'   => $players['player_name'],
                                                'ressources'    => $vp . ' [VP]') );
                                }

                                // pegasus management
                                if ($this->hasPegasus($all_player_id) && $all_player_id != $player_id) {
                                    $this->tokens->setTokenState('pegasus_remaining', $this->hasPegasus($all_player_id));
                                    $this->tokens->setTokenState('pegasus_player', $all_player_id);
                                    $pegasus = true;
                                }

                                // move ousted player
                                $this->dbSetPosition ($all_player_id, 'begin');

                                self::notifyAllPlayers("notifMovePawn", '',
                                    array(
                                        'player_color' => $this->loadPlayersBasicInfos()[$all_player_id]['player_color'],
                                        'island'       => 'init'
                                    )
                                );

                                $notifPlayerArgs['player_id'] = $all_player_id;
                                $side = $this->rollDice($all_player_id, 1);
                                $notifPlayerArgs['dice1'] = $side['type'];
                                $side = $this->rollDice($all_player_id, 2);
                                $notifPlayerArgs['dice2'] = $side['type'];
                                $notifPlayerArgs['roll'] = true;

                                self::notifyAllPlayers("notifBlessing", "", $notifPlayerArgs);
                                $ressourceChoice = $this->blessing($player_id, true, true, 1, true, false);
                            }
                        }

                        break ;
                    case 'freeExploit':
                        // titan
                        // if no card F1/MS1 => nothing to do
                        if ($this->exploits->countCardInLocation('F1') == 0 && $this->exploits->countCardInLocation('F2') == 0 && $this->exploits->countCardInLocation('M1') == 0 && $this->exploits->countCardInLocation('M2') == 0) {
                            $remainingThrows = 1;
                            self::notifyAllPlayers("notifEffect", clienttranslate('No more available exploit that cost 1 ${fireshard} or 1 ${moonshard}.'), [
                                'fireshard' => '[FS]',
                                'moonshard' => '[MS]',
                            ]);
                        }
                        else
                            $forge = true;
                        break ;
                    case 'chooseSides':
                        //goddess
                        $this->dbSetSideChoice($player_id, 1, -1);
                        $this->dbSetSideChoice($player_id, 2, -1);
                        $this->dbSetChoice($player_id, self::RC_SIDE_CHOICE);
                        $ressourceChoice = true;
                        $this->setGameStateValue("goddessChoice", 1);
                        self::notifyAllPlayers("notifEffect", clienttranslate('${player_name} chooses the visible face of each die'),
                            array(
                                'player_name' => $this->loadPlayersBasicInfos()[$player_id]['player_name'],
                            )
                        );

                        break;
                    // Rebellion : Titan
                    case 'memoryTokens':
                        $forge = true;
                        // init of tokens
                        // 1 = sun side
                        // 0 = moon side
                        if ($remainingThrows == 3) {
                            $tokensToInit[] = array ('key' => $card['type']. '_1_' . $player_id, 'nbr' => 1, 'state' => 0);
                            $tokensToInit[] = array ('key' => $card['type']. '_2_' . $player_id, 'nbr' => 1, 'state' => 0);
                            $this->tokens->createTokens($tokensToInit, "none");
                            $remainingThrows--;
                            $this->setGameStateValue( "exploitRemainingThrows", $remainingThrows);
                            $this->gamestate->nextState('exploitEffect');
                            return ;
                        }

                        break ;
                    // Rebellion : Goddess
                    case 'greatGolem':
                        //$this->tokens->setTokenState('mazestock_' . $player_id, 2);
                        $this->tokens->incTokenState("mazestock_$player_id", 2);
                        // $this->dbSetChoice($player_id, self::RC_MAZE);
                        break;
                    case 'timeGolem':
                        //$this->tokens->incTokenState("mazestock_$player_id", -2);
                        $this->setGameStateValue("timeGolem", -2);
                        //$this->tokens->setTokenState('mazestock_' . $player_id, -2);
                        // $this->dbSetChoice($player_id, self::RC_MAZE);
                        break;
                    case 'fortuneWheel':
                        if ($this->tokens->getTokenLocation('wheel_1') == 'none') {
                            $this->dbSetSideChoice($player_id, 1, -1);
                            $this->dbSetSideChoice($player_id, 2, -1);
                            $this->dbSetChoice($player_id, self::RC_SIDE_CHOICE);
                            $ressourceChoice = true;
                        } else {
                            // trigger blessing
                            $this->resetTwins();
                            $ressourceChoice = $this->blessing($player_id, true, true, 1, true);
                        }
                        // Check of the sides done in the end
                        break;
                }
            }

            // to manage the various cards with multiple throws
            $remainingThrows--;
            $this->setGameStateValue( "exploitRemainingThrows", $remainingThrows);
            self::notifyAllPlayers("updateCounters", "", $this->getPlayersRessources());
        }

        if ($card_id != -1) {
            $card = $this->exploits->getCard($card_id);
            $card_info = $this->exploit_types[$card['type']];
        }
        else
            $card_info['action'] = '';

        //if ($remainingThrows <= 0 && !$this->isRessourceChoice() && !$this->hasMazeStock($player_id) && !$forge && !$this->getGameStateValue( "celestialRunning")) {
        if (($remainingThrows <= 0 && !$this->isRessourceChoice() && !$this->hasMazeStock($player_id) && !$forge && !$this->hasUnresolvedSides($player_id)) ||
            // #35045
            $card_info['action'] == 'freeExploit') {
            // memoryTokens : if there are some available, get them
            // do not get the tokens that we just bought
            if ($card_id != -1) {
                $card = $this->exploits->getCard($card_id);
                $card_info = $this->exploit_types[$card['type']];
                $island = $card_info['island'];
                $memoryTokens =  $this->tokens->getTokensOfTypeInLocation('%Memory%' . $player_id, $island);
                foreach($memoryTokens as $tokenId => $token) {
                    $token_split = explode("_", $tokenId);
                    if ($token_split[0] == $card['type'])
                        continue ;

                    $notifPlayerArgs = $this->initNotif($player_id);

                    if ($token['state'] == '1') {
                        // gain = 2 L + 1 FS
                        $this->tokens->incTokenState("movetitan_$player_id", 2);
                        $this->increaseFireShard($player_id, 1);
                        $notifPlayerArgs['loyalty'] = 2;
                        $notifPlayerArgs['fireshard'] = 1;
                        unset($notifPlayerArgs['vp']);

                    }
                    else {
                        // gain = 2 AS + 1 MS
                        $this->tokens->incTokenState("movetitan_$player_id", -2);
                        $this->increaseAncientShard($player_id, 2);
                        $this->increaseMoonShard($player_id, 1);
                        $notifPlayerArgs['ancientshard'] = 2;
                        $notifPlayerArgs['moonshard'] = 1;
                        unset($notifPlayerArgs['vp']);
                    }
                    $this->tokens->moveToken($tokenId, 'used');
                    self::notifyAllPlayers("notifRemoveMemoryToken", clienttranslate('${player_name} collect the Memory token and get ${ressources}'),
                        array(
                            'player_name' => self::getActivePlayerName(),
                            'tokenId'     => $tokenId,
                            'ressources'  => $this->buildRessourceNotif( $notifPlayerArgs ),
                        )
                    );

                    $this->generateNotifLoyalty ($notifPlayerArgs);
                    self::notifyAllPlayers("updateCounters", "", $this->getPlayersRessources());

                }
                $this->titanMove($player_id);
            }

        }

        if (($remainingThrows <= 0 && !$this->isRessourceChoice() && !$this->hasMazeStock($player_id) && !$forge && !$this->hasUnresolvedSides($player_id))) {
            // Put all necessary global to -1
            $this->setGameStateValue( "enigmaDieNumber", -1);
            $this->setGameStateValue( "exploitBought", -1 );
            $this->setGameStateValue('oustedPlayerId', -1);
            $this->setGameStateValue('hasOusted', -1);
            $this->setGameStateValue('celestialRunning', 0);
            $this->resetThrowTokens();

            // throw new \feException("end of effect exploit");
            // throw new \feException(print_r(debug_print_backtrace()));

            // disable all users
            self::DbQuery('UPDATE player SET player_is_multiactive = 0');
            if ($card_info['action'] == 'fortuneWheel') {
                // check choice of sides
                // if one of the side match => 8 VP, if 2 => 20 VP
                // in progress
                $previsions = [$this->tokens->getTokenLocation('wheel_1'), $this->tokens->getTokenLocation('wheel_2')];
                $rolled =[current($this->sides->getCardsInLocation('dice1-p'.$player_id, 0))['type'], current($this->sides->getCardsInLocation('dice2-p'.$player_id, 0))['type']];

                if (($previsions[0] == $rolled[0] && $previsions[1] == $rolled[1]) || ($previsions[1] == $rolled[0] && $previsions[0] == $rolled[1])) {
                    $vp = 20;
                } elseif ($previsions[0] == $rolled[0] || $previsions[0] == $rolled[1] || $previsions[1] == $rolled[0] || $previsions[1] == $rolled[1]) {
                    $vp = 8;
                } else {
                    $vp = 0;
                }

                $this->increaseVP($player_id, $vp);
                $this->incStat($vp, 'nb_vp_exploit', $player_id);
                self::notifyAllPlayers("notifBlessing", clienttranslate('${player_name} gets ${ressources} from the Wheel of Fortune'), array(
                            'player_name'   => $this->getActivePlayerName(),
                            'ressources'    => $vp . ' [VP]') );
            }
        }

        // #35073 : add check of misfortune
        if ($this->isRessourceChoice() || $this->hasMazeStock($player_id) || $goAgain || $this->hasUnresolvedSides($player_id) || $this->misfortuneState() != 0)
            $this->gamestate->nextState("exploitEffect");
        elseif ($forge || $this->getGameStateValue( "celestialRunning"))
            // do nothing as action will be triggered
            return ;
        elseif ($newTurn)
            $this->gamestate->nextState('endPlayerTurn');
        elseif ($pegasus) {
            $this->gamestate->nextState('pegasus');
        }
        elseif ($remainingThrows <= 0 && $this->canTakeSecondAction($player_id))
            $this->gamestate->nextState("playerSecondAction");
        elseif ($remainingThrows <= 0)
            $this->gamestate->nextState('endPlayerTurn');
        else
            $this->gamestate->nextState("exploitEffect");
    }

    function misfortuneState() {
        $token = $this->tokens->getTokenInfo('resolveMisfortune');

        if (is_array($token))
            return $token['state'];
        else
            return 0;

    }

    function debugVTO($player_id) {
        //throw new feException($this->misfortuneState());
        $retour = $this->getPlayersAdditionnalInfo()[$player_id];
        $retour["puzzle"] = $this->tokens->getTokenState("puzzle_" . $player_id);
        $retour["canTwin"] = $this->canUseTwins($player_id);
        throw new feException(print_r($retour)); //['player_name'] . " " . $this->getPlayersAdditionnalInfo()[$player_id]['ressource_choice']);
    }

    function debugThib() {
        $this->gamestate->nextState("blessing");
    }

    function debugSetPos($pos) {
       $player_id = self::getCurrentPlayerId();    // !! We must only return informations visible by this player !!
       $this->tokens->setTokenState('position_' . $player_id, $pos);
        self::notifyAllPlayers("notifTitanMove",'' , [
            'player_name'  => $this->loadPlayersBasicInfos()[$player_id]['player_name'],
            'player_color' => $this->loadPlayersBasicInfos()[$player_id]['player_color'],
            'player_id'    => $player_id,
            'position'     => $pos
        ]);

        // Goddess
        //$this->tokens->setTokenState('position_' . $player_id, $pos);
        //self::notifyAllPlayers("notifMazeMove",'' , [
        //    'player_name'  => $this->loadPlayersBasicInfos()[$player_id]['player_name'],
        //    'player_color' => $this->loadPlayersBasicInfos()[$player_id]['player_color'],
        //    'player_id'    => $player_id,
        //    'position'     => $pos
        //]);

    }

    function stEndPlayerTurn()
    {
        $currentPlayerNum = $this->getGameStateValue( "currentPlayerNum" );
        $player_id = self::getActivePlayerId();

        $eternalFire = $this->getGameStateValue( "eternalFire" );

        $this->resetThrowTokens();
        $this->setGameStateValue("doubleCelestialRoll", 0);
        $this->actCancelAllScepters(true);

        foreach ($this->exploits->getCardsInLocation( "pile3-" . $player_id) as $card_id => $reinforcement) {
            if ($reinforcement['type'] != 'companion' || $this->tokens->getTokenState('companion_' . $card_id) <5)
                $this->dbUpdateExploitPlayed($card_id, false);
        }

        // If this a not a new turn linked to the Eternal Fire
        if (!$eternalFire) {
            self::activeNextPlayer();
            $turnCount = self::getGameStateValue( 'turnCount' );
            $nbTurns   = self::getGameStateValue( 'nbTurns' );

            if ( $currentPlayerNum == $this->getGameStateValue( "nbPlayers" ) )
            {

                if ( $turnCount < $nbTurns )
                {
                    self::notifyAllPlayers("notifEndTurn", clienttranslate('End of turn ${turn}'), array(
                        'turn'    => $turnCount,
                    ) );

                    self::incGameStateValue ('turnCount', 1);

                    $this->gamestate->nextState( "nextTurn" );
                }
                else
                {
                    // if last turn, autoaffect triton token
                    $this->autoAffectTritonToken ($player_id);
                    $this->setGameStateValue( "currentPlayerNum", $this->getGameStateValue( "nbPlayers" ) + 1 );
                    $this->gamestate->nextState( "endScoring" );
                }
                return;
            }
            else
            {
                // if last turn, autoaffect triton token
                if ( $turnCount >= $nbTurns ) {
                    $this->autoAffectTritonToken ($player_id);
                }
                $currentPlayerNum++;

                $this->setGameStateValue( "currentPlayerNum", $currentPlayerNum );

                $this->gamestate->nextState( "nextPlayer" );
            }
        }
        else
            $this->gamestate->nextState( "nextPlayer" );
    }


    function stEndScoring() {
        self::notifyAllPlayers("notifBeginScoring", '', array());
        $players = $this->loadPlayersBasicInfos();
        foreach($players as $player_id => $player) {
            $locations = ['pile1-' . $player_id, 'pile2-' . $player_id, 'pile3-'. $player_id, 'table-' . $player_id];
            foreach ($locations as $location) {
                foreach($this->exploits->getCardsInLocation($location) as $card) {
                    $card_vp   = $this->exploit_types[$card['type']]['VP'];
                    $card_name = $this->exploit_types[$card['type']]['name'];
                    // en attendant de trouver mieux
                    // $location = $location == 'table-' . $player_id ? 'pile1-' . $player_id : $location;
                    $location = 'pile-' . $player_id;

                    $this->increaseVP($player_id, $card_vp);

                    $this->incStat($card_vp, 'nb_vp_exploit', $player_id);

                    self::notifyAllPlayers("notifEndScoring", clienttranslate('${player_name} has won ${ressources} for the card ${card_name_trans}'),
                        array(
                            'i18n'              => array('card_name_trans'),
                            'card_id'           => $card['id'],
                            'card_name'         => $card_name,
                            'card_name_trans'   => $card_name,
                            'card_type'         => $card['type'],
                            'pile'              => $location,
                            'player_id'         => $player_id,
                            'player_name'       => $player['player_name'],
                            'ressources'        => $this->buildRessourceNotif( array("vp" => $card_vp) ),
                            'vp'                => $card_vp,
                        )
                    );
                }
            }

            // Titan, must score positive of negative amount.
            if ($this->getGameStateValue( "rebellion") == 3) {
                $position = $this->tokens->getTokenState("position_" . $player_id);
                $reward = $this->titan_board[$position]['vp'];


                if ($reward < 0) {
                    $this->decreaseVP($player_id, $reward * -1);
                    self::notifyAllPlayers("notifEndScoringTitan", clienttranslate('${player_name} looses ${ressources} due to its position on the Titan board'),
                            array(
                                'player_name'   => $this->loadPlayersBasicInfos()[$player_id]['player_name'],
                                'player_id'     => $player_id,
                                'ressources'    => $this->buildRessourceNotif( ['vp' => $reward * -1]),
                                'vp'            => $reward
                            )
                        );
                }
                elseif ($reward > 0) {
                    $this->increaseVP($player_id, $reward);
                    self::notifyAllPlayers("notifEndScoringTitan", clienttranslate('${player_name} gets ${ressources} due to its position on the Titan board'),
                            array(
                                'player_name'   => $this->loadPlayersBasicInfos()[$player_id]['player_name'],
                                'player_id'     => $player_id,
                                'ressources'    => $this->buildRessourceNotif( ['vp' => $reward]),
                                'vp'            => $reward
                            )
                        );
                }
            }
        }

        $this->calculateTieBreaker();

        self::notifyAllPlayers("updateCounters", "", $this->getPlayersRessources());
        $this->gamestate->nextState();
    }

    function stPoule() {

    }

//////////////////////////////////////////////////////////////////////////////
//////////// Zombie
////////////

    /*
        zombieTurn:

        This method is called each time it is the turn of a player who has quit the game (= "zombie" player).
        You can do whatever you want in order to make sure the turn of this player ends appropriately
        (ex: pass).
    */

    function zombieTurn( $state, $active_player )
    {
        $statename = $state['name'];

        // remove all sides + boar
        $this->dbSetSideChoice($active_player, 1, "0");
        $this->dbSetSideChoice($active_player, 2, "0");
        $this->dbSetChoice($active_player, self::RC_NOTHING_TODO);
        $this->tokens->setTokenState ("mazechoice_" . $active_player, "0");
        $this->tokens->setTokenState ("mazestock_" . $active_player, "0");
        $this->tokens->setTokenState ("puzzle_" . $active_player, "0");

        $sql = "UPDATE  player
                SET     boar = 0
                WHERE   player_id = $active_player";

        self::DbQuery( $sql );


        // trigger next state
        //throw new BgaUserException($statename);

        if ($state['type'] == "activeplayer") {
            switch ($statename) {
                //case 'beginPlayerTurn':
                //    $this->actRollDice();
                //    break;
                case 'reinforcement':
                    $this->gamestate->nextState('playerAction');
                    break;
                case 'playerAction':
                    $this->gamestate->nextState('endPlayerTurn');
                    break;
                case 'draft':
                    $this->gamestate->nextState( "draft" );
                    break;
                default:
                    $this->gamestate->nextState( "zombiePass" );
                    break;
            }

            return;
        }
        elseif ($state['type'] == "multipleactiveplayer") {
            if ($statename == 'exploitForgeBoar') {
                // if boar forge, we forge the first available side
                $sideToForge = $this->argsForgeBoar()[0];
                $sides = $this->sides->getCardsInLocation('dice1-p' . $active_player);
                foreach ($sides as $id => $side) {
                    if ($side['type_arg'] == 0) {
                        $this->actBuyForge($sideToForge, $side['id']);
                        break ;
                    }
                }
            }
            else {
                // Make sure player is in a non blocking status for role turn
                $sql = "
                    UPDATE  player
                    SET     player_is_multiactive = 0
                    WHERE   player_id = $active_player
                ";
                self::DbQuery( $sql );

                 $this->gamestate->setPlayerNonMultiactive($active_player, 'nextState');
            }
            return;
        }

        throw new feException( "Zombie mode not supported at this game state: ".$statename );
    }

///////////////////////////////////////////////////////////////////////////////////:
////////// DB upgrade
//////////

    /*
        upgradeTableDb:

        You don't have to care about this until your game has been published on BGA.
        Once your game is on BGA, this method is called everytime the system detects a game running with your old
        Database scheme.
        In this case, if you change your Database scheme, you just have to apply the needed changes in order to
        update the game database and allow the game to continue to run with your new version.

    */

    function upgradeTableDb( $from_version )
    {
        // $from_version is the current version of this game database, in numerical form.
        // For example, if the game was running with a release of your game named "140430-1345",
        // $from_version is equal to 1404301345

//        // Please add your future database scheme changes here
        //if ( $from_version <= 1805251618 ) {
        //    $sql = "ALTER TABLE `sides` CHANGE `card_location` `card_location` VARCHAR(40) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL;";
        //    self::DbQuery( $sql );
        //    $sql = "ALTER TABLE `exploit` CHANGE `card_location` `card_location` VARCHAR(40) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL;";
        //    self::DbQuery( $sql );
        //}

        if ( $from_version <= 1806062135) {
            // additon of value to know if we can allocate automatically the hammer
            $sql = "ALTER TABLE DBPREFIX_player ADD hammer_auto int(1) DEFAULT 0";
            self::applyDbUpgradeToAllDB( $sql );
            // addition to know if dice have been rolled
            $sql = "ALTER TABLE DBPREFIX_player ADD rolled int(1) DEFAULT 0";
            self::applyDbUpgradeToAllDB( $sql );
        }

        if ($from_version <= 1806302236) {
        //if (1 == 1) {
            $sql = "ALTER TABLE DBPREFIX_player ADD twins int(1) DEFAULT 0";
            self::applyDbUpgradeToAllDB( $sql );

            $sql = "ALTER TABLE DBPREFIX_sides MODIFY card_type VARCHAR(20) NOT NULL";
            self::applyDbUpgradeToAllDB( $sql );

            $sql = "CREATE TABLE IF NOT EXISTS DBPREFIX_token (
                token_key varchar(32) NOT NULL,
                token_location varchar(32) NOT NULL,
                token_state int(10),
                PRIMARY KEY (token_key)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8";
            self::applyDbUpgradeToAllDB( $sql );

            $tokensToInit = array();
            $players = $this->getPlayersAdditionnalInfo();
            foreach ($players as $player_id => $player_info) {
                $tokensToInit[] = array ('key' => 'triple_' . $player_id, 'nbr' => 1, 'state' => 0);
                $tokensToInit[] = array ('key' => 'throw1_' . $player_id, 'nbr' => 1, 'state' => 0);
                $tokensToInit[] = array ('key' => 'throw2_' . $player_id, 'nbr' => 1, 'state' => 0);
                $tokensToInit[] = array ('key' => 'mirror1_' . $player_id, 'nbr' => 1, 'state' => 0);
                $tokensToInit[] = array ('key' => 'mirror2_' . $player_id, 'nbr' => 1, 'state' => 0);
                $tokensToInit[] = array ('key' => 'gold_' . $player_id, 'nbr' => 1, 'state' => 0);
                $tokensToInit[] = array ('key' => 'vp_' . $player_id, 'nbr' => 1, 'state' => 0);
                $tokensToInit[] = array ('key' => 'moonshard_' . $player_id, 'nbr' => 1, 'state' => 0);
                $tokensToInit[] = array ('key' => 'fireshard_' . $player_id, 'nbr' => 1, 'state' => 0);
                $tokensToInit[] = array ('key' => 'ancientshard_' . $player_id, 'nbr' => 1, 'state' => 0);
                $tokensToInit[] = array ('key' => 'cerberus_' . $player_id, 'nbr' => 1, 'state' => 0);
            }

            $this->tokens->createTokens($tokensToInit, "none");
        }

        if ($from_version <=  2007262301) {
            $tokensToInit[] = array ('key' => 'celestial_choice', 'nbr' => 1, 'state' => 0, 'location' => '0');
            $this->tokens->createTokens($tokensToInit, "none");
        }

        if ($from_version < 2012021756) {
            $sql = "ALTER TABLE DBPREFIX_player ADD res_ancient int(1) DEFAULT 0";
            self::applyDbUpgradeToAllDB( $sql );

        }

        if ($from_version <= 2012031120) {
            $result = self::getUniqueValueFromDB("SHOW COLUMNS FROM `player` LIKE 'res_ancient'");
            if(is_null($result)){
                $sql = "ALTER TABLE DBPREFIX_player ADD res_ancient int(1) DEFAULT 0";
                self::applyDbUpgradeToAllDB( $sql );
            }
        }

        if ($from_version <= 2101301759)
        {
             $tokensToInit[] = array ('key' => 'resolveMisfortune', 'nbr' => 1, 'state' => 0);
            // will store the sides to resolve
            $tokensToInit[] = array ('key' => 'misfortune_1', 'nbr' => 1, 'state' => 0);
            $tokensToInit[] = array ('key' => 'misfortune_2', 'nbr' => 1, 'state' => 0);
            $tokensToInit[] = array ('key' => 'stateMisfortune', 'nbr' => 1, 'state' => 0);
            $this->tokens->createTokens($tokensToInit, "none");
        }
    }

    function debugNextState($state) {
        $this->gamestate->nextState($state);
    }
}
