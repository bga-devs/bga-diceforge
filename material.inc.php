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
 * material.inc.php
 *
 * diceforge game material description
 *
 * Here, you can describe the material of your game with PHP variables.
 *
 * This file is loaded in your game logic class constructor, ie these variables
 * are available everywhere in your game logic code.
 *
 */


/*

Example:

$this->card_types = array(
    1 => array( "card_name" => ...,
                ...
              )
);

*/

$this->exploit_slot = ["M1","M2","M3","M4","M5","M6","M7","M8","F1","F2","F3","F4","F5","F6","F7"];
$this->exploit_draft = ["M1","F1","M2","F2","M3","F3","M4","F4","M5","F5","M6","F6","M7","F7","M8"];
$this->exploit_cost = [
					   "M1" => ["costFire" => 0, 'costMoon' => 1],
					   "M2" => ["costFire" => 0, 'costMoon' => 1],
					   "M3" => ["costFire" => 0, 'costMoon' => 2],
					   "M4" => ["costFire" => 0, 'costMoon' => 3],
					   "M5" => ["costFire" => 0, 'costMoon' => 4],
					   "M6" => ["costFire" => 0, 'costMoon' => 5],
					   "M7" => ["costFire" => 0, 'costMoon' => 6],
					   "M8" => ["costFire" => 5, 'costMoon' => 5],
					   "F1" => ["costFire" => 1, 'costMoon' => 0],
					   "F2" => ["costFire" => 1, 'costMoon' => 0],
					   "F3" => ["costFire" => 2, 'costMoon' => 0],
					   "F4" => ["costFire" => 3, 'costMoon' => 0],
					   "F5" => ["costFire" => 4, 'costMoon' => 0],
					   "F6" => ["costFire" => 5, 'costMoon' => 0],
					   "F7" => ["costFire" => 6, 'costMoon' => 0]
					   ];

$this->decks = array(
    "beginner" => array ("hammer", "chest", "doe", "satyres", "passeur", "invisible", "claw", "hydra", "ancient", "grass", "owl", "minotaure", "medusa", "mirror", "enigma"),
    "alternate" => array ("hammer", "chest", "bear", "redBoar", "yellowBoar", "greenBoar", "blueBoar", "cerberus", "invisible", "sentinel", "typhon", "ancient", "grass", "ship", "shield", "triton", "mirror", "cyclops"),
    "promo" => array ("magicSeagull", "nymphe", "hydraPromo"),
    "titan_reco" => array ("scepter", "wind", "ancestor", "rightHand", "merchant", "light", "omniscient", "leftHand", "titan"),
    "titan_mandatory" => array("yellowMemory", "redMemory", "blueMemory", "greenMemory", "oracle", "chaos", "dogged", "guardian", "yellowMisfortune", "redMisfortune", "blueMisfortune", "greenMisfortune"),
    "goddess_reco" => array("twins", "companion", "celestial", "mists", "eternalNight", "woodNymph", "tree", "goldsmith", "trident", "eternalFire", "goddess"),
    "goddess_mandatory" => array("moonGolem", "sunGolem", "timeGolem", "greatGolem"),
    "rebellion_cards" => array("scepter", "wind", "ancestor", "rightHand", "merchant", "light", "omniscient", "leftHand", "titan", "twins", "companion", "celestial", "mists", "eternalNight", "woodNymph", "tree", "goldsmith", "trident", "eternalFire", "goddess"),
	"2players" => array("scepter", "companion", "doe", "celestial", "cerberus", "invisible", "rightHand", "merchant", "woodNymph", "ship", "shield", "omniscient", "mirror", "enigma", "goddess"),
	"bisRepetita" => array("hammer", "companion", "doe", "celestial", "cerberus", "invisible", "claw", "tree", "grass", "owl", "light", "goldsmith", "trident", "eternalFire", "titan"),
	"forge" => array("twins", "chest", "doe", "redBoar", "yellowBoar", "greenBoar", "blueBoar", "ancestor", "invisible", "claw", "merchant", "woodNymph", "ship", "shield", "omniscient", "mirror", "enigma", "typhon"),
	"polyvalence" => array("scepter", "chest", "doe", "satyres", "cerberus", "invisible", "sentinel", "merchant", "grass", "owl", "light", "triton", "mirror", "cyclops", "goddess"),
	"tournament1" => array("scepter", "companion", "doe", "celestial", "ancestor", "invisible", "rightHand", "tree", "woodNymph", "owl", "light", "omniscient", "trident", "cyclops", "typhon"),
	"tournament2" => array("hammer", "chest", "bear", "wind", "mists", "invisible", "eternalNight", "ancient", "woodNymph", "ship", "minotaure", "goldsmith", "trident", "leftHand", "hydraPromo"),
    'challenge2021' => [
    'hammer',
    'chest',
    'doe',
    'wind',
    'mists',
    'invisible',
    'sentinel',
    'goddess',
    'ancient',
    'woodNymph',
    'owl',
    'minotaure',
    'omniscient',
    'mirror',
    'eternalFire',
  ],
);

$this->decks['random'] = array_unique(array_merge($this->decks['beginner'], $this->decks['alternate']));
$this->ressourceToText = array ('gold' => '[G]', 'moonshard' => '[MS]', 'fireshard' => '[FS]', 'hammer' => '[H]', 'vp' => '[VP]', 'maze' => '[M]', 'loyalty' => '[L]', 'ancientshard' => '[AS]');
$this->shields = array('blueShield', 'redShield', 'greenShield', 'yellowShield', 'titanBlueShield', 'titanYellowShield', 'titanGreenShield', 'titanRedShield');

// Definition of the type of cards
// attributes :
// 	Name : name of the card
// 	VP : number of victory point
// 	actionType	: recurrent (each time it is the player turn), immediate (done when the card is taken)
// 	position : initial position on the board
// 	costFire : cost in fire
//	costMoon : cost in moon
//	action : action than can be played (refer to XXXXX)
//  nbStep: nunmber of times we must go to the state exploitEffect
//  island: position where the pawn must be

$boar_description = clienttranslate("[instant] : Take the face matching your Tenacious Boar card from the Temple Gardens. Choose another player as the “face bearer”. That player must immediately forge this die face onto one of his dice (he chooses which die face to replace). This die face cannot be removed from the die for the rest of the game.<br/>[hourglass] : Gain 1 [FS], 1 [MS] or 3 [VP]. Activation condition: Each time the effects of the matching die face are applied.<br />Note: The [hourglass] effect does not suffer the penalty of the Minotaur card.");
$boar_power_description = clienttranslate("[hourglass] : Gain 1 [FS], 1 [MS] or 3 [VP] each time the effects of the matching die face are applied");
$misfortune_description = clienttranslate("[instant] : Take the face matching this card from the Forest. Choose another player as the bearer of this die face. That player must immediately forge that die face on one of their dice. They cannot remove it until the end of the game.<br /> [hourglass] – <b>activation conditions</b>: Each time that the effects associated to this card are activated.<br />[hourglass] : Apply the effects of both dice of the bearer of the die face as if you were receiving divine blessings.");
$misfortune_power_description = clienttranslate("[hourglass] : Apply the effects of both dice of the bearer of the die face as if you were receiving divine blessings.");
$memory_description = clienttranslate("[instant] : Take from the reserve the 2 Memory tokens matching the color of this card. Choose a face on each token (2 [AS] + 1 [MS] or 2 [L] + 1 [FS]) then place it with this face-up on any Island. The two tokens must be placed on two different Islands.<br />[hourglass] - <b>activation conditions</b>: When you perform a Heroic feat on an Island that includes one of the tokens matching this card. That token is then discarded.<br />[hourglass] : Gain the reward showed by the token.<br />Note: If some other tokens of other color are on an island (belonging to you or an opponent) you can still place one of your tokens on the same island.");
$memory_power_description = clienttranslate("After having performed a Heroic feat on an Island that includes one of the tokens matching this card. Gain the reward showed by the token and then discard it.");


$this->exploit_types = array (
	"hammer" => array(
		'name'        => clienttranslate("The Blacksmith's Hammer"),
		'VP'          => 0,
		'actionType'  => 'immediate',
		'position'    => 'M1',
		'costFire'    => 0,
		'costMoon'    => 1,
		'island'      => 1,
		'nbStep'      => 1,
		'action'      => 'initHammer',
        'deckOption'  => 'all',
		'description' => clienttranslate("A [H] is attached to your inventory. From now on, each time you gain [G] by any means, you may choose not to add all the [G] to your reserve. Instead, you may spend some or all of the [G] to advance your Hammer token that many spaces along the Hammer track. If the Hammer token reaches the final space, gain 10 [VP], flip the Hammer token to its “II” side and place it back on its starting space, ready to move along the track again. If the Hammer reaches the final space again, gain 15 [VP], place your Hammer card on the pile of cards with no permanent effects and set your Hammer token aside."),
	),
	"chest" => array(
		'name'       => clienttranslate("Chest"),
		'VP'         => 2,
		'actionType' => 'immediate',
		'position'   => 'M2',
		'costFire'   => 0,
		'costMoon'   => 1,
		'island'     => 1,
        'nbStep'     => 1,
		'action'     => 'increaseResLimit',
        'deckOption'  => 'all',
		'description' => clienttranslate("A chest is added to your inventory and expands your inventory to 4 [G], 3 [FS] and 3 [MS]."),
	),
	"doe" => array(
		'name'       => clienttranslate("The Silver Hind"),
		'VP'         => 2,
		'actionType' => 'recurrent',
		'position'   => 'M3',
		'costFire'   => 0,
		'costMoon'   => 2,
		'island'     => 2,
        'nbStep'     => 0,
		'action'     => 'throw1',
        'deckOption'  => 'beginner',
		'description' => clienttranslate("[instant] : No effect.<br />[reinforcement] : You may receive a <b>minor blessing</b>."),
        'power_description' => clienttranslate("You may receive a <b>minor blessing</b>"),
	),
    "bear" => array(
		'name'       => clienttranslate("The Great Bear"),
		'VP'         => 2,
		'actionType' => '',
		'position'   => 'M3',
		'costFire'   => 0,
		'costMoon'   => 2,
		'island'     => 2,
        'nbStep'     => 0,
		'action'     => '',
        'deckOption'  => 'alternate',
		'description' => clienttranslate("[instant] : No effect.<br />[hourglass] : Gain 3 [VP] <b>each time</b> you oust a hero OR you are ousted by another hero"),
		'power_description' => clienttranslate("[hourglass] : Gain 3 [VP] <b>each time</b> you oust a hero OR you are ousted by another hero"),
	),
	"satyres" => array(
		'name'       => clienttranslate("Satyrs"),
		'VP'         => 6,
		'actionType' => 'immediate',
		'position'   => 'M4',
		'costFire'   => 0,
		'costMoon'   => 3,
		'island'     => 2,
        'nbStep'     => 1,
		'action'     => 'steal2',
        'deckOption'  => 'beginner',
		'description' => clienttranslate("All other players roll their dice, place them back on their Inventory, but do not apply their effects. Then, you choose 2 of the rolled faces and apply their effects, as if receiving a divine blessing."),
	),
    "greenBoar" => array(
		'name'              => clienttranslate("Tenacious Boar"),
		'VP'                => 4,
		'actionType'        => 'immediate',
		'position'          => 'M4',
		'costFire'          => 0,
		'costMoon'          => 3,
		'island'            => 2,
		'nbStep'            => 1,
		'action'            => 'boarForge',
		'deckOption'        => 'alternate',
		'description'       => $boar_description,
		'power_description' => $boar_power_description,
	),
    "redBoar" => array(
		'name'              => clienttranslate("Tenacious Boar"),
		'VP'                => 4,
		'actionType'        => 'immediate',
		'position'          => 'M4',
		'costFire'          => 0,
		'costMoon'          => 3,
		'island'            => 2,
		'nbStep'            => 1,
		'action'            => 'boarForge',
		'deckOption'        => 'alternate',
		'description'       => $boar_description,
		'power_description' => $boar_power_description,
	),
    "blueBoar" => array(
		'name'              => clienttranslate("Tenacious Boar"),
		'VP'                => 4,
		'actionType'        => 'immediate',
		'position'          => 'M4',
		'costFire'          => 0,
		'costMoon'          => 3,
		'island'            => 2,
		'nbStep'            => 1,
		'action'            => 'boarForge',
		'deckOption'        => 'alternate',
		'description'       => $boar_description,
		'power_description' => $boar_power_description,
	),
    "yellowBoar" => array(
		'name'              => clienttranslate("Tenacious Boar"),
		'VP'                => 4,
		'actionType'        => 'immediate',
		'position'          => 'M4',
		'costFire'          => 0,
		'costMoon'          => 3,
		'island'            => 2,
		'nbStep'            => 1,
		'action'            => 'boarForge',
		'deckOption'        => 'alternate',
		'description'       => $boar_description,
		'power_description' => $boar_power_description,
	),
	"passeur" => array(
		'name'       => clienttranslate("Ferryman"),
		'VP'         => 12,
		'actionType' => '',
		'position'   => 'M5',
		'costFire'   => 0,
		'costMoon'   => 4,
		'island'     => 3,
        'nbStep'     => 0,
		'action'     => '',
        'deckOption'  => 'beginner',
		'description' => clienttranslate("No effect, only [VP]"),
	),
	"cerberus" => array(
		'name'       => clienttranslate("Cerberus"),
		'VP'         => 6,
		'actionType' => 'immediate',
		'position'   => 'M5',
		'costFire'   => 0,
		'costMoon'   => 4,
		'island'     => 3,
        'nbStep'     => 1,
		'action'     => 'tokenCerberus',
        'deckOption'  => 'alternate',
		'description' => clienttranslate("Take a Cerberus single-use token and place it in front of you.<br />After receiving a <b>divine blessing</b> or a <b>minor blessing</b>, you may discard the Cerberus token to apply the result of your rolled dice a second time.<br />When discarded, place the Cerberus token facedown near your card piles.<br />Note: If the die result offers choices, you may choose different options when applying the result the second time.<br />Note 2: You may use only one Cerberus token per die roll."),
        'power_description' => clienttranslate("Use the token after a die roll to apply the result of your rolled dice a second time. Note: If the die result offers choices, you may choose different options when applying the result the second time."),
	),
	"invisible" => array(
		'name'       => clienttranslate("Helmet of Invisibility"),
		'VP'         => 4,
		'actionType' => 'immediate',
		'position'   => 'M6',
		'costFire'   => 0,
		'costMoon'   => 5,
		'island'     => 3,
        'nbStep'     => 1,
		'action'     => 'side3x',
        'deckOption'  => 'all',
		'description' => clienttranslate("Take a [triple] from the Temple Gardens and immediately <b>forge</b> it onto one of your dice."),
	),
	"claw" => array(
		'name'       => clienttranslate("Cancer"),
		'VP'         => 8,
		'actionType' => 'immediate',
		'position'   => 'M7',
		'costFire'   => 0,
		'costMoon'   => 6,
		'island'     => 4,
        'nbStep'     => 2,
		'action'     => 'fullThrow2',
        'deckOption'  => 'beginner',
		'description' => clienttranslate("Receive 2 <b>divine blessings</b> in a row."),
	),
	"sentinel" => array(
		'name'       => clienttranslate("Sentinel"),
		'VP'         => 6,
		'actionType' => 'immediate',
		'position'   => 'M7',
		'costFire'   => 0,
		'costMoon'   => 6,
		'island'     => 4,
        'nbStep'     => 2,
		'action'     => 'fullThrow2Transform',
        'deckOption'  => 'alternate',
		'description' => clienttranslate("Receive 2 <b>divine blessings</b> in a row. Each time you gain [FS] or [MS] you may choose not to add them to your reserve, in which case, gain 2 [VP] per [FS] or [MS]."),
	),
	"ancient" => array(
		'name'       => clienttranslate("The Elder"),
		'VP'         => 0,
		'actionType' => 'recurrent',
		'position'   => 'F1',
		'costFire'   => 1,
		'costMoon'   => 0,
		'island'     => 7,
        'nbStep'     => 0,
		'action'     => 'goldToVP',
        'deckOption'  => 'all',
		'description' => clienttranslate("[instant] : No effect.<br />[reinforcement] : You may spend 3 [G] to gain 4 [VP]."),
        'power_description' => clienttranslate("You may spend 3 [G] to gain 4 [VP]."),
	),
	"grass" => array(
		'name'       => clienttranslate("Wild Spirits"),
		'VP'         => 2,
		'actionType' => 'immediate',
		'position'   => 'F2',
		'costFire'   => 1,
		'costMoon'   => 0,
		'island'     => 7,
        'nbStep'     => 1,
		'action'     => '3G3M',
        'deckOption'  => 'all',
		'description' => clienttranslate("Gain 3 [G] and 3 [MS]."),
	),
    "owl" => array(
		'name'       => clienttranslate("The Guardian's Owl"),
		'VP'         => 4,
		'actionType' => 'recurrent',
		'position'   => 'F3',
		'costFire'   => 2,
		'costMoon'   => 0,
		'island'     => 6,
        'nbStep'     => 0,
		'action'     => 'choose1GMF',
        'deckOption'  => 'beginner',
		'description' => clienttranslate("[instant] : No effect.<br />[reinforcement] : Gain 1 [G], 1 [FS] or 1 [MS]."),
        'power_description' => clienttranslate("Gain 1 [G], 1 [FS] or 1 [MS]."),
	),
	"ship" => array(
		'name'       => clienttranslate("Celestial ship"),
		'VP'         => 4,
		'actionType' => 'immediate',
		'position'   => 'F3',
		'costFire'   => 2,
		'costMoon'   => 0,
		'island'     => 6,
        'nbStep'     => 1,
		'action'     => 'sideShip',
        'deckOption'  => 'alternate',
		'description' => clienttranslate("Take a [ship] die face from the Temple Gardens and immediately <b>forge</b> it onto one of your dice.<br/>When you roll the face, you may take and immediately forge one die face from the Sanctuary,spending the required [G] minus 2 [G].<br/>Note: If two or more players apply the effect of [ship] during a the start of a turn, resolve the effects in turn order starting with the active player.<br/> [ship] + [triple] : You may take and forge a die face from the Sanctuary with a discount of 6 [G]."),
	),
	"minotaure" => array(
		'name'       => clienttranslate("Minotaur"),
		'VP'         => 8,
		'actionType' => 'immediate',
		'position'   => 'F4',
		'costFire'   => 3,
		'costMoon'   => 0,
		'island'     => 6,
        'nbStep'     => 1,
		'action'     => 'looseThrow',
        'deckOption'  => 'beginner',
		'description' => clienttranslate("All other players roll their dice, place them back on their Inventory, and apply their effects, but with the following changes: <br/>&bull; All die faces that normally provide resources (including [VP]) cause them to be lost instead.<br />&bull; Celestial ship [ship] faces have no effect.<br />Note: If a particular reserve track reaches or is already at zero, any additional loss of resources is ignored.<br />Note 2: If a player can choose which resource to lose, he may minimize his loss by choosing a resource of which he has little or none."),
	),
    "shield" => array(
		'name'       => clienttranslate("The Guardian's shield"),
		'VP'         => 6,
		'actionType' => 'immediate',
		'position'   => 'F4',
		'costFire'   => 3,
		'costMoon'   => 0,
		'island'     => 6,
        'nbStep'     => 1,
		'action'     => 'shieldForge',
        'deckOption'  => 'alternate',
		'description' => clienttranslate("Choose a [blueShield][yellowShield][redShield][greenShield] die face from the Temple Gardens and immediately <b>forge</b> it onto one of your dice.<br/> > If you gain a resource that matches the one on the shield die face, gain 5 [VP].<br/> > If you gain a different resource than the one shown on the shield die face, gain the resource shown on the bottom."),
	),
	"medusa" => array(
		'name'       => clienttranslate("Gorgon"),
		'VP'         => 14,
		'actionType' => '',
		'position'   => 'F5',
		'costFire'   => 4,
		'costMoon'   => 0,
		'island'     => 5,
        'nbStep'     => 0,
		'action'     => '',
        'deckOption'  => 'beginner',
		'description' => clienttranslate("No effect, only [VP]."),
	),
    "triton" => array(
		'name'       => clienttranslate("Triton"),
		'VP'         => 8,
		'actionType' => 'immediate',
		'position'   => 'F5',
		'costFire'   => 4,
		'costMoon'   => 0,
		'island'     => 5,
        'nbStep'     => 1,
		'action'     => 'tokenTriton',
        'deckOption'  => 'alternate',
		'description' => clienttranslate("Take a Triton single-use token. Anytime during your turn as the active player, you may discard the Triton token to gain 2 [FS], 2 [MS], or 6 [G]. When discarded, place the Triton token facedown near your card piles."),
        'power_description' => clienttranslate("Single use token: Anytime during your turn as the active player, you may discard the Triton token to gain 2 [FS], 2 [MS], or 6 [G]."),
	),
	"mirror" => array(
		'name'       => clienttranslate("Mirror of the Abyss"),
		'VP'         => 10,
		'actionType' => 'immediate',
		'position'   => 'F6',
		'costFire'   => 5,
		'costMoon'   => 0,
		'island'     => 5,
        'nbStep'     => 1,
		'action'     => 'sideMirror',
        'deckOption'  => 'all',
		'description' => clienttranslate("Take a [mirror] from the Temple Gardens and immediately <b>forge</b> it onto one of your dice. <br/> When you roll the face, copy the effect(s) of a rolled face on an opponent’s die.<br/>[mirror] + [triple] : First choose which die face to copy and then multiply it."),
	),
	"enigma" => array(
		'name'       => clienttranslate("Sphinx"),
		'VP'         => 10,
		'actionType' => 'immediate',
		'position'   => 'F7',
		'costFire'   => 6,
		'costMoon'   => 0,
		'island'     => 4,
        'nbStep'     => 4,
		'action'     => '4Throws',
        'deckOption'  => 'beginner',
		'description' => clienttranslate("Receive 4 <b>minor blessings</b> in a row (use the same die for all 4 blessings)."),
	),
	"cyclops" => array(
		'name'       => clienttranslate("Cyclops"),
		'VP'         => 8,
		'actionType' => 'immediate',
		'position'   => 'F7',
		'costFire'   => 6,
		'costMoon'   => 0,
		'island'     => 4,
        'nbStep'     => 4,
		'action'     => '4ThrowsTransform',
        'deckOption'  => 'alternate',
		'description' => clienttranslate("Receive 4 <b>minor blessings</b> in a row.<br />Each time you gain [G], you may choose not to add them to your reserve, in which case, gain 1 [VP] per [G].<br />(Use the same die for all 4 blessings.)"),
	),
	"hydra" => array(
		'name'       => clienttranslate("Hydra"),
		'VP'         => 26,
		'actionType' => '',
		'position'   => 'M8',
		'costFire'   => 5,
		'costMoon'   => 5,
		'island'     => 4,
        'nbStep'     => 0,
		'action'     => '',
        'deckOption'  => 'beginner',
		'description' => clienttranslate("No effect, only [VP]."),
	),
    "typhon" => array(
		'name'       => clienttranslate("Typhon"),
		'VP'         => 16,
		'actionType' => 'immediate',
		'position'   => 'M8',
		'costFire'   => 5,
		'costMoon'   => 5,
		'island'     => 4,
        'nbStep'     => 1,
		'action'     => 'scoreForgedSides',
        'deckOption'  => 'alternate',
		'description' => clienttranslate("Gain 1 [VP] for each die face you <b>forged</b> since the start of the game. (Refer to the number of discarded die faces in front of you.)"),
    ),
    "nymphe" => array(
		'name'       => clienttranslate("Nymph"),
		'VP'         => 0,
		'actionType' => 'recurrent',
		'position'   => 'F1',
		'costFire'   => 1,
		'costMoon'   => 0,
		'island'     => 7,
        'nbStep'     => 0,
		'action'     => 'nympheReinforcement',
        'deckOption'  => 'promo',
		'description' => clienttranslate("[instant] : No effect.<br />[reinforcement] : Get 2 [VP]. If you have 5 [MS] or more, you get 4 [VP] instead."),
        'power_description' => clienttranslate("Get 2 [VP]. If you have 5 [MS] or more, you get 4 [VP] instead."),
    ),
    "magicSeagull" => array(
		'name'       => clienttranslate("Magical Seagull"),
		'VP'         => 10,
		'actionType' => 'immediate',
		'position'   => 'M4',
		'costFire'   => 0,
		'costMoon'   => 3,
		'island'     => 2,
        'nbStep'     => 1,
		'action'     => 'diceSwap',
        'deckOption'  => 'promo',
		'description' => clienttranslate("[instant]: For the whole of the next <b>turn</b>, each player uses the dice of the player to their right."),
    ),
	"harpy" => array(
		'name'       => clienttranslate("Harpy"),
		'VP'         => 27,
		'actionType' => '',
		'position'   => 'M8',
		'costFire'   => 5,
		'costMoon'   => 5,
		'island'     => 4,
        'nbStep'     => 0,
		'action'     => '',
        'deckOption'  => '',
		'description' => clienttranslate("No effect, only [VP]."),
	),
	"chimera" => array(
		'name'       => clienttranslate("Chimera"),
		'VP'         => 28,
		'actionType' => '',
		'position'   => 'M8',
		'costFire'   => 5,
		'costMoon'   => 5,
		'island'     => 4,
        'nbStep'     => 0,
		'action'     => '',
        'deckOption'  => '',
		'description' => clienttranslate("No effect, only [VP]."),
	),
    "monsterMother" => array(
		'name'       => clienttranslate("Mother of monsters"),
		'VP'         => 29,
		'actionType' => '',
		'position'   => 'M8',
		'costFire'   => 5,
		'costMoon'   => 5,
		'island'     => 4,
        'nbStep'     => 0,
		'action'     => '',
        'deckOption'  => '',
		'description' => clienttranslate("No effect, only [VP]."),
	),
    "hydraPromo" => array(
		'name'       => clienttranslate("Hydra, Harpy, Chimera, Mother of Monsters"),
		'VP'         => 0,
		'actionType' => '',
		'position'   => 'M8',
		'costFire'   => 5,
		'costMoon'   => 5,
		'island'     => 4,
        'nbStep'     => 0,
		'action'     => '',
        'deckOption'  => 'promo',
		'description' => clienttranslate("No effect, only [VP]. <br/><b>2 players</b>: Hydra 26 [VP], Harpy 27 [VP] <br/><b>3 players</b>: Hydra 26 [VP], Harpy 27 [VP], Chimera 28 [VP]<br/><b>4 players</b>: Hydra 26 [VP], Harpy 27 [VP], Chimera 28 [VP], Mother of monsters 29 [VP]"),
	),
    // Rebellion
    // Classical cards
    "twins" => array(
		'name'       => clienttranslate("The Twins"),
		'VP'         => 2,
		'actionType' => '',
		'position'   => 'M1',
		'costFire'   => 0,
		'costMoon'   => 1,
		'island'     => 1,
        'nbStep'     => 0,
		'action'     => '',
        'deckOption'  => 'rebellion',
		'description' => clienttranslate("[hourglass] - activation : when you receive a <b>divine blessing</b>, a <b>minor blessing</b>, or right after you rolled the Celestial Die, but before applying its effects.<br />[hourglass] : You may spend 3 [G] (once per card) to ignore the effect of one of your dice (including the Celestial Die). Reroll that die, then apply the new effect. Then, gain 1 [MS] or 1 [VP].<br />Note: If you have multiple copies of this card, you may use them, one after the other, to reroll the same die several times. In that case, you spend 3 [G] and gain 1 [MS] or 1 [VP] for each “The Twins” card that you use. If you reroll the same die multiple times, apply only the effect of the final roll."),
        'power_description' => '',
    ),
    'scepter' => array(
        'name' => clienttranslate("The Blacksmith’s Scepter"),
        'VP' => 2,
        'actionType' => 'immediate',
        'position' => 'M1',
        'costFire' => 0,
        'costMoon' => 1,
        'island' => 1,
        'nbStep' => 1,
        'action' => 'initScepter',
        'deckOption' => '',
        'description' => clienttranslate("A [S] is attached to your inventory.<br /> From now on, each time you gain [G], you can add it to your Hero Inventory reserve as usual, or use it (partially or completely) to advance that many spaces on the Scepter card. Any [G] kept on the Scepter card may be spent as usual. Consider your Scepter card as a secondary reserve.<br />If the Scepter token reaches or exceeds the 4th space of the Scepter course, then you may reset it to 0 to spend it like 1 [MS] or 1 [FS]. This shard can be spent to buy a Heroic feat card or pay the cost of an extra action.<br /> If the Scepter token reaches the 6th space of the Scepter course, then you may reset it to 0 to spend it like 2 [MS] or 2 [FS]. This shard can be spent to buy a Heroic feat card or pay the cost of an extra action.<br />The shards that you gain through this card may be used <b>only during your turn</b>. These shards may be spent alone or combined with other shards from your reserve. You may not add them to your reserve, though."),
        'power_description' => clienttranslate('From now on, each time you gain [G], you can add it to your Hero Inventory reserve as usual, or use it (partially or completely) to advance that many spaces on the Scepter card. Any [G] kept on the Scepter card may be spent as usual. Consider your Scepter card as a secondary reserve.<br />If the Scepter token reaches or exceeds the 4th space of the Scepter course, then you may reset it to 0 to spend it like 1 [MS] or 1 [FS]. This shard can be spent to buy a Heroic feat card or pay the cost of an extra action.<br /> If the Scepter token reaches the 6th space of the Scepter course, then you may reset it to 0 to spend it like 2 [MS] or 2 [FS]. This shard can be spent to buy a Heroic feat card or pay the cost of an extra action.<br />The shards that you gain through this card may be used <b>only during your turn</b>. These shards may be spent alone or combined with other shards from your reserve. You may not add them to your reserve, though.'),
    ),
    'companion' => array(
        'name' => clienttranslate("The Companion"),
        'VP' => 2,
        'actionType' => 'recurrent',
        'position' => 'M2',
        'costFire' => 0,
        'costMoon' => 1,
        'island' => 1,
        'nbStep' => 1,
        'action' => 'initCompanion',
        'deckOption' => '',
        'description' => clienttranslate("[reinforcement] : Move the Companion token one space forward. Once the token reaches the last space, you can no longer use this [reinforcement] effect.<br />At any time when you’re the Active player, you may decide to gain the resources shown in the space occupied by the Companion token. If you do, the card has no effect for the remainder of the game.<br /><br /><b>Steps</b><br />0 [reinforcement] > 0 [FS] 0 [VP]<br />1 [reinforcement] > 1 [FS] 1 [VP]<br />2 [reinforcement] > 2 [FS] 2 [VP]<br />3 [reinforcement] > 3 [FS] 3 [VP]<br />4 [reinforcement] > 4 [FS] 4 [VP]<br />5 [reinforcement] > 5 [FS] 5 [VP]"),
        'power_description' => clienttranslate("Move the Companion token one space forward. Once the token reaches the last space, you can no longer use this [reinforcement] effect.<br /><br /><b>Steps</b><br />0 [reinforcement] > 0 [FS] 0 [VP]<br />1 [reinforcement] > 1 [FS] 1 [VP]<br />2 [reinforcement] > 2 [FS] 2 [VP]<br />3 [reinforcement] > 3 [FS] 3 [VP]<br />4 [reinforcement] > 4 [FS] 4 [VP]<br />5 [reinforcement] > 5 [FS] 5 [VP]")
    ),
    'wind' => array(
        'name' => clienttranslate("The Wind"),
        'VP' => 6,
        'actionType' => 'immediate',
        'position' => 'M4',
        'costFire' => 0,
        'costMoon' => 3,
        'island' => 2,
        'nbStep' => 1,
        'action' => 'throwAllChooseResources',
        'deckOption' => '',
        'description' => clienttranslate("All players (including you) roll both of their dice, then place them back on their Hero Inventory. However, no one applies the effects of their dice.<br />You choose a type among the results ( [MS], [FS], [G], [VP], [L], [AS]). You immediately gain all the resources of that type among the dice results. <br />Note: The [mirror], [triple], [ship], [moonGolem] and [sunGolem] die faces do not show any resources, and therefore cannot be used by “The Wind” to gain resources.<br />Note 2: Only rewards B (light background) of [titanRedShield] and [titanYellowShield] die faces can be gained via this card. Rewards A (colored background) cannot be gained this way."),
    ),
    'celestial' => array(
        'name' => clienttranslate("The Celestial Die"),
        'VP' => 6,
        'actionType' => 'immediate',
        'position' => 'M4',
        'costFire' => 0,
        'costMoon' => 3,
        'island' => 2,
        'nbStep' => 1,
        'action' => 'throwCelestialDie',
        'deckOption' => '',
        'description' => clienttranslate("Roll the Celestial Die once and apply the effect of the rolled face"),
    ),
    'ancestor' => array(
        'name' => clienttranslate("The Ancestor"),
        'VP' => 6,
        'actionType' => 'immediate',
        'position' => 'M5',
        'costFire' => 0,
        'costMoon' => 4,
        'island' => 3,
        'nbStep' => 2,
        'action' => 'forgeVP',
        'deckOption' => '',
        'description' => clienttranslate("You may immediately forge the least expensive die face that shows [VP] from the sanctuary, <b>for free</b>.<br/> Then, gain a <b>minor blessing</b> with the die on which you forged this die face.<br/> Note: A die face shows [VP] if the symbol appears on it.<br />Note 2: If the Sanctuary has run out of die faces that show [VP], then you cannot forge a die face. However, you still receive a <b>minor blessing</b>, with any one of your dice."),
    ),
    'mists' => array(
        'name' => clienttranslate("The Mists"),
        'VP' => 6,
        'actionType' => 'immediate',
        'position' => 'M5',
        'costFire' => 0,
        'costMoon' => 4,
        'island' => 3,
        'nbStep' => 1,
        'action' => 'stealLessGold',
        'deckOption' => '',
        'description' => clienttranslate("The player that has the least [G] in their reserves (including you) loses 5 [VP]. Then you gain the 5 [VP] lost by the affected player.<br />Note: if you have less [G] than anyone else, then this card has no effect.<br />Note 2: If the affected player has less than 5 [VP], then they lose as many as possible. You gain only as many [VP] as the affected player actually lost.<br />Note 3: If multiple players are tied for the least [G], all tied players lose 5 [VP] each, and you gain the total amount of lost  [VP]."),
    ),
    'eternalNight' => array(
        'name' => clienttranslate("The Eternal Night"),
        'VP' => 8,
        'actionType' => 'immediate',
        'position' => 'M7',
        'costFire' => 0,
        'costMoon' => 6,
        'island' => 4,
        'nbStep' => 1,
        'action' => 'stealFireMoon',
        'deckOption' => '',
        'description' => clienttranslate("All other players lose 1 [MS] and 1 [FS] from their reserve. You gain these resources.<br />Note: Players simply lose what they can. If a player has 0 [MS] and/or 0 [FS], then they cannot lose them. You only gain as many as the players actually lost."),
    ),
    'rightHand' => array(
        'name' => clienttranslate("The Right Hand"),
        'VP' => 10,
        'actionType' => 'immediate',
        'position' => 'M7',
        'costFire' => 0,
        'costMoon' => 6,
        'island' => 4,
        'nbStep' => 1,
        'action' => 'convertGoldToVP',
        'deckOption' => '',
        'description' => clienttranslate("Spend as much [G] as you wish from your Hero Inventory reserve and/or from other reserves. Gain 1 [VP] for each [G] spent this way."),
    ),
    'tree' => array(
        'name' => clienttranslate("The Tree"),
        'VP' => 2,
        'actionType' => 'recurrent',
        'position' => 'F1',
        'costFire' => 1,
        'costMoon' => 0,
        'island' => 7,
        'nbStep' => 0,
        'action' => 'treeEffect',
        'deckOption' => '',
        'description' => clienttranslate("[reinforcement] : Gain 3 [G] and 1 [VP]. If you have 8 [G] or more in your Hero Inventory reserve, gain 2 [VP] instead. If you have in total 8 [G], you can choose between 3 [G] + 1 [VP] and 2 [VP]"),
        'power_description' => clienttranslate("Gain 3 [G] and 1 [VP]. If you have 8 [G] or more in your Hero Inventory reserve, gain 2 [VP] instead. If you have in total 8 [G], you can choose between 3 [G] + 1 [VP] and 2 [VP]")
    ),
    'woodNymph' => array(
        'name' => clienttranslate("The Wood Nymph"),
        'VP' => 2,
        'actionType' => 'immediate',
        'position' => 'F2',
        'costFire' => 1,
        'costMoon' => 0,
        'island' => 7,
        'nbStep' => 1,
        'action' => 'forge4G',
        'deckOption' => '',
        'description' => clienttranslate("Gain 4 [G], then buy any die face from the Sanctuary, spending its cost in [G], and forge it immediately."),
    ),
    'merchant' => array(
        'name' => clienttranslate("The Merchant"),
        'VP' => 2,
        'actionType' => 'recurrent',
        'position' => 'F1',
        'costFire' => 1,
        'costMoon' => 0,
        'island' => 7,
        'nbStep' => 0,
        'action' => 'upgradeSide',
        'deckOption' => '',
        'description' => clienttranslate("[reinforcement] : Upgrade a die face or gain 2 [VP]. <br /> Upgrading a die face means that you can replace it with a die face from a higher pool for free. The level of a pool is determined by the cost of the face.<br />To upgrade a die face by one level, choose one of your die faces, then take a die face from the pool of the next level. You may now forge that die face.<br />Note: If the chosen face has no equivalent in the Sanctuary, then it is considered a level 0.<br />Note 2: If the pool from which you would take your upgraded die face is empty, then choose a die face from the next non-empty pool.<br />Special rule: If you have multiple copies of this card, you may use the upgrade action several times, but only on the same die face, regardless of the number of cards that you want to use. The number of cards that you use will determine the level of the upgrade. If you use X “The Merchant” cards for their upgrade effect, then you’ll upgrade a given die face by X levels (at once), taking the upgraded die face from the pool that is X levels higher."),
        'power_description' => clienttranslate("Upgrade a die face or gain 2 [VP]. ")
    ),
    'light' => array(
        'name' => clienttranslate("The Light"),
        'VP' => 8,
        'actionType' => 'recurrent',
        'position' => 'F4',
        'costFire' => 3,
        'costMoon' => 0,
        'island' => 6,
        'nbStep' => 0,
        'action' => 'copySide',
        'deckOption' => '',
        'description' => clienttranslate("[reinforcement] : You may spend 3 [G] to apply the effect of any die face that is currently face-up, whether on your die or that of any other player."),
        'power_description' => clienttranslate("You may spend 3 [G] to apply the effect of any die face that is currently face-up, whether on your die or that of any other player.")
    ),
    'goldsmith' => array(
        'name' => clienttranslate("The Goldsmith"),
        'VP' => 4,
        'actionType' => 'immediate',
        'position' => 'F5',
        'costFire' => 4,
        'costMoon' => 0,
        'island' => 5,
        'nbStep' => 1,
        'action' => 'countFeats',
        'deckOption' => '',
        'description' => clienttranslate("Gain 2 [VP] for each different Heroic feat card that you bought since the beginning of the game, including this Heroic feat."),
    ),
    'omniscient' => array(
        'name' => clienttranslate("The Omniscient"),
        'VP' => 8,
        'actionType' => 'immediate',
        'position' => 'F5',
        'costFire' => 4,
        'costMoon' => 0,
        'island' => 5,
        'nbStep' => 1,
        'action' => 'countVP',
        'deckOption' => '',
        'description' => clienttranslate("Gain 2 [VP] for each die face that shows [VP] on your dice.<br />Note: A die face shows [VP] if the symbol appears on it."),
    ),
   'trident' => array(
        'name' => clienttranslate("The Abyssal Trident"),
        'VP' => 12,
        'actionType' => 'immediate',
        'position' => 'F6',
        'costFire' => 5,
        'costMoon' => 0,
        'island' => 5,
        'nbStep' => 1,
        'action' => 'forgeEverywhere',
        'deckOption' => '',
        'description' => clienttranslate("Spend all of the [G] from your Hero Inventory reserve (but not from any other reserves) and forge a die face from the Temple (Sanctuary or Garden) on one of your dice, for free.<br />Note: Die faces that could be acquired due to the effects of Heroic Feat cards that are available for this game cannot be chosen with this card.<br />Note 2: You can use “The Abyssal Trident” card even if you have 0 in your reserve."),
    ),
    'eternalFire' => array(
        'name' => clienttranslate("The Eternal Fire"),
        'VP' => 10,
        'actionType' => 'immediate',
        'position' => 'F7',
        'costFire' => 6,
        'costMoon' => 0,
        'island' => 4,
        'nbStep' => 1,
        'action' => 'gainTurn',
        'deckOption' => '',
        'description' => clienttranslate("<b>Immediately</b> take a new full turn as the active player. You’re the only player to roll dice to receive a divine blessing at the beginning of this turn"),
    ),
    'leftHand' => array(
        'name' => clienttranslate("The Left Hand"),
        'VP' => 8,
        'actionType' => 'immediate',
        'position' => 'F7',
        'costFire' => 6,
        'costMoon' => 0,
        'island' => 4,
        'nbStep' => 1,
        'action' => 'oustAll',
        'deckOption' => '',
        'description' => clienttranslate("All Hero pawns that occupy an Island are ousted, including you. All ousted players roll their dice, but only you apply the dice effects, as if you were receiving <b>divine blessing</b>. Resolve the different <b>divine blessing</b> in turn order, starting with your own dice.<br />Note: If you own the “Great Bear” card, apply its effect as soon as at least one other player is ousted, regardless of the number of players that are ousted. The effect applies once per card “Great Bear”. If you oust only your own Hero with “The Left Hand” card, you do not benefit from the “Great Bear” effect as it only triggers when ousting other heroes, or when being ousted by another hero."),
    ),
    'titan' => array(
        'name' => clienttranslate("The First Titan"),
        'VP' => 20,
        'actionType' => 'immediate',
        'position' => 'M8',
        'costFire' => 5,
        'costMoon' => 5,
        'island' => 4,
        'nbStep' => 1,
        'action' => 'freeExploit',
        'deckOption' => '',
        'description' => clienttranslate("Choose an available Heroic Feat card with a cost of 1 [MS] or 1 [FS]. Perform that Heroic feat without moving your Hero Pawn to the portal. The card’s effects are applied as normal.<br />Note: If there are no more available Heroic Feat cards with a cost of 1 [MS] or 1 [FS] then “The First Titan” card has no effect.<br />Note - <b>Titans module</b>: Completing this Heroic feat may activate the Rebellion zone effect if the player is in this zone and if the activation conditions are met."),
    ),
    'goddess' => array(
        'name' => clienttranslate("The Goddess"),
        'VP' => 18,
        'actionType' => 'immediate',
        'position' => 'M8',
        'costFire' => 5,
        'costMoon' => 5,
        'island' => 4,
        'nbStep' => 1,
        'action' => 'chooseSides',
        'deckOption' => '',
        'description' => clienttranslate("Place your dice on your Hero Inventory on the die faces of your choice. Apply the effects of the chosen faces, as if you were receiving a <b>divine blessing</b>."),
    ),
    'moonGolem' => array(
        'name' => clienttranslate("The Moon Golem"),
        'VP' => 2,
        'actionType' => 'immediate',
        'position' => 'M3',
        'costFire' => 0,
        'costMoon' => 2,
        'island' => 2,
        'nbStep' => 1,
        'action' => 'sideMoonGolem',
        'deckOption' => '',
        'description' => clienttranslate("Take a die face [moonGolem] from the Forest and forge it immediately on one of your dice."),
    ),
    'greatGolem' => array(
        'name' => clienttranslate("The Great Golem"),
        'VP' => 8,
        'actionType' => 'immediate',
        'position' => 'M6',
        'costFire' => 0,
        'costMoon' => 5,
        'island' => 3,
        'nbStep' => 1,
        'action' => 'greatGolem',
        'deckOption' => '',
        'description' => clienttranslate("Move your Golem token two spaces forward on the Goddess Board and apply the effects of each space, in order."),
    ),
    'sunGolem' => array(
        'name' => clienttranslate("The Sun Golem"),
        'VP' => 4,
        'actionType' => 'immediate',
        'position' => 'F3',
        'costFire' => 2,
        'costMoon' => 0,
        'island' => 6,
        'nbStep' => 1,
        'action' => 'sideSunGolem',
        'deckOption' => '',
        'description' => clienttranslate("Take a die face [sunGolem] from the Forest and forge it immediately on one of your dice."),
    ),
    'timeGolem' => array(
        'name' => clienttranslate("The Time Golem"),
        'VP' => 4,
        'actionType' => 'immediate',
        'position' => 'F4',
        'costFire' => 3,
        'costMoon' => 0,
        'island' => 6,
        'nbStep' => 1,
        'action' => 'timeGolem',
        'deckOption' => '',
        'description' => clienttranslate("Move your Golem token two spaces backward on the Goddess Board and apply the effects of each space, in order.<br />Note: If you pass through an intersection while moving backward, you are allowed to choose a different path than you chose originally."),
    ),
    'blueMemory' => array(
        'name' => clienttranslate("The Memory"),
        'VP' => 2,
        'actionType' => 'immediate',
        'position' => 'M2',
        'costFire' => 0,
        'costMoon' => 1,
        'island' => 1,
        'nbStep' => 3,
        'action' => 'memoryTokens',
        'deckOption' => '',
        'description' => $memory_description,
		'power_description' => $memory_power_description
    ),
    'greenMemory' => array(
        'name' => clienttranslate("The Memory"),
        'VP' => 2,
        'actionType' => 'immediate',
        'position' => 'M2',
        'costFire' => 0,
        'costMoon' => 1,
        'island' => 1,
        'nbStep' => 3,
        'action' => 'memoryTokens',
        'deckOption' => '',
        'description' => $memory_description,
		'power_description' => $memory_power_description
    ),
    'yellowMemory' => array(
        'name' => clienttranslate("The Memory"),
        'VP' => 2,
        'actionType' => 'immediate',
        'position' => 'M2',
        'costFire' => 0,
        'costMoon' => 1,
        'island' => 1,
        'nbStep' => 3,
        'action' => 'memoryTokens',
        'deckOption' => '',
        'description' => $memory_description,
		'power_description' => $memory_power_description
    ),
    'redMemory' => array(
        'name' => clienttranslate("The Memory"),
        'VP' => 2,
        'actionType' => 'immediate',
        'position' => 'M2',
        'costFire' => 0,
        'costMoon' => 1,
        'island' => 1,
        'nbStep' => 3,
        'action' => 'memoryTokens',
        'deckOption' => '',
        'description' => $memory_description,
		'power_description' => $memory_power_description
    ),
    'oracle' => array(
        'name' => clienttranslate("The Oracle"),
        'VP' => 0,
        'actionType' => 'recurrent',
        'position' => 'M3',
        'costFire' => 0,
        'costMoon' => 2,
        'island' => 2,
        'nbStep' => 0,
        'action' => '',
        'deckOption' => '',
        'description' => clienttranslate("[reinforcement] : Receive a <b>minor blessing</b>. Moreover, if you gain [AS] or [L] with this blessing, move the Allegiance tokens of every other players one space to the left (to Rebellion) on the Titans Board."),
        'power_description' => clienttranslate("Receive a <b>minor blessing</b>. Moreover, if you gain [AS] or [L] with this blessing, move the Allegiance tokens of every other players one space to the left (to Rebellion) on the Titans Board.")
    ),
    'chaos' => array(
        'name' => clienttranslate("The Chaos"),
        'VP' => 8,
        'actionType' => 'immediate',
        'position' => 'M6',
        'costFire' => 0,
        'costMoon' => 5,
        'island' => 3,
        'nbStep' => 1,
        'action' => 'sideShieldRebellion',
        'deckOption' => '',
        'description' => clienttranslate("Choose a [titanBlueShield][titanRedShield][titanYellowShield][titanGreenShield] die face  among the die faces available in the Forest and forge it immediately on one of your dice."),
    ),
    'dogged' => array(
        'name' => clienttranslate("The Dogged"),
        'VP' => 2,
        'actionType' => 'immediate',
        'position' => 'F2',
        'costFire' => 1,
        'costMoon' => 0,
        'island' => 7,
        'nbStep' => 1,
        'action' => 'sideDogged',
        'deckOption' => '',
        'description' => clienttranslate("Choose a die face [G3AS1] [L1V1G2] among the die faces available in the Forest and forge it immediately on one of your dice."),
    ),
    'guardian' => array(
        'name' => clienttranslate("The Guardian"),
        'VP' => 4,
        'actionType' => 'recurrent',
        'position' => 'F3',
        'costFire' => 2,
        'costMoon' => 0,
        'island' => 6,
        'nbStep' => 0,
        'action' => '',
        'deckOption' => '',
        'description' => clienttranslate("[reinforcement] : Gain 1 [AS] or 1 [L]."),
        'power_description' => clienttranslate("Gain 1 [AS] or 1 [L].")
    ),
    'blueMisfortune' => array(
        'name' => clienttranslate("The Mirror of Misfortune"),
        'VP' => 10,
        'actionType' => 'immediate',
        'position' => 'F6',
        'costFire' => 5,
        'costMoon' => 0,
        'island' => 5,
        'nbStep' => 1,
        'action' => 'sideMisfortune',
        'deckOption' => '',
        'description' => $misfortune_description,
		'power_description' => $misfortune_power_description,
    ),
    'yellowMisfortune' => array(
        'name' => clienttranslate("The Mirror of Misfortune"),
        'VP' => 10,
        'actionType' => 'immediate',
        'position' => 'F6',
        'costFire' => 5,
        'costMoon' => 0,
        'island' => 5,
        'nbStep' => 1,
        'action' => 'sideMisfortune',
        'deckOption' => '',
        'description' => $misfortune_description,
		'power_description' => $misfortune_power_description,
    ),
    'redMisfortune' => array(
        'name' => clienttranslate("The Mirror of Misfortune"),
        'VP' => 10,
        'actionType' => 'immediate',
        'position' => 'F6',
        'costFire' => 5,
        'costMoon' => 0,
        'island' => 5,
        'nbStep' => 1,
        'action' => 'sideMisfortune',
        'deckOption' => '',
        'description' => $misfortune_description,
		'power_description' => $misfortune_power_description,
    ),
    'greenMisfortune' => array(
        'name' => clienttranslate("The Mirror of Misfortune"),
        'VP' => 10,
        'actionType' => 'immediate',
        'position' => 'F6',
        'costFire' => 5,
        'costMoon' => 0,
        'island' => 5,
        'nbStep' => 1,
        'action' => 'sideMisfortune',
        'deckOption' => '',
        'description' => $misfortune_description,
		'power_description' => $misfortune_power_description,
    ),

);

$this->dice_sides = array(
	"G1" => array(
		'type'      => 'simple',
		'cost'      => 0,
		'ressource' => array(
			'gold' => 1
		),
	),
	"G3" => array(
		'type'      => 'simple',
		'cost'      => 2,
		'ressource' => array(
			'gold' => 3
		),
	),
	"G4" => array(
		'type'      => 'simple',
		'cost'      => 3,
		'ressource' => array(
			'gold' => 4
		),
	),
	"G6" => array(
		'type'      => 'simple',
		'cost'      => 4,
		'ressource' => array(
			'gold' => 6
		),
	),
	"FS1" => array(
		'type'       => 'simple',
		'cost'       => 3,
		'ressource' => array(
			'fireshard' => 1
		),
	),
	"FS2" => array(
		'type'      => 'simple',
		'cost'      => 8,
		'ressource' => array(
			'fireshard' => 2
		),
	),
	"MS1" => array(
		'type'      => 'simple',
		'cost'      => 2,
		'ressource' => array(
			'moonshard' => 1
		),
	),
	"MS2" => array(
		'type'      => 'simple',
		'cost'      => 6,
		'ressource' => array(
			'moonshard' => 2
		),
	),
	"V2" => array(
		'type'      => 'simple',
		'cost'      => 0,
		'ressource' => array(
			'vp' => 2
		),
	),
	"V3" => array(
		'type'      => 'simple',
		'cost'      => 8,
		'ressource' => array(
			'vp' => 3
		),
	),
	"V4" => array(
		'type'      => 'simple',
		'cost'      => 12,
		'ressource' => array(
			'vp' => 4
		),
	),
	"1Gor1FSor1MS" => array(
		'type'   => 'choice',
		'cost'   => 4,
		'ressource' => array(
			'gold'      => 1,
			'fireshard' => 1,
			'moonshard' => 1
		),
	),
	"2Gor2FSor2MS" => array(
		'type'   => 'choice',
		'cost'   => 12,
		'ressource' => array(
			'gold'      => 2,
			'fireshard' => 2,
			'moonshard' => 2
		),
	),
	"G2MS1" => array(
		'type'      => 'simple',
		'cost'      => 4,
		'ressource' => array(
			'gold'      => 2,
			'moonshard' => 1
		),
	),
	"V1FS1" => array(
		'type'      => 'simple',
		'cost'      => 4,
		'ressource' => array(
			'vp'        => 1,
			'fireshard' => 1
		),
	),
	"G3orV2" => array(
		'type'      => 'choice',
		'cost'      => 5,
		'ressource' => array(
			'gold' => 3,
			'vp'   => 2
		),
	),
	"G1V1FS1MS1" => array(
		'type'      => 'simple',
		'cost'      => 12,
		'ressource' => array(
			'gold'      => 1,
			'vp'        => 1,
			'fireshard' => 1,
			'moonshard' => 1
		),
	),
	"V2MS2" => array(
		'type'      => 'simple',
		'cost'      => 12,
		'ressource' => array(
			'vp'        => 2,
			'moonshard' => 2
		),
	),
	"mirror" => array(
		'type' => 'mirror',
        'cost' => 0,
		'ressource' => array()
	),
	"triple" => array(
		'type' => 'triple',
        'cost' => 0,
		'ressource' => array()
	),
    "ship"  => array(
        'type' => 'forge',
        'cost' => 0,
        'ressource' => array()
    ),
	"blueBoar"  => array(
        'type' => 'choice',
        'cost' => 0,
        'ressource' => array(
			'fireshard' 	=> 1,
			'moonshard'		=> 1)
    ),
	"redBoar"  => array(
        'type' => 'choice',
        'cost' => 0,
        'ressource' => array(
			'fireshard' 	=> 1,
			'moonshard'		=> 1)
    ),
	"yellowBoar"  => array(
        'type' => 'choice',
        'cost' => 0,
        'ressource' => array(
			'fireshard' 	=> 1,
			'moonshard'		=> 1)
    ),
	"greenBoar"  => array(
        'type' => 'choice',
        'cost' => 0,
        'ressource' => array(
			'fireshard' 	=> 1,
			'moonshard'		=> 1)
    ),
    "boar"  => array(
        'type' => 'choice',
        'cost' => 0,
        'ressource' => array(
			'fireshard' 	=> 1,
			'moonshard'		=> 1,
            'vp'            => 3)
    ),
    "tritonToken"  => array(
        'type' => 'choice',
        'cost' => 0,
        'ressource' => array(
            'gold'          => 6,
			'fireshard' 	=> 2,
			'moonshard'		=> 2)
    ),
	"blueShield" => array(
        'type' 				=> 'dependent',
        'cost' 				=> 0,
		'dependentRessource'=> 'moonshard',
        'ressource' 		=> array(
			'moonshard'		=> 2),
        'gainA' => array(
            'vp'                => 5
        ),
        'tooltip'           => clienttranslate('> If you gain [MS] on your other die, gain 5 [VP].<br/> > If you gain a different resource, gain 2 [MS]'),
    ),
	"yellowShield" => array(
        'type' 				=> 'dependent',
        'cost' 				=> 0,
		'dependentRessource'=> 'gold',
        'ressource' 		=> array(
			'gold'		=> 3),
        'gainA' => array(
            'vp'                => 5
        ),
        'tooltip'           => clienttranslate('> If you gain [G] on your other die, gain 5 [VP].<br/> > If you gain a different resource, gain 3 [G]'),
    ),
	"greenShield" => array(
        'type' 				=> 'dependent',
        'cost' 				=> 0,
		'dependentRessource'=> 'vp',
        'ressource' 		=> array(
			'vp'		=> 3),
        'gainA' => array(
            'vp'                => 5
        ),
        'tooltip'           => clienttranslate('> If you gain [VP] on your other die, gain 5 [VP].<br/> > If you gain a different resource, gain 3 [VP]'),
    ),
	"redShield" => array(
        'type' 				=> 'dependent',
        'cost' 				=> 0,
		'dependentRessource'=> 'fireshard',
        'ressource' 		=> array(
			'fireshard' => 2),
        'gainA'             => array(
            'vp' => 5
        ),
        'tooltip'           => clienttranslate('> If you gain [FS] on your other die, gain 5 [VP].<br/> > If you gain a different resource, gain 2 [FS]'),
    ),
    // Rebellion
    "AS1" => array(
		'type'      => 'simple',
		'cost'      => 0,
		'ressource' => array(
			'ancientshard' => 1
		),
	),
    "L1V1" => array(
		'type'      => 'simple',
		'cost'      => 0,
		'ressource' => array(
			'loyalty' => 1,
			'vp'      => 1
		),
	),
    "L1V1G2" => array(
		'type'      => 'simple',
		'cost'      => 0,
		'ressource' => array(
			'gold'  => 2,
            'loyalty'      => 1,
			'vp' => 1
		),
	),
    "G3AS1" => array(
		'type'      => 'simple',
		'cost'      => 0,
		'ressource' => array(
			'gold'         => 3,
			'ancientshard' => 1
		),
	),
    "titanBlueShield" => array(
        'type' 				=> 'dependent',
        'cost' 				=> 0,
		'dependentRessource'=> 'moonshard',
        'ressource' 		=> array(
			'ancientshard'		=> 2),
        'gainA'             => array(
            'ancientshard'		=> 2,
            'vp'                => 3
        ),
        'tooltip'           => clienttranslate('> If you gain [MS] on your other die, gain 2 [AS] and 3 [VP].<br/> > If you gain a different resource, gain 2 [AS]'),
    ),
    "titanYellowShield" => array(
        'type' 				=> 'dependent',
        'cost' 				=> 0,
		'dependentRessource'=> 'gold',
        'ressource' 		=> array(
			'loyalty'		=> 2),
        'gainA' => array(
            'loyalty'		=> 2,
            'vp'                => 3
        ),
        'tooltip'           => clienttranslate('> If you gain [G] on your other die, gain 2 [L] and 3 [VP].<br/> > If you gain a different resource, gain 2 [L]'),
    ),
    "titanRedShield" => array(
        'type' 				=> 'dependent',
        'cost' 				=> 0,
		'dependentRessource'=> 'fireshard',
        'ressource' 		=> array(
			'ancientshard'		=> 2),
        'gainA' => array(
            'ancientshard'		=> 2,
            'vp'                => 3
        ),
        'tooltip'           => clienttranslate('> If you gain [FS] on your other die, gain 2 [AS] and 3 [VP].<br/> > If you gain a different resource, gain 2 [AS]'),
    ),
    "titanGreenShield" => array(
        'type' 				=> 'dependent',
        'cost' 				=> 0,
		'dependentRessource'=> 'vp',
        'ressource' 		=> array(
			'loyalty'		=> 2),
        'gainA' => array(
            'loyalty'		=> 2,
            'vp'                => 3
        ),
        'tooltip'           => clienttranslate('> If you gain [VP] on your other die, gain 2 [L] and 3 [VP].<br/> > If you gain a different resource, gain 2 [L]'),
    ),
    "blueMisfortune"  => array(
        'type' => 'choice',
        'cost' => 0,
        'ressource' => array(
			'ancientshard' 	=> 1,
			'loyalty'		=> 1)
    ),
    "yellowMisfortune"  => array(
        'type' => 'choice',
        'cost' => 0,
        'ressource' => array(
			'ancientshard' 	=> 1,
			'loyalty'		=> 1)
    ),
    "redMisfortune"  => array(
        'type' => 'choice',
        'cost' => 0,
        'ressource' => array(
			'ancientshard' 	=> 1,
			'loyalty'		=> 1)
    ),
    "greenMisfortune"  => array(
        'type' => 'choice',
        'cost' => 0,
        'ressource' => array(
			'ancientshard' 	=> 1,
			'loyalty'		=> 1)
    ),
	"otherMisfor" => array(
        'type' => 'choice',
        'cost' => 0,
        'ressource' => array(
			'ancientshard' 	=> 1,
			'loyalty'		=> 1)
    ),
//	"tripleOtherMisfor" => array(
//        'type' => 'choice',
//        'cost' => 0,
//        'ressource' => array(
//			'ancientshard' 	=> 3,
//			'loyalty'		=> 3)
//    ),
    "moonGolem" => array(
        'type' => 'simple',
        'cost' => 0,
        'maze' => 1,
        'ressource' => array()
    ),
    "sunGolem" => array(
        'type' => 'simple',
        'cost' => 0,
        'maze' => 1,
        'ressource' => array()
    ),
    "twins"  => array(
        'type' => 'choice',
        'cost' => 0,
        'ressource' => array(
			'moonshard'		=> 1,
            'vp'            => 1)
    ),
    // celestial die
    "G12" => array(
        'type' => 'simple',
        'cost' => 0,
        'ressource' => array(
			'gold' 	=> 12
        )
    ),
    "V5" => array(
        'type' => 'simple',
        'cost' => 0,
        'ressource' => array(
			'vp' 	=> 5
        )
    ),
    "V3G3orFS1orMS1" => array(
        'type' => 'choice',
        'cost' => 0,
        'ressource' => array(
			'fireshard' => 1,
            'moonshard' => 1,
            'gold' => 3
        ),
        'bonus' => array(
            'vp' => 3
        )
    ),
    "celestialMirror" => array (
        'type' => 'celestialMirror',
        'cost' => 0
    ),
    "chooseSide" => array(
        'type' => 'chooseSide',
        'cost' => 0
    ),
    "doubleUpgrade" => array(
        'type' => 'doubleUpgrade',
        'cost' => 0
    ),
    // maze specific sides
    "G6orV3" => array(
		'type'      => 'choice',
		'cost'      => 0,
		'ressource' => array(
			'gold' => 6,
			'vp'   => 3
		),
	),
    "G3orMS1orFS1" => array(
		'type'      => 'choice',
		'cost'      => 0,
		'ressource' => array(
			'gold' => 3,
			'fireshard' => 1,
            'moonshard' => 1
		),
	),
    "MS2orV3" => array(
		'type'      => 'choice',
		'cost'      => 0,
		'ressource' => array(
			'moonshard' => 2,
			'vp'   => 3
		),
	),
    "MS2orFS2" => array(
		'type'      => 'choice',
		'cost'      => 0,
		'ressource' => array(
			'moonshard' => 2,
			'fireshard'   => 2
		),
	),
    "FS1MS1V3" => array(
		'type'      => 'simple',
		'cost'      => 12,
		'ressource' => array(
			'vp'        => 3,
			'fireshard' => 1,
			'moonshard' => 1
		),
	),
    "V15" => array(
        'type' => 'simple',
        'cost' => 0,
        'ressource' => array(
			'vp' 	=> 15
        )
    ),
    // Treasure
    "FS4" => array(
        'type' => 'simple',
        'cost' => 0,
        'ressource' => array(
			'fireshard' 	=> 4
        )
    ),
    "MS4" => array(
        'type' => 'simple',
        'cost' => 0,
        'ressource' => array(
			'moonshard' 	=> 4
        )
    ),
    "V10" => array(
        'type' => 'simple',
        'cost' => 0,
        'ressource' => array(
			'vp' 	=> 10
        )
    ),
    "V2" => array(
        'type' => 'simple',
        'cost' => 0,
        'ressource' => array(
			'vp' 	=> 2
        )
    ),
//	"tree" => array(
//        'type' => 'choice',
//        'cost' => 0,
//        'ressource' => array(
//			'gold' => 3,
//            'vp' => 1,
//        ),
//        'bonus' => array(
//            'vp' => 1
//        )
    //),

);

$this->celestialDie = ["G12", "V5", "V3G3orFS1orMS1", "celestialMirror", "chooseSide", "doubleUpgrade"];
$this->celestialInfo = [
	'name'        => 'The Celestial Die',
	'description' => clienttranslate('[G12] Gain 12 [G]<br />[V5] Gain 5 [VP]<br />[V3G3orFS1orMS1] Gain 3 [VP] and choose 3 [G] or 1 [MS] or 1 [FS]<br />[celestialMirror] Apply the effect(s) of one of your opponents’ dice, or one of your own dice<br />[chooseSide] Choose a die face from one of your dice, place it face up and apply its effect.<br />[doubleUpgrade] Upgrade one of your dice face by two levels at once. Upgrading a die face means that you can replace it with a die face from a higher pool for free. The level of a pool is determined by the cost of the face.<br />To upgrade a die face by one level, choose one of your die faces, then take a die face from the pool of the next level. You may now forge that die face.<br />Note: If the chosen face has no equivalent in the Sanctuary, then it is considered a level 0.<br />Note 2: If the pool from which you would take your upgraded die face is empty, then choose a die face from the next non-empty pool.'),
];

// Will contain the initialisation number of each side in the pool
// usage of dice_sides with pool number
$this->sides_init = [
	1  => ["G3", "G3", "G3", "G3"],
	2  => ["MS1", "MS1", "MS1", "MS1"],
	3  => ["G4", "G4", "G4", "G4"],
	4  => ["FS1", "FS1", "FS1", "FS1"],
	5  => ["G6", "1Gor1FSor1MS", "V1FS1", "G2MS1"],
	6  => ["G3orV2", "G3orV2", "G3orV2", "G3orV2"],
	7  => ["MS2", "MS2", "MS2", "MS2"],
	8  => ["FS2", "FS2", "FS2", "FS2"],
	9  => ["V3", "V3", "V3", "V3"],
	10 => ["V4", "V2MS2", "2Gor2FSor2MS","G1V1FS1MS1" ],
	11 => ["mirror","mirror","mirror","mirror"],
	12 => ['redShield', 'greenShield', 'yellowShield', 'blueShield'],
	13 => ['ship', 'ship', 'ship', 'ship'],
	14 => ['redBoar', 'greenBoar', 'yellowBoar', 'blueBoar'],
	15 => ["triple", "triple", "triple", "triple"],
	16 => ["redMisfortune", "blueMisfortune", "yellowMisfortune", "greenMisfortune"],
	17 => ["sunGolem", "sunGolem", "sunGolem", "sunGolem"],
	18 => ["L1V1G2", "G3AS1", "L1V1G2", "G3AS1"],
	19 => ["moonGolem", "moonGolem", "moonGolem", "moonGolem"],
	20 => ["titanRedShield", "titanBlueShield", "titanYellowShield", "titanGreenShield"],
];

$this->pools = array (
    1 => array (1, 2),
    2 => array (3, 4),
    3 => array (5),
    4 => array (6),
    5 => array (7),
    6 => array (8, 9),
    7 => array (10)
);

$this->powers = array(
	'initHammer'          => clienttranslate('gain a new hammer'),
	'increaseResLimit'    => clienttranslate('increases its resources max limit'),
	'throw1'              => clienttranslate('gets a minor blessing'),
	'steal2'              => clienttranslate('chooses two opponents dices and get displayed resources'),
	'boarForge'           => clienttranslate('chooses a player that will forge the side'),
	'tokenCerberus'       => clienttranslate('gets a Cerberus Token'),
	'side3x'              => clienttranslate('must forge a [triple]'),
	'fullThrow2'          => clienttranslate('gets 2 divine blessings (Cancer)'),
	'fullThrow2Transform' => clienttranslate('gets 2 divine blessings and may convert each [MS] and [FS] into 2 [VP] (Sentinel)'),
	'goldToVP'            => clienttranslate('can convert 3 [G] for 4 [VP]'),
	'3G3M'                => clienttranslate('gains 3 [G] and 3 [MS]'),
	'choose1GMF'          => clienttranslate('can choose one resource'),
	'sideShip'            => clienttranslate('must forge a [ship]'),
	'looseThrow'          => clienttranslate('will make the other players loose their throw'),
	'shieldForge'         => clienttranslate('must forge a Shield'),
	'tokenTriton'         => clienttranslate('gets a Triton Token'),
	'sideMirror'          => clienttranslate('must forge a [mirror]'),
	'4Throws'             => clienttranslate('gets 4 minor blessings (Sphinx)'),
	'4ThrowsTransform'    => clienttranslate('gets 4 minor blessings and may convert each [G] into 1 [VP] (Cyclops)'),
	'scoreForgedSides'    => clienttranslate('gets 1 [VP] for each of his discarded side(s)'),
	'nympheReinforcement' => clienttranslate('gets a lot of [VP]'),
	'diceSwap'            => clienttranslate('dice are being swapped'),
    //TODO: add new cards
    'freeExploit'         => '',
    "forgeVP"             => clienttranslate('must forge the selected side'),
    // Goddess
	'sideMoonGolem'       => clienttranslate('must forge a [moonGolem]'),
	'sideSunGolem'        => clienttranslate('must forge a [sunGolem]'),
	"forge4G"             => clienttranslate('must forge a side'),
	// Titan
	"sideDogged"          => clienttranslate('must forge a Dogged side'),
	"sideShieldRebellion" => clienttranslate('must forge a Shield'),
	"sideMisfortune"	  => clienttranslate('chooses a player that will forge the side'),

);

$memory_power_you = clienttranslate('select the reward to put on an island');

$this->powers_you = array(
	'initHammer'          => clienttranslate('you gain a new hammer'),
	'increaseResLimit'    => clienttranslate('you increase your resources max limit'),
	'throw1'              => clienttranslate('you get a minor blessing'),
	'steal2'              => clienttranslate('you choose two opponents dices and get displayed resources'),
	'boarForge'           => clienttranslate('you choose a player that will forge the side'),
	'tokenCerberus'       => clienttranslate('you get a Cerberus Token'),
	'side3x'              => clienttranslate('you must forge a [triple]'),
	'fullThrow2'          => clienttranslate('you get 2 divine blessings (Cancer)'),
	'fullThrow2Transform' => clienttranslate('you get 2 divine blessings and may convert each [MS] and [FS] into 2 [VP] (Sentinel)'),
	'goldToVP'            => clienttranslate('you can convert 3 [G] for 4 [VP]'),
	'3G3M'                => clienttranslate('you gain 3 [G] and 3 [MS]'),
	'choose1GMF'          => clienttranslate('you can choose one resource'),
	'sideShip'            => clienttranslate('you must forge a [ship]'),
	'looseThrow'          => clienttranslate('you will make the other players loose their throw'),
	'shieldForge'         => clienttranslate('you must forge a Shield'),
	'tokenTriton'         => clienttranslate('you get a Triton Token'),
	'sideMirror'          => clienttranslate('you must forge a [mirror]'),
	'4Throws'             => clienttranslate('you get 4 minor blessings (Sphinx)'),
	'4ThrowsTransform'    => clienttranslate('you get 4 minor blessings and may convert each [G] into 1 [VP] (Cyclops)'),
	'scoreForgedSides'    => clienttranslate('you get 1 [VP] for each of his discarded side(s)'),
	'nympheReinforcement' => clienttranslate('you get a lot of [VP]'),
	'diceSwap'            => clienttranslate('you dice are being swapped'),
    'forgeEverywhere'     => clienttranslate('you must forge a side'),
    "forgeVP"             => clienttranslate('you must forge the selected side'),
    // TODO: add new cards
    // Goddess
	'sideMoonGolem'       => clienttranslate('you must forge a [moonGolem]'),
	'sideSunGolem'        => clienttranslate('you must forge a [sunGolem]'),
	"forge4G"             => clienttranslate('you must forge a side'),
	// Titan
	"sideDogged"          => clienttranslate('must forge a Dogged side'),
	"sideShieldRebellion" => clienttranslate('must forge a Shield'),
	"memoryTokens"		  => $memory_power_you,
	"sideMisfortune"	  => clienttranslate('chooses a player that will forge the side'),
);



$treasure = clienttranslate("If you are the first player to reach this spot, take one of the available Treasure tokens of your choice and gain the highest reward");

$this->maze = array(
  1 => array (
    "path"      => [2,9,14],
    "reverse"   => [],
    "reward"    => "start",
    "hasGold"   => false,
    "needChoice"=> false,
    "description" => ""
    ),
  2 => array (
    "path"      => [3,9],
    "reverse"   => [1],
    "reward"    => "FS1",
    "hasGold"   => false,
    "needChoice"=> false,
    "description" => clienttranslate("Gain 1 [FS]")
    ),
  3 => array (
    "path"      => [4],
    "reverse"   => [2],
    "reward"    => "V3",
    "hasGold"   => false,
    "needChoice"=> false,
    "description" => clienttranslate("Gain 3 [VP]")
    ),
  4 => array (
    "path"      => [5,10],
    "reverse"   => [3],
    "reward"    => "G6orV3",
    //debug
    //"reward"    => "G6",
    "hasGold"   => true,
    "needChoice"=> true,
    "description" => clienttranslate("Gain 6 [G] or 3 [VP]")
    ),
  5 => array (
    "path"      => [6],
    "reverse"   => [4],
    "reward"    => "V5",
    "hasGold"   => false,
    "needChoice"=> false,
    "description" => clienttranslate("Gain 5 [VP]")
    ),
  6 => array (
    "path"      => [7,12],
    "reverse"   => [5],
    "reward"    => "FS1MS1V3",
    "hasGold"   => false,
    "needChoice"=> false,
    "description" => clienttranslate("Gain 1 [FS] and 1 [MS] and 3 [VP]")
    ),
  7 => array (
    "path"      => [8],
    "reverse"   => [6],
    "reward"    => "steal2VP",
    "hasGold"   => false,
    "needChoice"=> false,
    "description" => clienttranslate("All players (except you) lose 2 [VP]. You then gain the total amount of lost [VP]")
    ),
  8 => array (
    "path"      => [13],
    "reverse"   => [7],
    "reward"    => "treasure",
    "hasGold"   => false,
    "needChoice"=> false,
    "description" => $treasure
    ),
  9 => array (
    "path"      => [10],
    "reverse"   => [1, 2],
    "reward"    => "celestialRoll",
    "hasGold"   => false,
    "needChoice"=> true,
    "description" => clienttranslate("Roll the Celestial die once")
    ),
  10 => array (
    "path"      => [11],
    "reverse"   => [4, 9, 16],
    "reward"    => "forgeShip",
    "hasGold"   => false,
    "needChoice"=> false,
    "description" => clienttranslate("You may immediately forge a die face from the Sanctuary if you spend the required [G], -2 [G]")
    ),
  11 => array (
    "path"      => [12],
    "reverse"   => [10],
    "reward"    => "G6",
    "hasGold"   => true,
    "needChoice"=> false,
    "description" => clienttranslate("Gain 6 [G]")
    ),
  12 => array (
    "path"      => [13],
    "reverse"   => [6, 11, 19],
    "reward"    => "treasure",
    "hasGold"   => false,
    "needChoice"=> false,
    "description" => $treasure
    ),
  13 => array (
    "path"      => [24],
    "reverse"   => [8, 12, 23],
    "reward"    => "V3",
    "hasGold"   => false,
    "needChoice"=> false,
    "description" => clienttranslate("Gain 3 [VP]")
    ),
  14 => array (
    "path"      => [15],
    "reverse"   => [1],
    "reward"    => "MS1",
    "hasGold"   => false,
    "needChoice"=> false,
    "description" => clienttranslate("Gain 1 [MS]")
    ),
  15 => array (
    "path"      => [16],
    "reverse"   => [14],
    "reward"    => "G3orMS1orFS1",
    "hasGold"   => true,
    "needChoice"=> true,
    "description" => clienttranslate("Gain 3 [G] or 1 [MS] or 1 [FS]")
    ),
  16 => array (
    "path"      => [17, 10],
    "reverse"   => [15],
    "reward"    => "celestialRoll",
    "hasGold"   => false,
    "needChoice"=> true,
    "description" => clienttranslate("Roll the Celestial die once")
    ),
  17 => array (
    "path"      => [18],
    "reverse"   => [16],
    "reward"    => "G3orMS1orFS1",
    "hasGold"   => true,
    "needChoice"=> true,
    "description" => clienttranslate("Gain 3 [G] or 1 [MS] or 1 [FS]")
    ),
  18 => array (
    "path"      => [19],
    "reverse"   => [17],
    "reward"    => "forge",
    "hasGold"   => false,
    "needChoice"=> false,
    "description" => clienttranslate("You may immediately forge a die face from the Sanctuary if you spend the required [G].")
    ),
  19 => array (
    "path"      => [20, 12],
    "reverse"   => [18],
    "reward"    => "celestialRoll",
    "hasGold"   => false,
    "needChoice"=> true,
    "description" => clienttranslate("Roll the Celestial die once")
    ),
  20 => array (
    "path"      => [21],
    "reverse"   => [19],
    "reward"    => "MS2orV3",
    "hasGold"   => false,
    "needChoice"=> true,
    "description" => clienttranslate("Gain 2 [MS] or 3 [VP]")
    ),
  21 => array (
    "path"      => [22],
    "reverse"   => [20],
    "reward"    => "treasure",
    "hasGold"   => false,
    "needChoice"=> false,
    "description" => $treasure
    ),
  22 => array (
    "path"      => [23],
    "reverse"   => [18],
    "reward"    => "MS2orFS2",
    "hasGold"   => false,
    "needChoice"=> true,
    "description" => clienttranslate("Gain 2 [MS] or 2 [FS]")
    ),
  23 => array (
    "path"      => [13],
    "reverse"   => [22],
    "reward"    => "celestialRollx2",
    "hasGold"   => false,
    "needChoice"=> true,
    "description" => clienttranslate("Roll the Celestial die twice")
    ),
  24 => array (
    "path"      => [25],
    "reverse"   => [13],
    "reward"    => "forge",
    "hasGold"   => false,
    "needChoice"=> true,
    "description" => clienttranslate("You may immediately forge a die face from the Sanctuary if you spend the required [G].")
    ),
  25 => array (
    "path"      => [26],
    "reverse"   => [24],
    "reward"    => "celestialRoll",
    "hasGold"   => false,
    "needChoice"=> true,
    "description" => clienttranslate("Roll the Celestial die once")
    ),
  26 => array (
    "path"      => [27],
    "reverse"   => [25],
    "reward"    => "G3orMS1orFS1",
    "hasGold"   => true,
    "needChoice"=> true,
    "description" => clienttranslate("Gain 3 [G] or 1 [MS] or 1 [FS]")
    ),
  27 => array (
    "path"      => [28],
    "reverse"   => [26],
    "reward"    => "convert6Gto6VP",
    "hasGold"   => false,
    "needChoice"=> false,
    "description" => clienttranslate("You may spend 6 [G] to gain 6 [VP] (once).")
    ),
  28 => array (
    "path"      => [29],
    "reverse"   => [27],
    "reward"    => "celestialRollx2",
    "hasGold"   => false,
    "needChoice"=> true,
    "description" => clienttranslate("Roll the Celestial die twice")
    ),
  29 => array (
    "path"      => [30],
    "reverse"   => [28],
    "reward"    => "convertMS2to8VP",
    "hasGold"   => false,
    "needChoice"=> false,
    "description" => clienttranslate("You may spend 2 [MS] to gain 8 [VP] (once).")
    ),
  30 => array (
    "path"      => [31],
    "reverse"   => [29],
    "reward"    => "celestialRoll",
    "hasGold"   => false,
    "needChoice"=> true,
    "description" => clienttranslate("Roll the Celestial die once")
    ),
  31 => array (
    "path"      => [32],
    "reverse"   => [30],
    "reward"    => "none",
    "hasGold"   => false,
    "needChoice"=> false,
    "description" => ""
    ),
  32 => array (
    "path"      => [33],
    "reverse"   => [31],
    "reward"    => "none",
    "hasGold"   => false,
    "needChoice"=> false,
    "description" => ""
    ),
  33 => array (
    "path"      => [34],
    "reverse"   => [32],
    "reward"    => "scoreForgedSides",
    "hasGold"   => false,
    "needChoice"=> false,
    "description" => clienttranslate("Gain 1 [VP] for each die face that you have forged")
    ),
  34 => array (
    "path"      => [35],
    "reverse"   => [33],
    "reward"    => "none",
    "hasGold"   => false,
    "needChoice"=> false,
    "description" => ""
    ),
  35 => array (
    "path"      => [36],
    "reverse"   => [34],
    "reward"    => "none",
    "hasGold"   => false,
    "needChoice"=> false,
    "description" => ""
    ),
  36 => array (
    "path"      => [],
    "reverse"   => [35],
    "reward"    => "V15",
    "hasGold"   => false,
    "needChoice"=> false,
    "description" => clienttranslate("Gain 15 [VP]")
    ),
  'firstFinish' => array (
    "path"      => [],
    "reverse"   => [],
    "reward"    => "firstFinish",
    "hasGold"   => false,
    "needChoice"=> false,
    "description" => clienttranslate("If you are the first player to reach the Goddess space:<br />Place your dice on your Hero Inventory on the die faces that you wish. Apply the effects of the chosen faces, as if you were receiving a <b>divine blessing</b>.")
    ),
);

$loyaltyEndTooltip   = clienttranslate('<hr />Note: Always consider the effect of the zone where your token is when you apply the die face effect.<br />Note 2: When you receive a <b>divine blessing</b>, apply the effect each time that you meet the
activation condition, ie twice if both of your dice meet that condition.<br />Note 3:  Converting resources into [VP] thanks to a “Sentinel” or “Cyclop” card does not trigger the [hourglass] extra [VP] effect<br />Note 4: You cannot use [G] gained through [hourglass] extra [G] effects to apply the “Cyclop” card effect');

$this->titan_passives = [
	'titanPassive1' => [
		'description' => clienttranslate('[hourglass] - <b>activation conditions</b>: When you are the first one to perform a Heroic feat in this game.<br />[hourglass]: Gain 5 [VP].<hr />[hourglass] - <b>activation conditions</b>: When you are not and if you did not perform it during this game<br />[hourglass]: Gain 3 [VP].')
	],
	'titanPassive2' => [
		'description' => clienttranslate('[hourglass] - <b>activation conditions</b>: When you are the first one to perform a Heroic feat in this game.<br />[hourglass]: Gain 3 [VP].<hr />[hourglass] - <b>activation conditions</b>: When you are not and if you did not perform it during this game<br />[hourglass]: Gain 2 [VP].')
	],
	'loyaltyPassive1' => [
		'description' => clienttranslate('[hourglass] - <b>activation conditions</b>: When you gain [G] when receiving a <b>divine blessing</b> or a <b>minor blessing</b> with <b>your own dice</b>.<br />[hourglass]: Add 1 extra [G] to your reserve.') . $loyaltyEndTooltip
	],
	'loyaltyPassive2' => [
		'description' => clienttranslate('[hourglass] - <b>activation conditions</b>: When you gain [G] when receiving a <b>divine blessing</b> or a <b>minor blessing</b> with <b>your own dice</b>.<br />[hourglass]: Add 1 extra [G] and 1 extra [VP] to your reserve.') . $loyaltyEndTooltip
	],
	'loyaltyPassive3' => [
		'description' => clienttranslate('[hourglass] - <b>activation conditions</b>: When you gain [VP] when receiving a <b>divine blessing</b> or a <b>minor blessing</b> with <b>your own dice</b>.<br />[hourglass]: Add 1 extra [VP] to your reserve.<hr />[hourglass] - <b>activation conditions</b>: When you gain [G] when receiving a <b>divine blessing</b> or a <b>minor blessing</b> with <b>your own dice</b>.<br />[hourglass]: Add 1 extra [G] and 1 extra [VP] to your reserve.') . $loyaltyEndTooltip
	],
];

$this->titan_board = [
	1 => [
		'toDark'  => null,
		'toLight' => 2,
		'vp'      => -25,
		'bonus'   => 'titanPassive1',
	],
	2 => [
		'toDark'  => 1,
		'toLight' => 3,
		'vp'      => -25,
		'bonus'   => 'titanPassive1',
	],
	3 => [
		'toDark'  => 1,
		'toLight' => 5,
		'vp'      => -20,
		'bonus'   => 'titanPassive1',
	],
	4 => [
		'toDark'  => 3,
		'toLight' => 5,
		'vp'      => -17,
		'bonus'   => 'titanPassive1',
	],
	5 => [
		'toDark'  => 4,
		'toLight' => 6,
		'vp'      => -17,
		'bonus'   => 'titanPassive1',
	],
	6 => [
		'toDark'  => 4,
		'toLight' => 7,
		'vp'      => -14,
		'bonus'   => 'titanPassive1',
	],
	7 => [
		'toDark'  => 6,
		'toLight' => 8,
		'vp'      => -12,
		'bonus'   => 'titanPassive1',
	],
	8 => [
		'toDark'  => 7,
		'toLight' => 9,
		'vp'      => -10,
		'bonus'   => 'titanPassive1',
	],
	9 => [
		'toDark'  => 8,
		'toLight' => 10,
		'vp'      => -8,
		'bonus'   => 'titanPassive2',
	],
	10 => [
		'toDark'  => 9,
		'toLight' => 11,
		'vp'      => -7,
		'bonus'   => 'titanPassive2',
	],
	11 => [
		'toDark'  => 10,
		'toLight' => 12,
		'vp'      => -6,
		'bonus'   => 'titanPassive2',
	],
	12 => [
		'toDark'  => 11,
		'toLight' => 13,
		'vp'      => -5,
		'bonus'   => 'titanPassive2',
	],
	13 => [
		'toDark'  => 12,
		'toLight' => 14,
		'vp'      => -3,
		'bonus'   => null,
	],
	14 => [
		'toDark'  => 13,
		'toLight' => 15,
		'vp'      => -2,
		'bonus'   => null,
	],
	15 => [
		'toDark'  => 14,
		'toLight' => 16,
		'vp'      => -1,
		'bonus'   => null,
	],
	16 => [  // start tile
		'toDark'  => 15,
		'toLight' => 17,
		'vp'      => 0,
		'bonus'   => null,
	],
	17 => [
		'toDark'  => 16,
		'toLight' => 18,
		'vp'      => 1,
		'bonus'   => null,
	],
	18 => [
		'toDark'  => 17,
		'toLight' => 19,
		'vp'      => 2,
		'bonus'   => null,
	],
	19 => [
		'toDark'  => 18,
		'toLight' => 20,
		'vp'      => 3,
		'bonus'   => null,
	],
	20 => [
		'toDark'  => 19,
		'toLight' => 21,
		'vp'      => 5,
		'bonus'   => 'loyaltyPassive1',
	],
	21 => [
		'toDark'  => 20,
		'toLight' => 22,
		'vp'      => 6,
		'bonus'   => 'loyaltyPassive1',
	],
	22 => [
		'toDark'  => 21,
		'toLight' => 23,
		'vp'      => 7,
		'bonus'   => 'loyaltyPassive1',
	],
	23 => [
		'toDark'  => 22,
		'toLight' => 24,
		'vp'      => 8,
		'bonus'   => 'loyaltyPassive2',
	],
	24 => [
		'toDark'  => 23,
		'toLight' => 25,
		'vp'      => 10,
		'bonus'   => 'loyaltyPassive2',
	],
	25 => [
		'toDark'  => 24,
		'toLight' => 26,
		'vp'      => 12,
		'bonus'   => 'loyaltyPassive2',
	],
	26 => [
		'toDark'  => 25,
		'toLight' => 27,
		'vp'      => 14,
		'bonus'   => 'loyaltyPassive3',
	],
	27 => [
		'toDark'  => 26,
		'toLight' => 28,
		'vp'      => 17,
		'bonus'   => 'loyaltyPassive3',
	],
	28 => [
		'toDark'  => 27,
		'toLight' => 29,
		'vp'      => 17,
		'bonus'   => 'loyaltyPassive3',
	],
	29 => [
		'toDark'  => 27,
		'toLight' => 30,
		'vp'      => 20,
		'bonus'   => 'loyaltyPassive3',
	],
	30 => [
		'toDark'  => 29,
		'toLight' => 31,
		'vp'      => 20,
		'bonus'   => 'loyaltyPassive3',
	],
	31 => [
		'toDark'  => 29,
		'toLight' => null,
		'vp'      => 25,
		'bonus'   => 'loyaltyPassive3',
	],
];

$this->labyrinth_paths = array(
	1  => [2,9,14],
	2  => [3,9],
	3  => [4],
	4  => [5,10],
	5  => [6],
	6  => [7,12],
	7  => [8],
	8  => [13],
	9  => [10],
	10 => [11],
	11 => [12],
	12 => [13],
	13 => [24],
	14 => [15],
	15 => [16],
	16 => [10,17],
	17 => [18],
	18 => [19],
	19 => [12,20],
	20 => [21],
	21 => [22],
	22 => [23],
	23 => [13],
	24 => [25],
	25 => [26],
	26 => [27],
	27 => [28],
	28 => [29],
	29 => [30],
	30 => [31],
	31 => [32],
	32 => [33],
	33 => [34],
	34 => [35],
	35 => [36],
	36 => [],
);

$this->labyrinth_rewards = array(
	1  => null,
	2  => "1FS",
	3  => "3VP",
	4  => "6Gor3VP",
	5  => "5VP",
	6  => "1MS1FS3VP",
	7  => "steal2VP",
	8  => "randomResource1",
	9  => "1celestialRoll",
	10 => "ship2",
	11 => "",
	12 => "",
	13 => "",
	14 => "",
	15 => "",
	16 => "",
	17 => "",
	18 => "",
	19 => "",
	20 => "",
	21 => "",
	22 => "",
	23 => "",
	24 => "",
	25 => "",
	26 => "",
	27 => "",
	28 => "",
	29 => "",
	30 => "",
	31 => "",
	32 => "",
	33 => "",
	34 => "",
	35 => "",
	36 => "",
);
