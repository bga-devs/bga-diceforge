<?php

namespace Bga\Games\diceforge\Registries;

use Bga\Games\diceforge\Framework\Entities\StatDefinition;

enum PlayerStatRegistry
{
    case nb_vp_die;
    case nb_vp_exploit;
    case nb_vp_hammer;
    case nb_vp_ancient;
    case nb_vp_nymph;
    case nb_vp_bear;
    case nb_vp_guardian;
    case nb_vp_typhon;
    case nb_vp_boar;
    case nb_vp_tree;
    case nb_throws;
    case nb_boar;
    case nb_twins;
    case nb_side_forge;
    case nb_exploit_buy;
    case nb_reinforcement;
    case nb_won_gold;
    case nb_loss_gold;
    case nb_used_gold;
    case nb_won_fireshard;
    case nb_loss_fireshard;
    case nb_used_fireshard;
    case nb_won_moonshard;
    case nb_loss_moonshard;
    case nb_used_moonshard;
    case nb_second_action;
    case nb_has_ousted;
    case nb_been_ousted;
    case nb_action_forge;
    case nb_action_exploit;
    case nb_ressource_choice;
    case nb_minotaur_vp;
    case nb_minotaur_gold;
    case nb_minotaur_fireshard;
    case nb_minotaur_moonshard;
    case turns_number;
    case nb_won_ancientshard;
    case nb_loss_ancientshard;
    case nb_used_ancientshard;
    case nb_minotaur_ancientshard;

    public function definition(): StatDefinition
    {
        return match ($this) {
            self::nb_vp_die              => new StatDefinition(id: 11, name: 'VP from Dice', section: 'player'),
            self::nb_vp_exploit          => new StatDefinition(id: 12, name: 'VP from Heroic feats', section: 'player'),
            self::nb_vp_hammer           => new StatDefinition(id: 13, name: 'VP from Hammer', section: 'player'),
            self::nb_vp_ancient          => new StatDefinition(id: 14, name: 'VP from the Elder', section: 'player'),
            self::nb_vp_nymph            => new StatDefinition(id: 48, name: 'VP from the Nymph', section: 'player'),
            self::nb_vp_bear             => new StatDefinition(id: 15, name: 'VP from Bear', section: 'player'),
            self::nb_vp_guardian         => new StatDefinition(id: 16, name: "VP from Guardian's Shield / Chaos", section: 'player'),
            self::nb_vp_typhon           => new StatDefinition(id: 17, name: 'VP from Typhon', section: 'player'),
            self::nb_vp_boar             => new StatDefinition(id: 46, name: 'VP from Tenacious boar', section: 'player'),
            self::nb_vp_tree             => new StatDefinition(id: 49, name: 'VP from Tree', section: 'player'),
            self::nb_throws              => new StatDefinition(id: 22, name: 'Number of dice throws', section: 'player'),
            self::nb_boar                => new StatDefinition(id: 47, name: 'Number of times Tenacious Boar was activated', section: 'player'),
            self::nb_twins               => new StatDefinition(id: 50, name: 'Number of times Twins was activated', section: 'player'),
            self::nb_side_forge          => new StatDefinition(id: 23, name: 'Number of forged sides', section: 'player'),
            self::nb_exploit_buy         => new StatDefinition(id: 24, name: 'Number of bought heroic feats', section: 'player'),
            self::nb_reinforcement       => new StatDefinition(id: 25, name: 'Number of played reinforcements', section: 'player'),
            self::nb_won_gold            => new StatDefinition(id: 26, name: 'Amount of gold from the dice', section: 'player'),
            self::nb_loss_gold           => new StatDefinition(id: 27, name: 'Amount of lost gold', section: 'player'),
            self::nb_used_gold           => new StatDefinition(id: 28, name: 'Number of used gold', section: 'player'),
            self::nb_won_fireshard       => new StatDefinition(id: 29, name: 'Amount of fireshard from the dice', section: 'player'),
            self::nb_loss_fireshard      => new StatDefinition(id: 30, name: 'Amount of lost fireshard', section: 'player'),
            self::nb_used_fireshard      => new StatDefinition(id: 31, name: 'Number of used fireshard', section: 'player'),
            self::nb_won_moonshard       => new StatDefinition(id: 32, name: 'Amount of moonshard from the dice', section: 'player'),
            self::nb_loss_moonshard      => new StatDefinition(id: 33, name: 'Amount of lost moonshard', section: 'player'),
            self::nb_used_moonshard      => new StatDefinition(id: 34, name: 'Number of used moonshard', section: 'player'),
            self::nb_second_action       => new StatDefinition(id: 35, name: 'Number of second action taken', section: 'player'),
            self::nb_has_ousted          => new StatDefinition(id: 36, name: 'Number of times player has ousted another player', section: 'player'),
            self::nb_been_ousted         => new StatDefinition(id: 37, name: 'Number of times player has been ousted', section: 'player'),
            self::nb_action_forge        => new StatDefinition(id: 38, name: 'Number of forge action', section: 'player'),
            self::nb_action_exploit      => new StatDefinition(id: 39, name: 'Number of heroic feat action', section: 'player'),
            self::nb_ressource_choice    => new StatDefinition(id: 40, name: 'Number of times player choosed a ressource', section: 'player'),
            self::nb_minotaur_vp         => new StatDefinition(id: 41, name: 'Minotaur: Amount of lost VP', section: 'player'),
            self::nb_minotaur_gold       => new StatDefinition(id: 42, name: 'Minotaur: Amount of lost gold', section: 'player'),
            self::nb_minotaur_fireshard  => new StatDefinition(id: 43, name: 'Minotaur: Amount of lost fireshard', section: 'player'),
            self::nb_minotaur_moonshard  => new StatDefinition(id: 44, name: 'Minotaur: Amount of lost moonshard', section: 'player'),
            self::turns_number           => new StatDefinition(id: 45, name: 'Number of turns', section: 'player'),
            self::nb_won_ancientshard    => new StatDefinition(id: 52, name: 'Amount of moonshard from the dice', section: 'player'),
            self::nb_loss_ancientshard   => new StatDefinition(id: 53, name: 'Amount of lost moonshard', section: 'player'),
            self::nb_used_ancientshard   => new StatDefinition(id: 54, name: 'Number of used moonshard', section: 'player'),
            self::nb_minotaur_ancientshard => new StatDefinition(id: 55, name: 'Minotaur: Amount of lost ancientshard', section: 'player'),
        };
    }

    public function id(): int
    {
        return $this->definition()->id;
    }

    public static function fromName(string $name): self
    {
        foreach (self::cases() as $case) {
            if ($case->name === $name) {
                return $case;
            }
        }
        throw new \ValueError("Unknown player stat: '{$name}'");
    }
}
