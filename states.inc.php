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
 * states.inc.php
 *
 * diceforge game states description
 *
 */

/*
   Game state machine is a tool used to facilitate game developpement by doing common stuff that can be set up
   in a very easy way from this configuration file.

   Please check the BGA Studio presentation about game state to understand this, and associated documentation.

   Summary:

   States types:
   _ activeplayer: in this type of state, we expect some action from the active player.
   _ multipleactiveplayer: in this type of state, we expect some action from multiple players (the active players)
   _ game: this is an intermediary state where we don't expect any actions from players. Your game logic must decide what is the next game state.
   _ manager: special type for initial and final state

   Arguments of game states:
   _ name: the name of the GameState, in order you can recognize it on your own code.
   _ description: the description of the current game state is always displayed in the action status bar on
                  the top of the game. Most of the time this is useless for game state with "game" type.
   _ descriptionmyturn: the description of the current game state when it's your turn.
   _ type: defines the type of game states (activeplayer / multipleactiveplayer / game / manager)
   _ action: name of the method to call when this game state become the current game state. Usually, the
             action method is prefixed by "st" (ex: "stMyGameStateName").
   _ possibleactions: array that specify possible player actions on this step. It allows you to use "checkAction"
                      method on both client side (Javacript: this.checkAction) and server side (PHP: self::checkAction).
   _ transitions: the transitions are the possible paths to go from a game state to another. You must name
                  transitions in order to use transition names in "nextState" PHP method, and use IDs to
                  specify the next game state for each transition.
   _ args: name of the method to call to retrieve arguments for this gamestate. Arguments are sent to the
           client side to be used on "onEnteringState" or to set arguments in the gamestate description.
   _ updateGameProgression: when specified, the game progression is updated (=> call to your getGameProgression
                            method).
*/

//    !! It is not a good idea to modify this file when a game is running !!

if ( !defined('STATE_BEGIN_TURN') )
{
  define("STATE_BEGIN_TURN", 2);
  define("STATE_BEGIN_PLAYER_TURN", 3);
  define("STATE_BLESSING", 4);
  define("STATE_RESSOURCE_CHOICE", 5);
  define("STATE_REINFORCEMENT", 6);
  define("STATE_PLAYER_ACTION", 7);
  define("STATE_FORGE_BUY", 8);
  define("STATE_FORGE_DICE", 9);
  define("STATE_SECOND_ACTION", 10);
  define("STATE_EXPLOIT_BUY", 11);
  define("STATE_PLAYER_OUSTING", 12);
  define("STATE_OUSTED_PLAYER_CHOICE", 13);
  define("STATE_EXPLOIT_EFFECT", 14);
  define("STATE_END_PLAYER_TURN", 15);
  define("STATE_DOE_RESSOURCE_CHOICE", 16);
  define("STATE_EXPLOIT_RESSOURCE", 17);
  define("STATE_SCORING_END", 18);
  define("STATE_FORGE_SHIP", 19);
  define("STATE_DOE_FORGE_SHIP", 20);
  define("STATE_OUSTED_FORGE_SHIP", 21);
  define("STATE_EXPLOIT_FORGE_SHIP", 22);
  define("STATE_EXPLOIT_FORGE_BOAR", 23);
  define("STATE_DRAFT", 24);
  define("STATE_DRAFT_PLAYER", 25);
  define("STATE_END_GAME", 99);
}

 
$machinestates = array(

    // The initial state. Please do not modify.
    1 => array(
        "name"        => "gameSetup",
        "description" => "",
        "type"        => "manager",
        "action"      => "stGameSetup",
        "transitions" => array( "" => STATE_BEGIN_TURN )
    ),
    
    // Note: ID=2 => your first state

    /*
     * Beginning of a turn : stats, turn setup will be done here
     *
     * - Notif remaining turns ?
     *
    **/
    STATE_BEGIN_TURN => array(
        "name"                  => "beginTurn",
        "description"           => "",
        "type"                  => "game",
        "action"                => "stBeginTurn",
        "transitions"           => array( "beginPlayerTurn" => STATE_BEGIN_PLAYER_TURN, 'gameEnd' => STATE_END_GAME, 'draft' =>  STATE_DRAFT)
    ),
    
    STATE_DRAFT => array(
        "name"                  => "draftGame",
        "description"           => "",
        "type"                  => "game",
        "action"                => "stDraftGame",
        "transitions"           => array( "beginTurn" => STATE_BEGIN_TURN, 'draft' =>  STATE_DRAFT_PLAYER)
    ),
    
    /*
     * Beginning of a turn : stats, turn setup will be done here
     *
     * - Notif remaining turns ?
     *
    **/
    STATE_DRAFT_PLAYER => array(
        "name"                  => "draft",
        "description"           => clienttranslate('Draft : ${actplayer} is choosing a card'),
        "descriptionmyturn"     => clienttranslate('Draft : ${you} must choose a card'),
        "type"                  => "activeplayer",
        //"action"                => "stDraftMode",
        "possibleactions"       => array ('actDraft', 'actAutoHammer'),
        "args"                  => "argsDraft",
        "transitions"           => array( "draft" => STATE_DRAFT)
    ),

    /*
     * Beginning of a PLAYER turn : every automated tasks before a player takes his first action 
     *
     * - Notif who's turn ?
     *
    **/
    STATE_BEGIN_PLAYER_TURN => array(
        "name"                  => "beginPlayerTurn",
        "description"           => clienttranslate('${actplayer} must roll the dice'),
        "descriptionmyturn"     => clienttranslate('${you} must roll your dice'),
        "type"                  => "activeplayer",
        "action"                => "stBeginPlayerTurn",
        "possibleactions"       => array('actRollDice'),
        "transitions"           => array( "blessing" => STATE_BLESSING ),
        "updateGameProgression" => true, 
    ),

    /*
     * Roll of the dice
     *
     * - Make everyone rolls their dices
     * - Handle automatic resolutions
     * - Ressource choice or reinforcement (if no state necessary)
     *
    **/
    STATE_BLESSING => array(
        "name"              => "divineBlessing",
        "description"       => clienttranslate('Everyone rolls their dice'),
        "descriptionmyturn" => clienttranslate('${you} roll your dice'),
        "type"              => "game",
        "action"            => "stBlessing",
        "transitions"       => array( "reinforcement" => STATE_REINFORCEMENT, "blessing" => STATE_BLESSING, "ressourceChoice" => STATE_RESSOURCE_CHOICE, "forgeShip" => STATE_FORGE_SHIP, 'nextState' => STATE_BLESSING)
    ),
    
    /*
     * Choice of ressources based on input from the players
     *
     * - Process info of the players
     * - update ressources
     * - Then go to reinforcement step
     *
    **/
    STATE_RESSOURCE_CHOICE => array(
        "name"              => "ressourceChoice",
        "description"       => clienttranslate('Everyone choose the resources'),
        "descriptionmyturn" => clienttranslate('${you} choose the resources for the side ${loyalty}'),
        "type"              => "multipleactiveplayer",
        "args"              => "argsRessourceChoice",
        "action"            => "stRessourceChoice",
        "possibleactions"   => array('actRessourceChoice', 'actSideChoice', 'actActionChoice', 'actUseCerberusToken', 'actUseTritonToken', 'actAutoHammer', 'actChooseMazePath', 'actChooseTreasure', 'actMazePowerConfirm', 'actPuzzleCelestial', 'actPuzzleMaze'),
        //"transitions"       => array( "reinforcement" => STATE_REINFORCEMENT, "blessing" => STATE_BLESSING )
		"transitions"       => array( "blessing" => STATE_BLESSING, 'choice' => STATE_RESSOURCE_CHOICE, 'nextState' => STATE_BLESSING )
    ),
    
    /*
     * If users have thrown a Ship side
     *
     * - Enable active players, then each player in turn order
     * - Can forge or pass
     *
    **/
    STATE_FORGE_SHIP => array(
        "name"              => "forgeShip",
        "description"       => clienttranslate('[${ship}] ${actplayer} may forge a side'),
        "descriptionmyturn" => clienttranslate('[${ship}] ${you} may forge 1 side (cost ${minusCost})'),
        "type"              => "multipleactiveplayer",
        "args"              => "argsForgeShip",
        "possibleactions"   => array('actBuyForge', 'actForgeShipPass', 'actUseTritonToken', 'actAutoHammer', 'actCelestialUpgrade', 'actCancelCelestial','actPuzzleCelestial'),
        "transitions"       => array( "blessing" => STATE_BLESSING, 'forgeShip' => STATE_FORGE_SHIP, 'nextState' => STATE_BLESSING )
    ),
    
    /*
     * Active player may use his reinforcements cards
     * Args : will get the remaining reinforcement that can be triggered
     * - For every R cards the player got, generate a STATE ?
     * - Pick ressources / roll dices etc.
     *
    **/
    STATE_REINFORCEMENT => array(
        "name"              => "reinforcement",
        "description"       => clienttranslate('${actplayer} may activate reinforcement cards'),
        "descriptionmyturn" => clienttranslate('${you} may activate reinforcement cards'),
        "type"              => "activeplayer",
        "args"              => "argsReinforcement",
        "action"            => "stReinforcement",
        "possibleactions"   => array('actReinforcement', 'actReinforcementPass', 'actUseTritonToken', 'actAutoHammer'),
        "transitions"       => array( "reinforcement" => STATE_REINFORCEMENT, "playerAction" => STATE_PLAYER_ACTION,
                                      "choice" => STATE_DOE_RESSOURCE_CHOICE, "forgeShip" => STATE_DOE_FORGE_SHIP)
    ),

    /*
     * Active player use his doe and needs to choose a ressource
     *
    **/
    STATE_DOE_RESSOURCE_CHOICE => array(
        "name"              => "doeRessourceChoice",
        "description"       => clienttranslate('Players are choosing resources'),
        "descriptionmyturn" => clienttranslate('${you} choose the resources for the side '),
        "type"              => "multipleactiveplayer",
        "args"              => "argsRessourceChoice",
        "possibleactions"   => array('actDoeTakeRessource', 'actActionChoice', 'actSideChoice', 'actUseCerberusToken', 'actUseTritonToken', 'actAutoHammer', 'actChooseMazePath', 'actChooseTreasure', 'actMazePowerConfirm', 'actPuzzleCelestial', 'actPuzzleMaze'),
        "transitions"       => array( "nextState" => STATE_REINFORCEMENT, 'choice' => STATE_DOE_RESSOURCE_CHOICE, "forgeShip" => STATE_DOE_FORGE_SHIP )
    ),
    
	/*
     * If users have thrown a Ship side
     *
     * - Enable active players, then each player in turn order
     * - Can forge or pass
     *
    **/
    STATE_DOE_FORGE_SHIP => array(
        "name"              => "doeForgeShip",
        "description"       => clienttranslate('[${ship}] ${actplayer} may forge a side'),
        "descriptionmyturn" => clienttranslate('[${ship}] ${you} may forge 1 side (cost ${minusCost})'),
        "type"              => "multipleactiveplayer",
        "args"              => "argsForgeShip",
        "possibleactions"   => array('actBuyForge', 'actForgeShipPass', 'actUseTritonToken', 'actAutoHammer', 'actCelestialUpgrade', 'actCancelCelestial'),
        "transitions"       => array( "nextState" => STATE_REINFORCEMENT, 'forgeShip' => STATE_FORGE_SHIP, 'choice' => STATE_DOE_RESSOURCE_CHOICE)
    ),
	
    /*
     * Active player must choose an action (or pass)
     *
     * - Three big buttons : exploit, forge, pass
     *
    **/
    STATE_PLAYER_ACTION => array(
        "name"              => "playerAction",
        "description"       => clienttranslate('Turn ${currentTurn}/${maxTurn} : ${actplayer} may choose an action'),
        "descriptionmyturn" => clienttranslate('Turn ${currentTurn}/${maxTurn} : ${you} may choose an action'),
        "type"              => "activeplayer",
        "args"              => "argsPlayerAction",
        "possibleactions"   => array('actBuyForge', 'actEndForge', 'actBuyExploit', 'actEndPlayerTurn', 'actUseTritonToken', 'actAutoHammer', 'actUseCompanion', 'actUseScepter', 'actCancelScepter'),
        //"transitions"       => array( "forgeDice" => STATE_FORGE_DICE, "playerOusting" => STATE_PLAYER_OUSTING, "exploitEffect" => STATE_EXPLOIT_EFFECT, "endPlayerTurn" => STATE_END_PLAYER_TURN )
        "transitions"       => array( "playerAction" => STATE_PLAYER_ACTION, "playerOusting" => STATE_PLAYER_OUSTING,
                                     "exploitEffect" => STATE_EXPLOIT_EFFECT, "endPlayerTurn" => STATE_END_PLAYER_TURN,
                                     "playerSecondAction" => STATE_SECOND_ACTION, "current" => STATE_PLAYER_ACTION)
    ),
    
    /*
     * Forging of the dice
     * - send sides to forge / location
     * - display of the sides and the one to select
     * - Check if the player can make a new action (2FS necessary)
     * 
    **/
    //STATE_FORGE_DICE => array(
    //    "name"              => "forgeDice",
    //    "description"       => clienttranslate('${actplayer} is forging their dice'),
    //    "descriptionmyturn" => clienttranslate('${you} must forge your dice'),
    //    "type"              => "activeplayer",
    //    "args"              => "argsForgeDice",
    //    "possibleactions"   => array("actForgeDice", 'actUseTritonToken'), 
    //    "transitions"       => array( "playerSecondAction" => STATE_SECOND_ACTION, "endPlayerTurn" =>  STATE_END_PLAYER_TURN)
    //),

    /*
     *  Does the player wants to use his second action
     * - 3 buttons: forge, exploit, pass
     * - if different of pass, decrease of FS
     * 
    **/
    STATE_SECOND_ACTION => array(
        "name"              => "secondAction",
        "description"       => clienttranslate('${actplayer} is choosing if another action is played'),
        "descriptionmyturn" => clienttranslate('Do ${you} wish to take another action?'),
        "args"              => "argsSecondAction",
        "type"              => "activeplayer",
        "possibleactions"   => array("actSecondAction", 'actUseTritonToken', 'actAutoHammer', 'actUseCompanion', 'actUseScepter', 'actCancelScepter'), 
        "transitions"       => array( "playerAction" => STATE_PLAYER_ACTION, "endPlayerTurn" => STATE_END_PLAYER_TURN, "current" =>  STATE_SECOND_ACTION)
    ),

    /*
     *  if player ousting, generate the roll
     * - Get rewards for ousting / being ousted (bear card)
     *  - if choice => launch ousted player choice
     *
    **/
    STATE_PLAYER_OUSTING => array(
        "name"              => "playerOusting",
        "description"       => "",
        "descriptionmyturn" => "",
        "type"              => "game",
        "action"            => "stOusting",
        "transitions"       => array( "exploitEffect" => STATE_EXPLOIT_EFFECT, "choice" => STATE_OUSTED_PLAYER_CHOICE, "forgeShip" => STATE_OUSTED_FORGE_SHIP, "nextState" => STATE_PLAYER_OUSTING)
    ),
    
    /*
     *  Choice of ressource
     *  Change of active player
     *  after change of active player to old one?
    **/
    STATE_OUSTED_PLAYER_CHOICE => array(
        "name"              => "playerOustingChoice",
        //"description"       => clienttranslate('${actplayer} has been ousted and must choose a ressource'),
        //"descriptionmyturn" => clienttranslate('${you} have been ousted and must choose a ressource'),
        "description"       => clienttranslate('Players are choosing resources due to ousting'),
        "descriptionmyturn" => clienttranslate('${you} choose the resources for the side '),
        "type"              => "multipleactiveplayer",
        "args"              => "argsRessourceChoice",
        //"action"            => "stRessourceChoiceAdvanced",
        "possibleactions"   => array("actOustedRessources", 'actSideChoice', 'actActionChoice', 'actUseCerberusToken', 'actUseTritonToken', 'actAutoHammer', 'actChooseMazePath', 'actChooseTreasure', 'actMazePowerConfirm', 'actPuzzleCelestial', 'actPuzzleMaze'),
        "transitions"       => array( "nextState" => STATE_PLAYER_OUSTING, 'choice' => STATE_OUSTED_PLAYER_CHOICE, "forgeShip" => STATE_OUSTED_FORGE_SHIP )
    ),
	
	/*
     * If users have thrown a Ship side
     *
     * - Enable active players, then each player in turn order
     * - Can forge or pass
     *
    **/
    STATE_OUSTED_FORGE_SHIP => array(
        "name"              => "oustedForgeShip",
        "description"       => clienttranslate('[${ship}] ${actplayer} may forge a side'),
        "descriptionmyturn" => clienttranslate('[${ship}] ${you} may forge 1 side (cost ${minusCost})'),
        "type"              => "multipleactiveplayer",
        "args"              => "argsForgeShip",
        "possibleactions"   => array('actBuyForge', 'actForgeShipPass', 'actCelestialUpgrade', 'actCancelCelestial','actPuzzleCelestial'),
        "transitions"       => array( "nextState" => STATE_PLAYER_OUSTING, 'choice' => STATE_OUSTED_PLAYER_CHOICE, "forgeShip" => STATE_OUSTED_FORGE_SHIP )
    ),
	
    /*
     * Application of the effects of the exploit
     *
     * addition of ressource, addition of power
     * If ressource or second action played, end turn (function to do)
     *
    **/
    STATE_EXPLOIT_EFFECT => array(
        "name"              => "exploitEffect",
        "description"       => clienttranslate('Effects are being played'),
        "descriptionmyturn" => clienttranslate('Effects are being played'),
        "type"              => "activeplayer",
        "args"              => "argExploitEffect",
        "action"            => "stEffectExploit",
        "possibleactions"   => array("actBuyForge", "actExploitEnigma", "actExploitBoar", 'actUseTritonToken', 'actAutoHammer', 'actBuyExploit', 'actCelestialUpgrade', 'actForgeNymphPass', 'actAncestorSelect'), 
        "transitions"       => array( "playerSecondAction" => STATE_SECOND_ACTION, "endPlayerTurn" =>  STATE_END_PLAYER_TURN, "choice" => STATE_EXPLOIT_RESSOURCE,
                                      "exploitEffect" =>STATE_EXPLOIT_EFFECT, "forgeShip" => STATE_EXPLOIT_FORGE_SHIP, "forgeBoar" => STATE_EXPLOIT_FORGE_BOAR, "nextState" => STATE_EXPLOIT_EFFECT)
    ),

    STATE_EXPLOIT_RESSOURCE => array(
        "name"              => "exploitRessource",
        "description"       => clienttranslate('Players are choosing resources'),
        "descriptionmyturn" => clienttranslate('${you} choose the resources for the side ${loyalty}'),
        "type"              => "multipleactiveplayer",
        //"action"            => "stExploitRessource",
        "args"              =>  "argExploitRessource",
        "possibleactions"   => array("actExploitRessource", 'actSideChoice', 'actActionChoice', 'actUseCerberusToken', 'actUseTritonToken', 'actAutoHammer', 'actChooseMazePath', 'actChooseTreasure', 'actMazePowerConfirm', 'actPuzzleCelestial', 'actPuzzleMaze', 'actRessourceChoice'), 
        "transitions"       => array( "nextState" => STATE_EXPLOIT_EFFECT, 'choice' => STATE_EXPLOIT_RESSOURCE, "forgeShip" => STATE_EXPLOIT_FORGE_SHIP)
    ),
  
	/*
     * If users have thrown a Ship side
     *
     * - Enable active players, then each player in turn order
     * - Can forge or pass
     *
    **/
    STATE_EXPLOIT_FORGE_SHIP => array(
        "name"              => "exploitForgeShip",
        "description"       => clienttranslate('[${ship}] ${actplayer} may forge a side'),
        "descriptionmyturn" => clienttranslate('[${ship}] ${you} may forge 1 side (cost ${minusCost})'),
        "type"              => "multipleactiveplayer",
        "args"              => "argsForgeShip",
        "possibleactions"   => array('actBuyForge', 'actForgeShipPass', 'actUseTritonToken', 'actAutoHammer', 'actCelestialUpgrade', 'actCancelCelestial','actPuzzleCelestial'),
        "transitions"       => array( "nextState" => STATE_EXPLOIT_EFFECT, 'choice' => STATE_EXPLOIT_RESSOURCE, "forgeShip" => STATE_EXPLOIT_FORGE_SHIP/*, "exploitEffect" => STATE_EXPLOIT_EFFECT*/)
    ),
    
    /*
     * If users have thrown a Ship side
     *
     * - Enable active players, then each player in turn order
     * - Can forge or pass
     *
    **/
    STATE_EXPLOIT_FORGE_BOAR => array(
        "name"              => "exploitForgeBoar",
        "description"       => clienttranslate('${actplayer} is forging a boar side'),
        "descriptionmyturn" => clienttranslate('${you} must forge the selected boar side'),
        "type"              => "multipleactiveplayer",
        "args"              => "argsForgeBoar",
        "possibleactions"   => array('actBuyForge', 'actAutoHammer'),
        "transitions"       => array( "exploitEffect" => STATE_EXPLOIT_EFFECT)
    ),
	
    /*
     * Stuff at the end of active player turn
     *
     * - change active player ?
     *
    **/
    STATE_END_PLAYER_TURN => array(
        "name"        => "endPlayerTurn",
        "description" => "",
        "type"        => "game",
        "action"      => "stEndPlayerTurn",
        "transitions" => array( "nextPlayer" => STATE_BEGIN_PLAYER_TURN, "nextTurn" => STATE_BEGIN_TURN, "endScoring" =>  STATE_SCORING_END)
    ),

    STATE_SCORING_END => array(
        "name"                  => "endScoring",
        "description"           => "",
        "type"                  => "game",
        "action"                => "stEndScoring",
        "transitions"           => array( "endGame" => STATE_END_GAME),
          //"transitions"       => array( "poule" => 98),
        "updateGameProgression" => true, 
    ),
    
    //98 => array(
    //    "name"              => "poule",
    //    "description"       => clienttranslate("End of poule"),
    //    "descriptionmyturn" => clienttranslate('Effects are being played'),
    //    "type"              => "activeplayer",
    //    "action"            => "stPoule",
    //    "possibleactions"   => array("actExploitRessource"), 
    //    "transitions" => array( "endGame" => STATE_END_GAME),
    //  ),

    // Final state.
    // Please do not modify.
    STATE_END_GAME => array(
        "name"        => "gameEnd",
        "description" => clienttranslate("End of game"),
        "type"        => "manager",
        "action"      => "stGameEnd",
        "args"        => "argGameEnd"
    ),

);



