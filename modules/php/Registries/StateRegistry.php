<?php

namespace Bga\Games\diceforge\Registries;

use Bga\GameFramework\StateType;
use Bga\Games\diceforge\Framework\Entities\State;

enum StateRegistry
{
    case BEGIN_TURN;
    case BEGIN_PLAYER_TURN;
    case BLESSING;
    case RESSOURCE_CHOICE;
    case REINFORCEMENT;
    case PLAYER_ACTION;
    case FORGE_BUY;
    case FORGE_DICE;
    case SECOND_ACTION;
    case EXPLOIT_BUY;
    case PLAYER_OUSTING;
    case OUSTED_PLAYER_CHOICE;
    case EXPLOIT_EFFECT;
    case END_PLAYER_TURN;
    case DOE_RESSOURCE_CHOICE;
    case EXPLOIT_RESSOURCE;
    case SCORING_END;
    case FORGE_SHIP;
    case DOE_FORGE_SHIP;
    case OUSTED_FORGE_SHIP;
    case EXPLOIT_FORGE_SHIP;
    case EXPLOIT_FORGE_BOAR;
    case DRAFT;
    case DRAFT_PLAYER;
    case MISFORTUNE;
    case END_GAME;

    public function definition(): State
    {
        return match ($this) {
            self::BEGIN_TURN => new State(
                id: 2,
                name: 'beginTurn',
                type: StateType::GAME,
                action: 'stBeginTurn',
            ),
            self::BEGIN_PLAYER_TURN => new State(
                id: 3,
                name: 'beginPlayerTurn',
                type: StateType::ACTIVE_PLAYER,
                description: clienttranslate('${actplayer} must roll the dice'),
                descriptionMyTurn: clienttranslate('${you} must roll your dice'),
                action: 'stBeginPlayerTurn',
                possibleActions: ['actRollDice'],
                updateGameProgression: true,
            ),
            self::BLESSING => new State(
                id: 4,
                name: 'divineBlessing',
                type: StateType::GAME,
                description: clienttranslate('Everyone rolls their dice'),
                descriptionMyTurn: clienttranslate('${you} roll your dice'),
                action: 'stBlessing',
            ),
            self::RESSOURCE_CHOICE => new State(
                id: 5,
                name: 'ressourceChoice',
                type: StateType::MULTIPLE_ACTIVE_PLAYER,
                description: clienttranslate('Everyone choose the resources'),
                descriptionMyTurn: clienttranslate('${you} choose the resources for the side ${loyalty}'),
                action: 'stRessourceChoice',
                possibleActions: ['actRessourceChoice', 'actSideChoice', 'actActionChoice', 'actUseCerberusToken', 'actUseTritonToken', 'actAutoHammer', 'actChooseMazePath', 'actChooseTreasure', 'actMazePowerConfirm', 'actPuzzleCelestial', 'actPuzzleMaze'],
            ),
            self::REINFORCEMENT => new State(
                id: 6,
                name: 'reinforcement',
                type: StateType::ACTIVE_PLAYER,
                description: clienttranslate('${actplayer} may activate reinforcement cards'),
                descriptionMyTurn: clienttranslate('${you} may activate reinforcement cards'),
                action: 'stReinforcement',
                possibleActions: ['actReinforcement', 'actReinforcementPass', 'actUseTritonToken', 'actAutoHammer'],
            ),
            self::PLAYER_ACTION => new State(
                id: 7,
                name: 'playerAction',
                type: StateType::ACTIVE_PLAYER,
                description: clienttranslate('Turn ${currentTurn}/${maxTurn} : ${actplayer} may choose an action'),
                descriptionMyTurn: clienttranslate('Turn ${currentTurn}/${maxTurn} : ${you} may choose an action'),
                possibleActions: ['actBuyForge', 'actEndForge', 'actBuyExploit', 'actEndPlayerTurn', 'actUseTritonToken', 'actAutoHammer', 'actUseCompanion', 'actUseScepter', 'actCancelScepter'],
            ),
            self::FORGE_BUY => new State(
                id: 8,
                name: 'forgeBuy',
                type: StateType::ACTIVE_PLAYER,
            ),
            self::FORGE_DICE => new State(
                id: 9,
                name: 'forgeDice',
                type: StateType::ACTIVE_PLAYER,
            ),
            self::SECOND_ACTION => new State(
                id: 10,
                name: 'secondAction',
                type: StateType::ACTIVE_PLAYER,
                description: clienttranslate('${actplayer} is choosing if another action is played'),
                descriptionMyTurn: clienttranslate('Do ${you} wish to take another action?'),
                possibleActions: ['actSecondAction', 'actUseTritonToken', 'actAutoHammer', 'actUseCompanion', 'actUseScepter', 'actCancelScepter'],
            ),
            self::EXPLOIT_BUY => new State(
                id: 11,
                name: 'exploitBuy',
                type: StateType::ACTIVE_PLAYER,
            ),
            self::PLAYER_OUSTING => new State(
                id: 12,
                name: 'playerOusting',
                type: StateType::GAME,
                action: 'stOusting',
            ),
            self::OUSTED_PLAYER_CHOICE => new State(
                id: 13,
                name: 'playerOustingChoice',
                type: StateType::MULTIPLE_ACTIVE_PLAYER,
                description: clienttranslate('Players are choosing resources due to ousting'),
                descriptionMyTurn: clienttranslate('${you} choose the resources for the side  ${loyalty}'),
                possibleActions: ['actOustedRessources', 'actSideChoice', 'actActionChoice', 'actUseCerberusToken', 'actUseTritonToken', 'actAutoHammer', 'actChooseMazePath', 'actChooseTreasure', 'actMazePowerConfirm', 'actPuzzleCelestial', 'actPuzzleMaze'],
            ),
            self::EXPLOIT_EFFECT => new State(
                id: 14,
                name: 'exploitEffect',
                type: StateType::ACTIVE_PLAYER,
                description: clienttranslate('Effects are being played'),
                descriptionMyTurn: clienttranslate('Effects are being played'),
                action: 'stEffectExploit',
                possibleActions: ['actBuyForge', 'actExploitEnigma', 'actExploitBoar', 'actUseTritonToken', 'actAutoHammer', 'actBuyExploit', 'actCelestialUpgrade', 'actForgeNymphPass', 'actAncestorSelect', 'actMemoryToken'],
            ),
            self::END_PLAYER_TURN => new State(
                id: 15,
                name: 'endPlayerTurn',
                type: StateType::GAME,
                action: 'stEndPlayerTurn',
            ),
            self::DOE_RESSOURCE_CHOICE => new State(
                id: 16,
                name: 'doeRessourceChoice',
                type: StateType::MULTIPLE_ACTIVE_PLAYER,
                description: clienttranslate('Players are choosing resources'),
                descriptionMyTurn: clienttranslate('${you} choose the resources for the side  ${loyalty}'),
                possibleActions: ['actDoeTakeRessource', 'actActionChoice', 'actSideChoice', 'actUseCerberusToken', 'actUseTritonToken', 'actAutoHammer', 'actChooseMazePath', 'actChooseTreasure', 'actMazePowerConfirm', 'actPuzzleCelestial', 'actPuzzleMaze'],
            ),
            self::EXPLOIT_RESSOURCE => new State(
                id: 17,
                name: 'exploitRessource',
                type: StateType::MULTIPLE_ACTIVE_PLAYER,
                description: clienttranslate('Players are choosing resources'),
                descriptionMyTurn: clienttranslate('${you} choose the resources for the side ${loyalty}'),
                possibleActions: ['actExploitRessource', 'actSideChoice', 'actActionChoice', 'actUseCerberusToken', 'actUseTritonToken', 'actAutoHammer', 'actChooseMazePath', 'actChooseTreasure', 'actMazePowerConfirm', 'actPuzzleCelestial', 'actPuzzleMaze', 'actRessourceChoice'],
            ),
            self::SCORING_END => new State(
                id: 18,
                name: 'endScoring',
                type: StateType::GAME,
                action: 'stEndScoring',
                updateGameProgression: true,
            ),
            self::FORGE_SHIP => new State(
                id: 19,
                name: 'forgeShip',
                type: StateType::MULTIPLE_ACTIVE_PLAYER,
                description: clienttranslate('[${ship}] ${actplayer} may forge a side'),
                descriptionMyTurn: clienttranslate('[${ship}] ${you} may forge 1 side (cost ${minusCost})'),
                possibleActions: ['actBuyForge', 'actForgeShipPass', 'actUseTritonToken', 'actAutoHammer', 'actCelestialUpgrade', 'actCancelCelestial', 'actPuzzleCelestial'],
            ),
            self::DOE_FORGE_SHIP => new State(
                id: 20,
                name: 'doeForgeShip',
                type: StateType::MULTIPLE_ACTIVE_PLAYER,
                description: clienttranslate('[${ship}] ${actplayer} may forge a side'),
                descriptionMyTurn: clienttranslate('[${ship}] ${you} may forge 1 side (cost ${minusCost})'),
                possibleActions: ['actBuyForge', 'actForgeShipPass', 'actUseTritonToken', 'actAutoHammer', 'actCelestialUpgrade', 'actCancelCelestial'],
            ),
            self::OUSTED_FORGE_SHIP => new State(
                id: 21,
                name: 'oustedForgeShip',
                type: StateType::MULTIPLE_ACTIVE_PLAYER,
                description: clienttranslate('[${ship}] ${actplayer} may forge a side'),
                descriptionMyTurn: clienttranslate('[${ship}] ${you} may forge 1 side (cost ${minusCost})'),
                possibleActions: ['actBuyForge', 'actForgeShipPass', 'actCelestialUpgrade', 'actCancelCelestial', 'actPuzzleCelestial'],
            ),
            self::EXPLOIT_FORGE_SHIP => new State(
                id: 22,
                name: 'exploitForgeShip',
                type: StateType::MULTIPLE_ACTIVE_PLAYER,
                description: clienttranslate('[${ship}] ${actplayer} may forge a side'),
                descriptionMyTurn: clienttranslate('[${ship}] ${you} may forge 1 side (cost ${minusCost})'),
                possibleActions: ['actBuyForge', 'actForgeShipPass', 'actUseTritonToken', 'actAutoHammer', 'actCelestialUpgrade', 'actCancelCelestial', 'actPuzzleCelestial'],
            ),
            self::EXPLOIT_FORGE_BOAR => new State(
                id: 23,
                name: 'exploitForgeBoar',
                type: StateType::MULTIPLE_ACTIVE_PLAYER,
                description: clienttranslate('${actplayer} is forging a ${card_name} side'),
                descriptionMyTurn: clienttranslate('${you} must forge the selected ${card_name} side'),
                possibleActions: ['actBuyForge', 'actAutoHammer'],
            ),
            self::DRAFT => new State(
                id: 24,
                name: 'draftGame',
                type: StateType::GAME,
                action: 'stDraftGame',
            ),
            self::DRAFT_PLAYER => new State(
                id: 25,
                name: 'draft',
                type: StateType::ACTIVE_PLAYER,
                description: clienttranslate('Draft : ${actplayer} is choosing a card'),
                descriptionMyTurn: clienttranslate('Draft : ${you} must choose a card'),
                possibleActions: ['actDraft', 'actAutoHammer'],
            ),
            self::MISFORTUNE => new State(
                id: 26,
                name: 'misfortuneChoice',
                type: StateType::MULTIPLE_ACTIVE_PLAYER,
                description: clienttranslate('Mirror of Misfortune effect: Player is choosing resources'),
                descriptionMyTurn: clienttranslate('Mirror of Misfortune effect: ${you} choose the resources for the side  ${loyalty}'),
                action: 'stMisfortuneChoice',
                possibleActions: ['actMisfortuneChoice', 'actActionMisfortune', 'actAutoHammer'],
            ),
            self::END_GAME => new State(
                id: 99,
                name: 'endGame',
                type: StateType::MANAGER,
            ),
        };
    }

    public function id(): int
    {
        return $this->definition()->id;
    }

    public function name(): string
    {
        return $this->definition()->name;
    }

    public function type(): string
    {
        return $this->definition()->type;
    }

    public function action(): ?string
    {
        return $this->definition()->action;
    }

    public static function fromId(int $id): self
    {
        foreach (self::cases() as $case) {
            if ($case->id() === $id) {
                return $case;
            }
        }

        throw new \ValueError("Invalid state ID: {$id}");
    }
}
