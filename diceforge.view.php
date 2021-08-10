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
 * diceforge.view.php
 *
 * This is your "view" file.
 *
 * The method "build_page" below is called each time the game interface is displayed to a player, ie:
 * _ when the game starts
 * _ when a player refreshes the game page (F5)
 *
 * "build_page" method allows you to dynamically modify the HTML generated for the game interface. In
 * particular, you can set here the values of variables elements defined in diceforge_diceforge.tpl (elements
 * like {MY_VARIABLE_ELEMENT}), and insert HTML block elements (also defined in your HTML template file)
 *
 * Note: if the HTML of your game interface is always the same, you don't have to place anything here.
 *
 */

require_once( APP_BASE_PATH."view/common/game.view.php" );

class view_diceforge_diceforge extends game_view
{
    function getGameName() {
        return "diceforge";
    }
    
    function build_page( $viewArgs )
    {		
        // Get players & players number
        $players       = $this->game->loadPlayersBasicInfos();
        $players_nbr   = count( $players );
        $players_table = $this->game->getNextPlayerTable();
        global $g_user;
        $current_player_id = $g_user->get_id(); 
        $isSpectator   = isset( $players[ $current_player_id ]['player_color'] ) ? false : true;

        /*********** Place your code below:  ************/

        // Get first player id
        $this->tpl['firstPlayerId']   = $this->game->getGameStateValue( "firstPlayerId" );
        $this->tpl['currentPlayerId'] = $current_player_id;
        $this->tpl['yourName']        = self::_("Yourself");
        if ( !$isSpectator )
            $this->tpl['yourColor']       = $players[ $current_player_id ]['player_color'];
        else
            $this->tpl['yourColor']  = 'white';
        $this->tpl['TURN']            = $this->game->getGameStateValue( "turnCount" );

        // Pool initialisation
        $this->page->begin_block("diceforge_diceforge", "pool");

        foreach ($this->game->sides_init as $pool => $sides) {
            if ($pool > 15) {break;}
            $this->page->insert_block( "pool", array( 
                "POOL_ID" => $pool
            ));
        }

        // Exploit initialisation
        $this->page->begin_block("diceforge_diceforge", "exploit");

        foreach ($this->game->exploit_slot as $name => $exploit) {
            $this->page->insert_block( "exploit", array( 
                "CARD_POSITION" => $exploit
            ));
        }

        // Players dice container init
        // for the other players but the current player
        $this->page->begin_block("diceforge_diceforge", "players-dice");

        if ( $isSpectator ) {
            $previous = $players_table[ 0 ];
            $imax     = $players_nbr;
        } else {
            $previous = $players_table[ $current_player_id ];
            $imax     = $players_nbr - 1;
        }

        for ($i = 1 ; $i <= $imax ; $i++ ) {
            $player = $players[ $previous ];

            if($player['player_id'] != $current_player_id) {
                $this->page->insert_block( "players-dice", array( 
                    "PLAYER_NAME" => $player['player_name'] ,
                    "PLAYER_COLOR" => $player['player_color'],
                    "PLAYER_ID" => $player['player_id'],
                ) );
            }

            $previous = $players_table[ $previous ];
        }
        
/*********** Do not change anything below this line  ************/
    }
}


